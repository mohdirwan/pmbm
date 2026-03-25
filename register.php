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
                        <div class="step" id="step5-indicator" title="Upload Berkas" onclick="goToStep(5)"
                            style="cursor: pointer;"><i class="fas fa-file-upload"></i>
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
                                <div class="col-12">
                                    <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="alamat" rows="2" required></textarea>
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
                                <div class="col-md-6">
                                    <label class="form-label">Kecamatan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="kecamatan" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kab/Kota <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="kabupaten_kota" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Provinsi <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="provinsi" required>
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
                                <div class="col-12">
                                    <label class="form-label">Alamat Ayah <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="alamat_ayah" rows="2" required
                                        placeholder="Alamat lengkap ayah"></textarea>
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
                                <div class="col-12">
                                    <label class="form-label">Alamat Ibu <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="alamat_ibu" rows="2" required
                                        placeholder="Alamat lengkap ibu"></textarea>
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
                                <div class="col-12">
                                    <label class="form-label" id="label_alamat_wali">Alamat Wali</label>
                                    <textarea class="form-control" name="alamat_wali" id="alamat_wali"
                                        rows="2"></textarea>
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

                            <div class="mt-4 d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary px-4"
                                    onclick="prevStep(3)">Kembali</button>
                                <button type="button" class="btn btn-premium px-4" onclick="nextStep(5)">Lanjut <i
                                        class="fas fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Step 5: Upload Berkas Persyaratan -->
                        <div class="form-step d-none" id="step5">
                            <h4 class="mb-2"><i class="fas fa-file-upload me-2"></i>Upload Berkas Persyaratan</h4>
                            <p class="text-muted small mb-4">Lengkapi dokumen yang diperlukan sesuai dengan jalur
                                pendaftaran Anda. Pastikan file dalam format JPG, PNG, atau PDF (Max 2MB per file).</p>

                            <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle fa-2x me-3"></i>
                                    <div>
                                        <div class="fw-bold">PENTING:</div>
                                        Pastikan semua dokumen sudah terisi dengan benar. Dokumen yang diperlukan akan
                                        menyesuaikan dengan <strong>jalur pendaftaran</strong> yang Anda pilih di Step
                                        1.
                                    </div>
                                </div>
                            </div>

                            <div id="document-list">
                                <!-- Document upload fields will be dynamically loaded here based on jalur_id -->
                            </div>

                            <div class="form-check mt-4 mb-4">
                                <input class="form-check-input" type="checkbox" required id="checkDocuments" disabled>
                                <label class="form-check-label fw-bold text-primary" for="checkDocuments">
                                    Saya menyatakan bahwa semua dokumen yang saya upload adalah asli dan benar. Apabila
                                    ditemukan pemalsuan dokumen di kemudian hari, saya bersedia dibatalkan kelulusannya.
                                </label>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary px-4"
                                    onclick="prevStep(4)">Kembali</button>
                                <button type="button" id="btnPreviewPendaftaran"
                                    class="btn btn-primary btn-lg px-5 shadow rounded-pill" onclick="handleSubmit()">
                                    <i class="fas fa-eye me-2"></i>Preview Pendaftaran
                                </button>
                            </div>

                            <!-- Hidden field to track pakta status -->
                            <input type="hidden" id="paktaAccepted" value="0">
                        </div>

                    </form>
                </div>
            </div>
        </div>
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
                            <h5 class="modal-title fw-bold mb-1" id="previewDataModalLabel">Preview Data Pendaftaran
                            </h5>
                            <small class="opacity-90">Periksa kembali data Anda sebelum mengirim</small>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-4" id="previewDataContent">
                    <!-- Content will be filled by JavaScript -->
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-lg btn-secondary rounded-pill px-4" onclick="closePreview()">
                        <i class="fas fa-edit me-2"></i>Edit Data
                    </button>
                    <button type="button" class="btn btn-lg btn-success rounded-pill px-5" onclick="submitForm()">
                        <i class="fas fa-paper-plane me-2"></i>Kirim Pendaftaran
                    </button>
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
                if (field.offsetParent !== null && !field.disabled && (!field.value || field.value.trim() === '')) {
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

                    if (label) {
                        emptyFields.push(label.textContent.replace('*', '').trim());
                    } else {
                        emptyFields.push(field.name || 'Bidang Wajib');
                    }
                } else {
                    field.classList.remove('is-invalid');
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
                if (s === 4 && !validateNilaiStep()) return;
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

                // Load documents if going to step 5
                if (targetStepNumber === 5) {
                    loadDocumentUploadFields();
                }

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

        // Load document upload fields dynamically based on selected jalur
        function loadDocumentUploadFields() {
            const jalurSelect = document.querySelector('select[name="jalur_id"]');
            const jalurId = jalurSelect ? jalurSelect.value : '';
            const documentList = document.getElementById('document-list');

            if (!jalurId) {
                documentList.innerHTML = `
                    <div class="alert alert-warning border-0 rounded-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Silakan pilih jalur pendaftaran di Step 1 terlebih dahulu.
                    </div>
                `;
                return;
            }

            // Show loading
            documentList.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat daftar dokumen...</p>
                </div>
            `;

            // Fetch documents from API
            fetch('get_jalur_syarat.php?jalur_id=' + jalurId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        generateDocumentFields(data.documents, data.jalur.nama_jalur);
                    } else {
                        documentList.innerHTML = `
                            <div class="alert alert-danger border-0 rounded-4">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                Error: ${data.error}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    documentList.innerHTML = `
                        <div class="alert alert-danger border-0 rounded-4">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Gagal memuat daftar dokumen. Silakan coba lagi.
                        </div>
                    `;
                    console.error('Error:', error);
                });
        }

        // Generate document upload fields HTML
        function generateDocumentFields(documents, jalurName) {
            const documentList = document.getElementById('document-list');

            if (!documents || documents.length === 0) {
                documentList.innerHTML = `
                    <div class="alert alert-info border-0 rounded-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Tidak ada dokumen yang perlu diupload untuk jalur ini.
                    </div>
                `;
                return;
            }

            const mandatory = documents.filter(d => d.status === 'wajib');
            const additional = documents.filter(d => d.status === 'tambahan');
            const optional = documents.filter(d => d.status === 'pilihan');

            let html = `
                <div class="alert alert-primary border-0 rounded-4 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-route fa-2x me-3"></i>
                        <div>
                            <div class="fw-bold mb-1">Jalur Pendaftaran Anda:</div>
                            <div class="fs-5 fw-semibold text-dark">${jalurName}</div>
                        </div>
                    </div>
                </div>
            `;

            // 1. Berkas Wajib
            if (mandatory.length > 0) {
                html += `
                    <h6 class="fw-bold mb-3 text-danger"><i class="fas fa-file-signature me-2"></i>Berkas Wajib</h6>
                    <div class="row g-3 mb-4">
                `;
                mandatory.forEach((doc) => {
                    const isPhoto = doc.field === 'foto_siswa';
                    html += `
                        <div class="col-md-6">
                            <div class="card border shadow-sm rounded-4 h-100">
                                <div class="card-body">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-file-alt me-1 text-danger"></i>
                                        ${doc.label} <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" 
                                           name="document_${doc.field}" 
                                           id="document_${doc.field}"
                                           class="form-control mandatory-file-input" 
                                           accept="${isPhoto ? '.jpg,.jpeg,.png' : '.pdf'}"
                                           required
                                           onchange="validateFileSize(this, checkMandatoryFilled)">
                                    ${doc.field === 'foto_siswa' ? `
                                    <div class="mt-2 p-2 bg-light rounded-3 border d-flex align-items-center">
                                        <img src="assets/img/contoh_siswa_merah.png" class="rounded-2 shadow-sm me-3" style="width: 60px; height: 80px; object-fit: cover;">
                                        <div>
                                            <div class="fw-bold text-success small"><i class="fas fa-check-circle me-1"></i>Contoh Foto Benar</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">Latar merah, seragam sekolah, rapi, dan menghadap depan.</div>
                                        </div>
                                    </div>
                                    ` : ''}
                                    ${doc.field === 'file_nisn' ? `
                                    <div class="mt-2 p-2 bg-light rounded-3 border">
                                        <div class="d-flex flex-column">
                                            <div class="fw-bold text-primary small mb-2"><i class="fas fa-info-circle me-1"></i>Contoh Printout NISN</div>
                                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContohNISN_reg" aria-expanded="false" aria-controls="collapseContohNISN_reg">
                                                <i class="fas fa-image me-1"></i> Lihat Contoh Gambar
                                            </button>
                                            <div class="collapse mt-2" id="collapseContohNISN_reg">
                                                <img src="assets/img/contoh_nisn.png" class="img-fluid rounded shadow-sm border" alt="Contoh Printout NISN">
                                            </div>
                                        </div>
                                    </div>
                                    ` : ''}
                                    <div class="form-text small">
                                        Format: ${isPhoto ? 'JPG, PNG' : 'PDF'} (Max 2MB)
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += `</div>`;
            }

            // 2. Berkas Pendukung (Bukan wajib, bukan pilihan dropdown)
            if (additional.length > 0) {
                html += `
                    <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-file-medical me-2"></i>Berkas Pendukung</h6>
                    <div class="row g-3 mb-4">
                `;
                additional.forEach((doc) => {
                    const isPhoto = doc.field === 'foto_siswa';
                    html += `
                        <div class="col-md-6">
                            <div class="card border shadow-sm rounded-4 h-100">
                                <div class="card-body">
                                    <label class="form-label fw-bold" style="color: #2c3e50;">
                                        <i class="fas fa-file-alt me-1 text-primary"></i>
                                        ${doc.label}
                                    </label>
                                    <input type="file" 
                                           name="document_${doc.field}" 
                                           id="document_${doc.field}"
                                           class="form-control" 
                                           accept="${isPhoto ? '.jpg,.jpeg,.png' : '.pdf'}">
                                    <div class="form-text small">
                                        Format: ${isPhoto ? 'JPG, PNG' : 'PDF'} (Max 2MB)
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += `</div>`;
            }

            // 3. Berkas Pilihan (Dropdown)
            if (optional.length > 0) {
                html += `
                    <div class="card border-primary border-2 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-primary text-white py-3">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-star me-2"></i>Berkas Pilihan (Minimal Unggah 1)</h6>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted small mb-4">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Petunjuk Upload Berkas Pilihan:</strong>
                            </p>
                            <ul class="text-muted small mb-4">
                                <?php foreach ($petunjuk_berkas_pilihan as $baris): ?>
                                <li><?= $baris ?></li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <div id="optional-docs-container">
                                <div class="row g-3 optional-upload-row mb-3">
                                    <div class="col-md-5">
                                        <label class="form-label small fw-bold">Pilih Jenis Dokumen</label>
                                        <select class="form-select doc-type-selector" onchange="updateOptionalInputName(this)">
                                            <option value="">-- Pilih Berkas --</option>
                                            ${optional.map(d => `<option value="${d.field}">${d.label}</option>`).join('')}
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Upload File</label>
                                        <input type="file" class="form-control optional-file-input" accept=".jpg,.jpeg,.png,.pdf" disabled>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger w-100" onclick="removeOptionalRow(this)" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill mt-2" onclick="addOptionalRow()">
                                <i class="fas fa-plus me-1"></i> Tambah Berkas Pilihan Lain
                            </button>
                        </div>
                    </div>
                `;
            }

            html += `
                <div class="alert alert-info border-0 rounded-4 mt-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-lightbulb fa-2x me-3 text-warning"></i>
                        <div>
                            <div class="fw-bold mb-2">Tips Upload Dokumen:</div>
                            <ul class="mb-0 small">
                                <li>Pastikan file terlihat jelas dan tidak blur</li>
                                <li>Gunakan scan atau foto dengan pencahayaan yang baik</li>
                                <li>Ukuran file maksimal 2MB per dokumen</li>
                                <li>Dokumen bertanda <span class="text-danger">*</span> atau di kotak Wajib harus diisi</li>
                            </ul>
                        </div>
                    </div>
                </div>
            `;

            documentList.innerHTML = html;
        }

        function updateOptionalInputName(select) {
            const row = select.closest('.optional-upload-row');
            const input = row.querySelector('.optional-file-input');
            const helpText = row.querySelector('.form-text') || document.createElement('div');
            if (!row.querySelector('.form-text')) {
                helpText.className = 'form-text small mt-1';
                input.parentNode.appendChild(helpText);
            }

            if (select.value) {
                const isPhoto = select.value === 'foto_siswa';
                input.name = 'document_' + select.value;
                input.id = 'document_' + select.value;
                input.disabled = false;
                input.required = true;
                input.accept = isPhoto ? '.jpg,.jpeg,.png' : '.pdf';
                helpText.textContent = `Format: ${isPhoto ? 'JPG, PNG' : 'PDF'} (Max 2MB)`;
                input.onchange = function () { validateFileSize(this, checkMandatoryFilled); };
            } else {
                input.name = '';
                input.id = '';
                input.disabled = true;
                input.required = false;
                input.accept = '';
                helpText.textContent = '';
                input.onchange = null;
            }
            checkMandatoryFilled(); // Check status
        }

        function addOptionalRow() {
            const container = document.getElementById('optional-docs-container');
            const firstRow = container.querySelector('.optional-upload-row');
            const newRow = firstRow.cloneNode(true);

            // Reset values
            const select = newRow.querySelector('select');
            const input = newRow.querySelector('input');
            const deleteBtn = newRow.querySelector('button');

            select.value = '';
            input.value = '';
            input.name = '';
            input.disabled = true;
            input.required = false;
            deleteBtn.disabled = false;

            container.appendChild(newRow);
        }

        function removeOptionalRow(btn) {
            const row = btn.closest('.optional-upload-row');
            const container = document.getElementById('optional-docs-container');
            if (container.querySelectorAll('.optional-upload-row').length > 1) {
                row.remove();
            }
        }

        /**
         * Validates the selected file size (max 2MB).
         * If too large, shows a SweetAlert and resets the input.
         * @param {HTMLInputElement} input - The file input element.
         * @param {Function} callback - Optional callback to run after validation passes.
         */
        function validateFileSize(input, callback) {
            const maxSize = 2 * 1024 * 1024; // 2MB in bytes
            if (input.files && input.files[0] && input.files[0].size > maxSize) {
                const fileSizeMB = (input.files[0].size / 1024 / 1024).toFixed(2);
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar!',
                    html: `Ukuran file <strong>${input.files[0].name}</strong> adalah <strong>${fileSizeMB} MB</strong>.<br><br>Ukuran maksimal yang diperbolehkan adalah <strong>2 MB</strong>. Silakan kompres atau gunakan file yang lebih kecil.`,
                    confirmButtonText: 'Oke, Ganti File',
                    confirmButtonColor: '#dc3545'
                });
                input.value = ''; // Reset the input
                if (typeof callback === 'function') callback();
                return false;
            }
            if (typeof callback === 'function') callback();
            return true;
        }

        function toggleDeclarationCheckbox() {
            const checkbox = document.getElementById('checkDocuments');
            const isFilled = checkMandatoryFilled();
            checkbox.disabled = !isFilled;
            if (!isFilled) checkbox.checked = false;
        }

        function checkMandatoryFilled() {
            const mandatoryInputs = document.querySelectorAll('.mandatory-file-input');
            const checkbox = document.getElementById('checkDocuments');

            let allFilled = true;

            // 1. Check Mandatory
            mandatoryInputs.forEach(input => {
                if (!input.files || input.files.length === 0) {
                    allFilled = false;
                }
            });

            // 2. Check Optional (Minimal 1)
            const optionalContainer = document.getElementById('optional-docs-container');
            if (allFilled && optionalContainer) {
                const filledOptional = Array.from(optionalContainer.querySelectorAll('.optional-file-input'))
                    .filter(input => input.files && input.files.length > 0);
                if (filledOptional.length === 0) {
                    allFilled = false;
                }
            }

            checkbox.disabled = !allFilled;
            if (!allFilled) {
                checkbox.checked = false;
            }

            return allFilled;
        }

        // Add validation for optional docs before going to next/preview
        function validateStep5() {
            const optionalContainer = document.getElementById('optional-docs-container');
            if (optionalContainer) {
                const filledInputs = Array.from(optionalContainer.querySelectorAll('.optional-file-input')).filter(input => input.files.length > 0);
                if (filledInputs.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Berkas Pilihan Belum Diisi',
                        text: 'Silakan pilih dan unggah minimal satu dokumen pilihan untuk jalur pendaftaran ini.',
                        confirmButtonText: 'Siap!'
                    });
                    return false;
                }
            }
            return true;
        }



        // Handle submit button - Show Preview directly
        function handleSubmit() {
            // 1. Check if declaration checkbox is checked
            const checkDocs = document.getElementById('checkDocuments');
            if (!checkDocs.checked) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pernyataan Keaslian',
                    text: 'Silakan centang pernyataan keaslian dokumen sebelum melanjutkan.',
                    confirmButtonText: 'Oke'
                });
                return;
            }

            // 2. Validate ALL mandatory docs (Aggressive lock)
            const mandatoryInputs = document.querySelectorAll('.mandatory-file-input');
            let missingLabels = [];
            
            mandatoryInputs.forEach(input => {
                if (!input.files || input.files.length === 0) {
                    // Get label from the closest card or previous element
                    const label = input.closest('.card')?.querySelector('label')?.innerText.replace('*', '').trim() || input.name;
                    missingLabels.push(label);
                }
            });

            if (missingLabels.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Dokumen Belum Lengkap',
                    html: `Dokumen berikut <b>wajib</b> diunggah:<br><br><ul class="text-start">${missingLabels.map(l => `<li>${l}</li>`).join('')}</ul>`,
                    confirmButtonText: 'Lengkapi Sekarang'
                });
                return;
            }

            // 3. Validate optional docs (minimal 1)
            if (!validateStep5()) {
                return;
            }

            // Show preview directly as pakta integrity is now part of uploaded documents
            showPreviewData();
        }



        // Show preview data
        function showPreviewData() {
            const form = document.getElementById('pmbmForm');
            const formData = new FormData(form);

            let html = '';

            // STEP 1: DATA MURID - LENGKAP
            html += `
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-primary text-white p-3">
                        <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Data Murid</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover mb-0">
                            <tbody>
                                <tr><th width="35%" class="ps-4">Jalur Pendaftaran</th><td>${document.querySelector('select[name="jalur_id"] option:checked')?.text || '-'}</td></tr>
                                <tr><th class="ps-4">NISN</th><td>${formData.get('nisn') || '-'}</td></tr>
                                <tr><th class="ps-4">NIK</th><td>${formData.get('nik') || '-'}</td></tr>
                                <tr><th class="ps-4">Nama Lengkap</th><td>${formData.get('nama_lengkap') || '-'}</td></tr>
                                <tr><th class="ps-4">Jenis Kelamin</th><td>${formData.get('jenis_kelamin') === 'L' ? 'Laki-laki' : 'Perempuan'}</td></tr>
                                <tr><th class="ps-4">Agama</th><td>${formData.get('agama') || '-'}</td></tr>
                                <tr><th class="ps-4">Tempat, Tanggal Lahir</th><td>${formData.get('tempat_lahir') || '-'}, ${formData.get('tanggal_lahir') || '-'}</td></tr>
                                <tr><th class="ps-4">Anak Ke</th><td>${formData.get('anak_ke') || '-'}</td></tr>
                                <tr><th class="ps-4">Status dlm Keluarga</th><td>${formData.get('status_keluarga') || '-'}</td></tr>
                                <tr><th class="ps-4">Hobi</th><td>${formData.get('hobi') || '-'}</td></tr>
                                <tr><th class="ps-4">No. HP Murid</th><td>${formData.get('no_hp') || '-'}</td></tr>
                                <tr><th class="ps-4">Alamat Lengkap</th><td>${formData.get('alamat') || '-'}</td></tr>
                                <tr><th class="ps-4">Status Tinggal</th><td>${formData.get('status_tinggal') || '-'}</td></tr>
                                <tr><th class="ps-4">Jarak ke Sekolah</th><td>${formData.get('jarak_sekolah') || '-'}</td></tr>
                                <tr><th class="ps-4">Transportasi</th><td>${formData.get('transportasi_rumah') || '-'}</td></tr>
                                <tr><th class="ps-4">Wilayah</th><td>Kec. ${formData.get('kecamatan') || '-'}, ${formData.get('kabupaten_kota') || '-'}, ${formData.get('provinsi') || '-'}</td></tr>
                                <tr><th class="ps-4">WhatsApp Notifikasi</th><td><span class="text-success fw-bold">${formData.get('kontak_wa') || '-'}</span> (a.n ${formData.get('nama_kontak_wa') || '-'})</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            `;

            // STEP 2: ASAL SEKOLAH
            html += `
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-info text-white p-3">
                        <h5 class="mb-0"><i class="fas fa-school me-2"></i>Asal Sekolah</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover mb-0">
                            <tbody>
                                <tr><th width="35%" class="ps-4">Nama Sekolah</th><td>${formData.get('asal_sekolah') || '-'}</td></tr>
                                <tr><th class="ps-4">NPSN Sekolah</th><td>${formData.get('npsn_sekolah') || '-'}</td></tr>
                                <tr><th class="ps-4">Alamat Sekolah</th><td>${formData.get('alamat_sekolah') || '-'}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            `;

            // STEP 3: DATA ORANG TUA
            html += `
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-success text-white p-3">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Data Orang Tua & Wali</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover mb-0">
                            <tbody>
                                <tr class="table-light"><th colspan="2" class="ps-4 text-primary">Informasi Umum</th></tr>
                                <tr><th width="35%" class="ps-4">Nomor KK</th><td>${formData.get('no_kk') || '-'}</td></tr>
                                <tr><th class="ps-4">Status Orang Tua</th><td>${formData.get('status_orang_tua') || '-'}</td></tr>
                                
                                <tr class="table-light"><th colspan="2" class="ps-4 text-primary">Data Ayah Kandung</th></tr>
                                <tr><th class="ps-4">Nama Ayah</th><td>${formData.get('nama_ayah') || '-'}</td></tr>
                                <tr><th class="ps-4">NIK Ayah</th><td>${formData.get('nik_ayah') || '-'}</td></tr>
                                <tr><th class="ps-4">TTL / HP Ayah</th><td>${formData.get('tempat_lahir_ayah') || '-'}, ${formData.get('tanggal_lahir_ayah') || '-'} / <span class="text-primary">${formData.get('no_hp_ayah') || '-'}</span></td></tr>
                                <tr><th class="ps-4">Pendidikan / Pekerjaan</th><td>${formData.get('pendidikan_ayah') || '-'} / ${formData.get('pekerjaan_ayah') || '-'}</td></tr>
                                <tr><th class="ps-4">Penghasilan Ayah</th><td>${formData.get('penghasilan_ayah') || '-'}</td></tr>
                                <tr><th class="ps-4">Alamat Ayah</th><td>${formData.get('alamat_ayah') || '(Sama dengan murid)'}</td></tr>

                                <tr class="table-light"><th colspan="2" class="ps-4 text-primary">Data Ibu Kandung</th></tr>
                                <tr><th class="ps-4">Nama Ibu</th><td>${formData.get('nama_ibu') || '-'}</td></tr>
                                <tr><th class="ps-4">NIK Ibu</th><td>${formData.get('nik_ibu') || '-'}</td></tr>
                                <tr><th class="ps-4">TTL / HP Ibu</th><td>${formData.get('tempat_lahir_ibu') || '-'}, ${formData.get('tanggal_lahir_ibu') || '-'} / <span class="text-primary">${formData.get('no_hp_ibu') || '-'}</span></td></tr>
                                <tr><th class="ps-4">Pendidikan / Pekerjaan</th><td>${formData.get('pendidikan_ibu') || '-'} / ${formData.get('pekerjaan_ibu') || '-'}</td></tr>
                                <tr><th class="ps-4">Penghasilan Ibu</th><td>${formData.get('penghasilan_ibu') || '-'}</td></tr>
                                <tr><th class="ps-4">Alamat Ibu</th><td>${formData.get('alamat_ibu') || '(Sama dengan murid)'}</td></tr>

                                <tr class="table-light"><th colspan="2" class="ps-4 text-primary">Data Wali (Opsional)</th></tr>
                                <tr><th class="ps-4">Nama Wali</th><td>${formData.get('nama_wali') || '-'}</td></tr>
                                <tr><th class="ps-4">NIK / HP Wali</th><td>${formData.get('nik_wali') || '-'} / <span class="text-primary">${formData.get('no_hp_wali') || '-'}</span></td></tr>
                                <tr><th class="ps-4">TTL Wali</th><td>${formData.get('tempat_lahir_wali') || '-'}, ${formData.get('tanggal_lahir_wali') || '-'}</td></tr>
                                <tr><th class="ps-4">Pendidikan / Pekerjaan</th><td>${formData.get('pendidikan_wali') || '-'} / ${formData.get('pekerjaan_wali') || '-'}</td></tr>
                                <tr><th class="ps-4">Alamat Wali</th><td>${formData.get('alamat_wali') || '-'}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            `;

            // STEP 4: REKAP NILAI
            html += `
                <div class="card border-0 shadow-sm rounded-4 mb-3" style="background: linear-gradient(135deg, #ffc107 0%, #ffeb3b 100%);">
                    <div class="card-header border-0 text-dark p-4">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-list-ol me-2"></i>Rekap Nilai Rapor</h5>
                        <small class="text-dark opacity-75">Nilai Pengetahuan Setiap Semester</small>
                    </div>
                    <div class="card-body p-4" style="background: white; border-radius: 0 0 1rem 1rem;">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                    <tr>
                                        <th class="ps-3" style="border-radius: 0.5rem 0 0 0.5rem;">Kelas</th>
                                        <th>Semester</th>
                                        <th class="text-end pe-3" style="border-radius: 0 0.5rem 0.5rem 0;">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-bottom">
                                        <td rowspan="2" class="fw-bold ps-3" style="background: #f8f9fa;">IV (Empat)</td>
                                        <td>Ganjil (1)</td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-primary fs-6">${formData.get('nilai_k4_s1') || '-'}</span>
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td>Genap (2)</td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-primary fs-6">${formData.get('nilai_k4_s2') || '-'}</span>
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td rowspan="2" class="fw-bold ps-3" style="background: #f8f9fa;">V (Lima)</td>
                                        <td>Ganjil (1)</td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-primary fs-6">${formData.get('nilai_k5_s1') || '-'}</span>
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td>Genap (2)</td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-primary fs-6">${formData.get('nilai_k5_s2') || '-'}</span>
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="fw-bold ps-3" style="background: #f8f9fa;">VI (Enam)</td>
                                        <td>Ganjil (1)</td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-primary fs-6">${formData.get('nilai_k6_s1') || '-'}</span>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
                                    <tr>
                                        <th colspan="2" class="ps-3 py-3">Jumlah Nilai:</th>
                                        <th class="text-end pe-3 py-3"><span class="fs-5">${document.getElementById('jumlah_nilai_display')?.value || '-'}</span></th>
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="ps-3 py-3" style="border-radius: 0 0 0 0.5rem;">Rata-Rata Nilai:</th>
                                        <th class="text-end pe-3 py-3" style="border-radius: 0 0 0.5rem 0;"><span class="fs-4 fw-bold">${document.getElementById('rata_rata_display')?.value || '-'}</span></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            // STEP 5: DOKUMEN UPLOAD
            html += `
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-header bg-secondary text-white p-3">
                        <h5 class="mb-0"><i class="fas fa-file-upload me-2"></i>Dokumen Upload</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3" id="filesPreviewContainer">
                            <div class="col-12"><p class="text-muted mb-0">Memuat daftar file...</p></div>
                        </div>
                    </div>
                </div>
            `;

            // INFO
            html += `
                <div class="alert alert-info border-0 rounded-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Catatan:</strong> Periksa kembali semua data Anda. Jika ada yang salah, klik "Edit Data" untuk kembali ke form. Jika sudah benar, klik "Kirim Pendaftaran" untuk menyelesaikan proses.
                </div>
            `;

            // Insert into modal
            document.getElementById('previewDataContent').innerHTML = html;

            // Generate file previews
            setTimeout(() => {
                const fileInputs = document.querySelectorAll('input[type="file"]');
                const container = document.getElementById('filesPreviewContainer');

                if (fileInputs.length === 0) {
                    container.innerHTML = '<div class="col-12"><p class="text-muted mb-0">Tidak ada dokumen yang diupload.</p></div>';
                    return;
                }

                container.innerHTML = ''; // Clear loading message

                fileInputs.forEach(input => {
                    const file = input.files[0];
                    if (file) {
                        const fileName = file.name;
                        const fileSize = (file.size / 1024).toFixed(2) + ' KB';
                        const fileExt = fileName.split('.').pop().toUpperCase();
                        const isImage = ['JPG', 'JPEG', 'PNG'].includes(fileExt);

                        // Get label
                        let label = '';
                        const row = input.closest('.optional-upload-row');
                        if (row) {
                            const select = row.querySelector('select');
                            label = select.options[select.selectedIndex].text;
                        } else {
                            label = input.closest('.card')?.querySelector('label')?.textContent.replace('*', '').trim() || input.name;
                        }

                        let icon = 'fa-file-alt';
                        let iconColor = 'text-primary';
                        let bgGradient = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';

                        if (fileExt === 'PDF') {
                            icon = 'fa-file-pdf';
                            iconColor = 'text-danger';
                            bgGradient = 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)';
                        } else if (isImage) {
                            icon = 'fa-file-image';
                            iconColor = 'text-success';
                            bgGradient = 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)';
                        }

                        // Create the card element
                        const col = document.createElement('div');
                        col.className = 'col-md-6 mb-3';
                        col.innerHTML = `
                            <div class="card border-0 shadow-sm h-100" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3 img-preview-container d-flex align-items-center justify-content-center rounded" style="width: 80px; height: 80px; background: ${bgGradient}; flex-shrink: 0; overflow: hidden;">
                                            <i class="fas ${icon} fa-3x text-white icon-placeholder"></i>
                                        </div>
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <div class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">${label}</div>
                                            <div class="text-muted small mb-2" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${fileName}</div>
                                            <div>
                                                <span class="badge" style="background: ${bgGradient};">${fileExt}</span>
                                                <span class="badge bg-secondary ms-1">${fileSize}</span>
                                            </div>
                                        </div>
                                        <div class="ms-2">
                                            <i class="fas fa-check-circle text-success fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        container.appendChild(col);

                        // If image, read it and replace placeholder
                        if (isImage) {
                            const reader = new FileReader();
                            reader.onload = function (e) {
                                const previewContainer = col.querySelector('.img-preview-container');
                                previewContainer.innerHTML = `<img src="${e.target.result}" class="rounded" style="width: 100%; height: 100%; object-fit: cover;">`;
                            };
                            reader.readAsDataURL(file);
                        }
                    }
                });
            }, 200);

            // Show modal
   const previewModal = new bootstrap.Modal(document.getElementById('previewDataModal'));
            previewModal.show();
        }

        // Close preview and go back to edit
        function closePreview() {
            const previewModal = bootstrap.Modal.getInstance(document.getElementById('previewDataModal'));
            previewModal.hide();
        }

        // Submit form
        function submitForm() {
            document.getElementById('pmbmForm').submit();
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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