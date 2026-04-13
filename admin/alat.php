<?php
require_once '../includes/functions.php';
checkRole('admin');

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_cart'])) {
        $id_alat = (int)$_POST['id_alat'];
        $qty = (int)$_POST['qty'];
        $stmt = $pdo->prepare("SELECT * FROM alat WHERE id_alat = ?");
        $stmt->execute([$id_alat]);
        $alat = $stmt->fetch();
        if ($alat) {
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            $_SESSION['cart'][$id_alat] = [
                'qty' => $qty,
                'harga_per_hari' => $alat['harga_per_hari'],
                'nama' => $alat['nama_alat']
            ];
            $message = 'Ditambahkan ke keranjang!';
        }
        header('Location: alat.php');
        exit;
    }
    if (isset($_POST['add']) || isset($_POST['edit'])) {
        $nama = sanitize($_POST['nama_alat']);
        $deskripsi = sanitize($_POST['deskripsi']);
        $stok = (int)$_POST['stok'];
        $stok_cacat = (int)$_POST['stok_cacat'];
        $kategori = (int)$_POST['id_kategori'];
        $foto = '';
        
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $foto = uploadFoto($_FILES['foto']);
            if (!$foto) $message = 'Upload foto gagal!';
        }
        
        if (isset($_POST['add']) || isset($_POST['edit'])) {
            if (isset($_POST['edit'])) {

                $id = (int)$_POST['id_alat'];
$sql = "UPDATE alat SET no_alat=?, nama_alat=?, harga_per_hari=?, deskripsi=?, stok=?, stok_cacat=?, id_kategori=?";
                $params = [sanitize($_POST['no_alat']), $nama, (float)$_POST['harga_per_hari'], $deskripsi, $stok, $stok_cacat, $kategori];
                if ($foto) {
                    $sql .= ", foto=?";
                    $params[] = $foto;
                }
                // Handle galleries
                $galeri_utripot = handleGalleryUpload($_FILES['galeri_utripot'] ?? [], 'utripot');
                $galeri_booth = handleGalleryUpload($_FILES['galeri_booth'] ?? [], 'booth');
                if ($galeri_utripot !== '[]') {
                    $sql .= ", galeri_utripot=?";
                    $params[] = $galeri_utripot;
                }
                if ($galeri_booth !== '[]') {
                    $sql .= ", galeri_booth=?";
                    $params[] = $galeri_booth;
                }
                $sql .= " WHERE id_alat=?";
                $params[] = $id;
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            } else {
$stmt = $pdo->prepare("INSERT INTO alat (id_kategori, no_alat, nama_alat, harga_per_hari, deskripsi, stok, foto) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$kategori, sanitize($_POST['no_alat']), $nama, (float)$_POST['harga_per_hari'], $deskripsi, $stok, $foto]);
            }
            $message = 'Alat berhasil disimpan!';
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM alat WHERE id_alat = ?");
        if ($stmt->execute([$id])) {
            $message = 'Alat dihapus!';
        }
    }
}

$stmt = $pdo->query("SELECT a.*, k.nama_kategori FROM alat a JOIN kategori k ON a.id_kategori = k.id_kategori ORDER BY a.id_alat DESC");
$alat_list = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM kategori");
$kategori = $stmt->fetchAll();

$edit_alat = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM alat WHERE id_alat = ?");
    $stmt->execute([$id]);
    $edit_alat = $stmt->fetch();
}
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3"><!-- Sidebar --></div>
    <div class="col-md-9 p-4">
