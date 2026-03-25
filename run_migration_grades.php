<?php
require_once 'includes/config.php';
try {
    $sql = file_get_contents('add_grade_columns.sql');
    $pdo->exec($sql);
    echo "Columns added successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>