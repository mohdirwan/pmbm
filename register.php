<?php
require_once 'includes/config.php';

// Check PMBM status for registration access
$ppdb_status = get_setting('ppdb_status', 'belum');
if ($ppdb_status != 'buka') {
    die("<center>
    <h1>Maaf, Pendaftaran sedang ditutup.</h1><a href='index.php'>Kembali ke Beranda</a>
</center>");
}

check_rate_limit(); // Simple Anti-DDoS / Rate Limiting
$csrf_token = generate_csrf_token();

// Fetch Jalur Pendaftaran
$stmt_jalur = $pdo->query("SELECT * FROM jalur_pendaftaran ORDER BY id ASC");
$list_jalur = $stmt_jalur->fetchAll();

// Fetch Berkas Pilihan instruction text from settings
$raw_petunjuk = get_setting('berkas_pilihan_petunjuk', '');
$petunjuk_berkas_pilihan = [];
if (!empty($raw_petunjuk)) {
    $decoded = json_decode($raw_petunjuk, true);
    if (is_array($decoded)) {
        $petunjuk_berkas_pilihan = $decoded;
    }
}
if (empty($petunjuk_berkas_pilihan)) {
    $petunjuk_berkas_pilihan = [
        'Bagi calon murid yang menempati <strong>peringkat/ranking</strong>, silahkan upload <strong>surat keterangan peringkat/ranking</strong>.',
        'Bagi calon murid yang memiliki <strong>sertifikat prestasi</strong>, silahkan upload <strong>surat keterangan prestasi</strong> dan <strong>sertifikat prestasi</strong>.',
        'Bagi calon murid yang menempati peringkat dan memiliki sertifikat prestasi, silahkan upload <strong>suket peringkat/ranking, upload suket prestasi</strong> dan <strong>sertifikat prestasi</strong>.',
    ];
}

// Check Dummy Register setting
$dummy_register = get_setting('dummy_register', '0');

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pendaftaran - PMBM MTsN 1 Kota Pekanbaru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <?php if ($favicon = get_setting('school_logo')): ?>
        <link rel="icon" type="image/x-icon" href="<?= BASE_URL . $favicon ?>">
    <?php endif; ?>
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-animated-madrasah">

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand text-white" href="index.php"><i class="fas fa-chevron-left me-2"></i> Kembali ke
                Beranda</a>
        </div>
    </nav>

    <?php if ($dummy_register == '1'): ?>
        <div class="container mt-3">
            <div class="alert alert-warning text-center fw-bold shadow-sm rounded-3">
                <i class="fas fa-exclamation-triangle me-2"></i> Mode Dummy Register Aktif: Field wajib dinonaktifkan
                sementara.
            </div>
        </div>
    <?php endif; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-5">
                    <h2 class="fw-bold text-white">Formulir Pendaftaran</h2>
                    <p class="text-white-50">Lengkapi data di bawah ini dengan benar dan jujur.</p>
                </div>

                <div class="form-container">
                    <!-- Progress Bar -->
                    <div class="step-indicator">
                        <div class="step active" id="step1-indicator" title="Data Murid" onclick="goToStep(1)"
                            style="cursor: pointer;"><i class="fas fa-user-graduate"></i></div>
                        <div class="step" id="step2-indicator" title="Asal Sekolah" onclick="goToStep(2)"
                            style="cursor: pointer;"><i class="fas fa-school"></i></div>
                        <div class="step" id="step3-indicator" title="Orang Tua" onclick="goToStep(3)"
                            style="cursor: pointer;"><i class="fas fa-users"></i></div>
                        <div class="step" id="step4-indicator" title="Rekap Nilai" onclick="goToStep(4)"
                            style="cursor: pointer;"><i class="fas fa-list-ol"></i>
                        </div>
                    </div>

                    <form action="process_register.php" method="POST" enctype="multipart/form-data" id="pmbmForm">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                        <!-- Step 1: Data Murid -->
                        <div class="form-step" id="step1">
                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-route me-2"></i>Pilih Jalur
                                    Pendaftaran <span class="text-danger">*</span></label>
                                <select name="jalur_id" class="form-select form-control-lg bg-light" required>
                                    <option value="">-- Pilih Jalur --</option>
                                    <?php foreach ($list_jalur as $j): ?>
                                        <option value="<?= $j['id'] ?>">
                                            <?= htmlspecialchars($j['nama_jalur']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-muted">Pilih sesuai dengan kriteria dan persyaratan yang Anda
                                    miliki.</div>
                            </div>

                            <h4 class="mb-4"><i class="fas fa-user-graduate me-2"></i>Data Pribadi Murid</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">NISN (Nomor Induk Murid Nasional) <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nisn" id="nisn_input" required
                                        placeholder="10 digit NISN" maxlength="10" inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    <div id="nisn_feedback" class="invalid-feedback">NISN ini sudah terdaftar!</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">NIK (Nomor Induk Kependudukan) <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nik" id="nik_input" required
                                        placeholder="16 digit NIK" maxlength="16" inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    <div id="nik_feedback" class="invalid-feedback">NIK ini sudah terdaftar!</div>
                                </div>

                                <!-- Akun Calon Murid -->
                                <div class="col-md-6">
                                    <label class="form-label">Buat Password Login <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="password" id="password_input" required placeholder="Minimal 6 karakter">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_input')">
                                            <i class="fas fa-eye" id="icon_password_input"></i>
                                        </button>
                                    </div>
                                    <div id="password_length_feedback" class="small mt-1"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="confirm_password" id="confirm_password_input" required placeholder="Ulangi password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password_input')">
                                            <i class="fas fa-eye" id="icon_confirm_password_input"></i>
                                        </button>
                                    </div>
                                    <div id="password_match_feedback" class="small mt-1"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Nama Lengkap Murid <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_lengkap" required
                                        style="text-transform: uppercase;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="tempat_lahir" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="tanggal_lahir" id="tgl_lahir_input"
                                        placeholder="pilih tanggal (dd/mm/yyyy)" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select class="form-select" name="jenis_kelamin" required>
                                        <option value="">Pilih...</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Agama</label>
                                    <select class="form-select" name="agama" required>
                                        <option value="Islam">Islam</option>

                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Anak Ke <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="anak_ke" required
                                        placeholder="Contoh: 1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status Dalam Keluarga <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" name="status_keluarga" required>
                                        <option value="">Pilih...</option>
                                        <option value="Anak Kandung">Anak Kandung</option>
                                        <option value="Anak Tiri">Anak Tiri</option>
                                        <option value="Anak Angkat">Anak Angkat</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No HP Murid <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="no_hp" required
                                        placeholder="Contoh: 081234567890" maxlength="13" inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Hobi</label>
                                    <input type="text" class="form-control" name="hobi"
                                        placeholder="Contoh: Membaca, Olahraga">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Provinsi <span class="text-danger">*</span></label>
                                    <select class="form-select" name="provinsi" id="provinsi" required>
                                        <option value="">Pilih Provinsi...</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label>
                                    <select class="form-select" name="kabupaten_kota" id="kabupaten_kota" required disabled>
                                        <option value="">Pilih Kab/Kota...</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kecamatan <span class="text-danger">*</span></label>
                                    <select class="form-select" name="kecamatan" id="kecamatan" required disabled>
                                        <option value="">Pilih Kecamatan...</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kelurahan/Desa <span class="text-danger">*</span></label>
                                    <select class="form-select" name="desa_kelurahan" id="desa_kelurahan" required disabled>
                                        <option value="">Pilih Kelurahan/Desa...</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="alamat" rows="2" required placeholder="Nama jalan, nomor rumah, RT/RW"></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status Tempat Tinggal <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" name="status_tinggal" required>
                                        <option value="">Pilih...</option>
                                        <option value="Bersama Orang Tua">Bersama Orang Tua</option>
                                        <option value="Asrama">Asrama</option>
                                        <option value="Kost">Kost</option>
                                        <option value="Bersama Wali">Bersama Wali</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jarak ke Sekolah (Km) <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="jarak_sekolah" required
                                        placeholder="Contoh: 2 Km atau 2.5 Km">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Transportasi dari Rumah <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" name="transportasi_rumah" required>
                                        <option value="">Pilih...</option>
                                        <option value="Jalan Kaki">Jalan Kaki</option>
                                        <option value="Sepeda Motor">Sepeda Motor</option>
                                        <option value="Mobil Pribadi">Mobil Pribadi</option>
                                        <option value="Antar Jemput">Antar Jemput</option>
                                        <option value="Ojek">Ojek</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="button" class="btn btn-premium px-4" onclick="nextStep(2)">Lanjut <i
                                        class="fas fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Step 2: Data Sekolah Asal -->
                        <div class="form-step d-none" id="step2">
                            <h4 class="mb-4"><i class="fas fa-school me-2"></i>Data Sekolah Asal</h4>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Nama Sekolah Asal (SD/MI) <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="asal_sekolah" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">NPSN Sekolah Asal</label>
                                    <input type="text" class="form-control" name="npsn_sekolah">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Alamat Sekolah Asal</label>
                                    <textarea class="form-control" name="alamat_sekolah" rows="2"
                                        placeholder="Alamat lengkap sekolah asal"></textarea>
                                </div>
                            </div>
                            <div class="mt-4 d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary px-4"
                                    onclick="prevStep(1)">Kembali</button>
                                <button type="button" class="btn btn-premium px-4" onclick="nextStep(3)">Lanjut <i
                                        class="fas fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Step 3: Data Orang Tua -->
                        <div class="form-step d-none" id="step3">
                            <h4 class="mb-4"><i class="fas fa-users me-2"></i>Data Orang Tua & Wali</h4>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Nomor Kartu Keluarga (KK) <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" name="no_kk" required
                                    placeholder="16 digit Nomor KK" maxlength="16" inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Status Orang Tua <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" name="status_orang_tua" id="status_orang_tua" required>
                                    <option value="">Pilih Status...</option>
                                    <option value="Lengkap">Lengkap (Ayah & Ibu)</option>
                                    <option value="Yatim">Yatim (Ayah Meninggal)</option>
                                    <option value="Piatu">Piatu (Ibu Meninggal)</option>
                                    <option value="Yatim Piatu">Yatim Piatu (Keduanya Meninggal)</option>
                                    <option value="Cerai">Cerai</option>
                                </select>
                            </div>

                            <h5 class="text-success mt-4 mb-3 border-bottom pb-2">Informasi Ayah Kandung</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Lengkap Ayah <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_ayah" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">NIK Ayah <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nik_ayah" required
                                        placeholder="16 digit NIK Ayah" maxlength="16" inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tempat Lahir Ayah <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="tempat_lahir_ayah" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Lahir Ayah <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="tanggal_lahir_ayah" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Pendidikan Terakhir Ayah <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" name="pendidikan_ayah" required>
                                        <option value="">Pilih...</option>
                                        <option value="SD">SD/Sederajat</option>
                                        <option value="SMP">SMP/Sederajat</option>
                                        <option value="SMA">SMA/Sederajat</option>
                                        <option value="D1/D2/D3">D1/D2/D3</option>
                                        <option value="S1">S1</option>
                                        <option value="S2">S2</option>
                                        <option value="S3">S3</option>
                                        <option value="Tidak Sekolah">Tidak Sekolah</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pekerjaan Ayah <span class="text-danger">*</span></label>
                                    <select class="form-select" name="pekerjaan_ayah" required>
                                        <option value="">Pilih...</option>
                                        <option value="PNS">PNS</option>
                                        <option value="TNI/Polri">TNI/Polri</option>
                                        <option value="Pegawai Swasta">Pegawai Swasta</option>
                                        <option value="Wiraswasta">Wiraswasta</option>
                                        <option value="Petani/Nelayan">Petani/Nelayan</option>
                                        <option value="Buruh">Buruh</option>
                                        <option value="Lainnya">Lainnya</option>
                                        <option value="Tidak Bekerja">Tidak Bekerja</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Penghasilan Rata/Bulan <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" name="penghasilan_ayah" required>
                                        <option value="">Pilih...</option>
                                        <option value="< 1 Juta">Kurang dari 1 Juta</option>
                                        <option value="1-3 Juta">1 - 3 Juta</option>
                                        <option value="3-5 Juta">3 - 5 Juta</option>
                                        <option value="> 5 Juta">Lebih dari 5 Juta</option>
                                        <option value="Tidak Ada">Tidak Ada</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No HP/WA Ayah <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="no_hp_ayah" required>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="sama_alamat_ayah" name="sama_alamat_ayah">
                                        <label class="form-check-label fw-bold text-primary" for="sama_alamat_ayah">Alamat Ayah sama dengan Alamat Murid</label>
                                    </div>
                                </div>
                                <div id="section_alamat_ayah" class="col-12">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Provinsi Ayah <span class="text-danger">*</span></label>
                                            <select class="form-select address-prov" name="provinsi_ayah" id="provinsi_ayah" required>
                                                <option value="">Pilih Provinsi...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Kab/Kota Ayah <span class="text-danger">*</span></label>
                                            <select class="form-select address-kab" name="kabupaten_kota_ayah" id="kabupaten_kota_ayah" required disabled>
                                                <option value="">Pilih Kab/Kota...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Kecamatan Ayah <span class="text-danger">*</span></label>
                                            <select class="form-select address-kec" name="kecamatan_ayah" id="kecamatan_ayah" required disabled>
                                                <option value="">Pilih Kecamatan...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Kelurahan/Desa Ayah <span class="text-danger">*</span></label>
                                            <select class="form-select address-desa" name="desa_kelurahan_ayah" id="desa_kelurahan_ayah" required disabled>
                                                <option value="">Pilih Kelurahan/Desa...</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Alamat Lengkap Ayah <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="alamat_ayah" id="alamat_ayah" rows="2" required placeholder="Nama jalan, nomor rumah, RT/RW"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h5 class="text-success mt-4 mb-3 border-bottom pb-2">Informasi Ibu Kandung</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Lengkap Ibu <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_ibu" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">NIK Ibu <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nik_ibu" required
                                        placeholder="16 digit NIK Ibu" maxlength="16" inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tempat Lahir Ibu <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="tempat_lahir_ibu" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Lahir Ibu <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="tanggal_lahir_ibu" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pendidikan Terakhir Ibu <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" name="pendidikan_ibu" required>
                                        <option value="">Pilih...</option>
                                        <option value="SD">SD/Sederajat</option>
                                        <option value="SMP">SMP/Sederajat</option>
                                        <option value="SMA">SMA/Sederajat</option>
                                        <option value="D1/D2/D3">D1/D2/D3</option>
                                        <option value="S1">S1</option>
                                        <option value="S2">S2</option>
                                        <option value="S3">S3</option>
                                        <option value="Tidak Sekolah">Tidak Sekolah</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pekerjaan Ibu <span class="text-danger">*</span></label>
                                    <select class="form-select" name="pekerjaan_ibu" required>
                                        <option value="">Pilih...</option>
                                        <option value="IRT">Ibu Rumah Tangga</option>
                                        <option value="PNS">PNS</option>
                                        <option value="TNI/Polri">TNI/Polri</option>
                                        <option value="Pegawai Swasta">Pegawai Swasta</option>
                                        <option value="Wiraswasta">Wiraswasta</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Penghasilan Rata/Bulan <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" name="penghasilan_ibu" required>
                                        <option value="">Pilih...</option>
                                        <option value="< 1 Juta">Kurang dari 1 Juta</option>
                                        <option value="1-3 Juta">1 - 3 Juta</option>
                                        <option value="3-5 Juta">3 - 5 Juta</option>
                                        <option value="> 5 Juta">Lebih dari 5 Juta</option>
                                        <option value="Tidak Ada">Tidak Ada</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No HP/WA Ibu <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="no_hp_ibu" required>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="sama_alamat_ibu" name="sama_alamat_ibu">
                                        <label class="form-check-label fw-bold text-primary" for="sama_alamat_ibu">Alamat Ibu sama dengan Alamat Murid</label>
                                    </div>
                                </div>
                                <div id="section_alamat_ibu" class="col-12">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Provinsi Ibu <span class="text-danger">*</span></label>
                                            <select class="form-select address-prov" name="provinsi_ibu" id="provinsi_ibu" required>
                                                <option value="">Pilih Provinsi...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Kab/Kota Ibu <span class="text-danger">*</span></label>
                                            <select class="form-select address-kab" name="kabupaten_kota_ibu" id="kabupaten_kota_ibu" required disabled>
                                                <option value="">Pilih Kab/Kota...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Kecamatan Ibu <span class="text-danger">*</span></label>
                                            <select class="form-select address-kec" name="kecamatan_ibu" id="kecamatan_ibu" required disabled>
                                                <option value="">Pilih Kecamatan...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Kelurahan/Desa Ibu <span class="text-danger">*</span></label>
                                            <select class="form-select address-desa" name="desa_kelurahan_ibu" id="desa_kelurahan_ibu" required disabled>
                                                <option value="">Pilih Kelurahan/Desa...</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Alamat Lengkap Ibu <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="alamat_ibu" id="alamat_ibu" rows="2" required placeholder="Nama jalan, nomor rumah, RT/RW"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h5 class="text-success mt-4 mb-3 border-bottom pb-2">Informasi Wali (Opsional)</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" id="label_nama_wali">Nama Lengkap Wali</label>
                                    <input type="text" class="form-control" name="nama_wali" id="nama_wali">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" id="label_nik_wali">NIK Wali</label>
                                    <input type="text" class="form-control" name="nik_wali" id="nik_wali"
                                        placeholder="16 digit NIK Wali" maxlength="16" inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tempat Lahir Wali</label>
                                    <input type="text" class="form-control" name="tempat_lahir_wali">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Lahir Wali</label>
                                    <input type="date" class="form-control" name="tanggal_lahir_wali">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pendidikan Terakhir Wali</label>
                                    <select class="form-select" name="pendidikan_wali">
                                        <option value="">Pilih...</option>
                                        <option value="SD">SD/Sederajat</option>
                                        <option value="SMP">SMP/Sederajat</option>
                                        <option value="SMA">SMA/Sederajat</option>
                                        <option value="D1/D2/D3">D1/D2/D3</option>
                                        <option value="S1">S1</option>
                                        <option value="S2">S2</option>
                                        <option value="S3">S3</option>
                                        <option value="Tidak Sekolah">Tidak Sekolah</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" id="label_pekerjaan_wali">Pekerjaan Wali</label>
                                    <select class="form-select" name="pekerjaan_wali" id="pekerjaan_wali">
                                        <option value="">Pilih...</option>
                                        <option value="PNS">PNS</option>
                                        <option value="TNI/Polri">TNI/Polri</option>
                                        <option value="Pegawai Swasta">Pegawai Swasta</option>
                                        <option value="Wiraswasta">Wiraswasta</option>
                                        <option value="Petani/Nelayan">Petani/Nelayan</option>
                                        <option value="Buruh">Buruh</option>
                                        <option value="Lainnya">Lainnya</option>
                                        <option value="Tidak Bekerja">Tidak Bekerja</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Penghasilan Rata/Bulan</label>
                                    <select class="form-select" name="penghasilan_wali">
                                        <option value="">Pilih...</option>
                                        <option value="< 1 Juta">Kurang dari 1 Juta</option>
                                        <option value="1-3 Juta">1 - 3 Juta</option>
                                        <option value="3-5 Juta">3 - 5 Juta</option>
                                        <option value="> 5 Juta">Lebih dari 5 Juta</option>
                                        <option value="Tidak Ada">Tidak Ada</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" id="label_no_hp_wali">No HP/WA Wali</label>
                                    <input type="text" class="form-control" name="no_hp_wali" id="no_hp_wali">
                                </div>
                                <div id="section_alamat_wali" class="col-12">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Provinsi Wali</label>
                                            <select class="form-select address-prov" name="provinsi_wali" id="provinsi_wali">
                                                <option value="">Pilih Provinsi...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Kab/Kota Wali</label>
                                            <select class="form-select address-kab" name="kabupaten_kota_wali" id="kabupaten_kota_wali" disabled>
                                                <option value="">Pilih Kab/Kota...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Kecamatan Wali</label>
                                            <select class="form-select address-kec" name="kecamatan_wali" id="kecamatan_wali" disabled>
                                                <option value="">Pilih Kecamatan...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Kelurahan/Desa Wali</label>
                                            <select class="form-select address-desa" name="desa_kelurahan_wali" id="desa_kelurahan_wali" disabled>
                                                <option value="">Pilih Kelurahan/Desa...</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" id="label_alamat_wali">Alamat Lengkap Wali</label>
                                            <textarea class="form-control" name="alamat_wali" id="alamat_wali" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kontak yang Bisa Dihubungi -->
                            <div class="alert alert-info border-0 rounded-4 mt-4 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-info-circle fa-lg me-3 mt-1"></i>
                                    <div>
                                        <strong>Penting!</strong><br>
                                        <small>Nomor WhatsApp di bawah ini akan digunakan untuk mengirimkan informasi
                                            pendaftaran dan notifikasi penting lainnya.</small>
                                    </div>
                                </div>
                            </div>

                            <h5 class="text-success mt-3 mb-3 border-bottom pb-2">
                                <i class="fab fa-whatsapp me-2"></i>Kontak yang Bisa Dihubungi
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Nomor WhatsApp Aktif <span class="text-danger">*</span>
                                        <small class="text-muted d-block">Pastikan nomor ini aktif dan bisa menerima
                                            pesan</small>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-success text-white">
                                            <i class="fab fa-whatsapp"></i>
                                        </span>
                                        <input type="text" class="form-control" name="kontak_wa" id="kontak_wa_input"
                                            placeholder="Contoh: 081234567890" required maxlength="13"
                                            inputmode="numeric"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                        Format: 08xxxxxxxxxx (10-13 digit)
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Nama Pemilik Nomor <span class="text-danger">*</span>
                                        <small class="text-muted d-block">Contoh: Bapak Ahmad, Ibu Siti, dll</small>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" class="form-control" name="nama_kontak_wa"
                                            placeholder="Nama pemilik nomor WhatsApp" required>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Ayah/Ibu/Wali yang bisa dihubungi
                                    </small>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary px-4"
                                    onclick="prevStep(2)">Kembali</button>
                                <button type="button" class="btn btn-premium px-4" onclick="nextStep(4)">Lanjut <i
                                        class="fas fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Step 4: Rekap Nilai Murid -->
                        <div class="form-step d-none" id="step4">
                            <h4 class="mb-2"><i class="fas fa-list-ol me-2"></i>Rekap Nilai Rapor</h4>
                            <p class="text-muted small mb-4">Masukkan nilai rata-rata Rapor (Pengetahuan) sesuai dengan
                                semester yang diminta.</p>

                            <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle fa-2x me-3"></i>
                                    <div>
                                        <div class="fw-bold">PENTING:</div>
                                        Gunakan tanda titik (.) untuk angka desimal. Contoh: <strong>87.50</strong>
                                    </div>
                                </div>
                            </div>
                            <?php
                            // Check if minimal nilai validation is active
                            $status_validasi = get_setting('status_validasi_nilai', 'nonaktif');
                            $minimal_nilai = get_setting('minimal_nilai_rata', '0');

                            if ($status_validasi == 'aktif' && floatval($minimal_nilai) > 0):
                                ?>
                                <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-chart-line fa-2x me-3 text-primary"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-primary">SYARAT MINIMAL NILAI RATA-RATA</div>
                                            <div class="mt-1">
                                                Nilai minimal <strong>rata-rata (5 Semester)</strong>:
                                                <span
                                                    class="badge bg-primary fs-6 ms-1"><?= number_format(floatval($minimal_nilai), 2) ?></span>
                                            </div>
                                            <small class="text-muted">Nilai rata-rata dari semester 1 s/d 5 minimal harus
                                                mencapai syarat di atas agar dapat melanjutkan pendaftaran.</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="table-responsive mb-4">
                                <table class="table table-bordered align-middle text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kelas</th>
                                            <th>Semester</th>
                                            <th style="width: 200px;">Nilai Rapor (Pengetahuan)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td rowspan="2" class="fw-bold bg-light">IV (Empat)</td>
                                            <td>Ganjil (1)</td>
                                            <td><input type="number" step="0.01" name="nilai_k4_s1"
                                                    class="form-control text-center nilai-input" required
                                                    oninput="calculateNilai()"></td>
                                        </tr>
                                        <tr>
                                            <td>Genap (2)</td>
                                            <td><input type="number" step="0.01" name="nilai_k4_s2"
                                                    class="form-control text-center nilai-input" required
                                                    oninput="calculateNilai()"></td>
                                        </tr>
                                        <tr>
                                            <td rowspan="2" class="fw-bold bg-light">V (Lima)</td>
                                            <td>Ganjil (1)</td>
                                            <td><input type="number" step="0.01" name="nilai_k5_s1"
                                                    class="form-control text-center nilai-input" required
                                                    oninput="calculateNilai()"></td>
                                        </tr>
                                        <tr>
                                            <td>Genap (2)</td>
                                            <td><input type="number" step="0.01" name="nilai_k5_s2"
                                                    class="form-control text-center nilai-input" required
                                                    oninput="calculateNilai()"></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold bg-light">VI (Enam)</td>
                                            <td>Ganjil (1)</td>
                                            <td><input type="number" step="0.01" name="nilai_k6_s1"
                                                    class="form-control text-center nilai-input" required
                                                    oninput="calculateNilai()"></td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-primary fw-bold text-dark">
                                        <tr>
                                            <td colspan="2" class="text-end">Jumlah Nilai :</td>
                                            <td><input type="text" id="jumlah_nilai_display"
                                                    class="form-control text-center fw-bold border-0 bg-transparent"
                                                    readonly value="0"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-end">Rata-Rata Nilai :</td>
                                            <td><input type="text" id="rata_rata_display"
                                                    class="form-control text-center fw-bold border-0 bg-transparent"
                                                    readonly value="0"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                                <button type="button" class="btn btn-secondary px-4"
                                    onclick="prevStep(3)">Kembali</button>
                                <button type="button" class="btn btn-primary btn-lg px-5 shadow rounded-pill" onclick="handleSubmit()">
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Pendaftaran
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>



    <script>
        function calculateNilai() {
            const inputs = document.querySelectorAll('.nilai-input');
            let total = 0;
            let filledCount = 0;

            inputs.forEach(input => {
                const val = parseFloat(input.value);
                if (!isNaN(val)) {
                    total += val;
                    filledCount++;
                }
            });

            // Hitung rata-rata dari 5 semester
            const average = total / 5;

            const jumlahDisplay = document.getElementById('jumlah_nilai_display');
            const rataDisplay = document.getElementById('rata_rata_display');

            if (jumlahDisplay) jumlahDisplay.value = total.toFixed(2);
            if (rataDisplay) {
                rataDisplay.value = average.toFixed(2);

                const minimalNilai = parseFloat("<?= $minimal_nilai ?>");
                const statusValidasi = "<?= $status_validasi ?>";

                if (statusValidasi === 'aktif' && minimalNilai > 0 && filledCount === 5) {
                    if (average < minimalNilai) {
                        rataDisplay.style.background = '#ffebee';
                        rataDisplay.style.color = '#c62828';
                        // Pop-up tidak muncul di sini, hanya indikator visual
                    } else {
                        rataDisplay.style.background = '#e8f5e9';
                        rataDisplay.style.color = '#2e7d32';
                    }
                } else {
                    rataDisplay.style.background = 'transparent';
                    rataDisplay.style.color = 'inherit';
                }
            }
        }

        // Helper function to validate a specific step
        function validateStepFields(stepId) {
            const currentStep = document.getElementById(stepId);
            if (!currentStep) return true; // Just in case

            const requiredFields = currentStep.querySelectorAll('[required]');
            let emptyFields = [];

            requiredFields.forEach(field => {
                // Ensure field is visible and not disabled by some logic before enforcing required
                if (field.offsetParent !== null && !field.disabled) {
                    let isInvalid = false;
                    let errorMessage = "";

                    if (!field.value || field.value.trim() === '') {
                        isInvalid = true;
                    } else if (field.id === 'password_input' && field.value.length < 6) {
                        isInvalid = true;
                        errorMessage = "Password minimal 6 karakter";
                    }

                    if (isInvalid) {
                        field.classList.add('is-invalid');
                        let label = null;

                        if (stepId === 'step1') {
                            label = currentStep.querySelector(`label[for="${field.id}"]`) || field.closest('.col-md-6, .col-md-12, .col-12')?.querySelector('label');
                        } else if (stepId === 'step2') {
                            label = currentStep.querySelector(`label[for="${field.id}"]`) || field.closest('.col-md-6, .col-md-8, .col-md-12')?.querySelector('label');
                        } else if (stepId === 'step3') {
                            label = currentStep.querySelector(`label[for="${field.id}"]`) || field.closest('.col-md-6, .col-md-12, .col-12, .mb-4')?.querySelector('label');
                        } else {
                            label = currentStep.querySelector(`label[for="${field.id}"]`) || field.parentElement.querySelector('label');
                        }

                        let labelText = label ? label.textContent.replace('*', '').trim() : (field.name || 'Bidang Wajib');
                        if (errorMessage) {
                            emptyFields.push(`${labelText} (${errorMessage})`);
                        } else {
                            emptyFields.push(labelText);
                        }
                    } else {
                        field.classList.remove('is-invalid');
                    }
                }
            });

            if (emptyFields.length > 0) {
                // Remove duplicates in case of complex inputs
                emptyFields = [...new Set(emptyFields)];
                Swal.fire({
                    icon: 'warning',
                    title: 'Form Belum Lengkap!',
                    html: `<p>Harap lengkapi field berikut pada <b>` + document.querySelector(`#${stepId}-indicator`).getAttribute('title') + `</b>:</p><ul class="text-start">` +
                        emptyFields.map(f => `<li>${f}</li>`).join('') +
                        `</ul>`,
                    confirmButtonText: 'Oke, Saya Mengerti'
                });
                return false;
            }
            return true;
        }

        function validateNilaiStep() {
            const inputs = document.querySelectorAll('.nilai-input');
            let allFilled = true;
            inputs.forEach(input => {
                if (!input.value) allFilled = false;
            });

            if (!allFilled) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    text: 'Silakan isi semua nilai rapor semester 1 s/d 5.',
                    confirmButtonText: 'Oke'
                });
                return false;
            }

            const minimalNilai = parseFloat("<?= $minimal_nilai ?>");
            const statusValidasi = "<?= $status_validasi ?>";
            const rataDisplay = document.getElementById('rata_rata_display');
            const average = parseFloat(rataDisplay ? rataDisplay.value : 0);

            if (statusValidasi === 'aktif' && minimalNilai > 0 && average < minimalNilai) {
                Swal.fire({
                    icon: 'error',
                    title: 'Persyaratan Tidak Terpenuhi',
                    text: 'Maaf, nilai rata-rata Anda belum memenuhi syarat minimal rata-rata untuk melanjutkan pendaftaran.',
                    confirmButtonText: 'Periksa Kembali'
                });
                return false;
            }
            return true;
        }

        function nextStep(step) {
            // Validasi Step 1: Data Murid
            if (step === 2 && !validateStepFields('step1')) return;
            // Validasi Step 2: Asal Sekolah
            if (step === 3 && !validateStepFields('step2')) return;
            // Validasi Step 3: Data Orang Tua
            if (step === 4 && !validateStepFields('step3')) return;
            // Validasi Step 4 ke 5 (Nilai Rata-rata)
            if (step === 5) {
                if (!validateNilaiStep()) return;
                // Load documents if going to step 5
                loadDocumentUploadFields();
            }

            document.querySelectorAll('.form-step').forEach(el => el.classList.add('d-none'));
            document.getElementById('step' + step).classList.remove('d-none');

            // Update indicators
            document.querySelectorAll('.step').forEach((el, index) => {
                if (index + 1 <= step) el.classList.add('active');
                else el.classList.remove('active');
            });
            window.scrollTo(0, 0);
        }

        function prevStep(step) {
            document.querySelectorAll('.form-step').forEach(el => el.classList.add('d-none'));
            document.getElementById('step' + step).classList.remove('d-none');

            document.querySelectorAll('.step').forEach((el, index) => {
                if (index + 1 <= step) el.classList.add('active');
                else el.classList.remove('active');
            });
            window.scrollTo(0, 0);
        }

        // Function to navigate to specific step when clicking on step indicator
        function goToStep(targetStepNumber) {
            // Find which step is currently active
            let currentStepNumber = 1;
            document.querySelectorAll('.form-step').forEach((el, index) => {
                if (!el.classList.contains('d-none')) {
                    currentStepNumber = index + 1;
                }
            });

            // Allow moving backwards freely
            if (targetStepNumber < currentStepNumber) {
                prevStep(targetStepNumber);
                return;
            }

            // If moving forwards, validate every step in between sequentially
            for (let s = currentStepNumber; s < targetStepNumber; s++) {
                if (s === 1 && !validateStepFields('step1')) return;
                if (s === 2 && !validateStepFields('step2')) return;
                if (s === 3 && !validateStepFields('step3')) return;
            }

            // All validations passed, navigate to the target step
            // Hide all steps
            document.querySelectorAll('.form-step').forEach(el => el.classList.add('d-none'));

            // Show target step
            const targetStep = document.getElementById('step' + targetStepNumber);
            if (targetStep) {
                targetStep.classList.remove('d-none');

                // Update indicators - only activate up to target step
                document.querySelectorAll('.step').forEach((el, index) => {
                    if (index + 1 <= targetStepNumber) {
                        el.classList.add('active');
                    } else {
                        el.classList.remove('active');
                    }
                });

                // Scroll to top
                window.scrollTo(0, 0);
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr("#tgl_lahir_input", {
                locale: "id",
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d",
                onChange: function (selectedDates, dateStr, instance) {
                    // Trigger the age validation logic manually if needed
                    validateAge(dateStr);
                }
            });

            // Handle NISN & NIK Duplicate Check
            const checkDuplicate = (type, value, inputEl) => {
                if (value.length < (type === 'nisn' ? 10 : 16)) return;

                fetch(`api/check_${type}.php?${type}=${value}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.exists) {
                            inputEl.classList.add('is-invalid');
                            Swal.fire({
                                icon: 'warning',
                                title: 'Sudah Terdaftar!',
                                text: `${type.toUpperCase()} ${value} sudah terdaftar dalam sistem. Jika ini adalah data Anda, silakan hubungi panitia atau gunakan fitur Login Siswa.`,
                                confirmButtonText: 'Tutup',
                                confirmButtonColor: '#198754'
                            });
                        } else {
                            inputEl.classList.remove('is-invalid');
                        }
                    })
                    .catch(err => console.error(err));
            };

            const nisnInput = document.getElementById('nisn_input');
            const nikInput = document.getElementById('nik_input');

            if (nisnInput) {
                nisnInput.addEventListener('blur', function () {
                    checkDuplicate('nisn', this.value, this);
                });
            }

            if (nikInput) {
                nikInput.addEventListener('blur', function () {
                    checkDuplicate('nik', this.value, this);
                });
            }

            // Logic for Wali Validation (Requirement: if Yatim Piatu, Wali is mandatory)
            const statusOrtuSelect = document.getElementById('status_orang_tua');
            const waliFields = ['nama_wali', 'nik_wali', 'pekerjaan_wali', 'no_hp_wali', 'alamat_wali'];

            function updateWaliRequirement() {
                const status = statusOrtuSelect.value;
                const isYatimPiatu = (status === 'Yatim Piatu');

                waliFields.forEach(fieldId => {
                    const el = document.getElementById(fieldId);
                    const label = document.getElementById('label_' + fieldId);

                    if (el) {
                        if (isYatimPiatu) {
                            el.setAttribute('required', 'required');
                        } else {
                            el.removeAttribute('required');
                        }
                    }

                    if (label) {
                        const existingAsterisk = label.querySelector('.text-danger');
                        if (isYatimPiatu && !existingAsterisk) {
                            const asterisk = document.createElement('span');
                            asterisk.className = 'text-danger';
                            asterisk.textContent = ' *';
                            label.appendChild(asterisk);
                        } else if (!isYatimPiatu && existingAsterisk) {
                            existingAsterisk.remove();
                        }
                    }
                });
            }

            if (statusOrtuSelect) {
                statusOrtuSelect.addEventListener('change', updateWaliRequirement);
                // Run once on load to handle pre-filled data or status change
                updateWaliRequirement();
            }
        });

        function validateAge(dateValue) {
            const birthDate = new Date(dateValue);
            const cutoffDate = new Date("<?= get_setting('age_cutoff_date', '2026-07-01') ?>");
            const maxAge = parseInt("<?= get_setting('max_age_limit', '15') ?>");
            const ageLimitStatus = "<?= get_setting('age_limit_status', 'aktif') ?>";

            if (ageLimitStatus !== 'aktif') return;
            if (!dateValue) return;

            let age = cutoffDate.getFullYear() - birthDate.getFullYear();
            const monthDiff = cutoffDate.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && cutoffDate.getDate() < birthDate.getDate())) {
                age--;
            }

            if (age >= maxAge) {
                if (age > maxAge || (age === maxAge && (monthDiff > 0 || (monthDiff === 0 && cutoffDate.getDate() > birthDate.getDate())))) {
                    alert('⚠️ PERINGATAN BATAS UMUR!\nMaaf, umur Anda (' + age + ' tahun) telah melewati batasan maksimal ' + maxAge + ' tahun per tanggal ' + cutoffDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) + '. Anda tidak dapat melanjutkan pendaftaran.');
                    const instance = document.getElementById('tgl_lahir_input')._flatpickr;
                    if (instance) instance.clear();
                    document.getElementById('tgl_lahir_input').classList.add('is-invalid');
                }
            } else {
                document.getElementById('tgl_lahir_input').classList.remove('is-invalid');
            }
        }




        // Handle submit button - Submit directly with confirmation
        function handleSubmit() {
            // Validate Nilai Step (Step 4) first
            if (!validateNilaiStep()) return;
            
            // Password Match Check
            const pass = document.getElementById('password_input').value;
            const conf = document.getElementById('confirm_password_input').value;
            if (pass !== conf) {
                Swal.fire({ icon: 'error', title: 'Password Tidak Cocok', text: 'Konfirmasi password harus sama dengan password.' });
                return;
            }
            if (pass.length < 6) {
                Swal.fire({ icon: 'error', title: 'Password Terlalu Pendek', text: 'Password minimal 6 karakter.' });
                return;
            }

            Swal.fire({
                title: 'Kirim Pendaftaran?',
                text: "Pastikan seluruh data sudah benar sebelum mengirim.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Kirim Sekarang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitForm();
                }
            });
        }

        // Toggle Password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById('icon_' + inputId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        const passwordInput = document.getElementById('password_input');
        const confirmInput = document.getElementById('confirm_password_input');
        const feedback = document.getElementById('password_match_feedback');
        const lengthFeedback = document.getElementById('password_length_feedback');

        function checkPasswordLength() {
            const val = passwordInput.value;
            if (val === '') {
                lengthFeedback.innerHTML = '';
                passwordInput.classList.remove('is-invalid');
                return;
            }

            if (val.length < 6) {
                lengthFeedback.innerHTML = '<i class="fas fa-times-circle me-1"></i>Minimal 6 karakter';
                lengthFeedback.className = 'small mt-1 text-danger';
                // passwordInput.classList.add('is-invalid'); // Optional: show red border while typing
            } else {
                lengthFeedback.innerHTML = '<i class="fas fa-check-circle me-1"></i>Panjang password cukup';
                lengthFeedback.className = 'small mt-1 text-success';
                passwordInput.classList.remove('is-invalid');
            }
            checkPasswordMatch();
        }

        function checkPasswordMatch() {
            if (confirmInput.value === '') {
                feedback.innerHTML = '';
                return;
            }
            if (passwordInput.value === confirmInput.value) {
                feedback.innerHTML = '<i class="fas fa-check-circle me-1"></i>Password cocok';
                feedback.className = 'small mt-1 text-success';
                confirmInput.classList.remove('is-invalid');
            } else {
                feedback.innerHTML = '<i class="fas fa-times-circle me-1"></i>Password tidak cocok';
                feedback.className = 'small mt-1 text-danger';
                // confirmInput.classList.add('is-invalid');
            }
        }

        passwordInput.addEventListener('input', checkPasswordLength);
        confirmInput.addEventListener('input', checkPasswordMatch);


        // Submit form
        function submitForm() {
            document.getElementById('pmbmForm').submit();
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        const proxy = 'includes/proxy_wilayah.php?path=';

        function initAddressDropdowns(prefix = '') {
            const provSelect = document.getElementById('provinsi' + prefix);
            const kabSelect = document.getElementById('kabupaten_kota' + prefix);
            const kecSelect = document.getElementById('kecamatan' + prefix);
            const desaSelect = document.getElementById('desa_kelurahan' + prefix);

            if (!provSelect) return;

            // Load Provinces
            fetch(proxy + 'provinces')
                .then(res => res.json())
                .then(provinces => {
                    provinces.forEach(prov => {
                        let option = document.createElement('option');
                        option.value = prov.name;
                        option.dataset.id = prov.id;
                        option.textContent = prov.name;
                        provSelect.appendChild(option);
                    });
                });

            // Prov -> Kab
            provSelect.addEventListener('change', function() {
                kabSelect.innerHTML = '<option value="">Pilih Kab/Kota...</option>';
                kecSelect.innerHTML = '<option value="">Pilih Kecamatan...</option>';
                desaSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa...</option>';
                kabSelect.disabled = true; kecSelect.disabled = true; desaSelect.disabled = true;

                const provId = this.options[this.selectedIndex].dataset.id;
                if (provId) {
                    fetch(proxy + `regencies/${provId}`)
                        .then(res => res.json())
                        .then(data => {
                            data.forEach(item => {
                                let opt = document.createElement('option');
                                opt.value = item.name; opt.dataset.id = item.id; opt.textContent = item.name;
                                kabSelect.appendChild(opt);
                            });
                            kabSelect.disabled = false;
                        });
                }
            });

            // Kab -> Kec
            kabSelect.addEventListener('change', function() {
                kecSelect.innerHTML = '<option value="">Pilih Kecamatan...</option>';
                desaSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa...</option>';
                kecSelect.disabled = true; desaSelect.disabled = true;

                const kabId = this.options[this.selectedIndex].dataset.id;
                if (kabId) {
                    fetch(proxy + `districts/${kabId}`)
                        .then(res => res.json())
                        .then(data => {
                            data.forEach(item => {
                                let opt = document.createElement('option');
                                opt.value = item.name; opt.dataset.id = item.id; opt.textContent = item.name;
                                kecSelect.appendChild(opt);
                            });
                            kecSelect.disabled = false;
                        });
                }
            });

            // Kec -> Desa
            kecSelect.addEventListener('change', function() {
                desaSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa...</option>';
                desaSelect.disabled = true;

                const kecId = this.options[this.selectedIndex].dataset.id;
                if (kecId) {
                    fetch(proxy + `villages/${kecId}`)
                        .then(res => res.json())
                        .then(data => {
                            data.forEach(item => {
                                let opt = document.createElement('option');
                                opt.value = item.name; opt.textContent = item.name;
                                desaSelect.appendChild(opt);
                            });
                            desaSelect.disabled = false;
                        });
                }
            });
        }

        // Initialize all address dropdowns
        initAddressDropdowns('');
        initAddressDropdowns('_ayah');
        initAddressDropdowns('_ibu');
        initAddressDropdowns('_wali');

        // --- SAMA DENGAN ALAMAT MURID LOGIC ---
        function syncAddress(parent) {
            const checkbox = document.getElementById('sama_alamat_' + parent);
            const section = document.getElementById('section_alamat_' + parent);
            const inputs = section.querySelectorAll('input, select, textarea');
            
            if (checkbox.checked) {
                section.classList.add('bg-light', 'p-3', 'rounded-3');
                section.style.opacity = '0.7';
                
                // Disable all inputs in this section to skip validation
                inputs.forEach(input => {
                    input.disabled = true;
                    input.classList.remove('is-invalid');
                });
                
                // Copy values from student (optional, but good for UI)
                const prov = document.getElementById('provinsi');
                if (prov) document.getElementById('provinsi_' + parent).value = prov.value;
            } else {
                section.classList.remove('bg-light', 'p-3', 'rounded-3');
                section.style.opacity = '1';
                
                // Enable inputs
                inputs.forEach(input => {
                    // Re-enable primary fields, nested ones will be handled by the chained logic
                    if (input.id === 'provinsi_' + parent || input.id === 'alamat_' + parent) {
                        input.disabled = false;
                    } else {
                        // For nested selects (kab, kec, desa), only enable if parent has value
                        const provVal = document.getElementById('provinsi_' + parent).value;
                        if (input.id.includes('kabupaten') && provVal) input.disabled = false;
                        
                        const kabVal = document.getElementById('kabupaten_kota_' + parent).value;
                        if (input.id.includes('kecamatan') && kabVal) input.disabled = false;
                        
                        const kecVal = document.getElementById('kecamatan_' + parent).value;
                        if (input.id.includes('desa') && kecVal) input.disabled = false;
                    }
                });
            }
        }

        document.getElementById('sama_alamat_ayah').addEventListener('change', () => syncAddress('ayah'));
        document.getElementById('sama_alamat_ibu').addEventListener('change', () => syncAddress('ibu'));
        
        // Initial sync on load
        syncAddress('ayah');
        syncAddress('ibu');
        // --- END WILAYAH API ---

        // Apply dummy register settings globally
            <?php if ($dummy_register == '1'): ?>
                    console.log('Dummy Register mode active. Removing required attributes (Except Files).');
                    var inputs = document.querySelectorAll('form#pmbmForm input:not([type="file"]), form#pmbmForm select, form#pmbmForm textarea');
                    inputs.forEach(function (input) {
                        input.removeAttribute('required');
                    });
            <?php endif; ?>
        });
    </script>
</body>

</html>
```