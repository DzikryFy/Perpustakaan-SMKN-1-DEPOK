<?php
session_start();
require_once '../config/database.php';
requirePustakawan();

$base='../'; $loggedIn=true; $namaUser=$_SESSION['nama']??''; $roleUser=$_SESSION['role']??'';
$msg = $error = '';
$act = $_GET['act'] ?? 'list';
$id  = (int)($_GET['id'] ?? 0);

// HAPUS
if ($act === 'hapus' && $id) {
    $bk = $conn->query("SELECT gambar FROM buku WHERE id_buku=$id")->fetch_assoc();
    if(!empty($bk['gambar']) && file_exists('../img/buku/'.$bk['gambar'])) unlink('../img/buku/'.$bk['gambar']);
    $conn->query("DELETE FROM buku WHERE id_buku=$id");
    $msg = 'Buku berhasil dihapus.'; $act = 'list';
}

// Helper: upload gambar
function uploadGambar(): ?string {
    if(empty($_FILES['gambar']['name'])) return null;
    $f = $_FILES['gambar'];
    $ext = strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];
    if(!in_array($ext,$allowed)) return null;
    if($f['size'] > 2*1024*1024) return null;
    $fname = 'buku_'.time().'_'.rand(100,999).'.'.$ext;
    $dest  = '../img/buku/'.$fname;
    return move_uploaded_file($f['tmp_name'],$dest) ? $fname : null;
}

// SIMPAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_buku      = sanitize($_POST['nama_buku'] ?? '');
    $kategori       = sanitize($_POST['kategori'] ?? '');
    $stock          = (int)($_POST['stock'] ?? 0);
    $no_bos         = sanitize($_POST['no_bos'] ?? '');
    $penerbit       = sanitize($_POST['penerbit'] ?? '');
    $jenis_barang   = sanitize($_POST['jenis_barang'] ?? '');
    $tahun_cetak    = (int)($_POST['tahun_cetak'] ?? 0) ?: null;
    $asal_perolehan = sanitize($_POST['asal_perolehan'] ?? 'BOS');
    $eid            = (int)($_POST['eid'] ?? 0);
    $hapus_gambar   = isset($_POST['hapus_gambar']);

    if (empty($nama_buku) || empty($kategori)) {
        $error = 'Nama buku dan kategori wajib diisi.';
        $act   = $eid ? 'edit' : 'tambah'; $id = $eid;
    } elseif ($eid) {
        $old = $conn->query("SELECT gambar FROM buku WHERE id_buku=$eid")->fetch_assoc();
        $newGambar = uploadGambar();
        if($hapus_gambar && !$newGambar) {
            if(!empty($old['gambar']) && file_exists('../img/buku/'.$old['gambar'])) unlink('../img/buku/'.$old['gambar']);
            $newGambar = '';
        } elseif(!$newGambar) {
            $newGambar = $old['gambar'] ?? '';
        } else {
            if(!empty($old['gambar']) && file_exists('../img/buku/'.$old['gambar'])) unlink('../img/buku/'.$old['gambar']);
        }
        $gambarSQL = $conn->real_escape_string($newGambar ?? '');
        $s = $conn->prepare("UPDATE buku SET nama_buku=?,kategori=?,stock=?,no_bos=?,penerbit=?,jenis_barang=?,tahun_cetak=?,asal_perolehan=?,gambar=? WHERE id_buku=?");
        $s->bind_param("ssisiisssi", $nama_buku,$kategori,$stock,$no_bos,$penerbit,$jenis_barang,$tahun_cetak,$asal_perolehan,$gambarSQL,$eid);
        if ($s->execute()) { $msg='Buku berhasil diperbarui.'; $act='list'; }
        else { $error='Gagal memperbarui.'; $act='edit'; $id=$eid; }
        $s->close();
    } else {
        $newGambar = uploadGambar() ?? '';
        $s = $conn->prepare("INSERT INTO buku (nama_buku,kategori,stock,no_bos,penerbit,jenis_barang,tahun_cetak,asal_perolehan,gambar) VALUES (?,?,?,?,?,?,?,?,?)");
        $s->bind_param("ssisisss s",$nama_buku,$kategori,$stock,$no_bos,$penerbit,$jenis_barang,$tahun_cetak,$asal_perolehan,$newGambar);
        if ($s->execute()) { $msg='Buku berhasil ditambahkan.'; $act='list'; }
        else { $error='Gagal menyimpan.'; $act='tambah'; }
        $s->close();
    }
}

