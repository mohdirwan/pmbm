<?php
require_once 'includes/config.php';
$stmt = $pdo->query("SELECT * FROM settings");
$settings = $stmt->fetchAll();
print_r($settings);
?>