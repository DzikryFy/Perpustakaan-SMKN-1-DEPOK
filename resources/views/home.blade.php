@extends('layouts.app')

@section('title', 'Perpustakaan SMKN 1 Depok | Pusat Literasi & Kejuruan')

@section('content')
<!-- Hero Section: Hybrid One-Search (UT) & Editorial Book Spotlight (Malaka Books) -->
<section class="bg-slate-50/60 border-b border-slate-200/80 pt-10 pb-16 lg:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
            
            <!-- Left Column: One-Search & Library Mission -->
            <div class="lg:col-span-7">
                <p class="text-xs font-semibold tracking-wider uppercase text-blue-700 mb-3">
                    Perpustakaan SMK Negeri 1 Depok
                </p>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-[1.15]">
                    Pusat Literasi, Pengetahuan & Keahlian Kejuruan
                </h1>

                <p class="mt-4 text-sm sm:text-base text-slate-600 leading-relaxed max-w-xl">
                    Portal penelusuran katalog terpadu untuk siswa dan tenaga pendidik. Temukan buku teks kurikulum, literatur kejuruan, dan bacaan umum dengan ketersediaan fisik di rak.
                </p>

                <!-- One-Search Bar (Ala OPAC Perpustakaan UT) -->
                <form action="{{ route('catalog.index') }}" method="GET" class="mt-7">
                    <div class="bg-white rounded-xl border border-slate-300 p-1.5 shadow-sm focus-within:ring-2 focus-within:ring-blue-600 focus-within:border-transparent transition flex flex-col sm:flex-row gap-1.5">
                        <div class="flex-1 flex items-center px-3 py-1">
                            <span class="text-slate-400 mr-2.5">
                                <i class="bi bi-search text-sm"></i>
                            </span>
                            <input type="text" name="q" placeholder="Cari judul buku, topik kejuruan, pengarang, nomor BOS..."
                                class="w-full text-sm text-slate-800 bg-transparent placeholder-slate-400 focus:outline-hidden">
                        </div>

                        <div class="flex items-center gap-1.5 border-t sm:border-t-0 sm:border-l border-slate-200 pt-1.5 sm:pt-0 sm:pl-2">
                            <select name="kategori" class="text-xs text-slate-600 bg-transparent px-2 py-2 focus:outline-hidden cursor-pointer">
                                <option value="">Semua Kategori</option>
                                <option value="Umum">Umum & Pelajaran</option>
                                <option value="Motivasi">Motivasi Diri</option>
                                <option value="Sastra">Sastra & Novel</option>
                                <option value="Religi">Religi & Fikih</option>
                            </select>

                            <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-700 text-white text-xs font-semibold hover:bg-blue-800 transition shrink-0">
                                Cari
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Clean Search Keywords (No Bloated Pills) -->
                <div class="mt-4 flex items-center gap-x-2 gap-y-1.5 flex-wrap text-xs text-slate-500">
                    <span class="font-medium text-slate-700">Pencarian populer:</span>
                    <a href="{{ route('catalog.index', ['q' => 'Informatika']) }}" class="hover:text-blue-700 hover:underline">Informatika</a>
                    <span>•</span>
                    <a href="{{ route('catalog.index', ['q' => 'Kendaraan Ringan']) }}" class="hover:text-blue-700 hover:underline">Otomotif</a>
                    <span>•</span>
                    <a href="{{ route('catalog.index', ['q' => 'Bisnis Digital']) }}" class="hover:text-blue-700 hover:underline">Bisnis Digital</a>
                    <span>•</span>
                    <a href="{{ route('catalog.index', ['q' => 'Sastra']) }}" class="hover:text-blue-700 hover:underline">Sastra</a>
                    <span>•</span>
                    <a href="{{ route('catalog.index', ['q' => 'Pancasila']) }}" class="hover:text-blue-700 hover:underline">Pancasila</a>
                </div>
            </div>

            <!-- Right Column: Editorial Book Spotlight (Ala Malaka Books) -->
            <div class="lg:col-span-5">
                @if($spotlightBook)
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-sm">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">
                        Sorotan Pustakawan
                    </div>

                    <div class="flex gap-5 items-start">
                        <!-- Book Cover Physical Style -->
                        <div class="w-28 sm:w-32 shrink-0 aspect-3/4 rounded-lg bg-slate-800 text-white p-3 flex flex-col justify-between shadow-md border-l-4 border-slate-600 relative overflow-hidden">
                            @if($spotlightBook->cover_url)
                                <img src="{{ $spotlightBook->cover_url }}" alt="{{ $spotlightBook->judul }}" class="absolute inset-0 w-full h-full object-cover">
                            @else
                                <div class="text-[9px] font-semibold text-slate-300 uppercase tracking-wider">
                                    {{ $spotlightBook->kategori }}
                                </div>
                                <div class="font-bold text-xs leading-snug line-clamp-3 text-slate-100">
                                    {{ $spotlightBook->judul }}
                                </div>
                                <div class="text-[9px] text-slate-400 line-clamp-1">
                                    {{ $spotlightBook->penerbit ?? 'Kemdikbud' }}
                                </div>
                            @endif
                        </div>

                        <!-- Book Editorial Content -->
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-semibold text-blue-700">
                                {{ $spotlightBook->kategori }}
                            </span>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 leading-snug mt-0.5 line-clamp-2">
                                <a href="{{ route('catalog.show', $spotlightBook) }}" class="hover:text-blue-700 transition">
                                    {{ $spotlightBook->judul }}
                                </a>
                            </h2>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-1">
                                Penerbit: {{ $spotlightBook->penerbit ?? 'Pustaka Sekolah' }} • {{ $spotlightBook->tahun_cetak ?? '2025' }}
                            </p>

                            <p class="text-xs text-slate-600 mt-3 line-clamp-3 leading-relaxed">
                                {{ $spotlightBook->deskripsi ?? 'Buku bacaan dan bahan referensi pilihan yang direkomendasikan untuk menunjang wawasan akademik serta keahlian praktik siswa di sekolah.' }}
                            </p>

                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-xs {{ $spotlightBook->stok > 0 ? 'text-emerald-700 font-medium' : 'text-slate-400' }}">
                                    {{ $spotlightBook->stok > 0 ? 'Tersedia ' . $spotlightBook->stok . ' eks' : 'Stok habis' }}
                                </span>
                                <a href="{{ route('catalog.show', $spotlightBook) }}" class="text-xs font-semibold text-blue-700 hover:text-blue-900 transition">
                                    Buka Detail →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>
