<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BorrowRequest;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $category = $request->query('kategori');
        $availableOnly = $request->boolean('tersedia');

        $query = Book::query()
            ->search($search)
            ->category($category);

        if ($availableOnly) {
            $query->where('stok', '>', 0);
        }

        $books = $query->orderBy('judul', 'asc')->paginate(12)->withQueryString();

        $categories = Book::select('kategori')
            ->distinct()
            ->whereNotNull('kategori')
            ->orderBy('kategori')
            ->pluck('kategori');

        $categoryCounts = Book::selectRaw('kategori, count(*) as total')
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        return view('catalog.index', compact('books', 'categories', 'categoryCounts', 'search', 'category', 'availableOnly'));
    }

    public function show(Book $book): View
    {
        $user = Auth::user();
        $hasActiveLoan = false;
        $hasPendingRequest = false;

        if ($user) {
            $hasActiveLoan = Loan::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->where('status', 'dipinjam')
                ->exists();

            $hasPendingRequest = BorrowRequest::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->where('status', 'menunggu')
                ->exists();
        }

        $relatedBooks = Book::where('kategori', $book->kategori)
            ->where('id', '!=', $book->id)
            ->take(4)
            ->get();

        return view('catalog.show', compact('book', 'hasActiveLoan', 'hasPendingRequest', 'relatedBooks'));
    }
}
