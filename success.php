<?php
require_once 'includes/config.php';

$no_pendaftaran = $_GET['reg'] ?? '';
$student = null;

if ($no_pendaftaran) {
    $stmt = $pdo->prepare("SELECT p.nisn, p.nama_lengkap, p.kontak_wa, p.jalur_id, j.nama_jalur 
                           FROM pendaftar p 
                           JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
                           WHERE p.no_pendaftaran = ?");
    $stmt->execute([$no_pendaftaran]);
    $student = $stmt->fetch();
}

if (!$student) {
    header("Location: index.php");
    exit();
}

// Determine success message based on mapped jalur ids
$special_ids = explode(',', get_setting('narasi_special_jalur_ids', ''));
$general_ids = explode(',', get_setting('narasi_general_jalur_ids', ''));
$is_special = in_array($student['jalur_id'], $special_ids);
$is_general = in_array($student['jalur_id'], $general_ids);

if ($is_special) {
    // Priority 1: Special Narrative
    $success_message = get_setting('narasi_pendaftaran_tahfizh', "Selamat, pendaftaran Ananda di MTsN 1 Kota Pekanbaru melalui jalur tahfizh telah berhasil dan tercatat dalam sistem. Silakan mengikuti tes tahfizh pada hari Senin – Selasa, 09 – 10 Maret 2026 pukul 08.00 – 12.00 WIB di MTsN 1 Kota Pekanbaru.");
} else {
    // Priority 2: General Narrative (explicitly chosen or fallback)
    $default_general = "Selamat, pendaftaran Ananda di MTsN 1 Kota Pekanbaru melalui  … telah berhasil dan tercatat dalam sistem. Silakan menunggu informasi selanjutnya sesuai jadwal yang ditentukan.";
    $raw_narasi = get_setting('narasi_pendaftaran_berhasil', $default_general);
    $nama_jalur_clean = preg_replace('/^jalur\s+/i', '', htmlspecialchars($student['nama_jalur']));
    $success_message = str_replace(['…', '...', '[jalur]'], $nama_jalur_clean, $raw_narasi);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil - PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-success {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .login-info-box {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 20px;
            padding: 25px;
        }

        .info-label {
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
        }
    </style>
</head>

<body class="bg-animated-madrasah">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 text-center">
                <div class="card card-success p-5 border-0">
                    <div class="mb-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                            style="width: 100px; height: 100px;">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                    </div>

                    <h2 class="fw-bold mb-2">Alhamdulillah!</h2>
                    <h5 class="fw-bold text-success mb-4"><?= $success_message ?></h5>

                    <div class="alert alert-warning border-0 rounded-4 mb-4 shadow-sm text-start">
                        <div class="d-flex">
                            <i class="fas fa-file-upload fa-lg me-3 mt-1"></i>
                            <div>
                                <div class="fw-bold">Langkah Selanjutnya:</div>
                                <div class="small">Silakan <strong>Login Murid</strong> untuk melengkapi berkas persyaratan (Pas Foto, KK, Rapor, dll) di Dashboard Murid agar dapat diverifikasi oleh admin.</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-start mb-4">
                        <p class="text-center text-muted small mb-4">Gunakan informasi di bawah ini untuk login ke dashboard murid:</p>

                        <div class="login-info-box shadow-sm">
                            <div class="row g-3">
                                <div class="col-12 border-bottom pb-3 mb-2">
                                    <div class="info-label">Nama Murid</div>
                                    <div class="text-dark fw-bold"><?= htmlspecialchars($student['nama_lengkap']) ?>
                                    </div>
                                </div>
                                <div class="col-md-6 text-center border-end">
                                    <div class="info-label">Username (Input)</div>
                                    <div class="info-value"><?= htmlspecialchars($student['nisn']) ?> (NISN)</div>
                                </div>
                                <div class=" col-md-6 text-center">
                                    <div class="info-label">Password</div>
                                    <div class="info-value" style="font-size: 1.1rem;">Sesuai yang Anda buat</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="cetak_formulir.php?reg=<?= $no_pendaftaran ?>" target="_blank"
                                    class="btn btn-outline-primary btn-lg rounded-pill shadow w-100">
                                    <i class="fas fa-file-alt me-2"></i> Cetak Formulir
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="cetak_bukti.php?reg=<?= $no_pendaftaran ?>" target="_blank"
                                    class="btn btn-primary btn-lg rounded-pill shadow w-100">
                                    <i class="fas fa-id-card me-2"></i> Cetak Kartu
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="login_siswa.php" class="btn btn-success btn-lg rounded-pill shadow w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i> Login Murid
                                </a>
                            </div>
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary btn-lg rounded-pill w-100">
                            Beranda
                        </a>
                    </div>

                    <p class="text-muted small mt-4">
                        <i class="fas fa-info-circle me-1"></i> Jangan lupa simpan atau screenshot halaman ini sebagai bukti pendaftaran awal Anda. No. Pendaftaran Anda: <strong><?= $no_pendaftaran ?></strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>