</section>

<!-- Direct Service Portal (Modul Layanan Cepat ala Perpustakaan UT) -->
<section class="py-12 bg-white border-b border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Portal Layanan</p>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mt-0.5">Layanan Mandiri Perpustakaan</h2>
            </div>
            <p class="text-xs text-slate-500">Akses cepat seluruh fasilitas sirkulasi dan literasi sekolah</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Card 1: OPAC -->
            <a href="{{ route('catalog.index') }}" class="group p-5 rounded-xl border border-slate-200 hover:border-blue-300 hover:shadow-xs transition bg-white flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-blue-700 transition">Katalog Pustaka (OPAC)</h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Cari seluruh koleksi buku cetak, buku teks pelajaran BOS, dan materi pengayaan yang tersedia di rak.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-blue-700 flex items-center justify-between">
                    <span>Buka Katalog</span>
                    <span>→</span>
                </div>
            </a>

            <!-- Card 2: Peminjaman Mandiri -->
            <a href="{{ route('catalog.index') }}" class="group p-5 rounded-xl border border-slate-200 hover:border-blue-300 hover:shadow-xs transition bg-white flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-blue-700 transition">Peminjaman Buku</h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Ajukan peminjaman buku pilihan Anda sebelum mengambil fisik buku di meja sirkulasi perpustakaan.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-blue-700 flex items-center justify-between">
                    <span>Ajukan Pinjam</span>
                    <span>→</span>
                </div>
            </a>

            <!-- Card 3: Status Pinjaman Saya -->
            <a href="{{ route('member.loans') }}" class="group p-5 rounded-xl border border-slate-200 hover:border-blue-300 hover:shadow-xs transition bg-white flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-blue-700 transition">Pinjaman Aktif</h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Pantau tanggal batas kembali 7 hari, riwayat sirkulasi, dan pastikan buku kembali tepat waktu.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-blue-700 flex items-center justify-between">
                    <span>Cek Pinjaman</span>
                    <span>→</span>
                </div>
            </a>

            <!-- Card 4: Usul Pengadaan Koleksi -->
            <a href="{{ route('member.suggestions') }}" class="group p-5 rounded-xl border border-slate-200 hover:border-blue-300 hover:shadow-xs transition bg-white flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-blue-700 transition">Usulan Buku Baru</h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Sampaikan usulan judul buku atau literatur kejuruan baru untuk diajukan dalam pengadaan sekolah.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-blue-700 flex items-center justify-between">
                    <span>Ajukan Usul</span>
                    <span>→</span>
                </div>
            </a>

        </div>
    </div>
