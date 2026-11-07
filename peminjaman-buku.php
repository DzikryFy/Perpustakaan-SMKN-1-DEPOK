<?php
session_start();
require_once 'config/database.php';
requireLogin('login.php?redirect=peminjaman-buku.php');

if (!canBorrow()) redirect('index.php');

$loggedIn = true;
$namaUser = $_SESSION['nama'] ?? '';
$roleUser = $_SESSION['role'] ?? '';
$id_user  = (int)$_SESSION['id_user'];
$base     = '';
$msg = $error = '';

// ── PROSES REQUEST PINJAM ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi']??'') === 'request') {
    $id_buku = (int)($_POST['id_buku'] ?? 0);
    $catatan = sanitize($_POST['catatan'] ?? '');
    $bk = $conn->query("SELECT * FROM buku WHERE id_buku=$id_buku AND stock>0")->fetch_assoc();
    if (!$bk) {
        $error = 'Buku tidak ditemukan atau stok habis.';
    } else {
        $ada = $conn->query("SELECT id_request FROM request_peminjaman WHERE id_user=$id_user AND id_buku=$id_buku AND status='menunggu'")->num_rows;
        $pinjam = $conn->query("SELECT id_pinjam FROM peminjaman WHERE id_user=$id_user AND id_buku=$id_buku AND status='Tidak Ada'")->num_rows;
        if ($ada || $pinjam) {
            $error = 'Kamu sudah memiliki permintaan atau pinjaman aktif untuk buku ini.';
        } else {
            $tgl = date('Y-m-d');
            $nb  = sanitize($bk['nama_buku']);
            $s = $conn->prepare("INSERT INTO request_peminjaman (id_buku,id_user,tanggal_request,catatan,status) VALUES (?,?,?,?,'menunggu')");
            $s->bind_param("iiss", $id_buku, $id_user, $tgl, $catatan);
            $s->execute() ? $msg = '✅ Permintaan peminjaman <strong>'.htmlspecialchars($bk['nama_buku']).'</strong> terkirim. Tunggu persetujuan pustakawan.' : $error='Gagal mengirim permintaan.';
            $s->close();
        }
    }
}

// ── BATAL REQUEST ─────────────────────────────────────────────
if (isset($_GET['batal'])) {
    $rid = (int)$_GET['batal'];
    $conn->query("DELETE FROM request_peminjaman WHERE id_request=$rid AND id_user=$id_user AND status='menunggu'");
    redirect('peminjaman-buku.php?msg=batal');
}
if (isset($_GET['msg']) && $_GET['msg']==='batal') $msg = 'Permintaan berhasil dibatalkan.';

// ── DATA ──────────────────────────────────────────────────────
$stat_menunggu = $conn->query("SELECT COUNT(*) c FROM request_peminjaman WHERE id_user=$id_user AND status='menunggu'")->fetch_assoc()['c'];
$stat_aktif    = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE id_user=$id_user AND status='Tidak Ada'")->fetch_assoc()['c'];
$stat_kembali  = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE id_user=$id_user AND status='Ada'")->fetch_assoc()['c'];

// Request menunggu
$requests = $conn->query("SELECT r.*,b.nama_buku,b.gambar,b.kategori FROM request_peminjaman r JOIN buku b ON r.id_buku=b.id_buku WHERE r.id_user=$id_user ORDER BY r.id_request DESC LIMIT 10");

// Pinjaman aktif
$aktif = $conn->query("SELECT p.*,b.kategori,b.penerbit,b.gambar FROM peminjaman p JOIN buku b ON p.id_buku=b.id_buku WHERE p.id_user=$id_user AND p.status='Tidak Ada' ORDER BY p.id_pinjam DESC");

// Browse buku
$q   = sanitize($_GET['q']   ?? '');
$kat = sanitize($_GET['kat'] ?? '');
$where = "WHERE stock>0";
if ($q)   $where .= " AND nama_buku LIKE '%$q%'";
if ($kat) $where .= " AND kategori='$kat'";
$buku_list = $conn->query("SELECT * FROM buku $where ORDER BY nama_buku ASC");

