<?php
session_start();
require_once 'config/database.php';
requireLogin('login.php?redirect=form-pengembalian.php');
if(!canBorrow()) redirect('index.php');

$loggedIn=true; $namaUser=$_SESSION['nama']??''; $roleUser=$_SESSION['role']??'';
$id_user=(int)$_SESSION['id_user']; $base=''; $msg=$error='';

// Proses ajukan pengembalian
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['aksi']??'')==='ajukan'){
    $id_pinjam=(int)$_POST['id_pinjam'];
    $kondisi=in_array($_POST['kondisi']??'',['Baik','Rusak Ringan','Rusak Berat'])?$_POST['kondisi']:'Baik';
    $catatan=sanitize($_POST['catatan']??'');
    $tgl=date('Y-m-d');

    $pinjam=$conn->query("SELECT * FROM peminjaman WHERE id_pinjam=$id_pinjam AND id_user=$id_user AND status='Tidak Ada'")->fetch_assoc();
    if(!$pinjam){ $error='Data peminjaman tidak valid.'; }
    else {
        $ada=$conn->query("SELECT id_req_kembali FROM request_pengembalian WHERE id_pinjam=$id_pinjam AND status='menunggu'")->num_rows;
        if($ada){ $error='Kamu sudah mengajukan pengembalian untuk buku ini, tunggu diproses pustakawan.'; }
        else {
            $s=$conn->prepare("INSERT INTO request_pengembalian (id_pinjam,id_buku,id_user,tanggal_request,kondisi_buku,catatan,status) VALUES (?,?,?,?,?,?,'menunggu')");
            $s->bind_param("iiisss",$id_pinjam,$pinjam['id_buku'],$id_user,$tgl,$kondisi,$catatan);
            $s->execute()?($msg='✅ Pengajuan pengembalian terkirim! Pustakawan akan memverifikasi dan menghitung denda jika ada.'):($error='Gagal mengirim pengajuan.');
            $s->close();
        }
    }
}

// Batal request
if(isset($_GET['batal'])){
    $rid=(int)$_GET['batal'];
    $conn->query("DELETE FROM request_pengembalian WHERE id_req_kembali=$rid AND id_user=$id_user AND status='menunggu'");
    redirect('form-pengembalian.php?msg=batal');
}
if(isset($_GET['msg'])&&$_GET['msg']==='batal') $msg='Pengajuan pengembalian dibatalkan.';

// Data pinjaman aktif
$pinjaman_aktif=$conn->query("SELECT p.*,b.gambar,b.kategori,b.penerbit FROM peminjaman p JOIN buku b ON p.id_buku=b.id_buku WHERE p.id_user=$id_user AND p.status='Tidak Ada' ORDER BY p.batas_kembali ASC");

// Request pengembalian sedang diajukan
$req_kembali=$conn->query("SELECT r.*,p.nama_buku,p.batas_kembali,b.gambar,b.kategori FROM request_pengembalian r JOIN peminjaman p ON r.id_pinjam=p.id_pinjam JOIN buku b ON r.id_buku=b.id_buku WHERE r.id_user=$id_user ORDER BY r.id_req_kembali DESC LIMIT 10");

// id pinjam yang sudah diajukan
$ajukan_ids=[];
$rq=$conn->query("SELECT id_pinjam FROM request_pengembalian WHERE id_user=$id_user AND status='menunggu'");
while($r=$rq->fetch_assoc()) $ajukan_ids[]=$r['id_pinjam'];

