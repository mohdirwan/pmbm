<?php
require 'includes/config.php';
$stmt = $pdo->query("DESCRIBE pendaftar 'nilai_ujian'");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
