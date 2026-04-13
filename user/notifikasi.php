<?php
require_once '../includes/functions.php';
checkRole('user');

$notifs = [
    ['id' => 1, 'title' => 'Pesanan #001 Disetujui!', 'time' => '2 jam lalu', 'type' => 'success'],
    ['id' => 2, 'title' => 'Stok TR001 Tersedia', 'time' => '1 hari lalu', 'type' => 'info'],
    ['id' => 3, 'title' => 'Promo 20% Tripod', 'time' => '3 hari lalu', 'type' => 'promo']
];
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <nav class="sidebar p-3 text-white">
            <h5>Halo, <?php echo $_SESSION['nama']; ?>!</h5>
            <ul class="nav flex-column">
                <li><a class="nav-link text-white" href="index.php">🏠 Dashboard</a></li>
                <li><a class="nav-link text-white" href="alat.php">📷 Alat</a></li>
                <li><a class="nav-link text-white" href="keranjang.php">🛒 Keranjang</a></li>
                <li><a class="nav-link text-white active" href="notifikasi.php">🔔 Notifikasi</a></li>
                <li><a class="nav-link text-white" href="peminjaman.php">📋 Riwayat</a></li>
            </ul>
            <hr><a href="../profile.php" class="btn btn-light w-100 mb-2">👤 Profile</a>
            <a href="../logout.php" class="btn btn-outline-light w-100">Logout</a>
        </nav>
    </div>
    <div class="col-md-9">
        <div class="p-4">
            <h2>🔔 Notifikasi <span class="badge bg-primary"><?php echo count($notifs); ?></span></h2>
            <div class="card">
                <?php foreach ($notifs as $n): ?>
                <div class="card-body border-bottom <?php echo $n['type']=='success' ? 'bg-success bg-opacity-10' : ''; ?>">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><?php echo $n['title']; ?></h6>
                            <small class="text-muted"><?php echo $n['time']; ?></small>
                        </div>
                        <span class="badge bg-<?php echo $n['type']=='success' ? 'success' : 'info'; ?>"><?php echo ucfirst($n['type']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div><?php include '../includes/footer.php'; ?>
