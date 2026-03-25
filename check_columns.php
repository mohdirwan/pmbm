<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'includes/config.php';

echo "<h2>Check Columns</h2>";

try {
    echo "<h3>Jalur Pendaftaran</h3>";
    $stmt = $pdo->query("DESCRIBE jalur_pendaftaran");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($cols);

    echo "<h3>Pendaftar</h3>";
    $stmt = $pdo->query("DESCRIBE pendaftar");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($cols);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>