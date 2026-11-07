<?php
session_start();
require_once 'config/database.php';
if (isLoggedIn()) redirect(isStaff() ? 'admin/dashboard.php' : 'index.php');

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = sanitize($_POST['nama'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $role     = in_array($_POST['role'] ?? '', ['siswa','guru']) ? $_POST['role'] : 'siswa';
    $password = $_POST['password'] ?? '';
    $konfirm  = $_POST['konfirm'] ?? '';

    if (empty($nama) || empty($username) || empty($email) || empty($password))
        $error = 'Semua field wajib diisi.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $error = 'Format email tidak valid.';
    elseif (strlen($password) < 6)
        $error = 'Password minimal 6 karakter.';
    elseif ($password !== $konfirm)
        $error = 'Konfirmasi password tidak cocok.';
    else {
        $chk = $conn->prepare("SELECT id_user FROM users WHERE username=? OR email=?");
        $chk->bind_param("ss", $username, $email);
        $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) {
            $error = 'Username atau email sudah terdaftar.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $conn->prepare("INSERT INTO users (nama,username,email,password,role) VALUES (?,?,?,?,?)");
            $ins->bind_param("sssss", $nama, $username, $email, $hash, $role);
            if ($ins->execute()) $success = 'Pendaftaran berhasil! Silakan login.';
            else $error = 'Terjadi kesalahan, coba lagi.';
            $ins->close();
        }
        $chk->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | E-Library SMKN 1 Depok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root{--dark:#0c1520;--card:#152135;--accent:#e8b86d;--gold2:#f5d49a;--text:#cdd8e8;--muted:#7a8fa6;--border:rgba(255,255,255,0.07);}
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'DM Sans',sans-serif;background:var(--dark);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px 16px;}
        body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(232,184,109,0.07) 0%,transparent 70%);pointer-events:none;}
        .auth-wrap{width:100%;max-width:460px;position:relative;z-index:1;}
        .brand{text-align:center;margin-bottom:28px;}
        .brand-icon{width:50px;height:50px;background:linear-gradient(135deg,var(--accent),var(--gold2));border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;margin:0 auto 12px;box-shadow:0 8px 28px rgba(232,184,109,0.25);}
        .brand h1{font-family:'Playfair Display',serif;font-size:1.4rem;color:var(--accent);}
        .brand p{font-size:0.7rem;color:var(--muted);letter-spacing:0.1em;text-transform:uppercase;margin-top:2px;}
        .card-auth{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:32px 28px;box-shadow:0 24px 60px rgba(0,0,0,0.4);}
        .card-auth h2{font-family:'Playfair Display',serif;font-size:1.35rem;color:#fff;margin-bottom:4px;}
        .card-auth .sub{color:var(--muted);font-size:0.82rem;margin-bottom:24px;}
        .form-label{font-size:0.73rem;font-weight:500;color:var(--muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:5px;}
        .form-control,.form-select{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);color:var(--text);border-radius:10px;padding:10px 14px;font-size:0.9rem;transition:border .2s,box-shadow .2s;}
        .form-control:focus,.form-select:focus{background:rgba(255,255,255,0.06);border-color:var(--accent);box-shadow:0 0 0 3px rgba(232,184,109,0.12);color:var(--text);}
        .form-control::placeholder{color:rgba(255,255,255,0.2);}
        .form-select option{background:#152135;}
        .input-group .form-control{border-radius:10px 0 0 10px;}
        .input-group-text{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);border-left:none;color:var(--muted);cursor:pointer;border-radius:0 10px 10px 0;transition:color .2s;}
        .input-group-text:hover{color:var(--accent);}
        .btn-reg{background:linear-gradient(135deg,var(--accent),#d4a54e);color:#0c1520;font-weight:600;border:none;border-radius:10px;padding:12px;width:100%;font-size:0.93rem;transition:transform .15s,box-shadow .15s;box-shadow:0 4px 18px rgba(232,184,109,0.28);}
        .btn-reg:hover{transform:translateY(-2px);box-shadow:0 8px 26px rgba(232,184,109,0.38);color:#0c1520;}
        .divider{display:flex;align-items:center;gap:12px;margin:18px 0;color:var(--muted);font-size:0.8rem;}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,0.08);}
        .link-login{text-align:center;font-size:0.84rem;color:var(--muted);}
        .link-login a{color:var(--accent);text-decoration:none;font-weight:500;}
        .link-login a:hover{text-decoration:underline;}
        .err-box{background:rgba(220,53,69,0.1);border:1px solid rgba(220,53,69,0.2);color:#ff8a8a;border-radius:10px;padding:11px 14px;font-size:0.84rem;margin-bottom:18px;}
        .ok-box{background:rgba(58,171,110,0.1);border:1px solid rgba(58,171,110,0.2);color:#75d8a0;border-radius:10px;padding:11px 14px;font-size:0.84rem;margin-bottom:18px;}
        .back-link{display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-size:0.8rem;text-decoration:none;margin-bottom:22px;transition:color .2s;}
        .back-link:hover{color:var(--accent);}
        .grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
        @media(max-width:480px){.grid2{grid-template-columns:1fr;}}
        /* Role tabs */
        .role-tabs{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:0;}
        .role-tab input{display:none;}
        .role-tab label{display:flex;align-items:center;justify-content:center;gap:7px;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,0.1);color:var(--muted);font-size:0.84rem;cursor:pointer;transition:all .2s;width:100%;}
        .role-tab input:checked + label{border-color:var(--accent);background:rgba(232,184,109,0.1);color:var(--accent);}
    </style>
</head>
<body>
<div class="auth-wrap">
    <a href="index.php" class="back-link"><i class="bi bi-arrow-left"></i> Kembali ke Beranda</a>
    <div class="brand">
        <div class="brand-icon"><img src="img/logo-onedek.png" alt="" style="width: 30px;"></div>
        <h1>E-Library</h1>
        <p>SMKN 1 Depok</p>
    </div>
    <div class="card-auth">
        <h2>Buat Akun</h2>
        <p class="sub">Daftarkan diri sebagai anggota perpustakaan</p>
        <?php if ($error): ?><div class="err-box"><i class="bi bi-exclamation-circle me-2"></i><?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="ok-box"><i class="bi bi-check-circle me-2"></i><?= $success ?> <a href="login.php" style="color:#75d8a0;font-weight:600;">Login sekarang →</a></div><?php endif; ?>
        <?php if (!$success): ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Daftar Sebagai</label>
                <div class="role-tabs">
                    <div class="role-tab">
                        <input type="radio" name="role" id="r_siswa" value="siswa" <?= ($_POST['role']??'siswa')==='siswa'?'checked':'' ?>>
                        <label for="r_siswa"><i class="bi bi-person-fill"></i> Siswa</label>
                    </div>
                    <div class="role-tab">
                        <input type="radio" name="role" id="r_guru" value="guru" <?= ($_POST['role']??'')==='guru'?'checked':'' ?>>
                        <label for="r_guru"><i class="bi bi-mortarboard-fill"></i> Guru</label>
                    </div>
                </div>
            </div>
            <div class="grid2 mb-3">
                <div>
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-control" placeholder="Nama lengkap" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" placeholder="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" placeholder="email@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="grid2 mb-4">
                <div>
                    <label class="form-label">Password *</label>
                    <div class="input-group">
                        <input type="password" name="password" id="pw1" class="form-control" placeholder="Min. 6 karakter">
                        <span class="input-group-text" onclick="tgl('pw1','e1')"><i id="e1" class="bi bi-eye"></i></span>
                    </div>
                </div>
                <div>
                    <label class="form-label">Konfirmasi *</label>
                    <div class="input-group">
                        <input type="password" name="konfirm" id="pw2" class="form-control" placeholder="Ulangi password">
                        <span class="input-group-text" onclick="tgl('pw2','e2')"><i id="e2" class="bi bi-eye"></i></span>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn-reg"><i class="bi bi-person-plus me-2"></i>Daftar Sekarang</button>
        </form>
        <?php endif; ?>
        <div class="divider">atau</div>
        <div class="link-login">Sudah punya akun? <a href="login.php">Masuk di sini</a></div>
    </div>
    <p style="text-align:center;color:var(--muted);font-size:0.73rem;margin-top:18px;">&copy; <?= date('Y') ?> E-Library SMKN 1 Depok</p>
</div>
<script>
function tgl(fid,eid){
    var f=document.getElementById(fid),i=document.getElementById(eid);
    f.type=f.type==='password'?'text':'password';
    i.className=f.type==='password'?'bi bi-eye':'bi bi-eye-slash';
}
</script>
</body>
</html>
