4<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$success_msg = '';
$error_msg = '';

// Handle Form Submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo->beginTransaction();

        // Handle Status Change
        if (isset($_POST['ppdb_status'])) {
            $status = clean_input($_POST['ppdb_status']);
            $allowed_status = ['belum', 'buka', 'verifikasi', 'pengumuman_adm', 'cbt', 'finalisasi', 'pengumuman'];

            if (in_array($status, $allowed_status)) {
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('ppdb_status', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$status, $status]);

                // Auto-sync tahap_administrasi if needed
                if ($status == 'verifikasi') {
                    $stmt_sync = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('tahap_administrasi', 'verifikasi') ON DUPLICATE KEY UPDATE setting_value = 'verifikasi'");
                    $stmt_sync->execute();
                } else if ($status == 'pengumuman_adm') {
                    $stmt_sync = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('tahap_administrasi', 'pengumuman') ON DUPLICATE KEY UPDATE setting_value = 'pengumuman'");
                    $stmt_sync->execute();
                }

                // === AUTO-FINALISASI OLEH SISTEM ===
                // Jika status berpindah dari 'buka' ke tahap lain, finalisasi otomatis semua yang belum
                $prev_status = get_setting('ppdb_status', 'belum');
                if ($prev_status === 'buka' && $status !== 'buka') {
                    // Pastikan kolom finalisasi_oleh sudah ada
                    try {
                        $pdo->exec("ALTER TABLE pendaftar ADD COLUMN finalisasi_oleh ENUM('manual','sistem','admin') NULL DEFAULT NULL");
                    } catch (Exception $eAlter) { /* kolom sudah ada, abaikan */ }

                    // Finalisasi otomatis untuk semua yang belum finalisasi
                    $stmt_auto = $pdo->prepare(
                        "UPDATE pendaftar SET finalisasi = 'ya', finalisasi_oleh = 'sistem' 
                         WHERE (finalisasi = 'belum' OR finalisasi IS NULL)"
                    );
                    $stmt_auto->execute();
                    $auto_count = $stmt_auto->rowCount();

                    if ($auto_count > 0 && function_exists('log_activity')) {
                        log_activity("Finalisasi Otomatis", "Sistem memfinalisasi $auto_count pendaftar secara otomatis karena pendaftaran ditutup (status berubah ke '$status')");
                    }
                }
            }
        }

        // Handle Dates for all stages
        $stages = ['belum', 'buka', 'verifikasi', 'pengumuman_adm', 'cbt', 'finalisasi', 'pengumuman'];
        $stmt_date = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");

        foreach ($stages as $s) {
            $start_key = "stage_{$s}_start";
            $end_key = "stage_{$s}_end";

            if (isset($_POST[$start_key])) {
                $stmt_date->execute([$start_key, $_POST[$start_key], $_POST[$start_key]]);
            }
            if (isset($_POST[$end_key])) {
                $stmt_date->execute([$end_key, $_POST[$end_key], $_POST[$end_key]]);
            }
        }

        $pdo->commit();
        $success_msg = "Pengaturan tahapan dan jadwal berhasil diperbarui!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Force sync status before displaying (ensure UI is always up-to-date)
sync_ppdb_status();

// Get Current Status
$current_status = get_setting('ppdb_status', 'belum');

// UI Helpers
function getStatusCardClass($status, $current)
{
    return $status === $current ? 'border-primary ring-2 shadow-lg bg-primary-subtle' : 'border-0 shadow-sm bg-white';
}

function getStatusIcon($status)
{
    switch ($status) {
        case 'belum':
            return 'fa-clock';
        case 'buka':
            return 'fa-door-open';
        case 'verifikasi':
            return 'fa-user-check';
        case 'pengumuman_adm':
            return 'fa-clipboard-list';
        case 'cbt':
            return 'fa-laptop-code';
        case 'finalisasi':
            return 'fa-file-signature';
        case 'pengumuman':
            return 'fa-trophy';
        default:
            return 'fa-circle';
    }
}

