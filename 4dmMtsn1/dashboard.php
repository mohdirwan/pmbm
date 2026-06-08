<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/config.php';
require_once '../includes/auth_check.php';

try {
    $stmt = $pdo->query("SELECT p.*, j.nama_jalur 
                        FROM pendaftar p 
                        LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
                        ORDER BY p.id DESC");
    $students = $stmt->fetchAll();

    // Get Jalur Stats for Charts and Quota
    $stmt_stats = $pdo->query("SELECT j.nama_jalur, j.kuota, COUNT(p.id) as total_pendaftar 
                              FROM jalur_pendaftaran j 
                              LEFT JOIN pendaftar p ON j.id = p.jalur_id 
                              GROUP BY j.id");
    $jalur_stats = $stmt_stats->fetchAll();
} catch (Exception $e) {
    // Fallback if query fails (e.g. creating tables)
    $students = [];
    $jalur_stats = [];
    $error_dashboard = $e->getMessage();
}

$jalur_labels = [];
$jalur_data = [];
if (!empty($jalur_stats)) {
    foreach ($jalur_stats as $stat) {
        $jalur_labels[] = $stat['nama_jalur'];
        $jalur_data[] = (int) $stat['total_pendaftar'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Mobile Toggle Button Z-Index Fix */
        .btn-dark.d-lg-none {
            z-index: 1060 !important;
        }
    </style>
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-primary fw-bold">Dashboard Overview</h2>
                <span class="text-muted"><i class="far fa-calendar-alt me-2"></i><?= date('d F Y') ?></span>
            </div>

            <?php if (isset($error_dashboard)): ?>
                <div class="alert alert-danger">
                    Error Database: <?= htmlspecialchars($error_dashboard) ?>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div
                        class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white position-relative overflow-hidden">
                        <div class="card-body p-4">
                            <h6 class="text-white-50 text-uppercase ls-1 mb-2">Total Pendaftar</h6>
                            <h2 class="fw-bold mb-0">
                                <?php
                                try {
                                    echo $pdo->query("SELECT COUNT(*) FROM pendaftar")->fetchColumn() ?: 0;
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </h2>
                            <i class="fas fa-users position-absolute opacity-25"
                                style="font-size: 4rem; top: 10px; right: -10px;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div
                        class="card border-0 shadow-sm rounded-4 h-100 bg-success text-white position-relative overflow-hidden">
                        <div class="card-body p-4">
                            <h6 class="text-white-50 text-uppercase ls-1 mb-2">Diterima</h6>
                            <h2 class="fw-bold mb-0">
                                <?php
                                try {
                                    // Sinkronkan dengan logika finalisasi/index.php
                                    $query_lulus = "SELECT COUNT(*) FROM pendaftar p 
                                                    WHERE 
                                                    p.status = 'Diterima'
                                                    OR 
                                                    (
                                                        p.status = 'Terverifikasi' AND 
                                                        (p.jalur_id IN (8, 10) OR (p.jalur_id = 11 AND p.status_tahfidz = 'Lulus'))
                                                    )";
                                    echo $pdo->query($query_lulus)->fetchColumn() ?: 0;
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </h2>
                            <i class="fas fa-check-circle position-absolute opacity-25"
                                style="font-size: 4rem; top: 10px; right: -10px;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div
                        class="card border-0 shadow-sm rounded-4 h-100 bg-danger text-white position-relative overflow-hidden">
                        <div class="card-body p-4">
                            <h6 class="text-white-50 text-uppercase ls-1 mb-2">Ditolak</h6>
                            <h2 class="fw-bold mb-0">
                                <?php
                                try {
                                    echo $pdo->query("SELECT COUNT(*) FROM pendaftar WHERE status='Ditolak'")->fetchColumn() ?: 0;
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </h2>
                            <i class="fas fa-times-circle position-absolute opacity-25"
                                style="font-size: 4rem; top: 10px; right: -10px;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div
                        class="card border-0 shadow-sm rounded-4 h-100 bg-info text-white position-relative overflow-hidden">
                        <div class="card-body p-4">
                            <h6 class="text-white-50 text-uppercase ls-1 mb-2">Pending</h6>
                            <h2 class="fw-bold mb-0">
                                <?php
                                try {
                                    echo $pdo->query("SELECT COUNT(*) FROM pendaftar WHERE status='Pending'")->fetchColumn() ?: 0;
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </h2>
                            <i class="fas fa-clock position-absolute opacity-25"
                                style="font-size: 4rem; top: 10px; right: -10px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <!-- Chart: Pendaftar per Jalur -->
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 pt-4 px-4">
                            <h5 class="fw-bold"><i class="fas fa-chart-pie me-2 text-primary"></i>Statistik Jalur
                                Pendaftaran</h5>
                        </div>
                        <div class="card-body">
                            <div style="height: 250px; position: relative;">
                                <canvas id="jalurChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <!-- Recent Registrations Table -->
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div
                            class="card-header bg-white border-0 pt-4 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                            <h5 class="fw-bold mb-0">Data Pendaftar Terbaru</h5>
                            <a href="pendaftar/index.php"
                                class="btn btn-sm btn-outline-primary rounded-pill px-3 w-auto">Lihat
                                Semua</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tablePendaftar" class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>No</th>
                                            <th>No Pendaftaran</th>
                                            <th>Nama Murid</th>
                                            <th>Jalur</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($students)): ?>
                                            <?php foreach ($students as $index => $s): ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td class="fw-bold"><?= htmlspecialchars($s['no_pendaftaran']) ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-circle me-2 bg-light text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                                                style="width: 35px; height: 35px;">
                                                                <?= substr($s['nama_lengkap'], 0, 1) ?>
                                                            </div>
                                                            <span class="text-truncate"
                                                                style="max-width: 150px;"><?= htmlspecialchars($s['nama_lengkap']) ?></span>
                                                        </div>
                                                    </td>
                                                    <td><span
                                                            class="badge bg-secondary rounded-pill fw-normal"><?= htmlspecialchars($s['nama_jalur'] ?? 'N/A') ?></span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $status = $s['status'];
                                                        switch ($status) {
                                                            case 'Terverifikasi':
                                                            case 'Diterima':
                                                                $badgeClass = 'success';
                                                                break;
                                                            case 'Ditolak':
                                                                $badgeClass = 'danger';
                                                                break;
                                                            default:
                                                                $badgeClass = 'warning text-dark';
                                                                break;
                                                        }
                                                        ?>
                                                        <span
                                                            class="badge bg-<?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
                                                    </td>
                                                    <td class="text-nowrap">
                                                        <a href="pendaftar/detail_pendaftar.php?id=<?= $s['id'] ?>"
                                                            class="btn btn-sm btn-light text-primary" title="Detail"><i
                                                                class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data
                                                    pendaftar.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function () {
            $('#tablePendaftar').DataTable({
                "pageLength": 5,
                "lengthChange": false,
                "language": {
                    "search": "Cari Murid:"
                }
            });

            // Jalur Chart
            const ctx = document.getElementById('jalurChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($jalur_labels) ?>,
                    datasets: [{
                        data: <?= json_encode($jalur_data) ?>,
                        backgroundColor: ['#0d6efd', '#ffc107', '#0dcaf0', '#6c757d', '#198754', '#dc3545'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' }
                    }
                }
            });
        });
    </script>
</body>

</html>