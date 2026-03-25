<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

// Prepare query for scores
$query = "SELECT p.id, p.no_pendaftaran, p.nama_lengkap, p.nisn, p.nilai_ujian, j.nama_jalur 
          FROM pendaftar p 
          LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
          WHERE p.status != 'Ditolak' 
          ORDER BY p.nilai_ujian DESC, p.nama_lengkap ASC";
$scores = $pdo->query($query)->fetchAll();

// Statistics
$avg_score = $pdo->query("SELECT AVG(nilai_ujian) FROM pendaftar WHERE status != 'Ditolak' AND nilai_ujian > 0")->fetchColumn() ?: 0;
$max_score = $pdo->query("SELECT MAX(nilai_ujian) FROM pendaftar WHERE status != 'Ditolak'")->fetchColumn() ?: 0;
$total_tested = $pdo->query("SELECT COUNT(*) FROM pendaftar WHERE status != 'Ditolak' AND nilai_ujian > 0")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Nilai Ujian CBT - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .main-content {
            margin-left: 260px;
            padding: 30px;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .card-premium {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .score-badge {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0b2c24;
        }

        .rank-badge {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.8rem;
        }

        .bg-gold {
            background: #FFD700;
            color: #000;
        }

        .bg-silver {
            background: #C0C0C0;
            color: #000;
        }

        .bg-bronze {
            background: #CD7F32;
            color: #fff;
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content ps-5">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-primary fw-bold mb-1">Hasil Ujian CBT External</h2>
                    <p class="text-muted small">Daftar rekapitulasi nilai yang telah di-import dari sistem CBT.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="index.php" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="fas fa-file-import me-2"></i> Import Nilai Baru
                    </a>
                    <button class="btn btn-success rounded-pill px-4">
                        <i class="fas fa-file-excel me-2"></i> Export Excel
                    </button>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card card-premium p-3 border-start border-primary border-4">
                        <div class="d-flex align-items-center">
                            <div class="p-3 bg-primary bg-opacity-10 rounded-4 me-3">
                                <i class="fas fa-users text-primary fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase fw-bold ls-1"
                                    style="font-size: 0.7rem;">Sudah Ujian</small>
                                <h4 class="fw-bold mb-0">
                                    <?= $total_tested ?> <span class="text-muted fs-6 fw-normal">Murid</span>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-premium p-3 border-start border-success border-4">
                        <div class="d-flex align-items-center">
                            <div class="p-3 bg-success bg-opacity-10 rounded-4 me-3">
                                <i class="fas fa-chart-line text-success fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase fw-bold ls-1"
                                    style="font-size: 0.7rem;">Rata-rata Nilai</small>
                                <h4 class="fw-bold mb-0 text-success">
                                    <?= number_format($avg_score, 2) ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-premium p-3 border-start border-warning border-4">
                        <div class="d-flex align-items-center">
                            <div class="p-3 bg-warning bg-opacity-10 rounded-4 me-3">
                                <i class="fas fa-trophy text-warning fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase fw-bold ls-1"
                                    style="font-size: 0.7rem;">Nilai Tertinggi</small>
                                <h4 class="fw-bold mb-0 text-warning">
                                    <?= $max_score ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-premium overflow-hidden border-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 border-0">Rank</th>
                                <th class="border-0">No. Daftar</th>
                                <th class="border-0">Nama Murid</th>
                                <th class="border-0">Jalur</th>
                                <th class="text-center border-0">Nilai CBT</th>
                                <th class="text-end pe-4 border-0">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($scores)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Belum ada data nilai yang masuk.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($scores as $index => $s): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <?php
                                            $rankClass = '';
                                            if ($index == 0 && $s['nilai_ujian'] > 0)
                                                $rankClass = 'bg-gold';
                                            elseif ($index == 1 && $s['nilai_ujian'] > 0)
                                                $rankClass = 'bg-silver';
                                            elseif ($index == 2 && $s['nilai_ujian'] > 0)
                                                $rankClass = 'bg-bronze';
                                            ?>
                                            <span class="rank-badge <?= $rankClass ?>">
                                                <?= $index + 1 ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold text-primary">
                                            <?= $s['no_pendaftaran'] ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <?= htmlspecialchars($s['nama_lengkap']) ?>
                                            </div>
                                            <div class="small text-muted">NISN:
                                                <?= $s['nisn'] ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-3">
                                                <?= $s['nama_jalur'] ?: 'Umum' ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="score-badge">
                                                <?= $s['nilai_ujian'] ?: '<span class="text-muted opacity-50 fw-normal fs-6">Belum Ujian</span>' ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="../pendaftar/detail_pendaftar.php?id=<?= $s['id'] ?>"
                                                class="btn btn-sm btn-light text-primary rounded-3">
                                                <i class="fas fa-eye me-1"></i> Detail
                                            </a>
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
</body>

</html>