function format_indo_date($date_string)
{
    if (empty($date_string)) return 'Belum diatur';

    $months = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];

    $timestamp = strtotime($date_string);
    if (!$timestamp) return $date_string;

    $d = date('d', $timestamp);
    $m = date('m', $timestamp);
    $y = date('Y', $timestamp);
    $t = date('H:i', $timestamp);

    return $d . ' ' . $months[$m] . ' ' . $y . ' - ' . $t;
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tahapan Pelaksanaan - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .btn-dark.d-lg-none {
            z-index: 1060 !important;
        }

        .status-card {
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent !important;
        }

        .status-card.active {
            border-color: var(--bs-primary) !important;
            background-color: var(--bs-primary-bg-subtle) !important;
        }

        .icon-circle {
            width: 50px;
            height: 50px;
            line-height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .date-input-group {
            background: rgba(0, 0, 0, 0.03);
            border-radius: 12px;
            padding: 10px;
            margin-top: 15px;
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
                <h2 class="text-primary fw-bold mb-0"><i class="fas fa-stream me-2"></i>Kontrol Tahapan PMBM</h2>
                <div class="badge bg-primary px-3 py-2 rounded-pill">Status Aktif:
                    <?= strtoupper(str_replace('_', ' ', $current_status)) ?>
                </div>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= $success_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="row g-4 mb-5">
                    <?php
                    $stages_data = [
                        'belum' => ['title' => 'Pendaftaran Belum Dibuka', 'icon' => 'fa-clock', 'color' => 'secondary', 'desc' => 'Masa persiapan pendaftaran PMBM.'],
                        'buka' => ['title' => 'Pendaftaran Dibuka', 'icon' => 'fa-edit', 'color' => 'success', 'desc' => 'Murid dapat mendaftar dan melengkapi berkas.'],
                        'verifikasi' => ['title' => 'Masa Verifikasi Data', 'icon' => 'fa-user-check', 'color' => 'warning', 'desc' => 'Pendaftaran tutup. Admin verifikasi berkas.'],
                        'pengumuman_adm' => ['title' => 'Hasil Verifikasi Berkas', 'icon' => 'fa-clipboard-list', 'color' => 'primary', 'desc' => 'Pengumuman kelulusan administrasi dibuka.'],
                        'cbt' => ['title' => 'Masa Test CBT', 'icon' => 'fa-laptop-code', 'color' => 'info', 'desc' => 'Pelaksanaan ujian CBT secara online/offline.'],
                        'finalisasi' => ['title' => 'Finalisasi Pendaftaran', 'icon' => 'fa-file-signature', 'color' => 'dark', 'desc' => 'Pengisian data akhir sebelum pengumuman final.'],
                        'pengumuman' => ['title' => 'Pengumuman Kelulusan', 'icon' => 'fa-trophy', 'color' => 'danger', 'desc' => 'Pengumuman final kelulusan murid baru.'],
                    ];

                    foreach ($stages_data as $key => $data):
                        $is_active = ($current_status == $key);

                        // Pull value from settings
                        $val_start = get_setting("stage_{$key}_start");
                        $val_end = get_setting("stage_{$key}_end");

                        // Special logic for 'Pendaftaran Belum Dibuka'
                        $is_belum = ($key == 'belum');
                        if ($is_belum) {
                            $active_scheme = get_setting('active_scheme', '1');
                            if ($active_scheme == '1') {
                                $val_end = get_setting('scheme_1_start') . ' ' . get_setting('scheme_daily_start');
                            } elseif ($active_scheme == '2') {
                                $val_end = get_setting('scheme_2_start') . ' ' . get_setting('scheme_2_start_time', '00:01');
                            } else {
                                $val_end = get_setting('scheme_period_start') . ' ' . get_setting('scheme_period_start_time', '00:01');
                            }
                        }

                        // Special logic for 'Pendaftaran Dibuka' (sync with active scheme)
                        $is_buka = ($key == 'buka');
                        if ($is_buka) {
                            $active_scheme = get_setting('active_scheme', '1');
                            if ($active_scheme == '1') {
                                $val_start = get_setting('scheme_1_start') . ' ' . get_setting('scheme_daily_start');
                                $val_end = get_setting('scheme_1_end') . ' ' . get_setting('scheme_daily_end');
                            } elseif ($active_scheme == '2') {
                                $val_start = get_setting('scheme_2_start') . ' ' . get_setting('scheme_2_start_time', '00:01');
                                $val_end = get_setting('scheme_2_end') . ' ' . get_setting('scheme_2_end_time', '23:59');
                            } else {
                                $val_start = get_setting('scheme_period_start') . ' ' . get_setting('scheme_period_start_time', '00:01');
                                $val_end = get_setting('scheme_period_end') . ' ' . get_setting('scheme_period_end_time', '23:59');
                            }
                        }

                        // Special logic for 'Masa Verifikasi Data'
                        $is_verifikasi = ($key == 'verifikasi');
                        if ($is_verifikasi) {
                            $active_scheme = get_setting('active_scheme', '1');
                            if ($active_scheme == '1') {
                                $val_start = get_setting('scheme_1_end') . ' ' . get_setting('scheme_daily_end');
                            } elseif ($active_scheme == '2') {
                                $val_start = get_setting('scheme_2_end') . ' ' . get_setting('scheme_2_end_time', '23:59');
                            } else {
                                $val_start = get_setting('scheme_period_end') . ' ' . get_setting('scheme_period_end_time', '23:59');
                            }
                        }

                        // Special logic for 'Hasil Verifikasi Berkas' (starts when verification ends)
                        $is_pengumuman_adm = ($key == 'pengumuman_adm');
                        if ($is_pengumuman_adm) {
                            $val_start = get_setting('stage_verifikasi_end', '');
                        }

                        // Special logic for 'Masa Test CBT' (starts when admin announcement ends)
                        $is_cbt = ($key == 'cbt');
                        if ($is_cbt) {
                            $val_start = get_setting('stage_pengumuman_adm_end', '');
                        }
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div
                                class="card h-100 rounded-4 status-card p-3 <?= $is_active ? 'active' : 'bg-white shadow-sm' ?>">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-circle bg-<?= $data['color'] ?> text-white me-3">
                                            <i class="fas <?= $data['icon'] ?>"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-0"><?= $data['title'] ?></h6>
                                            <small class="text-muted"><?= $data['desc'] ?></small>
                                        </div>
                                        <input type="radio" name="ppdb_status" value="<?= $key ?>"
                                            class="form-check-input ms-2" <?= $is_active ? 'checked' : '' ?>
                                            style="width: 22px; height: 22px;">
                                    </div>

                                    <div class="date-input-group">
                                        <?php if ($is_belum): ?>
                                            <div class="alert alert-secondary py-1 px-2 border-0 rounded-3 mb-2"
                                                style="font-size: 0.75rem;">
                                                <i class="fas fa-sync-alt me-1"></i> Terkoneksi dengan Jadwal Skema
                                            </div>
                                            <div class="col-12">
                                                <label class="small fw-bold text-muted mb-1">Target Buka Pendaftaran</label>
                                                <input type="text"
                                                    class="form-control form-control-sm bg-light border-0 fw-bold"
                                                    value="<?= format_indo_date($val_end) ?>" readonly>
                                            </div>
                                        <?php elseif ($is_buka): ?>
                                            <div class="alert alert-info py-1 px-2 border-0 rounded-3 mb-2"
                                                style="font-size: 0.75rem;">
                                                <i class="fas fa-sync-alt me-1"></i> Terkoneksi dengan Skema Aktif
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="small fw-bold text-muted mb-1">Mulai Tanggal & Jam</label>
                                                    <input type="text"
                                                        class="form-control form-control-sm bg-light border-0 fw-bold"
                                                        value="<?= format_indo_date($val_start) ?>" readonly>
                                                </div>
                                                <div class="col-6">
                                                    <label class="small fw-bold text-muted mb-1">Sampai Tanggal & Jam</label>
                                                    <input type="text"
                                                        class="form-control form-control-sm bg-light border-0 fw-bold"
                                                        value="<?= format_indo_date($val_end) ?>" readonly>
                                                </div>
                                            </div>
                                        <?php elseif ($is_verifikasi): ?>
                                            <div class="alert alert-warning py-1 px-2 border-0 rounded-3 mb-2"
                                                style="font-size: 0.75rem;">
                                                <i class="fas fa-sync-alt me-1"></i> Mulai otomatis setelah Pendaftaran Tutup
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="small fw-bold text-muted mb-1">Mulai Tanggal & Jam</label>
                                                    <input type="text"
                                                        class="form-control form-control-sm bg-light border-0 fw-bold"
                                                        value="<?= format_indo_date($val_start) ?>" readonly>
                                                </div>
                                                <div class="col-6">
                                                    <label class="small fw-bold text-muted mb-1">Sampai Tanggal & Jam</label>
                                                    <input type="text" name="stage_<?= $key ?>_end"
                                                        class="form-control form-control-sm datetimepicker"
                                                        value="<?= $val_end ?>" placeholder="Pilih Jadwal">
                                                </div>
                                            </div>
                                        <?php elseif ($is_pengumuman_adm): ?>
                                            <div class="alert alert-success py-1 px-2 border-0 rounded-3 mb-2"
                                                style="font-size: 0.75rem;">
                                                <i class="fas fa-sync-alt me-1"></i> Mulai otomatis setelah Verifikasi Selesai
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="small fw-bold text-muted mb-1">Mulai Tanggal & Jam</label>
                                                    <input type="text"
                                                        class="form-control form-control-sm bg-light border-0 fw-bold"
                                                        value="<?= format_indo_date($val_start) ?>" readonly>
                                                </div>
                                                <div class="col-6">
                                                    <label class="small fw-bold text-muted mb-1">Sampai Tanggal & Jam</label>
                                                    <input type="text" name="stage_<?= $key ?>_end"
                                                        class="form-control form-control-sm datetimepicker"
                                                        value="<?= $val_end ?>" placeholder="Pilih Jadwal">
                                                </div>
                                            </div>
                                        <?php elseif ($is_cbt): ?>
                                            <div class="alert alert-info py-1 px-2 border-0 rounded-3 mb-2"
                                                style="font-size: 0.75rem;">
                                                <i class="fas fa-sync-alt me-1"></i> Mulai otomatis setelah Hasil Admin Selesai
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="small fw-bold text-muted mb-1">Mulai Tanggal & Jam</label>
                                                    <input type="text"
                                                        class="form-control form-control-sm bg-light border-0 fw-bold"
                                                        value="<?= format_indo_date($val_start) ?>" readonly>
                                                </div>
                                                <div class="col-6">
                                                    <label class="small fw-bold text-muted mb-1">Sampai Tanggal & Jam</label>
                                                    <input type="text" name="stage_<?= $key ?>_end"
                                                        class="form-control form-control-sm datetimepicker"
                                                        value="<?= $val_end ?>" placeholder="Pilih Jadwal">
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="small fw-bold text-muted mb-1">Mulai Tanggal & Jam</label>
                                                    <input type="text" name="stage_<?= $key ?>_start"
                                                        class="form-control form-control-sm datetimepicker"
                                                        value="<?= $val_start ?>" placeholder="Pilih Jadwal">
                                                </div>
                                                <div class="col-6">
                                                    <label class="small fw-bold text-muted mb-1">Sampai Tanggal & Jam</label>
                                                    <input type="text" name="stage_<?= $key ?>_end"
                                                        class="form-control form-control-sm datetimepicker"
                                                        value="<?= $val_end ?>" placeholder="Pilih Jadwal">
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-save-fixed shadow-lg">
                    <i class="fas fa-save me-2"></i> Simpan Semua Perubahan
                </button>
            </form>

        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                flatpickr(".datetimepicker", {
                    locale: "id",
                    enableTime: true,
                    time_24hr: true,
                    altInput: true,
                    altFormat: "d F Y - H:i",
                    dateFormat: "Y-m-d H:i",
                });

                // Card click logic
                document.querySelectorAll('.status-card').forEach(card => {
                    card.addEventListener('click', function (e) {
                        // Prevent radio toggle if clicking inside inputs
                        if (e.target.tagName === 'INPUT' || e.target.classList.contains('flatpickr-day')) return;

                        const radio = this.querySelector('input[type="radio"]');
                        radio.checked = true;

                        // Update UI
                        document.querySelectorAll('.status-card').forEach(c => c.classList.remove('active'));
                        this.classList.add('active');
                    });
                });
            });
        </script>
</body>

</html>