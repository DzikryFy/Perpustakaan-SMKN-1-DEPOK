<?php
session_start();
require_once 'config/database.php';

$loggedIn = isLoggedIn();
$namaUser = $_SESSION['nama'] ?? '';
$roleUser = $_SESSION['role'] ?? '';
$base     = '';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: koleksi.php"); exit(); }

$buku = $conn->query("SELECT * FROM buku WHERE id_buku=$id")->fetch_assoc();
if (!$buku) { header("Location: koleksi.php"); exit(); }

$msg   = '';
$error = '';

// Proses peminjaman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $loggedIn) {
    $id_user = $_SESSION['id_user'];
    $tgl     = date('Y-m-d');

    // Cek stok
    if ($buku['stock'] < 1) {
        $error = 'Maaf, stok buku ini habis.';
    } else {
        // Cek user sudah pinjam buku ini & belum kembali
        $cek = $conn->query("SELECT id_pinjam FROM peminjaman WHERE id_user=$id_user AND id_buku=$id AND status='Tidak Ada'")->num_rows;
        if ($cek > 0) {
            $error = 'Kamu masih memiliki pinjaman buku ini yang belum dikembalikan.';
        } else {
            $nama_buku = $buku['nama_buku'];
            $status    = 'Tidak Ada'; // buku sedang dipinjam = tidak ada di rak
            $s = $conn->prepare("INSERT INTO peminjaman (id_buku,id_user,tanggal_peminjaman,nama_buku,status) VALUES (?,?,?,?,?)");
            $s->bind_param("iisss", $id, $id_user, $tgl, $nama_buku, $status);
            if ($s->execute()) {
                $conn->query("UPDATE buku SET stock=stock-1 WHERE id_buku=$id");
                $msg = 'Buku berhasil dipinjam! Silakan ambil ke perpustakaan.';
                // Refresh data buku
                $buku = $conn->query("SELECT * FROM buku WHERE id_buku=$id")->fetch_assoc();
            } else {
                $error = 'Gagal meminjam buku. Coba lagi.';
            }
            $s->close();
        }
    }
}

// Cek apakah user sudah pinjam buku ini
$sudahPinjam = false;
if ($loggedIn) {
    $id_user = $_SESSION['id_user'];
    $sudahPinjam = $conn->query("SELECT id_pinjam FROM peminjaman WHERE id_user=$id_user AND id_buku=$id AND status='Tidak Ada'")->num_rows > 0;
}

// Riwayat peminjaman buku ini (untuk admin)
$riwayat = null;
if ($loggedIn && isStaff()) {
    $riwayat = $conn->query("SELECT p.*, u.nama AS nama_user FROM peminjaman p JOIN users u ON p.id_user=u.id_user WHERE p.id_buku=$id ORDER BY p.id_pinjam DESC LIMIT 10");
}

// Buku lain kategori sama
$kat = $conn->real_escape_string($buku['kategori']);
$related = $conn->query("SELECT * FROM buku WHERE kategori='$kat' AND id_buku!=$id LIMIT 4");

