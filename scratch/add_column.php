<?php
require_once 'includes/config.php';
try {
    $pdo->exec("ALTER TABLE pendaftar ADD COLUMN password_cbt VARCHAR(50) NULL AFTER password_plain");
    echo "Column password_cbt added successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
