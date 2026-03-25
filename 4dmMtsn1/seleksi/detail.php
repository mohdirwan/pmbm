<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';

$title = "";
$condition = "";
if ($type == 'tanpa_ujian') {
    $title = "Detail Siswa (Rekap Tanpa Ujian)";
    $condition = "WHERE j.id IN (8, 10) OR (j.id = 11 AND p.status_tahfidz = 'Lulus')";
} elseif ($type == 'dengan_ujian') {
    $title = "Detail Siswa (Rekap Dengan Ujian)";
    $condition = "WHERE j.id IN (7, 9) OR (j.id = 11 AND p.status_tahfidz = 'Tidak Lulus')";
} else {
    header("Location: index.php");
    exit();
}

// Ensure the condition is safe, though it's hardcoded based on $type
$query = "SELECT p.*, j.nama_jalur 
          FROM pendaftar p 
          LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
          $condition
          ORDER BY p.id ASC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?> - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

    <style>
        body { font-family: 'Outfit', sans-serif; }
        .main-content { margin-left: 260px; padding: 40px; background: #f4f7fe; min-height: 100vh; }
        .card-premium { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); background: #fff; }
        .table thead th { background-color: #f1f4f9; color: #555; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; border: none; padding: 15px; }
        .table tbody td { padding: 15px; vertical-align: middle; border-color: #f1f4f9; }
        .dataTables_wrapper .row { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content ps-5">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="index.php" class="btn btn-sm btn-light mb-3 text-muted">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Seleksi
                    </a>
                    <h2 class="text-primary fw-bold mb-1"><i class="fas fa-users me-2"></i><?= $title ?></h2>
                    <p class="text-muted">Daftar siswa sesuai dengan rekap yang dipilih.</p>
                </div>
            </div>

            <div class="card card-premium overflow-hidden border-0 p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="detailTable">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>No Pendaftaran</th>
                                <th>Nama Lengkap</th>
                                <th>Nomor WA Aktif</th>
                                <th>Jalur</th>
                                <th>Status Pendaftaran</th>
                                <th>Tahfidz</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data)): ?>
                                <?php foreach ($data as $index => $row): ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['no_pendaftaran']) ?></span></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                    </td>
                                    <td><span class="text-muted fw-500"><i class="fab fa-whatsapp text-success me-1"></i> <?= htmlspecialchars($row['kontak_wa'] ?: '-') ?></span></td>
                                    <td><span class="badge bg-secondary rounded-pill px-3"><?= htmlspecialchars($row['nama_jalur'] ?: 'Umum') ?></span></td>
                                    <td>
                                        <?php
                                        $sc = 'secondary-subtle text-secondary';
                                        if ($row['status'] == 'Terverifikasi') $sc = 'success-subtle text-success';
                                        elseif ($row['status'] == 'Diterima') $sc = 'success bg-success text-white';
                                        elseif ($row['status'] == 'Ditolak') $sc = 'danger-subtle text-danger';
                                        ?>
                                        <span class="badge rounded-pill <?= $sc ?> border" style="font-size: 0.75rem; font-weight: 600;">
                                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                            <?= htmlspecialchars($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['status_tahfidz']): ?>
                                            <?php 
                                            $tahfidz_bg = 'bg-secondary';
                                            if ($row['status_tahfidz'] == 'Lulus') $tahfidz_bg = 'bg-success';
                                            elseif ($row['status_tahfidz'] == 'Tidak Lulus') $tahfidz_bg = 'bg-danger';
                                            elseif ($row['status_tahfidz'] == 'Pending') $tahfidz_bg = 'bg-warning text-dark';
                                            ?>
                                            <span class="badge <?= $tahfidz_bg ?> rounded-pill">
                                                <?= htmlspecialchars($row['status_tahfidz']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#detailTable').DataTable({
                "pageLength": 50,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success btn-sm rounded-pill fw-bold'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                        className: 'btn btn-danger btn-sm rounded-pill fw-bold'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print me-1"></i> Print',
                        className: 'btn btn-primary btn-sm rounded-pill fw-bold'
                    }
                ]
            });
            // Update button styles
            $('.dt-buttons .btn').removeClass('btn-secondary');
        });
    </script>
</body>
</html>
