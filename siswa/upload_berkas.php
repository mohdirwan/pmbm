<?php
$page_title = "Upload Berkas Persyaratan";
require_once 'layout_top.php';

// Security Check: Only allow if not verified/accepted and PPDB is open
$ppdb_status = get_setting('ppdb_status', 'tutup');
$status = $siswa['status'] ?? 'Pending';
// Security Check Enhancement for Reupload
$allowed_stages = ['buka', 'verifikasi', 'pengumuman_adm'];
if ($status == 'Terverifikasi' || $status == 'Diterima' || !in_array($ppdb_status, $allowed_stages)) {
    // If rejected, they should still be allowed to upload during verification/announcement stages
    if ($status != 'Ditolak' || !in_array($ppdb_status, ['verifikasi', 'pengumuman_adm'])) {
        header("Location: dashboard.php");
        exit();
    }
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_upload'])) {
    $uploadDir = '../uploads/';
    $field = $_POST['field_name'];
    $isPhoto = ($field === 'foto_siswa');
    $allowedTypes = $isPhoto ? ['image/jpeg', 'image/png'] : ['application/pdf'];

    if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] == 0) {
        $fileType = $_FILES['file_upload']['type'];
        $fileSize = $_FILES['file_upload']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            $formatNeeded = $isPhoto ? 'JPG atau PNG' : 'PDF';
            $message = "<div class='alert alert-danger border-0 shadow-sm rounded-4'>Format file tidak didukung. Untuk berkas ini gunakan format $formatNeeded.</div>";
        } elseif ($fileSize > 2097152) { // 2MB
            $message = "<div class='alert alert-danger border-0 shadow-sm rounded-4'>Ukuran file terlalu besar. Maksimal 2MB.</div>";
        } else {
            $extension = pathinfo($_FILES['file_upload']['name'], PATHINFO_EXTENSION);
            $fileName = time() . '_' . $field . '_' . $_SESSION['siswa_id'] . '.' . $extension;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $targetPath)) {
                // Delete old file if exists (Overwrite Logic)
                $stmt_old = $pdo->prepare("SELECT $field FROM pendaftar WHERE id = ?");
                $stmt_old->execute([$_SESSION['siswa_id']]);
                $oldFile = $stmt_old->fetchColumn();
                if ($oldFile && file_exists($uploadDir . $oldFile)) {
                    unlink($uploadDir . $oldFile);
                }

                // Update file and reset status to Pending if it was Rejected
                $stmt = $pdo->prepare("UPDATE pendaftar SET $field = ?, status = 'Pending' WHERE id = ?");
                $stmt->execute([$fileName, $_SESSION['siswa_id']]);

                $message = "<div class='alert alert-success border-0 shadow-sm rounded-4'><i class='fas fa-check-circle me-2'></i> Berkas berhasil diunggah!</div>";
                // Refresh student data
                $stmt = $pdo->prepare("SELECT * FROM pendaftar WHERE id = ?");
                $stmt->execute([$_SESSION['siswa_id']]);
                $siswa = $stmt->fetch();
            } else {
                $message = "<div class='alert alert-danger border-0 shadow-sm rounded-4'>Gagal mengunggah berkas ke server.</div>";
            }
        }
    }
}

