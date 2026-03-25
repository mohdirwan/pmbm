<?php
require_once 'includes/config.php';

echo "=== FIX: Clean Syarat Jalur Akademik ===\n\n";

try {
    $sql = file_get_contents('fix_syarat_jalur_akademik.sql');
    $pdo->exec($sql);

    echo "✅ SUCCESS: Syarat berhasil dibersihkan!\n\n";

    // Show result
    $stmt = $pdo->query("SELECT syarat FROM jalur_pendaftaran WHERE nama_jalur = 'Jalur Akademik'");
    $result = $stmt->fetch();

    echo "Syarat baru (bersih):\n";
    echo "====================\n";
    echo $result['syarat'] . "\n\n";

    $syarat_list = array_map('trim', explode(',', $result['syarat']));
    echo "Total dokumen: " . count($syarat_list) . "\n\n";

    foreach ($syarat_list as $idx => $s) {
        echo ($idx + 1) . ". " . $s . "\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
