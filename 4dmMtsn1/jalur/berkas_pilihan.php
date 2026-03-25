<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$message = '';

// Handle Save
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $petunjuk_baris = $_POST['petunjuk_baris'] ?? [];

    // Filter empty lines
    $petunjuk_baris = array_values(array_filter(array_map('trim', $petunjuk_baris)));

    // Save as JSON in settings
    $json_value = json_encode($petunjuk_baris, JSON_UNESCAPED_UNICODE);

    try {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('berkas_pilihan_petunjuk', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$json_value, $json_value]);

        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>Kata-kata berkas pilihan berhasil disimpan!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>Gagal menyimpan: ' . $e->getMessage() . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
}

// Get current settings
$raw = get_setting('berkas_pilihan_petunjuk', '');
$petunjuk_list = [];
if (!empty($raw)) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $petunjuk_list = $decoded;
    }
}

// Default text if empty
if (empty($petunjuk_list)) {
    $petunjuk_list = [
        'Bagi calon murid yang menempati <strong>peringkat/ranking</strong>, silahkan upload <strong>surat keterangan peringkat/ranking</strong>.',
        'Bagi calon murid yang memiliki <strong>sertifikat prestasi</strong>, silahkan upload <strong>surat keterangan prestasi</strong> dan <strong>sertifikat prestasi</strong>.',
        'Bagi calon murid yang menempati peringkat dan memiliki sertifikat prestasi, silahkan upload <strong>suket peringkat/ranking, upload suket prestasi</strong> dan <strong>sertifikat prestasi</strong>.',
    ];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Atur Kata-kata Berkas Pilihan - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .btn-dark.d-lg-none {
            z-index: 1060 !important;
        }

        .petunjuk-row {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            transition: box-shadow 0.2s;
        }

        .petunjuk-row:hover {
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .drag-handle {
            color: #adb5bd;
            cursor: grab;
            padding-top: 4px;
            font-size: 1.1rem;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .row-number {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 600;
            min-width: 20px;
            padding-top: 5px;
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-primary mb-0">
                        <i class="fas fa-align-left me-2"></i>Atur Kata-kata Berkas Pilihan
                    </h2>
                    <p class="text-muted small mb-0 mt-1">
                        Kelola petunjuk yang tampil di bagian <strong>"Berkas Pilihan (Minimal Unggah 1)"</strong> pada
                        halaman pendaftaran.
                    </p>
                </div>
            </div>

            <?= $message ?>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div
                            class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-list-ul me-2 text-primary"></i>Baris-baris Petunjuk
                            </h6>
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill"
                                onclick="addRow()">
                                <i class="fas fa-plus me-1"></i>Tambah Baris
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info border-0 rounded-3 mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Tips:</strong> Anda bisa menggunakan tag HTML seperti
                                <code>&lt;strong&gt;</code> untuk teks <strong>tebal</strong> dan
                                <code>&lt;em&gt;</code> untuk teks <em>miring</em>.
                                Seret baris menggunakan ikon <i class="fas fa-grip-vertical"></i> untuk mengubah urutan.
                            </div>

                            <form method="POST" id="formPetunjuk">
                                <div id="petunjuk-container">
                                    <?php foreach ($petunjuk_list as $index => $baris): ?>
                                        <div class="petunjuk-row" draggable="true">
                                            <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
                                            <span class="row-number">
                                                <?= $index + 1 ?>.
                                            </span>
                                            <textarea class="form-control" name="petunjuk_baris[]" rows="2"
                                                placeholder="Ketikkan petunjuk di sini..."><?= htmlspecialchars($baris) ?></textarea>
                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="removeRow(this)" title="Hapus baris">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="mt-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-success px-4 rounded-pill">
                                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                                    </button>
                                    <a href="index.php" class="btn btn-outline-secondary px-4 rounded-pill">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Jalur
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Preview Panel -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                        <div class="card-header bg-primary text-white py-3 rounded-top-4">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-eye me-2"></i>Preview Tampilan
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">Beginilah tampilan yang akan dilihat calon pendaftar:</p>
                            <div class="border rounded-3 p-3 bg-light">
                                <p class="small fw-bold mb-2">
                                    <i class="fas fa-info-circle me-1 text-primary"></i>
                                    Petunjuk Upload Berkas Pilihan:
                                </p>
                                <ul class="small mb-0" id="preview-list">
                                    <li>Memuat preview...</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add new row
        function addRow() {
            const container = document.getElementById('petunjuk-container');
            const count = container.querySelectorAll('.petunjuk-row').length + 1;
            const div = document.createElement('div');
            div.className = 'petunjuk-row';
            div.draggable = true;
            div.innerHTML = `
                <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
                <span class="row-number">${count}.</span>
                <textarea class="form-control" name="petunjuk_baris[]" rows="2" placeholder="Ketikkan petunjuk di sini..."></textarea>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)" title="Hapus baris">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            attachDragEvents(div);
            container.appendChild(div);
            updateNumbers();
            updatePreview();
            div.querySelector('textarea').focus();
        }

        // Remove row
        function removeRow(btn) {
            const row = btn.closest('.petunjuk-row');
            const container = document.getElementById('petunjuk-container');
            if (container.querySelectorAll('.petunjuk-row').length <= 1) {
                alert('Minimal harus ada satu baris petunjuk.');
                return;
            }
            row.remove();
            updateNumbers();
            updatePreview();
        }

        // Update row numbers
        function updateNumbers() {
            document.querySelectorAll('#petunjuk-container .petunjuk-row').forEach((row, i) => {
                const num = row.querySelector('.row-number');
                if (num) num.textContent = (i + 1) + '.';
            });
        }

        // Update live preview
        function updatePreview() {
            const textareas = document.querySelectorAll('#petunjuk-container textarea');
            const previewList = document.getElementById('preview-list');
            let html = '';
            textareas.forEach(ta => {
                const val = ta.value.trim();
                if (val) {
                    html += `<li class="mb-1">${val}</li>`;
                }
            });
            previewList.innerHTML = html || '<li class="text-muted">Belum ada petunjuk.</li>';
        }

        // Drag & Drop reordering
        let dragSrc = null;

        function attachDragEvents(el) {
            el.addEventListener('dragstart', function (e) {
                dragSrc = this;
                e.dataTransfer.effectAllowed = 'move';
                this.style.opacity = '0.5';
            });
            el.addEventListener('dragend', function () {
                this.style.opacity = '1';
                updateNumbers();
                updatePreview();
            });
            el.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                this.style.borderColor = '#0d6efd';
            });
            el.addEventListener('dragleave', function () {
                this.style.borderColor = '#dee2e6';
            });
            el.addEventListener('drop', function (e) {
                e.stopPropagation();
                this.style.borderColor = '#dee2e6';
                if (dragSrc !== this) {
                    const container = document.getElementById('petunjuk-container');
                    const rows = Array.from(container.querySelectorAll('.petunjuk-row'));
                    const srcIdx = rows.indexOf(dragSrc);
                    const dstIdx = rows.indexOf(this);
                    if (srcIdx < dstIdx) {
                        container.insertBefore(dragSrc, this.nextSibling);
                    } else {
                        container.insertBefore(dragSrc, this);
                    }
                }
            });
        }

        // Init on load
        document.addEventListener('DOMContentLoaded', function () {
            const rows = document.querySelectorAll('.petunjuk-row');
            rows.forEach(attachDragEvents);

            // Live preview on type
            document.getElementById('petunjuk-container').addEventListener('input', updatePreview);

            updatePreview(); // Initial
        });
    </script>
</body>

</html>