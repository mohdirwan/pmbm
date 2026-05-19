<?php
// Set Timezone to Asia/Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// ============================================
// 1. DATABASE CONFIGURATION (AUTO-DETECT)
// ============================================
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    // Pengaturan Lokal (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'ppdb_mtsn1');
} else {
    // Pengaturan Hosting Sesuai Data Anda
    define('DB_HOST', 'localhost');
    define('DB_USER', 'u914642035_2026pmbm');
    define('DB_PASS', 'R!lis2026');
    define('DB_NAME', 'u914642035_pmbm2026');
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Set MySQL timezone to Asia/Jakarta (+07:00) to ensure CURRENT_TIMESTAMP is correct
    $pdo->exec("SET time_zone = '+07:00'");
} catch (PDOException $e) {
    // In production, log this error instead of showing it
    die("Koneksi Database Gagal: " . $e->getMessage() . ". Pastikan database '" . DB_NAME . "' telah dibuat.");
}

// Dynamic Base URL Detection
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$doc_root = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
$app_dir = str_replace('\\', '/', dirname(__DIR__));
$base_path = str_replace($doc_root, '', $app_dir);
$base_path = preg_replace('#/+#', '/', '/' . $base_path . '/');
define('BASE_URL', $protocol . "://" . $host . $base_path);

// Configurable Admin Login Path (Change this to make the login link unique)
define('ADMIN_DIR', '4dmMtsn1');
define('ADMIN_LOGIN_PATH', 'pintu_masuk_admin_pmbm.php');

// Start Session
session_start();

// Security Helpers
function clean_input($data)
{
    $decoded = decode_multiple_entities($data);
    return htmlspecialchars(stripslashes(trim($decoded)));
}

// Helper to decode recursively double/triple encoded HTML entities
if (!function_exists('decode_multiple_entities')) {
    function decode_multiple_entities($str) {
        if (!$str) return '';
        $decoded = htmlspecialchars_decode($str, ENT_QUOTES);
        while ($decoded !== $str) {
            $str = $decoded;
            $decoded = htmlspecialchars_decode($str, ENT_QUOTES);
        }
        return $decoded;
    }
}

function generate_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token)
{
    if (isset($_SESSION['csrf_token']) && $token !== null && hash_equals($_SESSION['csrf_token'], (string) $token)) {
        return true;
    }
    return false;
}

// Rate Limiting (Simple)
function check_rate_limit()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    $file = __DIR__ . '/rate_limit.json';
    $current_time = time();
    $limit = 20; // requests
    $time_window = 60; // seconds

    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

    // Clean old entries
    foreach ($data as $key => $val) {
        if ($val['time'] < $current_time - $time_window) {
            unset($data[$key]);
        }
    }

    if (isset($data[$ip])) {
        if ($data[$ip]['count'] > $limit) {
            die("Terlalu banyak permintaan. Silakan coba lagi nanti.");
        }
        $data[$ip]['count']++;
    } else {
        $data[$ip] = ['time' => $current_time, 'count' => 1];
    }

    file_put_contents($file, json_encode($data));
}

// Get Setting Helper
function get_setting($key, $default = '')
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : $default;
    } catch (Exception $e) {
        return $default;
    }
}

// Access Control Helper
function has_access($menu_key)
{
    global $pdo;
    if (session_status() === PHP_SESSION_NONE)
        session_start();
    $role = $_SESSION['role'] ?? null;

    if (!$role)
        return false;
    if ($role === 'admin')
        return true; // Admin always has full access

    // Check specific role_access
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM role_access WHERE role = ? AND menu_key = ?");
        $stmt->execute([$role, $menu_key]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Log System Activity
 */
function log_activity($action, $details = '')
{
    global $pdo;
    if (session_status() === PHP_SESSION_NONE)
        session_start();
    $user_id = $_SESSION['user_id'] ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    try {
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $details, $ip]);
    } catch (Exception $e) {
        // Silently fail
    }
}

/**
 * Automatic Status Synchronization
 * Handles transitions between PPDB stages based on scheduled dates
 */
