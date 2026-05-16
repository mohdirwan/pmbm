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

        // Handle Background Upload
        if (isset($_FILES['maintenance_bg']) && $_FILES['maintenance_bg']['error'] == 0) {
            $uploadDir = '../uploads/maintenance/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['maintenance_bg']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExt, $allowed)) {
                $fileName = 'bg_' . time() . '.' . $fileExt;
                $targetFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['maintenance_bg']['tmp_name'], $targetFile)) {
                    $dbPath = 'uploads/maintenance/' . $fileName;
                    
                    // Delete old file if exists
                    $oldFile = get_setting('maintenance_bg');
                    if ($oldFile && file_exists('../' . $oldFile)) {
                        unlink('../' . $oldFile);
                    }

                    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('maintenance_bg', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                    $stmt->execute([$dbPath]);
                }
            }
        }

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
$maintenance_bg = get_setting('maintenance_bg', '');
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
        .bg-preview { 
            width: 100%; 
            height: 150px; 
            background-size: cover; 
            background-position: center; 
            border-radius: 10px;
            margin-top: 10px;
            border: 2px dashed #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
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
                        <form method="POST" enctype="multipart/form-data">
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
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Pesan Pemberitahuan</label>
                                <textarea class="form-control" name="maintenance_message" rows="3" placeholder="Contoh: Saat ini belum ada info pendaftaran."><?= htmlspecialchars($maintenance_message) ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Background Khusus Maintenance</label>
                                <input type="file" class="form-control" name="maintenance_bg" accept="image/*">
                                <div class="form-text">Upload gambar untuk latar belakang halaman maintenance (Rekomendasi: 1920x1080).</div>
                                
                                <?php if ($maintenance_bg): ?>
                                    <div class="bg-preview" style="background-image: url('<?= BASE_URL . $maintenance_bg ?>');">
                                        <span class="badge bg-dark opacity-75">Background Aktif</span>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-preview">
                                        <span>Belum ada background kustom</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow w-100">
                                <i class="fas fa-save me-2"></i> Simpan Pengaturan
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card p-4 bg-dark text-white h-100">
                        <h5 class="fw-bold mb-3"><i class="fas fa-magic me-2 text-warning"></i> Visual Lebih Wah!</h5>
                        <p class="text-white-50 small">Dengan mengupload background kustom, tampilan halaman pemberitahuan akan terlihat lebih profesional dan eksklusif.</p>
                        
                        <div class="alert alert-info border-0 small mt-3 py-2">
                            <i class="fas fa-lightbulb me-1"></i> <strong>Tips:</strong> Gunakan gambar sekolah atau gradasi warna yang elegan agar pesan tetap terbaca jelas.
                        </div>

                        <div class="mt-4 pt-4 border-top border-secondary">
                            <h6 class="fw-bold mb-3">Elemen Visual Terkini:</h6>
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex align-items-center gap-2 small">
                                    <i class="fas fa-check-circle text-success"></i> Glassmorphism Effect
                                </div>
                                <div class="d-flex align-items-center gap-2 small">
                                    <i class="fas fa-check-circle text-success"></i> Dynamic Background Overlay
                                </div>
                                <div class="d-flex align-items-center gap-2 small">
                                    <i class="fas fa-check-circle text-success"></i> Smooth Micro-animations
                                </div>
                                <div class="d-flex align-items-center gap-2 small">
                                    <i class="fas fa-check-circle text-success"></i> Responsive Layout
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
        document.getElementById('maintenance_mode').addEventListener('change', function() {
            const label = this.nextElementSibling;
            label.innerHTML = this.checked ? '<span class="text-danger">OFF (Maintenance Aktif)</span>' : '<span class="text-success">ON (Website Normal)</span>';
        });
    </script>
</body>
</html>
