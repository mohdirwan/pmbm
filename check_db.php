<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'includes/config.php';
$stmt = $pdo->query('DESCRIBE pendaftar');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . PHP_EOL;
}