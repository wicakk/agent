<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar — DistribusiPro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans antialiased flex items-center justify-center px-4 py-12">

<div class="w-full max-w-lg">
    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-xl shadow-indigo-500/30 mb-4">
            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124"/></svg>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white">Buat Akun Baru</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Trial gratis 14 hari, tanpa kartu kredit</p>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-8">
        @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 rounded-xl">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li class="text-red-700 dark:text-red-300 text-sm">• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Perusahaan / Distributor</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" required
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all"
                    placeholder="PT Maju Bersama Distribusi">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Anda (Owner)</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all"
                        placeholder="Budi Santoso">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">No. HP</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all"
                        placeholder="08xxxxxxxxxx">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all"
                    placeholder="owner@perusahaan.com">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all"
                        placeholder="Min. 8 karakter">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all"
                        placeholder="Ulangi password">
                </div>
            </div>

            <button type="submit" class="w-full py-3 px-6 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 transition-all duration-200 text-sm mt-2">
                Mulai Trial Gratis 14 Hari →
            </button>
        </form>
    </div>

    <p class="text-center text-sm text-slate-500 dark:text-slate-400 mt-6">
        Sudah punya akun? <a href="{{ route('login') }}" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">Masuk di sini</a>
    </p>
</div>

</body>
</html>
