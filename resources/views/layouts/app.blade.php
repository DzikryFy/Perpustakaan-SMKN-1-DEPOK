<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Perpustakaan Digital SMKN 1 Depok')</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col font-sans">

    <!-- Top Navigation Bar -->
    <header x-data="{ mobileMenuOpen: false, userDropdownOpen: false }" class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18">
                
                <!-- Logo & Brand -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logo-onedek.png') }}" alt="Logo SMKN 1 Depok" class="h-10 w-auto object-contain transition-transform group-hover:scale-105">
                    <div>
                        <div class="font-bold text-base text-blue-900 tracking-tight leading-tight flex items-center gap-1.5">
                            E-Library <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-blue-50 text-blue-700 border border-blue-200">SMKN 1 DEPOK</span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Perpustakaan Digital Terpadu</p>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center gap-1 font-medium text-sm text-slate-600">
                    <a href="{{ route('home') }}" class="px-3.5 py-2 rounded-lg transition-colors {{ request()->routeIs('home') ? 'text-blue-700 bg-blue-50 font-semibold' : 'hover:text-blue-700 hover:bg-slate-100' }}">
                        <i class="bi bi-house-door me-1.5 text-base"></i>Beranda
                    </a>
                    <a href="{{ route('catalog.index') }}" class="px-3.5 py-2 rounded-lg transition-colors {{ request()->routeIs('catalog.*') ? 'text-blue-700 bg-blue-50 font-semibold' : 'hover:text-blue-700 hover:bg-slate-100' }}">
                        <i class="bi bi-book me-1.5 text-base"></i>Koleksi Buku
                    </a>
                    <a href="{{ route('home') }}#fasilitas" class="px-3.5 py-2 rounded-lg transition-colors hover:text-blue-700 hover:bg-slate-100">
                        <i class="bi bi-building me-1.5 text-base"></i>Fasilitas
                    </a>
                    <a href="{{ route('home') }}#tata-tertib" class="px-3.5 py-2 rounded-lg transition-colors hover:text-blue-700 hover:bg-slate-100">
                        <i class="bi bi-file-earmark-ruled me-1.5 text-base"></i>Tata Tertib
                    </a>
                    @auth
                    <a href="{{ route('member.loans') }}" class="px-3.5 py-2 rounded-lg transition-colors {{ request()->routeIs('member.loans') ? 'text-blue-700 bg-blue-50 font-semibold' : 'hover:text-blue-700 hover:bg-slate-100' }}">
                        <i class="bi bi-journal-bookmark me-1.5 text-base"></i>Pinjaman Saya
                    </a>
                    <a href="{{ route('member.suggestions') }}" class="px-3.5 py-2 rounded-lg transition-colors {{ request()->routeIs('member.suggestions') ? 'text-blue-700 bg-blue-50 font-semibold' : 'hover:text-blue-700 hover:bg-slate-100' }}">
                        <i class="bi bi-lightbulb me-1.5 text-base"></i>Saran Buku
                    </a>
                    @endauth
                </nav>

                <!-- Desktop User Auth Controls -->
                <div class="hidden md:flex items-center gap-3">
                    @auth
                        @if(Auth::user()->isStaff())
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition-colors">
                            <i class="bi bi-speedometer2"></i> Panel Admin
                        </a>
                        @endif

                        <!-- User Profile Dropdown -->
                        <div class="relative" @click.outside="userDropdownOpen = false">
                            <button @click="userDropdownOpen = !userDropdownOpen" type="button" class="flex items-center gap-2.5 p-1.5 pl-3 rounded-full border border-slate-200 bg-white hover:bg-slate-50 transition-colors focus:outline-hidden">
                                <div class="text-right">
                                    <div class="text-xs font-bold text-slate-800 leading-tight">{{ Auth::user()->name }}</div>
                                    <div class="text-[10px] font-semibold text-blue-600 uppercase tracking-wider">{{ Auth::user()->role_label }}</div>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center shadow-xs">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <i class="bi bi-chevron-down text-xs text-slate-400 me-1"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="userDropdownOpen" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-lg border border-slate-100 py-1.5 text-xs text-slate-700 z-50">
                                <div class="px-4 py-2 border-b border-slate-100">
                                    <div class="font-semibold text-slate-900">{{ Auth::user()->name }}</div>
                                    <div class="text-slate-500 truncate">{{ Auth::user()->email }}</div>
                                </div>
                                @if(Auth::user()->isStaff())
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-slate-700 hover:bg-slate-50">
                                    <i class="bi bi-speedometer2 text-blue-600"></i> Dashboard Staff
                                </a>
                                <a href="{{ route('admin.books.index') }}" class="flex items-center gap-2 px-4 py-2 text-slate-700 hover:bg-slate-50">
                                    <i class="bi bi-book text-blue-600"></i> Kelola Katalog Buku
                                </a>
                                @endif
                                <a href="{{ route('member.loans') }}" class="flex items-center gap-2 px-4 py-2 text-slate-700 hover:bg-slate-50">
                                    <i class="bi bi-journal-bookmark text-emerald-600"></i> Buku Sedang Dipinjam
                                </a>
                                <a href="{{ route('member.suggestions') }}" class="flex items-center gap-2 px-4 py-2 text-slate-700 hover:bg-slate-50">
                                    <i class="bi bi-lightbulb text-amber-500"></i> Usulan Koleksi Saya
                                </a>
                                <div class="border-t border-slate-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 text-rose-600 hover:bg-rose-50 font-medium">
                                        <i class="bi bi-box-arrow-right"></i> Keluar Akun
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-semibold rounded-lg text-slate-700 hover:text-blue-700 hover:bg-slate-100 transition-colors">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 text-xs font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition-all">
                            Daftar Anggota
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex items-center md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-hidden">
                        <i class="bi text-xl" :class="mobileMenuOpen ? 'bi-x-lg' : 'bi-list'"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div x-show="mobileMenuOpen" x-transition class="md:hidden border-t border-slate-200 py-3 space-y-1">
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('home') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <i class="bi bi-house-door me-2"></i>Beranda
                </a>
                <a href="{{ route('catalog.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('catalog.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <i class="bi bi-book me-2"></i>Koleksi Buku
                </a>
                <a href="{{ route('home') }}#fasilitas" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <i class="bi bi-building me-2"></i>Fasilitas
                </a>
                <a href="{{ route('home') }}#tata-tertib" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <i class="bi bi-file-earmark-ruled me-2"></i>Tata Tertib
                </a>

                @auth
                <div class="pt-2 border-t border-slate-100">
                    <div class="px-3 py-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Akun Saya</div>
                    <a href="{{ route('member.loans') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                        <i class="bi bi-journal-bookmark me-2 text-emerald-600"></i>Pinjaman Saya
                    </a>
                    <a href="{{ route('member.suggestions') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                        <i class="bi bi-lightbulb me-2 text-amber-500"></i>Saran Buku
                    </a>
                    @if(Auth::user()->isStaff())
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-indigo-700 bg-indigo-50">
                        <i class="bi bi-speedometer2 me-2"></i>Panel Staff Perpustakaan
                    </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-rose-600 hover:bg-rose-50">
                            <i class="bi bi-box-arrow-right me-2"></i>Keluar
                        </button>
                    </form>
                </div>
                @else
                <div class="pt-3 border-t border-slate-200 grid grid-cols-2 gap-2 px-1">
                    <a href="{{ route('login') }}" class="text-center py-2 px-3 text-xs font-semibold rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Masuk</a>
                    <a href="{{ route('register') }}" class="text-center py-2 px-3 text-xs font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700">Daftar</a>
                </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Global Alert Notifications -->
    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm shadow-xs">
            <i class="bi bi-check-circle-fill text-lg text-emerald-600 shrink-0"></i>
            <div class="flex-1 font-medium">{!! session('success') !!}</div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs">
            <i class="bi bi-exclamation-triangle-fill text-lg text-rose-600 shrink-0"></i>
            <div class="flex-1 font-medium">{!! session('error') !!}</div>
        </div>
    </div>
    @endif

    <!-- Main Page Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                
                <!-- School & Library Identity -->
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('images/logo-onedek.png') }}" alt="Logo SMKN 1 Depok" class="h-10 w-auto">
                        <div>
                            <div class="font-bold text-slate-900 text-base">Perpustakaan Digital SMKN 1 Depok</div>
                            <div class="text-xs text-slate-500 font-medium">Membangun Generasi Cerdas & Berkarakter Literat</div>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed max-w-md">
                        Pusat sumber belajar, referensi kejuruan, dan pengembangan wawasan bagi seluruh civitas akademika SMK Negeri 1 Depok.
                    </p>
                    <div class="mt-4 flex items-center gap-4 text-slate-500 text-sm">
                        <span class="inline-flex items-center gap-1.5"><i class="bi bi-geo-alt-fill text-blue-600"></i> Jl. Tapos Raya No. 1, Kota Depok</span>
                    </div>
                </div>

                <!-- Navigation Quick Links -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3">Tautan Cepat</h3>
                    <ul class="space-y-2 text-sm text-slate-600 font-medium">
                        <li><a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Beranda</a></li>
                        <li><a href="{{ route('catalog.index') }}" class="hover:text-blue-600 transition-colors">Koleksi Buku BOS & Umum</a></li>
                        <li><a href="{{ route('home') }}#fasilitas" class="hover:text-blue-600 transition-colors">Fasilitas Perpustakaan</a></li>
                        <li><a href="{{ route('home') }}#tata-tertib" class="hover:text-blue-600 transition-colors">Peraturan & Jam Operasional</a></li>
                        <li><a href="{{ route('member.suggestions') }}" class="hover:text-blue-600 transition-colors">Ajukan Usulan Buku</a></li>
                    </ul>
                </div>

                <!-- Library Hours -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3">Jam Layanan</h3>
                    <ul class="space-y-2 text-xs text-slate-600">
                        <li class="flex justify-between pb-1.5 border-b border-slate-100">
                            <span class="font-medium">Senin - Kamis</span>
                            <span class="text-slate-800 font-semibold">07.00 - 14.00 WIB</span>
                        </li>
                        <li class="flex justify-between pb-1.5 border-b border-slate-100">
                            <span class="font-medium">Jumat</span>
                            <span class="text-slate-800 font-semibold">07.00 - 11.00 WIB</span>
                        </li>
                        <li class="flex justify-between">
                            <span class="font-medium">Sabtu - Minggu</span>
                            <span class="text-rose-600 font-semibold">Tutup / Libur</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} Perpustakaan SMKN 1 Depok. Seluruh Hak Cipta Dilindungi.</p>
                <div class="flex items-center gap-4">
                    <span>E-Library Management System v2.0 (Laravel 12)</span>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
