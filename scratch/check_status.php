<?php
require 'c:/xampp/htdocs/pmbm/includes/config.php';
$stmt = $pdo->prepare('SELECT status, status_tahfidz FROM pendaftar WHERE no_pendaftaran = ?');
$stmt->execute(['202605210937']);
print_r($stmt->fetch(PDO::FETCH_ASSOC));
