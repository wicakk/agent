@extends('layouts.app')
@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')
@section('breadcrumb', 'Analitik & ringkasan transaksi')

@section('content')

{{-- Filter Bar --}}
<form method="GET" action="{{ route('reports.index') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 mb-5 flex flex-wrap items-end gap-3">
    <div>
        <label class="form-label text-xs">Dari Tanggal</label>
        <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="form-input py-2 text-sm">
    </div>
    <div>
        <label class="form-label text-xs">Sampai Tanggal</label>
        <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="form-input py-2 text-sm">
    </div>
    @if(auth()->user()->isAdminOrOwner())
    <div>
        <label class="form-label text-xs">Sales</label>
        <select name="user_id" class="form-input py-2 text-sm">
            <option value="">Semua Sales</option>
            @foreach($salesUsers as $s)
            <option value="{{ $s->id }}" {{ request('user_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
        Terapkan
    </button>
    <div class="flex gap-2 flex-wrap">
        @foreach([
            ['Hari ini', now()->format('Y-m-d'), now()->format('Y-m-d')],
            ['7 Hari', now()->subDays(6)->format('Y-m-d'), now()->format('Y-m-d')],
            ['Bulan ini', now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
        ] as [$label, $start, $end])
        <a href="{{ route('reports.index', ['start_date' => $start, 'end_date' => $end]) }}"
           class="px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors whitespace-nowrap">
            {{ $label }}
        </a>
        @endforeach
    </div>
</form>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/></svg>
        </div>
        <p class="text-lg font-extrabold text-slate-900 dark:text-white">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Total Omzet</p>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-950 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p class="text-lg font-extrabold text-slate-900 dark:text-white">Rp {{ number_format($paidRevenue, 0, ',', '.') }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Sudah Dibayar</p>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
        </div>
        <p class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ number_format($totalOrders) }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Total Transaksi</p>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-950 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
        </div>
        <p class="text-lg font-extrabold text-slate-900 dark:text-white">Rp {{ number_format($avgOrder, 0, ',', '.') }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Rata-rata/Transaksi</p>
    </div>
</div>

{{-- Charts + Top Products --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
    <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">Grafik Penjualan Harian</h3>
                <p class="text-xs text-slate-400">{{ $startDate->format('d M') }} — {{ $endDate->format('d M Y') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.export-pdf', request()->query()) }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950 rounded-lg hover:bg-red-100 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Export PDF
                </a>
                <a href="{{ route('reports.export-excel', request()->query()) }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-950 rounded-lg hover:bg-green-100 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Export Excel
                </a>
            </div>
        </div>
        <div id="revenueChart" class="h-56"></div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
        <h3 class="font-bold text-slate-900 dark:text-white mb-4">Produk Terlaris</h3>
        @if($topProducts->isEmpty())
            <p class="text-sm text-slate-400 text-center py-8">Belum ada data</p>
        @else
        <div class="space-y-3">
            @foreach($topProducts as $i => $item)
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold shrink-0
                    {{ $i === 0 ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950 dark:text-yellow-400' :
                      ($i === 1 ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' :
                      'bg-slate-50 text-slate-400 dark:bg-slate-800/50 dark:text-slate-500') }}">
                    {{ $i + 1 }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $item->product->name ?? 'Produk dihapus' }}</p>
                    <p class="text-xs text-slate-400">{{ number_format($item->total_qty) }} pcs</p>
                </div>
                <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400 shrink-0 tabular-nums">
                    Rp {{ number_format($item->total_revenue/1000, 0, ',', '.') }}k
                </p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Sales Performance & Top Stores --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="font-bold text-slate-900 dark:text-white">Performa Sales</h3>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($salesPerformance as $i => $sales)
            <div class="flex items-center gap-3 px-5 py-3.5">
                <span class="w-5 text-center text-xs font-bold text-slate-400">{{ $i+1 }}</span>
                <img src="{{ $sales->avatar_url }}" class="w-8 h-8 rounded-full shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $sales->name }}</p>
                    <p class="text-xs text-slate-400">{{ $sales->orders_count }} order · {{ $sales->visit_count }} kunjungan</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-bold text-slate-900 dark:text-white tabular-nums">
                        Rp {{ number_format(($sales->revenue ?? 0)/1000, 0, ',', '.') }}k
                    </p>
                    @if($sales->anomaly_count > 0)
                    <span class="text-xs text-red-500 font-semibold">⚠ {{ $sales->anomaly_count }} anomali GPS</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-slate-400 text-sm">Belum ada data performa sales</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="font-bold text-slate-900 dark:text-white">Toko Terbaik</h3>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($topStores as $i => $item)
            <div class="flex items-center gap-3 px-5 py-3.5">
                <span class="w-5 text-center text-xs font-bold text-slate-400">{{ $i+1 }}</span>
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                    {{ strtoupper(substr($item->store->name ?? '?', 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $item->store->name ?? 'Toko dihapus' }}</p>
                    <p class="text-xs text-slate-400">{{ $item->orders }} transaksi</p>
                </div>
                <p class="text-sm font-bold text-indigo-600 dark:text-indigo-400 tabular-nums shrink-0">
                    Rp {{ number_format($item->total/1000, 0, ',', '.') }}k
                </p>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-slate-400 text-sm">Belum ada data toko</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Transaction Table --}}
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h3 class="font-bold text-slate-900 dark:text-white">Riwayat Transaksi</h3>
        <span class="text-sm text-slate-400">{{ $transactions->total() }} transaksi</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Invoice</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">Toko</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Sales</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden sm:table-cell">Tanggal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($transactions as $tx)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $tx->invoice_no }}</p>
                        <p class="text-xs text-slate-400">{{ $tx->items->count() }} item · {{ ucfirst($tx->payment_method ?? '-') }}</p>
                    </td>
                    <td class="px-4 py-3.5 hidden md:table-cell text-slate-600 dark:text-slate-400">{{ $tx->store->name ?? '-' }}</td>
                    <td class="px-4 py-3.5 hidden lg:table-cell text-slate-600 dark:text-slate-400">{{ $tx->user->name ?? '-' }}</td>
                    <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-white tabular-nums">
                        Rp {{ number_format($tx->total, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <x-badge :color="$tx->status_color" size="xs">{{ $tx->status_label }}</x-badge>
                    </td>
                    <td class="px-5 py-3.5 hidden sm:table-cell text-slate-400 text-xs whitespace-nowrap">
                        {{ $tx->created_at->format('d M Y H:i') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                        Belum ada transaksi pada periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
        {{ $transactions->links() }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? '#1e293b' : '#f1f5f9';
    const labels = {!! json_encode($chartLabels) !!};
    const data   = {!! json_encode($chartData) !!};

    if (document.querySelector('#revenueChart')) {
        new ApexCharts(document.querySelector('#revenueChart'), {
            series: [{ name: 'Penjualan', data: data.length ? data : [0] }],
            chart: { type: 'bar', height: 224, toolbar: { show: false }, background: 'transparent' },
            colors: ['#6366f1'],
            plotOptions: { bar: { borderRadius: 5, columnWidth: '55%' } },
            xaxis: {
                categories: labels.length ? labels : ['Tidak ada data'],
                labels: { style: { colors: textColor, fontSize: '11px' } },
                axisBorder: { show: false }, axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { colors: textColor, fontSize: '11px' },
                    formatter: v => 'Rp ' + (v >= 1000000 ? (v/1000000).toFixed(1) + 'jt' : (v/1000).toFixed(0) + 'k')
                }
            },
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            dataLabels: { enabled: false },
            tooltip: {
                y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) },
                theme: isDark ? 'dark' : 'light'
            },
        }).render();
    }
});
</script>
@endpush
