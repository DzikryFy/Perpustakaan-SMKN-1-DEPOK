@extends('layouts.admin')

@section('title', 'Usulan Buku Masuk | Panel Kelola')
@section('page_title', 'Peninjauan Usulan Buku')
@section('page_subtitle', 'Tinjau rekomendasi bahan pustaka dari siswa dan guru untuk pengadaan koleksi baru')

@section('content')
<div class="space-y-6" x-data="{
    reviewModal: false,
    selectedSuggestion: null,
    statusVal: 'ditinjau',
    noteVal: '',
    openReviewModal(s) {
        this.selectedSuggestion = s;
        this.statusVal = s.status;
        this.noteVal = s.note || '';
        this.reviewModal = true;
    }
}">
    
    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2 overflow-x-auto text-xs font-semibold">
        <a href="{{ route('admin.suggestions.index') }}" 
           class="px-3.5 py-1.5 rounded-xl transition {{ !$status ? 'bg-blue-600 text-white shadow-2xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
            Semua ({{ $counts['all'] }})
        </a>
        <a href="{{ route('admin.suggestions.index', ['status' => 'baru']) }}" 
           class="px-3.5 py-1.5 rounded-xl transition flex items-center gap-1.5 {{ $status === 'baru' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
            <span>Baru Masuk</span>
            @if($counts['baru'] > 0)
            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $status === 'baru' ? 'bg-white/20 text-white' : 'bg-amber-500 text-white' }}">{{ $counts['baru'] }}</span>
            @endif
        </a>
        <a href="{{ route('admin.suggestions.index', ['status' => 'ditinjau']) }}" 
           class="px-3.5 py-1.5 rounded-xl transition {{ $status === 'ditinjau' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
            Sedang Ditinjau ({{ $counts['ditinjau'] }})
        </a>
        <a href="{{ route('admin.suggestions.index', ['status' => 'disetujui']) }}" 
           class="px-3.5 py-1.5 rounded-xl transition {{ $status === 'disetujui' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
            Disetujui Pengadaan ({{ $counts['disetujui'] }})
        </a>
        <a href="{{ route('admin.suggestions.index', ['status' => 'ditolak']) }}" 
           class="px-3.5 py-1.5 rounded-xl transition {{ $status === 'ditolak' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
            Ditolak ({{ $counts['ditolak'] }})
        </a>
    </div>

    <!-- Suggestions Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Daftar Usulan Koleksi Anggota</h3>
            <span class="text-xs text-slate-500 font-medium">{{ $suggestions->total() }} total</span>
        </div>

        @if($suggestions->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Pengusul</th>
                        <th class="px-5 py-3">Judul Buku & Penulis</th>
                        <th class="px-5 py-3">Alasan Pengajuan</th>
                        <th class="px-5 py-3">Tgl Usulan</th>
                        <th class="px-5 py-3">Status Saat Ini</th>
                        <th class="px-5 py-3 text-right">Review</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($suggestions as $sug)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900">{{ $sug->user->name }}</div>
                            <span class="text-[10px] text-slate-400 capitalize">{{ $sug->user->role }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-800">{{ $sug->judul }}</div>
                            <span class="text-[10px] text-slate-500">Penulis: {{ $sug->penulis ?? '-' }} &bull; Penerbit: {{ $sug->penerbit ?? '-' }}</span>
                        </td>
                        <td class="px-5 py-3.5 max-w-xs">
                            <p class="truncate italic text-slate-600" title="{{ $sug->alasan }}">
                                "{{ $sug->alasan ?? 'Tidak ada alasan' }}"
                            </p>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500">
                            {{ $sug->tanggal_saran->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $sug->status_badge['class'] }}">
                                {{ $sug->status_badge['label'] }}
                            </span>
                            @if($sug->reviewer)
                            <div class="text-[10px] text-slate-400 mt-0.5">Oleh: {{ $sug->reviewer->name }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <button type="button" @click="openReviewModal({
                                id: {{ $sug->id }},
                                title: '{{ addslashes($sug->judul) }}',
                                user: '{{ addslashes($sug->user->name) }}',
                                status: '{{ $sug->status }}',
                                note: '{{ addslashes($sug->catatan_review ?? '') }}'
                            })" class="px-3 py-1.5 rounded-lg bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition shadow-2xs">
                                <i class="bi bi-pencil-square me-1"></i> Update Status
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $suggestions->links() }}
        </div>
        @else
        <div class="p-10 text-center text-slate-400 text-xs">
            Tidak ada usulan buku pada kategori filter ini.
        </div>
        @endif
    </div>

    <!-- REVIEW MODAL -->
    <div x-show="reviewModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/50" @click="reviewModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Review Usulan Buku</h3>
                    <button @click="reviewModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <template x-if="selectedSuggestion">
                    <form :action="'{{ url('admin/saran') }}/' + selectedSuggestion.id" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                            <div class="font-bold text-slate-900" x-text="selectedSuggestion.title"></div>
                            <div class="text-slate-500 mt-0.5">Diusulkan oleh: <strong x-text="selectedSuggestion.user"></strong></div>
                        </div>

                        <!-- Status Selection -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Status Keputusan <span class="text-rose-500">*</span></label>
                            <select name="status" x-model="statusVal" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600">
                                <option value="baru">Baru (Belum Ditindaklanjuti)</option>
                                <option value="ditinjau">Ditinjau (Sedang Dipelajari Tim Literasi)</option>
                                <option value="disetujui">Disetujui (Masuk Rencana Pengadaan BOS / Perpustakaan)</option>
                                <option value="ditolak">Ditolak (Koleksi Kurang Relevan / Telah Tersedia)</option>
                            </select>
                        </div>

                        <!-- Review Note -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Evaluasi / Alasan Keputusan</label>
                            <textarea name="catatan_review" x-model="noteVal" rows="3" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600" placeholder="Berikan catatan singkat yang dapat dibaca oleh pengusul..."></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="reviewModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-xs">
                                Simpan Status Review
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
