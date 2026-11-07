<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookReturn;
use App\Models\BookSuggestion;
use App\Models\BorrowRequest;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalBooks = Book::count();
        $totalStock = Book::sum('stok');
        $totalMembers = User::whereIn('role', ['siswa', 'guru'])->count();
        $activeLoans = Loan::where('status', 'dipinjam')->count();
        $totalReturns = BookReturn::count();
        $pendingRequests = BorrowRequest::where('status', 'menunggu')->count();
        $pendingSuggestions = BookSuggestion::where('status', 'baru')->count();
        $totalFines = BookReturn::sum('total_denda');

        $overdueLoans = Loan::where('status', 'dipinjam')
            ->whereDate('batas_kembali', '<', Carbon::today())
            ->count();

        // Recent loans with relations
        $recentLoans = Loan::with(['book', 'user'])
            ->latest('id')
            ->take(8)
            ->get();

        // Most borrowed books
        $popularBooks = Book::withCount('loans')
            ->orderByDesc('loans_count')
            ->take(5)
            ->get();

        // Monthly trends for last 6 months
        $months = [];
        $trendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->translatedFormat('M Y');
            $months[] = $monthName;

            $count = Loan::whereYear('tanggal_pinjam', $date->year)
                ->whereMonth('tanggal_pinjam', $date->month)
                ->count();
            $trendData[] = $count;
        }

        // Suggestions pending for quick review
        $quickSuggestions = BookSuggestion::with('user')
            ->where('status', 'baru')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalBooks',
            'totalStock',
            'totalMembers',
            'activeLoans',
            'totalReturns',
            'pendingRequests',
            'pendingSuggestions',
            'totalFines',
            'overdueLoans',
            'recentLoans',
            'popularBooks',
            'months',
            'trendData',
            'quickSuggestions'
        ));
    }
}
