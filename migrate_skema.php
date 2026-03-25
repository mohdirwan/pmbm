<?php
require_once 'includes/config.php';
$sql = file_get_contents('update_skema.sql');
try {
    $pdo->exec($sql);
    echo "Skema settings updated.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>