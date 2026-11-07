<?php
session_start();
require_once '../config/database.php';
requirePustakawan();

$msg = $error = '';
$act = $_GET['act'] ?? 'list';
$id  = (int)($_GET['id'] ?? 0);

// HAPUS
if ($act==='hapus' && $id) {
    if ($id == $_SESSION['id_user']) { $error='Tidak bisa menghapus akun sendiri.'; $act='list'; }
    else { $conn->query("DELETE FROM users WHERE id_user=$id"); $msg='User berhasil dihapus.'; $act='list'; }
}

// SIMPAN
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nama     = sanitize($_POST['nama'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $role     = in_array($_POST['role']??'',['siswa','guru','pustakawan','kepala_perpustakaan'])?$_POST['role']:'siswa';
    $password = $_POST['password'] ?? '';
    $eid      = (int)($_POST['eid'] ?? 0);

    if (empty($nama)||empty($username)||empty($email)) { $error='Nama, username, email wajib diisi.'; $act=$eid?'edit':'tambah'; $id=$eid; }
    elseif ($eid) {
        // Update (password opsional)
        if (!empty($password) && strlen($password)<6) { $error='Password minimal 6 karakter.'; $act='edit'; $id=$eid; }
        else {
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $s = $conn->prepare("UPDATE users SET nama=?,username=?,email=?,password=?,role=? WHERE id_user=?");
                $s->bind_param("sssssi",$nama,$username,$email,$hash,$role,$eid);
            } else {
                $s = $conn->prepare("UPDATE users SET nama=?,username=?,email=?,role=? WHERE id_user=?");
                $s->bind_param("ssssi",$nama,$username,$email,$role,$eid);
            }
            if ($s->execute()) { $msg='User berhasil diperbarui.'; $act='list'; }
            else { $error='Gagal update (username/email mungkin duplikat).'; $act='edit'; $id=$eid; }
            $s->close();
        }
    } else {
        if (empty($password)||strlen($password)<6) { $error='Password minimal 6 karakter.'; $act='tambah'; }
        else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $s = $conn->prepare("INSERT INTO users (nama,username,email,password,role) VALUES (?,?,?,?,?)");
            $s->bind_param("sssss",$nama,$username,$email,$hash,$role);
            if ($s->execute()) { $msg='User berhasil ditambahkan.'; $act='list'; }
            else { $error='Gagal (username/email mungkin sudah ada).'; $act='tambah'; }
            $s->close();
        }
    }
}

$ed = null;
if ($act==='edit' && $id && !$error) {
    $ed = $conn->query("SELECT * FROM users WHERE id_user=$id")->fetch_assoc();
    if (!$ed) $act='list';
}

$q     = sanitize($_GET['q'] ?? '');
$where = $q ? "WHERE nama LIKE '%$q%' OR username LIKE '%$q%' OR email LIKE '%$q%'" : '';
$list  = $conn->query("SELECT * FROM users $where ORDER BY id_user DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Data User | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php include 'sidebar.php'; ?>
</head>
<body>
<div class="main">
    <div class="topbar">
        <div><h1>Data User</h1><div class="bc">Admin / User</div></div>
        <?php if ($act==='list'): ?>
        <a href="?act=tambah" class="btn-gold"><i class="bi bi-plus-lg"></i> Tambah User</a>
        <?php else: ?>
        <a href="users.php" class="btn-out"><i class="bi bi-arrow-left"></i> Kembali</a>
        <?php endif; ?>
    </div>
    <div class="content">
        <?php if ($msg): ?><div class="msg-ok"><i class="bi bi-check-circle me-2"></i><?= $msg ?></div><?php endif; ?>
        <?php if ($error): ?><div class="msg-err"><i class="bi bi-exclamation-circle me-2"></i><?= $error ?></div><?php endif; ?>

        <?php if ($act==='list'): ?>
        <div class="panel">
            <div class="ph">
                <h3><i class="bi bi-people me-2" style="color:var(--accent)"></i>Daftar User (<?= $list->num_rows ?>)</h3>
                <form method="GET" style="display:flex;gap:8px">
                    <input type="hidden" name="act" value="list">
                    <input type="text" name="q" class="srch" placeholder="Cari nama / username..." value="<?= htmlspecialchars($q) ?>">
                    <button type="submit" class="btn-gold" style="padding:8px 14px"><i class="bi bi-search"></i></button>
                    <?php if($q): ?><a href="users.php" class="btn-out" style="padding:8px 14px">Reset</a><?php endif; ?>
                </form>
            </div>
            <table class="tbl">
                <thead><tr><th>#</th><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php $no=1; while ($u=$list->fetch_assoc()): ?>
                <tr>
                    <td style="color:var(--muted)"><?= $no++ ?></td>
                    <td style="font-weight:500;color:#fff"><?= htmlspecialchars($u['nama']) ?></td>
                    <td style="font-family:monospace;font-size:0.8rem;color:var(--accent)">@<?= htmlspecialchars($u['username']) ?></td>
                    <td style="color:var(--muted);font-size:0.8rem"><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge-role role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td>
                        <div class="abtn-wrap">
                            <a href="?act=edit&id=<?= $u['id_user'] ?>" class="abtn abtn-edit"><i class="bi bi-pencil"></i> Edit</a>
                            <?php if ($u['id_user'] != $_SESSION['id_user']): ?>
                            <a href="?act=hapus&id=<?= $u['id_user'] ?>" class="abtn abtn-del" onclick="return confirm('Hapus user ini?')"><i class="bi bi-trash"></i> Hapus</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($list->num_rows===0): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px">Tidak ada data user</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>
        <div class="panel" style="max-width:580px">
            <div class="ph"><h3><i class="bi bi-<?= $act==='tambah'?'person-plus':'pencil' ?> me-2" style="color:var(--accent)"></i><?= $act==='tambah'?'Tambah User Baru':'Edit User' ?></h3></div>
            <div class="pb">
                <form method="POST" action="users.php">
                    <?php if ($act==='edit'): ?><input type="hidden" name="eid" value="<?= $id ?>"><?php endif; ?>
                    <div class="fgrid mb-3">
                        <div>
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" name="nama" class="form-control" placeholder="Nama lengkap"
                                   value="<?= htmlspecialchars($ed['nama'] ?? $_POST['nama'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" placeholder="username"
                                   value="<?= htmlspecialchars($ed['username'] ?? $_POST['username'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com"
                               value="<?= htmlspecialchars($ed['email'] ?? $_POST['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <?php foreach(['siswa','guru','admin'] as $r):
                                $sel=($ed['role']??$_POST['role']??'siswa')===$r?'selected':''; ?>
                            <option value="<?= $r ?>" <?= $sel ?>><?= ucfirst($r) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password <?= $act==='edit'?'(kosongkan jika tidak diubah)':'*' ?></label>
                        <input type="password" name="password" class="form-control" placeholder="<?= $act==='edit'?'Isi untuk mengubah password':'Min. 6 karakter' ?>">
                    </div>
                    <div style="display:flex;gap:10px">
                        <button type="submit" class="btn-gold"><i class="bi bi-check-lg"></i> <?= $act==='tambah'?'Simpan':'Update' ?></button>
                        <a href="users.php" class="btn-out">Batal</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
