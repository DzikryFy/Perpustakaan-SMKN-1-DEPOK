<?php
session_start();
require_once '../config/database.php';
requireKepala();
$base='../'; $loggedIn=true; $namaUser=$_SESSION['nama']??''; $roleUser=$_SESSION['role']??'';

// Stats
$s_buku      = $conn->query("SELECT COUNT(*) c FROM buku")->fetch_assoc()['c'];
$s_stok      = $conn->query("SELECT SUM(stock) s FROM buku")->fetch_assoc()['s']??0;
$s_user      = $conn->query("SELECT COUNT(*) c FROM users WHERE role IN('siswa','guru')")->fetch_assoc()['c'];
$s_aktif     = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE status='Tidak Ada'")->fetch_assoc()['c'];
$s_terlambat = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE status='Tidak Ada' AND batas_kembali<CURDATE()")->fetch_assoc()['c'];
$s_saran     = $conn->query("SELECT COUNT(*) c FROM saran_buku WHERE status='baru'")->fetch_assoc()['c'];
$s_denda     = $conn->query("SELECT SUM(denda) s FROM pengembalian")->fetch_assoc()['s']??0;

// Saran buku (semua, terbaru)
$saranList = $conn->query("SELECT s.*,u.nama AS nama_user,u.role FROM saran_buku s JOIN users u ON s.id_user=u.id_user ORDER BY FIELD(s.status,'baru','ditinjau','disetujui','ditolak'), s.id_saran DESC LIMIT 20");

// Koleksi buku (search)
$q   = htmlspecialchars($_GET['q']??'');
$kat = htmlspecialchars($_GET['kat']??'');
$sq  = $conn->real_escape_string($q);
$sk  = $conn->real_escape_string($kat);
$wh  = "WHERE 1";
if($sq) $wh.=" AND nama_buku LIKE '%$sq%'";
if($sk) $wh.=" AND kategori='$sk'";
$bukuList = $conn->query("SELECT * FROM buku $wh ORDER BY nama_buku ASC LIMIT 40");
$katList  = $conn->query("SELECT DISTINCT kategori FROM buku ORDER BY kategori");

// Laporan ringkas
$top_buku = $conn->query("SELECT b.nama_buku,b.gambar,COUNT(p.id_pinjam) t FROM peminjaman p JOIN buku b ON p.id_buku=b.id_buku GROUP BY p.id_buku,b.nama_buku,b.gambar ORDER BY t DESC LIMIT 5");
$terlambat_list = $conn->query("SELECT p.nama_buku,u.nama AS nm,DATEDIFF(CURDATE(),p.batas_kembali) AS hari FROM peminjaman p JOIN users u ON p.id_user=u.id_user WHERE p.status='Tidak Ada' AND p.batas_kembali<CURDATE() ORDER BY hari DESC LIMIT 5");
$bulan_data = $conn->query("SELECT DATE_FORMAT(tanggal_peminjaman,'%b %Y') AS bln,COUNT(*) AS cnt,YEAR(tanggal_peminjaman) y,MONTH(tanggal_peminjaman) m FROM peminjaman WHERE tanggal_peminjaman>=DATE_SUB(NOW(),INTERVAL 5 MONTH) GROUP BY y,m,bln ORDER BY y,m");
$chart_l=[]; $chart_v=[];
while($r=$bulan_data->fetch_assoc()){ $chart_l[]=$r['bln']; $chart_v[]=$r['cnt']; }

