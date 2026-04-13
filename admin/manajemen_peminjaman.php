<?php
require_once '../includes/functions.php';
checkRole('admin');  // Changed from petugas

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['simpan'])) {
        $id_user = (int)$_POST['id_user'];
        $id_alat = (int)$_POST['id_alat'];
        $jumlah = (int)$_POST['jumlah'];
        $tanggal_sewa = $_POST['tanggal_sewa'];
        $tanggal_kembali = $_POST['tanggal_kembali'];
        $catatan = sanitize($_POST['catatan']);
        
        $harga_stmt = $pdo->prepare("SELECT harga_per_hari FROM alat WHERE id_alat = ?");
        $harga_stmt->execute([$id_alat]);
        $harga_data = $harga_stmt->fetch();
        if (!$harga_data) {
            $error = 'Alat tidak ditemukan!';
        } elseif (!validateStokCacat($pdo, $id_alat, $jumlah)) {
            $error = 'Stok tidak mencukupi atau cacat tidak tersedia!';
        } else {
            $harga_per_hari = (float)$harga_data['harga_per_hari'];
            $total_hari = floor((strtotime($tanggal_kembali) - strtotime($tanggal_sewa)) / 86400) + 1;
            $total_harga = calcTotalHarga($harga_per_hari, $total_hari, $jumlah);
            
            $stmt = $pdo->prepare("
                INSERT INTO peminjaman (id_user, id_alat, jumlah, harga_per_hari, total_harga, tanggal_sewa, tanggal_kembali, catatan, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'menunggu')
            ");
            if ($stmt->execute([$id_user, $id_alat, $jumlah, $harga_per_hari, $total_harga, $tanggal_sewa, $tanggal_kembali, $catatan])) {
                $message = 'Peminjaman berhasil dibuat dan status menunggu!';
            } else {
                $error = 'Gagal menyimpan.';
            }
        }
    } elseif (isset($_POST['update_status'])) {
        $id = (int)$_POST['id_peminjaman'];
        $status = sanitize($_POST['status']);
        
        // Update stok based on status
        if ($status == 'disetujui') {
            // Kurangi stok safely
            $pdo->prepare("UPDATE alat SET stok = stok - ? WHERE id_alat = ?")
                ->execute([$_SESSION['temp_jumlah'] ?? (int)$_POST['jumlah'], (int)$_POST['temp_id_alat'] ?? $id_alat]);
        } elseif (in_array($status, ['dikembalikan', 'selesai', 'rusak'])) {
            // Tambah kembali stok safely
            $update_stmt = $pdo->prepare("UPDATE alat SET stok = stok + ? WHERE id_alat = (SELECT id_alat FROM peminjaman WHERE id_peminjaman = ?)");
            $jumlah_stmt = $pdo->prepare("SELECT jumlah FROM peminjaman WHERE id_peminjaman = ?");
            $jumlah_stmt->execute([$id]);
            $jumlah = $jumlah_stmt->fetchColumn();
            $update_stmt->execute([$jumlah, $id]);
        }
        
        $stmt = $pdo->prepare("UPDATE peminjaman SET status = ? WHERE id_peminjaman = ?");
        if ($stmt->execute([$status, $id])) {
            $message = 'Status berhasil diupdate dan stok disesuaikan!';
        }
    } elseif (isset($_POST['proses_kembalikan'])) {
        $id = (int)$_POST['id_peminjaman'];
        $is_rusak = $_POST['is_rusak'] === 'yes';
        $manual_denda = (float)($_POST['manual_denda'] ?? 0);
        
        $jumlah_stmt = $pdo->prepare("SELECT jumlah, id_alat, tanggal_kembali FROM peminjaman WHERE id_peminjaman = ?");
        $jumlah_stmt->execute([$id]);
        $data = $jumlah_stmt->fetch();
        $jumlah = $data['jumlah'];
        $id_alat = $data['id_alat'];
        $tgl_kembali = $data['tanggal_kembali'];
        
        // Tambah stok kembali
        $pdo->prepare("UPDATE alat SET stok = stok + ? WHERE id_alat = ?")->execute([$jumlah, $id_alat]);
        
        if ($is_rusak) {
            $stmt = $pdo->prepare("UPDATE peminjaman SET status = 'rusak', denda = ?, tanggal_dikembalikan = CURDATE() WHERE id_peminjaman = ?");
            $stmt->execute([$manual_denda, $id]);
            $message = 'Status rusak dengan denda ' . formatRupiah($manual_denda) . ' ✅ Stok ditambah.';
        } else {
            $hari_terlambat = max(0, (strtotime('today') - strtotime($tgl_kembali)) / 86400);
            $denda_terlambat = calcDendaTerlambat($pdo, $id);
            $stmt = $pdo->prepare("UPDATE peminjaman SET status = 'dikembalikan', denda = ?, tanggal_dikembalikan = CURDATE() WHERE id_peminjaman = ?");
            $stmt->execute([$denda_terlambat, $id]);
            $message = 'Dikembalikan' . ($denda_terlambat > 0 ? ' + Denda terlambat ' . formatRupiah($denda_terlambat) : '') . ' ✅ Stok ditambah. <a href="../petugas/cetak_struk.php?id=' . $id . '" target="_blank" class="btn btn-sm btn-success">Cetak Struk</a>';
        }
    } elseif (isset($_POST['kembalikan_rusak'])) { // Legacy
        // ... existing code
    }
}

