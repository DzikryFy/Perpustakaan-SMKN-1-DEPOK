<?php
session_start();
require_once '../config/database.php';
requireStaff();
$base = '../';
$loggedIn=true; $namaUser=$_SESSION['nama']??''; $roleUser=$_SESSION['role']??'';
$msg=$error='';
$canEdit = isPustakawan();

// ── SETUJUI REQUEST ───────────────────────────────────────────
if($canEdit && isset($_GET['setujui'])){
    $rid = (int)$_GET['setujui'];
    $req = $conn->query("SELECT r.*,b.nama_buku,b.stock FROM request_peminjaman r JOIN buku b ON r.id_buku=b.id_buku WHERE r.id_request=$rid AND r.status='menunggu'")->fetch_assoc();
    if($req && $req['stock']>0){
        $tgl  = date('Y-m-d');
        $batas = date('Y-m-d',strtotime("+".BATAS_PINJAM_HARI." days"));
        $nb = $conn->real_escape_string($req['nama_buku']);
        $conn->query("INSERT INTO peminjaman (id_buku,id_user,tanggal_peminjaman,batas_kembali,nama_buku,status) VALUES ({$req['id_buku']},{$req['id_user']},'$tgl','$batas','$nb','Tidak Ada')");
        $pid = $conn->insert_id;
        $conn->query("UPDATE buku SET stock=stock-1 WHERE id_buku={$req['id_buku']}");
        $conn->query("UPDATE request_peminjaman SET status='disetujui',id_pinjam=$pid WHERE id_request=$rid");
        $msg = 'Permintaan disetujui. Peminjaman #'.$pid.' dibuat.';
    } else { $error='Stok habis atau permintaan tidak valid.'; }
}
// ── TOLAK REQUEST ─────────────────────────────────────────────
if($canEdit && $_SERVER['REQUEST_METHOD']==='POST' && ($_POST['aksi']??'')==='tolak'){
    $rid   = (int)$_POST['id_request'];
    $alasan= sanitize($_POST['alasan_tolak']??'');
    $conn->query("UPDATE request_peminjaman SET status='ditolak',alasan_tolak='$alasan' WHERE id_request=$rid AND status='menunggu'");
    $msg='Permintaan ditolak.';
}

$tab = $_GET['tab'] ?? 'request';

// Data request menunggu
$requests = $conn->query("SELECT r.*,b.nama_buku,b.gambar,b.kategori,b.stock,u.nama AS nama_user,u.role AS role_user FROM request_peminjaman r JOIN buku b ON r.id_buku=b.id_buku JOIN users u ON r.id_user=u.id_user WHERE r.status='menunggu' ORDER BY r.id_request ASC");
$cnt_req = $requests->num_rows;

// Data pinjaman aktif
$aktif = $conn->query("SELECT p.*,b.gambar,b.kategori,u.nama AS nama_user FROM peminjaman p JOIN buku b ON p.id_buku=b.id_buku JOIN users u ON p.id_user=u.id_user WHERE p.status='Tidak Ada' ORDER BY p.batas_kembali ASC");
$cnt_aktif = $aktif->num_rows;

