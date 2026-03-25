<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kuota & Kelas - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .btn-dark.d-lg-none {
            z-index: 1060 !important;
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4 text-primary fw-bold"><i class="fas fa-chart-pie me-2"></i>Kuota & Kelas</h2>

            <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4">
                <i class="fas fa-info-circle me-2"></i> Halaman ini digunakan untuk mengatur jumlah rombel dan kapasitas
                per kelas.
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <img src="https://cdni.iconscout.com/illustration/premium/thumb/classroom-training-4488804-3765116.png"
                        style="width: 250px; opacity: 0.8;">
                    <h4 class="mt-3">Manajemen Rombel</h4>
                    <p class="text-muted">Fitur ini akan segera hadir.</p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>