<?php
require_once '../includes/functions.php';
checkRole('user');

$kategori_filter = $_GET['kategori'] ?? '';
$where = $kategori_filter ? "WHERE a.id_kategori = " . (int)$kategori_filter : '';
$stmt = $pdo->query("SELECT a.*, k.nama_kategori, a.no_alat, a.harga_per_hari FROM alat a JOIN kategori k ON a.id_kategori = k.id_kategori $where ORDER BY a.nama_alat");
$alat = $stmt->fetchAll();

$kats = $pdo->query("SELECT * FROM kategori")->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <!-- Sidebar -->
    </div>
    <div class="col-md-9 p-4">
        <h2>Daftar Alat <?php echo $kategori_filter ? ' - ' . array_column($kats, 'nama_kategori', 'id_kategori')[$kategori_filter ?? ''] : 'Tersedia'; ?></h2>
        <div class="mb-3">
            <a href="alat.php" class="btn btn-outline-primary me-2 <?php echo !$kategori_filter ? 'active' : ''; ?>">Semua</a>
            <?php foreach ($kats as $kat): ?>
            <a href="?kategori=<?php echo $kat['id_kategori']; ?>" class="btn btn-outline-primary me-2 <?php echo $kategori_filter == $kat['id_kategori'] ? 'active' : ''; ?>">
                <?php echo $kat['nama_kategori']; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="row">
            <?php foreach ($alat as $item): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-img-top p-3 text-center bg-light">
                        <?php if ($item['foto']): ?>
                            <img src="../uploads/<?php echo $item['foto']; ?>" class="img-fluid" style="max-height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-camera fa-5x text-muted"></i>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $item['nama_alat']; ?></h5>
                        <p class="card-text text-muted"><?php echo $item['nama_kategori']; ?> | No. <?php echo $item['no_alat']; ?></p>
                        <p class="card-text"><?php echo substr($item['deskripsi'], 0, 100); ?>...</p>
                        <p><strong><?php echo formatRupiah($item['harga_per_hari']); ?>/hari</strong></p>
                        <p>Stok: <span class="badge bg-<?php echo $item['stok'] > 0 ? 'success' : 'danger'; ?>"><?php echo $item['stok']; ?></span></p>
                        <?php if ($item['stok'] > 0): ?>
                            <div class="btn-group w-100" role="group">
                                <a href="alat_detail.php?id=<?php echo $item['id_alat']; ?>" class="btn btn-primary flex-fill">Detail</a>
                                <form method="POST" action="add_cart.php" class="flex-fill">
                                    <input type="hidden" name="id_alat" value="<?php echo $item['id_alat']; ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <input type="hidden" name="return_url" value="alat.php">
                                    <button type="submit" class="btn btn-outline-warning flex-fill">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100" disabled>Stok Habis</button>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>

