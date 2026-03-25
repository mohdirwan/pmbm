<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require_once 'includes/config.php';
$stmt = $pdo->query("SELECT * FROM settings");
$settings = $stmt->fetchAll();
header('Content-Type: text/plain');
foreach ($settings as $s) {
    echo $s['setting_key'] . ": " . $s['setting_value'] . "\n";
}
