<?php
require_once '../includes/functions.php';
checkRole('user');

$stmt = $pdo->prepare("
    SELECT p.*, a.nama_alat, k.nama_kategori 
    FROM peminjaman p 
    JOIN alat a ON p.id_alat = a.id_alat
    JOIN kategori k ON a.id_kategori = k.id_kategori
    WHERE p.id_user = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$peminjaman = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3"><!-- Sidebar --></div>
    <div class="col-md-9 p-4">
        <h2>Peminjaman Saya</h2>
        <div class="card">
            <div class="card-header">
                <h5>Riwayat Peminjaman</h5>
            </div>
            <div class="card-body">
                <?php if (empty($peminjaman)): ?>
                    <div class="text-center p-5">
                        <i class="fas fa-list fa-3x text-muted mb-3"></i>
                        <h5>Belum ada peminjaman</h5>
                        <a href="alat.php" class="btn btn-primary">Sewa Sekarang</a>
                    </div>
                <?php else: ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Alat</th>
                                <th>Tgl Sewa</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($peminjaman as $pinjam): ?>
                            <tr class="<?php echo $pinjam['status'] === 'menunggu' ? 'table-warning' : ($pinjam['status'] === 'ditolak' ? 'table-danger' : 'table-success'); ?>">
                                <td><?php echo $pinjam['nama_alat']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($pinjam['tanggal_sewa'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($pinjam['tanggal_kembali'])); ?></td>
                                <td><span class="badge bg-<?php 
                                    echo $pinjam['status'] === 'menunggu' ? 'warning' : 
                                         ($pinjam['status'] === 'disetujui' ? 'success' : 
                                          ($pinjam['status'] === 'selesai' ? 'info' : 'danger')); 
                                ?>"><?php echo ucwords($pinjam['status']); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>

