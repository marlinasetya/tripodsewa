<?php
require_once '../includes/functions.php';
checkRole('user');

$cart = getCart();
if (empty($cart)) {
    header('Location: alat.php');
    exit;
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        foreach ($_POST['qty'] as $id => $qty) {
            updateCartQty($id, (int)$qty, $pdo);
        }
        $success = 'Keranjang updated!';
    } elseif (isset($_POST['remove'])) {
        updateCartQty($_POST['remove'], 0, $pdo);
        $success = 'Item removed!';
    } elseif (isset($_POST['checkout'])) {
        $tgl_sewa = $_POST['tanggal_sewa'];
        $tgl_kembali = $_POST['tanggal_kembali'];
        
        if (strtotime($tgl_kembali) < strtotime($tgl_sewa)) {
            $error = 'Tanggal kembali harus setelah sewa!';
        } else {
            $start = new DateTime($tgl_sewa);
            $end = new DateTime($tgl_kembali);
            $interval = $start->diff($end);
            $jumlah_hari = $interval->days + 1;
            
            $created = 0;
            foreach ($cart as $id_alat => $item) {
                // Check stok_cacat logic
                if (!validateStokCacat($pdo, $id_alat, $item['qty'])) {
                    $error = 'Stok tidak cukup atau cacat tidak tersedia!';
                    break;
                }
                $status = (isset($item['stok_cacat']) && $item['stok_cacat'] > 0 && $item['stok_cacat'] >= $item['qty']) ? 'menunggu' : 'menunggu'; // always menunggu if cacat check passed
                
                $total_harga = $item['harga_per_hari'] * $jumlah_hari * $item['qty'];
                
                $stmt = $pdo->prepare("
                    INSERT INTO peminjaman (id_user, id_alat, jumlah, harga_per_hari, tanggal_sewa, tanggal_kembali, total_harga, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                if ($stmt->execute([$_SESSION['user_id'], $id_alat, $item['qty'], $item['harga_per_hari'], $tgl_sewa, $tgl_kembali, $total_harga, $status])) {
                    $created++;
                }
            }
            if ($created > 0) {
                emptyCart();
                $success = "$created peminjaman berhasil diajukan!";
            }
        }
    }
$cart = getCart(); // Refresh
}

$total = getCartTotal($pdo);
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3"><!-- Sidebar --></div>
    <div class="col-md-9 p-4">
        <h2>Keranjang Sewa (<?= count($cart) ?> item)</h2>
        <!-- Cart items with stok info -->
        <div class="card mb-4">
            <div class="card-body">
                <?php foreach ($cart as $id_alat => $item): 
                    $stmt = $pdo->prepare("SELECT nama_alat, stok, stok_cacat FROM alat WHERE id_alat = ?");
                    $stmt->execute([$id_alat]);
                    $alat_info = $stmt->fetch();
                ?>
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <div>
                        <h6><?php echo $alat_info['nama_alat']; ?></h6>
                        <small class="text-muted">Qty: <?php echo $item['qty']; ?> | Stok normal: <?php echo $alat_info['stok']; ?> | Cacat: <?php echo $alat_info['stok_cacat']; ?> 
                            <?php if ($alat_info['stok_cacat'] > 0 && $alat_info['stok_cacat'] >= $item['qty']): ?>
                                <span class="badge bg-warning">Cacat tersedia (status menunggu)</span>
                            <?php elseif ($alat_info['stok'] >= $item['qty']): ?>
                                <span class="badge bg-success">Stok OK</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Stok kurang!</span>
                            <?php endif; ?>
                        </small>
                    </div>
                    <div>
                        <form method="POST" class="d-inline">
                            <input type="number" name="qty[<?php echo $id_alat; ?>]" value="<?php echo $item['qty']; ?>" min="1" class="form-control form-control-sm w-50 d-inline">
                            <button type="submit" name="update" class="btn btn-sm btn-outline-primary">Update</button>
                        </form>
                        <form method="POST" class="d-inline ms-1">
                            <input type="hidden" name="remove" value="<?php echo $id_alat; ?>">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                <hr>
                <h5>Total sementara (per hari): <?php echo formatRupiah($total); ?></h5>
            </div>
        </div>

        <!-- Checkout Form -->
        <?php if (empty($error) && empty($success) || $success == 'Keranjang updated!' || $success == 'Item removed!'): ?>
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-calendar"></i> Lengkapi Jadwal Sewa untuk Lanjutkan</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Tanggal Sewa</label>
                            <input type="date" name="tanggal_sewa" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Tanggal Kembali</label>
                            <input type="date" name="tanggal_kembali" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Catatan (opsional)</label>
                        <textarea name="catatan" class="form-control" rows="2" placeholder="Keterangan khusus..."></textarea>
                    </div>
                    <button type="submit" name="checkout" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-check"></i> Lanjutkan Checkout & Ajukan Peminjaman
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-3">
            <a href="alat.php" class="btn btn-outline-secondary">Lanjut Belanja</a>
            <a href="peminjaman.php" class="btn btn-outline-primary">Lihat Peminjaman Saya</a>
        </div>
        <?php if ($success): ?>
            <div class="alert alert-success mt-3"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger mt-3"><?= $error ?></div>
        <?php endif; ?>

