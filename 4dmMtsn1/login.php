<?php
require_once '../includes/config.php';
// File ini dialihkan ke beranda untuk keamanan.
// Login admin gunakan link rahasia.
header("Location: ../index.php");
exit();
?>