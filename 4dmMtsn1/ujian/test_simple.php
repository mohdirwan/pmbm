<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "Simple test in subdirectory works!";
require_once '../../includes/config.php';
echo "<br>Config included successfully!";
echo "<br>Base URL: " . BASE_URL;

echo "<h2>Session Test</h2>";
session_start();
$_SESSION['test_val'] = "hello";
echo "Session ID: " . session_id() . "<br>";
echo "Session Val: " . $_SESSION['test_val'];
?>
