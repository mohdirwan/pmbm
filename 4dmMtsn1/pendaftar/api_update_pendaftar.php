<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$id = $_POST['id'] ?? null;
$jalur_id = $_POST['jalur_id'] ?? null;
$nisn = $_POST['nisn'] ?? null;
$nik = $_POST['nik'] ?? null;

if (!$id || !$jalur_id || !$nisn || !$nik) {
    echo json_encode(['success' => false, 'message' => 'Semua data harus diisi.']);
    exit;
}

try {
    // Check if NISN already exists for other students
    $stmt_check = $pdo->prepare("SELECT id FROM pendaftar WHERE nisn = ? AND id != ?");
    $stmt_check->execute([$nisn, $id]);
    if ($stmt_check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'NISN sudah digunakan oleh pendaftar lain.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE pendaftar SET jalur_id = ?, nisn = ?, nik = ? WHERE id = ?");
    $result = $stmt->execute([$jalur_id, $nisn, $nik, $id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Data pendaftar berhasil diperbarui.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
