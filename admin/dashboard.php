<?php
session_start();
require_once '../config/database.php';
requirePustakawan(); // hanya pustakawan
if (isKepala()) redirect('dashboard-kepala.php');

$loggedIn=true; $namaUser=$_SESSION['nama']??''; $roleUser=$_SESSION['role']??''; $base='../';

// Stats
$s_buku      = $conn->query("SELECT COUNT(*) c FROM buku")->fetch_assoc()['c'];
$s_user      = $conn->query("SELECT COUNT(*) c FROM users WHERE role IN('siswa','guru')")->fetch_assoc()['c'];
$s_req       = $conn->query("SELECT COUNT(*) c FROM request_peminjaman WHERE status='menunggu'")->fetch_assoc()['c'];
$s_aktif     = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE status='Tidak Ada'")->fetch_assoc()['c'];
$s_terlambat = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE status='Tidak Ada' AND batas_kembali<CURDATE()")->fetch_assoc()['c'];
$s_kembali   = $conn->query("SELECT COUNT(*) c FROM pengembalian")->fetch_assoc()['c'];
$s_denda     = $conn->query("SELECT SUM(denda) s FROM pengembalian WHERE MONTH(tanggal_pengembalian)=MONTH(NOW())")->fetch_assoc()['s'] ?? 0;

// Request terbaru menunggu
$requests = $conn->query("SELECT r.*,b.nama_buku,b.gambar,b.kategori,u.nama AS nama_user,u.role AS role_user FROM request_peminjaman r JOIN buku b ON r.id_buku=b.id_buku JOIN users u ON r.id_user=u.id_user WHERE r.status='menunggu' ORDER BY r.id_request ASC LIMIT 5");

// Pengembalian terlambat
$terlambat = $conn->query("SELECT p.*,b.gambar,b.kategori,u.nama AS nama_user,DATEDIFF(CURDATE(),p.batas_kembali) AS hari FROM peminjaman p JOIN buku b ON p.id_buku=b.id_buku JOIN users u ON p.id_user=u.id_user WHERE p.status='Tidak Ada' AND p.batas_kembali<CURDATE() ORDER BY hari DESC LIMIT 5");

