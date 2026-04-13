<?php
require_once '../includes/functions.php';
checkRole('admin');

$riwayat = getRiwayatPeminjaman($pdo);
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3"><!-- Sidebar --></div>
    <div class="col-md-9 p-4">
        <h2>Riwayat Peminjaman</h2>
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Alat</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th>Denda</th>
                            <th>Status</th>
                            <th>Tgl</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayat as $row): ?>
                        <tr>
                            <td><?php echo $row['id_peminjaman']; ?></td>
                            <td><?php echo $row['nama_user']; ?></td>
                            <td><?php echo $row['no_alat'] . ' - ' . $row['nama_alat']; ?></td>
                            <td><?php echo $row['jumlah']; ?></td>
                            <td><?php echo formatRupiah($row['total_harga']); ?></td>
                            <td><?php echo formatRupiah($row['denda']); ?></td>
                            <td><span class="badge bg-<?php echo $row['status'] == 'selesai' || $row['status'] == 'dikembalikan' ? 'success' : ($row['status'] == 'rusak' ? 'danger' : 'warning'); ?>"><?php echo ucfirst($row['status']); ?></span></td>
                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <a href="index.php" class="btn btn-secondary mt-3">Kembali</a>
    </div>
</div>
<?php include '../includes/footer.php'; ?>

