@extends('layouts.app')
@section('title', 'Manajemen Cabang')
@section('page-title', 'Manajemen Cabang')
@section('breadcrumb', 'Kelola cabang perusahaan')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div></div>
    <x-button href="{{ route('branches.create') }}">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Tambah Cabang
    </x-button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($branches as $branch)
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all p-5">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                    {{ strtoupper(substr($branch->city ?? $branch->name, 0, 2)) }}
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">{{ $branch->name }}</h3>
                    <p class="text-xs text-slate-400">{{ $branch->code }} · {{ $branch->city }}</p>
                </div>
            </div>
            <x-badge :color="$branch->is_active ? 'green' : 'red'" size="xs">
                {{ $branch->is_active ? 'Aktif' : 'Nonaktif' }}
            </x-badge>
        </div>

        <div class="grid grid-cols-3 gap-2 mb-4">
            <div class="text-center p-2 bg-slate-50 dark:bg-slate-800 rounded-xl">
                <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $branch->admin_count }}</p>
                <p class="text-xs text-slate-400">Admin</p>
            </div>
            <div class="text-center p-2 bg-slate-50 dark:bg-slate-800 rounded-xl">
                <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $branch->sales_count }}</p>
                <p class="text-xs text-slate-400">Sales</p>
            </div>
            <div class="text-center p-2 bg-slate-50 dark:bg-slate-800 rounded-xl">
                <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $branch->stores_count }}</p>
                <p class="text-xs text-slate-400">Toko</p>
            </div>
        </div>

        @if($branch->pic_name)
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
            <svg class="w-3.5 h-3.5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
            PIC: {{ $branch->pic_name }}
        </p>
        @endif

        <div class="flex gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('branches.show', $branch) }}" class="flex-1 text-center text-xs font-semibold text-indigo-600 dark:text-indigo-400 py-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950 transition-colors">
                Detail
            </a>
            <a href="{{ route('branches.edit', $branch) }}" class="flex-1 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                Edit
            </a>
            <form method="POST" action="{{ route('branches.destroy', $branch) }}" onsubmit="return confirm('Hapus cabang {{ addslashes($branch->name) }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-3 text-xs font-semibold text-red-500 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-950 transition-colors">
                    Hapus
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="md:col-span-2 lg:col-span-3 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-16 text-center">
        <p class="text-slate-500 font-medium text-lg mb-4">Belum ada cabang</p>
        <x-button href="{{ route('branches.create') }}">+ Tambah Cabang Pertama</x-button>
    </div>
    @endforelse
</div>
@endsection
