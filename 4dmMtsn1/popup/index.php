<?php
require_once '../../includes/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../' . ADMIN_LOGIN_PATH);
    exit;
}

// Fetch popups
$stmt = $pdo->query("SELECT * FROM app_popup ORDER BY id DESC");
$popups = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Manajemen Pop-up Iklan - Admin PMBM</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .popup-img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            background: #f8f9fa;
            border-radius: 10px;
        }

        /* Essential Sidebar Styles from Dashboard */
        .sidebar {
            height: 100vh;
            background: #0f5132;
            color: white;
            padding-top: 20px;
            position: fixed;
            width: 260px;
            z-index: 1050;
            transition: transform 0.3s ease-in-out;
            overflow-y: auto;
            left: 0;
        }

        .main-content {
            margin-left: 260px;
            padding: 30px;
            transition: margin-left 0.3s ease-in-out;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-primary fw-bold">Manajemen Pop-up Iklan</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPopup"
                    onclick="openModal('add')">
                    <i class="fas fa-plus me-2"></i> Tambah Pop-up
                </button>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="alert alert-info border-0 shadow-sm rounded-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Catatan:</strong> Jika ada lebih dari satu Pop-up yang <strong>Aktif</strong>, hanya yang
                terbaru yang akan ditampilkan di website.
            </div>

            <!-- Popups Grid -->
            <div class="row g-4">
                <?php if (count($popups) > 0): ?>
                    <?php foreach ($popups as $popup): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                                <div class="position-relative">
                                    <img src="<?= BASE_URL . $popup['image_path'] ?>" class="popup-img" alt="Popup Image">
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <span class="badge bg-<?= $popup['status'] ? 'success' : 'secondary' ?>">
                                            <?= $popup['status'] ? 'Aktif' : 'Non-Aktif' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title fw-bold text-truncate">
                                        <?= $popup['link'] ? 'Link: ' . htmlspecialchars($popup['link']) : 'Tanpa Link' ?>
                                    </h5>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <small class="text-muted"><i class="fas fa-clock me-1"></i> Durasi:
                                            <?= $popup['timer'] / 1000 ?> detik
                                        </small>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick='openModal("edit", <?= json_encode($popup) ?>)'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="deletePopup(<?= $popup['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-window-maximize fa-3x mb-3 opacity-50"></i>
                            <p>Belum ada pop-up yang ditambahkan.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="modalPopup" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="process.php" method="POST" enctype="multipart/form-data" id="formPopup">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Pop-up</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="inputAction" value="add">
                        <input type="hidden" name="id" id="inputId">
                        <input type="hidden" name="existing_image" id="inputExistingImage">

                        <div class="mb-3">
                            <label class="form-label">Gambar Pop-up</label>
                            <input type="file" class="form-control" name="image" id="inputImage" accept="image/*">
                            <small class="text-muted d-block mt-1">Format: JPG, PNG, WEBP, GIF. Maks 2MB.</small>
                            <div id="previewContainer" class="mt-2 d-none">
                                <img src="" id="imgPreview" class="img-fluid rounded" style="max-height: 200px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link Tujuan (Opsional)</label>
                            <input type="url" class="form-control" name="link" id="inputLink"
                                placeholder="https://example.com">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Durasi Tampil (ms)</label>
                                <input type="number" class="form-control" name="timer" id="inputTimer" value="5000"
                                    step="1000">
                                <small class="text-muted">1000 ms = 1 detik. Isi 0 jika tanpa timer (tutup
                                    manual).</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="inputStatus">
                                    <option value="1">Aktif</option>
                                    <option value="0">Non-Aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSave">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Form -->
    <form action="process.php" method="POST" id="formDelete">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>


    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function openModal(mode, data = null) {
            const modalTitle = document.getElementById('modalTitle');
            const inputAction = document.getElementById('inputAction');
            const btnSave = document.getElementById('btnSave');
            const previewContainer = document.getElementById('previewContainer');

            // Reset Form
            document.getElementById('formPopup').reset();
            previewContainer.classList.add('d-none');

            if (mode === 'edit' && data) {
                modalTitle.innerText = 'Edit Pop-up';
                inputAction.value = 'update';
                btnSave.innerText = 'Update';

                document.getElementById('inputId').value = data.id;
                document.getElementById('inputExistingImage').value = data.image_path;
                document.getElementById('inputLink').value = data.link;
                document.getElementById('inputTimer').value = data.timer;
                document.getElementById('inputStatus').value = data.status;

                // Show existing image
                if (data.image_path) {
                    previewContainer.classList.remove('d-none');
                    document.getElementById('imgPreview').src = '<?= BASE_URL ?>' + data.image_path;
                }
            } else {
                modalTitle.innerText = 'Tambah Pop-up Baru';
                inputAction.value = 'add';
                btnSave.innerText = 'Simpan';
                document.getElementById('inputTimer').value = 5000;
            }

            var myModal = new bootstrap.Modal(document.getElementById('modalPopup'));
            myModal.show();
        }

        function deletePopup(id) {
            if (confirm('Apakah Anda yakin ingin menghapus pop-up ini?')) {
                document.getElementById('deleteId').value = id;
                document.getElementById('formDelete').submit();
            }
        }
    </script>
</body>

</html>