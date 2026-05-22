<?php
session_start();
require_once 'config/database.php';

$loggedIn = isLoggedIn();
$namaUser = $_SESSION['nama'] ?? '';
$roleUser = $_SESSION['role'] ?? '';
$base     = '';

// Filter & search
$q   = sanitize($_GET['q'] ?? '');
$kat = sanitize($_GET['kat'] ?? '');

$where = 'WHERE 1';
if ($q)   $where .= " AND nama_buku LIKE '%$q%'";
if ($kat) $where .= " AND kategori = '$kat'";

$buku_list = $conn->query("SELECT * FROM buku $where ORDER BY nama_buku ASC");

// Hitung per kategori untuk badge
$kat_counts = [];
$kc = $conn->query("SELECT kategori, COUNT(*) AS total FROM buku GROUP BY kategori");
while ($r = $kc->fetch_assoc()) $kat_counts[$r['kategori']] = $r['total'];
$total_buku = array_sum($kat_counts);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Koleksi Buku | E-Library SMKN 1 Depok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Page header */
        .page-header {
            padding: 52px 0 40px;
            background: linear-gradient(180deg, rgba(232,184,109,0.05) 0%, transparent 100%);
            border-bottom: 1px solid var(--border);
            text-align: center;
        }
        .page-header h1 { font-family:'Playfair Display',serif; font-size:2.2rem; color:#fff; margin-bottom:8px; }
        .page-header p  { color:var(--muted); font-size:0.9rem; }

        /* Search bar */
        .search-section { padding: 28px 0 0; }
        .search-box {
            display: flex; gap: 10px; max-width: 560px; margin: 0 auto;
        }
        .search-input-main {
            flex: 1;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.12);
            color: var(--text); border-radius: 12px;
            padding: 12px 18px; font-size: 0.9rem;
            font-family: 'DM Sans', sans-serif;
            transition: border .2s;
        }
        .search-input-main:focus { outline:none; border-color:var(--accent); background:rgba(255,255,255,0.07); }
        .search-input-main::placeholder { color:var(--muted); }
        .btn-search { background:linear-gradient(135deg,var(--accent),#d4a54e); border:none; border-radius:12px; padding:12px 22px; color:#0c1520; font-weight:600; cursor:pointer; transition:opacity .2s; }
        .btn-search:hover { opacity:.88; }

        /* Filter tabs */
        .filter-tabs { display:flex; gap:8px; flex-wrap:wrap; justify-content:center; padding:20px 0 32px; }
        .filter-tab {
            padding:7px 18px; border-radius:20px; font-size:0.82rem; font-weight:500;
            border:1px solid var(--border); color:var(--muted); text-decoration:none;
            transition:all .2s; display:flex; align-items:center; gap:6px;
        }
        .filter-tab:hover { border-color:var(--accent); color:var(--accent); background:rgba(232,184,109,0.07); }
        .filter-tab.active { background:var(--accent); border-color:var(--accent); color:#0c1520; font-weight:600; }
        .filter-tab .cnt { font-size:0.7rem; background:rgba(0,0,0,0.15); border-radius:10px; padding:1px 7px; }
        .filter-tab.active .cnt { background:rgba(0,0,0,0.2); }

        /* Buku grid */
        .buku-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:18px; padding-bottom:60px; }

        /* Buku card */
        .buku-card {
            background:var(--card); border:1px solid var(--border); border-radius:14px;
            overflow:hidden; transition:transform .22s, border-color .22s;
            display:flex; flex-direction:column;
        }
        .buku-card:hover { transform:translateY(-5px); border-color:rgba(232,184,109,0.25); }
        .buku-cover {
            height: 160px;
            display:flex; align-items:center; justify-content:center;
            font-size: 52px;
            position: relative; overflow:hidden;
        }
        .buku-cover img { width:100%;height:100%;object-fit:cover;display:block;position:absolute;inset:0; }
        .buku-cover.c-umum     { background: linear-gradient(135deg,#0d2540,#1a3d60); }
        .buku-cover.c-motivasi { background: linear-gradient(135deg,#2d1f00,#5a3c00); }
        .buku-cover.c-sastra   { background: linear-gradient(135deg,#1f0d2d,#3d1a5a); }
        .buku-cover.c-religi   { background: linear-gradient(135deg,#0d2d1a,#1a5a3c); }
        .buku-cover.c-default  { background: linear-gradient(135deg,#1a1f2d,#2d3a4a); }
        .stock-badge {
            position:absolute; top:10px; right:10px;
            padding:3px 9px; border-radius:20px; font-size:0.68rem; font-weight:600;
        }
        .stock-ada    { background:rgba(58,171,110,0.2); color:#5dd896; border:1px solid rgba(58,171,110,0.3); }
        .stock-habis  { background:rgba(232,93,93,0.2);  color:#ff9090; border:1px solid rgba(232,93,93,0.3); }
        .buku-body { padding:16px; flex:1; display:flex; flex-direction:column; }
        .buku-kat {
            font-size:0.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em;
            margin-bottom:7px; display:inline-block;
        }
        .buku-kat.ck-umum     { color:var(--blue); }
        .buku-kat.ck-motivasi { color:var(--accent); }
        .buku-kat.ck-sastra   { color:var(--purple); }
        .buku-kat.ck-religi   { color:var(--green); }
        .buku-kat.ck-default  { color:var(--muted); }
        .buku-judul { font-size:0.9rem; font-weight:600; color:#fff; line-height:1.35; margin-bottom:4px; flex:1; }
        .buku-stok-info { font-size:0.75rem; color:var(--muted); margin-bottom:14px; }
        .btn-detail {
            display:block; text-align:center; padding:9px; border-radius:9px;
            font-size:0.82rem; font-weight:600; text-decoration:none;
            border:1px solid rgba(232,184,109,0.3); color:var(--accent);
            transition:all .2s;
        }
        .btn-detail:hover { background:rgba(232,184,109,0.1); border-color:var(--accent); color:var(--accent); }

        /* Buku card - tambahan */
        .bos-badge {
            position:absolute; bottom:10px; left:10px;
            font-size:0.6rem; font-weight:700; text-transform:uppercase;
            background:rgba(232,184,109,0.18); color:var(--accent);
            border:1px solid rgba(232,184,109,0.3); border-radius:6px;
            padding:2px 7px; letter-spacing:.04em;
        }
        .buku-penerbit {
            font-size:0.72rem; color:var(--muted); margin-bottom:6px;
            white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
        }
        .buku-meta-row {
            display:flex; gap:12px; font-size:0.72rem; color:var(--muted); margin-bottom:14px;
        }

        /* Empty state */
        .empty-state { text-align:center; padding:60px 20px; color:var(--muted); }
        .empty-state i { font-size:3rem; display:block; margin-bottom:14px; opacity:.4; }
        .empty-state h3 { color:var(--text); font-size:1.1rem; margin-bottom:6px; }

        /* Result info */
        .result-info { color:var(--muted); font-size:0.83rem; margin-bottom:20px; }
        .result-info strong { color:var(--accent); }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="sec-label mb-2">Perpustakaan Digital</div>
        <h1>Koleksi Buku</h1>
        <p>Temukan dan pinjam buku dari koleksi perpustakaan SMKN 1 Depok</p>
    </div>
</div>

<div class="container">
    <!-- Search -->
    <div class="search-section">
        <form method="GET" class="search-box">
            <?php if($kat): ?><input type="hidden" name="kat" value="<?= htmlspecialchars($kat) ?>"><?php endif; ?>
            <input type="text" name="q" class="search-input-main"
                   placeholder="Cari judul buku..." value="<?= htmlspecialchars($q) ?>">
            <button type="submit" class="btn-search"><i class="bi bi-search me-1"></i> Cari</button>
        </form>
    </div>

    <!-- Filter Kategori -->
    <div class="filter-tabs">
        <?php
        $tabs = [
            ''         => ['label'=>'Semua', 'icon'=>'bi-grid'],
            'Umum'     => ['label'=>'Umum',     'icon'=>'bi-book-half'],
            'Motivasi' => ['label'=>'Motivasi', 'icon'=>'bi-rocket-takeoff'],
            'Sastra'   => ['label'=>'Sastra',   'icon'=>'bi-pen'],
            'Religi'   => ['label'=>'Religi',   'icon'=>'bi-moon-stars'],
        ];
        foreach ($tabs as $val => $t):
            $isActive = ($kat === $val);
            $cnt = $val === '' ? $total_buku : ($kat_counts[$val] ?? 0);
            $url = '?kat='.urlencode($val).($q?'&q='.urlencode($q):'');
        ?>
        <a href="<?= $url ?>" class="filter-tab <?= $isActive?'active':'' ?>">
            <i class="bi <?= $t['icon'] ?>"></i> <?= $t['label'] ?>
            <span class="cnt"><?= $cnt ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Result info -->
    <?php if ($q || $kat): ?>
    <p class="result-info">
        Menampilkan <strong><?= $buku_list->num_rows ?></strong> hasil
        <?= $q ? 'untuk "<strong>'.htmlspecialchars($q).'</strong>"' : '' ?>
        <?= $kat ? 'kategori <strong>'.htmlspecialchars($kat).'</strong>' : '' ?>
        — <a href="koleksi.php" style="color:var(--accent);text-decoration:none">Reset filter</a>
    </p>
    <?php endif; ?>

    <!-- Grid Buku -->
    <?php
    $cover_map = [
        'umum'     => ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','class'=>'c-umum'],
        'motivasi' => ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','class'=>'c-motivasi'],
        'sastra'   => ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','class'=>'c-sastra'],
        'religi'   => ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','class'=>'c-religi'],
    ];
    if ($buku_list->num_rows > 0):
    ?>
    <div class="buku-grid">
        <?php while ($b = $buku_list->fetch_assoc()):
            $katKey = strtolower($b['kategori']);
            $cm     = $cover_map[$katKey] ?? ['emoji'=>'<img src="img/logo-wandek.png" alt="" style="width:42px;opacity:.8;position:relative;z-index:1;filter:drop-shadow(0 2px 6px rgba(0,0,0,.4));">','class'=>'c-default'];
        ?>
        <div class="buku-card">
            <div class="buku-cover <?= $cm['class'] ?>" data-book-title="<?= htmlspecialchars($b['nama_buku'] ?? '') ?>">
                <?php
                    $imgName = basename($b['gambar'] ?? '');
                    if ($imgName && file_exists('img/buku/'.$imgName)):
                ?>
                    <img src="img/buku/<?= htmlspecialchars($imgName) ?>" alt="<?= htmlspecialchars($b['nama_buku']) ?>">
                <?php else: ?>
                    <?= $cm['emoji'] ?>
                <?php endif; ?>
                <span class="stock-badge <?= $b['stock']>0?'stock-ada':'stock-habis' ?>">
                    <?= $b['stock']>0 ? 'Stok: '.$b['stock'] : 'Habis' ?>
                </span>
                <?php if (!empty($b['no_bos'])): ?>
                <span class="bos-badge">BOS <?= htmlspecialchars($b['no_bos']) ?></span>
                <?php endif; ?>
            </div>
            <div class="buku-body">
                <span class="buku-kat ck-<?= $katKey ?>"><?= htmlspecialchars($b['kategori']) ?></span>
                <div class="buku-judul"><?= htmlspecialchars($b['nama_buku']) ?></div>
                <?php if (!empty($b['penerbit'])): ?>
                <div class="buku-penerbit"><i class="bi bi-building me-1"></i><?= htmlspecialchars($b['penerbit']) ?></div>
                <?php endif; ?>
                <div class="buku-meta-row">
                    <span><i class="bi bi-layers me-1"></i><?= $b['stock'] ?> eks</span>
                    <?php if (!empty($b['tahun_cetak'])): ?>
                    <span><i class="bi bi-calendar3 me-1"></i><?= $b['tahun_cetak'] ?></span>
                    <?php endif; ?>
                </div>
                <a href="detail_buku.php?id=<?= $b['id_buku'] ?>" class="btn-detail">
                    <i class="bi bi-eye me-1"></i> Lihat Detail
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="bi bi-search"></i>
        <h3>Buku tidak ditemukan</h3>
        <p>Coba kata kunci lain atau reset filter pencarian.</p>
    </div>
    <?php endif; ?>
</div>

<footer class="site-footer" style="margin-top:0">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand"><span class="footer-icon"><img src="img/logo-wandek.png" alt="" style="width:44px;opacity:.75;position:relative;z-index:1;"></span> E-Library SMKN 1 Depok</div>
            <div class="footer-copy">&copy; <?= date('Y') ?> E-Library Project. All Rights Reserved.</div>
            <div class="footer-links">
                <?php if ($loggedIn): ?>
                <a href="logout.php">Keluar</a>
                <?php else: ?>
                <a href="login.php">Masuk</a><a href="register.php">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.addEventListener('scroll',()=>{document.getElementById('mainNav').classList.toggle('scrolled',window.scrollY>30);});
</script>

<script>
// Google Books cover fetcher
(function(){
    function fetchCover(div){
        const title=div.dataset.bookTitle;
        if(!title) return;
        const ph=div.querySelector('img.logo-ph, span.book-emoji');
        if(!ph) return;
        fetch('https://www.googleapis.com/books/v1/volumes?q='+encodeURIComponent(title)+'&maxResults=1')
        .then(r=>r.json()).then(d=>{
            const thumb=d.items?.[0]?.volumeInfo?.imageLinks?.thumbnail;
            if(thumb){
                const img=document.createElement('img');
                img.src=thumb.replace('http:','https:');
                img.style.cssText='width:100%;height:100%;object-fit:cover;position:absolute;inset:0;';
                img.onerror=()=>img.remove();
                ph.replaceWith ? ph.replaceWith(img) : ph.parentNode.insertBefore(img,ph);
            }
        }).catch(()=>{});
    }
    const divs=document.querySelectorAll('[data-book-title]');
    divs.forEach((div,i)=>setTimeout(()=>fetchCover(div),i*150));
})();
</script>
</body>
</html>
