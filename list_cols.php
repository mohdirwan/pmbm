<?php
require 'includes/config.php';
$stmt = $pdo->query('DESCRIBE pendaftar');
$cols = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $cols[] = $row['Field'];
}
echo "Total columns: " . count($cols) . "\n";
echo "First 20: " . implode(", ", array_slice($cols, 0, 20)) . "\n";
echo "Check for no_pendaftaran: " . (in_array('no_pendaftaran', $cols) ? "FOUND" : "NOT FOUND") . "\n";
