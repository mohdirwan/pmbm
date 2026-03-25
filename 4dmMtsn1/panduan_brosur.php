<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

$success_msg = '';
$error_msg = '';

// Handle DELETE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['delete_id']);
    try {
        // Delete file if exists
        $stmt = $pdo->prepare("SELECT file_path FROM panduan_brosur WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if ($data && $data['file_path']) {
            $filePath = '../' . $data['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM panduan_brosur WHERE id = ?");
        $stmt->execute([$id]);
        $success_msg = 'Item berhasil dihapus!';
    } catch (Exception $e) {
        $error_msg = 'Gagal menghapus: ' . $e->getMessage();
    }
}

// Check if POST is empty but Content-Length is not (typical sign of exceeding post_max_size)
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $displayMaxSize = ini_get('post_max_size');
    $error_msg = "File yang diupload terlalu besar! Melebihi batas server ($displayMaxSize). Silakan kompres file PDF/Gambar Anda atau hubungi admin untuk menaikkan limit.";
}

// Handle ADD & EDIT
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['action'])) {
    try {
        $id = $_POST['id'] ?? null;
        $judul = $_POST['judul'] ?? ''; // Pakai null coalescence

        if (empty($judul)) {
            throw new Exception("Judul tidak boleh kosong. Jika Anda mengupload file besar, kemungkinan ukuran file melebihi batas server.");
        }

        $tipe = $_POST['tipe'] ?? 'file';
        $video_url = $_POST['video_url'] ?? '';
        $icon_class = $_POST['icon_class'] ?? 'fa-book-open';
        $color_class = $_POST['color_class'] ?? 'primary';
        $urutan = intval($_POST['urutan'] ?? 0);

        $uploadDir = '../uploads/panduan/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if ($id) {
            // UPDATE
            if ($tipe == 'file' && isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                // Delete old file
                $stmt = $pdo->prepare("SELECT file_path FROM panduan_brosur WHERE id = ?");
                $stmt->execute([$id]);
                $oldData = $stmt->fetch();
                if ($oldData && $oldData['file_path']) {
                    $oldFilePath = '../' . $oldData['file_path'];
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                // Upload new file
                $fileName = time() . '_' . basename($_FILES['file']['name']);
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                    $file_path = 'uploads/panduan/' . $fileName;
                    $stmt = $pdo->prepare("UPDATE panduan_brosur SET judul = ?, tipe = ?, file_path = ?, video_url = ?, icon_class = ?, color_class = ?, urutan = ? WHERE id = ?");
                    $stmt->execute([$judul, $tipe, $file_path, $video_url, $icon_class, $color_class, $urutan, $id]);
                }
            } else {
                // Update without file
                $stmt = $pdo->prepare("UPDATE panduan_brosur SET judul = ?, tipe = ?, video_url = ?, icon_class = ?, color_class = ?, urutan = ? WHERE id = ?");
                $stmt->execute([$judul, $tipe, $video_url, $icon_class, $color_class, $urutan, $id]);
            }
            $success_msg = 'Data berhasil diupdate!';
        } else {
            // INSERT
            $file_path = null;
            if ($tipe == 'file' && isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                $fileName = time() . '_' . basename($_FILES['file']['name']);
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                    $file_path = 'uploads/panduan/' . $fileName;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO panduan_brosur (judul, tipe, file_path, video_url, icon_class, color_class, urutan) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$judul, $tipe, $file_path, $video_url, $icon_class, $color_class, $urutan]);
            $success_msg = 'Item berhasil ditambahkan!';
        }
    } catch (Exception $e) {
        $error_msg = 'Gagal menyimpan: ' . $e->getMessage();
    }
}

// Get all data
$stmt = $pdo->query("SELECT * FROM panduan_brosur ORDER BY urutan ASC, id DESC");
$items = $stmt->fetchAll();

// Icon options
$iconOptions = [
    'fa-book-open' => 'Buku',
    'fa-file-pdf' => 'PDF',
    'fa-video' => 'Video',
    'fa-download' => 'Download',
    'fa-info-circle' => 'Info',
    'fa-question-circle' => 'Tanya Jawab',
    'fa-graduation-cap' => 'Pendidikan',
    'fa-clipboard-list' => 'Checklist'
];

