<?php
require_once "includes/config.php";
$stmt = $pdo->query("SHOW INDEX FROM pendaftar");
print_r($stmt->fetchAll());
$stmt = $pdo->query("SHOW INDEX FROM settings");
print_r($stmt->fetchAll());
