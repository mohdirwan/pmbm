<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (isset($_GET['nik'])) {
    $nik = clean_input($_GET['nik']);

    $stmt = $pdo->prepare("SELECT id FROM pendaftar WHERE nik = ?");
    $stmt->execute([$nik]);
    $exists = $stmt->fetch() ? true : false;

    echo json_encode(['exists' => $exists]);
} else {
    echo json_encode(['error' => 'No NIK provided']);
}
?>