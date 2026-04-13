<?php
require_once '../includes/functions.php';
checkRole(['petugas']);

$stmt = $pdo->query("SELECT COUNT(*) as total FROM peminjaman WHERE status='menunggu'");
$pending = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM peminjaman WHERE status IN ('disetujui', 'selesai')");
$approved = $stmt->fetch()['total'];
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <nav class="sidebar p-3 text-white">
            <h5>Petugas Panel</h5>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
<li class="nav-item"><a class="nav-link text-white" href="peminjaman.php"><i class="fas fa-list"></i> Peminjaman</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="statistik.php"><i class="fas fa-chart-line"></i> Statistik</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="alat_stok.php"><i class="fas fa-boxes"></i> Stok Alat</a></li>
            </ul>
            <hr>
            <a href="../logout.php" class="btn btn-outline-light w-100">Logout (<?php echo $_SESSION['nama']; ?>)</a>
        </nav>
    </div>
    <div class="col-md-9 p-4">
        <h2>Dashboard Petugas</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card stats-card text-white p-4 mb-4" style="background: linear-gradient(45deg, #ffecd2, #fcb69f);">
                    <h3><?php echo $pending; ?></h3>
                    <p>Menunggu Konfirmasi</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stats-card text-white p-4 mb-4" style="background: linear-gradient(45deg, #a8edea, #fed6e3);">
                    <h3><?php echo $approved; ?></h3>
                    <p>Terverifikasi</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5>Peminjaman Menunggu</h5>
                <a href="peminjaman.php" class="btn btn-primary">Kelola Peminjaman <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>

