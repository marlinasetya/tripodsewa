<?php
require_once '../includes/functions.php';
checkRole(['petugas']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id_peminjaman'];
    $status = sanitize($_POST['status']);
    
    $stmt = $pdo->prepare("UPDATE peminjaman SET status = ? WHERE id_peminjaman = ?");
    if ($stmt->execute([$status, $id])) {
        $message = 'Status berhasil diupdate!';
    }
}

$stmt = $pdo->query("
    SELECT p.*, u.nama as nama_user, a.nama_alat, k.nama_kategori 
    FROM peminjaman p 
    JOIN users u ON p.id_user = u.id_user 
    JOIN alat a ON p.id_alat = a.id_alat
    JOIN kategori k ON a.id_kategori = k.id_kategori
    ORDER BY p.created_at DESC
");
$peminjaman = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <!-- Sidebar sama seperti dashboard -->
    </div>
    <div class="col-md-9 p-4">
        <h2>Kelola Peminjaman</h2>
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5>Daftar Peminjaman</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Alat</th>
                            <th>Tgl Sewa</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($peminjaman as $pinjam): ?>
                        <tr class="<?php echo $pinjam['status'] === 'menunggu' ? 'table-warning' : ($pinjam['status'] === 'ditolak' ? 'table-danger' : 'table-success'); ?>">
                            <td><?php echo $pinjam['id_peminjaman']; ?></td>
                            <td><?php echo $pinjam['nama_user']; ?></td>
                            <td><?php echo $pinjam['nama_alat']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($pinjam['tanggal_sewa'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($pinjam['tanggal_kembali'])); ?></td>
                            <td><span class="badge bg-<?php 
                                echo $pinjam['status'] === 'menunggu' ? 'warning' : 
                                     ($pinjam['status'] === 'disetujui' ? 'success' : 
                                      ($pinjam['status'] === 'selesai' ? 'info' : 'danger')); 
                            ?>"><?php echo ucwords($pinjam['status']); ?></span></td>
                            <td>
                                <?php if ($pinjam['status'] === 'menunggu'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="id_peminjaman" value="<?php echo $pinjam['id_peminjaman']; ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="menunggu" <?php echo $pinjam['status'] === 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                            <option value="disetujui">Disetujui</option>
                                            <option value="ditolak">Ditolak</option>
                                        </select>
                                    </form>
                                <?php else: ?>
                                    <span class="small text-muted"><?php echo ucwords($pinjam['status']); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>

