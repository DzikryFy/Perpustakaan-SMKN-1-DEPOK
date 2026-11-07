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

        $featuredBooks = Book::where('stok', '>', 0)
            ->latest('id')
            ->take(8)
            ->get();

        $facilities = [
            [
                'title' => 'Taman Baca Literasi',
                'desc' => 'Area santai terbuka untuk membaca buku dengan suasana asri dan nyaman.',
                'image' => asset('images/taman-baca-1.jpg'),
                'tag' => 'Outdoor Zone',
            ],
            [
                'title' => 'Koleksi Rak Buku Terpadu',
                'desc' => 'Ribuan buku pelajaran, referensi kejuruan, dan sastra tersusun rapi menurut klasifikasi.',
                'image' => asset('images/rak-buku-1.jpg'),
                'tag' => 'Koleksi Utama',
            ],
            [
                'title' => 'Area Pojok Majalah & Surat Kabar',
                'desc' => 'Informasi terbitan berkala, majalah edukatif, dan wawasan industri terkini.',
                'image' => asset('images/rak-majalah.jpg'),
                'tag' => 'Periodicals',
            ],
            [
                'title' => 'Gazebo Belajar & Diskusi',
                'desc' => 'Ruang kolaborasi yang dirancang untuk belajar kelompok dan diskusi tugas literasi.',
                'image' => asset('images/taman-baca-2.jpg'),
                'tag' => 'Study Zone',
            ],
            [
                'title' => 'Pintu Masuk & Meja Sirkulasi',
                'desc' => 'Layanan peminjaman, pengembalian, dan bantuan pustakawan yang ramah.',
                'image' => asset('images/pintu-masuk.jpg'),
                'tag' => 'Sirkulasi',
            ],
            [
                'title' => 'Plaza Luar Perpustakaan',
                'desc' => 'Akses penghubung langsung ke gedung utama SMKN 1 Depok.',
                'image' => asset('images/area-luar.jpg'),
                'tag' => 'Lingkungan',
            ],
        ];

        return view('home', compact(
            'totalTitles',
            'totalStock',
            'totalMembers',
            'activeLoans',
            'featuredBooks',
            'facilities'
        ));
    }
}
