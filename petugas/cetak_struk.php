<?php
require_once '../includes/functions.php';
checkRole(['petugas']);
error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = (int)$_GET['id'] ?? 0;
if (!$id) {
    header('Location: manajemen_peminjaman.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, u.nama as nama_user, a.no_alat, a.nama_alat, k.nama_kategori 
    FROM peminjaman p 
    JOIN users u ON p.id_user = u.id_user 
    JOIN alat a ON p.id_alat = a.id_alat 
    JOIN kategori k ON a.id_kategori = k.id_kategori 
    WHERE p.id_peminjaman = ?
");
$stmt->execute([$id]);

// DEBUG BLOCK
echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;">';
echo '<h4>🔍 DEBUG INFO:</h4>';
echo '<p><strong>ID received:</strong> ' . $id . '</p>';
echo '<p><strong>Query rowCount:</strong> ' . $stmt->rowCount() . '</p>';
echo '<p><strong>PDO Error:</strong> ' . print_r($pdo->errorInfo(), true) . '</p>';

$count_p = $pdo->query('SELECT COUNT(*) FROM peminjaman')->fetchColumn();
$count_u = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$count_a = $pdo->query('SELECT COUNT(*) FROM alat')->fetchColumn();
echo '<p><strong>Table counts:</strong> peminjaman=' . $count_p . ', users=' . $count_u . ', alat=' . $count_a . '</p>';

$raw_p = $pdo->prepare('SELECT * FROM peminjaman WHERE id_peminjaman = ?');
$raw_p->execute([$id]);
$raw_data = $raw_p->fetch();
echo '<p><strong>Raw peminjaman:</strong> ' . ($raw_data ? 'EXISTS' : 'MISSING') . '</p>';

$peminjaman = $stmt->fetch();

// Fallback if no JOIN data
if (!$peminjaman && $raw_data) {
    $peminjaman = $raw_data;
    $peminjaman['nama_user'] = 'Unknown User';
    $peminjaman['no_alat'] = 'Unknown';
    $peminjaman['nama_alat'] = 'Unknown Alat';
}

if (!$peminjaman) {
    echo '<p style="color: red;"><strong>NO DATA FOUND - Check DB!</strong></p>';
    echo '</div>';
} else {
    echo '<p style="color: green;"><strong>Data loaded OK</strong></p>';
    echo '</div>';
}

// Update stok sudah dihandle di manajemen, avoid double
// if ($peminjaman['status'] == 'disetujui') {
//     $pdo->prepare("UPDATE alat SET stok = stok - ? WHERE id_alat = ?")->execute([$peminjaman['jumlah'], $peminjaman['id_alat']]);
// }



?>
<!DOCTYPE html>
<html>
<head>
    <title>Struk Peminjaman #<?php echo $peminjaman['id_peminjaman']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body { font-size: 12px; margin: 0; padding: 10px; }
            .no-print { display: none; }
        }
        body { font-family: 'Arial', sans-serif; max-width: 400px; margin: 0 auto; padding: 20px; }
        .struk-header { text-align: center; border-bottom: 3px double #333; padding-bottom: 15px; margin-bottom: 20px; }
        .struk-footer { text-align: center; margin-top: 30px; font-size: 11px; border-top: 2px solid #333; padding-top: 10px; }
        table { width: 100%; font-size: 13px; }
        th { text-align: left; padding: 5px 0; }
    </style>
</head>
<body onload="window.print()">
    <div class="struk-header">
        <h3>Sewa Tripod Pro</h3>
        <p><strong>STRUK PEMINJAMAN #<?php echo $peminjaman['id_peminjaman']; ?></strong></p>
        <p>Tanggal Cetak: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
    
    <table>
        <tr><th>No Item:</th><td><?php echo $peminjaman['no_alat']; ?></td></tr>
        <tr><th>Alat:</th><td><?php echo $peminjaman['nama_alat']; ?></td></tr>
        <tr><th>User:</th><td><?php echo $peminjaman['nama_user']; ?></td></tr>
        <tr><th>Jumlah:</th><td><?php echo $peminjaman['jumlah']; ?> unit</td></tr>
        <tr><th>Tgl Sewa:</th><td><?php echo date('d/m/Y', strtotime($peminjaman['tanggal_sewa'])); ?></td></tr>
        <tr><th>Tgl Kembali:</th><td><?php echo date('d/m/Y', strtotime($peminjaman['tanggal_kembali'])); ?></td></tr>
        <tr><th>Harga/Hari:</th><td><?php echo formatRupiah($peminjaman['harga_per_hari']); ?></td></tr>
        <tr><th>Total:</th><td><strong><?php echo formatRupiah($peminjaman['total_harga']); ?></strong></td></tr>
        <tr><th>Denda<?php echo $peminjaman['denda'] > 0 ? ($peminjaman['status'] == 'terlambat' ? ' (Terlambat)' : ' (Rusak)') : ''; ?>: </th><td><?php echo $peminjaman['denda'] > 0 ? '<strong class="text-danger">' . formatRupiah($peminjaman['denda']) . '</strong>' : formatRupiah($peminjaman['denda']); ?></td></tr>
        <?php if ($peminjaman['catatan']): ?>
        <tr><th>Catatan:</th><td><?php echo $peminjaman['catatan']; ?></td></tr>
        <tr><th>Status Akhir:</th><td><strong><?php echo ucwords(str_replace('_', ' ', $peminjaman['status'])); ?></strong></td></tr>
        <?php endif; ?>
    </table>
    
    <div class="struk-footer">
        <p><strong>Status: <?php echo ucwords(str_replace('_', ' ', $peminjaman['status'])); ?></strong></p>
        <p>Terima kasih telah menggunakan layanan kami!</p>
        <p>Kembali bersih & lengkap | Denda Rp50.000/hari keterlambatan</p>
    </div>
    
    <div class="no-print text-center mt-4">
        <a href="manajemen_peminjaman.php" class="btn btn-secondary">Kembali</a>
    </div>
</body>
</html>
