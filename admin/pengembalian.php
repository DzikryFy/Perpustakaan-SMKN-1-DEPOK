<?php
session_start();
require_once '../config/database.php';
requireStaff();
$base='../'; $loggedIn=true; $namaUser=$_SESSION['nama']??''; $roleUser=$_SESSION['role']??'';
$msg=$error=''; $canEdit=isPustakawan();
$DENDA=DENDA_PER_HARI;

// ── PROSES PENGEMBALIAN ───────────────────────────────────────
if($canEdit && $_SERVER['REQUEST_METHOD']==='POST' && ($_POST['aksi']??'')==='kembalikan'){
    $id_pinjam = (int)$_POST['id_pinjam'];
    $tgl_kembali = sanitize($_POST['tanggal_pengembalian']??date('Y-m-d'));
    $kondisi = sanitize($_POST['kondisi_buku']??'Baik');
    $catatan = sanitize($_POST['catatan_petugas']??'');
    $denda_tambahan = (int)($_POST['denda_tambahan']??0); // denda kerusakan manual

    $pinjam = $conn->query("SELECT p.*,u.nama AS nama_user FROM peminjaman p JOIN users u ON p.id_user=u.id_user WHERE p.id_pinjam=$id_pinjam AND p.status='Tidak Ada'")->fetch_assoc();
    if(!$pinjam){ $error='Data peminjaman tidak ditemukan.'; }
    else {
        $denda_hari = hitungDenda($pinjam['batas_kembali'], $tgl_kembali);
        $denda_kerusakan = $kondisi==='Rusak Berat' ? 50000 : ($kondisi==='Rusak Ringan' ? 10000 : 0);
        $total_denda = $denda_hari + $denda_kerusakan + $denda_tambahan;
        $nb = $conn->real_escape_string($pinjam['nama_buku']);

        $s=$conn->prepare("INSERT INTO pengembalian (id_pinjam,id_buku,id_user,tanggal_pengembalian,nama_buku,denda,kondisi_buku,catatan_petugas,status) VALUES (?,?,?,?,?,?,?,?,'Ada')");
        $s->bind_param("iiissis s",$id_pinjam,$pinjam['id_buku'],$pinjam['id_user'],$tgl_kembali,$nb,$total_denda,$kondisi,$catatan);
        if($s->execute()){
            $conn->query("UPDATE peminjaman SET status='Ada' WHERE id_pinjam=$id_pinjam");
            $conn->query("UPDATE buku SET stock=stock+1 WHERE id_buku={$pinjam['id_buku']}");
            $msg='Pengembalian berhasil dicatat. Denda: '.rupiah($total_denda).($total_denda>0?' — harap dibayar ke petugas.':'');
        } else { $error='Gagal menyimpan pengembalian.'; }
        $s->close();
    }
}

// ── PROSES FORM LANGSUNG (dari GET proses) ────────────────────
$prosesData = null;
if(isset($_GET['proses'])){
    $pid = (int)$_GET['proses'];
    $prosesData = $conn->query("SELECT p.*,b.gambar,b.kategori,u.nama AS nama_user FROM peminjaman p JOIN buku b ON p.id_buku=b.id_buku JOIN users u ON p.id_user=u.id_user WHERE p.id_pinjam=$pid AND p.status='Tidak Ada'")->fetch_assoc();
}

