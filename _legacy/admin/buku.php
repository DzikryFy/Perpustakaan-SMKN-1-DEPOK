<?php
session_start();
require_once '../config/database.php';
requirePustakawan();
$base='../'; $loggedIn=true; $namaUser=$_SESSION['nama']??''; $roleUser=$_SESSION['role']??'';
$msg=$error='';
$act=$_GET['act']??'list';
$id=(int)($_GET['id']??0);

function uploadGambar():?string{
    if(empty($_FILES['gambar']['name'])) return null;
    $f=$_FILES['gambar']; $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
    if(!in_array($ext,['jpg','jpeg','png','webp'])||$f['size']>2*1024*1024) return null;
    $fname='buku_'.time().'_'.rand(100,999).'.'.$ext;
    return move_uploaded_file($f['tmp_name'],'../img/buku/'.$fname)?$fname:null;
}

// HAPUS
if($act==='hapus'&&$id){
    $bk=$conn->query("SELECT gambar FROM buku WHERE id_buku=$id")->fetch_assoc();
    if(!empty($bk['gambar'])&&file_exists('../img/buku/'.$bk['gambar'])) unlink('../img/buku/'.$bk['gambar']);
    $conn->query("DELETE FROM buku WHERE id_buku=$id");
    $msg='Buku berhasil dihapus.'; $act='list';
}

// SIMPAN
if($_SERVER['REQUEST_METHOD']==='POST'){
    $nama_buku     =sanitize($_POST['nama_buku']??'');
    $kategori      =sanitize($_POST['kategori']??'');
    $stock         =(int)($_POST['stock']??0);
    $no_bos        =sanitize($_POST['no_bos']??'');
    $penerbit      =sanitize($_POST['penerbit']??'');
    $jenis_barang  =sanitize($_POST['jenis_barang']??'');
    $tahun_cetak   =(int)($_POST['tahun_cetak']??0)?:null;
    $asal          =sanitize($_POST['asal_perolehan']??'BOS');
    $eid           =(int)($_POST['eid']??0);
    $hapus_gambar  =isset($_POST['hapus_gambar']);

    if(empty($nama_buku)||empty($kategori)){
        $error='Nama buku dan kategori wajib diisi.'; $act=$eid?'edit':'tambah'; $id=$eid;
    } elseif($eid){
        $old=$conn->query("SELECT gambar FROM buku WHERE id_buku=$eid")->fetch_assoc();
        $newG=uploadGambar();
        if($hapus_gambar&&!$newG){
            if(!empty($old['gambar'])&&file_exists('../img/buku/'.$old['gambar'])) unlink('../img/buku/'.$old['gambar']);
            $newG='';
        } elseif(!$newG){ $newG=$old['gambar']??''; }
        else { if(!empty($old['gambar'])&&file_exists('../img/buku/'.$old['gambar'])) unlink('../img/buku/'.$old['gambar']); }
        $gSQL=$conn->real_escape_string($newG??'');
        $s=$conn->prepare("UPDATE buku SET nama_buku=?,kategori=?,stock=?,no_bos=?,penerbit=?,jenis_barang=?,tahun_cetak=?,asal_perolehan=?,gambar=? WHERE id_buku=?");
        $s->bind_param("ssisiisssi",$nama_buku,$kategori,$stock,$no_bos,$penerbit,$jenis_barang,$tahun_cetak,$asal,$gSQL,$eid);
        if($s->execute()){
            $msg='Buku diperbarui.';
            $act='list';
        } else {
            $error='Gagal.';
            $act='edit';
            $id=$eid;
        }
        $s->close();
    } else {
        $newG=uploadGambar()??'';
        $s=$conn->prepare("INSERT INTO buku (nama_buku,kategori,stock,no_bos,penerbit,jenis_barang,tahun_cetak,asal_perolehan,gambar) VALUES (?,?,?,?,?,?,?,?,?)");
        $s->bind_param("ssisssiss",$nama_buku,$kategori,$stock,$no_bos,$penerbit,$jenis_barang,$tahun_cetak,$asal,$newG);
        if($s->execute()){
            $msg='Buku ditambahkan.';
            $act='list';
        } else {
            $error='Gagal.';
            $act='tambah';
        }
        $s->close();
    }
}