$cover_map = [
    'umum'     => ['emoji'=>'📖','class'=>'c-umum',    'color'=>'var(--blue)'],
    'motivasi' => ['emoji'=>'🚀','class'=>'c-motivasi', 'color'=>'var(--accent)'],
    'sastra'   => ['emoji'=>'✍️','class'=>'c-sastra',  'color'=>'var(--purple)'],
    'religi'   => ['emoji'=>'🕌','class'=>'c-religi',  'color'=>'var(--green)'],
];
$katKey = strtolower($buku['kategori']);
$cm     = $cover_map[$katKey] ?? ['emoji'=>'📚','class'=>'c-default','color'=>'var(--muted)'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= htmlspecialchars($buku['nama_buku']) ?> | E-Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .detail-wrap { padding: 40px 0 70px; }
        .back-link { display:inline-flex;align-items:center;gap:6px;color:var(--muted);text-decoration:none;font-size:0.83rem;margin-bottom:28px;transition:color .2s; }
        .back-link:hover { color:var(--accent); }

        /* Layout */
        .detail-grid { display:grid; grid-template-columns:280px 1fr; gap:36px; align-items:start; }

        /* Cover */
        .cover-box {
            border-radius:16px; overflow:hidden;
            border:1px solid var(--border); position:sticky; top:90px;
        }
        .cover-img {
            height:320px; display:flex; align-items:center; justify-content:center; font-size:72px;
        }
        .cover-meta { padding:18px; background:var(--card); }
        .cover-meta .stok-big {
            font-size:2rem; font-weight:700; text-align:center; margin-bottom:4px;
        }
        .cover-meta .stok-lbl { font-size:0.72rem; color:var(--muted); text-align:center; text-transform:uppercase; letter-spacing:.07em; }
        .cover-meta .stok-bar { height:6px; border-radius:3px; background:rgba(255,255,255,0.08); margin:12px 0 16px; overflow:hidden; }
        .cover-meta .stok-fill { height:100%; border-radius:3px; background:linear-gradient(90deg,var(--green),#5dd896); transition:width .5s; }

        /* Info */
        .buku-info-header { margin-bottom:24px; }
        .buku-info-header .kat-pill {
            display:inline-flex;align-items:center;gap:5px; padding:5px 13px; border-radius:20px;
            font-size:0.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em;
            margin-bottom:12px;
        }
        .buku-info-header h1 { font-family:'Playfair Display',serif; font-size:2rem; color:#fff; line-height:1.2; margin-bottom:10px; }
        .buku-info-header .buku-id { font-size:0.75rem; color:var(--muted); font-family:monospace; }

        /* Stats row */
        .info-stats { display:flex; gap:24px; padding:18px 0; border-top:1px solid var(--border); border-bottom:1px solid var(--border); margin-bottom:24px; flex-wrap:wrap; }
        .info-stat-item { display:flex;flex-direction:column; }
        .info-stat-item .isv { font-size:1.1rem; font-weight:700; color:#fff; }
        .info-stat-item .isl { font-size:0.7rem; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; margin-top:2px; }

        /* Action buttons */
        .action-section { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:28px; }
        .btn-pinjam {
            flex:1; min-width:160px; padding:14px 24px; border-radius:12px; font-size:0.92rem; font-weight:600;
            background:linear-gradient(135deg,var(--accent),#d4a54e); color:#0c1520; border:none; cursor:pointer;
            transition:transform .15s,box-shadow .15s; box-shadow:0 4px 18px rgba(232,184,109,0.28); text-align:center; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:8px;
        }
        .btn-pinjam:hover { transform:translateY(-2px); box-shadow:0 8px 26px rgba(232,184,109,0.38); color:#0c1520; }
        .btn-pinjam:disabled, .btn-pinjam.disabled { background:rgba(255,255,255,0.08); color:var(--muted); cursor:not-allowed; box-shadow:none; transform:none; border:1px solid var(--border); }
        .btn-back-kat {
            padding:14px 20px; border-radius:12px; font-size:0.88rem; font-weight:500;
            border:1px solid var(--border); color:var(--muted); text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:all .2s;
        }
        .btn-back-kat:hover { border-color:var(--accent); color:var(--accent); }

        /* Alert */
        .alert-ok  { background:rgba(58,171,110,0.1); border:1px solid rgba(58,171,110,0.2); color:#75d8a0; border-radius:10px; padding:12px 16px; font-size:0.85rem; margin-bottom:18px; }
        .alert-err { background:rgba(232,93,93,0.1);  border:1px solid rgba(232,93,93,0.2);  color:#ff9090; border-radius:10px; padding:12px 16px; font-size:0.85rem; margin-bottom:18px; }
        .alert-info{ background:rgba(74,158,255,0.1); border:1px solid rgba(74,158,255,0.2); color:#82c4ff; border-radius:10px; padding:12px 16px; font-size:0.85rem; margin-bottom:18px; }

        /* Riwayat admin */
        .riwayat-section { margin-top:32px; }
        .riwayat-section h3 { font-size:0.95rem; font-weight:600; color:#fff; margin-bottom:14px; }
        .tbl-mini { width:100%; border-collapse:collapse; font-size:0.8rem; }
        .tbl-mini th { padding:8px 12px; color:var(--muted); font-size:0.68rem; text-transform:uppercase; letter-spacing:.07em; border-bottom:1px solid var(--border); }
        .tbl-mini td { padding:9px 12px; border-bottom:1px solid rgba(255,255,255,0.04); }
        .tbl-mini tr:last-child td { border-bottom:none; }

        /* Related */
        .related-section { padding:40px 0 60px; border-top:1px solid var(--border); }
        .related-section h2 { font-family:'Playfair Display',serif; font-size:1.4rem; color:#fff; margin-bottom:22px; }
        .related-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:14px; }
        .rel-card { background:var(--card); border:1px solid var(--border); border-radius:12px; overflow:hidden; text-decoration:none; transition:transform .2s,border-color .2s; display:block; }
        .rel-card:hover { transform:translateY(-4px); border-color:rgba(232,184,109,0.25); }
        .rel-cover { height:100px; display:flex; align-items:center; justify-content:center; font-size:32px; }
        .rel-body { padding:12px; }
        .rel-title { font-size:0.82rem; font-weight:500; color:#fff; line-height:1.3; margin-bottom:4px; }
        .rel-stok  { font-size:0.72rem; color:var(--muted); }

        @media(max-width:768px){
            .detail-grid { grid-template-columns:1fr; }
            .cover-box { position:static; }
            .cover-img { height:200px; font-size:56px; }
        }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container detail-wrap">
    <a href="koleksi.php" class="back-link"><i class="bi bi-arrow-left"></i> Kembali ke Koleksi</a>

    <?php if ($msg): ?><div class="alert-ok"><i class="bi bi-check-circle me-2"></i><?= $msg ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert-err"><i class="bi bi-exclamation-circle me-2"></i><?= $error ?></div><?php endif; ?>

    <div class="detail-grid">
        <!-- COVER KIRI -->
        <div class="cover-box">
            <div class="buku-cover <?= $cm['class'] ?> cover-img"><?= $cm['emoji'] ?></div>
            <div class="cover-meta">
                <?php $stokPersen = min(100, ($buku['stock'] / max(1, $buku['stock']+3)) * 100); ?>
                <div class="stok-big" style="color:<?= $buku['stock']>0?'var(--green)':'var(--red)' ?>"><?= $buku['stock'] ?></div>
                <div class="stok-lbl">Stok Tersedia</div>
                <div class="stok-bar"><div class="stok-fill" style="width:<?= $stokPersen ?>%"></div></div>
                <?php if ($buku['stock'] > 0): ?>
                <div style="font-size:0.75rem;color:var(--green);text-align:center"><i class="bi bi-check-circle me-1"></i>Buku tersedia untuk dipinjam</div>
                <?php else: ?>
                <div style="font-size:0.75rem;color:var(--red);text-align:center"><i class="bi bi-x-circle me-1"></i>Stok habis</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- INFO KANAN -->
        <div>
            <div class="buku-info-header">
                <div class="kat-pill" style="background:rgba(<?= $cm['color']==='var(--blue)'?'74,158,255':($cm['color']==='var(--accent)'?'232,184,109':($cm['color']==='var(--purple)'?'180,109,232':'58,171,110')) ?>,0.12);color:<?= $cm['color'] ?>">
                    <?= htmlspecialchars($buku['kategori']) ?>
                </div>
                <h1><?= htmlspecialchars($buku['nama_buku']) ?></h1>
                <div class="buku-id">ID Buku: #<?= $buku['id_buku'] ?></div>
            </div>

            <div class="info-stats">
                <div class="info-stat-item">
                    <span class="isv"><?= $buku['stock'] ?></span>
                    <span class="isl">Stok Tersedia</span>
                </div>
                <div class="info-stat-item">
                    <span class="isv"><?= htmlspecialchars($buku['kategori']) ?></span>
                    <span class="isl">Kategori</span>
                </div>
                <?php if (!empty($buku['penerbit'])): ?>
                <div class="info-stat-item">
                    <span class="isv"><?= htmlspecialchars($buku['penerbit']) ?></span>
                    <span class="isl">Penerbit</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($buku['tahun_cetak'])): ?>
                <div class="info-stat-item">
                    <span class="isv"><?= $buku['tahun_cetak'] ?></span>
                    <span class="isl">Tahun Cetak</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($buku['no_bos'])): ?>
                <div class="info-stat-item">
                    <span class="isv"><?= htmlspecialchars($buku['no_bos']) ?></span>
                    <span class="isl">No. BOS</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($buku['asal_perolehan'])): ?>
                <div class="info-stat-item">
                    <span class="isv"><?= htmlspecialchars($buku['asal_perolehan']) ?></span>
                    <span class="isl">Asal Perolehan</span>
                </div>
                <?php endif; ?>
                <?php
                $totalPinjam = $conn->query("SELECT COUNT(*) AS c FROM peminjaman WHERE id_buku=$id")->fetch_assoc()['c'];
                ?>
                <div class="info-stat-item">
                    <span class="isv"><?= $totalPinjam ?>x</span>
                    <span class="isl">Total Dipinjam</span>
                </div>
            </div>

            <!-- Action -->
            <div class="action-section">
                <?php if (!$loggedIn): ?>
                <a href="login.php" class="btn-pinjam"><i class="bi bi-box-arrow-in-right"></i> Login untuk Meminjam</a>
                <?php elseif ($sudahPinjam): ?>
                <span class="btn-pinjam disabled"><i class="bi bi-clock"></i> Sedang Dipinjam</span>
                <a href="riwayat-peminjaman.php" class="btn-back-kat"><i class="bi bi-list-check"></i> Lihat Pinjaman</a>
                <?php elseif ($buku['stock'] < 1): ?>
                <span class="btn-pinjam disabled"><i class="bi bi-x-circle"></i> Stok Habis</span>
                <?php else: ?>
                <form method="POST" style="flex:1;min-width:160px">
                    <button type="submit" class="btn-pinjam w-100" onclick="return confirm('Pinjam buku ini sekarang?')">
                        <i class="bi bi-arrow-bar-right"></i> Pinjam Sekarang
                    </button>
                </form>
                <a href="riwayat-peminjaman.php" class="btn-back-kat"><i class="bi bi-list-check"></i> Pinjaman Saya</a>
                <?php endif; ?>
            </div>

            <?php if ($sudahPinjam): ?>
            <div class="alert-info"><i class="bi bi-info-circle me-2"></i>Kamu sedang meminjam buku ini. Kembalikan dulu sebelum meminjam lagi.</div>
            <?php endif; ?>

            <?php if (!$loggedIn): ?>
            <div class="alert-info"><i class="bi bi-info-circle me-2"></i>Silakan <a href="login.php" style="color:#82c4ff;font-weight:600">login</a> atau <a href="register.php" style="color:#82c4ff;font-weight:600">daftar</a> untuk meminjam buku ini.</div>
            <?php endif; ?>

            <!-- Riwayat peminjaman (admin only) -->
            <?php if ($riwayat && $riwayat->num_rows > 0): ?>
            <div class="riwayat-section">
                <h3><i class="bi bi-clock-history me-2" style="color:var(--accent)"></i>Riwayat Peminjaman Buku Ini</h3>
                <table class="tbl-mini">
                    <thead><tr><th>Peminjam</th><th>Tgl Pinjam</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php while ($r = $riwayat->fetch_assoc()): ?>
                    <tr>
                        <td style="color:#fff;font-weight:500"><?= htmlspecialchars($r['nama_user']) ?></td>
                        <td><?= date('d/m/Y', strtotime($r['tanggal_peminjaman'])) ?></td>
                        <td>
                            <span class="<?= $r['status']==='Ada'?'badge-ada':'badge-tidak' ?>" style="padding:3px 9px;border-radius:20px;font-size:0.69rem;font-weight:600">
                                <?= $r['status']==='Ada' ? 'Dikembalikan' : 'Dipinjam' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- BUKU TERKAIT -->
<?php if ($related->num_rows > 0): ?>
<div class="container related-section">
    <h2>Buku Lain Kategori <?= htmlspecialchars($buku['kategori']) ?></h2>
    <div class="related-grid">
        <?php while ($r = $related->fetch_assoc()):
            $rKey = strtolower($r['kategori']);
            $rcm  = $cover_map[$rKey] ?? ['emoji'=>'📚','class'=>'c-default'];
        ?>
        <a href="detail_buku.php?id=<?= $r['id_buku'] ?>" class="rel-card">
            <div class="buku-cover <?= $rcm['class'] ?> rel-cover"><?= $rcm['emoji'] ?></div>
            <div class="rel-body">
                <div class="rel-title"><?= htmlspecialchars($r['nama_buku']) ?></div>
                <div class="rel-stok"><i class="bi bi-layers me-1"></i><?= $r['stock'] ?> tersedia</div>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

<footer class="site-footer">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand"><span class="footer-icon">📚</span> E-Library SMKN 1 Depok</div>
            <div class="footer-copy">&copy; <?= date('Y') ?> E-Library Project. All Rights Reserved.</div>
            <div class="footer-links">
                <?php if ($loggedIn): ?><a href="logout.php">Keluar</a>
                <?php else: ?><a href="login.php">Masuk</a><a href="register.php">Daftar</a><?php endif; ?>
            </div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>window.addEventListener('scroll',()=>{document.getElementById('mainNav').classList.toggle('scrolled',window.scrollY>30);});</script>
</body>
</html>
