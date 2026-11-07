<?php
session_start();
require_once '../config/database.php';
requireStaff();

$total_buku    = $conn->query("SELECT COUNT(*) AS c FROM buku")->fetch_assoc()['c'];
$total_user    = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role IN('siswa','guru')")->fetch_assoc()['c'];
$total_pinjam  = $conn->query("SELECT COUNT(*) AS c FROM peminjaman WHERE status='Tidak Ada'")->fetch_assoc()['c'];
$total_kembali = $conn->query("SELECT COUNT(*) AS c FROM pengembalian WHERE status='Ada'")->fetch_assoc()['c'];

$recent = $conn->query("
    SELECT p.*, u.nama AS nama_user, u.role
    FROM peminjaman p
    JOIN users u ON p.id_user = u.id_user
    ORDER BY p.id_pinjam DESC LIMIT 7
");

$kat = $conn->query("SELECT kategori, COUNT(*) AS total, SUM(stock) AS stok FROM buku GROUP BY kategori");
$kat_labels = []; $kat_data = [];
while ($r = $kat->fetch_assoc()) { $kat_labels[] = $r['kategori']; $kat_data[] = (int)$r['total']; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Admin E-Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <?php include 'sidebar.php'; // loads shared styles ?>
    <style>
    .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;}
    .sc{background:var(--card);border:1px solid var(--border);border-radius:13px;padding:20px 18px;position:relative;overflow:hidden;transition:transform .2s;}
    .sc:hover{transform:translateY(-3px);}
    .sc::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;}
    .sc.y::before{background:linear-gradient(90deg,var(--accent),var(--gold2));}
    .sc.g::before{background:linear-gradient(90deg,var(--green),#5dd896);}
    .sc.b::before{background:linear-gradient(90deg,var(--blue),#82c4ff);}
    .sc.r::before{background:linear-gradient(90deg,var(--red),#ff9090);}
    .sc .ico{width:40px;height:40px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:12px;}
    .sc.y .ico{background:rgba(232,184,109,0.12);color:var(--accent);}
    .sc.g .ico{background:rgba(58,171,110,0.12);color:var(--green);}
    .sc.b .ico{background:rgba(74,158,255,0.12);color:var(--blue);}
    .sc.r .ico{background:rgba(232,93,93,0.12);color:var(--red);}
    .sc .num{font-size:1.9rem;font-weight:700;color:#fff;line-height:1;}
    .sc .lbl{font-size:0.73rem;color:var(--muted);margin-top:4px;text-transform:uppercase;letter-spacing:.05em;}
    .g2{display:grid;grid-template-columns:1fr 300px;gap:16px;}
    .qa{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:14px;}
    .qa a{display:flex;align-items:center;gap:8px;padding:12px;border-radius:9px;border:1px solid var(--border);background:rgba(255,255,255,0.02);color:var(--text);text-decoration:none;font-size:0.82rem;font-weight:500;transition:all .2s;}
    .qa a:hover{background:rgba(232,184,109,0.08);border-color:var(--accent);color:var(--accent);}
    .qa a i{color:var(--accent);font-size:1rem;}
    @media(max-width:1050px){.stats-grid{grid-template-columns:repeat(2,1fr);}.g2{grid-template-columns:1fr;}}
    </style>
</head>
<body>
<div class="main">
    <div class="topbar">
        <div><h1>Dashboard</h1><div class="bc">Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?> 👋</div></div>
        <div class="user-chip">
            <div class="av"><?= strtoupper(substr($_SESSION['nama'],0,1)) ?></div>
            <span><?= htmlspecialchars($_SESSION['nama']) ?></span>
        </div>
    </div>
    <div class="content">

        <!-- STATS -->
        <div class="stats-grid">
            <div class="sc y"><div class="ico"><i class="bi bi-book-half"></i></div><div class="num"><?= $total_buku ?></div><div class="lbl">Total Buku</div></div>
            <div class="sc g"><div class="ico"><i class="bi bi-people-fill"></i></div><div class="num"><?= $total_user ?></div><div class="lbl">Total User</div></div>
            <div class="sc b"><div class="ico"><i class="bi bi-arrow-bar-right"></i></div><div class="num"><?= $total_pinjam ?></div><div class="lbl">Sedang Dipinjam</div></div>
            <div class="sc r"><div class="ico"><i class="bi bi-arrow-bar-left"></i></div><div class="num"><?= $total_kembali ?></div><div class="lbl">Dikembalikan</div></div>
        </div>

        <div class="g2">
            <!-- Tabel peminjaman terbaru -->
            <div class="panel">
                <div class="ph"><h3><i class="bi bi-clock-history me-2" style="color:var(--accent)"></i>Peminjaman Terbaru</h3><a href="peminjaman.php">Lihat semua →</a></div>
                <table class="tbl">
                    <thead><tr><th>User</th><th>Buku</th><th>Tgl Pinjam</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if ($recent->num_rows > 0): while($r = $recent->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div style="font-weight:500;color:#fff"><?= htmlspecialchars($r['nama_user']) ?></div>
                            <div style="font-size:0.71rem;color:var(--muted)"><?= ucfirst($r['role']) ?></div>
                        </td>
                        <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($r['nama_buku']) ?></td>
                        <td><?= date('d/m/Y', strtotime($r['tanggal_peminjaman'])) ?></td>
                        <td><span class="<?= $r['status']==='Ada'?'badge-ada':'badge-tidak' ?>"><?= $r['status'] ?></span></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:28px">Belum ada data</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Right -->
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div class="panel">
                    <div class="ph"><h3><i class="bi bi-pie-chart me-2" style="color:var(--accent)"></i>Koleksi per Kategori</h3></div>
                    <div style="padding:18px"><canvas id="katChart" height="190"></canvas></div>
                </div>
                <div class="panel">
                    <div class="ph"><h3><i class="bi bi-lightning me-2" style="color:var(--accent)"></i>Aksi Cepat</h3></div>
                    <div class="qa">
                        <a href="buku.php?act=tambah"><i class="bi bi-plus-circle"></i> Tambah Buku</a>
                        <a href="users.php"><i class="bi bi-people"></i> Data User</a>
                        <a href="peminjaman.php?act=tambah"><i class="bi bi-arrow-bar-right"></i> Catat Pinjam</a>
                        <a href="pengembalian.php?act=tambah"><i class="bi bi-arrow-bar-left"></i> Catat Kembali</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<script>
new Chart(document.getElementById('katChart').getContext('2d'), {
    type:'doughnut',
    data:{
        labels: <?= json_encode($kat_labels) ?>,
        datasets:[{data:<?= json_encode($kat_data) ?>,backgroundColor:['#e8b86d','#3aab6e','#4a9eff','#e85d5d','#b46de8'],borderWidth:0,hoverOffset:6}]
    },
    options:{plugins:{legend:{position:'bottom',labels:{color:'#7a8fa6',font:{size:11},padding:10,boxWidth:10}}},cutout:'65%'}
});
</script>
</body>
</html>
