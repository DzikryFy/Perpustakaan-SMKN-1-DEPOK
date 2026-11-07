@extends('layouts.admin')

@section('title', 'Dashboard | Panel Kelola Perpustakaan')
@section('page_title', 'Ringkasan Eksekutif Perpustakaan')
@section('page_subtitle', 'Monitoring sirkulasi, statistik koleksi, dan peninjauan layanan')

@section('content')
<div class="space-y-6">
    
    <!-- Welcome Card Banner -->
    <div class="bg-gradient-to-r from-blue-700 to-indigo-800 rounded-2xl p-6 text-white shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/15 text-blue-100 mb-2 border border-white/20">
                Layanan Digital Aktif
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">Selamat Datang, {{ Auth::user()->name }} 👋</h2>
            <p class="text-xs sm:text-sm text-blue-100 mt-1 max-w-xl">
                Anda masuk sebagai <strong class="text-white">{{ Auth::user()->role_label }}</strong>. Pantau perputaran sirkulasi buku dan verifikasi pengajuan siswa/guru.
            </p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
            <a href="{{ route('admin.loans.index') }}" class="px-4 py-2 rounded-xl bg-white text-blue-800 text-xs font-bold hover:bg-blue-50 shadow-xs transition">
                <i class="bi bi-arrow-left-right me-1"></i> Sirkulasi Pinjaman
            </a>
            @if(Auth::user()->isPustakawan())
            <a href="{{ route('admin.books.create') }}" class="px-4 py-2 rounded-xl bg-blue-600 border border-blue-400 text-white text-xs font-bold hover:bg-blue-500 shadow-xs transition">
                <i class="bi bi-plus-lg me-1"></i> Tambah Buku
            </a>
            @endif
        </div>
    </div>

    <!-- 4 Main Metric Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Books -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Judul Koleksi</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                    <i class="bi bi-book"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2">{{ $totalBooks }}</div>
            <div class="text-[11px] text-slate-500 mt-1 font-medium">Total stok: <strong class="text-slate-800">{{ number_format($totalStock, 0, ',', '.') }}</strong> eks</div>
        </div>

        <!-- Active Loans -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sedang Dipinjam</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base">
                    <i class="bi bi-journal-arrow-up"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2">{{ $activeLoans }}</div>
            <div class="text-[11px] mt-1 font-medium {{ $overdueLoans > 0 ? 'text-rose-600' : 'text-slate-500' }}">
                @if($overdueLoans > 0)
                    <strong>{{ $overdueLoans }} buku</strong> terlambat kembali
                @else
                    Semua dalam batas tempo
                @endif
            </div>
        </div>

        <!-- Pending Requests -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Request Masuk</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2">{{ $pendingRequests }}</div>
            <div class="text-[11px] text-slate-500 mt-1 font-medium">Menunggu persetujuan sirkulasi</div>
        </div>

        <!-- Total Fines -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Denda Terhimpun</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <div class="text-xl font-extrabold text-slate-900 mt-2">Rp {{ number_format($totalFines, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-1 font-medium">Dari {{ $totalReturns }} pengembalian</div>
        </div>
    </div>

    <!-- Charts & Popular Books Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Loan Trend Chart -->
        <div class="lg:col-span-8 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="bi bi-graph-up text-blue-600"></i> Tren Peminjaman Buku (6 Bulan Terakhir)
                    </h3>
                    <p class="text-[11px] text-slate-500">Volume transaksi peminjaman buku oleh siswa dan guru</p>
                </div>
            </div>
            <div class="h-64">
                <canvas id="loanTrendChart"></canvas>
            </div>
        </div>

        <!-- Popular Books List -->
        <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2 mb-1">
                    <i class="bi bi-star-fill text-amber-500"></i> Koleksi Paling Diminati
                </h3>
                <p class="text-[11px] text-slate-500 mb-4">Buku dengan frekuensi peminjaman tertinggi</p>

                <div class="space-y-3">
                    @forelse($popularBooks as $index => $pb)
                    <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition">
                        <div class="w-6 h-6 rounded-md bg-blue-50 text-blue-700 text-xs font-bold flex items-center justify-center shrink-0">
                            #{{ $index + 1 }}
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <h4 class="text-xs font-bold text-slate-900 truncate" title="{{ $pb->judul }}">{{ $pb->judul }}</h4>
                            <span class="text-[10px] text-slate-500">{{ $pb->kategori }} &bull; Stok: {{ $pb->stok }}</span>
                        </div>
                        <span class="text-xs font-extrabold text-blue-600 shrink-0">
                            {{ $pb->loans_count }}x
                        </span>
                    </div>
                    @empty
                    <div class="text-xs text-slate-400 text-center py-6">Belum ada riwayat peminjaman</div>
                    @endforelse
                </div>
            </div>

            <a href="{{ route('admin.reports.index') }}" class="mt-4 pt-3 border-t border-slate-100 text-center text-xs font-bold text-blue-600 hover:text-blue-800 block">
                Lihat Laporan Lengkap &rarr;
            </a>
        </div>
    </div>

    <!-- Recent Circulation Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="bi bi-clock-history text-blue-600"></i> Transaksi Peminjaman Terbaru
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Peminjaman terkini yang tercatat di sistem sirkulasi</p>
            </div>
            <a href="{{ route('admin.loans.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                Buka Meja Sirkulasi &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/70 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Nama Anggota</th>
                        <th class="px-5 py-3">Judul Buku</th>
                        <th class="px-5 py-3">Tanggal Pinjam</th>
                        <th class="px-5 py-3">Batas Kembali</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($recentLoans as $rl)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-5 py-3">
                            <div class="font-bold text-slate-900">{{ $rl->user->name }}</div>
                            <span class="text-[10px] text-slate-400 capitalize">{{ $rl->user->role }}</span>
                        </td>
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $rl->book->judul }}</td>
                        <td class="px-5 py-3">{{ $rl->tanggal_pinjam->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-3">
                            <span class="{{ $rl->is_overdue ? 'text-rose-600 font-bold' : '' }}">
                                {{ $rl->batas_kembali->translatedFormat('d M Y') }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @if($rl->status === 'dikembalikan')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Dikembalikan
                                </span>
                            @elseif($rl->is_overdue)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    Terlambat
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    Dipinjam
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-6 text-center text-slate-400">Belum ada transaksi peminjaman</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('loanTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($months) !!},
                datasets: [{
                    label: 'Peminjaman Buku',
                    data: {!! json_encode($trendData) !!},
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#2563eb',
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#64748b', font: { size: 11 } },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { color: '#64748b', font: { size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endpush
