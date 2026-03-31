@extends('layouts.app')
@section('title', 'Detail Distribusi')
@section('page-title', 'Detail Distribusi')
@section('breadcrumb', $distribution->delivery_no)

@section('content')
<div class="mb-5 flex items-center gap-3 flex-wrap">
    <x-button variant="secondary" href="{{ route('distribution.index') }}" size="sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Kembali
    </x-button>

    @if(in_array($distribution->status, ['pending','in_transit']))
    <div class="flex gap-2">
        @if($distribution->status === 'pending')
        <form method="POST" action="{{ route('distribution.update-status', $distribution) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="in_transit">
            <x-button type="submit" variant="secondary" size="sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25"/></svg>
                Berangkat
            </x-button>
        </form>
        @endif
        @if($distribution->status === 'in_transit')
        <form method="POST" action="{{ route('distribution.update-status', $distribution) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="delivered">
            <x-button type="submit" size="sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Tandai Terkirim
            </x-button>
        </form>
        @endif
        @if(auth()->user()->isAdminOrOwner() && $distribution->status === 'pending')
        <form method="POST" action="{{ route('distribution.update-status', $distribution) }}" onsubmit="return confirm('Batalkan distribusi ini?')">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="cancelled">
            <x-button type="submit" variant="danger" size="sm">Batalkan</x-button>
        </form>
        @endif
    </div>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Left: Main Info --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Header Card --}}
        <x-card>
            <div class="flex items-start justify-between flex-wrap gap-3 mb-5">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h2 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $distribution->delivery_no }}</h2>
                        <x-badge :color="$distribution->status_color">{{ $distribution->status_label }}</x-badge>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Dibuat {{ $distribution->created_at->format('d M Y, H:i') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Driver / Sales</p>
                        <div class="flex items-center gap-2">
                            <img src="{{ $distribution->driver->avatar_url }}" class="w-7 h-7 rounded-full">
                            <span class="font-semibold text-slate-900 dark:text-white text-sm">{{ $distribution->driver->name }}</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Gudang Asal</p>
                        <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $distribution->warehouse->name }}</p>
                    </div>
                    @if($distribution->store)
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Toko Tujuan</p>
                        <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $distribution->store->name }}</p>
                    </div>
                    @endif
                </div>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Alamat Tujuan</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $distribution->destination_address }}</p>
                    </div>
                    @if($distribution->scheduled_at)
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Jadwal</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $distribution->scheduled_at->format('d M Y, H:i') }}</p>
                    </div>
                    @endif
                    @if($distribution->notes)
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Catatan</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $distribution->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </x-card>

        {{-- Timeline --}}
        <x-card>
            <h3 class="font-bold text-slate-900 dark:text-white mb-4">Status Timeline</h3>
            <div class="space-y-4">
                @php
                $steps = [
                    ['status' => 'pending',    'label' => 'Dibuat',          'time' => $distribution->created_at,  'color' => 'yellow'],
                    ['status' => 'in_transit', 'label' => 'Berangkat',       'time' => $distribution->departed_at, 'color' => 'blue'],
                    ['status' => 'delivered',  'label' => 'Terkirim',        'time' => $distribution->delivered_at,'color' => 'green'],
                ];
                $statusOrder = ['pending' => 0, 'in_transit' => 1, 'delivered' => 2, 'cancelled' => -1];
                $currentIdx  = $statusOrder[$distribution->status] ?? 0;
                @endphp

                @foreach($steps as $i => $step)
                <div class="flex items-start gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                            {{ $i <= $currentIdx && $distribution->status !== 'cancelled'
                               ? 'bg-' . $step['color'] . '-100 dark:bg-' . $step['color'] . '-950'
                               : 'bg-slate-100 dark:bg-slate-800' }}">
                            @if($i < $currentIdx || ($i === $currentIdx && $distribution->status === 'delivered'))
                                <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            @elseif($i === $currentIdx && $distribution->status !== 'cancelled')
                                <div class="w-2.5 h-2.5 rounded-full bg-{{ $step['color'] }}-500 animate-pulse"></div>
                            @else
                                <div class="w-2.5 h-2.5 rounded-full bg-slate-300 dark:bg-slate-600"></div>
                            @endif
                        </div>
                        @if($i < count($steps) - 1)
                        <div class="w-px flex-1 min-h-[20px] mt-1 {{ $i < $currentIdx ? 'bg-green-300 dark:bg-green-800' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
                        @endif
                    </div>
                    <div class="pb-4">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $step['label'] }}</p>
                        @if($step['time'])
                            <p class="text-xs text-slate-400">{{ $step['time']->format('d M Y, H:i') }}</p>
                        @else
                            <p class="text-xs text-slate-300 dark:text-slate-600">Belum</p>
                        @endif
                    </div>
                </div>
                @endforeach

                @if($distribution->status === 'cancelled')
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-950 flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-red-600 dark:text-red-400">Dibatalkan</p>
                        <p class="text-xs text-slate-400">{{ $distribution->updated_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </x-card>

        {{-- Items Table --}}
        <x-card :padding="false">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 dark:text-white">Daftar Barang</h3>
                <span class="text-sm text-slate-400">{{ $distribution->items->count() }} item</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Produk</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Dipesan</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Terkirim</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Harga Satuan</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @php $grandTotal = 0; @endphp
                        @foreach($distribution->items as $item)
                        @php $subtotal = $item->quantity_delivered * $item->unit_price; $grandTotal += $subtotal; @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-slate-900 dark:text-white">{{ $item->product->name ?? 'Produk dihapus' }}</p>
                                <p class="text-xs text-slate-400">{{ $item->product->sku ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-center font-medium text-slate-700 dark:text-slate-300">{{ number_format($item->quantity_requested) }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="font-bold {{ $item->quantity_delivered >= $item->quantity_requested ? 'text-green-600' : ($item->quantity_delivered > 0 ? 'text-yellow-600' : 'text-slate-400') }}">
                                    {{ number_format($item->quantity_delivered) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right text-slate-600 dark:text-slate-400">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="px-5 py-3.5 text-right font-semibold text-slate-900 dark:text-white">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700">
                            <td colspan="4" class="px-5 py-3.5 text-sm font-bold text-slate-700 dark:text-slate-300">Total Nilai</td>
                            <td class="px-5 py-3.5 text-right font-extrabold text-indigo-600 dark:text-indigo-400">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-card>
    </div>

    {{-- Right: Sidebar Info --}}
    <div class="space-y-4">
        {{-- Quick Stats --}}
        <x-card>
            <h4 class="font-bold text-slate-900 dark:text-white mb-4 text-sm">Ringkasan</h4>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Total Jenis</span>
                    <span class="font-semibold text-slate-900 dark:text-white">{{ $distribution->items->count() }} produk</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Total Dipesan</span>
                    <span class="font-semibold text-slate-900 dark:text-white">{{ number_format($distribution->items->sum('quantity_requested')) }} pcs</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Total Terkirim</span>
                    <span class="font-semibold {{ $distribution->status === 'delivered' ? 'text-green-600' : 'text-slate-900 dark:text-white' }}">{{ number_format($distribution->items->sum('quantity_delivered')) }} pcs</span>
                </div>
                <div class="border-t border-slate-100 dark:border-slate-800 pt-3 flex justify-between text-sm">
                    <span class="font-semibold text-slate-700 dark:text-slate-300">Nilai Total</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400">
                        Rp {{ number_format($distribution->items->sum(fn($i) => $i->quantity_delivered * $i->unit_price), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </x-card>

        {{-- Driver Info --}}
        <x-card>
            <h4 class="font-bold text-slate-900 dark:text-white mb-4 text-sm">Info Driver</h4>
            <div class="flex items-center gap-3 mb-3">
                <img src="{{ $distribution->driver->avatar_url }}" class="w-12 h-12 rounded-xl">
                <div>
                    <p class="font-bold text-slate-900 dark:text-white">{{ $distribution->driver->name }}</p>
                    <p class="text-xs text-slate-400">{{ $distribution->driver->phone ?? 'Tidak ada telp' }}</p>
                </div>
            </div>
            @if($distribution->driver->phone)
            <a href="tel:{{ $distribution->driver->phone }}" class="flex items-center justify-center gap-2 w-full py-2 px-4 bg-green-50 dark:bg-green-950 text-green-700 dark:text-green-400 rounded-xl text-sm font-semibold hover:bg-green-100 dark:hover:bg-green-900 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                Hubungi Driver
            </a>
            @endif
        </x-card>

        {{-- Map --}}
        @if($distribution->destination_lat && $distribution->destination_lng)
        <x-card>
            <h4 class="font-bold text-slate-900 dark:text-white mb-3 text-sm">Peta Tujuan</h4>
            <div id="dist-map" class="h-48 rounded-xl overflow-hidden"></div>
        </x-card>
        @endif

        {{-- Proof photo --}}
        @if($distribution->proof_photo)
        <x-card>
            <h4 class="font-bold text-slate-900 dark:text-white mb-3 text-sm">Bukti Pengiriman</h4>
            <img src="{{ asset('storage/' . $distribution->proof_photo) }}" class="w-full rounded-xl" alt="Bukti pengiriman">
        </x-card>
        @endif
    </div>
</div>

@if($distribution->destination_lat && $distribution->destination_lng)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('dist-map').setView([{{ $distribution->destination_lat }}, {{ $distribution->destination_lng }}], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
    L.marker([{{ $distribution->destination_lat }}, {{ $distribution->destination_lng }}])
        .addTo(map)
        .bindPopup('<b>Tujuan Pengiriman</b><br>{{ addslashes(\Illuminate\Support\Str::limit($distribution->destination_address, 60)) }}')
        .openPopup();
});
</script>
@endpush
@endif

@endsection
