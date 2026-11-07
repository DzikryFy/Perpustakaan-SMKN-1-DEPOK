@extends('layouts.app')

@section('title', 'Perpustakaan Digital SMKN 1 Depok | Literasi & Kejuruan')

@section('content')
<!-- Hero Section -->
<section class="relative bg-gradient-to-b from-blue-50/70 via-white to-slate-50 pt-12 pb-20 border-b border-slate-200/60 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto">
            
            <!-- School Pill -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-100/70 border border-blue-200 text-blue-800 text-xs font-semibold mb-6 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                E-Library SMK Negeri 1 Depok (OneDek)
            </div>

            <!-- Title -->
            <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                Pusat Literasi & Pengetahuan <br class="hidden sm:inline">
                <span class="text-blue-600">SMKN 1 Depok</span>
            </h1>

            <p class="mt-4 text-sm sm:text-base text-slate-600 leading-relaxed max-w-2xl mx-auto">
                Layanan perpustakaan digital resmi untuk siswa dan guru. Cari katalog buku BOS, pinjam buku secara terstruktur, dan nikmati fasilitas literasi yang nyaman.
            </p>

            <!-- Search Bar Form -->
            <form action="{{ route('catalog.index') }}" method="GET" class="mt-8 max-w-xl mx-auto">
                <div class="flex items-center p-1.5 rounded-2xl bg-white border border-slate-200/90 shadow-sm focus-within:ring-2 focus-within:ring-blue-600 focus-within:border-transparent transition-all">
                    <div class="pl-3.5 text-slate-400">
                        <i class="bi bi-search text-base"></i>
                    </div>
                    <input type="text" name="q" placeholder="Cari judul buku, pengarang, nomor BOS..."
                        class="w-full px-3 py-2 text-sm text-slate-800 bg-transparent placeholder-slate-400 focus:outline-hidden">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-xs transition-colors shrink-0">
                        Cari Buku
                    </button>
                </div>
            </form>

            <!-- Quick Category Badges -->
            <div class="mt-5 flex items-center justify-center gap-2 flex-wrap text-xs text-slate-500">
                <span class="font-semibold text-slate-700">Kategori Cepat:</span>
                <a href="{{ route('catalog.index', ['kategori' => 'Umum']) }}" class="px-3 py-1 rounded-full bg-white border border-slate-200 text-slate-700 hover:border-blue-300 hover:text-blue-600 transition">Umum & Pelajaran</a>
                <a href="{{ route('catalog.index', ['kategori' => 'Motivasi']) }}" class="px-3 py-1 rounded-full bg-white border border-slate-200 text-slate-700 hover:border-blue-300 hover:text-blue-600 transition">Motivasi Diri</a>
                <a href="{{ route('catalog.index', ['kategori' => 'Sastra']) }}" class="px-3 py-1 rounded-full bg-white border border-slate-200 text-slate-700 hover:border-blue-300 hover:text-blue-600 transition">Sastra & Novel</a>
                <a href="{{ route('catalog.index', ['kategori' => 'Religi']) }}" class="px-3 py-1 rounded-full bg-white border border-slate-200 text-slate-700 hover:border-blue-300 hover:text-blue-600 transition">Religi & Fikih</a>
            </div>
        </div>

        <!-- Metric Counter Grid -->
        <div class="mt-14 grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs text-center">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3 text-lg font-bold">
                    <i class="bi bi-book"></i>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight">{{ $totalTitles }}</div>
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider mt-1">Judul Buku</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs text-center">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3 text-lg font-bold">
                    <i class="bi bi-stack"></i>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight">{{ number_format($totalStock, 0, ',', '.') }}</div>
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider mt-1">Total Eksemplar</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs text-center">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mx-auto mb-3 text-lg font-bold">
                    <i class="bi bi-people"></i>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight">{{ $totalMembers }}</div>
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider mt-1">Anggota Aktif</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs text-center">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-3 text-lg font-bold">
                    <i class="bi bi-journal-arrow-up"></i>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight">{{ $activeLoans }}</div>
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider mt-1">Pinjaman Berjalan</div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Books Section -->
<section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-blue-600">Koleksi Terpilih</div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">Buku Terbaru & Populer</h2>
            <p class="text-xs text-slate-500 mt-1">Koleksi buku siap pinjam di meja sirkulasi perpustakaan</p>
        </div>
        <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 transition">
            Lihat Semua Koleksi ({{ $totalTitles }}) <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
        @foreach($featuredBooks as $b)
        <div class="group bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-2xs hover:shadow-md hover:border-blue-200 transition-all flex flex-col">
            <!-- Book Cover -->
            <div class="h-44 bg-gradient-to-br from-slate-100 to-slate-200 relative flex items-center justify-center overflow-hidden p-4">
                @if($b->cover_url)
                    <img src="{{ $b->cover_url }}" alt="{{ $b->judul }}" class="h-full w-auto object-cover rounded-md shadow-md group-hover:scale-105 transition-transform duration-300">
                @else
                    <div class="text-center p-3">
                        <div class="text-3xl mb-1 opacity-70">📖</div>
                        <div class="text-[11px] font-bold text-slate-700 line-clamp-2">{{ $b->judul }}</div>
                        <div class="text-[10px] text-slate-500 mt-1">{{ $b->penerbit ?? 'Kemdikbud' }}</div>
                    </div>
                @endif
                
                <!-- Stock Pill -->
                <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full text-[10px] font-bold shadow-2xs {{ $b->stok > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                    {{ $b->stok > 0 ? 'Tersedia (' . $b->stok . ')' : 'Habis' }}
                </span>
            </div>

            <!-- Book Info -->
            <div class="p-4 flex-1 flex flex-col justify-between">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-blue-600 mb-1">
                        {{ $b->kategori }}
                    </div>
                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-blue-600 line-clamp-2 transition-colors" title="{{ $b->judul }}">
                        <a href="{{ route('catalog.show', $b) }}">{{ $b->judul }}</a>
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 line-clamp-1">
                        {{ $b->penerbit ?? 'Kemdikbud/Pemerintah' }}
                    </p>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-500">Tahun: {{ $b->tahun_cetak ?? '-' }}</span>
                    <a href="{{ route('catalog.show', $b) }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-800">
                        Detail <i class="bi bi-chevron-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>

<!-- Facilities Photo Gallery Section (Using Real School Photos) -->
<section id="fasilitas" class="py-16 bg-white border-y border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <div class="text-xs font-bold uppercase tracking-wider text-blue-600">Fasilitas Resmi</div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                Kenyamanan Membaca di SMKN 1 Depok
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-2">
                Fasilitas ruang perpustakaan dan taman baca outdoor yang asri untuk mendukung gerakan literasi sekolah.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($facilities as $f)
            <div class="group rounded-2xl overflow-hidden border border-slate-200/80 shadow-2xs hover:shadow-md transition-all flex flex-col bg-slate-50/50">
                <div class="h-52 overflow-hidden relative">
                    <img src="{{ $f['image'] }}" alt="{{ $f['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/90 backdrop-blur-xs text-slate-800 shadow-xs border border-white">
                        {{ $f['tag'] }}
                    </span>
                </div>
                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">{{ $f['title'] }}</h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">{{ $f['desc'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Tata Tertib & Rules Section -->
<section id="tata-tertib" class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        
        <!-- Text & Rules Content -->
        <div class="lg:col-span-7 space-y-6">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-blue-600">Peraturan Perpustakaan</div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                    Tata Tertib & Prosedur Sirkulasi
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed">
                    Untuk menjaga kenyamanan bersama dan keutuhan koleksi pustaka, seluruh pengunjung diharapkan mematuhi panduan berikut:
                </p>
            </div>

            <div class="space-y-3.5">
                <div class="flex items-start gap-3.5 p-4 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 font-bold flex items-center justify-center shrink-0">1</div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Batas Waktu Peminjaman</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Maksimal peminjaman buku adalah <strong>7 (tujuh) hari kalender</strong> terhitung sejak disetujui pustakawan.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-4 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 font-bold flex items-center justify-center shrink-0">2</div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Denda Keterlambatan Pengembalian</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Keterlambatan dikenakan denda sebesar <strong>Rp 1.000,- / hari</strong> per buku untuk mendisiplinkan pengembalian.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-4 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 font-bold flex items-center justify-center shrink-0">3</div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Pemeliharaan & Integritas Fisik Buku</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Dilarang melipat, mencoret, atau merobek halaman buku. Kerusakan ringan dikenakan biaya ganti Rp 10.000, rusak berat Rp 50.000.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-4 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 font-bold flex items-center justify-center shrink-0">4</div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Ketertiban Ruangan Perpustakaan</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Menjaga ketenangan, tidak membawa makanan/minuman berbau ke area rak, dan meletakkan tas di loker yang disediakan.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Poster Image Preview -->
        <div class="lg:col-span-5">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-md">
                <img src="{{ asset('images/tata-tertib.jpg') }}" alt="Poster Tata Tertib Perpustakaan SMKN 1 Depok" class="rounded-xl w-full h-auto object-cover max-h-[500px]">
                <div class="mt-3 text-center text-xs text-slate-500 font-medium">
                    Dokumen Resmi Tata Tertib Perpustakaan SMKN 1 Depok
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Book Suggestion CTA Banner -->
<section class="py-12 bg-blue-700 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <h2 class="text-2xl font-bold tracking-tight">Punya Rekomendasi Buku Baru untuk Sekolah?</h2>
            <p class="text-blue-100 text-xs sm:text-sm mt-1 max-w-xl">
                Ajukan usulan buku favoritmu agar dapat dipertimbangkan dalam pengadaan koleksi perpustakaan berikutnya oleh Kepala Perpustakaan.
            </p>
        </div>
        <a href="{{ route('member.suggestions') }}" class="px-6 py-3 rounded-xl bg-white text-blue-800 text-xs font-bold hover:bg-blue-50 shadow-md transition-colors shrink-0">
            <i class="bi bi-lightbulb me-1.5 text-amber-500"></i> Ajukan Usulan Buku
        </a>
    </div>
</section>
@endsection
