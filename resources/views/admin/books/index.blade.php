@extends('layouts.admin')

@section('title', 'Katalog Buku | Panel Kelola')
@section('page_title', 'Manajemen Katalog Buku')
@section('page_subtitle', 'Kelola koleksi bahan pustaka, buku cetak BOS, dan ketersediaan stok fisik')

@section('content')
<div class="space-y-6">
    
    <!-- Top Action & Search Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Search & Filter Form -->
        <form method="GET" action="{{ route('admin.books.index') }}" class="flex-1 flex flex-wrap items-center gap-2">
            <div class="relative flex-1 min-w-[200px]">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari judul, pengarang, no. BOS..."
                    class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600">
                <i class="bi bi-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
            </div>

            <select name="kategori" class="px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 transition">
                Filter
            </button>
            @if($search || $category)
            <a href="{{ route('admin.books.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800">
                Reset
            </a>
            @endif
        </form>

        @if(Auth::user()->isPustakawan())
        <!-- Add Book Button -->
        <a href="{{ route('admin.books.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-xs transition inline-flex items-center gap-1.5 self-start md:self-auto shrink-0">
            <i class="bi bi-plus-lg"></i> Tambah Buku Baru
        </a>
        @endif
    </div>

    <!-- Books Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Daftar Bahan Pustaka ({{ $books->total() }} Total Judul)</h3>
            <span class="text-xs text-slate-500">Hal. {{ $books->currentPage() }} dari {{ $books->lastPage() }}</span>
        </div>

        @if($books->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 w-12">Cover</th>
                        <th class="px-5 py-3">Judul Buku & Kategori</th>
                        <th class="px-5 py-3">No. BOS</th>
                        <th class="px-5 py-3">Penerbit & Tahun</th>
                        <th class="px-5 py-3">Sisa Stok</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($books as $b)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-5 py-3">
                            <div class="w-10 h-14 rounded-md bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center text-lg shrink-0">
                                @if($b->cover_url)
                                    <img src="{{ $b->cover_url }}" alt="" class="w-full h-full object-cover">
                                @else
                                    📖
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="font-bold text-slate-900 leading-snug">{{ $b->judul }}</div>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $b->kategori }}
                                </span>
                                <span class="text-[10px] text-slate-400">&bull; {{ $b->jenis_barang ?? 'Koleksi Umum' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 font-mono text-slate-600">
                            {{ $b->no_bos ? '#' . $b->no_bos : '-' }}
                        </td>
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-800">{{ $b->penerbit ?? '-' }}</div>
                            <span class="text-[10px] text-slate-400">Tahun: {{ $b->tahun_cetak ?? '-' }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $b->stok > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ $b->stok }} eksemplar
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('catalog.show', $b) }}" target="_blank" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-slate-100 transition" title="Lihat di Web">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @if(Auth::user()->isPustakawan())
                                <a href="{{ route('admin.books.edit', $b) }}" class="p-1.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-slate-100 transition" title="Edit Buku">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <form action="{{ route('admin.books.destroy', $b) }}" method="POST" onsubmit="return confirm('Hapus buku ini dari katalog?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Buku">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $books->links() }}
        </div>
        @else
        <div class="p-10 text-center text-slate-400 text-xs">
            Tidak ada buku ditemukan sesuai kriteria filter.
        </div>
        @endif
    </div>
</div>
@endsection
