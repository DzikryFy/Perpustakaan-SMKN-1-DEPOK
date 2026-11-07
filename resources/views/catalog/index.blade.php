@extends('layouts.app')

@section('title', 'Koleksi Buku | Perpustakaan SMKN 1 Depok')

@section('content')
<div class="py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Page Header -->
    <div class="mb-8">
        <div class="text-xs font-bold uppercase tracking-wider text-blue-600">Katalog Pustaka</div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Eksplorasi Koleksi Buku</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Cari dan pinjam buku referensi, materi kejuruan, serta literatur umum SMKN 1 Depok</p>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs mb-8">
        <form method="GET" action="{{ route('catalog.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            
            <!-- Search Text -->
            <div class="md:col-span-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="bi bi-search"></i>
                </div>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari judul, pengarang, penerbit, nomor BOS..."
                    class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent transition">
            </div>

            <!-- Category Select -->
            <div class="md:col-span-3">
                <select name="kategori" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent transition">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Buttons & Available Only -->
            <div class="md:col-span-3 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-xs transition">
                    Terapkan
                </button>
                @if($search || $category || $availableOnly)
                <a href="{{ route('catalog.index') }}" class="py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Reset
                </a>
                @endif
            </div>

            <!-- Available Only Checkbox -->
            <div class="md:col-span-12 flex items-center pt-2 border-t border-slate-100">
                <label class="flex items-center text-xs text-slate-600 cursor-pointer">
                    <input type="checkbox" name="tersedia" value="1" {{ $availableOnly ? 'checked' : '' }} onchange="this.form.submit()" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded">
                    <span class="ml-2 font-medium">Hanya tampilkan buku yang sedang tersedia (stok &gt; 0)</span>
                </label>
            </div>
        </form>
    </div>

    <!-- Category Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-6 text-xs font-semibold">
        <a href="{{ route('catalog.index', array_merge(request()->except('kategori', 'page'))) }}"
           class="px-3.5 py-1.5 rounded-full border transition whitespace-nowrap {{ !$category ? 'bg-blue-600 text-white border-blue-600 shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:border-blue-300' }}">
            Semua ({{ \App\Models\Book::count() }})
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('catalog.index', array_merge(request()->except('kategori', 'page'), ['kategori' => $cat])) }}"
           class="px-3.5 py-1.5 rounded-full border transition whitespace-nowrap {{ $category === $cat ? 'bg-blue-600 text-white border-blue-600 shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:border-blue-300' }}">
            {{ $cat }} ({{ $categoryCounts[$cat] ?? 0 }})
        </a>
        @endforeach
    </div>

    <!-- Books Grid -->
    @if($books->count() > 0)
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
        @foreach($books as $b)
        <div class="group bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-2xs hover:shadow-md hover:border-blue-200 transition-all flex flex-col">
            
            <!-- Book Cover -->
            <div class="h-48 bg-gradient-to-br from-slate-100 to-slate-200 relative flex items-center justify-center overflow-hidden p-4">
                @if($b->cover_url)
                    <img src="{{ $b->cover_url }}" alt="{{ $b->judul }}" class="h-full w-auto object-cover rounded-md shadow-md group-hover:scale-105 transition-transform duration-300">
                @else
                    <div class="text-center p-3">
                        <div class="text-4xl mb-1 opacity-70">📖</div>
                        <div class="text-xs font-bold text-slate-800 line-clamp-2">{{ $b->judul }}</div>
                        <div class="text-[10px] text-slate-500 mt-1">{{ $b->penerbit ?? 'Kemdikbud' }}</div>
                    </div>
                @endif
                
                <!-- Stock Pill -->
                <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full text-[10px] font-bold shadow-2xs {{ $b->stok > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                    {{ $b->stok > 0 ? 'Tersedia (' . $b->stok . ')' : 'Stok Habis' }}
                </span>
            </div>

            <!-- Book Info Body -->
            <div class="p-4 flex-1 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-blue-600 mb-1">
                        <span>{{ $b->kategori }}</span>
                        @if($b->no_bos)
                        <span class="text-slate-400 font-mono">BOS: #{{ $b->no_bos }}</span>
                        @endif
                    </div>
                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-blue-600 line-clamp-2 transition-colors">
                        <a href="{{ route('catalog.show', $b) }}">{{ $b->judul }}</a>
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 line-clamp-1">
                        Penerbit: {{ $b->penerbit ?? 'Kemdikbud/Pemerintah' }}
                    </p>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">Tahun: {{ $b->tahun_cetak ?? '-' }}</span>
                    <a href="{{ route('catalog.show', $b) }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-800">
                        Detail <i class="bi bi-chevron-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-10">
        {{ $books->links() }}
    </div>
    @else
    <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center max-w-md mx-auto">
        <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-2xl mb-4">
            <i class="bi bi-book"></i>
        </div>
        <h3 class="font-bold text-slate-800 text-base">Tidak ada buku ditemukan</h3>
        <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci pencarian lain atau pilih kategori yang berbeda.</p>
        <a href="{{ route('catalog.index') }}" class="mt-4 inline-block px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
            Lihat Semua Koleksi
        </a>
    </div>
    @endif
</div>
@endsection
