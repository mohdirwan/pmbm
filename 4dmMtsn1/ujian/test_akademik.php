<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$success_msg = '';
$error_msg = '';

// Fetch Actual Rooms & Sessions for info and generation
$db_rooms = $pdo->query("SELECT * FROM ujian_ruangan ORDER BY id ASC")->fetchAll();
$db_sessions = $pdo->query("SELECT * FROM ujian_sesi ORDER BY waktu_mulai ASC")->fetchAll();

$total_kapasitas_per_sesi = 0;
foreach ($db_rooms as $r) { $total_kapasitas_per_sesi += (int)$r['kapasitas_pc']; }
$jumlah_sesi = count($db_sessions);

// Ensure password_cbt column exists
try {
    $pdo->query("SELECT password_cbt FROM pendaftar LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("ALTER TABLE pendaftar ADD COLUMN password_cbt VARCHAR(50) NULL AFTER password_plain");
}

// 1. Simpan Pengaturan Test
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_settings'])) {
    try {
        $settings = [
            'test_hari_list' => $_POST['test_hari_list']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($settings as $key => $val) {
            $stmt->execute([$key, $val]);
        }
        $success_msg = "Pengaturan berhasil disimpan!";
    } catch (Exception $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

// 2. Generate Jadwal Otomatis
if (isset($_POST['generate_jadwal'])) {
    try {
        $hari_raw = get_setting('test_hari_list', '');
        $hari_list = array_map('trim', explode(',', $hari_raw));
        
        if (empty($db_rooms)) throw new Exception("Belum ada data ruangan di Pengaturan Ruangan!");
        if (empty($db_sessions)) throw new Exception("Belum ada data sesi di Pengaturan Sesi!");
        if (empty($hari_raw)) throw new Exception("Daftar hari belum diatur!");

        // Ambil siswa terverifikasi yang belum punya jadwal
        $stmt = $pdo->query("SELECT id FROM pendaftar WHERE status = 'Terverifikasi' ORDER BY id ASC");
        $students = $stmt->fetchAll();

        $pdo->beginTransaction();
        $student_index = 0;
        $total_assigned = 0;

        foreach ($hari_list as $hari) {
            foreach ($db_sessions as $s) {
                $nama_sesi = $s['nama_sesi'];
                $jam_mulai = $s['waktu_mulai'];
                $jam_selesai = $s['waktu_selesai'];

                // Distribusi ke tiap ruangan
                foreach ($db_rooms as $room) {
                    $kapasitas_ruang = (int)$room['kapasitas_pc'];
                    $nama_ruang = $room['nama_ruangan'];

                    for ($k = 0; $k < $kapasitas_ruang; $k++) {
                        if ($student_index >= count($students)) break 3;
                        
                        $student_id = $students[$student_index]['id'];
                        $update = $pdo->prepare("UPDATE pendaftar SET test_hari = ?, test_sesi = ?, test_jam_mulai = ?, test_jam_selesai = ?, test_ruangan = ? WHERE id = ?");
                        $update->execute([$hari, $nama_sesi, $jam_mulai, $jam_selesai, $nama_ruang, $student_id]);
                        
                        $student_index++;
                        $total_assigned++;
                    }
                }
            }
        }
        $pdo->commit();
        $success_msg = "Berhasil membuat jadwal untuk $total_assigned siswa!";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error_msg = "Gagal generate: " . $e->getMessage();
    }
}

// 3. Reset Jadwal
if (isset($_POST['reset_jadwal'])) {
    $pdo->exec("UPDATE pendaftar SET test_hari = NULL, test_sesi = NULL, test_jam_mulai = NULL, test_jam_selesai = NULL, test_ruangan = NULL");
    $success_msg = "Semua jadwal ujian telah dikosongkan!";
}

// 4. Generate Password CBT
if (isset($_POST['generate_password'])) {
    try {
        $pdo->exec("UPDATE pendaftar SET password_cbt = CONCAT(YEAR(tanggal_lahir), nisn) WHERE status = 'Terverifikasi'");
        $success_msg = "Berhasil membuat password ujian untuk semua siswa terverifikasi!";
    } catch (Exception $e) {
        $error_msg = "Gagal generate password: " . $e->getMessage();
    }
}

// Ambil Data Pendaftar
$stmt = $pdo->query("SELECT * FROM pendaftar WHERE status = 'Terverifikasi' ORDER BY test_hari ASC, test_sesi ASC, id ASC");
$list_pendaftar = $stmt->fetchAll();

function format_indo_date($date) {
    if (!$date || $date == '-') return '-';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return $date;

    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $time = strtotime($date);
    $day_name = $days[date('w', $time)];
    $day = date('j', $time);
    $month_name = $months[(int)date('m', $time)];
    $year = date('Y', $time);
    
    return "$day_name, $day $month_name $year";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Test Akademik - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .main-content { margin-left: 260px; padding: 30px; background: #f8f9fa; min-height: 100vh; }
        .card-premium { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; }
        .info-label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 5px; }
        .info-value { font-size: 1.1rem; font-weight: 700; color: #1e293b; }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <h2 class="fw-bold mb-4"><i class="fas fa-microchip me-2 text-success"></i>Data Test Akademik</h2>

            <?php if($success_msg): ?>
                <div class="alert alert-success rounded-4 border-0 shadow-sm"><i class="fas fa-check-circle me-2"></i><?= $success_msg ?></div>
            <?php endif; ?>
            <?php if($error_msg): ?>
                <div class="alert alert-danger rounded-4 border-0 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i><?= $error_msg ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card card-premium p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Pengaturan Slot Ujian</h5>
                            <a href="ruangan.php" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-cog me-1"></i> Atur Ruangan</a>
                        </div>
                        
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Daftar Tanggal Ujian</label>
                                <input type="text" name="test_hari_list" id="test_hari_list" class="form-control rounded-3 bg-white" style="cursor: pointer;" value="<?= get_setting('test_hari_list') ?>" placeholder="Klik untuk pilih tanggal..." readonly>
                                <small class="text-muted">Pilih satu atau lebih tanggal pelaksanaan ujian.</small>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="info-box">
                                        <div class="info-label">Jumlah Sesi</div>
                                        <div class="info-value"><?= $jumlah_sesi ?> <span class="fw-normal text-muted small">Sesi</span></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box">
                                        <div class="info-label">Kapasitas Per Sesi</div>
                                        <div class="info-value"><?= $total_kapasitas_per_sesi ?> <span class="fw-normal text-muted small">Siswa</span></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-info py-2 small rounded-3 mb-0">
                                        <i class="fas fa-info-circle me-1"></i> Informasi sesi dan kapasitas di atas diambil otomatis dari data di <strong>Pengaturan Ruangan</strong>.
                                    </div>
                                </div>
                            </div>

                            <button type="submit" name="save_settings" class="btn btn-success w-100 rounded-pill mb-3 fw-bold py-2">
                                Simpan Daftar Hari
                            </button>
                            <hr>
                            <button type="submit" name="generate_jadwal" class="btn btn-primary w-100 rounded-pill mb-2 fw-bold py-2" onclick="return confirm('Sistem akan membagikan jadwal & jam ujian secara otomatis sesuai kapasitas labor. Lanjutkan?')">
                                <i class="fas fa-sync me-2"></i>Generate Jadwal Otomatis
                            </button>
                            <button type="submit" name="reset_jadwal" class="btn btn-outline-danger w-100 rounded-pill btn-sm" onclick="return confirm('Hapus semua jadwal & jam siswa?')">Reset Semua Jadwal</button>
                        </form>
                    </div>

                    <div class="card card-premium p-4">
                        <h5 class="fw-bold mb-4">Aksi Cepat</h5>
                        <div class="row g-2">
                            <div class="col-6">
                                <form method="POST">
                                    <button type="submit" name="generate_password" class="btn btn-outline-warning w-100 rounded-pill btn-sm" onclick="return confirm('Sistem akan membuat password ujian otomatis (Tahun Lahir + NISN). Lanjutkan?')">
                                        <i class="fas fa-key me-2"></i>Generate Pass
                                    </button>
                                </form>
                            </div>
                            <div class="col-6">
                                <a href="export_test.php" class="btn btn-outline-success w-100 rounded-pill btn-sm">
                                    <i class="fas fa-file-excel me-2"></i>Export Excel
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="print_kartu_massal.php" target="_blank" class="btn btn-outline-primary w-100 rounded-pill btn-sm">
                                    <i class="fas fa-print me-2"></i>Cetak Kartu
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card card-premium p-4">
                        <h5 class="fw-bold mb-4">Daftar Peserta & Jam Ujian</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>No. Reg / Nama</th>
                                        <th>JK</th>
                                        <th>Hari / Lokasi</th>
                                        <th>Sesi & Pass CBT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($list_pendaftar as $s): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold small text-success"><?= $s['no_pendaftaran'] ?></div>
                                            <div class="small fw-semibold"><?= $s['nama_lengkap'] ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border small">
                                                <?= $s['jenis_kelamin'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($s['test_hari']): ?>
                                                <div class="small fw-bold text-dark"><?= format_indo_date($s['test_hari']) ?></div>
                                                <div class="small text-secondary"><?= $s['test_ruangan'] ?></div>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($s['test_sesi']): ?>
                                                <div class="fw-bold small text-primary"><?= $s['test_sesi'] ?></div>
                                                <div class="fw-bold text-danger" style="font-size: 11px;">
                                                    <i class="fas fa-lock me-1"></i>PW: <?= $s['password_cbt'] ?: 'Belum Ada' ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($list_pendaftar)): ?>
                                        <tr><td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fas fa-users-slash d-block mb-2 fa-2x opacity-25"></i>
                                            Belum ada siswa terverifikasi.
                                        </td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#test_hari_list", {
                mode: "multiple",
                dateFormat: "Y-m-d",
                conjunction: ", "
            });
        });
    </script>
</body>
</html>
