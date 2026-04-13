<?php
require_once '../includes/functions.php';
checkRole(['petugas']);

$total_peminjaman = $pdo->query("SELECT COUNT(*) FROM peminjaman")->fetchColumn();
$active_users = $pdo->query("SELECT COUNT(DISTINCT id_user) FROM peminjaman WHERE status != 'ditolak'")->fetchColumn();
$return_rate = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status IN ('dikembalikan', 'selesai')")->fetchColumn();
$return_rate = $total_peminjaman > 0 ? round(($return_rate / $total_peminjaman) * 100, 1) : 0;

$stmt = $pdo->query("
    SELECT AVG(DATEDIFF(tanggal_kembali, tanggal_sewa)) as avg_duration 
    FROM peminjaman WHERE status IN ('dikembalikan', 'selesai')
")->fetch();
$avg_duration = $stmt['avg_duration'] ?? 0;

$total_revenue = $pdo->query("SELECT SUM(total_harga) FROM peminjaman WHERE status = 'selesai'")->fetchColumn() ?? 0;
$total_denda = $pdo->query("SELECT SUM(denda) FROM peminjaman WHERE denda > 0")->fetchColumn() ?? 0;

$terlambat = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'terlambat'")->fetchColumn();

$detail = $pdo->query("
    SELECT p.*, u.nama as nama_user, a.no_alat, a.nama_alat, k.nama_kategori 
    FROM peminjaman p 
    JOIN users u ON p.id_user = u.id_user 
    JOIN alat a ON p.id_alat = a.id_alat 
    JOIN kategori k ON a.id_kategori = k.id_kategori 
    ORDER BY p.created_at DESC
")->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <nav class="sidebar p-3 text-white">
            <h5>Petugas Panel</h5>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="manajemen_peminjaman.php"><i class="fas fa-list"></i> Manajemen</a></li>
                <li class="nav-item"><a class="nav-link text-white active" href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
            </ul>
            <hr>
            <a href="../logout.php" class="btn btn-outline-light w-100">Logout</a>
        </nav>
    </div>
    <div class="col-md-9 p-4">
        <h2>Laporan Lengkap</h2>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card text-white p-4" style="background: linear-gradient(45deg, #ff6b6b, #ee5a52);">
                    <h3><?php echo $total_peminjaman; ?></h3>
                    <small>Total Peminjaman</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card text-white p-4" style="background: linear-gradient(45deg, #4facfe, #00f2fe);">
                    <h3><?php echo $active_users; ?></h3>
                    <small>User Aktif</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card text-white p-4" style="background: linear-gradient(45deg, #43e97b, #38f9d7);">
                    <h3><?php echo $return_rate; ?>%</h3>
                    <small>Tingkat Pengembalian</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card text-white p-4" style="background: linear-gradient(45deg, #fa709a, #fee140);">
                    <h3><?php echo $avg_duration; ?> hari</h3>
                    <small>Rata-rata Durasi</small>
                </div>
            </div>
        </div>

        <!-- Revenue Cards -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card stats-card text-white p-4" style="background: linear-gradient(45deg, #11998e, #38ef7d);">
                    <h3><?php echo formatRupiah($total_revenue); ?></h3>
                    <small>Total Revenue</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stats-card text-white p-4" style="background: linear-gradient(45deg, #ffecd2, #fcb69f);">
                    <h3><?php echo formatRupiah($total_denda); ?></h3>
                    <small>Total Denda</small>
                </div>
            </div>
        </div>

        <!-- Detail Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Detail Penyewaan</h5>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>No Item</th>
                                <th>User</th>
                                <th>Alat</th>
                                <th>Jumlah</th>
                                <th>Total</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                                <th>Denda</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detail as $row): ?>
                            <tr>
                                <td><?php echo $row['id_peminjaman']; ?></td>
                                <td><strong><?php echo $row['no_alat']; ?></strong></td>
                                <td><?php echo $row['nama_user']; ?></td>
                                <td><?php echo $row['nama_alat']; ?> (<?php echo $row['nama_kategori']; ?>)</td>
                                <td><?php echo $row['jumlah']; ?></td>
                                <td><?php echo formatRupiah($row['total_harga']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_sewa'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?></td>
                                <td><span class="badge bg-<?php 
                                    $status_class = ['menunggu' => 'warning', 'disetujui' => 'info', 'terlambat' => 'danger', 'dikembalikan' => 'success', 'rusak' => 'danger', 'selesai' => 'success'];
                                    echo $status_class[$row['status']] ?? 'secondary';
                                ?>"><?php echo ucwords(str_replace('_', ' ', $row['status'])); ?></span></td>
                                <td><?php echo formatRupiah($row['denda']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
