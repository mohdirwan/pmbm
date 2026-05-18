<?php
require_once 'includes/config.php';

$no_pendaftaran = $_GET['reg'] ?? '';
$student = null;

if ($no_pendaftaran) {
    $sql = "SELECT p.*, j.nama_jalur 
            FROM pendaftar p 
            LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
            WHERE p.no_pendaftaran = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$no_pendaftaran]);
    $student = $stmt->fetch();
}

if (!$student) {
    die("Data tidak ditemukan.");
}

// Helper function for address
function format_address($p, $prefix = '') {
    if ($prefix && !empty($p[$prefix . 'provinsi'])) {
        return htmlspecialchars($p[$prefix . 'alamat']) . ", " . 
               htmlspecialchars($p[$prefix . 'desa_kelurahan']) . ", " . 
               htmlspecialchars($p[$prefix . 'kecamatan']) . ", " . 
               htmlspecialchars($p[$prefix . 'kabupaten_kota']) . ", " . 
               htmlspecialchars($p[$prefix . 'provinsi']);
    } else {
        return htmlspecialchars($p['alamat']) . ", " . 
               htmlspecialchars($p['desa_kelurahan']) . ", " . 
               htmlspecialchars($p['kecamatan']) . ", " . 
               htmlspecialchars($p['kabupaten_kota']) . ", " . 
               htmlspecialchars($p['provinsi']);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Pendaftaran - <?= htmlspecialchars($student['no_pendaftaran']) ?></title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10.5pt; line-height: 1.3; margin: 0; padding: 10mm; background: #fff; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 10px; position: relative; }
        .header img { position: absolute; left: 0; top: 0; width: 65px; height: 65px; }
        .header h1 { margin: 0; font-size: 14pt; text-transform: uppercase; }
        .header h2 { margin: 0; font-size: 12pt; text-transform: uppercase; }
        .header p { margin: 2px 0 0; font-size: 9pt; font-style: italic; }
        
        .title { text-align: center; margin-bottom: 10px; }
        .title h3 { margin: 0; text-decoration: underline; text-transform: uppercase; font-size: 11pt; }
        .title p { margin: 2px 0; font-weight: bold; font-size: 10pt; }

        .section-title { font-weight: bold; text-decoration: underline; margin-top: 8px; margin-bottom: 3px; background: #f4f4f4; padding: 2px 5px; display: block; font-size: 10pt; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        table td { padding: 1.5px 5px; vertical-align: top; }
        .label { width: 30%; }
        .colon { width: 2%; }
        .value { width: 68%; }
        
        .footer { margin-top: 15px; }
        .footer-table td { text-align: center; width: 33%; }
        .signature-space { height: 60px; }

        @media print {
            body { padding: 0; margin: 0; }
            .no-print { display: none; }
            @page { size: A4; margin: 10mm; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="background: #f8d7da; padding: 10px; text-align: center; margin-bottom: 20px; border-radius: 5px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Cetak Sekarang</button>
    </div>

    <div class="header">
        <?php $logo = get_setting('school_logo'); if($logo): ?>
            <img src="<?= BASE_URL . $logo ?>" alt="Logo">
        <?php endif; ?>
        <h2>KEMENTERIAN AGAMA REPUBLIK INDONESIA</h2>
        <h1>MTs NEGERI 1 KOTA PEKANBARU</h1>
        <p>Jl. Teratai No.105, Pulau Karomah, Kec. Sukajadi, Kota Pekanbaru, Riau 28127</p>
    </div>

    <div class="title">
        <h3>FORMULIR PENDAFTARAN MURID BARU</h3>
        <p>Tahun Pelajaran 2026/2027</p>
    </div>

    <div class="section-title">A. DATA PENDAFTAR</div>
    <div style="display: flex; justify-content: space-between;">
        <div style="flex: 1;">
            <table>
                <tr><td class="label">No. Pendaftaran</td><td class="colon">:</td><td class="value"><strong><?= htmlspecialchars($student['no_pendaftaran']) ?></strong></td></tr>
                <tr><td class="label">Jalur Pendaftaran</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['nama_jalur']) ?></td></tr>
                <tr><td class="label">Nama Lengkap</td><td class="colon">:</td><td class="value" style="text-transform: uppercase;"><?= htmlspecialchars($student['nama_lengkap']) ?></td></tr>
                <tr><td class="label">NISN</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['nisn']) ?></td></tr>
                <tr><td class="label">NIK</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['nik']) ?></td></tr>
                <tr><td class="label">Jenis Kelamin</td><td class="colon">:</td><td class="value"><?= $student['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td></tr>
                <tr><td class="label">Tempat, Tgl Lahir</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['tempat_lahir']) ?>, <?= date('d F Y', strtotime($student['tanggal_lahir'])) ?></td></tr>
                <tr><td class="label">Agama</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['agama']) ?></td></tr>
                <tr><td class="label">Anak Ke</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['anak_ke']) ?></td></tr>
                <tr><td class="label">Status dalam Keluarga</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['status_keluarga']) ?></td></tr>
                <tr><td class="label">No. HP / WA</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['no_hp']) ?></td></tr>
                <tr><td class="label">Alamat Lengkap</td><td class="colon">:</td><td class="value"><?= format_address($student) ?></td></tr>
            </table>
        </div>
        <div style="width: 120px; padding-left: 15px; padding-top: 10px;">
            <?php if (!empty($student['foto_siswa'])): ?>
                <img src="uploads/<?= htmlspecialchars($student['foto_siswa']) ?>" alt="Pas Foto" style="width: 3cm; height: 4cm; object-fit: cover; border: 1px solid #000; margin-left: auto; display: block;">
            <?php else: ?>
                <div style="width: 3cm; height: 4cm; border: 1px solid #000; text-align: center; line-height: 4cm; font-size: 10pt; color: #666; margin-left: auto;">3x4</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="section-title">B. DATA ORANG TUA / WALI</div>
    <table>
        <tr><td colspan="3" style="font-weight: bold; text-decoration: underline; padding-top: 10px;">1. Data Ayah Kandung</td></tr>
        <tr><td class="label">Nama Lengkap</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['nama_ayah']) ?></td></tr>
        <tr><td class="label">NIK Ayah</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['nik_ayah']) ?: '-' ?></td></tr>
        <tr><td class="label">Pendidikan Terakhir</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['pendidikan_ayah']) ?: '-' ?></td></tr>
        <tr><td class="label">Pekerjaan</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['pekerjaan_ayah']) ?: '-' ?></td></tr>
        <tr><td class="label">Penghasilan / Bulan</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['penghasilan_ayah']) ?: '-' ?></td></tr>
        <tr><td class="label">No. HP / WA Ayah</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['no_hp_ayah']) ?: '-' ?></td></tr>
        <tr><td class="label">Alamat Ayah</td><td class="colon">:</td><td class="value"><?= format_address($student, 'ayah_') ?></td></tr>

        <tr><td colspan="3" style="font-weight: bold; text-decoration: underline; padding-top: 10px;">2. Data Ibu Kandung</td></tr>
        <tr><td class="label">Nama Lengkap</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['nama_ibu']) ?></td></tr>
        <tr><td class="label">NIK Ibu</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['nik_ibu']) ?: '-' ?></td></tr>
        <tr><td class="label">Pendidikan Terakhir</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['pendidikan_ibu']) ?: '-' ?></td></tr>
        <tr><td class="label">Pekerjaan</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['pekerjaan_ibu']) ?: '-' ?></td></tr>
        <tr><td class="label">Penghasilan / Bulan</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['penghasilan_ibu']) ?: '-' ?></td></tr>
        <tr><td class="label">No. HP / WA Ibu</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['no_hp_ibu']) ?: '-' ?></td></tr>
        <tr><td class="label">Alamat Ibu</td><td class="colon">:</td><td class="value"><?= format_address($student, 'ibu_') ?></td></tr>

        <?php if(!empty($student['nama_wali'])): ?>
        <tr><td colspan="3" style="font-weight: bold; text-decoration: underline; padding-top: 10px;">3. Data Wali (Jika Ada)</td></tr>
        <tr><td class="label">Nama Lengkap</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['nama_wali']) ?></td></tr>
        <tr><td class="label">NIK Wali</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['nik_wali']) ?: '-' ?></td></tr>
        <tr><td class="label">Pekerjaan Wali</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['pekerjaan_wali']) ?: '-' ?></td></tr>
        <tr><td class="label">No. HP / WA Wali</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($student['no_hp_wali']) ?: '-' ?></td></tr>
        <tr><td class="label">Alamat Wali</td><td class="colon">:</td><td class="value"><?= format_address($student, 'wali_') ?></td></tr>
        <?php endif; ?>
    </table>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>
                    Mengetahui,<br>Orang Tua / Wali
                    <div class="signature-space"></div>
                    ( ........................................... )
                </td>
                <td></td>
                <td>
                    Pekanbaru, <?= date('d F Y') ?><br>Calon Murid
                    <div class="signature-space"></div>
                    <strong><?= htmlspecialchars($student['nama_lengkap']) ?></strong>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 20px; font-size: 9pt; color: #666; text-align: center; border-top: 1px dashed #ccc; padding-top: 10px;">
        Dicetak secara otomatis melalui Sistem PMBM Online MTsN 1 Kota Pekanbaru pada <?= date('d/m/Y H:i:s') ?>
    </div>
</body>
</html>
