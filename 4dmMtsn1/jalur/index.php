<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$message = '';

// Handle DELETE (changed from GET to POST for security)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM jalur_pendaftaran WHERE id = ?");
        $stmt->execute([$id]);
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>Jalur berhasil dihapus!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>Gagal menghapus: ' . $e->getMessage() . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
}


// Handle ADD & EDIT (only if not deleting)
elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $nama_jalur = $_POST['nama_jalur'];
    $syarat = $_POST['syarat'];
    $syarat_pilihan = $_POST['syarat_pilihan'] ?? '';

    try {
        if ($id) {
            // UPDATE
            $stmt = $pdo->prepare("UPDATE jalur_pendaftaran SET nama_jalur = ?, syarat = ?, syarat_pilihan = ? WHERE id = ?");
            $stmt->execute([$nama_jalur, $syarat, $syarat_pilihan, $id]);
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>Jalur berhasil diupdate!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>';
        } else {
            // INSERT
            $stmt = $pdo->prepare("INSERT INTO jalur_pendaftaran (nama_jalur, syarat, syarat_pilihan) VALUES (?, ?, ?)");
            $stmt->execute([$nama_jalur, $syarat, $syarat_pilihan]);
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>Jalur berhasil ditambahkan!
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
$jalur = $pdo->query("SELECT * FROM jalur_pendaftaran ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Jalur Pendaftaran - Admin PMBM</title>
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
            <h2 class="mb-4 text-primary fw-bold"><i class="fas fa-road me-2"></i>Jalur Pendaftaran</h2>

            <?= $message ?>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Daftar Jalur</h6>
                    <button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal"
                        data-bs-target="#modalJalur" onclick="resetForm()">
                        <i class="fas fa-plus me-2"></i>Tambah Jalur
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Nama Jalur</th>
                                    <th>Syarat Khusus</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($jalur) > 0): ?>
                                    <?php foreach ($jalur as $index => $j): ?>
                                        <tr>
                                            <td class="ps-4"><?= $index + 1 ?></td>
                                            <td class="fw-bold"><?= htmlspecialchars($j['nama_jalur']) ?></td>
                                            <td class="small">
                                                <div class="syarat-container">
                                                    <?php
                                                    if (!empty($j['syarat'])) {
                                                        $syarat_list = explode(',', $j['syarat']);
                                                        echo '<div class="d-flex flex-wrap gap-1">';
                                                        foreach ($syarat_list as $s) {
                                                            $trimmed = trim($s);
                                                            $is_wajib = stripos($trimmed, '(wajib)') !== false;
                                                            $is_pilihan = stripos($trimmed, '(pilihan)') !== false;

                                                            // Clean text for display
                                                            $display_text = preg_replace('/\s*\(.*?\)\s*/', '', $trimmed);
                                                            $display_text = str_ireplace('/n', '<br>', $display_text);

                                                            $badge_class = 'bg-light text-dark border';
                                                            $label_status = '';

                                                            if ($is_wajib) {
                                                                $badge_class = 'bg-danger-subtle text-danger border border-danger-subtle';
                                                                $label_status = ' <small class="fw-bold">[WAJIB]</small>';
                                                            } elseif ($is_pilihan) {
                                                                $badge_class = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                                                                $label_status = ' <small class="fw-bold">[PILIHAN]</small>';
                                                            }

                                                            echo '<span class="badge ' . $badge_class . ' py-2 px-3 rounded-pill mb-1" style="font-weight: 500;">';
                                                            echo '<i class="fas ' . ($is_wajib ? 'fa-exclamation-circle' : 'fa-check-circle') . ' me-1"></i>';
                                                            echo htmlspecialchars($display_text) . $label_status;
                                                            echo '</span>';
                                                        }
                                                        echo '</div>';
                                                    }
                                                    ?>
                                                </div>
                                                <?php if (!empty($j['syarat_pilihan'])): ?>
                                                    <div class="mt-2 pt-2 border-top small text-muted">Dokumen Pilihan:</div>
                                                    <div class="syarat-container">
                                                        <?php
                                                        $syarat_pilih_list = explode(',', $j['syarat_pilihan']);
                                                        echo '<div class="d-flex flex-wrap gap-1">';
                                                        foreach ($syarat_pilih_list as $s) {
                                                            $display_text = trim($s);
                                                            echo '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-2 px-3 rounded-pill mb-1" style="font-weight: 500;">';
                                                            echo '<i class="fas fa-check-circle me-1"></i>';
                                                            echo htmlspecialchars($display_text) . ' <small class="fw-bold">[PILIHAN]</small>';
                                                            echo '</span>';
                                                        }
                                                        echo '</div>';
                                                        ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <button class="btn btn-sm btn-outline-warning"
                                                    onclick="editJalur(<?= $j['id'] ?>, '<?= htmlspecialchars($j['nama_jalur'], ENT_QUOTES) ?>', '<?= htmlspecialchars($j['syarat'], ENT_QUOTES) ?>', '<?= htmlspecialchars($j['syarat_pilihan'] ?? '', ENT_QUOTES) ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteJalur(<?= $j['id'] ?>, '<?= htmlspecialchars($j['nama_jalur'], ENT_QUOTES) ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            Belum ada jalur pendaftaran
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

    <!-- Modal Tambah/Edit Jalur -->
    <div class="modal fade" id="modalJalur" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Jalur Pendaftaran
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="jalur_id">

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-road me-1 text-primary"></i>
                                Nama Jalur <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="nama_jalur" id="nama_jalur" required
                                placeholder="Contoh: Jalur Akademik">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-list-ul me-1 text-warning"></i>
                                Syarat Khusus
                            </label>
                            <textarea class="form-control" name="syarat" id="syarat" rows="5"
                                placeholder="Pisahkan setiap syarat dengan koma (,)&#10;Contoh: Pas Foto,Rapor Asli,Surat Keterangan Nilai Rata-rata"></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Pisahkan setiap syarat dengan koma (,)
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-list-ul me-1 text-warning"></i>
                                Syarat Pilihan (Opsional)
                            </label>
                            <textarea class="form-control" name="syarat_pilihan" id="syarat_pilihan" rows="3"
                                placeholder="Contoh: Sertifikat Tahfidz, Piagam Juara (Kosongkan jika tidak ada)"></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Dokumen yang tidak wajib tapi bisa menambah nilai/pertimbangan.
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
            document.getElementById('jalur_id').value = '';
            document.getElementById('nama_jalur').value = '';
            document.getElementById('syarat').value = '';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Tambah Jalur Pendaftaran';
        }

        function editJalur(id, nama, syarat, syarat_pilihan) {
            document.getElementById('jalur_id').value = id;
            document.getElementById('nama_jalur').value = nama;
            document.getElementById('syarat').value = syarat;
            document.getElementById('syarat_pilihan').value = syarat_pilihan;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Jalur Pendaftaran';

            var modal = new bootstrap.Modal(document.getElementById('modalJalur'));
            modal.show();
        }

        function deleteJalur(id, nama) {
            if (confirm('Apakah Anda yakin ingin menghapus jalur "' + nama + '"?\n\nData yang terhapus tidak dapat dikembalikan!')) {
                // Create a form and submit it
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