// Get jalur pendaftaran info with syarat
$stmt_jalur = $pdo->prepare("SELECT jp.* FROM pendaftar p 
                               LEFT JOIN jalur_pendaftaran jp ON p.jalur_id = jp.id 
                               WHERE p.id = ?");
$stmt_jalur->execute([$_SESSION['siswa_id']]);
$jalur_data = $stmt_jalur->fetch();
$nama_jalur = $jalur_data['nama_jalur'] ?? '';
$syarat_text = $jalur_data['syarat'] ?? '';

// Field mapping - maps requirement keywords to database fields
// ORDER MATTERS: More specific keywords should come first
$field_mapping = [
    // Photos & Basic Docs  
    'pas foto' => ['field' => 'foto_siswa', 'label' => 'Pas Foto 3x4'],
    'rapor' => ['field' => 'file_rapor', 'label' => 'Rapor Asli'],

    // Surat Keterangan - Specific first
    'surat keterangan tahfidz' => ['field' => 'file_surat_tahfidz', 'label' => 'Surat Keterangan Tahfidz'],
    'surat keterangan prestasi' => ['field' => 'file_surat_prestasi', 'label' => 'Surat Keterangan Prestasi'],
    'nilai rata-rata' => ['field' => 'file_nilai_rata', 'label' => 'Surat Keterangan Nilai Rata-rata'],
    'rata rata nilai' => ['field' => 'file_nilai_rata', 'label' => 'Surat Keterangan Rata-rata Nilai'],
    'peringkat' => ['field' => 'file_ranking', 'label' => 'Surat Keterangan Peringkat'],
    'ranking' => ['field' => 'file_ranking', 'label' => 'Surat Keterangan Ranking'],

    // Sertifikat - Check full phrase first
    'sertifikat prestasi akademik' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertifikat Prestasi Akademik'],
    'sertifikat prestasi non-akademik' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertifikat Prestasi Non-Akademik'],
    'sertifikat prestasi' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertifikat Prestasi'],
    'sertifikat tahfidz' => ['field' => 'file_sertifikat_tahfidz', 'label' => 'Sertifikat Tahfidz'],

    // Fallbacks for achievement types
    'prestasi akademik' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertifikat Prestasi Akademik'],
    'prestasi non akademik' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertifikat Prestasi Non-Akademik'],
    'tahfidz' => ['field' => 'file_sertifikat_tahfidz', 'label' => 'Sertifikat Tahfidz'],

    // ID Documents
    'kartu keluarga' => ['field' => 'file_kk', 'label' => 'Kartu Keluarga'],
    'kk' => ['field' => 'file_kk', 'label' => 'Kartu Keluarga (KK)'],

    // Pakta Integritas
    'pakta integritas' => ['field' => 'file_pakta', 'label' => 'Pakta Integritas'],

    'akta' => ['field' => 'file_akta', 'label' => 'Akta Kelahiran'],

    // NISN
    'nisn' => ['field' => 'file_nisn', 'label' => 'Print Out NISN'],

    // Generic / Persyaratan Tambahan
    'persyaratan' => ['field' => 'file_persyaratan', 'label' => 'Persyaratan'],
    'aktual' => ['field' => 'file_persyaratan', 'label' => 'Persyaratan Aktual']
];

// Parse syarat from database and build dynamic docs array
$docs = [];
$added_fields = []; // Track already added fields to avoid duplicates

// DEBUG: Uncomment these lines to see matching process
// echo "<!-- DEBUG: Syarat Text: " . htmlspecialchars($syarat_text) . " -->\n";

if (!empty($syarat_text)) {
    // Split by comma
    $syarat_list = array_map('trim', explode(',', $syarat_text));

    // DEBUG
    // echo "<!-- DEBUG: Total Syarat: " . count($syarat_list) . " -->\n";

    foreach ($syarat_list as $syarat) {
        // Remove details in parentheses for matching
        $syarat_clean = preg_replace('/\s*\(.*?\)\s*/', '', $syarat);

        // Remove standalone numbers at the end (e.g., "Akta Kelahiran 7" -> "Akta Kelahiran")
        $syarat_clean = preg_replace('/\s+\d+\s*$/', '', $syarat_clean);

        // Clean extra whitespace
        $syarat_clean = trim(preg_replace('/\s+/', ' ', $syarat_clean));

        $syarat_lower = strtolower($syarat_clean);

        // DEBUG
        // echo "<!-- DEBUG: Processing: '$syarat' --> Clean: '$syarat_clean' -->\n";

        // Check for (wajib) or (pilihan) suffix
        $is_required = stripos($syarat, '(wajib)') !== false;
        $is_optional = stripos($syarat, '(pilihan)') !== false;
        $suffix = $is_required ? ' <span class="badge bg-danger-subtle text-danger" style="font-size: 0.65rem;">WAJIB</span>' : ($is_optional ? ' <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">OPSIONAL</span>' : '');

        // Try to match with field mapping
        $matched = false;
        foreach ($field_mapping as $keyword => $mapping) {
            if (stripos($syarat_lower, $keyword) !== false) {
                // Avoid duplicate fields
                if (!in_array($mapping['field'], $added_fields)) {
                    $display_label = str_ireplace('/n', '<br>', $syarat_clean);
                    $docs[] = [
                        'label' => $display_label . $suffix,
                        'field' => $mapping['field']
                    ];
                    $added_fields[] = $mapping['field'];
                    $matched = true;

                    // DEBUG
                    // echo "<!-- DEBUG: MATCHED '$syarat' with keyword '$keyword' --> Field: {$mapping['field']} -->\n";
                    break;
                }
            }
        }

        // DEBUG unmatched
        // if (!$matched) {
        //     echo "<!-- DEBUG: NOT MATCHED: '$syarat' -->\n";
        // }
    }
}

// If no docs from database (empty syarat or no jalur), use default minimal set
if (empty($docs)) {
    $docs = [
        ['label' => 'Pas Foto', 'field' => 'foto_siswa'],
        ['label' => 'Rapor Asli', 'field' => 'file_rapor'],
        ['label' => 'Kartu Keluarga', 'field' => 'file_kk'],
        ['label' => 'Akta Kelahiran', 'field' => 'file_akta']
    ];
}
?>

<div class="row">
    <div class="col-12">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-file-upload me-2"></i> Data Berkas Anda</h5>

            <?php if ($status == 'Ditolak' && !empty($siswa['catatan_admin'])): ?>
                <div class="alert alert-danger border-0 rounded-4 mb-4 shadow-sm">
                    <div class="d-flex">
                        <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1"></i>
                        <div>
                            <div class="fw-bold fs-5">Perbaikan Berkas Diperlukan!</div>
                            <div class="mt-1"><?= nl2br(htmlspecialchars($siswa['catatan_admin'])) ?></div>
                            <div class="small mt-2 opacity-75">*Silakan unggah ulang berkas yang diminta di bawah ini.</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($nama_jalur): ?>
                <div class="alert alert-primary border-0 rounded-4 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-route fa-2x me-3"></i>
                        <div>
                            <div class="fw-bold mb-1">Jalur Pendaftaran Anda:</div>
                            <div class="fs-5 fw-semibold text-dark"><?= htmlspecialchars($nama_jalur) ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?= $message ?>

            <div class="table-responsive">
                <table class="table table-hover border-light align-middle">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="py-3 ps-4" style="width: 5%;">No</th>
                            <th class="py-3" style="width: 30%;">Deskripsi Berkas</th>
                            <th class="py-3" style="width: 30%;">Nama File</th>
                            <th class="py-3 text-center" style="width: 15%;">Status</th>
                            <th class="py-3 text-center" style="width: 20%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($docs as $doc): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                <td class="ps-2 py-3 fw-medium text-dark"><?= $doc['label'] ?></td>
                                <td class="py-3">
                                    <?php if (isset($siswa[$doc['field']]) && $siswa[$doc['field']]): ?>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-alt text-primary me-2"></i>
                                            <span class="small text-truncate" style="max-width: 200px;"
                                                title="<?= $siswa[$doc['field']] ?>">
                                                <?= $siswa[$doc['field']] ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small italic">Belum ada file</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center py-3">
                                    <?php if (isset($siswa[$doc['field']]) && $siswa[$doc['field']]): ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3">
                                            <i class="fas fa-check-circle me-1"></i> Terunggah
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3">
                                            <i class="fas fa-times-circle me-1"></i> Belum
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center py-3">
                                    <div class="btn-group btn-group-sm">
                                        <?php if (isset($siswa[$doc['field']]) && $siswa[$doc['field']]): ?>
                                            <a href="../uploads/<?= $siswa[$doc['field']] ?>" target="_blank"
                                                class="btn btn-info text-white" title="Preview">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-warning text-white" data-bs-toggle="modal"
                                                data-bs-target="#modal_<?= $doc['field'] ?>" title="Upload Ulang">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary px-3" data-bs-toggle="modal"
                                                data-bs-target="#modal_<?= $doc['field'] ?>">
                                                <i class="fas fa-upload me-1"></i> Upload
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info border-0 rounded-4 mt-4 mb-0 small">
                <div class="d-flex align-items-start">
                    <i class="fas fa-info-circle mt-1 me-3 text-primary fs-5"></i>
                    <div>
                        <div class="fw-bold mb-1">Catatan:</div>
                        Pastikan dokumen yang diunggah terlihat jelas dan tidak buram. Status <strong>Centang
                            Hijau</strong> menandakan sistem telah menerima berkas Anda. Panitia akan memverifikasi
                        berkas tersebut kemudian.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php foreach ($docs as $doc): ?>
    <!-- Modal Upload -->
    <div class="modal fade" id="modal_<?= $doc['field'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary-subtle text-primary p-2 rounded-3 me-3">
                            <i class="fas fa-file-upload"></i>
                        </div>
                        <h5 class="modal-title fw-bold">Upload <?= strip_tags($doc['label']) ?></h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body py-4 px-4">
                        <input type="hidden" name="field_name" value="<?= $doc['field'] ?>">
                        <?php $isPhotoModal = ($doc['field'] === 'foto_siswa'); ?>
                        <div class="p-3 bg-light rounded-4 mb-3">
                            <label class="form-label-premium mb-2">Pilih file (<?= $isPhotoModal ? 'JPG/PNG' : 'PDF' ?>, Max
                                2MB)</label>
                            <input type="file" name="file_upload" class="form-control form-control-premium"
                                accept="<?= $isPhotoModal ? '.jpg,.jpeg,.png' : '.pdf' ?>" required>
                            <?php if ($doc['field'] === 'foto_siswa'): ?>
                                <div class="mt-3 p-3 bg-white rounded-3 border d-flex align-items-center">
                                    <img src="../assets/img/contoh_siswa_merah.png" class="rounded-2 shadow-sm me-3"
                                        style="width: 70px; height: 93px; object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-success small"><i class="fas fa-check-circle me-1"></i>Contoh
                                            Foto Benar</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            • Latar belakang merah<br>
                                            • Menggunakan seragam sekolah<br>
                                            • Menghadap tepat ke depan<br>
                                            • Kualitas foto jernih/tajam
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($doc['field'] === 'file_nisn'): ?>
                                <div class="mt-3 p-3 bg-white rounded-3 border">
                                    <div class="d-flex flex-column">
                                        <div class="fw-bold text-primary small mb-2"><i
                                                class="fas fa-info-circle me-1"></i>Contoh Printout NISN</div>
                                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseContohNISN_<?= $doc['field'] ?>" aria-expanded="false"
                                            aria-controls="collapseContohNISN_<?= $doc['field'] ?>">
                                            <i class="fas fa-image me-1"></i> Lihat Contoh Gambar
                                        </button>
                                        <div class="collapse mt-3" id="collapseContohNISN_<?= $doc['field'] ?>">
                                            <img src="../assets/img/contoh_nisn.png" class="img-fluid rounded shadow-sm border"
                                                alt="Contoh Printout NISN">
                                            <div class="text-muted mt-2" style="font-size: 0.75rem;">
                                                Pastikan dokumen yang diunggah memuat informasi pencarian NISN lengkap seperti
                                                contoh di atas (dari web referensi Kemdikbud).
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <p class="small text-muted mb-0"><i class="fas fa-info-circle me-1"></i> Pastikan dokumen terbaca
                            dengan jelas sebelum diunggah.</p>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-premium-action px-4">Mulai Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php require_once 'layout_bottom.php'; ?>