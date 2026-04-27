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

// Generate Password for Exam
$exam_username = $student['no_pendaftaran'];
$exam_password = "26" . $student['nisn'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kartu Peserta Ujian -
        <?= htmlspecialchars($student['nama_lengkap']) ?>
    </title>
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .page-container {
            max-width: 210mm;
            margin: 0 auto;
        }

        .exam-card {
            background: white;
            border: 2px solid #0b2c24;
            border-radius: 15px;
            padding: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        /* Decorative Background */
        .exam-card::before {
            content: 'CBT';
            position: absolute;
            top: -20px;
            right: -20px;
            font-size: 150px;
            font-weight: 900;
            color: #0b2c24;
            opacity: 0.03;
            transform: rotate(-15deg);
            z-index: 0;
        }

        .header {
            display: flex;
            align-items: center;
            border-bottom: 3px double #0b2c24;
            padding-bottom: 15px;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }

        .logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }

        .logo-left {
            margin-right: 15px;
        }

        .logo-right {
            margin-left: 15px;
        }

        .header-text {
            flex-grow: 1;
            text-align: center;
        }

        .header-text h1 {
            margin: 0;
            font-size: 22px;
            color: #0b2c24;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-text h2 {
            margin: 5px 0 0;
            font-size: 16px;
            font-weight: 600;
            color: #1a4d40;
        }

        .header-text p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #666;
        }

        .main-content {
            display: flex;
            gap: 30px;
            position: relative;
            z-index: 1;
        }

        .student-info {
            flex-grow: 1;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 10px 0;
            vertical-align: top;
            font-size: 14px;
        }

        .info-table .label {
            width: 160px;
            font-weight: 600;
            color: #555;
        }

        .info-table .colon {
            width: 20px;
            color: #555;
        }

        .info-table .value {
            font-weight: 700;
            color: #000;
        }

        .photo-area {
            width: 120px;
            height: 160px;
            border: 2px solid #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9f9f9;
            overflow: hidden;
            flex-shrink: 0;
        }

        .photo-area img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .account-box {
            background: #f0f7f4;
            border: 2px dashed #198754;
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            display: flex;
            justify-content: space-around;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .account-item p {
            margin: 0 0 5px;
            font-size: 11px;
            font-weight: 700;
            color: #198754;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .account-item h3 {
            margin: 0;
            font-size: 24px;
            color: #0b2c24;
            font-family: 'Courier New', Courier, monospace;
            letter-spacing: 2px;
        }

        .instructions {
            margin-top: 30px;
            padding: 15px;
            background: #fff8e1;
            border-left: 5px solid #ffc107;
            border-radius: 4px;
            font-size: 12px;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: flex-end;
            position: relative;
            z-index: 1;
        }

        .signature {
            text-align: center;
            width: 250px;
            font-size: 14px;
        }

        .qr-code {
            position: absolute;
            bottom: 30px;
            left: 30px;
            opacity: 0.1;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .exam-card {
                box-shadow: none;
                border: 2px solid #000;
                border-radius: 0;
                margin: 0;
                width: 100%;
            }

            .no-print {
                display: none;
            }

            .account-box {
                background: #f0f0f0;
                border: 2px dashed #000;
            }

            .instructions {
                background: #fff;
                border: 1px solid #ccc;
            }
        }

        .btn-print {
            display: inline-block;
            padding: 12px 30px;
            background: #0b2c24;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            margin: 20px 0;
            transition: all 0.3s;
        }

        .btn-print:hover {
            background: #1a4d40;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="page-container">
        <div class="no-print" style="text-align: center;">
            <a href="javascript:window.print()" class="btn-print">
                CETAK KARTU UJIAN
            </a>
            <p style="font-size: 13px; color: #666;">Silakan cetak kartu ini dan simpan untuk digunakan pada saat
                pelaksanaan Computer Based Test (CBT).</p>
        </div>

        <div class="exam-card">
            <div class="header">
                <img src="assets/img/kemenag.png" alt="Logo Kemenag" class="logo logo-left">
                <div class="header-text">
                    <h1>KARTU PESERTA UJIAN (CBT)</h1>
                    <h2>PENERIMAAN MURID BARU MADRASAH (PMBM)</h2>
                    <p>MTsN 1 KOTA PEKANBARU - TAHUN PELAJARAN 2026/2027</p>
                </div>
                <img src="assets/img/sekolah.png" alt="Logo Sekolah" class="logo logo-right">
            </div>

            <div class="main-content">
                <div class="student-info">
                    <table class="info-table">
                        <tr>
                            <td class="label">Nama Peserta</td>
                            <td class="colon">:</td>
                            <td class="value" style="text-transform: uppercase;">
                                <?= htmlspecialchars($student['nama_lengkap']) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">No. Pendaftaran</td>
                            <td class="colon">:</td>
                            <td class="value">
                                <?= htmlspecialchars($student['no_pendaftaran']) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">NISN</td>
                            <td class="colon">:</td>
                            <td class="value">
                                <?= htmlspecialchars($student['nisn']) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Jalur Pendaftaran</td>
                            <td class="colon">:</td>
                            <td class="value">
                                <?= htmlspecialchars($student['nama_jalur']) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Asal Sekolah</td>
                            <td class="colon">:</td>
                            <td class="value">
                                <?= htmlspecialchars($student['asal_sekolah']) ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="photo-area">
                    <?php if (!empty($student['foto_siswa'])): ?>
                        <img src="uploads/<?= $student['foto_siswa'] ?>" alt="Foto Murid">
                    <?php else: ?>
                        <div style="text-align: center; color: #999; font-size: 11px;">
                            Pas Foto<br>3 x 4
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($student['test_hari'])): ?>
                <div class="jadwal-box-wrapper" style="margin-top: 20px; background: #f0f7f4; border: 2px dashed #198754; border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1;">
                    <div>
                        <small style="color: #198754; font-weight: 700; text-transform: uppercase; font-size: 10px; display: block; margin-bottom: 5px;">Jadwal Pelaksanaan Ujian:</small>
                        <h4 style="margin: 0; color: #0b2c24; font-weight: 800;"><?= $student['test_hari'] ?> | <?= $student['test_sesi'] ?></h4>
                    </div>
                    <div style="text-align: right;">
                        <small style="color: #198754; font-weight: 700; text-transform: uppercase; font-size: 10px; display: block; margin-bottom: 5px;">Jam Ujian (WIB):</small>
                        <h4 style="margin: 0; color: #d32f2f; font-weight: 800;"><i class="far fa-clock me-1"></i> <?= $student['test_jam_mulai'] ?> - <?= $student['test_jam_selesai'] ?></h4>
                    </div>
                </div>
            <?php endif; ?>

            <div class="account-box">
                <div class="account-item">
                    <p>Username Ujian</p>
                    <h3>
                        <?= htmlspecialchars($exam_username) ?>
                    </h3>
                </div>
                <div style="width: 2px; background: #ddd; margin: 0 20px;"></div>
                <div class="account-item">
                    <p>Password / PIN</p>
                    <h3>
                        <?= htmlspecialchars($exam_password) ?>
                    </h3>
                </div>
            </div>

            <div class="instructions">
                <strong><i class="fas fa-info-circle"></i> PENTING:</strong>
                <ul style="margin: 5px 0 0; padding-left: 20px;">
                    <li>Kartu ini wajib dibawa/disiapkan pada saat pelaksanaan ujian online.</li>
                    <li>Siswa diharapkan login 15 menit sebelum waktu ujian dimulai.</li>
                    <li>Gunakan perangkat (HP/Laptop) dengan koneksi internet yang stabil.</li>
                    <li>Jika ada kendala login, segera lapor kepada pengawas/proktor ujian.</li>
                </ul>
            </div>

            <!--<div class="footer">-->
            <!--    <div class="signature">-->
            <!--        <?php-->
            <!--        $months = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];-->
            <!--        $date = date('d') . ' ' . $months[date('m')] . ' ' . date('Y');-->
            <!--        ?>-->
            <!--        Pekanbaru,-->
            <!--        <?= $date ?><br>-->
            <!--        <strong>Ketua Panitia PMBM</strong><br><br><br><br>-->
            <!--        <strong>( ........................................ )</strong>-->
            <!--    </div>-->
            <!--</div>-->

            <div class="no-print" style="margin-top: 30px; text-align: center;">
                <button onclick="window.close()"
                    style="padding: 10px 20px; border: 1px solid #ccc; background: #fff; border-radius: 5px; cursor: pointer;">Tutup
                    Halaman</button>
            </div>
        </div>
    </div>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script>
        // Auto print logic can be added here if desired
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>