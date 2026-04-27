<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Only Admin can access
if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak. Hanya Administrator yang dapat melakukan operasi ini.");
}

$success_msg = '';
$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'archive_reset') {
    try {
        $pdo->beginTransaction();

        $year = get_setting('ppdb_year', date('Y'));
        $clean_year = preg_replace('/[^A-Za-z0-9]/', '_', $year);
        $archive_table = "pendaftar_archive_" . $clean_year . "_" . date('Ymd_His');

        // 1. ARCHIVE TABLE
        // Check if table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'pendaftar'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            // Rename current pendaftar to archive
            $pdo->exec("RENAME TABLE pendaftar TO `$archive_table` ");
            
            // Re-create empty pendaftar table
            $pdo->exec("CREATE TABLE pendaftar LIKE `$archive_table` ");
        }

        // 2. ARCHIVE UPLOADS FOLDER
        $uploadDir = '../uploads/';
        if (is_dir($uploadDir)) {
            $timestamp = date('Ymd_His');
            $backupDir = '../uploads_archive_' . $clean_year . '_' . $timestamp . '/';
            
            // Move current uploads to archive
            if (rename($uploadDir, $backupDir)) {
                // Create new fresh uploads directory
                mkdir($uploadDir, 0755, true);
                
                // RESTORE LOGOS & SYSTEM DOCUMENTS (so UI doesn't break)
                $folders_to_restore = ['logo', 'documents'];
                foreach ($folders_to_restore as $folder) {
                    $src = $backupDir . $folder;
                    $dst = $uploadDir . $folder;
                    
                    if (is_dir($src)) {
                        mkdir($dst, 0755, true);
                        $files = scandir($src);
                        foreach ($files as $file) {
                            if ($file != "." && $file != "..") {
                                copy($src . '/' . $file, $dst . '/' . $file);
                            }
                        }
                    }
                }
            }
        }

        // 3. RESET SETTINGS
        $settings_to_reset = [
            'ppdb_status' => 'belum',
            'announcement_status' => 'closed',
            'tahap_administrasi' => 'pendaftaran'
        ];

        $stmtSet = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($settings_to_reset as $key => $val) {
            $stmtSet->execute([$key, $val]);
        }

        // 4. LOG ACTIVITY
        log_activity("Arsip & Reset Sistem", "Sistem diarsip ke tabel $archive_table dan folder pendaftaran dibersihkan.");

        $pdo->commit();
        $success_msg = "Sistem Berhasil Diarsip! Data lama disimpan di tabel <strong>$archive_table</strong> dan folder pendaftaran kini kosong (Fresh Start).";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error_msg = "Gagal melakukan arsip: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pemeliharaan Sistem - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar { height: 100vh; background: #0f5132; color: white; padding-top: 20px; position: fixed; width: 260px; z-index: 1000; }
        .main-content { margin-left: 260px; padding: 30px; min-height: 100vh; background: #f8f9fa; }
        .warning-card { border-left: 5px solid #dc3545; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4 fw-bold text-danger"><i class="fas fa-tools me-2"></i> Pemeliharaan & Reset Sistem</h2>
            
            <?php if ($success_msg): ?>
                <div class="alert alert-success shadow-sm border-0 mb-4 p-4 rounded-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fa-3x me-3 text-success"></i>
                        <div>
                            <h4 class="fw-bold mb-1">Berhasil!</h4>
                            <p class="mb-0"><?= $success_msg ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger shadow-sm border-0 mb-4 p-4 rounded-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-3x me-3 text-danger"></i>
                        <div>
                            <h4 class="fw-bold mb-1">Terjadi Kesalahan!</h4>
                            <p class="mb-0"><?= $error_msg ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4 warning-card">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-danger mb-3">Arsip & Mulai Pendaftaran Baru</h5>
                            <p class="text-muted">Fitur ini digunakan saat Anda ingin memulai tahun ajaran baru atau gelombang baru dan ingin mengosongkan data pendaftar yang sekarang.</p>
                            
                            <div class="alert alert-warning border-0 rounded-3">
                                <h6 class="fw-bold"><i class="fas fa-info-circle me-1"></i> Apa yang terjadi saat tombol diklik?</h6>
                                <ul class="mb-0 small">
                                    <li>Data di tabel <strong>pendaftar</strong> akan dipindahkan ke tabel arsip baru.</li>
                                    <li>Semua file di folder <strong>uploads/</strong> akan dipindahkan ke folder arsip (Backup).</li>
                                    <li>Tabel pendaftar akan kembali kosong (0 siswa).</li>
                                    <li>Status PPDB akan diatur ulang ke "Belum Dibuka".</li>
                                </ul>
                            </div>

                            <form method="POST" onsubmit="return confirm('APAKAH ANDA YAKIN? Tindakan ini akan mengosongkan data pendaftar yang aktif sekarang. Data lama akan aman di tabel arsip, tapi pendaftaran aktif akan jadi kosong.');">
                                <input type="hidden" name="action" value="archive_reset">
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Konfirmasi Tahun Ajaran Saat Ini:</label>
                                    <input type="text" class="form-control bg-light" value="<?= get_setting('ppdb_year') ?>" readonly>
                                    <div class="form-text">Nama arsip akan menggunakan tahun ajaran di atas.</div>
                                </div>

                                <button type="submit" class="btn btn-danger btn-lg px-4 rounded-pill shadow-sm">
                                    <i class="fas fa-archive me-2"></i> Arsip & Reset Pendaftaran Sekarang
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white">
                        <h5 class="fw-bold mb-3"><i class="fas fa-shield-alt me-2"></i> Tips Keamanan</h5>
                        <ul class="mb-0">
                            <li class="mb-2">Gunakan fitur ini hanya sekali setahun atau per periode besar.</li>
                            <li class="mb-2">Admin disarankan tetap melakukan export ke Excel di menu Laporan sebagai cadangan fisik.</li>
                            <li>Pastikan tidak ada pendaftar yang sedang mengisi form saat proses ini dilakukan.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
