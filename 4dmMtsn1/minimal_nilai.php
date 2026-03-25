<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

$page_title = 'Minimal Nilai Rata-rata';

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_minimal'])) {
    $minimal_nilai = floatval($_POST['minimal_nilai']);
    $status_validasi = $_POST['status_validasi'] ?? 'nonaktif';

    try {
        $pdo->beginTransaction();

        // Save to settings using INSERT ON DUPLICATE KEY UPDATE
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute(['minimal_nilai_rata', $minimal_nilai]);
        $stmt->execute(['status_validasi_nilai', $status_validasi]);

        $pdo->commit();

        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        Minimal nilai rata-rata berhasil disimpan!
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
$minimal_nilai = get_setting('minimal_nilai_rata', '0');
$status_validasi = get_setting('status_validasi_nilai', 'nonaktif');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1"><?= $page_title ?></h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item">Sekolah</li>
                            <li class="breadcrumb-item active"><?= $page_title ?></li>
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
                                <i class="fas fa-calculator text-primary me-2"></i>
                                Pengaturan Minimal Nilai Rata-rata
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-chart-line me-2 text-success"></i>
                                        Minimal Nilai Rata-rata
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" step="0.01" min="0" max="100"
                                            class="form-control form-control-lg" name="minimal_nilai"
                                            value="<?= htmlspecialchars($minimal_nilai) ?>" required
                                            placeholder="Contoh: 75.00">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-star"></i>
                                        </span>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Nilai minimal rata-rata rapor yang diperbolehkan untuk mendaftar (skala 0-100).
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-toggle-on me-2 text-warning"></i>
                                        Status Validasi
                                    </label>
                                    <select class="form-select form-select-lg" name="status_validasi" required>
                                        <option value="aktif" <?= $status_validasi == 'aktif' ? 'selected' : '' ?>>
                                            Aktif - Terapkan validasi minimal nilai
                                        </option>
                                        <option value="nonaktif" <?= $status_validasi == 'nonaktif' ? 'selected' : '' ?>>
                                            Nonaktif - Biarkan semua nilai diterima
                                        </option>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Jika <strong>Aktif</strong>, sistem akan menolak pendaftaran dengan nilai
                                        rata-rata di bawah minimal.
                                        Jika <strong>Nonaktif</strong>, semua nilai diterima tanpa validasi.
                                    </div>
                                </div>

                                <div class="border-top pt-4">
                                    <button type="submit" name="save_minimal" class="btn btn-primary btn-lg px-5">
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
                        <div class="card-header bg-info text-white border-0 py-3">
                            <h6 class="fw-bold mb-0">
                                <i class="fas fa-lightbulb me-2"></i>
                                Informasi
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong class="d-block mb-2">📊 Current Setting:</strong>
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <div class="display-4 fw-bold text-primary mb-1"><?= $minimal_nilai ?></div>
                                    <div class="text-muted small">Minimal Nilai Rata-rata</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <strong class="d-block mb-2">🎯 Status Validasi:</strong>
                                <?php if ($status_validasi == 'aktif'): ?>
                                    <span class="badge bg-success fs-6 px-3 py-2 w-100">
                                        <i class="fas fa-check-circle me-1"></i> AKTIF
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary fs-6 px-3 py-2 w-100">
                                        <i class="fas fa-times-circle me-1"></i> NONAKTIF
                                    </span>
                                <?php endif; ?>
                            </div>

                            <hr>

                            <div class="small">
                                <strong><i class="fas fa-question-circle me-1"></i> Cara Kerja:</strong>
                                <ul class="mt-2 mb-0">
                                    <li>Murid menginput nilai rapor untuk 5 semester (Kelas 4 s/d Kelas 6).</li>
                                    <li>Sistem secara otomatis menghitung <strong>rata-rata</strong> dari total nilai
                                        tersebut.</li>
                                    <li>Jika validasi <strong>AKTIF</strong>, sistem akan mengecek apakah rata-rata
                                        tersebut mencapai ambang batas.</li>
                                    <li>Jika di bawah minimal, pendaftaran pada form akan terblokir (Popup Alert).</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Alert -->
                    <div class="card border-warning shadow-sm rounded-4">
                        <div class="card-header bg-warning bg-opacity-10 border-0 py-3">
                            <h6 class="fw-bold mb-0 text-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Preview Pesan Error
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-times-circle me-2"></i>
                                <strong>Maaf!</strong> Nilai rata-rata Anda (<strong>70.50</strong>) belum mencapai
                                minimal yang ditetapkan (<strong><?= $minimal_nilai ?></strong>).
                            </div>
                            <small class="text-muted d-block mt-2">
                                * Contoh pesan yang akan muncul saat murid input nilai di bawah minimal
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>