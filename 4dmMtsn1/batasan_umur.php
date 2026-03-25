<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

$page_title = 'Batasan Umur Pendaftaran';

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_age'])) {
    $age_limit_status = $_POST['age_limit_status'] ?? 'nonaktif';
    $age_cutoff_date = $_POST['age_cutoff_date'] ?? '';
    $max_age_limit = intval($_POST['max_age_limit']);

    try {
        $pdo->beginTransaction();

        // Save to settings using INSERT ON DUPLICATE KEY UPDATE
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute(['age_limit_status', $age_limit_status]);
        $stmt->execute(['age_cutoff_date', $age_cutoff_date]);
        $stmt->execute(['max_age_limit', $max_age_limit]);

        $pdo->commit();

        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        Pengaturan batasan umur berhasil disimpan!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Gagal menyimpan: ' . $e->getMessage() . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
}

// Get current settings
$age_limit_status = get_setting('age_limit_status', 'nonaktif');
$age_cutoff_date = get_setting('age_cutoff_date', '2026-07-01');
$max_age_limit = get_setting('max_age_limit', '15');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $page_title ?> - Admin PMBM
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">
                        <?= $page_title ?>
                    </h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item">Sekolah</li>
                            <li class="breadcrumb-item active">
                                <?= $page_title ?>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>

            <?= $message ?>

            <div class="row">
                <!-- Form Setting -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-transparent border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="fas fa-user-clock text-warning me-2"></i>
                                Pengaturan Batasan Umur
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-warning border-0 rounded-4 mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                    <div>
                                        <strong>Perhatian:</strong><br>
                                        Jika batasan umur diaktifkan, sistem akan otomatis menolak pendaftaran murid
                                        yang umurnya melebihi batas maksimal.
                                    </div>
                                </div>
                            </div>

                            <form method="POST">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-toggle-on me-2 text-success"></i>
                                        Status Batasan Umur
                                    </label>
                                    <select class="form-select form-select-lg" name="age_limit_status" required>
                                        <option value="aktif" <?= $age_limit_status == 'aktif' ? 'selected' : '' ?>>
                                            Aktif - Terapkan batasan umur
                                        </option>
                                        <option value="nonaktif" <?= $age_limit_status == 'nonaktif' ? 'selected' : '' ?>>
                                            Nonaktif - Terima semua umur
                                        </option>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Jika <strong>Aktif</strong>, pendaftar yang melebihi umur maksimal akan ditolak
                                        otomatis.
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-calendar me-2 text-primary"></i>
                                            Tanggal Cut-off Batasan
                                        </label>
                                        <input type="text" class="form-control form-control-lg datepicker"
                                            name="age_cutoff_date" value="<?= htmlspecialchars($age_cutoff_date) ?>"
                                            required>
                                        <div class="form-text">
                                            Contoh: Per 01/07/2026 umur maksimal harus 15 tahun
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-user-check me-2 text-success"></i>
                                            Maksimal Umur (Tahun)
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <input type="number" class="form-control" name="max_age_limit"
                                                value="<?= htmlspecialchars($max_age_limit) ?>" min="1" max="30"
                                                required>
                                            <span class="input-group-text">Tahun</span>
                                        </div>
                                        <div class="form-text">
                                            Umur maksimal yang diperbolehkan
                                        </div>
                                    </div>
                                </div>

                                <div class="border-top pt-4">
                                    <button type="submit" name="save_age" class="btn btn-warning btn-lg px-5">
                                        <i class="fas fa-save me-2"></i>
                                        Simpan Pengaturan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Info Panel -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-warning text-dark border-0 py-3">
                            <h6 class="fw-bold mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Pengaturan Saat Ini
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong class="d-block mb-2">🎯 Status Validasi:</strong>
                                <?php if ($age_limit_status == 'aktif'): ?>
                                    <span class="badge bg-success fs-6 px-3 py-2 w-100">
                                        <i class="fas fa-check-circle me-1"></i> AKTIF
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary fs-6 px-3 py-2 w-100">
                                        <i class="fas fa-times-circle me-1"></i> NONAKTIF
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <strong class="d-block mb-2">📅 Tanggal Cut-off:</strong>
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <div class="h5 fw-bold text-primary mb-0">
                                        <?php
                                        $date_obj = new DateTime($age_cutoff_date);
                                        echo $date_obj->format('d F Y');
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <strong class="d-block mb-2">👤 Umur Maksimal:</strong>
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <div class="display-4 fw-bold text-warning mb-0">
                                        <?= $max_age_limit ?>
                                    </div>
                                    <small class="text-muted">Tahun</small>
                                </div>
                            </div>

                            <hr>

                            <div class="small">
                                <strong><i class="fas fa-question-circle me-1"></i> Cara Kerja:</strong>
                                <ul class="mt-2 mb-0">
                                    <li>Sistem menghitung umur dari tanggal lahir</li>
                                    <li>Dibandingkan dengan tanggal cut-off yang ditentukan</li>
                                    <li>Jika umur > maksimal, pendaftaran ditolak</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Example -->
                    <div class="card border-info shadow-sm rounded-4">
                        <div class="card-header bg-info bg-opacity-10 border-0 py-3">
                            <h6 class="fw-bold mb-0 text-info">
                                <i class="fas fa-calculator me-2"></i>
                                Contoh Perhitungan
                            </h6>
                        </div>
                        <div class="card-body small">
                            <p class="mb-2"><strong>Pengaturan:</strong></p>
                            <ul class="mb-3">
                                <li>Tanggal cut-off: 01/07/2026</li>
                                <li>Umur maksimal: 15 tahun</li>
                            </ul>

                            <p class="mb-2"><strong>Murid A:</strong></p>
                            <ul class="mb-3">
                                <li>Tanggal lahir: 15/08/2010</li>
                                <li>Umur per 01/07/2026: 15 tahun 10 bulan</li>
                                <li class="text-danger fw-bold">❌ DITOLAK (lebih dari 15 tahun)</li>
                            </ul>

                            <p class="mb-2"><strong>Murid B:</strong></p>
                            <ul class="mb-0">
                                <li>Tanggal lahir: 15/08/2011</li>
                                <li>Umur per 01/07/2026: 14 tahun 10 bulan</li>
                                <li class="text-success fw-bold">✅ DITERIMA (kurang dari 15 tahun)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script>
        // Initialize Flatpickr for date only
        flatpickr(".datepicker", {
            locale: "id",
            altInput: true,
            altFormat: "d/m/Y",
            dateFormat: "Y-m-d",
        });
    </script>
</body>

</html>