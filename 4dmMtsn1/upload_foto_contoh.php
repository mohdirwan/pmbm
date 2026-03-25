<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

$message = '';
$target_file = '../assets/img/contoh_siswa_merah.png';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_contoh'])) {
    $file = $_FILES['file_contoh'];
    $allowed_types = ['image/jpeg', 'image/png'];
    $max_size = 2 * 1024 * 1024; // 2MB

    if ($file['error'] !== 0) {
        $message = '<div class="alert alert-danger">Terjadi kesalahan saat upload file.</div>';
    } elseif (!in_array($file['type'], $allowed_types)) {
        $message = '<div class="alert alert-danger">Format file tidak didukung. Gunakan JPG atau PNG.</div>';
    } elseif ($file['size'] > $max_size) {
        $message = '<div class="alert alert-danger">Ukuran file terlalu besar. Maksimal 2MB.</div>';
    } else {
        // Ensure directory exists
        if (!is_dir('../assets/img')) {
            mkdir('../assets/img', 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $message = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Berhasil memperbarui foto contoh!</div>';
        } else {
            $message = '<div class="alert alert-danger">Gagal memindahkan file ke direktori tujuan. Pastikan folder assets/img punya izin tulis.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Upload Foto Contoh - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4 text-primary fw-bold"><i class="fas fa-camera me-2"></i>Upload Foto Contoh</h2>

            <?= $message ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold">Upload File Baru</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">File ini akan digunakan sebagai panduan murid saat mengunggah
                                pas foto. Disarankan menggunakan foto dengan latar merah dan rasio 3x4.</p>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pilih Foto (JPG/PNG, Max 2MB)</label>
                                    <input type="file" name="file_contoh" class="form-control" required
                                        accept="image/jpeg,image/png">
                                </div>
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <i class="fas fa-upload me-2"></i>Upload & Ganti
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold">Preview Foto Saat Ini</h6>
                        </div>
                        <div class="card-body text-center">
                            <?php if (file_exists($target_file)): ?>
                                <img src="<?= $target_file ?>?v=<?= time() ?>" class="img-fluid rounded shadow-sm"
                                    style="max-height: 300px;">
                                <div class="mt-3 text-muted small">Lokasi: assets/img/contoh_siswa_merah.png</div>
                            <?php else: ?>
                                <div class="py-5 text-muted">
                                    <i class="fas fa-image fa-3x mb-3"></i><br>
                                    Belum ada foto contoh yang diupload.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>