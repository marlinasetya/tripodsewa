<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Fungsi cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi cek role
function checkRole($roles) {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
    if (!in_array($_SESSION['role'], (array)$roles)) {
        header('Location: ../index.php');
        exit;
    }
}

// Fungsi redirect berdasarkan role
function redirectByRole() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    $role = $_SESSION['role'];
    if ($role === 'admin') {
        header('Location: admin/');
    } elseif ($role === 'petugas') {
        header('Location: petugas/');
    } else {
        header('Location: user/');
    }
    exit;
}

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Upload gambar alat
function uploadFoto($file) {
    $target_dir = "../uploads/";
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $target_file = $target_dir . uniqid() . '.' . $file_extension;
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    
    if (!in_array($file_extension, $allowed)) {
        return false;
    }
    
    // No size limit per user request
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return basename($target_file);
    }
    return false;
}

// Hitung total harga
function calcTotalHarga($harga_per_hari, $jumlah_hari, $jumlah_unit = 1) {
    return $harga_per_hari * $jumlah_hari * $jumlah_unit;
}

// Validasi stok tersedia
function validateStok($pdo, $id_alat, $jumlah) {
    $stmt = $pdo->prepare("SELECT stok FROM alat WHERE id_alat = ?");
    $stmt->execute([$id_alat]);
    $alat = $stmt->fetch();
    return $alat && $alat['stok'] >= $jumlah;
}

// Validasi untuk cacat - if stok_cacat >0, allow 'menunggu', else check normal stok
function validateStokCacat($pdo, $id_alat, $jumlah) {
    $stmt = $pdo->prepare("SELECT stok, stok_cacat FROM alat WHERE id_alat = ?");
    $stmt->execute([$id_alat]);
    $alat = $stmt->fetch();
    return $alat && ($alat['stok_cacat'] >= $jumlah || $alat['stok'] >= $jumlah);
}

// Hitung denda terlambat: 100 * hari * jumlah
function calcDendaTerlambat($pdo, $id_peminjaman) {
    $stmt = $pdo->prepare("
        SELECT p.jumlah, p.tanggal_kembali, p.tanggal_dikembalikan 
        FROM peminjaman p WHERE id_peminjaman = ?
    ");
    $stmt->execute([$id_peminjaman]);
    $pinjam = $stmt->fetch();
    if (!$pinjam || !$pinjam['tanggal_dikembalikan']) return 0;
    
    $hari_terlambat = max(0, (strtotime($pinjam['tanggal_dikembalikan']) - strtotime($pinjam['tanggal_kembali'])) / 86400);
    return 100000 * $hari_terlambat * $pinjam['jumlah'];
}

// Handle multi gallery upload, return JSON array paths
function handleGalleryUpload($files, $type = 'utripot') { // type: utripot or booth
    $gallery = [];
    $target_dir = "../uploads/gallery/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    foreach ($files['name'] as $key => $name) {
        if ($files['error'][$key] === 0) { // No size limit
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','mp4','mov','avi'])) { // photos + videos
                $filename = uniqid() . '.' . $ext;
                $target_file = $target_dir . $filename;
                if (move_uploaded_file($files['tmp_name'][$key], $target_file)) {
                    $gallery[] = 'gallery/' . $filename;
                }
            }
        }
    }
    return json_encode($gallery);
}

// Get riwayat
function getRiwayatPeminjaman($pdo, $limit = 50) {
    $stmt = $pdo->query("SELECT * FROM riwayat_peminjaman ORDER BY created_at DESC LIMIT $limit");
    return $stmt->fetchAll();
}


// Generate HTML struk untuk print
function generateStrukHTML($peminjaman) {
    $html = '<div class="print-area p-4" style="max-width: 300px; font-size: 12px; line-height: 1.4;">';
    $html .= '<div style="text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px;">';
    $html .= '<h4>Sewa Tripod Pro</h4><small>Struk Peminjaman #' . $peminjaman['id_peminjaman'] . '</small>';
    $html .= '</div>';
    $html .= '<table style="width: 100%;">';
    $html .= '<tr><td><strong>No Item:</strong></td><td>' . $peminjaman['no_alat'] . '</td></tr>';
    $html .= '<tr><td><strong>User:</strong></td><td>' . $peminjaman['nama_user'] . '</td></tr>';
    $html .= '<tr><td><strong>Tgl Sewa:</strong></td><td>' . date('d/m/Y', strtotime($peminjaman['tanggal_sewa'])) . '</td></tr>';
    $html .= '<tr><td><strong>Tgl Kembali:</strong></td><td>' . date('d/m/Y', strtotime($peminjaman['tanggal_kembali'])) . '</td></tr>';
    $html .= '<tr><td><strong>Jumlah:</strong></td><td>' . $peminjaman['jumlah'] . '</td></tr>';
    $html .= '<tr><td><strong>Total:</strong></td><td>Rp ' . number_format($peminjaman['total_harga']) . '</td></tr>';
    if ($peminjaman['denda'] > 0) {
        $html .= '<tr><td><strong>Denda:</strong></td><td>Rp ' . number_format($peminjaman['denda']) . '</td></tr>';
    }
    $html .= '</table>';
    $html .= '<div style="text-align: center; margin-top: 20px; font-size: 10px;">Terima kasih!</div>';
    $html .= '</div>';
    return $html;
}

// Format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka ?? 0, 0, ',', '.');
}

// Cart functions
function initCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function addToCart($id_alat, $pdo, $qty = 1) {
    initCart();
    if (!validateStok($pdo, $id_alat, $qty)) return false;
    
    if (isset($_SESSION['cart'][$id_alat])) {
        $_SESSION['cart'][$id_alat]['qty'] += $qty;
    } else {
        $stmt = $pdo->prepare("SELECT harga_per_hari FROM alat WHERE id_alat = ?");
        $stmt->execute([$id_alat]);
        $alat = $stmt->fetch();
        $_SESSION['cart'][$id_alat] = ['qty' => $qty, 'harga_per_hari' => $alat['harga_per_hari']];
    }
    return true;
}

function getCart() {
    initCart();
    return $_SESSION['cart'];
}

function updateCartQty($id_alat, $qty, $pdo) {
    initCart();
    if ($qty <= 0) {
        unset($_SESSION['cart'][$id_alat]);
    } else if (isset($_SESSION['cart'][$id_alat]) && validateStok($pdo, $id_alat, $qty)) {
        $_SESSION['cart'][$id_alat]['qty'] = $qty;
    }
}

function getCartTotal($pdo) {
    $total = 0;
    $cart = getCart();
    foreach ($cart as $id => $item) {
        $total += $item['qty'] * $item['harga_per_hari'];
    }
    return $total;
}

function emptyCart() {
    unset($_SESSION['cart']);
}
?>


