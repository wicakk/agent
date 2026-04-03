@extends('layouts.app')
@section('title', 'Detail Distribusi')
@section('page-title', 'Detail Distribusi')
@section('breadcrumb', $distribution->delivery_no)

@section('content')

{{-- ===== ACTION BAR ===== --}}
<div class="mb-5 flex items-center gap-3 flex-wrap">
    <x-button variant="secondary" href="{{ route('distribution.index') }}" size="sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Kembali
    </x-button>

    @if($distribution->status === 'pending')
    <button onclick="openDepartModal()"
        class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/30 transition-all">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125"/></svg>
        Berangkat
    </button>
    @endif

    @if($distribution->status === 'in_transit')
    <button onclick="openDeliverModal()"
        class="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-500/30 transition-all">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
        Foto & Konfirmasi Terkirim
    </button>
    @endif

    @if($distribution->status === 'pending' && auth()->user()->isAdminOrOwner())
    <form method="POST" action="{{ route('distribution.update-status', $distribution) }}" onsubmit="return confirm('Batalkan distribusi ini?')">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="cancelled">
        <x-button type="submit" variant="danger" size="sm">Batalkan</x-button>
    </form>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- LEFT: Main Content --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Info Card --}}
        <x-card>
            <div class="flex items-start justify-between flex-wrap gap-3 mb-5">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $distribution->delivery_no }}</h2>
                        <x-badge :color="$distribution->status_color">{{ $distribution->status_label }}</x-badge>
                    </div>
                    <p class="text-sm text-slate-500">Dibuat {{ $distribution->created_at->format('d M Y, H:i') }}</p>
                </div>
                @if($distribution->status === 'in_transit')
                <div class="flex items-center gap-2 px-3 py-1.5 bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-900 rounded-xl">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    <span class="text-xs font-semibold text-green-700 dark:text-green-400">GPS Tracking Aktif</span>
                </div>
                @endif
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

        {{-- LIVE MAP --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h3 class="font-bold text-slate-900 dark:text-white">Peta Pengiriman</h3>
                    @if($distribution->status === 'in_transit')
                    <span class="text-xs text-green-600 dark:text-green-400 font-semibold flex items-center gap-1.5">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse inline-block"></span>Live
                    </span>
                    @endif
                </div>
                <div class="flex items-center gap-4 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-indigo-500 inline-block"></span>Rute</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span>Driver</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>Tujuan</span>
                </div>
            </div>
            <div id="delivery-map" class="h-72 lg:h-96"></div>
            @if($distribution->status === 'in_transit')
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center gap-6 text-sm">
                <div>
                    <p class="text-xs text-slate-400">Driver</p>
                    <p class="font-bold text-slate-900 dark:text-white">{{ $distribution->driver->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Berangkat</p>
                    <p class="font-bold text-slate-900 dark:text-white">{{ $distribution->departed_at?->format('H:i') }}</p>
                </div>
                <div id="speed-box" class="hidden">
                    <p class="text-xs text-slate-400">Kecepatan</p>
                    <p class="font-bold text-blue-600 dark:text-blue-400" id="live-speed">—</p>
                </div>
                <div class="ml-auto">
                    <p class="text-xs text-slate-400">Update</p>
                    <p class="font-bold text-slate-900 dark:text-white" id="last-update">—</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Timeline --}}
        <x-card>
            <h3 class="font-bold text-slate-900 dark:text-white mb-4">Status Timeline</h3>
            <div class="space-y-4">
                @php
                $steps = [
                    ['label'=>'Dibuat',    'sub'=>$distribution->created_at?->format('d M Y, H:i'),  'done'=>true,                                    'active'=>false, 'color'=>'yellow'],
                    ['label'=>'Berangkat', 'sub'=>$distribution->departed_at?->format('d M Y, H:i'), 'done'=>!!$distribution->departed_at,            'active'=>$distribution->status==='in_transit', 'color'=>'blue'],
                    ['label'=>'Terkirim',  'sub'=>$distribution->delivered_at?->format('d M Y, H:i'),'done'=>!!$distribution->delivered_at,            'active'=>false, 'color'=>'green'],
                ];
                if ($distribution->status==='cancelled') $steps[]=['label'=>'Dibatalkan','sub'=>$distribution->updated_at?->format('d M Y, H:i'),'done'=>true,'active'=>false,'color'=>'red'];
                @endphp
                @foreach($steps as $i => $s)
                <div class="flex items-start gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                            {{ $s['done'] ? 'bg-'.$s['color'].'-100 dark:bg-'.$s['color'].'-950' : 'bg-slate-100 dark:bg-slate-800' }}">
                            @if($s['done'] && !$s['active'])
                                <svg class="w-4 h-4 text-{{ $s['color'] }}-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            @elseif($s['active'])
                                <div class="w-2.5 h-2.5 rounded-full bg-{{ $s['color'] }}-500 animate-pulse"></div>
                            @else
                                <div class="w-2.5 h-2.5 rounded-full bg-slate-300 dark:bg-slate-600"></div>
                            @endif
                        </div>
                        @if($i < count($steps)-1)
                        <div class="w-px flex-1 min-h-[20px] mt-1 {{ $s['done'] ? 'bg-'.$s['color'].'-200 dark:bg-'.$s['color'].'-900' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
                        @endif
                    </div>
                    <div class="pb-4">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $s['label'] }}</p>
                        <p class="text-xs {{ $s['sub'] ? 'text-slate-400' : 'text-slate-300 dark:text-slate-600' }}">{{ $s['sub'] ?? 'Belum' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </x-card>

        {{-- Items --}}
        <x-card :padding="false">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 dark:text-white">Daftar Barang</h3>
                <span class="text-sm text-slate-400">{{ $distribution->items->count() }} item</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-slate-50 dark:bg-slate-800/50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Produk</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Dipesan</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Terkirim</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden sm:table-cell">Harga Satuan</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Subtotal</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @php $grand=0 @endphp
                        @foreach($distribution->items as $item)
                        @php $sub=$item->quantity_delivered*$item->unit_price; $grand+=$sub; @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="px-5 py-3.5"><p class="font-medium text-slate-900 dark:text-white">{{ $item->product->name ?? '—' }}</p><p class="text-xs text-slate-400">{{ $item->product->sku ?? '' }}</p></td>
                            <td class="px-4 py-3.5 text-center font-medium text-slate-700 dark:text-slate-300">{{ $item->quantity_requested }}</td>
                            <td class="px-4 py-3.5 text-center font-bold {{ $item->quantity_delivered >= $item->quantity_requested ? 'text-green-600' : ($item->quantity_delivered > 0 ? 'text-yellow-600' : 'text-slate-400') }}">{{ $item->quantity_delivered }}</td>
                            <td class="px-4 py-3.5 text-right text-slate-500 dark:text-slate-400 hidden sm:table-cell">Rp {{ number_format($item->unit_price,0,',','.') }}</td>
                            <td class="px-5 py-3.5 text-right font-semibold text-slate-900 dark:text-white">Rp {{ number_format($sub,0,',','.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr class="bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700">
                        <td colspan="4" class="px-5 py-3.5 font-bold text-slate-700 dark:text-slate-300 text-sm">Total Nilai</td>
                        <td class="px-5 py-3.5 text-right font-extrabold text-indigo-600 dark:text-indigo-400">Rp {{ number_format($grand,0,',','.') }}</td>
                    </tr></tfoot>
                </table>
            </div>
        </x-card>

        {{-- Proof Photo --}}
        @if($distribution->proof_photo)
        <x-card>
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">Bukti Pengiriman</h3>
            <img src="{{ asset('storage/'.$distribution->proof_photo) }}" class="w-full rounded-xl max-h-72 object-cover" alt="Bukti">
            @if($distribution->delivery_notes)
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 rounded-xl p-3 italic">{{ $distribution->delivery_notes }}</p>
            @endif
        </x-card>
        @endif
    </div>

    {{-- RIGHT: Sidebar --}}
    <div class="space-y-4">
        <x-card>
            <h4 class="font-bold text-slate-900 dark:text-white mb-4 text-sm">Ringkasan</h4>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Total Jenis</span><span class="font-semibold text-slate-900 dark:text-white">{{ $distribution->items->count() }} produk</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Total Dipesan</span><span class="font-semibold text-slate-900 dark:text-white">{{ number_format($distribution->items->sum('quantity_requested')) }} pcs</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Total Terkirim</span><span class="font-semibold {{ $distribution->status==='delivered' ? 'text-green-600' : 'text-slate-900 dark:text-white' }}">{{ number_format($distribution->items->sum('quantity_delivered')) }} pcs</span></div>
                <div class="border-t border-slate-100 dark:border-slate-800 pt-3 flex justify-between">
                    <span class="font-semibold text-slate-700 dark:text-slate-300">Nilai Total</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400">Rp {{ number_format($distribution->items->sum(fn($i)=>$i->quantity_delivered*$i->unit_price),0,',','.') }}</span>
                </div>
            </div>
        </x-card>

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
            <a href="tel:{{ $distribution->driver->phone }}"
               class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-green-50 dark:bg-green-950 text-green-700 dark:text-green-400 rounded-xl text-sm font-semibold hover:bg-green-100 transition-colors border border-green-200 dark:border-green-900">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                Hubungi Driver
            </a>
            @endif
        </x-card>

        @if($trackPoints->count() > 0)
        <x-card>
            <h4 class="font-bold text-slate-900 dark:text-white mb-3 text-sm">Statistik Perjalanan</h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Titik GPS</span><span class="font-semibold text-slate-900 dark:text-white">{{ $trackPoints->count() }}</span></div>
                @php $anom = $trackPoints->where('is_mock_location',true)->count() + $trackPoints->where('is_location_jump',true)->count() @endphp
                <div class="flex justify-between"><span class="text-slate-500">Anomali</span><span class="font-semibold {{ $anom > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $anom > 0 ? "⚠ $anom" : '✓ Aman' }}</span></div>
            </div>
        </x-card>
        @endif
    </div>
</div>


{{-- ===================== MODAL BERANGKAT ===================== --}}
<div id="modal-depart" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDepartModal()"></div>
    <div class="relative w-full max-w-sm bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800">
        <div class="p-6">
            <div class="w-14 h-14 bg-blue-100 dark:bg-blue-950 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white text-center mb-2">Aktifkan GPS Tracking</h3>
            <p class="text-sm text-slate-500 text-center mb-5">Lokasi Anda akan direkam selama perjalanan untuk memastikan pengiriman aman.</p>

            <div id="gps-status" class="mb-4 p-3 bg-slate-50 dark:bg-slate-800 rounded-xl text-center">
                <p class="text-sm text-slate-500" id="gps-status-text">⏳ Mendeteksi lokasi GPS...</p>
                <p class="text-xs text-slate-400 mt-1" id="gps-accuracy-text"></p>
            </div>

            <div id="gps-map-mini" class="hidden h-36 rounded-xl overflow-hidden mb-4"></div>

            <div class="flex gap-3">
                <button onclick="closeDepartModal()" class="flex-1 px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </button>
                <button id="btn-confirm-depart" disabled onclick="confirmDepart()"
                    class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-xl text-sm font-semibold transition-all">
                    Mulai Berangkat
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ===================== MODAL TERKIRIM ===================== --}}
<div id="modal-deliver" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDeliverModal()"></div>
    <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="font-bold text-slate-900 dark:text-white">Konfirmasi Terkirim</h3>
            <button onclick="closeDeliverModal()" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 space-y-4">
            {{-- Camera area --}}
            <div>
                <label class="form-label mb-2">Foto Bukti Pengiriman <span class="text-red-500">*</span></label>
                <div class="relative rounded-xl overflow-hidden bg-slate-900" style="aspect-ratio:4/3">
                    <video id="cam-video" autoplay playsinline muted class="w-full h-full object-cover"></video>
                    <img id="cam-photo" class="hidden w-full h-full object-cover" alt="">
                    <div id="cam-hint" class="absolute inset-0 flex flex-col items-center justify-center text-white/70 gap-2">
                        <svg class="w-12 h-12 opacity-50" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                        <p class="text-sm">Tekan Buka Kamera</p>
                    </div>
                </div>
                <canvas id="cam-canvas" class="hidden"></canvas>

                <div class="flex gap-2 mt-2">
                    <button id="btn-cam-open" onclick="startCamera()" class="flex-1 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                        Buka Kamera
                    </button>
                    <button id="btn-cam-snap" onclick="snapPhoto()" class="hidden flex-1 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="12" r="4" fill="currentColor"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/></svg>
                        Ambil Foto
                    </button>
                    <button id="btn-cam-retake" onclick="retakePhoto()" class="hidden flex-1 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        Foto Ulang
                    </button>
                    <label class="py-2 px-3 text-sm font-semibold text-slate-500 bg-slate-50 dark:bg-slate-800 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer flex items-center" title="Upload dari galeri">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        <input type="file" accept="image/*" class="hidden" onchange="loadGallery(this)">
                    </label>
                </div>
            </div>

            <div>
                <label class="form-label">Catatan (opsional)</label>
                <textarea id="deliver-notes" rows="2" class="form-input resize-none" placeholder="Barang diterima oleh... / Kondisi barang..."></textarea>
            </div>

            <div id="deliver-gps-info" class="p-3 bg-amber-50 dark:bg-amber-950 border border-amber-200 dark:border-amber-900 rounded-xl">
                <p class="text-xs text-amber-700 dark:text-amber-300 font-semibold">📍 Mendeteksi lokasi Anda...</p>
            </div>

            <button id="btn-do-deliver" disabled onclick="doDeliver()"
                class="w-full py-3 px-4 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold rounded-xl text-sm flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Konfirmasi Terkirim
            </button>
        </div>
    </div>
</div>

{{-- Loading --}}
<div id="loading-overlay" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-8 flex flex-col items-center gap-4 shadow-2xl min-w-[200px]">
        <div class="w-12 h-12 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
        <p class="text-slate-700 dark:text-slate-300 font-semibold text-center" id="loading-text">Memproses...</p>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ─── Config ─── */
const DIST_ID   = {{ $distribution->id }};
const STATUS    = '{{ $distribution->status }}';
const DEST_LAT  = {{ $distribution->destination_lat ?? 'null' }};
const DEST_LNG  = {{ $distribution->destination_lng ?? 'null' }};
const DEST_ADDR = '{{ addslashes(\Illuminate\Support\Str::limit($distribution->destination_address, 60)) }}';
const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const TRACK_POINTS = {!! $trackPoints->map(fn($p) => [
    'lat'     => (float)$p->latitude,
    'lng'     => (float)$p->longitude,
    'time'    => $p->logged_at->format('H:i:s'),
    'speed'   => $p->speed,
    'anomaly' => $p->is_mock_location || $p->is_location_jump,
])->toJson() !!};

/* ─── State ─── */
let map, driverMarker, polyline, miniMap, miniMarker;
let gpsWatchId = null, pollInterval = null;
let curLat = null, curLng = null;
let deliverLat = null, deliverLng = null;
let photoData = null;
let camStream = null;
let submitting = false;

/* ═══════════════════════════════════════
   MAP
═══════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    initMap();
    if (STATUS === 'in_transit') startLiveTracking();
});

function initMap() {
    const center = (DEST_LAT && DEST_LNG) ? [DEST_LAT, DEST_LNG] : [-6.9175, 107.6191];
    map = L.map('delivery-map').setView(center, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

    if (DEST_LAT && DEST_LNG) {
        const redIcon = L.divIcon({ className:'', html:`<div style="width:22px;height:22px;background:#ef4444;border:3px solid white;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,.4)"></div>`, iconSize:[22,22], iconAnchor:[11,11] });
        L.marker([DEST_LAT, DEST_LNG], { icon: redIcon }).addTo(map).bindPopup(`<b>Tujuan</b><br>${DEST_ADDR}`).openPopup();
    }

    if (TRACK_POINTS.length > 0) {
        const lls = TRACK_POINTS.map(p => [p.lat, p.lng]);
        polyline = L.polyline(lls, { color:'#6366f1', weight:4, opacity:.85 }).addTo(map);

        const last = TRACK_POINTS[TRACK_POINTS.length-1];
        driverMarker = makeDriverMarker(last.lat, last.lng);

        const bounds = polyline.getBounds();
        if (DEST_LAT && DEST_LNG) bounds.extend([DEST_LAT, DEST_LNG]);
        map.fitBounds(bounds, { padding:[40,40] });

        // Anomaly markers
        TRACK_POINTS.filter(p => p.anomaly).forEach(p => {
            L.circleMarker([p.lat, p.lng], { radius:7, color:'#ef4444', fillColor:'#ef4444', fillOpacity:.8 })
                .addTo(map).bindPopup('⚠ Anomali GPS');
        });
    }
}

function makeDriverMarker(lat, lng) {
    const icon = L.divIcon({
        className: '',
        html: `<div style="position:relative;width:20px;height:20px">
            <div style="position:absolute;top:-4px;left:-4px;width:28px;height:28px;background:rgba(59,130,246,.3);border-radius:50%;animation:gps-pulse 2s infinite"></div>
            <div style="width:20px;height:20px;background:#3b82f6;border:3px solid white;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,.4)"></div>
        </div>`,
        iconSize:[20,20], iconAnchor:[10,10]
    });
    return L.marker([lat, lng], { icon }).addTo(map).bindPopup('🚚 Driver');
}

/* ═══════════════════════════════════════
   LIVE TRACKING
═══════════════════════════════════════ */
function startLiveTracking() {
    // Poll server every 8s for new points
    pollInterval = setInterval(pollTrack, 8000);

    // Push GPS from this device
    if (navigator.geolocation) {
        gpsWatchId = navigator.geolocation.watchPosition(pos => {
            const lat = pos.coords.latitude, lng = pos.coords.longitude;
            const spd = pos.coords.speed ? (pos.coords.speed * 3.6).toFixed(0) : null;

            // Push to server silently
            fetch(`/distribution/${DIST_ID}/gps-log`, {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ latitude: lat, longitude: lng, speed: spd, accuracy: pos.coords.accuracy }),
            }).catch(()=>{});

            updateDriverPos(lat, lng);

            if (spd) {
                document.getElementById('live-speed').textContent = `${spd} km/h`;
                document.getElementById('speed-box')?.classList.remove('hidden');
            }
            document.getElementById('last-update').textContent = 'Baru saja';
        }, null, { enableHighAccuracy:true, maximumAge:5000, timeout:12000 });
    }
}

async function pollTrack() {
    try {
        const r = await fetch(`/distribution/${DIST_ID}/track-data`);
        const d = await r.json();

        if (d.points?.length > 0) {
            const lls = d.points.map(p => [+p.latitude, +p.longitude]);
            if (polyline) polyline.setLatLngs(lls);
            else polyline = L.polyline(lls, { color:'#6366f1', weight:4 }).addTo(map);

            const last = d.points[d.points.length-1];
            updateDriverPos(+last.latitude, +last.longitude);
        }
        if (d.driver?.last_seen) document.getElementById('last-update').textContent = d.driver.last_seen;
        if (d.status === 'delivered') { clearInterval(pollInterval); location.reload(); }
    } catch(e) {}
}

function updateDriverPos(lat, lng) {
    if (driverMarker) driverMarker.setLatLng([lat, lng]);
    else driverMarker = makeDriverMarker(lat, lng);

    if (polyline) { const pts = polyline.getLatLngs(); pts.push(L.latLng(lat,lng)); polyline.setLatLngs(pts); }
    else polyline = L.polyline([[lat,lng]], { color:'#6366f1', weight:4 }).addTo(map);
}

/* ═══════════════════════════════════════
   MODAL BERANGKAT
═══════════════════════════════════════ */
function openDepartModal() {
    document.getElementById('modal-depart').classList.remove('hidden');
    curLat = curLng = null;
    document.getElementById('btn-confirm-depart').disabled = true;
    document.getElementById('gps-status-text').textContent = '⏳ Mendeteksi lokasi GPS...';
    document.getElementById('gps-accuracy-text').textContent = '';

    if (!navigator.geolocation) {
        document.getElementById('gps-status-text').textContent = '❌ GPS tidak tersedia di perangkat ini.';
        return;
    }

    navigator.geolocation.getCurrentPosition(pos => {
        curLat = pos.coords.latitude;
        curLng = pos.coords.longitude;
        const acc = Math.round(pos.coords.accuracy);
        document.getElementById('gps-status-text').textContent = '✅ Lokasi berhasil dideteksi';
        document.getElementById('gps-accuracy-text').textContent = `Akurasi: ±${acc}m`;
        document.getElementById('btn-confirm-depart').disabled = false;

        // Mini map
        const miniEl = document.getElementById('gps-map-mini');
        miniEl.classList.remove('hidden');
        if (!miniMap) {
            miniMap = L.map('gps-map-mini', { zoomControl:false, dragging:false, scrollWheelZoom:false });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(miniMap);
        }
        miniMap.setView([curLat, curLng], 15);
        if (miniMarker) miniMarker.setLatLng([curLat, curLng]);
        else miniMarker = L.circleMarker([curLat, curLng], { radius:8, color:'#3b82f6', fillColor:'#3b82f6', fillOpacity:1 }).addTo(miniMap);
    }, err => {
        const m = { 1:'Izin GPS ditolak. Aktifkan di browser.', 2:'Sinyal GPS lemah, coba di luar ruangan.', 3:'Timeout, coba lagi.' };
        document.getElementById('gps-status-text').textContent = `❌ ${m[err.code]||'GPS error'}`;
    }, { enableHighAccuracy:true, timeout:15000 });
}

function closeDepartModal() { document.getElementById('modal-depart').classList.add('hidden'); }

async function confirmDepart() {
    if (!curLat || !curLng) return;
    showLoading('Memulai perjalanan...');
    try {
        const r = await fetch(`/distribution/${DIST_ID}/depart`, {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
            body: JSON.stringify({ latitude:curLat, longitude:curLng, accuracy:10 }),
        });
        const d = await r.json();
        if (d.success) { closeDepartModal(); location.reload(); }
        else { hideLoading(); alert(d.error||'Gagal.'); }
    } catch { hideLoading(); alert('Tidak dapat terhubung ke server.'); }
}

/* ═══════════════════════════════════════
   MODAL TERKIRIM
═══════════════════════════════════════ */
function openDeliverModal() {
    document.getElementById('modal-deliver').classList.remove('hidden');
    document.getElementById('btn-do-deliver').disabled = true;
    photoData = deliverLat = deliverLng = null;
    submitting = false;

    // Get GPS
    const tryGPS = () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(pos => {
                deliverLat = pos.coords.latitude;
                deliverLng = pos.coords.longitude;
                document.getElementById('deliver-gps-info').innerHTML =
                    `<p class="text-xs text-green-700 dark:text-green-300 font-semibold">✅ Lokasi terdeteksi — siap dikonfirmasi</p>`;
                checkDeliverReady();
            }, () => {
                deliverLat = DEST_LAT || -6.9175;
                deliverLng = DEST_LNG || 107.6191;
                document.getElementById('deliver-gps-info').innerHTML =
                    `<p class="text-xs text-amber-700 dark:text-amber-300 font-semibold">⚠ GPS tidak aktif — lokasi tujuan digunakan</p>`;
                checkDeliverReady();
            }, { enableHighAccuracy:true, timeout:8000 });
        } else {
            deliverLat = DEST_LAT||0; deliverLng = DEST_LNG||0;
            checkDeliverReady();
        }
    };
    tryGPS();
}

