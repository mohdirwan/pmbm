<?php
require 'includes/config.php';
$stmt = $pdo->query("SELECT status, count(*) as count FROM pendaftar GROUP BY status");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
