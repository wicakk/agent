<!DOCTYPE html>
<html lang="id" x-data="appLayout()" :class="{ 'dark': darkMode }" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ auth()->user()->company->name ?? 'DistribusiPro' }}</title>

    <!-- Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>

<body class="h-full bg-slate-50 dark:bg-slate-950 font-sans antialiased transition-colors duration-300">

<div class="flex h-full">

    {{-- ===== SIDEBAR (Desktop) ===== --}}
    <aside
        id="sidebar"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-auto"
    >
        {{-- Logo --}}
        <div class="flex items-center gap-3 h-16 px-5 border-b border-slate-100 dark:border-slate-800">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                </svg>
            </div>
            <div>
                <span class="font-bold text-slate-900 dark:text-white text-sm">DistribusiPro</span>
                <p class="text-xs text-slate-400 truncate max-w-[120px]">{{ auth()->user()->company->name ?? '' }}</p>
            </div>
            {{-- Close on mobile --}}
            <button @click="sidebarOpen = false" class="ml-auto lg:hidden text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto scrollbar-hide">

            {{-- Branch Badge (Admin/Sales) --}}
            @if(!auth()->user()->isOwner() && auth()->user()->branch)
            <div class="mb-2 px-3 py-2 bg-indigo-50 dark:bg-indigo-950 rounded-xl border border-indigo-100 dark:border-indigo-900">
                <p class="text-[10px] font-semibold text-indigo-400 uppercase tracking-wider">Cabang Anda</p>
                <p class="text-sm font-bold text-indigo-700 dark:text-indigo-300 truncate">{{ auth()->user()->branch->name }}</p>
            </div>
            @endif

            {{-- No Branch Warning --}}
            @if(!auth()->user()->isOwner() && !auth()->user()->branch_id)
            <div class="mb-2 px-3 py-2 bg-amber-50 dark:bg-amber-950 rounded-xl border border-amber-200 dark:border-amber-900">
                <p class="text-xs font-semibold text-amber-700 dark:text-amber-300">⚠ Belum di-assign ke cabang</p>
                <p class="text-xs text-amber-600 dark:text-amber-400">Hubungi Owner untuk assign cabang</p>
            </div>
            @endif

            @php
                $navItems = [
                    ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'roles' => ['owner','admin','sales']],
                    ['route' => 'stock.index', 'label' => 'Stok Gudang', 'icon' => 'cube', 'roles' => ['owner','admin','sales']],
                    ['route' => 'distribution.index', 'label' => 'Distribusi', 'icon' => 'truck', 'roles' => ['owner','admin','sales']],
                    ['route' => 'stores.index', 'label' => 'Manajemen Toko', 'icon' => 'building-storefront', 'roles' => ['owner','admin','sales']],
                    ['route' => 'users.index', 'label' => 'Tim Sales', 'icon' => 'user-group', 'roles' => ['owner','admin']],
                    ['route' => 'branches.index', 'label' => 'Cabang', 'icon' => 'building-office', 'roles' => ['owner']],
                ];
            @endphp

            @foreach($navItems as $item)
                @if(in_array(auth()->user()->role, $item['roles']))
                    <a
                        href="{{ route($item['route']) }}"
                        @class([
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150',
                            'bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300' => request()->routeIs($item['route'].'*'),
                            'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' => !request()->routeIs($item['route'].'*'),
                        ])
                    >
                        <x-nav-icon :name="$item['icon']" :active="request()->routeIs($item['route'].'*')"/>
                        {{ $item['label'] }}
                        @if($item['icon'] === 'cube')
                            @php $lowStock = auth()->user()->company->products()->whereColumn('stock_current', '<=', 'stock_minimum')->count() @endphp
                            @if($lowStock > 0)
                                <span class="ml-auto bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">{{ $lowStock }}</span>
                            @endif
                        @endif
                    </a>
                @endif
            @endforeach

            <div class="pt-3 pb-1">
                <p class="px-3 text-[10px] font-semibold text-slate-400 dark:text-slate-600 uppercase tracking-widest">Laporan</p>
            </div>

            <a href="{{ route('reports.index') }}"
               @class(['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150',
                   'bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300' => request()->routeIs('reports.*'),
                   'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' => !request()->routeIs('reports.*')])>
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('reports.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                Laporan Penjualan
            </a>

            <a href="{{ route('tracking.index') }}"
               @class(['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150',
                   'bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300' => request()->routeIs('tracking.*'),
                   'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' => !request()->routeIs('tracking.*')])>
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('tracking.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                </svg>
                Tracking GPS
            </a>

            @if(auth()->user()->isOwner())
            <div class="pt-3 pb-1">
                <p class="px-3 text-[10px] font-semibold text-slate-400 dark:text-slate-600 uppercase tracking-widest">Akun</p>
            </div>
            <a href="{{ route('billing.index') }}"
               @class(['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150',
                   'bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300' => request()->routeIs('billing.*'),
                   'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' => !request()->routeIs('billing.*')])>
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('billing.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                </svg>
                Billing & Paket
            </a>
            @endif
        </nav>

        {{-- User Profile Footer --}}
        <div class="px-3 py-4 border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full ring-2 ring-indigo-100 dark:ring-indigo-900">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 capitalize">{{ auth()->user()->role }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors" title="Logout">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Sidebar backdrop (mobile) --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-black/50 lg:hidden backdrop-blur-sm"></div>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Topbar --}}
        <header class="sticky top-0 z-30 h-16 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200 dark:border-slate-800 flex items-center gap-4 px-4 lg:px-6">
            {{-- Hamburger --}}
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>

            {{-- Page Title --}}
            <div class="flex-1">
                <h1 class="text-lg font-bold text-slate-900 dark:text-white">@yield('page-title', 'Dashboard')</h1>
                @if(View::hasSection('breadcrumb'))
                <p class="text-xs text-slate-500 dark:text-slate-400">@yield('breadcrumb')</p>
                @endif
            </div>

            {{-- Right actions --}}
            <div class="flex items-center gap-2">
                {{-- Dark mode toggle --}}
                <button @click="toggleDark()" class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                    </svg>
                    <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                    </svg>
                </button>

                {{-- Notifications --}}
                <button class="relative p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>

                {{-- Avatar --}}
                <img src="{{ auth()->user()->avatar_url }}" class="w-8 h-8 rounded-full ring-2 ring-indigo-200 dark:ring-indigo-800 cursor-pointer">
            </div>
        </header>

        {{-- Flash Messages --}}
        @if(session('success') || session('error') || session('warning'))
        <div class="px-4 lg:px-6 pt-4">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-900 text-green-800 dark:text-green-300 rounded-xl text-sm font-medium animate-slide-up">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                    <button @click="show = false" class="ml-auto text-green-500 hover:text-green-700"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
            @endif
            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 text-red-800 dark:text-red-300 rounded-xl text-sm font-medium animate-slide-up">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="flex items-center gap-3 p-4 bg-yellow-50 dark:bg-yellow-950 border border-yellow-200 dark:border-yellow-900 text-yellow-800 dark:text-yellow-300 rounded-xl text-sm font-medium">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    {{ session('warning') }}
                </div>
            @endif
        </div>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto pb-24 lg:pb-6">
            <div class="px-4 lg:px-6 py-6">
                @yield('content')
            </div>
        </main>
    </div>
</div>

{{-- ===== BOTTOM NAVIGATION (Mobile) ===== --}}
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 pb-safe">
    <div class="flex items-center justify-around h-16">
        <a href="{{ route('dashboard') }}" @class(['flex flex-col items-center gap-0.5 px-3 py-2 rounded-xl transition-colors', 'text-indigo-600 dark:text-indigo-400' => request()->routeIs('dashboard'), 'text-slate-400 dark:text-slate-500' => !request()->routeIs('dashboard')])>
            <svg class="w-6 h-6" fill="{{ request()->routeIs('dashboard') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
            <span class="text-[10px] font-medium">Home</span>
        </a>
        <a href="{{ route('stores.index') }}" @class(['flex flex-col items-center gap-0.5 px-3 py-2 rounded-xl transition-colors', 'text-indigo-600 dark:text-indigo-400' => request()->routeIs('stores.*'), 'text-slate-400 dark:text-slate-500' => !request()->routeIs('stores.*')])>
            <svg class="w-6 h-6" fill="{{ request()->routeIs('stores.*') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/></svg>
            <span class="text-[10px] font-medium">Toko</span>
        </a>
        {{-- FAB --}}
        <div class="relative">
            <a href="{{ route('distribution.create') }}" class="w-14 h-14 -mt-5 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg shadow-indigo-500/40 flex items-center justify-center text-white hover:scale-105 transition-transform">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </a>
        </div>
        <a href="{{ route('stock.index') }}" @class(['flex flex-col items-center gap-0.5 px-3 py-2 rounded-xl transition-colors', 'text-indigo-600 dark:text-indigo-400' => request()->routeIs('stock.*'), 'text-slate-400 dark:text-slate-500' => !request()->routeIs('stock.*')])>
            <svg class="w-6 h-6" fill="{{ request()->routeIs('stock.*') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
            <span class="text-[10px] font-medium">Stok</span>
        </a>
        <a href="{{ route('users.index') }}" @class(['flex flex-col items-center gap-0.5 px-3 py-2 rounded-xl transition-colors', 'text-indigo-600 dark:text-indigo-400' => request()->routeIs('users.*'), 'text-slate-400 dark:text-slate-500' => !request()->routeIs('users.*')])>
            <svg class="w-6 h-6" fill="{{ request()->routeIs('users.*') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
            <span class="text-[10px] font-medium">Profil</span>
        </a>
    </div>
</nav>

<script>
function appLayout() {
    return {
        sidebarOpen: false,
        darkMode: localStorage.getItem('darkMode') === 'true' ||
            (localStorage.getItem('darkMode') === null && window.matchMedia('(prefers-color-scheme: dark)').matches),
        toggleDark() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('darkMode', this.darkMode);
        }
    }
}
</script>

@stack('scripts')
</body>
</html>
