@extends('layouts.app')

@section('title', 'Pinjaman & Riwayat Saya | Perpustakaan SMKN 1 Depok')

@section('content')
<div class="py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Page Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-blue-600">Sirkulasi Saya</div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Pinjaman & Riwayat Buku</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Pantau status permintaan, batas waktu pengembalian, dan riwayat peminjaman Anda.</p>
        </div>
        <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-xs transition self-start sm:self-auto">
            <i class="bi bi-plus-lg"></i> Pinjam Buku Baru
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-900">{{ $pendingRequests->count() }}</div>
                <div class="text-[11px] text-slate-500 font-semibold uppercase">Menunggu Persetujuan</div>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                <i class="bi bi-journal-bookmark"></i>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-900">{{ $activeLoans->count() }}</div>
                <div class="text-[11px] text-slate-500 font-semibold uppercase">Sedang Dipinjam</div>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                <i class="bi bi-check-circle"></i>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-900">{{ $pastLoans->count() }}</div>
                <div class="text-[11px] text-slate-500 font-semibold uppercase">Buku Telah Kembali</div>
            </div>
        </div>
    </div>

    <!-- Section 1: Active Loans -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden mb-8">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-journal-check text-blue-600"></i> Buku Sedang Dipinjam
            </h2>
            <span class="text-xs font-semibold text-slate-500">{{ $activeLoans->count() }} buku aktif</span>
        </div>

        @if($activeLoans->count() > 0)
        <div class="divide-y divide-slate-100">
            @foreach($activeLoans as $loan)
            <div class="p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:bg-slate-50/50 transition">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-16 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center text-xl shrink-0 shadow-2xs">
                        @if($loan->book->cover_url)
                            <img src="{{ $loan->book->cover_url }}" alt="{{ $loan->book->judul }}" class="w-full h-full object-cover">
                        @else
                            📖
                        @endif
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600">{{ $loan->book->kategori }}</span>
                        <h3 class="text-sm font-bold text-slate-900">{{ $loan->book->judul }}</h3>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Tanggal Pinjam: <span class="font-medium text-slate-700">{{ $loan->tanggal_pinjam->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:items-end w-full sm:w-auto pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                    <div class="text-xs">
                        Batas Kembali: <span class="font-bold {{ $loan->is_overdue ? 'text-rose-600' : 'text-slate-800' }}">{{ $loan->batas_kembali->translatedFormat('d M Y') }}</span>
                    </div>

                    @if($loan->is_overdue)
                    <div class="mt-1 px-2.5 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-[11px] font-bold inline-flex items-center gap-1">
                        <i class="bi bi-exclamation-triangle-fill"></i> Terlambat {{ $loan->overdue_days }} hari (Est. Denda: Rp {{ number_format($loan->estimated_fine, 0, ',', '.') }})
                    </div>
                    @else
                    <div class="mt-1 px-2.5 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px] font-bold">
                        Sisa waktu: {{ max(0, \Carbon\Carbon::today()->diffInDays($loan->batas_kembali, false)) }} hari lagi
                    </div>
                    @endif
                    
                    <span class="text-[11px] text-slate-400 mt-1">Kembalikan ke meja sirkulasi pustakawan</span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="p-8 text-center text-slate-500 text-xs">
            Anda tidak memiliki peminjaman buku yang aktif saat ini.
        </div>
        @endif
    </div>

    <!-- Section 2: Pending Borrow Requests -->
    @if($pendingRequests->count() > 0)
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden mb-8">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-hourglass-split text-amber-500"></i> Permintaan Menunggu Persetujuan
            </h2>
            <span class="text-xs font-semibold text-slate-500">{{ $pendingRequests->count() }} permintaan</span>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach($pendingRequests as $req)
            <div class="p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:bg-slate-50/50 transition">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-16 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center text-xl shrink-0 shadow-2xs">
                        @if($req->book->cover_url)
                            <img src="{{ $req->book->cover_url }}" alt="{{ $req->book->judul }}" class="w-full h-full object-cover">
                        @else
                            📖
                        @endif
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Menunggu Verifikasi</span>
                        <h3 class="text-sm font-bold text-slate-900">{{ $req->book->judul }}</h3>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Diajukan pada: <span class="font-medium text-slate-700">{{ $req->tanggal_request->translatedFormat('d M Y') }}</span>
                            @if($req->catatan)
                            &bull; Catatan: <span class="italic text-slate-600">"{{ $req->catatan }}"</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="w-full sm:w-auto flex items-center justify-end">
                    <form action="{{ route('member.borrow.cancel', $req) }}" method="POST" onsubmit="return confirm('Batalkan permintaan peminjaman buku ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition">
                            Batalkan Request
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Section 3: Past Returns History -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-clock-history text-slate-500"></i> Riwayat Pengembalian Terakhir
            </h2>
        </div>

        @if($pastLoans->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Judul Buku</th>
                        <th class="px-5 py-3.5">Tanggal Pinjam</th>
                        <th class="px-5 py-3.5">Tanggal Kembali</th>
                        <th class="px-5 py-3.5">Kondisi Buku</th>
                        <th class="px-5 py-3.5">Denda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($pastLoans as $past)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-5 py-3 font-semibold text-slate-900">{{ $past->book->judul }}</td>
                        <td class="px-5 py-3">{{ $past->tanggal_pinjam->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-3">{{ $past->bookReturn ? $past->bookReturn->tanggal_kembali->translatedFormat('d M Y') : '-' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                {{ $past->bookReturn->kondisi_buku ?? 'Baik' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @if($past->bookReturn && $past->bookReturn->total_denda > 0)
                                <span class="font-bold text-rose-600">Rp {{ number_format($past->bookReturn->total_denda, 0, ',', '.') }}</span>
                            @else
                                <span class="text-emerald-600 font-semibold">Rp 0 (Tepat Waktu)</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center text-slate-500 text-xs">
            Belum ada catatan riwayat buku yang telah dikembalikan.
        </div>
        @endif
    </div>
</div>
@endsection
