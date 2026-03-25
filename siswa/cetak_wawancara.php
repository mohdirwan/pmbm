<?php
require_once '../includes/config.php';

if (!isset($_SESSION['siswa_id']) || $_SESSION['role'] !== 'siswa') {
    die("Akses ditolak.");
}

$stmt = $pdo->prepare("SELECT * FROM pendaftar WHERE id = ?");
$stmt->execute([$_SESSION['siswa_id']]);
$siswa = $stmt->fetch();

if (!$siswa)
    die("Data tidak ditemukan.");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Form Wawancara -
        <?= htmlspecialchars($siswa['no_pendaftaran']) ?>
    </title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            line-height: 1.5;
            padding: 40px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0;
            font-size: 14px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 30px;
        }

        .student-info {
            margin-bottom: 30px;
        }

        .student-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-info td {
            padding: 5px;
        }

        .label {
            width: 25%;
        }

        .score-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .score-table th,
        .score-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }

        .score-table th {
            background: #eee;
        }

        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        .sig-box {
            text-align: center;
            width: 45%;
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
        <h2>PANITIA PMBM MTSN 1 KOTA PEKANBARU</h2>
        <p>Tahun Pelajaran 2026/2027</p>
        <p>Jl. Arifin Ahmad No.1, Pekanbaru, Riau</p>
    </div>

    <div class="title">LEMBAR PENILAIAN WAWANCARA</div>

    <div class="student-info">
        <table>
            <tr>
                <td class="label">No. Pendaftaran</td>
                <td>: <strong>
                        <?= $siswa['no_pendaftaran'] ?>
                    </strong></td>
            </tr>
            <tr>
                <td class="label">Nama Lengkap</td>
                <td>:
                    <?= strtoupper($siswa['nama_lengkap']) ?>
                </td>
            </tr>
            <tr>
                <td class="label">Asal Sekolah</td>
                <td>:
                    <?= $siswa['asal_sekolah'] ?>
                </td>
            </tr>
        </table>
    </div>

    <p>Mohon penguji memberikan penilaian (skala 0-100) pada aspek berikut:</p>

    <table class="score-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="45%">Aspek Penilaian</th>
                <th width="20%">Skor</th>
                <th width="30%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td style="text-align: left;">Kemampuan Baca Tulis Al-Qur'an (BTQ)</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>2</td>
                <td style="text-align: left;">Hafalan Surah Pendek (Juz Amma)</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>3</td>
                <td style="text-align: left;">Praktik Ibadah Shalat & Wudhu</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>4</td>
                <td style="text-align: left;">Kepribadian & Motivasi Belajar</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>5</td>
                <td style="text-align: left;">Wawancara Orang Tua</td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="2">TOTAL SKOR RATA-RATA</td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <div class="sig-box">
            <br>Orang Tua/Wali,<br><br><br><br>
            ( ........................................ )
        </div>
        <div class="sig-box">
            Pekanbaru,
            <?= date('d F Y') ?><br>
            Penguji/Pewawancara,<br><br><br><br>
            ( ........................................ )
        </div>
    </div>

    <div class="no-print" style="position: fixed; bottom: 20px; right: 20px;">
        <button onclick="window.print()"
            style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Print
            Dokumen</button>
    </div>
</body>

</html>