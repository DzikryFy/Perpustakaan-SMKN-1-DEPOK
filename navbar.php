<?php
// navbar.php — dipanggil setelah session_start() & require config
$loggedIn = isLoggedIn();
$namaUser = $_SESSION['nama'] ?? '';
$roleUser = $_SESSION['role'] ?? '';
$inisial  = $loggedIn ? strtoupper(substr($namaUser, 0, 1)) : '';
$base     = $base ?? '';
$_isStaff = $loggedIn && in_array($roleUser, ['pustakawan','kepala_perpustakaan']);
$_canBorrow = $loggedIn && in_array($roleUser, ['siswa','guru','pustakawan']);
?>
<nav class="navbar navbar-expand-lg sticky-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $base ?>index.php">
            <div class="brand-icon"><img src="img/logo-onedek.png" alt="" style="width: 30px;"></div>
            <div><div class="brand-name">E-Library</div><div class="brand-sub">SMKN 1 DEPOK</div></div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="<?= $base ?>index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $base ?>koleksi.php">Koleksi Buku</a></li>
                <?php if ($_canBorrow): ?>
                <li class="nav-item"><a class="nav-link" href="<?= $base ?>peminjaman-buku.php">Peminjaman</a></li>
                <?php endif; ?>
                <?php if ($loggedIn && !$_isStaff): ?>
                <li class="nav-item"><a class="nav-link" href="<?= $base ?>saran-buku.php">Saran Buku</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="<?= $base ?>index.php#tata-tertib">Tata Tertib</a></li>
            </ul>
            <div class="d-flex gap-2 align-items-center">
            <?php if ($loggedIn): ?>
                <?php if ($_isStaff): ?>
                <a href="<?= $base ?>admin/dashboard.php" class="btn btn-outline-gold btn-sm">
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                </a>
                <?php endif; ?>
                <div class="dropdown">
                    <button class="user-nav-chip dropdown-toggle" data-bs-toggle="dropdown">
                        <div class="nav-avatar"><?= $inisial ?></div>
                        <div class="nav-user-info">
                            <span class="nav-user-name"><?= htmlspecialchars($namaUser) ?></span>
                            <span class="nav-user-role"><?= roleLabel($roleUser) ?></span>
                        </div>
                        <i class="bi bi-chevron-down nav-chevron"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end nav-dropdown">
                        <li class="dropdown-header-custom">
                            <div class="dh-avatar"><?= $inisial ?></div>
                            <div>
                                <div class="dh-name"><?= htmlspecialchars($namaUser) ?></div>
                                <div class="dh-role"><?= roleLabel($roleUser) ?></div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider-custom"></li>
                        <?php if ($_canBorrow): ?>
                        <li><a class="dropdown-item-custom" href="<?= $base ?>peminjaman-buku.php"><i class="bi bi-bookmark-plus"></i> Peminjaman Saya</a></li>
                        <li><a class="dropdown-item-custom" href="<?= $base ?>riwayat-peminjaman.php"><i class="bi bi-clock-history"></i> Riwayat Pinjaman</a></li>
                        <?php endif; ?>
                        <?php if ($loggedIn && !$_isStaff): ?>
                        <li><a class="dropdown-item-custom" href="<?= $base ?>saran-buku.php"><i class="bi bi-lightbulb"></i> Saran Buku</a></li>
                        <?php endif; ?>
                        <?php if ($_isStaff): ?>
                        <li><hr class="dropdown-divider-custom"></li>
                        <li><a class="dropdown-item-custom" href="<?= $base ?>admin/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                        <?php if ($roleUser === 'kepala_perpustakaan'): ?>
                        <li><a class="dropdown-item-custom" href="<?= $base ?>admin/laporan.php"><i class="bi bi-bar-chart-line"></i> Laporan</a></li>
                        <?php endif; ?>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider-custom"></li>
                        <li><a class="dropdown-item-custom text-danger-custom" href="<?= $base ?>logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="<?= $base ?>login.php" class="btn btn-gold">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                </a>
            <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
