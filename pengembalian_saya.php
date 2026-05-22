<?php
session_start();
require_once 'config/database.php';
requireLogin();

$loggedIn = true;
$namaUser = $_SESSION['nama'] ?? '';
$roleUser = $_SESSION['role'] ?? '';
$id_user  = $_SESSION['id_user'];
$base     = '';

$msg   = '';
$error = '';

// Proses form pengembalian
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pinjam = (int)($_POST['id_pinjam'] ?? 0);
    $tgl_kembali = date('Y-m-d');

    // Validasi: pastikan pinjaman milik user ini
    $pinjam = $conn->query("SELECT * FROM peminjaman WHERE id_pinjam=$id_pinjam AND id_user=$id_user AND status='Tidak Ada'")->fetch_assoc();

    if (!$pinjam) {
        $error = 'Data peminjaman tidak valid atau sudah dikembalikan.';
    } else {
        // Cek belum ada pengembalian
        $cek = $conn->query("SELECT id_pengembalian FROM pengembalian WHERE id_pinjam=$id_pinjam")->num_rows;
        if ($cek > 0) {
            $error = 'Pengembalian untuk pinjaman ini sudah tercatat. Menunggu verifikasi admin.';
        } else {
            $id_buku   = $pinjam['id_buku'];
            $nama_buku = $pinjam['nama_buku'];
            $status    = 'Ada'; // buku dikembalikan = ada di rak

            $s = $conn->prepare("INSERT INTO pengembalian (id_pinjam,id_buku,id_user,tanggal_pengembalian,nama_buku,status) VALUES (?,?,?,?,?,?)");
            $s->bind_param("iiisss", $id_pinjam, $id_buku, $id_user, $tgl_kembali, $nama_buku, $status);
            if ($s->execute()) {
                // Update status peminjaman
                $conn->query("UPDATE peminjaman SET status='Ada' WHERE id_pinjam=$id_pinjam");
                // Tambah stok kembali
                $conn->query("UPDATE buku SET stock=stock+1 WHERE id_buku=$id_buku");
                $msg = 'Pengembalian berhasil dicatat! Terima kasih sudah mengembalikan buku.';
            } else {
                $error = 'Gagal mencatat pengembalian. Coba lagi.';
            }
            $s->close();
        }
    }
}

// Jika dari tombol "Kembalikan" di halaman pinjaman
$pre_pinjam = (int)($_GET['pinjam'] ?? 0);
$pre_data   = null;
if ($pre_pinjam) {
    $pre_data = $conn->query("SELECT * FROM peminjaman WHERE id_pinjam=$pre_pinjam AND id_user=$id_user AND status='Tidak Ada'")->fetch_assoc();
}

