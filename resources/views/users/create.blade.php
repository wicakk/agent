@extends('layouts.app')
@section('title', 'Tambah User')
@section('page-title', 'Tambah User Baru')
@section('breadcrumb', 'Tambah anggota tim')

@section('content')
<div class="max-w-xl">
    <div class="mb-5">
        <x-button variant="secondary" href="{{ route('users.index') }}" size="sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali
        </x-button>
    </div>

    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <x-card>
            <h3 class="font-bold text-slate-900 dark:text-white mb-5">Informasi User</h3>

            @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 rounded-xl">
                @foreach($errors->all() as $error)
                    <p class="text-red-700 dark:text-red-300 text-sm">• {{ $error }}</p>
                @endforeach
            </div>
            @endif

            <div class="space-y-4">
                <div>
                    <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Andi Prasetyo">
                </div>
                <div>
                    <label class="form-label">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="form-input" placeholder="andi@perusahaan.com">
                </div>
                <div>
                    <label class="form-label">No. HP</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="08xxxxxxxxxx">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Role <span class="text-red-500">*</span></label>
                        <select name="role" required class="form-input">
                            <option value="sales" {{ old('role') === 'sales' ? 'selected' : '' }}>Sales (Lapangan)</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Kantor)</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Cabang
                            @if(!auth()->user()->isOwner())
                            <span class="text-slate-400 font-normal">(otomatis)</span>
                            @else
                            <span class="text-red-500">*</span>
                            @endif
                        </label>
                        @if(auth()->user()->isOwner())
                        <select name="branch_id" class="form-input">
                            <option value="">— Pilih Cabang —</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                            @endforeach
                        </select>
                        @else
                        <input type="text" value="{{ auth()->user()->branch?->name ?? 'Cabang Anda' }}" disabled class="form-input opacity-60 bg-slate-100 dark:bg-slate-800 cursor-not-allowed">
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="8" class="form-input" placeholder="Min. 8 karakter">
                    </div>
                    <div>
                        <label class="form-label">Konfirmasi Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required class="form-input" placeholder="Ulangi password">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <x-button type="submit">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                        Tambah User
                    </x-button>
                    <x-button variant="secondary" href="{{ route('users.index') }}">Batal</x-button>
                </div>
            </div>
        </x-card>
    </form>
</div>
@endsection