</section>

<!-- Collection Stats Bar: Minimalist & Clean (No Candy Icons) -->
<section class="py-8 bg-slate-50/80 border-b border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center divide-y md:divide-y-0 md:divide-x divide-slate-200">
            <div class="pt-3 md:pt-0">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $totalTitles }}</div>
                <div class="text-xs font-medium uppercase tracking-wider text-slate-500 mt-1">Judul Koleksi</div>
            </div>
            <div class="pt-3 md:pt-0">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ number_format($totalStock, 0, ',', '.') }}</div>
                <div class="text-xs font-medium uppercase tracking-wider text-slate-500 mt-1">Total Eksemplar</div>
            </div>
            <div class="pt-3 md:pt-0">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $totalMembers }}</div>
                <div class="text-xs font-medium uppercase tracking-wider text-slate-500 mt-1">Anggota Terdaftar</div>
            </div>
            <div class="pt-3 md:pt-0">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $activeLoans }}</div>
                <div class="text-xs font-medium uppercase tracking-wider text-slate-500 mt-1">Pinjaman Berjalan</div>
            </div>
        </div>
    </div>
</section>

<!-- Curated Collection: Featured Books (Editorial Grid ala Malaka Books) -->
<section class="py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-2">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Koleksi Pilihan</p>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mt-0.5">Buku Terbaru & Siap Pinjam</h2>
        </div>
        <a href="{{ route('catalog.index') }}" class="text-xs font-semibold text-blue-700 hover:text-blue-900 transition">
            Lihat Seluruh Koleksi ({{ $totalTitles }}) →
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
        @foreach($featuredBooks as $b)
        <div class="group bg-white rounded-xl border border-slate-200 hover:border-slate-300 hover:shadow-xs transition flex flex-col justify-between overflow-hidden">
            <!-- Book Cover -->
            <a href="{{ route('catalog.show', $b) }}" class="block aspect-3/4 bg-slate-800 text-white p-4 relative overflow-hidden border-l-4 border-slate-700">
                @if($b->cover_url)
                    <img src="{{ $b->cover_url }}" alt="{{ $b->judul }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-102 transition duration-300">
                @else
                    <div class="h-full flex flex-col justify-between">
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                            {{ $b->kategori }}
                        </div>
                        <div>
                            <div class="font-bold text-sm text-slate-100 leading-snug line-clamp-3">
                                {{ $b->judul }}
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1 line-clamp-1">
                                {{ $b->penerbit ?? 'Pustaka Sekolah' }}
                            </div>
                        </div>
                        <div class="text-[10px] text-slate-500 font-mono">
                            {{ $b->no_bos ? 'BOS #' . $b->no_bos : 'Koleksi Umum' }}
                        </div>
                    </div>
                @endif
            </a>

            <!-- Book Info -->
            <div class="p-3.5 flex-1 flex flex-col justify-between">
                <div>
                    <div class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">
                        {{ $b->kategori }}
                    </div>
                    <h3 class="font-bold text-xs sm:text-sm text-slate-900 line-clamp-2 mt-0.5 group-hover:text-blue-700 transition" title="{{ $b->judul }}">
                        <a href="{{ route('catalog.show', $b) }}">{{ $b->judul }}</a>
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-1 line-clamp-1">
                        {{ $b->penerbit ?? 'Kemdikbud' }}
                    </p>
                </div>

                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="{{ $b->stok > 0 ? 'text-emerald-700 font-medium' : 'text-slate-400' }}">
                        {{ $b->stok > 0 ? 'Stok: ' . $b->stok : 'Habis' }}
                    </span>
                    <a href="{{ route('catalog.show', $b) }}" class="font-semibold text-blue-700 hover:text-blue-900 transition">
                        Detail →
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>