$kat_counts = [];
$kc = $conn->query("SELECT kategori,COUNT(*) t FROM buku WHERE stock>0 GROUP BY kategori");
while ($r=$kc->fetch_assoc()) $kat_counts[$r['kategori']] = $r['t'];

// id buku yang sudah di-request atau dipinjam
$req_ids  = [];
$pj_ids   = [];
$rq = $conn->query("SELECT id_buku FROM request_peminjaman WHERE id_user=$id_user AND status='menunggu'");
while ($r=$rq->fetch_assoc()) $req_ids[] = $r['id_buku'];
$pj = $conn->query("SELECT id_buku FROM peminjaman WHERE id_user=$id_user AND status='Tidak Ada'");
while ($r=$pj->fetch_assoc()) $pj_ids[] = $r['id_buku'];

$cm_map = ['umum'=>['emoji'=>'📖','class'=>'c-umum'],'motivasi'=>['emoji'=>'🚀','class'=>'c-motivasi'],'sastra'=>['emoji'=>'✍️','class'=>'c-sastra'],'religi'=>['emoji'=>'🕌','class'=>'c-religi']];
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Peminjaman Buku | E-Library SMKN 1 Depok</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="style.css">
<style>
.page-header{padding:40px 0 28px;border-bottom:1px solid var(--border);}
.page-header h1{font-family:'Playfair Display',serif;font-size:2rem;color:#fff;margin-bottom:4px;}
.stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:32px;}
.stat-box{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px;display:flex;align-items:center;gap:12px;}
.stat-ico{width:44px;height:44px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.ico-gold{background:rgba(232,184,109,.12);color:var(--accent);}
.ico-blue{background:rgba(74,158,255,.12);color:var(--blue);}
.ico-green{background:rgba(58,171,110,.12);color:var(--green);}
.stat-num{font-size:1.8rem;font-weight:700;color:#fff;line-height:1;}
.stat-lbl{font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-top:2px;}
.sec-hdr{display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;}
.sec-label-sm{font-size:.68rem;text-transform:uppercase;letter-spacing:.12em;color:var(--accent);font-weight:600;}
.sec-title-sm{font-family:'Playfair Display',serif;font-size:1.35rem;color:#fff;margin:0;}
.alert-ok{background:rgba(58,171,110,.1);border:1px solid rgba(58,171,110,.3);color:#5dd896;border-radius:11px;padding:13px 16px;margin-bottom:20px;font-size:.87rem;display:flex;gap:9px;}
.alert-err{background:rgba(232,93,93,.1);border:1px solid rgba(232,93,93,.3);color:#ff9090;border-radius:11px;padding:13px 16px;margin-bottom:20px;font-size:.87rem;display:flex;gap:9px;}
.req-card{background:var(--card);border:1px solid var(--border);border-radius:13px;display:flex;align-items:center;gap:14px;padding:14px 16px;margin-bottom:10px;}
.req-cover{width:46px;height:58px;border-radius:8px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:22px;}
.req-cover img{width:100%;height:100%;object-fit:cover;}
.req-cover.c-umum{background:linear-gradient(135deg,#0d2540,#1a3d60);}
.req-cover.c-motivasi{background:linear-gradient(135deg,#2d1f00,#5a3c00);}
.req-cover.c-sastra{background:linear-gradient(135deg,#1f0d2d,#3d1a5a);}
.req-cover.c-religi{background:linear-gradient(135deg,#0d2d1a,#1a5a3c);}
.req-cover.c-default{background:linear-gradient(135deg,#1a1f2d,#2d3a4a);}
.badge-menunggu{background:rgba(240,192,112,.12);color:#f0c070;border:1px solid rgba(240,192,112,.25);border-radius:20px;font-size:.68rem;font-weight:600;padding:3px 10px;}
.badge-disetujui{background:rgba(58,171,110,.12);color:#5dd896;border:1px solid rgba(58,171,110,.25);border-radius:20px;font-size:.68rem;font-weight:600;padding:3px 10px;}
.badge-ditolak{background:rgba(232,93,93,.1);color:#ff9090;border:1px solid rgba(232,93,93,.25);border-radius:20px;font-size:.68rem;font-weight:600;padding:3px 10px;}
.btn-batal-req{font-size:.75rem;color:var(--muted);background:none;border:1px solid var(--border);border-radius:8px;padding:5px 12px;cursor:pointer;transition:all .2s;}
.btn-batal-req:hover{border-color:rgba(232,93,93,.4);color:#ff9090;}
.filter-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;}
.filter-tab{padding:6px 15px;border-radius:20px;font-size:.8rem;font-weight:500;border:1px solid var(--border);color:var(--muted);text-decoration:none;transition:all .2s;display:flex;align-items:center;gap:5px;}
.filter-tab:hover{border-color:var(--accent);color:var(--accent);}
.filter-tab.active{background:var(--accent);border-color:var(--accent);color:#0c1520;font-weight:600;}
.filter-tab .cnt{font-size:.65rem;background:rgba(0,0,0,.15);border-radius:10px;padding:1px 6px;}
.search-inline{display:flex;gap:8px;max-width:360px;}
.search-inline input{flex:1;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:var(--text);border-radius:9px;padding:8px 13px;font-size:.85rem;font-family:'DM Sans',sans-serif;}
.search-inline input:focus{outline:none;border-color:var(--accent);}
.search-inline button{background:var(--accent);border:none;border-radius:9px;padding:8px 14px;color:#0c1520;font-weight:600;cursor:pointer;}
.buku-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:14px;margin-top:4px;}
.buku-pm{background:var(--card);border:1px solid var(--border);border-radius:13px;overflow:hidden;display:flex;flex-direction:column;transition:transform .22s,border-color .22s;}
.buku-pm:hover{transform:translateY(-4px);border-color:rgba(232,184,109,.2);}
.pm-cover{height:130px;display:flex;align-items:center;justify-content:center;font-size:44px;position:relative;overflow:hidden;}
.pm-cover img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0;}
.pm-cover.c-umum{background:linear-gradient(135deg,#0d2540,#1a3d60);}
.pm-cover.c-motivasi{background:linear-gradient(135deg,#2d1f00,#5a3c00);}
.pm-cover.c-sastra{background:linear-gradient(135deg,#1f0d2d,#3d1a5a);}
.pm-cover.c-religi{background:linear-gradient(135deg,#0d2d1a,#1a5a3c);}
.pm-cover.c-default{background:linear-gradient(135deg,#1a1f2d,#2d3a4a);}
.stk-ada{position:absolute;top:7px;right:7px;background:rgba(58,171,110,.2);color:#5dd896;border:1px solid rgba(58,171,110,.3);border-radius:10px;font-size:.62rem;font-weight:600;padding:2px 7px;}
.stk-habis{position:absolute;top:7px;right:7px;background:rgba(232,93,93,.2);color:#ff9090;border:1px solid rgba(232,93,93,.3);border-radius:10px;font-size:.62rem;font-weight:600;padding:2px 7px;}
.pm-body{padding:12px;flex:1;display:flex;flex-direction:column;gap:3px;}
.pm-kat{font-size:.63rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;}
.pm-kat.ck-umum{color:var(--blue);}.pm-kat.ck-motivasi{color:var(--accent);}.pm-kat.ck-sastra{color:var(--purple);}.pm-kat.ck-religi{color:var(--green);}.pm-kat.ck-default{color:var(--muted);}
.pm-judul{font-size:.85rem;font-weight:600;color:#fff;line-height:1.3;flex:1;}
.pm-pub{font-size:.7rem;color:var(--muted);margin-bottom:7px;}
.btn-req-pm{display:block;text-align:center;padding:9px;border-radius:8px;font-size:.78rem;font-weight:600;cursor:pointer;border:none;width:100%;background:linear-gradient(135deg,var(--accent),#d4a54e);color:#0c1520;transition:opacity .2s;margin-top:auto;}
.btn-req-pm:hover{opacity:.88;}
.btn-req-pm:disabled{background:rgba(255,255,255,.06);color:var(--muted);border:1px solid var(--border);cursor:not-allowed;}
.btn-det-pm{display:block;text-align:center;padding:6px;border-radius:8px;font-size:.73rem;border:1px solid var(--border);color:var(--muted);text-decoration:none;transition:all .2s;margin-top:5px;}
.btn-det-pm:hover{border-color:var(--accent);color:var(--accent);}
.modal-catatan{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9999;align-items:center;justify-content:center;}
.modal-catatan.show{display:flex;}
.modal-box{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:28px;width:420px;max-width:94vw;}
.modal-box h4{font-family:'Playfair Display',serif;color:#fff;margin-bottom:16px;}
.modal-box textarea{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:var(--text);border-radius:9px;padding:10px 13px;font-size:.85rem;font-family:'DM Sans',sans-serif;resize:vertical;min-height:80px;}
.modal-box textarea:focus{outline:none;border-color:var(--accent);}
.empty-box{text-align:center;padding:30px;background:var(--card);border:1px dashed var(--border);border-radius:13px;color:var(--muted);font-size:.85rem;}
.empty-box i{font-size:1.8rem;display:block;margin-bottom:8px;opacity:.3;}
.divider{border:none;border-top:1px solid var(--border);margin:32px 0;}
.aktif-card{background:var(--card);border:1px solid var(--border);border-radius:13px;display:flex;align-items:center;gap:14px;padding:14px 16px;margin-bottom:10px;}
.aktif-cover{width:46px;height:58px;border-radius:8px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:22px;}
.aktif-cover img{width:100%;height:100%;object-fit:cover;}
.terlambat{border-color:rgba(232,93,93,.3)!important;background:rgba(232,93,93,.04)!important;}
.badge-terlambat{background:rgba(232,93,93,.15);color:#ff9090;border:1px solid rgba(232,93,93,.3);border-radius:20px;font-size:.65rem;font-weight:600;padding:3px 9px;}
@media(max-width:600px){.stat-row{grid-template-columns:1fr 1fr;}.search-inline{max-width:100%;}}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="page-header">
    <div class="container">
        <div class="sec-label-sm mb-1">Sistem Peminjaman</div>
        <h1>Peminjaman Buku</h1>
        <p style="color:var(--muted);font-size:.87rem;margin:0">Halo, <strong style="color:var(--accent)"><?= htmlspecialchars($namaUser) ?></strong> — ajukan permintaan pinjam dan pantau status di sini.</p>
    </div>
</div>
<div class="container" style="padding-top:32px;padding-bottom:80px;">

<?php if($msg): ?><div class="alert-ok"><i class="bi bi-check-circle-fill"></i><div><?= $msg ?></div></div><?php endif; ?>
<?php if($error): ?><div class="alert-err"><i class="bi bi-exclamation-triangle-fill"></i><div><?= $error ?></div></div><?php endif; ?>

<!-- Statistik -->
<div class="stat-row">
    <div class="stat-box"><div class="stat-ico ico-gold"><i class="bi bi-hourglass-split"></i></div><div><div class="stat-num"><?= $stat_menunggu ?></div><div class="stat-lbl">Menunggu Persetujuan</div></div></div>
    <div class="stat-box"><div class="stat-ico ico-blue"><i class="bi bi-book-half"></i></div><div><div class="stat-num"><?= $stat_aktif ?></div><div class="stat-lbl">Sedang Dipinjam</div></div></div>
    <div class="stat-box"><div class="stat-ico ico-green"><i class="bi bi-check-circle"></i></div><div><div class="stat-num"><?= $stat_kembali ?></div><div class="stat-lbl">Sudah Dikembalikan</div></div></div>
</div>

<!-- Info alur -->
<div style="background:rgba(232,184,109,.06);border:1px solid rgba(232,184,109,.15);border-radius:12px;padding:14px 18px;margin-bottom:28px;font-size:.82rem;color:var(--muted);display:flex;align-items:flex-start;gap:10px;">
    <i class="bi bi-info-circle-fill" style="color:var(--accent);font-size:1rem;flex-shrink:0;margin-top:1px"></i>
    <div><strong style="color:var(--accent)">Cara Meminjam:</strong> Pilih buku → klik <em>Ajukan Pinjaman</em> → tunggu persetujuan pustakawan → ambil buku di perpustakaan. Batas pinjam <strong style="color:#fff">7 hari</strong>. Terlambat dikenakan denda <strong style="color:#fff">Rp 1.000/hari</strong>.</div>
</div>

<!-- Permintaan saya -->
<div class="sec-hdr">
    <div><div class="sec-label-sm">Status Terkini</div><h2 class="sec-title-sm">Permintaan Saya</h2></div>
    <a href="riwayat-peminjaman.php" style="font-size:.8rem;color:var(--muted);text-decoration:none;border:1px solid var(--border);border-radius:8px;padding:6px 13px;"><i class="bi bi-list-check me-1"></i>Riwayat Lengkap</a>
</div>
<?php if($requests && $requests->num_rows > 0): ?>
<?php while($r=$requests->fetch_assoc()):
    $kk = strtolower($r['kategori']);
    $cm = $cm_map[$kk] ?? ['emoji'=>'📚','class'=>'c-default'];
    $isAktif = ($r['status']==='disetujui') && $conn->query("SELECT id_pinjam FROM peminjaman WHERE id_user=$id_user AND id_buku={$r['id_buku']} AND status='Tidak Ada'")->num_rows > 0;
?>
<div class="req-card">
    <div class="req-cover <?= $cm['class'] ?>">
        <?php if(!empty($r['gambar'])&&file_exists('img/buku/'.$r['gambar'])): ?><img src="img/buku/<?= htmlspecialchars($r['gambar']) ?>" alt=""><?php else: ?><?= $cm['emoji'] ?><?php endif; ?>
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-weight:600;color:#fff;font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($r['nama_buku']) ?></div>
        <div style="font-size:.73rem;color:var(--muted);margin-top:3px;"><i class="bi bi-calendar3 me-1"></i><?= date('d M Y',strtotime($r['tanggal_request'])) ?></div>
        <?php if($r['alasan_tolak']): ?><div style="font-size:.73rem;color:#ff9090;margin-top:3px;"><i class="bi bi-x-circle me-1"></i><?= htmlspecialchars($r['alasan_tolak']) ?></div><?php endif; ?>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
        <?php if($r['status']==='menunggu'): ?>
            <span class="badge-menunggu"><i class="bi bi-hourglass-split me-1"></i>Menunggu</span>
            <a href="peminjaman-buku.php?batal=<?= $r['id_request'] ?>" class="btn-batal-req" onclick="return confirm('Batalkan permintaan ini?')">Batal</a>
        <?php elseif($r['status']==='disetujui'): ?>
            <span class="badge-disetujui"><i class="bi bi-check-circle me-1"></i>Disetujui</span>
        <?php else: ?>
            <span class="badge-ditolak"><i class="bi bi-x-circle me-1"></i>Ditolak</span>
        <?php endif; ?>
    </div>
</div>
<?php endwhile; ?>
<?php else: ?>
<div class="empty-box"><i class="bi bi-inbox"></i>Belum ada permintaan peminjaman.</div>
<?php endif; ?>

<!-- Pinjaman aktif -->
<?php if($aktif && $aktif->num_rows > 0): ?>
<hr class="divider">
<div class="sec-hdr"><div><div class="sec-label-sm">Sedang Dipinjam</div><h2 class="sec-title-sm">Buku Aktif</h2></div></div>
<?php while($r=$aktif->fetch_assoc()):
    $kk = strtolower($r['kategori']);
    $cm = $cm_map[$kk] ?? ['emoji'=>'📚','class'=>'c-default'];
    $terlambat = strtotime(date('Y-m-d')) > strtotime($r['batas_kembali']);
    $sisa = ceil((strtotime($r['batas_kembali']) - strtotime(date('Y-m-d'))) / 86400);
?>
<div class="aktif-card <?= $terlambat?'terlambat':'' ?>">
    <div class="aktif-cover <?= $cm['class'] ?>">
        <?php if(!empty($r['gambar'])&&file_exists('img/buku/'.$r['gambar'])): ?><img src="img/buku/<?= htmlspecialchars($r['gambar']) ?>" alt=""><?php else: ?><?= $cm['emoji'] ?><?php endif; ?>
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-weight:600;color:#fff;font-size:.9rem;"><?= htmlspecialchars($r['nama_buku']) ?></div>
        <div style="font-size:.73rem;color:var(--muted);margin-top:3px;display:flex;gap:12px;flex-wrap:wrap;">
            <span><i class="bi bi-calendar3 me-1"></i>Dipinjam: <?= date('d M Y',strtotime($r['tanggal_peminjaman'])) ?></span>
            <span><i class="bi bi-calendar-x me-1"></i>Batas: <?= date('d M Y',strtotime($r['batas_kembali'])) ?></span>
        </div>
    </div>
    <div style="flex-shrink:0;">
        <?php if($terlambat): ?>
            <span class="badge-terlambat"><i class="bi bi-exclamation-triangle me-1"></i>Terlambat <?= abs($sisa) ?> hari</span>
        <?php else: ?>
            <span class="badge-disetujui">Sisa <?= $sisa ?> hari</span>
        <?php endif; ?>
    </div>
</div>
<?php endwhile; ?>
<?php endif; ?>

<hr class="divider">

<!-- Browse & Request -->
<div class="sec-hdr">
    <div><div class="sec-label-sm">Koleksi Tersedia</div><h2 class="sec-title-sm">Ajukan Pinjaman Baru</h2></div>
    <form method="GET" class="search-inline">
        <?php if($kat): ?><input type="hidden" name="kat" value="<?= htmlspecialchars($kat) ?>"><?php endif; ?>
        <input type="text" name="q" placeholder="Cari judul..." value="<?= htmlspecialchars($q) ?>">
        <button><i class="bi bi-search"></i></button>
    </form>
</div>
<div class="filter-tabs">
    <?php foreach([''=> 'Semua','Umum'=>'Umum','Motivasi'=>'Motivasi','Sastra'=>'Sastra','Religi'=>'Religi'] as $v=>$lb):
        $cnt = $v===''? array_sum($kat_counts) : ($kat_counts[$v]??0);
    ?>
    <a href="?kat=<?= urlencode($v) ?><?= $q?'&q='.urlencode($q):'' ?>" class="filter-tab <?= $kat===$v?'active':'' ?>"><?= $lb ?><span class="cnt"><?= $cnt ?></span></a>
    <?php endforeach; ?>
</div>

<?php if($buku_list && $buku_list->num_rows > 0): ?>
<div class="buku-grid">
<?php while($b=$buku_list->fetch_assoc()):
    $kk = strtolower($b['kategori']);
    $cm = $cm_map[$kk] ?? ['emoji'=>'📚','class'=>'c-default'];
    $isReq   = in_array($b['id_buku'],$req_ids);
    $isPinjam = in_array($b['id_buku'],$pj_ids);
?>
<div class="buku-pm">
    <div class="pm-cover <?= $cm['class'] ?>">
        <?php if(!empty($b['gambar'])&&file_exists('img/buku/'.$b['gambar'])): ?><img src="img/buku/<?= htmlspecialchars($b['gambar']) ?>" alt=""><?php else: ?><?= $cm['emoji'] ?><?php endif; ?>
        <span class="<?= $b['stock']>0?'stk-ada':'stk-habis' ?>"><?= $b['stock']>0?'Stok:'.$b['stock']:'Habis' ?></span>
    </div>
    <div class="pm-body">
        <span class="pm-kat ck-<?= $kk ?>"><?= htmlspecialchars($b['kategori']) ?></span>
        <div class="pm-judul"><?= htmlspecialchars($b['nama_buku']) ?></div>
        <?php if(!empty($b['penerbit'])): ?><div class="pm-pub"><i class="bi bi-building me-1"></i><?= htmlspecialchars($b['penerbit']) ?></div><?php endif; ?>
        <?php if($isPinjam): ?>
            <button class="btn-req-pm" disabled><i class="bi bi-book-half me-1"></i>Sedang Dipinjam</button>
        <?php elseif($isReq): ?>
            <button class="btn-req-pm" disabled><i class="bi bi-hourglass-split me-1"></i>Sudah Diajukan</button>
        <?php elseif($b['stock']<1): ?>
            <button class="btn-req-pm" disabled><i class="bi bi-x-circle me-1"></i>Stok Habis</button>
        <?php else: ?>
            <button class="btn-req-pm" onclick="bukaModal(<?= $b['id_buku'] ?>,<?= htmlspecialchars(json_encode($b['nama_buku'])) ?>)"><i class="bi bi-bookmark-plus me-1"></i>Ajukan Pinjaman</button>
        <?php endif; ?>
        <a href="detail_buku.php?id=<?= $b['id_buku'] ?>" class="btn-det-pm"><i class="bi bi-eye me-1"></i>Detail</a>
    </div>
</div>
<?php endwhile; ?>
</div>
<?php else: ?>
<div class="empty-box"><i class="bi bi-search"></i><div>Tidak ada buku yang ditemukan.</div></div>
<?php endif; ?>
</div>

<!-- Modal Catatan -->
<div class="modal-catatan" id="modalCatatan">
    <div class="modal-box">
        <h4><i class="bi bi-bookmark-plus me-2"></i>Ajukan Pinjaman</h4>
        <p id="modalNama" style="color:var(--accent);font-size:.9rem;margin-bottom:14px;font-weight:600;"></p>
        <form method="POST" id="formRequest">
            <input type="hidden" name="aksi" value="request">
            <input type="hidden" name="id_buku" id="inputBuku">
            <?php if($kat): ?><input type="hidden" name="kat" value="<?= htmlspecialchars($kat) ?>"><?php endif; ?>
            <label style="font-size:.8rem;color:var(--muted);margin-bottom:6px;display:block;">Catatan untuk pustakawan (opsional)</label>
            <textarea name="catatan" placeholder="misal: untuk tugas kelas 11..."></textarea>
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button type="button" onclick="tutupModal()" style="flex:1;padding:10px;border-radius:9px;background:none;border:1px solid var(--border);color:var(--muted);cursor:pointer;">Batal</button>
                <button type="submit" style="flex:2;padding:10px;border-radius:9px;background:linear-gradient(135deg,var(--accent),#d4a54e);border:none;font-weight:600;color:#0c1520;cursor:pointer;"><i class="bi bi-send me-1"></i>Kirim Permintaan</button>
            </div>
        </form>
    </div>
</div>

<footer class="site-footer"><div class="container"><div class="footer-inner">
    <div class="footer-brand">📚 E-Library SMKN 1 Depok</div>
    <div class="footer-copy">&copy; <?= date('Y') ?> E-Library. All Rights Reserved.</div>
    <div class="footer-links"><a href="riwayat-peminjaman.php">Riwayat</a><a href="saran-buku.php">Saran Buku</a></div>
</div></div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.addEventListener('scroll',()=>document.getElementById('mainNav').classList.toggle('scrolled',scrollY>30));
function bukaModal(id,nama){
    document.getElementById('inputBuku').value=id;
    document.getElementById('modalNama').textContent=nama;
    document.getElementById('modalCatatan').classList.add('show');
}
function tutupModal(){document.getElementById('modalCatatan').classList.remove('show');}
document.getElementById('modalCatatan').addEventListener('click',function(e){if(e.target===this)tutupModal();});
</script>
</body></html>
