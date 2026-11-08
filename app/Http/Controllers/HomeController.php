<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $totalTitles = Book::count();
        $totalStock = Book::sum('stok');
        $totalMembers = User::whereIn('role', ['siswa', 'guru'])->count();
        $activeLoans = Loan::where('status', 'dipinjam')->count();

        // Spotlight Book (Buku Sorotan Pustakawan ala Malaka Books)
        $spotlightBook = Book::where('judul', 'LIKE', '%Koding & Kecerdasan Artificial%')
            ->orWhere('judul', 'LIKE', '%Atomic Habits%')
            ->first() ?? Book::first();

        // Koleksi Unggulan & Terpopuler (Buku dengan stok aktif)
        $featuredBooks = Book::where('stok', '>', 0)
            ->latest('id')
            ->take(8)
            ->get();

        // Koleksi Kejuruan SMK (BOS)
        $vocationalBooks = Book::whereNotNull('no_bos')
            ->where('stok', '>', 0)
            ->take(6)
            ->get();

        // Fasilitas Nyata Perpustakaan SMKN 1 Depok
        $facilities = [
            [
                'title' => 'Taman Baca Terbuka',
                'desc' => 'Area luar ruangan yang asri dan tenang di samping gedung perpustakaan untuk membaca mandiri maupun berdiskusi.',
                'image' => asset('images/taman-baca-1.jpg'),
            ],
            [
                'title' => 'Koleksi Rak Buku Utama',
                'desc' => 'Penyimpanan buku pelajaran umum, buku teks kejuruan BOS, dan literatur referensi yang tersusun sistematis.',
                'image' => asset('images/rak-buku-1.jpg'),
            ],
            [
                'title' => 'Pojok Majalah & Terbitan Berkala',
                'desc' => 'Menyediakan buletin sekolah, majalah kejuruan, dan bacaan berkala untuk memperluas wawasan industri.',
                'image' => asset('images/rak-majalah.jpg'),
            ],
            [
                'title' => 'Gazebo Belajar & Diskusi',
                'desc' => 'Fasilitas belajar kelompok semi-terbuka yang nyaman bagi siswa menyelesaikan tugas literasi.',
                'image' => asset('images/taman-baca-2.jpg'),
            ],
            [
                'title' => 'Meja Layanan Sirkulasi',
                'desc' => 'Pelayanan peminjaman, pengembalian, perpanjangan buku, dan konsultasi referensi oleh staf perpustakaan.',
                'image' => asset('images/pintu-masuk.jpg'),
            ],
            [
                'title' => 'Plaza Lingkungan Perpustakaan',
                'desc' => 'Kawasan hijau yang terhubung langsung dengan koridor utama kegiatan belajar mengajar SMKN 1 Depok.',
                'image' => asset('images/area-luar.jpg'),
            ],
        ];

        return view('home', compact(
            'totalTitles',
            'totalStock',
            'totalMembers',
            'activeLoans',
            'spotlightBook',
            'featuredBooks',
            'vocationalBooks',
            'facilities'
        ));
    }
}
