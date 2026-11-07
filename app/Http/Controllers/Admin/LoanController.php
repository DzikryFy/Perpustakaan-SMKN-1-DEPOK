<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookReturn;
use App\Models\BorrowRequest;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'requests');

        // Requests pending
        $requests = BorrowRequest::with(['book', 'user'])
            ->where('status', 'menunggu')
            ->orderBy('id', 'asc')
            ->get();

        // Active loans
        $activeQuery = Loan::with(['book', 'user'])
            ->where('status', 'dipinjam');

        if ($search = $request->query('q')) {
            $activeQuery->where(function ($q) use ($search) {
                $q->whereHas('book', fn ($b) => $b->where('judul', 'like', "%{$search}%"))
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%"));
            });
        }

        $activeLoans = $activeQuery->orderBy('batas_kembali', 'asc')->paginate(15, ['*'], 'active_page');

        // History
        $historyLoans = BookReturn::with(['loan', 'book', 'user', 'petugas'])
            ->latest('id')
            ->paginate(15, ['*'], 'history_page');

        $books = Book::where('stok', '>', 0)->orderBy('judul')->get();
        $members = User::whereIn('role', ['siswa', 'guru'])->orderBy('name')->get();

        return view('admin.loans.index', compact('requests', 'activeLoans', 'historyLoans', 'tab', 'books', 'members'));
    }

    public function approveRequest(BorrowRequest $borrowRequest): RedirectResponse
    {
        if ($borrowRequest->status !== 'menunggu') {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $book = $borrowRequest->book;
        if ($book->stok < 1) {
            return back()->with('error', 'Stok buku tidak mencukupi untuk disetujui.');
        }

        DB::transaction(function () use ($borrowRequest, $book) {
            $loan = Loan::create([
                'book_id' => $book->id,
                'user_id' => $borrowRequest->user_id,
                'borrow_request_id' => $borrowRequest->id,
                'tanggal_pinjam' => Carbon::today(),
                'batas_kembali' => Carbon::today()->addDays(7),
                'status' => 'dipinjam',
                'catatan' => $borrowRequest->catatan,
            ]);

            $book->decrement('stok');

            $borrowRequest->update([
                'status' => 'disetujui',
                'loan_id' => $loan->id,
            ]);
        });

        return back()->with('success', 'Permintaan peminjaman disetujui! Peminjaman aktif dibuat dengan batas pengembalian 7 hari.');
    }

    public function rejectRequest(Request $request, BorrowRequest $borrowRequest): RedirectResponse
    {
        $validated = $request->validate([
            'alasan_tolak' => ['required', 'string', 'max:500'],
        ]);

        $borrowRequest->update([
            'status' => 'ditolak',
            'alasan_tolak' => $validated['alasan_tolak'],
        ]);

        return back()->with('success', 'Permintaan peminjaman telah ditolak.');
    }

    public function createDirectLoan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'book_id' => ['required', 'exists:books,id'],
            'durasi_hari' => ['required', 'integer', 'min:1', 'max:30'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $book = Book::findOrFail($validated['book_id']);
        if ($book->stok < 1) {
            return back()->with('error', 'Stok buku habis.');
        }

        DB::transaction(function () use ($validated, $book) {
            Loan::create([
                'book_id' => $book->id,
                'user_id' => $validated['user_id'],
                'tanggal_pinjam' => Carbon::today(),
                'batas_kembali' => Carbon::today()->addDays((int) $validated['durasi_hari']),
                'status' => 'dipinjam',
                'catatan' => $validated['catatan'] ?? 'Peminjaman langsung di meja sirkulasi',
            ]);

            $book->decrement('stok');
        });

        return redirect()->route('admin.loans.index', ['tab' => 'active'])
            ->with('success', 'Peminjaman langsung berhasil dicatat!');
    }

    public function processReturn(Request $request, Loan $loan): RedirectResponse
    {
        if ($loan->status !== 'dipinjam') {
            return back()->with('error', 'Peminjaman ini sudah selesai atau dikembalikan.');
        }

        $validated = $request->validate([
            'kondisi_buku' => ['required', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'catatan_petugas' => ['nullable', 'string', 'max:500'],
            'denda_tambahan' => ['nullable', 'integer', 'min:0'],
        ]);

        $today = Carbon::today();
        $overdueDays = max(0, $today->diffInDays($loan->batas_kembali, false) * -1);
        $dendaKeterlambatan = (int) $overdueDays * 1000;

        $dendaKerusakan = match ($validated['kondisi_buku']) {
            'Rusak Ringan' => 10000,
            'Rusak Berat' => 50000,
            default => 0,
        };

        $dendaTambahan = (int) ($validated['denda_tambahan'] ?? 0);
        $totalDenda = $dendaKeterlambatan + $dendaKerusakan + $dendaTambahan;

        DB::transaction(function () use ($loan, $validated, $today, $dendaKeterlambatan, $dendaKerusakan, $totalDenda) {
            BookReturn::create([
                'loan_id' => $loan->id,
                'book_id' => $loan->book_id,
                'user_id' => $loan->user_id,
                'petugas_id' => Auth::id(),
                'tanggal_kembali' => $today,
                'kondisi_buku' => $validated['kondisi_buku'],
                'denda_keterlambatan' => $dendaKeterlambatan,
                'denda_kerusakan' => $dendaKerusakan + ($validated['denda_tambahan'] ?? 0),
                'total_denda' => $totalDenda,
                'status_pembayaran_denda' => 'lunas',
                'catatan_petugas' => $validated['catatan_petugas'] ?? null,
            ]);

            $loan->update(['status' => 'dikembalikan']);
            $loan->book->increment('stok');
        });

        $msg = 'Buku "' . $loan->book->judul . '" berhasil dikembalikan.';
        if ($totalDenda > 0) {
            $msg .= ' Total denda tercatat: Rp ' . number_format($totalDenda, 0, ',', '.');
        }

        return redirect()->route('admin.loans.index', ['tab' => 'history'])
            ->with('success', $msg);
    }
}
