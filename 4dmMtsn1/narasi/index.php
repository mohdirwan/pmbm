<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$success_msg = '';
$error_msg = '';

// Handle Form Submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");

        foreach ($_POST['narasi'] as $key => $value) {
            $stmt->execute([$key, $value, $value]);
        }

        // Special handling for special_jalur_ids
        $special_ids = isset($_POST['special_jalur_ids']) ? implode(',', $_POST['special_jalur_ids']) : '';
        $stmt->execute(['narasi_special_jalur_ids', $special_ids, $special_ids]);

        // Special handling for general_jalur_ids
        $general_ids = isset($_POST['general_jalur_ids']) ? implode(',', $_POST['general_jalur_ids']) : '';
        $stmt->execute(['narasi_general_jalur_ids', $general_ids, $general_ids]);

        $pdo->commit();
        $success_msg = "Pengaturan narasi berhasil diperbarui!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Fetch Jalur List
$stmt_jalur = $pdo->query("SELECT id, nama_jalur FROM jalur_pendaftaran ORDER BY id ASC");
$all_jalur = $stmt_jalur->fetchAll();
$selected_special_ids = explode(',', get_setting('narasi_special_jalur_ids', ''));
$selected_general_ids = explode(',', get_setting('narasi_general_jalur_ids', ''));

