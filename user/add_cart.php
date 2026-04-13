<?php
require_once '../includes/functions.php';
checkRole('user');

if (!isset($_POST['id_alat']) || !isset($_POST['qty'])) {
    $_SESSION['error'] = 'Data tidak lengkap!';
    header('Location: alat.php');
    exit;
}

$id_alat = (int)$_POST['id_alat'];
$qty = (int)$_POST['qty'];

if (addToCart($id_alat, $pdo, $qty)) {
    $_SESSION['success'] = "$qty item ditambahkan ke keranjang!";
} else {
    $_SESSION['error'] = 'Gagal tambah ke keranjang! Stok tidak cukup.';
}

header('Location: ' . ($_POST['return_url'] ?? 'alat.php'));
exit;
?>

