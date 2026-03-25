<?php
/**
 * Migration Script: Add Kontak WA Fields
 * Date: 2026-02-07
 * Purpose: Menambahkan kolom kontak_wa dan nama_kontak_wa ke tabel pendaftar
 */

require_once 'includes/config.php';

echo "=== Migration: Add Kontak WA Fields ===\n\n";

try {
    $pdo->beginTransaction();

    // Read SQL migration file
    $sqlFile = __DIR__ . '/migration_add_kontak_wa.sql';

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
    echo "\nKontak WA fields are now ready to use!\n";
    echo "\nNew fields added:\n";
    echo "  - kontak_wa (VARCHAR 15) - Nomor WhatsApp yang bisa dihubungi\n";
    echo "  - nama_kontak_wa (VARCHAR 100) - Nama pemilik nomor WhatsApp\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Migration Complete ===\n";
?>