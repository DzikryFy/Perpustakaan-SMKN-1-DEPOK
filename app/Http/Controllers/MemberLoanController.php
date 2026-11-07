<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookSuggestion;
use App\Models\BorrowRequest;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MemberLoanController extends Controller
{
    public function myLoans(): View
    {
        $user = Auth::user();

        $activeLoans = Loan::with('book')
            ->where('user_id', $user->id)
            ->where('status', 'dipinjam')
            ->orderBy('batas_kembali', 'asc')
            ->get();

        $pendingRequests = BorrowRequest::with('book')
            ->where('user_id', $user->id)
            ->where('status', 'menunggu')
            ->latest()
            ->get();

        $pastLoans = Loan::with(['book', 'bookReturn'])
            ->where('user_id', $user->id)
            ->where('status', 'dikembalikan')
            ->latest('updated_at')
            ->take(20)
            ->get();

        return view('member.loans', compact('activeLoans', 'pendingRequests', 'pastLoans'));
    }

    public function requestBorrow(Request $request, Book $book): RedirectResponse
    {
        $user = Auth::user();

        if ($book->stok < 1) {
            return back()->with('error', 'Maaf, stok buku ini sedang habis.');
        }

        $activeLoan = Loan::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('status', 'dipinjam')
            ->exists();

        if ($activeLoan) {
            return back()->with('error', 'Anda masih memiliki pinjaman aktif untuk buku ini.');
        }

        $pendingRequest = BorrowRequest::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('status', 'menunggu')
            ->exists();

        if ($pendingRequest) {
            return back()->with('error', 'Anda sudah mengajukan permintaan untuk buku ini. Silakan tunggu persetujuan pustakawan.');
        }

        $validated = $request->validate([
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        BorrowRequest::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'tanggal_request' => Carbon::today(),
            'catatan' => $validated['catatan'] ?? null,
            'status' => 'menunggu',
        ]);

        return redirect()->route('member.loans')
            ->with('success', 'Permintaan peminjaman buku "' . $book->judul . '" berhasil diajukan! Pustakawan akan segera meninjaunya.');
    }

    public function cancelRequest(BorrowRequest $borrowRequest): RedirectResponse
    {
        if ($borrowRequest->user_id !== Auth::id()) {
            abort(403);
        }

        if ($borrowRequest->status !== 'menunggu') {
            return back()->with('error', 'Permintaan ini sudah tidak dalam status menunggu.');
        }

        $borrowRequest->delete();

        return back()->with('success', 'Permintaan peminjaman berhasil dibatalkan.');
    }

    public function mySuggestions(): View
    {
        $user = Auth::user();

        $suggestions = BookSuggestion::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('member.suggestions', compact('suggestions'));
    }

    public function storeSuggestion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'penulis' => ['nullable', 'string', 'max:255'],
            'penerbit' => ['nullable', 'string', 'max:255'],
            'alasan' => ['nullable', 'string', 'max:1000'],
        ], [
            'judul.required' => 'Judul buku wajib diisi.',
        ]);

        BookSuggestion::create([
            'user_id' => Auth::id(),
            'judul' => $validated['judul'],
            'penulis' => $validated['penulis'],
            'penerbit' => $validated['penerbit'],
            'alasan' => $validated['alasan'],
            'tanggal_saran' => Carbon::today(),
            'status' => 'baru',
        ]);

        return redirect()->route('member.suggestions')
            ->with('success', 'Terima kasih! Usulan buku Anda telah dikirim dan akan ditinjau oleh Kepala Perpustakaan.');
    }
}
