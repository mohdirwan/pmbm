<?php
require_once 'includes/config.php';

echo "=== RUNNING DATABASE MIGRATION ===\n\n";

// Read SQL file
$sql_content = file_get_contents('migration_dokumen_jalur.sql');

// Remove SQL comments
$sql_content = preg_replace('/^--.*$/m', '', $sql_content);
$sql_content = preg_replace('/\/\*.*?\*\//s', '', $sql_content);

// Split by semicolon at end of line
$statements = explode(';', $sql_content);

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    $statement = trim($statement);

    // Skip empty statements
    if (empty($statement)) {
        continue;
    }

    echo "Executing statement...\n";

    try {
        $pdo->exec($statement);
        echo "✓ Executed successfully\n\n";
        $success_count++;
    } catch (PDOException $e) {
        // Check if error is "duplicate column" which is acceptable
        if (
            strpos($e->getMessage(), 'Duplicate column') !== false ||
            strpos($e->getMessage(), 'duplicate column') !== false
        ) {
            echo "⚠ Column already exists (skipped): " . $e->getMessage() . "\n\n";
        } else {
            echo "✗ Error: " . $e->getMessage() . "\n\n";
            $error_count++;
        }
    }
}

echo "\n=== MIGRATION COMPLETE ===\n";
echo "Success: $success_count\n";
echo "Errors: $error_count\n";
echo "\nNOTE: 'Duplicate column' warnings are normal and can be ignored.\n";
