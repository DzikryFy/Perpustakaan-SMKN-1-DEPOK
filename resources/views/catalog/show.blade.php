@extends('layouts.app')

@section('title', $book->judul . ' | Perpustakaan SMKN 1 Depok')

@section('content')
<div class="py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Breadcrumb -->
    <nav class="flex text-xs text-slate-500 font-medium mb-6 gap-2 items-center">
        <a href="{{ route('home') }}" class="hover:text-blue-700">Beranda</a>
        <span>/</span>
        <a href="{{ route('catalog.index') }}" class="hover:text-blue-700">Koleksi Buku</a>
        <span>/</span>
        <span class="text-slate-800 truncate max-w-xs">{{ $book->judul }}</span>
    </nav>

    <!-- Main Detail Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 lg:p-10 mb-12">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12 items-start">
            
            <!-- Book Cover Column (Editorial Book Presentation) -->
            <div class="md:col-span-5 lg:col-span-4 flex flex-col items-center">
                <div class="w-full max-w-[260px] aspect-3/4 rounded-xl bg-slate-800 text-white p-5 flex flex-col justify-between shadow-md border-l-4 border-slate-600 relative overflow-hidden">
                    @if($book->cover_url)
                        <img src="{{ $book->cover_url }}" alt="{{ $book->judul }}" class="absolute inset-0 w-full h-full object-cover">
                    @else
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                            {{ $book->kategori }}
                        </div>
                        <div>
                            <div class="font-bold text-base text-slate-100 leading-snug">
                                {{ $book->judul }}
                            </div>
                            <div class="text-xs text-slate-400 mt-2">
                                {{ $book->penerbit ?? 'Pustaka Sekolah' }}
                            </div>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono">
                            {{ $book->no_bos ? 'BOS #' . $book->no_bos : 'Koleksi Umum' }}
                        </div>
                    @endif
                </div>

                <div class="mt-4 text-center">
                    <span class="text-xs font-medium {{ $book->stok > 0 ? 'text-emerald-700' : 'text-slate-400' }}">
                        {{ $book->stok > 0 ? 'Tersedia di rak: ' . $book->stok . ' eksemplar' : 'Stok sedang habis' }}
                    </span>
                </div>
            </div>

            <!-- Book Specs & Action Column -->
            <div class="md:col-span-7 lg:col-span-8 flex flex-col justify-between h-full">
                <div>
                    <!-- Category & BOS Metadata -->
                    <div class="flex items-center gap-2 mb-2 text-xs text-slate-500">
                        <span class="font-semibold text-blue-700 uppercase tracking-wider">{{ $book->kategori }}</span>
                        @if($book->no_bos)
                        <span>•</span>
                        <span class="font-mono text-slate-600">Nomor BOS: #{{ $book->no_bos }}</span>
                        @endif
                        @if($book->asal_perolehan)
                        <span>•</span>
                        <span>Asal: {{ $book->asal_perolehan }}</span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">
                        {{ $book->judul }}
                    </h1>

                    <p class="text-sm text-slate-600 mb-6 leading-relaxed">
                        {{ $book->deskripsi ?? 'Buku pegangan dan bahan pustaka resmi koleksi Perpustakaan SMKN 1 Depok untuk menunjang kegiatan pembelajaran, literasi mandiri, dan penguatan kejuruan siswa di sekolah.' }}
                    </p>

                    <!-- Specifications Table (Clean Bordered Grid) -->
                    <div class="rounded-xl border border-slate-200 overflow-hidden mb-6 text-xs">
                        <div class="grid grid-cols-2 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 bg-slate-50/60">
                            <div class="p-3">
                                <div class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Penerbit</div>
                                <div class="font-semibold text-slate-800 mt-0.5 truncate">{{ $book->penerbit ?? 'Kemdikbud' }}</div>
                            </div>
                            <div class="p-3">
                                <div class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Tahun Terbit</div>
                                <div class="font-semibold text-slate-800 mt-0.5">{{ $book->tahun_cetak ?? '-' }}</div>
                            </div>
                            <div class="p-3 col-span-2 sm:col-span-1">
                                <div class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Jenis Barang</div>
                                <div class="font-semibold text-slate-800 mt-0.5 truncate">{{ $book->jenis_barang ?? 'Koleksi Umum' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Peminjaman Rules Alert -->
                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-600 mb-6">
                        <span class="font-semibold text-slate-900">Ketentuan Sirkulasi:</span> Batas waktu peminjaman buku maksimal <strong>7 hari kalender</strong>. Keterlambatan pengembalian dikenakan denda <strong>Rp 1.000,- / hari</strong> per buku.
                    </div>
                </div>

                <!-- Action Button Controls -->
                <div class="pt-4 border-t border-slate-100" x-data="{ borrowModalOpen: false }">
                    @guest
                        <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 rounded-lg bg-blue-700 text-white text-xs font-semibold hover:bg-blue-800 transition">
                            Masuk untuk Meminjam Buku
                        </a>
                    @else
                        @if($hasActiveLoan)
                            <div class="p-3 rounded-lg bg-amber-50 text-amber-900 border border-amber-200 text-xs">
                                Anda sedang meminjam buku ini. Cek rincian di halaman <a href="{{ route('member.loans') }}" class="underline font-semibold">Pinjaman Saya</a>.
                            </div>
                        @elseif($hasPendingRequest)
                            <div class="p-3 rounded-lg bg-blue-50 text-blue-900 border border-blue-200 text-xs">
                                Permintaan peminjaman buku ini telah dikirim dan menunggu persetujuan pustakawan.
                            </div>
                        @elseif($book->stok < 1)
                            <button disabled class="px-5 py-2.5 rounded-lg bg-slate-100 text-slate-400 text-xs font-medium cursor-not-allowed">
                                Stok Buku Sedang Habis
                            </button>
                        @else
                            <button @click="borrowModalOpen = true" type="button" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 rounded-lg bg-blue-700 text-white text-xs font-semibold hover:bg-blue-800 transition cursor-pointer">
                                Ajukan Peminjaman Buku
                            </button>

                            <!-- Borrow Request Modal -->
                            <div x-show="borrowModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                    <div class="fixed inset-0 transition-opacity bg-slate-900/40" @click="borrowModalOpen = false"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                    
                                    <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-lg transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6">
                                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                            <h3 class="text-sm font-bold text-slate-900">Konfirmasi Pengajuan Peminjaman</h3>
                                            <button @click="borrowModalOpen = false" class="text-slate-400 hover:text-slate-600 text-sm">
                                                ✕
                                            </button>
                                        </div>

                                        <form action="{{ route('member.borrow.request', $book) }}" method="POST" class="mt-4 space-y-4">
                                            @csrf
                                            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs">
                                                <div class="font-semibold text-slate-900">{{ $book->judul }}</div>
                                                <div class="text-slate-500 mt-0.5">Kategori: {{ $book->kategori }} • Sisa Stok: {{ $book->stok }} eksemplar</div>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-slate-700 mb-1">Catatan Peminjaman (Opsional)</label>
                                                <textarea name="catatan" rows="3" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-hidden focus:ring-2 focus:ring-blue-600" placeholder="Misal: untuk materi belajar kelas atau tugas kejuruan..."></textarea>
                                            </div>

                                            <p class="text-[11px] text-slate-500">
                                                Setelah diajukan, silakan temui staf perpustakaan di meja sirkulasi untuk mengambil fisik buku.
                                            </p>

                                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                                                <button type="button" @click="borrowModalOpen = false" class="px-4 py-2 text-xs font-medium rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">
                                                    Batal
                                                </button>
                                                <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-lg bg-blue-700 text-white hover:bg-blue-800 transition">
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
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Rekomendasi Terkait</p>
        <h2 class="text-lg font-bold text-slate-900 tracking-tight mt-0.5 mb-5">Koleksi Kategori {{ $book->kategori }} Lainnya</h2>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($relatedBooks as $rb)
            <a href="{{ route('catalog.show', $rb) }}" class="group bg-white p-3 rounded-xl border border-slate-200 hover:border-slate-300 transition flex items-center gap-3">
                <div class="w-12 h-16 rounded-md bg-slate-800 text-white shrink-0 overflow-hidden text-[9px] p-1 flex flex-col justify-between border-l-2 border-slate-600">
                    @if($rb->cover_url)
                        <img src="{{ $rb->cover_url }}" alt="{{ $rb->judul }}" class="w-full h-full object-cover">
                    @else
                        <span class="line-clamp-2 leading-tight font-semibold text-slate-200">{{ $rb->judul }}</span>
                        <span class="text-slate-400 font-mono text-[8px]">{{ $rb->stok }} eks</span>
                    @endif
                </div>
                <div class="overflow-hidden">
                    <h3 class="text-xs font-bold text-slate-900 group-hover:text-blue-700 line-clamp-2 transition">{{ $rb->judul }}</h3>
                    <span class="text-[11px] text-slate-500 mt-0.5 block">{{ $rb->stok }} eksemplar</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
