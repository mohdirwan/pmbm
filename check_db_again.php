<?php
require 'includes/config.php';
$stmt = $pdo->query('DESCRIBE pendaftar');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . "\n";
}
