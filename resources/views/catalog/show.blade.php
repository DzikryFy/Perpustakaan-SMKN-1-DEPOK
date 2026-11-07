@extends('layouts.app')

@section('title', $book->judul . ' | Perpustakaan SMKN 1 Depok')

@section('content')
<div class="py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Breadcrumb -->
    <nav class="flex text-xs text-slate-500 font-medium mb-6 gap-2 items-center">
        <a href="{{ route('home') }}" class="hover:text-blue-600">Beranda</a>
        <i class="bi bi-chevron-right text-[10px]"></i>
        <a href="{{ route('catalog.index') }}" class="hover:text-blue-600">Koleksi Buku</a>
        <i class="bi bi-chevron-right text-[10px]"></i>
        <span class="text-slate-800 font-semibold truncate max-w-xs">{{ $book->judul }}</span>
    </nav>

    <!-- Main Detail Card -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-8 lg:p-10 mb-12">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
            
            <!-- Book Cover Column -->
            <div class="md:col-span-5 lg:col-span-4 flex flex-col items-center">
                <div class="w-full max-w-[280px] aspect-3/4 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 border border-slate-200 p-4 flex items-center justify-center relative overflow-hidden shadow-sm">
                    @if($book->cover_url)
                        <img src="{{ $book->cover_url }}" alt="{{ $book->judul }}" class="h-full w-auto object-cover rounded-lg shadow-md">
                    @else
                        <div class="text-center p-4">
                            <div class="text-6xl mb-3">📖</div>
                            <div class="text-sm font-bold text-slate-800 line-clamp-3">{{ $book->judul }}</div>
                            <div class="text-xs text-slate-500 mt-2">{{ $book->penerbit ?? 'Kemdikbud' }}</div>
                        </div>
                    @endif

                    <!-- Stock Status Pill -->
                    <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold shadow-xs {{ $book->stok > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                        {{ $book->stok > 0 ? 'Tersedia: ' . $book->stok . ' eks' : 'Stok Habis' }}
                    </span>
                </div>

                <div class="mt-4 text-center">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Kategori: {{ $book->kategori }}
                    </span>
                </div>
            </div>

            <!-- Book Specs & Action Column -->
            <div class="md:col-span-7 lg:col-span-8 flex flex-col justify-between h-full">
                <div>
                    <!-- Title & BOS Badge -->
                    <div class="flex items-center gap-2 mb-2">
                        @if($book->no_bos)
                        <span class="px-2.5 py-0.5 rounded-md text-[11px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            No. BOS: #{{ $book->no_bos }}
                        </span>
                        @endif
                        <span class="text-xs text-slate-500 font-medium">Asal: {{ $book->asal_perolehan ?? 'BOS' }}</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight mb-3">
                        {{ $book->judul }}
                    </h1>

                    <p class="text-sm text-slate-600 mb-6 leading-relaxed">
                        {{ $book->deskripsi ?? 'Buku pegangan dan bahan pustaka resmi koleksi Perpustakaan SMKN 1 Depok untuk menunjang kegiatan pembelajaran dan wawasan literasi sekolah.' }}
                    </p>

                    <!-- Specifications Table -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden mb-6 text-xs">
                        <div class="grid grid-cols-2 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 bg-slate-50/50">
                            <div class="p-3">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Penerbit</div>
                                <div class="font-bold text-slate-800 mt-0.5 truncate">{{ $book->penerbit ?? 'Kemdikbud' }}</div>
                            </div>
                            <div class="p-3">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tahun Terbit</div>
                                <div class="font-bold text-slate-800 mt-0.5">{{ $book->tahun_cetak ?? '-' }}</div>
                            </div>
                            <div class="p-3 col-span-2 sm:col-span-1">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jenis Barang</div>
                                <div class="font-bold text-slate-800 mt-0.5 truncate">{{ $book->jenis_barang ?? 'Koleksi Umum' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Peminjaman Rules Alert -->
                    <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-200 text-xs text-blue-900 flex items-start gap-3 mb-6">
                        <i class="bi bi-info-circle-fill text-blue-600 text-sm mt-0.5 shrink-0"></i>
                        <div>
                            <strong>Informasi Peminjaman:</strong> Durasi pinjam maksimal <strong>7 hari</strong>. Pengembalian terlambat dikenakan denda keterlambatan <strong>Rp 1.000,- / hari</strong>.
                        </div>
                    </div>
                </div>

                <!-- Action Button Controls -->
                <div class="pt-4 border-t border-slate-100" x-data="{ borrowModalOpen: false }">
                    @guest
                        <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-xs transition">
                            <i class="bi bi-box-arrow-in-right"></i> Masuk untuk Meminjam Buku
                        </a>
                    @else
                        @if($hasActiveLoan)
                            <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold">
                                <i class="bi bi-journal-check text-amber-600 text-base"></i>
                                Anda sedang meminjam buku ini. Cek di menu <a href="{{ route('member.loans') }}" class="underline font-bold">Pinjaman Saya</a>.
                            </div>
                        @elseif($hasPendingRequest)
                            <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-50 text-blue-800 border border-blue-200 text-xs font-semibold">
                                <i class="bi bi-clock-history text-blue-600 text-base"></i>
                                Permintaan peminjaman Anda sedang menunggu persetujuan pustakawan.
                            </div>
                        @elseif($book->stok < 1)
                            <button disabled class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-200 text-slate-500 text-xs font-bold cursor-not-allowed">
                                <i class="bi bi-x-circle"></i> Stok Buku Sedang Habis
                            </button>
                        @else
                            <button @click="borrowModalOpen = true" type="button" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-xs transition cursor-pointer">
                                <i class="bi bi-journal-plus"></i> Ajukan Peminjaman Buku
                            </button>

                            <!-- Borrow Request Modal -->
                            <div x-show="borrowModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                    <div class="fixed inset-0 transition-opacity bg-slate-900/50" @click="borrowModalOpen = false"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                    
                                    <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6">
                                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                            <h3 class="text-base font-bold text-slate-900">Konfirmasi Pengajuan Peminjaman</h3>
                                            <button @click="borrowModalOpen = false" class="text-slate-400 hover:text-slate-600">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>

                                        <form action="{{ route('member.borrow.request', $book) }}" method="POST" class="mt-4 space-y-4">
                                            @csrf
                                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                                                <div class="font-bold text-slate-900">{{ $book->judul }}</div>
                                                <div class="text-slate-500 mt-0.5">Kategori: {{ $book->kategori }} &bull; Sisa Stok: {{ $book->stok }} eksemplar</div>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Peminjaman (Opsional)</label>
                                                <textarea name="catatan" rows="3" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600" placeholder="Misal: untuk tugas literasi kelas X atau referensi kejuruan..."></textarea>
                                            </div>

                                            <div class="text-[11px] text-slate-500 bg-amber-50/50 p-2.5 rounded-lg border border-amber-200/60">
                                                <i class="bi bi-exclamation-circle text-amber-600 me-1"></i>
                                                Setelah diajukan, silakan temui petugas pustakawan di meja sirkulasi untuk mengambil buku fisik.
                                            </div>

                                            <div class="flex items-center justify-end gap-2 pt-2">
                                                <button type="button" @click="borrowModalOpen = false" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
                                                    Batal
                                                </button>
                                                <button type="submit" class="px-5 py-2 text-xs font-bold rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-xs">
                                                    Kirim Permintaan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endguest
                </div>
            </div>
        </div>
    </div>

    <!-- Related Books -->
    @if($relatedBooks->count() > 0)
    <div>
        <div class="text-xs font-bold uppercase tracking-wider text-blue-600">Rekomendasi Terkait</div>
        <h2 class="text-xl font-bold text-slate-900 tracking-tight mt-1 mb-6">Buku Kategori {{ $book->kategori }} Lainnya</h2>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($relatedBooks as $rb)
            <a href="{{ route('catalog.show', $rb) }}" class="group bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-2xs hover:shadow-sm hover:border-blue-200 transition flex items-center gap-3">
                <div class="w-12 h-16 rounded-md bg-slate-100 flex items-center justify-center shrink-0 overflow-hidden text-xl shadow-xs">
                    @if($rb->cover_url)
                        <img src="{{ $rb->cover_url }}" alt="{{ $rb->judul }}" class="w-full h-full object-cover">
                    @else
                        📖
                    @endif
                </div>
                <div class="overflow-hidden">
                    <h4 class="text-xs font-bold text-slate-900 group-hover:text-blue-600 line-clamp-2 transition-colors">{{ $rb->judul }}</h4>
                    <span class="text-[10px] text-slate-400 mt-1 block">Stok: {{ $rb->stok }} eks</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
