@extends('layouts.app')
@section('title', $branch->name)
@section('page-title', $branch->name)
@section('breadcrumb', 'Detail cabang')

@section('content')
<div class="mb-5 flex gap-3">
    <x-button variant="secondary" href="{{ route('branches.index') }}" size="sm">← Kembali</x-button>
    <x-button href="{{ route('branches.edit', $branch) }}" size="sm" variant="secondary">Edit Cabang</x-button>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-card>
        <p class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $stats['sales_count'] }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Sales Aktif</p>
    </x-card>
    <x-card>
        <p class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $stats['admin_count'] }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Admin</p>
    </x-card>
    <x-card>
        <p class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $stats['store_count'] }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Toko</p>
    </x-card>
    <x-card>
        <p class="text-lg font-extrabold text-indigo-600 dark:text-indigo-400">Rp {{ number_format($stats['revenue_month']/1000, 0, ',', '.') }}k</p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Omzet Bulan Ini</p>
    </x-card>
</div>

{{-- Users in this branch --}}
<x-card :padding="false">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h3 class="font-bold text-slate-900 dark:text-white">Tim Cabang {{ $branch->name }}</h3>
        <a href="{{ route('users.create') }}" class="text-sm text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">+ Tambah User</a>
    </div>
    <div class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($branch->users as $user)
        <div class="flex items-center gap-3 px-5 py-3.5">
            <img src="{{ $user->avatar_url }}" class="w-9 h-9 rounded-full">
            <div class="flex-1">
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $user->name }}</p>
                <p class="text-xs text-slate-400">{{ $user->email }}</p>
            </div>
            <x-badge :color="$user->role_color" size="xs">{{ ucfirst($user->role) }}</x-badge>
            <x-badge :color="$user->is_active ? 'green' : 'red'" size="xs">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-slate-400 text-sm">Belum ada user di cabang ini</div>
        @endforelse
    </div>
</x-card>
@endsection