// Kategori chart
$kat_q = $conn->query("SELECT kategori,COUNT(*) t,SUM(stock) s FROM buku GROUP BY kategori");
$kl=[]; $kd=[]; $ks=[];
while($r=$kat_q->fetch_assoc()){ $kl[]=$r['kategori']; $kd[]=(int)$r['t']; $ks[]=(int)$r['s']; }
?><!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><title>Dashboard Pustakawan | E-Library</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php include 'sidebar.php'; ?>
<style>
.main-content{margin-left:230px;padding:30px;overflow-x:hidden;}
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;}
.topbar h1{font-family:'Playfair Display',serif;font-size:1.7rem;color:#fff;margin:0;}
.topbar .sub{font-size:.82rem;color:var(--muted);margin-top:3px;}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;}
.sc{background:var(--card);border:1px solid var(--border);border-radius:13px;padding:18px 16px;position:relative;overflow:hidden;transition:transform .2s;}
.sc:hover{transform:translateY(-3px);}
.sc::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;}
.sc.y::before{background:linear-gradient(90deg,var(--accent),var(--gold2));}
.sc.g::before{background:linear-gradient(90deg,var(--green),#5dd896);}
.sc.b::before{background:linear-gradient(90deg,var(--blue),#82c4ff);}
.sc.r::before{background:linear-gradient(90deg,var(--red),#ff9090);}
.sc.p::before{background:linear-gradient(90deg,var(--purple),#c4b5fd);}
.sc.o::before{background:linear-gradient(90deg,#f97316,#fb923c);}
.sc .ico{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1rem;margin-bottom:10px;}
.sc.y .ico{background:rgba(232,184,109,.12);color:var(--accent);}
.sc.g .ico{background:rgba(58,171,110,.12);color:var(--green);}
.sc.b .ico{background:rgba(74,158,255,.12);color:var(--blue);}
.sc.r .ico{background:rgba(232,93,93,.12);color:var(--red);}
.sc.p .ico{background:rgba(167,139,250,.12);color:var(--purple);}
.sc.o .ico{background:rgba(249,115,22,.12);color:#f97316;}
.sc .num{font-size:1.8rem;font-weight:700;color:#fff;line-height:1;}
.sc .num.small{font-size:1.1rem;}
.sc .lbl{font-size:.7rem;color:var(--muted);margin-top:3px;text-transform:uppercase;letter-spacing:.05em;}
.sc .badge-warn{position:absolute;top:12px;right:12px;background:rgba(232,93,93,.15);color:#ff9090;border-radius:10px;font-size:.65rem;font-weight:700;padding:2px 8px;}
.layout2{display:grid;grid-template-columns:1fr 320px;gap:16px;margin-bottom:16px;}
.layout3{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.qa{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:14px;}
.qa a{display:flex;align-items:center;gap:8px;padding:11px 13px;border-radius:9px;border:1px solid var(--border);background:rgba(255,255,255,.02);color:var(--text);text-decoration:none;font-size:.82rem;font-weight:500;transition:all .2s;}
.qa a:hover{background:rgba(232,184,109,.08);border-color:var(--accent);color:var(--accent);}
.qa a i{color:var(--accent);font-size:1rem;}
.req-item{display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid var(--border);}
.req-item:last-child{border-bottom:none;}
.mini-cover{width:38px;height:48px;border-radius:7px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;}
.mini-cover img.cover-img{width:100%;height:100%;object-fit:cover;}
.mini-cover img.logo-ph{width:24px;opacity:.6;}
.mc-umum{background:linear-gradient(135deg,#0d2540,#1a3d60);}
.mc-motivasi{background:linear-gradient(135deg,#2d1f00,#5a3c00);}
.mc-sastra{background:linear-gradient(135deg,#1f0d2d,#3d1a5a);}
.mc-religi{background:linear-gradient(135deg,#0d2d1a,#1a5a3c);}
.mc-default{background:linear-gradient(135deg,#1a1f2d,#2d3a4a);}
.btn-app{background:linear-gradient(135deg,var(--accent),#d4a54e);border:none;border-radius:7px;padding:5px 12px;font-size:.75rem;font-weight:600;color:#0c1520;cursor:pointer;white-space:nowrap;text-decoration:none;}
.btn-app:hover{opacity:.88;color:#0c1520;}
.tl-item{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border);}
.tl-item:last-child{border-bottom:none;}
.badge-tl{background:rgba(232,93,93,.15);color:#ff9090;border:1px solid rgba(232,93,93,.25);border-radius:20px;font-size:.65rem;font-weight:700;padding:2px 9px;white-space:nowrap;}
@media(max-width:1100px){.stat-grid{grid-template-columns:repeat(3,1fr);}.layout2{grid-template-columns:1fr;}.layout3{grid-template-columns:1fr;}}
@media(max-width:768px){.main-content{margin-left:0;}.stat-grid{grid-template-columns:repeat(2,1fr);}}
</style>
</head>
<body>
<div class="main-content">

<div class="topbar">
    <div>
        <h1>Dashboard Pustakawan</h1>
        <div class="sub">Selamat datang, <strong style="color:var(--accent)"><?= htmlspecialchars($namaUser) ?></strong> &mdash; <?= date('l, d F Y') ?></div>
    </div>
    <a href="buku.php?act=tambah" class="btn-app" style="padding:10px 18px;font-size:.85rem;"><i class="bi bi-plus-circle me-1"></i>Tambah Buku</a>
</div>

<!-- Stats -->
<div class="stat-grid">
    <div class="sc y"><div class="ico"><i class="bi bi-book-half"></i></div><div class="num"><?= $s_buku ?></div><div class="lbl">Total Buku</div></div>
    <div class="sc b"><div class="ico"><i class="bi bi-people-fill"></i></div><div class="num"><?= $s_user ?></div><div class="lbl">Anggota</div></div>
    <div class="sc r"><div class="ico"><i class="bi bi-inbox-fill"></i></div><div class="num"><?= $s_req ?></div><div class="lbl">Request Pending<?php if($s_req>0):?><span class="badge-warn">!</span><?php endif;?></div></div>
    <div class="sc b"><div class="ico"><i class="bi bi-arrow-bar-right"></i></div><div class="num"><?= $s_aktif ?></div><div class="lbl">Dipinjam Aktif</div></div>
    <div class="sc r"><div class="ico"><i class="bi bi-exclamation-triangle"></i></div><div class="num"><?= $s_terlambat ?></div><div class="lbl">Terlambat<?php if($s_terlambat>0):?><span class="badge-warn">!</span><?php endif;?></div></div>
    <div class="sc g"><div class="ico"><i class="bi bi-arrow-bar-left"></i></div><div class="num"><?= $s_kembali ?></div><div class="lbl">Dikembalikan</div></div>
    <div class="sc o"><div class="ico"><i class="bi bi-cash-coin"></i></div><div class="num small"><?= rupiah($s_denda) ?></div><div class="lbl">Denda Bulan Ini</div></div>
</div>

<div class="layout2">
    <!-- Kiri: request + terlambat -->
    <div style="display:flex;flex-direction:column;gap:14px;">
        <!-- Request menunggu -->
        <div class="panel">
            <div class="ph"><h3><i class="bi bi-inbox me-2" style="color:var(--accent)"></i>Request Menunggu Persetujuan</h3><a href="peminjaman.php?tab=request" style="font-size:.77rem;color:var(--muted);text-decoration:none;">Semua →</a></div>
            <div class="pb" style="padding:0 18px;">
            <?php if($requests->num_rows>0): while($r=$requests->fetch_assoc()):
                $kk=strtolower($r['kategori']??'');
            ?>
            <div class="req-item">
                <div class="mini-cover mc-<?= $kk ?: 'default' ?>" data-book-title="<?= htmlspecialchars($r['nama_buku']) ?>">
                    <?php if(!empty($r['gambar'])&&file_exists('../img/buku/'.$r['gambar'])): ?>
                        <img src="../img/buku/<?= htmlspecialchars($r['gambar']) ?>" class="cover-img" alt="">
                    <?php else: ?>
                        <img src="../img/logo-wandek.png" class="logo-ph" alt="">
                    <?php endif; ?>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:.85rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($r['nama_buku']) ?></div>
                    <div style="font-size:.72rem;color:var(--muted);"><?= htmlspecialchars($r['nama_user']) ?> · <?= date('d M',strtotime($r['tanggal_request'])) ?></div>
                </div>
                <a href="peminjaman.php?setujui=<?= $r['id_request'] ?>&tab=request" class="btn-app" onclick="return confirm('Setujui?')"><i class="bi bi-check-lg"></i></a>
            </div>
            <?php endwhile; else: ?>
            <div style="text-align:center;padding:22px;color:var(--muted);font-size:.85rem;"><i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:6px;opacity:.3;"></i>Tidak ada request menunggu</div>
            <?php endif; ?>
            </div>
        </div>
        <!-- Terlambat -->
        <div class="panel">
            <div class="ph"><h3><i class="bi bi-exclamation-triangle me-2" style="color:#ff9090"></i>Keterlambatan</h3><a href="pengembalian.php?tab=aktif">Proses →</a></div>
            <div class="pb" style="padding:0 18px;">
            <?php if($terlambat->num_rows>0): while($r=$terlambat->fetch_assoc()):?>
            <div class="tl-item">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:.83rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($r['nama_buku']) ?></div>
                    <div style="font-size:.71rem;color:var(--muted);"><?= htmlspecialchars($r['nama_user']) ?></div>
                </div>
                <span class="badge-tl"><?= $r['hari'] ?> hari · <?= rupiah($r['hari']*DENDA_PER_HARI) ?></span>
            </div>
            <?php endwhile; else: ?>
            <div style="text-align:center;padding:22px;color:var(--muted);font-size:.85rem;"><i class="bi bi-check-circle" style="font-size:1.4rem;display:block;margin-bottom:6px;opacity:.3;color:var(--green)"></i>Tidak ada keterlambatan</div>
            <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Kanan: chart + aksi cepat -->
    <div style="display:flex;flex-direction:column;gap:14px;">
        <div class="panel">
            <div class="ph"><h3><i class="bi bi-pie-chart me-2" style="color:var(--accent)"></i>Koleksi per Kategori</h3></div>
            <div style="padding:18px"><canvas id="katChart" height="200"></canvas></div>
        </div>
        <div class="panel">
            <div class="ph"><h3><i class="bi bi-lightning me-2" style="color:var(--accent)"></i>Aksi Cepat</h3></div>
            <div class="qa">
                <a href="buku.php?act=tambah"><i class="bi bi-plus-circle"></i> Tambah Buku</a>
                <a href="buku.php"><i class="bi bi-collection"></i> Kelola Buku</a>
                <a href="peminjaman.php"><i class="bi bi-arrow-bar-right"></i> Peminjaman</a>
                <a href="pengembalian.php"><i class="bi bi-arrow-bar-left"></i> Pengembalian</a>
                <a href="users.php"><i class="bi bi-people"></i> Data User</a>
                <a href="../koleksi.php" target="_blank"><i class="bi bi-search"></i> Cari Buku</a>
            </div>
        </div>
    </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
new Chart(document.getElementById('katChart'),{
    type:'doughnut',
    data:{labels:<?= json_encode($kl) ?>,datasets:[{data:<?= json_encode($kd) ?>,backgroundColor:['#e8b86d','#3aab6e','#4a9eff','#e85d5d','#a78bfa'],borderWidth:0,hoverOffset:6}]},
    options:{plugins:{legend:{position:'bottom',labels:{color:'#637a96',font:{size:11},padding:8,boxWidth:10}}},cutout:'65%'}
});
// Auto-fetch book covers
document.querySelectorAll('[data-book-title]').forEach(div=>{
    const ph=div.querySelector('.logo-ph');
    if(!ph) return;
    fetch(`https://www.googleapis.com/books/v1/volumes?q=${encodeURIComponent(div.dataset.bookTitle)}&maxResults=1`)
    .then(r=>r.json()).then(d=>{
        const t=d.items?.[0]?.volumeInfo?.imageLinks?.thumbnail;
        if(t){const img=document.createElement('img');img.src=t.replace('http:','https:');img.className='cover-img';img.style.cssText='width:100%;height:100%;object-fit:cover;';ph.replaceWith(img);}
    }).catch(()=>{});
});
</script>
</body></html>
