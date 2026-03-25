<?php
require 'includes/config.php';
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'jalur_pendaftaran'");
    if ($stmt->fetch()) {
        echo "Table 'jalur_pendaftaran' exists.\n";
    } else {
        echo "Table 'jalur_pendaftaran' MISSING!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
