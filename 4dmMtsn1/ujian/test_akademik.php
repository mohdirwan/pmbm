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
        
        $jumlah_sesi = (int)$_POST['test_jumlah_sesi'];
        for ($i = 1; $i <= $jumlah_sesi; $i++) {
            $settings["test_sesi_{$i}_mulai"] = $_POST["sesi_{$i}_mulai"] ?? '';
            $settings["test_sesi_{$i}_selesai"] = $_POST["sesi_{$i}_selesai"] ?? '';
        }

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

        // Ambil siswa terverifikasi
        $stmt = $pdo->query("SELECT id FROM pendaftar WHERE status = 'Terverifikasi' ORDER BY id ASC");
        $students = $stmt->fetchAll();

        $pdo->beginTransaction();
        $student_index = 0;
        $total_assigned = 0;

        foreach ($hari_list as $hari) {
            for ($sesi = 1; $sesi <= $jumlah_sesi; $sesi++) {
                // Ambil jam untuk sesi ini
                $jam_mulai = get_setting("test_sesi_{$sesi}_mulai", '');
                $jam_selesai = get_setting("test_sesi_{$sesi}_selesai", '');

                for ($k = 0; $k < $kapasitas; $k++) {
                    if ($student_index >= count($students)) break 3;
                    
                    $student_id = $students[$student_index]['id'];
                    $update = $pdo->prepare("UPDATE pendaftar SET test_hari = ?, test_sesi = ?, test_jam_mulai = ?, test_jam_selesai = ? WHERE id = ?");
                    $update->execute([$hari, "Sesi $sesi", $jam_mulai, $jam_selesai, $student_id]);
                    
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
    $pdo->exec("UPDATE pendaftar SET test_hari = NULL, test_sesi = NULL, test_jam_mulai = NULL, test_jam_selesai = NULL");
    $success_msg = "Semua jadwal ujian telah dikosongkan!";
}

// Ambil Data Pendaftar
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
        .sesi-input-box { background: #f0f7f4; border-radius: 12px; padding: 15px; margin-bottom: 10px; border-left: 4px solid #198754; }
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
                <div class="col-lg-5">
                    <div class="card card-premium p-4 mb-4">
                        <h5 class="fw-bold mb-4">Pengaturan Slot Ujian</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Daftar Hari (Pisahkan dengan koma)</label>
                                <input type="text" name="test_hari_list" class="form-control" value="<?= get_setting('test_hari_list') ?>" placeholder="Senin, Selasa, Rabu">
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Jumlah Sesi Per Hari</label>
                                    <input type="number" id="input_jumlah_sesi" name="test_jumlah_sesi" class="form-control" value="<?= get_setting('test_jumlah_sesi', 1) ?>" min="1" max="10">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Kapasitas Per Sesi</label>
                                    <input type="number" name="test_kapasitas_sesi" class="form-control" value="<?= get_setting('test_kapasitas_sesi', 40) ?>">
                                </div>
                            </div>

                            <!-- Dynamic Session Time Inputs -->
                            <div id="session_times_container" class="mb-4">
                                <!-- JS will inject inputs here -->
                            </div>

                            <button type="submit" name="save_settings" class="btn btn-success w-100 rounded-pill mb-3">Simpan Pengaturan</button>
                            <hr>
                            <button type="submit" name="generate_jadwal" class="btn btn-primary w-100 rounded-pill mb-2" onclick="return confirm('Sistem akan membagikan jadwal & jam ujian secara otomatis. Lanjutkan?')">
                                <i class="fas fa-sync me-2"></i>Generate Jadwal Otomatis
                            </button>
                            <button type="submit" name="reset_jadwal" class="btn btn-outline-danger w-100 rounded-pill btn-sm" onclick="return confirm('Hapus semua jadwal & jam siswa?')">Reset Semua Jadwal</button>
                        </form>
                    </div>

                    <div class="card card-premium p-4">
                        <h5 class="fw-bold mb-4">Aksi Cepat</h5>
                        <div class="row g-2">
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
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No. Reg / Nama</th>
                                        <th>Hari</th>
                                        <th>Sesi & Jam</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($list_pendaftar as $s): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold small text-success"><?= $s['no_pendaftaran'] ?></div>
                                            <div class="small"><?= $s['nama_lengkap'] ?></div>
                                        </td>
                                        <td>
                                            <?php if($s['test_hari']): ?>
                                                <span class="badge bg-info text-dark"><?= $s['test_hari'] ?></span>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($s['test_sesi']): ?>
                                                <div class="fw-bold small"><?= $s['test_sesi'] ?></div>
                                                <div class="text-muted" style="font-size: 11px;">
                                                    <i class="far fa-clock me-1"></i><?= $s['test_jam_mulai'] ?> - <?= $s['test_jam_selesai'] ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($list_pendaftar)): ?>
                                        <tr><td colspan="3" class="text-center py-4">Belum ada siswa terverifikasi.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputSesi = document.getElementById('input_jumlah_sesi');
        const container = document.getElementById('session_times_container');

        // Existing data from PHP
        const sessionData = {
            <?php 
            $jml = (int)get_setting('test_jumlah_sesi', 1);
            for($i=1; $i<=$jml; $i++) {
                echo "sesi_{$i}_mulai: '" . get_setting("test_sesi_{$i}_mulai") . "',";
                echo "sesi_{$i}_selesai: '" . get_setting("test_sesi_{$i}_selesai") . "',";
            }
            ?>
        };

        function renderSessionInputs() {
            const count = parseInt(inputSesi.value) || 0;
            container.innerHTML = '<label class="form-label small fw-bold mb-2">Atur Jam Per Sesi:</label>';
            
            for (let i = 1; i <= count; i++) {
                const valMulai = sessionData[`sesi_${i}_mulai`] || '';
                const valSelesai = sessionData[`sesi_${i}_selesai`] || '';
                
                const html = `
                    <div class="sesi-input-box">
                        <div class="small fw-bold text-success mb-2">Sesi ${i}</div>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="time" name="sesi_${i}_mulai" class="form-control form-control-sm" value="${valMulai}" placeholder="Mulai">
                            </div>
                            <div class="col-6">
                                <input type="time" name="sesi_${i}_selesai" class="form-control form-control-sm" value="${valSelesai}" placeholder="Selesai">
                            </div>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', html);
            }
        }

        inputSesi.addEventListener('change', renderSessionInputs);
        renderSessionInputs(); // Initial render
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
