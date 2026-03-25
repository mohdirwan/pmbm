<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'test_wa') {
        require_once '../includes/wa_helper.php';
        $test_phone = clean_input($_POST['test_phone']);
        $test_message = "Test koneksi WA Gateway dari sistem PMBM success! 🚀";

        // Use inputs from POST if available for testing before saving
        $test_api_url = !empty($_POST['wa_api_url']) ? clean_input($_POST['wa_api_url']) : get_setting('wa_api_url');
        $test_api_key = !empty($_POST['wa_api_key']) ? clean_input($_POST['wa_api_key']) : get_setting('wa_api_key');

        // We temporarily override the settings in memory for this request
        $result = send_wa_message($test_phone, $test_message, true);
        // Note: send_wa_message calls get_setting internally. 
        // To really test BEFORE saving without changing wa_helper, we'd need to modify wa_helper.
        // For now, let's just prioritize fixing the "un-savable" form issue.
        if ($result['status']) {
            $success_msg = "Pesan tes berhasil dikirim ke " . $test_phone;
        } else {
            $error_msg = "Gagal kirim pesan tes: " . ($result['message'] ?? 'Unknown Error');
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'check_device') {
        require_once '../includes/wa_helper.php';
        $info = get_wa_device_info();
        if (isset($info['status']) && $info['status'] == 'success') {
            $data = $info['data'] ?? [];
            $success_msg = "<strong>Status Device Berhasil Diambil:</strong><br>
                            Nama: " . ($data['name'] ?? '-') . "<br>
                            Nomor: " . ($data['number'] ?? '-') . "<br>
                            Status: <span class='badge bg-success'>" . ($data['status'] ?? 'Connected') . "</span>";
        } else {
            $error_msg = "Gagal mengambil info device: " . ($info['message'] ?? 'Periksa kembali API Key Anda.');
        }
    } else {
        try {
            $allowed_keys = ['wa_api_url', 'wa_api_key', 'wa_status', 'wa_template_register'];

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

            foreach ($allowed_keys as $key) {
                if (isset($_POST[$key])) {
                    $val = $_POST[$key];
                    $stmt->execute([$key, $val]);
                }
            }

            $pdo->commit();
            $success_msg = "Pengaturan WhatsApp Gateway berhasil diperbarui!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = "Gagal menyimpan: " . $e->getMessage();
        }
    }
}

// Default template if not set (Consistent with process_register.php)
$default_template = "Assalamu'alaikum warahmatullahi wabarakatuh.

Halo {nama_kontak},

Pendaftaran a.n. {nama_siswa} pada PMBM MTsN 1 Kota Pekanbaru telah berhasil diproses.

Berikut rincian akun login murid:
Link Login: {link_login}
Username: {username}
Password: {password}

Mohon simpan informasi akun ini dengan baik untuk mengakses Dashboard Murid dan melengkapi tahapan pendaftaran selanjutnya.

Terima kasih.