<!-- Vocational Learning Section (Koleksi Buku Kejuruan BOS) -->
@if(count($vocationalBooks) > 0)
<section class="py-14 bg-slate-50/70 border-y border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kurikulum Merdeka & Kejuruan</p>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mt-0.5">Buku Teks Keahlian & Praktik</h2>
            </div>
            <a href="{{ route('catalog.index', ['q' => 'BOS']) }}" class="text-xs font-semibold text-blue-700 hover:text-blue-900 transition">
                Lihat Koleksi Kejuruan →
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($vocationalBooks as $vb)
            <div class="bg-white rounded-xl border border-slate-200 p-3 hover:border-slate-300 hover:shadow-xs transition flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-mono text-slate-400 block mb-1">
                        #{{ $vb->no_bos }}
                    </span>
                    <h3 class="font-bold text-xs text-slate-900 line-clamp-2 leading-snug" title="{{ $vb->judul }}">
                        <a href="{{ route('catalog.show', $vb) }}" class="hover:text-blue-700 transition">{{ $vb->judul }}</a>
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-1 line-clamp-1">
                        {{ $vb->penerbit }}
                    </p>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="text-slate-600 font-medium">{{ $vb->stok }} eks</span>
                    <a href="{{ route('catalog.show', $vb) }}" class="text-blue-700 font-semibold hover:underline">Pinjam</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Literary Culture & Quotes (Pojok Literasi SMKN 1 Depok ala Malaka Books) -->
<section class="py-14 bg-white border-b border-slate-200/80">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Budaya Literasi Sekolah</p>
        <blockquote class="text-lg sm:text-xl font-medium text-slate-800 leading-relaxed italic">
            "Membaca adalah gerbang menuju pengetahuan luas, dan berpikir kritis adalah lentera untuk melangkah di masa depan."
        </blockquote>
        <div class="mt-4 text-xs font-semibold text-slate-900">
            Perpustakaan SMK Negeri 1 Depok
        </div>

        <div class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-6 text-left border-t border-slate-100 pt-8">
            <div>
                <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider">Akses Terbuka</h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Setiap siswa dan guru memiliki hak yang sama untuk meminjam koleksi bacaan dan referensi sekolah.
                </p>
            </div>
            <div>
                <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider">Disiplin Waktu</h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Masa pinjam 7 hari kalender dengan pelaporan tepat waktu agar perputaran buku tetap terjaga lancar.
                </p>
            </div>
            <div>
                <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider">Tanggung Jawab Bersama</h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Merawat keutuhan fisik buku dan menjaga kenyamanan area taman baca perpustakaan.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Real Facilities Gallery Section (No AI-Slop Fake Tags) -->
<section id="fasilitas" class="py-14 bg-slate-50/60 border-b border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mb-10">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Lingkungan Belajar</p>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mt-0.5">
                Fasilitas Nyata Perpustakaan SMKN 1 Depok
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Ruang baca indoor yang tenang dan area taman baca terbuka untuk mendukung kegiatan literasi siswa.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($facilities as $f)
            <div class="bg-white rounded-xl overflow-hidden border border-slate-200 hover:border-slate-300 transition flex flex-col justify-between">
                <div class="h-48 overflow-hidden bg-slate-100">
                    <img src="{{ $f['image'] }}" alt="{{ $f['title'] }}" class="w-full h-full object-cover">
                </div>
                <div class="p-4 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">{{ $f['title'] }}</h3>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">{{ $f['desc'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Operational Rules & Official Poster Section -->
