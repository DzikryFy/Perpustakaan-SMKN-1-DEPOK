<?php
session_start();
require_once 'config/database.php';
requireLogin();

$loggedIn = true;
$namaUser = $_SESSION['nama'] ?? '';
$roleUser = $_SESSION['role'] ?? '';
$id_user  = $_SESSION['id_user'];
$base     = '';

// Filter
$sf    = sanitize($_GET['sf'] ?? '');
$where = "WHERE p.id_user=$id_user";
if ($sf) $where .= " AND p.status='$sf'";

$list = $conn->query("
    SELECT p.*, b.stock AS stok_buku
    FROM peminjaman p
    JOIN buku b ON p.id_buku = b.id_buku
    $where
    ORDER BY p.id_pinjam DESC
");

$total_pinjam   = $conn->query("SELECT COUNT(*) AS c FROM peminjaman WHERE id_user=$id_user AND status='Tidak Ada'")->fetch_assoc()['c'];
$total_kembali  = $conn->query("SELECT COUNT(*) AS c FROM peminjaman WHERE id_user=$id_user AND status='Ada'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pinjaman Saya | E-Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .page-wrap { padding:40px 0 70px; }
        .page-title { font-family:'Playfair Display',serif; font-size:2rem; color:#fff; margin-bottom:6px; }
        .page-sub   { color:var(--muted); font-size:0.87rem; margin-bottom:32px; }

        /* Stats */
        .my-stats { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; margin-bottom:28px; max-width:420px; }
        .my-stat  { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:18px; display:flex; align-items:center; gap:14px; }
        .my-stat .ico { width:40px;height:40px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0; }
        .ico-blue { background:rgba(74,158,255,0.12); color:var(--blue); }
        .ico-green{ background:rgba(58,171,110,0.12); color:var(--green); }
        .my-stat .num { font-size:1.6rem;font-weight:700;color:#fff;line-height:1; }
        .my-stat .lbl { font-size:0.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-top:2px; }

        /* Filter tabs */
        .filter-tabs { display:flex;gap:8px;margin-bottom:22px;flex-wrap:wrap; }
        .ftab { padding:7px 16px;border-radius:20px;font-size:0.82rem;border:1px solid var(--border);color:var(--muted);text-decoration:none;transition:all .2s; }
        .ftab:hover { border-color:var(--accent);color:var(--accent); }
        .ftab.active { background:var(--accent);border-color:var(--accent);color:#0c1520;font-weight:600; }

        /* Cards pinjaman */
        .pinjam-list { display:flex;flex-direction:column;gap:14px; }
        .pinjam-card {
            background:var(--card);border:1px solid var(--border);border-radius:14px;
            display:flex;align-items:center;gap:18px;padding:18px 20px;
            transition:border-color .2s;
        }
        .pinjam-card:hover { border-color:rgba(232,184,109,0.2); }
        .pinjam-cover {
            width:60px;height:72px;border-radius:9px;
            display:flex;align-items:center;justify-content:center;font-size:26px;flex-shrink:0;
        }
        .pinjam-info { flex:1; }
        .pinjam-judul { font-size:0.95rem;font-weight:600;color:#fff;margin-bottom:4px; }
        .pinjam-meta  { font-size:0.78rem;color:var(--muted);display:flex;gap:16px;flex-wrap:wrap; }
        .pinjam-meta span { display:flex;align-items:center;gap:4px; }
        .pinjam-actions { display:flex;flex-direction:column;gap:8px;align-items:flex-end; }
        .badge-dipinjam   { padding:4px 12px;border-radius:20px;font-size:0.72rem;font-weight:600;background:rgba(74,158,255,0.12);color:var(--blue); }
        .badge-dikembalikan{padding:4px 12px;border-radius:20px;font-size:0.72rem;font-weight:600;background:rgba(58,171,110,0.12);color:var(--green); }
        .btn-kembalikan {
            padding:7px 14px;border-radius:8px;font-size:0.78rem;font-weight:600;
            background:rgba(232,184,109,0.1);color:var(--accent);border:1px solid rgba(232,184,109,0.25);
            text-decoration:none;transition:all .2s;white-space:nowrap;
        }
        .btn-kembalikan:hover { background:rgba(232,184,109,0.18);color:var(--accent); }
        .btn-detail-sm {
            padding:7px 14px;border-radius:8px;font-size:0.78rem;
            border:1px solid var(--border);color:var(--muted);text-decoration:none;transition:all .2s;white-space:nowrap;
        }
        .btn-detail-sm:hover { border-color:var(--accent);color:var(--accent); }

        /* Empty */
        .empty-state { text-align:center;padding:60px 20px;color:var(--muted); }
        .empty-state i { font-size:3rem;display:block;margin-bottom:14px;opacity:.35; }
        .empty-state h3 { color:var(--text);font-size:1.1rem;margin-bottom:8px; }
        .empty-state a  { color:var(--accent);font-weight:600;text-decoration:none; }

        @media(max-width:600px){
            .pinjam-card { flex-wrap:wrap; }
            .pinjam-actions { flex-direction:row;align-items:center;width:100%; }
        }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container page-wrap">
    <h1 class="page-title">Pinjaman Saya</h1>
    <p class="page-sub">Riwayat semua buku yang pernah kamu pinjam</p>

    <!-- Stats -->
    <div class="my-stats">
        <div class="my-stat">
            <div class="ico ico-blue"><i class="bi bi-arrow-bar-right"></i></div>
            <div><div class="num"><?= $total_pinjam ?></div><div class="lbl">Sedang Dipinjam</div></div>
        </div>
        <div class="my-stat">
            <div class="ico ico-green"><i class="bi bi-check-circle"></i></div>
            <div><div class="num"><?= $total_kembali ?></div><div class="lbl">Sudah Dikembalikan</div></div>
        </div>
    </div>

    <!-- Filter -->
    <div class="filter-tabs">
        <a href="peminjaman_saya.php" class="ftab <?= !$sf?'active':'' ?>">Semua</a>
        <a href="?sf=Tidak+Ada"       class="ftab <?= $sf==='Tidak Ada'?'active':'' ?>"><i class="bi bi-clock me-1"></i>Sedang Dipinjam</a>
        <a href="?sf=Ada"             class="ftab <?= $sf==='Ada'?'active':'' ?>"><i class="bi bi-check-circle me-1"></i>Dikembalikan</a>
    </div>

    <!-- List -->
    <?php
    $cover_map = [
        'umum'     => ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;">','class'=>'c-umum'],
        'motivasi' => ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;">','class'=>'c-motivasi'],
        'sastra'   => ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;">','class'=>'c-sastra'],
        'religi'   => ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;">','class'=>'c-religi'],
    ];
    if ($list && $list->num_rows > 0):
    ?>
    <div class="pinjam-list">
        <?php while ($r = $list->fetch_assoc()):
            // Cek apakah sudah ada pengembalian
            $sudahKembali = $conn->query("SELECT id_pengembalian FROM pengembalian WHERE id_pinjam={$r['id_pinjam']}")->num_rows > 0;
            $katKey = strtolower($r['kategori'] ?? '');
            // Ambil kategori dari tabel buku
            $bukuRow = $conn->query("SELECT kategori FROM buku WHERE id_buku={$r['id_buku']}")->fetch_assoc();
            $katKey  = strtolower($bukuRow['kategori'] ?? '');
            $cm      = $cover_map[$katKey] ?? ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;">','class'=>'c-default'];
        ?>
        <div class="pinjam-card">
            <div class="buku-cover <?= $cm['class'] ?> pinjam-cover"><?= $cm['emoji'] ?></div>
            <div class="pinjam-info">
                <div class="pinjam-judul"><?= htmlspecialchars($r['nama_buku']) ?></div>
                <div class="pinjam-meta">
                    <span><i class="bi bi-calendar3"></i> Dipinjam: <?= date('d M Y', strtotime($r['tanggal_peminjaman'])) ?></span>
                    <span><i class="bi bi-hash"></i> ID: #<?= $r['id_pinjam'] ?></span>
                </div>
            </div>
            <div class="pinjam-actions">
                <?php if ($r['status'] === 'Tidak Ada'): ?>
                    <span class="badge-dipinjam"><i class="bi bi-clock me-1"></i>Dipinjam</span>
                    <?php if (!$sudahKembali): ?>
                    <a href="pengembalian_saya.php?pinjam=<?= $r['id_pinjam'] ?>" class="btn-kembalikan">
                        <i class="bi bi-arrow-bar-left me-1"></i>Kembalikan
                    </a>
                    <?php else: ?>
                    <span style="font-size:0.75rem;color:var(--muted)">Menunggu verifikasi</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="badge-dikembalikan"><i class="bi bi-check-circle me-1"></i>Dikembalikan</span>
                <?php endif; ?>
                <a href="detail_buku.php?id=<?= $r['id_buku'] ?>" class="btn-detail-sm">
                    <i class="bi bi-eye me-1"></i>Detail
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <h3>Belum ada pinjaman</h3>
        <p>Kamu belum pernah meminjam buku.<br><a href="koleksi.php">Jelajahi koleksi buku</a> dan mulai meminjam sekarang.</p>
    </div>
    <?php endif; ?>
</div>

<footer class="site-footer">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand"><span class="footer-icon">📚</span> E-Library SMKN 1 Depok</div>
            <div class="footer-copy">&copy; <?= date('Y') ?> E-Library Project. All Rights Reserved.</div>
            <div class="footer-links"><a href="logout.php">Keluar</a></div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>window.addEventListener('scroll',()=>{document.getElementById('mainNav').classList.toggle('scrolled',window.scrollY>30);});</script>
</body>
</html>
