<?php
$_SERVER['HTTP_HOST'] = 'localhost'; // Mock for config.php
require_once 'includes/config.php';
try {
    $stmt = $pdo->query("DESCRIBE jalur_pendaftaran");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($columns, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
