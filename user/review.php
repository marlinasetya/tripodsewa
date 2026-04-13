ye<?php
require_once '../includes/functions.php';
checkRole('user');
?>
<?php include '../includes/header.php'; ?>
<div class="row">
    <div class="col-md-3">
        <!-- Same sidebar as notifikasi -->
        <nav class="sidebar p-3 text-white">
            <!-- ... copy from above ... -->
        </nav>
    </div>
    <div class="col-md-9 p-4">
        <h2>⭐ Review Alat</h2>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <img src="../uploads/69da2301a507d.jpg" class="card-img-top" alt="Tripod">
                            <div class="card-body">
                                <h6>Tripod Manfrotto</h6>
                                <div class="stars">★★★★☆ <small>(4.5)</small></div>
                                <button class="btn btn-warning btn-sm">Tulis Review</button>
                            </div>
                        </div>
                    </div>
                    <!-- More items -->
                </div>
            </div>
        </div>
    </div>
</div><?php include '../includes/footer.php'; ?>
