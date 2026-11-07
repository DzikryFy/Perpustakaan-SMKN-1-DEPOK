<?php
session_start();
require_once '../config/database.php';
requireStaff();
$base='../'; $loggedIn=true; $namaUser=$_SESSION['nama']??''; $roleUser=$_SESSION['role']??'';
$msg=$error=''; $canEdit=isPustakawan();

// ── SETUJUI REQUEST PENGEMBALIAN ─────────────────────────────
if($canEdit && $_SERVER['REQUEST_METHOD']==='POST' && ($_POST['aksi']??'')==='setujui'){
    $rid=(int)$_POST['id_req'];
    $kondisi=in_array($_POST['kondisi_buku']??'',['Baik','Rusak Ringan','Rusak Berat'])?$_POST['kondisi_buku']:'Baik';
    $catatan=sanitize($_POST['catatan_petugas']??'');
    $tgl_kembali=$_POST['tanggal_pengembalian']??date('Y-m-d');

    $req=$conn->query("SELECT r.*,p.batas_kembali,p.id_buku,p.id_user,p.nama_buku FROM request_pengembalian r JOIN peminjaman p ON r.id_pinjam=p.id_pinjam WHERE r.id_req_kembali=$rid AND r.status='menunggu'")->fetch_assoc();
    if(!$req){ $error='Request tidak ditemukan.'; }
    else {
        $denda_hari=hitungDenda($req['batas_kembali'],$tgl_kembali);
        $denda_rusak=$kondisi==='Rusak Berat'?50000:($kondisi==='Rusak Ringan'?10000:0);
        $total_denda=$denda_hari+$denda_rusak;
        $nb=$conn->real_escape_string($req['nama_buku']);

        $s=$conn->prepare("INSERT INTO pengembalian (id_pinjam,id_buku,id_user,tanggal_pengembalian,nama_buku,denda,kondisi_buku,catatan_petugas,status) VALUES (?,?,?,?,?,?,?,?,'Ada')");
        $s->bind_param("iiississ",$req['id_pinjam'],$req['id_buku'],$req['id_user'],$tgl_kembali,$nb,$total_denda,$kondisi,$catatan);
        if($s->execute()){
            $conn->query("UPDATE peminjaman SET status='Ada' WHERE id_pinjam={$req['id_pinjam']}");
            $conn->query("UPDATE buku SET stock=stock+1 WHERE id_buku={$req['id_buku']}");
            $conn->query("UPDATE request_pengembalian SET status='disetujui',denda=$total_denda WHERE id_req_kembali=$rid");
            $msg='Pengembalian dikonfirmasi. Denda: '.rupiah($total_denda);
        } else { $error='Gagal menyimpan pengembalian: '.$conn->error; }
        $s->close();
    }
}

// ── TOLAK REQUEST ─────────────────────────────────────────────
if($canEdit && isset($_GET['tolak'])){
    $rid=(int)$_GET['tolak'];
    $conn->query("UPDATE request_pengembalian SET status='ditolak' WHERE id_req_kembali=$rid AND status='menunggu'");
    $msg='Pengajuan pengembalian ditolak.';
}

$tab=$_GET['tab']??'request';

