<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Only Admin can access
if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak. Hanya Administrator yang dapat melakukan operasi ini.");
}

$success_msg = '';
$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $status = $_POST['maintenance_mode'] ?? 'off';
        $message = $_POST['maintenance_message'] ?? 'Saat ini belum ada info pendaftaran.';

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        $stmt->execute(['maintenance_mode', $status]);
        $stmt->execute(['maintenance_message', $message]);

        $pdo->commit();
        $success_msg = "Status website berhasil diperbarui!";
        log_activity("Update Status Website", "Maintenance mode diatur ke: $status");
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error_msg = "Gagal menyimpan: " . $e->getMessage();
    }
}

$maintenance_mode = get_setting('maintenance_mode', 'off');
$maintenance_message = get_setting('maintenance_message', 'Saat ini belum ada info pendaftaran.');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Status Website - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .main-content { margin-left: 260px; padding: 30px; min-height: 100vh; background: #f8f9fa; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .status-badge { font-size: 0.9rem; padding: 5px 15px; border-radius: 20px; }
        .form-switch .form-check-input { width: 3em; height: 1.5em; cursor: pointer; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-primary"><i class="fas fa-power-off me-2"></i> Pengaturan Status Website</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Status Website</li>
                    </ol>
                </nav>
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

            <div class="row">
                <div class="col-md-7">
                    <div class="card p-4 mb-4">
                        <h5 class="fw-bold mb-4">Konfigurasi Set Off (Maintenance)</h5>
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-bold d-block">Status Website Saat Ini</label>
                                <div class="d-flex align-items-center p-3 bg-light rounded-4 border">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode" value="on" <?= $maintenance_mode == 'on' ? 'checked' : '' ?>>
                                        <label class="form-check-label ms-2 fw-bold" for="maintenance_mode">
                                            <?= $maintenance_mode == 'on' ? '<span class="text-danger">OFF (Maintenance Aktif)</span>' : '<span class="text-success">ON (Website Normal)</span>' ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-text mt-2">
                                    Jika dimatikan (OFF), pengunjung website hanya akan melihat pesan pemberitahuan dan tidak dapat mengakses fitur pendaftaran.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Pesan Pemberitahuan</label>
                                <textarea class="form-control" name="maintenance_message" rows="4" placeholder="Contoh: Saat ini belum ada info pendaftaran."><?= htmlspecialchars($maintenance_message) ?></textarea>
                                <div class="form-text">Pesan ini akan muncul di halaman depan saat website dalam status OFF.</div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
                                <i class="fas fa-save me-2"></i> Simpan Pengaturan
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card p-4 bg-dark text-white h-100">
                        <h5 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-warning"></i> Panduan Penggunaan</h5>
                        <p class="text-white-50 small">Fitur "Set Off" digunakan untuk menonaktifkan seluruh akses publik ke fitur PMBM sementara waktu. Ini berguna saat:</p>
                        <ul class="text-white-50 small">
                            <li>Persiapan tahun ajaran baru.</li>
                            <li>Pemeliharaan database atau server.</li>
                            <li>Jeda antar gelombang pendaftaran.</li>
                        </ul>
                        <div class="alert alert-warning border-0 small mt-3">
                            <i class="fas fa-exclamation-circle me-1"></i> <strong>Catatan:</strong> Panel Admin tetap dapat diakses meskipun website dalam status OFF.
                        </div>
                        
                        <div class="mt-auto pt-4 border-top border-secondary">
                            <h6 class="fw-bold mb-2">Preview Tampilan:</h6>
                            <div class="p-3 bg-secondary bg-opacity-25 rounded-3 border border-secondary border-opacity-50">
                                <div class="text-center">
                                    <i class="fas fa-bullhorn fa-2x mb-2 text-warning"></i>
                                    <p class="mb-0 small fst-italic">"<?= htmlspecialchars($maintenance_message) ?>"</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update label text dynamically when switch changes
        document.getElementById('maintenance_mode').addEventListener('change', function() {
            const label = this.nextElementSibling;
            if(this.checked) {
                label.innerHTML = '<span class="text-danger">OFF (Maintenance Aktif)</span>';
            } else {
                label.innerHTML = '<span class="text-success">ON (Website Normal)</span>';
            }
        });
    </script>
</body>
</html>
