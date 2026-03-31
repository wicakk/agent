@extends('layouts.app')
@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk Baru')
@section('breadcrumb', 'Tambah produk ke gudang')

@section('content')
<div class="max-w-2xl">

    <div class="mb-5">
        <x-button variant="secondary" href="{{ route('stock.index') }}" size="sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </x-button>
    </div>

    <form method="POST" action="{{ route('stock.store') }}">
        @csrf
        <x-card>
            <h3 class="font-bold text-slate-900 dark:text-white mb-5">Informasi Produk</h3>

            @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 rounded-xl">
                @foreach($errors->all() as $error)
                    <p class="text-red-700 dark:text-red-300 text-sm">• {{ $error }}</p>
                @endforeach
            </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="form-input" placeholder="Gudang Garam Surya 12">
                </div>
                <div>
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku') }}" class="form-input" placeholder="GGS-12">
                </div>
                <div>
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" value="{{ old('brand') }}" class="form-input" placeholder="Gudang Garam">
                </div>
                <div>
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-input">
                        <option value="">— Pilih Kategori —</option>
                        @foreach(['rokok' => 'Rokok', 'mie' => 'Mie Instan', 'air' => 'Air Mineral', 'minum' => 'Minuman', 'snack' => 'Snack', 'lainnya' => 'Lainnya'] as $val => $label)
                            <option value="{{ $val }}" {{ old('category') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Gudang <span class="text-red-500">*</span></label>
                    <select name="warehouse_id" required class="form-input">
                        <option value="">— Pilih Gudang —</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Satuan <span class="text-red-500">*</span></label>
                    <select name="unit" class="form-input">
                        @foreach(['pcs' => 'Pcs', 'pack' => 'Pack', 'karton' => 'Karton', 'dus' => 'Dus', 'kg' => 'Kg'] as $val => $label)
                            <option value="{{ $val }}" {{ old('unit', 'pcs') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Isi per Pack</label>
                    <input type="number" name="unit_per_pack" value="{{ old('unit_per_pack', 12) }}" min="1" class="form-input">
                </div>
                <div>
                    <label class="form-label">Stok Awal</label>
                    <input type="number" name="stock_current" value="{{ old('stock_current', 0) }}" min="0" class="form-input">
                </div>
                <div>
                    <label class="form-label">Stok Minimum Alert</label>
                    <input type="number" name="stock_minimum" value="{{ old('stock_minimum', 10) }}" min="0" class="form-input">
                    <p class="text-xs text-slate-400 mt-1">Akan muncul badge merah jika stok ≤ nilai ini</p>
                </div>
                <div>
                    <label class="form-label">Harga Beli (Rp)</label>
                    <input type="number" name="buy_price" value="{{ old('buy_price', 0) }}" min="0" class="form-input">
                </div>
                <div>
                    <label class="form-label">Harga Jual (Rp)</label>
                    <input type="number" name="sell_price" value="{{ old('sell_price', 0) }}" min="0" class="form-input">
                </div>
            </div>

            <div class="flex gap-3 pt-5">
                <x-button type="submit">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                    Simpan Produk
                </x-button>
                <x-button variant="secondary" href="{{ route('stock.index') }}">Batal</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection
