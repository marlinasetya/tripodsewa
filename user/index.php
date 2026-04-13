<?php
require_once '../includes/functions.php';
checkRole('user');
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <nav class="sidebar p-3 text-white">
            <h5>Halo, <?php echo $_SESSION['nama']; ?>!</h5>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="alat.php"><i class="fas fa-camera"></i> Lihat Alat</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="peminjaman.php"><i class="fas fa-list"></i> Peminjaman Saya</a></li>
            </ul>
            <hr>
            <a href="../logout.php" class="btn btn-outline-light w-100">Logout</a>
        </nav>
    </div>
    <div class="col-md-9 p-4">
<h2>Dashboard User</h2>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card h-100 text-center p-4 bg-light">
                            <i class="fas fa-camera fa-3x text-primary mb-3"></i>
                            <h5>Sewa Alat</h5>
                            <p>Lihat dan sewa tripod & video booth berkualitas</p>
                            <a href="alat.php" class="btn btn-primary">Mulai Sewa</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 text-center p-4 bg-light">
                            <i class="fas fa-list fa-3x text-success mb-3"></i>
                            <h5>Riwayat</h5>
                            <p>Kelola peminjaman Anda</p>
                            <a href="peminjaman.php" class="btn btn-success">Lihat</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 text-center p-4 bg-light">
                            <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                            <h5>Info</h5>
                            <p>Cara sewa dan ketentuan</p>
                            <a href="#" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#infoModal">Baca</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Info Modal -->
<div class="modal fade" id="infoModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Informasi Penting</h5>
            </div>
            <div class="modal-body">
                <ul>
                    <li>Konfirmasi dalam 24 jam</li>
                    <li>Bayar DP 50%</li>
                    <li>Kembali bersih & lengkap</li>
                    <li>Denda keterlambatan Rp50rb/hari</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>