// Daftar pinjaman aktif user (belum dikembalikan & belum ada pengembalian)
$aktif = $conn->query("
    SELECT p.*, b.kategori
    FROM peminjaman p
    JOIN buku b ON p.id_buku = b.id_buku
    WHERE p.id_user=$id_user AND p.status='Tidak Ada'
    AND p.id_pinjam NOT IN (SELECT id_pinjam FROM pengembalian)
    ORDER BY p.id_pinjam DESC
");

// Riwayat pengembalian user
$riwayat = $conn->query("
    SELECT pg.*, p.tanggal_peminjaman
    FROM pengembalian pg
    JOIN peminjaman p ON pg.id_pinjam = p.id_pinjam
    WHERE pg.id_user = $id_user
    ORDER BY pg.id_pengembalian DESC
");

$cover_map = [
    'umum'     => ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;">','class'=>'c-umum'],
    'motivasi' => ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;">','class'=>'c-motivasi'],
    'sastra'   => ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;">','class'=>'c-sastra'],
    'religi'   => ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;">','class'=>'c-religi'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pengembalian Buku | E-Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .page-wrap { padding:40px 0 70px; }
        .page-title { font-family:'Playfair Display',serif; font-size:2rem; color:#fff; margin-bottom:6px; }
        .page-sub   { color:var(--muted); font-size:0.87rem; margin-bottom:32px; }
        .two-col    { display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start; }

        /* Panel */
        .panel-user { background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:24px; }
        .panel-head { padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px; }
        .panel-head h3 { font-size:0.92rem;font-weight:600;color:#fff;margin:0; }
        .panel-head i  { color:var(--accent);font-size:1rem; }
        .panel-body-p  { padding:18px; }

        /* Buku aktif card */
        .aktif-card {
            display:flex;align-items:center;gap:14px;padding:14px;border-radius:10px;
            border:1px solid var(--border);margin-bottom:10px;background:rgba(255,255,255,0.02);
            cursor:pointer;transition:border-color .2s;
        }
        .aktif-card:hover { border-color:rgba(232,184,109,0.3); }
        .aktif-card.selected { border-color:var(--accent);background:rgba(232,184,109,0.05); }
        .aktif-cover { width:44px;height:54px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0; }
        .aktif-info .aj { font-size:0.87rem;font-weight:500;color:#fff;line-height:1.3;margin-bottom:3px; }
        .aktif-info .am { font-size:0.73rem;color:var(--muted); }
        .radio-dot { width:18px;height:18px;border-radius:50%;border:2px solid var(--border);margin-left:auto;flex-shrink:0;transition:all .2s; }
        .aktif-card.selected .radio-dot { border-color:var(--accent);background:var(--accent);box-shadow:0 0 0 3px rgba(232,184,109,0.2); }

        /* Form kembalikan */
        .form-confirm { background:rgba(232,184,109,0.06);border:1px solid rgba(232,184,109,0.2);border-radius:12px;padding:18px;margin-top:14px; display:none; }
        .form-confirm.show { display:block; }
        .confirm-buku { display:flex;align-items:center;gap:12px;margin-bottom:14px;padding:12px;background:rgba(255,255,255,0.04);border-radius:9px; }
        .confirm-buku .cj { font-size:0.9rem;font-weight:600;color:#fff; }
        .confirm-buku .cm { font-size:0.75rem;color:var(--muted);margin-top:2px; }
        .btn-submit-kembali { width:100%;padding:12px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#d4a54e);color:#0c1520;font-weight:600;font-size:0.9rem;border:none;cursor:pointer;transition:transform .15s; }
        .btn-submit-kembali:hover { transform:translateY(-2px); }

        /* Alert */
        .alert-ok  { background:rgba(58,171,110,0.1);border:1px solid rgba(58,171,110,0.2);color:#75d8a0;border-radius:10px;padding:12px 16px;font-size:0.85rem;margin-bottom:18px; }
        .alert-err { background:rgba(232,93,93,0.1); border:1px solid rgba(232,93,93,0.2); color:#ff9090; border-radius:10px;padding:12px 16px;font-size:0.85rem;margin-bottom:18px; }

        /* Riwayat tabel */
        .tbl-mini { width:100%;border-collapse:collapse;font-size:0.8rem; }
        .tbl-mini th { padding:9px 14px;color:var(--muted);font-size:0.68rem;text-transform:uppercase;letter-spacing:.07em;border-bottom:1px solid var(--border);text-align:left; }
        .tbl-mini td { padding:10px 14px;border-bottom:1px solid rgba(255,255,255,0.04); }
        .tbl-mini tr:last-child td { border-bottom:none; }

        /* Empty */
        .empty-sm { text-align:center;padding:28px;color:var(--muted);font-size:0.85rem; }
        .empty-sm i { display:block;font-size:1.8rem;margin-bottom:8px;opacity:.3; }

        @media(max-width:768px){ .two-col { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container page-wrap">
    <h1 class="page-title">Pengembalian Buku</h1>
    <p class="page-sub">Catat pengembalian buku yang sedang kamu pinjam</p>

    <?php if ($msg): ?><div class="alert-ok"><i class="bi bi-check-circle me-2"></i><?= $msg ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert-err"><i class="bi bi-exclamation-circle me-2"></i><?= $error ?></div><?php endif; ?>

    <div class="two-col">

        <!-- KIRI: Form Kembalikan -->
        <div>
            <div class="panel-user">
                <div class="panel-head"><i class="bi bi-arrow-bar-left"></i><h3>Buku yang Sedang Dipinjam</h3></div>
                <div class="panel-body-p">
                    <?php if ($aktif && $aktif->num_rows > 0): ?>
                    <p style="font-size:0.82rem;color:var(--muted);margin-bottom:14px">Pilih buku yang ingin dikembalikan:</p>
                    <form method="POST" id="formKembali">
                        <input type="hidden" name="id_pinjam" id="selectedPinjam" value="">

                        <?php while ($a = $aktif->fetch_assoc()):
                            $katK = strtolower($a['kategori'] ?? '');
                            $cm   = $cover_map[$katK] ?? ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;">','class'=>'c-default'];
                            $isPreSelected = ($pre_pinjam == $a['id_pinjam']);
                        ?>
                        <div class="aktif-card <?= $isPreSelected?'selected':'' ?>"
                             onclick="selectBuku(this, <?= $a['id_pinjam'] ?>, '<?= addslashes(htmlspecialchars($a['nama_buku'])) ?>', '<?= date('d M Y',strtotime($a['tanggal_peminjaman'])) ?>', '<?= $cm['emoji'] ?>', '<?= $cm['class'] ?>')">
                            <div class="buku-cover <?= $cm['class'] ?> aktif-cover"><?= $cm['emoji'] ?></div>
                            <div class="aktif-info">
                                <div class="aj"><?= htmlspecialchars($a['nama_buku']) ?></div>
                                <div class="am"><i class="bi bi-calendar3 me-1"></i>Dipinjam: <?= date('d M Y', strtotime($a['tanggal_peminjaman'])) ?></div>
                            </div>
                            <div class="radio-dot"></div>
                        </div>
                        <?php endwhile; ?>

                        <!-- Form konfirmasi -->
                        <div class="form-confirm <?= $pre_data?'show':'' ?>" id="formConfirm">
                            <p style="font-size:0.8rem;color:var(--muted);margin-bottom:10px;font-weight:500;text-transform:uppercase;letter-spacing:.06em">Konfirmasi Pengembalian</p>
                            <div class="confirm-buku" id="confirmDetail">
                                <?php if ($pre_data): ?>
                                <?php
                                $pd = $conn->query("SELECT kategori FROM buku WHERE id_buku={$pre_data['id_buku']}")->fetch_assoc();
                                $pkatK = strtolower($pd['kategori']??'');
                                $pcm   = $cover_map[$pkatK] ?? ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;">','class'=>'c-default'];
                                ?>
                                <div class="buku-cover <?= $pcm['class'] ?> aktif-cover"><?= $pcm['emoji'] ?></div>
                                <div>
                                    <div class="cj"><?= htmlspecialchars($pre_data['nama_buku']) ?></div>
                                    <div class="cm">Dipinjam: <?= date('d M Y',strtotime($pre_data['tanggal_peminjaman'])) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div style="background:rgba(58,171,110,0.08);border:1px solid rgba(58,171,110,0.2);border-radius:8px;padding:10px 12px;font-size:0.8rem;color:#75d8a0;margin-bottom:14px">
                                <i class="bi bi-info-circle me-2"></i>Tanggal pengembalian: <strong><?= date('d M Y') ?></strong>
                            </div>
                            <button type="submit" class="btn-submit-kembali" onclick="return confirm('Konfirmasi pengembalian buku ini?')">
                                <i class="bi bi-check-lg me-2"></i>Konfirmasi Pengembalian
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="empty-sm">
                        <i class="bi bi-check-all"></i>
                        Tidak ada buku yang sedang dipinjam.
                        <br><a href="koleksi.php" style="color:var(--accent);text-decoration:none;margin-top:8px;display:inline-block">Pinjam buku sekarang →</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- KANAN: Riwayat Pengembalian -->
        <div>
            <div class="panel-user">
                <div class="panel-head"><i class="bi bi-clock-history"></i><h3>Riwayat Pengembalian</h3></div>
                <?php if ($riwayat && $riwayat->num_rows > 0): ?>
                <table class="tbl-mini">
                    <thead><tr><th>Buku</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php while ($r = $riwayat->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight:500;color:#fff;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($r['nama_buku']) ?></td>
                        <td style="color:var(--muted)"><?= date('d/m/Y', strtotime($r['tanggal_peminjaman'])) ?></td>
                        <td style="color:var(--muted)"><?= date('d/m/Y', strtotime($r['tanggal_pengembalian'])) ?></td>
                        <td>
                            <span style="padding:3px 9px;border-radius:20px;font-size:0.68rem;font-weight:600;background:<?= $r['status']==='Ada'?'rgba(58,171,110,0.12)':'rgba(232,93,93,0.12)' ?>;color:<?= $r['status']==='Ada'?'var(--green)':'var(--red)' ?>">
                                <?= $r['status']==='Ada' ? 'Kembali' : 'Tidak Ada' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-sm">
                    <i class="bi bi-inbox"></i>
                    Belum ada riwayat pengembalian.
                </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div style="background:rgba(74,158,255,0.07);border:1px solid rgba(74,158,255,0.15);border-radius:12px;padding:16px 18px;font-size:0.82rem;color:rgba(255,255,255,0.6);line-height:1.7">
                <div style="font-weight:600;color:var(--blue);margin-bottom:6px"><i class="bi bi-info-circle me-2"></i>Informasi</div>
                Pengembalian buku dilakukan secara mandiri melalui sistem ini. Pastikan buku sudah diserahkan ke petugas perpustakaan sebelum mengkonfirmasi pengembalian.
            </div>
        </div>
    </div>
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
<script>
window.addEventListener('scroll',()=>{document.getElementById('mainNav').classList.toggle('scrolled',window.scrollY>30);});

// Pre-select jika dari URL ?pinjam=
<?php if ($pre_pinjam && $pre_data): ?>
document.getElementById('selectedPinjam').value = <?= $pre_pinjam ?>;
<?php endif; ?>

function selectBuku(el, id, judul, tglPinjam, emoji, coverClass) {
    // Reset semua
    document.querySelectorAll('.aktif-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');

    // Set value hidden input
    document.getElementById('selectedPinjam').value = id;

    // Update konfirmasi detail
    document.getElementById('confirmDetail').innerHTML =
        `<div class="buku-cover ${coverClass} aktif-cover" style="width:44px;height:54px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:20px">${emoji}</div>
         <div><div class="cj">${judul}</div><div class="cm">Dipinjam: ${tglPinjam}</div></div>`;

    // Tampilkan form konfirmasi
    document.getElementById('formConfirm').classList.add('show');
}
</script>
</body>
</html>
