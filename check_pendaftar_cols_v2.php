<?php
require_once 'includes/config.php';
$stmt = $pdo->query("DESCRIBE pendaftar");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($columns as $col)
    echo $col . "\n";
