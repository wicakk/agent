@extends('layouts.app')

@section('title', 'Buat Distribusi')
@section('page-title', 'Buat Distribusi Baru')
@section('breadcrumb', 'Input surat jalan pengiriman')

@section('content')

<form method="POST" action="{{ route('distribution.store') }}" x-data="distributionForm()">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Left column --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Header Info --}}
            <x-card>
                <h3 class="font-bold text-slate-900 dark:text-white mb-4">Informasi Pengiriman</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Gudang Asal</label>
                        <select name="warehouse_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Pilih Gudang</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Driver / Sales</label>
                        <select name="driver_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Pilih Driver</option>
                            @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Toko Tujuan (opsional)</label>
                        <select name="store_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Pilih Toko</option>
                            @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->name }} — {{ $store->city }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jadwal Pengiriman</label>
                        <input type="datetime-local" name="scheduled_at"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alamat Tujuan</label>
                        <textarea name="destination_address" rows="2" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                            placeholder="Masukkan alamat tujuan pengiriman..."></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Catatan (opsional)</label>
                        <textarea name="notes" rows="2"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                            placeholder="Instruksi khusus..."></textarea>
                    </div>
                </div>
            </x-card>

            {{-- Items --}}
            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-900 dark:text-white">Daftar Barang</h3>
                    <button type="button" @click="addItem()" class="flex items-center gap-1.5 text-sm text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah Barang
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="flex items-start gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700">
                            <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Produk</label>
                                    <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" required
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">Pilih Produk</option>
                                        @foreach($products as $prod)
                                        <option value="{{ $prod->id }}" data-price="{{ $prod->sell_price }}">{{ $prod->name }} ({{ $prod->stock_current }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Jumlah</label>
                                    <input type="number" :name="'items[' + index + '][quantity]'" x-model="item.quantity" min="1" required
                                        @input="updateSubtotal(index)"
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Harga Satuan</label>
                                    <input type="number" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" min="0" required
                                        @input="updateSubtotal(index)"
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                            <button type="button" @click="removeItem(index)" class="mt-5 p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950 rounded-xl transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>

                    <div x-show="items.length === 0" class="text-center py-8 text-slate-400 dark:text-slate-500">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25"/></svg>
                        <p class="text-sm">Belum ada barang. Klik "Tambah Barang".</p>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Right column: Summary --}}
        <div class="space-y-4">
            <x-card>
                <h3 class="font-bold text-slate-900 dark:text-white mb-4">Ringkasan</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 dark:text-slate-400">Total Item</span>
                        <span class="font-semibold text-slate-900 dark:text-white" x-text="items.length + ' jenis'"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 dark:text-slate-400">Total Qty</span>
                        <span class="font-semibold text-slate-900 dark:text-white" x-text="totalQty + ' pcs'"></span>
                    </div>
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-3 flex justify-between">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Total Nilai</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(totalValue)"></span>
                    </div>
                </div>

                <div class="mt-5 space-y-2">
                    <x-button type="submit" class="w-full justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                        Buat Distribusi
                    </x-button>
                    <x-button variant="secondary" href="{{ route('distribution.index') }}" class="w-full justify-center">
                        Batal
                    </x-button>
                </div>
            </x-card>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
function distributionForm() {
    return {
        items: [{ product_id: '', quantity: 1, unit_price: 0, subtotal: 0 }],
        get totalQty() { return this.items.reduce((s, i) => s + parseInt(i.quantity || 0), 0); },
        get totalValue() { return this.items.reduce((s, i) => s + (parseInt(i.quantity || 0) * parseFloat(i.unit_price || 0)), 0); },
        addItem() { this.items.push({ product_id: '', quantity: 1, unit_price: 0, subtotal: 0 }); },
        removeItem(i) { if (this.items.length > 1) this.items.splice(i, 1); },
        updateSubtotal(i) {
            const item = this.items[i];
            item.subtotal = parseInt(item.quantity || 0) * parseFloat(item.unit_price || 0);
        }
    }
}
</script>
@endpush
