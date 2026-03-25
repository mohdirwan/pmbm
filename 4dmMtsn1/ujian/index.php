<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$success_msg = '';
$error_msg = '';
$cbt_status = 'disconnected'; // Default

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $allowed_keys = ['cbt_url', 'cbt_token', 'cbt_client_id'];

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        foreach ($allowed_keys as $key) {
            if (isset($_POST[$key])) {
                $val = trim($_POST[$key]);
                $stmt->execute([$key, $val]);
            }
        }

        // Simulate Connection Test
        $cbt_url = $_POST['cbt_url'];
        if (filter_var($cbt_url, FILTER_VALIDATE_URL)) {
            $pdo->commit();
            $success_msg = "Konfigurasi berhasil disimpan. Koneksi ke server CBT berhasil diinisialisasi.";
            $cbt_status = 'connected';
        } else {
            throw new Exception("URL Server CBT tidak valid.");
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error_msg = "Gagal menyimpan konfigurasi: " . $e->getMessage();
    }
}

// Retrieve current settings
$cbt_url = get_setting('cbt_url', '');
$cbt_token = get_setting('cbt_token', '');
$cbt_client_id = get_setting('cbt_client_id', '');

// Simulate status check if URL exists
if (!empty($cbt_url)) {
    $cbt_status = 'connected';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Integrasi Ujian (CBT) - PMBM Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            background: #0f5132;
            color: white;
            padding-top: 20px;
            position: fixed;
            width: 250px;
            z-index: 1000;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .status-indicator {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-connected {
            background-color: #198754;
            box-shadow: 0 0 10px rgba(25, 135, 84, 0.5);
        }

        .status-disconnected {
            background-color: #dc3545;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
        }
    </style>
</head>

<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-primary fw-bold mb-0">Integrasi CBT</h2>
                    <p class="text-muted small">Hubungkan sistem PPDB dengan Aplikasi Ujian Berbasis Komputer (CBT).</p>
                </div>
                <div>
                    <div
                        class="card bg-white border-0 shadow-sm px-3 py-2 rounded-pill d-flex flex-row align-items-center">
                        <span
                            class="status-indicator <?= $cbt_status == 'connected' ? 'status-connected' : 'status-disconnected' ?> me-2"></span>
                        <span class="fw-bold small <?= $cbt_status == 'connected' ? 'text-success' : 'text-danger' ?>">
                            <?= $cbt_status == 'connected' ? 'Terhubung ke Server' : 'Terputus / Belum Dikonfigurasi' ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?= $success_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?= $error_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Configuration Form -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-cog me-2 text-secondary"></i>Konfigurasi
                                Server</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">URL Server CBT Utama</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-globe"></i></span>
                                        <input type="url" name="cbt_url" class="form-control"
                                            placeholder="https://cbt.sekolah.sch.id"
                                            value="<?= htmlspecialchars($cbt_url) ?>" required>
                                    </div>
                                    <div class="form-text">Pastikan URL dapat diakses dan diakhiri tanpa tanda slash
                                        (/).</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small">Client ID</label>
                                        <input type="text" name="cbt_client_id" class="form-control"
                                            placeholder="ID Aplikasi" value="<?= htmlspecialchars($cbt_client_id) ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small">Secret Token / API Key</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-key"></i></span>
                                            <input type="password" name="cbt_token" class="form-control"
                                                placeholder="********" value="<?= htmlspecialchars($cbt_token) ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-warning border-0 small rounded-3 mt-3">
                                    <i class="fas fa-info-circle me-2"></i> <strong>Catatan:</strong> Hubungi
                                    administrator server CBT untuk mendapatkan kredensial akses API.
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                        <i class="fas fa-save me-2"></i> Simpan & Tes Koneksi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Features / Actions -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-primary text-white"
                        style="background: linear-gradient(135deg, #0d6efd, #0a58ca);">
                        <div class="card-body p-4 position-relative overflow-hidden">
                            <i class="fas fa-laptop-code position-absolute top-0 end-0 opacity-25"
                                style="font-size: 8rem; margin-top: -20px; margin-right: -20px;"></i>
                            <h4 class="fw-bold mb-3">Panel Sinkronisasi</h4>
                            <p class="mb-4 opacity-75 small">Lakukan sinkronisasi data murid pendaftar ke sistem ujian
                                untuk men-generate akun ujian peserta.</p>

                            <div class="d-grid gap-2">
                                <button
                                    class="btn btn-light text-primary fw-bold text-start p-3 rounded-3 shadow-sm d-flex justify-content-between align-items-center"
                                    <?= empty($cbt_url) ? 'disabled' : '' ?>>
                                    <span><i class="fas fa-sync me-2"></i> Sinkronisasi Peserta Ujian</span>
                                    <i class="fas fa-chevron-right small"></i>
                                </button>
                                <button
                                    class="btn btn-outline-light text-start p-3 rounded-3 d-flex justify-content-between align-items-center"
                                    <?= empty($cbt_url) ? 'disabled' : '' ?>>
                                    <span><i class="fas fa-download me-2"></i> Tarik Nilai Hasil Ujian</span>
                                    <i class="fas fa-chevron-right small"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="fw-bold mb-0 text-muted text-uppercase small">Log Aktivitas Integrasi</h6>
                        </div>
                        <div class="list-group list-group-flush small">
                            <div class="list-group-item px-4 py-3 border-light">
                                <div class="d-flex justify-content-between">
                                    <span class="text-danger fw-bold"><i class="fas fa-times-circle me-2"></i>Koneksi
                                        Gagal</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">Baru saja</span>
                                </div>
                                <p class="mb-0 text-muted mt-1">Gagal menghubungi server CBT. Timeout connection.</p>
                            </div>
                            <div class="list-group-item px-4 py-3 border-light">
                                <div class="d-flex justify-content-between">
                                    <span class="text-secondary fw-bold"><i class="fas fa-cog me-2"></i>System
                                        Init</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">1 hari lalu</span>
                                </div>
                                <p class="mb-0 text-muted mt-1">Modul integrasi CBT diaktifkan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>