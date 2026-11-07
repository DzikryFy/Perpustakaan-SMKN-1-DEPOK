<?php
session_start();
require_once 'config/database.php';
if (isLoggedIn()) redirect(isStaff() ? 'admin/dashboard.php' : 'index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT id_user,nama,username,password,role FROM users WHERE username=? OR email=?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['id_user']  = $user['id_user'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $dest = in_array($user['role'],['pustakawan','kepala_perpustakaan']) ? 'admin/dashboard.php' : (isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php'); redirect($dest);
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | E-Library SMKN 1 Depok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root{--dark:#0c1520;--card:#152135;--accent:#e8b86d;--gold2:#f5d49a;--text:#cdd8e8;--muted:#7a8fa6;--border:rgba(255,255,255,0.07);}
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'DM Sans',sans-serif;background:var(--dark);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
        body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(232,184,109,0.07) 0%,transparent 70%);pointer-events:none;}
        .auth-wrap{width:100%;max-width:420px;position:relative;z-index:1;}
        .brand{text-align:center;margin-bottom:32px;}
        .brand-icon{width:52px;height:52px;background:linear-gradient(135deg,var(--accent),var(--gold2));border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;margin:0 auto 14px;box-shadow:0 8px 28px rgba(232,184,109,0.25);}
        .brand h1{font-family:'Playfair Display',serif;font-size:1.5rem;color:var(--accent);}
        .brand p{font-size:0.72rem;color:var(--muted);letter-spacing:0.1em;text-transform:uppercase;margin-top:2px;}
        .card-auth{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:34px 30px;box-shadow:0 24px 60px rgba(0,0,0,0.4);}
        .card-auth h2{font-family:'Playfair Display',serif;font-size:1.4rem;color:#fff;margin-bottom:4px;}
        .card-auth .sub{color:var(--muted);font-size:0.83rem;margin-bottom:26px;}
        .form-label{font-size:0.73rem;font-weight:500;color:var(--muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:5px;}
        .form-control{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);color:var(--text);border-radius:10px;padding:10px 14px;font-size:0.9rem;transition:border .2s,box-shadow .2s;}
        .form-control:focus{background:rgba(255,255,255,0.06);border-color:var(--accent);box-shadow:0 0 0 3px rgba(232,184,109,0.12);color:var(--text);}
        .form-control::placeholder{color:rgba(255,255,255,0.2);}
        .input-group .form-control{border-radius:10px 0 0 10px;}
        .input-group-text{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);border-left:none;color:var(--muted);cursor:pointer;border-radius:0 10px 10px 0;transition:color .2s;}
        .input-group-text:hover{color:var(--accent);}
        .btn-login{background:linear-gradient(135deg,var(--accent),#d4a54e);color:#0c1520;font-weight:600;border:none;border-radius:10px;padding:12px;width:100%;font-size:0.93rem;transition:transform .15s,box-shadow .15s;box-shadow:0 4px 18px rgba(232,184,109,0.28);}
        .btn-login:hover{transform:translateY(-2px);box-shadow:0 8px 26px rgba(232,184,109,0.38);color:#0c1520;}
        .divider{display:flex;align-items:center;gap:12px;margin:18px 0;color:var(--muted);font-size:0.8rem;}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,0.08);}
        .link-reg{text-align:center;font-size:0.84rem;color:var(--muted);}
        .link-reg a{color:var(--accent);text-decoration:none;font-weight:500;}
        .link-reg a:hover{text-decoration:underline;}
        .err-box{background:rgba(220,53,69,0.1);border:1px solid rgba(220,53,69,0.2);color:#ff8a8a;border-radius:10px;padding:11px 14px;font-size:0.84rem;margin-bottom:18px;}
        .back-link{display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-size:0.8rem;text-decoration:none;margin-bottom:22px;transition:color .2s;}
        .back-link:hover{color:var(--accent);}
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
        <h2>Masuk Akun</h2>
        <p class="sub">Selamat datang kembali 👋</p>
        <?php if ($error): ?><div class="err-box"><i class="bi bi-exclamation-circle me-2"></i><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username / Email</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username atau email" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="pw" class="form-control" placeholder="Masukkan password">
                    <span class="input-group-text" onclick="let f=document.getElementById('pw'),i=document.getElementById('eye');f.type=f.type==='password'?'text':'password';i.className=f.type==='password'?'bi bi-eye':'bi bi-eye-slash';">
                        <i class="bi bi-eye" id="eye"></i>
                    </span>
                </div>
            </div>
            <button type="submit" class="btn-login"><i class="bi bi-box-arrow-in-right me-2"></i>Masuk</button>
        </form>
        <div class="divider">atau</div>
        <div class="link-reg">Belum punya akun? <a href="register.php">Daftar sekarang</a></div>
    </div>
    <p style="text-align:center;color:var(--muted);font-size:0.73rem;margin-top:18px;">&copy; <?= date('Y') ?> E-Library SMKN 1 Depok</p>
</div>
</body>
</html>