$q=sanitize($_GET['q']??'');
$where=$q?"WHERE nama_buku LIKE '%$q%' OR kategori LIKE '%$q%' OR no_bos LIKE '%$q%'":"";
$list=$conn->query("SELECT * FROM buku $where ORDER BY id_buku DESC");
$ed=($act==='edit'&&$id)?$conn->query("SELECT * FROM buku WHERE id_buku=$id")->fetch_assoc():[];
?><!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Data Buku | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<?php include 'sidebar.php'; ?>
<style>
.main-content{margin-left:230px;padding:30px;min-height:100vh;overflow-x:hidden;}
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;}
.topbar h1{font-family:'Playfair Display',serif;font-size:1.7rem;color:#fff;margin:0;}
.topbar .bc{font-size:.78rem;color:var(--muted);margin-top:3px;}
/* Form layout - two column */
.form-wrap{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-wrap .full{grid-column:1/-1;}
.fi{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:var(--text);border-radius:9px;padding:10px 14px;font-size:.87rem;font-family:'DM Sans',sans-serif;transition:border .2s;}
.fi:focus{outline:none;border-color:var(--accent);background:rgba(255,255,255,.07);}
.fi::placeholder{color:rgba(255,255,255,.2);}
.fi-select option{background:#152135;}
.fl{font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);display:block;margin-bottom:5px;font-weight:500;}
.cover-preview{width:80px;height:100px;border-radius:8px;object-fit:cover;border:1px solid var(--border);margin-bottom:8px;}
.file-input{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);color:var(--text);border-radius:9px;padding:8px;font-size:.83rem;}
.form-actions{display:flex;gap:10px;margin-top:6px;padding-top:16px;border-top:1px solid var(--border);}
@media(max-width:900px){.form-wrap{grid-template-columns:1fr;}.main-content{margin-left:0;}}
</style>
</head><body>
<div class="main-content">
<div class="topbar">
    <div>
        <h1><?= $act==='list'?'Data Buku':($act==='tambah'?'Tambah Buku':'Edit Buku') ?></h1>
        <div class="bc">Dashboard / <?= $act==='list'?'Buku':($act==='tambah'?'Tambah':'Edit') ?></div>
    </div>
    <?php if($act==='list'): ?>
    <a href="?act=tambah" class="btn-gold"><i class="bi bi-plus-lg me-1"></i>Tambah Buku</a>
    <?php else: ?>
    <a href="buku.php" class="btn-out"><i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar</a>
    <?php endif; ?>
</div>

<?php if($msg): ?><div class="msg-ok"><i class="bi bi-check-circle me-2"></i><?= $msg ?></div><?php endif; ?>
<?php if($error): ?><div class="msg-err"><i class="bi bi-exclamation-circle me-2"></i><?= $error ?></div><?php endif; ?>

<?php if($act==='list'): ?>
<!-- LIST -->
<div class="panel">
    <div class="ph">
        <h3><i class="bi bi-book me-2" style="color:var(--accent)"></i>Koleksi Buku (<?= $list->num_rows ?>)</h3>
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <input type="text" name="q" class="srch" placeholder="Cari judul, kategori, no BOS..." value="<?= htmlspecialchars($q) ?>" style="width:220px;">
            <button type="submit" class="btn-gold" style="padding:8px 14px;"><i class="bi bi-search"></i></button>
            <?php if($q): ?><a href="buku.php" class="btn-out" style="padding:8px 14px;">Reset</a><?php endif; ?>
        </form>
    </div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><th>#</th><th>Cover</th><th>No.BOS</th><th>Nama Buku</th><th>Penerbit</th><th>Kategori</th><th>Stok</th><th>Tahun</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php $no=1; while($b=$list->fetch_assoc()): ?>
        <tr>
            <td style="color:var(--muted);font-size:.82rem;"><?= $no++ ?></td>
            <td>
                <?php
                    $imgName = basename($b['gambar'] ?? '');
                    if ($imgName && file_exists('../img/buku/'.$imgName)):
                ?>
                <img src="../img/buku/<?= htmlspecialchars($imgName) ?>" style="width:36px;height:46px;object-fit:cover;border-radius:6px;border:1px solid var(--border);">
                <?php else: ?>
                <div data-book-title="<?= htmlspecialchars($b['nama_buku']) ?>" style="width:36px;height:46px;border-radius:6px;overflow:hidden;background:linear-gradient(135deg,#1a1f2d,#2d3a4a);display:flex;align-items:center;justify-content:center;position:relative;">
                    <img src="../img/logo-wandek.png" class="logo-ph" style="width:22px;opacity:.6;">
                </div>
                <?php endif; ?>
            </td>
            <td style="font-size:.77rem;color:var(--accent);"><?= htmlspecialchars($b['no_bos']??'-') ?></td>
            <td style="font-weight:600;color:#fff;max-width:200px;"><div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($b['nama_buku']) ?></div><?php if($b['penerbit']): ?><div style="font-size:.71rem;color:var(--muted);"><?= htmlspecialchars($b['penerbit']) ?></div><?php endif; ?></td>
            <td style="font-size:.8rem;color:var(--muted);"><?= htmlspecialchars($b['penerbit']??'-') ?></td>
            <td><span style="padding:3px 10px;border-radius:20px;font-size:.68rem;font-weight:600;background:rgba(232,184,109,.1);color:var(--accent);"><?= htmlspecialchars($b['kategori']) ?></span></td>
            <td><span style="font-weight:700;color:<?= $b['stock']>0?'var(--green)':'var(--red)' ?>;"><?= $b['stock'] ?></span></td>
            <td style="font-size:.8rem;color:var(--muted);"><?= $b['tahun_cetak']??'-' ?></td>
            <td>
                <div class="abtn-wrap">
                    <a href="?act=edit&id=<?= $b['id_buku'] ?>" class="abtn abtn-edit"><i class="bi bi-pencil"></i></a>
                    <a href="?act=hapus&id=<?= $b['id_buku'] ?>" class="abtn abtn-del" onclick="return confirm('Hapus buku ini?')"><i class="bi bi-trash"></i></a>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php if($list->num_rows===0): ?><tr><td colspan="9" style="text-align:center;color:var(--muted);padding:36px;">Tidak ada buku ditemukan.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php else: ?>
<!-- FORM TAMBAH / EDIT -->
<div class="panel" style="max-width:860px;">
    <div class="ph"><h3><i class="bi bi-<?= $act==='tambah'?'plus-circle':'pencil-square' ?> me-2" style="color:var(--accent)"></i><?= $act==='tambah'?'Tambah Buku Baru':'Edit Data Buku' ?></h3></div>
    <div class="pb">
        <form method="POST" action="buku.php" enctype="multipart/form-data">
            <input type="hidden" name="eid" value="<?= $eid['id_buku']??$id ?>">
            <div class="form-wrap">
                <!-- Nama Buku -->
                <div class="full">
                    <label class="fl">Judul / Nama Buku *</label>
                    <input type="text" name="nama_buku" class="fi" placeholder="Masukkan judul buku lengkap" required
                           value="<?= htmlspecialchars($ed['nama_buku']??$_POST['nama_buku']??'') ?>">
                </div>
                <!-- Kategori + Stock -->
                <div>
                    <label class="fl">Kategori *</label>
                    <select name="kategori" class="fi fi-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach(['Umum','Motivasi','Sastra','Religi'] as $k):
                            $sel=($ed['kategori']??$_POST['kategori']??'')===$k?'selected':''; ?>
                        <option value="<?= $k ?>" <?= $sel ?>><?= $k ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="fl">Stok / Jumlah Eksemplar</label>
                    <input type="number" name="stock" class="fi" min="0" placeholder="0"
                           value="<?= htmlspecialchars($ed['stock']??$_POST['stock']??0) ?>">
                </div>
                <!-- No BOS + Penerbit -->
                <div>
                    <label class="fl">No. BOS</label>
                    <input type="text" name="no_bos" class="fi" placeholder="mis. 5439"
                           value="<?= htmlspecialchars($ed['no_bos']??$_POST['no_bos']??'') ?>">
                </div>
                <div>
                    <label class="fl">Penerbit</label>
                    <input type="text" name="penerbit" class="fi" placeholder="mis. Erlangga, Mediatama"
                           value="<?= htmlspecialchars($ed['penerbit']??$_POST['penerbit']??'') ?>">
                </div>
                <!-- Tahun + Asal -->
                <div>
                    <label class="fl">Tahun Cetak</label>
                    <input type="number" name="tahun_cetak" class="fi" placeholder="2025" min="1900" max="2100"
                           value="<?= htmlspecialchars($ed['tahun_cetak']??$_POST['tahun_cetak']??'') ?>">
                </div>
                <div>
                    <label class="fl">Asal Perolehan</label>
                    <select name="asal_perolehan" class="fi fi-select">
                        <?php foreach(['BOS','BOS Kin','Donasi','Pembelian Mandiri','Koleksi Awal'] as $ap):
                            $sel2=($ed['asal_perolehan']??$_POST['asal_perolehan']??'BOS')===$ap?'selected':''; ?>
                        <option value="<?= $ap ?>" <?= $sel2 ?>><?= $ap ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Jenis Barang -->
                <div class="full">
                    <label class="fl">Jenis Barang</label>
                    <input type="text" name="jenis_barang" class="fi" placeholder="mis. Lain-Lain Bahan Perpustakaan Tercetak Lainnya"
                           value="<?= htmlspecialchars($ed['jenis_barang']??$_POST['jenis_barang']??'') ?>">
                </div>
                <!-- Gambar -->
                <div class="full">
                    <label class="fl">Cover Buku <span style="color:var(--muted);font-weight:400;">(JPG/PNG/WebP, maks 2MB — kosongkan untuk auto-fetch dari Google Books)</span></label>
                    <?php if(!empty($ed['gambar'])&&file_exists('../img/buku/'.$ed['gambar'])): ?>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                        <img src="../img/buku/<?= htmlspecialchars($ed['gambar']) ?>" class="cover-preview">
                        <label style="display:flex;align-items:center;gap:6px;font-size:.8rem;color:var(--muted);cursor:pointer;">
                            <input type="checkbox" name="hapus_gambar" value="1"> Hapus gambar ini
                        </label>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="gambar" accept="image/jpeg,image/png,image/webp" class="file-input">
                </div>
            </div>
            <!-- Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-gold"><i class="bi bi-check-lg me-1"></i><?= $act==='tambah'?'Simpan Buku':'Perbarui Buku' ?></button>
                <a href="buku.php" class="btn-out">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
function fetchCover(div){var t=div.dataset.bookTitle;if(!t)return;var ph=div.querySelector('.logo-ph');if(!ph)return;fetch('https://www.googleapis.com/books/v1/volumes?q='+encodeURIComponent(t)+'&maxResults=1').then(r=>r.json()).then(d=>{var thumb=d.items?.[0]?.volumeInfo?.imageLinks?.thumbnail;if(thumb){var img=document.createElement('img');img.src=thumb.replace('http:','https:');img.style.cssText='width:100%;height:100%;object-fit:cover;position:absolute;inset:0;';img.onerror=()=>img.remove();ph.replaceWith(img);}}).catch(()=>{});}
document.querySelectorAll('[data-book-title]').forEach((d,i)=>setTimeout(()=>fetchCover(d),i*120));
})();
</script>
</body></html>
