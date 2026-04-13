<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa Tripod & Video Booth</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-camera"></i> Sewa Tripod Pro
            </a>
            <div class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <?php 
                    $cart_count = count(getCart());
                    $role_path = $_SESSION['role'] == 'admin' ? 'admin/' : ($_SESSION['role'] == 'petugas' ? 'petugas/' : 'user/');
                    ?>
                    <a class="nav-link" href="../<?php echo $role_path; ?>index.php">Dashboard</a>
                    <?php if ($_SESSION['role'] == 'user'): ?>
                        <a class="nav-link" href="../user/alat.php">Alat</a>
                        <a class="nav-link position-relative" href="../user/keranjang.php">
                            Keranjang
                            <?php if ($cart_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $cart_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <a class="nav-link" href="../user/peminjaman.php">Peminjaman</a>
                    <?php endif; ?>
                    <a class="nav-link" href="../logout.php">Logout (<?php echo $_SESSION['username'] ?? $_SESSION['role']; ?>)</a>
                <?php else: ?>
                    <a class="nav-link" href="../login.php">Login</a>
                    <a class="nav-link" href="../register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container-fluid mt-4">


