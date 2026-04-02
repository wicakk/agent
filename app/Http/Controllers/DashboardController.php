<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesByBranch;
use App\Models\SalesActivity;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ScopesByBranch;

    public function index()
    {
        $user    = Auth::user();
        $company = $user->company;

        // Base queries scoped to branch
        $salesQuery = $this->branchScope(
            Transaction::query()->where('type', 'sale')->where('status', '!=', 'cancelled')
        );
        $visitQuery = $this->branchScope(
            SalesActivity::query()->where('type', 'check_in')
        );

        // Further scope for Sales role
        if ($user->isSales()) {
            $salesQuery->where('user_id', $user->id);
            $visitQuery->where('user_id', $user->id);
        }

        // Stats
        $totalStock = $this->branchScope(\App\Models\Product::query())->sum('stock_current');
        $lowStock   = $this->branchScope(\App\Models\Product::query())
            ->whereColumn('stock_current', '<=', 'stock_minimum')->count();

        $salesToday      = (clone $salesQuery)->whereDate('created_at', today())->sum('total');
        $salesTotalMonth = (clone $salesQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total');
        $visitToday      = (clone $visitQuery)->whereDate('activity_at', today())->count();
        $activeSales     = $this->branchScope(User::query())->where('role', 'sales')->where('is_active', true)->count();

        $pendingDistributions   = $this->branchScope(\App\Models\Distribution::query())->where('status', 'pending')->count();
        $inTransitDistributions = $this->branchScope(\App\Models\Distribution::query())->where('status', 'in_transit')->count();

        // Chart
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

        $stockByCategory = $this->branchScope(\App\Models\Product::query())
            ->select('category', DB::raw('SUM(stock_current) as total'))
            ->groupBy('category')->orderByDesc('total')->get();

        $recentActivities = $this->branchScope(SalesActivity::query())
            ->with(['user', 'store'])
            ->when($user->isSales(), fn($q) => $q->where('user_id', $user->id))
            ->latest('activity_at')->take(8)->get();

        $recentDistributions = $this->branchScope(\App\Models\Distribution::query())
            ->with(['driver', 'store', 'items'])->latest()->take(5)->get();

        $topSales = $this->branchScope(User::query())->where('role', 'sales')
            ->withCount(['transactions as sales_count' => fn($q) => $q->where('type','sale')->whereMonth('created_at', now()->month)])
            ->withSum(['transactions as sales_total' => fn($q) => $q->where('type','sale')->whereMonth('created_at', now()->month)], 'total')
            ->orderByDesc('sales_total')->take(5)->get();

        // Branch selector for Owner
        $branches = $user->isOwner()
            ? $company->branches()->where('is_active', true)->withCount('users')->get()
            : collect();

        return view('dashboard.index', compact(
            'totalStock','lowStock','salesToday','salesTotalMonth',
            'visitToday','activeSales','pendingDistributions','inTransitDistributions',
            'chartLabels','chartData','stockByCategory',
            'recentActivities','recentDistributions','topSales','branches'
        ));
    }
}
