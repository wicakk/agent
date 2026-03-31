@extends('layouts.app')
@section('title', 'Tracking GPS')
@section('page-title', 'Tracking GPS Sales')
@section('breadcrumb', 'Pantau lokasi & aktivitas tim lapangan')

@section('content')

{{-- Filter --}}
<div class="flex flex-wrap gap-3 mb-5">
    <form method="GET" action="{{ route('tracking.index') }}" class="flex flex-wrap gap-3 flex-1">
        <input type="date" name="date" value="{{ $selectedDate }}"
            class="form-input py-2 text-sm w-auto"
            max="{{ now()->format('Y-m-d') }}">

        @if(auth()->user()->isAdminOrOwner())
        <select name="user_id" class="form-input py-2 text-sm w-auto">
            <option value="">Semua Sales</option>
            @foreach($salesUsers as $s)
            <option value="{{ $s->id }}" {{ $selectedUserId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        @endif

        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
            Tampilkan
        </button>

        @if($selectedDate !== now()->format('Y-m-d'))
        <a href="{{ route('tracking.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-sm font-semibold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
            Hari Ini
        </a>
        @endif
    </form>
</div>

{{-- Stats Row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
        <p class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $activities->where('type','check_in')->count() }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-400">Total Check-in</p>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
        <p class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $gpsLogs->count() }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-400">Log GPS</p>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm {{ $anomalies->count() > 0 ? 'border-red-300 dark:border-red-900 bg-red-50 dark:bg-red-950/50' : '' }}">
        <p class="text-2xl font-extrabold {{ $anomalies->count() > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }}">{{ $anomalies->count() }}</p>
        <p class="text-sm {{ $anomalies->count() > 0 ? 'text-red-500' : 'text-slate-500 dark:text-slate-400' }}">
            Anomali GPS{{ $anomalies->count() > 0 ? ' ⚠' : '' }}
        </p>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
        <p class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $activities->pluck('store_id')->unique()->count() }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-400">Toko Dikunjungi</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Map --}}
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 dark:text-white">Peta Aktivitas</h3>
                <div class="flex items-center gap-3 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-indigo-500 inline-block"></span>Check-in</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>Anomali</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>Lokasi Kini</span>
                </div>
            </div>
            <div id="tracking-map" class="h-[420px] bg-slate-100 dark:bg-slate-800"></div>
        </div>
    </div>

    {{-- Sidebar: Sales Status & Activities --}}
    <div class="space-y-4">

        {{-- Sales Status Cards --}}
        @if(auth()->user()->isAdminOrOwner())
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm">Status Sales Hari Ini</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($salesStats as $sales)
                <div class="flex items-center gap-3 px-5 py-3">
                    <div class="relative">
                        <img src="{{ $sales->avatar_url }}" class="w-8 h-8 rounded-full">
                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white dark:border-slate-900
                            {{ $sales->visits_today > 0 ? 'bg-green-500' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $sales->name }}</p>
                        <div class="flex items-center gap-2 text-xs text-slate-400">
                            <span>{{ $sales->visits_today }} kunjungan</span>
                            @if($sales->anomaly_count > 0)
                            <span class="text-red-500 font-semibold">⚠ {{ $sales->anomaly_count }} anomali</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Activity Feed --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm">Log Aktivitas</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-64 overflow-y-auto">
                @forelse($activities as $act)
                <div class="flex items-start gap-3 px-5 py-3">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5
                        {{ $act->is_mock_location ? 'bg-red-100 dark:bg-red-950' : 'bg-indigo-100 dark:bg-indigo-950' }}">
                        @if($act->is_mock_location)
                        <svg class="w-3.5 h-3.5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z" clip-rule="evenodd"/></svg>
                        @else
                        <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-900 dark:text-white">{{ $act->user->name }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $act->store->name }}</p>
                        @if($act->is_mock_location)
                        <p class="text-xs text-red-500 font-semibold">⚠ Fake GPS terdeteksi</p>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 shrink-0">{{ $act->activity_at->format('H:i') }}</p>
                </div>
                @empty
                <div class="px-5 py-8 text-center">
                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-700 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    <p class="text-sm text-slate-400">Tidak ada aktivitas</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Anomaly Alerts --}}
        @if($anomalies->count() > 0)
        <div class="bg-red-50 dark:bg-red-950 rounded-2xl border border-red-200 dark:border-red-900 p-4">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <h4 class="font-bold text-red-700 dark:text-red-300 text-sm">{{ $anomalies->count() }} Anomali GPS Terdeteksi</h4>
            </div>
            <div class="space-y-2">
                @foreach($anomalies->take(5) as $anomaly)
                <div class="bg-white/70 dark:bg-red-900/30 rounded-xl p-3">
                    <p class="text-xs font-semibold text-red-800 dark:text-red-300">{{ $anomaly->user->name }}</p>
                    <p class="text-xs text-red-600 dark:text-red-400 mt-0.5">
                        {{ $anomaly->is_mock_location ? '🚨 Fake GPS/Mock Location' : '' }}
                        {{ $anomaly->is_location_jump ? '⚡ Loncatan Lokasi Tidak Wajar' : '' }}
                    </p>
                    <p class="text-xs text-red-500 mt-0.5">{{ $anomaly->logged_at->format('H:i:s') }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const map = L.map('tracking-map').setView([-6.9175, 107.6191], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    // Custom icons
    const makeIcon = (color) => L.divIcon({
        className: '',
        html: `<div style="width:12px;height:12px;border-radius:50%;background:${color};border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.3)"></div>`,
        iconSize: [12, 12],
        iconAnchor: [6, 6],
    });

    const activities = @json($activities->whereNotNull('latitude')->whereNotNull('longitude'));
    const salesLocations = @json($salesLocations);
    const anomalies = @json($anomalies);

    let bounds = [];

    // Plot activity markers
    activities.forEach(act => {
        const lat = parseFloat(act.latitude);
        const lng = parseFloat(act.longitude);
        if (isNaN(lat) || isNaN(lng)) return;

        const isAnomaly = act.is_mock_location;
        const marker = L.marker([lat, lng], { icon: makeIcon(isAnomaly ? '#ef4444' : '#6366f1') });
        marker.addTo(map).bindPopup(
            `<b>${act.user?.name ?? 'Sales'}</b><br>${act.store?.name ?? ''}<br>${act.type_label ?? act.type}<br>${new Date(act.activity_at).toLocaleTimeString('id-ID')}${isAnomaly ? '<br><span style="color:red">⚠ Fake GPS</span>' : ''}`
        );
        bounds.push([lat, lng]);
    });

    // Plot current locations of sales
    salesLocations.forEach(sales => {
        if (!sales.latitude || !sales.longitude) return;
        L.marker([sales.latitude, sales.longitude], { icon: makeIcon('#22c55e') })
            .addTo(map)
            .bindPopup(`<b>${sales.name}</b><br>📍 Lokasi terakhir<br>${sales.last_location_at ? new Date(sales.last_location_at).toLocaleString('id-ID') : ''}`);
    });

    // Fit bounds
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [40, 40] });
    }
});
</script>
@endpush
