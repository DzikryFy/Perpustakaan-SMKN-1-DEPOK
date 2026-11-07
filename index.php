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
                    <li class="nav-item"><a class="nav-link" href="koleksi.php">Koleksi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tata-tertib">Tata Tertib</a></li>
                    <li class="nav-item"><a class="nav-link" href="#fasilitas">Fasilitas</a></li>
                </ul>
                <div class="d-flex gap-2 align-items-center">
                <?php if ($loggedIn): ?>
                    <?php if (isStaff()): ?>
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
                            <?php if (isStaff()): ?>
                            <li><a class="dropdown-item-custom" href="admin/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard Admin</a></li>
                            <li><a class="dropdown-item-custom" href="admin/buku.php"><i class="bi bi-book"></i> Kelola Buku</a></li>
                            <li><hr class="dropdown-divider-custom"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item-custom text-danger-custom" href="logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-gold">Masuk</a>
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
                <a href="koleksi.php" class="btn btn-gold btn-lg">Jelajahi Koleksi</a>
                <?php if ($loggedIn): ?>
                <a href="<?= $roleUser===isStaff() ? 'admin/dashboard.php' : 'koleksi.php' ?>" class="btn btn-outline-ghost btn-lg">
                    <i class="bi bi-person-check me-1"></i>Halo, <?= htmlspecialchars(explode(' ', $namaUser)[0]) ?>!
                </a>
                <?php else: ?>
                <?php endif; ?>
            </div>

            <!-- Stats -->
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-num">500+</span>
                    <span class="stat-lbl">Koleksi Buku</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-num">1.2K</span>
                    <span class="stat-lbl">Anggota Aktif</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-num">4</span>
                    <span class="stat-lbl">Kategori</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-num">6 Hari</span>
                    <span class="stat-lbl">Buka Per Minggu</span>
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
                $kategori = [
                    ['slug' => 'umum',     'nama' => 'Umum',     'icon' => 'bi-book-half',        'color' => 'blue',   'desc' => 'Buku pengetahuan umum, ensiklopedia, dan referensi pelajaran.'],
                    ['slug' => 'motivasi', 'nama' => 'Motivasi', 'icon' => 'bi-rocket-takeoff',   'color' => 'gold',   'desc' => 'Buku pengembangan diri dan inspirasi untuk meraih mimpi.'],
                    ['slug' => 'sastra',   'nama' => 'Sastra',   'icon' => 'bi-pen',              'color' => 'purple', 'desc' => 'Novel, cerpen, puisi, dan karya sastra Indonesia & dunia.'],
                    ['slug' => 'religi',   'nama' => 'Religi',   'icon' => 'bi-moon-stars',       'color' => 'green',  'desc' => 'Al-Qur\'an, tafsir, hadits, dan buku keagamaan pilihan.'],
                ];
                foreach ($kategori as $k) :
                ?>
                <div class="col-md-6 col-lg-3">
                    <div class="kat-card kat-<?= $k['color'] ?>">
                        <div class="kat-icon-wrap">
                            <i class="bi <?= $k['icon'] ?>"></i>
                        </div>
                        <h4><?= $k['nama'] ?></h4>
                        <p><?= $k['desc'] ?></p>
                        <a href="login.php" class="kat-link">Lihat Detail <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Preview Rak Buku Asli -->
            <div class="rak-preview-wrap mt-5">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-7">
                        <div class="rak-photo-box">
                            <img src="img/rak-buku-1.jpg" alt="Rak Koleksi Buku" class="rak-photo-main">
                            <div class="rak-photo-badge">📸 Foto Nyata Koleksi Kami</div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="sec-label mb-2">Koleksi Fisik</div>
                        <h3 style="color:#fff;font-family:'Playfair Display',serif;font-size:1.6rem;margin-bottom:12px;">Rak Buku Perpustakaan</h3>
                        <p style="color:var(--muted);font-size:0.88rem;line-height:1.7;">Koleksi buku tersedia langsung di perpustakaan, tersusun rapi dalam rak berdasarkan kategori: <strong style="color:var(--accent)">Umum, Motivasi, Sastra,</strong> dan <strong style="color:var(--accent)">Religi</strong>. Kamu bisa datang langsung atau pinjam melalui sistem digital ini.</p>
                        <div class="rak-stats mt-4">
                            <div class="rak-stat"><span class="rs-num">500+</span><span class="rs-lbl">Buku Fisik</span></div>
                            <div class="rak-stat"><span class="rs-num">4</span><span class="rs-lbl">Kategori</span></div>
                            <div class="rak-stat"><span class="rs-num">2</span><span class="rs-lbl">Rak Utama</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
                    <div class="tatatertib-photo-wrap">
                        <img src="img/tata-tertib.jpg" alt="Tata Tertib Perpustakaan" class="tatatertib-photo">
                        <div class="tatatertib-badge">
                            <i class="bi bi-camera-video-fill me-1"></i> Kawasan dipantau CCTV
                        </div>
                        <div class="jam-ops-float">
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

            <!-- Fasilitas Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-6 col-lg-3">
                    <div class="fas-mini-card">
                        <div class="fas-mini-img">
                            <img src="img/taman-baca-2.jpg" alt="Taman Baca">
                        </div>
                        <div class="fas-mini-body">
                            <h5>🌿 Taman Baca Outdoor</h5>
                            <p>Area duduk bertema alam dengan kursi batang pohon dan rumput sintetis yang asri.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="fas-mini-card">
                        <div class="fas-mini-img">
                            <img src="img/taman-baca-1.jpg" alt="Kolam Ikan">
                        </div>
                        <div class="fas-mini-body">
                            <h5>🐠 Kolam &amp; Air Mancur</h5>
                            <p>Gemericik air dari kolam mini memberikan suasana tenang dan nyaman saat membaca.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="fas-mini-card">
                        <div class="fas-mini-img">
                            <img src="img/area-luar.jpg" alt="Rak Buku Luar">
                        </div>
                        <div class="fas-mini-body">
                            <h5>📚 Rak Buku &amp; Majalah</h5>
                            <p>Rak display kayu dan metal berisi koleksi buku, majalah, dan bacaan populer.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="fas-mini-card">
                        <div class="fas-mini-img">
                            <img src="img/pintu-masuk.jpg" alt="Ruang Perpustakaan">
                        </div>
                        <div class="fas-mini-body">
                            <h5>🏛️ Ruang Perpustakaan</h5>
                            <p>Ruangan ber-AC dengan petugas siap membantu, koleksi buku lengkap dan tertata rapi.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Galeri Foto -->
            <div class="galeri-header mb-3">
                <div class="sec-label">Foto Nyata</div>
                <h3 class="sec-title" style="font-size:1.6rem;">Galeri Perpustakaan</h3>
            </div>
            <div class="galeri-grid">
                <div class="galeri-item galeri-big" onclick="bukaFoto(this)">
                    <img src="img/taman-baca-3.jpg" alt="Taman Baca Utama">
                    <div class="galeri-overlay"><i class="bi bi-zoom-in"></i><span>Taman Baca Utama</span></div>
                </div>
                <div class="galeri-item" onclick="bukaFoto(this)">
                    <img src="img/taman-baca-1.jpg" alt="Kolam &amp; Area Duduk">
                    <div class="galeri-overlay"><i class="bi bi-zoom-in"></i><span>Kolam &amp; Area Duduk</span></div>
                </div>
                <div class="galeri-item" onclick="bukaFoto(this)">
                    <img src="img/rak-buku-1.jpg" alt="Rak Koleksi Buku">
                    <div class="galeri-overlay"><i class="bi bi-zoom-in"></i><span>Rak Koleksi Buku</span></div>
                </div>
                <div class="galeri-item" onclick="bukaFoto(this)">
                    <img src="img/area-luar.jpg" alt="Area Luar Perpustakaan">
                    <div class="galeri-overlay"><i class="bi bi-zoom-in"></i><span>Area Luar Perpustakaan</span></div>
                </div>
                <div class="galeri-item" onclick="bukaFoto(this)">
                    <img src="img/rak-majalah.jpg" alt="Rak Majalah &amp; Buku Display">
                    <div class="galeri-overlay"><i class="bi bi-zoom-in"></i><span>Rak Majalah &amp; Display</span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- LIGHTBOX -->
    <div id="lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:9999;align-items:center;justify-content:center;flex-direction:column;" onclick="tutupFoto()">
        <img id="lb-img" src="" alt="" style="max-width:90vw;max-height:85vh;border-radius:12px;object-fit:contain;box-shadow:0 8px 40px rgba(0,0,0,0.6);">
        <p id="lb-cap" style="color:#e8b86d;margin-top:14px;font-size:0.95rem;"></p>
        <span style="position:absolute;top:20px;right:28px;color:#fff;font-size:2rem;cursor:pointer;line-height:1;">&times;</span>
    </div>

    <!-- FOOTER -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-brand">
                    <span class="footer-icon"><img src="img/logo-onedek.png" alt="" style="width: 30px;"></span>
                    E-Library SMKN 1 Depok
                </div>
                <div class="footer-copy">
                    &copy; <?php echo date('Y'); ?> E-Library Project. All Rights Reserved.
                </div>
                <div class="footer-links">
                    <?php if ($loggedIn): ?>
                        <?php if (isStaff()): ?>
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
    <script>
        function bukaFoto(el) {
            const img = el.querySelector('img');
            const cap = el.querySelector('.galeri-overlay span');
            document.getElementById('lb-img').src = img.src;
            document.getElementById('lb-cap').textContent = cap ? cap.textContent : '';
            const lb = document.getElementById('lightbox');
            lb.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function tutupFoto() {
            document.getElementById('lightbox').style.display = 'none';
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') tutupFoto(); });
    </script>
</body>
</html>