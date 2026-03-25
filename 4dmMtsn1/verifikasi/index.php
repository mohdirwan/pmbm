<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

// Get verified students (already confirmed)
$stmt = $pdo->query("SELECT p.*, j.nama_jalur 
                    FROM pendaftar p 
                    LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
                    WHERE p.status = 'Terverifikasi' 
                    ORDER BY p.tanggal_daftar ASC");
$students = $stmt->fetchAll();

// Get Statistics per Jalur
$stats_stmt = $pdo->query("SELECT 
                            j.id as jalur_id,
                            j.nama_jalur,
                            COUNT(p.id) as total_pendaftar,
                            SUM(CASE WHEN p.status = 'Terverifikasi' THEN 1 ELSE 0 END) as total_verifikasi,
                            SUM(CASE WHEN p.status = 'Ditolak' THEN 1 ELSE 0 END) as total_ditolak,
                            SUM(CASE WHEN p.status_tahfidz = 'Lulus' THEN 1 ELSE 0 END) as total_lulus_tahfidz,
                            SUM(CASE WHEN p.status_tahfidz = 'Tidak Lulus' THEN 1 ELSE 0 END) as total_tidak_lulus_tahfidz
                        FROM jalur_pendaftaran j
                        LEFT JOIN pendaftar p ON j.id = p.jalur_id
                        GROUP BY j.id, j.nama_jalur");
$jalur_stats = $stats_stmt->fetchAll();

// Calculate Grand Totals
$grand_total_pendaftar = 0;
$grand_total_verifikasi = 0;
$grand_total_ditolak = 0;
foreach ($jalur_stats as $stat) {
    $grand_total_pendaftar += $stat['total_pendaftar'];
    $grand_total_verifikasi += $stat['total_verifikasi'];
    $grand_total_ditolak += $stat['total_ditolak'];
}

// 1. Rekap Tanpa Ujian (Jalur 8 & 10 + Tahfidz Lulus)
$tanpa_ujian_stmt = $pdo->query("SELECT 
    COUNT(id) as total,
    SUM(CASE WHEN status = 'Terverifikasi' THEN 1 ELSE 0 END) as verif,
    SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) as tolak
FROM pendaftar 
WHERE jalur_id IN (8, 10) 
   OR (jalur_id = 11 AND status_tahfidz = 'Lulus')");
$rekap_tanpa_ujian = $tanpa_ujian_stmt->fetch();

// 2. Rekap Dengan Ujian (Jalur 7 & 9 + Tahfidz Tidak Lulus)
$dengan_ujian_stmt = $pdo->query("SELECT 
    COUNT(id) as total,
    SUM(CASE WHEN status = 'Terverifikasi' THEN 1 ELSE 0 END) as verif,
    SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) as tolak
FROM pendaftar 
WHERE jalur_id IN (7, 9) 
   OR (jalur_id = 11 AND status_tahfidz = 'Tidak Lulus')");
$rekap_dengan_ujian = $dengan_ujian_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Verifikasi Data - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .main-content {
            margin-left: 260px;
            padding: 30px;
            background: #f8f9fa;
            min-height: 100vh;
            transition: all 0.3s;
        }

        .card-premium {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .card-premium:hover {
            transform: translateY(-5px);
        }

        .btn-premium {
            background: linear-gradient(135deg, #0b2c24, #1a4d40);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(11, 44, 36, 0.2);
        }

        .btn-premium:hover {
            color: white;
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(11, 44, 36, 0.3);
        }

        .btn-dark.d-lg-none {
            z-index: 1060 !important;
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-1 text-primary fw-bold"><i class="fas fa-clipboard-check me-2"></i>Verifikasi Data</h2>
            <p class="text-muted mb-4">Summary pendaftaran dan daftar murid yang menunggu tindakan verifikasi detail.</p>

            <!-- Statistics Summary -->
            <div class="row g-3 mb-4">
                <?php foreach ($jalur_stats as $stat): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="card card-premium h-100 border-start border-4 border-primary">
                            <div class="card-body p-3">
                                <h6 class="fw-bold text-dark small mb-3 text-truncate"><?= htmlspecialchars($stat['nama_jalur']) ?></h6>
                                <div class="row g-2 text-center">
                                    <div class="col-4">
                                        <div class="small text-muted">Total</div>
                                        <div class="fw-bold text-primary"><?= $stat['total_pendaftar'] ?></div>
                                    </div>
                                    <div class="col-4 border-start">
                                        <div class="small text-muted">Verif</div>
                                        <div class="fw-bold text-success"><?= $stat['total_verifikasi'] ?></div>
                                    </div>
                                    <div class="col-4 border-start">
                                        <div class="small text-muted">Tolak</div>
                                        <div class="fw-bold text-danger"><?= $stat['total_ditolak'] ?></div>
                                    </div>
                                </div>

                                <?php if ($stat['jalur_id'] == 11): ?>
                                    <hr class="my-2 opacity-25">
                                    <div class="row g-2 text-center">
                                        <div class="col-6">
                                            <div class="small text-muted" style="font-size: 0.65rem;">Lulus Tahfidz</div>
                                            <div class="fw-bold text-success small"><?= $stat['total_lulus_tahfidz'] ?></div>
                                        </div>
                                        <div class="col-6 border-start">
                                            <div class="small text-muted" style="font-size: 0.65rem;">Tdk Lulus</div>
                                            <div class="fw-bold text-danger small"><?= $stat['total_tidak_lulus_tahfidz'] ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Grand Total Rekap -->
                <div class="col-md-4 col-lg-3">
                    <div class="card card-premium h-100 border-start border-4 border-dark bg-dark bg-opacity-10">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-dark small mb-3 text-underline">REKAP KESELURUHAN</h6>
                            <div class="row g-2 text-center">
                                <div class="col-4">
                                    <div class="small text-muted text-nowrap">Total</div>
                                    <div class="fw-bold text-primary"><?= $grand_total_pendaftar ?></div>
                                </div>
                                <div class="col-4 border-start">
                                    <div class="small text-muted text-nowrap">Verif</div>
                                    <div class="fw-bold text-success"><?= $grand_total_verifikasi ?></div>
                                </div>
                                <div class="col-4 border-start">
                                    <div class="small text-muted text-nowrap">Tolak</div>
                                    <div class="fw-bold text-danger"><?= $grand_total_ditolak ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rekap Tanpa Ujian -->
                <div class="col-md-4 col-lg-3">
                    <div class="card card-premium h-100 border-start border-4 border-success bg-success bg-opacity-10">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-success small mb-3">REKAP TANPA UJIAN</h6>
                            <div class="row g-2 text-center">
                                <div class="col-4">
                                    <div class="small text-muted text-nowrap">Total</div>
                                    <div class="fw-bold text-primary"><?= $rekap_tanpa_ujian['total'] ?></div>
                                </div>
                                <div class="col-4 border-start">
                                    <div class="small text-muted text-nowrap">Verif</div>
                                    <div class="fw-bold text-success"><?= $rekap_tanpa_ujian['verif'] ?></div>
                                </div>
                                <div class="col-4 border-start">
                                    <div class="small text-muted text-nowrap">Tolak</div>
                                    <div class="fw-bold text-danger"><?= $rekap_tanpa_ujian['tolak'] ?></div>
                                </div>
                            </div>
                            <div class="mt-2 small text-muted italic" style="font-size: 0.65rem;">
                                *Jalur Tanpa Tes & Tahfidz Lulus
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rekap Dengan Ujian -->
                <div class="col-md-4 col-lg-3">
                    <div class="card card-premium h-100 border-start border-4 border-warning bg-warning bg-opacity-10">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-dark small mb-3 text-nowrap">REKAP DENGAN UJIAN</h6>
                            <div class="row g-2 text-center">
                                <div class="col-4">
                                    <div class="small text-muted text-nowrap">Total</div>
                                    <div class="fw-bold text-primary"><?= $rekap_dengan_ujian['total'] ?></div>
                                </div>
                                <div class="col-4 border-start">
                                    <div class="small text-muted text-nowrap">Verif</div>
                                    <div class="fw-bold text-success"><?= $rekap_dengan_ujian['verif'] ?></div>
                                </div>
                                <div class="col-4 border-start">
                                    <div class="small text-muted text-nowrap">Tolak</div>
                                    <div class="fw-bold text-danger"><?= $rekap_dengan_ujian['tolak'] ?></div>
                                </div>
                            </div>
                            <div class="mt-2 small text-muted italic" style="font-size: 0.65rem;">
                                *Jalur Tes & Tahfidz Tdk Lulus
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-premium overflow-hidden">
                <div class="card-header bg-white py-4 px-4 border-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-check-circle me-2 text-success"></i> Daftar
                            Terverifikasi</h6>
                        <span class="badge bg-success-subtle text-success rounded-pill px-3"><?= count($students) ?>
                            Data</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0">No. Daftar</th>
                                    <th class="border-0">Nama Murid</th>
                                    <th class="border-0">Jalur</th>
                                    <th class="border-0">Tanggal Daftar</th>
                                    <th class="text-end pe-4 border-0">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted border-0">
                                            <div class="py-4">
                                                <i class="fas fa-clipboard-list fa-3x mb-3 text-muted opacity-50"></i>
                                                <h5 class="fw-bold text-dark">Belum Ada Data Terverifikasi</h5>
                                                <p class="mb-0">Belum ada murid yang terverifikasi.<br>Gunakan tombol
                                                    <strong>Verifikasi Otomatis</strong> di halaman <a
                                                        href="../pendaftar/">Data Pendaftar</a>.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $s): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-primary">
                                                <?= $s['no_pendaftaran'] ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?= $s['nama_lengkap'] ?></div>
                                                <div class="small text-muted"><?= $s['nisn'] ?></div>
                                            </td>
                                            <td><span
                                                    class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-3">
                                                    <?= htmlspecialchars($s['nama_jalur'] ?? 'N/A') ?>
                                                </span></td>
                                            <td>
                                                <div class="small fw-medium">
                                                    <?= date('d M Y', strtotime($s['tanggal_daftar'])) ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <?= date('H:i', strtotime($s['tanggal_daftar'])) ?> WIB
                                                </div>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="../pendaftar/detail_pendaftar.php?id=<?= $s['id'] ?>"
                                                    class="btn btn-premium btn-sm">Verifikasi</a>
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
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>