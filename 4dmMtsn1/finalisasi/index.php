<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

// Filter Kategori
// Kategori 1: Jalur 8, 10 + (Jalur 11 & Tahfidz Lulus) -> Otomatis Lulus jika sudah Finalisasi (Verifikasi Berkas OK)
// Kategori 2: Jalur 7, 9 + (Jalur 11 & Tahfidz Tdk Lulus) -> Lulus jika status = 'Diterima'

$query = "SELECT p.*, j.nama_jalur 
          FROM pendaftar p 
          LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
          WHERE 
          (
            -- Kategori 1: Langsung masuk jika sudah diverifikasi berkasnya (dan status bukan pending)
            (p.jalur_id IN (8, 10) OR (p.jalur_id = 11 AND p.status_tahfidz = 'Lulus'))
            AND p.status IN ('Terverifikasi', 'Diterima')
          )
          OR 
          (
            -- Kategori 2: Masuk jika sudah dinyatakan Lulus (Diterima)
            (p.jalur_id IN (7, 9) OR (p.jalur_id = 11 AND p.status_tahfidz = 'Tidak Lulus'))
            AND p.status = 'Diterima'
          )
          ORDER BY p.no_pendaftaran ASC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$lulus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung Statistik
$count_langsung = 0;
$count_seleksi = 0;
foreach ($lulus as $row) {
    if (in_array($row['jalur_id'], [8, 10]) || ($row['jalur_id'] == 11 && $row['status_tahfidz'] == 'Lulus')) {
        $count_langsung++;
    } else {
        $count_seleksi++;
    }
}
$total_lulus = count($lulus);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Kelulusan Final - Admin PMBM</title>
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
        .badge-kategori { font-size: 0.7rem; font-weight: 700; padding: 4px 10px; border-radius: 6px; }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content ps-5">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-primary fw-bold mb-1"><i class="fas fa-user-check me-2"></i>Daftar Siswa Lulus (Final)</h2>
                    <p class="text-muted">Daftar gabungan siswa kategori lulus langsung dan hasil seleksi ranking.</p>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-white bg-opacity-20 p-3 rounded-3 me-3">
                                <i class="fas fa-users-check fs-4"></i>
                            </div>
                            <div>
                                <small class="text-white-50 d-block">Total Siswa Lulus</small>
                                <h4 class="fw-bold mb-0"><?= $total_lulus ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-success text-white p-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-white bg-opacity-20 p-3 rounded-3 me-3">
                                <i class="fas fa-bolt fs-4"></i>
                            </div>
                            <div>
                                <small class="text-white-50 d-block">Lulus Langsung</small>
                                <h4 class="fw-bold mb-0"><?= $count_langsung ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-info text-white p-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-white bg-opacity-20 p-3 rounded-3 me-3">
                                <i class="fas fa-trophy fs-4"></i>
                            </div>
                            <div>
                                <small class="text-white-50 d-block">Jalur Seleksi</small>
                                <h4 class="fw-bold mb-0"><?= $count_seleksi ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-premium overflow-hidden border-0 p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="finalTable">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>No Pendaftaran</th>
                                <th>Nama Lengkap</th>
                                <th>Nomor WA Aktif</th>
                                <th>Jalur</th>
                                <th>Kategori</th>
                                <th class="text-center">Status Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lulus as $index => $row): 
                                $is_kategori1 = (in_array($row['jalur_id'], [8, 10]) || ($row['jalur_id'] == 11 && $row['status_tahfidz'] == 'Lulus'));
                            ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['no_pendaftaran']) ?></span></td>
                                <td><div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_lengkap']) ?></div></td>
                                <td><span class="text-muted fw-500"><i class="fab fa-whatsapp text-success me-1"></i> <?= htmlspecialchars($row['kontak_wa'] ?: '-') ?></span></td>
                                <td><span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3"><?= htmlspecialchars($row['nama_jalur']) ?></span></td>
                                <td>
                                    <?php if ($is_kategori1): ?>
                                        <span class="badge-kategori bg-success bg-opacity-10 text-success">
                                            <i class="fas fa-bolt me-1"></i> Lulus Langsung
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-kategori bg-primary bg-opacity-10 text-primary">
                                            <i class="fas fa-trophy me-1"></i> Jalur Seleksi
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success rounded-pill px-3">
                                        <i class="fas fa-check-circle me-1"></i> LULUS
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
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
            $('#finalTable').DataTable({
                "pageLength": 50,
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" },
                dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: [
                    { extend: 'excel', text: '<i class="fas fa-file-excel me-1"></i> Excel', className: 'btn btn-success btn-sm rounded-pill fw-bold' },
                    { extend: 'pdf', text: '<i class="fas fa-file-pdf me-1"></i> PDF', className: 'btn btn-danger btn-sm rounded-pill fw-bold' },
                    { extend: 'print', text: '<i class="fas fa-print me-1"></i> Print', className: 'btn btn-primary btn-sm rounded-pill fw-bold' }
                ]
            });
            $('.dt-buttons .btn').removeClass('btn-secondary');
        });
    </script>
</body>
</html>
