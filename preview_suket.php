<?php
require_once 'includes/config.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    die("ID surat keterangan tidak valid");
}

$stmt = $pdo->prepare("SELECT * FROM surat_keterangan WHERE id = ? AND is_active = 1");
$stmt->execute([$id]);
$surat = $stmt->fetch();

if (!$surat) {
    die("Surat keterangan tidak ditemukan");
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Preview -
        <?= htmlspecialchars($surat['nama_surat']) ?>
    </title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.6;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
            text-transform: uppercase;
            font-size: 20px;
        }

        .header p {
            margin: 5px 0;
            font-size: 14px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 30px 0;
            font-size: 16px;
        }

        .content {
            text-align: justify;
            margin: 20px 0;
        }

        .field {
            display: inline-block;
            border-bottom: 1px dotted #000;
            min-width: 200px;
            padding: 0 10px;
        }

        .signature {
            margin-top: 50px;
            text-align: right;
            padding-right: 100px;
        }

        .no-print {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 20px;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="header">
        <h2>MTsN 1 KOTA PEKANBARU</h2>
        <p>Jl. Arifin Ahmad No.1, Pekanbaru, Riau 28114</p>
        <p>Telp: (0761) 123456 | Email: info@mtsn1pekanbaru.sch.id</p>
    </div>

    <div class="title">
        <?= strtoupper(htmlspecialchars($surat['nama_surat'])) ?>
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini:</p>

        <table style="width: 100%; margin: 20px 0;">
            <tr>
                <td style="width: 150px;">Nama</td>
                <td>: <span class="field">[Nama Kepala Sekolah]</span></td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>: Kepala Sekolah <span class="field">[Nama Sekolah Asal]</span></td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>: <span class="field">[Alamat Sekolah Lengkap]</span></td>
            </tr>
        </table>

        <p>Dengan ini menerangkan bahwa:</p>

        <table style="width: 100%; margin: 20px 0;">
            <tr>
                <td style="width: 150px;">Nama</td>
                <td>: <span class="field">[Nama Lengkap Murid]</span></td>
            </tr>
            <tr>
                <td>NISN</td>
                <td>: <span class="field">[Nomor NISN]</span></td>
            </tr>
            <tr>
                <td>Tempat, Tgl Lahir</td>
                <td>: <span class="field">[Tempat, DD/MM/YYYY]</span></td>
            </tr>
            <tr>
                <td>Asal Sekolah</td>
                <td>: <span class="field">[Nama Sekolah Asal]</span></td>
            </tr>
        </table>

        <?php if (stripos($surat['nama_surat'], 'PRESTASI') !== false): ?>
            <p>Adalah benar siswa/siswi kami yang telah meraih prestasi sebagai berikut:</p>
            <ol>
                <li><span class="field">[Nama Prestasi 1]</span> - Tingkat <span
                        class="field">[Kota/Provinsi/Nasional]</span> - Tahun <span class="field">[YYYY]</span></li>
                <li><span class="field">[Nama Prestasi 2]</span> - Tingkat <span
                        class="field">[Kota/Provinsi/Nasional]</span> - Tahun <span class="field">[YYYY]</span></li>
            </ol>
        <?php elseif (stripos($surat['nama_surat'], 'PERINGKAT') !== false): ?>
            <p>Adalah benar siswa/siswi kami yang memiliki:</p>
            <ul>
                <li>Peringkat: <span class="field">[Ranking]</span> dari <span class="field">[Jumlah Murid]</span> murid
                </li>
                <li>Tahun Pelajaran: <span class="field">[Tahun Pelajaran]</span></li>
            </ul>
        <?php elseif (stripos($surat['nama_surat'], 'NILAI') !== false): ?>
            <p>Adalah benar siswa/siswi kami yang memiliki nilai rata-rata rapor sebagai berikut:</p>
            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <tr>
                    <th style="border: 1px solid #000; padding: 8px;">Kelas</th>
                    <th style="border: 1px solid #000; padding: 8px;">Semester</th>
                    <th style="border: 1px solid #000; padding: 8px;">Nilai Rata-rata</th>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;">IV</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;">Ganjil</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;"><span class="field">[0.00]</span>
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;">IV</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;">Genap</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;"><span class="field">[0.00]</span>
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;">V</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;">Ganjil</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;"><span class="field">[0.00]</span>
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;">V</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;">Genap</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;"><span class="field">[0.00]</span>
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;">VI</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;">Ganjil</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;"><span class="field">[0.00]</span>
                    </td>
                </tr>
            </table>
        <?php elseif (stripos($surat['nama_surat'], 'TAHFIDZ') !== false): ?>
            <p>Adalah benar siswa/siswi kami yang memiliki hafalan Al-Qur'an sebagai berikut:</p>
            <ul>
                <li>Jumlah Juz: <span class="field">[Jumlah Juz]</span> Juz</li>
                <li>Detail Hafalan: <span class="field">[Juz 1, Juz 2, dst]</span></li>
                <li>Tahun: <span class="field">[YYYY]</span></li>
            </ul>
        <?php endif; ?>

        <p style="margin-top: 30px;">
            Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.
        </p>
    </div>

    <div class="signature">
        <p>Pekanbaru, <span class="field">[Tanggal]</span></p>
        <p>Kepala Sekolah,</p>
        <br><br><br>
        <p>
            <strong><span class="field">[Nama Kepala Sekolah]</span></strong><br>
            NIP. <span class="field">[NIP]</span>
        </p>
        <p style="margin-top: -15px; font-size: 12px;">(Stempel Sekolah)</p>
    </div>

    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary"
            style="padding: 10px 20px; border-radius: 50px; border: none; background: #0f5132; color: white; cursor: pointer; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
            <i class="fas fa-print"></i> Cetak
        </button>
        <button onclick="window.close()" class="btn btn-secondary"
            style="padding: 10px 20px; border-radius: 50px; border: none; background: #6c757d; color: white; cursor: pointer; margin-left: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
            Tutup
        </button>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>

</html>