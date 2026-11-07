@extends('layouts.admin')

@section('title', 'Tambah Buku Baru | Panel Kelola')
@section('page_title', 'Tambah Buku ke Katalog')
@section('page_subtitle', 'Masukkan data identitas buku baru, nomor inventaris BOS, dan stok fisik')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-2xs">
        
        <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- Judul Buku -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Judul Buku <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="judul" required value="{{ old('judul') }}"
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                    placeholder="Contoh: Koding & Kecerdasan Artifisial Kelas X">
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
                    <input type="text" name="kategori" required value="{{ old('kategori') }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                        placeholder="Contoh: Umum, Sastra, Motivasi, Religi, Kejuruan">
                    @error('kategori')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No BOS -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nomor BOS / Inventaris
                    </label>
                    <input type="text" name="no_bos" value="{{ old('no_bos') }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                        placeholder="Contoh: 5439">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Stok -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Jumlah Stok (Eks) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="stok" required min="0" value="{{ old('stok', 1) }}"
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
                    <input type="number" name="tahun_cetak" min="1900" max="{{ date('Y') + 1 }}" value="{{ old('tahun_cetak', date('Y')) }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>

                <!-- Asal Perolehan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Asal Perolehan
                    </label>
                    <input type="text" name="asal_perolehan" value="{{ old('asal_perolehan', 'BOS') }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Penerbit -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Penerbit
                    </label>
                    <input type="text" name="penerbit" value="{{ old('penerbit') }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                        placeholder="Contoh: Erlangga / Mediatama">
                </div>

                <!-- Jenis Barang -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Jenis Barang
                    </label>
                    <input type="text" name="jenis_barang" value="{{ old('jenis_barang', 'Koleksi Umum') }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                        placeholder="Koleksi Umum / Buku Teks Pendamping">
                </div>
            </div>

            <!-- Upload Cover Gambar -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Foto Sampul Buku (Opsional)
                </label>
                <input type="file" name="gambar" accept="image/*"
                    class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                <p class="text-[11px] text-slate-400 mt-1">Format: JPG, PNG, WEBP (Maksimal 2MB)</p>
                @error('gambar')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Deskripsi -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Sinopsis / Deskripsi Buku
                </label>
                <textarea name="deskripsi" rows="3"
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                    placeholder="Uraian singkat mengenai cakupan materi atau isi buku...">{{ old('deskripsi') }}</textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.books.index') }}" class="px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-xs transition">
                    Simpan Buku Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
