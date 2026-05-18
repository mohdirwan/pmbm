<?php
$pdo = new PDO('mysql:host=localhost;dbname=ppdb_mtsn1', 'root', '');
echo $pdo->query('SELECT COUNT(*) FROM pendaftar')->fetchColumn();
?>