<h2>Manajemen Alat (Keranjang: <?= count($_SESSION['cart'] ?? []) ?>)</h2>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Add/Edit Form -->
        <div class="card mb-4">
            <div class="card-header"><?php echo $edit_alat ? 'Edit' : 'Tambah'; ?> Alat</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit_alat): ?>
                        <input type="hidden" name="id_alat" value="<?php echo $edit_alat['id_alat']; ?>">
                        <input type="hidden" name="edit" value="1">
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-3">
                            <label>Nama Alat</label>
                            <input type="text" name="nama_alat" class="form-control" value="<?php echo $edit_alat['nama_alat'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label>Kategori</label>
                            <select name="id_kategori" class="form-control" required>
                                <option value="">Pilih</option>
                                <?php foreach ($kategori as $kat): ?>
                                <option value="<?php echo $kat['id_kategori']; ?>" <?php echo ($edit_alat && $edit_alat['id_kategori'] == $kat['id_kategori']) ? 'selected' : ''; ?>>
                                    <?php echo $kat['nama_kategori']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>No. Alat</label>
                            <input type="text" name="no_alat" class="form-control" value="<?php echo $edit_alat['no_alat'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label>Harga/Hari</label>
                            <input type="number" name="harga_per_hari" class="form-control" value="<?php echo $edit_alat['harga_per_hari'] ?? 0; ?>" step="1000" min="0" required>
                        </div>
                        <div class="col-md-1">
                            <label>Stok</label>
                            <input type="number" name="stok" class="form-control" value="<?php echo $edit_alat['stok'] ?? 0; ?>" min="0" required>
                        </div>
                        <div class="col-md-1">
                            <label>Stok Cacat</label>
                            <input type="number" name="stok_cacat" class="form-control" value="<?php echo $edit_alat['stok_cacat'] ?? 0; ?>" min="0">
                        </div>
                        <div class="col-md-3">
                            <label>Foto Utama</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <?php if ($edit_alat && $edit_alat['foto']): ?>
                                <img src="../uploads/<?php echo $edit_alat['foto']; ?>" class="img-thumbnail mt-2" style="max-width: 100px;">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <label>Galeri Utripot (max 10 foto/video)</label>
                            <input type="file" name="galeri_utripot[]" class="form-control" multiple accept="image/*,video/*">
                            <?php if ($edit_alat && $edit_alat['galeri_utripot']): 
                                $gallery = json_decode($edit_alat['galeri_utripot'], true) ?? [];
                                foreach (array_slice($gallery, 0, 4) as $img): ?>
                                <img src="../uploads/<?php echo $img; ?>" class="img-thumbnail mt-1" style="max-width: 60px;">
                            <?php endforeach; endif; ?>
                            <small class="text-muted">Max 5MB/file</small>
                        </div>
                        <div class="col-md-3">
                            <label>Galeri Booth (max 10 foto/video)</label>
                            <input type="file" name="galeri_booth[]" class="form-control" multiple accept="image/*,video/*">
                            <?php if ($edit_alat && $edit_alat['galeri_booth']): 
                                $gallery = json_decode($edit_alat['galeri_booth'], true) ?? [];
                                foreach (array_slice($gallery, 0, 4) as $img): ?>
                                <img src="../uploads/<?php echo $img; ?>" class="img-thumbnail mt-1" style="max-width: 60px;">
                            <?php endforeach; endif; ?>
                            <small class="text-muted">Max 5MB/file</small>
                        </div>
                        <div class="col-12">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="2"><?php echo $edit_alat['deskripsi'] ?? ''; ?></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3"><?php echo $edit_alat ? 'Update' : 'Tambah'; ?></button>
                    <?php if ($edit_alat): ?>
                        <a href="alat.php" class="btn btn-secondary mt-3">Batal</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- List -->
        <div class="card">
            <div class="card-header">Daftar Alat (<?php echo count($alat_list); ?>)</div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Harga/Hari</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Gallery</th>
<th>Keranjang</th>
<th>Aksi</th>
</tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alat_list as $alat): ?>
                        <tr>
                            <td>
                                <?php if ($alat['foto']): ?>
                                    <img src="../uploads/<?php echo $alat['foto']; ?>" class="img-thumbnail" style="width: 50px;">
                                <?php else: ?>
                                    <span class="text-muted">No foto</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $alat['no_alat']; ?></td>
                            <td><?php echo $alat['nama_alat']; ?></td>
                            <td><strong><?php echo formatRupiah($alat['harga_per_hari']); ?>/hari</strong></td>
                            <td><?php echo $alat['nama_kategori']; ?></td>
                            <td>
                                <?php if (($alat['stok_cacat'] ?? 0) > 0): ?>
                                    <span class="badge bg-warning"><?php echo ($alat['stok_cacat'] ?? 0); ?> cacat</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (($alat['galeri_utripot'] ?? '') && json_decode($alat['galeri_utripot'] ?? '') ): ?>
                                    <span class="badge bg-info">Utri ✅</span>
                                <?php endif; ?>
                                <?php if (($alat['galeri_booth'] ?? '') && json_decode($alat['galeri_booth'] ?? '') ): ?>
                                    <span class="badge bg-primary">Booth ✅</span>
                                <?php endif; ?>
                            </td>
<td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="id_alat" value="<?php echo $alat['id_alat']; ?>">
                                    <input type="number" name="qty" value="1" min="1" class="form-control form-control-sm d-inline w-auto me-1" style="width:60px;">
                                    <button type="submit" name="add_cart" class="btn btn-sm btn-success"><i class="fas fa-cart-plus"></i></button>
                                </form>
                            </td>
                            <td>
                                <a href="?edit=<?php echo $alat['id_alat']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $alat['id_alat']; ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger delete-btn" onclick="return confirm('Yakin?')"><i class="fas fa-trash"></i></button>
                                </form>
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

