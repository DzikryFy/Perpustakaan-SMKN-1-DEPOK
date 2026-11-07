@extends('layouts.admin')

@section('title', 'Edit Buku | Panel Kelola')
@section('page_title', 'Perbarui Data Buku')
@section('page_subtitle', 'Edit informasi ' . $book->judul)

@section('content')
<div class="max-w-3xl">
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-2xs">
        
        <form action="{{ route('admin.books.update', $book) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Judul Buku -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Judul Buku <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="judul" required value="{{ old('judul', $book->judul) }}"
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('judul')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Kategori -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Kategori Buku <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="kategori" required value="{{ old('kategori', $book->kategori) }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    @error('kategori')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No BOS -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nomor BOS / Inventaris
                    </label>
                    <input type="text" name="no_bos" value="{{ old('no_bos', $book->no_bos) }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Stok -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Jumlah Stok (Eks) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="stok" required min="0" value="{{ old('stok', $book->stok) }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    @error('stok')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tahun Cetak -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Tahun Terbit
                    </label>
                    <input type="number" name="tahun_cetak" min="1900" max="{{ date('Y') + 1 }}" value="{{ old('tahun_cetak', $book->tahun_cetak) }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>

                <!-- Asal Perolehan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Asal Perolehan
                    </label>
                    <input type="text" name="asal_perolehan" value="{{ old('asal_perolehan', $book->asal_perolehan) }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Penerbit -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Penerbit
                    </label>
                    <input type="text" name="penerbit" value="{{ old('penerbit', $book->penerbit) }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>

                <!-- Jenis Barang -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Jenis Barang
                    </label>
                    <input type="text" name="jenis_barang" value="{{ old('jenis_barang', $book->jenis_barang) }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>
            </div>

            <!-- Current Image Preview & Upload New -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <label class="block text-xs font-semibold text-slate-700 mb-2">Sampul Buku Saat Ini</label>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-22 rounded-lg bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                        @if($book->cover_url)
                            <img src="{{ $book->cover_url }}" alt="" class="w-full h-full object-cover">
                        @else
                            <span class="text-2xl">📖</span>
                        @endif
                    </div>
                    <div class="flex-1 space-y-2">
                        <input type="file" name="gambar" accept="image/*"
                            class="block w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        @if($book->gambar)
                        <label class="flex items-center text-xs text-rose-600 cursor-pointer mt-1">
                            <input type="checkbox" name="hapus_gambar" value="1" class="h-3.5 w-3.5 text-rose-600 rounded">
                            <span class="ml-1.5 font-medium">Hapus foto sampul saat ini (gunakan icon standar)</span>
                        </label>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Deskripsi -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Sinopsis / Deskripsi Buku
                </label>
                <textarea name="deskripsi" rows="3"
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent">{{ old('deskripsi', $book->deskripsi) }}</textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.books.index') }}" class="px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-xs transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
