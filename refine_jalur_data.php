<?php
require_once 'includes/config.php';

try {
    // Correct the requirements for all channels
    $jalur_data = [
        [
            'nama' => 'Jalur Akademik Melalui Tes',
            'syarat' => 'Pas foto 3x4 latar belakang merah (wajib),Surat keterangan nilai rata-rata (wajib),Surat Keterangan Ranking/Peringkat (pilihan),Sertifikat Prestasi Akademik (pilihan),Printout NISN (wajib),Pakta Integritas (wajib)'
        ],
        [
            'nama' => 'Jalur Akademik Tanpa Tes',
            'syarat' => 'Pas foto 3x4 latar belakang merah (wajib),Surat keterangan nilai rata-rata (wajib),Sertifikat Prestasi Akademik (wajib),Printout NISN (wajib),Pakta Integritas (wajib)'
        ],
        [
            'nama' => 'Jalur Non Akademik Melalui Tes',
            'syarat' => 'Pas foto 3x4 latar belakang merah (wajib),Surat keterangan nilai rata-rata (wajib),Surat Keterangan Prestasi Non Akademik (pilihan),Sertifikat Prestasi Non Akademik (pilihan),Printout NISN (wajib),Pakta Integritas (wajib)'
        ],
        [
            'nama' => 'Jalur Non Akademik Tanpa Tes',
            'syarat' => 'Pas foto 3x4 latar belakang merah (wajib),Surat keterangan nilai rata-rata (wajib),Sertifikat Prestasi Non Akademik (wajib),Printout NISN (wajib),Pakta Integritas (wajib)'
        ],
        [
            'nama' => 'Jalur Tahfidz',
            'syarat' => 'Pas foto 3x4 latar belakang merah (wajib),Surat keterangan nilai rata-rata (wajib),Surat Keterangan Tahfidz (wajib),Sertifikat Tahfidz (wajib),Printout NISN (wajib),Pakta Integritas (wajib)'
        ]
    ];

    // Delete old ambiguous jalur if ID 1 is "Jalur Akademik" exactly
    $pdo->exec("DELETE FROM jalur_pendaftaran WHERE nama_jalur = 'Jalur Akademik' AND id NOT IN (SELECT id FROM (SELECT id FROM jalur_pendaftaran WHERE nama_jalur IN ('Jalur Akademik Melalui Tes','Jalur Akademik Tanpa Tes')) as t)");

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

    echo "Successfully refined jalur requirements.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>