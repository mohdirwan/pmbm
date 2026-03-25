<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$view_type = isset($_GET['type']) ? $_GET['type'] : '';
$type_label = '';
$column_name = '';

switch ($view_type) {
    case 'foto':
        $type_label = 'Pas Foto';
        $column_name = 'foto_siswa';
        $icon = 'fa-file-image text-info';
        break;
    case 'kk':
        $type_label = 'Kartu Keluarga';
        $column_name = 'file_kk';
        $icon = 'fa-file-pdf text-danger';
        break;
    case 'akta':
        $type_label = 'Akta Kelahiran';
        $column_name = 'file_akta';
        $icon = 'fa-file-contract text-warning';
        break;
    case 'prestasi':
    case 'sertifikat':
        $type_label = 'Sertifikat Prestasi';
        $column_name = 'file_sertifikat_prestasi';
        $icon = 'fa-award text-success';
        break;
    case 'nisn':
        $type_label = 'NISN';
        $column_name = 'file_nisn';
        $icon = 'fa-id-card text-primary';
        break;
    case 'rapor':
        $type_label = 'Rapor';
        $column_name = 'file_rapor';
        $icon = 'fa-book text-secondary';
        break;
    case 'rata':
        $type_label = 'SK Nilai Rata-Rata';
        $column_name = 'file_nilai_rata';
        $icon = 'fa-file-alt text-info';
        break;
    case 'ranking':
        $type_label = 'SK Ranking';
        $column_name = 'file_ranking';
        $icon = 'fa-trophy text-warning';
        break;
    case 'surat_prestasi':
        $type_label = 'Surat Prestasi';
        $column_name = 'file_surat_prestasi';
        $icon = 'fa-certificate text-danger';
        break;
    case 'surat_tahfidz':
        $type_label = 'Surat Tahfidz';
        $column_name = 'file_surat_tahfidz';
        $icon = 'fa-quran text-success';
        break;
    case 'sertifikat_tahfidz':
        $type_label = 'Sertifikat Tahfidz';
        $column_name = 'file_sertifikat_tahfidz';
        $icon = 'fa-star text-warning';
        break;
    case 'pakta':
        $type_label = 'Pakta Integritas';
        $column_name = 'file_pakta';
        $icon = 'fa-file-signature text-dark';
        break;
}

