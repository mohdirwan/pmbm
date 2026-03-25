<?php
require_once 'includes/config.php';

echo "=== DEBUG: Cek Data Jalur Akademik ===\n\n";

try {
    $stmt = $pdo->query("SELECT * FROM jalur_pendaftaran WHERE nama_jalur = 'Jalur Akademik'");
    $jalur = $stmt->fetch();

    if ($jalur) {
        echo "ID: " . $jalur['id'] . "\n";
        echo "Nama Jalur: " . $jalur['nama_jalur'] . "\n\n";
        echo "Syarat saat ini:\n";
        echo "================\n";
        echo $jalur['syarat'] . "\n\n";

        echo "Syarat dipecah per item:\n";
        echo "========================\n";
        $syarat_list = array_map('trim', explode(',', $jalur['syarat']));
        foreach ($syarat_list as $index => $syarat) {
            echo ($index + 1) . ". " . $syarat . "\n";
        }

    } else {
        echo "❌ Jalur Akademik tidak ditemukan!\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
