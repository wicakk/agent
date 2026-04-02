@extends('layouts.app')
@section('title', 'Edit Cabang')
@section('page-title', 'Edit Cabang')
@section('breadcrumb', $branch->name)

@section('content')
<div class="max-w-lg">
    <div class="mb-5"><x-button variant="secondary" href="{{ route('branches.index') }}" size="sm">← Kembali</x-button></div>
    <form method="POST" action="{{ route('branches.update', $branch) }}">
        @csrf @method('PUT')
        <x-card>
            <h3 class="font-bold text-slate-900 dark:text-white mb-5">Edit Cabang</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2"><label class="form-label">Nama Cabang *</label><input type="text" name="name" value="{{ old('name', $branch->name) }}" required class="form-input"></div>
                    <div><label class="form-label">Kode</label><input type="text" name="code" value="{{ old('code', $branch->code) }}" class="form-input"></div>
                    <div><label class="form-label">Kota</label><input type="text" name="city" value="{{ old('city', $branch->city) }}" class="form-input"></div>
                    <div class="col-span-2"><label class="form-label">Alamat</label><textarea name="address" rows="2" class="form-input resize-none">{{ old('address', $branch->address) }}</textarea></div>
                    <div><label class="form-label">Telepon</label><input type="tel" name="phone" value="{{ old('phone', $branch->phone) }}" class="form-input"></div>
                    <div><label class="form-label">PIC</label><input type="text" name="pic_name" value="{{ old('pic_name', $branch->pic_name) }}" class="form-input"></div>
                    <div class="col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ $branch->is_active ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600">
                        <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Cabang Aktif</label>
                    </div>
                </div>
                <div class="flex gap-3 pt-2"><x-button type="submit">Simpan Perubahan</x-button><x-button variant="secondary" href="{{ route('branches.index') }}">Batal</x-button></div>
            </div>
        </x-card>
    </form>
</div>
@endsection
