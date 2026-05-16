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
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Daftar Pendaftar - PMBM</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11pt;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .no-print {
            margin-top: 20px;
            text-align: center;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="header">
        <h2>DAFTAR CALON PESERTA DIDIK BARU</h2>
        <h3>MTsN 1 KOTA PEKANBARU</h3>
        <p>Tahun Pelajaran 2026/2027</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Daftar</th>
                <th>Nama Lengkap</th>
                <th>Nomor WA Aktif</th>
                <th>NISN</th>
                <th>Password</th>
                <th>JK</th>
                <th>Asal Sekolah</th>
                <th>Jalur</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $index => $s): ?>
                <tr>
                    <td>
                        <?= $index + 1 ?>
                    </td>
                    <td>
                        <?= $s['no_pendaftaran'] ?>
                    </td>
                    <td style="text-transform: uppercase;">
                        <?= htmlspecialchars($s['nama_lengkap']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($s['kontak_wa'] ?: '-') ?>
                    </td>
                    <td>
                        <?= $s['nisn'] ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($s['password_plain'] ?? '********') ?>
                    </td>
                    <td>
                        <?= $s['jenis_kelamin'] ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($s['asal_sekolah']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($s['nama_jalur'] ?? 'N/A') ?>
                    </td>
                    <td>
                        <?= $s['status'] ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="no-print">
        <button onclick="window.print()">Cetak Halaman</button>
        <button onclick="window.close()">Tutup</button>
    </div>
</body>

</html>