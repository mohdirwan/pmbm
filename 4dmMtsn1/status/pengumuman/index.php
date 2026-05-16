<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$success_msg = '';
$error_msg = '';

// Handle Form POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_settings'])) {
        try {
            $keys = ['announcement_title', 'announcement_body', 'announcement_status'];
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

            foreach ($keys as $key) {
                $val = $_POST[$key] ?? '';
                $stmt->execute([$key, $val]);
            }
            $pdo->commit();
            $success_msg = "Pengaturan pengumuman berhasil disimpan.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = "Gagal menyimpan: " . $e->getMessage();
        }
    }
}

// Get current settings
$announcement_title = get_setting('announcement_title', 'Hasil Seleksi PPDB');
$announcement_body = get_setting('announcement_body', 'Silakan cek status kelulusan Anda pada menu Status Akhir.');
$announcement_status = get_setting('announcement_status', 'closed'); // open / closed

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Manajemen Pengumuman - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <style>
        .main-content {
            margin-left: 260px;
            padding: 30px;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .card-announcement {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .card-announcement:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        }

        .status-toggle {
            cursor: pointer;
            padding: 10px 20px;
            border-radius: 50px;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .status-toggle.active {
            background-color: #d1e7dd;
            color: #0f5132;
            border-color: #badbcc;
        }

        .status-toggle.inactive {
            background-color: #f8d7da;
            color: #842029;
            border-color: #f5c2c7;
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
                    <h2 class="text-primary fw-bold mb-1"><i class="fas fa-bullhorn me-2"></i>Manajemen Pengumuman</h2>
                    <p class="text-muted small">Kelola informasi kelulusan dan notifikasi kepada pendaftar.</p>
                </div>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?= $success_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Left Column: Settings -->
                <div class="col-lg-8">
                    <form method="POST">
                        <input type="hidden" name="update_settings" value="1">

                        <div class="card card-announcement mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4 text-dark border-bottom pb-2">Konten Pengumuman</h5>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Judul
                                        Pengumuman</label>
                                    <input type="text" name="announcement_title"
                                        class="form-control form-control-lg fw-bold"
                                        value="<?= htmlspecialchars($announcement_title) ?>"
                                        placeholder="Contoh: Hasil Seleksi Tahap 1">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Isi
                                        Pengumuman</label>
                                    <textarea name="announcement_body" id="summernote"
                                        class="form-control"><?= $announcement_body ?></textarea>
                                    <div class="form-text small"><i class="fas fa-info-circle me-1"></i> Informasi ini
                                        akan tampil di dashboard murid saat pengumuman dibuka.</div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-announcement">
                            <div class="card-body p-4 bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-1">Simpan Perubahan</h6>
                                        <p class="text-muted small mb-0">Pastikan data sudah benar sebelum disimpan.</p>
                                    </div>
                                    <button type="submit"
                                        class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">
                                        <i class="fas fa-save me-2"></i> Simpan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Status & Broadcast -->
                <div class="col-lg-4">
                    <!-- Status Toggle Card -->
                    <div class="card card-announcement mb-4">
                        <div class="card-body p-4 text-center">
                            <h5 class="fw-bold mb-3">Status Pengumuman</h5>
                            <p class="text-muted small mb-4">Atur visibilitas hasil seleksi bagi siswa.</p>

                            <form method="POST">
                                <input type="hidden" name="update_settings" value="1">
                                <input type="hidden" name="announcement_title"
                                    value="<?= htmlspecialchars($announcement_title) ?>">
                                <input type="hidden" name="announcement_body"
                                    value="<?= htmlspecialchars($announcement_body) ?>">

                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="announcement_status" id="status_closed"
                                        value="closed" <?= $announcement_status == 'closed' ? 'checked' : '' ?>
                                        onchange="this.form.submit()">
                                    <label class="btn btn-outline-danger py-3 rounded-start-pill" for="status_closed">
                                        <i class="fas fa-lock me-2"></i> Ditutup
                                    </label>

                                    <input type="radio" class="btn-check" name="announcement_status" id="status_open"
                                        value="open" <?= $announcement_status == 'open' ? 'checked' : '' ?>
                                        onchange="this.form.submit()">
                                    <label class="btn btn-outline-success py-3 rounded-end-pill" for="status_open">
                                        <i class="fas fa-lock-open me-2"></i> Dibuka
                                    </label>
                                </div>
                            </form>

                            <div class="mt-3">
                                <?php if ($announcement_status == 'open'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                        <i class="fas fa-check-circle me-1"></i> Murid dapa melihat hasil
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
                                        <i class="fas fa-ban me-1"></i> Hasil disembunyikan
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#summernote').summernote({
                placeholder: 'Tulis isi pengumuman di sini...',
                tabsize: 2,
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
</body>

</html>