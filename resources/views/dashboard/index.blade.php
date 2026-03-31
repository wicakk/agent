@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Selamat datang, ' . auth()->user()->name)

@section('content')

{{-- Subscription Warning --}}
@php $sub = auth()->user()->company->activeSubscription; @endphp
@if($sub && $sub->isTrial() && $sub->days_remaining <= 7)
<div class="mb-6 p-4 bg-amber-50 dark:bg-amber-950 border border-amber-200 dark:border-amber-900 rounded-2xl flex items-center gap-3">
    <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
    <p class="text-amber-800 dark:text-amber-200 text-sm font-medium">
        Trial Anda berakhir dalam <strong>{{ $sub->days_remaining }} hari</strong>.
        <a href="#" class="underline font-bold">Upgrade sekarang →</a>
    </p>
</div>
@endif

{{-- ===== STAT CARDS ===== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- Total Stok --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                </svg>
            </div>
            @if($lowStock > 0)
            <span class="flex items-center gap-1 text-xs font-semibold text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-950 px-2 py-1 rounded-lg">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ $lowStock }}
            </span>
            @endif
        </div>
        <p class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ number_format($totalStock) }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Total Stok Gudang</p>
        @if($lowStock > 0)
        <p class="text-xs text-red-500 mt-1">{{ $lowStock }} produk stok rendah</p>
        @endif
    </div>

    {{-- Penjualan Hari Ini --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-950 dark:text-emerald-400 px-2 py-1 rounded-lg">Hari ini</span>
        </div>
        <p class="text-xl font-extrabold text-slate-900 dark:text-white">Rp {{ number_format($salesToday, 0, ',', '.') }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Penjualan Hari Ini</p>
        <p class="text-xs text-slate-400 mt-1">Bulan ini: Rp {{ number_format($salesTotalMonth, 0, ',', '.') }}</p>
    </div>

    {{-- Kunjungan --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-xl bg-orange-100 dark:bg-orange-950 flex items-center justify-center">
                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-orange-600 bg-orange-50 dark:bg-orange-950 dark:text-orange-400 px-2 py-1 rounded-lg">Check-in</span>
        </div>
        <p class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $visitToday }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kunjungan Hari Ini</p>
    </div>

    {{-- Active Sales --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                </svg>
            </div>
            <div class="flex gap-1">
                @if($pendingDistributions > 0)
                <span class="text-xs font-semibold text-yellow-600 bg-yellow-50 dark:bg-yellow-950 dark:text-yellow-400 px-1.5 py-1 rounded-lg">{{ $pendingDistributions }} pending</span>
                @endif
            </div>
        </div>
        <p class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $activeSales }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Sales Aktif</p>
        @if($inTransitDistributions > 0)
        <p class="text-xs text-blue-500 mt-1">{{ $inTransitDistributions }} dalam perjalanan</p>
        @endif
    </div>
</div>

{{-- ===== CHARTS ROW ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    {{-- Sales Chart --}}
    <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">Grafik Penjualan</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">7 hari terakhir</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="w-3 h-3 rounded-full bg-indigo-500 inline-block"></span> Penjualan
                </span>
            </div>
        </div>
        <div id="salesChart" class="h-56"></div>
    </div>

    {{-- Stock by Category --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
        <div class="mb-5">
            <h3 class="font-bold text-slate-900 dark:text-white">Stok per Kategori</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Distribusi stok gudang</p>
        </div>
        <div id="stockChart" class="h-48"></div>
        <div class="mt-3 space-y-2">
            @foreach($stockByCategory->take(4) as $cat)
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-600 dark:text-slate-400 capitalize">{{ $cat->category ?: 'Lainnya' }}</span>
                <span class="font-semibold text-slate-900 dark:text-white">{{ number_format($cat->total) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ===== BOTTOM ROW ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- Recent Activities --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="font-bold text-slate-900 dark:text-white">Aktivitas Sales Terkini</h3>
            <a href="#" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">Lihat semua</a>
        </div>
        <div class="divide-y divide-slate-50 dark:divide-slate-800">
            @forelse($recentActivities as $activity)
            <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                <img src="{{ $activity->user->avatar_url }}" class="w-8 h-8 rounded-full shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-900 dark:text-white truncate">
                        {{ $activity->user->name }}
                        <span class="text-slate-400 font-normal">→</span>
                        {{ $activity->store->name }}
                    </p>
                    <p class="text-xs text-slate-400">{{ $activity->activity_at->diffForHumans() }}</p>
                </div>
                <x-badge :color="$activity->type_color" size="xs">{{ $activity->type_label }}</x-badge>
                @if($activity->is_mock_location)
                <span title="Kemungkinan fake GPS" class="text-red-500">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z" clip-rule="evenodd"/></svg>
                </span>
                @endif
            </div>
            @empty
            <div class="px-5 py-10 text-center">
                <svg class="w-10 h-10 text-slate-300 dark:text-slate-700 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                <p class="text-sm text-slate-400">Belum ada aktivitas hari ini</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Recent Distributions --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="font-bold text-slate-900 dark:text-white">Distribusi Terkini</h3>
            <a href="{{ route('distribution.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">Lihat semua</a>
        </div>
        <div class="divide-y divide-slate-50 dark:divide-slate-800">
            @forelse($recentDistributions as $dist)
            <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
                    {{ $dist->status === 'delivered' ? 'bg-green-100 dark:bg-green-950' :
                      ($dist->status === 'in_transit' ? 'bg-blue-100 dark:bg-blue-950' : 'bg-yellow-100 dark:bg-yellow-950') }}">
                    @if($dist->status === 'delivered')
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif($dist->status === 'in_transit')
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25"/></svg>
                    @else
                        <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $dist->delivery_no }}</p>
                    <p class="text-xs text-slate-400">{{ $dist->driver->name }} · {{ $dist->total_items }} item</p>
                </div>
                <x-badge :color="$dist->status_color" size="xs">{{ $dist->status_label }}</x-badge>
            </div>
            @empty
            <div class="px-5 py-10 text-center">
                <svg class="w-10 h-10 text-slate-300 dark:text-slate-700 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25"/></svg>
                <p class="text-sm text-slate-400">Belum ada distribusi</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? '#1e293b' : '#f1f5f9';

    // Sales Chart
    const salesOptions = {
        series: [{ name: 'Penjualan', data: {!! json_encode($chartData) !!} }],
        chart: { type: 'area', height: 224, toolbar: { show: false }, sparkline: { enabled: false }, background: 'transparent' },
        colors: ['#6366f1'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.0, stops: [0, 100] } },
        stroke: { curve: 'smooth', width: 2.5 },
        xaxis: { categories: {!! json_encode($chartLabels) !!}, labels: { style: { colors: textColor, fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { style: { colors: textColor, fontSize: '11px' }, formatter: v => 'Rp ' + (v/1000000).toFixed(1) + 'jt' } },
        grid: { borderColor: gridColor, strokeDashArray: 4, yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } } },
        tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) }, theme: isDark ? 'dark' : 'light' },
        dataLabels: { enabled: false },
    };
    new ApexCharts(document.querySelector("#salesChart"), salesOptions).render();

    // Stock Donut Chart
    const stockCategories = {!! json_encode($stockByCategory->pluck('category')->map(fn($c) => $c ?: 'Lainnya')) !!};
    const stockData = {!! json_encode($stockByCategory->pluck('total')) !!};
    if (stockData.length > 0) {
        const stockOptions = {
            series: stockData,
            chart: { type: 'donut', height: 192, background: 'transparent' },
            labels: stockCategories,
            colors: ['#6366f1', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b'],
            legend: { show: false },
            dataLabels: { enabled: false },
            plotOptions: { pie: { donut: { size: '70%' } } },
            stroke: { show: false },
            tooltip: { theme: isDark ? 'dark' : 'light' },
        };
        new ApexCharts(document.querySelector("#stockChart"), stockOptions).render();
    }
});
</script>
@endpush
