<?php
require_once 'includes/config.php';

echo "=== Update Data Jalur Pendaftaran ===\n\n";

try {
    $sql = file_get_contents('update_jalur_data.sql');
    $pdo->exec($sql);

    echo "✅ SUCCESS: Data jalur pendaftaran berhasil diupdate!\n\n";

    echo "Jalur yang ditambahkan:\n";
    echo "1. Jalur Akademik (100 murid)\n";
    echo "2. Jalur Minat Bakat Bidang Akademik (28 murid)\n";
    echo "3. Jalur Minat Bakat Bidang Akademik Tanpa Tes Tertulis (15 murid)\n";
    echo "4. Jalur Minat Bakat Bidang Non-Akademik (20 murid)\n";
    echo "5. Jalur Minat Bakat Bidang Non-Akademik Tanpa Tes Tertulis (10 murid)\n";
    echo "6. Jalur Tahfidz (50 murid)\n\n";

    echo "Total Kuota: 223 murid\n\n";

    echo "Silakan akses:\n";
    echo "http://localhost/pmbm/admin/jalur/index.php\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
