<?php
require_once '../includes/functions.php';
checkRole('user');

if (!isset($_POST['id_alat']) || !isset($_POST['tanggal_sewa']) || !isset($_POST['tanggal_kembali'])) {
    header('Location: alat.php');
    exit;
}

$id_alat = (int)$_POST['id_alat'];
$tanggal_sewa = $_POST['tanggal_sewa'];
$tanggal_kembali = $_POST['tanggal_kembali'];
$jumlah = (int)($_POST['jumlah'] ?? 1);

$stmt = $pdo->prepare("SELECT * FROM alat WHERE id_alat = ?");
$stmt->execute([$id_alat]);
$alat = $stmt->fetch();

if (!$alat || $alat['stok'] < $jumlah) {
    $_SESSION['error'] = 'Stok tidak mencukupi!';
    header('Location: alat_detail.php?id=' . $id_alat);
    exit;
}

if (strtotime($tanggal_kembali) <= strtotime($tanggal_sewa)) {
    $_SESSION['error'] = 'Tanggal kembali harus setelah tanggal sewa!';
    header('Location: alat_detail.php?id=' . $id_alat);
    exit;
}

$start = new DateTime($tanggal_sewa);
$end = new DateTime($tanggal_kembali);
$hari = $start->diff($end)->days + 1;
$total_harga = $alat['harga_per_hari'] * $hari * $jumlah;

// Insert peminjaman dengan status 'menunggu' - admin akan approve & kurangi stok
$stmt = $pdo->prepare("
    INSERT INTO peminjaman (id_user, id_alat, jumlah, harga_per_hari, total_harga, tanggal_sewa, tanggal_kembali, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, 'menunggu')
");
if ($stmt->execute([$_SESSION['user_id'], $id_alat, $jumlah, $alat['harga_per_hari'], $total_harga, $tanggal_sewa, $tanggal_kembali])) {
    $_SESSION['success'] = 'Permintaan sewa berhasil! Menunggu persetujuan admin.';
} else {
    $_SESSION['error'] = 'Gagal membuat permintaan sewa.';
}

header('Location: peminjaman.php');
exit;
?>

