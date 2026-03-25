<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/wa_helper.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit();
}

$success_msg = "";
$error_msg = "";

// 1. Fetch Student Data
$stmt = $pdo->prepare("SELECT p.*, j.nama_jalur, j.syarat FROM pendaftar p LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id WHERE p.id = ?");
$stmt->execute([$id]);
$siswa = $stmt->fetch();

if (!$siswa) {
    die("Data tidak ditemukan!");
}

// 2. Handle Logic Updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 2a. Handle Tahfidz Status Update
    if (isset($_POST['action']) && $_POST['action'] == 'update_tahfidz') {
        $status_tahfidz = clean_input($_POST['status_tahfidz'] ?? 'Pending');
        try {
            // Update Status Tahfidz
            $stmt = $pdo->prepare("UPDATE pendaftar SET status_tahfidz = ? WHERE id = ?");
            $stmt->execute([$status_tahfidz, $id]);

            // Send WhatsApp notification if status is "Tidak Lulus"
            if ($status_tahfidz == 'Tidak Lulus') {
                $pesan_wa = "Kepada Yth. Orang tua/Wali dari:\n\n";
                $pesan_wa .= "Nama: " . $siswa['nama_lengkap'] . "\n";
                $pesan_wa .= "NISN: " . $siswa['nisn'] . "\n\n";
                $pesan_wa .= "Mohon Maaf, Ananda tidak lulus tes tahfizh. Selanjutnya Ananda dapat mengikuti tes akademik sesuai jadwal yang telah ditentukan.\n\n";
                $pesan_wa .= "Terima kasih atas perhatiannya.\n";
                $pesan_wa .= "Panitia PMBM MTsN 1 Kota Pekanbaru";

                send_wa_message($siswa['no_hp_ayah'], $pesan_wa);
            }

            // Record Log
            log_activity("Update Status Tahfidz", "Murid: {$siswa['no_pendaftaran']}, Status: $status_tahfidz");

            $success_msg = "Status Tes Tahfidz berhasil diperbarui menjadi $status_tahfidz.";

            // Refresh student data
            $stmt = $pdo->prepare("SELECT p.*, j.nama_jalur FROM pendaftar p LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id WHERE p.id = ?");
            $stmt->execute([$id]);
            $siswa = $stmt->fetch();
        } catch (Exception $e) {
            $error_msg = "Gagal memperbarui status tahfidz: " . $e->getMessage();
        }
    }

    // 2b. Handle Verification Logic
    if (isset($_POST['action']) && ($_POST['action'] == 'verifikasi' || $_POST['action'] == 'tolak')) {
        $action = $_POST['action'];
        $status = ($action == 'verifikasi') ? 'Terverifikasi' : 'Ditolak';
        $catatan = clean_input($_POST['catatan'] ?? '');

        try {
            $stmt = $pdo->prepare("UPDATE pendaftar SET status = ?, catatan_admin = ? WHERE id = ?");
            $stmt->execute([$status, $catatan, $id]);

            // Record Log
            log_activity("Verifikasi Pendaftar", "Murid: {$siswa['no_pendaftaran']}, Action: $action ($status)");

            // Send WhatsApp Notification (DISABLED AS REQUESTED)
            // if ($status == 'Terverifikasi') {
            //     $pesan = "Halo " . $siswa['nama_lengkap'] . ",\n\nSelamat! Berkas pendaftaran Anda telah *DIVERIFIKASI*. Silakan cetak bukti verifikasi dan pantau terus dashboard Anda.\n\nReg: " . $siswa['no_pendaftaran'];
            // } else {
            //     $pesan = "Halo " . $siswa['nama_lengkap'] . ",\n\nMohon maaf, berkas pendaftaran Anda *DITOLAK*.\nAlasan: " . $catatan . "\n\nSilakan perbaiki data Anda di dashboard siswa.";
            // }
            // send_wa_message($siswa['no_hp_ayah'], $pesan);

            // Redirect back to list with success message
            header("Location: index.php?msg=" . urlencode("Status pendaftaran berhasil diubah menjadi $status"));
            exit();
        } catch (Exception $e) {
            $error_msg = "Gagal memperbarui status: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail Pendaftar -
        <?= $siswa['nama_lengkap'] ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .main-content {
            margin-left: 260px;
            padding: 30px;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .card-detail {
            border: none;
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 2px;
        }

        .info-value {
            font-weight: 600;
            color: #33475b;
        }

        .section-title {
            border-bottom: 2px solid #eef0f2;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #0b2c24;
            font-weight: 800;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 100px;
            font-weight: 700;
        }
    </style>
</head>

<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Back Button -->
            <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-pill px-4 mb-4">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke List
            </a>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1 text-primary">
                        <?= $siswa['nama_lengkap'] ?>
                    </h2>
                    <p class="text-muted small mb-1"><i class="fas fa-id-card me-2"></i> No Pendaftaran: <span
                            class="fw-bold">
                            <?= $siswa['no_pendaftaran'] ?>
                        </span></p>
                    <p class="text-muted small mb-0"><i class="fas fa-route me-2"></i> Jalur: <span
                            class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-3 fw-bold">
                            <?= htmlspecialchars($siswa['nama_jalur'] ?? 'N/A') ?>
                        </span></p>
                </div>
                <div>
                    <?php
                    $statusColor = match ($siswa['status']) {
                        'Diterima' => 'success',
                        'Terverifikasi' => 'primary',
                        'Ditolak' => 'danger',
                        default => 'warning text-dark'
                    };
                    ?>
                    <span class="status-badge bg-<?= $statusColor ?>">
                        <?= $siswa['status'] ?>
                    </span>
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

            <div class="row g-4">
                <!-- Left Column: Personal Data -->
                <div class="col-lg-8">
                    <div class="card card-detail p-4 mb-4">
                        <h5 class="section-title"><i class="fas fa-user-graduate me-2"></i>Data Pribadi</h5>
                        <div class="row g-4">
                            <div class="col-md-6 text-center mb-3">
                                <div class="p-3 bg-light rounded-4">
                                    <?php if ($siswa['foto_siswa']): ?>
                                        <img src="<?= BASE_URL ?>uploads/<?= $siswa['foto_siswa'] ?>"
                                            class="img-fluid rounded-4 shadow-sm" style="max-height: 200px;">
                                    <?php else: ?>
                                        <div class="py-5 text-muted"><i class="fas fa-user fa-5x"></i><br>Foto belum
                                            diupload</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="info-label">Nama Lengkap</div>
                                    <div class="info-value">
                                        <?= $siswa['nama_lengkap'] ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="info-label">NISN / NIK</div>
                                    <div class="info-value">
                                        <?= $siswa['nisn'] ?> /
                                        <?= $siswa['nik'] ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="info-label">Tempat, Tanggal Lahir / JK</div>
                                    <div class="info-value">
                                        <?= $siswa['tempat_lahir'] ?>,
                                        <?= date('d/m/Y', strtotime($siswa['tanggal_lahir'])) ?> /
                                        <?= $siswa['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="info-label">Agama</div>
                                    <div class="info-value">
                                        <?= $siswa['agama'] ?>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Anak Ke</div>
                                        <div class="info-value"><?= $siswa['anak_ke'] ?: '-' ?></div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Status dlm Keluarga</div>
                                        <div class="info-value"><?= $siswa['status_keluarga'] ?: '-' ?></div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Hobi</div>
                                        <div class="info-value"><?= $siswa['hobi'] ?: '-' ?></div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="info-label">No HP Murid</div>
                                        <div class="info-value fst-italic text-primary"><?= $siswa['no_hp'] ?: '-' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="info-label">Alamat Lengkap</div>
                                    <div class="info-value">
                                        <?= $siswa['alamat'] ?>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-4 mb-3">
                                        <div class="info-label">Status Tinggal</div>
                                        <div class="info-value"><?= $siswa['status_tinggal'] ?: '-' ?></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="info-label">Jarak ke Sekolah</div>
                                        <div class="info-value"><?= $siswa['jarak_sekolah'] ?: '-' ?></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="info-label">Transportasi</div>
                                        <div class="info-value"><?= $siswa['transportasi_rumah'] ?: '-' ?></div>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-3">
                                        <div class="info-label">Kecamatan</div>
                                        <div class="info-value"><?= $siswa['kecamatan'] ?></div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Kabupaten / Kota</div>
                                        <div class="info-value"><?= $siswa['kabupaten_kota'] ?></div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Provinsi</div>
                                        <div class="info-value"><?= $siswa['provinsi'] ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5 class="section-title mt-4"><i class="fas fa-school me-2"></i>Sekolah Asal</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-label">Nama Sekolah</div>
                                <div class="info-value">
                                    <?= $siswa['asal_sekolah'] ?>
                                </div>
                                <div class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i>
                                    <?= $siswa['kabupaten_kota'] . ', ' . $siswa['provinsi'] ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">NPSN Sekolah</div>
                                <div class="info-value">
                                    <?= $siswa['npsn_sekolah'] ?: '-' ?>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="info-label">Alamat Sekolah Asal</div>
                                <div class="info-value">
                                    <?= $siswa['alamat_sekolah'] ?: '-' ?>
                                </div>
                            </div>
                        </div>

                        <h5 class="section-title mt-4"><i class="fas fa-users me-2"></i>Data Orang Tua & Wali</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="info-label">Nomor Kartu Keluarga (KK)</div>
                                <div class="info-value fst-italic text-primary"><?= $siswa['no_kk'] ?: '-' ?></div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Status Orang Tua</div>
                                <div class="info-value badge bg-info text-dark"><?= $siswa['status_orang_tua'] ?: '-' ?>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4">
                            <div class="col-md-4 border-end">
                                <p class="fw-bold text-success mb-2 small text-uppercase">Informasi Ayah</p>
                                <div class="mb-2">
                                    <div class="info-label">Nama Ayah</div>
                                    <div class="info-value"><?= $siswa['nama_ayah'] ?></div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">NIK Ayah</div>
                                    <div class="info-value"><?= $siswa['nik_ayah'] ?: '-' ?></div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">TTL Ayah</div>
                                    <div class="info-value"><?= $siswa['tempat_lahir_ayah'] ?: '-' ?>,
                                        <?= $siswa['tanggal_lahir_ayah'] ?: '-' ?>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">Pendidikan / Pekerjaan</div>
                                    <div class="info-value"><?= $siswa['pendidikan_ayah'] ?: '-' ?> /
                                        <?= $siswa['pekerjaan_ayah'] ?: '-' ?>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">Penghasilan / HP</div>
                                    <div class="info-value"><?= $siswa['penghasilan_ayah'] ?: '-' ?> / <span
                                            class="text-primary"><?= $siswa['no_hp_ayah'] ?: '-' ?></span></div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">Alamat Ayah</div>
                                    <div class="info-value small text-muted">
                                        <?= $siswa['alamat_ayah'] ?: '(Sama dengan murid)' ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 border-end">
                                <p class="fw-bold text-success mb-2 small text-uppercase">Informasi Ibu</p>
                                <div class="mb-2">
                                    <div class="info-label">Nama Ibu</div>
                                    <div class="info-value"><?= $siswa['nama_ibu'] ?></div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">NIK Ibu</div>
                                    <div class="info-value"><?= $siswa['nik_ibu'] ?: '-' ?></div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">TTL Ibu</div>
                                    <div class="info-value"><?= $siswa['tempat_lahir_ibu'] ?: '-' ?>,
                                        <?= $siswa['tanggal_lahir_ibu'] ?: '-' ?>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">Pendidikan / Pekerjaan</div>
                                    <div class="info-value"><?= $siswa['pendidikan_ibu'] ?: '-' ?> /
                                        <?= $siswa['pekerjaan_ibu'] ?: '-' ?>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">Penghasilan / HP</div>
                                    <div class="info-value"><?= $siswa['penghasilan_ibu'] ?: '-' ?> / <span
                                            class="text-primary"><?= $siswa['no_hp_ibu'] ?: '-' ?></span></div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">Alamat Ibu</div>
                                    <div class="info-value small text-muted">
                                        <?= $siswa['alamat_ibu'] ?: '(Sama dengan murid)' ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <p class="fw-bold text-primary mb-2 small text-uppercase">Informasi Wali</p>
                                <div class="mb-2">
                                    <div class="info-label">Nama Wali</div>
                                    <div class="info-value"><?= $siswa['nama_wali'] ?: '-' ?></div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">NIK Wali</div>
                                    <div class="info-value"><?= $siswa['nik_wali'] ?: '-' ?></div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">TTL Wali</div>
                                    <div class="info-value"><?= $siswa['tempat_lahir_wali'] ?: '-' ?>,
                                        <?= $siswa['tanggal_lahir_wali'] ?: '-' ?>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">Pendidikan / Pekerjaan</div>
                                    <div class="info-value"><?= $siswa['pendidikan_wali'] ?: '-' ?> /
                                        <?= $siswa['pekerjaan_wali'] ?: '-' ?>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">Penghasilan / HP</div>
                                    <div class="info-value"><?= $siswa['penghasilan_wali'] ?: '-' ?> / <span
                                            class="text-primary"><?= $siswa['no_hp_wali'] ?: '-' ?></span></div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">Alamat Wali</div>
                                    <div class="info-value small text-muted"><?= $siswa['alamat_wali'] ?: '-' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Verification & Grades -->
                <div class="col-lg-4">
                    <?php if (stripos(($siswa['nama_jalur'] ?? ''), 'tahfi') !== false): ?>
                        <!-- Form Tahfidz Specific -->
                        <div class="card card-detail p-4 mb-4 border-top border-success border-4 shadow-sm">
                            <h5 class="section-title text-success"><i class="fas fa-quran me-2"></i>Status Seleksi Tahfidz
                            </h5>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Hasil Tes Tahfidz</label>
                                    <select name="status_tahfidz" class="form-select rounded-4">
                                        <option value="Pending" <?= ($siswa['status_tahfidz'] == 'Pending') ? 'selected' : '' ?>>-- Menunggu Tes --</option>
                                        <option value="Lulus" <?= ($siswa['status_tahfidz'] == 'Lulus') ? 'selected' : '' ?>>✅
                                            Lulus Tahfidz</option>
                                        <option value="Tidak Lulus" <?= ($siswa['status_tahfidz'] == 'Tidak Lulus') ? 'selected' : '' ?>>❌ Tidak Lulus Tahfidz</option>
                                    </select>
                                    <?php if ($siswa['status_tahfidz'] == 'Tidak Lulus'): ?>
                                        <div class="alert alert-warning py-2 px-3 rounded-4 mt-2 mb-0 small"
                                            style="font-size: 0.75rem;">
                                            <i class="fas fa-info-circle me-1"></i> Murid tidak lulus seleksi tahfidz,
                                            disarankan mengikuti jalur tes akademik.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" name="action" value="update_tahfidz"
                                    class="btn btn-success w-100 rounded-pill">
                                    <i class="fas fa-save me-2"></i> Update Status Tahfidz
                                </button>
                            </form>
                            <script>
                                // Dynamic warning for Tahfidz status selection
                                document.addEventListener('DOMContentLoaded', function () {
                                    const selectEl = document.querySelector('select[name="status_tahfidz"]');
                                    const warningBox = selectEl?.closest('.mb-3')?.querySelector('.alert-warning');

                                    if (selectEl) {
                                        selectEl.addEventListener('change', function () {
                                            if (this.value === 'Tidak Lulus') {
                                                if (warningBox) {
                                                    warningBox.style.display = 'block';
                                                } else {
                                                    // Create warning if it doesn't exist
                                                    const newWarning = document.createElement('div');
                                                    newWarning.className = 'alert alert-warning py-2 px-3 rounded-4 mt-2 mb-0 small';
                                                    newWarning.style.fontSize = '0.75rem';
                                                    newWarning.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Murid tidak lulus seleksi tahfidz, disarankan mengikuti jalur tes akademik.';
                                                    this.closest('.mb-3').appendChild(newWarning);
                                                }
                                            } else {
                                                if (warningBox) warningBox.style.display = 'none';
                                            }
                                        });
                                    }
                                });
                            </script>
                        </div>
                    <?php endif; ?>

                    <div class="card card-detail p-4 mb-4 border-top border-primary border-4">
                        <h5 class="section-title"><i class="fas fa-check-double me-2 text-primary"></i>Verifikasi</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Catatan Admin</label>
                                <textarea name="catatan" class="form-control rounded-4" rows="3"
                                    placeholder="Contoh: Lampiran Akta belum lengkap..."><?= htmlspecialchars($siswa['catatan_admin'] ?? '') ?></textarea>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <button type="submit" name="action" value="verifikasi"
                                        class="btn btn-primary w-100 rounded-pill">
                                        <i class="fas fa-check me-2"></i> Verifikasi
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="submit" name="action" value="tolak"
                                        class="btn btn-outline-danger w-100 rounded-pill">
                                        <i class="fas fa-times me-2"></i> Tolak
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card card-detail p-4 mb-4">
                        <h5 class="section-title"><i class="fas fa-list-ol me-2"></i>Rekap Nilai Rapor</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted small">K4 Sem 1</td>
                                    <td class="text-end fw-bold">
                                        <?= $siswa['nilai_k4_s1'] ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted small">K4 Sem 2</td>
                                    <td class="text-end fw-bold">
                                        <?= $siswa['nilai_k4_s2'] ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted small">K5 Sem 1</td>
                                    <td class="text-end fw-bold">
                                        <?= $siswa['nilai_k5_s1'] ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted small">K5 Sem 2</td>
                                    <td class="text-end fw-bold">
                                        <?= $siswa['nilai_k5_s2'] ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted small">K6 Sem 1</td>
                                    <td class="text-end fw-bold">
                                        <?= $siswa['nilai_k6_s1'] ?>
                                    </td>
                                </tr>
                                <tr class="border-top">
                                    <td class="fw-bold">Rata-rata</td>
                                    <td class="text-end text-primary fw-bold">
                                        <?= number_format($siswa['nilai_rapor_rata2'], 2) ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card card-detail p-4">
                        <h5 class="section-title"><i class="fas fa-file-pdf me-2 text-danger"></i>Dokumen Lampiran</h5>
                        <div class="list-group list-group-flush small">
                            <?php
                            $field_mapping = [
                                'pas foto' => ['field' => 'foto_siswa', 'label' => 'Pas Foto 3x4', 'icon' => 'fas fa-image', 'color' => 'text-primary'],
                                'rapor' => ['field' => 'file_rapor', 'label' => 'Rapor Asli', 'icon' => 'fas fa-book', 'color' => 'text-success'],
                                'surat keterangan tahfidz' => ['field' => 'file_surat_tahfidz', 'label' => 'SK. Tahfidz', 'icon' => 'fas fa-quran', 'color' => 'text-success'],
                                'surat keterangan prestasi' => ['field' => 'file_surat_prestasi', 'label' => 'SK. Prestasi', 'icon' => 'fas fa-certificate', 'color' => 'text-warning'],
                                'nilai rata-rata' => ['field' => 'file_nilai_rata', 'label' => 'SK. Nilai Rata-rata', 'icon' => 'fas fa-file-alt', 'color' => 'text-info'],
                                'rata rata nilai' => ['field' => 'file_nilai_rata', 'label' => 'SK. Nilai Rata-rata', 'icon' => 'fas fa-file-alt', 'color' => 'text-info'],
                                'peringkat' => ['field' => 'file_ranking', 'label' => 'SK. Peringkat', 'icon' => 'fas fa-trophy', 'color' => 'text-warning'],
                                'ranking' => ['field' => 'file_ranking', 'label' => 'SK. Ranking', 'icon' => 'fas fa-trophy', 'color' => 'text-warning'],
                                'sertifikat prestasi akademik' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertif. Prestasi Akdmk', 'icon' => 'fas fa-medal', 'color' => 'text-warning'],
                                'sertifikat prestasi non-akademik' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertif. Prestasi Non-Akdmk', 'icon' => 'fas fa-medal', 'color' => 'text-warning'],
                                'sertifikat prestasi' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertifikat Prestasi', 'icon' => 'fas fa-medal', 'color' => 'text-warning'],
                                'sertifikat tahfidz' => ['field' => 'file_sertifikat_tahfidz', 'label' => 'Sertifikat Tahfidz', 'icon' => 'fas fa-star', 'color' => 'text-success'],
                                'kartu keluarga' => ['field' => 'file_kk', 'label' => 'Kartu Keluarga', 'icon' => 'fas fa-users', 'color' => 'text-danger'],
                                'kk' => ['field' => 'file_kk', 'label' => 'Kartu Keluarga', 'icon' => 'fas fa-users', 'color' => 'text-danger'],
                                'pakta integritas' => ['field' => 'file_pakta', 'label' => 'Pakta Integritas', 'icon' => 'fas fa-file-contract', 'color' => 'text-secondary'],
                                'akta' => ['field' => 'file_akta', 'label' => 'Akta Kelahiran', 'icon' => 'fas fa-baby', 'color' => 'text-primary'],
                                'nisn' => ['field' => 'file_nisn', 'label' => 'Print Out NISN', 'icon' => 'fas fa-id-card', 'color' => 'text-info'],
                                'persyaratan' => ['field' => 'file_persyaratan', 'label' => 'Persyaratan', 'icon' => 'fas fa-file-alt', 'color' => 'text-primary'],
                                'aktual' => ['field' => 'file_persyaratan', 'label' => 'Persyaratan Aktual', 'icon' => 'fas fa-file-alt', 'color' => 'text-primary']
                            ];

                            $docs = [];
                            $added_fields = [];
                            $syarat_text = $siswa['syarat'] ?? '';

                            if (!empty($syarat_text)) {
                                $syarat_list = array_map('trim', explode(',', $syarat_text));
                                foreach ($syarat_list as $syarat) {
                                    $syarat_clean = preg_replace('/\s*\([^)]*\)\s*/', '', $syarat);
                                    $syarat_clean = preg_replace('/\s+\d+\s*$/', '', $syarat_clean);
                                    $syarat_clean = trim(preg_replace('/\s+/', ' ', $syarat_clean));
                                    $syarat_lower = strtolower($syarat_clean);

                                    foreach ($field_mapping as $keyword => $mapping) {
                                        if (stripos($syarat_lower, $keyword) !== false) {
                                            if (!in_array($mapping['field'], $added_fields)) {
                                                $is_optional = stripos($syarat, '(pilihan)') !== false;
                                                $docs[] = [
                                                    'label' => $mapping['label'],
                                                    'field' => $mapping['field'],
                                                    'icon' => $mapping['icon'],
                                                    'color' => $mapping['color'],
                                                    'optional' => $is_optional
                                                ];
                                                $added_fields[] = $mapping['field'];
                                                break;
                                            }
                                        }
                                    }
                                }
                            }

                            if (empty($docs)) {
                                $docs = [
                                    ['label' => 'KK', 'field' => 'file_kk', 'icon' => 'fas fa-users', 'color' => 'text-danger', 'optional' => false],
                                    ['label' => 'Akta', 'field' => 'file_akta', 'icon' => 'fas fa-baby', 'color' => 'text-primary', 'optional' => false],
                                    ['label' => 'Rapor', 'field' => 'file_rapor', 'icon' => 'fas fa-book', 'color' => 'text-success', 'optional' => false],
                                    ['label' => 'Foto', 'field' => 'foto_siswa', 'icon' => 'fas fa-image', 'color' => 'text-primary', 'optional' => false]
                                ];
                            }

                            foreach ($docs as $doc):
                                $exists = !empty($siswa[$doc['field']]);
                                if ($exists):
                                    ?>
                                    <a href="<?= BASE_URL ?>uploads/<?= $siswa[$doc['field']] ?>" target="_blank"
                                        class="list-group-item list-group-item-action py-3 px-0 d-flex justify-content-between align-items-center">
                                        <div><i class="<?= $doc['icon'] ?> me-2 <?= $doc['color'] ?>"></i>
                                            <?= $doc['label'] ?>
                                            <?php if ($doc['optional']): ?>
                                                <span class="badge bg-secondary-subtle text-secondary ms-1"
                                                    style="font-size: 0.6rem;">Pilihan</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge bg-success rounded-pill px-2"><i class="fas fa-check me-1"></i>
                                            Ada</span>
                                    </a>
                                <?php else: ?>
                                    <div
                                        class="list-group-item py-3 px-0 d-flex justify-content-between align-items-center bg-light opacity-75">
                                        <div><i class="<?= $doc['icon'] ?> me-2 text-muted"></i>
                                            <span class="text-muted"><?= $doc['label'] ?></span>
                                            <?php if ($doc['optional']): ?>
                                                <span class="badge bg-secondary-subtle text-secondary ms-1"
                                                    style="font-size: 0.6rem;">Pilihan</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($doc['optional']): ?>
                                            <span class="badge bg-secondary rounded-pill px-2"><i class="fas fa-minus me-1"></i>
                                                Kosong</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger rounded-pill px-2"><i class="fas fa-times me-1"></i>
                                                Kosong</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                endif;
                            endforeach;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>