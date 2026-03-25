<?php
require_once 'includes/config.php';

echo "=== Update Syarat Jalur Akademik ===\n\n";

try {
    $sql = file_get_contents('update_syarat_jalur_akademik.sql');
    $pdo->exec($sql);

    echo "✅ SUCCESS: Syarat Jalur Akademik berhasil diupdate!\n\n";
    echo "Syarat baru:\n";
    echo "- Pas Foto 3x4 Berlatar Merah\n";
    echo "- Rapor Asli\n";
    echo "- Surat Keterangan Rata Rata Nilai\n";
    echo "- Surat Keterangan Ranking/Peringkat\n";
    echo "- Sertifikat Prestasi Akademik\n";
    echo "- Akta Kelahiran\n";
    echo "- Print Out NISN\n\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
