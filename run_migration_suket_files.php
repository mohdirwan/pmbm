<?php
require_once 'includes/config.php';

echo "=== Running Migration: Add File Upload Fields ===\n\n";

try {
    $sql = file_get_contents('migration_add_suket_files.sql');
    $pdo->exec($sql);

    echo "✅ SUCCESS: File upload fields added to surat_keterangan table!\n";
    echo "✅ Added columns: file_preview_pdf, file_template_docx\n\n";

    echo "Next step: Upload files via admin panel\n";
    echo "Access: http://localhost/pmbm/admin/surat_keterangan/index.php\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
