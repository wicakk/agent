@extends('layouts.app')

@section('title', 'Manajemen Toko')
@section('page-title', 'Manajemen Toko')
@section('breadcrumb', 'Daftar toko & outlet mitra')

@section('content')

{{-- Status Summary --}}
<div class="grid grid-cols-3 gap-3 mb-5">
    @php
    $statusDefs = [
        ['key' => 'active',   'label' => 'Aktif',       'color' => 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300 border-green-200 dark:border-green-900'],
        ['key' => 'potential','label' => 'Potensial',    'color' => 'bg-yellow-100 dark:bg-yellow-950 text-yellow-700 dark:text-yellow-300 border-yellow-200 dark:border-yellow-900'],
        ['key' => 'inactive', 'label' => 'Tidak Aktif',  'color' => 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300 border-red-200 dark:border-red-900'],
    ];
    @endphp
    @foreach($statusDefs as $s)
    <a href="{{ route('stores.index', ['status' => $s['key']]) }}"
       class="rounded-2xl border p-4 text-center {{ $s['color'] }} {{ request('status') === $s['key'] ? 'ring-2 ring-indigo-400 dark:ring-indigo-600' : '' }} hover:opacity-90 transition-all">
        <p class="text-2xl font-extrabold">{{ $statusCount[$s['key']] ?? 0 }}</p>
        <p class="text-xs font-semibold mt-0.5">{{ $s['label'] }}</p>
    </a>
    @endforeach
</div>

{{-- Toolbar --}}
<div class="flex flex-col sm:flex-row gap-3 mb-5">
    <form method="GET" action="{{ route('stores.index') }}" class="flex flex-1 flex-wrap gap-2">
        <div class="relative flex-1 min-w-[180px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                class="w-full pl-9 pr-4 py-2.5 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Cari toko, kota...">
        </div>
        <select name="status" class="text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="potential" {{ request('status') === 'potential' ? 'selected' : '' }}>Potensial</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
        </select>
        <select name="user_id" class="text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Sales</option>
            @foreach($salesUsers as $s)
            <option value="{{ $s->id }}" {{ request('user_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Filter</button>
    </form>
    <x-button href="{{ route('stores.create') }}">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Tambah Toko
    </x-button>
</div>

{{-- Stores Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($stores as $store)
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all hover:-translate-y-0.5 group">
        <div class="p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white font-bold text-base bg-gradient-to-br from-indigo-500 to-purple-600 shrink-0">
                        {{ strtoupper(substr($store->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-slate-900 dark:text-white text-sm truncate">{{ $store->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $store->store_type ?: 'Toko' }}</p>
                    </div>
                </div>
                <x-badge :color="$store->status_color" size="xs">{{ $store->status_label }}</x-badge>
            </div>

            <div class="space-y-1.5 mb-4">
                @if($store->owner_name)
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
                    {{ $store->owner_name }}
                </div>
                @endif
                <div class="flex items-start gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <svg class="w-3.5 h-3.5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    <span class="line-clamp-1">{{ $store->city ? $store->city . ' — ' : '' }}{{ $store->address }}</span>
                </div>
                @if($store->salesUser)
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z"/></svg>
                    {{ $store->salesUser->name }}
                </div>
                @endif
                @if($store->last_visited_at)
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Kunjungan: {{ $store->last_visited_at->format('d M Y') }}
                </div>
                @endif
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('stores.show', $store) }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">Lihat detail →</a>
                <div class="flex gap-1">
                    <a href="{{ route('stores.edit', $store) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                    </a>
                    @if(auth()->user()->isAdminOrOwner())
                    <form method="POST" action="{{ route('stores.destroy', $store) }}" onsubmit="return confirm('Hapus toko ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="sm:col-span-2 lg:col-span-3 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-16 text-center">
        <svg class="w-14 h-14 text-slate-300 dark:text-slate-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35"/></svg>
        <p class="text-slate-500 dark:text-slate-400 font-medium text-lg mb-1">Belum ada toko</p>
        <p class="text-slate-400 text-sm mb-4">Tambahkan toko pertama untuk mulai tracking kunjungan</p>
        <x-button href="{{ route('stores.create') }}">+ Tambah Toko</x-button>
    </div>
    @endforelse
</div>

@if($stores->hasPages())
<div class="mt-4">{{ $stores->links() }}</div>
@endif

@endsection