// Default Values if not in DB
$default_narasi = [
    'narasi_pendaftaran_berhasil' => 'Selamat, pendaftaran Ananda di MTsN 1 Kota Pekanbaru melalui jalur … telah berhasil dan tercatat dalam sistem. Silakan menunggu informasi selanjutnya sesuai jadwal yang ditentukan.',
    'narasi_pendaftaran_tahfizh' => 'Selamat, pendaftaran Ananda di MTsN 1 Kota Pekanbaru melalui jalur tahfizh telah berhasil dan tercatat dalam sistem. Silakan mengikuti tes tahfizh pada hari Senin – Selasa, 09 – 10 Maret 2026 pukul 08.00 – 12.00 WIB di MTsN 1 Kota Pekanbaru.',
    'narasi_tahfizh_tidak_lolos' => 'Mohon Maaf, Ananda tidak lulus tes tahfizh. Selanjutnya Ananda dapat mengikuti tes akademik sesuai jadwal yang telah ditentukan.',
    'narasi_lulus_administrasi' => 'Selamat, Ananda dinyatakan lulus tahap administrasi pada proses Penerimaan Murid Baru di MTsN 1 Kota Pekanbaru. Silakan melanjutkan ke tahap berikutnya sesuai jadwal dan ketentuan yang telah ditetapkan.',
    'narasi_tidak_lulus_administrasi' => 'Mohon Maaf Ananda Tidak Lulus Seleksi Administrasi',
    'narasi_info_test_akademik' => 'Bagi Ananda yang lulus administrasi, silakan mengikuti tes akademik yang akan dilaksanakan pada hari Sabtu, 14 Maret 2026 pukul 08.00 – 11.00 WIB di MTsN 1 Kota Pekanbaru. Silakan bawa handphone (HP) atau tablet yang terhubung dengan jaringan internet untuk dapat mengikuti tes akademik.',
    'narasi_lulus_test_akademik' => 'Selamat, Ananda lulus tes akademik di MTsN 1 Kota Pekanbaru. Silakan melakukan daftar ulang sesuai jadwal yang telah ditentukan.',
    'narasi_tidak_lulus_test_akademik' => 'Mohon maaf, Ananda tidak lulus tes akademik di MTsN 1 Kota Pekanbaru.',
    'narasi_finalisasi' => 'Ananda telah melaksanakan tes akademik. Mohon kesediaannya untuk secara berkala mengecek jadwal pengumuman kelulusan.',
    'narasi_info_daftar_ulang' => 'Bagi Ananda yang lulus tes akademik, silakan melakukan daftar ulang pada hari Rabu – Jumat, 01 – 03 April 2026 pukul 08.00 – 15.00 WIB di MTsN 1 Kota Pekanbaru.'
];

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pengaturan Narasi & Keterangan - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .narasi-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .narasi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .narasi-label {
            font-weight: 700;
            color: #0b2c24;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .narasi-label i {
            width: 30px;
            height: 30px;
            background: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 0.9rem;
            color: #198754;
        }

        textarea.form-control {
            border-radius: 12px;
            border: 1px solid #dee2e6;
            padding: 15px;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        textarea.form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.1);
            border-color: #198754;
        }

        .btn-save-fixed {
            position: fixed;
            bottom: 30px;
            right: 50px;
            z-index: 1000;
            padding: 15px 40px;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid pe-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-primary fw-bold mb-0"><i class="fas fa-comment-dots me-2"></i>Pengaturan Narasi &
                        Keterangan</h2>
                    <p class="text-muted">Kelola pesan dan teks narasi yang muncul di halaman pendaftaran & dashboard
                        siswa.</p>
                </div>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $success_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="row">
                    <!-- Pendaftaran Section -->
                    <div class="col-12 mb-4">
                        <h5 class="fw-bold text-success border-bottom pb-2 mb-4">1. Narasi Pendaftaran Berhasil</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card narasi-card">
                                    <div class="card-body">
                                        <label class="narasi-label"><i>a</i> Narasi Umum</label>
                                        <textarea name="narasi[narasi_pendaftaran_berhasil]" class="form-control mb-3"
                                            rows="4"><?= get_setting('narasi_pendaftaran_berhasil', $default_narasi['narasi_pendaftaran_berhasil']) ?></textarea>
                                        
                                        <label class="fw-bold small text-muted mb-2">Pilih Jalur Yang Menggunakan Narasi Ini:</label>
                                        <div class="p-3 bg-light rounded-3 border-start border-success border-4">
                                            <?php foreach ($all_jalur as $j): ?>
                                                <div class="form-check small mb-1">
                                                    <input class="form-check-input" type="checkbox" name="general_jalur_ids[]" 
                                                           value="<?= $j['id'] ?>" id="jalur_gen_<?= $j['id'] ?>"
                                                           <?= in_array($j['id'], $selected_general_ids) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="jalur_gen_<?= $j['id'] ?>">
                                                        <?= $j['nama_jalur'] ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card narasi-card">
                                    <div class="card-body">
                                        <label class="narasi-label"><i>b</i> Narasi Khusus (Cth: Tahfizh)</label>
                                        <textarea name="narasi[narasi_pendaftaran_tahfizh]" class="form-control mb-3"
                                            rows="4"><?= get_setting('narasi_pendaftaran_tahfizh', $default_narasi['narasi_pendaftaran_tahfizh']) ?></textarea>
                                        
                                        <label class="fw-bold small text-muted mb-2">Pilih Jalur Yang Menggunakan Narasi Ini:</label>
                                        <div class="p-3 bg-light rounded-3 border-start border-warning border-4">
                                            <?php foreach ($all_jalur as $j): ?>
                                                <div class="form-check small mb-1">
                                                    <input class="form-check-input" type="checkbox" name="special_jalur_ids[]" 
                                                           value="<?= $j['id'] ?>" id="jalur_<?= $j['id'] ?>"
                                                           <?= in_array($j['id'], $selected_special_ids) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="jalur_<?= $j['id'] ?>">
                                                        <?= $j['nama_jalur'] ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seleksi Administrasi Section -->
                    <div class="col-12 mb-4">
                        <h5 class="fw-bold text-primary border-bottom pb-2 mb-4">2. Narasi Seleksi Administrasi</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card narasi-card">
                                    <div class="card-body">
                                        <label class="narasi-label"><i>d</i> Lulus Administrasi</label>
                                        <textarea name="narasi[narasi_lulus_administrasi]" class="form-control"
                                            rows="4"><?= get_setting('narasi_lulus_administrasi', $default_narasi['narasi_lulus_administrasi']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card narasi-card">
                                    <div class="card-body">
                                        <label class="narasi-label"><i>d</i> Tidak Lulus Administrasi</label>
                                        <textarea name="narasi[narasi_tidak_lulus_administrasi]" class="form-control"
                                            rows="4"><?= get_setting('narasi_tidak_lulus_administrasi', $default_narasi['narasi_tidak_lulus_administrasi']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card narasi-card">
                                    <div class="card-body">
                                        <label class="narasi-label"><i>c</i> Gagal Tes Tahfizh</label>
                                        <textarea name="narasi[narasi_tahfizh_tidak_lolos]" class="form-control"
                                            rows="4"><?= get_setting('narasi_tahfizh_tidak_lolos', $default_narasi['narasi_tahfizh_tidak_lolos']) ?></textarea>
                                        <small class="text-muted mt-2 d-block">Muncul untuk jalur tahfizh yang tidak
                                            lulus tes tahfizh namun lanjut tes akademik.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card narasi-card">
                                    <div class="card-body">
                                        <label class="narasi-label"><i>e</i> Info Tes Akademik</label>
                                        <textarea name="narasi[narasi_info_test_akademik]" class="form-control"
                                            rows="4"><?= get_setting('narasi_info_test_akademik', $default_narasi['narasi_info_test_akademik']) ?></textarea>
                                        <small class="text-muted mt-2 d-block">Info jadwal dan persiapan tes
                                            akademik.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tes Akademik Section -->
                    <div class="col-12 mb-4">
                        <h5 class="fw-bold text-danger border-bottom pb-2 mb-4">3. Narasi Hasil Tes & Daftar Ulang</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card narasi-card">
                                    <div class="card-body">
                                        <label class="narasi-label"><i>f</i> Lulus Tes Akademik</label>
                                        <textarea name="narasi[narasi_lulus_test_akademik]" class="form-control"
                                            rows="4"><?= get_setting('narasi_lulus_test_akademik', $default_narasi['narasi_lulus_test_akademik']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card narasi-card">
                                    <div class="card-body">
                                        <label class="narasi-label"><i>g</i> Tidak Lulus Tes Akademik</label>
                                        <textarea name="narasi[narasi_tidak_lulus_test_akademik]" class="form-control"
                                            rows="4"><?= get_setting('narasi_tidak_lulus_test_akademik', $default_narasi['narasi_tidak_lulus_test_akademik']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card narasi-card">
                                    <div class="card-body">
                                        <label class="narasi-label"><i>h</i> Narasi Masa Finalisasi</label>
                                        <textarea name="narasi[narasi_finalisasi]" class="form-control"
                                            rows="3"><?= get_setting('narasi_finalisasi', $default_narasi['narasi_finalisasi']) ?></textarea>
                                        <small class="text-muted mt-2 d-block">Pesan saat Dashboard berada di Tahap Finalisasi Pendaftaran.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card narasi-card">
                                    <div class="card-body">
                                        <label class="narasi-label"><i>h</i> Narasi Daftar Ulang</label>
                                        <textarea name="narasi[narasi_info_daftar_ulang]" class="form-control"
                                            rows="3"><?= get_setting('narasi_info_daftar_ulang', $default_narasi['narasi_info_daftar_ulang']) ?></textarea>
                                        <small class="text-muted mt-2 d-block">Informasi jadwal dan lokasi daftar ulang
                                            fisik.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-save-fixed shadow-lg">
                    <i class="fas fa-save me-2"></i> Simpan Narasi
                </button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>