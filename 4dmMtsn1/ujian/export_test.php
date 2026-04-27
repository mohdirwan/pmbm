<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Data_Test_Akademik_" . date('Ymd_His') . ".xls");

$stmt = $pdo->query("SELECT * FROM pendaftar WHERE status = 'Terverifikasi' ORDER BY test_hari ASC, test_sesi ASC");
$data = $stmt->fetchAll();
?>

<table border="1">
    <thead>
        <tr style="background-color: #0b2c24; color: white; font-weight: bold;">
            <th>No</th>
            <th>No Pendaftaran</th>
            <th>Nama Lengkap</th>
            <th>NISN</th>
            <th>Hari Test</th>
            <th>Sesi Test</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; foreach($data as $row): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $row['no_pendaftaran'] ?></td>
            <td><?= strtoupper($row['nama_lengkap']) ?></td>
            <td>'<?= $row['nisn'] ?></td> <!-- Tanda petik agar tidak jadi scientific format di excel -->
            <td><?= $row['test_hari'] ?? '-' ?></td>
            <td><?= $row['test_sesi'] ?? '-' ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
