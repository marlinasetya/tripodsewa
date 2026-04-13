<?php
require_once '../includes/functions.php';
checkRole('admin');

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $nama = sanitize($_POST['nama_kategori']);
        $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
        if ($stmt->execute([$nama])) {
            $message = 'Kategori berhasil ditambahkan!';
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM kategori WHERE id_kategori = ?");
        if ($stmt->execute([$id])) {
            $message = 'Kategori dihapus!';
        }
    }
}

$stmt = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori");
$kategori = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <!-- Sidebar akan dibuat terpisah -->
    </div>
    <div class="col-md-9 p-4">
        <h2>Manajemen Kategori</h2>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Add Form -->
        <div class="card mb-4">
            <div class="card-header">Tambah Kategori</div>
            <div class="card-body">
                <form method="POST">
                    <div class="input-group">
                        <input type="text" name="nama_kategori" class="form-control" placeholder="Nama kategori" required>
                        <button type="submit" name="add" class="btn btn-primary">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- List -->
        <div class="card">
            <div class="card-header">Daftar Kategori (<?php echo count($kategori); ?>)</div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kategori as $kat): ?>
                        <tr>
                            <td><?php echo $kat['id_kategori']; ?></td>
                            <td><?php echo $kat['nama_kategori']; ?></td>
                            <td>
                                <form method="POST" style="display:inline;" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo $kat['id_kategori']; ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger delete-btn" onclick="return confirm('Yakin hapus kategori ini? Alat terkait juga akan terhapus!')"><i class="fas fa-trash"></i></button>
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

