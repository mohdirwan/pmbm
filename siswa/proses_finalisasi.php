<?php
require_once '../includes/config.php';

// Auth Check for Student
if (!isset($_SESSION['siswa_id']) || $_SESSION['role'] !== 'siswa') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE pendaftar SET finalisasi = 'ya' WHERE id = ?");
        $stmt->execute([$_SESSION['siswa_id']]);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
?>
