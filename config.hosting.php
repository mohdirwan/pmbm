<?php
// ============================================
// KONFIGURASI UNTUK HOSTING
// ============================================
// File ini khusus untuk hosting!
// Ganti kredensial database sesuai hosting Anda
// ============================================

// Set Timezone to Asia/Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// ============================================
// DATABASE CONFIGURATION - UBAH INI!
// ============================================
// Dapatkan kredensial dari cPanel → MySQL Databases

define('DB_HOST', 'localhost');           // Biasanya 'localhost', tapi bisa berbeda
define('DB_USER', 'GANTI_USERNAME_DB');   // Username database dari hosting
define('DB_PASS', 'GANTI_PASSWORD_DB');   // Password database dari hosting  
define('DB_NAME', 'GANTI_NAMA_DB');       // Nama database di hosting

// CONTOH (JANGAN LANGSUNG COPY):
// define('DB_HOST', 'localhost');
// define('DB_USER', 'smanpeka_pmbmuser');
// define('DB_PASS', 'P@ssw0rd123!');
// define('DB_NAME', 'smanpeka_ppdb_db');

// ============================================
// ERROR REPORTING - PRODUCTION MODE
// ============================================
// DI PRODUCTION: Matikan display errors
// SAAT DEBUG: Aktifkan (1) untuk melihat error

ini_set('display_errors', 0);  // Ubah ke 1 jika perlu debug
ini_set('log_errors', 1);      // Log error ke file
error_reporting(0);            // Ubah ke E_ALL jika perlu debug

// ============================================
// DATABASE CONNECTION
// ============================================
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In production, log this error instead of showing it
    error_log("Database Connection Failed: " . $e->getMessage());
    die("Koneksi Database Gagal. Silakan hubungi administrator.");
}

// ============================================
// BASE URL - OTOMATIS DETECT
// ============================================
// Jika auto-detect gagal, uncomment baris manual di bawah

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_dir = str_replace(basename($script_name), "", $script_name);
$dir_parts = explode('/', trim($base_dir, '/'));
$root_folder = isset($dir_parts[0]) && $dir_parts[0] !== '' ? '/' . $dir_parts[0] . '/' : '/';

// Auto-detect (default)
if (count($dir_parts) > 1 && $dir_parts[0] === 'pmbm') {
    define('BASE_URL', $protocol . "://" . $host . "/pmbm/");
} else {
    define('BASE_URL', $protocol . "://" . $host . $root_folder);
}

// Manual override (uncomment jika auto-detect gagal)
// define('BASE_URL', 'https://pmbm.mtsn1pekanbaru.sch.id/');

// Configurable Admin Login Path (Change this to make the login link unique)
if (!defined('ADMIN_LOGIN_PATH')) {
    define('ADMIN_LOGIN_PATH', 'pintu_masuk_admin_pmbm.php');
}

// ============================================
// SESSION START
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// SECURITY HELPERS
// ============================================

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

// ============================================
// RATE LIMITING
// ============================================

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

// ============================================
// GET SETTING HELPER
// ============================================

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

// ============================================
// ACCESS CONTROL HELPER
// ============================================

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

// ============================================
// LOG SYSTEM ACTIVITY
// ============================================

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
        error_log("Log Activity Failed: " . $e->getMessage());
    }
}

// ============================================
// AUTOMATIC STATUS SYNCHRONIZATION
// ============================================

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
        }
    }
}

// Perform Auto Sync on Config load
sync_ppdb_status();
?>