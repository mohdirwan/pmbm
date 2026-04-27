<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require 'includes/config.php';
$stmt = $pdo->query("SHOW CREATE TABLE pendaftar");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo $row['Create Table'];
