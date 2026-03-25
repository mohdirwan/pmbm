<?php
/**
 * Migration Script: Add Document Upload Fields
 * Date: 2026-02-07
 * Purpose: Menambahkan kolom dokumen ke tabel pendaftar
 */

require_once 'includes/config.php';

echo "=== Migration: Add Document Upload Fields ===\n\n";

try {
    $pdo->beginTransaction();

    // Read SQL migration file
    $sqlFile = __DIR__ . '/migration_add_document_fields.sql';

    if (!file_exists($sqlFile)) {
        throw new Exception("Migration file not found: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);

    // Split by semicolon to execute multiple statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function ($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );

    echo "Executing migration...\n";

    foreach ($statements as $statement) {
        if (stripos($statement, 'SELECT') === 0) {
            // Execute SELECT and show results
            $stmt = $pdo->query($statement);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($results)) {
                echo "\n✅ Verified columns:\n";
                foreach ($results as $row) {
                    echo "  - {$row['COLUMN_NAME']}: {$row['COLUMN_TYPE']} ";
                    echo "({$row['COLUMN_COMMENT']})\n";
                }
            }
        } else {
            // Execute ALTER TABLE
            $pdo->exec($statement);
            echo "  ✓ Executed: " . substr($statement, 0, 50) . "...\n";
        }
    }

    $pdo->commit();

    echo "\n✅ Migration completed successfully!\n";
    echo "\nDocument upload fields are now ready to use in register.php\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Migration Complete ===\n";
?>