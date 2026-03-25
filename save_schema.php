<?php
require_once 'includes/config.php';
$stmt = $pdo->query("DESCRIBE pendaftar");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
file_put_contents('pendaftar_schema.json', json_encode($columns, JSON_PRETTY_PRINT));
echo "Schema saved to pendaftar_schema.json";
