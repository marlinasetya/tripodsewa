<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirectByRole();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa Tripod & Video Booth - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h2 class="mb-4"><i class="fas fa-camera text-primary"></i> Sewa Tripod & Video Booth</h2>
                        <p class="lead">Silakan login untuk melanjutkan</p>
                        <a href="login.php" class="btn btn-primary btn-lg">Login</a>
                        <hr>
                        <a href="register.php" class="btn btn-outline-secondary">Daftar Akun Baru</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