$documents = [];
if ($column_name) {
    $stmt = $pdo->query("SELECT id, no_pendaftaran, nama_lengkap, $column_name as file_path 
                         FROM pendaftar 
                         WHERE ($column_name IS NOT NULL AND $column_name != '') 
                         AND (no_pendaftaran IS NOT NULL AND no_pendaftaran != '')
                         ORDER BY id DESC");
    $documents = $stmt->fetchAll();
} else {
    // 1. Get all syarat from all jalur to determine which documents are active
    $stmt_all_syarat = $pdo->query("SELECT syarat, syarat_pilihan FROM jalur_pendaftaran");
    $all_syarat_rows = $stmt_all_syarat->fetchAll();
    
    $active_fields = ['foto_siswa']; // Pas Foto always active
    
    $fieldMappingLookup = [
        'pas foto' => 'foto_siswa',
        'rapor' => 'file_rapor',
        'surat keterangan tahfidz' => 'file_surat_tahfidz',
        'surat keterangan prestasi' => 'file_surat_prestasi',
        'nilai rata-rata' => 'file_nilai_rata',
        'rata rata nilai' => 'file_nilai_rata',
        'ranking' => 'file_ranking',
        'peringkat' => 'file_ranking',
        'sertifikat prestasi' => 'file_sertifikat_prestasi',
        'sertifikat tahfidz' => 'file_sertifikat_tahfidz',
        'kartu keluarga' => 'file_kk',
        'kk' => 'file_kk',
        'pakta integritas' => 'file_pakta',
        'akta' => 'file_akta',
        'nisn' => 'file_nisn'
    ];

    foreach ($all_syarat_rows as $row) {
        $combined_syarat = strtolower($row['syarat'] . ',' . $row['syarat_pilihan']);
        foreach ($fieldMappingLookup as $keyword => $field) {
            if (stripos($combined_syarat, $keyword) !== false) {
                if (!in_array($field, $active_fields)) {
                    $active_fields[] = $field;
                }
            }
        }
    }

    $total_pendaftar = $pdo->query("SELECT COUNT(*) FROM pendaftar WHERE (no_pendaftaran IS NOT NULL AND no_pendaftaran != '')")->fetchColumn();

    // Array of all document columns to check
    $all_possible_docs = [
        'foto_siswa' => ['type' => 'foto', 'label' => 'Pas Foto', 'icon' => 'fa-file-image text-info', 'bg' => 'bg-info', 'status' => 'Wajib'],
        'file_kk' => ['type' => 'kk', 'label' => 'Kartu Keluarga', 'icon' => 'fa-file-pdf text-danger', 'bg' => 'bg-danger', 'status' => 'Wajib'],
        'file_akta' => ['type' => 'akta', 'label' => 'Akta Kelahiran', 'icon' => 'fa-file-contract text-warning', 'bg' => 'bg-warning', 'status' => 'Wajib'],
        'file_nisn' => ['type' => 'nisn', 'label' => 'NISN', 'icon' => 'fa-id-card text-primary', 'bg' => 'bg-primary', 'status' => 'Wajib'],
        'file_rapor' => ['type' => 'rapor', 'label' => 'Rapor', 'icon' => 'fa-book text-secondary', 'bg' => 'bg-secondary', 'status' => 'Wajib'],
        'file_nilai_rata' => ['type' => 'rata', 'label' => 'SK Nilai Rata-Rata', 'icon' => 'fa-file-alt text-info', 'bg' => 'bg-info', 'status' => 'Wajib'],
        'file_ranking' => ['type' => 'ranking', 'label' => 'SK Ranking', 'icon' => 'fa-trophy text-warning', 'bg' => 'bg-warning', 'status' => 'Pilihan'],
        'file_surat_prestasi' => ['type' => 'surat_prestasi', 'label' => 'Surat Prestasi', 'icon' => 'fa-certificate text-danger', 'bg' => 'bg-danger', 'status' => 'Pilihan'],
        'file_sertifikat_prestasi' => ['type' => 'sertifikat', 'label' => 'Sertifikat Prestasi', 'icon' => 'fa-award text-success', 'bg' => 'bg-success', 'status' => 'Pilihan'],
        'file_surat_tahfidz' => ['type' => 'surat_tahfidz', 'label' => 'Surat Tahfidz', 'icon' => 'fa-quran text-success', 'bg' => 'bg-success', 'status' => 'Pilihan'],
        'file_sertifikat_tahfidz' => ['type' => 'sertifikat_tahfidz', 'label' => 'Sertifikat Tahfidz', 'icon' => 'fa-star text-warning', 'bg' => 'bg-warning', 'status' => 'Pilihan'],
        'file_pakta' => ['type' => 'pakta', 'label' => 'Pakta Integritas', 'icon' => 'fa-file-signature text-dark', 'bg' => 'bg-dark', 'status' => 'Wajib']
    ];

    $doc_counts = [];
    foreach ($all_possible_docs as $col => $meta) {
        // Only process if the field is active in at least one jalur
        if (!in_array($col, $active_fields)) continue;

        // Count uploaded
        $stmt_up = $pdo->prepare("SELECT COUNT(*) FROM pendaftar 
                               WHERE ($col IS NOT NULL AND $col != '') 
                               AND (no_pendaftaran IS NOT NULL AND no_pendaftaran != '')");
        $stmt_up->execute();
        $uploaded_count = $stmt_up->fetchColumn();

        // Fetch students who haven't uploaded (Missing)
        $stmt_miss = $pdo->prepare("SELECT id, no_pendaftaran, nama_lengkap 
                                FROM pendaftar 
                                WHERE ($col IS NULL OR $col = '') 
                                AND (no_pendaftaran IS NOT NULL AND no_pendaftaran != '')
                                ORDER BY nama_lengkap ASC");
        $stmt_miss->execute();
        $missing_list = $stmt_miss->fetchAll();

        $meta['count'] = $uploaded_count;
        $meta['total'] = $total_pendaftar;
        $meta['missing_count'] = count($missing_list);
        $meta['missing_list'] = $missing_list;
        $meta['column'] = $col;
        $doc_counts[] = $meta;
    }

    // Sort by missing_count ascending (least missing first)
    usort($doc_counts, function ($a, $b) {
        return $a['missing_count'] <=> $b['missing_count'];
    });
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Manajemen Dokumen - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #f4f7fe;
        }

        .main-content {
            margin-left: 260px;
            padding: 40px;
            min-height: 100vh;
        }

        .card-premium {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            background: #fff;
            transition: transform 0.3s;
        }

        .card-premium:hover {
            transform: translateY(-5px);
        }

        .doc-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .table-premium thead th {
            background: #f8faff;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            color: #666;
            padding: 15px;
            border: none;
        }

        .table-premium tbody td {
            padding: 15px;
            vertical-align: middle;
            border-color: #f1f4f9;
        }

        .img-preview {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-primary fw-bold mb-1"><i class="fas fa-folder-open me-2"></i>Manajemen Dokumen</h2>
                    <p class="text-muted small">Kelola dan verifikasi berkas pendaftaran murid secara terpusat.</p>
                </div>
                <?php if ($view_type): ?>
                    <a href="index.php" class="btn btn-light rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i> Kembali
                    </a>
                <?php endif; ?>
            </div>

            <?php if (!$view_type): ?>
                <div class="row g-4">
                    <?php if (empty($doc_counts)): ?>
                        <div class="col-12 text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 text-secondary"></i>
                            <p>Belum ada dokumen yang diunggah oleh siswa.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($doc_counts as $doc): ?>
                            <div class="col-md-3">
                                <div class="card card-premium p-4 text-center h-100">
                                    <div
                                        class="doc-icon <?= $doc['bg'] ?> bg-opacity-10 <?= str_replace('bg-', 'text-', $doc['bg']) ?> mx-auto position-relative">
                                        <i class="fas <?= explode(' ', $doc['icon'])[0] ?>"></i>
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill <?= $doc['status'] == 'Wajib' ? 'bg-danger' : 'bg-primary' ?>"
                                            style="font-size: 0.6rem;">
                                            <?= $doc['status'] ?>
                                        </span>
                                    </div>
                                    <h5 class="fw-bold mb-1"><?= $doc['label'] ?></h5>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">Progres:</span>
                                            <span class="fw-bold"><?= $doc['count'] ?> / <?= $doc['total'] ?></span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar <?= $doc['bg'] ?>" role="progressbar"
                                                style="width: <?= ($doc['total'] > 0 ? ($doc['count'] / $doc['total'] * 100) : 0) ?>%">
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($doc['missing_count'] > 0): ?>
                                        <button class="btn btn-danger btn-sm rounded-pill px-3 mb-2 fw-bold w-100"
                                            data-bs-toggle="modal" data-bs-target="#missingModal<?= $doc['column'] ?>">
                                            <i class="fas fa-exclamation-circle me-1"></i> Cek Data Tidak Lengkap
                                            (<?= $doc['missing_count'] ?>)
                                        </button>
                                    <?php else: ?>
                                        <div class="alert alert-success py-1 px-2 small mb-2 fw-bold rounded-pill"><i
                                                class="fas fa-check-circle me-1"></i> Data Lengkap</div>
                                    <?php endif; ?>

                                    <a href="?type=<?= $doc['type'] ?>"
                                        class="btn btn-outline-<?= str_replace('bg-', '', $doc['bg']) ?> rounded-pill w-100 mt-auto">Kelola</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Modals for Missing Students -->
                <?php foreach ($doc_counts as $doc): ?>
                    <div class="modal fade" id="missingModal<?= $doc['column'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0 rounded-4 shadow">
                                <div class="modal-header border-0 p-4 pb-0">
                                    <h5 class="fw-bold text-danger">Siswa Belum Unggah: <?= $doc['label'] ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="table-responsive" style="max-height: 400px;">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>No. Pendaftaran</th>
                                                    <th>Nama Lengkap</th>
                                                    <th class="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($doc['missing_list'] as $i => $m): ?>
                                                    <tr>
                                                        <td><?= $i + 1 ?></td>
                                                        <td><code><?= $m['no_pendaftaran'] ?></code></td>
                                                        <td class="fw-bold"><?= $m['nama_lengkap'] ?></td>
                                                        <td class="text-end">
                                                            <a href="../pendaftar/detail_pendaftar.php?id=<?= $m['id'] ?>"
                                                                class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                                Detail <i class="fas fa-arrow-right ms-1"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 p-4 pt-0">
                                    <button type="button" class="btn btn-light rounded-pill px-4"
                                        data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Document List View -->
                <div class="card card-premium border-0 overflow-hidden">
                    <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Daftar <?= $type_label ?></h5>
                        <span class="badge bg-primary rounded-pill px-3"><?= count($documents) ?> Berkas</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-premium mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">No</th>
                                    <th>Data Murid</th>
                                    <th>Preview / File</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($documents)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">Belum ada file diunggah untuk
                                            kategori ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($documents as $index => $doc):
                                        $file_name = $doc['file_path'];
                                        // Path for browser/frontend
                                        $browser_path = BASE_URL . 'uploads/' . $file_name;
                                        // Relative path back-up
                                        $relative_path = '../../uploads/' . $file_name;

                                        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                                        $is_img = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($doc['nama_lengkap']) ?></div>
                                                <div class="small text-muted"><?= $doc['no_pendaftaran'] ?></div>
                                            </td>
                                            <td>
                                                <?php if ($is_img): ?>
                                                    <img src="<?= $browser_path ?>" class="img-preview" alt="Preview"
                                                        data-bs-toggle="modal" data-bs-target="#imgModal<?= $doc['id'] ?>"
                                                        onerror="this.src='<?= $relative_path ?>'; this.onerror=null;">
                                                    <!-- Modal Preview -->
                                                    <div class="modal fade" id="imgModal<?= $doc['id'] ?>" tabindex="-1"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content border-0 bg-transparent">
                                                                <div class="modal-body p-0 text-center">
                                                                    <img src="<?= $browser_path ?>"
                                                                        class="img-fluid rounded-4 shadow-lg"
                                                                        onerror="this.src='<?= $relative_path ?>'; this.onerror=null;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark border"><i
                                                            class="fas fa-file-pdf me-1 text-danger"></i> Dokumen PDF</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="<?= $browser_path ?>" target="_blank"
                                                    class="btn btn-sm btn-light text-primary rounded-pill px-3">
                                                    <i class="fas fa-external-link-alt me-1"></i> Buka
                                                </a>
                                                <a href="../pendaftar/detail_pendaftar.php?id=<?= $doc['id'] ?>"
                                                    class="btn btn-sm btn-light text-dark rounded-pill px-3 ms-1">
                                                    <i class="fas fa-user me-1"></i> Profil
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>