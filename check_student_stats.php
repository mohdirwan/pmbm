<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require_once 'includes/config.php';
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM pendaftar GROUP BY status");
$stats = $stmt->fetchAll();
foreach ($stats as $s) {
    echo $s['status'] . ": " . $s['count'] . "\n";
}
