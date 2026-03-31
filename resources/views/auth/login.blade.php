<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — DistribusiPro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 font-sans antialiased">

<div class="min-h-screen flex">
    {{-- Left Panel (branding) --}}
    <div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 overflow-hidden">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                <defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/></pattern></defs>
                <rect width="100%" height="100%" fill="url(#grid)"/>
            </svg>
        </div>

        {{-- Floating cards --}}
        <div class="absolute top-24 left-12 bg-white/15 backdrop-blur-sm rounded-2xl p-4 border border-white/20 shadow-xl animate-pulse-slow">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
                </div>
                <div>
                    <p class="text-white/70 text-xs">Penjualan Hari Ini</p>
                    <p class="text-white font-bold text-lg">Rp 8.250.000</p>
                </div>
            </div>
        </div>

        <div class="absolute top-52 right-12 bg-white/15 backdrop-blur-sm rounded-2xl p-4 border border-white/20 shadow-xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-400/30 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-white/70 text-xs">12 Toko Dikunjungi</p>
                    <p class="text-emerald-300 font-bold text-sm">↑ 18% vs kemarin</p>
                </div>
            </div>
        </div>

        <div class="absolute bottom-32 left-16 bg-white/15 backdrop-blur-sm rounded-2xl p-4 border border-white/20 shadow-xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-400/30 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25"/></svg>
                </div>
                <div>
                    <p class="text-white/70 text-xs">5 Distribusi Aktif</p>
                    <p class="text-orange-300 font-bold text-sm">Dalam perjalanan</p>
                </div>
            </div>
        </div>

        {{-- Center Content --}}
        <div class="relative z-10 flex flex-col justify-center px-16 py-12">
            <div class="flex items-center gap-3 mb-12">
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center border border-white/30">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                </div>
                <span class="text-white font-bold text-2xl tracking-tight">DistribusiPro</span>
            </div>

            <h2 class="text-4xl font-extrabold text-white leading-tight mb-4">
                Kelola Distribusi<br>
                <span class="text-indigo-200">Lebih Cerdas.</span>
            </h2>
            <p class="text-indigo-200 text-lg leading-relaxed max-w-sm">
                Platform manajemen distribusi FMCG all-in-one untuk tim sales lapangan Anda.
            </p>

            <div class="mt-10 flex items-center gap-6">
                <div class="text-center">
                    <p class="text-white font-bold text-2xl">500+</p>
                    <p class="text-indigo-300 text-xs">Perusahaan</p>
                </div>
                <div class="w-px h-10 bg-white/20"></div>
                <div class="text-center">
                    <p class="text-white font-bold text-2xl">10rb+</p>
                    <p class="text-indigo-300 text-xs">Sales Aktif</p>
                </div>
                <div class="w-px h-10 bg-white/20"></div>
                <div class="text-center">
                    <p class="text-white font-bold text-2xl">99.9%</p>
                    <p class="text-indigo-300 text-xs">Uptime</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Panel (form) --}}
    <div class="flex-1 flex items-center justify-center px-6 py-12 lg:px-16">
        <div class="w-full max-w-md">
            {{-- Mobile logo --}}
            <div class="lg:hidden flex items-center gap-2 mb-8">
                <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375"/></svg>
                </div>
                <span class="font-bold text-slate-900 dark:text-white">DistribusiPro</span>
            </div>

            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-2">Masuk ke Akun</h1>
                <p class="text-slate-500 dark:text-slate-400">Belum punya akun? <a href="{{ route('register') }}" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">Daftar gratis</a></p>
            </div>

            @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 rounded-xl">
                <p class="text-red-700 dark:text-red-300 text-sm font-medium">{{ $errors->first() }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                    <input
                        type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                        placeholder="nama@perusahaan.com"
                    >
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Password
                        <a href="#" class="float-right font-normal text-indigo-600 dark:text-indigo-400 hover:underline">Lupa password?</a>
                    </label>
                    <div class="relative" x-data="{ show: false }">
                        <input
                            :type="show ? 'text' : 'password'" name="password" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm pr-12"
                            placeholder="••••••••"
                        >
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                    <label for="remember" class="ml-2 text-sm text-slate-600 dark:text-slate-400">Ingat saya</label>
                </div>

                <button type="submit" class="w-full py-3 px-6 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-200 text-sm">
                    Masuk Sekarang
                </button>
            </form>

            {{-- Demo credentials --}}
            <div class="mt-6 p-4 bg-indigo-50 dark:bg-indigo-950 rounded-xl border border-indigo-100 dark:border-indigo-900">
                <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-300 mb-2">Demo Kredensial:</p>
                <div class="space-y-1 text-xs text-indigo-600 dark:text-indigo-400">
                    <p>Owner: <span class="font-mono">owner@demo.com</span> / <span class="font-mono">password</span></p>
                    <p>Sales: <span class="font-mono">sales@demo.com</span> / <span class="font-mono">password</span></p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
