<?php
/**
 * Security Configuration & Helper Functions
 * For PPDB System Production Environment
 * 
 * @author PPDB Security Team
 * @date 2026-02-07
 */

// =====================================================
// 1. SECURE SESSION CONFIGURATION
// =====================================================

function init_secure_session()
{
    // Check if session is NOT already active before setting ini
    if (session_status() === PHP_SESSION_NONE) {
        // Prevent session hijacking
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_httponly', 1);

        // Only set secure cookie if on HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            ini_set('session.cookie_secure', 1);
        } else {
            ini_set('session.cookie_secure', 0);
        }

        ini_set('session.cookie_samesite', 'Lax'); // Lax is better for local dev than Strict

        // Session timeout (30 minutes)
        ini_set('session.gc_maxlifetime', 1800);
        ini_set('session.cookie_lifetime', 1800);

        // Strong session ID
        ini_set('session.sid_length', 48);
        ini_set('session.sid_bits_per_character', 6);

        session_start();
    }

    // Session regeneration to prevent fixation
    if (session_status() === PHP_SESSION_ACTIVE) {
        // Regenerate session ID periodically
        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = time();
        } else if (time() - $_SESSION['CREATED'] > 600) {
            // Regenerate every 10 minutes
            session_regenerate_id(true);
            $_SESSION['CREATED'] = time();
        }
    }
}

// =====================================================
// 2. ENHANCED INPUT VALIDATION
// =====================================================

/**
 * Validate and sanitize input with type checking
 */
function validate_input($input, $type = 'string', $options = [])
{
    $input = trim($input);

    switch ($type) {
        case 'email':
            $input = filter_var($input, FILTER_SANITIZE_EMAIL);
            if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            break;

        case 'phone':
            // Remove all non-numeric characters
            $input = preg_replace('/[^0-9]/', '', $input);
            // Indonesian phone format (10-13 digits)
            if (strlen($input) < 10 || strlen($input) > 13) {
                return false;
            }
            break;

        case 'nisn':
            // NISN must be exactly 10 digits
            $input = preg_replace('/[^0-9]/', '', $input);
            if (strlen($input) !== 10) {
                return false;
            }
            break;

        case 'nik':
            // NIK must be exactly 16 digits
            $input = preg_replace('/[^0-9]/', '', $input);
            if (strlen($input) !== 16) {
                return false;
            }
            break;

        case 'number':
            if (!is_numeric($input)) {
                return false;
            }
            $input = floatval($input);
            break;

        case 'integer':
            if (!filter_var($input, FILTER_VALIDATE_INT)) {
                return false;
            }
            $input = intval($input);
            break;

        case 'date':
            // Validate date format (Y-m-d)
            $d = DateTime::createFromFormat('Y-m-d', $input);
            if (!$d || $d->format('Y-m-d') !== $input) {
                return false;
            }
            break;

        case 'alpha':
            // Only letters and spaces
            if (!preg_match('/^[a-zA-Z\s]+$/', $input)) {
                return false;
            }
            break;

        case 'alphanumeric':
            // Letters, numbers, and spaces
            if (!preg_match('/^[a-zA-Z0-9\s]+$/', $input)) {
                return false;
            }
            break;

        case 'string':
        default:
            // Remove HTML tags and encode special chars
            $input = strip_tags($input);
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            break;
    }

    // Check length if specified
    if (isset($options['min_length']) && strlen($input) < $options['min_length']) {
        return false;
    }
    if (isset($options['max_length']) && strlen($input) > $options['max_length']) {
        return false;
    }

    return $input;
}

// =====================================================
// 3. ENHANCED FILE UPLOAD VALIDATION
// =====================================================

/**
 * Validate uploaded file with comprehensive checks
 */
function validate_uploaded_file($file, $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'], $max_size = 2097152)
{
    $errors = [];

    // Check if file was uploaded
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'No file uploaded';
        return ['valid' => false, 'errors' => $errors];
    }

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload error code: ' . $file['error'];
        return ['valid' => false, 'errors' => $errors];
    }

    // Check file size
    if ($file['size'] > $max_size) {
        $errors[] = 'File size exceeds maximum allowed (' . ($max_size / 1048576) . 'MB)';
        return ['valid' => false, 'errors' => $errors];
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        $errors[] = 'Invalid file type. Allowed: ' . implode(', ', $allowed_types);
        return ['valid' => false, 'errors' => $errors];
    }

    // Validate file extension (map from MIME types)
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Map MIME types to allowed extensions
    $mime_to_ext = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/jpg' => ['jpg', 'jpeg'],
        'image/pjpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/webp' => ['webp'],
        'application/pdf' => ['pdf'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'application/vnd.ms-excel' => ['xls']
    ];

    // Build allowed extensions from allowed types
    $allowed_extensions = [];
    foreach ($allowed_types as $type) {
        if (isset($mime_to_ext[$type])) {
            $allowed_extensions = array_merge($allowed_extensions, $mime_to_ext[$type]);
        }
    }

    if (!in_array($extension, $allowed_extensions)) {
        $errors[] = 'Invalid file extension. Allowed: ' . implode(', ', $allowed_extensions);
        return ['valid' => false, 'errors' => $errors];
    }

    // Additional security checks for images
    if (in_array($mime_type, ['image/jpeg', 'image/png', 'image/jpg', 'image/pjpeg', 'image/webp'])) {
        $image_info = getimagesize($file['tmp_name']);
        if ($image_info === false) {
            $errors[] = 'File is not a valid image';
            return ['valid' => false, 'errors' => $errors];
        }

        // Check image dimensions (max 7000x7000) - modern phones often exceed 4000px
        if ($image_info[0] > 7000 || $image_info[1] > 7000) {
            $errors[] = 'Image dimensions too large (max 7000x7000px)';
            return ['valid' => false, 'errors' => $errors];
        }
    }

    // Generate safe filename
    $safe_filename = bin2hex(random_bytes(16)) . '_' . time() . '.' . $extension;

    return [
        'valid' => true,
        'mime_type' => $mime_type,
        'extension' => $extension,
        'safe_filename' => $safe_filename,
        'original_name' => basename($file['name'])
    ];
}

