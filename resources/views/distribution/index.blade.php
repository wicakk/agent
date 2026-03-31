@extends('layouts.app')

@section('title', 'Manajemen Distribusi')
@section('page-title', 'Distribusi')
@section('breadcrumb', 'Kelola surat jalan & pengiriman')

@section('content')

{{-- Status Summary Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    @php
    $statuses = [
        ['key' => 'pending',    'label' => 'Pending',        'color' => 'yellow', 'icon' => 'clock'],
        ['key' => 'in_transit', 'label' => 'Dalam Perjalanan','color' => 'blue',   'icon' => 'truck'],
        ['key' => 'delivered',  'label' => 'Terkirim',        'color' => 'green',  'icon' => 'check'],
        ['key' => 'cancelled',  'label' => 'Dibatalkan',      'color' => 'red',    'icon' => 'x'],
    ];
    @endphp
    @foreach($statuses as $s)
    <a href="{{ route('distribution.index', ['status' => $s['key']]) }}"
       class="bg-white dark:bg-slate-900 rounded-2xl border {{ request('status') === $s['key'] ? 'border-indigo-400 dark:border-indigo-600 ring-2 ring-indigo-200 dark:ring-indigo-900' : 'border-slate-200 dark:border-slate-800' }} p-4 shadow-sm hover:shadow-md transition-all">
        <div class="flex items-center justify-between mb-2">
            <span class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $statusCounts[$s['key']] ?? 0 }}</span>
        </div>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $s['label'] }}</p>
    </a>
    @endforeach
</div>

{{-- Toolbar --}}
<div class="flex flex-col sm:flex-row gap-3 mb-5">
    <form method="GET" action="{{ route('distribution.index') }}" class="flex flex-1 flex-wrap gap-2">
        <div class="relative flex-1 min-w-[180px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                class="w-full pl-9 pr-4 py-2.5 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="No. surat jalan...">
        </div>
        <select name="status" class="text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Status</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>Dalam Perjalanan</option>
            <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Terkirim</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
        </select>
        <select name="driver_id" class="text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Driver</option>
            @foreach($drivers as $driver)
            <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>{{ $driver->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Filter</button>
    </form>

    @if(auth()->user()->isAdminOrOwner())
    <x-button href="{{ route('distribution.create') }}">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Buat Distribusi
    </x-button>
    @endif
</div>

{{-- Distribution Cards Grid --}}
<div class="grid grid-cols-1 gap-4">
    @forelse($distributions as $dist)
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
        <div class="p-5">
            <div class="flex items-start gap-4">
                {{-- Status Icon --}}
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0
                    {{ $dist->status === 'delivered' ? 'bg-green-100 dark:bg-green-950' :
                      ($dist->status === 'in_transit' ? 'bg-blue-100 dark:bg-blue-950' :
                      ($dist->status === 'cancelled' ? 'bg-red-100 dark:bg-red-950' : 'bg-yellow-100 dark:bg-yellow-950')) }}">
                    @if($dist->status === 'delivered')
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif($dist->status === 'in_transit')
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124"/></svg>
                    @elseif($dist->status === 'cancelled')
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @else
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </div>

                {{-- Main Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <a href="{{ route('distribution.show', $dist) }}" class="font-bold text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                            {{ $dist->delivery_no }}
                        </a>
                        <x-badge :color="$dist->status_color">{{ $dist->status_label }}</x-badge>
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
                            {{ $dist->driver->name }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            {{ \Illuminate\Support\Str::limit($dist->destination_address, 40) }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                            {{ $dist->total_items }} item
                        </span>
                        <span>{{ $dist->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('distribution.show', $dist) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </a>

                    @if(in_array($dist->status, ['pending', 'in_transit']))
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/></svg>
                        </button>
                        <div x-show="open" x-cloak class="absolute right-0 mt-1 w-44 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-lg z-10 overflow-hidden">
                            @if($dist->status === 'pending')
                            <form method="POST" action="{{ route('distribution.update-status', $dist) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="in_transit">
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-blue-950 hover:text-blue-700 dark:hover:text-blue-300 flex items-center gap-2 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375"/></svg>
                                    Berangkat
                                </button>
                            </form>
                            @endif
                            @if($dist->status === 'in_transit')
                            <form method="POST" action="{{ route('distribution.update-status', $dist) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="delivered">
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-green-50 dark:hover:bg-green-950 hover:text-green-700 dark:hover:text-green-300 flex items-center gap-2 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Tandai Terkirim
                                </button>
                            </form>
                            @endif
                            @if($dist->status === 'pending' && auth()->user()->isAdminOrOwner())
                            <form method="POST" action="{{ route('distribution.update-status', $dist) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm hover:bg-red-50 dark:hover:bg-red-950 text-red-600 dark:text-red-400 flex items-center gap-2 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Batalkan
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Item pills --}}
            @if($dist->items->count())
            <div class="mt-3 flex flex-wrap gap-1.5 pl-16">
                @foreach($dist->items->take(3) as $item)
                <span class="inline-flex items-center text-xs bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-lg px-2.5 py-1 font-medium">
                    {{ $item->product->name ?? 'Produk' }} × {{ $item->quantity_requested }}
                </span>
                @endforeach
                @if($dist->items->count() > 3)
                <span class="inline-flex items-center text-xs bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 rounded-lg px-2.5 py-1 font-medium">
                    +{{ $dist->items->count() - 3 }} lainnya
                </span>
                @endif
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-16 text-center">
        <svg class="w-14 h-14 text-slate-300 dark:text-slate-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0"/></svg>
        <p class="text-slate-500 dark:text-slate-400 font-medium text-lg mb-1">Belum ada distribusi</p>
        <p class="text-slate-400 text-sm mb-4">Mulai buat surat jalan distribusi pertama Anda</p>
        @if(auth()->user()->isAdminOrOwner())
        <x-button href="{{ route('distribution.create') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Buat Distribusi
        </x-button>
        @endif
    </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($distributions->hasPages())
<div class="mt-4">{{ $distributions->links() }}</div>
@endif

@endsection
