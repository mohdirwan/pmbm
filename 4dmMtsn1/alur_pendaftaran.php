<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

$success_msg = '';
$error_msg = '';

$flow_keys = [
    'flow_step1_time', // Pendaftaran Online
    'flow_step2_time', // Lengkapi Data
    'flow_step3_time', // Bukti Pendaftaran
    'flow_step4_time', // Pakta Integritas
    'flow_step5_time', // Test Tahfidz
    'flow_step6_time', // Pengumuman Administrasi
    'flow_step7_time', // Tes Akademik
    'flow_step8_time', // Pengumuman Akhir
    'flow_step9_time'  // Daftar Ulang
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        foreach ($flow_keys as $key) {
            if (isset($_POST[$key])) {
                $val = $_POST[$key];
                $stmt->execute([$key, $val]);
            }
        }

        $pdo->commit();
        $success_msg = "Alur pendaftaran sesuai infografis berhasil diperbarui!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Fetch current values
$settings = [];
foreach ($flow_keys as $key) {
    $settings[$key] = get_setting($key);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Manajemen Alur Pendaftaran - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --admin-primary: #0b2c24;
            --admin-secondary: #ffc107;
            --admin-bg: #f8f9fa;
        }

        body {
            background-color: var(--admin-bg);
        }

        .main-content {
            padding: 40px;
        }

        .page-header {
            background: linear-gradient(135deg, var(--admin-primary) 0%, #1a4d40 100%);
            border-radius: 20px;
            padding: 30px;
            color: white;
            margin-bottom: 35px;
            box-shadow: 0 10px 30px rgba(11, 44, 36, 0.1);
        }

        .step-card {
            background: white;
            border-radius: 20px;
            border: none;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            display: flex;
            align-items: center;
        }

        .step-card:hover {
            transform: translateX(10px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
            border-left: 5px solid var(--admin-secondary);
        }

        .step-number {
            width: 70px;
            height: 100%;
            background: #f1f3f4;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--admin-primary);
            flex-shrink: 0;
            border-right: 1px solid #eee;
        }

        .step-body {
            padding: 20px 25px;
            flex-grow: 1;
        }

        .form-label-premium {
            font-weight: 700;
            color: var(--admin-primary);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .form-label-premium i {
            width: 25px;
            color: var(--admin-secondary);
        }

        .form-control-premium {
            border: 2px solid #eef0f2;
            padding: 10px 15px;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.2s;
            background: #fdfdfd;
        }

        .form-control-premium:focus {
            border-color: var(--admin-primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(11, 44, 36, 0.05);
        }

        .preview-box {
            position: sticky;
            top: 20px;
        }

        .btn-save-floating {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            padding: 15px 40px;
            border-radius: 100px;
            font-weight: 700;
            box-shadow: 0 15px 35px rgba(11, 44, 36, 0.2);
            border: none;
            background: linear-gradient(to right, var(--admin-primary), #1a4d40);
            color: white;
            transition: all 0.3s;
        }

        .btn-save-floating:hover {
            transform: scale(1.05) translateY(-5px);
            box-shadow: 0 20px 40px rgba(11, 44, 36, 0.3);
            color: var(--admin-secondary);
        }
    </style>
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h1 class="fw-bold mb-0"><i class="fas fa-calendar-alt me-3 text-warning"></i>Alur PMBM Sesuai
                    Infografis</h1>
                <p class="text-white opacity-75 mb-0 mt-2">Sinkronkan 9 tahapan pendaftaran sesuai desain resmi
                    madrasah.</p>
            </div>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 p-3 mb-4" role="alert">
                <i class="fas fa-check-circle fs-4 me-3"></i> <strong>Berhasil!</strong> <?= $success_msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-4 pe-5">
                <div class="col-lg-12">
                    <div class="row row-cols-1 row-cols-md-2 g-3">
                        <!-- Step 1 -->
                        <div class="col">
                            <div class="step-card h-100">
                                <div class="step-number">01</div>
                                <div class="step-body">
                                    <label class="form-label-premium"><i class="fas fa-globe"></i> Pendaftaran
                                        Online</label>
                                    <input type="text" class="form-control form-control-premium" name="flow_step1_time"
                                        value="<?= htmlspecialchars($settings['flow_step1_time']) ?>"
                                        placeholder="E.g. 03 - 05 Maret 2026">
                                </div>
                            </div>
                        </div>
                        <!-- Step 2 -->
                        <div class="col">
                            <div class="step-card h-100">
                                <div class="step-number">02</div>
                                <div class="step-body">
                                    <label class="form-label-premium"><i class="fas fa-file-edit"></i> Lengkapi Data &
                                        Upload</label>
                                    <input type="text" class="form-control form-control-premium" name="flow_step2_time"
                                        value="<?= htmlspecialchars($settings['flow_step2_time']) ?>"
                                        placeholder="E.g. 03 - 05 Maret 2026">
                                </div>
                            </div>
                        </div>
                        <!-- Step 3 -->
                        <div class="col">
                            <div class="step-card h-100">
                                <div class="step-number">03</div>
                                <div class="step-body">
                                    <label class="form-label-premium"><i class="fas fa-print"></i> Cetak Bukti
                                        Pendaftaran</label>
                                    <input type="text" class="form-control form-control-premium" name="flow_step3_time"
                                        value="<?= htmlspecialchars($settings['flow_step3_time']) ?>"
                                        placeholder="E.g. 03 - 05 Maret 2026">
                                </div>
                            </div>
                        </div>
                        <!-- Step 4 -->
                        <div class="col">
                            <div class="step-card h-100">
                                <div class="step-number">04</div>
                                <div class="step-body">
                                    <label class="form-label-premium"><i class="fas fa-file-signature"></i> Cetak Pakta
                                        Integritas</label>
                                    <input type="text" class="form-control form-control-premium" name="flow_step4_time"
                                        value="<?= htmlspecialchars($settings['flow_step4_time']) ?>"
                                        placeholder="E.g. 09 - 10 Maret 2026">
                                </div>
                            </div>
                        </div>
                        <!-- Step 5 -->
                        <div class="col">
                            <div class="step-card h-100">
                                <div class="step-number">05</div>
                                <div class="step-body">
                                    <label class="form-label-premium"><i class="fas fa-quran"></i> Pelaksanaan Test
                                        Tahfidz</label>
                                    <input type="text" class="form-control form-control-premium" name="flow_step5_time"
                                        value="<?= htmlspecialchars($settings['flow_step5_time']) ?>"
                                        placeholder="E.g. 09 - 10 Maret 2026">
                                </div>
                            </div>
                        </div>
                        <!-- Step 6 -->
                        <div class="col">
                            <div class="step-card h-100">
                                <div class="step-number">06</div>
                                <div class="step-body">
                                    <label class="form-label-premium"><i class="fas fa-bullhorn"></i> Pengumuman Hasil
                                        Administrasi</label>
                                    <input type="text" class="form-control form-control-premium" name="flow_step6_time"
                                        value="<?= htmlspecialchars($settings['flow_step6_time']) ?>"
                                        placeholder="E.g. 11 Maret 2026">
                                </div>
                            </div>
                        </div>
                        <!-- Step 7 -->
                        <div class="col">
                            <div class="step-card h-100">
                                <div class="step-number">07</div>
                                <div class="step-body">
                                    <label class="form-label-premium"><i class="fas fa-laptop-code"></i> Pelaksanaan Tes
                                        Akademik</label>
                                    <input type="text" class="form-control form-control-premium" name="flow_step7_time"
                                        value="<?= htmlspecialchars($settings['flow_step7_time']) ?>"
                                        placeholder="E.g. 14 Maret 2026">
                                </div>
                            </div>
                        </div>
                        <!-- Step 8 -->
                        <div class="col">
                            <div class="step-card h-100">
                                <div class="step-number">08</div>
                                <div class="step-body">
                                    <label class="form-label-premium"><i class="fas fa-trophy"></i> Pengumuman Lulus Tes
                                        Akademik</label>
                                    <input type="text" class="form-control form-control-premium" name="flow_step8_time"
                                        value="<?= htmlspecialchars($settings['flow_step8_time']) ?>"
                                        placeholder="E.g. 14 Maret 2026">
                                </div>
                            </div>
                        </div>
                        <!-- Step 9 -->
                        <div class="col">
                            <div class="step-card h-100">
                                <div class="step-number">09</div>
                                <div class="step-body">
                                    <label class="form-label-premium"><i class="fas fa-user-check"></i> Proses Daftar
                                        Ulang</label>
                                    <input type="text" class="form-control form-control-premium" name="flow_step9_time"
                                        value="<?= htmlspecialchars($settings['flow_step9_time']) ?>"
                                        placeholder="E.g. 01 - 03 April 2026">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-save-floating">
                <i class="fas fa-save me-2"></i> Update Alur Pendaftaran
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>