<?php
$page_title = "Ubah Identitas Pendaftaran";
require_once 'layout_top.php';

// Security Check: Only allow if not verified/accepted and PPDB is open
$ppdb_status = get_setting('ppdb_status', 'tutup');
$status = $siswa['status'] ?? 'Pending';
// Security Check Enhancement for Data Correction
$allowed_stages = ['buka', 'verifikasi', 'pengumuman_adm'];
if ($status == 'Terverifikasi' || $status == 'Diterima' || !in_array($ppdb_status, $allowed_stages)) {
    // If rejected, they should still be allowed to correct data during verification/announcement stages
    if ($status != 'Ditolak' || !in_array($ppdb_status, ['verifikasi', 'pengumuman_adm'])) {
        echo "<script>window.location.href='identitas.php';</script>";
        exit();
    }
}

// Finalisasi block: regardless of status, if finalized, block edit
if (isset($siswa['finalisasi']) && $siswa['finalisasi'] == 'ya') {
    echo "<script>window.location.href='identitas.php';</script>";
    exit();
}

// Fetch Jalur Pendaftaran
$stmt_jalur = $pdo->query("SELECT * FROM jalur_pendaftaran ORDER BY id ASC");
$list_jalur = $stmt_jalur->fetchAll();
?>

<style>
    .edit-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        padding: 35px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
    }

    .section-header {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f1f3f9;
        color: #0b2c24;
    }

    .section-header i {
        width: 40px;
        height: 40px;
        background: #e6f0ed;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: #0b2c24;
    }

    .form-label-premium {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control-premium {
        border-radius: 12px;
        padding: 12px 18px;
        background: #f8f9fa;
        border: 1.5px solid #eef0f2;
        width: 100%;
        transition: all 0.2s;
    }

    .form-control-premium:focus {
        background: #fff;
        border-color: #0b2c24;
        box-shadow: 0 0 0 4px rgba(11, 44, 36, 0.1);
        outline: none;
    }

    .sticky-actions {
        position: sticky;
        bottom: 20px;
        z-index: 100;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        padding: 15px;
        border-radius: 20px;
        box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 40px;
    }
</style>

<div class="animate-fade-in">
    <div class="d-flex align-items-center mb-4">
        <a href="identitas.php" class="btn btn-light rounded-circle p-3 me-3 shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="fw-bold mb-0">Ubah Data Profil</h2>
            <p class="text-muted mb-0">Pastikan data yang Anda perbaiki sudah benar dan sesuai dokumen asli.</p>
        </div>
    </div>

    <form action="proses_update_identitas.php" method="POST" enctype="multipart/form-data" id="updateForm">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

        <!-- Section 1: Jalur & Identitas Dasar -->
        <div class="edit-card">
            <div class="section-header">
                <i class="fas fa-user-graduate"></i>
                <h5 class="fw-bold mb-0">Identitas Utama & Jalur</h5>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label-premium">Jalur Pendaftaran</label>
                    <select name="jalur_id" class="form-select form-control-premium" required>
                        <?php foreach ($list_jalur as $j): ?>
                            <option value="<?= $j['id'] ?>" <?= $siswa['jalur_id'] == $j['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($j['nama_jalur']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">NISN (10 Digit) <span class="text-danger small fw-normal ms-1">*Tidak dapat diubah</span></label>
                    <input type="text" class="form-control form-control-premium bg-light text-muted" name="nisn"
                        value="<?= htmlspecialchars($siswa['nisn']) ?>" required maxlength="10" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">NIK (16 Digit)</label>
                    <input type="text" class="form-control form-control-premium" name="nik"
                        value="<?= htmlspecialchars($siswa['nik']) ?>" required maxlength="16"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Nama Lengkap (Sesuai Ijazah)</label>
                    <input type="text" class="form-control form-control-premium" name="nama_lengkap"
                        value="<?= htmlspecialchars($siswa['nama_lengkap']) ?>" required
                        style="text-transform: uppercase;">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Tempat Lahir</label>
                    <input type="text" class="form-control form-control-premium" name="tempat_lahir"
                        value="<?= htmlspecialchars($siswa['tempat_lahir']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Tanggal Lahir</label>
                    <input type="date" class="form-control form-control-premium" name="tanggal_lahir"
                        value="<?= $siswa['tanggal_lahir'] ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select form-control-premium" required>
                        <option value="L" <?= $siswa['jenis_kelamin'] == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= $siswa['jenis_kelamin'] == 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Agama</label>
                    <select name="agama" class="form-select form-control-premium" required>
                        <option value="Islam" <?= $siswa['agama'] == 'Islam' ? 'selected' : '' ?>>Islam</option>
                        <option value="Kristen" <?= $siswa['agama'] == 'Kristen' ? 'selected' : '' ?>>Kristen</option>
                        <option value="Katolik" <?= $siswa['agama'] == 'Katolik' ? 'selected' : '' ?>>Katolik</option>
                        <option value="Hindu" <?= $siswa['agama'] == 'Hindu' ? 'selected' : '' ?>>Hindu</option>
                        <option value="Budha" <?= $siswa['agama'] == 'Budha' ? 'selected' : '' ?>>Budha</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">No. HP Siswa</label>
                    <input type="text" class="form-control form-control-premium" name="no_hp"
                        value="<?= htmlspecialchars($siswa['no_hp']) ?>" required maxlength="15">
                </div>
            </div>
        </div>

        <!-- Section 2: Domisili & Keluarga -->
        <div class="edit-card">
            <div class="section-header">
                <i class="fas fa-home"></i>
                <h5 class="fw-bold mb-0">Domisili & Keluarga</h5>
            </div>
            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label-premium">Alamat Lengkap Berdasarkan KK</label>
                    <textarea name="alamat" class="form-control form-control-premium" rows="3"
                        required><?= htmlspecialchars($siswa['alamat']) ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Kecamatan</label>
                    <input type="text" class="form-control form-control-premium" name="kecamatan"
                        value="<?= htmlspecialchars($siswa['kecamatan']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Kabupaten / Kota</label>
                    <input type="text" class="form-control form-control-premium" name="kabupaten_kota"
                        value="<?= htmlspecialchars($siswa['kabupaten_kota']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Provinsi</label>
                    <input type="text" class="form-control form-control-premium" name="provinsi"
                        value="<?= htmlspecialchars($siswa['provinsi']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label-premium">Anak Ke</label>
                    <input type="number" class="form-control form-control-premium" name="anak_ke"
                        value="<?= $siswa['anak_ke'] ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label-premium">Status Keluarga</label>
                    <select name="status_keluarga" class="form-select form-control-premium" required>
                        <option value="Anak Kandung" <?= $siswa['status_keluarga'] == 'Anak Kandung' ? 'selected' : '' ?>>
                            Anak Kandung</option>
                        <option value="Anak Tiri" <?= $siswa['status_keluarga'] == 'Anak Tiri' ? 'selected' : '' ?>>Anak
                            Tiri</option>
                        <option value="Anak Angkat" <?= $siswa['status_keluarga'] == 'Anak Angkat' ? 'selected' : '' ?>>
                            Anak Angkat</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Hobi</label>
                    <input type="text" class="form-control form-control-premium" name="hobi"
                        value="<?= htmlspecialchars($siswa['hobi']) ?>">
                </div>
                <!-- New Student Fields -->
                <div class="col-md-4">
                    <label class="form-label-premium">Status Tempat Tinggal</label>
                    <select name="status_tinggal" class="form-select form-control-premium" required>
                        <option value="">Pilih...</option>
                        <option value="Bersama Orang Tua" <?= $siswa['status_tinggal'] == 'Bersama Orang Tua' ? 'selected' : '' ?>>Bersama Orang Tua</option>
                        <option value="Asrama" <?= $siswa['status_tinggal'] == 'Asrama' ? 'selected' : '' ?>>Asrama
                        </option>
                        <option value="Kost" <?= $siswa['status_tinggal'] == 'Kost' ? 'selected' : '' ?>>Kost</option>
                        <option value="Bersama Wali" <?= $siswa['status_tinggal'] == 'Bersama Wali' ? 'selected' : '' ?>>
                            Bersama Wali</option>
                        <option value="Lainnya" <?= $siswa['status_tinggal'] == 'Lainnya' ? 'selected' : '' ?>>Lainnya
                        </option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Jarak ke Sekolah (Km)</label>
                    <input type="text" class="form-control form-control-premium" name="jarak_sekolah"
                        value="<?= htmlspecialchars($siswa['jarak_sekolah']) ?>" placeholder="Contoh: 2">
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Transportasi</label>
                    <select name="transportasi_rumah" class="form-select form-control-premium" required>
                        <option value="">Pilih...</option>
                        <option value="Jalan Kaki" <?= $siswa['transportasi_rumah'] == 'Jalan Kaki' ? 'selected' : '' ?>>
                            Jalan Kaki</option>
                        <option value="Sepeda Motor" <?= $siswa['transportasi_rumah'] == 'Sepeda Motor' ? 'selected' : '' ?>>Sepeda Motor</option>
                        <option value="Mobil Pribadi" <?= $siswa['transportasi_rumah'] == 'Mobil Pribadi' ? 'selected' : '' ?>>Mobil Pribadi</option>
                        <option value="Antar Jemput" <?= $siswa['transportasi_rumah'] == 'Antar Jemput' ? 'selected' : '' ?>>Antar Jemput</option>
                        <option value="Ojek" <?= $siswa['transportasi_rumah'] == 'Ojek' ? 'selected' : '' ?>>Ojek</option>
                        <option value="Lainnya" <?= $siswa['transportasi_rumah'] == 'Lainnya' ? 'selected' : '' ?>>Lainnya
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 3: Sekolah Asal -->
        <div class="edit-card">
            <div class="section-header">
                <i class="fas fa-school"></i>
                <h5 class="fw-bold mb-0">Data Sekolah Asal</h5>
            </div>
            <div class="row g-4">
                <div class="col-md-8">
                    <label class="form-label-premium">Nama Sekolah Asal (SD/MI)</label>
                    <input type="text" class="form-control form-control-premium" name="asal_sekolah"
                        value="<?= htmlspecialchars($siswa['asal_sekolah']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">NPSN Sekolah</label>
                    <input type="text" class="form-control form-control-premium" name="npsn_sekolah"
                        value="<?= htmlspecialchars($siswa['npsn_sekolah']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label-premium">Alamat Sekolah Asal</label>
                    <textarea name="alamat_sekolah" class="form-control form-control-premium"
                        rows="2"><?= htmlspecialchars($siswa['alamat_sekolah']) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Section 4: Data Orang Tua -->
        <div class="edit-card">
            <div class="section-header">
                <i class="fas fa-users"></i>
                <h5 class="fw-bold mb-0">Data Orang Tua</h5>
            </div>
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label-premium">Nomor Kartu Keluarga (KK)</label>
                    <input type="text" class="form-control form-control-premium" name="no_kk"
                        value="<?= htmlspecialchars($siswa['no_kk']) ?>" required maxlength="16">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Status Orang Tua</label>
                    <select name="status_orang_tua" class="form-select form-control-premium" required>
                        <option value="Lengkap" <?= $siswa['status_orang_tua'] == 'Lengkap' ? 'selected' : '' ?>>Lengkap
                        </option>
                        <option value="Yatim" <?= $siswa['status_orang_tua'] == 'Yatim' ? 'selected' : '' ?>>Yatim</option>
                        <option value="Piatu" <?= $siswa['status_orang_tua'] == 'Piatu' ? 'selected' : '' ?>>Piatu</option>
                        <option value="Yatim Piatu" <?= $siswa['status_orang_tua'] == 'Yatim Piatu' ? 'selected' : '' ?>>
                            Yatim Piatu</option>
                        <option value="Cerai" <?= $siswa['status_orang_tua'] == 'Cerai' ? 'selected' : '' ?>>Cerai</option>
                    </select>
                </div>
            </div>

            <h6 class="text-primary fw-bold mb-3">Data Ayah Kandung</h6>
            <div class="row g-4 mb-4 border-bottom pb-4">
                <div class="col-md-6">
                    <label class="form-label-premium">Nama Ayah</label>
                    <input type="text" class="form-control form-control-premium" name="nama_ayah"
                        value="<?= htmlspecialchars($siswa['nama_ayah'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">NIK Ayah</label>
                    <input type="text" class="form-control form-control-premium" name="nik_ayah"
                        value="<?= htmlspecialchars($siswa['nik_ayah'] ?? '') ?>" maxlength="16">
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Pendidikan Ayah</label>
                    <select name="pendidikan_ayah" class="form-select form-control-premium">
                        <option value="">Pilih...</option>
                        <option value="SD" <?= ($siswa['pendidikan_ayah'] ?? '') == 'SD' ? 'selected' : '' ?>>SD</option>
                        <option value="SMP" <?= ($siswa['pendidikan_ayah'] ?? '') == 'SMP' ? 'selected' : '' ?>>SMP
                        </option>
                        <option value="SMA" <?= ($siswa['pendidikan_ayah'] ?? '') == 'SMA' ? 'selected' : '' ?>>SMA
                        </option>
                        <option value="D1/D2/D3" <?= ($siswa['pendidikan_ayah'] ?? '') == 'D1/D2/D3' ? 'selected' : '' ?>>
                            D1/D2/D3</option>
                        <option value="S1" <?= ($siswa['pendidikan_ayah'] ?? '') == 'S1' ? 'selected' : '' ?>>S1</option>
                        <option value="S2" <?= ($siswa['pendidikan_ayah'] ?? '') == 'S2' ? 'selected' : '' ?>>S2</option>
                        <option value="S3" <?= ($siswa['pendidikan_ayah'] ?? '') == 'S3' ? 'selected' : '' ?>>S3</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Pekerjaan Ayah</label>
                    <input type="text" class="form-control form-control-premium" name="pekerjaan_ayah"
                        value="<?= htmlspecialchars($siswa['pekerjaan_ayah'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">No. HP Ayah</label>
                    <input type="text" class="form-control form-control-premium" name="no_hp_ayah"
                        value="<?= htmlspecialchars($siswa['no_hp_ayah'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Tempat Lahir Ayah</label>
                    <input type="text" class="form-control form-control-premium" name="tempat_lahir_ayah"
                        value="<?= htmlspecialchars($siswa['tempat_lahir_ayah'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Tanggal Lahir Ayah</label>
                    <input type="date" class="form-control form-control-premium" name="tanggal_lahir_ayah"
                        value="<?= $siswa['tanggal_lahir_ayah'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Penghasilan Ayah</label>
                    <select name="penghasilan_ayah" class="form-select form-control-premium">
                        <option value="">Pilih...</option>
                        <option value="< 1 Juta" <?= ($siswa['penghasilan_ayah'] ?? '') == '< 1 Juta' ? 'selected' : '' ?>>
                            Kurang dari 1 Juta</option>
                        <option value="1-3 Juta" <?= ($siswa['penghasilan_ayah'] ?? '') == '1-3 Juta' ? 'selected' : '' ?>>
                            1 - 3 Juta</option>
                        <option value="3-5 Juta" <?= ($siswa['penghasilan_ayah'] ?? '') == '3-5 Juta' ? 'selected' : '' ?>>
                            3 - 5 Juta</option>
                        <option value="> 5 Juta" <?= ($siswa['penghasilan_ayah'] ?? '') == '> 5 Juta' ? 'selected' : '' ?>>
                            Lebih dari 5 Juta</option>
                        <option value="Tidak Ada" <?= ($siswa['penghasilan_ayah'] ?? '') == 'Tidak Ada' ? 'selected' : '' ?>>Tidak Ada</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Provinsi Ayah</label>
                    <input type="text" class="form-control form-control-premium" name="provinsi_ayah"
                        value="<?= htmlspecialchars($siswa['provinsi_ayah'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Kab/Kota Ayah</label>
                    <input type="text" class="form-control form-control-premium" name="kabupaten_kota_ayah"
                        value="<?= htmlspecialchars($siswa['kabupaten_kota_ayah'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Kecamatan Ayah</label>
                    <input type="text" class="form-control form-control-premium" name="kecamatan_ayah"
                        value="<?= htmlspecialchars($siswa['kecamatan_ayah'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Kelurahan/Desa Ayah</label>
                    <input type="text" class="form-control form-control-premium" name="desa_kelurahan_ayah"
                        value="<?= htmlspecialchars($siswa['desa_kelurahan_ayah'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label-premium">Alamat Lengkap Ayah</label>
                    <textarea name="alamat_ayah" class="form-control form-control-premium" rows="2"
                        placeholder="Nama jalan, nomor rumah, RT/RW"><?= htmlspecialchars($siswa['alamat_ayah'] ?? '') ?></textarea>
                </div>
            </div>

            <h6 class="text-danger fw-bold mb-3">Data Ibu Kandung</h6>
            <div class="row g-4 mb-4 border-bottom pb-4">
                <div class="col-md-6">
                    <label class="form-label-premium">Nama Ibu</label>
                    <input type="text" class="form-control form-control-premium" name="nama_ibu"
                        value="<?= htmlspecialchars($siswa['nama_ibu'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">NIK Ibu</label>
                    <input type="text" class="form-control form-control-premium" name="nik_ibu"
                        value="<?= htmlspecialchars($siswa['nik_ibu'] ?? '') ?>" maxlength="16">
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Pendidikan Ibu</label>
                    <select name="pendidikan_ibu" class="form-select form-control-premium">
                        <option value="">Pilih...</option>
                        <option value="SD" <?= ($siswa['pendidikan_ibu'] ?? '') == 'SD' ? 'selected' : '' ?>>SD</option>
                        <option value="SMP" <?= ($siswa['pendidikan_ibu'] ?? '') == 'SMP' ? 'selected' : '' ?>>SMP</option>
                        <option value="SMA" <?= ($siswa['pendidikan_ibu'] ?? '') == 'SMA' ? 'selected' : '' ?>>SMA</option>
                        <option value="D1/D2/D3" <?= ($siswa['pendidikan_ibu'] ?? '') == 'D1/D2/D3' ? 'selected' : '' ?>>
                            D1/D2/D3</option>
                        <option value="S1" <?= ($siswa['pendidikan_ibu'] ?? '') == 'S1' ? 'selected' : '' ?>>S1</option>
                        <option value="S2" <?= ($siswa['pendidikan_ibu'] ?? '') == 'S2' ? 'selected' : '' ?>>S2</option>
                        <option value="S3" <?= ($siswa['pendidikan_ibu'] ?? '') == 'S3' ? 'selected' : '' ?>>S3</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Pekerjaan Ibu</label>
                    <input type="text" class="form-control form-control-premium" name="pekerjaan_ibu"
                        value="<?= htmlspecialchars($siswa['pekerjaan_ibu'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">No. HP Ibu</label>
                    <input type="text" class="form-control form-control-premium" name="no_hp_ibu"
                        value="<?= htmlspecialchars($siswa['no_hp_ibu'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Tempat Lahir Ibu</label>
                    <input type="text" class="form-control form-control-premium" name="tempat_lahir_ibu"
                        value="<?= htmlspecialchars($siswa['tempat_lahir_ibu'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Tanggal Lahir Ibu</label>
                    <input type="date" class="form-control form-control-premium" name="tanggal_lahir_ibu"
                        value="<?= $siswa['tanggal_lahir_ibu'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Penghasilan Ibu</label>
                    <select name="penghasilan_ibu" class="form-select form-control-premium">
                        <option value="">Pilih...</option>
                        <option value="< 1 Juta" <?= ($siswa['penghasilan_ibu'] ?? '') == '< 1 Juta' ? 'selected' : '' ?>>
                            Kurang dari 1 Juta</option>
                        <option value="1-3 Juta" <?= ($siswa['penghasilan_ibu'] ?? '') == '1-3 Juta' ? 'selected' : '' ?>>1
                            - 3 Juta</option>
                        <option value="3-5 Juta" <?= ($siswa['penghasilan_ibu'] ?? '') == '3-5 Juta' ? 'selected' : '' ?>>3
                            - 5 Juta</option>
                        <option value="> 5 Juta" <?= ($siswa['penghasilan_ibu'] ?? '') == '> 5 Juta' ? 'selected' : '' ?>>
                            Lebih dari 5 Juta</option>
                        <option value="Tidak Ada" <?= ($siswa['penghasilan_ibu'] ?? '') == 'Tidak Ada' ? 'selected' : '' ?>>Tidak Ada</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Provinsi Ibu</label>
                    <input type="text" class="form-control form-control-premium" name="provinsi_ibu"
                        value="<?= htmlspecialchars($siswa['provinsi_ibu'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Kab/Kota Ibu</label>
                    <input type="text" class="form-control form-control-premium" name="kabupaten_kota_ibu"
                        value="<?= htmlspecialchars($siswa['kabupaten_kota_ibu'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Kecamatan Ibu</label>
                    <input type="text" class="form-control form-control-premium" name="kecamatan_ibu"
                        value="<?= htmlspecialchars($siswa['kecamatan_ibu'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Kelurahan/Desa Ibu</label>
                    <input type="text" class="form-control form-control-premium" name="desa_kelurahan_ibu"
                        value="<?= htmlspecialchars($siswa['desa_kelurahan_ibu'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label-premium">Alamat Lengkap Ibu</label>
                    <textarea name="alamat_ibu" class="form-control form-control-premium" rows="2"
                        placeholder="Nama jalan, nomor rumah, RT/RW"><?= htmlspecialchars($siswa['alamat_ibu'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Data Wali (Optional) -->
            <h6 class="text-warning fw-bold mb-3 mt-4">Data Wali (Opsional)</h6>
            <div class="row g-4 mb-4 border-bottom pb-4">
                <div class="col-md-6">
                    <label class="form-label-premium">Nama Wali</label>
                    <input type="text" class="form-control form-control-premium" name="nama_wali"
                        value="<?= htmlspecialchars($siswa['nama_wali'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">NIK Wali</label>
                    <input type="text" class="form-control form-control-premium" name="nik_wali"
                        value="<?= htmlspecialchars($siswa['nik_wali'] ?? '') ?>" maxlength="16">
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Tempat Lahir Wali</label>
                    <input type="text" class="form-control form-control-premium" name="tempat_lahir_wali"
                        value="<?= htmlspecialchars($siswa['tempat_lahir_wali'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Tanggal Lahir Wali</label>
                    <input type="date" class="form-control form-control-premium" name="tanggal_lahir_wali"
                        value="<?= $siswa['tanggal_lahir_wali'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Pendidikan Wali</label>
                    <select name="pendidikan_wali" class="form-select form-control-premium">
                        <option value="">Pilih...</option>
                        <option value="SD" <?= ($siswa['pendidikan_wali'] ?? '') == 'SD' ? 'selected' : '' ?>>SD</option>
                        <option value="SMP" <?= ($siswa['pendidikan_wali'] ?? '') == 'SMP' ? 'selected' : '' ?>>SMP
                        </option>
                        <option value="SMA" <?= ($siswa['pendidikan_wali'] ?? '') == 'SMA' ? 'selected' : '' ?>>SMA
                        </option>
                        <option value="D1/D2/D3" <?= ($siswa['pendidikan_wali'] ?? '') == 'D1/D2/D3' ? 'selected' : '' ?>>
                            D1/D2/D3</option>
                        <option value="S1" <?= ($siswa['pendidikan_wali'] ?? '') == 'S1' ? 'selected' : '' ?>>S1</option>
                        <option value="S2" <?= ($siswa['pendidikan_wali'] ?? '') == 'S2' ? 'selected' : '' ?>>S2</option>
                        <option value="S3" <?= ($siswa['pendidikan_wali'] ?? '') == 'S3' ? 'selected' : '' ?>>S3</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Pekerjaan Wali</label>
                    <input type="text" class="form-control form-control-premium" name="pekerjaan_wali"
                        value="<?= htmlspecialchars($siswa['pekerjaan_wali'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Penghasilan Wali</label>
                    <select name="penghasilan_wali" class="form-select form-control-premium">
                        <option value="">Pilih...</option>
                        <option value="< 1 Juta" <?= ($siswa['penghasilan_wali'] ?? '') == '< 1 Juta' ? 'selected' : '' ?>>
                            Kurang dari 1 Juta</option>
                        <option value="1-3 Juta" <?= ($siswa['penghasilan_wali'] ?? '') == '1-3 Juta' ? 'selected' : '' ?>>
                            1 - 3 Juta</option>
                        <option value="3-5 Juta" <?= ($siswa['penghasilan_wali'] ?? '') == '3-5 Juta' ? 'selected' : '' ?>>
                            3 - 5 Juta</option>
                        <option value="> 5 Juta" <?= ($siswa['penghasilan_wali'] ?? '') == '> 5 Juta' ? 'selected' : '' ?>>
                            Lebih dari 5 Juta</option>
                        <option value="Tidak Ada" <?= ($siswa['penghasilan_wali'] ?? '') == 'Tidak Ada' ? 'selected' : '' ?>>Tidak Ada</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">No. HP Wali</label>
                    <input type="text" class="form-control form-control-premium" name="no_hp_wali"
                        value="<?= htmlspecialchars($siswa['no_hp_wali'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Provinsi Wali</label>
                    <input type="text" class="form-control form-control-premium" name="provinsi_wali"
                        value="<?= htmlspecialchars($siswa['provinsi_wali'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Kab/Kota Wali</label>
                    <input type="text" class="form-control form-control-premium" name="kabupaten_kota_wali"
                        value="<?= htmlspecialchars($siswa['kabupaten_kota_wali'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Kecamatan Wali</label>
                    <input type="text" class="form-control form-control-premium" name="kecamatan_wali"
                        value="<?= htmlspecialchars($siswa['kecamatan_wali'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Kelurahan/Desa Wali</label>
                    <input type="text" class="form-control form-control-premium" name="desa_kelurahan_wali"
                        value="<?= htmlspecialchars($siswa['desa_kelurahan_wali'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label-premium">Alamat Lengkap Wali</label>
                    <textarea name="alamat_wali" class="form-control form-control-premium" rows="2"
                        placeholder="Abaikan jika tidak ada wali"><?= htmlspecialchars($siswa['alamat_wali'] ?? '') ?></textarea>
                </div>
            </div>

            <h6 class="text-success fw-bold mb-3">Kontak WhatsApp Notifikasi (Aktif)</h6>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label-premium">Nomor WhatsApp</label>
                    <input type="text" class="form-control form-control-premium" name="kontak_wa"
                        value="<?= htmlspecialchars($siswa['kontak_wa']) ?>" required placeholder="08xxxx">
                </div>
                <div class="col-md-6">
                    <label class="form-label-premium">Atas Nama Kontak</label>
                    <input type="text" class="form-control form-control-premium" name="nama_kontak_wa"
                        value="<?= htmlspecialchars($siswa['nama_kontak_wa']) ?>" required>
                </div>
            </div>
        </div>

        <!-- Section 5: Nilai Rapor -->
        <div class="edit-card">
            <div class="section-header">
                <i class="fas fa-list-ol"></i>
                <h5 class="fw-bold mb-0">Rekap Nilai Rapor</h5>
            </div>
            <div class="alert alert-warning small rounded-4 shadow-sm">
                <i class="fas fa-info-circle me-2"></i> Input nilai pengetahuan (angka 0-100). Sistem akan menghitung
                rata-rata secara otomatis.
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label-premium">Kls 4 Sem 1</label>
                    <input type="number" step="0.01" class="form-control form-control-premium grade-input"
                        name="nilai_k4_s1" value="<?= $siswa['nilai_k4_s1'] ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Kls 4 Sem 2</label>
                    <input type="number" step="0.01" class="form-control form-control-premium grade-input"
                        name="nilai_k4_s2" value="<?= $siswa['nilai_k4_s2'] ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Kls 5 Sem 1</label>
                    <input type="number" step="0.01" class="form-control form-control-premium grade-input"
                        name="nilai_k5_s1" value="<?= $siswa['nilai_k5_s1'] ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Kls 5 Sem 2</label>
                    <input type="number" step="0.01" class="form-control form-control-premium grade-input"
                        name="nilai_k5_s2" value="<?= $siswa['nilai_k5_s2'] ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Kls 6 Sem 1</label>
                    <input type="number" step="0.01" class="form-control form-control-premium grade-input"
                        name="nilai_k6_s1" value="<?= $siswa['nilai_k6_s1'] ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label-premium">Rata-rata Terhitung</label>
                    <div class="bg-light p-2 rounded-3 text-center fw-bold fs-5 border" id="avg-display">
                        <?= number_format($siswa['nilai_rapor_rata2'], 2) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 6: Berkas Persyaratan (Re-upload) -->
        <div class="edit-card">
            <div class="section-header">
                <i class="fas fa-file-upload"></i>
                <h5 class="fw-bold mb-0">Berkas Persyaratan</h5>
            </div>
            <p class="text-muted small mb-4">Pastikan seluruh berkas sudah diupload dengan benar. Jika ada kesalahan,
                silakan klik tombol di bawah untuk mengelola dan mengunggah ulang berkas Anda.</p>

            <div class="bg-light p-4 rounded-4 border-dashed text-center">
                <div class="mb-3">
                    <i class="fas fa-folder-open fa-3x text-muted opacity-50"></i>
                </div>
                <h6 class="fw-bold">Manajemen Berkas & Dokumen</h6>
                <p class="small text-muted px-lg-5">Anda dapat mengganti foto, rapor, KK, akta, dan dokumen pendukung
                    lainnya melalui halaman khusus berkas.</p>
                <a href="upload_berkas.php" class="btn btn-primary rounded-pill px-4 py-2 mt-2">
                    <i class="fas fa-sync-alt me-2"></i> Kelola Berkas & Re-upload
                </a>
            </div>
        </div>

        <div class="sticky-actions">
            <div>
                <a href="identitas.php" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
            </div>
            <button type="button" class="btn btn-premium-action px-5" onclick="handleUpdateSubmit()">
                <i class="fas fa-eye me-2"></i> Preview & Simpan
            </button>
        </div>
    </form>
</div>

    <!-- Modal Preview Data -->
    <div class="modal fade" id="previewDataModal" tabindex="-1" aria-labelledby="previewDataModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 bg-success text-white rounded-top-4 p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 p-3 rounded-3 me-3">
                            <i class="fas fa-eye fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-1" id="previewDataModalLabel">Pratinjau Perubahan Data</h5>
                            <small class="opacity-90">Pastikan data yang Anda perbaiki sudah benar sebelum disimpan</small>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-4" id="previewDataContent">
                    <!-- Content will be filled by JavaScript -->
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-lg btn-secondary rounded-pill px-4" onclick="closePreview()">
                        <i class="fas fa-edit me-2"></i>Kembali Edit
                    </button>
                    <button type="button" class="btn btn-lg btn-success rounded-pill px-5" onclick="submitUpdateForm()">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS if not already included in layout_bottom -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('.grade-input');
        const display = document.getElementById('avg-display');

        inputs.forEach(input => {
            input.addEventListener('input', function () {
                let total = 0;
                let count = 0;
                inputs.forEach(i => {
                    const val = parseFloat(i.value) || 0;
                    if (val > 0) {
                        total += val;
                        count++;
                    }
                });
                if (count > 0) {
                    const avg = total / count;
                    display.innerText = avg.toFixed(2);
                } else {
                    display.innerText = "0.00";
                }
            });
        });
    });

    function handleUpdateSubmit() {
        const form = document.getElementById('updateForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        showPreviewData();
    }

    function showPreviewData() {
        const form = document.getElementById('updateForm');
        const formData = new FormData(form);
        let html = '';

        // Generate HTML for preview (Similar to register.php but adapted for this form)
        html += `
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-primary text-white p-3">
                    <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Data Murid</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <tbody>
                            <tr><th width="35%" class="ps-4">Jalur Pendaftaran</th><td>${form.querySelector('select[name="jalur_id"] option:checked')?.text || '-'}</td></tr>
                            <tr><th class="ps-4">NISN</th><td>${formData.get('nisn') || '-'}</td></tr>
                            <tr><th class="ps-4">NIK</th><td>${formData.get('nik') || '-'}</td></tr>
                            <tr><th class="ps-4">Nama Lengkap</th><td>${formData.get('nama_lengkap') || '-'}</td></tr>
                            <tr><th class="ps-4">Tempat, Tanggal Lahir</th><td>${formData.get('tempat_lahir') || '-'}, ${formData.get('tanggal_lahir') || '-'}</td></tr>
                            <tr><th class="ps-4">Jenis Kelamin</th><td>${formData.get('jenis_kelamin') === 'L' ? 'Laki-laki' : 'Perempuan'}</td></tr>
                            <tr><th class="ps-4">No. HP</th><td>${formData.get('no_hp') || '-'}</td></tr>
                            <tr><th class="ps-4">Alamat</th><td>${formData.get('alamat') || '-'}</td></tr>
                            <tr><th class="ps-4">Kecamatan</th><td>${formData.get('kecamatan') || '-'}</td></tr>
                            <tr><th class="ps-4">WhatsApp Notifikasi</th><td>${formData.get('kontak_wa') || '-'} (a.n ${formData.get('nama_kontak_wa') || '-'})</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-success text-white p-3">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Data Orang Tua</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <tbody>
                            <tr><th width="35%" class="ps-4">No. KK</th><td>${formData.get('no_kk') || '-'}</td></tr>
                            <tr><th class="ps-4">Nama Ayah</th><td>${formData.get('nama_ayah') || '-'}</td></tr>
                            <tr><th class="ps-4">Nama Ibu</th><td>${formData.get('nama_ibu') || '-'}</td></tr>
                            <tr><th class="ps-4">Pekerjaan Ayah</th><td>${formData.get('pekerjaan_ayah') || '-'}</td></tr>
                            <tr><th class="ps-4">Pekerjaan Ibu</th><td>${formData.get('pekerjaan_ibu') || '-'}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-warning text-dark p-3">
                    <h5 class="mb-0"><i class="fas fa-list-ol me-2"></i>Rekap Nilai Rapor</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr><th>Semester</th><th class="text-center">Nilai</th></tr>
                        </thead>
                        <tbody>
                            <tr><td class="ps-4">Kelas 4 Sem 1</td><td class="text-center">${formData.get('nilai_k4_s1') || '-'}</td></tr>
                            <tr><td class="ps-4">Kelas 4 Sem 2</td><td class="text-center">${formData.get('nilai_k4_s2') || '-'}</td></tr>
                            <tr><td class="ps-4">Kelas 5 Sem 1</td><td class="text-center">${formData.get('nilai_k5_s1') || '-'}</td></tr>
                            <tr><td class="ps-4">Kelas 5 Sem 2</td><td class="text-center">${formData.get('nilai_k5_s2') || '-'}</td></tr>
                            <tr><td class="ps-4">Kelas 6 Sem 1</td><td class="text-center">${formData.get('nilai_k6_s1') || '-'}</td></tr>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr><th class="ps-4">RATA-RATA</th><th class="text-center">${document.getElementById('avg-display').innerText}</th></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `;

        document.getElementById('previewDataContent').innerHTML = html;
        const previewModal = new bootstrap.Modal(document.getElementById('previewDataModal'));
        previewModal.show();
    }

    function closePreview() {
        const modalEl = document.getElementById('previewDataModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
    }

    function submitUpdateForm() {
        document.getElementById('updateForm').submit();
    }
</script>

<?php require_once 'layout_bottom.php'; ?>