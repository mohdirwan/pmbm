<?php
require_once 'includes/config.php';

echo "=== Running Migration: Remove Kuota Column ===\n\n";

try {
    $sql = file_get_contents('migration_remove_kuota.sql');
    $pdo->exec($sql);

    echo "✅ SUCCESS: Kolom 'kuota' berhasil dihapus dari tabel jalur_pendaftaran!\n";
    echo "✅ Jalur pendaftaran sekarang tidak memiliki batasan kuota.\n\n";

    echo "Silakan akses:\n";
    echo "http://localhost/pmbm/admin/jalur/index.php\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
