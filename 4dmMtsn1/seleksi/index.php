<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

// Handle Weights Update
if (isset($_POST['update_weights'])) {
    $weight_rapor = (int) $_POST['weight_rapor'];
    $weight_cbt = (int) $_POST['weight_cbt'];

    // Save to settings
    $stmt1 = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('weight_rapor', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt1->execute([$weight_rapor, $weight_rapor]);

    $stmt2 = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('weight_cbt', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt2->execute([$weight_cbt, $weight_cbt]);

    header("Location: index.php?msg=weights_saved");
    exit();
}

// Fetch Weights
$w_rapor = (int) get_setting('weight_rapor', 50);
$w_cbt = (int) get_setting('weight_cbt', 50);

// Handle Penetapan Kelulusan
if (isset($_POST['tetapkan_kelulusan'])) {
    $batas_ranking = (int)$_POST['batas_ranking'];
    $password = $_POST['admin_password'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $query_all = "SELECT p.id
                      FROM pendaftar p
                      WHERE p.status IN ('Terverifikasi', 'Diterima', 'Ditolak')
                      AND (p.jalur_id IN (7, 9) OR (p.jalur_id = 11 AND p.status_tahfidz = 'Tidak Lulus'))
                      ORDER BY ((p.nilai_rapor_rata2 * $w_rapor / 100) + (p.nilai_ujian * $w_cbt / 100)) DESC, p.nilai_rapor_rata2 DESC";
        
        $stmt_all = $pdo->prepare($query_all);
        $stmt_all->execute();
        $all_ids = $stmt_all->fetchAll(PDO::FETCH_COLUMN);

        $top_ids = array_slice($all_ids, 0, $batas_ranking);
        $bottom_ids = array_slice($all_ids, $batas_ranking);

        if (!empty($top_ids)) {
            $inQueryTop = implode(',', array_fill(0, count($top_ids), '?'));
            $update_lulus = $pdo->prepare("UPDATE pendaftar SET status = 'Diterima' WHERE id IN ($inQueryTop)");
            $update_lulus->execute($top_ids);
        }

        if (!empty($bottom_ids)) {
            $inQueryBottom = implode(',', array_fill(0, count($bottom_ids), '?'));
            $update_tolak = $pdo->prepare("UPDATE pendaftar SET status = 'Ditolak' WHERE id IN ($inQueryBottom)");
            $update_tolak->execute($bottom_ids);
        }

        header("Location: index.php?msg=kelulusan_ditetapkan");
        exit();
    } else {
        header("Location: index.php?msg=wrong_pass");
        exit();
    }
}

// Ambil data pendaftar yang sudah diverifikasi (atau semua)
// Hitung nilai akhir berdasarkan bobot persentase
$query = "SELECT p.id, p.nama_lengkap, p.no_pendaftaran, p.jalur_id, p.nilai_rapor_rata2, p.nilai_ujian, p.status, j.nama_jalur,
          ((p.nilai_rapor_rata2 * $w_rapor / 100) + (p.nilai_ujian * $w_cbt / 100)) as nilai_akhir
          FROM pendaftar p
          LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id
          WHERE p.status IN ('Terverifikasi', 'Diterima', 'Ditolak')
          AND (p.jalur_id IN (7, 9) OR (p.jalur_id = 11 AND p.status_tahfidz = 'Tidak Lulus'))
          ORDER BY nilai_akhir DESC, p.nilai_rapor_rata2 DESC";
$pendaftar = $pdo->query($query)->fetchAll();

// Handle Status Update
if (isset($_POST['update_status']) && isset($_POST['status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE pendaftar SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $id])) {
        // Redirect with success message to refresh list
        header("Location: index.php?msg=success");
        exit();
    }
}

// --- STATS LOGIC FOR SUMMARY CARDS ---
// Get Statistics per Jalur
$stats_stmt = $pdo->query("SELECT 
                            j.id as jalur_id,
                            j.nama_jalur,
                            COUNT(p.id) as total_pendaftar,
                            SUM(CASE WHEN p.status = 'Terverifikasi' OR p.status = 'Diterima' THEN 1 ELSE 0 END) as total_verifikasi,
                            SUM(CASE WHEN p.status = 'Ditolak' THEN 1 ELSE 0 END) as total_ditolak,
                            SUM(CASE WHEN p.status_tahfidz = 'Lulus' THEN 1 ELSE 0 END) as total_lulus_tahfidz,
                            SUM(CASE WHEN p.status_tahfidz = 'Tidak Lulus' THEN 1 ELSE 0 END) as total_tidak_lulus_tahfidz
                        FROM jalur_pendaftaran j
                        LEFT JOIN pendaftar p ON j.id = p.jalur_id
                        GROUP BY j.id, j.nama_jalur");
$jalur_stats = $stats_stmt->fetchAll();

// Calculate Grand Totals
$grand_total_all = 0;
$grand_total_verifikasi = 0;
$grand_total_ditolak = 0;
foreach ($jalur_stats as $stat) {
    $grand_total_all += $stat['total_pendaftar'];
    $grand_total_verifikasi += $stat['total_verifikasi'];
    $grand_total_ditolak += $stat['total_ditolak'];
}

// 1. Rekap Tanpa Ujian (Jalur 8 & 10 + Tahfidz Lulus)
$tanpa_ujian_stmt = $pdo->query("SELECT 
    COUNT(id) as total,
    SUM(CASE WHEN status = 'Terverifikasi' OR status = 'Diterima' THEN 1 ELSE 0 END) as verif,
    SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) as tolak
FROM pendaftar 
WHERE jalur_id IN (8, 10) 
   OR (jalur_id = 11 AND status_tahfidz = 'Lulus')");
$rekap_tanpa_ujian = $tanpa_ujian_stmt->fetch();

// 2. Rekap Dengan Ujian (Jalur 7 & 9 + Tahfidz Tidak Lulus)
$dengan_ujian_stmt = $pdo->query("SELECT 
    COUNT(id) as total,
    SUM(CASE WHEN status = 'Terverifikasi' OR status = 'Diterima' THEN 1 ELSE 0 END) as verif,
    SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) as tolak
FROM pendaftar 
WHERE jalur_id IN (7, 9) 
   OR (jalur_id = 11 AND status_tahfidz = 'Tidak Lulus')");
$rekap_dengan_ujian = $dengan_ujian_stmt->fetch();

// Handle Clear All Scores
if (isset($_POST['clear_scores'])) {
    $password = $_POST['admin_password'];
    $user_id = $_SESSION['user_id'];

    // Get admin password hash
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        // Reset ALL Nilai Ujian CBT for pendaftar
        $pdo->query("UPDATE pendaftar SET nilai_ujian = 0");
        header("Location: index.php?msg=cleared");
        exit();
    } else {
        header("Location: index.php?msg=wrong_pass");
        exit();
    }
}

// Handle Individual Score Update
if (isset($_POST['update_individual_score'])) {
    $id = $_POST['id'];
    $n_rapor = $_POST['nilai_rapor'];
    $n_cbt = $_POST['nilai_cbt'];
    $password = $_POST['admin_password'];
    $user_id = $_SESSION['user_id'];

    // Get admin password hash
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $stmt_update = $pdo->prepare("UPDATE pendaftar SET nilai_rapor_rata2 = ?, nilai_ujian = ? WHERE id = ?");
        if ($stmt_update->execute([$n_rapor, $n_cbt, $id])) {
            header("Location: index.php?msg=score_updated");
            exit();
        }
    } else {
        header("Location: index.php?msg=wrong_pass");
        exit();
    }
}

// Cek apakah sudah ada nilai CBT yang masuk untuk kategori seleksi
$check_cbt_stmt = $pdo->query("SELECT COUNT(*) FROM pendaftar WHERE (jalur_id IN (7, 9) OR (jalur_id = 11 AND status_tahfidz = 'Tidak Lulus')) AND nilai_ujian > 0 AND status IN ('Terverifikasi', 'Diterima', 'Ditolak')");
$has_cbt_scores = $check_cbt_stmt->fetchColumn() > 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Seleksi & Ranking - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .main-content {
            margin-left: 260px;
            padding: 40px;
            background: #f4f7fe;
            min-height: 100vh;
        }

        .card-premium {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .table thead th {
            background-color: #f1f4f9;
            color: #555;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            border: none;
            padding: 15px;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-color: #f1f4f9;
        }

        .badge-ranking {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-weight: 800;
            font-size: 0.9rem;
        }

        .rank-1 {
            background: #FFD700;
            color: #000;
        }

        .rank-2 {
            background: #C0C0C0;
            color: #000;
        }

        .rank-3 {
            background: #CD7F32;
            color: #fff;
        }

        .rank-other {
            background: #f1f4f9;
            color: #333;
        }

        .nilai-akhir-box {
            background: #eef2ff;
            color: #4338ca;
            font-weight: 800;
            padding: 5px 12px;
            border-radius: 8px;
            display: inline-block;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content ps-5">
        <div class="container-fluid">

            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] == 'cleared'): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i> Semua Nilai CBT telah berhasil direset ke 0.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($_GET['msg'] == 'wrong_pass'): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> Konfirmasi Gagal: Password Admin Salah!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($_GET['msg'] == 'score_updated'): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i> Nilai pendaftar berhasil diperbarui.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($_GET['msg'] == 'kelulusan_ditetapkan'): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i> Kelulusan berhasil ditetapkan untuk ranking tersebut.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-primary fw-bold mb-1"><i class="fas fa-trophy me-2"></i>Hasil Seleksi & Ranking</h2>
                    <p class="text-muted">Peringkat murid berdasarkan gabungan Nilai Rapor dan Nilai CBT.</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-success rounded-pill px-4 no-print" onclick="checkCbtBeforeModal()">
                        <i class="fas fa-check-double me-2"></i> Penetapan Kelulusan
                    </button>
                    <button class="btn btn-outline-danger rounded-pill px-4 no-print" data-bs-toggle="modal"
                        data-bs-target="#modalClear">
                        <i class="fas fa-trash-alt me-2"></i> Clear CBT List
                    </button>
                    <button class="btn btn-primary rounded-pill px-4 no-print" onclick="window.print()">
                        <i class="fas fa-print me-2"></i> Cetak Laporan
                    </button>
                </div>
            </div>

            <!-- Statistics Summary Cards -->
            <div class="row g-3 mb-4">
                <?php foreach ($jalur_stats as $stat): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="card card-premium h-100 border-start border-4 border-primary shadow-sm bg-white">
                            <div class="card-body p-3">
                                <h6 class="fw-bold text-dark small mb-3 text-truncate"><?= htmlspecialchars($stat['nama_jalur']) ?></h6>
                                <div class="row g-2 text-center">
                                    <div class="col-4">
                                        <div class="small text-muted" style="font-size: 0.7rem;">Total</div>
                                        <div class="fw-bold text-primary"><?= $stat['total_pendaftar'] ?></div>
                                    </div>
                                    <div class="col-4 border-start">
                                        <div class="small text-muted" style="font-size: 0.7rem;">Verif</div>
                                        <div class="fw-bold text-success"><?= $stat['total_verifikasi'] ?></div>
                                    </div>
                                    <div class="col-4 border-start">
                                        <div class="small text-muted" style="font-size: 0.7rem;">Tolak</div>
                                        <div class="fw-bold text-danger"><?= $stat['total_ditolak'] ?></div>
                                    </div>
                                </div>

                                <?php if ($stat['jalur_id'] == 11): ?>
                                    <hr class="my-2 opacity-25">
                                    <div class="row g-2 text-center">
                                        <div class="col-6 text-center">
                                            <div class="small text-muted" style="font-size: 0.65rem;">Lulus Tahfidz</div>
                                            <div class="fw-bold text-success small"><?= $stat['total_lulus_tahfidz'] ?></div>
                                        </div>
                                        <div class="col-6 border-start text-center">
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
                    <div class="card card-premium h-100 border-start border-4 border-dark bg-secondary bg-opacity-10 shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-dark small mb-3">REKAP KESELURUHAN</h6>
                            <div class="row g-2 text-center">
                                <div class="col-4 text-center">
                                    <div class="small text-muted" style="font-size: 0.7rem;">Total</div>
                                    <div class="fw-bold text-primary"><?= $grand_total_all ?></div>
                                </div>
                                <div class="col-4 border-start text-center">
                                    <div class="small text-muted" style="font-size: 0.7rem;">Verif</div>
                                    <div class="fw-bold text-success"><?= $grand_total_verifikasi ?></div>
                                </div>
                                <div class="col-4 border-start text-center">
                                    <div class="small text-muted" style="font-size: 0.7rem;">Tolak</div>
                                    <div class="fw-bold text-danger"><?= $grand_total_ditolak ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rekap Tanpa Ujian -->
                <div class="col-md-4 col-lg-3">
                    <div class="card card-premium h-100 border-start border-4 border-success bg-success bg-opacity-10 shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-success small mb-3">REKAP TANPA UJIAN</h6>
                            <div class="row g-2 text-center">
                                <div class="col-4 text-center">
                                    <div class="small text-muted" style="font-size: 0.7rem;">Total</div>
                                    <div class="fw-bold text-primary"><?= $rekap_tanpa_ujian['total'] ?></div>
                                </div>
                                <div class="col-4 border-start text-center">
                                    <div class="small text-muted" style="font-size: 0.7rem;">Verif</div>
                                    <div class="fw-bold text-success"><?= $rekap_tanpa_ujian['verif'] ?></div>
                                </div>
                                <div class="col-4 border-start text-center">
                                    <div class="small text-muted" style="font-size: 0.7rem;">Tolak</div>
                                    <div class="fw-bold text-danger"><?= $rekap_tanpa_ujian['tolak'] ?></div>
                                </div>
                            </div>
                            <hr class="my-2 opacity-25 border-success">
                            <a href="detail.php?type=tanpa_ujian" class="btn btn-sm btn-success bg-opacity-10 text-success rounded-pill w-100 fw-bold border-0 shadow-sm" style="font-size: 0.75rem;">
                                <i class="fas fa-search me-1"></i> Klik Detail
                            </a>
                            <div class="mt-2 small text-muted fst-italic text-center" style="font-size: 0.65rem;">
                                *Jalur Tanpa Tes & Tahfidz Lulus
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rekap Dengan Ujian -->
                <div class="col-md-4 col-lg-3">
                    <div class="card card-premium h-100 border-start border-4 border-warning bg-warning bg-opacity-10 shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-dark small mb-3">REKAP DENGAN UJIAN</h6>
                            <div class="row g-2 text-center">
                                <div class="col-4 text-center">
                                    <div class="small text-muted" style="font-size: 0.7rem;">Total</div>
                                    <div class="fw-bold text-primary"><?= $rekap_dengan_ujian['total'] ?></div>
                                </div>
                                <div class="col-4 border-start text-center">
                                    <div class="small text-muted" style="font-size: 0.7rem;">Verif</div>
                                    <div class="fw-bold text-success"><?= $rekap_dengan_ujian['verif'] ?></div>
                                </div>
                                <div class="col-4 border-start text-center">
                                    <div class="small text-muted" style="font-size: 0.7rem;">Tolak</div>
                                    <div class="fw-bold text-danger"><?= $rekap_dengan_ujian['tolak'] ?></div>
                                </div>
                            </div>
                            <hr class="my-2 opacity-25 border-warning">
                            <a href="detail.php?type=dengan_ujian" class="btn btn-sm btn-warning bg-opacity-10 text-dark rounded-pill w-100 fw-bold border-0 shadow-sm" style="font-size: 0.75rem;">
                                <i class="fas fa-search me-1"></i> Klik Detail
                            </a>
                            <div class="mt-2 small text-muted fst-italic text-center" style="font-size: 0.65rem;">
                                *Jalur Tes & Tahfidz Tdk Lulus
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Skema Pembobotan & Weights -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-premium p-4 border-0 bg-white shadow-sm">
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-warning bg-opacity-10 p-2 rounded-3 me-3">
                                <i class="fas fa-calculator text-warning"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Skema Pembobotan Kelulusan</h6>
                                <p class="small text-muted mb-0">Atur persentase bobot antara Nilai Rapor dan Nilai CBT.</p>
                            </div>
                        </div>
                        <form method="POST" class="row g-3 align-items-end">
                            <input type="hidden" name="update_weights" value="1">
                            <div class="col-6 col-md-3">
                                <label class="small text-muted fw-bold mb-1">Bobot Rapor (%)</label>
                                <div class="input-group">
                                    <input type="number" name="weight_rapor" id="weight_rapor"
                                        class="form-control form-control-sm border-0 bg-light" value="<?= $w_rapor ?>"
                                        min="0" max="100" required>
                                    <span class="input-group-text border-0 bg-light small text-muted">%</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="small text-muted fw-bold mb-1">Bobot CBT (%)</label>
                                <div class="input-group">
                                    <input type="number" name="weight_cbt" id="weight_cbt"
                                        class="form-control form-control-sm border-0 bg-light" value="<?= $w_cbt ?>"
                                        min="0" max="100" required>
                                    <span class="input-group-text border-0 bg-light small text-muted">%</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 rounded-2 bg-light border border-dashed text-dark text-center h-100 d-flex align-items-center justify-content-center">
                                    <span class="small fw-500">Hasil: (Rapor × <?= $w_rapor ?>%) + (CBT × <?= $w_cbt ?>%)</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit"
                                    class="btn btn-warning btn-sm w-100 rounded-pill fw-bold text-white shadow-sm py-2">
                                    <i class="fas fa-save me-1"></i> Update Bobot
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Ranking Table -->
            <div class="card card-premium overflow-hidden border-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 80px;">Rank</th>
                                <th>Murid</th>
                                <th>Jalur Masuk</th>
                                <th class="text-center">Rata-rata Rapor</th>
                                <th class="text-center">Nilai Ujian CBT</th>
                                <th class="text-center">Nilai Akhir</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendaftar)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">Belum ada data pendaftar untuk
                                        diranking.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendaftar as $index => $row):
                                    $rank = $index + 1;
                                    $rankClass = 'rank-other';
                                    if ($rank == 1)
                                        $rankClass = 'rank-1';
                                    elseif ($rank == 2)
                                        $rankClass = 'rank-2';
                                    elseif ($rank == 3)
                                        $rankClass = 'rank-3';

                                    // Determine status badge color
                                    $statusColor = 'secondary-subtle text-secondary';
                                    if ($row['status'] == 'Diterima')
                                        $statusColor = 'success-subtle text-success';
                                    elseif ($row['status'] == 'Ditolak')
                                        $statusColor = 'danger-subtle text-danger';
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <div class="badge-ranking mx-auto <?= $rankClass ?>">
                                                <?= $rank ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                            <div class="small text-muted"><?= $row['no_pendaftaran'] ?></div>
                                            <div class="mt-1">
                                                <span class="badge rounded-pill <?= $statusColor ?> border"
                                                    style="font-size: 0.65rem; font-weight: 600;">
                                                    <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                                    <?= strtoupper($row['status'] ?: 'Belum Ditentukan') ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary border rounded-pill px-3">
                                                <?= $row['nama_jalur'] ?: 'Umum' ?>
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold">
                                            <?= number_format($row['nilai_rapor_rata2'], 2) ?>
                                        </td>
                                        <td class="text-center fw-bold">
                                            <?= number_format($row['nilai_ujian'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="nilai-akhir-box">
                                                <?= number_format($row['nilai_akhir'], 2) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" class="d-flex gap-2 justify-content-center">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="update_status" value="1">
                                                <?php if ($row['status'] != 'Diterima'): ?>
                                                    <button type="submit" name="status" value="Diterima"
                                                        class="btn btn-sm btn-success rounded-pill px-3 shadow-sm border-0"
                                                        onclick="return confirm('Apakah Anda yakin ingin MELULUSKAN siswa bernama <?= addslashes($row['nama_lengkap']) ?>?')">
                                                        <i class="fas fa-check-circle me-1"></i> Lulus
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($row['status'] != 'Ditolak'): ?>
                                                    <button type="submit" name="status" value="Ditolak"
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm no-print"
                                                        onclick="return confirm('Apakah Anda yakin ingin MENGGAGALKAN siswa bernama <?= addslashes($row['nama_lengkap']) ?>?')">
                                                        <i class="fas fa-times-circle me-1"></i> Gagal
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3 shadow-sm no-print"
                                                    onclick="editIndividualScore(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_lengkap'], ENT_QUOTES) ?>', <?= $row['nilai_rapor_rata2'] ?>, <?= $row['nilai_ujian'] ?>)">
                                                    <i class="fas fa-edit me-1"></i> Edit Nilai
                                                </button>
                                            </form>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const raporInput = document.getElementById('weight_rapor');
        const cbtInput = document.getElementById('weight_cbt');

        raporInput.addEventListener('input', function () {
            let val = parseInt(this.value) || 0;
            if (val > 100) val = 100;
            if (val < 0) val = 0;
            this.value = val;
            cbtInput.value = 100 - val;
        });

        cbtInput.addEventListener('input', function () {
            let val = parseInt(this.value) || 0;
            if (val > 100) val = 100;
            if (val < 0) val = 0;
            this.value = val;
            raporInput.value = 100 - val;
        });
    </script>

    <!-- Modal Clear List -->
    <div class="modal fade" id="modalClear" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-danger text-white border-0 py-3 rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Clear Nilai
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3">
                            <i class="fas fa-shield-alt fa-3x text-danger opacity-25"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Hapus Semua Nilai Ujian CBT?</h5>
                        <p class="text-muted small">Tindakan ini akan mereset nilai ujian seluruh pendaftar menjadi 0.
                            Silakan masukkan password admin untuk konfirmasi.</p>

                        <div class="text-start mt-4">
                            <label class="small fw-bold text-muted mb-1">Password Admin</label>
                            <input type="password" name="admin_password" class="form-control rounded-3 py-2" required
                                placeholder="Masukkan password anda">
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="clear_scores" class="btn btn-danger rounded-pill px-4 fw-bold">
                            <i class="fas fa-trash-alt me-1"></i> Konfirmasi Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Individual Score -->
    <div class="modal fade" id="modalEditScore" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-primary text-white border-0 py-3 rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Nilai Pendaftar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-4 text-center">
                            <h6 id="edit_nama" class="fw-bold text-primary mb-0">Nama Pendaftar</h6>
                            <small class="text-muted">Update nilai secara manual</small>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="small fw-bold text-muted mb-1">Rata-rata Rapor</label>
                                <input type="number" step="0.01" name="nilai_rapor" id="edit_nilai_rapor"
                                    class="form-control rounded-3 py-2" required>
                            </div>
                            <div class="col-6">
                                <label class="small fw-bold text-muted mb-1">Nilai Ujian CBT</label>
                                <input type="number" step="0.01" name="nilai_cbt" id="edit_nilai_cbt"
                                    class="form-control rounded-3 py-2" required>
                            </div>
                        </div>

                        <div class="mt-4 border-top pt-3">
                            <label class="small fw-bold text-muted mb-1">
                                <i class="fas fa-lock me-1"></i> Password Konfirmasi
                            </label>
                            <input type="password" name="admin_password" class="form-control rounded-3 py-2" required
                                placeholder="Masukkan password admin">
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_individual_score"
                            class="btn btn-primary rounded-pill px-4 fw-bold">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Penetapan Kelulusan -->
    <div class="modal fade" id="modalPenetapanKelulusan" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-success text-white border-0 py-3 rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="fas fa-check-double me-2"></i>Penetapan Kelulusan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formPenetapanKelulusan">
                    <!-- Step 1 -->
                    <div id="penetapanStep1" class="modal-body p-4 text-center">
                        <div class="mb-3">
                            <i class="fas fa-list-ol fa-3x text-success opacity-25"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Tentukan Batas Kelulusan</h5>
                        <p class="text-muted small">Update otomatis menjadi "Diterima (Lulus)" dari Ranking 1 hingga batas, dan "Tolak (Gagal)" untuk sisanya.</p>

                        <div class="text-start mt-4">
                            <label class="small fw-bold text-muted mb-1">Tetapkan Lulus hingga Ranking Ke-</label>
                            <input type="number" id="batas_ranking_input" class="form-control rounded-3 py-2" min="1"
                                placeholder="Contoh: 100">
                        </div>
                    </div>
                    <div id="footerStep1" class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="button" onclick="lanjutPenetapan()" class="btn btn-success rounded-pill px-4 fw-bold">
                            Lanjut <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>

                    <!-- Step 2 (Hidden by default) -->
                    <div id="penetapanStep2" class="modal-body p-4 text-center d-none">
                        <div class="mb-3">
                            <i class="fas fa-shield-alt fa-3x text-warning opacity-25"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Konfirmasi Password</h5>
                        <p class="text-muted small">Anda akan meluluskan pendaftar Rank 1 hingga Rank <span id="display_batas_rank" class="fw-bold text-dark"></span>, dan menolak sisanya. Tindakan ini akan mengupdate status mereka.</p>

                        <div class="text-start mt-4">
                            <input type="hidden" name="tetapkan_kelulusan" value="1">
                            <input type="hidden" name="batas_ranking" id="batas_ranking_hidden">
                            <label class="small fw-bold text-muted mb-1">Password Admin</label>
                            <input type="password" name="admin_password" id="admin_password_penetapan" class="form-control rounded-3 py-2"
                                placeholder="Masukkan password anda">
                        </div>
                    </div>
                    <div id="footerStep2" class="modal-footer border-0 p-4 pt-0 d-none">
                        <button type="button" onclick="kembaliPenetapan()" class="btn btn-light rounded-pill px-4">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                            <i class="fas fa-check me-1"></i> Tetapkan Lulus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function checkCbtBeforeModal() {
            var hasCbt = <?= $has_cbt_scores ? 'true' : 'false' ?>;
            if (!hasCbt) {
                alert("Nilai ujian belum masuk, mohon periksa kembali.");
                return;
            }
            // Jika ada nilai, buka modal secara manual
            var myModal = new bootstrap.Modal(document.getElementById('modalPenetapanKelulusan'));
            myModal.show();
        }

        function lanjutPenetapan() {
            var batas = document.getElementById('batas_ranking_input').value;
            if (!batas || batas <= 0) {
                alert("Silakan masukkan batas ranking yang valid.");
                return;
            }
            
            document.getElementById('batas_ranking_hidden').value = batas;
            document.getElementById('display_batas_rank').innerText = batas;
            
            // Hide step 1, show step 2
            document.getElementById('penetapanStep1').classList.add('d-none');
            document.getElementById('footerStep1').classList.add('d-none');
            
            document.getElementById('penetapanStep2').classList.remove('d-none');
            document.getElementById('footerStep2').classList.remove('d-none');
            
            // Require password in step 2
            document.getElementById('admin_password_penetapan').required = true;
        }

        function kembaliPenetapan() {
            // Hide step 2, show step 1
            document.getElementById('penetapanStep2').classList.add('d-none');
            document.getElementById('footerStep2').classList.add('d-none');
            
            document.getElementById('penetapanStep1').classList.remove('d-none');
            document.getElementById('footerStep1').classList.remove('d-none');
            
            // Remove required from password
            document.getElementById('admin_password_penetapan').required = false;
        }

        // Reset modal state when closed
        var modalPenetapan = document.getElementById('modalPenetapanKelulusan')
        if (modalPenetapan) {
            modalPenetapan.addEventListener('hidden.bs.modal', function (event) {
                kembaliPenetapan();
                document.getElementById('formPenetapanKelulusan').reset();
            })
        }

        function editIndividualScore(id, nama, rapor, cbt) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').innerText = nama;
            document.getElementById('edit_nilai_rapor').value = rapor;
            document.getElementById('edit_nilai_cbt').value = cbt;

            var modal = new bootstrap.Modal(document.getElementById('modalEditScore'));
            modal.show();
        }
    </script>
</body>

</html>