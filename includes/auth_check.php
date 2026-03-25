<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    // Safety fallback if constant is missing
    $login_path = defined('ADMIN_LOGIN_PATH') ? ADMIN_LOGIN_PATH : 'pintu_masuk_admin_pmbm.php';
    $base_url = defined('BASE_URL') ? BASE_URL : '';

    header("Location: " . $base_url . $login_path);
    exit();
}
?>