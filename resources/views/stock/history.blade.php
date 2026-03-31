@extends('layouts.app')
@section('title', 'Riwayat Stok')
@section('page-title', 'Riwayat Stok: ' . $product->name)
@section('breadcrumb', 'Riwayat pergerakan stok produk')

@section('content')

<div class="mb-5">
    <x-button variant="secondary" href="{{ route('stock.index') }}" size="sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Kembali
    </x-button>
</div>

{{-- Product Info --}}
<x-card class="mb-5">
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xl">
            {{ strtoupper(substr($product->name, 0, 2)) }}
        </div>
        <div class="flex-1">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $product->name }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $product->sku }} · {{ $product->brand }} · {{ $product->warehouse->name }}</p>
        </div>
        <div class="text-right">
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ number_format($product->stock_current) }}</p>
            <p class="text-sm text-slate-500">Stok Saat Ini</p>
        </div>
    </div>
</x-card>

{{-- History Table --}}
<x-card :padding="false">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
        <h3 class="font-bold text-slate-900 dark:text-white">Riwayat Pergerakan</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipe</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jumlah</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Sebelum</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Sesudah</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Alasan</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($movements as $mv)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <td class="px-5 py-3.5 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                        {{ $mv->created_at->format('d M Y H:i') }}
                    </td>
                    <td class="px-4 py-3.5">
                        <x-badge :color="$mv->type_color" size="xs">
                            {{ strtoupper($mv->type) }}
                        </x-badge>
                    </td>
                    <td class="px-4 py-3.5 text-center font-bold {{ in_array($mv->type, ['in','return']) ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ in_array($mv->type, ['in','return']) ? '+' : '-' }}{{ number_format($mv->quantity) }}
                    </td>
                    <td class="px-4 py-3.5 text-center text-slate-600 dark:text-slate-400">{{ number_format($mv->stock_before) }}</td>
                    <td class="px-4 py-3.5 text-center font-semibold text-slate-900 dark:text-white">{{ number_format($mv->stock_after) }}</td>
                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-400">{{ $mv->reason ?: '-' }}</td>
                    <td class="px-5 py-3.5 text-slate-600 dark:text-slate-400">{{ $mv->user->name }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-slate-400">Belum ada riwayat pergerakan stok</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($movements->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">{{ $movements->links() }}</div>
    @endif
</x-card>

@endsection
