<?php
require_once 'includes/config.php';
$stmt = $pdo->query("DESCRIBE pendaftar");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($columns, JSON_PRETTY_PRINT);