function closeDeliverModal() {
    stopCam();
    document.getElementById('modal-deliver').classList.add('hidden');
    resetCamUI();
    photoData = null;
}

function checkDeliverReady() {
    document.getElementById('btn-do-deliver').disabled = !(photoData && deliverLat !== null);
}

/* Camera */
async function startCamera() {
    try {
        camStream = await navigator.mediaDevices.getUserMedia({ video:{ facingMode:'environment', width:{ideal:1280} } });
        const vid = document.getElementById('cam-video');
        vid.srcObject = camStream;
        vid.classList.remove('hidden');
        document.getElementById('cam-hint').style.display = 'none';
        document.getElementById('cam-photo').classList.add('hidden');
        document.getElementById('btn-cam-open').classList.add('hidden');
        document.getElementById('btn-cam-snap').classList.remove('hidden');
        document.getElementById('btn-cam-retake').classList.add('hidden');
    } catch {
        alert('Tidak bisa akses kamera. Gunakan tombol upload (ikon panah) sebagai alternatif.');
    }
}

function snapPhoto() {
    const vid = document.getElementById('cam-video');
    const cvs = document.getElementById('cam-canvas');
    cvs.width = vid.videoWidth; cvs.height = vid.videoHeight;
    cvs.getContext('2d').drawImage(vid,0,0);
    photoData = cvs.toDataURL('image/jpeg', 0.85);
    stopCam();

    const img = document.getElementById('cam-photo');
    img.src = photoData;
    img.classList.remove('hidden');
    document.getElementById('cam-video').classList.add('hidden');
    document.getElementById('cam-hint').style.display = 'none';
    document.getElementById('btn-cam-snap').classList.add('hidden');
    document.getElementById('btn-cam-retake').classList.remove('hidden');
    checkDeliverReady();
}

