@extends('layouts.admin')

@section('title', 'Laporan & Statistik | Panel Kelola')
@section('page_title', 'Laporan Kinerja & Sirkulasi Pustaka')
@section('page_subtitle', 'Analisis komprehensif data sirkulasi, inventaris buku, dan kepatuhan pengembalian')

@section('content')
<div class="space-y-6">
    
    <!-- Action Bar & Print Link -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Rekapitulasi Periode Berjalan</h3>
            <p class="text-xs text-slate-500">Cetak laporan resmi sirkulasi untuk arsip sekolah atau evaluasi kepala perpustakaan</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.print', ['periode' => 'bulan_ini']) }}" target="_blank" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-xs transition inline-flex items-center gap-1.5">
                <i class="bi bi-printer"></i> Cetak Laporan Bulan Ini
            </a>
            <a href="{{ route('admin.reports.print', ['periode' => 'semua']) }}" target="_blank" class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition inline-flex items-center gap-1.5">
                <i class="bi bi-file-earmark-text"></i> Cetak Semua
            </a>
        </div>
    </div>

    <!-- 5 Stats Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Judul</span>
            <div class="text-xl font-extrabold text-slate-900 mt-1">{{ $totalBooks }}</div>
            <span class="text-[10px] text-slate-500">{{ number_format($totalStock, 0, ',', '.') }} total eksemplar</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Transaksi</span>
            <div class="text-xl font-extrabold text-blue-600 mt-1">{{ $totalLoans }}</div>
            <span class="text-[10px] text-slate-500">{{ $activeLoans }} saat ini berjalan</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pengembalian</span>
            <div class="text-xl font-extrabold text-emerald-600 mt-1">{{ $totalReturns }}</div>
            <span class="text-[10px] text-slate-500">Telah diverifikasi fisik</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Buku Terlambat</span>
            <div class="text-xl font-extrabold text-rose-600 mt-1">{{ $overdueLoansList->count() }}</div>
            <span class="text-[10px] text-slate-500">Melewati tanggal tempo</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs col-span-2 sm:col-span-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kas Denda</span>
            <div class="text-lg font-extrabold text-slate-900 mt-1">Rp {{ number_format($totalFines, 0, ',', '.') }}</div>
            <span class="text-[10px] text-emerald-600 font-semibold">Tercatat di pembukuan</span>
        </div>
    </div>

    <!-- Annual Trend Chart -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2 mb-1">
            <i class="bi bi-graph-up-arrow text-blue-600"></i> Tren Peminjaman 12 Bulan Terakhir
        </h3>
        <p class="text-[11px] text-slate-500 mb-4">Grafik dinamika minat baca dan sirkulasi peminjaman per bulan</p>
        <div class="h-64">
            <canvas id="annualChart"></canvas>
        </div>
    </div>

    <!-- 2 Column: Popular Books & Active Borrowers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Top 8 Books -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2 mb-3">
                <i class="bi bi-trophy-fill text-amber-500"></i> 8 Buku Paling Sering Dipinjam
            </h3>
            <div class="divide-y divide-slate-100 text-xs">
                @foreach($popularBooks as $index => $bk)
                <div class="py-2.5 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <span class="w-6 h-6 rounded-md bg-slate-100 text-slate-600 font-bold flex items-center justify-center shrink-0 text-[11px]">
                            {{ $index + 1 }}
                        </span>
                        <div class="truncate">
                            <div class="font-bold text-slate-800 truncate">{{ $bk->judul }}</div>
                            <span class="text-[10px] text-slate-400">{{ $bk->kategori }} &bull; Stok: {{ $bk->stok }} eks</span>
                        </div>
                    </div>
                    <span class="font-extrabold text-blue-600 shrink-0 text-xs">
                        {{ $bk->loans_count }}x dipinjam
                    </span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Top 8 Borrowers -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2 mb-3">
                <i class="bi bi-person-check-fill text-blue-600"></i> Anggota Paling Aktif Membaca
            </h3>
            <div class="divide-y divide-slate-100 text-xs">
                @foreach($activeBorrowers as $index => $ab)
                <div class="py-2.5 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <span class="w-6 h-6 rounded-md bg-blue-50 text-blue-700 font-bold flex items-center justify-center shrink-0 text-[11px]">
                            {{ $index + 1 }}
                        </span>
                        <div class="truncate">
                            <div class="font-bold text-slate-800 truncate">{{ $ab->name }}</div>
                            <span class="text-[10px] text-slate-400 capitalize">{{ $ab->role }} &bull; {{ $ab->username }}</span>
                        </div>
                    </div>
                    <span class="font-extrabold text-emerald-600 shrink-0 text-xs">
                        {{ $ab->loans_count }} pinjaman
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Category Distribution Breakdown -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2 mb-4">
            <i class="bi bi-pie-chart-fill text-purple-600"></i> Distribusi Judul & Stok Berdasarkan Kategori
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($categories as $cat)
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                <div class="text-xs font-bold text-slate-800">{{ $cat->kategori }}</div>
                <div class="text-lg font-extrabold text-blue-600 mt-1">{{ $cat->count }} Judul</div>
                <span class="text-[10px] text-slate-400">{{ number_format($cat->stock, 0, ',', '.') }} total eksemplar</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Overdue Loans Table -->
    @if($overdueLoansList->count() > 0)
    <div class="bg-white rounded-2xl border border-rose-200 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-rose-100 bg-rose-50/50 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-rose-800 flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-rose-600"></i> Daftar Peminjaman Melewati Batas Tempo
                </h3>
                <p class="text-[11px] text-rose-600 mt-0.5">Segera hubungi siswa/guru terkait untuk pengembalian dan penyelesaian denda</p>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-200 text-rose-800">
                {{ $overdueLoansList->count() }} kasus terlambat
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-rose-50/30 border-b border-rose-100 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Nama Anggota</th>
                        <th class="px-5 py-3">Judul Buku</th>
                        <th class="px-5 py-3">Batas Kembali</th>
                        <th class="px-5 py-3">Hari Telat</th>
                        <th class="px-5 py-3">Akumulasi Denda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rose-50 text-slate-700">
                    @foreach($overdueLoansList as $ol)
                    <tr class="hover:bg-rose-50/20">
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900">{{ $ol->user->name }}</div>
                            <span class="text-[10px] text-slate-400 capitalize">{{ $ol->user->role }}</span>
                        </td>
                        <td class="px-5 py-3.5 font-medium text-slate-800">{{ $ol->book->judul }}</td>
                        <td class="px-5 py-3.5 text-rose-600 font-semibold">{{ $ol->batas_kembali->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-3.5 font-bold text-rose-600">{{ $ol->overdue_days }} hari</td>
                        <td class="px-5 py-3.5 font-extrabold text-rose-700">
                            Rp {{ number_format($ol->estimated_fine, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('annualChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Jumlah Peminjaman',
                    data: {!! json_encode($chartValues) !!},
                    backgroundColor: 'rgba(37, 99, 235, 0.75)',
                    borderRadius: 6,
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
                        ticks: { color: '#64748b', font: { size: 10 } },
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endpush
