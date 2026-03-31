@extends('layouts.app')

@section('title', 'Stok Gudang')
@section('page-title', 'Stok Gudang')
@section('breadcrumb', 'Manajemen produk & pergerakan stok')

@section('content')

{{-- Low stock alert --}}
@if($lowStockCount > 0)
<div class="mb-5 p-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 rounded-2xl flex items-center gap-3">
    <div class="w-9 h-9 bg-red-100 dark:bg-red-900 rounded-xl flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
    </div>
    <p class="text-red-800 dark:text-red-200 text-sm font-medium flex-1">
        <strong>{{ $lowStockCount }} produk</strong> memiliki stok di bawah minimum.
        <a href="{{ route('stock.index', ['status' => 'low']) }}" class="underline">Lihat sekarang →</a>
    </p>
</div>
@endif

{{-- Toolbar --}}
<div class="flex flex-col sm:flex-row gap-3 mb-5">
    <form method="GET" action="{{ route('stock.index') }}" class="flex flex-1 flex-wrap gap-2">
        <div class="relative flex-1 min-w-[180px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                class="w-full pl-9 pr-4 py-2.5 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Cari produk, SKU...">
        </div>
        <select name="warehouse_id" class="text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Gudang</option>
            @foreach($warehouses as $wh)
            <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
            @endforeach
        </select>
        <select name="status" class="text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Status</option>
            <option value="low" {{ request('status') === 'low' ? 'selected' : '' }}>Stok Rendah</option>
        </select>
        <button type="submit" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Filter</button>
    </form>

    @if(auth()->user()->isAdminOrOwner())
    <x-button href="{{ route('stock.create') }}">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Tambah Produk
    </x-button>
    @endif
</div>

{{-- Table --}}
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Produk</th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Kategori</th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Gudang</th>
                    <th class="text-center px-4 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stok</th>
                    <th class="text-right px-4 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Harga Jual</th>
                    <th class="text-center px-4 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($products as $product)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm shrink-0">
                                {{ strtoupper(substr($product->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $product->name }}</p>
                                <p class="text-xs text-slate-400">{{ $product->sku ?: 'No SKU' }} · {{ $product->brand }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 hidden md:table-cell">
                        <span class="capitalize text-slate-600 dark:text-slate-400">{{ $product->category ?: '-' }}</span>
                    </td>
                    <td class="px-4 py-4 hidden md:table-cell text-slate-600 dark:text-slate-400">{{ $product->warehouse->name }}</td>
                    <td class="px-4 py-4 text-center">
                        <div class="flex flex-col items-center">
                            <span class="font-bold text-slate-900 dark:text-white">{{ number_format($product->stock_current) }}</span>
                            <span class="text-xs text-slate-400">min: {{ $product->stock_minimum }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-4 text-right hidden sm:table-cell text-slate-700 dark:text-slate-300">
                        Rp {{ number_format($product->sell_price, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4 text-center">
                        @if($product->stock_status === 'empty')
                            <x-badge color="red">Habis</x-badge>
                        @elseif($product->stock_status === 'low')
                            <x-badge color="yellow">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z" clip-rule="evenodd"/></svg>
                                Rendah
                            </x-badge>
                        @else
                            <x-badge color="green">Normal</x-badge>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1" x-data="{ open: false }" @click.away="open = false">
                            {{-- Movement button --}}
                            <button
                                x-data
                                @click="$dispatch('open-movement-modal', { id: {{ $product->id }}, name: '{{ addslashes($product->name) }}', stock: {{ $product->stock_current }} })"
                                class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950 rounded-lg transition-colors"
                                title="Tambah/Kurang Stok"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                            </button>

                            <a href="{{ route('stock.history', $product) }}" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors" title="Riwayat">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </a>

                            @if(auth()->user()->isAdminOrOwner())
                            <a href="{{ route('stock.edit', $product) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950 rounded-lg transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            </a>

                            <form method="POST" action="{{ route('stock.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950 rounded-lg transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-16 text-center">
                        <svg class="w-12 h-12 text-slate-300 dark:text-slate-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                        <p class="text-slate-500 dark:text-slate-400 font-medium">Tidak ada produk ditemukan</p>
                        @if(auth()->user()->isAdminOrOwner())
                        <a href="{{ route('stock.create') }}" class="mt-3 inline-block text-sm text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">+ Tambah produk pertama</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
        {{ $products->links() }}
    </div>
    @endif
</div>

{{-- ===== STOCK MOVEMENT MODAL ===== --}}
<div
    x-data="movementModal()"
    x-show="open"
    x-cloak
    @open-movement-modal.window="show($event.detail)"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
>
    <div @click="open = false" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 animate-slide-up">
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">Pergerakan Stok</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400" x-text="productName"></p>
            </div>
            <button @click="open = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form :action="'/stock/' + productId + '/movement'" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Jenis Pergerakan</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-3 border-2 rounded-xl cursor-pointer transition-all" :class="type === 'in' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950' : 'border-slate-200 dark:border-slate-700'">
                        <input type="radio" name="type" value="in" x-model="type" class="sr-only">
                        <svg class="w-4 h-4" :class="type === 'in' ? 'text-emerald-600' : 'text-slate-400'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span class="text-sm font-semibold" :class="type === 'in' ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400'">Masuk (IN)</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 border-2 rounded-xl cursor-pointer transition-all" :class="type === 'out' ? 'border-red-500 bg-red-50 dark:bg-red-950' : 'border-slate-200 dark:border-slate-700'">
                        <input type="radio" name="type" value="out" x-model="type" class="sr-only">
                        <svg class="w-4 h-4" :class="type === 'out' ? 'text-red-600' : 'text-slate-400'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/></svg>
                        <span class="text-sm font-semibold" :class="type === 'out' ? 'text-red-700 dark:text-red-300' : 'text-slate-600 dark:text-slate-400'">Keluar (OUT)</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Jumlah <span class="text-slate-400 font-normal">(Stok saat ini: <span x-text="currentStock"></span>)</span>
                </label>
                <input type="number" name="quantity" x-model="quantity" min="1" required
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alasan</label>
                <input type="text" name="reason"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                    placeholder="Penerimaan dari supplier, penjualan, dll.">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="open = false" class="flex-1 px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl text-sm font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/25">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function movementModal() {
    return {
        open: false,
        productId: null,
        productName: '',
        currentStock: 0,
        type: 'in',
        quantity: 1,
        show(detail) {
            this.productId = detail.id;
            this.productName = detail.name;
            this.currentStock = detail.stock;
            this.type = 'in';
            this.quantity = 1;
            this.open = true;
        }
    }
}
</script>
@endpush
