<?php
require_once 'includes/config.php';

try {
    // 1. Ensure columns exist
    $pdo->exec("ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS file_nisn VARCHAR(255) NULL");
    $pdo->exec("ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS file_pakta VARCHAR(255) NULL");
    $pdo->exec("ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS file_nilai_rata VARCHAR(255) NULL");
    $pdo->exec("ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS file_ranking VARCHAR(255) NULL");
    $pdo->exec("ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS file_surat_prestasi VARCHAR(255) NULL");
    $pdo->exec("ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS file_sertifikat_prestasi VARCHAR(255) NULL");
    $pdo->exec("ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS file_surat_tahfidz VARCHAR(255) NULL");
    $pdo->exec("ALTER TABLE pendaftar ADD COLUMN IF NOT EXISTS file_sertifikat_tahfidz VARCHAR(255) NULL");

    // 2. Update Jalur Pendaftaran data
    $jalur_data = [
        [
            'nama' => 'Jalur Akademik Melalui Tes',
            'syarat' => 'Pas foto 3x4 latar belakang merah (wajib),Surat keterangan nilai rata-rata (wajib),Surat Keterangan Peringkat (pilihan),Surat Keterangan Prestasi Akademik (pilihan),Sertifikat Prestasi Akademik (pilihan),Printout NISN (wajib),Pakta Integritas (wajib)'
        ],
        [
            'nama' => 'Jalur Akademik Tanpa Tes',
            'syarat' => 'Pas foto 3x4 latar belakang merah (wajib),Surat keterangan nilai rata-rata (wajib),Surat Keterangan Prestasi Akademik (wajib),Sertifikat Prestasi Akademik (wajib),Printout NISN (wajib),Pakta Integritas (wajib)'
        ],
        [
            'nama' => 'Jalur Non Akademik Melalui Tes',
            'syarat' => 'Pas foto 3x4 latar belakang merah (wajib),Surat keterangan nilai rata-rata (wajib),Surat Keterangan Prestasi Non Akademik (wajib),Sertifikat Prestasi Non Akademik (wajib),Printout NISN (wajib),Pakta Integritas (wajib)'
        ],
        [
            'nama' => 'Jalur Non Akademik Tanpa Tes',
            'syarat' => 'Pas foto 3x4 latar belakang merah (wajib),Surat keterangan nilai rata-rata (wajib),Surat Keterangan Prestasi Non Akademik (wajib),Sertifikat Prestasi Non Akademik (wajib),Printout NISN (wajib),Pakta Integritas (wajib)'
        ],
        [
            'nama' => 'Jalur Tahfidz',
            'syarat' => 'Pas foto 3x4 latar belakang merah (wajib),Surat keterangan nilai rata-rata (wajib),Surat Keterangan Tahfidz (wajib),Sertifikat Tahfidz (wajib),Printout NISN (wajib),Pakta Integritas (wajib)'
        ]
    ];

    $stmt_check = $pdo->prepare("SELECT id FROM jalur_pendaftaran WHERE nama_jalur = ?");
    $stmt_insert = $pdo->prepare("INSERT INTO jalur_pendaftaran (nama_jalur, syarat) VALUES (?, ?)");
    $stmt_update = $pdo->prepare("UPDATE jalur_pendaftaran SET syarat = ? WHERE id = ?");

    foreach ($jalur_data as $j) {
        $stmt_check->execute([$j['nama']]);
        $row = $stmt_check->fetch();

        if ($row) {
            $stmt_update->execute([$j['syarat'], $row['id']]);
            echo "Updated: {$j['nama']}\n";
        } else {
            $stmt_insert->execute([$j['nama'], $j['syarat']]);
            echo "Inserted: {$j['nama']}\n";
        }
    }

    echo "Successfully updated jalur requirements.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>