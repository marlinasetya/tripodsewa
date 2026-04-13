<?php
require_once '../includes/functions.php';
checkRole('admin');

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $nama = sanitize($_POST['nama']);
        $email = sanitize($_POST['email']);
        $password = password_hash($_POST['password'] ?: 'password', PASSWORD_DEFAULT);
        $role = sanitize($_POST['role']);
        
        $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nama, $email, $password, $role])) {
            $message = 'Pengguna berhasil ditambahkan!';
        }
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = ? AND role != 'admin'");
        if ($stmt->execute([$id])) {
            $message = 'Pengguna dihapus!';
        }
    }
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY id_user DESC");
$users = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <?php include 'sidebar.php'; // Will create later ?>
    </div>
    <div class="col-md-9 p-4">
        <h2>Manajemen Pengguna</h2>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Add Form -->
        <div class="card mb-4">
            <div class="card-header">Tambah Pengguna</div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" name="nama" class="form-control" placeholder="Nama" required>
                        </div>
                        <div class="col-md-3">
                            <input type="email" name="email" class="form-control" placeholder="Email" required>
                        </div>
                        <div class="col-md-2">
                            <input type="password" name="password" class="form-control" placeholder="Password">
                        </div>
                        <div class="col-md-2">
                            <select name="role" class="form-control">
                                <option value="user">User</option>
                                <option value="petugas">Petugas</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="add" class="btn btn-primary w-100">Tambah</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- List -->
        <div class="card">
            <div class="card-header">Daftar Pengguna</div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id_user']; ?></td>
                            <td><?php echo $user['nama']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'petugas' ? 'warning' : 'success'); ?>"><?php echo ucfirst($user['role']); ?></span></td>
                            <td>
                                <?php if ($user['role'] !== 'admin'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $user['id_user']; ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger delete-btn" onclick="return confirm('Yakin?')"><i class="fas fa-trash"></i></button>
                                </form>
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