function retakePhoto() {
    photoData = null;
    document.getElementById('cam-photo').classList.add('hidden');
    document.getElementById('btn-cam-retake').classList.add('hidden');
    checkDeliverReady();
    startCamera();
}

function loadGallery(input) {
    const file = input.files[0];
    if (!file) return;
    const r = new FileReader();
    r.onload = e => {
        photoData = e.target.result;
        const img = document.getElementById('cam-photo');
        img.src = photoData;
        img.classList.remove('hidden');
        document.getElementById('cam-video').classList.add('hidden');
        document.getElementById('cam-hint').style.display = 'none';
        document.getElementById('btn-cam-snap').classList.add('hidden');
        document.getElementById('btn-cam-retake').classList.remove('hidden');
        document.getElementById('btn-cam-open').classList.add('hidden');
        stopCam();
        checkDeliverReady();
    };
    r.readAsDataURL(file);
}

function stopCam() {
    if (camStream) { camStream.getTracks().forEach(t=>t.stop()); camStream = null; }
}

function resetCamUI() {
    document.getElementById('cam-video').classList.remove('hidden');
    document.getElementById('cam-photo').classList.add('hidden');
    document.getElementById('cam-hint').style.display = 'flex';
    document.getElementById('btn-cam-open').classList.remove('hidden');
    document.getElementById('btn-cam-snap').classList.add('hidden');
    document.getElementById('btn-cam-retake').classList.add('hidden');
}

