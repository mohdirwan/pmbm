<?php
require_once '../includes/config.php';

// Auth Check for Student
if (!isset($_SESSION['siswa_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login_siswa.php");
    exit();
}

// Get Student Data
$stmt = $pdo->prepare("SELECT p.*, j.nama_jalur 
                       FROM pendaftar p 
                       LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
                       WHERE p.id = ?");
$stmt->execute([$_SESSION['siswa_id']]);
$siswa = $stmt->fetch();

// Get PPDB Status
$ppdb_status = get_setting('ppdb_status', 'belum');

if (!$siswa || ($siswa['status'] !== 'Diterima' && $siswa['status'] !== 'Lulus') || $ppdb_status !== 'pengumuman') {
    echo "<script>alert('Akses ditolak. Bukti kelulusan belum tersedia atau Anda tidak dinyatakan lulus.'); window.location.href='status_akhir.php';</script>";
    exit();
}

// Get School Settings
$school_name = get_setting('nama_sekolah', 'MTsN 1 Kota Pekanbaru');
$school_address = get_setting('alamat_sekolah', 'Jl. Arifin Ahmad No. 1, Pekanbaru');
$school_phone = get_setting('telepon_sekolah', '(0761) 123456');
$school_logo = get_setting('school_logo', 'assets/img/logo.png');
$kemenag_logo = 'assets/img/logo_kemenag.png'; // Standard path
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Kelulusan - <?= htmlspecialchars($siswa['nama_lengkap']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Times New Roman', Times, serif;
        }
        .cert-container {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 20px auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .kop-surat {
            border-bottom: 3px double #000;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .logo-kemenag { height: 80px; }
        .logo-school { height: 80px; }
        .kop-text h4 { margin: 0; font-weight: bold; text-transform: uppercase; }
        .kop-text h2 { margin: 5px 0; font-weight: 800; text-transform: uppercase; font-size: 1.5rem; }
        .kop-text p { margin: 0; font-size: 0.9rem; }
        
        .title-box {
            text-align: center;
            margin-bottom: 40px;
        }
        .title-box h3 {
            text-decoration: underline;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .main-content {
            font-size: 1.1rem;
            line-height: 1.6;
            text-align: justify;
        }
        
        .student-info {
            width: 80%;
            margin: 20px auto;
        }
        
        .student-info td {
            padding: 5px 0;
        }
        
        .signature-box {
            margin-top: 50px;
            float: right;
            width: 250px;
            text-align: center;
        }

        .no-print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        @media print {
            body { background: #fff; }
            .cert-container { 
                margin: 0; 
                box-shadow: none;
                width: 100%;
            }
            .no-print-btn { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print-btn">
        <button class="btn btn-primary shadow" onclick="window.print()">
            <i class="fas fa-print"></i> Cetak Bukti Lulus
        </button>
        <a href="status_akhir.php" class="btn btn-secondary shadow">Kembali</a>
    </div>

    <div class="cert-container">
        <!-- Kop Surat -->
        <div class="kop-surat d-flex align-items-center justify-content-between">
            <img src="<?= BASE_URL . $kemenag_logo ?>" alt="Kemenag" class="logo-kemenag">
            <div class="kop-text text-center flex-grow-1 px-3">
                <h4>Kementerian Agama Republik Indonesia</h4>
                <h4>Kantor Kementerian Agama Kota Pekanbaru</h4>
                <h2><?= htmlspecialchars($school_name) ?></h2>
                <p><?= htmlspecialchars($school_address) ?></p>
                <p>Telp: <?= htmlspecialchars($school_phone) ?></p>
            </div>
            <img src="<?= BASE_URL . $school_logo ?>" alt="Logo Sekolah" class="logo-school">
        </div>

        <div class="title-box">
            <h3>SURAT KETERANGAN LULUS SELEKSI</h3>
            <p>Nomor: <?= date('Y') ?>/PMBM/SKL/<?= str_pad($siswa['id'], 4, '0', STR_PAD_LEFT) ?></p>
        </div>

        <div class="main-content">
            <p>Kepala <?= htmlspecialchars($school_name) ?> dengan ini menerangkan bahwa:</p>
            
            <table class="student-info">
                <tr>
                    <td width="35%">Nama Lengkap</td>
                    <td width="3%">:</td>
                    <td class="fw-bold"><?= htmlspecialchars($siswa['nama_lengkap']) ?></td>
                </tr>
                <tr>
                    <td>Nomor Pendaftaran</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($siswa['no_pendaftaran']) ?></td>
                </tr>
                <tr>
                    <td>NISN</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($siswa['nisn']) ?></td>
                </tr>
                <tr>
                    <td>Jalur Pendaftaran</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($siswa['nama_jalur']) ?></td>
                </tr>
                <tr>
                    <td>Asal Sekolah</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($siswa['asal_sekolah']) ?></td>
                </tr>
            </table>

            <p class="mt-4">
                Berdasarkan hasil seleksi Penerimaan Murid Baru Madrasah (PMBM) Tahun Pelajaran <?= date('Y') ?>/<?= date('Y')+1 ?>, 
                Ananda dinyatakan:
            </p>

            <div class="text-center my-4 py-3 border border-dark rounded-3 bg-light">
                <h2 class="fw-bold mb-0">LULUS SELEKSI</h2>
            </div>

            <p>
                Demikian Surat Keterangan ini diberikan sebagai bukti kelulusan sementara untuk dipergunakan sebagaimana mestinya. 
                Selanjutnya, Ananda diwajibkan melakukan proses <strong>Daftar Ulang</strong> sesuai dengan jadwal dan teknis yang telah ditentukan.
            </p>
        </div>

        <!-- <div class="signature-box">
            <p>Pekanbaru, <?= date('d F Y') ?></p>
            <p>Panitia PMBM</p>
            <div style="height: 80px;"></div>
            <p class="fw-bold text-decoration-underline">PANITIA PMBM</p>
            <p>NIP. ...........................</p>
        </div> -->

        <div style="clear: both;"></div>
        
        <div class="mt-5 pt-5 small text-muted text-center border-top">
            <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?> melalui Sistem Informasi PMBM</p>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
