<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/config.php';
echo "Config loaded.<br>";

try {
    $stmt = $pdo->query("SELECT * FROM pendaftar LIMIT 1");
    echo "Table pendaftar exists.<br>";
} catch (Exception $e) {
    echo "Error pendaftar: " . $e->getMessage() . "<br>";
}

try {
    $stmt = $pdo->query("SELECT * FROM jalur_pendaftaran LIMIT 1");
    echo "Table jalur_pendaftaran exists.<br>";
} catch (Exception $e) {
    echo "Error jalur: " . $e->getMessage() . "<br>";
}

echo "Done.";
?>