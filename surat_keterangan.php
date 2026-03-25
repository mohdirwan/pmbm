<?php
require_once 'includes/config.php';

// Get active surat keterangan
$stmt = $pdo->query("SELECT * FROM surat_keterangan WHERE is_active = 1 ORDER BY urutan ASC");
$surat_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan - PMBM MTsN 1 Kota Pekanbaru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f5132 0%, #1a7f5a 100%);
            min-height: 100vh;
        }

        .header-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem 0;
            margin-bottom: 3rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .card-surat {
            border: none;
            border-radius: 24px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .card-surat:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .card-header-custom {
            background: linear-gradient(135deg, #0f5132, #1a7f5a);
            color: white;
            padding: 1.5rem;
            border: none;
        }

        .btn-action {
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-preview {
            background: linear-gradient(135deg, #0dcaf0, #0d6efd);
            border: none;
            color: white;
        }

        .btn-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(13, 202, 240, 0.4);
            color: white;
        }

        .btn-download {
            background: linear-gradient(135deg, #198754, #20c997);
            border: none;
            color: white;
        }

        .btn-download:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(25, 135, 84, 0.4);
            color: white;
        }

        .badge-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffc107, #ff9800);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 800;
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="text-white fw-bold mb-2">
                        <i class="fas fa-file-alt me-3"></i>Surat Keterangan
                    </h1>
                    <p class="text-white-50 mb-0">Template Surat Keterangan untuk PMBM MTsN 1 Kota Pekanbaru</p>
                </div>
                <a href="index.php" class="btn btn-light rounded-pill px-4">
                    <i class="fas fa-home me-2"></i>Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="container pb-5">
        <div class="row g-4">
            <?php if (count($surat_list) > 0): ?>
                <?php foreach ($surat_list as $index => $surat): ?>
                    <div class="col-lg-6">
                        <div class="card card-surat h-100">
                            <div class="card-header-custom">
                                <div class="d-flex align-items-center">
                                    <div class="badge-number me-3">
                                        <?= $index + 1 ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1 fw-bold">
                                            <?= htmlspecialchars($surat['nama_surat']) ?>
                                        </h5>
                                        <small class="opacity-75">PMBM MTsN 1 Kota Pekanbaru</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <p class="text-muted mb-4">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>
                                    <?= htmlspecialchars($surat['keterangan']) ?>
                                </p>

                                <div class="d-flex gap-3">
                                    <?php
                                    // Determine preview URL - use uploaded PDF if exists, otherwise generated preview
                                    $preview_url = !empty($surat['file_preview_pdf'])
                                        ? BASE_URL . 'uploads/suket_templates/' . $surat['file_preview_pdf']
                                        : 'preview_suket.php?id=' . $surat['id'];

                                    // Determine download URL - use uploaded DOCX if exists
                                    $download_url = !empty($surat['file_template_docx'])
                                        ? BASE_URL . 'uploads/suket_templates/' . $surat['file_template_docx']
                                        : 'preview_suket.php?id=' . $surat['id'];

                                    $download_attr = !empty($surat['file_template_docx']) ? 'download' : 'target="_blank"';
                                    ?>

                                    <a href="<?= $preview_url ?>" target="_blank" class="btn btn-preview btn-action flex-fill">
                                        <i class="fas fa-eye me-2"></i>Preview
                                    </a>
                                    <a href="<?= $download_url ?>" <?= $download_attr ?>
                                        class="btn btn-download btn-action flex-fill">
                                        <i class="fas fa-download me-2"></i>Download Template
                                    </a>
                                </div>

                                <div class="mt-3 p-3 bg-light rounded-3">
                                    <small class="text-muted d-block mb-1">
                                        <i class="fas fa-lightbulb text-warning me-2"></i>
                                        <strong>Tips:</strong>
                                    </small>
                                    <small class="text-muted">
                                        Download template, isi sesuai data Anda, kemudian minta tanda tangan dari pihak
                                        berwenang (Kepala Sekolah/Guru).
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card card-surat text-center p-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Belum ada surat keterangan tersedia</h4>
                        <p class="text-muted">Silakan hubungi admin untuk informasi lebih lanjut.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info Additional -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card border-0 bg-white bg-opacity-90 rounded-4 shadow">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-primary mb-3">
                            <i class="fas fa-question-circle me-2"></i>Informasi Penting
                        </h5>
                        <ul class="mb-0">
                            <li class="mb-2">Surat keterangan harus ditandatangani oleh Kepala Sekolah asal dan diberi
                                stempel sekolah</li>
                            <li class="mb-2">Pastikan semua data yang diisi sudah benar dan sesuai dengan dokumen asli
                            </li>
                            <li class="mb-2">Surat keterangan yang telah diisi harus di-scan dan diupload saat
                                pendaftaran</li>
                            <li class="mb-0">Untuk bantuan lebih lanjut, hubungi panitia PMBM</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>