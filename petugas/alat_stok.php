<?php
require_once '../includes/functions.php';
checkRole(['petugas','admin']);

$alat = $pdo->query("SELECT a.*, k.nama_kategori FROM alat a JOIN kategori k ON a.id_kategori = k.id_kategori ORDER BY stok ASC")->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <nav class="sidebar p-3 text-white">
            <h5>Petugas Panel</h5>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="alat_stok.php"><i class="fas fa-boxes"></i> Stok Alat</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="peminjaman.php"><i class="fas fa-list"></i> Peminjaman</a></li>
            </ul>
            <hr>
            <a href="../logout.php" class="btn btn-outline-light w-100">Logout</a>
        </nav>
    </div>
    <div class="col-md-9 p-4">
        <h2>Stok Alat (Low Stock First)</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kategori</th>
                        <th>Alat</th>
                        <th>Stok Normal</th>
                        <th>Stok Rusak</th>
                        <th>Gambar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alat as $item): ?>
                    <tr class="<?php echo $item['stok'] < 3 ? 'table-warning' : ''; ?>">
                        <td><?php echo $item['no_alat']; ?></td>
                        <td><span class="badge bg-info"><?php echo $item['nama_kategori']; ?></span></td>
                        <td><?php echo htmlspecialchars($item['nama_alat']); ?></td>
                        <td><strong class="<?php echo $item['stok'] == 0 ? 'text-danger' : ($item['stok'] < 3 ? 'text-warning' : 'text-success'); ?>"><?php echo $item['stok']; ?></strong></td>
                        <td><span class="badge bg-danger"><?php echo $item['stok_cacat'] ?? 0; ?></span></td>
                        <td><?php if ($item['foto']): ?><img src="../uploads/<?php echo $item['foto']; ?>" style="width:50px;height:50px;object-fit:cover;" class="rounded"><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a href="manajemen_peminjaman.php" class="btn btn-primary mt-3">Kelola Peminjaman</a>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
