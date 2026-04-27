<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$success_msg = '';
$error_msg = '';

// 1. Simpan Pengaturan Test
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_settings'])) {
    try {
        $settings = [
            'test_hari_list' => $_POST['test_hari_list'],
            'test_jumlah_sesi' => $_POST['test_jumlah_sesi'],
            'test_kapasitas_sesi' => $_POST['test_kapasitas_sesi']
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
        $jumlah_sesi = (int)get_setting('test_jumlah_sesi', 1);
        $kapasitas = (int)get_setting('test_kapasitas_sesi', 40);

        if (empty($hari_raw)) throw new Exception("Daftar hari belum diatur!");

        // Ambil siswa terverifikasi yang belum punya jadwal (atau semua terverifikasi jika ingin reset)
        $stmt = $pdo->query("SELECT id FROM pendaftar WHERE status = 'Terverifikasi' ORDER BY id ASC");
        $students = $stmt->fetchAll();

        $pdo->beginTransaction();
        $student_index = 0;
        $total_assigned = 0;

        foreach ($hari_list as $hari) {
            for ($sesi = 1; $sesi <= $jumlah_sesi; $sesi++) {
                for ($k = 0; $k < $kapasitas; $k++) {
                    if ($student_index >= count($students)) break 3;
                    
                    $student_id = $students[$student_index]['id'];
                    $update = $pdo->prepare("UPDATE pendaftar SET test_hari = ?, test_sesi = ? WHERE id = ?");
                    $update->execute([$hari, "Sesi $sesi", $student_id]);
                    
                    $student_index++;
                    $total_assigned++;
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
    $pdo->exec("UPDATE pendaftar SET test_hari = NULL, test_sesi = NULL");
    $success_msg = "Semua jadwal ujian telah dikosongkan!";
}

// Ambil Data Pendaftar dengan Jadwal
$stmt = $pdo->query("SELECT * FROM pendaftar WHERE status = 'Terverifikasi' ORDER BY test_hari ASC, test_sesi ASC, id ASC");
$list_pendaftar = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Test Akademik - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .main-content { margin-left: 260px; padding: 30px; background: #f8f9fa; min-height: 100vh; }
        .card-premium { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <h2 class="fw-bold mb-4"><i class="fas fa-microchip me-2 text-success"></i>Data Test Akademik</h2>

            <?php if($success_msg): ?>
                <div class="alert alert-success rounded-4 border-0 shadow-sm"><?= $success_msg ?></div>
            <?php endif; ?>
            <?php if($error_msg): ?>
                <div class="alert alert-danger rounded-4 border-0 shadow-sm"><?= $error_msg ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Pengaturan -->
                <div class="col-lg-4">
                    <div class="card card-premium p-4 mb-4">
                        <h5 class="fw-bold mb-4">Pengaturan Slot Ujian</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Daftar Hari (Pisahkan dengan koma)</label>
                                <input type="text" name="test_hari_list" class="form-control" value="<?= get_setting('test_hari_list') ?>" placeholder="Senin, Selasa, Rabu">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Jumlah Sesi Per Hari</label>
                                <input type="number" name="test_jumlah_sesi" class="form-control" value="<?= get_setting('test_jumlah_sesi') ?>">
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Kapasitas Murid Per Sesi</label>
                                <input type="number" name="test_kapasitas_sesi" class="form-control" value="<?= get_setting('test_kapasitas_sesi') ?>">
                            </div>
                            <button type="submit" name="save_settings" class="btn btn-success w-100 rounded-pill mb-3">Simpan Pengaturan</button>
                            <hr>
                            <button type="submit" name="generate_jadwal" class="btn btn-primary w-100 rounded-pill mb-2" onclick="return confirm('Sistem akan membagikan jadwal ke seluruh siswa terverifikasi secara otomatis. Lanjutkan?')">
                                <i class="fas fa-sync me-2"></i>Generate Jadwal Otomatis
                            </button>
                            <button type="submit" name="reset_jadwal" class="btn btn-outline-danger w-100 rounded-pill btn-sm" onclick="return confirm('Hapus semua jadwal siswa?')">Reset Semua Jadwal</button>
                        </form>
                    </div>

                    <div class="card card-premium p-4">
                        <h5 class="fw-bold mb-4">Aksi Cepat</h5>
                        <a href="export_test.php" class="btn btn-outline-success w-100 rounded-pill mb-3">
                            <i class="fas fa-file-excel me-2"></i>Export Excel Data Test
                        </a>
                        <a href="print_kartu_massal.php" target="_blank" class="btn btn-outline-primary w-100 rounded-pill">
                            <i class="fas fa-print me-2"></i>Print Semua Kartu Ujian
                        </a>
                    </div>
                </div>

                <!-- Tabel Data -->
                <div class="col-lg-8">
                    <div class="card card-premium p-4">
                        <h5 class="fw-bold mb-4">Daftar Peserta & Jadwal Ujian</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No. Reg</th>
                                        <th>Nama Murid</th>
                                        <th>Jadwal Hari</th>
                                        <th>Sesi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($list_pendaftar as $s): ?>
                                    <tr>
                                        <td class="small fw-bold"><?= $s['no_pendaftaran'] ?></td>
                                        <td><?= $s['nama_lengkap'] ?></td>
                                        <td>
                                            <?php if($s['test_hari']): ?>
                                                <span class="badge bg-info text-dark"><?= $s['test_hari'] ?></span>
                                            <?php else: ?>
                                                <span class="text-muted small">Belum diatur</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $s['test_sesi'] ?? '-' ?></td>
                                        <td>
                                            <span class="badge bg-success">Terverifikasi</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($list_pendaftar)): ?>
                                        <tr><td colspan="5" class="text-center py-4">Belum ada siswa terverifikasi.</td></tr>
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
</body>
</html>
