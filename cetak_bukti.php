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
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kartu Pendaftaran -
        <?= htmlspecialchars($no_pendaftaran) ?>
    </title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        .card-container {
            width: 210mm;
            /* A4 width */
            margin: 0 auto;
            background: white;
            border: 1px solid #ccc;
            padding: 20px 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .top-label {
            font-size: 11px;
            margin-bottom: 2px;
            text-decoration: underline;
        }

        hr {
            border: 0;
            border-top: 1px solid #000;
            margin: 5px 0;
        }

        .header-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 10px 0;
            gap: 15px;
        }

        .logo-box {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .header-content {
            flex-grow: 1;
            text-align: center;
        }

        .header-content h1 {
            margin: 0;
            font-size: 18px;
        }

        .header-content h2 {
            margin: 2px 0;
            font-size: 14px;
            font-weight: normal;
        }

        .header-content h3 {
            margin: 2px 0;
            font-size: 16px;
            font-weight: bold;
        }

        .content-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 20px;
            gap: 20px;
        }

        .info-table {
            flex-grow: 1;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 0;
            vertical-align: top;
            font-size: 13px;
        }

        .label {
            width: 150px;
            font-weight: normal;
        }

        .colon {
            width: 15px;
        }

        .photo-box {
            width: 113px;
            height: 151px;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 11px;
            background-color: #fafafa;
            flex-shrink: 0;
            padding: 2px;
        }

        .login-info {
            border: 1px solid #000;
            padding: 12px;
            margin-top: 20px;
            width: 100%;
            font-size: 13px;
        }

        .login-info strong {
            display: block;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
            font-size: 13px;
        }

        .signature-box {
            text-align: center;
            width: 250px;
        }

        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                background: none;
                padding: 0;
            }

            .card-container {
                box-shadow: none;
                border: none;
                width: 100%;
                padding: 15mm;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="card-container">
        <div class="top-label">Untuk Kartu Pendaftaran</div>
        <hr>

        <div class="header-main">
            <div class="logo-box" style="border: none;">
                <?php
                $school_logo = get_setting('school_logo', '');
                if (!empty($school_logo)):
                    // Support both relative paths and full URLs
                    $logo_url = (strpos($school_logo, 'http') === 0) ? $school_logo : BASE_URL . $school_logo;
                    ?>
                    <img src="<?= $logo_url ?>" alt="Logo" style="width: 80px; height: 80px; object-fit: contain;">
                <?php else: ?>
                    <div
                        style="border: 1px solid #000; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                        Logo
                    </div>
                <?php endif; ?>
            </div>
            <div class="header-content">
                <h1 style="font-size: 18px; margin-bottom: 2px;">KARTU PENDAFTARAN</h1>
                <h2 style="font-size: 14px; margin-bottom: 2px;">PENERIMAAN MURID BARU MADRASAH (PMBM)</h2>
                <?php
                $nama_jalur = strtoupper($student['nama_jalur']);
                // Avoid "JALUR JALUR" duplication
                if (strpos($nama_jalur, 'JALUR') === 0) {
                    $jalur_text = $nama_jalur;
                } else {
                    $jalur_text = 'JALUR ' . $nama_jalur;
                }
                ?>

                <h3 style="font-size: 16px; margin-bottom: 2px;"><?= htmlspecialchars($jalur_text) ?></h3>

                <h1 style="font-size: 18px; margin-bottom: 2px;">MTsN 1 KOTA PEKANBARU</h1>

            </div>
        </div>

        <hr style="margin-top: 5px;">

        <div class="content-section">
            <table class="info-table">
                <tr>
                    <td class="label">No. Pendaftaran</td>
                    <td class="colon">:</td>
                    <td style="font-weight: bold;">
                        <?= htmlspecialchars($student['no_pendaftaran']) ?>
                    </td>
                </tr>
                <tr>
                    <td class="label">Nama Lengkap</td>
                    <td class="colon">:</td>
                    <td style="text-transform: uppercase;">
                        <?= htmlspecialchars($student['nama_lengkap']) ?>
                    </td>
                </tr>
                <tr>
                    <td class="label">NISN</td>
                    <td class="colon">:</td>
                    <td>
                        <?= htmlspecialchars($student['nisn']) ?>
                    </td>
                </tr>
                <tr>
                    <td class="label">Jalur Pendaftaran</td>
                    <td class="colon">:</td>
                    <td>
                        <?= htmlspecialchars($student['nama_jalur']) ?>
                    </td>
                </tr>
                <tr>
                    <td class="label">Tempat, Tanggal Lahir</td>
                    <td class="colon">:</td>
                    <td>
                        <?= htmlspecialchars($student['tempat_lahir']) ?>,
                        <?= date('d/m/Y', strtotime($student['tanggal_lahir'])) ?>
                    </td>
                </tr>
                <tr>
                    <td class="label">Asal Sekolah</td>
                    <td class="colon">:</td>
                    <td>
                        <?= htmlspecialchars($student['asal_sekolah']) ?>
                    </td>
                </tr>
                <tr>
                    <td class="label">Nama Orangtua/Wali</td>
                    <td class="colon">:</td>
                    <td>
                        <?= htmlspecialchars($student['nama_ayah']) ?> /
                        <?= htmlspecialchars($student['nama_ibu']) ?>
                    </td>
                </tr>
            </table>

        </div>

        <div class="login-info">
            <strong>INFORMASI LOGIN</strong>
            <div style="margin-top: 5px;">
                Username : <?= htmlspecialchars($student['nisn']) ?><br>
                Password : <?= !empty($student['password_plain']) ? htmlspecialchars($student['password_plain']) : '[Sesuai yang Anda buat]' ?><br>
                <div style="font-size: 11px; margin-top: 5px;">*Gunakan informasi ini untuk melengkapi berkas di
                    dashboard siswa.</div>
            </div>
        </div>

        <div class="footer">
            <div class="signature-box">
                <?php
                $months = [
                    '01' => 'Januari',
                    '02' => 'Februari',
                    '03' => 'Maret',
                    '04' => 'April',
                    '05' => 'Mei',
                    '06' => 'Juni',
                    '07' => 'Juli',
                    '08' => 'Agustus',
                    '09' => 'September',
                    '10' => 'Oktober',
                    '11' => 'November',
                    '12' => 'Desember'
                ];
                $date = date('d') . ' ' . $months[date('m')] . ' ' . date('Y');
                ?>
                Pekanbaru, <?= $date ?><br>
                Pendaftar,<br><br><br><br><br>
                ( ........................................ )
            </div>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 30px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Cetak Kartu</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">Tutup Halaman</button>
    </div>
</body>

</html>