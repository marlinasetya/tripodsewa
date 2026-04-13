<?php
require_once '../includes/functions.php';
checkRole('admin');

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$total_users = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM kategori");
$total_kategori = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM alat");
$total_alat = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM peminjaman WHERE status='menunggu'");
$pending = $stmt->fetch()['total'];
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <nav class="sidebar p-3 text-white">
            <h5>Sistem Sewa Tripod</h5>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="manajemen_peminjaman.php"><i class="fas fa-list"></i> Peminjaman</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="alat.php"><i class="fas fa-camera"></i> Alat</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="kategori.php"><i class="fas fa-tags"></i> Kategori</a></li>
            </ul>
            <hr>
            <a href="../logout.php" class="btn btn-outline-light w-100">Logout</a>
        </nav>
    </div>
    <div class="col-md-9">
        <div class="p-4">
            <h2>Dashboard Admin</h2>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card text-white p-3">
                        <h3><?php echo $total_users; ?></h3>
                        <small>Total Pengguna</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card text-white p-3" style="background: linear-gradient(45deg, #f093fb, #f5576c);">
                        <h3><?php echo $total_kategori; ?></h3>
                        <small>Kategori</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card text-white p-3" style="background: linear-gradient(45deg, #4facfe, #00f2fe);">
                        <h3><?php echo $total_alat; ?></h3>
                        <small>Alat</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card text-white p-3" style="background: linear-gradient(45deg, #43e97b, #38f9d7);">
                        <h3><?php echo $pending; ?></h3>
                        <small>Menunggu</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>

