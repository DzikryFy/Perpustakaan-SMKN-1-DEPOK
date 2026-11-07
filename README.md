# Perpustakaan Digital SMKN 1 Depok (E-Library OneDek)

Aplikasi Sistem Informasi dan Layanan Perpustakaan Digital Resmi **SMK Negeri 1 Depok**. Dibangun ulang secara modern menggunakan **Laravel 12**, **Tailwind CSS v4**, dan **Vite** dengan tema akademis yang bersih, terang, dan responsif.

---

## 🌟 Fitur Utama

### 1. Portal Publik & Anggota (Siswa & Guru)
- **Katalog Buku & Bahan Pustaka BOS:** Pencarian instan (judul, nomor BOS, pengarang, penerbit), filter kategori (Umum, Kejuruan, Sastra, Religi, Motivasi), dan indikator ketersediaan stok fisik.
- **Peminjaman Terstruktur:** Alur permohonan pinjam resmi dengan notifikasi peninjauan pustakawan.
- **Pinjaman Saya & Hitung Denda:** Informasi batas jatuh tempo (7 hari) dan estimasi denda keterlambatan otomatis (Rp 1.000 / hari).
- **Usulan Buku Baru:** Siswa dan guru dapat mengajukan usulan buku bacaan baru untuk ditinjau oleh Kepala Perpustakaan.
- **Galeri Fasilitas Resmi:** Menampilkan foto asli area perpustakaan SMKN 1 Depok (Taman Baca, Rak Buku, Pojok Majalah, Tata Tertib).

### 2. Panel Sirkulasi Pustakawan & Kepala Perpustakaan
- **Dashboard Ringkasan Eksekutif:** Grafik tren peminjaman bulanan (Chart.js), metrik total koleksi, buku terpopuler, dan kas denda.
- **Meja Sirkulasi Digital:**
  - Persetujuan (Approve) & Penolakan (Reject) permintaan peminjaman.
  - Pencatatan peminjaman langsung untuk transaksi tatap muka di meja sirkulasi.
  - Formulir sirkulasi pengembalian buku dengan evaluasi kondisi fisik (Baik, Rusak Ringan Rp 10.000, Rusak Berat Rp 50.000) dan penyesuaian stok otomatis.
- **Katalog Buku:** Manajemen CRUD buku dengan unggah foto sampul.
- **Review Usulan Buku:** Kepala Perpustakaan dan pustakawan dapat mengubah status usulan (Ditinjau, Disetujui untuk pengadaan, Ditolak) disertai catatan evaluasi.
- **Laporan & Format Cetak Resmi:** Laporan sirkulasi periodik dengan Kop Surat resmi SMKN 1 Depok siap cetak A4.
- **Kelola Pengguna:** Administrasi akun anggota dan staf perpustakaan.

---

## 🔑 Akun Demo Bawaan

| Role | Username | Password | Keterangan |
|---|---|---|---|
| **Pustakawan** | `pustakawan` | `password` | Kelola sirkulasi, buku, dan anggota |
| **Kepala Perpustakaan** | `kepala` | `password` | Review usulan buku & monitoring eksekutif |
| **Siswa** | `putra` | `password` | Peminjaman buku & pengajuan usulan |
| **Guru** | `dzikry` | `password` | Peminjaman buku & pengajuan usulan |
| **Guru** | `hilda` | `password` | Peminjaman buku & pengajuan usulan |

---

## 🚀 Panduan Menjalankan Aplikasi

### Persyaratan Sistem
- PHP >= 8.2 (Direkomendasikan PHP 8.3 / 8.4 di Laragon)
- Composer
- Node.js & NPM

### Langkah Menjalankan:
1. Pastikan dependensi telah terpasang:
   ```bash
   composer install
   npm install
   ```
2. Build aset frontend (Tailwind CSS + Vite):
   ```bash
   npm run build
   ```
3. Jalankan migrasi dan seeder awal:
   ```bash
   php artisan migrate --seed
   ```
4. Jalankan development server:
   ```bash
   php artisan serve
   ```
   Buka peramban di `http://127.0.0.1:8000` atau akses via virtual host Laragon di `http://perpussmk.test`.

---

## 📂 Struktur Arsip Versi Native
Kode sumber PHP native terdahulu tersimpan aman di dalam:
- Folder lokal: `_legacy/`
- Branch Git: `legacy-native`
