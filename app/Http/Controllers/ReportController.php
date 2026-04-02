<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesByBranch;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use ScopesByBranch;

    public function index(Request $request)
    {
        $user      = Auth::user();
        $startDate = $request->filled('start_date')
            ? \Carbon\Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? \Carbon\Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        $txQuery = $this->branchScope(Transaction::query())
            ->where('type', 'sale')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($user->isSales()) $txQuery->where('user_id', $user->id);
        if ($request->filled('user_id')) $txQuery->where('user_id', $request->user_id);

        $totalRevenue    = (clone $txQuery)->sum('total');
        $totalOrders     = (clone $txQuery)->count();
        $avgOrder        = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $paidRevenue     = (clone $txQuery)->where('status', 'paid')->sum('total');

        $dailySales = (clone $txQuery)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
            ->groupBy(DB::raw('DATE(created_at)'))->orderBy('date')->get();

        $topProducts = TransactionItem::whereHas('transaction', function($q) use ($user, $startDate, $endDate, $request) {
            $this->branchScope($q)->where('type','sale')->where('status','!=','cancelled')->whereBetween('created_at', [$startDate, $endDate]);
            if ($user->isSales()) $q->where('user_id', $user->id);
            if ($request->filled('user_id')) $q->where('user_id', $request->user_id);
        })->select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
          ->groupBy('product_id')->orderByDesc('total_qty')->with('product')->take(10)->get();

        $salesPerformance = $this->branchScope(User::query())->where('role', 'sales')
            ->withCount(['transactions as orders_count' => fn($q) => $q->where('type','sale')->where('status','!=','cancelled')->whereBetween('created_at',[$startDate,$endDate])])
            ->withSum(['transactions as revenue' => fn($q) => $q->where('type','sale')->where('status','!=','cancelled')->whereBetween('created_at',[$startDate,$endDate])], 'total')
            ->withCount(['salesActivities as visit_count' => fn($q) => $q->where('type','check_in')->whereBetween('activity_at',[$startDate,$endDate])])
            ->orderByDesc('revenue')
            ->when($user->isSales(), fn($q) => $q->where('id', $user->id))
            ->get();

        $topStores = $this->branchScope(Transaction::query())
            ->where('type','sale')->where('status','!=','cancelled')->whereBetween('created_at',[$startDate,$endDate])
            ->select('store_id', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as orders'))
            ->groupBy('store_id')->orderByDesc('total')->with('store')->take(10)->get();

        $transactions = (clone $txQuery)->with(['user','store','items'])->latest()->paginate(20)->withQueryString();

        $salesUsers = $this->branchScope(User::query())->where('role','sales')->where('is_active',true)->get();

        $chartLabels = $dailySales->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->toArray();
        $chartData   = $dailySales->pluck('total')->map(fn($v) => (float)$v)->toArray();

        return view('reports.index', compact(
            'totalRevenue','totalOrders','avgOrder','paidRevenue',
            'topProducts','salesPerformance','topStores','transactions',
            'salesUsers','startDate','endDate','chartLabels','chartData'
        ));
    }

    public function exportExcel(Request $request)
    {
        return back()->with('info', 'Export Excel memerlukan: composer require maatwebsite/excel');
    }

    public function exportPdf(Request $request)
    {
        return back()->with('info', 'Export PDF memerlukan: composer require barryvdh/laravel-dompdf');
    }
}
