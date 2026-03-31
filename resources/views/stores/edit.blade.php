@extends('layouts.app')
@section('title', 'Edit Toko')
@section('page-title', 'Edit Toko')
@section('breadcrumb', $store->name)

@section('content')
<div class="max-w-2xl">
    <div class="mb-5">
        <x-button variant="secondary" href="{{ route('stores.index') }}" size="sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali
        </x-button>
    </div>

    <form method="POST" action="{{ route('stores.update', $store) }}">
        @csrf
        @method('PUT')
        <x-card>
            <h3 class="font-bold text-slate-900 dark:text-white mb-5">Edit Toko</h3>

            @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 rounded-xl">
                @foreach($errors->all() as $error)
                    <p class="text-red-700 dark:text-red-300 text-sm">• {{ $error }}</p>
                @endforeach
            </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Nama Toko <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $store->name) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Nama Pemilik</label>
                    <input type="text" name="owner_name" value="{{ old('owner_name', $store->owner_name) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">No. Telepon</label>
                    <input type="tel" name="phone" value="{{ old('phone', $store->phone) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Jenis Toko</label>
                    <select name="store_type" class="form-input">
                        <option value="">Pilih Jenis</option>
                        @foreach(['warung','minimarket','supermarket','toko_kelontong','depot'] as $type)
                        <option value="{{ $type }}" {{ old('store_type', $store->store_type) === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$type)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="form-input">
                        @foreach(['active' => 'Aktif', 'potential' => 'Potensial', 'inactive' => 'Tidak Aktif'] as $val => $label)
                        <option value="{{ $val }}" {{ old('status', $store->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Alamat Lengkap <span class="text-red-500">*</span></label>
                    <textarea name="address" required rows="2" class="form-input resize-none">{{ old('address', $store->address) }}</textarea>
                </div>
                <div>
                    <label class="form-label">Kota</label>
                    <input type="text" name="city" value="{{ old('city', $store->city) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Kecamatan</label>
                    <input type="text" name="district" value="{{ old('district', $store->district) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Latitude</label>
                    <input type="text" name="latitude" value="{{ old('latitude', $store->latitude) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Longitude</label>
                    <input type="text" name="longitude" value="{{ old('longitude', $store->longitude) }}" class="form-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Sales Penanggung Jawab</label>
                    <select name="user_id" class="form-input">
                        <option value="">Pilih Sales</option>
                        @foreach($salesUsers as $sales)
                        <option value="{{ $sales->id }}" {{ old('user_id', $store->user_id) == $sales->id ? 'selected' : '' }}>{{ $sales->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <x-button type="submit">Simpan Perubahan</x-button>
                <x-button variant="secondary" href="{{ route('stores.index') }}">Batal</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection
