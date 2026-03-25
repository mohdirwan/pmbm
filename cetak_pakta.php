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
    <title>Pakta Integritas -
        <?= htmlspecialchars($no_pendaftaran) ?>
    </title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 40px;
            background: #fff;
        }

        .document-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
            text-transform: uppercase;
            font-size: 20px;
            letter-spacing: 1px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 22px;
            margin-bottom: 30px;
            text-decoration: underline;
        }

        .content {
            margin-bottom: 20px;
            text-align: justify;
        }

        .identity-table {
            width: 100%;
            margin: 20px 0 20px 40px;
        }

        .identity-table td {
            padding: 5px;
        }

        .label {
            width: 200px;
        }

        .colon {
            width: 20px;
        }

        .point-list {
            margin-left: 20px;
            margin-bottom: 20px;
        }

        .point-item {
            margin-bottom: 10px;
            display: flex;
        }

        .point-number {
            width: 25px;
            flex-shrink: 0;
        }

        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: flex-end;
        }

        .signature-area {
            text-align: center;
            width: 300px;
        }

        .stamp-box {
            border: 1px dashed #666;
            width: 80px;
            height: 40px;
            margin: 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #666;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="document-container">
        <div class="header">
            <h2>PANITIA PENERIMAAN MURID BARU MADRASAH (PMBM)</h2>
            <h2>MTsN 1 KOTA PEKANBARU</h2>
            <p>Tahun Pelajaran 2026/2027</p>
        </div>

        <div class="title">SURAT PERNYATAAN / PAKTA INTEGRITAS</div>

        <div class="content">
            Yang bertanda tangan di bawah ini :
        </div>

        <table class="identity-table">
            <tr>
                <td class="label">Nama Lengkap Murid</td>
                <td class="colon">:</td>
                <td style="text-transform: uppercase; font-weight: bold;">
                    <?= htmlspecialchars($student['nama_lengkap']) ?>
                </td>
            </tr>
            <tr>
                <td class="label">No. Pendaftaran</td>
                <td class="colon">:</td>
                <td style="font-weight: bold;">
                    <?= htmlspecialchars($student['no_pendaftaran']) ?>
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
                <td class="label">Asal Sekolah</td>
                <td class="colon">:</td>
                <td>
                    <?= htmlspecialchars($student['asal_sekolah']) ?>
                </td>
            </tr>
            <tr>
                <td class="label">Nama Orang Tua/Wali</td>
                <td class="colon">:</td>
                <td>
                    <?= htmlspecialchars($student['nama_ayah'] ?: $student['nama_ibu']) ?>
                </td>
            </tr>
        </table>

        <div class="content">
            Menyatakan dengan sesungguhnya bahwa:
        </div>

        <div class="point-list">
            <div class="point-item">
                <div class="point-number">1.</div>
                <div>Seluruh data dan informasi yang saya isikan dalam formulir pendaftaran PMBM MTsN 1 Kota Pekanbaru
                    ini adalah <strong>BENAR</strong> dan sesuai dengan dokumen aslinya.</div>
            </div>
            <div class="point-item">
                <div class="point-number">2.</div>
                <div>Seluruh dokumen unggahan (upload) baik berupa foto maupun berkas PDF adalah dokumen asli dan tidak
                    ada unsur rekayasa.</div>
            </div>
            <div class="point-item">
                <div class="point-number">3.</div>
                <div>Apabila di kemudian hari ditemukan bahwa data atau dokumen yang saya sampaikan tidak benar/palsu,
                    maka saya bersedia menerima sanksi berupa <strong>PEMBATALAN</strong> status sebagai calon peserta
                    didik di MTsN 1 Kota Pekanbaru.</div>
            </div>
            <div class="point-item">
                <div class="point-number">4.</div>
                <div>Saya akan mematuhi segala peraturan dan ketentuan yang telah ditetapkan oleh Panitia PMBM MTsN 1
                    Kota Pekanbaru.</div>
            </div>
        </div>

        <div class="content">
            Demikian surat pernyataan ini saya buat dengan penuh kesadaran dan tanggung jawab tanpa ada paksaan dari
            pihak manapun.
        </div>

        <div class="footer">
            <div class="signature-area">
                Pekanbaru,
                <?= date('d F Y') ?><br>
                Orang Tua / Wali Murid,<br>
                <div class="stamp-box">MATERAI<br>10.000</div>
                <br>
                ( ........................................ )<br>
                <small>Tanda tangan & Nama Terang</small>
            </div>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 50px; border-top: 1px solid #ddd; padding-top: 20px;">
        <button onclick="window.print()"
            style="padding: 12px 24px; font-weight: bold; cursor: pointer; background: #28a745; color: white; border: none; border-radius: 5px;">Cetak
            Pakta Integritas</button>
        <button onclick="window.close()"
            style="padding: 12px 24px; cursor: pointer; border-radius: 5px; margin-left: 10px;">Tutup</button>
    </div>
</body>

</html>