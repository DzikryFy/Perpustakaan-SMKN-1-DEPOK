<?php
session_start();
require_once '../config/database.php';
requirePustakawan();
$base='../'; $loggedIn=true; $namaUser=$_SESSION['nama']??''; $roleUser=$_SESSION['role']??'';
$msg=$error='';
$act=$_GET['act']??'list';
$id=(int)($_GET['id']??0);

// HAPUS
if($act==='hapus'&&$id&&$id!==(int)$_SESSION['id_user']){
    $conn->query("DELETE FROM users WHERE id_user=$id");
    $msg='Pengguna dihapus.'; $act='list';
}

// SIMPAN
if($_SERVER['REQUEST_METHOD']==='POST'){
    $nama    =sanitize($_POST['nama']??'');
    $username=sanitize($_POST['username']??'');
    $email   =sanitize($_POST['email']??'');
    $role    =in_array($_POST['role']??'',['siswa','guru','pustakawan','kepala_perpustakaan'])?$_POST['role']:'siswa';
    $pass    =trim($_POST['password']??'');
    $eid     =(int)($_POST['eid']??0);

    if(empty($nama)||empty($username)||empty($email)){
        $error='Nama, username, dan email wajib diisi.'; $act=$eid?'edit':'tambah'; $id=$eid;
    } elseif($eid){
        if($pass){
            $hash=password_hash($pass,PASSWORD_BCRYPT);
            $s=$conn->prepare("UPDATE users SET nama=?,username=?,email=?,role=?,password=? WHERE id_user=?");
            $s->bind_param("sssssi",$nama,$username,$email,$role,$hash,$eid);
        } else {
            $s=$conn->prepare("UPDATE users SET nama=?,username=?,email=?,role=? WHERE id_user=?");
            $s->bind_param("ssssi",$nama,$username,$email,$role,$eid);
        }
        if($s->execute()){
            $msg='Data pengguna diperbarui.';
            $act='list';
        } else {
            $error='Gagal: username/email mungkin sudah digunakan.';
            $act='edit';
            $id=$eid;
        }
        $s->close();
    } else {
        if(strlen($pass)<6){$error='Password minimal 6 karakter.';$act='tambah';}
        else {
            $hash=password_hash($pass,PASSWORD_BCRYPT);
            $s=$conn->prepare("INSERT INTO users (nama,username,email,password,role) VALUES (?,?,?,?,?)");
            $s->bind_param("sssss",$nama,$username,$email,$hash,$role);
            if($s->execute()){
                $msg='Pengguna ditambahkan.';
                $act='list';
            } else {
                $error='Gagal: username/email sudah digunakan.';
                $act='tambah';
            }
            $s->close();
        }
    }
}

$q=sanitize($_GET['q']??'');
$rf=sanitize($_GET['rf']??'');
$wh="WHERE 1";
if($q) $wh.=" AND (nama LIKE '%$q%' OR username LIKE '%$q%' OR email LIKE '%$q%')";
if($rf) $wh.=" AND role='$rf'";
$list=$conn->query("SELECT * FROM users $wh ORDER BY role,nama ASC");
$ed=($act==='edit'&&$id)?$conn->query("SELECT * FROM users WHERE id_user=$id")->fetch_assoc():[];

