<?php
require_once 'includes/config.php';
$stmt = $pdo->query("SELECT * FROM role_access");
$access = $stmt->fetchAll();
header('Content-Type: application/json');
echo json_encode($access);
