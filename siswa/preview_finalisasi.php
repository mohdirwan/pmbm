<?php
$page_title = "Preview Data & Cetak Formulir";
require_once 'layout_top.php';

// Fetch Jalur Name and Syarat Khusus
$stmt_jalur = $pdo->prepare("SELECT nama_jalur, syarat FROM jalur_pendaftaran WHERE id = ?");
$stmt_jalur->execute([$siswa['jalur_id']]);
$jalur_data = $stmt_jalur->fetch();
$nama_jalur = $jalur_data['nama_jalur'] ?? '';
$syarat_text = $jalur_data['syarat'] ?? '';

// Handle Finalisasi POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'finalisasi') {
    try {
        // Auto-add column to prevent errors on hosting servers that haven't run the SQL
        try {
            $pdo->exec("ALTER TABLE pendaftar ADD COLUMN finalisasi ENUM('belum', 'ya') DEFAULT 'belum'");
        } catch (Exception $e) {
            // Column already exists, ignore
        }

        // Update status
        $stmt = $pdo->prepare("UPDATE pendaftar SET finalisasi = 'ya' WHERE id = ?");
        $stmt->execute([$_SESSION['siswa_id']]);

        // Redirect directly to print page using JS (to avoid header already sent error)
        echo "<script>window.location.href = '../cetak_formulir.php?reg=" . urlencode($siswa['no_pendaftaran']) . "';</script>";
        exit();
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

// Status Badge Logic
$ppdb_status = get_setting('ppdb_status', 'belum');
$status = $siswa['status'] ?? 'Pending';
$status_color = 'warning';
$status_text = 'Dalam Proses';

// Refined logic to prevent "kegaduhan" (premature status leaks)
if ($ppdb_status == 'pengumuman_adm' || $ppdb_status == 'cbt' || $ppdb_status == 'finalisasi') {
    // Stage: Results of Administrative Selection
    if ($status == 'Terverifikasi' || $status == 'Diterima' || $status == 'Lulus' || $status == 'Tidak Lulus') {
        $status_color = 'info';
        $status_text = 'Terverifikasi';
    } elseif ($status == 'Ditolak') {
        $status_color = 'danger';
        $status_text = 'Ditolak Administrasi';
    }
} elseif ($ppdb_status == 'pengumuman') {
    // Stage: Final Results
    if ($status == 'Terverifikasi') {
        $status_color = 'info';
        $status_text = 'Terverifikasi';
    } elseif ($status == 'Diterima' || $status == 'Lulus') {
        $status_color = 'success';
        $status_text = 'Lulus Seleksi';
    } elseif ($status == 'Ditolak' || $status == 'Tidak Lulus') {
        $status_color = 'danger';
        $status_text = 'Ditolak';
    }
}
?>

<style>
    .profile-hero {
        background: linear-gradient(135deg, #0b2c24 0%, #1a4d40 100%);
        border-radius: 30px;
        color: white;
        padding: 40px;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(11, 44, 36, 0.15);
    }

    .profile-hero::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 50%;
    }

    .profile-avatar {
        width: 140px;
        height: 140px;
        border-radius: 25px;
        object-fit: cover;
        border: 4px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .info-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        padding: 28px;
        position: relative;
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
    }

    .data-group {
        margin-bottom: 1.5rem;
    }

    .data-label {
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: #8898aa;
        margin-bottom: 6px;
    }

    .data-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #32325d;
        word-break: break-word;
    }

    .section-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #0b2c24;
        margin-top: 40px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
    }

    .section-title i {
        width: 45px;
        height: 45px;
        background: #e6f0ed;
        color: #0b2c24;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 18px;
        font-size: 1.2rem;
    }

    .rapor-table {
        border-radius: 20px;
        overflow: hidden;
        border: none;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.04);
    }

    .rapor-table thead {
        background: #0b2c24;
        color: white;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .rapor-table td {
        padding: 20px;
        vertical-align: middle;
        font-weight: 600;
        border-color: #f1f3f9;
    }

    .status-badge-floating {
        position: absolute;
        top: 30px;
        right: 30px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        padding: 10px 25px;
        border-radius: 100px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        font-weight: 600;
        font-size: 0.85rem;
        z-index: 10;
    }

    .mini-status {
        font-size: 0.75rem;
        padding: 4px 12px;
        border-radius: 100px;
        font-weight: 700;
    }

    .label-group {
        background: #f8f9fa;
        padding: 15px 20px;
        border-radius: 18px;
        height: 100%;
        border: 1px dashed #dee2e6;
    }

    @media print {

        #sidebar,
        .top-header,
        .status-badge-floating,
        .btn-print-action,
        .alert {
            display: none !important;
        }

        .profile-hero {
            background: #fff !important;
            color: #000 !important;
            border: 2px solid #0b2c24;
            box-shadow: none;
        }

        .info-card {
            border: 1px solid #eee;
            box-shadow: none;
            break-inside: avoid;
        }

        .page-body {
            padding: 0 !important;
        }
    }
</style>

<div class="animate-fade-in">
    <!-- HERO SECTION: PROFILE SUMMARY -->
    <div class="profile-hero">
        <div class="row align-items-center">
            <div class="col-auto">
                <?php if (!empty($siswa['foto_siswa'])): ?>
                    <img src="../uploads/<?= $siswa['foto_siswa'] ?>" class="profile-avatar" alt="Foto Murid">
                <?php else: ?>
                    <div class="profile-avatar bg-white d-flex align-items-center justify-content-center">
                        <i class="fas fa-user-graduate fa-4x text-success"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col mt-3 mt-md-0">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold">ID:
                        <?= $siswa['no_pendaftaran'] ?></span>
                    <span class="mini-status bg-white text-dark opacity-75">Tahun Ajaran
                        <?= get_setting('ppdb_year', '2026/2027') ?></span>
                </div>
                <h1 class="display-6 fw-bold mb-1 text-white"><?= htmlspecialchars($siswa['nama_lengkap']) ?></h1>
                <p class="mb-0 opacity-75 fs-5">
                    <i class="fas fa-school me-2"></i> <?= htmlspecialchars($siswa['asal_sekolah']) ?>
                    <span class="ms-2 opacity-50">|</span>
                    <span class="ms-2"><i class="fas fa-map-marker-alt me-1"></i>
                        <?= htmlspecialchars($siswa['kabupaten_kota'] . ', ' . $siswa['provinsi']) ?></span>
                </p>
                <div class="mt-4 d-flex flex-wrap gap-2">
                    <div class="bg-white text-dark shadow-sm rounded-pill px-4 py-2 small fw-bold"><i
                            class="fas fa-route me-2 text-success"></i> <?= htmlspecialchars($nama_jalur ?: 'N/A') ?>
                    </div>
                    <div class="bg-white text-dark shadow-sm rounded-pill px-4 py-2 small fw-bold"><i
                            class="fas fa-id-card me-2 text-primary"></i> NISN: <?= htmlspecialchars($siswa['nisn']) ?>
                    </div>
                    <div class="bg-white text-dark shadow-sm rounded-pill px-4 py-2 small fw-bold"><i
                            class="fas fa-calendar-check me-2 text-info"></i> Terdaftar:
                        <?= date('d/m/Y', strtotime($siswa['tanggal_daftar'])) ?>
                    </div>
                </div>
            </div>
            <div class="col-auto mt-3 mt-lg-0">
                <?php
                $ppdb_status = get_setting('ppdb_status', 'tutup');
                $status = $siswa['status'] ?? 'Pending';
                
                // Allow editing if not verified/accepted and during registration or verification stages
                $allowed_edit_stages = ['buka', 'verifikasi', 'pengumuman_adm'];
                $is_editable = (!in_array($status, ['Terverifikasi', 'Diterima', 'Lulus']) && in_array($ppdb_status, $allowed_edit_stages));
                
                // If they are rejected, they should definitely be allowed to fix their data
                if ($status == 'Ditolak' && in_array($ppdb_status, $allowed_edit_stages)) {
                    $is_editable = true;
                }
                ?>
                <div class="d-flex flex-column gap-2">
                    <?php if (isset($siswa['finalisasi']) && $siswa['finalisasi'] == 'ya'): ?>
                        <button onclick="window.open('<?= BASE_URL ?>cetak_formulir.php?reg=<?= urlencode($siswa['no_pendaftaran']) ?>', '_blank')"
                            class="btn btn-success btn-lg rounded-pill px-4 fw-bold shadow-lg" style="position: relative; z-index: 100;">
                            <i class="fas fa-print me-2"></i> Cetak Formulir Pendaftaran
                        </button>
                        <div class="text-center mt-2">
                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 py-2 px-3 rounded-pill">
                                <i class="fas fa-lock me-1"></i> Data Telah Dikunci (Finalisasi)
                            </span>
                        </div>
                    <?php else: ?>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#finalisasiModal"
                            class="btn btn-light btn-lg rounded-pill px-4 fw-bold shadow-lg" style="position: relative; z-index: 100;">
                            <i class="fas fa-print me-2 text-primary"></i> Finalisasi dan Cetak Formulir Pendaftaran
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- SUCCESS NOTIFICATION -->
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div class="alert alert-success border-0 shadow-lg rounded-4 p-4 mb-4 animate-fade-in d-flex align-items-center">
            <div class="bg-success text-white rounded-circle p-2 me-3">
                <i class="fas fa-check"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0">Update Berhasil!</h6>
                <p class="mb-0 small opacity-75">Perubahan data profil Anda telah disimpan ke sistem.</p>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- INFO ALERT -->
    <div class="alert alert-info border-0 shadow-sm rounded-4 p-4 mb-5 d-flex align-items-center">
        <i class="fas fa-search fs-2 me-4 text-primary"></i>
        <div>
            <h6 class="fw-bold mb-1">Pratinjau Data Pendaftaran</h6>
            <p class="mb-0 small opacity-75">Halaman ini digunakan untuk melihat rangkuman seluruh data pendaftaran Anda dan mencetak formulir. Pastikan seluruh data dan berkas sudah benar.</p>
        </div>
    </div>

    <!-- SECTION 1: DATA MURID (STEP 1 & 2 REGISTER) -->
    <h4 class="section-title"><i class="fas fa-user-graduate"></i> Identitas & Sekolah Asal</h4>
    <div class="row g-4">
        <div class="col-md-8">
            <div class="info-card">
                <div class="row g-4">
                    <div class="col-md-6 data-group">
                        <div class="data-label">Nama Lengkap (Sesuai Ijazah)</div>
                        <div class="data-value"><?= htmlspecialchars($siswa['nama_lengkap']) ?></div>
                    </div>
                    <div class="col-md-3 data-group">
                        <div class="data-label">Nomor Induk Kependudukan (NIK)</div>
                        <div class="data-value"><?= htmlspecialchars($siswa['nik']) ?></div>
                    </div>
                    <div class="col-md-3 data-group">
                        <div class="data-label">NISN Murid</div>
                        <div class="data-value"><?= htmlspecialchars($siswa['nisn']) ?></div>
                    </div>
                    <div class="col-md-6 data-group">
                        <div class="data-label">Tempat & Tanggal Lahir</div>
                        <div class="data-value"><?= htmlspecialchars($siswa['tempat_lahir']) ?>,
                            <?= date('d F Y', strtotime($siswa['tanggal_lahir'])) ?>
                        </div>
                    </div>
                    <div class="col-md-3 data-group">
                        <div class="data-label">Jenis Kelamin</div>
                        <div class="data-value"><?= $siswa['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></div>
                    </div>
                    <div class="col-md-3 data-group">
                        <div class="data-label">Agama</div>
                        <div class="data-value"><?= htmlspecialchars($siswa['agama']) ?></div>
                    </div>
                    <div class="col-md-3 data-group">
                        <div class="data-label">Anak Ke</div>
                        <div class="data-value"><?= htmlspecialchars($siswa['anak_ke'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-3 data-group">
                        <div class="data-label">Status Keluarga</div>
                        <div class="data-value"><?= htmlspecialchars($siswa['status_keluarga'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-3 data-group">
                        <div class="data-label">Hobi</div>
                        <div class="data-value"><?= htmlspecialchars($siswa['hobi'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-3 data-group">
                        <div class="data-label">Nomor HP Siswa</div>
                        <div class="data-value text-primary fw-bold"><?= htmlspecialchars($siswa['no_hp'] ?: '-') ?></div>
                    </div>
                    <div class="col-12 data-group">
                        <div class="data-label">Alamat Lengkap Berdasarkan KK</div>
                        <div class="data-value"><?= nl2br(htmlspecialchars($siswa['alamat'])) ?></div>
                    </div>
                    <div class="col-md-4 data-group">
                        <div class="data-label">Status Tinggal</div>
                        <div class="data-value"><?= htmlspecialchars($siswa['status_tinggal'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-4 data-group">
                        <div class="data-label">Jarak ke Sekolah</div>
                        <div class="data-value"><?= htmlspecialchars($siswa['jarak_sekolah'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-4 data-group">
                        <div class="data-label">Transportasi</div>
                        <div class="data-value"><?= htmlspecialchars($siswa['transportasi_rumah'] ?: '-') ?></div>
                    </div>
                    <div class="col-md-6 data-group">
                        <div class="data-label">Kecamatan</div>
                        <div class="data-value"><?= htmlspecialchars($siswa['kecamatan']) ?></div>
                    </div>
                    <div class="col-md-6 data-group">
                        <div class="data-label">Kabupaten / Kota</div>
                        <div class="data-value"><?= htmlspecialchars($siswa['kabupaten_kota']) ?></div>
                    </div>
                    <div class="col-md-6 data-group">
                        <div class="data-label">Provinsi</div>
                        <div class="data-value"><?= htmlspecialchars($siswa['provinsi']) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card" style="border-left: 5px solid #ffc107;">
                <h6 class="fw-bold mb-4 text-muted small text-uppercase"><i class="fas fa-school me-2"></i>Informasi
                    Sekolah</h6>
                <div class="data-group">
                    <div class="data-label">Nama Sekolah Asal (SD/MI)</div>
                    <div class="data-value fs-5"><?= htmlspecialchars($siswa['asal_sekolah']) ?></div>
                </div>
                <div class="data-group">
                    <div class="data-label">NPSN Sekolah</div>
                    <div class="data-value"><?= htmlspecialchars($siswa['npsn_sekolah'] ?: '-') ?></div>
                </div>
                <div class="data-group">
                    <div class="data-label">Alamat Sekolah Asal</div>
                    <div class="data-value small"><?= htmlspecialchars($siswa['alamat_sekolah'] ?: '-') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: DATA ORANG TUA (STEP 3 REGISTER) -->
    <div class="row g-3 mb-2">
        <div class="col-md-6">
            <div class="info-card d-flex justify-content-between align-items-center py-2 px-3">
                <span class="text-muted small">Nomor Kartu Keluarga</span>
                <span
                    class="fw-bold text-primary fst-italic fs-5"><?= htmlspecialchars($siswa['no_kk'] ?: '-') ?></span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-card d-flex justify-content-between align-items-center py-2 px-3">
                <span class="text-muted small">Status Orang Tua</span>
                <span class="badge bg-info text-dark"><?= htmlspecialchars($siswa['status_orang_tua'] ?: '-') ?></span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Ayah -->
        <div class="col-lg-4">
            <div class="info-card h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3"><i
                            class="fas fa-user-tie"></i></div>
                    <h6 class="fw-bold mb-0">Ayah Kandung</h6>
                </div>
                <div class="data-group mb-3">
                    <div class="data-label">Nama Lengkap</div>
                    <div class="data-value"><?= htmlspecialchars($siswa['nama_ayah'] ?: '-') ?></div>
                    <div class="small text-muted">NIK: <?= htmlspecialchars($siswa['nik_ayah'] ?: '-') ?></div>
                </div>
                <div class="data-group mb-3">
                    <div class="data-label">TTL & Pendidikan</div>
                    <div class="data-value small"><?= htmlspecialchars($siswa['tempat_lahir_ayah'] ?: '-') ?>,
                        <?= htmlspecialchars($siswa['tanggal_lahir_ayah'] ?: '-') ?>
                    </div>
                    <div class="data-value small text-primary"><?= htmlspecialchars($siswa['pendidikan_ayah'] ?: '-') ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="data-label">Pekerjaan</div>
                        <div class="data-value small"><?= htmlspecialchars($siswa['pekerjaan_ayah'] ?: '-') ?></div>
                    </div>
                    <div class="col-6">
                        <div class="data-label">Penghasilan</div>
                        <div class="data-value small text-success">
                            <?= htmlspecialchars($siswa['penghasilan_ayah'] ?: '-') ?>
                        </div>
                    </div>
                </div>
                <div class="data-group">
                    <div class="data-label">No. HP & Alamat</div>
                    <div class="data-value small text-primary"><?= htmlspecialchars($siswa['no_hp_ayah'] ?: '-') ?>
                    </div>
                    <div class="data-value small text-muted">
                        <?php if (!empty($siswa['provinsi_ayah'])): ?>
                            <?= htmlspecialchars($siswa['alamat_ayah']) ?>, 
                            <?= htmlspecialchars($siswa['desa_kelurahan_ayah']) ?>, 
                            <?= htmlspecialchars($siswa['kecamatan_ayah']) ?>, 
                            <?= htmlspecialchars($siswa['kabupaten_kota_ayah']) ?>, 
                            <?= htmlspecialchars($siswa['provinsi_ayah']) ?>
                        <?php else: ?>
                            <?= htmlspecialchars($siswa['alamat_ayah'] ?: '(Sama dengan murid)') ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ibu -->
        <div class="col-lg-4">
            <div class="info-card h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-danger bg-opacity-10 text-danger p-2 rounded-3 me-3"><i
                            class="fas fa-user-nurse"></i></div>
                    <h6 class="fw-bold mb-0">Ibu Kandung</h6>
                </div>
                <div class="data-group mb-3">
                    <div class="data-label">Nama Lengkap</div>
                    <div class="data-value"><?= htmlspecialchars($siswa['nama_ibu'] ?: '-') ?></div>
                    <div class="small text-muted">NIK: <?= htmlspecialchars($siswa['nik_ibu'] ?: '-') ?></div>
                </div>
                <div class="data-group mb-3">
                    <div class="data-label">TTL & Pendidikan</div>
                    <div class="data-value small"><?= htmlspecialchars($siswa['tempat_lahir_ibu'] ?: '-') ?>,
                        <?= htmlspecialchars($siswa['tanggal_lahir_ibu'] ?: '-') ?>
                    </div>
                    <div class="data-value small text-primary"><?= htmlspecialchars($siswa['pendidikan_ibu'] ?: '-') ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="data-label">Pekerjaan</div>
                        <div class="data-value small"><?= htmlspecialchars($siswa['pekerjaan_ibu'] ?: '-') ?></div>
                    </div>
                    <div class="col-6">
                        <div class="data-label">Penghasilan</div>
                        <div class="data-value small text-success">
                            <?= htmlspecialchars($siswa['penghasilan_ibu'] ?: '-') ?>
                        </div>
                    </div>
                </div>
                <div class="data-group">
                    <div class="data-label">No. HP & Alamat</div>
                    <div class="data-value small text-primary"><?= htmlspecialchars($siswa['no_hp_ibu'] ?: '-') ?></div>
                    <div class="data-value small text-muted">
                        <?php if (!empty($siswa['provinsi_ibu'])): ?>
                            <?= htmlspecialchars($siswa['alamat_ibu']) ?>, 
                            <?= htmlspecialchars($siswa['desa_kelurahan_ibu']) ?>, 
                            <?= htmlspecialchars($siswa['kecamatan_ibu']) ?>, 
                            <?= htmlspecialchars($siswa['kabupaten_kota_ibu']) ?>, 
                            <?= htmlspecialchars($siswa['provinsi_ibu']) ?>
                        <?php else: ?>
                            <?= htmlspecialchars($siswa['alamat_ibu'] ?: '(Sama dengan murid)') ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wali / Kontak -->
        <div class="col-lg-4">
            <?php if (!empty($siswa['nama_wali'])): ?>
                <div class="info-card mb-3 h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-3 me-3"><i
                                class="fas fa-user-friends"></i></div>
                        <h6 class="fw-bold mb-0 text-warning-emphasis">Wali Murid</h6>
                    </div>
                    <div class="data-group mb-2">
                        <div class="data-label">Nama Wali</div>
                        <div class="data-value"><?= htmlspecialchars($siswa['nama_wali']) ?></div>
                        <div class="small text-muted">NIK: <?= htmlspecialchars($siswa['nik_wali'] ?: '-') ?></div>
                    </div>
                    <div class="data-group mb-2">
                        <div class="data-label">Pekerjaan & HP</div>
                        <div class="data-value small"><?= htmlspecialchars($siswa['pekerjaan_wali'] ?: '-') ?></div>
                        <div class="data-value small text-primary"><?= htmlspecialchars($siswa['no_hp_wali'] ?: '-') ?>
                        </div>
                    </div>
                    <div class="data-group">
                        <div class="data-label">Alamat Wali</div>
                        <div class="data-value small text-muted">
                            <?php if (!empty($siswa['provinsi_wali'])): ?>
                                <?= htmlspecialchars($siswa['alamat_wali']) ?>, 
                                <?= htmlspecialchars($siswa['desa_kelurahan_wali']) ?>, 
                                <?= htmlspecialchars($siswa['kecamatan_wali']) ?>, 
                                <?= htmlspecialchars($siswa['kabupaten_kota_wali']) ?>, 
                                <?= htmlspecialchars($siswa['provinsi_wali']) ?>
                            <?php else: ?>
                                <?= htmlspecialchars($siswa['alamat_wali'] ?: '-') ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="info-card style=" background: #eefdf7; border: 1px solid #c3e6cb;" h-100">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-success text-white p-2 rounded-3 me-3 shadow-sm"><i class="fab fa-whatsapp"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-success">Kontak WA Utama</h6>
                    </div>
                    <div class="data-group">
                        <div class="data-label text-success">Nomor WhatsApp Notifikasi</div>
                        <div class="data-value fs-4 fw-bold text-dark"><?= htmlspecialchars($siswa['kontak_wa'] ?: '-') ?>
                        </div>
                    </div>
                    <div class="bg-white p-3 rounded-4 border mt-3">
                        <div class="data-label text-muted small">Atas Nama (Pemilik Nomor)</div>
                        <div class="data-value fw-bold text-success"><i
                                class="fas fa-user-check me-2"></i><?= htmlspecialchars($siswa['nama_kontak_wa'] ?: '-') ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- SECTION 3: REKAP NILAI & BERKAS (STEP 4 & 5 REGISTER) -->
    <div class="row g-4 mt-2">
        <!-- Nilai Rapor -->
        <div class="col-lg-8">
            <h4 class="section-title"><i class="fas fa-chart-line"></i> Rekapitulasi Nilai Rapor</h4>
            <div class="info-card p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table rapor-table text-center mb-0">
                        <thead>
                            <tr>
                                <th>K4 S1</th>
                                <th>K4 S2</th>
                                <th>K5 S1</th>
                                <th>K5 S2</th>
                                <th>K6 S1</th>
                                <th class="bg-success">Rata-Rata Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="fs-5">
                                <td><?= number_format($siswa['nilai_k4_s1'], 2) ?></td>
                                <td><?= number_format($siswa['nilai_k4_s2'], 2) ?></td>
                                <td><?= number_format($siswa['nilai_k5_s1'], 2) ?></td>
                                <td><?= number_format($siswa['nilai_k5_s2'], 2) ?></td>
                                <td><?= number_format($siswa['nilai_k6_s1'], 2) ?></td>
                                <td class="bg-success bg-opacity-10 text-success fw-bold fs-3">
                                    <?= number_format($siswa['nilai_rapor_rata2'], 2) ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Berkas Status -->
        <div class="col-lg-4">
            <h4 class="section-title"><i class="fas fa-file-check"></i> Status Berkas</h4>
            <div class="info-card">
                <?php
                $field_mapping = [
                    'pas foto' => ['field' => 'foto_siswa', 'label' => 'Pas Foto 3x4'],
                    'rapor' => ['field' => 'file_rapor', 'label' => 'Rapor Asli'],
                    'surat keterangan tahfidz' => ['field' => 'file_surat_tahfidz', 'label' => 'SK. Tahfidz'],
                    'surat keterangan prestasi' => ['field' => 'file_surat_prestasi', 'label' => 'SK. Prestasi'],
                    'nilai rata-rata' => ['field' => 'file_nilai_rata', 'label' => 'SK. Nilai Rata-rata'],
                    'rata rata nilai' => ['field' => 'file_nilai_rata', 'label' => 'SK. Nilai Rata-rata'],
                    'peringkat' => ['field' => 'file_ranking', 'label' => 'SK. Peringkat'],
                    'ranking' => ['field' => 'file_ranking', 'label' => 'SK. Ranking'],
                    'sertifikat prestasi akademik' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertif. Prestasi Akdmk'],
                    'sertifikat prestasi non-akademik' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertif. Prestasi Non-Akdmk'],
                    'sertifikat prestasi' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertifikat Prestasi'],
                    'sertifikat tahfidz' => ['field' => 'file_sertifikat_tahfidz', 'label' => 'Sertifikat Tahfidz'],
                    'prestasi akademik' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertif. Prestasi Akdmk'],
                    'prestasi non akademik' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertif. Prestasi Non-Akdmk'],
                    'tahfidz' => ['field' => 'file_sertifikat_tahfidz', 'label' => 'Sertifikat Tahfidz'],
                    'kartu keluarga' => ['field' => 'file_kk', 'label' => 'Kartu Keluarga'],
                    'kk' => ['field' => 'file_kk', 'label' => 'Kartu Keluarga'],
                    'pakta integritas' => ['field' => 'file_pakta', 'label' => 'Pakta Integritas'],
                    'akta' => ['field' => 'file_akta', 'label' => 'Akta Kelahiran'],
                    'nisn' => ['field' => 'file_nisn', 'label' => 'Print Out NISN']
                ];

                $docs = [];
                $added_fields = [];
                if (!empty($syarat_text)) {
                    $syarat_list = array_map('trim', explode(',', $syarat_text));
                    foreach ($syarat_list as $syarat) {
                        $syarat_clean = preg_replace('/\s*\([^)]*\)\s*/', '', $syarat);
                        $syarat_clean = preg_replace('/\s+\d+\s*$/', '', $syarat_clean);
                        $syarat_clean = trim(preg_replace('/\s+/', ' ', $syarat_clean));
                        $syarat_lower = strtolower($syarat_clean);

                        foreach ($field_mapping as $keyword => $mapping) {
                            if (stripos($syarat_lower, $keyword) !== false) {
                                if (!in_array($mapping['field'], $added_fields)) {
                                    $is_optional = stripos($syarat, '(pilihan)') !== false;
                                    $docs[] = [
                                        'label' => $mapping['label'],
                                        'field' => $mapping['field'],
                                        'optional' => $is_optional
                                    ];
                                    $added_fields[] = $mapping['field'];
                                    break;
                                }
                            }
                        }
                    }
                }

                if (empty($docs)) {
                    $docs = [
                        ['label' => 'KK Murid', 'field' => 'file_kk', 'optional' => false],
                        ['label' => 'Akta Murid', 'field' => 'file_akta', 'optional' => false],
                        ['label' => 'Rapor Murid', 'field' => 'file_rapor', 'optional' => false],
                        ['label' => 'Foto Murid', 'field' => 'foto_siswa', 'optional' => false]
                    ];
                }

                foreach ($docs as $doc):
                    $exists = !empty($siswa[$doc['field']]);
                    $bg_class = $exists ? 'bg-success bg-opacity-10' : ($doc['optional'] ? 'bg-secondary bg-opacity-10' : 'bg-light');
                    ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded-4 <?= $bg_class ?>">
                        <div class="fw-bold small text-dark d-flex align-items-center">
                            <i class="fas fa-file-alt me-2 text-muted"></i><?= $doc['label'] ?>
                            <?php if ($doc['optional']): ?>
                                <span class="badge bg-secondary-subtle text-secondary ms-2"
                                    style="font-size: 0.55rem;">Opsional</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($exists): ?>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success rounded-pill px-3 me-2"><i class="fas fa-check me-1"></i> Ada</span>
                                <a href="../uploads/<?= $siswa[$doc['field']] ?>" target="_blank" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-sm">
                                    <i class="fas fa-eye me-1"></i> Lihat Berkas
                                </a>
                            </div>
                        <?php else: ?>
                            <?php if ($doc['optional']): ?>
                                <span class="badge bg-secondary rounded-pill px-3"><i class="fas fa-minus me-1"></i> Kosong</span>
                            <?php else: ?>
                                <span class="badge bg-danger rounded-pill px-3"><i class="fas fa-times me-1"></i> Kosong</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- FINAL FOOTER (ONLY PRINT) -->
    <div class="mt-5 pt-5 border-top d-none d-print-block">
        <div class="row">
            <div class="col-8">
                <small class="text-muted italic">Dicetak secara otomatis oleh sistem PMBM MTsN 1 Kota Pekanbaru pada
                    <?= date('d/m/Y H:i') ?></small>
            </div>
            <div class="col-4 text-center">
                <div class="mb-5">Tanda Tangan Pendaftar,</div>
                <br><br>
                <div class="fw-bold">( <?= htmlspecialchars($siswa['nama_lengkap']) ?> )</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Finalisasi -->
<div class="modal fade" id="finalisasiModal" tabindex="-1" aria-labelledby="finalisasiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 pt-4 px-4 text-center d-block">
                <div class="d-flex justify-content-center mb-3">
                    <div class="bg-warning bg-opacity-25 text-warning p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
                <h5 class="modal-title fw-bold" id="finalisasiModalLabel">Apakah Anda Yakin?</h5>
            </div>
            <div class="modal-body py-4 px-4 text-center">
                <p class="mb-0 text-muted">Pastikan seluruh data diri dan berkas Anda sudah benar dan sesuai. Setelah dicetak, proses finalisasi dianggap selesai.</p>
            </div>
            <form method="POST" action="" target="_blank" onsubmit="setTimeout(() => { document.getElementById('btnSubmitFinal').innerHTML = '<i class=\'fas fa-spinner fa-spin me-2\'></i> Memproses...'; document.getElementById('btnSubmitFinal').disabled = true; setTimeout(() => window.location.reload(), 1000); }, 50);">
                <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-center gap-2">
                    <input type="hidden" name="action" value="finalisasi">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Belum, Cek Ulang</button>
                    <button type="submit" id="btnSubmitFinal" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Ya, Saya Yakin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'layout_bottom.php'; ?>