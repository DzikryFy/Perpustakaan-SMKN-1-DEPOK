<?php
session_start();
require_once 'config/database.php';
$loggedIn  = isLoggedIn();
$namaUser  = $_SESSION['nama'] ?? '';
$roleUser  = $_SESSION['role'] ?? '';
$inisial   = $loggedIn ? strtoupper(substr($namaUser, 0, 1)) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Library | Perpustakaan Digital SMKN 1 Depok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg sticky-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <div class="brand-icon"><img src="img/logo-onedek.png" alt="" style="width: 30px;"></div>
                <div>
                    <div class="brand-name">E-Library</div>
                    <div class="brand-sub">SMKN 1 DEPOK</div>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="#hero">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#koleksi">Koleksi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tata-tertib">Tata Tertib</a></li>
                    <li class="nav-item"><a class="nav-link" href="#fasilitas">Fasilitas</a></li>
                </ul>
                <div class="d-flex gap-2 align-items-center">
                <?php if ($loggedIn): ?>
                    <?php if ($roleUser === 'admin'): ?>
                    <a href="admin/dashboard.php" class="btn btn-outline-gold">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                    <?php endif; ?>
                    <!-- User dropdown -->
                    <div class="dropdown">
                        <button class="user-nav-chip dropdown-toggle" data-bs-toggle="dropdown">
                            <div class="nav-avatar"><?= $inisial ?></div>
                            <div class="nav-user-info">
                                <span class="nav-user-name"><?= htmlspecialchars($namaUser) ?></span>
                                <span class="nav-user-role"><?= ucfirst($roleUser) ?></span>
                            </div>
                            <i class="bi bi-chevron-down nav-chevron"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end nav-dropdown">
                            <li class="dropdown-header-custom">
                                <div class="dh-avatar"><?= $inisial ?></div>
                                <div>
                                    <div class="dh-name"><?= htmlspecialchars($namaUser) ?></div>
                                    <div class="dh-role"><?= ucfirst($roleUser) ?></div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider-custom"></li>
                            <?php if ($roleUser === 'admin'): ?>
                            <li><a class="dropdown-item-custom" href="admin/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard Admin</a></li>
                            <li><a class="dropdown-item-custom" href="admin/buku.php"><i class="bi bi-book"></i> Kelola Buku</a></li>
                            <li><hr class="dropdown-divider-custom"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item-custom text-danger-custom" href="logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-gold">Masuk</a>
                    <a href="register.php" class="btn btn-gold">Daftar</a>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section id="hero" class="hero-section">
        <div class="hero-glow"></div>
        <div class="container text-center position-relative">
            <div class="hero-badge mb-4">
                <span class="badge-dot"></span> Perpustakaan Digital SMKN 1 Depok
            </div>
            <h1 class="hero-title">
                Selamat Datang di<br>
                <em>Perpustakaan</em> Digital Kami
            </h1>
            <p class="hero-desc">
                Temukan ribuan koleksi buku, perluas wawasan, dan nikmati pengalaman membaca yang menyenangkan.
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="#koleksi" class="btn btn-gold btn-lg">Jelajahi Koleksi</a>
                <?php if ($loggedIn): ?>
                <a href="<?= $roleUser==='admin' ? 'admin/dashboard.php' : '#koleksi' ?>" class="btn btn-outline-ghost btn-lg">
                    <i class="bi bi-person-check me-1"></i>Halo, <?= htmlspecialchars(explode(' ', $namaUser)[0]) ?>!
                </a>
                <?php else: ?>
                <a href="register.php" class="btn btn-outline-ghost btn-lg">Daftar Jadi Anggota</a>
                <?php endif; ?>
            </div>

            <!-- Stats dari DB -->
            <?php
            $s_buku   = $conn->query("SELECT COUNT(*) AS c FROM buku")->fetch_assoc()['c'];
            $s_user   = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role!='admin'")->fetch_assoc()['c'];
            $s_pinjam = $conn->query("SELECT COUNT(*) AS c FROM peminjaman WHERE status='Tidak Ada'")->fetch_assoc()['c'];
            ?>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-num"><?= $s_buku ?>+</span>
                    <span class="stat-lbl">Koleksi Buku</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-num"><?= $s_user ?></span>
                    <span class="stat-lbl">Anggota Aktif</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-num">4</span>
                    <span class="stat-lbl">Kategori</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-num"><?= $s_pinjam ?></span>
                    <span class="stat-lbl">Buku Dipinjam</span>
                </div>
            </div>
        </div>
    </section>

    <!-- KOLEKSI -->
    <section id="koleksi" class="py-section">
        <div class="container">
            <div class="sec-header">
                <div class="sec-label">Kategori Koleksi</div>
                <h2 class="sec-title">Temukan Buku Favoritmu</h2>
                <p class="sec-sub">Koleksi kami mencakup berbagai kategori untuk semua kebutuhan belajarmu.</p>
            </div>

            <div class="row g-4">
                <?php
                $kat_config = [
                    'Umum'     => ['icon'=>'bi-book-half',      'color'=>'blue',   'desc'=>'Buku pengetahuan umum, ensiklopedia, dan referensi pelajaran.'],
                    'Motivasi' => ['icon'=>'bi-rocket-takeoff', 'color'=>'gold',   'desc'=>'Buku pengembangan diri dan inspirasi untuk meraih mimpi.'],
                    'Sastra'   => ['icon'=>'bi-pen',            'color'=>'purple', 'desc'=>'Novel, cerpen, puisi, dan karya sastra Indonesia & dunia.'],
                    'Religi'   => ['icon'=>'bi-moon-stars',     'color'=>'green',  'desc'=>'Al-Qur\'an, tafsir, hadits, dan buku keagamaan pilihan.'],
                ];
                // Ambil jumlah & stok dari DB per kategori
                $kat_db = [];
                $res = $conn->query("SELECT kategori, COUNT(*) AS total, SUM(stock) AS stok FROM buku GROUP BY kategori");
                while ($r = $res->fetch_assoc()) $kat_db[$r['kategori']] = $r;

                foreach ($kat_config as $nama => $cfg):
                    $total = $kat_db[$nama]['total'] ?? 0;
                    $stok  = $kat_db[$nama]['stok']  ?? 0;
                ?>
                <div class="col-md-6 col-lg-3">
                    <div class="kat-card kat-<?= $cfg['color'] ?>">
                        <div class="kat-icon-wrap">
                            <i class="bi <?= $cfg['icon'] ?>"></i>
                        </div>
                        <h4><?= $nama ?></h4>
                        <p><?= $cfg['desc'] ?></p>
                        <div style="font-size:0.74rem;color:var(--muted);margin-bottom:14px">
                            <i class="bi bi-collection me-1"></i><?= $total ?> judul &nbsp;·&nbsp;
                            <i class="bi bi-layers me-1"></i><?= $stok ?> stok
                        </div>
                        <a href="koleksi.php?kat=<?= urlencode($nama) ?>" class="kat-link">
                            Lihat Koleksi <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="text-align:center;margin-top:32px">
                <a href="koleksi.php" class="btn btn-outline-gold">
                    <i class="bi bi-grid me-2"></i>Lihat Semua Buku
                </a>
            </div>
        </div>
    </section>

    <!-- TATA TERTIB -->
    <section id="tata-tertib" class="py-section section-dark">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="sec-label">Aturan Perpustakaan</div>
                    <h2 class="sec-title">Tata Tertib</h2>
                    <p class="sec-sub mb-4">Harap dipatuhi demi kenyamanan bersama.</p>
                    <ul class="tertib-list">
                        <li><span class="t-num">1</span> Pengunjung harap tertib di dalam ruang perpustakaan.</li>
                        <li><span class="t-num">2</span> Dilarang membawa tas, jaket, dan makanan/minuman.</li>
                        <li><span class="t-num">3</span> Pengunjung wajib mengisi buku kunjungan.</li>
                        <li><span class="t-num">4</span> Kembalikan buku pada tempat semula setelah dibaca.</li>
                        <li><span class="t-num">5</span> Jagalah ketenangan &mdash; dilarang berisik.</li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <div class="cctv-box">
                        <div class="cctv-icon-wrap">
                            <i class="bi bi-camera-video-fill"></i>
                        </div>
                        <h4>Perhatian!</h4>
                        <p>Kawasan ini dipantau oleh <strong>CCTV</strong> untuk keamanan dan kenyamanan seluruh pengunjung perpustakaan.</p>
                        <div class="cctv-divider"></div>
                        <div class="jam-ops">
                            <div class="jam-label">Jam Operasional</div>
                            <div class="jam-hari">Senin &ndash; Sabtu</div>
                            <div class="jam-waktu">07.00 &ndash; 15.00 WIB</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FASILITAS -->
    <section id="fasilitas" class="py-section">
        <div class="container">
            <div class="sec-header">
                <div class="sec-label">Unggulan</div>
                <h2 class="sec-title">Fasilitas Perpustakaan</h2>
                <p class="sec-sub">Ruang membaca nyaman dengan suasana alami yang mendukung fokus belajar.</p>
            </div>
            <div class="fasilitas-card">
                <div class="fas-icon-big">🌿</div>
                <div class="fas-content">
                    <h3>Taman Baca &amp; Kolam Ikan</h3>
                    <p>Nikmati suasana membaca yang asri dengan gemericik air kolam dan area duduk bertema alam. Dirancang untuk memberikan kenyamanan maksimal dan membantu konsentrasi dalam belajar.</p>
                    <div class="fas-tags">
                        <span class="fas-tag">Area Outdoor</span>
                        <span class="fas-tag">Kolam Ikan</span>
                        <span class="fas-tag">Taman Hijau</span>
                        <span class="fas-tag">Area Duduk Santai</span>
                        <span class="fas-tag">WiFi Tersedia</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-brand">
                    <span class="footer-icon">📚</span>
                    E-Library SMKN 1 Depok
                </div>
                <div class="footer-copy">
                    &copy; <?php echo date('Y'); ?> E-Library Project. All Rights Reserved.
                </div>
                <div class="footer-links">
                    <?php if ($loggedIn): ?>
                        <?php if ($roleUser === 'admin'): ?>
                        <a href="admin/dashboard.php">Dashboard</a>
                        <?php endif; ?>
                        <a href="logout.php">Keluar</a>
                    <?php else: ?>
                        <a href="login.php">Masuk</a>
                        <a href="register.php">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('mainNav');
            nav.classList.toggle('scrolled', window.scrollY > 30);
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                const target = document.querySelector(a.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