// Handle saran approval
$msg='';
if(isset($_GET['saran_status']) && isset($_GET['saran_id'])){
    $sid=(int)$_GET['saran_id'];
    $st=$_GET['saran_status'];
    if(in_array($st,['ditinjau','disetujui','ditolak'])){
        $conn->query("UPDATE saran_buku SET status='$st' WHERE id_saran=$sid");
        $msg="Status saran berhasil diperbarui.";
        header("Location: dashboard-kepala.php?msg=ok#saran"); exit;
    }
}
if(isset($_GET['msg'])&&$_GET['msg']==='ok') $msg="Status saran berhasil diperbarui.";
?><!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><title>Dashboard Kepala Perpustakaan | E-Library</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php include 'sidebar.php'; ?>
<style>
.main-content{margin-left:230px;padding:30px;overflow-x:hidden;}
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:26px;flex-wrap:wrap;gap:12px;}
.topbar h1{font-family:'Playfair Display',serif;font-size:1.7rem;color:#fff;margin:0;}
.topbar .sub{font-size:.82rem;color:var(--muted);margin-top:3px;}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px;}
.sc{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px;position:relative;overflow:hidden;transition:transform .2s;}
.sc:hover{transform:translateY(-2px);}
.sc::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;}
.sc.y::before{background:linear-gradient(90deg,var(--accent),var(--gold2));}.sc.g::before{background:linear-gradient(90deg,var(--green),#5dd896);}
.sc.b::before{background:linear-gradient(90deg,var(--blue),#82c4ff);}.sc.r::before{background:linear-gradient(90deg,var(--red),#ff9090);}
.sc.p::before{background:linear-gradient(90deg,var(--purple),#c4b5fd);}
.sc .ico{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.95rem;margin-bottom:8px;}
.sc.y .ico{background:rgba(232,184,109,.12);color:var(--accent);}.sc.g .ico{background:rgba(58,171,110,.12);color:var(--green);}
.sc.b .ico{background:rgba(74,158,255,.12);color:var(--blue);}.sc.r .ico{background:rgba(232,93,93,.12);color:var(--red);}
.sc.p .ico{background:rgba(167,139,250,.12);color:var(--purple);}
.sc .num{font-size:1.7rem;font-weight:700;color:#fff;line-height:1;}.sc .lbl{font-size:.68rem;color:var(--muted);margin-top:2px;text-transform:uppercase;letter-spacing:.05em;}
.bw{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;}
.bw3{display:grid;grid-template-columns:1.4fr 1fr;gap:16px;margin-bottom:16px;}
.alert-ok{background:rgba(58,171,110,.1);border:1px solid rgba(58,171,110,.3);color:#5dd896;border-radius:10px;padding:10px 16px;margin-bottom:18px;font-size:.84rem;}
/* Saran */
.saran-row{display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);}
.saran-row:last-child{border-bottom:none;}
.s-avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#d4a54e);display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;color:#0c1520;flex-shrink:0;}
.badge-baru{background:rgba(232,184,109,.12);color:var(--accent);border:1px solid rgba(232,184,109,.2);border-radius:12px;font-size:.64rem;padding:2px 8px;font-weight:600;}
.badge-ditinjau{background:rgba(74,158,255,.12);color:var(--blue);border:1px solid rgba(74,158,255,.2);border-radius:12px;font-size:.64rem;padding:2px 8px;font-weight:600;}
.badge-ok{background:rgba(58,171,110,.12);color:var(--green);border:1px solid rgba(58,171,110,.2);border-radius:12px;font-size:.64rem;padding:2px 8px;font-weight:600;}
.badge-no{background:rgba(232,93,93,.1);color:#ff9090;border:1px solid rgba(232,93,93,.2);border-radius:12px;font-size:.64rem;padding:2px 8px;font-weight:600;}
.saran-actions{display:flex;gap:5px;flex-shrink:0;flex-wrap:wrap;}
.ab{font-size:.71rem;border:none;border-radius:7px;padding:4px 10px;cursor:pointer;font-weight:600;text-decoration:none;transition:all .2s;}
.ab-tinjau{background:rgba(74,158,255,.12);color:var(--blue);}.ab-ok{background:rgba(58,171,110,.12);color:var(--green);}
.ab-no{background:rgba(232,93,93,.1);color:#ff9090;}
/* Koleksi search */
.srch-bar{display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;}
.srch-bar input{flex:1;min-width:180px;background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--text);border-radius:8px;padding:8px 12px;font-size:.83rem;font-family:'DM Sans',sans-serif;}
.srch-bar input:focus{outline:none;border-color:var(--accent);}
.srch-bar select{background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--text);border-radius:8px;padding:8px 12px;font-size:.83rem;font-family:'DM Sans',sans-serif;}
.srch-bar select option{background:#152135;}
.srch-bar button{background:var(--accent);border:none;border-radius:8px;padding:8px 14px;color:#0c1520;font-weight:600;cursor:pointer;}
.buku-mini-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:10px;max-height:420px;overflow-y:auto;}
.bm-card{border-radius:10px;overflow:hidden;border:1px solid var(--border);background:var(--card);transition:border-color .2s;}
.bm-card:hover{border-color:rgba(232,184,109,.25);}
.bm-cover{height:90px;background:linear-gradient(135deg,#0d2540,#1a3d60);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;}
.bm-cover img.cover-img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0;}
.bm-cover img.logo-ph{width:26px;opacity:.6;}
.stk-dot{position:absolute;top:5px;right:5px;width:8px;height:8px;border-radius:50%;}
.bm-info{padding:7px 8px;}
.bm-title{font-size:.7rem;font-weight:600;color:#fff;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.bm-stk{font-size:.62rem;color:var(--muted);}
/* Laporan mini */
.pop-row{display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--border);}
.pop-row:last-child{border-bottom:none;}
.pop-cover{width:34px;height:44px;border-radius:6px;overflow:hidden;flex-shrink:0;background:linear-gradient(135deg,#1a1f2d,#2d3a4a);display:flex;align-items:center;justify-content:center;}
.pop-cover img.cover-img{width:100%;height:100%;object-fit:cover;}
.pop-cover img.logo-ph{width:20px;opacity:.6;}
.bar-fill{height:4px;border-radius:2px;background:linear-gradient(90deg,var(--accent),#d4a54e);margin-top:3px;}
@media(max-width:1100px){.stat-grid{grid-template-columns:repeat(3,1fr);}.bw{grid-template-columns:1fr;}.bw3{grid-template-columns:1fr;}}
@media(max-width:768px){.main-content{margin-left:0;}.stat-grid{grid-template-columns:repeat(2,1fr);}}
</style>
</head>
<body>
<div class="main-content">
<div class="topbar">
    <div>
        <h1>Dashboard Kepala Perpustakaan</h1>
        <div class="sub">Selamat datang, <strong style="color:var(--accent)"><?= htmlspecialchars($namaUser) ?></strong> &mdash; <?= date('l, d F Y') ?></div>
    </div>
    <a href="laporan.php" class="btn-app" style="padding:10px 18px;font-size:.85rem;background:linear-gradient(135deg,var(--accent),#d4a54e);border:none;border-radius:9px;font-weight:600;color:#0c1520;text-decoration:none;display:flex;align-items:center;gap:6px;"><i class="bi bi-bar-chart-line"></i>Buka Laporan Lengkap</a>
</div>

<?php if($msg): ?><div class="alert-ok"><i class="bi bi-check-circle-fill me-2"></i><?= $msg ?></div><?php endif; ?>

<!-- Stats -->
<div class="stat-grid">
    <div class="sc y"><div class="ico"><i class="bi bi-book-half"></i></div><div class="num"><?= $s_buku ?></div><div class="lbl">Judul Buku</div></div>
    <div class="sc b"><div class="ico"><i class="bi bi-layers"></i></div><div class="num"><?= $s_stok ?></div><div class="lbl">Total Stok</div></div>
    <div class="sc g"><div class="ico"><i class="bi bi-people-fill"></i></div><div class="num"><?= $s_user ?></div><div class="lbl">Anggota</div></div>
    <div class="sc b"><div class="ico"><i class="bi bi-arrow-bar-right"></i></div><div class="num"><?= $s_aktif ?></div><div class="lbl">Dipinjam</div></div>
    <div class="sc r"><div class="ico"><i class="bi bi-exclamation-triangle"></i></div><div class="num"><?= $s_terlambat ?></div><div class="lbl">Terlambat</div></div>
    <div class="sc p"><div class="ico"><i class="bi bi-lightbulb"></i></div><div class="num"><?= $s_saran ?></div><div class="lbl">Saran Baru</div></div>
    <div class="sc y"><div class="ico"><i class="bi bi-cash-coin"></i></div><div class="num" style="font-size:1rem;"><?= rupiah($s_denda) ?></div><div class="lbl">Total Denda</div></div>
</div>

<!-- Row 1: Saran + Laporan mini -->
<div class="bw3" id="saran">
    <!-- Saran Buku — kepala yang approve -->
    <div class="panel">
        <div class="ph">
            <h3><i class="bi bi-lightbulb me-2" style="color:var(--accent)"></i>Saran Buku dari Anggota
                <?php if($s_saran>0): ?><span style="background:rgba(232,93,93,.2);color:#ff9090;border-radius:10px;font-size:.65rem;padding:1px 8px;font-weight:700;margin-left:6px;"><?= $s_saran ?> baru</span><?php endif; ?>
            </h3>
            <a href="saran-buku.php" style="font-size:.77rem;color:var(--muted);text-decoration:none;">Semua →</a>
        </div>
        <div class="pb" style="padding:0 18px;max-height:420px;overflow-y:auto;">
        <?php if($saranList->num_rows>0): while($r=$saranList->fetch_assoc()):
            $bs=['baru'=>'badge-baru','ditinjau'=>'badge-ditinjau','disetujui'=>'badge-ok','ditolak'=>'badge-no'];
            $bl=['baru'=>'Baru','ditinjau'=>'Ditinjau','disetujui'=>'Disetujui','ditolak'=>'Ditolak'];
            $st=$r['status'];
        ?>
        <div class="saran-row">
            <div class="s-avatar"><?= strtoupper(substr($r['nama_user'],0,1)) ?></div>
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:3px;">
                    <span style="font-size:.85rem;font-weight:600;color:#fff;"><?= htmlspecialchars($r['judul_saran']) ?></span>
                    <span class="<?= $bs[$st]??'badge-baru' ?>"><?= $bl[$st]??$st ?></span>
                </div>
                <?php if($r['penulis_saran']): ?><div style="font-size:.71rem;color:var(--muted);"><?= htmlspecialchars($r['penulis_saran']) ?><?= $r['penerbit_saran']?' · '.htmlspecialchars($r['penerbit_saran']):'' ?></div><?php endif; ?>
                <?php if($r['alasan']): ?><div style="font-size:.72rem;color:var(--muted);font-style:italic;margin-top:2px;">"<?= mb_substr(htmlspecialchars($r['alasan']),0,80) ?>..."</div><?php endif; ?>
                <div style="font-size:.68rem;color:var(--muted);margin-top:3px;"><?= htmlspecialchars($r['nama_user']) ?> · <?= date('d M Y',strtotime($r['tanggal_saran'])) ?></div>
            </div>
            <div class="saran-actions">
                <?php if($st!=='ditinjau'&&$st!=='disetujui'&&$st!=='ditolak'): ?>
                <a href="?saran_id=<?= $r['id_saran'] ?>&saran_status=ditinjau" class="ab ab-tinjau">Tinjau</a>
                <?php endif; ?>
                <?php if($st!=='disetujui'): ?>
                <a href="?saran_id=<?= $r['id_saran'] ?>&saran_status=disetujui" class="ab ab-ok" onclick="return confirm('Setujui saran ini?')">✓</a>
                <?php endif; ?>
                <?php if($st!=='ditolak'): ?>
                <a href="?saran_id=<?= $r['id_saran'] ?>&saran_status=ditolak" class="ab ab-no" onclick="return confirm('Tolak saran ini?')">✕</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; else: ?>
        <div style="text-align:center;padding:28px;color:var(--muted);font-size:.84rem;"><i class="bi bi-lightbulb" style="font-size:1.5rem;display:block;margin-bottom:6px;opacity:.3;"></i>Belum ada saran buku.</div>
        <?php endif; ?>
        </div>
    </div>
    <!-- Laporan ringkas -->
    <div style="display:flex;flex-direction:column;gap:14px;">
        <div class="panel">
            <div class="ph"><h3><i class="bi bi-trophy me-2" style="color:var(--accent)"></i>Buku Terpopuler</h3></div>
            <div class="pb" style="padding:0 18px;">
            <?php $mx=1; $pr=[]; while($r=$top_buku->fetch_assoc()){ $pr[]=$r; if($r['t']>$mx) $mx=$r['t']; }
            foreach($pr as $r): $pct=round($r['t']/$mx*100); ?>
            <div class="pop-row">
                <div class="pop-cover" data-book-title="<?= htmlspecialchars($r['nama_buku']) ?>">
                    <?php if(!empty($r['gambar'])&&file_exists('../img/buku/'.$r['gambar'])): ?>
                        <img src="../img/buku/<?= htmlspecialchars($r['gambar']) ?>" class="cover-img">
                    <?php else: ?>
                        <img src="../img/logo-wandek.png" class="logo-ph">
                    <?php endif; ?>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:.78rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($r['nama_buku']) ?></div>
                    <div class="bar-fill" style="width:<?= $pct ?>%"></div>
                </div>
                <div style="font-size:.82rem;font-weight:700;color:var(--accent);flex-shrink:0;"><?= $r['t'] ?>x</div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
        <div class="panel">
            <div class="ph"><h3><i class="bi bi-bar-chart-line me-2" style="color:var(--accent)"></i>Tren Peminjaman</h3></div>
            <div style="padding:16px;"><canvas id="trendChart" height="140"></canvas></div>
        </div>
    </div>
</div>

<!-- Row 2: Koleksi Buku (search + browse) -->
<div class="panel">
    <div class="ph"><h3><i class="bi bi-collection me-2" style="color:var(--accent)"></i>Koleksi Buku <span style="font-size:.75rem;font-weight:400;color:var(--muted);">(hanya lihat)</span></h3></div>
    <div class="pb">
        <form method="GET" class="srch-bar">
            <input type="hidden" name="saran_dummy" value="0">
            <input type="text" name="q" placeholder="Cari judul buku..." value="<?= $q ?>">
            <select name="kat">
                <option value="">Semua Kategori</option>
                <?php $katList->data_seek(0); while($r=$katList->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($r['kategori']) ?>" <?= $kat===$r['kategori']?'selected':'' ?>><?= htmlspecialchars($r['kategori']) ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit"><i class="bi bi-search"></i></button>
            <?php if($q||$kat): ?><a href="dashboard-kepala.php" style="padding:8px 14px;border-radius:8px;border:1px solid var(--border);color:var(--muted);text-decoration:none;font-size:.82rem;">Reset</a><?php endif; ?>
        </form>
        <div class="buku-mini-grid">
        <?php if($bukuList && $bukuList->num_rows>0): while($b=$bukuList->fetch_assoc()):
            $kk=strtolower($b['kategori']);
            $bgmap=['umum'=>'linear-gradient(135deg,#0d2540,#1a3d60)','motivasi'=>'linear-gradient(135deg,#2d1f00,#5a3c00)','sastra'=>'linear-gradient(135deg,#1f0d2d,#3d1a5a)','religi'=>'linear-gradient(135deg,#0d2d1a,#1a5a3c)'];
            $bg=$bgmap[$kk]??'linear-gradient(135deg,#1a1f2d,#2d3a4a)';
        ?>
        <div class="bm-card">
            <div class="bm-cover" style="background:<?= $bg ?>;" data-book-title="<?= htmlspecialchars($b['nama_buku']) ?>">
                <?php if(!empty($b['gambar'])&&file_exists('../img/buku/'.$b['gambar'])): ?>
                    <img src="../img/buku/<?= htmlspecialchars($b['gambar']) ?>" class="cover-img">
                <?php else: ?>
                    <img src="../img/logo-wandek.png" class="logo-ph">
                <?php endif; ?>
                <span class="stk-dot" style="background:<?= $b['stock']>0?'var(--green)':'var(--red)' ?>;"></span>
            </div>
            <div class="bm-info">
                <div class="bm-title" title="<?= htmlspecialchars($b['nama_buku']) ?>"><?= htmlspecialchars($b['nama_buku']) ?></div>
                <div class="bm-stk"><?= $b['stock'] ?> eks · <?= htmlspecialchars($b['kategori']) ?></div>
            </div>
        </div>
        <?php endwhile; else: ?>
        <div style="grid-column:1/-1;text-align:center;padding:24px;color:var(--muted);font-size:.84rem;">Tidak ada buku ditemukan.</div>
        <?php endif; ?>
        </div>
    </div>
</div>

</div><!-- /main-content -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Chart tren
new Chart(document.getElementById('trendChart'),{
    type:'line',
    data:{labels:<?= json_encode($chart_l) ?>,datasets:[{label:'Peminjaman',data:<?= json_encode($chart_v) ?>,borderColor:'#e8b86d',backgroundColor:'rgba(232,184,109,.12)',borderWidth:2,pointBackgroundColor:'#e8b86d',tension:.4,fill:true}]},
    options:{responsive:true,plugins:{legend:{display:false}},scales:{x:{ticks:{color:'#637a96',font:{size:10}},grid:{display:false}},y:{ticks:{color:'#637a96',font:{size:10}},grid:{color:'rgba(255,255,255,.04)'}}}}
});
// Auto-fetch book covers via Google Books API
function fetchCover(div){
    const title=div.dataset.bookTitle;
    if(!title) return;
    const ph=div.querySelector('.logo-ph');
    if(!ph) return;
    fetch(`https://www.googleapis.com/books/v1/volumes?q=${encodeURIComponent(title)}&maxResults=1`)
    .then(r=>r.json()).then(d=>{
        const thumb=d.items?.[0]?.volumeInfo?.imageLinks?.thumbnail;
        if(thumb){
            const img=document.createElement('img');
            img.src=thumb.replace('http:','https:');
            img.className='cover-img';
            img.onerror=()=>img.remove();
            ph.replaceWith(img);
        }
    }).catch(()=>{});
}
// Stagger requests to avoid rate limiting
document.querySelectorAll('[data-book-title]').forEach((div,i)=>{
    setTimeout(()=>fetchCover(div), i*120);
});
</script>
</body></html>
