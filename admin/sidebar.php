<!-- SIDEBAR — E-Library SMKN 1 Depok -->
<style>
:root{--dark:#0d1520;--sidebar:#111d2e;--card:#152135;--accent:#e8b86d;--gold2:#f5d49a;--green:#3aab6e;--red:#e85d5d;--blue:#4a9eff;--purple:#a78bfa;--text:#cdd8e8;--muted:#637a96;--border:rgba(255,255,255,0.07);}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'DM Sans',sans-serif;background:var(--dark);color:var(--text);}
.sidebar{width:230px;background:var(--sidebar);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;top:0;bottom:0;left:0;z-index:100;overflow-y:auto;}
.sb-brand{padding:18px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;flex-shrink:0;}
.sb-brand .ico{width:34px;height:34px;background:linear-gradient(135deg,var(--accent),var(--gold2));border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;}
.sb-brand .nm{font-family:'Playfair Display',serif;color:var(--accent);font-size:.9rem;line-height:1.1;}
.sb-brand .sm{color:var(--muted);font-size:.6rem;letter-spacing:.08em;text-transform:uppercase;}
.sb-role-chip{margin:10px 12px;background:rgba(232,184,109,.08);border:1px solid rgba(232,184,109,.15);border-radius:8px;padding:8px 12px;}
.sb-role-name{font-size:.82rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.sb-role-label{font-size:.66rem;color:var(--accent);margin-top:1px;}
.sb-nav{flex:1;padding:10px 8px;}
.sb-lbl{font-size:.6rem;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);padding:10px 12px 4px;margin-top:4px;}
.sb-nav a{display:flex;align-items:center;gap:9px;padding:9px 12px;border-radius:9px;color:var(--muted);text-decoration:none;font-size:.83rem;font-weight:500;transition:all .2s;margin-bottom:2px;}
.sb-nav a:hover{background:rgba(232,184,109,.08);color:var(--accent);}
.sb-nav a.active{background:rgba(232,184,109,.12);color:var(--accent);border-left:3px solid var(--accent);padding-left:9px;}
.sb-nav a i{font-size:.9rem;width:16px;text-align:center;}
.sb-nav a .sb-badge{margin-left:auto;background:rgba(232,93,93,.2);color:#ff9090;border-radius:10px;font-size:.65rem;padding:1px 7px;font-weight:700;}
.sb-foot{padding:12px 8px;border-top:1px solid var(--border);flex-shrink:0;}
.sb-foot a{display:flex;align-items:center;gap:9px;padding:9px 12px;border-radius:9px;color:var(--muted);text-decoration:none;font-size:.83rem;transition:all .2s;}
.sb-foot a:hover{background:rgba(232,93,93,.1);color:var(--red);}
/* Layout */
.admin-wrap{display:flex;width:100%;min-height:100vh;}
.main-content{margin-left:230px;flex:1;padding:32px;overflow-x:hidden;}
/* Global components */
.form-label{font-size:.72rem;font-weight:500;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;display:block;}
.form-control,.form-select{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);color:var(--text);border-radius:9px;padding:9px 13px;font-size:.87rem;transition:border .2s;font-family:'DM Sans',sans-serif;}
.form-control:focus,.form-select:focus{outline:none;background:rgba(255,255,255,.06);border-color:var(--accent);box-shadow:0 0 0 3px rgba(232,184,109,.12);color:var(--text);}
.form-control::placeholder{color:rgba(255,255,255,.2);}
.form-select option{background:#152135;}
textarea.form-control{resize:vertical;min-height:80px;}
.btn-gold{background:linear-gradient(135deg,var(--accent),#d4a54e);color:#0d1520;font-weight:600;border:none;border-radius:9px;padding:9px 20px;font-size:.85rem;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:opacity .2s;}
.btn-gold:hover{opacity:.88;color:#0d1520;}
.btn-out{background:transparent;border:1px solid var(--border);color:var(--muted);border-radius:9px;padding:9px 18px;font-size:.85rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:all .2s;}
.btn-out:hover{border-color:var(--accent);color:var(--accent);}
.panel{background:var(--card);border:1px solid var(--border);border-radius:13px;overflow:hidden;margin-bottom:22px;}
.ph{padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.ph h3{font-size:.92rem;font-weight:600;color:#fff;}
.ph a,.ph button{font-size:.77rem;color:var(--accent);text-decoration:none;background:none;border:none;cursor:pointer;}
.pb{padding:18px;}
.tbl{width:100%;border-collapse:collapse;font-size:.82rem;}
.tbl th{padding:10px 16px;text-align:left;font-size:.68rem;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);background:rgba(255,255,255,.02);border-bottom:1px solid var(--border);}
.tbl td{padding:11px 16px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle;}
.tbl tr:last-child td{border-bottom:none;}
.tbl tr:hover td{background:rgba(255,255,255,.02);}
.abtn-wrap{display:flex;gap:5px;}
.abtn{padding:4px 10px;border-radius:6px;font-size:.73rem;text-decoration:none;border:none;cursor:pointer;transition:all .2s;}
.abtn-edit{background:rgba(74,158,255,.12);color:var(--blue);}
.abtn-edit:hover{background:rgba(74,158,255,.22);}
.abtn-del{background:rgba(232,93,93,.12);color:var(--red);}
.abtn-del:hover{background:rgba(232,93,93,.22);}
.srch{background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--text);border-radius:9px;padding:8px 13px;font-size:.83rem;width:220px;transition:border .2s;font-family:'DM Sans',sans-serif;}
.srch:focus{outline:none;border-color:var(--accent);}
.srch::placeholder{color:var(--muted);}
.msg-ok{background:rgba(58,171,110,.1);border:1px solid rgba(58,171,110,.2);color:#75d8a0;border-radius:9px;padding:10px 14px;font-size:.83rem;margin-bottom:16px;}
.msg-err{background:rgba(232,93,93,.1);border:1px solid rgba(232,93,93,.2);color:#ff9090;border-radius:9px;padding:10px 14px;font-size:.83rem;margin-bottom:16px;}
.fgrid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.badge-ada{padding:3px 10px;border-radius:20px;font-size:.68rem;font-weight:600;background:rgba(58,171,110,.12);color:var(--green);}
.badge-tidak{padding:3px 10px;border-radius:20px;font-size:.68rem;font-weight:600;background:rgba(232,93,93,.12);color:var(--red);}
.sec-label{font-size:.68rem;text-transform:uppercase;letter-spacing:.12em;color:var(--accent);font-weight:600;margin-bottom:4px;}
@media(max-width:900px){.fgrid{grid-template-columns:1fr;}}
@media(max-width:768px){.sidebar{display:none;}.main-content{margin-left:0;}}
</style>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<?php
$_sb_page  = basename($_SERVER['PHP_SELF']);
$_sb_role  = $_SESSION['role'] ?? '';
$_sb_nama  = $_SESSION['nama'] ?? '';
$_sb_isPust = $_sb_role === 'pustakawan';
$_sb_isKep  = $_sb_role === 'kepala_perpustakaan';
// Pending requests badge
$_req_cnt = (int)($conn->query("SELECT COUNT(*) c FROM request_peminjaman WHERE status='menunggu'")->fetch_assoc()['c'] ?? 0);
$_saran_cnt = (int)($conn->query("SELECT COUNT(*) c FROM saran_buku WHERE status='baru'")->fetch_assoc()['c'] ?? 0);
?>
<aside class="sidebar">
    <div class="sb-brand">
        <div class="ico"><img src="../img/logo-onedek.png" alt="" style="width:30px;"></div>
        <div><div class="nm">E-Library</div><div class="sm">SMKN 1 Depok</div></div>
    </div>
    <div class="sb-role-chip">
        <div class="sb-role-name"><?= htmlspecialchars($_sb_nama) ?></div>
        <div class="sb-role-label"><?= roleLabel($_sb_role) ?></div>
    </div>
    <nav class="sb-nav">
        <div class="sb-lbl">Utama</div>
        <a href="dashboard.php" <?= $_sb_page==='dashboard.php'?'class="active"':'' ?>><i class="bi bi-grid-1x2"></i> Dashboard</a>

        <?php if($_sb_isPust): ?>
        <div class="sb-lbl">Koleksi</div>
        <a href="buku.php" <?= $_sb_page==='buku.php'?'class="active"':'' ?>><i class="bi bi-book"></i> Data Buku</a>
        <a href="users.php" <?= $_sb_page==='users.php'?'class="active"':'' ?>><i class="bi bi-people"></i> Data User</a>

        <div class="sb-lbl">Transaksi</div>
        <a href="peminjaman.php" <?= $_sb_page==='peminjaman.php'?'class="active"':'' ?>>
            <i class="bi bi-arrow-bar-right"></i> Peminjaman
            <?php if($_req_cnt>0): ?><span class="sb-badge"><?= $_req_cnt ?></span><?php endif; ?>
        </a>
        <a href="pengembalian.php" <?= $_sb_page==='pengembalian.php'?'class="active"':'' ?>><i class="bi bi-arrow-bar-left"></i> Pengembalian</a>

        <div class="sb-lbl">Laporan</div>
        <a href="laporan.php" <?= $_sb_page==='laporan.php'?'class="active"':'' ?>><i class="bi bi-bar-chart-line"></i> Laporan</a>
        <a href="saran-buku.php" <?= $_sb_page==='saran-buku.php'?'class="active"':'' ?>>
            <i class="bi bi-lightbulb"></i> Saran Buku
            <?php if($_saran_cnt>0): ?><span class="sb-badge"><?= $_saran_cnt ?></span><?php endif; ?>
        </a>
        <?php endif; ?>

        <?php if($_sb_isKep): ?>
        <div class="sb-lbl">Data (Hanya Lihat)</div>
        <a href="buku-readonly.php" <?= $_sb_page==='buku-readonly.php'?'class="active"':'' ?>><i class="bi bi-book"></i> Koleksi Buku</a>
        <a href="users-readonly.php" <?= $_sb_page==='users-readonly.php'?'class="active"':'' ?>><i class="bi bi-people"></i> Data User</a>
        <a href="peminjaman.php" <?= $_sb_page==='peminjaman.php'?'class="active"':'' ?>><i class="bi bi-arrow-bar-right"></i> Data Peminjaman</a>
        <a href="pengembalian.php" <?= $_sb_page==='pengembalian.php'?'class="active"':'' ?>><i class="bi bi-arrow-bar-left"></i> Data Pengembalian</a>

        <div class="sb-lbl">Khusus Kepala</div>
        <a href="laporan.php" <?= $_sb_page==='laporan.php'?'class="active"':'' ?>><i class="bi bi-bar-chart-line"></i> Laporan & Statistik</a>
        <a href="saran-buku.php" <?= $_sb_page==='saran-buku.php'?'class="active"':'' ?>>
            <i class="bi bi-lightbulb"></i> Saran Buku
            <?php if($_saran_cnt>0): ?><span class="sb-badge"><?= $_saran_cnt ?></span><?php endif; ?>
        </a>
        <?php endif; ?>

        <div class="sb-lbl">Navigasi</div>
        <a href="../index.php"><i class="bi bi-house"></i> Halaman Utama</a>
        <a href="../koleksi.php"><i class="bi bi-search"></i> Cari Buku</a>
    </nav>
    <div class="sb-foot">
        <a href="../logout.php"><i class="bi bi-box-arrow-left"></i> Keluar</a>
    </div>
</aside>
