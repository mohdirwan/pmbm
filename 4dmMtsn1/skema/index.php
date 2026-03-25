<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $allowed_keys = [
            'active_scheme',
            'scheme_daily_quota',
            'scheme_daily_start',
            'scheme_daily_end',
            'scheme_1_start',
            'scheme_1_end',
            'scheme_2_daily_quota',
            'scheme_2_start',
            'scheme_2_end',
            'scheme_2_start_time',
            'scheme_2_end_time',
            'scheme_total_quota',
            'scheme_period_start',
            'scheme_period_end',
            'scheme_period_start_time',
            'scheme_period_end_time'
        ];

        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");

        foreach ($allowed_keys as $key) {
            if (isset($_POST[$key])) {
                $val = clean_input($_POST[$key]);
                $stmt->execute([$key, $val, $val]);
            }
        }

        $pdo->commit();
        $success_msg = "Pengaturan skema berhasil disimpan!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Fetch current values
$active_scheme = get_setting('active_scheme', '1');
// Scheme 1
$scheme_daily_quota = get_setting('scheme_daily_quota', '150');
$scheme_daily_start = get_setting('scheme_daily_start', '08:00');
$scheme_daily_end = get_setting('scheme_daily_end', '16:00');
$scheme_1_start = get_setting('scheme_1_start', date('Y-m-d'));
$scheme_1_end = get_setting('scheme_1_end', date('Y-m-d', strtotime('+7 days')));
// Scheme 2
$scheme_2_daily_quota = get_setting('scheme_2_daily_quota', '150');
$scheme_2_start = get_setting('scheme_2_start', date('Y-m-d'));
$scheme_2_end = get_setting('scheme_2_end', date('Y-m-d', strtotime('+7 days')));
// Scheme 3
$scheme_total_quota = get_setting('scheme_total_quota', '500');
$scheme_period_start = get_setting('scheme_period_start', date('Y-m-d'));
$scheme_period_end = get_setting('scheme_period_end', date('Y-m-d', strtotime('+7 days')));

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Skema PMBM - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .btn-dark.d-lg-none {
            z-index: 1060 !important;
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4 text-primary fw-bold"><i class="fas fa-layer-group me-2"></i>Pengaturan Skema PMBM</h2>

            <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?= $success_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <!-- Section 1: Select Active Scheme -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-primary border-5">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-primary mb-3">Langkah 1: Pilih Skema Utama</h5>
                        <p class="text-muted mb-3">Tentukan skema mana yang sedang aktif digunakan untuk pendaftaran
                            saat ini.</p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label
                                    class="card-radio-btn p-3 border rounded-3 h-100 d-flex align-items-center cursor-pointer <?= $active_scheme == '1' ? 'bg-primary-subtle border-primary' : 'bg-light' ?>">
                                    <input type="radio" name="active_scheme" value="1" class="form-check-input me-2"
                                        <?= $active_scheme == '1' ? 'checked' : '' ?>>
                                    <div>
                                        <span class="fw-bold d-block">Skema 1</span>
                                        <small class="text-muted">Per Hari (Ada Jam)</small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label
                                    class="card-radio-btn p-3 border rounded-3 h-100 d-flex align-items-center cursor-pointer <?= $active_scheme == '2' ? 'bg-primary-subtle border-primary' : 'bg-light' ?>">
                                    <input type="radio" name="active_scheme" value="2" class="form-check-input me-2"
                                        <?= $active_scheme == '2' ? 'checked' : '' ?>>
                                    <div>
                                        <span class="fw-bold d-block">Skema 2</span>
                                        <small class="text-muted">Periode (24 Jam)</small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label
                                    class="card-radio-btn p-3 border rounded-3 h-100 d-flex align-items-center cursor-pointer <?= $active_scheme == '3' ? 'bg-primary-subtle border-primary' : 'bg-light' ?>">
                                    <input type="radio" name="active_scheme" value="3" class="form-check-input me-2"
                                        <?= $active_scheme == '3' ? 'checked' : '' ?>>
                                    <div>
                                        <span class="fw-bold d-block">Skema 3</span>
                                        <small class="text-muted">Per Periode (Total)</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Configuration -->
                <h5 class="fw-bold text-dark mb-3 ps-2">Langkah 2: Konfigurasi Detail Skema</h5>
                <div class="row g-4">
                    <!-- Scheme 1 Config -->
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h6 class="fw-bold text-primary">Detail Skema 1</h6>
                                <small class="text-muted">Kuota Per Hari (Terbatas Waktu)</small>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Kuota Harian</label>
                                    <input type="number" class="form-control" name="scheme_daily_quota"
                                        value="<?= $scheme_daily_quota ?>">
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Tanggal Mulai</label>
                                        <input type="text" class="form-control flatpickr-date" name="scheme_1_start"
                                            value="<?= $scheme_1_start ?>">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Tanggal Selesai</label>
                                        <input type="text" class="form-control flatpickr-date" name="scheme_1_end"
                                            value="<?= $scheme_1_end ?>">
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Jam Buka</label>
                                        <input type="text" class="form-control flatpickr-time" name="scheme_daily_start"
                                            value="<?= $scheme_daily_start ?>">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Jam Tutup</label>
                                        <input type="text" class="form-control flatpickr-time" name="scheme_daily_end"
                                            value="<?= $scheme_daily_end ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scheme 2 Config -->
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h6 class="fw-bold text-primary">Detail Skema 2</h6>
                                <small class="text-muted">Kuota Harian (24 Jam)</small>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Kuota Harian</label>
                                    <input type="number" class="form-control" name="scheme_2_daily_quota"
                                        value="<?= $scheme_2_daily_quota ?>">
                                    <div class="form-text small">Tiap hari kuota akan di-reset (Buka 24 Jam).</div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Tanggal Mulai</label>
                                        <input type="text" class="form-control flatpickr-date" name="scheme_2_start"
                                            value="<?= $scheme_2_start ?>">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Tanggal Selesai</label>
                                        <input type="text" class="form-control flatpickr-date" name="scheme_2_end"
                                            value="<?= $scheme_2_end ?>">
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Jam Buka</label>
                                        <input type="time" class="form-control" name="scheme_2_start_time"
                                            value="<?= get_setting('scheme_2_start_time', '00:01') ?>">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Jam Tutup</label>
                                        <input type="time" class="form-control" name="scheme_2_end_time"
                                            value="<?= get_setting('scheme_2_end_time', '23:59') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scheme 3 Config -->
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h6 class="fw-bold text-primary">Detail Skema 3</h6>
                                <small class="text-muted">Kuota Total (Periode)</small>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Total Kuota</label>
                                    <input type="number" class="form-control" name="scheme_total_quota"
                                        value="<?= $scheme_total_quota ?>">
                                    <div class="form-text small text-info"><i class="fas fa-info-circle me-1"></i>Buka
                                        24 jam selama kuota ada.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Tanggal Mulai</label>
                                    <input type="text" class="form-control flatpickr-date" name="scheme_period_start"
                                        value="<?= $scheme_period_start ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Tanggal Selesai</label>
                                    <input type="text" class="form-control flatpickr-date" name="scheme_period_end"
                                        value="<?= $scheme_period_end ?>">
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Jam Buka</label>
                                        <input type="time" class="form-control" name="scheme_period_start_time"
                                            value="<?= get_setting('scheme_period_start_time', '00:01') ?>">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Jam Tutup</label>
                                        <input type="time" class="form-control" name="scheme_period_end_time"
                                            value="<?= get_setting('scheme_period_end_time', '23:59') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mt-4 bg-white sticky-bottom">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle text-primary fa-2x me-3"></i>
                            <span class="text-muted">Skema yang dipilih akan langsung diterapkan pada sistem pendaftaran
                                user.</span>
                        </div>
                        <button type="submit" class="btn btn-premium px-5 rounded-pill shadow"><i
                                class="fas fa-save me-2"></i> Simpan Skema</button>
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
            flatpickr(".flatpickr-date", {
                locale: "id",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d F Y"
            });

            flatpickr(".flatpickr-time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                altInput: true,
                altFormat: "H:i"
            });

            // Card highlight logic
            document.querySelectorAll('input[name="active_scheme"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    // Force submit or visual only? Based on original it was visual.
                    // But card-radio-btn needs classes updated.
                    document.querySelectorAll('.card-radio-btn').forEach(card => {
                        card.classList.remove('bg-primary-subtle', 'border-primary');
                        card.classList.add('bg-light');
                    });
                    if (this.checked) {
                        this.closest('.card-radio-btn').classList.remove('bg-light');
                        this.closest('.card-radio-btn').classList.add('bg-primary-subtle', 'border-primary');
                    }
                });
            });
        });
    </script>
</body>

</html>