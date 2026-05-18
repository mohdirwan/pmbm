<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Data_Test_Akademik_" . date('Ymd_His') . ".xls");

// Fetch data with Jalur information
$stmt = $pdo->query("SELECT p.*, j.nama_jalur 
                    FROM pendaftar p 
                    LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
                    WHERE p.status = 'Terverifikasi' 
                    ORDER BY p.test_hari ASC, p.test_sesi ASC");
$data = $stmt->fetchAll();

function format_indo_date($date) {
    if (!$date || $date == '-') return '-';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return $date;

    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $time = strtotime($date);
    $day_name = $days[date('w', $time)];
    $day = date('j', $time);
    $month_name = $months[(int)date('m', $time)];
    $year = date('Y', $time);
    
    return "$day_name, $day $month_name $year";
}
?>

<table border="1">
    <thead>
        <tr style="background-color: #0b2c24; color: white; font-weight: bold;">
            <th>No</th>
            <th>No Pendaftaran</th>
            <th>Nama Lengkap</th>
            <th>Jenis Kelamin</th>
            <th>NISN</th>
            <th>Hari Test</th>
            <th>Sesi Test</th>
            <th>Jam Mulai</th>
            <th>Jam Selesai</th>
            <th>Username</th>
            <th>Pass Sistem</th>
            <th>Pass CBT</th>
            <th>Jalur</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; foreach($data as $row): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $row['no_pendaftaran'] ?></td>
            <td><?= strtoupper($row['nama_lengkap']) ?></td>
            <td><?= $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
            <td>'<?= $row['nisn'] ?></td> <!-- Tanda petik agar tidak jadi scientific format di excel -->
            <td><?= format_indo_date($row['test_hari']) ?></td>
            <td><?= $row['test_sesi'] ?? '-' ?></td>
            <td><?= $row['test_jam_mulai'] ?? '-' ?></td>
            <td><?= $row['test_jam_selesai'] ?? '-' ?></td>
            <td><?= $row['no_pendaftaran'] ?></td>
            <td><?= $row['password_plain'] ?? '-' ?></td>
            <td><?= $row['password_cbt'] ?? '-' ?></td>
            <td><?= $row['nama_jalur'] ?? '-' ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
