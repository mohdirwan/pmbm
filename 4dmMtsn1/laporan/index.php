<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan & Export - Admin PMBM</title>
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
            <h2 class="mb-4 text-primary fw-bold"><i class="fas fa-file-excel me-2"></i>Laporan & Export</h2>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                        <h5>Export Data Murid</h5>
                        <p class="text-muted small">Unduh data semua pendaftar dalam format Excel untuk analisis lebih
                            lanjut.</p>
                        <button class="btn btn-success w-100 mt-auto"><i class="fas fa-file-excel me-2"></i>Download
                            .XLSX</button>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                        <h5>Laporan Statistik</h5>
                        <p class="text-muted small">Cetak laporan statistik pendaftaran per jalur dan jenis kelamin.</p>
                        <button class="btn btn-primary w-100 mt-auto"><i class="fas fa-print me-2"></i>Cetak
                            PDF</button>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                        <h5>Rekap Daftar Ulang</h5>
                        <p class="text-muted small">Laporan murid yang sudah dan belum melakukan daftar ulang.</p>
                        <button class="btn btn-info text-white w-100 mt-auto"><i class="fas fa-file-alt me-2"></i>Lihat
                            Laporan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>