// Color options
$colorOptions = [
    'primary' => 'Biru',
    'success' => 'Hijau',
    'danger' => 'Merah',
    'warning' => 'Kuning',
    'info' => 'Biru Muda',
    'dark' => 'Hitam'
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Panduan & Brosur - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .item-card {
            transition: all 0.3s;
            cursor: pointer;
        }

        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .btn-dark.d-lg-none {
            z-index: 1060 !important;
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-primary fw-bold mb-0">Panduan & Brosur</h2>
                    <p class="text-muted small">Kelola file panduan dan brosur pendaftaran untuk calon siswa.</p>
                </div>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalItem"
                    onclick="resetForm()">
                    <i class="fas fa-plus me-2"></i>Tambah Item
                </button>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?= $success_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?= $error_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Items Grid -->
            <div class="row">
                <?php if (count($items) > 0): ?>
                    <?php foreach ($items as $item): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm rounded-4 item-card">
                                <div class="card-header bg-white border-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0 text-<?= $item['color_class'] ?>">
                                            <i class="fas <?= $item['icon_class'] ?> me-2"></i>
                                            <?= htmlspecialchars($item['judul']) ?>
                                        </h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <span
                                            class="badge bg-<?= $item['tipe'] == 'file' ? 'primary' : 'danger' ?> rounded-pill">
                                            <i class="fas <?= $item['tipe'] == 'file' ? 'fa-file' : 'fa-video' ?> me-1"></i>
                                            <?= $item['tipe'] == 'file' ? 'File (PDF/Image)' : 'Video (YouTube)' ?>
                                        </span>
                                        <span class="badge bg-light text-dark border ms-2">
                                            Urutan: <?= $item['urutan'] ?>
                                        </span>
                                    </div>

                                    <?php if ($item['tipe'] == 'file' && $item['file_path']): ?>
                                        <div class="mb-3">
                                            <small class="text-muted">File:</small>
                                            <div>
                                                <a href="<?= BASE_URL . $item['file_path'] ?>" target="_blank"
                                                    class="text-decoration-none">
                                                    <i class="fas fa-external-link-alt me-1"></i>
                                                    Lihat File
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($item['tipe'] == 'video' && $item['video_url']): ?>
                                        <div class="mb-3">
                                            <small class="text-muted">URL Video:</small>
                                            <div class="small">
                                                <a href="<?= htmlspecialchars($item['video_url']) ?>" target="_blank"
                                                    class="text-decoration-none text-truncate d-block">
                                                    <?= htmlspecialchars($item['video_url']) ?>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-warning flex-fill"
                                            onclick='editItem(<?= json_encode($item) ?>)'>
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="deleteItem(<?= $item['id'] ?>, '<?= htmlspecialchars($item['judul'], ENT_QUOTES) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Belum Ada Item</h5>
                                <p class="text-muted small">Klik tombol "Tambah Item" untuk menambah panduan atau brosur</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit -->
    <div class="modal fade" id="modalItem" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title" id="modalTitle">
                            <i class="fas fa-plus-circle me-2"></i>Tambah Item Baru
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="id" id="itemId">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Judul Panduan/Brosur <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="judul" id="judul" required
                                placeholder="Contoh: Petunjuk Teknis PMBM">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Icon</label>
                                <select class="form-select" name="icon_class" id="icon_class">
                                    <?php foreach ($iconOptions as $value => $label): ?>
                                        <option value="<?= $value ?>">
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Warna</label>
                                <select class="form-select" name="color_class" id="color_class">
                                    <?php foreach ($colorOptions as $value => $label): ?>
                                        <option value="<?= $value ?>">
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Urutan</label>
                            <input type="number" class="form-control" name="urutan" id="urutan" value="0" min="0">
                            <small class="text-muted">Semakin kecil angka, semakin di depan urutannya</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipe</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe" id="tipeFile" value="file"
                                        checked>
                                    <label class="form-check-label" for="tipeFile">
                                        File (PDF/Image)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe" id="tipeVideo"
                                        value="video">
                                    <label class="form-check-label" for="tipeVideo">
                                        Video (YouTube URL)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="inputFile" class="mb-3">
                            <label class="form-label fw-bold">Upload File</label>
                            <input type="file" class="form-control" name="file" id="fileInput"
                                accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted" id="currentFile"></small>
                            <div class="text-info small mt-1">
                                <i class="fas fa-info-circle"></i> Maksimal ukuran file:
                                <?= ini_get('upload_max_filesize') ?>
                                (Disarankan di bawah 5MB agar cepat diakses)
                            </div>
                        </div>

                        <div id="inputVideo" class="mb-3 d-none">
                            <label class="form-label fw-bold">URL Video YouTube</label>
                            <input type="url" class="form-control" name="video_url" id="video_url"
                                placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-save me-2"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('itemId').value = '';
            document.getElementById('judul').value = '';
            document.getElementById('icon_class').value = 'fa-book-open';
            document.getElementById('color_class').value = 'primary';
            document.getElementById('urutan').value = '0';
            document.getElementById('tipeFile').checked = true;
            document.getElementById('video_url').value = '';
            document.getElementById('currentFile').textContent = '';
            document.getElementById('inputFile').classList.remove('d-none');
            document.getElementById('inputVideo').classList.add('d-none');
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Tambah Item Baru';
        }

        function editItem(item) {
            document.getElementById('itemId').value = item.id;
            document.getElementById('judul').value = item.judul;
            document.getElementById('icon_class').value = item.icon_class;
            document.getElementById('color_class').value = item.color_class;
            document.getElementById('urutan').value = item.urutan;
            document.getElementById('video_url').value = item.video_url || '';

            if (item.tipe === 'video') {
                document.getElementById('tipeVideo').checked = true;
                document.getElementById('inputFile').classList.add('d-none');
                document.getElementById('inputVideo').classList.remove('d-none');
            } else {
                document.getElementById('tipeFile').checked = true;
                document.getElementById('inputFile').classList.remove('d-none');
                document.getElementById('inputVideo').classList.add('d-none');
                if (item.file_path) {
                    document.getElementById('currentFile').textContent = 'File saat ini: ' + item.file_path.split('/').pop();
                }
            }

            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Item';
            new bootstrap.Modal(document.getElementById('modalItem')).show();
        }

        function deleteItem(id, judul) {
            if (confirm('Apakah Anda yakin ingin menghapus "' + judul + '"?\n\nData yang terhapus tidak dapat dikembalikan!')) {
                var form = document.createElement('form');
                form.method = 'POST';

                var actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                form.appendChild(actionInput);

                var idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'delete_id';
                idInput.value = id;
                form.appendChild(idInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        // Toggle file/video input
        document.querySelectorAll('input[name="tipe"]').forEach(input => {
            input.addEventListener('change', function () {
                if (this.value === 'video') {
                    document.getElementById('inputFile').classList.add('d-none');
                    document.getElementById('inputVideo').classList.remove('d-none');
                } else {
                    document.getElementById('inputFile').classList.remove('d-none');
                    document.getElementById('inputVideo').classList.add('d-none');
                }
            });
        });
    </script>
    <script>
        // Client-side file size validation
        document.getElementById('fileInput').addEventListener('change', function () {
            if (this.files && this.files[0]) {
                var file = this.files[0];

                // Get server limit from PHP (approximate parsing)
                var serverLimitStr = "<?= ini_get('upload_max_filesize') ?>";
                var serverLimit = parseSize(serverLimitStr);

                if (file.size > serverLimit) {
                    alert('Ukuran file terlalu besar! Maksimal: ' + serverLimitStr + '. File Anda: ' + (file.size / 1024 / 1024).toFixed(2) + ' MB.\nSilakan kompres file atau hubungi admin.');
                    this.value = ''; // Reset input
                }
            }
        });

        function parseSize(sizeStr) {
            // Remove non-numeric characters except . if any, but simplistic approach first
            var size = parseFloat(sizeStr);
            var unit = sizeStr.replace(/[0-9.]/g, '').toUpperCase().trim();

            if (unit.indexOf('M') > -1) size *= 1024 * 1024;
            else if (unit.indexOf('K') > -1) size *= 1024;
            else if (unit.indexOf('G') > -1) size *= 1024 * 1024 * 1024;

            return size;
        }
    </script>
</body>

</html>