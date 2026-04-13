<?php
require_once '../includes/functions.php';
checkRole('user');

$error = '';
$success = '';

if (!isset($_GET['id'])) {
    header('Location: alat.php');
    exit;
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT a.*, k.nama_kategori 
    FROM alat a 
    JOIN kategori k ON a.id_kategori = k.id_kategori 
    WHERE a.id_alat = ?
");
$stmt->execute([$id]);
$alat = $stmt->fetch();

if (!$alat || $alat['stok'] == 0) {
    header('Location: alat.php');
    exit;
}

        // Form processing moved to sewa_process.php
        if (isset($_SESSION['success'])) {
            $success = $_SESSION['success'];
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            $error = $_SESSION['error'];
            unset($_SESSION['error']);
        }
?>

<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-md-3"></div>

    <div class="col-md-9 p-4">
        <a href="alat.php" class="btn btn-secondary mb-3">&larr; Kembali</a>

        <div class="row">
            <!-- FOTO -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-img-top p-4 text-center bg-light">
                        <?php if ($alat['foto']): ?>
                            <img src="../uploads/<?php echo $alat['foto']; ?>" 
                                 class="img-fluid" 
                                 style="max-height: 300px;">
                        <?php else: ?>

                            <i class="fas fa-camera fa-5x text-muted"></i>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- DETAIL -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h2><?php echo $alat['nama_alat']; ?></h2>
                        <p class="text-muted"><?php echo $alat['nama_kategori']; ?></p>

                        <h5>
                            Stok: 
                            <span class="text-success"><?php echo $alat['stok']; ?></span>
                        </h5>

                        <hr>

                        <h5>
                            Harga: 
                            <?php echo formatRupiah($alat['harga_per_hari']); ?>/hari
                        </h5>


                        <div class="mb-4 mt-3">
                            <h6>Deskripsi:</h6>
                            <p><?php echo nl2br($alat['deskripsi']); ?></p>
                        </div>

                        <?php if (isset($_SESSION['success'])) {
                            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                            unset($_SESSION['success']);
                        }
                        if (isset($_SESSION['error'])) {
                            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                            unset($_SESSION['error']);
                        } ?>

                        <!-- Add to Cart Form -->
                        <form method="POST" action="add_cart.php" class="mb-4">
                            <input type="hidden" name="id_alat" value="<?php echo $alat['id_alat']; ?>">
                            <input type="hidden" name="return_url" value="alat_detail.php?id=<?php echo $id; ?>">
                            <div class="mb-3">
                                <label>Jumlah untuk Keranjang</label>
                                <input type="number" name="qty" value="1" min="1" max="<?php echo $alat['stok']; ?>" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-warning btn-lg w-100 mb-2">
                                <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                            </button>
                        </form>

                        <!-- Direct Sewa Form (existing) -->
                        <?php if (!$success): ?>
                        <form method="POST" action="sewa_process.php">
                            <input type="hidden" name="id_alat" value="<?php echo $alat['id_alat']; ?>">
                            <div class="mb-3">
                                <label>Tanggal Sewa</label>
                                <input type="date" name="tanggal_sewa" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Jumlah</label>
                                <input type="number" name="jumlah" value="1" min="1" max="<?php echo $alat['stok']; ?>" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Tanggal Kembali</label>
                                <input type="date" name="tanggal_kembali" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-check"></i> Ajukan Sewa Langsung
                            </button>
                        </form>
                        <?php endif; ?>


                        <?php if ($error): ?>
                            <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success mt-3"><?php echo $success; ?></div>
                            <a href="peminjaman.php" class="btn btn-primary w-100">
                                Lihat Peminjaman Saya
                            </a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>