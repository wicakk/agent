@extends('layouts.app')
@section('title', $store->name)
@section('page-title', $store->name)
@section('breadcrumb', 'Detail toko')

@section('content')
<div class="mb-5 flex items-center gap-3">
    <x-button variant="secondary" href="{{ route('stores.index') }}" size="sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Kembali
    </x-button>
    <x-button href="{{ route('stores.edit', $store) }}" size="sm" variant="secondary">Edit Toko</x-button>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    {{-- Store Info Card --}}
    <div class="lg:col-span-1">
        <x-card>
            <div class="text-center mb-5">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-3xl mx-auto mb-3">
                    {{ strtoupper(substr($store->name, 0, 2)) }}
                </div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $store->name }}</h2>
                <x-badge :color="$store->status_color" class="mt-2">{{ $store->status_label }}</x-badge>
            </div>

            <div class="space-y-3 text-sm">
                @if($store->owner_name)
                <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
                    <div>
                        <p class="text-xs text-slate-400">Pemilik</p>
                        <p class="font-medium text-slate-900 dark:text-white">{{ $store->owner_name }}</p>
                    </div>
                </div>
                @endif
                @if($store->phone)
                <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                    <div>
                        <p class="text-xs text-slate-400">Telepon</p>
                        <p class="font-medium text-slate-900 dark:text-white">{{ $store->phone }}</p>
                    </div>
                </div>
                @endif
                <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    <div>
                        <p class="text-xs text-slate-400">Alamat</p>
                        <p class="font-medium text-slate-900 dark:text-white">{{ $store->address }}</p>
                        @if($store->city) <p class="text-slate-500">{{ $store->city }}{{ $store->district ? ', ' . $store->district : '' }}</p> @endif
                    </div>
                </div>
                @if($store->salesUser)
                <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42"/></svg>
                    <div>
                        <p class="text-xs text-slate-400">Sales</p>
                        <p class="font-medium text-slate-900 dark:text-white">{{ $store->salesUser->name }}</p>
                    </div>
                </div>
                @endif
                @if($store->last_visited_at)
                <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-xs text-slate-400">Kunjungan Terakhir</p>
                        <p class="font-medium text-slate-900 dark:text-white">{{ $store->last_visited_at->format('d M Y') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </x-card>

        {{-- Map placeholder --}}
        @if($store->latitude && $store->longitude)
        <x-card class="mt-4">
            <h4 class="font-semibold text-slate-900 dark:text-white mb-3 text-sm">Lokasi Toko</h4>
            <div id="store-map" class="h-48 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800"></div>
        </x-card>
        @endif
    </div>

    {{-- Transactions & Activities --}}
    <div class="lg:col-span-2 space-y-5">
        {{-- Recent Transactions --}}
        <x-card :padding="false">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-bold text-slate-900 dark:text-white">Transaksi Terakhir</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($store->transactions as $tx)
                <div class="flex items-center justify-between px-5 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $tx->invoice_no }}</p>
                        <p class="text-xs text-slate-400">{{ $tx->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-900 dark:text-white">Rp {{ number_format($tx->total, 0, ',', '.') }}</p>
                        <x-badge :color="$tx->status_color" size="xs">{{ $tx->status_label }}</x-badge>
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-slate-400 text-sm">Belum ada transaksi</div>
                @endforelse
            </div>
        </x-card>

        {{-- Recent Activities --}}
        <x-card :padding="false">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-bold text-slate-900 dark:text-white">Aktivitas Sales</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($store->activities as $act)
                <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <img src="{{ $act->user->avatar_url }}" class="w-8 h-8 rounded-full">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $act->user->name }}</p>
                        <p class="text-xs text-slate-400">{{ $act->activity_at->format('d M Y H:i') }}</p>
                    </div>
                    <x-badge :color="$act->type_color" size="xs">{{ $act->type_label }}</x-badge>
                    @if($act->is_mock_location)
                    <span title="Fake GPS terdeteksi" class="text-red-500">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z" clip-rule="evenodd"/></svg>
                    </span>
                    @endif
                </div>
                @empty
                <div class="px-5 py-8 text-center text-slate-400 text-sm">Belum ada aktivitas</div>
                @endforelse
            </div>
        </x-card>
    </div>
</div>

@if($store->latitude && $store->longitude)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('store-map').setView([{{ $store->latitude }}, {{ $store->longitude }}], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    L.marker([{{ $store->latitude }}, {{ $store->longitude }}])
        .addTo(map)
        .bindPopup('<b>{{ addslashes($store->name) }}</b><br>{{ addslashes($store->address) }}')
        .openPopup();
});
</script>
@endpush
@endif
@endsection
