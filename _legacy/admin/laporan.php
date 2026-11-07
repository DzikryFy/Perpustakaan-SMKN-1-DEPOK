<?php
session_start();
require_once '../config/database.php';
requireKepala(); // hanya kepala perpustakaan
$base='../'; $loggedIn=true; $namaUser=$_SESSION['nama']??''; $roleUser=$_SESSION['role']??'';

// ── STATISTIK UTAMA ───────────────────────────────────────────
$total_buku    = $conn->query("SELECT COUNT(*) c FROM buku")->fetch_assoc()['c'];
$total_stok    = $conn->query("SELECT SUM(stock) s FROM buku")->fetch_assoc()['s'] ?? 0;
$total_user    = $conn->query("SELECT COUNT(*) c FROM users WHERE role IN('siswa','guru')")->fetch_assoc()['c'];
$total_pinjam  = $conn->query("SELECT COUNT(*) c FROM peminjaman")->fetch_assoc()['c'];
$aktif_pinjam  = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE status='Tidak Ada'")->fetch_assoc()['c'];
$total_kembali = $conn->query("SELECT COUNT(*) c FROM pengembalian")->fetch_assoc()['c'];
$total_denda   = $conn->query("SELECT SUM(denda) s FROM pengembalian")->fetch_assoc()['s'] ?? 0;
$terlambat     = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE status='Tidak Ada' AND batas_kembali < CURDATE()")->fetch_assoc()['c'];
$request_pending = $conn->query("SELECT COUNT(*) c FROM request_peminjaman WHERE status='menunggu'")->fetch_assoc()['c'];
$saran_baru    = $conn->query("SELECT COUNT(*) c FROM saran_buku WHERE status='baru'")->fetch_assoc()['c'];

// ── BUKU TERPOPULER ───────────────────────────────────────────
$buku_populer = $conn->query("SELECT b.nama_buku,b.kategori,b.gambar,COUNT(p.id_pinjam) AS total FROM peminjaman p JOIN buku b ON p.id_buku=b.id_buku GROUP BY p.id_buku,b.nama_buku,b.kategori,b.gambar ORDER BY total DESC LIMIT 8");

// ── PEMINJAMAN PER BULAN (12 bulan terakhir) ──────────────────
$bulan_data = $conn->query("SELECT DATE_FORMAT(tanggal_peminjaman,'%b %Y') AS bln, COUNT(*) AS cnt, YEAR(tanggal_peminjaman) AS y, MONTH(tanggal_peminjaman) AS m FROM peminjaman WHERE tanggal_peminjaman >= DATE_SUB(NOW(),INTERVAL 11 MONTH) GROUP BY y,m,bln ORDER BY y ASC,m ASC");
$chart_labels=[]; $chart_vals=[];
while($r=$bulan_data->fetch_assoc()){ $chart_labels[]=$r['bln']; $chart_vals[]=$r['cnt']; }

// ── KATEGORI DISTRIBUSI ───────────────────────────────────────
$kat_dist = $conn->query("SELECT kategori,COUNT(*) cnt,SUM(stock) stok FROM buku GROUP BY kategori ORDER BY cnt DESC");

// ── USER AKTIF (banyak pinjam) ────────────────────────────────
$user_aktif = $conn->query("SELECT u.nama,u.role,COUNT(p.id_pinjam) total FROM peminjaman p JOIN users u ON p.id_user=u.id_user GROUP BY p.id_user ORDER BY total DESC LIMIT 8");

// ── PEMINJAMAN TERLAMBAT ──────────────────────────────────────
$terlambat_list = $conn->query("SELECT p.*,b.nama_buku,u.nama AS nama_user,DATEDIFF(CURDATE(),p.batas_kembali) AS hari_tl FROM peminjaman p JOIN buku b ON p.id_buku=b.id_buku JOIN users u ON p.id_user=u.id_user WHERE p.status='Tidak Ada' AND p.batas_kembali<CURDATE() ORDER BY hari_tl DESC LIMIT 20");

// ── SARAN BUKU TERBARU ────────────────────────────────────────
$saran_list = $conn->query("SELECT s.*,u.nama AS nama_user,u.role FROM saran_buku s JOIN users u ON s.id_user=u.id_user ORDER BY s.id_saran DESC LIMIT 20");

