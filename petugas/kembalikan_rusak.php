<?php
require_once '../includes/functions.php';
checkRole(['petugas']);

$id = (int)$_GET['id'] ?? 0;
if (!$id) {
    header('Location: manajemen_peminjaman.php');
    exit;
}

$peminjaman = $pdo->prepare("
    SELECT p.*, u.nama as nama_user, a.no_alat, a.nama_alat 
    FROM peminjaman p JOIN users u ON p.id_user = u.id_user JOIN alat a ON p.id_alat = a.id_alat 
    WHERE p.id_peminjaman = ?
")->execute([$id]) && $pdo->prepare("SELECT * FROM peminjaman WHERE id_peminjaman = ?")->fetch();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $denda = (float)$_POST['denda'];
    $ansuran = isset($_POST['ansuran']);
    
$jumlah_stmt = $pdo->prepare("SELECT jumlah, id_alat FROM peminjaman WHERE id_peminjaman = ?");
    $jumlah_stmt->execute([$id]);
    $data = $jumlah_stmt->fetch();
    $jumlah = $data['jumlah'];
    $id_alat = $data['id_alat'];
    
    // Update stock back
    $pdo->prepare("UPDATE alat SET stok = stok + ? WHERE id_alat = ?")->execute([$jumlah, $id_alat]);
    
    $denda = (float)$_POST['denda'];
    $stmt = $pdo->prepare("UPDATE peminjaman SET status = 'rusak', denda = ?, tanggal_dikembalikan = CURDATE() WHERE id_peminjaman = ?");
    $stmt->execute([$denda, $id]);
    
    $message = 'Rusak dengan denda ' . formatRupiah($denda) . ' dan stok +' . $jumlah . ' berhasil! <a href="manajemen_peminjaman.php" class="btn btn-primary">Kembali</a>';
}
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <nav class="sidebar p-3 text-white">
            <h5>Petugas Panel</h5>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="manajemen_peminjaman.php"><i class="fas fa-list"></i> Manajemen</a></li>
            </ul>
            <hr>
            <a href="../logout.php" class="btn btn-outline-light w-100">Logout</a>
        </nav>
    </div>
    <div class="col-md-9 p-4">
        <h2>Kembalikan Rusak - #<?php echo $id; ?></h2>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
            <a href="manajemen_peminjaman.php" class="btn btn-primary">Kembali</a>
        <?php else: ?>
        <div class="card">
            <div class="card-body">
                <h5>Detail Peminjaman</h5>
                <p><strong>User:</strong> <?php echo $peminjaman['nama_user']; ?></p>
                <p><strong>Alat:</strong> <?php echo $peminjaman['no_alat']; ?> - <?php echo $peminjaman['nama_alat']; ?></p>
                <p><strong>Total:</strong> <?php echo formatRupiah($peminjaman['total_harga']); ?></p>
                
                <form method="POST">
                    <div class="mb-3">
                        <label>Denda Rusak (Rp)</label>
                        <input type="number" name="denda" value="0" step="5000" min="0" class="form-control" required placeholder="Input denda manual">
                    </div>
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle"></i> Stock akan otomatis +<?php echo $peminjaman['jumlah']; ?> | Gunakan modal di manajemen untuk Normal return.</small>
                    </div>
                    <button type="submit" class="btn btn-danger mt-3">
                        <i class="fas fa-save"></i> Simpan Rusak + Update Stok
                    </button>
                    <a href="manajemen_peminjaman.php" class="btn btn-secondary mt-3">← Kembali ke Manajemen</a>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