// =====================================================
// 4. ENHANCED RATE LIMITING
// =====================================================

/**
 * Enhanced rate limiting with Redis/File storage
 */
function check_rate_limit_enhanced($max_attempts = 5, $time_window = 300)
{
    $ip = get_client_ip();
    $cache_file = sys_get_temp_dir() . '/rate_limit_' . md5($ip) . '.json';

    $now = time();
    $attempts = [];

    // Load existing attempts
    if (file_exists($cache_file)) {
        $data = json_decode(file_get_contents($cache_file), true);
        if ($data && isset($data['attempts'])) {
            $attempts = $data['attempts'];
        }
    }

    // Remove old attempts outside time window
    $attempts = array_filter($attempts, function ($timestamp) use ($now, $time_window) {
        return ($now - $timestamp) < $time_window;
    });

    // Check if limit exceeded
    if (count($attempts) >= $max_attempts) {
        $wait_time = $time_window - ($now - min($attempts));
        header('HTTP/1.1 429 Too Many Requests');
        header('Retry-After: ' . $wait_time);
        die(json_encode([
            'error' => 'Too many requests',
            'message' => 'Terlalu banyak percobaan. Silakan coba lagi dalam ' . ceil($wait_time / 60) . ' menit.',
            'retry_after' => $wait_time
        ]));
    }

    // Add current attempt
    $attempts[] = $now;

    // Save attempts
    file_put_contents($cache_file, json_encode(['attempts' => $attempts]));

    return true;
}

/**
 * Get real client IP address
 */
function get_client_ip()
{
    $ip = '';

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // In case of proxy
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    // Validate IP
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = '0.0.0.0';
    }

    return $ip;
}

// =====================================================
// 5. SECURITY HEADERS
// =====================================================

/**
 * Set security headers
 */
function set_security_headers()
{
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');

    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');

    // XSS Protection
    header('X-XSS-Protection: 1; mode=block');

    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Content Security Policy (Simplified for hosting compatibility)
    header("Content-Security-Policy: default-src 'self' *; script-src 'self' 'unsafe-inline' 'unsafe-eval' *; style-src 'self' 'unsafe-inline' *; font-src 'self' data: *; img-src 'self' data: *; connect-src 'self' *;");

    // HTTPS Strict Transport Security (if using HTTPS)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }

    // Permissions Policy
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// =====================================================
// 6. SECURE ERROR HANDLER
// =====================================================

/**
 * Custom error handler - don't expose sensitive info
 */
function secure_error_handler($errno, $errstr, $errfile, $errline)
{
    // Log detailed error for admin
    $log_message = sprintf(
        "[%s] Error %d: %s in %s on line %d\n",
        date('Y-m-d H:i:s'),
        $errno,
        $errstr,
        $errfile,
        $errline
    );

    error_log($log_message, 3, __DIR__ . '/logs/error.log');

    // Show generic error to user (don't expose details)
    if (!(error_reporting() & $errno)) {
        return false;
    }

    // In production, show generic message
    if (defined('PRODUCTION') && PRODUCTION === true) {
        echo "<h2>Terjadi Kesalahan</h2>";
        echo "<p>Maaf, terjadi kesalahan saat memproses permintaan Anda. Silakan coba lagi nanti.</p>";
        exit;
    }

    return true;
}

// =====================================================
// 7. SQL INJECTION PREVENTION HELPER
// =====================================================

/**
 * Additional SQL injection prevention check
 */
function check_sql_injection($input)
{
    $dangerous_patterns = [
        '/(\bUNION\b.*\bSELECT\b)/i',
        '/(\bOR\b.*=.*)/i',
        '/(\bAND\b.*=.*)/i',
        '/(--)/i',
        '/(;)/i',
        '/(\bDROP\b)/i',
        '/(\bDELETE\b)/i',
        '/(\bTRUNCATE\b)/i',
        '/(\bEXEC\b)/i',
        '/(<script)/i',
        '/(<iframe)/i',
    ];

    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            log_security_event('SQL Injection Attempt', $input);
            return false;
        }
    }

    return true;
}

// =====================================================
// 8. SECURITY EVENT LOGGING
// =====================================================

/**
 * Log security events
 */
function log_security_event($event_type, $details = '')
{
    $log_dir = __DIR__ . '/logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $log_file = $log_dir . '/security_' . date('Y-m-d') . '.log';

    $log_entry = sprintf(
        "[%s] [%s] IP: %s | Event: %s | Details: %s\n",
        date('Y-m-d H:i:s'),
        $event_type,
        get_client_ip(),
        $event_type,
        $details
    );

    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// =====================================================
// 9. FORCE HTTPS
// =====================================================

/**
 * Redirect HTTP to HTTPS
 */
function force_https()
{
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        if (php_sapi_name() !== 'cli') {
            $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $redirect_url);
            exit;
        }
    }
}

// =====================================================
// 10. INITIALIZE SECURITY (Call this in every page)
// =====================================================

function initialize_security($force_https_enabled = false)
{
    // Set error handler
    set_error_handler('secure_error_handler');

    // Set security headers
    set_security_headers();

    // Initialize secure session
    init_secure_session();

    // Force HTTPS in production
    if ($force_https_enabled) {
        force_https();
    }

    // Enhanced rate limiting
    check_rate_limit_enhanced();
}

?>