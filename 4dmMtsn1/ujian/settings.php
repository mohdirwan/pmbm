<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $allowed_keys = [
            'cbt_status',
            'cbt_duration',
            'cbt_passing_grade',
            'cbt_announcement_date',
            'cbt_rules',
            'cbt_info_visibility'
        ];

        $pdo->beginTransaction();

        // Handle File Upload
        if (isset($_FILES['cbt_tutorial_pdf']) && $_FILES['cbt_tutorial_pdf']['error'] == 0) {
            $file = $_FILES['cbt_tutorial_pdf'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

            if (strtolower($ext) !== 'pdf') {
                throw new Exception("File harus berformat PDF.");
            }

            $filename = 'tutorial_cbt_' . time() . '.pdf';
            $upload_dir = '../../uploads/docs/';
            if (!is_dir($upload_dir))
                mkdir($upload_dir, 0777, true);

            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                // Save filename to settings
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('cbt_tutorial_pdf', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->execute([$filename]);
            }
        }

        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        foreach ($allowed_keys as $key) {
            if (isset($_POST[$key])) {
                $stmt->execute([$key, $_POST[$key]]);
            }
        }

        $pdo->commit();
        $success_msg = "Pengaturan CBT berhasil diperbarui!";
    } catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        $error_msg = "Gagal menyimpan: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Jadwal & Pengaturan CBT - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .main-content {
            margin-left: 260px;
            padding: 30px;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .card-premium {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .form-control-premium {
            border: 1.5px solid #eef0f2;
            border-radius: 12px;
            padding: 12px 18px;
        }

        .form-control-premium:focus {
            border-color: #0b2c24;
            box-shadow: 0 0 0 4px rgba(11, 44, 36, 0.1);
        }

        .btn-premium {
            background: linear-gradient(135deg, #0b2c24, #1a4d40);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 15px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(11, 44, 36, 0.2);
            transition: all 0.3s;
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(11, 44, 36, 0.3);
            color: white;
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content ps-5">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-primary fw-bold mb-1">Jadwal & Pengaturan CBT</h2>
                    <p class="text-muted small">Kelola jadwal ujian, tata tertib, dan akun CBT untuk siswa.</p>
                </div>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $success_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= $error_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card card-premium p-4 mb-4">
                            <h5 class="fw-bold mb-4 text-dark"><i class="fas fa-eye me-2 text-primary"></i>Visibilitas Panduan PDF</h5>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Panduan / Tutorial Ujian (PDF) di Siswa</label>
                                <select name="cbt_info_visibility" class="form-select form-control-premium">
                                    <option value="aktif" <?= get_setting('cbt_info_visibility') == 'aktif' ? 'selected' : '' ?>>Aktif (Muncul)</option>
                                    <option value="tidak_aktif" <?= get_setting('cbt_info_visibility') == 'tidak_aktif' ? 'selected' : '' ?>>Tidak Aktif (Sembunyi)</option>
                                </select>
                                <div class="form-text mt-2 text-muted small">Jika diset <b>Tidak Aktif</b>, maka file Panduan PDF akan disembunyikan dari halaman Informasi Ujian siswa. <b>Tata Tertib akan tetap muncul.</b></div>
                            </div>

                            <hr class="my-4 opacity-25">

                            <h5 class="fw-bold mt-5 mb-4 text-dark"><i class="fas fa-gavel me-2 text-primary"></i>Tata
                                Tertib Ujian</h5>
                            <div class="mb-3">
                                <textarea name="cbt_rules" class="form-control form-control-premium" rows="8"
                                    placeholder="Masukkan aturan ujian untuk siswa..."><?= get_setting('cbt_rules') ?></textarea>
                            </div>
                            <h5 class="fw-bold mt-5 mb-4 text-dark"><i
                                    class="fas fa-file-pdf me-2 text-primary"></i>Panduan / Tutorial Ujian (PDF)</h5>
                            <div class="mb-3">
                                <div class="p-3 border rounded-4 bg-light mb-3">
                                    <?php $current_pdf = get_setting('cbt_tutorial_pdf'); ?>
                                    <?php if ($current_pdf): ?>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-white p-2 rounded-3 shadow-sm me-3">
                                                <i class="fas fa-file-pdf text-danger fs-3"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold small text-dark"><?= $current_pdf ?></div>
                                                <a href="<?= BASE_URL ?>uploads/docs/<?= $current_pdf ?>" target="_blank"
                                                    class="small text-decoration-none">
                                                    <i class="fas fa-eye me-1"></i> Lihat File
                                                </a>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-2 text-muted small">
                                            <i class="fas fa-info-circle me-1"></i> Belum ada file panduan yang diupload.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="cbt_tutorial_pdf" class="form-control form-control-premium"
                                    accept="application/pdf">
                                <div class="form-text mt-2 small">Upload file tutorial dalam format PDF agar siswa dapat
                                    mendownload panduan cara mengerjakan ujian.</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card card-premium p-4 sticky-top" style="top: 100px;">
                            <div class="bg-info-subtle text-info p-3 rounded-4 mb-4">
                                <i class="fas fa-info-circle fs-4 me-2"></i><strong>Penting!</strong>
                            </div>
                            <p class="small text-muted mb-4">Pengaturan ini akan berdampak langsung pada tampilan
                                dashboard murid di menu "Informasi Ujian".</p>

                            <ul class="small text-muted ps-3">
                                <li class="mb-2">Jika status <strong>Buka</strong>, tombol "Mulai Ujian" akan muncul.
                                </li>
                                <li class="mb-2">Durasi digunakan sebagai timer hitung mundur.</li>
                                <li>Passing grade digunakan untuk bantuan analisis kelulusan otomatis.</li>
                            </ul>

                            <hr class="my-4 opacity-50">

                            <button type="submit" class="btn btn-premium w-100">
                                <i class="fas fa-save me-2"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr(".datepicker", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d F Y",
            });
        });
    </script>
</body>

</html>