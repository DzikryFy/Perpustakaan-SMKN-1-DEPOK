<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Kelola Perpustakaan | SMKN 1 Depok')</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100/70 text-slate-800 antialiased font-sans min-h-screen flex" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 bg-slate-900/40 z-40 lg:hidden"></div>

    <!-- Sidebar Navigation -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed lg:static top-0 bottom-0 left-0 w-64 bg-white border-r border-slate-200/80 z-50 flex flex-col transition-transform duration-200 ease-in-out shadow-xs">
        
        <!-- Sidebar Header Brand -->
        <div class="h-18 px-5 flex items-center gap-3 border-b border-slate-100">
            <img src="{{ asset('images/logo-onedek.png') }}" alt="Logo SMKN 1 Depok" class="h-9 w-auto">
            <div>
                <div class="font-bold text-sm text-blue-900 leading-tight">Panel Perpustakaan</div>
                <div class="text-[11px] text-slate-500 font-medium">SMKN 1 DEPOK</div>
            </div>
        </div>

        <!-- User Role Profile Badge -->
        <div class="p-4 mx-3 my-3 rounded-xl bg-slate-50 border border-slate-200/70">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center shadow-xs shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="overflow-hidden">
                    <div class="text-xs font-bold text-slate-900 truncate">{{ Auth::user()->name }}</div>
                    <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full {{ Auth::user()->isKepala() ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ Auth::user()->role_label }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Sidebar Navigation Menu -->
        <nav class="flex-1 px-3 py-2 space-y-1 text-xs font-semibold overflow-y-auto">
            <div class="px-3 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Utama</div>
            
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/60' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="bi bi-speedometer2 text-base {{ request()->routeIs('admin.dashboard') ? 'text-blue-600' : 'text-slate-400' }}"></i>
                <span>Dashboard Ringkasan</span>
            </a>

            <div class="pt-3 px-3 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Layanan Sirkulasi</div>

            @php
                $pendingRequestsCount = \App\Models\BorrowRequest::where('status', 'menunggu')->count();
                $pendingSuggestionsCount = \App\Models\BookSuggestion::where('status', 'baru')->count();
            @endphp

            <a href="{{ route('admin.loans.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.loans.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/60' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <div class="flex items-center gap-2.5">
                    <i class="bi bi-arrow-left-right text-base {{ request()->routeIs('admin.loans.*') ? 'text-blue-600' : 'text-slate-400' }}"></i>
                    <span>Sirkulasi & Pinjaman</span>
                </div>
                @if($pendingRequestsCount > 0)
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-500 text-white animate-pulse">
                    {{ $pendingRequestsCount }}
                </span>
                @endif
            </a>

            <a href="{{ route('admin.books.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.books.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/60' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="bi bi-book text-base {{ request()->routeIs('admin.books.*') ? 'text-blue-600' : 'text-slate-400' }}"></i>
                <span>Katalog Buku (BOS & Umum)</span>
            </a>

            <a href="{{ route('admin.suggestions.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.suggestions.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/60' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <div class="flex items-center gap-2.5">
                    <i class="bi bi-lightbulb text-base {{ request()->routeIs('admin.suggestions.*') ? 'text-blue-600' : 'text-slate-400' }}"></i>
                    <span>Usulan Buku Masuk</span>
                </div>
                @if($pendingSuggestionsCount > 0)
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-600 text-white">
                    {{ $pendingSuggestionsCount }}
                </span>
                @endif
            </a>

            <div class="pt-3 px-3 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Administrasi</div>

            <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.reports.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/60' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="bi bi-bar-chart-line text-base {{ request()->routeIs('admin.reports.*') ? 'text-blue-600' : 'text-slate-400' }}"></i>
                <span>Laporan & Statistik</span>
            </a>

            @if(Auth::user()->isPustakawan())
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/60' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="bi bi-people text-base {{ request()->routeIs('admin.users.*') ? 'text-blue-600' : 'text-slate-400' }}"></i>
                <span>Kelola Pengguna & Anggota</span>
            </a>
            @endif
        </nav>

        <!-- Sidebar Footer Action -->
        <div class="p-3 border-t border-slate-100 space-y-1">
            <a href="{{ route('home') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                <i class="bi bi-box-arrow-up-right text-slate-400"></i>
                <span>Lihat Web Pengunjung</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Keluar Sistem</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        
        <!-- Admin Topbar -->
        <header class="h-18 bg-white border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between shadow-2xs sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                    <i class="bi bi-list text-xl"></i>
                </button>
                <div>
                    <h1 class="text-base font-bold text-slate-900">@yield('page_title', 'Dashboard')</h1>
                    <p class="text-xs text-slate-500">@yield('page_subtitle', 'Sistem Informasi Perpustakaan SMKN 1 Depok')</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('catalog.index') }}" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                    <i class="bi bi-search"></i> Cari Buku
                </a>
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-bold text-slate-800">{{ Auth::user()->name }}</div>
                    <div class="text-[10px] text-slate-500">{{ Auth::user()->role_label }}</div>
                </div>
            </div>
        </header>

        <!-- Flash Alert Messages -->
        @if(session('success'))
        <div class="px-4 sm:px-6 lg:px-8 pt-4">
            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium shadow-xs">
                <i class="bi bi-check-circle-fill text-emerald-600 text-base shrink-0"></i>
                <div class="flex-1">{!! session('success') !!}</div>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="px-4 sm:px-6 lg:px-8 pt-4">
            <div class="flex items-center gap-3 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium shadow-xs">
                <i class="bi bi-exclamation-triangle-fill text-rose-600 text-base shrink-0"></i>
                <div class="flex-1">{!! session('error') !!}</div>
            </div>
        </div>
        @endif

        <!-- Page Main View Body -->
        <main class="p-4 sm:p-6 lg:p-8 flex-1">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