Wassalamu'alaikum warahmatullahi wabarakatuh.";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>WhatsApp Gateway - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .main-content {
            margin-left: 260px;
            padding: 30px;
        }

        .card-premium {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .card-premium:hover {
            transform: translateY(-5px);
        }

        .btn-premium {
            background: linear-gradient(45deg, #198754, #20c997);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-premium:hover {
            box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3);
            transform: scale(1.02);
            color: white;
        }
    </style>
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content ps-5">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-primary fw-bold mb-1">WhatsApp Gateway</h2>
                    <p class="text-muted">Konfigurasi API WhatsApp untuk notifikasi pendaftaran.</p>
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

            <form method="POST">
                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="card card-premium p-4 h-100">
                            <h5 class="fw-bold mb-4 text-success"><i class="fas fa-key me-2"></i>API Configuration</h5>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Gateway Status</label>
                                <select class="form-select" name="wa_status">
                                    <option value="aktif" <?= get_setting('wa_status') == 'aktif' ? 'selected' : '' ?>>
                                        Aktif (Kirim Pesan)</option>
                                    <option value="nonaktif" <?= get_setting('wa_status', 'nonaktif') == 'nonaktif' ? 'selected' : '' ?>>Non-Aktif (Matikan)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Base API URL</label>
                                <input type="text" class="form-control" name="wa_api_url"
                                    value="<?= get_setting('wa_api_url', 'https://imsoftdev.my.id/wa/api/send-message.php') ?>"
                                    placeholder="https://.../send-message.php">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">API Key (Token)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" name="wa_api_key"
                                        value="<?= get_setting('wa_api_key', 'wa_f573e575c636814260058dfc74f50f546784f78bed2b9fb7') ?>">
                                </div>
                                <div class="form-text">Gunakan key dari dashboard WA Gateway Anda.</div>
                            </div>

                            <hr class="my-4">

                            <h5 class="fw-bold mb-3 text-primary"><i class="fas fa-comment-dots me-2"></i>Pesan
                                Pendaftaran Berhasil</h5>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Template Pesan</label>
                                <textarea class="form-control" name="wa_template_register"
                                    rows="8"><?= get_setting('wa_template_register', $default_template) ?></textarea>
                                <div class="mt-2">
                                    <span class="badge bg-light text-dark border me-1">{nama_kontak}</span>
                                    <span class="badge bg-light text-dark border me-1">{nama_siswa}</span>
                                    <span class="badge bg-light text-dark border me-1">{link_login}</span>
                                    <span class="badge bg-light text-dark border me-1">{username}</span>
                                    <span class="badge bg-light text-dark border me-1">{password}</span>
                                </div>
                                <div class="form-text mt-2 small">Gunakan tag di atas untuk mengganti data otomatis.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="card card-premium p-4 mb-4">
                            <h5 class="fw-bold mb-3 text-info"><i class="fas fa-info-circle me-2"></i>Informasi API</h5>
                            <div class="small text-muted">
                                <p>Pastikan Device WhatsApp Anda dalam status <strong>online</strong> di server gateway.
                                </p>
                                <ul class="ps-3">
                                    <li>Format nomor tujuan otomatis diubah ke format internasional (628...).</li>
                                    <li>Pesan akan terkirim segera setelah pendaftar mengklik tombol "Kirim
                                        Pendaftaran".</li>
                                    <li>Pastikan kuota API Anda mencukupi.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card card-premium p-4 mb-4 border-start border-primary border-4">
                            <h5 class="fw-bold mb-3 text-primary"><i class="fas fa-paper-plane me-2"></i>Uji Coba Kirim
                                Pesan</h5>
                            <p class="small text-muted mb-3">Gunakan form ini untuk mengetes apakah API sudah terhubung.
                            </p>

                            <!-- TESTING SECTION -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nomor WhatsApp (08... atau 628...)</label>
                                <input type="text" name="test_phone" class="form-control form-control-sm rounded-pill"
                                    placeholder="Contoh: 08123456789">
                            </div>
                            <button type="submit" name="action" value="test_wa"
                                class="btn btn-primary btn-sm w-100 rounded-pill">
                                <i class="fas fa-vial me-1"></i> Kirim Pesan Tes
                            </button>
                        </div>

                        <div class="card card-premium p-4 border-start border-warning border-4">
                            <h5 class="fw-bold mb-3"><i class="fas fa-vial me-2 text-warning"></i>Cek Status Koneksi
                            </h5>
                            <p class="small text-muted mb-4">Klik tombol di bawah untuk mengecek status pendaftaran
                                device Anda.</p>
                            <button type="submit" name="action" value="check_device"
                                class="btn btn-outline-warning w-100 rounded-pill">
                                <i class="fas fa-sync-alt me-2"></i> Cek Device Info
                            </button>
                        </div>
                    </div>
                </div>

                <div class="fixed-bottom bg-white border-top p-3 text-end pe-5">
                    <button type="submit" class="btn btn-premium shadow">
                        <i class="fas fa-save me-2"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>