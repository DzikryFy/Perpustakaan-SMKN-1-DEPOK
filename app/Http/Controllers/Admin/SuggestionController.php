<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookSuggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SuggestionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $query = BookSuggestion::with(['user', 'reviewer'])->latest('id');

        if ($status && in_array($status, ['baru', 'ditinjau', 'disetujui', 'ditolak'])) {
            $query->where('status', $status);
        }

        $suggestions = $query->paginate(15)->withQueryString();

        $counts = [
            'all' => BookSuggestion::count(),
            'baru' => BookSuggestion::where('status', 'baru')->count(),
            'ditinjau' => BookSuggestion::where('status', 'ditinjau')->count(),
            'disetujui' => BookSuggestion::where('status', 'disetujui')->count(),
            'ditolak' => BookSuggestion::where('status', 'ditolak')->count(),
        ];

        return view('admin.suggestions.index', compact('suggestions', 'status', 'counts'));
    }

    public function updateStatus(Request $request, BookSuggestion $bookSuggestion): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:baru,ditinjau,disetujui,ditolak'],
            'catatan_review' => ['nullable', 'string', 'max:500'],
        ]);

        $bookSuggestion->update([
            'status' => $validated['status'],
            'catatan_review' => $validated['catatan_review'] ?? null,
            'reviewed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Status usulan buku berhasil diperbarui menjadi: ' . ucfirst($validated['status']));
    }
}
