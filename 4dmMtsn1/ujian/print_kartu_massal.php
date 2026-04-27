<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$stmt = $pdo->query("SELECT * FROM pendaftar WHERE status = 'Terverifikasi' AND test_hari IS NOT NULL ORDER BY test_hari ASC, test_sesi ASC");
$students = $stmt->fetchAll();

$school_name = get_setting('school_name', 'MTsN 1 KOTA PEKANBARU');
$school_logo = get_setting('school_logo');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu Ujian Massal</title>
    <style>
        @media print {
            .no-print { display: none; }
            .page-break { page-break-after: always; }
        }
        body { font-family: 'Arial', sans-serif; background: #f0f0f0; margin: 0; padding: 20px; }
        .grid-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .kartu { 
            background: white; border: 2px solid #0b2c24; border-radius: 10px; 
            padding: 15px; position: relative; overflow: hidden; height: 350px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .header { display: flex; align-items: center; border-bottom: 2px solid #0b2c24; padding-bottom: 10px; margin-bottom: 15px; }
        .logo { width: 50px; height: 50px; margin-right: 15px; object-fit: contain; }
        .title { flex-grow: 1; text-align: center; }
        .title h4 { margin: 0; color: #0b2c24; font-size: 14px; text-transform: uppercase; }
        .title p { margin: 2px 0 0; font-size: 10px; color: #666; }
        
        .content { display: flex; }
        .photo { width: 90px; height: 120px; border: 1px solid #ddd; margin-right: 15px; padding: 2px; }
        .photo img { width: 100%; height: 100%; object-fit: cover; }
        
        .info { flex-grow: 1; font-size: 12px; }
        .info table { width: 100%; border-collapse: collapse; }
        .info td { padding: 3px 0; vertical-align: top; }
        .label { font-weight: bold; width: 100px; color: #555; }
        
        .jadwal-box { 
            margin-top: 15px; background: #e8f5e9; border: 1px dashed #2e7d32; 
            padding: 8px; border-radius: 5px; text-align: center;
        }
        .jadwal-box strong { color: #1b5e20; display: block; font-size: 13px; }
        
        .watermark { 
            position: absolute; bottom: -20px; right: -20px; font-size: 80px; 
            color: rgba(11, 44, 36, 0.05); transform: rotate(-25deg); z-index: 0; pointer-events: none;
        }
        .footer-note { font-size: 9px; color: #888; font-style: italic; margin-top: 10px; border-top: 1px solid #eee; padding-top: 5px; }
        
        .btn-print { 
            position: fixed; bottom: 30px; right: 30px; padding: 15px 30px; 
            background: #0b2c24; color: white; border: none; border-radius: 50px;
            font-weight: bold; cursor: pointer; box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            text-decoration: none;
        }
    </style>
</head>
<body>

    <a href="javascript:window.print()" class="btn-print no-print"><i class="fas fa-print"></i> CETAK SEKARANG</a>

    <div class="grid-container">
        <?php foreach($students as $index => $s): ?>
            <div class="kartu">
                <div class="watermark"><i class="fas fa-graduation-cap"></i></div>
                <div class="header">
                    <img src="<?= BASE_URL . ($school_logo ?: 'assets/img/logo-default.png') ?>" class="logo">
                    <div class="title">
                        <h4>KARTU PESERTA TEST AKADEMIK</h4>
                        <p>PENERIMAAN MURID BARU MADRASAH (PMBM)</p>
                        <p class="fw-bold"><?= $school_name ?></p>
                    </div>
                </div>

                <div class="content">
                    <div class="photo">
                        <?php if($s['foto_siswa']): ?>
                            <img src="<?= BASE_URL ?>uploads/<?= $s['foto_siswa'] ?>" alt="Foto">
                        <?php else: ?>
                            <div style="width:100%; height:100%; background:#eee; display:flex; align-items:center; text-align:center; font-size:10px;">Foto Kosong</div>
                        <?php endif; ?>
                    </div>
                    <div class="info">
                        <table>
                            <tr>
                                <td class="label">No. Reg</td>
                                <td>: <strong><?= $s['no_pendaftaran'] ?></strong></td>
                            </tr>
                            <tr>
                                <td class="label">Nama</td>
                                <td>: <?= strtoupper($s['nama_lengkap']) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Username</td>
                                <td>: <strong><?= $s['nisn'] ?></strong></td>
                            </tr>
                            <tr>
                                <td class="label">Password</td>
                                <td>: <span style="font-family: monospace; color: #d32f2f;">[Password Rahasia Anda]</span></td>
                            </tr>
                        </table>

                        <div class="jadwal-box">
                            <small>JADWAL UJIAN:</small>
                            <strong><?= $s['test_hari'] ?> | <?= $s['test_sesi'] ?></strong>
                            <div style="font-size: 11px; margin-top: 3px; color: #2e7d32; font-weight: bold;">
                                <i class="far fa-clock"></i> Pukul: <?= $s['test_jam_mulai'] ?> - <?= $s['test_jam_selesai'] ?> WIB
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer-note">
                    * Harap dibawa saat ujian. Jika lupa password, hubungi panitia sebelum ujian dimulai.
                </div>
            </div>

            <?php if(($index + 1) % 6 == 0): ?>
                </div><div class="page-break"></div><div class="grid-container">
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>
</html>