$cm_map=['umum'=>['e'=>'📖','c'=>'c-umum'],'motivasi'=>['e'=>'🚀','c'=>'c-motivasi'],'sastra'=>['e'=>'✍️','c'=>'c-sastra'],'religi'=>['e'=>'🕌','c'=>'c-religi']];
?><!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pengajuan Pengembalian | E-Library</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="style.css">
<style>
.ph1{padding:40px 0 26px;border-bottom:1px solid var(--border);}
.ph1 h1{font-family:'Playfair Display',serif;font-size:1.9rem;color:#fff;margin-bottom:4px;}
.pb1{padding:32px 0 80px;}
.sec-lbl{font-size:.68rem;text-transform:uppercase;letter-spacing:.12em;color:var(--accent);font-weight:600;margin-bottom:4px;}
.sec-t{font-family:'Playfair Display',serif;font-size:1.3rem;color:#fff;margin-bottom:16px;}
.pinjam-card{background:var(--card);border:1px solid var(--border);border-radius:13px;display:flex;align-items:center;gap:14px;padding:14px 18px;margin-bottom:10px;transition:border-color .2s;}
.pinjam-card:hover{border-color:rgba(232,184,109,.2);}
.pinjam-card.terlambat{border-color:rgba(232,93,93,.3);background:rgba(232,93,93,.03);}
.p-cover{width:46px;height:58px;border-radius:8px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:22px;position:relative;}
.p-cover img.cover-img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0;}
.p-cover img.logo-ph{width:24px;opacity:.6;}
.c-umum{background:linear-gradient(135deg,#0d2540,#1a3d60);}
.c-motivasi{background:linear-gradient(135deg,#2d1f00,#5a3c00);}
.c-sastra{background:linear-gradient(135deg,#1f0d2d,#3d1a5a);}
.c-religi{background:linear-gradient(135deg,#0d2d1a,#1a5a3c);}
.c-default{background:linear-gradient(135deg,#1a1f2d,#2d3a4a);}
.badge-tl{background:rgba(232,93,93,.15);color:#ff9090;border:1px solid rgba(232,93,93,.3);border-radius:20px;font-size:.68rem;font-weight:600;padding:3px 10px;}
.badge-ok{background:rgba(58,171,110,.12);color:var(--green);border:1px solid rgba(58,171,110,.25);border-radius:20px;font-size:.68rem;font-weight:600;padding:3px 10px;}
.badge-m{background:rgba(232,184,109,.12);color:var(--accent);border:1px solid rgba(232,184,109,.25);border-radius:20px;font-size:.68rem;font-weight:600;padding:3px 10px;}
.btn-ajukan{background:linear-gradient(135deg,var(--accent),#d4a54e);border:none;border-radius:8px;padding:8px 16px;font-size:.8rem;font-weight:600;color:#0c1520;cursor:pointer;white-space:nowrap;transition:opacity .2s;}
.btn-ajukan:hover{opacity:.88;}
.btn-ajukan:disabled{background:rgba(255,255,255,.06);color:var(--muted);border:1px solid var(--border);cursor:not-allowed;}
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999;align-items:center;justify-content:center;}
.modal-bg.show{display:flex;}
.modal-box{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:28px;width:460px;max-width:94vw;}
.modal-box h4{font-family:'Playfair Display',serif;color:#fff;margin-bottom:6px;}
.fi{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:var(--text);border-radius:9px;padding:10px 13px;font-size:.86rem;font-family:'DM Sans',sans-serif;transition:border .2s;}
.fi:focus{outline:none;border-color:var(--accent);}
.fi-select option{background:#152135;}
.alert-ok{background:rgba(58,171,110,.1);border:1px solid rgba(58,171,110,.3);color:#5dd896;border-radius:11px;padding:13px 16px;margin-bottom:20px;font-size:.87rem;display:flex;gap:9px;}
.alert-err{background:rgba(232,93,93,.1);border:1px solid rgba(232,93,93,.3);color:#ff9090;border-radius:11px;padding:13px 16px;margin-bottom:20px;font-size:.87rem;display:flex;gap:9px;}
.info-box{background:rgba(232,184,109,.06);border:1px solid rgba(232,184,109,.15);border-radius:12px;padding:13px 16px;margin-bottom:24px;font-size:.82rem;color:var(--muted);display:flex;gap:9px;}
.divider{border:none;border-top:1px solid var(--border);margin:28px 0;}
.req-row{display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid var(--border);}
.req-row:last-child{border-bottom:none;}
.btn-batal-kecil{font-size:.73rem;color:var(--muted);background:none;border:1px solid var(--border);border-radius:7px;padding:4px 10px;cursor:pointer;transition:all .2s;}
.btn-batal-kecil:hover{border-color:rgba(232,93,93,.4);color:#ff9090;}
</style>
</head><body>
<?php include 'includes/navbar.php'; ?>
<div class="ph1"><div class="container">
    <div class="sec-lbl mb-1">Sistem Pengembalian</div>
    <h1>Pengajuan Pengembalian</h1>
    <p style="color:var(--muted);font-size:.87rem;margin:0">Ajukan pengembalian buku — pustakawan akan memverifikasi dan menghitung denda jika ada.</p>
</div></div>

<div class="container pb1">
<?php if($msg): ?><div class="alert-ok"><i class="bi bi-check-circle-fill"></i><div><?= $msg ?></div></div><?php endif; ?>
<?php if($error): ?><div class="alert-err"><i class="bi bi-exclamation-triangle-fill"></i><div><?= $error ?></div></div><?php endif; ?>

<div class="info-box">
    <i class="bi bi-info-circle-fill" style="color:var(--accent);font-size:1rem;flex-shrink:0;margin-top:1px;"></i>
    <div><strong style="color:var(--accent)">Alur Pengembalian:</strong> Ajukan pengembalian di sini → Bawa buku fisik ke perpustakaan → Pustakawan verifikasi kondisi &amp; hitung denda → Stok dikembalikan. Denda keterlambatan: <strong style="color:#fff">Rp 1.000/hari</strong>.</div>
</div>

<!-- Request dalam proses -->
<?php if($req_kembali && $req_kembali->num_rows > 0): ?>
<div class="sec-lbl">Pengajuan Dalam Proses</div>
<div class="panel mb-4">
    <div style="padding:0 18px;">
    <?php while($r=$req_kembali->fetch_assoc()):
        $kk=strtolower($r['kategori']??'');
        $cm=$cm_map[$kk]??['e'=>'📚','c'=>'c-default'];
        $bs=['menunggu'=>'badge-m','disetujui'=>'badge-ok','ditolak'=>'badge-tl'];
        $bl=['menunggu'=>'Menunggu Verifikasi','disetujui'=>'Disetujui','ditolak'=>'Ditolak'];
        $st=$r['status'];
    ?>
    <div class="req-row">
        <div class="p-cover <?= $cm['c'] ?>" data-book-title="<?= htmlspecialchars($r['nama_buku']) ?>">
            <?php if(!empty($r['gambar'])&&file_exists('img/buku/'.$r['gambar'])): ?>
                <img src="img/buku/<?= htmlspecialchars($r['gambar']) ?>" class="cover-img">
            <?php else: ?><img src="img/logo-wandek.png" class="logo-ph"><?php endif; ?>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-weight:600;color:#fff;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($r['nama_buku']) ?></div>
            <div style="font-size:.72rem;color:var(--muted);margin-top:2px;">Kondisi: <?= $r['kondisi_buku'] ?> · <?= date('d M Y',strtotime($r['tanggal_request'])) ?></div>
            <?php if($r['status']==='disetujui'&&$r['denda']>0): ?>
            <div style="font-size:.74rem;color:#ff9090;margin-top:2px;"><i class="bi bi-cash-coin me-1"></i>Denda: <?= rupiah($r['denda']) ?></div>
            <?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
            <span class="<?= $bs[$st]??'badge-m' ?>"><?= $bl[$st]??$st ?></span>
            <?php if($st==='menunggu'): ?>
            <a href="form-pengembalian.php?batal=<?= $r['id_req_kembali'] ?>" class="btn-batal-kecil" onclick="return confirm('Batalkan pengajuan ini?')">Batal</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; ?>
    </div>
</div>
<hr class="divider">
<?php endif; ?>

<!-- Pinjaman aktif -->
<div class="sec-lbl">Pinjaman Aktif</div>
<div class="sec-t">Pilih Buku untuk Dikembalikan</div>

<?php if($pinjaman_aktif && $pinjaman_aktif->num_rows > 0):
while($r=$pinjaman_aktif->fetch_assoc()):
    $kk=strtolower($r['kategori']??'');
    $cm=$cm_map[$kk]??['e'=>'📚','c'=>'c-default'];
    $terlambat=strtotime(date('Y-m-d'))>strtotime($r['batas_kembali']);
    $sisa=ceil((strtotime($r['batas_kembali'])-strtotime(date('Y-m-d')))/86400);
    $sudahAjukan=in_array($r['id_pinjam'],$ajukan_ids);
?>
<div class="pinjam-card <?= $terlambat?'terlambat':'' ?>">
    <div class="p-cover <?= $cm['c'] ?>" data-book-title="<?= htmlspecialchars($r['nama_buku']) ?>">
        <?php if(!empty($r['gambar'])&&file_exists('img/buku/'.$r['gambar'])): ?>
            <img src="img/buku/<?= htmlspecialchars($r['gambar']) ?>" class="cover-img">
        <?php else: ?><img src="img/logo-wandek.png" class="logo-ph"><?php endif; ?>
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-weight:600;color:#fff;font-size:.9rem;"><?= htmlspecialchars($r['nama_buku']) ?></div>
        <div style="font-size:.73rem;color:var(--muted);margin-top:3px;display:flex;gap:12px;flex-wrap:wrap;">
            <span><i class="bi bi-calendar3 me-1"></i>Dipinjam: <?= date('d M Y',strtotime($r['tanggal_peminjaman'])) ?></span>
            <span><i class="bi bi-calendar-x me-1"></i>Batas: <?= date('d M Y',strtotime($r['batas_kembali'])) ?></span>
            <?php if($terlambat): ?>
            <span style="color:#ff9090;"><i class="bi bi-exclamation-triangle me-1"></i>Terlambat <?= abs($sisa) ?> hari · Est. denda <?= rupiah(abs($sisa)*DENDA_PER_HARI) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div style="flex-shrink:0;display:flex;align-items:center;gap:8px;">
        <?= $terlambat?'<span class="badge-tl">Terlambat</span>':'<span class="badge-ok">Sisa '.max(0,$sisa).' hari</span>' ?>
        <?php if($sudahAjukan): ?>
        <button class="btn-ajukan" disabled><i class="bi bi-hourglass-split me-1"></i>Diajukan</button>
        <?php else: ?>
        <button class="btn-ajukan" onclick="bukaModal(<?= $r['id_pinjam'] ?>,<?= json_encode($r['nama_buku']) ?>,<?= $terlambat?abs($sisa):0 ?>)">
            <i class="bi bi-arrow-return-left me-1"></i>Ajukan Kembali
        </button>
        <?php endif; ?>
    </div>
</div>
<?php endwhile;
else: ?>
<div style="text-align:center;padding:40px;background:var(--card);border:1px dashed var(--border);border-radius:13px;color:var(--muted);font-size:.85rem;">
    <i class="bi bi-book-half" style="font-size:1.8rem;display:block;margin-bottom:8px;opacity:.3;"></i>
    Tidak ada buku yang sedang dipinjam.
    <a href="peminjaman-buku.php" style="color:var(--accent);text-decoration:none;display:block;margin-top:8px;">Pinjam buku sekarang →</a>
</div>
<?php endif; ?>
</div>

<!-- Modal -->
<div class="modal-bg" id="modalKembali">
    <div class="modal-box">
        <h4><i class="bi bi-arrow-return-left me-2" style="color:var(--accent)"></i>Ajukan Pengembalian</h4>
        <p id="mkJudul" style="color:var(--accent);font-size:.88rem;font-weight:600;margin-bottom:16px;"></p>
        <div id="mkDendaInfo" style="display:none;background:rgba(232,93,93,.08);border:1px solid rgba(232,93,93,.2);border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:.8rem;color:#ff9090;">
            <i class="bi bi-exclamation-triangle me-1"></i>Buku ini terlambat. Perkiraan denda: <strong id="mkDendaEst"></strong>
        </div>
        <form method="POST" id="formKembali">
            <input type="hidden" name="aksi" value="ajukan">
            <input type="hidden" name="id_pinjam" id="mkPinjam">
            <div style="margin-bottom:14px;">
                <label style="font-size:.75rem;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);display:block;margin-bottom:5px;">Kondisi Buku</label>
                <select name="kondisi" class="fi fi-select">
                    <option value="Baik">Baik — tidak ada kerusakan</option>
                    <option value="Rusak Ringan">Rusak Ringan — lecet / halaman lusuh</option>
                    <option value="Rusak Berat">Rusak Berat — robek / basah / hilang halaman</option>
                </select>
            </div>
            <div style="margin-bottom:18px;">
                <label style="font-size:.75rem;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);display:block;margin-bottom:5px;">Catatan (opsional)</label>
                <textarea name="catatan" class="fi" style="resize:vertical;min-height:60px;" placeholder="Ceritakan kondisi buku jika ada kerusakan..."></textarea>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="tutupModal()" style="flex:1;padding:10px;border-radius:9px;background:none;border:1px solid var(--border);color:var(--muted);cursor:pointer;">Batal</button>
                <button type="submit" style="flex:2;padding:10px;border-radius:9px;background:linear-gradient(135deg,var(--accent),#d4a54e);border:none;font-weight:600;color:#0c1520;cursor:pointer;"><i class="bi bi-send me-1"></i>Kirim Pengajuan</button>
            </div>
        </form>
    </div>
</div>

<footer class="site-footer"><div class="container"><div class="footer-inner">
    <div class="footer-brand"><img src="img/logo-wandek.png" style="width:24px;vertical-align:middle;margin-right:6px;">E-Library SMKN 1 Depok</div>
    <div class="footer-copy">&copy; <?= date('Y') ?> E-Library. All Rights Reserved.</div>
    <div class="footer-links"><a href="peminjaman-buku.php">Peminjaman</a><a href="riwayat-peminjaman.php">Riwayat</a></div>
</div></div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.addEventListener('scroll',()=>document.getElementById('mainNav').classList.toggle('scrolled',scrollY>30));
const DENDA=<?= DENDA_PER_HARI ?>;
function rupiah(n){return 'Rp '+n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.');}
function bukaModal(pid,judul,hariTl){
    document.getElementById('mkPinjam').value=pid;
    document.getElementById('mkJudul').textContent=judul;
    const di=document.getElementById('mkDendaInfo');
    if(hariTl>0){di.style.display='block';document.getElementById('mkDendaEst').textContent=rupiah(hariTl*DENDA);}
    else di.style.display='none';
    document.getElementById('modalKembali').classList.add('show');
}
function tutupModal(){document.getElementById('modalKembali').classList.remove('show');}
document.getElementById('modalKembali').addEventListener('click',function(e){if(e.target===this)tutupModal();});
// Cover fetch
(function(){
function fc(div){var t=div.dataset.bookTitle;if(!t)return;var ph=div.querySelector('.logo-ph');if(!ph)return;fetch('https://www.googleapis.com/books/v1/volumes?q='+encodeURIComponent(t)+'&maxResults=1').then(r=>r.json()).then(d=>{var th=d.items?.[0]?.volumeInfo?.imageLinks?.thumbnail;if(th){var img=document.createElement('img');img.src=th.replace('http:','https:');img.className='cover-img';img.style.cssText='width:100%;height:100%;object-fit:cover;position:absolute;inset:0;';img.onerror=()=>img.remove();ph.replaceWith(img);}}).catch(()=>{});}
document.querySelectorAll('[data-book-title]').forEach((d,i)=>setTimeout(()=>fc(d),i*150));
})();
</script>
</body></html>
