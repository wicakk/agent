@extends('layouts.app')
@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')
@section('breadcrumb', $product->name)

@section('content')
<div class="max-w-2xl">
    <div class="mb-4">
        <x-button variant="secondary" href="{{ route('stock.index') }}" size="sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Stok
        </x-button>
    </div>

    <form method="POST" action="{{ route('stock.update', $product) }}">
        @csrf
        @method('PUT')
        <x-card>
            <h3 class="font-bold text-slate-900 dark:text-white mb-5">Edit Informasi Produk</h3>

            @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 rounded-xl">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li class="text-red-700 dark:text-red-300 text-sm">• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="form-input" placeholder="Gudang Garam Surya 12">
                    </div>
                    <div>
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="form-input" placeholder="GGS-12">
                    </div>
                    <div>
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Kategori</label>
                        <select name="category" class="form-input">
                            <option value="">Pilih Kategori</option>
                            @foreach(['rokok','mie','air','minum','snack','lainnya'] as $cat)
                            <option value="{{ $cat }}" {{ old('category', $product->category) === $cat ? 'selected' : '' }}>
                                {{ ucfirst($cat) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Satuan</label>
                        <select name="unit" class="form-input">
                            @foreach(['pcs','pack','karton','dus'] as $u)
                            <option value="{{ $u }}" {{ old('unit', $product->unit) === $u ? 'selected' : '' }}>{{ ucfirst($u) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Gudang (read-only) --}}
                    <div>
                        <label class="form-label">Gudang</label>
                        <input type="text" value="{{ $product->warehouse->name }}" class="form-input bg-slate-50 dark:bg-slate-800 cursor-not-allowed" readonly>
                        <p class="text-xs text-slate-400 mt-1">Gudang tidak bisa diubah. Buat produk baru untuk gudang berbeda.</p>
                    </div>

                    <div>
                        <label class="form-label">Stok Minimum</label>
                        <input type="number" name="stock_minimum" value="{{ old('stock_minimum', $product->stock_minimum) }}" min="0" class="form-input">
                        <p class="text-xs text-slate-400 mt-1">Stok saat ini: <strong class="text-slate-600 dark:text-slate-300">{{ number_format($product->stock_current) }}</strong> (gunakan menu Pergerakan Stok untuk mengubah)</p>
                    </div>
                    <div>
                        <label class="form-label">Harga Beli (Rp)</label>
                        <input type="number" name="buy_price" value="{{ old('buy_price', $product->buy_price) }}" min="0" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Harga Jual (Rp)</label>
                        <input type="number" name="sell_price" value="{{ old('sell_price', $product->sell_price) }}" min="0" class="form-input">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="form-label">Deskripsi (opsional)</label>
                        <textarea name="description" rows="2" class="form-input resize-none" placeholder="Keterangan produk...">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                                class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Produk Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <x-button type="submit">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Simpan Perubahan
                    </x-button>
                    <x-button variant="secondary" href="{{ route('stock.index') }}">Batal</x-button>
                </div>
            </div>
        </x-card>
    </form>
</div>
@endsection
