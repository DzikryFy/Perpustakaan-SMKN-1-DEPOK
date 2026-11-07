<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookSuggestion;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        $defaultPassword = Hash::make('password');

        $pustakawan = User::updateOrCreate(
            ['username' => 'pustakawan'],
            [
                'name' => 'Pustakawan',
                'email' => 'pustakawan@smkn1depok.sch.id',
                'password' => $defaultPassword,
                'role' => 'pustakawan',
            ]
        );

        $kepala = User::updateOrCreate(
            ['username' => 'kepala'],
            [
                'name' => 'Kepala Perpustakaan',
                'email' => 'kepala@smkn1depok.sch.id',
                'password' => $defaultPassword,
                'role' => 'kepala_perpustakaan',
            ]
        );

        $putra = User::updateOrCreate(
            ['username' => 'putra'],
            [
                'name' => 'Putra Miftah',
                'email' => 'putra@smkn1depok.sch.id',
                'password' => $defaultPassword,
                'role' => 'siswa',
            ]
        );

        $dzikry = User::updateOrCreate(
            ['username' => 'dzikry'],
            [
                'name' => 'Dzikry Fikry',
                'email' => 'dzikry@smkn1depok.sch.id',
                'password' => $defaultPassword,
                'role' => 'guru',
            ]
        );

        $hilda = User::updateOrCreate(
            ['username' => 'hilda'],
            [
                'name' => 'Bu Hilda',
                'email' => 'hilda@smkn1depok.sch.id',
                'password' => $defaultPassword,
                'role' => 'guru',
            ]
        );

        // 2. Books
        $booksData = [
            ['no_bos' => null, 'judul' => 'Laskar Pelangi', 'jenis_barang' => 'Koleksi Umum', 'kategori' => 'Sastra', 'penerbit' => 'Bentang Pustaka', 'stok' => 3, 'tahun_cetak' => 2008, 'asal_perolehan' => 'Koleksi Awal'],
            ['no_bos' => null, 'judul' => 'Atomic Habits', 'jenis_barang' => 'Koleksi Umum', 'kategori' => 'Motivasi', 'penerbit' => 'Gramedia', 'stok' => 2, 'tahun_cetak' => 2019, 'asal_perolehan' => 'Koleksi Awal'],
            ['no_bos' => null, 'judul' => 'Al-Quran dan Terjemahan', 'jenis_barang' => 'Koleksi Umum', 'kategori' => 'Religi', 'penerbit' => 'Kemenag RI', 'stok' => 5, 'tahun_cetak' => 2020, 'asal_perolehan' => 'Koleksi Awal'],
            ['no_bos' => null, 'judul' => 'Cara Berpikir Kritis', 'jenis_barang' => 'Koleksi Umum', 'kategori' => 'Umum', 'penerbit' => 'Elex Media', 'stok' => 4, 'tahun_cetak' => 2021, 'asal_perolehan' => 'Koleksi Awal'],
            ['no_bos' => null, 'judul' => 'Bumi Manusia', 'jenis_barang' => 'Koleksi Umum', 'kategori' => 'Sastra', 'penerbit' => 'Lentera Dipantara', 'stok' => 2, 'tahun_cetak' => 2015, 'asal_perolehan' => 'Koleksi Awal'],
            ['no_bos' => null, 'judul' => 'The Power of Now', 'jenis_barang' => 'Koleksi Umum', 'kategori' => 'Motivasi', 'penerbit' => 'Gramedia', 'stok' => 3, 'tahun_cetak' => 2018, 'asal_perolehan' => 'Koleksi Awal'],
            ['no_bos' => null, 'judul' => 'Matematika Kelas X', 'jenis_barang' => 'Koleksi Umum', 'kategori' => 'Umum', 'penerbit' => 'Kemdikbud', 'stok' => 6, 'tahun_cetak' => 2022, 'asal_perolehan' => 'Koleksi Awal'],
            ['no_bos' => null, 'judul' => 'Fikih Kontemporer', 'jenis_barang' => 'Koleksi Umum', 'kategori' => 'Religi', 'penerbit' => 'Gema Insani', 'stok' => 2, 'tahun_cetak' => 2020, 'asal_perolehan' => 'Koleksi Awal'],
            ['no_bos' => '5439', 'judul' => 'English For Business', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Erlangga', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5440', 'judul' => 'Informatika', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Erlangga', 'stok' => 100, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5441', 'judul' => 'Bahasa Inggris Untuk Frontliner', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5442', 'judul' => 'Industri Perhotelan', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5443', 'judul' => 'Tata Graha (HouseKeeping)', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5444', 'judul' => 'Laundry Attendant', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5445', 'judul' => 'Chef Pastry Anda Bakker', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5446', 'judul' => 'Essensi Bisnis Digital', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5447', 'judul' => 'Essensi Akuntansi', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5448', 'judul' => 'Essensi Akuntansi (Edisi 2)', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5449', 'judul' => 'Koding & Kecerdasan Artificial (KA)', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 62, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5450', 'judul' => 'Essensi Bisnis Digital (Edisi 2)', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5451', 'judul' => 'Pendidikan Pancasila 3', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Yudhistira', 'stok' => 100, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5452', 'judul' => 'Pendidikan Pancasila 2', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Yudhistira', 'stok' => 100, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5453', 'judul' => 'Matematika 2', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Yudhistira', 'stok' => 100, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5454', 'judul' => 'Matematika 3', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Yudhistira', 'stok' => 100, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5455', 'judul' => 'Koding & Kecerdasan Artificial', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Umum', 'penerbit' => 'Yudhistira', 'stok' => 100, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5456', 'judul' => 'Meneladani Kepahlawanan Wanita', 'jenis_barang' => 'Lain-Lain Bahan Perpustakaan Tercetak Lainnya', 'kategori' => 'Motivasi', 'penerbit' => 'Yudhistira', 'stok' => 2, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5437', 'judul' => 'Koding dan Kecerdasan Artifisial', 'jenis_barang' => 'Buku Teks Pendamping', 'kategori' => 'Umum', 'penerbit' => 'Intan Prawira Edukasi', 'stok' => 100, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS Kin'],
            ['no_bos' => '5438', 'judul' => 'Koding dan Kecerdasan Artifisial (2)', 'jenis_barang' => 'Buku Teks Pendamping', 'kategori' => 'Umum', 'penerbit' => 'Intan Prawira Edukasi', 'stok' => 100, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS Kin'],
            ['no_bos' => '5457', 'judul' => 'Esensi Teknik Kendaraan Ringan Kelas 11', 'jenis_barang' => 'Ilmu Pengetahuan Umum', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5458', 'judul' => 'Esensi Teknik Kendaraan Ringan Kelas 12', 'jenis_barang' => 'Ilmu Pengetahuan Umum', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5459', 'judul' => 'Esensi Teknik Sepeda Motor Kelas 11', 'jenis_barang' => 'Ilmu Pengetahuan Umum', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5460', 'judul' => 'Esensi Teknik Sepeda Motor Kelas 12', 'jenis_barang' => 'Ilmu Pengetahuan Umum', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5461', 'judul' => 'Esensi Rekayasa Perangkat Lunak Kelas 11', 'jenis_barang' => 'Ilmu Pengetahuan Umum', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5462', 'judul' => 'Esensi Rekayasa Perangkat Lunak Kelas 12', 'jenis_barang' => 'Ilmu Pengetahuan Umum', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5463', 'judul' => 'Esensi Desain Komunikasi Visual Kelas 11', 'jenis_barang' => 'Ilmu Pengetahuan Umum', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5464', 'judul' => 'Esensi Desain Komunikasi Visual Kelas 12', 'jenis_barang' => 'Ilmu Pengetahuan Umum', 'kategori' => 'Umum', 'penerbit' => 'Mediatama', 'stok' => 50, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
            ['no_bos' => '5465', 'judul' => 'Meretas Keterbatasan', 'jenis_barang' => 'Ilmu Pengetahuan Umum', 'kategori' => 'Motivasi', 'penerbit' => 'INDOCAMP', 'stok' => 1, 'tahun_cetak' => 2025, 'asal_perolehan' => 'BOS'],
        ];

        foreach ($booksData as $b) {
            Book::create($b);
        }

        // 3. Sample Loan data for demonstration
        $b1 = Book::where('judul', 'Laskar Pelangi')->first();
        if ($b1) {
            Loan::create([
                'book_id' => $b1->id,
                'user_id' => $putra->id,
                'tanggal_pinjam' => Carbon::now()->subDays(3)->toDateString(),
                'batas_kembali' => Carbon::now()->addDays(4)->toDateString(),
                'status' => 'dipinjam',
                'catatan' => 'Dipinjam untuk tugas literasi',
            ]);
        }

        // 4. Sample Book Suggestion
        BookSuggestion::create([
            'user_id' => $putra->id,
            'judul' => 'Filosofi Teras',
            'penulis' => 'Henry Manampiring',
            'penerbit' => 'Kompas',
            'alasan' => 'Buku yang sangat bagus untuk pembentukan karakter dan ketahanan mental siswa SMK.',
            'tanggal_saran' => Carbon::now()->subDays(2)->toDateString(),
            'status' => 'baru',
        ]);
    }
}