// Data for dropdowns
$users = $pdo->query("SELECT id_user, nama FROM users WHERE role = 'user' ORDER BY nama")->fetchAll();
$alat_list = $pdo->query("SELECT a.*, k.nama_kategori FROM alat a JOIN kategori k ON a.id_kategori = k.id_kategori WHERE stok > 0 ORDER BY nama_alat")->fetchAll();
$peminjaman = $pdo->query("
    SELECT p.*, u.nama as nama_user, a.no_alat, a.nama_alat, k.nama_kategori 
    FROM peminjaman p JOIN users u ON p.id_user = u.id_user 
    JOIN alat a ON p.id_alat = a.id_alat JOIN kategori k ON a.id_kategori = k.id_kategori 
    ORDER BY p.created_at DESC
")->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <nav class="sidebar p-3 text-white">
            <h5>Sistem Sewa Tripod - Admin</h5>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-white active" href="manajemen_peminjaman.php"><i class="fas fa-list"></i> Peminjaman</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="alat.php"><i class="fas fa-camera"></i> Alat</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="kategori.php"><i class="fas fa-tags"></i> Kategori</a></li>
            </ul>
            <hr>
            <a href="../logout.php" class="btn btn-outline-light w-100">Logout</a>
        </nav>
    </div>
    <div class="col-md-9 p-4">
        <h2>Manajemen Peminjaman - Admin</h2>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Buat Peminjaman Baru -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-plus"></i> Buat Peminjaman Baru</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Pilih User</label>
                            <select name="id_user" class="form-select" required>
                                <option value="">Pilih User</option>
                                <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id_user']; ?>"><?php echo $user['nama']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Pilih Alat (Stok Tersedia)</label>
                            <select name="id_alat" class="form-select" required onchange="updateTotal()">
                                <option value="">Pilih Alat</option>
                                <?php foreach ($alat_list as $alat): ?>
                                <option value="<?php echo $alat['id_alat']; ?>" data-harga="<?php echo $alat['harga_per_hari']; ?>">
                                    <?php echo $alat['no_alat']; ?> - <?php echo $alat['nama_alat']; ?> (<?php echo formatRupiah($alat['harga_per_hari']); ?>/hari, Stok: <?php echo $alat['stok']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Jumlah</label>
                            <input type="number" name="jumlah" id="jumlah" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-md-2">
                            <label>Tgl Sewa</label>
                            <input type="date" name="tanggal_sewa" id="tgl_sewa" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label>Tgl Kembali</label>
                            <input type="date" name="tanggal_kembali" id="tgl_kembali" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-8">
                            <label>Catatan (Optional)</label>
                            <textarea name="catatan" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label>Total Estimasi</label>
                            <input type="text" id="total_harga" class="form-control" readonly style="background: #f8f9fa; font-weight: bold;">
                        </div>
                    </div>
                    <button type="submit" name="simpan" class="btn btn-success mt-3">Simpan Peminjaman</button>
                </form>
            </div>
        </div>

        <!-- Daftar Peminjaman -->
        <div class="card">
            <div class="card-header">
                <h5>Daftar Peminjaman (<?php echo count($peminjaman); ?>)</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>No Item</th>
                            <th>User</th>
                            <th>Alat</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th>Tgl Sewa</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($peminjaman as $pinjam): ?>
                        <tr class="<?php echo $pinjam['status'] == 'menunggu' ? 'table-warning' : ($pinjam['status'] == 'terlambat' ? 'table-danger' : ($pinjam['status'] == 'rusak' ? 'table-danger' : 'table-success')); ?>">
                            <td><?php echo $pinjam['id_peminjaman']; ?></td>
                            <td><?php echo $pinjam['no_alat']; ?></td>
                            <td><?php echo $pinjam['nama_user']; ?></td>
                            <td><?php echo $pinjam['nama_alat']; ?></td>
                            <td><?php echo $pinjam['jumlah']; ?></td>
                            <td><?php echo formatRupiah($pinjam['total_harga']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($pinjam['tanggal_sewa'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($pinjam['tanggal_kembali'])); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    $status_class = ['menunggu' => 'warning', 'disetujui' => 'info', 'terlambat' => 'danger', 'dikembalikan' => 'success', 'rusak' => 'danger', 'selesai' => 'success'];
                                    echo $status_class[$pinjam['status']] ?? 'secondary';
                                ?>"><?php echo ucwords(str_replace('_', ' ', $pinjam['status'])); ?></span>
                                <?php if ($pinjam['denda'] > 0): ?>
                                    <small class="text-danger d-block">Denda: <?php echo formatRupiah($pinjam['denda']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <form method="POST" style="display:inline;" class="me-1">
                                        <input type="hidden" name="id_peminjaman" value="<?php echo $pinjam['id_peminjaman']; ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="menunggu" <?php echo $pinjam['status']=='menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                            <option value="disetujui" <?php echo $pinjam['status']=='disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                                            <option value="ditolak" <?php echo $pinjam['status']=='ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                            <option value="selesai">Selesai</option>
                                            <option value="dikembalikan">Dikembalikan</option>
                                        </select>
                                    </form>
                                    <?php if (in_array($pinjam['status'], ['disetujui', 'menunggu'])): ?>
                                    <button class="btn btn-sm btn-primary" onclick="prosesKembalikan(<?php echo $pinjam['id_peminjaman']; ?>, '<?php echo $pinjam['nama_alat']; ?>')" data-bs-toggle="modal" data-bs-target="#returnModal<?php echo $pinjam['id_peminjaman']; ?>">
                                        <i class="fas fa-undo"></i> Kembalikan
                                    </button>
                                    <!-- Modal for each peminjaman -->
                                    <div class="modal fade" id="returnModal<?php echo $pinjam['id_peminjaman']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5>Proses Kembalikan <?php echo htmlspecialchars($pinjam['nama_alat']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id_peminjaman" value="<?php echo $pinjam['id_peminjaman']; ?>">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="is_rusak" value="no" id="normal<?php echo $pinjam['id_peminjaman']; ?>" checked>
                                                            <label class="form-check-label" for="normal<?php echo $pinjam['id_peminjaman']; ?>">
                                                                Normal (cek terlambat otomatis)
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="is_rusak" value="yes" id="rusak<?php echo $pinjam['id_peminjaman']; ?>">
                                                            <label class="form-check-label" for="rusak<?php echo $pinjam['id_peminjaman']; ?>">
                                                                Rusak - Input denda manual <input type="number" name="manual_denda" class="form-control form-control-sm mt-1" min="0" step="1000" placeholder="0">
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="proses_kembalikan" class="btn btn-primary">Proses & Tambah Stok</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function updateTotal() {
    const alat = document.querySelector('[name=\"id_alat\"]');
    const jumlah = document.getElementById('jumlah').value || 1;
    const tglSewa = document.getElementById('tgl_sewa').value;
    const tglKembali = document.getElementById('tgl_kembali').value;
    const totalField = document.getElementById('total_harga');
    
    if (alat?.selectedOptions[0]?.dataset.harga && tglSewa && tglKembali) {
        const harga = parseFloat(alat.selectedOptions[0].dataset.harga);
        const hari = Math.ceil((new Date(tglKembali) - new Date(tglSewa)) / (1000 * 60 * 60 * 24));
        const total = harga * hari * parseInt(jumlah);
        totalField.value = 'Rp ' + total.toLocaleString('id-ID');
    } else {
        totalField.value = '';
    }
}

document.querySelectorAll('input[type="date"], select, input[type="number"]').forEach(el => {
    el.addEventListener('change', updateTotal);
});
</script>

<?php include '../includes/footer.php'; ?>

