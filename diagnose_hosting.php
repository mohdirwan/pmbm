<?php
/**
 * PMBM Hosting Diagnostic Script
 * Upload this file to your hosting root and access it via browser.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PMBM Hosting Diagnostic</h1>";

// 1. PHP Version
echo "<h2>1. Environment</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Current File Path: " . __FILE__ . "<br>";

// 2. Database Config Test
echo "<h2>2. Database Configuration</h2>";
$config_path = __DIR__ . '/includes/config.php';
if (file_exists($config_path)) {
    echo "✅ config.php found at: $config_path<br>";
    
    // Temporarily capture defined constants
    $before_constants = get_defined_constants(true)['user'] ?? [];
    include $config_path;
    $after_constants = get_defined_constants(true)['user'] ?? [];
    
    $new_constants = array_diff_assoc($after_constants, $before_constants);
    
    echo "Constants defined in config.php:<br>";
    echo "<ul>";
    foreach (['DB_HOST', 'DB_USER', 'DB_NAME', 'BASE_URL'] as $const) {
        if (defined($const)) {
            $val = constant($const);
            echo "<li><strong>$const:</strong> " . htmlspecialchars($val) . "</li>";
        } else {
            echo "<li>❌ <strong>$const:</strong> NOT DEFINED</li>";
        }
    }
    echo "</ul>";
    
    // 3. Database Connection Test
    echo "<h2>3. Connection Test</h2>";
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
        $test_pdo = new PDO($dsn, DB_USER, DB_PASS);
        echo "✅ <strong>SUCCESS!</strong> Database connected successfully.<br>";
        
        // 4. Table Check
        echo "<h2>4. Table Check</h2>";
        $tables = ['pendaftar', 'settings', 'ujian_ruangan', 'ujian_sesi', 'users', 'role_access'];
        echo "<ul>";
        foreach ($tables as $table) {
            try {
                $stmt = $test_pdo->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                echo "<li>✅ Table <strong>$table</strong> exists ($count rows).</li>";
            } catch (Exception $e) {
                echo "<li>❌ Table <strong>$table</strong> MISSING or error: " . $e->getMessage() . "</li>";
            }
        }
        echo "</ul>";
        
    } catch (PDOException $e) {
        echo "❌ <strong>FAILED!</strong> Connection error: " . $e->getMessage() . "<br>";
        echo "Possible reasons: incorrect username/password, database not created, or host not allowed.<br>";
    }
} else {
    echo "❌ <strong>FAILED!</strong> config.php NOT FOUND at: $config_path<br>";
}

// 5. File Permissions
echo "<h2>5. Permissions Check</h2>";
$paths = [
    'includes',
    'uploads',
    '4dmMtsn1/ujian/test_akademik.php'
];
echo "<ul>";
foreach ($paths as $p) {
    $full_p = __DIR__ . '/' . $p;
    if (file_exists($full_p)) {
        $perms = substr(sprintf('%o', fileperms($full_p)), -4);
        echo "<li>$p: <strong>$perms</strong></li>";
    } else {
        echo "<li>❌ $p: NOT FOUND</li>";
    }
}
echo "</ul>";

echo "<br><hr><p><em>Remove this file after debugging for security.</em></p>";