<section id="tata-tertib" class="py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Rules Text Column -->
        <div class="lg:col-span-7">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Panduan Sirkulasi</p>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mt-0.5">
                Tata Tertib & Ketentuan Peminjaman
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Demi ketertiban dan kelancaran sirkulasi buku, seluruh anggota wajib mematuhi aturan berikut:
            </p>

            <div class="mt-6 space-y-3">
                <div class="p-4 rounded-xl border border-slate-200 bg-white">
                    <div class="text-xs font-bold text-slate-900">1. Durasi Maksimal Peminjaman</div>
                    <p class="text-xs text-slate-500 mt-1">
                        Peminjaman buku maksimal berlangsung selama <strong>7 (tujuh) hari kalender</strong> sejak disetujui petugas perpustakaan.
                    </p>
                </div>

                <div class="p-4 rounded-xl border border-slate-200 bg-white">
                    <div class="text-xs font-bold text-slate-900">2. Denda Keterlambatan Pengembalian</div>
                    <p class="text-xs text-slate-500 mt-1">
                        Keterlambatan pengembalian buku dikenakan sanksi denda sebesar <strong>Rp 1.000,- / hari</strong> per buku.
                    </p>
                </div>

                <div class="p-4 rounded-xl border border-slate-200 bg-white">
                    <div class="text-xs font-bold text-slate-900">3. Pemeliharaan Integritas Fisik Buku</div>
                    <p class="text-xs text-slate-500 mt-1">
                        Dilarang melipat, mencorat-coret, atau merusak halaman buku. Kerusakan ringan dikenakan biaya ganti Rp 10.000, rusak berat Rp 50.000.
                    </p>
                </div>

                <div class="p-4 rounded-xl border border-slate-200 bg-white">
                    <div class="text-xs font-bold text-slate-900">4. Menjaga Ketenangan Ruangan</div>
                    <p class="text-xs text-slate-500 mt-1">
                        Tidak membawa makanan ke area rak buku dan meletakkan tas di tempat penyimpanan yang disediakan saat berada di dalam ruangan.
                    </p>
                </div>
            </div>
        </div>

        <!-- Poster & Hours Column -->
        <div class="lg:col-span-5 space-y-4">
            <!-- Official Poster -->
            <div class="bg-white p-3 rounded-xl border border-slate-200">
                <img src="{{ asset('images/tata-tertib.jpg') }}" alt="Tata Tertib Perpustakaan SMKN 1 Depok" class="rounded-lg w-full h-auto object-cover max-h-[380px]">
                <div class="mt-2 text-center text-[11px] text-slate-500 font-medium">
                    Dokumen Resmi Tata Tertib Perpustakaan SMKN 1 Depok
                </div>
            </div>

            <!-- Operational Hours Card -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 text-xs">
                <div class="font-bold text-slate-900 uppercase tracking-wider text-[11px] mb-2">
                    Jadwal Jam Layanan Sirkulasi
                </div>
                <div class="space-y-1.5 text-slate-600">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span>Senin – Kamis</span>
                        <span class="font-semibold text-slate-900">07.00 – 14.00 WIB</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span>Jumat</span>
                        <span class="font-semibold text-slate-900">07.00 – 11.00 WIB</span>
                    </div>
                    <div class="flex justify-between py-1 text-rose-600">
                        <span>Sabtu – Minggu & Hari Libur</span>
                        <span class="font-medium">Tutup / Libur</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Book Suggestion Banner (Clean, Minimalist Text & Button) -->
<section class="py-10 bg-slate-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h2 class="text-lg sm:text-xl font-bold tracking-tight">Punya Usulan Judul Buku Baru untuk Sekolah?</h2>
            <p class="text-slate-400 text-xs sm:text-sm mt-1 max-w-xl">
                Ajukan saran bacaan Anda agar dapat dipertimbangkan dalam pengadaan koleksi perpustakaan SMKN 1 Depok.
            </p>
        </div>
        <a href="{{ route('member.suggestions') }}" class="px-5 py-2.5 rounded-lg bg-white text-slate-900 text-xs font-semibold hover:bg-slate-100 transition shrink-0">
            Kirim Usulan Buku
        </a>
    </div>
</section>
@endsection