// DATA EDIT
$ed = null;
if ($act === 'edit' && $id && !$error) {
    $ed = $conn->query("SELECT * FROM buku WHERE id_buku=$id")->fetch_assoc();
    if (!$ed) $act = 'list';
}

// LIST
$q     = sanitize($_GET['q'] ?? '');
$where = $q ? "WHERE nama_buku LIKE '%$q%' OR kategori LIKE '%$q%'" : '';
$list  = $conn->query("SELECT * FROM buku $where ORDER BY id_buku DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Data Buku | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php include 'sidebar.php'; ?>
</head>
<body>
<div class="main">
    <div class="topbar">
        <div><h1>Data Buku</h1><div class="bc">Admin / Buku</div></div>
        <?php if ($act==='list'): ?>
        <a href="?act=tambah" class="btn-gold"><i class="bi bi-plus-lg"></i> Tambah Buku</a>
        <?php else: ?>
        <a href="buku.php" class="btn-out"><i class="bi bi-arrow-left"></i> Kembali</a>
        <?php endif; ?>
    </div>
    <div class="content">
        <?php if ($msg): ?><div class="msg-ok"><i class="bi bi-check-circle me-2"></i><?= $msg ?></div><?php endif; ?>
        <?php if ($error): ?><div class="msg-err"><i class="bi bi-exclamation-circle me-2"></i><?= $error ?></div><?php endif; ?>

        <?php if ($act==='list'): ?>
        <div class="panel">
            <div class="ph">
                <h3><i class="bi bi-book me-2" style="color:var(--accent)"></i>Daftar Buku (<?= $list->num_rows ?>)</h3>
                <form method="GET" style="display:flex;gap:8px">
                    <input type="hidden" name="act" value="list">
                    <input type="text" name="q" class="srch" placeholder="Cari nama / kategori..." value="<?= htmlspecialchars($q) ?>">
                    <button type="submit" class="btn-gold" style="padding:8px 14px"><i class="bi bi-search"></i></button>
                    <?php if($q): ?><a href="buku.php" class="btn-out" style="padding:8px 14px">Reset</a><?php endif; ?>
                </form>
            </div>
            <table class="tbl">
                <thead><tr><th>#</th><th>Cover</th><th>No. BOS</th><th>Nama Buku</th><th>Penerbit</th><th>Kategori</th><th>Stock</th><th>Tahun</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php $no=1; while ($b=$list->fetch_assoc()): ?>
                <tr>
                    <td style="color:var(--muted)"><?= $no++ ?></td>
                    <td>
                        <?php if(!empty($b['gambar']) && file_exists('../img/buku/'.$b['gambar'])): ?>
                        <img src="../img/buku/<?= htmlspecialchars($b['gambar']) ?>" style="width:38px;height:48px;object-fit:cover;border-radius:6px;border:1px solid var(--border);">
                        <?php else: ?>
                        <div style="width:38px;height:48px;border-radius:6px;background:var(--border);display:flex;align-items:center;justify-content:center;font-size:1.1rem;">📚</div>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--accent);font-size:0.78rem"><?= htmlspecialchars($b['no_bos'] ?? '-') ?></td>
                    <td style="font-weight:500;color:#fff"><?= htmlspecialchars($b['nama_buku']) ?></td>
                    <td style="color:var(--muted);font-size:0.82rem"><?= htmlspecialchars($b['penerbit'] ?? '-') ?></td>
                    <td><span style="padding:3px 10px;border-radius:20px;font-size:0.7rem;font-weight:600;background:rgba(232,184,109,0.1);color:var(--accent)"><?= htmlspecialchars($b['kategori']) ?></span></td>
                    <td><span style="font-weight:600;color:<?= $b['stock']>0?'var(--green)':'var(--red)' ?>"><?= $b['stock'] ?></span></td>
                    <td style="color:var(--muted);font-size:0.82rem"><?= $b['tahun_cetak'] ?? '-' ?></td>
                    <td>
                        <div class="abtn-wrap">
                            <a href="?act=edit&id=<?= $b['id_buku'] ?>" class="abtn abtn-edit"><i class="bi bi-pencil"></i> Edit</a>
                            <a href="?act=hapus&id=<?= $b['id_buku'] ?>" class="abtn abtn-del" onclick="return confirm('Hapus buku ini?')"><i class="bi bi-trash"></i> Hapus</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($list->num_rows===0): ?>
                <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:30px">Tidak ada data buku</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>
        <!-- FORM -->
        <div class="panel" style="max-width:540px">
            <div class="ph"><h3><i class="bi bi-<?= $act==='tambah'?'plus':'pencil' ?> me-2" style="color:var(--accent)"></i><?= $act==='tambah'?'Tambah Buku Baru':'Edit Buku' ?></h3></div>
            <div class="pb">
                <form method="POST" action="buku.php" enctype="multipart/form-data">
                    <?php if ($act==='edit'): ?><input type="hidden" name="eid" value="<?= $id ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Nama Buku *</label>
                        <input type="text" name="nama_buku" class="form-control" placeholder="Masukkan nama buku"
                               value="<?= htmlspecialchars($ed['nama_buku'] ?? $_POST['nama_buku'] ?? '') ?>">
                    </div>
                    <div class="fgrid mb-3">
                        <div>
                            <label class="form-label">Kategori *</label>
                            <select name="kategori" class="form-select">
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach(['Umum','Motivasi','Sastra','Religi'] as $k):
                                    $sel = ($ed['kategori']??$_POST['kategori']??'')===$k?'selected':''; ?>
                                <option value="<?= $k ?>" <?= $sel ?>><?= $k ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" min="0"
                                   value="<?= $ed['stock'] ?? $_POST['stock'] ?? 0 ?>">
                        </div>
                    </div>
                    <div class="fgrid mb-3">
                        <div>
                            <label class="form-label">No. BOS</label>
                            <input type="text" name="no_bos" class="form-control" placeholder="mis. 5439"
                                   value="<?= htmlspecialchars($ed['no_bos'] ?? $_POST['no_bos'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="form-label">Penerbit</label>
                            <input type="text" name="penerbit" class="form-control" placeholder="mis. Erlangga"
                                   value="<?= htmlspecialchars($ed['penerbit'] ?? $_POST['penerbit'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="fgrid mb-3">
                        <div>
                            <label class="form-label">Tahun Cetak</label>
                            <input type="number" name="tahun_cetak" class="form-control" placeholder="mis. 2025" min="1900" max="2100"
                                   value="<?= $ed['tahun_cetak'] ?? $_POST['tahun_cetak'] ?? '' ?>">
                        </div>
                        <div>
                            <label class="form-label">Asal Perolehan</label>
                            <select name="asal_perolehan" class="form-select">
                                <?php foreach(['BOS','BOS Kin','Donasi','Pembelian Mandiri','Koleksi Awal'] as $ap):
                                    $sel2 = ($ed['asal_perolehan']??$_POST['asal_perolehan']??'BOS')===$ap?'selected':''; ?>
                                <option value="<?= $ap ?>" <?= $sel2 ?>><?= $ap ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Barang</label>
                        <input type="text" name="jenis_barang" class="form-control" placeholder="mis. Lain-Lain Bahan Perpustakaan Tercetak Lainnya"
                               value="<?= htmlspecialchars($ed['jenis_barang'] ?? $_POST['jenis_barang'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar Cover Buku <span style="font-size:.72rem;color:var(--muted)">(JPG/PNG/WebP, maks 2MB)</span></label>
                        <?php if(!empty($ed['gambar']) && file_exists('../img/buku/'.$ed['gambar'])): ?>
                        <div style="margin-bottom:10px;display:flex;align-items:center;gap:12px;">
                            <img src="../img/buku/<?= htmlspecialchars($ed['gambar']) ?>" style="height:80px;border-radius:8px;object-fit:cover;border:1px solid var(--border);">
                            <label style="display:flex;align-items:center;gap:6px;font-size:.8rem;color:var(--muted);cursor:pointer;">
                                <input type="checkbox" name="hapus_gambar" value="1"> Hapus gambar ini
                            </label>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="gambar" class="form-control" accept="image/jpeg,image/png,image/webp"
                               style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:var(--text);border-radius:9px;padding:8px;">
                    </div>
                    <div style="display:flex;gap:10px;margin-top:6px">
                        <button type="submit" class="btn-gold"><i class="bi bi-check-lg"></i> <?= $act==='tambah'?'Simpan':'Update' ?></button>
                        <a href="buku.php" class="btn-out">Batal</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
