<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/security.php';

$id = intval($_GET['id'] ?? 0);
$message = '';

if (!$id) {
    header("Location: index.php");
    exit();
}

// Get surat data
$stmt = $pdo->prepare("SELECT * FROM surat_keterangan WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch();

if (!$surat) {
    header("Location: index.php");
    exit();
}

// Handle File Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $upload_dir = '../../uploads/suket_templates/';

    // Create directory if not exists
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $pdf_uploaded = false;
    $docx_uploaded = false;
    $errors = [];

    try {
        // Upload PDF Preview
        if (isset($_FILES['file_preview_pdf']) && $_FILES['file_preview_pdf']['error'] !== UPLOAD_ERR_NO_FILE) {
            $pdf_validation = validate_uploaded_file($_FILES['file_preview_pdf'], ['application/pdf'], 5242880); // 5MB

            if ($pdf_validation['valid']) {
                $pdf_filename = 'preview_' . $id . '_' . time() . '.pdf';
                $pdf_path = $upload_dir . $pdf_filename;

                if (move_uploaded_file($_FILES['file_preview_pdf']['tmp_name'], $pdf_path)) {
                    chmod($pdf_path, 0644);

                    // Delete old file if exists
                    if (!empty($surat['file_preview_pdf']) && file_exists($upload_dir . $surat['file_preview_pdf'])) {
                        unlink($upload_dir . $surat['file_preview_pdf']);
                    }

                    // Update database
                    $stmt = $pdo->prepare("UPDATE surat_keterangan SET file_preview_pdf = ? WHERE id = ?");
                    $stmt->execute([$pdf_filename, $id]);
                    $pdf_uploaded = true;

                    log_security_event('FILE_UPLOAD_SUCCESS', 'PDF Preview: ' . $pdf_filename);
                }
            } else {
                $errors[] = 'PDF: ' . implode(', ', $pdf_validation['errors']);
                log_security_event('INVALID_FILE_UPLOAD', 'PDF Preview: ' . implode(', ', $pdf_validation['errors']));
            }
        }

        // Upload DOCX Template
        if (isset($_FILES['file_template_docx']) && $_FILES['file_template_docx']['error'] !== UPLOAD_ERR_NO_FILE) {
            $allowed_docx = [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword'
            ];
            $docx_validation = validate_uploaded_file($_FILES['file_template_docx'], $allowed_docx, 5242880); // 5MB

            if ($docx_validation['valid']) {
                $docx_filename = 'template_' . $id . '_' . time() . '.docx';
                $docx_path = $upload_dir . $docx_filename;

                if (move_uploaded_file($_FILES['file_template_docx']['tmp_name'], $docx_path)) {
                    chmod($docx_path, 0644);

                    // Delete old file if exists
                    if (!empty($surat['file_template_docx']) && file_exists($upload_dir . $surat['file_template_docx'])) {
                        unlink($upload_dir . $surat['file_template_docx']);
                    }

                    // Update database
                    $stmt = $pdo->prepare("UPDATE surat_keterangan SET file_template_docx = ? WHERE id = ?");
                    $stmt->execute([$docx_filename, $id]);
                    $docx_uploaded = true;

                    log_security_event('FILE_UPLOAD_SUCCESS', 'DOCX Template: ' . $docx_filename);
                }
            } else {
                $errors[] = 'DOCX: ' . implode(', ', $docx_validation['errors']);
                log_security_event('INVALID_FILE_UPLOAD', 'DOCX Template: ' . implode(', ', $docx_validation['errors']));
            }
        }

        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM surat_keterangan WHERE id = ?");
        $stmt->execute([$id]);
        $surat = $stmt->fetch();

        if ($pdf_uploaded || $docx_uploaded) {
            $uploaded_files = [];
            if ($pdf_uploaded)
                $uploaded_files[] = 'PDF Preview';
            if ($docx_uploaded)
                $uploaded_files[] = 'DOCX Template';

            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>File berhasil diupload: ' . implode(', ', $uploaded_files) . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>';
        }

        if (!empty($errors)) {
            $message .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>Beberapa file gagal diupload: ' . implode('<br>', $errors) . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>';
        }

    } catch (Exception $e) {
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>Error: ' . $e->getMessage() . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Upload Files -
        <?= htmlspecialchars($surat['nama_surat']) ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .btn-dark.d-lg-none {
            z-index: 1060 !important;
        }

        .upload-box {
            border: 2px dashed #dee2e6;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .upload-box:hover {
            border-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.05);
        }

        .upload-box.has-file {
            border-color: #198754;
            background-color: rgba(25, 135, 84, 0.05);
        }

        .file-preview {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-primary fw-bold mb-1">
                        <i class="fas fa-cloud-upload-alt me-2"></i>Upload Files
                    </h2>
                    <p class="text-muted">Upload file preview (PDF) dan template (DOCX) untuk: <strong>
                            <?= htmlspecialchars($surat['nama_surat']) ?>
                        </strong></p>
                </div>
                <a href="index.php" class="btn btn-outline-secondary rounded-pill">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <?= $message ?>

            <div class="row g-4">
                <!-- Upload PDF Preview -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-danger bg-opacity-10 border-0 py-3">
                            <h5 class="mb-0 fw-bold text-danger">
                                <i class="fas fa-file-pdf me-2"></i>Preview (PDF)
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="upload-box <?= !empty($surat['file_preview_pdf']) ? 'has-file' : '' ?>">
                                    <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                                    <h6 class="fw-bold">Upload PDF Preview</h6>
                                    <p class="small text-muted mb-3">Max 5MB, format PDF</p>
                                    <input type="file" name="file_preview_pdf" id="file_preview_pdf"
                                        class="form-control" accept=".pdf" required>
                                </div>

                                <?php if (!empty($surat['file_preview_pdf'])): ?>
                                    <div class="file-preview">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <strong>File saat ini:</strong>
                                                <span class="text-muted">
                                                    <?= htmlspecialchars($surat['file_preview_pdf']) ?>
                                                </span>
                                            </div>
                                            <a href="<?= BASE_URL ?>uploads/suket_templates/<?= $surat['file_preview_pdf'] ?>"
                                                target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <button type="submit" class="btn btn-danger w-100 mt-3">
                                    <i class="fas fa-upload me-2"></i>Upload PDF Preview
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Upload DOCX Template -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-primary bg-opacity-10 border-0 py-3">
                            <h5 class="mb-0 fw-bold text-primary">
                                <i class="fas fa-file-word me-2"></i>Template (DOCX)
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="upload-box <?= !empty($surat['file_template_docx']) ? 'has-file' : '' ?>">
                                    <i class="fas fa-file-word fa-3x text-primary mb-3"></i>
                                    <h6 class="fw-bold">Upload DOCX Template</h6>
                                    <p class="small text-muted mb-3">Max 5MB, format DOCX/DOC</p>
                                    <input type="file" name="file_template_docx" id="file_template_docx"
                                        class="form-control" accept=".docx,.doc" required>
                                </div>

                                <?php if (!empty($surat['file_template_docx'])): ?>
                                    <div class="file-preview">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <strong>File saat ini:</strong>
                                                <span class="text-muted">
                                                    <?= htmlspecialchars($surat['file_template_docx']) ?>
                                                </span>
                                            </div>
                                            <a href="<?= BASE_URL ?>uploads/suket_templates/<?= $surat['file_template_docx'] ?>"
                                                download class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download me-1"></i>Download
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <button type="submit" class="btn btn-primary w-100 mt-3">
                                    <i class="fas fa-upload me-2"></i>Upload DOCX Template
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="card border-0 bg-info bg-opacity-10 rounded-4 mt-4">
                <div class="card-body">
                    <h6 class="fw-bold text-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>Informasi File Upload
                    </h6>
                    <ul class="mb-0">
                        <li class="mb-2"><strong>PDF Preview:</strong> File yang akan ditampilkan ketika user klik
                            tombol "Preview" di halaman public</li>
                        <li class="mb-2"><strong>DOCX Template:</strong> File template Word yang bisa di-download dan
                            diedit oleh user</li>
                        <li class="mb-2">File yang sudah diupload akan menggantikan file lama (otomatis dihapus)</li>
                        <li class="mb-0">Maksimal ukuran file: 5MB per file</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>