<?php
session_start();
require_once 'config/database.php';
requireLogin('login.php?redirect=saran-buku.php');
if(isStaff()) redirect('admin/saran-buku.php');

$loggedIn=true; $namaUser=$_SESSION['nama']??''; $roleUser=$_SESSION['role']??'';
$id_user=(int)$_SESSION['id_user']; $base=''; $msg=$error='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $judul   = sanitize($_POST['judul_saran']??'');
    $penulis = sanitize($_POST['penulis_saran']??'');
    $penerbit= sanitize($_POST['penerbit_saran']??'');
    $alasan  = sanitize($_POST['alasan']??'');
    $tgl     = date('Y-m-d');
    if(empty($judul)){ $error='Judul buku wajib diisi.'; }
    else {
        $s=$conn->prepare("INSERT INTO saran_buku (id_user,judul_saran,penulis_saran,penerbit_saran,alasan,tanggal_saran,status) VALUES (?,?,?,?,?,'$tgl','baru')");
        $s->bind_param("issss",$id_user,$judul,$penulis,$penerbit,$alasan);
        $s->execute() ? $msg='✅ Saran buku kamu berhasil dikirim! Pustakawan akan meninjaunya.' : $error='Gagal mengirim saran.';
        $s->close();
    }
}

$saran_saya=$conn->query("SELECT * FROM saran_buku WHERE id_user=$id_user ORDER BY id_saran DESC LIMIT 10");
?><!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Saran Buku | E-Library SMKN 1 Depok</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="style.css">
<style>
.page-header{padding:44px 0 30px;border-bottom:1px solid var(--border);}
.page-header h1{font-family:'Playfair Display',serif;font-size:2rem;color:#fff;margin-bottom:4px;}
.page-body{padding:38px 0 80px;}
.form-card{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:30px;}
.form-card h3{font-family:'Playfair Display',serif;color:#fff;font-size:1.3rem;margin-bottom:20px;}
.fl{font-size:.75rem;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);display:block;margin-bottom:6px;}
.fi{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:var(--text);border-radius:9px;padding:10px 14px;font-size:.87rem;font-family:'DM Sans',sans-serif;transition:border .2s;}
.fi:focus{outline:none;border-color:var(--accent);}
.fi::placeholder{color:rgba(255,255,255,.2);}
.btn-kirim{width:100%;padding:13px;background:linear-gradient(135deg,var(--accent),#d4a54e);border:none;border-radius:10px;font-size:.92rem;font-weight:700;color:#0c1520;cursor:pointer;transition:opacity .2s;margin-top:8px;}
.btn-kirim:hover{opacity:.88;}
.alert-ok{background:rgba(58,171,110,.1);border:1px solid rgba(58,171,110,.3);color:#5dd896;border-radius:11px;padding:13px 16px;margin-bottom:22px;font-size:.87rem;display:flex;gap:9px;}
.alert-err{background:rgba(232,93,93,.1);border:1px solid rgba(232,93,93,.3);color:#ff9090;border-radius:11px;padding:13px 16px;margin-bottom:22px;font-size:.87rem;display:flex;gap:9px;}
.saran-item{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:10px;}
.badge-baru{background:rgba(232,184,109,.12);color:var(--accent);border:1px solid rgba(232,184,109,.2);border-radius:12px;font-size:.66rem;padding:3px 9px;font-weight:600;}
.badge-ditinjau{background:rgba(74,158,255,.12);color:var(--blue);border:1px solid rgba(74,158,255,.2);border-radius:12px;font-size:.66rem;padding:3px 9px;font-weight:600;}
.badge-ok{background:rgba(58,171,110,.12);color:var(--green);border:1px solid rgba(58,171,110,.2);border-radius:12px;font-size:.66rem;padding:3px 9px;font-weight:600;}
.badge-no{background:rgba(232,93,93,.1);color:#ff9090;border:1px solid rgba(232,93,93,.2);border-radius:12px;font-size:.66rem;padding:3px 9px;font-weight:600;}
.info-box{background:rgba(232,184,109,.06);border:1px solid rgba(232,184,109,.15);border-radius:12px;padding:14px 18px;margin-bottom:26px;font-size:.82rem;color:var(--muted);display:flex;gap:10px;}
</style>
</head><body>
<?php include 'includes/navbar.php'; ?>
<div class="page-header"><div class="container">
    <div class="sec-label mb-1" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.12em;color:var(--accent);font-weight:600;">Partisipasi Anggota</div>
    <h1>Saran Buku</h1>
    <p style="color:var(--muted);font-size:.87rem;margin:0">Sarankan buku yang perlu ditambahkan ke koleksi perpustakaan.</p>
</div></div>

<div class="container page-body">
<?php if($msg): ?><div class="alert-ok"><i class="bi bi-check-circle-fill"></i><div><?= $msg ?></div></div><?php endif; ?>
<?php if($error): ?><div class="alert-err"><i class="bi bi-exclamation-triangle-fill"></i><div><?= $error ?></div></div><?php endif; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="info-box">
            <i class="bi bi-info-circle-fill" style="color:var(--accent);font-size:1rem;flex-shrink:0;margin-top:1px"></i>
            <div>Saran kamu akan ditinjau oleh <strong style="color:var(--accent)">Kepala Perpustakaan</strong> dan tim. Jika disetujui, buku akan diajukan untuk pengadaan koleksi berikutnya. Semakin banyak detail yang kamu isi, semakin mudah untuk ditindaklanjuti!</div>
        </div>
        <div class="form-card">
            <h3><i class="bi bi-lightbulb me-2" style="color:var(--accent)"></i>Form Saran Buku</h3>
            <form method="POST">
                <div style="margin-bottom:16px;">
                    <label class="fl">Judul Buku <span style="color:#ff9090">*</span></label>
                    <input type="text" name="judul_saran" class="fi" placeholder="mis. Pemrograman Python untuk Pemula" required value="<?= htmlspecialchars($_POST['judul_saran']??'') ?>">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                    <div>
                        <label class="fl">Penulis / Pengarang</label>
                        <input type="text" name="penulis_saran" class="fi" placeholder="opsional" value="<?= htmlspecialchars($_POST['penulis_saran']??'') ?>">
                    </div>
                    <div>
                        <label class="fl">Penerbit</label>
                        <input type="text" name="penerbit_saran" class="fi" placeholder="opsional" value="<?= htmlspecialchars($_POST['penerbit_saran']??'') ?>">
                    </div>
                </div>
                <div style="margin-bottom:20px;">
                    <label class="fl">Alasan / Manfaat Buku Ini</label>
                    <textarea name="alasan" class="fi" style="resize:vertical;min-height:90px;" placeholder="Jelaskan mengapa buku ini perlu ditambahkan ke koleksi perpustakaan..."><?= htmlspecialchars($_POST['alasan']??'') ?></textarea>
                </div>
                <button type="submit" class="btn-kirim"><i class="bi bi-send me-2"></i>Kirim Saran</button>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);font-weight:600;margin-bottom:12px;">Saran Saya Sebelumnya</div>
        <?php if($saran_saya->num_rows>0): while($r=$saran_saya->fetch_assoc()):
            $bs=['baru'=>'badge-baru','ditinjau'=>'badge-ditinjau','disetujui'=>'badge-ok','ditolak'=>'badge-no'];
            $bl=['baru'=>'Baru','ditinjau'=>'Ditinjau','disetujui'=>'Disetujui','ditolak'=>'Ditolak'];
            $st=$r['status'];
        ?>
        <div class="saran-item">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;flex-wrap:wrap;">
                <div style="font-size:.9rem;font-weight:600;color:#fff;flex:1;"><?= htmlspecialchars($r['judul_saran']) ?></div>
                <span class="<?= $bs[$st]??'badge-baru' ?>"><?= $bl[$st]??$st ?></span>
            </div>
            <?php if($r['penulis_saran']||$r['penerbit_saran']): ?><div style="font-size:.73rem;color:var(--muted);margin-top:4px;"><?= htmlspecialchars(implode(' / ',array_filter([$r['penulis_saran'],$r['penerbit_saran']]))) ?></div><?php endif; ?>
            <div style="font-size:.7rem;color:var(--muted);margin-top:6px;"><i class="bi bi-calendar3 me-1"></i><?= date('d M Y',strtotime($r['tanggal_saran'])) ?></div>
        </div>
        <?php endwhile; else: ?>
        <div style="text-align:center;padding:32px;background:var(--card);border:1px dashed var(--border);border-radius:12px;color:var(--muted);font-size:.84rem;">
            <i class="bi bi-lightbulb" style="font-size:1.8rem;display:block;margin-bottom:8px;opacity:.3;"></i>
            Belum ada saran yang dikirim.<br>Jadilah yang pertama!
        </div>
        <?php endif; ?>
    </div>
</div>
</div>

<footer class="site-footer"><div class="container"><div class="footer-inner">
    <div class="footer-brand">📚 E-Library SMKN 1 Depok</div>
    <div class="footer-copy">&copy; <?= date('Y') ?> E-Library. All Rights Reserved.</div>
    <div class="footer-links"><a href="koleksi.php">Koleksi</a><a href="peminjaman-buku.php">Peminjaman</a></div>
</div></div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>window.addEventListener('scroll',()=>document.getElementById('mainNav').classList.toggle('scrolled',scrollY>30));</script>
</body></html>
