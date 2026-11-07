<?php
session_start();
require_once '../config/database.php';
requireKepala(); // hanya kepala perpustakaan
$base='../'; $loggedIn=true; $namaUser=$_SESSION['nama']??''; $roleUser=$_SESSION['role']??'';
$msg=$error=''; $canEdit=isKepala(); // kepala menyetujui saran

if($canEdit && isset($_GET['status']) && isset($_GET['id'])){
    $sid=(int)$_GET['id']; $st=in_array($_GET['status'],['ditinjau','disetujui','ditolak'])?$_GET['status']:'';
    if($st) $conn->query("UPDATE saran_buku SET status='$st' WHERE id_saran=$sid");
    $msg='Status saran diperbarui.';
}

$filter=$_GET['filter']??'semua';
$where=$filter==='semua'?'':($filter==='baru'?"WHERE s.status='baru'":(in_array($filter,['ditinjau','disetujui','ditolak'])?"WHERE s.status='$filter'":''));
$list=$conn->query("SELECT s.*,u.nama AS nama_user,u.role FROM saran_buku s JOIN users u ON s.id_user=u.id_user $where ORDER BY s.id_saran DESC");
$cnt_baru=$conn->query("SELECT COUNT(*) c FROM saran_buku WHERE status='baru'")->fetch_assoc()['c'];
?><!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><title>Saran Buku | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="../style.css">
<style>
body{display:flex;min-height:100vh;}.admin-wrap{display:flex;width:100%;min-height:100vh;}.main-content{flex:1;padding:32px;overflow-x:hidden;}
.page-title{font-family:'Playfair Display',serif;font-size:1.7rem;color:#fff;margin-bottom:4px;}
.ftab{display:flex;gap:8px;margin-bottom:22px;flex-wrap:wrap;}
.ftab a{padding:7px 16px;border-radius:20px;font-size:.81rem;border:1px solid var(--border);color:var(--muted);text-decoration:none;transition:all .2s;}
.ftab a:hover,.ftab a.active{background:var(--accent);border-color:var(--accent);color:#0c1520;font-weight:600;}
.saran-card{background:var(--card);border:1px solid var(--border);border-radius:13px;padding:18px;margin-bottom:12px;}
.saran-card:hover{border-color:rgba(232,184,109,.2);}
.badge-baru{background:rgba(232,184,109,.12);color:var(--accent);border:1px solid rgba(232,184,109,.2);border-radius:12px;font-size:.67rem;padding:3px 10px;font-weight:600;}
.badge-ditinjau{background:rgba(74,158,255,.12);color:var(--blue);border:1px solid rgba(74,158,255,.2);border-radius:12px;font-size:.67rem;padding:3px 10px;font-weight:600;}
.badge-ok{background:rgba(58,171,110,.12);color:var(--green);border:1px solid rgba(58,171,110,.2);border-radius:12px;font-size:.67rem;padding:3px 10px;font-weight:600;}
.badge-no{background:rgba(232,93,93,.1);color:#ff9090;border:1px solid rgba(232,93,93,.2);border-radius:12px;font-size:.67rem;padding:3px 10px;font-weight:600;}
.action-btn{font-size:.75rem;border:none;border-radius:7px;padding:5px 12px;cursor:pointer;font-weight:600;text-decoration:none;transition:all .2s;}
.ab-tinjau{background:rgba(74,158,255,.12);color:var(--blue);}
.ab-ok{background:rgba(58,171,110,.12);color:var(--green);}
.ab-no{background:rgba(232,93,93,.1);color:#ff9090;}
.alert-ok{background:rgba(58,171,110,.1);border:1px solid rgba(58,171,110,.3);color:#5dd896;border-radius:10px;padding:11px 15px;margin-bottom:16px;font-size:.84rem;}
.empty-state{text-align:center;padding:48px;color:var(--muted);}
.empty-state i{font-size:2.2rem;display:block;margin-bottom:10px;opacity:.3;}
</style>
</head><body>
<?php include 'sidebar.php'; ?>
<div class="admin-wrap"><div class="main-content">
<div style="margin-bottom:24px;">
    <div class="sec-label" style="margin-bottom:4px;">Masukan Anggota</div>
    <h1 class="page-title">Saran Buku</h1>
    <p style="color:var(--muted);font-size:.82rem;margin-top:4px;"><?= $cnt_baru ?> saran baru belum ditinjau</p>
</div>
<?php if($msg): ?><div class="alert-ok"><i class="bi bi-check-circle-fill me-2"></i><?= $msg ?></div><?php endif; ?>

<div class="ftab">
    <a href="?filter=semua"    class="<?= $filter==='semua'   ?'active':'' ?>">Semua</a>
    <a href="?filter=baru"     class="<?= $filter==='baru'    ?'active':'' ?>">Baru <span style="font-size:.72rem;background:rgba(232,93,93,.2);color:#ff9090;border-radius:10px;padding:0 7px;"><?= $cnt_baru ?></span></a>
    <a href="?filter=ditinjau" class="<?= $filter==='ditinjau'?'active':'' ?>">Ditinjau</a>
    <a href="?filter=disetujui"class="<?= $filter==='disetujui'?'active':'' ?>">Disetujui</a>
    <a href="?filter=ditolak"  class="<?= $filter==='ditolak' ?'active':'' ?>">Ditolak</a>
</div>

<?php if($list->num_rows>0): while($r=$list->fetch_assoc()):
    $bs=['baru'=>'badge-baru','ditinjau'=>'badge-ditinjau','disetujui'=>'badge-ok','ditolak'=>'badge-no'];
    $bl=['baru'=>'Baru','ditinjau'=>'Ditinjau','disetujui'=>'Disetujui','ditolak'=>'Ditolak'];
    $st=$r['status'];
?>
<div class="saran-card">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap;">
                <span style="font-size:1rem;font-weight:700;color:#fff;"><?= htmlspecialchars($r['judul_saran']) ?></span>
                <span class="<?= $bs[$st]??'badge-baru' ?>"><?= $bl[$st]??$st ?></span>
            </div>
            <?php if($r['penulis_saran']): ?><div style="font-size:.78rem;color:var(--muted);margin-bottom:3px;"><i class="bi bi-pen me-1"></i>Penulis: <?= htmlspecialchars($r['penulis_saran']) ?></div><?php endif; ?>
            <?php if($r['penerbit_saran']): ?><div style="font-size:.78rem;color:var(--muted);margin-bottom:3px;"><i class="bi bi-building me-1"></i>Penerbit: <?= htmlspecialchars($r['penerbit_saran']) ?></div><?php endif; ?>
            <?php if($r['alasan']): ?><div style="font-size:.8rem;color:var(--text);margin-top:8px;padding:10px;background:rgba(255,255,255,.03);border-radius:8px;border-left:3px solid var(--accent);font-style:italic;">"<?= htmlspecialchars($r['alasan']) ?>"</div><?php endif; ?>
            <div style="font-size:.72rem;color:var(--muted);margin-top:10px;">
                <i class="bi bi-person me-1"></i><?= htmlspecialchars($r['nama_user']) ?> (<?= roleLabel($r['role']) ?>)
                &nbsp;·&nbsp; <i class="bi bi-calendar3 me-1"></i><?= date('d M Y',strtotime($r['tanggal_saran'])) ?>
            </div>
        </div>
        <?php if($canEdit): ?>
        <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0;">
            <?php if($st==='baru'): ?><a href="?id=<?= $r['id_saran'] ?>&status=ditinjau&filter=<?= $filter ?>" class="action-btn ab-tinjau"><i class="bi bi-eye me-1"></i>Tinjau</a><?php endif; ?>
            <?php if($st!=='disetujui'): ?><a href="?id=<?= $r['id_saran'] ?>&status=disetujui&filter=<?= $filter ?>" class="action-btn ab-ok"><i class="bi bi-check-lg me-1"></i>Setujui</a><?php endif; ?>
            <?php if($st!=='ditolak'): ?><a href="?id=<?= $r['id_saran'] ?>&status=ditolak&filter=<?= $filter ?>" class="action-btn ab-no" onclick="return confirm('Tolak saran ini?')"><i class="bi bi-x-lg me-1"></i>Tolak</a><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endwhile; else: ?>
<div class="empty-state"><i class="bi bi-lightbulb"></i><p>Tidak ada saran buku untuk ditampilkan.</p></div>
<?php endif; ?>
</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
