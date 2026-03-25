<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (isset($_GET['nisn'])) {
    $nisn = clean_input($_GET['nisn']);

    $stmt = $pdo->prepare("SELECT id FROM pendaftar WHERE nisn = ?");
    $stmt->execute([$nisn]);
    $exists = $stmt->fetch() ? true : false;

    echo json_encode(['exists' => $exists]);
} else {
    echo json_encode(['error' => 'No NISN provided']);
}
?>