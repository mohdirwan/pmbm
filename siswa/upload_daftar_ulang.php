<?php
$page_title = "Upload Berkas Daftar Ulang";
require_once 'layout_top.php';

// Pastikan pengumuman sudah dibuka secara global
$ppdb_status = get_setting('ppdb_status', 'belum');
$ann_status = get_setting('announcement_status', 'closed');
if ($ann_status == 'closed' || $ppdb_status !== 'pengumuman') {
    header("Location: dashboard.php");
    exit;
}

// Pastikan siswa sudah lulus
if ($siswa['status'] != 'Diterima' && $siswa['status'] != 'Lulus') {
    echo "<script>alert('Anda belum dinyatakan lulus seleksi, belum bisa upload berkas daftar ulang.'); window.location.href='status_akhir.php';</script>";
    exit;
}

// Ambil syarat daftar ulang
$stmt = $pdo->query("SELECT * FROM syarat_daftar_ulang ORDER BY id ASC");
$syarat_list = $stmt->fetchAll();

$total_syarat = count($syarat_list);

// Handle Upload
$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_berkas'])) {
    $syarat_id = $_POST['syarat_id'];
    $tipe_file = $_POST['tipe_file']; 
    
    // Pastikan validasi
    if (isset($_FILES['file_berkas']) && $_FILES['file_berkas']['error'] == UPLOAD_ERR_OK) {
        $file_name = $_FILES['file_berkas']['name'];
        $file_tmp = $_FILES['file_berkas']['tmp_name'];
        $file_size = $_FILES['file_berkas']['size'];
        
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Define allowed extensions based on requirement
        $allowed = ($tipe_file == 'pdf') ? ['pdf'] : ['jpg', 'jpeg', 'png'];
        
        if (!in_array($ext, $allowed)) {
            $msg = "Format file tidak valid. Harap unggah file " . strtoupper(implode(', ', $allowed));
            $msgType = "danger";
        } elseif ($file_size > 2 * 1024 * 1024) { // 2MB Max
            $msg = "Ukuran file terlalu besar. Maksimal 2MB.";
            $msgType = "danger";
        } else {
            // Generate unique filename
            $new_filename = uniqid('du_') . '_' . $siswa['no_pendaftaran'] . '_' . $syarat_id . '.' . $ext;
            $upload_path = '../uploads/daftar_ulang/' . $new_filename;
            
            // Create folder if not exists
            if (!file_exists('../uploads/daftar_ulang/')) {
                mkdir('../uploads/daftar_ulang/', 0777, true);
            }
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $db_path = 'uploads/daftar_ulang/' . $new_filename;
                
                // Cek apakah sudah ada file sebelumnya (Replace)
                $cek = $pdo->prepare("SELECT file_path FROM berkas_daftar_ulang WHERE pendaftar_id = ? AND syarat_id = ?");
                $cek->execute([$siswa['id'], $syarat_id]);
                $old_file = $cek->fetchColumn();
                
                if ($old_file && file_exists('../' . $old_file)) {
                    unlink('../' . $old_file); // Hapus file lama
                }
                
                // Simpan atau update ke database
                $stmt = $pdo->prepare("INSERT INTO berkas_daftar_ulang (pendaftar_id, syarat_id, file_path) 
                                       VALUES (?, ?, ?) 
                                       ON DUPLICATE KEY UPDATE file_path = ?, diunggah_pada = CURRENT_TIMESTAMP");
                $stmt->execute([$siswa['id'], $syarat_id, $db_path, $db_path]);
                
                $msg = "Berkas berhasil diupload!";
                $msgType = "success";
            } else {
                $msg = "Gagal mengupload file ke server.";
                $msgType = "danger";
            }
        }
    } else {
        $msg = "Pilih file terlebih dahulu.";
        $msgType = "danger";
    }
}

// Ambil riwayat upload berkas murid ini
$stmt = $pdo->prepare("SELECT syarat_id, file_path, diunggah_pada FROM berkas_daftar_ulang WHERE pendaftar_id = ?");
$stmt->execute([$siswa['id']]);
$uploaded = [];
$total_uploaded = 0;
while ($row = $stmt->fetch()) {
    $uploaded[$row['syarat_id']] = $row;
    $total_uploaded++;
}

// Progress completion
$progress = $total_syarat > 0 ? ($total_uploaded / $total_syarat) * 100 : 0;
?>

