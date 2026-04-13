<?php
require_once '../includes/functions.php';
checkRole(['petugas']);

$stats = [
    'monthly_revenue' => $pdo->query("SELECT SUM(total_harga) FROM peminjaman WHERE MONTH(created_at) = MONTH(CURDATE()) AND status = 'selesai'")->fetchColumn() ?? 0,
    'top_alat' => $pdo->query("SELECT nama_alat, COUNT(*) as times FROM peminjaman p JOIN alat a ON p.id_alat = a.id_alat GROUP BY p.id_alat ORDER BY times DESC LIMIT 5")->fetchAll(),
    'user_activity' => $pdo->query("SELECT nama, COUNT(*) as pinjams FROM peminjaman p JOIN users u ON p.id_user = u.id_user GROUP BY p.id_user ORDER BY pinjams DESC LIMIT 10")->fetchAll(),
    'status_dist' => $pdo->query("SELECT status, COUNT(*) as count FROM peminjaman GROUP BY status")->fetchAll(),
];

?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <nav class="sidebar p-3 text-white">
            <h5>Petugas Panel</h5>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="peminjaman.php"><i class="fas fa-list"></i> Peminjaman</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="manajemen_peminjaman.php"><i class="fas fa-cogs"></i> Manajemen</a></li>
                <li class="nav-item"><a class="nav-item"><a class="nav-link text-white" href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li class="nav-item active"><a class="nav-link text-white" href="statistik.php"><i class="fas fa-chart-line"></i> Statistik</a></li>
            </ul>
            <hr>
            <a href="../logout.php" class="btn btn-outline-light w-100">Logout</a>
        </nav>
    </div>
    <div class="col-md-9 p-4">
        <h2>Statistik Lengkap <a href="#" class="btn btn-success btn-sm" onclick="exportCSV()">Export CSV</a> <button class="btn btn-primary btn-sm" onclick="window.print()">Print</button></h2>
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center p-4 bg-primary text-white">
                    <h3><?php echo formatRupiah($stats['monthly_revenue']); ?></h3>
                    <small>Revenue Bulan Ini</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Top 5 Alat</div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($stats['top_alat'] as $item): ?>
                        <li class="list-group-item d-flex justify-content-between"><?php echo htmlspecialchars($item['nama_alat']); ?><span class="badge bg-primary"><?php echo $item['times']; ?>x</span></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Distribusi Status</div>
                    <div class="card-body">
                        <?php foreach ($stats['status_dist'] as $s): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?php echo ucwords(str_replace('_',' ',$s['status'])); ?></span>
                            <span class="badge bg-secondary"><?php echo $s['count']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">Aktivitas User Top 10</div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>User</th><th>Peminjaman</th></tr></thead>
                    <tbody>
                        <?php foreach ($stats['user_activity'] as $u): ?>
                        <tr><td><?php echo htmlspecialchars($u['nama']); ?></td><td><strong><?php echo $u['pinjams']; ?></strong></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function exportCSV() {
    let csv = 'Statistik Petugas\\n';
    csv += 'Alat,Times\\n';
    <?php foreach ($stats['top_alat'] as $item): ?>
    csv += '<?php echo addslashes($item['nama_alat']); ?>,<?php echo $item['times']; ?>\\n';
    <?php endforeach; ?>
    const blob = new Blob([csv], {type: 'text/csv'});
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'petugas_stats.csv';
    a.click();
}
</script>

<?php include '../includes/footer.php'; ?>
