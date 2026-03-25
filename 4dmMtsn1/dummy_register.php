<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Enforce admin access if necessary, or let regular dashboard access apply.
if ($_SESSION['role'] !== 'admin' && !has_access('sistem')) {
    die("Akses ditolak.");
}

$success_msg = '';
$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $allowed_keys = ['dummy_register'];

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        foreach ($allowed_keys as $key) {
            if (isset($_POST[$key])) {
                $val = clean_input($_POST[$key]);
                $stmt->execute([$key, $val]);
            }
        }

        $pdo->commit();
        $success_msg = "Pengaturan Dummy Register berhasil diperbarui!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Gagal menyimpan: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dummy Register - Admin PMBM</title>
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

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            padding: 12px 20px;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-left: 4px solid #ffc107;
        }

        .nav-link i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
        }
    </style>
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content ps-5">
        <div class="container-fluid">
            <h2 class="mb-4 text-primary fw-bold">Pengaturan Dummy Register</h2>

            <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?= $success_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?= $error_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                        <div class="alert alert-info border-0 rounded-3">
                            <i class="fas fa-info-circle me-2"></i> <strong>Informasi:</strong> Saat Dummy Register
                            diaktifkan, semua atribut <code>required</code> (wajib isi) pada form pendaftaran murid
                            <code>(register.php)</code> akan dinonaktifkan sementara. Ini berguna untuk mempercepat
                            pengujian tanpa mengisi seluruh form. Status normal akan kembali jika fitur dimatikan.
                        </div>

                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-bold d-block">Status Dummy Register</label>
                                <?php $dummy_status = get_setting('dummy_register', '0'); ?>

                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input border-primary" type="checkbox" role="switch"
                                        id="dummy_register_toggle" name="dummy_register" value="1" <?= $dummy_status == '1' ? 'checked' : '' ?>
                                        style="transform: scale(1.5); margin-right: 15px; cursor: pointer;">
                                    <label class="form-check-label ms-2" for="dummy_register_toggle" id="dummy_label"
                                        style="cursor: pointer;">
                                        <?= $dummy_status == '1' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Non-Aktif</span>' ?>
                                    </label>
                                </div>
                                <div class="form-text mt-2">Geser tombol untuk (Mengaktifkan / Mematikan) mode Dummy.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Simpan
                                Pengaturan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('dummy_register_toggle').addEventListener('change', function () {
            var label = document.getElementById('dummy_label');
            if (this.checked) {
                label.innerHTML = '<span class="badge bg-success">Aktif</span>';
            } else {
                label.innerHTML = '<span class="badge bg-secondary">Non-Aktif</span>';
            }
        });
    </script>
</body>

</html>