<div class="row">
    <div class="col-12 mb-4">
        <!-- Progress Bar -->
        <div class="card glass-card border-0 p-4 shadow-sm text-center">
            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-tasks me-2 text-primary"></i> Progress Kelengkapan Administrasi</h5>
            <div class="progress" style="height: 20px; border-radius: 10px; background: #eef2f5;">
                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $progress ?>%;" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                    <span class="fw-bold shadow-sm"><?= round($progress) ?>% Selesai</span>
                </div>
            </div>
            <p class="mt-3 mb-0 text-muted small fw-bold">Anda telah mengunggah <?= $total_uploaded ?> dari <?= $total_syarat ?> syarat kelengkapan pendaftaran ulang.</p>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($msg): ?>
        <div class="col-12 mb-4">
            <div class="alert alert-<?= $msgType ?> alert-dismissible bg-<?= $msgType ?> bg-opacity-10 text-<?= $msgType ?> border-<?= $msgType ?> border-opacity-25 rounded-4 shadow-sm fade show" role="alert">
                <i class="fas fa-<?= $msgType == 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2 mt-1 fa-lg float-start"></i>
                <div>
                    <h6 class="fw-bold mb-1">Upload Indikator</h6>
                    <span><?= htmlspecialchars($msg) ?></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Daftar Syarat -->
    <?php foreach ($syarat_list as $syarat): 
        $id = $syarat['id'];
        $is_uploaded = isset($uploaded[$id]);
        $file_path = $is_uploaded ? $uploaded[$id]['file_path'] : '';
    ?>
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 <?=$is_uploaded ? 'bd-success' : 'bd-warning'?>">
            <!-- Styling the border subtly -->
            <style>
                .bd-success { border-bottom: 4px solid #198754 !important; }
                .bd-warning { border-bottom: 4px solid #ffc107 !important; }
            </style>
            
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($syarat['nama_syarat']) ?></h5>
                    <?php if ($is_uploaded): ?>
                        <span class="badge bg-success rounded-pill px-3 py-2 shadow-sm"><i class="fas fa-check-circle me-1"></i> Sudah Diupload</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2 shadow-sm"><i class="fas fa-exclamation-circle me-1"></i> Belum Diupload</span>
                    <?php endif; ?>
                </div>

                <div class="alert bg-light border-0 small text-muted mb-4 d-flex p-3 rounded-4">
                    <i class="fas fa-info-circle me-3 fa-2x opacity-50 text-secondary mt-1"></i>
                    <div>
                        <strong>Format Wajib:</strong> <?= $syarat['tipe_file'] == 'pdf' ? 'Dokumen PDF' : 'Gambar (JPG / PNG)' ?><br>
                        <strong>Ukuran Maksimal:</strong> 2 MB
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data" class="mt-auto">
                    <input type="hidden" name="upload_berkas" value="1">
                    <input type="hidden" name="syarat_id" value="<?= $id ?>">
                    <input type="hidden" name="tipe_file" value="<?= $syarat['tipe_file'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted"><?= $is_uploaded ? 'Ganti File (Opsional)' : 'Pilih File' ?></label>
                        <input type="file" name="file_berkas" class="form-control mb-3 p-3 bg-light border-0 rounded-4" 
                               accept="<?= $syarat['tipe_file'] == 'pdf' ? '.pdf' : '.jpg,.jpeg,.png' ?>" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn <?= $is_uploaded ? 'btn-outline-primary' : 'btn-primary' ?> w-100 rounded-pill fw-bold">
                            <i class="fas fa-upload me-1"></i> <?= $is_uploaded ? 'Upload Ulang' : 'Upload Berkas' ?>
                        </button>
                        
                        <?php if ($is_uploaded): ?>
                            <a href="<?= BASE_URL . $file_path ?>" target="_blank" class="btn btn-outline-secondary btn-sm px-3 rounded-pill d-flex align-items-center">
                                <i class="fas fa-eye"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($is_uploaded): ?>
                    <div class="text-center mt-3">
                        <small class="text-success fw-bold"><i class="fas fa-clock me-1"></i> Terakhir diunggah: <?= date('d M Y, H:i', strtotime($uploaded[$id]['diunggah_pada'])) ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

</div>

<?php require_once 'layout_bottom.php'; ?>
