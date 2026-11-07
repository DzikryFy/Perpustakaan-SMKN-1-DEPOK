@extends('layouts.app')

@section('title', 'Saran Buku Baru | Perpustakaan SMKN 1 Depok')

@section('content')
<div class="py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Page Header -->
    <div class="mb-8">
        <div class="text-xs font-bold uppercase tracking-wider text-blue-600">Partisipasi Anggota</div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Usulkan Buku Baru</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Rekomendasikan buku bacaan favorit atau materi kejuruan untuk pengadaan koleksi perpustakaan berikutnya.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Suggestion Form -->
        <div class="lg:col-span-5 bg-white p-6 sm:p-7 rounded-2xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center gap-2 pb-4 mb-4 border-b border-slate-100">
                <i class="bi bi-lightbulb-fill text-amber-500 text-lg"></i>
                <h2 class="text-base font-bold text-slate-900">Form Pengajuan Usulan</h2>
            </div>

            <form action="{{ route('member.suggestions.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Judul Buku -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Judul Buku yang Diusulkan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="judul" required value="{{ old('judul') }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                        placeholder="Contoh: Filosofi Teras / Clean Code">
                    @error('judul')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Penulis / Pengarang -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nama Penulis / Pengarang
                    </label>
                    <input type="text" name="penulis" value="{{ old('penulis') }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                        placeholder="Contoh: Henry Manampiring">
                </div>

                <!-- Penerbit -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Penerbit (Jika Diketahui)
                    </label>
                    <input type="text" name="penerbit" value="{{ old('penerbit') }}"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                        placeholder="Contoh: Penerbit Buku Kompas">
                </div>

                <!-- Alasan Rekomendasi -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Alasan & Manfaat Rekomendasi
                    </label>
                    <textarea name="alasan" rows="4"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                        placeholder="Jelaskan secara singkat mengapa buku ini bermanfaat bagi siswa/guru SMKN 1 Depok...">{{ old('alasan') }}</textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-xs transition">
                        Kirim Usulan Buku
                    </button>
                </div>
            </form>
        </div>

        <!-- History of Suggestions -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="bi bi-clock-history text-blue-600"></i> Usulan Koleksi Saya
                </h2>
                <span class="text-xs text-slate-500 font-medium">{{ $suggestions->total() }} total usulan</span>
            </div>

            @if($suggestions->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($suggestions as $s)
                <div class="p-5 hover:bg-slate-50/50 transition">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $s->status_badge['class'] }}">
                                {{ $s->status_badge['label'] }}
                            </span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1.5">{{ $s->judul }}</h3>
                            <div class="text-xs text-slate-500 mt-0.5">
                                Penulis: <span class="font-medium text-slate-700">{{ $s->penulis ?? '-' }}</span> &bull;
                                Penerbit: <span class="font-medium text-slate-700">{{ $s->penerbit ?? '-' }}</span>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-400 shrink-0">
                            {{ $s->tanggal_saran->translatedFormat('d M Y') }}
                        </span>
                    </div>

                    @if($s->alasan)
                    <p class="mt-2.5 text-xs text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-100 italic">
                        "{{ $s->alasan }}"
                    </p>
                    @endif

                    @if($s->catatan_review)
                    <div class="mt-2.5 p-2.5 rounded-lg bg-blue-50/60 border border-blue-200/70 text-xs text-blue-900">
                        <strong class="font-bold flex items-center gap-1">
                            <i class="bi bi-chat-quote-fill text-blue-600"></i> Catatan Pustakawan / Kepala:
                        </strong>
                        <div class="mt-0.5">{{ $s->catatan_review }}</div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $suggestions->links() }}
            </div>
            @else
            <div class="p-10 text-center text-slate-500 text-xs">
                Anda belum pernah mengajukan usulan buku. Gunakan form di sebelah kiri untuk merekomendasikan buku bacaan baru.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
