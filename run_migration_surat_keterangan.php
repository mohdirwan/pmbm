<?php
require_once 'includes/config.php';

echo "=== Running Migration: Add Surat Keterangan Table ===\n\n";

try {
    $sql = file_get_contents('migration_add_surat_keterangan.sql');
    $pdo->exec($sql);

    echo "✅ SUCCESS: Surat Keterangan table created and seeded!\n";
    echo "✅ 4 default surat keterangan added.\n\n";

    echo "Next steps:\n";
    echo "1. Access: http://localhost/pmbm/surat_keterangan.php\n";
    echo "2. Admin menu: http://localhost/pmbm/admin/surat_keterangan/index.php\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
