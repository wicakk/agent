<?php

namespace App\Http\Controllers;

use App\Models\SalesActivity;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $company = $user->company;

        $salesQuery = Transaction::where('company_id', $company->id)
            ->where('type', 'sale')->where('status', '!=', 'cancelled');
        $visitQuery = SalesActivity::where('company_id', $company->id)->where('type', 'check_in');

        if ($user->isSales()) {
            $salesQuery->where('user_id', $user->id);
            $visitQuery->where('user_id', $user->id);
        }

        $totalStock = $company->products()->sum('stock_current');
        $lowStock   = $company->products()->whereColumn('stock_current', '<=', 'stock_minimum')->count();
        $salesToday = (clone $salesQuery)->whereDate('created_at', today())->sum('total');
        $salesTotalMonth = (clone $salesQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total');
        $visitToday = (clone $visitQuery)->whereDate('activity_at', today())->count();
        $activeSales = $company->users()->where('role', 'sales')->where('is_active', true)->count();
        $pendingDistributions = $company->distributions()->where('status', 'pending')->count();
        $inTransitDistributions = $company->distributions()->where('status', 'in_transit')->count();

        $salesChart = (clone $salesQuery)
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
            ->groupBy(DB::raw('DATE(created_at)'))->orderBy('date')->get()->keyBy('date');

        $chartLabels = [];
        $chartData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('D');
            $chartData[]   = (float)($salesChart[$date]->total ?? 0);
        }

        $stockByCategory = $company->products()
            ->select('category', DB::raw('SUM(stock_current) as total'))
            ->groupBy('category')->orderByDesc('total')->get();

        $recentActivities = SalesActivity::where('company_id', $company->id)
            ->with(['user', 'store'])
            ->when($user->isSales(), fn($q) => $q->where('user_id', $user->id))
            ->latest('activity_at')->take(8)->get();

        $recentDistributions = $company->distributions()
            ->with(['driver', 'store', 'items'])->latest()->take(5)->get();

        $topSales = User::where('company_id', $company->id)->where('role', 'sales')
            ->withCount(['transactions as sales_count' => fn($q) => $q->where('type','sale')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)])
            ->withSum(['transactions as sales_total' => fn($q) => $q->where('type','sale')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)], 'total')
            ->orderByDesc('sales_total')->take(5)->get();

        return view('dashboard.index', compact(
            'totalStock','lowStock','salesToday','salesTotalMonth',
            'visitToday','activeSales','pendingDistributions','inTransitDistributions',
            'chartLabels','chartData','stockByCategory',
            'recentActivities','recentDistributions','topSales'
        ));
    }
}
