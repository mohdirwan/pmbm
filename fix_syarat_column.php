<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require_once 'includes/config.php';

try {
    // Check if column exists
    $stmt = $pdo->query("DESCRIBE jalur_pendaftaran");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('syarat_pilihan', $columns)) {
        $pdo->exec("ALTER TABLE jalur_pendaftaran ADD COLUMN syarat_pilihan TEXT NULL AFTER syarat");
        echo "Successfully added 'syarat_pilihan' column to 'jalur_pendaftaran' table.\n";
    } else {
        echo "Column 'syarat_pilihan' already exists.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
