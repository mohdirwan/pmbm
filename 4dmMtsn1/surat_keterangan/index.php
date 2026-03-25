<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$message = '';

// Handle DELETE (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM surat_keterangan WHERE id = ?");
        $stmt->execute([$id]);
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>Surat keterangan berhasil dihapus!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>Gagal menghapus: ' . $e->getMessage() . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
}

// Handle ADD & EDIT
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['action'])) {
    $id = $_POST['id'] ?? null;
    $nama_surat = clean_input($_POST['nama_surat']);
    $keterangan = clean_input($_POST['keterangan']);
    $urutan = intval($_POST['urutan']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    try {
        if ($id) {
            // UPDATE
            $stmt = $pdo->prepare("UPDATE surat_keterangan SET nama_surat = ?, keterangan = ?, urutan = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$nama_surat, $keterangan, $urutan, $is_active, $id]);
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>Surat keterangan berhasil diupdate!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>';
        } else {
            // INSERT
            $stmt = $pdo->prepare("INSERT INTO surat_keterangan (nama_surat, keterangan, urutan, is_active) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nama_surat, $keterangan, $urutan, $is_active]);
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>Surat keterangan berhasil ditambahkan!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>';
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>Gagal menyimpan: ' . $e->getMessage() . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
}

// Get Data
$surat_list = $pdo->query("SELECT * FROM surat_keterangan ORDER BY urutan ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .btn-dark.d-lg-none {
            z-index: 1060 !important;
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
                        <i class="fas fa-file-alt me-2"></i>Surat Keterangan
                    </h2>
                    <p class="text-muted">Kelola template surat keterangan untuk calon peserta didik</p>
                </div>
                <a href="<?= BASE_URL ?>surat_keterangan.php" target="_blank"
                    class="btn btn-outline-success rounded-pill">
                    <i class="fas fa-external-link-alt me-2"></i>Lihat Halaman Public
                </a>
            </div>

            <?= $message ?>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Daftar Surat Keterangan</h6>
                    <button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal"
                        data-bs-target="#modalSurat" onclick="resetForm()">
                        <i class="fas fa-plus me-2"></i>Tambah Surat Keterangan
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Nama Surat</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">Urutan</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Files</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($surat_list) > 0): ?>
                                    <?php foreach ($surat_list as $index => $s): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <?= $index + 1 ?>
                                            </td>
                                            <td class="fw-bold">
                                                <?= htmlspecialchars($s['nama_surat']) ?>
                                            </td>
                                            <td class="small">
                                                <?= htmlspecialchars($s['keterangan']) ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary">
                                                    <?= $s['urutan'] ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($s['is_active']): ?>
                                                    <span class="badge bg-success rounded-pill">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary rounded-pill">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <?php if (!empty($s['file_preview_pdf'])): ?>
                                                        <span class="badge bg-danger" title="PDF tersedia">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary" title="PDF belum diupload">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </span>
                                                    <?php endif; ?>

                                                    <?php if (!empty($s['file_template_docx'])): ?>
                                                        <span class="badge bg-primary" title="DOCX tersedia">
                                                            <i class="fas fa-file-word"></i>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary" title="DOCX belum diupload">
                                                            <i class="fas fa-file-word"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="upload_files.php?id=<?= $s['id'] ?>"
                                                    class="btn btn-sm btn-outline-success" title="Upload Files">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>preview_suket.php?id=<?= $s['id'] ?>" target="_blank"
                                                    class="btn btn-sm btn-outline-info" title="Preview">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-warning"
                                                    onclick="editSurat(<?= $s['id'] ?>, '<?= htmlspecialchars($s['nama_surat'], ENT_QUOTES) ?>', '<?= htmlspecialchars($s['keterangan'], ENT_QUOTES) ?>', <?= $s['urutan'] ?>, <?= $s['is_active'] ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteSurat(<?= $s['id'] ?>, '<?= htmlspecialchars($s['nama_surat'], ENT_QUOTES) ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            Belum ada surat keterangan
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit -->
    <div class="modal fade" id="modalSurat" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Surat Keterangan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="surat_id">

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-file-alt me-1 text-primary"></i>
                                Nama Surat <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="nama_surat" id="nama_surat" required
                                placeholder="Contoh: Surat Keterangan PRESTASI">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-info-circle me-1 text-info"></i>
                                Keterangan
                            </label>
                            <textarea class="form-control" name="keterangan" id="keterangan" rows="3"
                                placeholder="Deskripsi singkat tentang surat keterangan ini"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-sort-numeric-up me-1 text-success"></i>
                                        Urutan Tampil
                                    </label>
                                    <input type="number" class="form-control" name="urutan" id="urutan" value="0"
                                        min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold d-block">
                                        <i class="fas fa-toggle-on me-1 text-warning"></i>
                                        Status
                                    </label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                            checked>
                                        <label class="form-check-label" for="is_active">
                                            Aktif (Tampil di halaman public)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('surat_id').value = '';
            document.getElementById('nama_surat').value = '';
            document.getElementById('keterangan').value = '';
            document.getElementById('urutan').value = '0';
            document.getElementById('is_active').checked = true;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Tambah Surat Keterangan';
        }

        function editSurat(id, nama, keterangan, urutan, is_active) {
            document.getElementById('surat_id').value = id;
            document.getElementById('nama_surat').value = nama;
            document.getElementById('keterangan').value = keterangan;
            document.getElementById('urutan').value = urutan;
            document.getElementById('is_active').checked = is_active == 1;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Surat Keterangan';

            var modal = new bootstrap.Modal(document.getElementById('modalSurat'));
            modal.show();
        }

        function deleteSurat(id, nama) {
            if (confirm('Apakah Anda yakin ingin menghapus "' + nama + '"?\n\nData yang terhapus tidak dapat dikembalikan!')) {
                // Create form and submit
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

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
    </script>
</body>

</html>