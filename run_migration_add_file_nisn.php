<?php
require_once 'includes/config.php';

echo "=== Running Migration: Add file_nisn Column ===\n\n";

try {
    $sql = file_get_contents('migration_add_file_nisn.sql');
    $pdo->exec($sql);

    echo "✅ SUCCESS: Kolom 'file_nisn' berhasil ditambahkan!\n";
    echo "✅ Murid sekarang bisa upload Print Out NISN.\n\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
