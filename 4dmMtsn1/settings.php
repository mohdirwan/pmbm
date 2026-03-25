<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $allowed_keys = ['school_name', 'ppdb_year', 'wave_name', 'countdown_date', 'contact_phone', 'contact_email', 'hero_title', 'hero_desc', 'age_limit_status', 'age_cutoff_date', 'max_age_limit', 'admin_theme'];

        $pdo->beginTransaction();

        // Handle File Uploads (Logos & Documents)
        $logo_fields = ['school_logo', 'header_logo', 'juknis_file'];
        foreach ($logo_fields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
                $uploadDir = '../uploads/' . ($field == 'juknis_file' ? 'documents/' : 'logo/');
                if (!is_dir($uploadDir))
                    mkdir($uploadDir, 0755, true);

                $fileExt = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'pdf'];

                if (in_array($fileExt, $allowed)) {
                    $fileName = $field . '_' . time() . '.' . $fileExt;
                    $targetFile = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES[$field]['tmp_name'], $targetFile)) {
                        $folderName = ($field == 'juknis_file') ? 'documents/' : 'logo/';
                        $dbPath = 'uploads/' . $folderName . $fileName;

                        // Delete old file if exists
                        $oldFile = get_setting($field);
                        if ($oldFile && file_exists('../' . $oldFile)) {
                            unlink('../' . $oldFile);
                        }

                        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                        $stmt->execute([$field, $dbPath]);
                    }
                }
            }
        }

        // Use INSERT ... ON DUPLICATE KEY UPDATE to ensure values are saved even if the row doesn't exist yet
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        foreach ($allowed_keys as $key) {
            if (isset($_POST[$key])) {
                $val = $_POST[$key];
                // Store date directly in ISO format which is safer
                $stmt->execute([$key, $val]);
            }
        }

        $pdo->commit();
        $success_msg = "Pengaturan berhasil diperbarui!";
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
    <title>Pengaturan Website - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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

    </head>

    <body>

        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content ps-5">
            <div class="container-fluid">
                <h2 class="mb-4 text-primary fw-bold">Pengaturan Website</h2>

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

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <!-- Navigation Pills for Settings -->
                        <div class="col-md-3">
                            <div class="list-group shadow-sm border-0 sticky-top" style="top: 20px;">
                                <a class="list-group-item list-group-item-action active border-0 py-3" href="#general"
                                    data-bs-toggle="list">
                                    <i class="fas fa-info-circle me-2"></i> Informasi Umum
                                </a>
                                <a class="list-group-item list-group-item-action border-0 py-3" href="#hero"
                                    data-bs-toggle="list">
                                    <i class="fas fa-chalkboard me-2"></i> Tampilan Beranda
                                </a>
                                <a class="list-group-item list-group-item-action border-0 py-3" href="#documents"
                                    data-bs-toggle="list">
                                    <i class="fas fa-file-alt me-2"></i> Dokumen & Juknis
                                </a>
                                <a class="list-group-item list-group-item-action border-0 py-3" href="#contact"
                                    data-bs-toggle="list">
                                    <i class="fas fa-address-book me-2"></i> Kontak Kami
                                </a>
                                <a class="list-group-item list-group-item-action border-0 py-3" href="#appearance"
                                    data-bs-toggle="list">
                                    <i class="fas fa-paint-brush me-2"></i> Tema Admin
                                </a>
                            </div>
                        </div>

                        <!-- Settings Content -->
                        <div class="col-md-9">
                            <div class="tab-content">
                                <!-- Helper: General -->
                                <div class="tab-pane fade show active" id="general">
                                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                                        <h5 class="fw-bold mb-4 text-primary">Informasi Sekolah</h5>
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">Nama Sekolah</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i
                                                            class="fas fa-school"></i></span>
                                                    <input type="text" class="form-control" name="school_name"
                                                        value="<?= get_setting('school_name') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Tahun Ajaran</label>
                                                <input type="text" class="form-control" name="ppdb_year"
                                                    value="<?= get_setting('ppdb_year') ?>"
                                                    placeholder="Contoh: 2026/2027">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="wave_name"
                                                    value="<?= get_setting('wave_name') ?>"
                                                    placeholder="Contoh: Gelombang 1">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Logo Sekolah (Navbar)</label>
                                                <div class="input-group mb-2">
                                                    <input type="file" class="form-control" name="school_logo"
                                                        accept="image/*">
                                                </div>
                                                <small class="text-muted">Akan muncul di pojok kiri atas
                                                    (Navbar).</small>
                                                <?php if ($logo = get_setting('school_logo')): ?>
                                                    <div class="mt-2 text-center bg-light p-2 rounded border">
                                                        <img src="<?= BASE_URL . $logo ?>" style="max-height: 50px;">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Logo Besar (Header/Hero)</label>
                                                <div class="input-group mb-2">
                                                    <input type="file" class="form-control" name="header_logo"
                                                        accept="image/*">
                                                </div>
                                                <small class="text-muted">Akan muncul di tengah hero section (ikon
                                                    kuning).</small>
                                                <?php if ($logo = get_setting('header_logo')): ?>
                                                    <div class="mt-2 text-center bg-dark p-2 rounded">
                                                        <img src="<?= BASE_URL . $logo ?>" style="max-height: 80px;">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Helper: Hero -->
                                <div class="tab-pane fade" id="hero">
                                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                                        <h5 class="fw-bold mb-4 text-primary">Tampilan Utama (Hero Section)</h5>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Judul Utama</label>
                                            <input type="text" class="form-control form-control-lg" name="hero_title"
                                                value="<?= get_setting('hero_title') ?>">
                                            <div class="form-text">Judul besar yang muncul pertama kali di website.
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Deskripsi Singkat</label>
                                            <textarea class="form-control" name="hero_desc"
                                                rows="3"><?= get_setting('hero_desc') ?></textarea>
                                            <div class="form-text">Kalimat ajakan ramah yang muncul di bawah judul.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Helper: Documents -->
                                <div class="tab-pane fade" id="documents">
                                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                                        <h5 class="fw-bold mb-4 text-primary">Dokumen & Petunjuk Teknis</h5>
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">File Petunjuk Teknis (Juknis)</label>
                                            <div class="input-group mb-2">
                                                <span class="input-group-text bg-light"><i
                                                        class="fas fa-file-pdf"></i></span>
                                                <input type="file" class="form-control" name="juknis_file"
                                                    accept=".pdf">
                                            </div>
                                            <div class="form-text">Upload file PDF petunjuk teknis yang akan didownload
                                                calon siswa.</div>

                                            <?php if ($juknis = get_setting('juknis_file')): ?>
                                                <div class="mt-3 p-3 bg-light rounded border">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="fw-bold text-success"><i
                                                                class="fas fa-check-circle me-2"></i>File Tersedia</span>
                                                        <a href="<?= BASE_URL . $juknis ?>" target="_blank"
                                                            class="btn btn-sm btn-outline-primary"><i
                                                                class="fas fa-download me-1"></i>Download</a>
                                                    </div>

                                                    <!-- PDF Preview -->
                                                    <?php if (strpos($juknis, '.pdf') !== false): ?>
                                                        <div class="ratio ratio-16x9 border shadow-sm rounded overflow-hidden">
                                                            <iframe src="<?= BASE_URL . $juknis ?>" allowfullscreen></iframe>
                                                        </div>
                                                        <div class="text-center mt-2 small text-muted">Preview File Juknis</div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Helper: Contact -->
                                <div class="tab-pane fade" id="contact">
                                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                                        <h5 class="fw-bold mb-4 text-primary">Kontak Informasi</h5>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">No. Telepon / WhatsApp</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i
                                                            class="fas fa-phone"></i></span>
                                                    <input type="text" class="form-control" name="contact_phone"
                                                        value="<?= get_setting('contact_phone') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Alamat Email</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i
                                                            class="fas fa-envelope"></i></span>
                                                    <input type="text" class="form-control" name="contact_email"
                                                        value="<?= get_setting('contact_email') ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Helper: Appearance -->
                                <div class="tab-pane fade" id="appearance">
                                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                                        <h5 class="fw-bold mb-4 text-primary">Tema Panel Admin</h5>
                                        <p class="text-muted">Pilih tampilan yang paling sesuai dengan keinginan Anda.
                                        </p>

                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <div class="theme-option p-3 border rounded-4 text-center">
                                                    <div class="theme-preview mb-3 bg-success rounded-3"
                                                        style="height: 120px; background: #0f5132 !important;">
                                                        <div
                                                            class="d-flex h-100 align-items-center justify-content-center text-white">
                                                            <i class="fas fa-school fa-3x"></i>
                                                        </div>
                                                    </div>
                                                    <div class="form-check justify-content-center d-flex gap-2">
                                                        <input class="form-check-input" type="radio" name="admin_theme"
                                                            id="theme1" value="theme1" <?= get_setting('admin_theme', 'theme1') == 'theme1' ? 'checked' : '' ?>>
                                                        <label class="form-check-label fw-bold" for="theme1">
                                                            Tema 1 (Original Green)
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="theme-option p-3 border rounded-4 text-center">
                                                    <div class="theme-preview mb-3 rounded-3"
                                                        style="height: 120px; background: linear-gradient(135deg, #8B5CF6, #EC4899) !important;">
                                                        <div
                                                            class="d-flex h-100 align-items-center justify-content-center text-white">
                                                            <i class="fas fa-magic fa-3x"></i>
                                                        </div>
                                                    </div>
                                                    <div class="form-check justify-content-center d-flex gap-2">
                                                        <input class="form-check-input" type="radio" name="admin_theme"
                                                            id="theme2" value="theme2" <?= get_setting('admin_theme', 'theme1') == 'theme2' ? 'checked' : '' ?>>
                                                        <label class="form-check-label fw-bold" for="theme2">
                                                            Tema 2 (Modern Purple)
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sticky Clean Save Button -->
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white sticky-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i class="fas fa-check-circle me-1"></i> Perubahan
                                        akan
                                        langsung tampil di website.</span>
                                    <button type="submit" class="btn btn-premium px-5 rounded-pill shadow"><i
                                            class="fas fa-save me-2"></i> Simpan Semua Perubahan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Settings for Date Only
                flatpickr(".datepicker", {
                    locale: "id",
                    altInput: true,
                    altFormat: "d/m/Y",
                    dateFormat: "Y-m-d",
                });

                // Settings for DateTime
                flatpickr(".datetimepicker", {
                    locale: "id",
                    enableTime: true,
                    time_24hr: true,
                    altInput: true,
                    altFormat: "d/m/Y H:i",
                    dateFormat: "Y-m-d H:i:S",
                });
            });

            // Activate tabs on click
            var triggerTabList = [].slice.call(document.querySelectorAll('.list-group-item'))
            triggerTabList.forEach(function (triggerEl) {
                var tabTrigger = new bootstrap.Tab(triggerEl)
                triggerEl.addEventListener('click', function (event) {
                    event.preventDefault()
                    tabTrigger.show()
                })
            })
        </script>
    </body>

</html>