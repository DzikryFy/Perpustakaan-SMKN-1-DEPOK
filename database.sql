CREATE TABLE IF NOT EXISTS users (
    id_user  INT AUTO_INCREMENT PRIMARY KEY,
    nama     VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    email    VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role     ENUM('siswa','guru','pustakawan','kepala_perpustakaan') NOT NULL DEFAULT 'siswa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS buku (
    id_buku        INT AUTO_INCREMENT PRIMARY KEY,
    no_bos         VARCHAR(20)  DEFAULT NULL,
    nama_buku      VARCHAR(255) NOT NULL,
    jenis_barang   VARCHAR(255) DEFAULT NULL,
    kategori       VARCHAR(100) NOT NULL,
    penerbit       VARCHAR(100) DEFAULT NULL,
    stock          INT          NOT NULL DEFAULT 0,
    tahun_cetak    YEAR         DEFAULT NULL,
    asal_perolehan VARCHAR(50)  DEFAULT 'BOS',
    gambar         VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS request_peminjaman (
    id_request     INT AUTO_INCREMENT PRIMARY KEY,
    id_buku        INT NOT NULL,
    id_user        INT NOT NULL,
    tanggal_request DATE NOT NULL,
    catatan        TEXT DEFAULT NULL,
    status         ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
    alasan_tolak   TEXT DEFAULT NULL,
    id_pinjam      INT DEFAULT NULL,
    FOREIGN KEY (id_buku) REFERENCES buku(id_buku) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS peminjaman (
    id_pinjam          INT AUTO_INCREMENT PRIMARY KEY,
    id_buku            INT NOT NULL,
    id_user            INT NOT NULL,
    tanggal_peminjaman DATE NOT NULL,
    batas_kembali      DATE NOT NULL,
    nama_buku          VARCHAR(255) NOT NULL,
    status             ENUM('Ada','Tidak Ada') NOT NULL DEFAULT 'Tidak Ada',
    FOREIGN KEY (id_buku) REFERENCES buku(id_buku) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pengembalian (
    id_pengembalian      INT AUTO_INCREMENT PRIMARY KEY,
    id_pinjam            INT NOT NULL,
    id_buku              INT NOT NULL,
    id_user              INT NOT NULL,
    tanggal_pengembalian DATE NOT NULL,
    nama_buku            VARCHAR(255) NOT NULL,
    denda                INT NOT NULL DEFAULT 0,
    kondisi_buku         ENUM('Baik','Rusak Ringan','Rusak Berat') NOT NULL DEFAULT 'Baik',
    catatan_petugas      TEXT DEFAULT NULL,
    status               ENUM('Ada','Tidak Ada') NOT NULL DEFAULT 'Ada',
    FOREIGN KEY (id_pinjam) REFERENCES peminjaman(id_pinjam) ON DELETE CASCADE,
    FOREIGN KEY (id_buku)   REFERENCES buku(id_buku)         ON DELETE CASCADE,
    FOREIGN KEY (id_user)   REFERENCES users(id_user)        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS saran_buku (
    id_saran       INT AUTO_INCREMENT PRIMARY KEY,
    id_user        INT NOT NULL,
    judul_saran    VARCHAR(255) NOT NULL,
    penulis_saran  VARCHAR(255) DEFAULT NULL,
    penerbit_saran VARCHAR(255) DEFAULT NULL,
    alasan         TEXT DEFAULT NULL,
    tanggal_saran  DATE NOT NULL,
    status         ENUM('baru','ditinjau','disetujui','ditolak') NOT NULL DEFAULT 'baru',
    catatan_admin  TEXT DEFAULT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- password semua akun: password
INSERT INTO users (nama,username,email,password,role) VALUES
('Pustakawan','pustakawan','pustakawan@smkn1depok.sch.id','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','pustakawan'),
('Kepala Perpustakaan','kepala','kepala@smkn1depok.sch.id','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','kepala_perpustakaan'),
('Putra Miftah','putra','putra@smkn1depok.sch.id','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','siswa'),
('Dzikry Fikry','dzikry','dzikry@smkn1depok.sch.id','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','guru'),
('Bu Hilda','hilda','hilda@smkn1depok.sch.id','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','guru')
;

INSERT INTO buku (no_bos,nama_buku,jenis_barang,kategori,penerbit,stock,tahun_cetak,asal_perolehan) VALUES
(NULL,'Laskar Pelangi','Koleksi Umum','Sastra',NULL,3,NULL,'Koleksi Awal'),
(NULL,'Atomic Habits','Koleksi Umum','Motivasi',NULL,2,NULL,'Koleksi Awal'),
(NULL,'Al-Quran dan Terjemahan','Koleksi Umum','Religi',NULL,5,NULL,'Koleksi Awal'),
(NULL,'Cara Berpikir Kritis','Koleksi Umum','Umum',NULL,4,NULL,'Koleksi Awal'),
(NULL,'Bumi Manusia','Koleksi Umum','Sastra',NULL,2,NULL,'Koleksi Awal'),
(NULL,'The Power of Now','Koleksi Umum','Motivasi',NULL,3,NULL,'Koleksi Awal'),
(NULL,'Matematika Kelas X','Koleksi Umum','Umum',NULL,6,NULL,'Koleksi Awal'),
(NULL,'Fikih Kontemporer','Koleksi Umum','Religi',NULL,2,NULL,'Koleksi Awal'),
('5439','English For Business','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Erlangga',50,2025,'BOS'),
('5440','Informatika','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Erlangga',100,2025,'BOS'),
('5441','Bahasa Inggris Untuk Frontliner','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Mediatama',50,2025,'BOS'),
('5442','Industri Perhotelan','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Mediatama',50,2025,'BOS'),
('5443','Tata Graha (HouseKeeping)','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Mediatama',50,2025,'BOS'),
('5444','Laundry Attendant','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Mediatama',50,2025,'BOS'),
('5445','Chef Pastry Anda Bakker','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Mediatama',50,2025,'BOS'),
('5446','Essensi Bisnis Digital','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Mediatama',50,2025,'BOS'),
('5447','Essensi Akuntansi','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Mediatama',50,2025,'BOS'),
('5448','Essensi Akuntansi (Edisi 2)','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Mediatama',50,2025,'BOS'),
('5449','Koding & Kecerdasan Artificial (KA)','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Mediatama',62,2025,'BOS'),
('5450','Essensi Bisnis Digital (Edisi 2)','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Mediatama',50,2025,'BOS'),
('5451','Pendidikan Pancasila 3','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Yudhistira',100,2025,'BOS'),
('5452','Pendidikan Pancasila 2','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Yudhistira',100,2025,'BOS'),
('5453','Matematika 2','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Yudhistira',100,2025,'BOS'),
('5454','Matematika 3','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Yudhistira',100,2025,'BOS'),
('5455','Koding & Kecerdasan Artificial','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Umum','Yudhistira',100,2025,'BOS'),
('5456','Meneladani Kepahlawanan Wanita','Lain-Lain Bahan Perpustakaan Tercetak Lainnya','Motivasi','Yudhistira',2,2025,'BOS'),
('5437','Koding dan Kecerdasan Artifisial','Buku Teks Pendamping','Umum','Intan Prawira Edukasi',100,2025,'BOS Kin'),
('5438','Koding dan Kecerdasan Artifisial (2)','Buku Teks Pendamping','Umum','Intan Prawira Edukasi',100,2025,'BOS Kin'),
('5457','Esensi Teknik Kendaraan Ringan Kelas 11','Ilmu Pengetahuan Umum','Umum','Mediatama',50,2025,'BOS'),
('5458','Esensi Teknik Kendaraan Ringan Kelas 12','Ilmu Pengetahuan Umum','Umum','Mediatama',50,2025,'BOS'),
('5459','Esensi Teknik Sepeda Motor Kelas 11','Ilmu Pengetahuan Umum','Umum','Mediatama',50,2025,'BOS'),
('5460','Esensi Teknik Sepeda Motor Kelas 12','Ilmu Pengetahuan Umum','Umum','Mediatama',50,2025,'BOS'),
('5461','Esensi Rekayasa Perangkat Lunak Kelas 11','Ilmu Pengetahuan Umum','Umum','Mediatama',50,2025,'BOS'),
('5462','Esensi Rekayasa Perangkat Lunak Kelas 12','Ilmu Pengetahuan Umum','Umum','Mediatama',50,2025,'BOS'),
('5463','Esensi Desain Komunikasi Visual Kelas 11','Ilmu Pengetahuan Umum','Umum','Mediatama',50,2025,'BOS'),
('5464','Esensi Desain Komunikasi Visual Kelas 12','Ilmu Pengetahuan Umum','Umum','Mediatama',50,2025,'BOS'),
('5465','Meretas Keterbatasan','Ilmu Pengetahuan Umum','Motivasi','INDOCAMP',1,2025,'BOS');