// Data
$requests=$conn->query("SELECT r.*,p.batas_kembali,p.tanggal_peminjaman,b.nama_buku,b.gambar,b.kategori,u.nama AS nama_user FROM request_pengembalian r JOIN peminjaman p ON r.id_pinjam=p.id_pinjam JOIN buku b ON r.id_buku=b.id_buku JOIN users u ON r.id_user=u.id_user WHERE r.status='menunggu' ORDER BY r.tanggal_request ASC");
$cnt_req=$requests->num_rows;
$histori=$conn->query("SELECT pg.*,u.nama AS nama_user FROM pengembalian pg JOIN users u ON pg.id_user=u.id_user ORDER BY pg.id_pengembalian DESC LIMIT 80");
$total_denda_bln=$conn->query("SELECT SUM(denda) s FROM pengembalian WHERE MONTH(tanggal_pengembalian)=MONTH(NOW()) AND YEAR(tanggal_pengembalian)=YEAR(NOW())")->fetch_assoc()['s']??0;
$aktif_pinjam=$conn->query("SELECT COUNT(*) c FROM peminjaman WHERE status='Tidak Ada'")->fetch_assoc()['c'];
$terlambat=$conn->query("SELECT COUNT(*) c FROM peminjaman WHERE status='Tidak Ada' AND batas_kembali<CURDATE()")->fetch_assoc()['c'];
?><!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pengembalian Buku | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<?php include 'sidebar.php'; ?>
<style>
.main-content{margin-left:230px;padding:30px;min-height:100vh;}
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;}
.topbar h1{font-family:'Playfair Display',serif;font-size:1.7rem;color:#fff;margin:0;}
.stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:22px;}
.sc{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px;display:flex;align-items:center;gap:12px;}
.sc .ico{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.ico-y{background:rgba(232,184,109,.12);color:var(--accent);}
.ico-r{background:rgba(232,93,93,.12);color:var(--red);}
.ico-g{background:rgba(58,171,110,.12);color:var(--green);}
.sc .num{font-size:1.5rem;font-weight:700;color:#fff;line-height:1;}
.sc .lbl{font-size:.68rem;color:var(--muted);text-transform:uppercase;}
.atab{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;}
.atab a{padding:8px 18px;border-radius:20px;font-size:.82rem;border:1px solid var(--border);color:var(--muted);text-decoration:none;transition:all .2s;display:flex;align-items:center;gap:6px;}
.atab a.active,.atab a:hover{background:var(--accent);border-color:var(--accent);color:#0c1520;font-weight:600;}
.req-row{background:var(--card);border:1px solid var(--border);border-radius:12px;display:flex;align-items:flex-start;gap:14px;padding:16px 18px;margin-bottom:10px;transition:border-color .2s;}
.req-row:hover{border-color:rgba(232,184,109,.2);}
.req-row.terlambat{border-color:rgba(232,93,93,.3)!important;background:rgba(232,93,93,.03)!important;}
.mini-cover{width:44px;height:56px;border-radius:8px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;position:relative;}
.mini-cover img.cover-img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0;}
.mini-cover img.logo-ph{width:24px;opacity:.6;}
.mini-cover.c-umum{background:linear-gradient(135deg,#0d2540,#1a3d60);}
.mini-cover.c-motivasi{background:linear-gradient(135deg,#2d1f00,#5a3c00);}
.mini-cover.c-sastra{background:linear-gradient(135deg,#1f0d2d,#3d1a5a);}
.mini-cover.c-religi{background:linear-gradient(135deg,#0d2d1a,#1a5a3c);}
.mini-cover.c-default{background:linear-gradient(135deg,#1a1f2d,#2d3a4a);}
.btn-setujui{background:linear-gradient(135deg,var(--accent),#d4a54e);border:none;border-radius:8px;padding:7px 15px;font-size:.78rem;font-weight:600;color:#0c1520;cursor:pointer;white-space:nowrap;}
.btn-tolak{background:none;border:1px solid rgba(232,93,93,.3);border-radius:8px;padding:7px 14px;font-size:.78rem;font-weight:600;color:#ff9090;cursor:pointer;white-space:nowrap;text-decoration:none;}
.btn-tolak:hover{background:rgba(232,93,93,.1);color:#ff9090;}
.badge-m{background:rgba(232,184,109,.12);color:var(--accent);border:1px solid rgba(232,184,109,.25);border-radius:20px;font-size:.67rem;padding:3px 9px;font-weight:600;}
.badge-ok{background:rgba(58,171,110,.12);color:var(--green);border:1px solid rgba(58,171,110,.25);border-radius:20px;font-size:.67rem;padding:3px 9px;font-weight:600;}
.badge-tl{background:rgba(232,93,93,.15);color:#ff9090;border:1px solid rgba(232,93,93,.3);border-radius:20px;font-size:.67rem;padding:3px 9px;font-weight:600;}
.alert-ok{background:rgba(58,171,110,.1);border:1px solid rgba(58,171,110,.3);color:#5dd896;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:.85rem;display:flex;gap:8px;align-items:flex-start;}
.alert-err{background:rgba(232,93,93,.1);border:1px solid rgba(232,93,93,.3);color:#ff9090;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:.85rem;display:flex;gap:8px;align-items:flex-start;}
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:9999;align-items:center;justify-content:center;}
.modal-bg.show{display:flex;}
.modal-box{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:28px;width:500px;max-width:95vw;max-height:90vh;overflow-y:auto;}
.fl{font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);display:block;margin-bottom:5px;}
.fi{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:var(--text);border-radius:9px;padding:9px 13px;font-size:.86rem;font-family:'DM Sans',sans-serif;}
.fi:focus{outline:none;border-color:var(--accent);}
.fi option,.fi-select option{background:#152135;}
.denda-box{background:rgba(232,93,93,.06);border:1px solid rgba(232,93,93,.2);border-radius:10px;padding:14px;margin-top:14px;}
.dr{display:flex;justify-content:space-between;font-size:.82rem;color:var(--muted);padding:4px 0;}
.dtotal{font-size:1.05rem;font-weight:700;color:#ff9090;padding-top:8px;border-top:1px solid rgba(232,93,93,.2);display:flex;justify-content:space-between;}
.empty-state{text-align:center;padding:44px;background:var(--card);border:1px dashed var(--border);border-radius:12px;color:var(--muted);}
.empty-state i{font-size:2.2rem;display:block;margin-bottom:10px;opacity:.3;}
@media(max-width:900px){.stat-row{grid-template-columns:1fr 1fr;}.main-content{margin-left:0;}}
</style>
</head><body>
<div class="main-content">
<div class="topbar">
    <div>
        <h1>Pengembalian Buku</h1>
        <div style="font-size:.78rem;color:var(--muted);margin-top:3px;">Verifikasi pengajuan pengembalian dari anggota</div>
    </div>
</div>

<?php if($msg): ?><div class="alert-ok"><i class="bi bi-check-circle-fill"></i><div><?= $msg ?></div></div><?php endif; ?>
<?php if($error): ?><div class="alert-err"><i class="bi bi-exclamation-triangle-fill"></i><div><?= $error ?></div></div><?php endif; ?>

<div class="stat-row">
    <div class="sc"><div class="ico ico-y"><i class="bi bi-inbox"></i></div><div><div class="num"><?= $cnt_req ?></div><div class="lbl">Request Masuk</div></div></div>
    <div class="sc"><div class="ico ico-r"><i class="bi bi-exclamation-triangle"></i></div><div><div class="num"><?= $terlambat ?></div><div class="lbl">Pinjaman Terlambat</div></div></div>
    <div class="sc"><div class="ico ico-g"><i class="bi bi-cash-coin"></i></div><div><div class="num" style="font-size:1rem;"><?= rupiah($total_denda_bln) ?></div><div class="lbl">Denda Bulan Ini</div></div></div>
</div>

<div class="atab">
    <a href="?tab=request" class="<?= $tab==='request'?'active':'' ?>"><i class="bi bi-inbox"></i>Request Masuk <span style="background:rgba(0,0,0,.18);border-radius:10px;padding:1px 7px;font-size:.68rem;"><?= $cnt_req ?></span></a>
    <a href="?tab=histori" class="<?= $tab==='histori'?'active':'' ?>"><i class="bi bi-clock-history"></i>Histori</a>
</div>

<?php if($tab==='request'): ?>
<?php if($cnt_req > 0): while($r=$requests->fetch_assoc()):
    $kk=strtolower($r['kategori']??'');
    $cmap=['umum'=>['e'=>'📖','c'=>'c-umum'],'motivasi'=>['e'=>'🚀','c'=>'c-motivasi'],'sastra'=>['e'=>'✍️','c'=>'c-sastra'],'religi'=>['e'=>'🕌','c'=>'c-religi']];
    $cm=$cmap[$kk]??['e'=>'📚','c'=>'c-default'];
    $tl=strtotime(date('Y-m-d'))>strtotime($r['batas_kembali']);
    $sisa=ceil((strtotime($r['batas_kembali'])-strtotime(date('Y-m-d')))/86400);
    $denda_est=$tl?abs($sisa)*DENDA_PER_HARI:0;
?>
<div class="req-row <?= $tl?'terlambat':'' ?>">
    <div class="mini-cover <?= $cm['c'] ?>" data-book-title="<?= htmlspecialchars($r['nama_buku']) ?>">
        <?php if(!empty($r['gambar'])&&file_exists('../img/buku/'.$r['gambar'])): ?>
            <img src="../img/buku/<?= htmlspecialchars($r['gambar']) ?>" class="cover-img">
        <?php else: ?><img src="../img/logo-wandek.png" class="logo-ph"><?php endif; ?>
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-weight:600;color:#fff;font-size:.9rem;"><?= htmlspecialchars($r['nama_buku']) ?></div>
        <div style="font-size:.73rem;color:var(--muted);margin-top:3px;display:flex;gap:12px;flex-wrap:wrap;">
            <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($r['nama_user']) ?></span>
            <span><i class="bi bi-calendar3 me-1"></i>Dipinjam: <?= date('d M Y',strtotime($r['tanggal_peminjaman'])) ?></span>
            <span><i class="bi bi-calendar-x me-1"></i>Batas: <?= date('d M Y',strtotime($r['batas_kembali'])) ?></span>
            <span><i class="bi bi-shield-check me-1"></i>Kondisi laporan: <?= $r['kondisi_buku'] ?></span>
        </div>
        <?php if($r['catatan']): ?><div style="font-size:.73rem;color:var(--muted);margin-top:3px;font-style:italic;">"<?= htmlspecialchars($r['catatan']) ?>"</div><?php endif; ?>
        <?php if($tl): ?><div style="font-size:.75rem;color:#ff9090;margin-top:4px;"><i class="bi bi-exclamation-triangle me-1"></i>Terlambat <?= abs($sisa) ?> hari · Est. denda: <?= rupiah($denda_est) ?></div><?php endif; ?>
    </div>
    <?php if($canEdit): ?>
    <div style="display:flex;gap:8px;flex-direction:column;flex-shrink:0;">
        <button class="btn-setujui" onclick="bukaKonfirmasi(<?= $r['id_req_kembali'] ?>,<?= json_encode($r['nama_buku']) ?>,'<?= $r['batas_kembali'] ?>',<?= $tl?abs($sisa):0 ?>,'<?= htmlspecialchars($r['kondisi_buku']) ?>')"><i class="bi bi-check-lg me-1"></i>Konfirmasi</button>
        <a href="?tolak=<?= $r['id_req_kembali'] ?>&tab=request" class="btn-tolak" onclick="return confirm('Tolak pengajuan ini?')"><i class="bi bi-x-lg me-1"></i>Tolak</a>
    </div>
    <?php else: ?><span class="badge-m">Menunggu</span><?php endif; ?>
</div>
<?php endwhile;
else: ?><div class="empty-state"><i class="bi bi-inbox"></i><p>Tidak ada pengajuan pengembalian yang menunggu.</p></div><?php endif; ?>

<?php else: // histori ?>
<?php while($r=$histori->fetch_assoc()): ?>
<div class="req-row">
    <div style="flex:1;min-width:0;">
        <div style="font-weight:600;color:#fff;font-size:.88rem;"><?= htmlspecialchars($r['nama_buku']) ?></div>
        <div style="font-size:.73rem;color:var(--muted);margin-top:3px;display:flex;gap:12px;flex-wrap:wrap;">
            <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($r['nama_user']) ?></span>
            <span><i class="bi bi-calendar-check me-1"></i><?= date('d M Y',strtotime($r['tanggal_pengembalian'])) ?></span>
            <span><i class="bi bi-shield-check me-1"></i><?= $r['kondisi_buku'] ?></span>
            <?php if($r['catatan_petugas']): ?><span><i class="bi bi-chat-text me-1"></i><?= htmlspecialchars($r['catatan_petugas']) ?></span><?php endif; ?>
        </div>
    </div>
    <div style="text-align:right;flex-shrink:0;">
        <?php if($r['denda']>0): ?><div style="font-weight:700;color:#ff9090;font-size:.9rem;"><?= rupiah($r['denda']) ?></div><div style="font-size:.68rem;color:var(--muted);">denda</div>
        <?php else: ?><span class="badge-ok">Tanpa Denda</span><?php endif; ?>
    </div>
</div>
<?php endwhile; ?>
<?php endif; ?>

</div>

<!-- Modal Konfirmasi Pengembalian -->
<div class="modal-bg" id="modalKonfirmasi">
    <div class="modal-box">
        <h4 style="font-family:'Playfair Display',serif;color:#fff;margin-bottom:6px;"><i class="bi bi-arrow-return-left me-2" style="color:var(--accent)"></i>Konfirmasi Pengembalian</h4>
        <p id="mkJudul" style="color:var(--accent);font-size:.88rem;font-weight:600;margin-bottom:16px;"></p>
        <form method="POST">
            <input type="hidden" name="aksi" value="setujui">
            <input type="hidden" name="id_req" id="mkReq">
            <input type="hidden" name="tab" value="request">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label class="fl">Tanggal Kembali Aktual</label>
                    <input type="date" name="tanggal_pengembalian" id="mkTgl" class="fi" value="<?= date('Y-m-d') ?>" onchange="hitungDenda()">
                </div>
                <div>
                    <label class="fl">Kondisi Fisik Buku</label>
                    <select name="kondisi_buku" id="mkKondisi" class="fi fi-select" onchange="hitungDenda()">
                        <option value="Baik">Baik</option>
                        <option value="Rusak Ringan">Rusak Ringan (+Rp 10.000)</option>
                        <option value="Rusak Berat">Rusak Berat (+Rp 50.000)</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom:12px;">
                <label class="fl">Catatan Petugas</label>
                <textarea name="catatan_petugas" class="fi" style="resize:vertical;min-height:55px;" placeholder="Catatan kondisi buku..."></textarea>
            </div>
            <div class="denda-box">
                <div style="font-size:.76rem;font-weight:600;color:#ff9090;margin-bottom:8px;"><i class="bi bi-cash-coin me-1"></i>Rincian Denda</div>
                <div class="dr"><span>Denda keterlambatan</span><span id="dHari">Rp 0</span></div>
                <div class="dr"><span>Denda kerusakan</span><span id="dRusak">Rp 0</span></div>
                <div class="dtotal"><span>Total Denda</span><span id="dTotal">Rp 0</span></div>
            </div>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" onclick="tutupModal()" style="flex:1;padding:10px;border-radius:9px;background:none;border:1px solid var(--border);color:var(--muted);cursor:pointer;">Batal</button>
                <button type="submit" style="flex:2;padding:10px;border-radius:9px;background:linear-gradient(135deg,var(--accent),#d4a54e);border:none;font-weight:600;color:#0c1520;cursor:pointer;"><i class="bi bi-check-lg me-1"></i>Konfirmasi Pengembalian</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const DENDA=<?= DENDA_PER_HARI ?>;
let _batas='';
function rupiah(n){return 'Rp '+Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.');}
function bukaKonfirmasi(rid,judul,batas,hariTl,kondisi){
    document.getElementById('mkReq').value=rid;
    document.getElementById('mkJudul').textContent=judul;
    _batas=batas;
    document.getElementById('mkKondisi').value=kondisi||'Baik';
    hitungDenda();
    document.getElementById('modalKonfirmasi').classList.add('show');
}
function tutupModal(){document.getElementById('modalKonfirmasi').classList.remove('show');}
function hitungDenda(){
    const tgl=document.getElementById('mkTgl').value;
    const k=document.getElementById('mkKondisi').value;
    let dh=0;
    if(_batas&&tgl){const s=Math.ceil((new Date(tgl)-new Date(_batas))/(1000*60*60*24));if(s>0)dh=s*DENDA;}
    const dr=k==='Rusak Berat'?50000:k==='Rusak Ringan'?10000:0;
    document.getElementById('dHari').textContent=rupiah(dh);
    document.getElementById('dRusak').textContent=rupiah(dr);
    document.getElementById('dTotal').textContent=rupiah(dh+dr);
}
document.getElementById('modalKonfirmasi').addEventListener('click',e=>{if(e.target===document.getElementById('modalKonfirmasi'))tutupModal();});
// Cover fetch
(function(){
function fc(div){var t=div.dataset.bookTitle;if(!t)return;var ph=div.querySelector('.logo-ph');if(!ph)return;fetch('https://www.googleapis.com/books/v1/volumes?q='+encodeURIComponent(t)+'&maxResults=1').then(r=>r.json()).then(d=>{var th=d.items?.[0]?.volumeInfo?.imageLinks?.thumbnail;if(th){var img=document.createElement('img');img.src=th.replace('http:','https:');img.className='cover-img';img.style.cssText='width:100%;height:100%;object-fit:cover;position:absolute;inset:0;';img.onerror=()=>img.remove();ph.replaceWith(img);}}).catch(()=>{});}
document.querySelectorAll('[data-book-title]').forEach((d,i)=>setTimeout(()=>fc(d),i*150));
})();
</script>
</body></html>
