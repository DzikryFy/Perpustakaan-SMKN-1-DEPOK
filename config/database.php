<?php
$host   = 'localhost';
$dbname = 'db_perpustakaan';
$user   = 'root';
$pass   = '';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;color:#c00">
    <b>Koneksi database gagal:</b> ' . $conn->connect_error . '
    <br><small>Pastikan MySQL aktif dan database <code>db_perpustakaan</code> sudah dibuat.</small></div>');
}
$conn->set_charset('utf8mb4');

// ── Helper: role checks ──────────────────────────────────────
function isLoggedIn()    { return isset($_SESSION['id_user']); }
function getRole()       { return $_SESSION['role'] ?? ''; }
function isPustakawan()  { return getRole() === 'pustakawan'; }
function isKepala()      { return getRole() === 'kepala_perpustakaan'; }
function isStaff()       { return isPustakawan() || isKepala(); }
function isAdmin()       { return isStaff(); }
function canBorrow()     { $r=getRole(); return in_array($r,['siswa','guru','pustakawan']); }
function canCRUD()       { return isPustakawan(); }

// ── Helper: redirects ────────────────────────────────────────
function redirect($url) { header("Location: $url"); exit; }

function requireLogin($back='../login.php') {
    if (!isLoggedIn()) redirect($back);
}
function requireStaff() {       // pustakawan ATAU kepala
    requireLogin('../login.php');
    if (!isStaff()) redirect('../index.php');
}
function requirePustakawan() {  // hanya pustakawan
    requireLogin('../login.php');
    if (!isPustakawan()) redirect('dashboard.php?err=akses');
}

// ── Helper: sanitize ─────────────────────────────────────────
function sanitize($str) {
    global $conn;
    return $conn->real_escape_string(trim(strip_tags($str)));
}

// ── Helper: format rupiah ────────────────────────────────────
function rupiah($n) { return 'Rp ' . number_format($n,0,',','.'); }

// ── Helper: hitung denda ─────────────────────────────────────
// Rp 1.000 / hari keterlambatan
define('DENDA_PER_HARI', 1000);
define('BATAS_PINJAM_HARI', 7);   // batas kembali default

function hitungDenda(string $batas_kembali, string $tgl_kembali): int {
    $b = new DateTime($batas_kembali);
    $k = new DateTime($tgl_kembali);
    $selisih = $b->diff($k);
    if ($k <= $b) return 0;
    return $selisih->days * DENDA_PER_HARI;
}

// ── Helper: role label ────────────────────────────────────────
function roleLabel($role): string {
    return match($role) {
        'pustakawan'          => 'Pustakawan',
        'kepala_perpustakaan' => 'Kepala Perpustakaan',
        'guru'                => 'Guru',
        'siswa'               => 'Siswa',
        default               => ucfirst($role),
    };
}

// ── Helper: gambar buku ───────────────────────────────────────
function coverBuku(?string $gambar, string $kategori, string $size='card'): string {
    $map = [
        'sastra'   => ['emoji'=>'✍️','gradient'=>'135deg,#1f0d2d,#3d1a5a'],
        'motivasi' => ['emoji'=>'🚀','gradient'=>'135deg,#2d1f00,#5a3c00'],
        'religi'   => ['emoji'=>'🕌','gradient'=>'135deg,#0d2d1a,#1a5a3c'],
        'umum'     => ['emoji'=>'📖','gradient'=>'135deg,#0d2540,#1a3d60'],
    ];
    $k  = strtolower($kategori);
    $cm = $map[$k] ?? ['emoji'=>'📚','gradient'=>'135deg,#1a1f2d,#2d3a4a'];

    if (!empty($gambar) && file_exists(__DIR__.'/../img/buku/'.$gambar)) {
        $path = ($size==='admin' ? '../' : '') . 'img/buku/'.htmlspecialchars($gambar);
        if ($size === 'card') {
            return '<img src="'.$path.'" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">';
        }
        return '<img src="'.$path.'" alt="" style="width:100%;height:200px;object-fit:cover;border-radius:10px;">';
    }
    $h = $size==='small' ? '90px' : ($size==='admin'?'120px':'150px');
    return '<div style="width:100%;height:'.$h.';background:linear-gradient('.$cm['gradient'].');display:flex;align-items:center;justify-content:center;font-size:2.4rem;">'.$cm['emoji'].'</div>';
}
