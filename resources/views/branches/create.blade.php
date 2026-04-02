@extends('layouts.app')
@section('title', 'Tambah Cabang')
@section('page-title', 'Tambah Cabang Baru')

@section('content')
<div class="max-w-lg">
    <div class="mb-5"><x-button variant="secondary" href="{{ route('branches.index') }}" size="sm">← Kembali</x-button></div>
    <form method="POST" action="{{ route('branches.store') }}">
        @csrf
        <x-card>
            <h3 class="font-bold text-slate-900 dark:text-white mb-5">Informasi Cabang</h3>
            @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 rounded-xl">
                @foreach($errors->all() as $e)<p class="text-red-700 dark:text-red-300 text-sm">• {{ $e }}</p>@endforeach
            </div>
            @endif
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2"><label class="form-label">Nama Cabang *</label><input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Cabang Bandung"></div>
                    <div><label class="form-label">Kode Cabang</label><input type="text" name="code" value="{{ old('code') }}" class="form-input" placeholder="CBG"></div>
                    <div><label class="form-label">Kota</label><input type="text" name="city" value="{{ old('city') }}" class="form-input" placeholder="Bandung"></div>
                    <div class="col-span-2"><label class="form-label">Alamat</label><textarea name="address" rows="2" class="form-input resize-none" placeholder="Jl. Raya No. 1...">{{ old('address') }}</textarea></div>
                    <div><label class="form-label">Telepon</label><input type="tel" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="022-xxx"></div>
                    <div><label class="form-label">PIC (Nama Penanggung Jawab)</label><input type="text" name="pic_name" value="{{ old('pic_name') }}" class="form-input" placeholder="Budi Santoso"></div>
                </div>
                <div class="flex gap-3 pt-2"><x-button type="submit">Simpan Cabang</x-button><x-button variant="secondary" href="{{ route('branches.index') }}">Batal</x-button></div>
            </div>
        </x-card>
    </form>
</div>
@endsection
