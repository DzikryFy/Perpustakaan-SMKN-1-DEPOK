@extends('layouts.app')

@section('title', 'Koleksi Buku | Perpustakaan SMKN 1 Depok')

@section('content')
<div class="py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Page Header -->
    <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Katalog Pustaka</p>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-0.5">Eksplorasi Koleksi Buku</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Cari dan pinjam buku referensi kurikulum, materi kejuruan, serta literatur umum SMKN 1 Depok</p>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200 mb-8">
        <form method="GET" action="{{ route('catalog.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            
            <!-- Search Text -->
            <div class="md:col-span-6 relative">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari judul, pengarang, penerbit, nomor BOS..."
                    class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-slate-300 bg-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent transition">
            </div>

            <!-- Category Select -->
            <div class="md:col-span-3">
                <select name="kategori" class="w-full px-3 py-2.5 text-sm rounded-lg border border-slate-300 bg-white text-slate-700 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent transition">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Buttons & Reset -->
            <div class="md:col-span-3 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 rounded-lg bg-blue-700 text-white text-xs font-semibold hover:bg-blue-800 transition">
                    Terapkan
                </button>
                @if($search || $category || $availableOnly)
                <a href="{{ route('catalog.index') }}" class="py-2.5 px-3.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
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

    <!-- Category Tabs (Clean Text Chips) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-6 text-xs font-medium">
        <a href="{{ route('catalog.index', array_merge(request()->except('kategori', 'page'))) }}"
           class="px-3.5 py-1.5 rounded-lg border transition whitespace-nowrap {{ !$category ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300' }}">
            Semua ({{ \App\Models\Book::count() }})
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('catalog.index', array_merge(request()->except('kategori', 'page'), ['kategori' => $cat])) }}"
           class="px-3.5 py-1.5 rounded-lg border transition whitespace-nowrap {{ $category === $cat ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300' }}">
            {{ $cat }} ({{ $categoryCounts[$cat] ?? 0 }})
        </a>
        @endforeach
    </div>

    <!-- Books Grid (Editorial Physical Style ala Malaka Books) -->
    @if($books->count() > 0)
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
        @foreach($books as $b)
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

            <!-- Book Info Body -->
            <div class="p-3.5 flex-1 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1">
                        <span>{{ $b->kategori }}</span>
                        @if($b->no_bos)
                        <span class="font-mono text-slate-500">#{{ $b->no_bos }}</span>
                        @endif
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

    <!-- Pagination -->
    <div class="mt-10">
        {{ $books->links() }}
    </div>
    @else
    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center max-w-md mx-auto">
        <h3 class="font-bold text-slate-800 text-base">Tidak ada koleksi buku yang cocok</h3>
        <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci pencarian lain atau pilih kategori yang berbeda.</p>
        <a href="{{ route('catalog.index') }}" class="mt-4 inline-block px-4 py-2 rounded-lg bg-blue-700 text-white text-xs font-semibold hover:bg-blue-800 transition">
            Lihat Semua Koleksi
        </a>
    </div>
    @endif
</div>
@endsection