// Semua histori
$semua = $conn->query("SELECT p.*,b.gambar,b.kategori,u.nama AS nama_user FROM peminjaman p JOIN buku b ON p.id_buku=b.id_buku JOIN users u ON p.id_user=u.id_user ORDER BY p.id_pinjam DESC LIMIT 100");
?><!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><title>Kelola Peminjaman | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="../style.css">
<style>
body{display:flex;min-height:100vh;}
.admin-wrap{display:flex;width:100%;min-height:100vh;}
.main-content{flex:1;padding:32px;overflow-x:hidden;}
.page-title{font-family:'Playfair Display',serif;font-size:1.7rem;color:#fff;margin-bottom:4px;}
.atab{display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap;}
.atab a{padding:9px 20px;border-radius:20px;font-size:.83rem;font-weight:500;border:1px solid var(--border);color:var(--muted);text-decoration:none;transition:all .2s;display:flex;align-items:center;gap:6px;}
.atab a:hover,.atab a.active{background:var(--accent);border-color:var(--accent);color:#0c1520;font-weight:600;}
.atab a .cnt-b{background:rgba(0,0,0,.18);border-radius:10px;padding:1px 7px;font-size:.68rem;}
.req-row{background:var(--card);border:1px solid var(--border);border-radius:13px;display:flex;align-items:center;gap:14px;padding:14px 18px;margin-bottom:10px;}
.req-row:hover{border-color:rgba(232,184,109,.2);}
.mini-cover{width:44px;height:56px;border-radius:8px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:20px;}
.mini-cover img{width:100%;height:100%;object-fit:cover;}
.mini-cover.c-umum{background:linear-gradient(135deg,#0d2540,#1a3d60);}
.mini-cover.c-motivasi{background:linear-gradient(135deg,#2d1f00,#5a3c00);}
.mini-cover.c-sastra{background:linear-gradient(135deg,#1f0d2d,#3d1a5a);}
.mini-cover.c-religi{background:linear-gradient(135deg,#0d2d1a,#1a5a3c);}
.mini-cover.c-default{background:linear-gradient(135deg,#1a1f2d,#2d3a4a);}
.badge-m{background:rgba(240,192,112,.12);color:#f0c070;border:1px solid rgba(240,192,112,.25);border-radius:20px;font-size:.67rem;font-weight:600;padding:3px 9px;}
.badge-ok{background:rgba(58,171,110,.12);color:#5dd896;border:1px solid rgba(58,171,110,.25);border-radius:20px;font-size:.67rem;font-weight:600;padding:3px 9px;}
.badge-no{background:rgba(232,93,93,.1);color:#ff9090;border:1px solid rgba(232,93,93,.25);border-radius:20px;font-size:.67rem;font-weight:600;padding:3px 9px;}
.badge-tl{background:rgba(232,93,93,.15);color:#ff9090;border:1px solid rgba(232,93,93,.3);border-radius:20px;font-size:.67rem;font-weight:600;padding:3px 9px;}
.btn-setujui{background:linear-gradient(135deg,var(--accent),#d4a54e);border:none;border-radius:8px;padding:7px 16px;font-size:.78rem;font-weight:600;color:#0c1520;cursor:pointer;text-decoration:none;white-space:nowrap;}
.btn-setujui:hover{opacity:.88;color:#0c1520;}
.btn-tolak{background:none;border:1px solid rgba(232,93,93,.3);border-radius:8px;padding:7px 14px;font-size:.78rem;font-weight:600;color:#ff9090;cursor:pointer;white-space:nowrap;}
.btn-tolak:hover{background:rgba(232,93,93,.1);}
.modal-tolak{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999;align-items:center;justify-content:center;}
.modal-tolak.show{display:flex;}
.modal-box{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:26px;width:400px;max-width:92vw;}
.alert-ok{background:rgba(58,171,110,.1);border:1px solid rgba(58,171,110,.3);color:#5dd896;border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:.85rem;display:flex;gap:8px;}
.alert-err{background:rgba(232,93,93,.1);border:1px solid rgba(232,93,93,.3);color:#ff9090;border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:.85rem;display:flex;gap:8px;}
.empty-state{text-align:center;padding:44px;background:var(--card);border:1px dashed var(--border);border-radius:13px;color:var(--muted);}
.empty-state i{font-size:2.4rem;display:block;margin-bottom:10px;opacity:.3;}
.terlambat-row{border-color:rgba(232,93,93,.3)!important;background:rgba(232,93,93,.03)!important;}
</style>
</head><body>
<?php include 'sidebar.php'; ?>
<div class="admin-wrap"><div class="main-content">
<div style="margin-bottom:24px;">
    <div class="sec-label" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.12em;color:var(--accent);font-weight:600;margin-bottom:4px;">Manajemen</div>
    <h1 class="page-title">Kelola Peminjaman</h1>
</div>
<?php if($msg): ?><div class="alert-ok"><i class="bi bi-check-circle-fill"></i><?= $msg ?></div><?php endif; ?>
<?php if($error): ?><div class="alert-err"><i class="bi bi-exclamation-triangle-fill"></i><?= $error ?></div><?php endif; ?>

<div class="atab">
    <a href="?tab=request" class="<?= $tab==='request'?'active':'' ?>"><i class="bi bi-inbox"></i>Permintaan <span class="cnt-b"><?= $cnt_req ?></span></a>
    <a href="?tab=aktif"   class="<?= $tab==='aktif'  ?'active':'' ?>"><i class="bi bi-book-half"></i>Dipinjam <span class="cnt-b"><?= $cnt_aktif ?></span></a>
    <a href="?tab=semua"   class="<?= $tab==='semua'  ?'active':'' ?>"><i class="bi bi-list-ul"></i>Semua Histori</a>
</div>

<?php if($tab==='request'): ?>
<?php if($cnt_req>0): while($r=$requests->fetch_assoc()):
    $kk=$r['kategori']?strtolower($r['kategori']):''; $cmap=['umum'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-umum'],'motivasi'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-motivasi'],'sastra'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-sastra'],'religi'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-religi']];
    $cm=$cmap[$kk]??['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-default'];
?>
<div class="req-row">
    <div class="mini-cover <?= $cm['c'] ?>" data-book-title="<?= htmlspecialchars($r['nama_buku'] ?? '') ?>"><?php if(!empty($r['gambar'])&&file_exists('../img/buku/'.$r['gambar'])): ?><img src="../img/buku/<?= htmlspecialchars($r['gambar']) ?>"><?php else: ?><?= $cm['e'] ?><?php endif; ?></div>
    <div style="flex:1;min-width:0;">
        <div style="font-weight:600;color:#fff;font-size:.9rem;"><?= htmlspecialchars($r['nama_buku']) ?></div>
        <div style="font-size:.73rem;color:var(--muted);margin-top:3px;display:flex;gap:12px;flex-wrap:wrap;">
            <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($r['nama_user']) ?> (<?= roleLabel($r['role_user']) ?>)</span>
            <span><i class="bi bi-calendar3 me-1"></i><?= date('d M Y',strtotime($r['tanggal_request'])) ?></span>
            <span><i class="bi bi-layers me-1"></i>Stok: <?= $r['stock'] ?></span>
        </div>
        <?php if($r['catatan']): ?><div style="font-size:.73rem;color:var(--muted);margin-top:3px;font-style:italic;">"<?= htmlspecialchars($r['catatan']) ?>"</div><?php endif; ?>
    </div>
    <?php if($canEdit): ?>
    <div style="display:flex;gap:8px;flex-shrink:0;flex-wrap:wrap;">
        <a href="?setujui=<?= $r['id_request'] ?>&tab=request" class="btn-setujui" onclick="return confirm('Setujui peminjaman ini?')"><i class="bi bi-check-lg me-1"></i>Setujui</a>
        <button class="btn-tolak" onclick="bukaTolak(<?= $r['id_request'] ?>)"><i class="bi bi-x-lg me-1"></i>Tolak</button>
    </div>
    <?php else: ?><span class="badge-m">Menunggu</span><?php endif; ?>
</div>
<?php endwhile; else: ?><div class="empty-state"><i class="bi bi-inbox"></i><p>Tidak ada permintaan yang menunggu.</p></div><?php endif; ?>

<?php elseif($tab==='aktif'): ?>
<?php if($cnt_aktif>0): while($r=$aktif->fetch_assoc()):
    $kk=strtolower($r['kategori']??''); $cmap=['umum'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-umum'],'motivasi'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-motivasi'],'sastra'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-sastra'],'religi'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-religi']];
    $cm=$cmap[$kk]??['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-default'];
    $tl = strtotime(date('Y-m-d'))>strtotime($r['batas_kembali']);
    $sisa = ceil((strtotime($r['batas_kembali'])-strtotime(date('Y-m-d')))/86400);
    $denda_pred = $tl ? abs($sisa)*DENDA_PER_HARI : 0;
?>
<div class="req-row <?= $tl?'terlambat-row':'' ?>">
    <div class="mini-cover <?= $cm['c'] ?>" data-book-title="<?= htmlspecialchars($r['nama_buku'] ?? '') ?>"><?php if(!empty($r['gambar'])&&file_exists('../img/buku/'.$r['gambar'])): ?><img src="../img/buku/<?= htmlspecialchars($r['gambar']) ?>"><?php else: ?><?= $cm['e'] ?><?php endif; ?></div>
    <div style="flex:1;min-width:0;">
        <div style="font-weight:600;color:#fff;font-size:.9rem;"><?= htmlspecialchars($r['nama_buku']) ?></div>
        <div style="font-size:.73rem;color:var(--muted);margin-top:3px;display:flex;gap:12px;flex-wrap:wrap;">
            <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($r['nama_user']) ?></span>
            <span><i class="bi bi-calendar3 me-1"></i>Pinjam: <?= date('d M Y',strtotime($r['tanggal_peminjaman'])) ?></span>
            <span><i class="bi bi-calendar-x me-1"></i>Batas: <?= date('d M Y',strtotime($r['batas_kembali'])) ?></span>
            <?php if($tl): ?><span style="color:#ff9090"><i class="bi bi-exclamation-triangle me-1"></i>Terlambat <?= abs($sisa) ?> hari | Denda perkiraan: <?= rupiah($denda_pred) ?></span><?php endif; ?>
        </div>
    </div>
    <?php if($canEdit): ?>
    <a href="pengembalian.php?proses=<?= $r['id_pinjam'] ?>" style="text-decoration:none;"><button class="btn-setujui"><i class="bi bi-arrow-return-left me-1"></i>Proses Kembali</button></a>
    <?php elseif($tl): ?><span class="badge-tl">Terlambat</span><?php else: ?><span class="badge-ok">Aktif</span><?php endif; ?>
</div>
<?php endwhile; else: ?><div class="empty-state"><i class="bi bi-book-half"></i><p>Tidak ada pinjaman aktif.</p></div><?php endif; ?>

<?php else: // semua ?>
<?php while($r=$semua->fetch_assoc()):
    $kk=strtolower($r['kategori']??''); $cmap=['umum'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-umum'],'motivasi'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-motivasi'],'sastra'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-sastra'],'religi'=>['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-religi']];
    $cm=$cmap[$kk]??['e'=>'<img src="../img/logo-wandek.png" alt="" style="width:36px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','c'=>'c-default'];
?>
<div class="req-row">
    <div class="mini-cover <?= $cm['c'] ?>" data-book-title="<?= htmlspecialchars($r['nama_buku'] ?? '') ?>"><?php if(!empty($r['gambar'])&&file_exists('../img/buku/'.$r['gambar'])): ?><img src="../img/buku/<?= htmlspecialchars($r['gambar']) ?>"><?php else: ?><?= $cm['e'] ?><?php endif; ?></div>
    <div style="flex:1;min-width:0;">
        <div style="font-weight:600;color:#fff;font-size:.9rem;"><?= htmlspecialchars($r['nama_buku']) ?></div>
        <div style="font-size:.73rem;color:var(--muted);margin-top:3px;display:flex;gap:12px;flex-wrap:wrap;">
            <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($r['nama_user']) ?></span>
            <span><i class="bi bi-calendar3 me-1"></i><?= date('d M Y',strtotime($r['tanggal_peminjaman'])) ?></span>
        </div>
    </div>
    <?= $r['status']==='Ada' ? '<span class="badge-ok">Dikembalikan</span>' : '<span class="badge-m">Belum Kembali</span>' ?>
</div>
<?php endwhile; ?>
<?php endif; ?>

</div></div>

<!-- Modal Tolak -->
<div class="modal-tolak" id="modalTolak">
    <div class="modal-box">
        <h4 style="font-family:'Playfair Display',serif;color:#fff;margin-bottom:14px;"><i class="bi bi-x-circle me-2"></i>Tolak Permintaan</h4>
        <form method="POST">
            <input type="hidden" name="aksi" value="tolak">
            <input type="hidden" name="id_request" id="inputReq">
            <input type="hidden" name="tab" value="request">
            <label style="font-size:.8rem;color:var(--muted);margin-bottom:6px;display:block;">Alasan penolakan (opsional)</label>
            <textarea name="alasan_tolak" style="width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:var(--text);border-radius:9px;padding:10px;font-family:'DM Sans',sans-serif;font-size:.85rem;resize:vertical;min-height:70px;" placeholder="misal: stok sedang dalam perbaikan..."></textarea>
            <div style="display:flex;gap:10px;margin-top:14px;">
                <button type="button" onclick="tutupTolak()" style="flex:1;padding:9px;border-radius:9px;background:none;border:1px solid var(--border);color:var(--muted);cursor:pointer;">Batal</button>
                <button type="submit" style="flex:2;padding:9px;border-radius:9px;background:rgba(232,93,93,.15);border:1px solid rgba(232,93,93,.3);color:#ff9090;font-weight:600;cursor:pointer;"><i class="bi bi-x-lg me-1"></i>Tolak</button>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function bukaTolak(id){document.getElementById('inputReq').value=id;document.getElementById('modalTolak').classList.add('show');}
function tutupTolak(){document.getElementById('modalTolak').classList.remove('show');}
document.getElementById('modalTolak').addEventListener('click',function(e){if(e.target===this)tutupTolak();});
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