function sync_ppdb_status()
{
    global $pdo;
    $current_status = get_setting('ppdb_status', 'belum');
    $active_scheme = get_setting('active_scheme', '1');
    $now = time();

    // Stage 1: BELUM -> BUKA (Check if it's time to open)
    if ($current_status == 'belum') {
        $scheme_start = '';
        if ($active_scheme == '1') {
            $scheme_start = get_setting('scheme_1_start') . ' ' . get_setting('scheme_daily_start');
        } elseif ($active_scheme == '2') {
            $start_time = get_setting('scheme_2_start_time', '00:01');
            $scheme_start = get_setting('scheme_2_start') . ' ' . $start_time;
        } else {
            $start_time = get_setting('scheme_period_start_time', '00:01');
            $scheme_start = get_setting('scheme_period_start') . ' ' . $start_time;
        }

        if (!empty(trim($scheme_start)) && $now >= strtotime($scheme_start)) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('ppdb_status', 'buka') ON DUPLICATE KEY UPDATE setting_value = 'buka'");
            $stmt->execute();
            $current_status = 'buka'; // Move to next check in same request
        }
    }

    // Stage 2: BUKA -> VERIFIKASI (Check if registration period has ended)
    if ($current_status == 'buka') {
        $scheme_end = '';
        if ($active_scheme == '1') {
            $scheme_end = get_setting('scheme_1_end') . ' ' . get_setting('scheme_daily_end');
        } elseif ($active_scheme == '2') {
            $end_time = get_setting('scheme_2_end_time', '23:59');
            $scheme_end = get_setting('scheme_2_end') . ' ' . $end_time;
        } else {
            $end_time = get_setting('scheme_period_end_time', '23:59');
            $scheme_end = get_setting('scheme_period_end') . ' ' . $end_time;
        }

        if (!empty(trim($scheme_end)) && $now >= strtotime($scheme_end)) {
            // Switch to Verification Stage
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('ppdb_status', 'verifikasi') ON DUPLICATE KEY UPDATE setting_value = 'verifikasi'");
            $stmt->execute();

            // Sync administrative stage
            $stmt_sync = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('tahap_administrasi', 'verifikasi') ON DUPLICATE KEY UPDATE setting_value = 'verifikasi'");
            $stmt_sync->execute();

            $current_status = 'verifikasi'; // Move to next check
        }
    }

    // Stage 3: VERIFIKASI -> PENGUMUMAN_ADM (Check if verification period has ended)
    if ($current_status == 'verifikasi') {
        $verif_end = get_setting('stage_verifikasi_end', '');

        if (!empty(trim($verif_end)) && $now >= strtotime($verif_end)) {
            // Switch to Administrative Announcement Stage
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('ppdb_status', 'pengumuman_adm') ON DUPLICATE KEY UPDATE setting_value = 'pengumuman_adm'");
            $stmt->execute();

            // Sync administrative stage
            $stmt_sync = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('tahap_administrasi', 'pengumuman') ON DUPLICATE KEY UPDATE setting_value = 'pengumuman'");
            $stmt_sync->execute();

            // AUTO-VERIFY: Automatically verify all pending students (except rejected)
            try {
                $stmt_verify = $pdo->prepare("UPDATE pendaftar SET status = 'Terverifikasi' WHERE status = 'Pending'");
                $stmt_verify->execute();
                $verified_count = $stmt_verify->rowCount();

                if ($verified_count > 0) {
                    log_activity("Auto-Verifikasi Massal", "Sistem otomatis memverifikasi $verified_count murid saat transisi ke Hasil Verifikasi Berkas");
                }
            } catch (Exception $e) {
                // Log error but don't stop the status transition
                log_activity("Error Auto-Verifikasi", "Gagal auto-verifikasi saat transisi: " . $e->getMessage());
            }

            $current_status = 'pengumuman_adm'; // Move to next check
        }
    }

    // Stage 4: PENGUMUMAN_ADM -> CBT (Check if admin announcement period has ended)
    if ($current_status == 'pengumuman_adm') {
        $pengumuman_adm_end = get_setting('stage_pengumuman_adm_end', '');

        if (!empty(trim($pengumuman_adm_end)) && $now >= strtotime($pengumuman_adm_end)) {
            // Switch to CBT (Academic Test) Stage
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('ppdb_status', 'cbt') ON DUPLICATE KEY UPDATE setting_value = 'cbt'");
            $stmt->execute();
            $current_status = 'cbt';
        }
    }

    // Stage 5: CBT -> FINALISASI (Check if CBT period has ended)
    if ($current_status == 'cbt') {
        $cbt_end = get_setting('stage_cbt_end', '');

        if (!empty(trim($cbt_end)) && $now >= strtotime($cbt_end)) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('ppdb_status', 'finalisasi') ON DUPLICATE KEY UPDATE setting_value = 'finalisasi'");
            $stmt->execute();
            $current_status = 'finalisasi';
        }
    }

    // Stage 6: FINALISASI -> PENGUMUMAN & Auto-Open Announcement (Check if Pengumuman starts)
    if ($current_status != 'pengumuman') {
        $pengumuman_start = get_setting('stage_pengumuman_start', '');

        if (!empty(trim($pengumuman_start)) && $now >= strtotime($pengumuman_start)) {
            // Auto switch DB to pengumuman
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('ppdb_status', 'pengumuman') ON DUPLICATE KEY UPDATE setting_value = 'pengumuman'");
            $stmt->execute();

            // Auto reveal the announcement to students
            $stmt2 = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('announcement_status', 'open') ON DUPLICATE KEY UPDATE setting_value = 'open'");
            $stmt2->execute();

            $current_status = 'pengumuman';
        }
    }
}

// Perform Auto Sync on Config load
sync_ppdb_status();

// ============================================
// GLOBAL MAINTENANCE CHECK (SET OFF)
// ============================================
if (get_setting('maintenance_mode', 'off') === 'on') {
    $current_path = $_SERVER['PHP_SELF'];
    $is_admin_path = strpos($current_path, '/' . ADMIN_DIR . '/') !== false;
    $is_admin_login = basename($current_path) === ADMIN_LOGIN_PATH;
    
    // Only block if NOT in admin directory and NOT the admin login page
    if (!$is_admin_path && !$is_admin_login) {
        $maintenance_message = get_setting('maintenance_message', 'Saat ini belum ada info pendaftaran.');
        $maintenance_view_path = dirname(__DIR__) . '/maintenance_view.php';
        
        if (file_exists($maintenance_view_path)) {
            include $maintenance_view_path;
            exit;
        } else {
            // Fallback if view file is missing
            die("<div style='text-align:center;margin-top:100px;font-family:sans-serif;'>
                    <h1>Pemberitahuan</h1>
                    <p>$maintenance_message</p>
                 </div>");
        }
    }
}
?>