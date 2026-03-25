<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require_once 'includes/config.php';
try {
    $stmt = $pdo->query("SELECT syarat, syarat_pilihan FROM jalur_pendaftaran");
    $rows = $stmt->fetchAll();
    echo "Successfully queried " . count($rows) . " rows.\n";
    foreach ($rows as $row) {
        echo "Jalur: " . substr($row['syarat'], 0, 20) . "... | Syarat Pilihan: " . ($row['syarat_pilihan'] ?? 'NULL') . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
