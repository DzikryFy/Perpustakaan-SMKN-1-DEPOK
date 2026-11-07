@extends('layouts.admin')

@section('title', 'Sirkulasi & Peminjaman | Panel Kelola')
@section('page_title', 'Meja Sirkulasi & Peminjaman')
@section('page_subtitle', 'Persetujuan permintaan, pencatatan pinjaman langsung, dan sirkulasi pengembalian buku')

@section('content')
<div class="space-y-6" x-data="{ 
    directLoanModal: false, 
    returnModal: false, 
    rejectModal: false,
    selectedLoan: null,
    selectedRequest: null,
    returnCondition: 'Baik',
    extraFine: 0,
    openReturnModal(loan) {
        this.selectedLoan = loan;
        this.returnCondition = 'Baik';
        this.extraFine = 0;
        this.returnModal = true;
    },
    openRejectModal(req) {
        this.selectedRequest = req;
        this.rejectModal = true;
    }
}">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        
        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-200 pb-2 overflow-x-auto text-xs font-semibold">
            <a href="{{ route('admin.loans.index', ['tab' => 'requests']) }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 whitespace-nowrap {{ $tab === 'requests' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                <i class="bi bi-hourglass-split"></i> Permintaan Masuk
                @if($requests->count() > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tab === 'requests' ? 'bg-white/20 text-white' : 'bg-amber-500 text-white' }}">
                    {{ $requests->count() }}
                </span>
                @endif
            </a>

            <a href="{{ route('admin.loans.index', ['tab' => 'active']) }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 whitespace-nowrap {{ $tab === 'active' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                <i class="bi bi-journal-check"></i> Pinjaman Berjalan
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tab === 'active' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">
                    {{ $activeLoans->total() }}
                </span>
            </a>

            <a href="{{ route('admin.loans.index', ['tab' => 'history']) }}" 
               class="px-4 py-2 rounded-xl transition flex items-center gap-2 whitespace-nowrap {{ $tab === 'history' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                <i class="bi bi-clock-history"></i> Riwayat Selesai
            </a>
        </div>

        <!-- Direct Loan Button -->
        <button @click="directLoanModal = true" type="button" class="px-4 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-xs transition inline-flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
            <i class="bi bi-plus-lg"></i> Catat Peminjaman Langsung
        </button>
    </div>

    <!-- TAB 1: PENDING REQUESTS -->
    @if($tab === 'requests')
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Daftar Pengajuan Peminjaman Buku</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Permintaan yang diajukan oleh siswa/guru melalui portal digital dan siap diverifikasi fisik</p>
        </div>

        @if($requests->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Nama Anggota</th>
                        <th class="px-5 py-3">Judul Buku</th>
                        <th class="px-5 py-3">Tgl Request</th>
                        <th class="px-5 py-3">Sisa Stok</th>
                        <th class="px-5 py-3">Catatan Siswa</th>
                        <th class="px-5 py-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($requests as $req)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900">{{ $req->user->name }}</div>
                            <span class="text-[10px] text-slate-400 capitalize">Role: {{ $req->user->role }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-800">{{ $req->book->judul }}</div>
                            <span class="text-[10px] text-slate-400">{{ $req->book->kategori }}</span>
                        </td>
                        <td class="px-5 py-3.5">{{ $req->tanggal_request->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $req->book->stok > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ $req->book->stok }} eksemplar
                            </span>
                        </td>
                        <td class="px-5 py-3.5 italic text-slate-500 max-w-xs truncate">
                            {{ $req->catatan ?? '-' }}
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-2">
                                <form action="{{ route('admin.requests.approve', $req) }}" method="POST" onsubmit="return confirm('Setujui peminjaman buku ini dan kurangi stok 1?')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition">
                                        <i class="bi bi-check-lg me-1"></i> Setujui
                                    </button>
                                </form>

                                <button type="button" @click="openRejectModal({ id: {{ $req->id }}, title: '{{ addslashes($req->book->judul) }}', user: '{{ addslashes($req->user->name) }}' })" class="px-3 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-xs transition">
                                    <i class="bi bi-x-lg me-1"></i> Tolak
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-10 text-center text-slate-400 text-xs">
            <i class="bi bi-inbox text-3xl block mb-2 opacity-50"></i>
            Tidak ada permintaan peminjaman baru yang menunggu persetujuan.
        </div>
        @endif
    </div>
    @endif

    <!-- TAB 2: ACTIVE LOANS -->
    @if($tab === 'active')
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Daftar Buku Sedang Dipinjam</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Buku yang saat ini berada di tangan anggota perpustakaan</p>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.loans.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="active">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama anggota / buku..."
                    class="px-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 w-48 sm:w-60">
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700">
                    Cari
                </button>
            </form>
        </div>

        @if($activeLoans->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Peminjam</th>
                        <th class="px-5 py-3">Judul Buku</th>
                        <th class="px-5 py-3">Tgl Pinjam</th>
                        <th class="px-5 py-3">Batas Kembali</th>
                        <th class="px-5 py-3">Status / Denda</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($activeLoans as $al)
                    <tr class="hover:bg-slate-50/50 {{ $al->is_overdue ? 'bg-rose-50/30' : '' }}">
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900">{{ $al->user->name }}</div>
                            <span class="text-[10px] text-slate-400 capitalize">{{ $al->user->role }}</span>
                        </td>
                        <td class="px-5 py-3.5 font-medium text-slate-800">{{ $al->book->judul }}</td>
                        <td class="px-5 py-3.5">{{ $al->tanggal_pinjam->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-3.5 font-semibold {{ $al->is_overdue ? 'text-rose-600' : 'text-slate-900' }}">
                            {{ $al->batas_kembali->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if($al->is_overdue)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200 inline-block">
                                Terlambat {{ $al->overdue_days }} hari (Denda: Rp {{ number_format($al->estimated_fine, 0, ',', '.') }})
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-block">
                                Sisa {{ max(0, \Carbon\Carbon::today()->diffInDays($al->batas_kembali, false)) }} hari
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <button type="button" @click="openReturnModal({
                                id: {{ $al->id }},
                                title: '{{ addslashes($al->book->judul) }}',
                                user: '{{ addslashes($al->user->name) }}',
                                dueDate: '{{ $al->batas_kembali->translatedFormat('d M Y') }}',
                                isOverdue: {{ $al->is_overdue ? 'true' : 'false' }},
                                overdueDays: {{ $al->overdue_days }},
                                overdueFine: {{ $al->estimated_fine }}
                            })" class="px-3.5 py-1.5 rounded-lg bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition shadow-2xs">
                                <i class="bi bi-box-arrow-in-left me-1"></i> Proses Kembali
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $activeLoans->links() }}
        </div>
        @else
        <div class="p-10 text-center text-slate-400 text-xs">
            Tidak ada data pinjaman aktif.
        </div>
        @endif
    </div>
    @endif

    <!-- TAB 3: RETURN HISTORY -->
    @if($tab === 'history')
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Riwayat Pengembalian Buku</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Catatan seluruh pengembalian buku beserta pemeriksaan fisik dan pelunasan denda</p>
        </div>

        @if($historyLoans->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Nama Anggota</th>
                        <th class="px-5 py-3">Judul Buku</th>
                        <th class="px-5 py-3">Tgl Kembali</th>
                        <th class="px-5 py-3">Kondisi Fisik</th>
                        <th class="px-5 py-3">Rincian Denda</th>
                        <th class="px-5 py-3">Petugas Penerima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($historyLoans as $hl)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-5 py-3.5 font-bold text-slate-900">{{ $hl->user->name }}</td>
                        <td class="px-5 py-3.5 font-medium text-slate-800">{{ $hl->book->judul }}</td>
                        <td class="px-5 py-3.5">{{ $hl->tanggal_kembali->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $hl->kondisi_buku === 'Baik' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ $hl->kondisi_buku }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($hl->total_denda > 0)
                            <div class="font-bold text-rose-600">Rp {{ number_format($hl->total_denda, 0, ',', '.') }}</div>
                            <span class="text-[10px] text-slate-400">Telat: Rp {{ number_format($hl->denda_keterlambatan, 0, ',', '.') }} | Fisik: Rp {{ number_format($hl->denda_kerusakan, 0, ',', '.') }}</span>
                            @else
                            <span class="text-emerald-600 font-semibold">Rp 0 (Lengkap & Tepat Waktu)</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 font-medium">
                            {{ $hl->petugas->name ?? 'Pustakawan' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $historyLoans->links() }}
        </div>
        @else
        <div class="p-10 text-center text-slate-400 text-xs">
            Belum ada data pengembalian yang tercatat.
        </div>
        @endif
    </div>
    @endif

    <!-- MODAL 1: PROCESS RETURN (KEMBALIKAN BUKU) -->
    <div x-show="returnModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/50" @click="returnModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Form Pengembalian Buku</h3>
                    <button @click="returnModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <template x-if="selectedLoan">
                    <form :action="'{{ url('admin/peminjaman') }}/' + selectedLoan.id + '/kembali'" method="POST" class="mt-4 space-y-4">
                        @csrf
                        
                        <!-- Loan Overview -->
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1">
                            <div><strong>Buku:</strong> <span x-text="selectedLoan.title"></span></div>
                            <div><strong>Peminjam:</strong> <span x-text="selectedLoan.user"></span></div>
                            <div><strong>Batas Tempo:</strong> <span x-text="selectedLoan.dueDate"></span></div>
                            <div x-show="selectedLoan.isOverdue" class="text-rose-600 font-bold">
                                Keterlambatan: <span x-text="selectedLoan.overdueDays"></span> hari (Denda: Rp <span x-text="selectedLoan.overdueFine.toLocaleString('id-ID')"></span>)
                            </div>
                        </div>

                        <!-- Kondisi Buku -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pemeriksaan Fisik Buku <span class="text-rose-500">*</span></label>
                            <select name="kondisi_buku" x-model="returnCondition" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600">
                                <option value="Baik">Kondisi Baik (Tidak ada kerusakan - Denda Rp 0)</option>
                                <option value="Rusak Ringan">Rusak Ringan (Lipatan/coretan minor - Denda Rp 10.000)</option>
                                <option value="Rusak Berat">Rusak Berat (Robek/halaman hilang - Denda Rp 50.000)</option>
                            </select>
                        </div>

                        <!-- Denda Tambahan / Kerusakan Lain -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Denda Tambahan Manual (Opsional)</label>
                            <input type="number" name="denda_tambahan" x-model.number="extraFine" min="0" step="1000" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600" placeholder="0">
                        </div>

                        <!-- Catatan Petugas -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Pustakawan</label>
                            <textarea name="catatan_petugas" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600" placeholder="Keterangan pengembalian..."></textarea>
                        </div>

                        <!-- Total Calculation Preview -->
                        <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-200 text-xs text-blue-900 font-semibold flex justify-between items-center">
                            <span>Estimasi Total Denda Dibayar:</span>
                            <span class="text-sm font-extrabold text-blue-700">
                                Rp <span x-text="(
                                    (selectedLoan.isOverdue ? selectedLoan.overdueFine : 0) +
                                    (returnCondition === 'Rusak Berat' ? 50000 : (returnCondition === 'Rusak Ringan' ? 10000 : 0)) +
                                    (extraFine || 0)
                                ).toLocaleString('id-ID')"></span>
                            </span>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="returnModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-xs">
                                Selesaikan Pengembalian & Tambah Stok
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>

    <!-- MODAL 2: DIRECT WALK-IN LOAN -->
    <div x-show="directLoanModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/50" @click="directLoanModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Peminjaman Langsung di Meja Sirkulasi</h3>
                    <button @click="directLoanModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form action="{{ route('admin.loans.direct') }}" method="POST" class="mt-4 space-y-4">
                    @csrf

                    <!-- Pilih Anggota -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Anggota (Siswa / Guru) <span class="text-rose-500">*</span></label>
                        <select name="user_id" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600">
                            <option value="">-- Pilih Anggota --</option>
                            @foreach($members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ ucfirst($m->role) }} - {{ $m->username }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Buku -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Buku <span class="text-rose-500">*</span></label>
                        <select name="book_id" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600">
                            <option value="">-- Pilih Buku Tersedia --</option>
                            @foreach($books as $bk)
                            <option value="{{ $bk->id }}">{{ $bk->judul }} (Stok: {{ $bk->stok }} eks)</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Durasi Pinjam -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Durasi Pinjam (Hari)</label>
                        <input type="number" name="durasi_hari" value="7" min="1" max="30" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600">
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Peminjaman</label>
                        <input type="text" name="catatan" value="Peminjaman langsung di meja sirkulasi" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="directLoanModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-xs">
                            Simpan Peminjaman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 3: REJECT REQUEST -->
    <div x-show="rejectModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/50" @click="rejectModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Tolak Permintaan Peminjaman</h3>
                    <button @click="rejectModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <template x-if="selectedRequest">
                    <form :action="'{{ url('admin/request') }}/' + selectedRequest.id + '/tolak'" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div class="text-xs text-slate-600">
                            Tolak permintaan buku <strong x-text="selectedRequest.title"></strong> oleh <strong x-text="selectedRequest.user"></strong>?
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                            <textarea name="alasan_tolak" required rows="3" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-rose-500" placeholder="Misal: Stok buku fisik sedang dipreservasi atau kuota pinjam siswa telah melampaui batas..."></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="rejectModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold rounded-xl bg-rose-600 text-white hover:bg-rose-700 shadow-xs">
                                Konfirmasi Tolak
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection
