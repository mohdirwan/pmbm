<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

// Filter data (sama dengan di index.php)
$search = $_GET['search'] ?? '';
$jalur = $_GET['jalur'] ?? '';
$status = $_GET['status'] ?? '';

$query = "SELECT p.*, j.nama_jalur 
          FROM pendaftar p 
          LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (p.nama_lengkap LIKE ? OR p.nisn LIKE ? OR p.no_pendaftaran LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($jalur) {
    $query .= " AND p.jalur_id = ?";
    $params[] = $jalur;
}

if ($status) {
    $query .= " AND p.status = ?";
    $params[] = $status;
}

$query .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Filename
$filename = "Data_Pendaftar_PMBM_" . date('Ymd_His') . ".xls";

// Headers for Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

?>
<table border="1">
    <thead>
        <tr>
            <th style="background-color: #0b2d24; color: white;">No</th>
            <th style="background-color: #0b2d24; color: white;">No Pendaftaran</th>
            <th style="background-color: #0b2d24; color: white;">NISN</th>
            <th style="background-color: #0b2d24; color: white;">Password</th>
            <th style="background-color: #0b2d24; color: white;">NIK</th>
            <th style="background-color: #0b2d24; color: white;">No KK</th>
            <th style="background-color: #0b2d24; color: white;">Nama Lengkap</th>
            <th style="background-color: #0b2d24; color: white;">Nomor WA Aktif</th>
            <th style="background-color: #0b2d24; color: white;">JK</th>
            <th style="background-color: #0b2d24; color: white;">Agama</th>
            <th style="background-color: #0b2d24; color: white;">Anak Ke</th>
            <th style="background-color: #0b2d24; color: white;">Status Keluarga</th>
            <th style="background-color: #0b2d24; color: white;">No HP Murid</th>
            <th style="background-color: #0b2d24; color: white;">Alamat</th>
            <th style="background-color: #0b2d24; color: white;">Status Orang Tua</th>
            <th style="background-color: #0b2d24; color: white;">Nama Ayah</th>
            <th style="background-color: #0b2d24; color: white;">NIK Ayah</th>
            <th style="background-color: #0b2d24; color: white;">No HP Ayah</th>
            <th style="background-color: #0b2d24; color: white;">Nama Ibu</th>
            <th style="background-color: #0b2d24; color: white;">NIK Ibu</th>
            <th style="background-color: #0b2d24; color: white;">No HP Ibu</th>
            <th style="background-color: #0b2d24; color: white;">Nama Wali</th>
            <th style="background-color: #0b2d24; color: white;">Asal Sekolah</th>
            <th style="background-color: #0b2d24; color: white;">Nilai S1</th>
            <th style="background-color: #0b2d24; color: white;">Nilai S2</th>
            <th style="background-color: #0b2d24; color: white;">Nilai S3</th>
            <th style="background-color: #0b2d24; color: white;">Nilai S4</th>
            <th style="background-color: #0b2d24; color: white;">Nilai S5/S6</th>
            <th style="background-color: #0b2d24; color: white;">Total Nilai</th>
            <th style="background-color: #0b2d24; color: white;">Rata-rata</th>
            <th style="background-color: #0b2d24; color: white;">Jalur</th>
            <th style="background-color: #0b2d24; color: white;">Status</th>
            <th style="background-color: #0b2d24; color: white;">Tanggal Daftar</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($students as $index => $s):
            $total_nilai = $s['nilai_k4_s1'] + $s['nilai_k4_s2'] + $s['nilai_k5_s1'] + $s['nilai_k5_s2'] + $s['nilai_k6_s1'];
            ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td>'<?= $s['no_pendaftaran'] ?></td>
                <td>'<?= $s['nisn'] ?></td>
                <td><?= htmlspecialchars($s['password_plain'] ?? '********') ?></td>
                <td>'<?= $s['nik'] ?></td>
                <td>'<?= $s['no_kk'] ?></td>
                <td><?= htmlspecialchars($s['nama_lengkap']) ?></td>
                <td>'<?= htmlspecialchars($s['kontak_wa'] ?: '-') ?></td>
                <td><?= $s['jenis_kelamin'] ?></td>
                <td><?= htmlspecialchars($s['agama']) ?></td>
                <td><?= htmlspecialchars($s['anak_ke']) ?></td>
                <td><?= htmlspecialchars($s['status_keluarga']) ?></td>
                <td>'<?= htmlspecialchars($s['no_hp']) ?></td>
                <td><?= htmlspecialchars($s['alamat']) ?></td>
                <td><?= htmlspecialchars($s['status_orang_tua']) ?></td>
                <td><?= htmlspecialchars($s['nama_ayah']) ?></td>
                <td>'<?= htmlspecialchars($s['nik_ayah']) ?></td>
                <td>'<?= htmlspecialchars($s['no_hp_ayah']) ?></td>
                <td><?= htmlspecialchars($s['nama_ibu']) ?></td>
                <td>'<?= htmlspecialchars($s['nik_ibu']) ?></td>
                <td>'<?= htmlspecialchars($s['no_hp_ibu']) ?></td>
                <td><?= htmlspecialchars($s['nama_wali'] ?: '-') ?></td>
                <td><?= htmlspecialchars($s['asal_sekolah']) ?></td>
                <td><?= number_format($s['nilai_k4_s1'], 2) ?></td>
                <td><?= number_format($s['nilai_k4_s2'], 2) ?></td>
                <td><?= number_format($s['nilai_k5_s1'], 2) ?></td>
                <td><?= number_format($s['nilai_k5_s2'], 2) ?></td>
                <td><?= number_format($s['nilai_k6_s1'], 2) ?></td>
                <td><?= number_format($total_nilai, 2) ?></td>
                <td><?= number_format($s['nilai_rapor_rata2'], 2) ?></td>
                <td><?= htmlspecialchars($s['nama_jalur'] ?? 'N/A') ?></td>
                <td><?= $s['status'] ?></td>
                <td><?= $s['tanggal_daftar'] ?></td>
            </tr>
        <?php endforeach; ?>

    </tbody>
</table>