async function doDeliver() {
    if (!photoData || submitting) return;
    submitting = true;
    showLoading('Menyimpan bukti pengiriman...');

    try {
        const r = await fetch(`/distribution/${DIST_ID}/deliver`, {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
            body: JSON.stringify({
                latitude:       deliverLat,
                longitude:      deliverLng,
                proof_photo:    photoData,
                delivery_notes: document.getElementById('deliver-notes').value,
            }),
        });
        const d = await r.json();
        if (d.success) {
            stopCam();
            closeDeliverModal();
            clearInterval(pollInterval);
            if (gpsWatchId) navigator.geolocation.clearWatch(gpsWatchId);
            showLoading('✅ Terkirim! Memuat ulang halaman...');
            setTimeout(()=>location.reload(), 1800);
        } else {
            submitting = false;
            hideLoading();
            alert(d.error||'Terjadi kesalahan, coba lagi.');
        }
    } catch {
        submitting = false;
        hideLoading();
        alert('Koneksi gagal. Pastikan internet aktif dan coba lagi.');
    }
}

/* Helpers */
function showLoading(txt) { document.getElementById('loading-text').textContent = txt; document.getElementById('loading-overlay').classList.remove('hidden'); }
function hideLoading() { document.getElementById('loading-overlay').classList.add('hidden'); }

/* CSS animation for driver marker pulse */
const s = document.createElement('style');
s.textContent = `@keyframes gps-pulse { 0%,100%{opacity:.5;transform:scale(1)} 50%{opacity:.15;transform:scale(1.6)} }`;
document.head.appendChild(s);
</script>
@endpush
