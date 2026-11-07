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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $totalBooks = Book::count();
        $totalStock = Book::sum('stok');
        $totalMembers = User::whereIn('role', ['siswa', 'guru'])->count();
        $totalLoans = Loan::count();
        $activeLoans = Loan::where('status', 'dipinjam')->count();
        $totalReturns = BookReturn::count();
        $totalFines = BookReturn::sum('total_denda');

        $overdueLoansList = Loan::with(['book', 'user'])
            ->where('status', 'dipinjam')
            ->whereDate('batas_kembali', '<', Carbon::today())
            ->orderBy('batas_kembali', 'asc')
            ->get();

        $popularBooks = Book::withCount('loans')
            ->orderByDesc('loans_count')
            ->take(8)
            ->get();

        $activeBorrowers = User::whereIn('role', ['siswa', 'guru'])
            ->withCount('loans')
            ->orderByDesc('loans_count')
            ->take(8)
            ->get();

        $categories = Book::select('kategori', DB::raw('count(*) as count'), DB::raw('sum(stok) as stock'))
            ->groupBy('kategori')
            ->orderByDesc('count')
            ->get();

        // 12 Months Trend
        $chartLabels = [];
        $chartValues = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i);
            $chartLabels[] = $d->translatedFormat('M Y');
            $chartValues[] = Loan::whereYear('tanggal_pinjam', $d->year)
                ->whereMonth('tanggal_pinjam', $d->month)
                ->count();
        }

        return view('admin.reports.index', compact(
            'totalBooks',
            'totalStock',
            'totalMembers',
            'totalLoans',
            'activeLoans',
            'totalReturns',
            'totalFines',
            'overdueLoansList',
            'popularBooks',
            'activeBorrowers',
            'categories',
            'chartLabels',
            'chartValues'
        ));
    }

    public function print(Request $request): View
    {
        $period = $request->query('periode', 'bulan_ini');

        $query = Loan::with(['book', 'user', 'bookReturn']);

        if ($period === 'bulan_ini') {
            $query->whereYear('tanggal_pinjam', Carbon::now()->year)
                  ->whereMonth('tanggal_pinjam', Carbon::now()->month);
            $periodeLabel = Carbon::now()->translatedFormat('F Y');
        } elseif ($period === 'tahun_ini') {
            $query->whereYear('tanggal_pinjam', Carbon::now()->year);
            $periodeLabel = 'Tahun ' . Carbon::now()->year;
        } else {
            $periodeLabel = 'Semua Periode';
        }

        $loans = $query->latest('tanggal_pinjam')->get();

        $totalPinjam = $loans->count();
        $totalKembali = $loans->where('status', 'dikembalikan')->count();
        $totalAktif = $loans->where('status', 'dipinjam')->count();
        $totalDenda = BookReturn::whereIn('loan_id', $loans->pluck('id'))->sum('total_denda');

        $kepala = User::where('role', 'kepala_perpustakaan')->first();

        return view('admin.reports.print', compact(
            'loans',
            'periodeLabel',
            'totalPinjam',
            'totalKembali',
            'totalAktif',
            'totalDenda',
            'kepala'
        ));
    }
}
