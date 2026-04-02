@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('breadcrumb', $user->name)

@section('content')
<div class="max-w-xl">
    <div class="mb-5">
        <x-button variant="secondary" href="{{ route('users.index') }}" size="sm">← Kembali</x-button>
    </div>
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf @method('PUT')
        <x-card>
            <div class="flex items-center gap-4 mb-6 pb-5 border-b border-slate-100 dark:border-slate-800">
                <img src="{{ $user->avatar_url }}" class="w-14 h-14 rounded-2xl ring-2 ring-indigo-100 dark:ring-indigo-900">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">{{ $user->name }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <p class="text-sm text-slate-500">{{ $user->email }}</p>
                        @if($user->branch)
                        <x-badge color="indigo" size="xs">{{ $user->branch->name }}</x-badge>
                        @endif
                    </div>
                </div>
            </div>
            @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 rounded-xl">
                @foreach($errors->all() as $error)<p class="text-red-700 dark:text-red-300 text-sm">• {{ $error }}</p>@endforeach
            </div>
            @endif
            <div class="space-y-4">
                <div>
                    <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">No. HP</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Role <span class="text-red-500">*</span></label>
                        <select name="role" required class="form-input" {{ $user->isOwner() ? 'disabled' : '' }}>
                            <option value="sales" {{ old('role', $user->role) === 'sales' ? 'selected' : '' }}>Sales</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                            @if($user->isOwner())<option value="owner" selected>Owner</option>@endif
                        </select>
                    </div>
                    @if(auth()->user()->isOwner() && !$user->isOwner())
                    <div>
                        <label class="form-label">Cabang</label>
                        <select name="branch_id" class="form-input">
                            <option value="">— Tanpa Cabang —</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                <div class="border-t border-slate-100 dark:border-slate-800 pt-4">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Ganti Password <span class="font-normal text-slate-400">(kosongkan jika tidak ingin diubah)</span></p>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="form-label">Password Baru</label><input type="password" name="password" minlength="8" class="form-input" placeholder="Min. 8 karakter"></div>
                        <div><label class="form-label">Konfirmasi</label><input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi password baru"></div>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <x-button type="submit">Simpan Perubahan</x-button>
                    <x-button variant="secondary" href="{{ route('users.index') }}">Batal</x-button>
                </div>
            </div>
        </x-card>
    </form>
</div>
@endsection