$bulan_ini = date('F Y');
?><!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><title>Laporan | E-Library Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="../style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
body{display:flex;min-height:100vh;}
.admin-wrap{display:flex;width:100%;min-height:100vh;}
.main-content{flex:1;padding:32px;overflow-x:hidden;}
.page-title{font-family:'Playfair Display',serif;font-size:1.7rem;color:#fff;margin-bottom:4px;}
.stat-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:28px;}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:13px;padding:16px 14px;text-align:center;}
.stat-card .sn{font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:#fff;line-height:1;}
.stat-card .sl{font-size:.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-top:4px;}
.stat-card.warn .sn{color:#ff9090;}
.stat-card.gold .sn{color:var(--accent);}
.stat-card.green .sn{color:var(--green);}
.row2{display:grid;grid-template-columns:2fr 1fr;gap:18px;margin-bottom:18px;}
.row3{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;}
.card-block{background:var(--card);border:1px solid var(--border);border-radius:13px;padding:20px;}
.card-block h3{font-family:'Playfair Display',serif;font-size:1.1rem;color:#fff;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.badge-role{display:inline-block;padding:2px 9px;border-radius:12px;font-size:.67rem;font-weight:600;}
.role-siswa{background:rgba(58,171,110,.12);color:var(--green);}
.role-guru{background:rgba(74,158,255,.12);color:var(--blue);}
.badge-baru{background:rgba(232,184,109,.12);color:var(--accent);border:1px solid rgba(232,184,109,.2);border-radius:12px;font-size:.67rem;padding:2px 9px;font-weight:600;}
.badge-ditinjau{background:rgba(74,158,255,.12);color:var(--blue);border:1px solid rgba(74,158,255,.2);border-radius:12px;font-size:.67rem;padding:2px 9px;font-weight:600;}
.badge-ok{background:rgba(58,171,110,.12);color:var(--green);border:1px solid rgba(58,171,110,.2);border-radius:12px;font-size:.67rem;padding:2px 9px;font-weight:600;}
.badge-no{background:rgba(232,93,93,.1);color:#ff9090;border:1px solid rgba(232,93,93,.2);border-radius:12px;font-size:.67rem;padding:2px 9px;font-weight:600;}
.pop-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);}
.pop-row:last-child{border-bottom:none;}
.pop-cover{width:38px;height:48px;border-radius:7px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:18px;}
.pop-cover img{width:100%;height:100%;object-fit:cover;}
.pop-cover.c-umum{background:linear-gradient(135deg,#0d2540,#1a3d60);}
.pop-cover.c-motivasi{background:linear-gradient(135deg,#2d1f00,#5a3c00);}
.pop-cover.c-sastra{background:linear-gradient(135deg,#1f0d2d,#3d1a5a);}
.pop-cover.c-religi{background:linear-gradient(135deg,#0d2d1a,#1a5a3c);}
.pop-cover.c-default{background:linear-gradient(135deg,#1a1f2d,#2d3a4a);}
.bar-fill{height:6px;border-radius:3px;background:linear-gradient(90deg,var(--accent),#d4a54e);margin-top:4px;}
.terlambat-row{background:rgba(232,93,93,.04);border-bottom:1px solid var(--border);padding:10px 0;}
.terlambat-row:last-child{border-bottom:none;}
.print-btn{background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--muted);border-radius:9px;padding:8px 16px;font-size:.8rem;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:6px;}
.print-btn:hover{border-color:var(--accent);color:var(--accent);}
@media(max-width:1100px){.stat-grid{grid-template-columns:repeat(3,1fr);}.row2{grid-template-columns:1fr;}.row3{grid-template-columns:1fr;}}
@media(max-width:700px){.stat-grid{grid-template-columns:repeat(2,1fr);}}
</style>
</head><body>
<?php include 'sidebar.php'; ?>
<div class="admin-wrap"><div class="main-content">

<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <div class="sec-label" style="margin-bottom:4px;">Laporan Perpustakaan</div>
        <h1 class="page-title">Laporan & Statistik</h1>
        <p style="color:var(--muted);font-size:.82rem;margin-top:4px;">Per tanggal: <?= date('d F Y') ?></p>
    </div>
    <button class="print-btn" onclick="window.print()"><i class="bi bi-printer"></i> Cetak Laporan</button>
</div>

<!-- Stat Cards -->
<div class="stat-grid">
    <div class="stat-card gold"><div class="sn"><?= $total_buku ?></div><div class="sl">Total Judul Buku</div></div>
    <div class="stat-card"><div class="sn"><?= $total_stok ?></div><div class="sl">Total Stok</div></div>
    <div class="stat-card"><div class="sn"><?= $total_user ?></div><div class="sl">Anggota Aktif</div></div>
    <div class="stat-card green"><div class="sn"><?= $total_pinjam ?></div><div class="sl">Total Peminjaman</div></div>
    <div class="stat-card warn"><div class="sn"><?= $terlambat ?></div><div class="sl">Keterlambatan</div></div>
    <div class="stat-card"><div class="sn"><?= $aktif_pinjam ?></div><div class="sl">Sedang Dipinjam</div></div>
    <div class="stat-card"><div class="sn"><?= $total_kembali ?></div><div class="sl">Total Kembali</div></div>
    <div class="stat-card gold"><div class="sn" style="font-size:1.2rem;"><?= rupiah($total_denda) ?></div><div class="sl">Total Denda</div></div>
    <div class="stat-card warn"><div class="sn"><?= $request_pending ?></div><div class="sl">Request Pending</div></div>
    <div class="stat-card"><div class="sn"><?= $saran_baru ?></div><div class="sl">Saran Buku Baru</div></div>
</div>

<!-- Grafik Peminjaman & Populer -->
<div class="row2">
    <div class="card-block">
        <h3><i class="bi bi-bar-chart-line" style="color:var(--accent)"></i> Tren Peminjaman (12 Bulan Terakhir)</h3>
        <canvas id="chartPinjam" height="80"></canvas>
    </div>
    <div class="card-block">
        <h3><i class="bi bi-pie-chart" style="color:var(--accent)"></i> Distribusi Kategori</h3>
        <canvas id="chartKat" height="180"></canvas>
    </div>
</div>

<!-- Buku Populer & User Aktif -->
<div class="row3">
    <div class="card-block">
        <h3><i class="bi bi-trophy" style="color:var(--accent)"></i> Buku Terpopuler</h3>
        <?php
        $max_pop = 1;
        $pop_rows = [];
        while($r=$buku_populer->fetch_assoc()) { $pop_rows[]=$r; if($r['total']>$max_pop) $max_pop=$r['total']; }
        foreach($pop_rows as $r):
            $kk=strtolower($r['kategori']??'');
            $cmap=['umum'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-umum'],'motivasi'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-motivasi'],'sastra'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-sastra'],'religi'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-religi']];
            $cm=$cmap[$kk]??['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-default'];
            $pct=round($r['total']/$max_pop*100);
        ?>
        <div class="pop-row">
            <div class="pop-cover <?= $cm['c'] ?>"><?php if(!empty($r['gambar'])&&file_exists('../img/buku/'.$r['gambar'])): ?><img src="../img/buku/<?= htmlspecialchars($r['gambar']) ?>"><?php else: ?><?= $cm['e'] ?><?php endif; ?></div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.83rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($r['nama_buku']) ?></div>
                <div class="bar-fill" style="width:<?= $pct ?>%"></div>
            </div>
            <div style="font-size:.88rem;font-weight:700;color:var(--accent);flex-shrink:0;"><?= $r['total'] ?>x</div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="card-block">
        <h3><i class="bi bi-person-check" style="color:var(--accent)"></i> Anggota Paling Aktif</h3>
        <?php while($r=$user_aktif->fetch_assoc()): ?>
        <div class="pop-row">
            <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#d4a54e);display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;color:#0c1520;flex-shrink:0;"><?= strtoupper(substr($r['nama'],0,1)) ?></div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.83rem;font-weight:600;color:#fff;"><?= htmlspecialchars($r['nama']) ?></div>
                <span class="badge-role role-<?= $r['role'] ?>"><?= roleLabel($r['role']) ?></span>
            </div>
            <div style="font-size:.88rem;font-weight:700;color:var(--accent);"><?= $r['total'] ?>x</div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Keterlambatan -->
<?php if($terlambat_list->num_rows>0): ?>
<div class="card-block" style="margin-bottom:18px;">
    <h3><i class="bi bi-exclamation-triangle" style="color:#ff9090"></i> Daftar Keterlambatan <span style="font-size:.8rem;font-weight:400;color:#ff9090">(<?= $terlambat_list->num_rows ?> buku)</span></h3>
    <?php while($r=$terlambat_list->fetch_assoc()): ?>
    <div class="terlambat-row">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <div>
                <div style="font-size:.85rem;font-weight:600;color:#fff;"><?= htmlspecialchars($r['nama_buku']) ?></div>
                <div style="font-size:.73rem;color:var(--muted);margin-top:2px;">
                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($r['nama_user']) ?>
                    &nbsp;·&nbsp; Batas: <?= date('d M Y',strtotime($r['batas_kembali'])) ?>
                    &nbsp;·&nbsp; Denda est: <?= rupiah($r['hari_tl']*DENDA_PER_HARI) ?>
                </div>
            </div>
            <span style="background:rgba(232,93,93,.15);color:#ff9090;border-radius:20px;font-size:.72rem;font-weight:700;padding:4px 12px;"><?= $r['hari_tl'] ?> hari terlambat</span>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<!-- Saran Buku -->
<div class="card-block">
    <h3><i class="bi bi-lightbulb" style="color:var(--accent)"></i> Saran Buku dari Anggota</h3>
    <?php if($saran_list->num_rows>0): while($r=$saran_list->fetch_assoc()): ?>
    <div style="padding:12px 0;border-bottom:1px solid var(--border);">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;flex-wrap:wrap;">
            <div style="flex:1;min-width:0;">
                <div style="font-size:.88rem;font-weight:600;color:#fff;"><?= htmlspecialchars($r['judul_saran']) ?></div>
                <?php if($r['penulis_saran']): ?><div style="font-size:.75rem;color:var(--muted);">Penulis: <?= htmlspecialchars($r['penulis_saran']) ?></div><?php endif; ?>
                <?php if($r['alasan']): ?><div style="font-size:.77rem;color:var(--muted);margin-top:4px;font-style:italic;">"<?= htmlspecialchars($r['alasan']) ?>"</div><?php endif; ?>
                <div style="font-size:.72rem;color:var(--muted);margin-top:5px;">
                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($r['nama_user']) ?> (<?= roleLabel($r['role']) ?>)
                    &nbsp;·&nbsp; <?= date('d M Y',strtotime($r['tanggal_saran'])) ?>
                </div>
            </div>
            <div style="flex-shrink:0;">
                <?php
                $bs=['baru'=>'badge-baru','ditinjau'=>'badge-ditinjau','disetujui'=>'badge-ok','ditolak'=>'badge-no'];
                $bl=['baru'=>'Baru','ditinjau'=>'Ditinjau','disetujui'=>'Disetujui','ditolak'=>'Ditolak'];
                $st=$r['status'];
                echo '<span class="'.($bs[$st]??'badge-baru').'">'.($bl[$st]??$st).'</span>';
                ?>
            </div>
        </div>
    </div>
    <?php endwhile; else: ?>
    <p style="color:var(--muted);font-size:.85rem;text-align:center;padding:24px 0;">Belum ada saran buku.</p>
    <?php endif; ?>
</div>

</div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Chart Peminjaman
new Chart(document.getElementById('chartPinjam'),{
    type:'bar',
    data:{
        labels:<?= json_encode($chart_labels) ?>,
        datasets:[{label:'Peminjaman',data:<?= json_encode($chart_vals) ?>,backgroundColor:'rgba(232,184,109,.3)',borderColor:'#e8b86d',borderWidth:2,borderRadius:6}]
    },
    options:{responsive:true,plugins:{legend:{display:false}},scales:{x:{ticks:{color:'#637a96'},grid:{color:'rgba(255,255,255,.04)'}},y:{ticks:{color:'#637a96'},grid:{color:'rgba(255,255,255,.04)'}}}}
});
// Chart Kategori
<?php
$kat_labels=[]; $kat_vals=[]; $kat_colors=['#e8b86d','#4a9eff','#a78bfa','#3aab6e','#e85d5d'];
$ki=0; $kat_dist->data_seek(0);
while($r=$kat_dist->fetch_assoc()){ $kat_labels[]=$r['kategori']; $kat_vals[]=(int)$r['cnt']; $ki++; }
?>
new Chart(document.getElementById('chartKat'),{
    type:'doughnut',
    data:{labels:<?= json_encode($kat_labels) ?>,datasets:[{data:<?= json_encode($kat_vals) ?>,backgroundColor:['#e8b86d','#4a9eff','#a78bfa','#3aab6e','#e85d5d'],borderWidth:0,hoverOffset:8}]},
    options:{responsive:true,plugins:{legend:{position:'bottom',labels:{color:'#637a96',font:{size:11}}}}}
});
</script>
<script>
(function(){
function fetchCover(div){
    var title=div.dataset.bookTitle; if(!title) return;
    var ph=div.querySelector('img.logo-ph'); if(!ph) return;
    fetch('https://www.googleapis.com/books/v1/volumes?q='+encodeURIComponent(title)+'&maxResults=1')
    .then(function(r){return r.json();}).then(function(d){
        var t=d.items&&d.items[0]&&d.items[0].volumeInfo&&d.items[0].volumeInfo.imageLinks&&d.items[0].volumeInfo.imageLinks.thumbnail;
        if(t){var img=document.createElement('img');img.src=t.replace('http:','https:');img.className='cover-img';img.style.cssText='width:100%;height:100%;object-fit:cover;';img.onerror=function(){img.remove();};ph.parentNode.replaceChild(img,ph);}
    }).catch(function(){});
}
var divs=document.querySelectorAll('[data-book-title]');
divs.forEach(function(div,i){setTimeout(function(){fetchCover(div);},i*150);});
})();
</script>
</body></html>