// ── DATA ──────────────────────────────────────────────────────
$tab = $_GET['tab']??'aktif';
$aktif = $conn->query("SELECT p.*,b.gambar,b.kategori,u.nama AS nama_user FROM peminjaman p JOIN buku b ON p.id_buku=b.id_buku JOIN users u ON p.id_user=u.id_user WHERE p.status='Tidak Ada' ORDER BY p.batas_kembali ASC");
$histori = $conn->query("SELECT pg.*,u.nama AS nama_user FROM pengembalian pg JOIN users u ON pg.id_user=u.id_user ORDER BY pg.id_pengembalian DESC LIMIT 80");
$total_denda_bulan = $conn->query("SELECT SUM(denda) s FROM pengembalian WHERE MONTH(tanggal_pengembalian)=MONTH(NOW()) AND YEAR(tanggal_pengembalian)=YEAR(NOW())")->fetch_assoc()['s'] ?? 0;
?><!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><title>Pengembalian Buku | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="../style.css">
<style>
body{display:flex;min-height:100vh;}
.admin-wrap{display:flex;width:100%;min-height:100vh;}
.main-content{flex:1;padding:32px;overflow-x:hidden;}
.page-title{font-family:'Playfair Display',serif;font-size:1.7rem;color:#fff;margin-bottom:4px;}
.stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:26px;}
.stat-box{background:var(--card);border:1px solid var(--border);border-radius:13px;padding:16px;display:flex;align-items:center;gap:12px;}
.stat-ico{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.05rem;flex-shrink:0;}
.ico-gold{background:rgba(232,184,109,.12);color:var(--accent);}
.ico-red{background:rgba(232,93,93,.12);color:#ff9090;}
.ico-green{background:rgba(58,171,110,.12);color:var(--green);}
.stat-num{font-size:1.6rem;font-weight:700;color:#fff;line-height:1;}
.stat-lbl{font-size:.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;}
.atab{display:flex;gap:10px;margin-bottom:22px;flex-wrap:wrap;}
.atab a{padding:8px 18px;border-radius:20px;font-size:.82rem;border:1px solid var(--border);color:var(--muted);text-decoration:none;transition:all .2s;}
.atab a.active,.atab a:hover{background:var(--accent);border-color:var(--accent);color:#0c1520;font-weight:600;}
.pinjam-row{background:var(--card);border:1px solid var(--border);border-radius:13px;display:flex;align-items:center;gap:14px;padding:14px 18px;margin-bottom:10px;}
.pinjam-row:hover{border-color:rgba(232,184,109,.2);}
.terlambat-row{border-color:rgba(232,93,93,.35)!important;background:rgba(232,93,93,.04)!important;}
.mini-cover{width:44px;height:56px;border-radius:8px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:20px;}
.mini-cover img{width:100%;height:100%;object-fit:cover;}
.mini-cover.c-umum{background:linear-gradient(135deg,#0d2540,#1a3d60);}
.mini-cover.c-motivasi{background:linear-gradient(135deg,#2d1f00,#5a3c00);}
.mini-cover.c-sastra{background:linear-gradient(135deg,#1f0d2d,#3d1a5a);}
.mini-cover.c-religi{background:linear-gradient(135deg,#0d2d1a,#1a5a3c);}
.mini-cover.c-default{background:linear-gradient(135deg,#1a1f2d,#2d3a4a);}
.badge-tl{background:rgba(232,93,93,.15);color:#ff9090;border:1px solid rgba(232,93,93,.3);border-radius:20px;font-size:.67rem;padding:3px 9px;font-weight:600;}
.badge-ok{background:rgba(58,171,110,.12);color:#5dd896;border:1px solid rgba(58,171,110,.25);border-radius:20px;font-size:.67rem;padding:3px 9px;font-weight:600;}
.btn-kemb{background:linear-gradient(135deg,var(--accent),#d4a54e);border:none;border-radius:8px;padding:7px 15px;font-size:.78rem;font-weight:600;color:#0c1520;cursor:pointer;white-space:nowrap;}
.modal-kemb{display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:9999;align-items:center;justify-content:center;}
.modal-kemb.show{display:flex;}
.modal-box{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:28px;width:500px;max-width:95vw;max-height:90vh;overflow-y:auto;}
.form-label-sm{font-size:.78rem;color:var(--muted);display:block;margin-bottom:5px;}
.form-ctrl{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:var(--text);border-radius:9px;padding:9px 13px;font-family:'DM Sans',sans-serif;font-size:.85rem;}
.form-ctrl:focus{outline:none;border-color:var(--accent);}
.denda-preview{background:rgba(232,93,93,.08);border:1px solid rgba(232,93,93,.2);border-radius:10px;padding:14px;margin-top:16px;}
.denda-row{display:flex;justify-content:space-between;font-size:.82rem;color:var(--muted);padding:4px 0;}
.denda-total{font-size:1.1rem;font-weight:700;color:#ff9090;padding-top:8px;border-top:1px solid rgba(232,93,93,.2);display:flex;justify-content:space-between;}
.alert-ok{background:rgba(58,171,110,.1);border:1px solid rgba(58,171,110,.3);color:#5dd896;border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:.85rem;display:flex;gap:8px;}
.alert-err{background:rgba(232,93,93,.1);border:1px solid rgba(232,93,93,.3);color:#ff9090;border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:.85rem;display:flex;gap:8px;}
</style>
</head><body>
<?php include 'sidebar.php'; ?>
<div class="admin-wrap"><div class="main-content">
<div style="margin-bottom:24px;">
    <div class="sec-label" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.12em;color:var(--accent);font-weight:600;margin-bottom:4px;">Manajemen</div>
    <h1 class="page-title">Pengembalian & Denda</h1>
</div>
<?php if($msg): ?><div class="alert-ok"><i class="bi bi-check-circle-fill"></i><?= $msg ?></div><?php endif; ?>
<?php if($error): ?><div class="alert-err"><i class="bi bi-exclamation-triangle-fill"></i><?= $error ?></div><?php endif; ?>

<div class="stat-row">
    <?php
    $jml_aktif=$aktif->num_rows;
    $jml_tl=$conn->query("SELECT COUNT(*) c FROM peminjaman WHERE status='Tidak Ada' AND batas_kembali < CURDATE()")->fetch_assoc()['c'];
    ?>
    <div class="stat-box"><div class="stat-ico ico-gold"><i class="bi bi-book-half"></i></div><div><div class="stat-num"><?= $jml_aktif ?></div><div class="stat-lbl">Total Pinjaman Aktif</div></div></div>
    <div class="stat-box"><div class="stat-ico ico-red"><i class="bi bi-exclamation-triangle"></i></div><div><div class="stat-num"><?= $jml_tl ?></div><div class="stat-lbl">Terlambat</div></div></div>
    <div class="stat-box"><div class="stat-ico ico-green"><i class="bi bi-cash-coin"></i></div><div><div class="stat-num" style="font-size:1.1rem;"><?= rupiah($total_denda_bulan) ?></div><div class="stat-lbl">Denda Bulan Ini</div></div></div>
</div>

<div class="atab">
    <a href="?tab=aktif"   class="<?= $tab==='aktif'  ?'active':'' ?>">Pinjaman Aktif</a>
    <a href="?tab=histori" class="<?= $tab==='histori'?'active':'' ?>">Histori Pengembalian</a>
</div>

<?php if($tab==='aktif'):
$aktif->data_seek(0);
while($r=$aktif->fetch_assoc()):
    $kk=strtolower($r['kategori']??''); $cmap=['umum'=>['e'=>'📖','c'=>'c-umum'],'motivasi'=>['e'=>'🚀','c'=>'c-motivasi'],'sastra'=>['e'=>'✍️','c'=>'c-sastra'],'religi'=>['e'=>'🕌','c'=>'c-religi']];
    $cm=$cmap[$kk]??['e'=>'📚','c'=>'c-default'];
    $tl=strtotime(date('Y-m-d'))>strtotime($r['batas_kembali']);
    $sisa=ceil((strtotime($r['batas_kembali'])-strtotime(date('Y-m-d')))/86400);
    $denda_pred=$tl?abs($sisa)*$DENDA:0;
?>
<div class="pinjam-row <?= $tl?'terlambat-row':'' ?>">
    <div class="mini-cover <?= $cm['c'] ?>"><?php if(!empty($r['gambar'])&&file_exists('../img/buku/'.$r['gambar'])): ?><img src="../img/buku/<?= htmlspecialchars($r['gambar']) ?>"><?php else: ?><?= $cm['e'] ?><?php endif; ?></div>
    <div style="flex:1;min-width:0;">
        <div style="font-weight:600;color:#fff;font-size:.9rem;"><?= htmlspecialchars($r['nama_buku']) ?></div>
        <div style="font-size:.73rem;color:var(--muted);margin-top:3px;display:flex;gap:12px;flex-wrap:wrap;">
            <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($r['nama_user']) ?></span>
            <span><i class="bi bi-calendar3 me-1"></i>Pinjam: <?= date('d M Y',strtotime($r['tanggal_peminjaman'])) ?></span>
            <span><i class="bi bi-calendar-x me-1"></i>Batas: <?= date('d M Y',strtotime($r['batas_kembali'])) ?></span>
            <?php if($tl): ?><span style="color:#ff9090;"><i class="bi bi-exclamation-triangle me-1"></i>Terlambat <?= abs($sisa) ?> hari · Denda: <?= rupiah($denda_pred) ?></span><?php endif; ?>
        </div>
    </div>
    <div style="flex-shrink:0;display:flex;align-items:center;gap:8px;">
        <?= $tl?'<span class="badge-tl">Terlambat</span>':'<span class="badge-ok">Aktif</span>' ?>
        <?php if($canEdit): ?><button class="btn-kemb" onclick="bukaKemb(<?= $r['id_pinjam'] ?>,<?= json_encode($r['nama_buku']) ?>,<?= json_encode($r['nama_user']) ?>,'<?= $r['batas_kembali'] ?>',<?= $tl?abs($sisa):0 ?>)"><i class="bi bi-arrow-return-left me-1"></i>Kembalikan</button><?php endif; ?>
    </div>
</div>
<?php endwhile; ?>

<?php else: // histori ?>
<?php while($r=$histori->fetch_assoc()): ?>
<div class="pinjam-row">
    <div style="flex:1;min-width:0;">
        <div style="font-weight:600;color:#fff;font-size:.9rem;"><?= htmlspecialchars($r['nama_buku']) ?></div>
        <div style="font-size:.73rem;color:var(--muted);margin-top:3px;display:flex;gap:12px;flex-wrap:wrap;">
            <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($r['nama_user']) ?></span>
            <span><i class="bi bi-calendar-check me-1"></i>Dikembalikan: <?= date('d M Y',strtotime($r['tanggal_pengembalian'])) ?></span>
            <span><i class="bi bi-shield-check me-1"></i><?= $r['kondisi_buku'] ?></span>
            <?php if($r['catatan_petugas']): ?><span><i class="bi bi-chat-text me-1"></i><?= htmlspecialchars($r['catatan_petugas']) ?></span><?php endif; ?>
        </div>
    </div>
    <div style="flex-shrink:0;text-align:right;">
        <?php if($r['denda']>0): ?><div style="font-weight:700;color:#ff9090;font-size:.9rem;"><?= rupiah($r['denda']) ?></div><div style="font-size:.68rem;color:var(--muted);">denda</div><?php else: ?><span class="badge-ok">Tanpa Denda</span><?php endif; ?>
    </div>
</div>
<?php endwhile; ?>
<?php endif; ?>

</div></div>

<!-- Modal Kembalikan -->
<div class="modal-kemb" id="modalKemb">
<div class="modal-box">
    <h4 style="font-family:'Playfair Display',serif;color:#fff;margin-bottom:6px;"><i class="bi bi-arrow-return-left me-2"></i>Proses Pengembalian</h4>
    <p id="mkJudul" style="color:var(--accent);font-size:.88rem;margin-bottom:16px;font-weight:600;"></p>
    <form method="POST" id="formKemb">
        <input type="hidden" name="aksi" value="kembalikan">
        <input type="hidden" name="id_pinjam" id="mkPinjam">
        <input type="hidden" name="tab" value="<?= $tab ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
            <div>
                <label class="form-label-sm">Nama Peminjam</label>
                <input type="text" id="mkUser" class="form-ctrl" readonly>
            </div>
            <div>
                <label class="form-label-sm">Tanggal Kembali</label>
                <input type="date" name="tanggal_pengembalian" id="mkTgl" class="form-ctrl" value="<?= date('Y-m-d') ?>" onchange="hitungDenda()">
            </div>
        </div>
        <div style="margin-bottom:12px;">
            <label class="form-label-sm">Kondisi Buku</label>
            <select name="kondisi_buku" id="mkKondisi" class="form-ctrl" onchange="hitungDenda()">
                <option value="Baik">Baik</option>
                <option value="Rusak Ringan">Rusak Ringan (+Rp 10.000)</option>
                <option value="Rusak Berat">Rusak Berat (+Rp 50.000)</option>
            </select>
        </div>
        <div style="margin-bottom:12px;">
            <label class="form-label-sm">Catatan Petugas</label>
            <textarea name="catatan_petugas" class="form-ctrl" style="resize:vertical;min-height:55px;" placeholder="opsional..."></textarea>
        </div>
        <!-- Preview Denda -->
        <div class="denda-preview">
            <div style="font-size:.78rem;font-weight:600;color:#ff9090;margin-bottom:8px;"><i class="bi bi-cash-coin me-1"></i>Rincian Denda</div>
            <div class="denda-row"><span>Denda keterlambatan</span><span id="dHari">Rp 0</span></div>
            <div class="denda-row"><span>Denda kerusakan</span><span id="dRusak">Rp 0</span></div>
            <div class="denda-total"><span>Total Denda</span><span id="dTotal">Rp 0</span></div>
        </div>
        <input type="hidden" name="denda_tambahan" value="0">
        <div style="display:flex;gap:10px;margin-top:18px;">
            <button type="button" onclick="tutupKemb()" style="flex:1;padding:10px;border-radius:9px;background:none;border:1px solid var(--border);color:var(--muted);cursor:pointer;">Batal</button>
            <button type="submit" style="flex:2;padding:10px;border-radius:9px;background:linear-gradient(135deg,var(--accent),#d4a54e);border:none;font-weight:600;color:#0c1520;cursor:pointer;"><i class="bi bi-check-lg me-1"></i>Konfirmasi Pengembalian</button>
        </div>
    </form>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const DENDA_HARI=<?= $DENDA ?>;
let _batasKembali='';
function rupiah(n){return 'Rp '+n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.');}
function bukaKemb(id,judul,user,batas,hariTl){
    document.getElementById('mkPinjam').value=id;
    document.getElementById('mkJudul').textContent=judul;
    document.getElementById('mkUser').value=user;
    _batasKembali=batas;
    hitungDenda();
    document.getElementById('modalKemb').classList.add('show');
}
function tutupKemb(){document.getElementById('modalKemb').classList.remove('show');}
function hitungDenda(){
    const tgl=document.getElementById('mkTgl').value;
    const kondisi=document.getElementById('mkKondisi').value;
    let dHari=0;
    if(_batasKembali&&tgl){
        const selisih=Math.ceil((new Date(tgl)-new Date(_batasKembali))/(1000*60*60*24));
        if(selisih>0) dHari=selisih*DENDA_HARI;
    }
    const dRusak=kondisi==='Rusak Berat'?50000:kondisi==='Rusak Ringan'?10000:0;
    const total=dHari+dRusak;
    document.getElementById('dHari').textContent=rupiah(dHari);
    document.getElementById('dRusak').textContent=rupiah(dRusak);
    document.getElementById('dTotal').textContent=rupiah(total);
}
document.getElementById('modalKemb').addEventListener('click',function(e){if(e.target===this)tutupKemb();});
</script>
</body></html>
