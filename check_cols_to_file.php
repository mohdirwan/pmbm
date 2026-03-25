<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require_once 'includes/config.php';
$fh = fopen('columns.txt', 'w');
try {
    $stmt = $pdo->query("DESCRIBE jalur_pendaftaran");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fwrite($fh, print_r($row, true));
    }
} catch (Exception $e) {
    fwrite($fh, "Error: " . $e->getMessage());
}
fclose($fh);
echo "Done";
