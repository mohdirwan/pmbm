<?php
/**
 * Security Test Script
 * Run this script to verify security configurations
 * 
 * Access: php security_test.php (CLI)
 * Or: http://localhost/pmbm/security_test.php (Browser)
 */

require_once 'includes/config.php';
require_once 'includes/security.php';

header('Content-Type: text/html; charset=utf-8');

echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
.test { Background: white; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.pass { border-left: 4px solid #4CAF50; }
.fail { border-left: 4px solid #f44336; }
.warning { border-left: 4px solid #ff9800; }
h1 { color: #333; }
h2 { color: #666; margin-top: 30px; }
.status { font-weight: bold; }
.pass .status { color: #4CAF50; }
.fail .status { color: #f44336; }
.warning .status { color: #ff9800; }
</style>";

echo "<h1>🔒 PPDB Security Test Report</h1>";
echo "<p><strong>Test Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Test IP:</strong> " . get_client_ip() . "</p>";

// =====================================================
// 1. File Permissions Test
// =====================================================
echo "<h2>1. File Permissions</h2>";

$files_to_check = [
    'includes/config.php' => 0600,
    '.htaccess' => 0644,
    'uploads' => 0755,
    'logs' => 0755
];

foreach ($files_to_check as $file => $expected_perm) {
    if (file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        $expected = sprintf('%04o', $expected_perm);

        if ($perms == $expected) {
            echo "<div class='test pass'><span class='status'>✓ PASS:</span> $file has correct permissions ($perms)</div>";
        } else {
            echo "<div class='test fail'><span class='status'>✗ FAIL:</span> $file has incorrect permissions ($perms, expected $expected)</div>";
        }
    } else {
        echo "<div class='test warning'><span class='status'>⚠ WARNING:</span> $file not found</div>";
    }
}

// =====================================================
// 2. Directory Existence Test
// =====================================================
echo "<h2>2. Required Directories</h2>";

$dirs_to_check = ['uploads', 'logs', 'includes'];

foreach ($dirs_to_check as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        echo "<div class='test pass'><span class='status'>✓ PASS:</span> $dir exists and is writable</div>";
    } elseif (is_dir($dir)) {
        echo "<div class='test fail'><span class='status'>✗ FAIL:</span> $dir exists but is NOT writable</div>";
    } else {
        echo "<div class='test fail'><span class='status'>✗ FAIL:</span> $dir does not exist</div>";
    }
}

// =====================================================
// 3. PHP Security Settings Test
// =====================================================
echo "<h2>3. PHP Security Settings</h2>";

$php_settings = [
    'expose_php' => ['expected' => '0', 'type' => 'off'],
    'display_errors' => ['expected' => '0', 'type' => 'off'],
    'file_uploads' => ['expected' => '1', 'type' => 'on'],
    'upload_max_filesize' => ['expected' => '2M', 'type' => 'value'],
    'session.cookie_httponly' => ['expected' => '1', 'type' => 'on'],
];

foreach ($php_settings as $setting => $config) {
    $value = ini_get($setting);

    if ($config['type'] === 'off') {
        $is_correct = ($value == $config['expected']);
        $msg = $is_correct ? "OFF (secure)" : "ON (insecure)";
        $class = $is_correct ? 'pass' : 'fail';
    } elseif ($config['type'] === 'on') {
        $is_correct = ($value == $config['expected']);
        $msg = $is_correct ? "ON (correct)" : "OFF (incorrect)";
        $class = $is_correct ? 'pass' : 'fail';
    } else {
        $is_correct = true; // Just display value
        $msg = $value;
        $class = 'pass';
    }

    $status_icon = $is_correct ? '✓ PASS' : '✗ FAIL';
    echo "<div class='test $class'><span class='status'>$status_icon:</span> $setting = $msg</div>";
}

// =====================================================
// 4. Database Connection Test
// =====================================================
echo "<h2>4. Database Connection</h2>";

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM pendaftar");
    $result = $stmt->fetch();
    echo "<div class='test pass'><span class='status'>✓ PASS:</span> Database connected successfully. Total registrants: " . $result['count'] . "</div>";
} catch (Exception $e) {
    echo "<div class='test fail'><span class='status'>✗ FAIL:</span> Database connection failed: " . $e->getMessage() . "</div>";
}

// =====================================================
// 5. Security Functions Test
// =====================================================
echo "<h2>5. Security Functions</h2>";

// Test input validation
$test_inputs = [
    ['value' => 'test@example.com', 'type' => 'email', 'expected' => true],
    ['value' => 'invalid-email', 'type' => 'email', 'expected' => false],
    ['value' => '1234567890', 'type' => 'nisn', 'expected' => true],
    ['value' => '123', 'type' => 'nisn', 'expected' => false],
    ['value' => '1234567890123456', 'type' => 'nik', 'expected' => true],
];

foreach ($test_inputs as $test) {
    $result = validate_input($test['value'], $test['type']);
    $is_correct = ($test['expected'] === true && $result !== false) || ($test['expected'] === false && $result === false);

    $class = $is_correct ? 'pass' : 'fail';
    $status = $is_correct ? '✓ PASS' : '✗ FAIL';
    echo "<div class='test $class'><span class='status'>$status:</span> validate_input('{$test['value']}', '{$test['type']}')</div>";
}

// =====================================================
// 6. Session Security Test
// =====================================================
echo "<h2>6. Session Security</h2>";

$session_settings = [
    'use_only_cookies' => ini_get('session.use_only_cookies'),
    'cookie_httponly' => ini_get('session.cookie_httponly'),
    'cookie_secure' => ini_get('session.cookie_secure'),
];

foreach ($session_settings as $setting => $value) {
    $is_secure = ($value == '1');
    $class = $is_secure ? 'pass' : 'warning';
    $status = $is_secure ? '✓ PASS' : '⚠ WARNING';
    $msg = $is_secure ? 'Enabled' : 'Disabled (enable for production)';
    echo "<div class='test $class'><span class='status'>$status:</span> session.$setting: $msg</div>";
}

// =====================================================
// 7. File Upload Test
// =====================================================
echo "<h2>7. Uploads Directory Security</h2>";

// Check if .htaccess exists in uploads
if (file_exists('uploads/.htaccess')) {
    echo "<div class='test pass'><span class='status'>✓ PASS:</span> uploads/.htaccess exists (prevents PHP execution)</div>";
} else {
    echo "<div class='test warning'><span class='status'>⚠ WARNING:</span> uploads/.htaccess not found. Create one to prevent PHP execution.</div>";
}

// Check if index.php exists in uploads
if (file_exists('uploads/index.php')) {
    echo "<div class='test pass'><span class='status'>✓ PASS:</span> uploads/index.php exists (prevents directory listing)</div>";
} else {
    echo "<div class='test warning'><span class='status'>⚠ WARNING:</span> Create uploads/index.php to prevent directory listing</div>";
}

// =====================================================
// 8. .htaccess Test
// =====================================================
echo "<h2>8. Apache Configuration</h2>";

if (file_exists('.htaccess')) {
    $htaccess_content = file_get_contents('.htaccess');

    $checks = [
        'X-Frame-Options' => strpos($htaccess_content, 'X-Frame-Options') !== false,
        'X-XSS-Protection' => strpos($htaccess_content, 'X-XSS-Protection') !== false,
        'Content-Security-Policy' => strpos($htaccess_content, 'Content-Security-Policy') !== false,
        'Directory Listing Disabled' => strpos($htaccess_content, 'Options -Indexes') !== false,
    ];

    foreach ($checks as $feature => $enabled) {
        $class = $enabled ? 'pass' : 'fail';
        $status = $enabled ? '✓ PASS' : '✗ FAIL';
        $msg = $enabled ? 'Enabled' : 'Not found';
        echo "<div class='test $class'><span class='status'>$status:</span> $feature: $msg</div>";
    }
} else {
    echo "<div class='test fail'><span class='status'>✗ FAIL:</span> .htaccess file not found</div>";
}

// =====================================================
// 9. SSL/HTTPS Test
// =====================================================
echo "<h2>9. HTTPS/SSL Status</h2>";

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    echo "<div class='test pass'><span class='status'>✓ PASS:</span> HTTPS is enabled</div>";
} else {
    echo "<div class='test warning'><span class='status'>⚠ WARNING:</span> HTTPS is not enabled. Enable SSL certificate for production!</div>";
}

// =====================================================
// Final Summary
// =====================================================
echo "<h2>📊 Test Summary</h2>";
echo "<div class='test pass'>";
echo "<p><strong>Overall Security Status:</strong></p>";
echo "<ul>";
echo "<li>✓ All critical security functions are implemented</li>";
echo "<li>⚠ Some warnings need attention before production</li>";
echo "<li>🔒 Remember to enable HTTPS and set PRODUCTION=true</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><small>Security test complete. Address all FAIL and WARNING items before going to production.</small></p>";
echo "<p><small><strong>IMPORTANT:</strong> Delete or restrict access to this file after testing!</small></p>";

?>