$roleCounts=[];
$rc=$conn->query("SELECT role,COUNT(*) c FROM users GROUP BY role");
while($r=$rc->fetch_assoc()) $roleCounts[$r['role']]=$r['c'];
?><!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Data User | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<?php include 'sidebar.php'; ?>
<style>
.main-content{margin-left:230px;padding:30px;min-height:100vh;}
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;}
.topbar h1{font-family:'Playfair Display',serif;font-size:1.7rem;color:#fff;margin:0;}
.topbar .bc{font-size:.78rem;color:var(--muted);margin-top:3px;}
.stat-mini{display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;}
.sm-card{background:var(--card);border:1px solid var(--border);border-radius:10px;padding:12px 16px;display:flex;align-items:center;gap:10px;cursor:pointer;text-decoration:none;transition:all .2s;}
.sm-card:hover{border-color:rgba(232,184,109,.3);}
.sm-card.active-filter{border-color:var(--accent);background:rgba(232,184,109,.08);}
.sm-card .n{font-size:1.3rem;font-weight:700;color:#fff;line-height:1;}
.sm-card .l{font-size:.68rem;color:var(--muted);text-transform:uppercase;}
.role-chip{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:600;}
.role-siswa{background:rgba(58,171,110,.12);color:var(--green);}
.role-guru{background:rgba(74,158,255,.12);color:var(--blue);}
.role-pustakawan{background:rgba(232,184,109,.12);color:var(--accent);}
.role-kepala_perpustakaan{background:rgba(167,139,250,.12);color:var(--purple);}
.fi{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:var(--text);border-radius:9px;padding:10px 14px;font-size:.87rem;font-family:'DM Sans',sans-serif;transition:border .2s;}
.fi:focus{outline:none;border-color:var(--accent);}
.fi::placeholder{color:rgba(255,255,255,.2);}
.fi-select option{background:#152135;}
.fl{font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);display:block;margin-bottom:5px;font-weight:500;}
.form-wrap{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-wrap .full{grid-column:1/-1;}
.form-actions{display:flex;gap:10px;margin-top:6px;padding-top:16px;border-top:1px solid var(--border);}
.avatar{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;color:#0c1520;flex-shrink:0;}
@media(max-width:900px){.form-wrap{grid-template-columns:1fr;}.main-content{margin-left:0;}}
</style>
</head><body>
<div class="main-content">
<div class="topbar">
    <div>
        <h1><?= $act==='list'?'Data Pengguna':($act==='tambah'?'Tambah Pengguna':'Edit Pengguna') ?></h1>
        <div class="bc">Dashboard / <?= $act==='list'?'Users':($act==='tambah'?'Tambah':'Edit') ?></div>
    </div>
    <?php if($act==='list'): ?>
    <a href="?act=tambah" class="btn-gold"><i class="bi bi-person-plus me-1"></i>Tambah Pengguna</a>
    <?php else: ?>
    <a href="users.php" class="btn-out"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    <?php endif; ?>
</div>

<?php if($msg): ?><div class="msg-ok"><i class="bi bi-check-circle me-2"></i><?= $msg ?></div><?php endif; ?>
<?php if($error): ?><div class="msg-err"><i class="bi bi-exclamation-circle me-2"></i><?= $error ?></div><?php endif; ?>

<?php if($act==='list'): ?>
<!-- Stats mini -->
<div class="stat-mini">
    <a href="users.php" class="sm-card <?= !$rf?'active-filter':'' ?>">
        <div><div class="n"><?= array_sum($roleCounts) ?></div><div class="l">Semua</div></div>
    </a>
    <?php $roleColors=['siswa'=>'var(--green)','guru'=>'var(--blue)','pustakawan'=>'var(--accent)','kepala_perpustakaan'=>'var(--purple)'];
    foreach($roleCounts as $r=>$c): ?>
    <a href="?rf=<?= $r ?>" class="sm-card <?= $rf===$r?'active-filter':'' ?>">
        <div style="width:8px;height:8px;border-radius:50%;background:<?= $roleColors[$r]??'var(--muted)' ?>;flex-shrink:0;margin-top:4px;"></div>
        <div><div class="n"><?= $c ?></div><div class="l"><?= roleLabel($r) ?></div></div>
    </a>
    <?php endforeach; ?>
</div>

<div class="panel">
    <div class="ph">
        <h3><i class="bi bi-people me-2" style="color:var(--accent)"></i>Daftar Pengguna (<?= $list->num_rows ?>)</h3>
        <form method="GET" style="display:flex;gap:8px;">
            <?php if($rf): ?><input type="hidden" name="rf" value="<?= $rf ?>"><?php endif; ?>
            <input type="text" name="q" class="srch" placeholder="Cari nama / username / email..." value="<?= htmlspecialchars($q) ?>" style="width:220px;">
            <button type="submit" class="btn-gold" style="padding:8px 14px;"><i class="bi bi-search"></i></button>
            <?php if($q): ?><a href="users.php<?= $rf?'?rf='.$rf:'' ?>" class="btn-out" style="padding:8px 14px;">Reset</a><?php endif; ?>
        </form>
    </div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><th>#</th><th>Pengguna</th><th>Username</th><th>Email</th><th>Role</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php $no=1; while($u=$list->fetch_assoc()):
            $colors=['siswa'=>'#3aab6e','guru'=>'#4a9eff','pustakawan'=>'#e8b86d','kepala_perpustakaan'=>'#a78bfa'];
            $bg=$colors[$u['role']]??'var(--muted)';
        ?>
        <tr>
            <td style="color:var(--muted);font-size:.82rem;"><?= $no++ ?></td>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="avatar" style="background:<?= $bg ?>;"><?= strtoupper(substr($u['nama'],0,1)) ?></div>
                    <div>
                        <div style="font-weight:600;color:#fff;font-size:.88rem;"><?= htmlspecialchars($u['nama']) ?></div>
                        <?php if($u['id_user']===(int)$_SESSION['id_user']): ?><div style="font-size:.68rem;color:var(--accent);">— Akun Anda</div><?php endif; ?>
                    </div>
                </div>
            </td>
            <td style="font-size:.83rem;color:var(--muted);">@<?= htmlspecialchars($u['username']) ?></td>
            <td style="font-size:.8rem;color:var(--muted);"><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="role-chip role-<?= $u['role'] ?>"><?= roleLabel($u['role']) ?></span></td>
            <td>
                <div class="abtn-wrap">
                    <a href="?act=edit&id=<?= $u['id_user'] ?>" class="abtn abtn-edit"><i class="bi bi-pencil"></i> Edit</a>
                    <?php if($u['id_user']!==(int)$_SESSION['id_user']): ?>
                    <a href="?act=hapus&id=<?= $u['id_user'] ?>" class="abtn abtn-del" onclick="return confirm('Hapus pengguna ini?')"><i class="bi bi-trash"></i></a>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php if($list->num_rows===0): ?><tr><td colspan="6" style="text-align:center;color:var(--muted);padding:36px;">Tidak ada pengguna ditemukan.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php else: ?>
<!-- FORM -->
<div class="panel" style="max-width:700px;">
    <div class="ph"><h3><i class="bi bi-person-<?= $act==='tambah'?'plus':'gear' ?> me-2" style="color:var(--accent)"></i><?= $act==='tambah'?'Tambah Pengguna Baru':'Edit Data Pengguna' ?></h3></div>
    <div class="pb">
        <form method="POST" action="users.php">
            <input type="hidden" name="eid" value="<?= $ed['id_user']??0 ?>">
            <div class="form-wrap">
                <div class="full">
                    <label class="fl">Nama Lengkap *</label>
                    <input type="text" name="nama" class="fi" placeholder="Nama lengkap pengguna" required
                           value="<?= htmlspecialchars($ed['nama']??$_POST['nama']??'') ?>">
                </div>
                <div>
                    <label class="fl">Username *</label>
                    <input type="text" name="username" class="fi" placeholder="Username unik" required
                           value="<?= htmlspecialchars($ed['username']??$_POST['username']??'') ?>">
                </div>
                <div>
                    <label class="fl">Email *</label>
                    <input type="email" name="email" class="fi" placeholder="email@sekolah.sch.id" required
                           value="<?= htmlspecialchars($ed['email']??$_POST['email']??'') ?>">
                </div>
                <div>
                    <label class="fl">Role / Jabatan</label>
                    <select name="role" class="fi fi-select">
                        <?php foreach(['siswa'=>'Siswa','guru'=>'Guru','pustakawan'=>'Pustakawan','kepala_perpustakaan'=>'Kepala Perpustakaan'] as $v=>$lb):
                            $sel=($ed['role']??$_POST['role']??'siswa')===$v?'selected':''; ?>
                        <option value="<?= $v ?>" <?= $sel ?>><?= $lb ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="fl">Password <?= $act==='edit'?'<span style="font-weight:400;color:var(--muted)">(kosongkan jika tidak diubah)</span>':'' ?></label>
                    <input type="password" name="password" class="fi"
                           placeholder="<?= $act==='edit'?'Isi untuk ganti password':'Min. 6 karakter' ?>">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-gold"><i class="bi bi-check-lg me-1"></i><?= $act==='tambah'?'Simpan':'Perbarui' ?></button>
                <a href="users.php" class="btn-out">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
