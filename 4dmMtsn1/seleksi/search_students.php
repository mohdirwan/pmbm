<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ppdb_mts1');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

$names = ['35235235235235', 'YUSUF ANWAR', 'DOREMI', 'SUSI SUSANTI'];
echo "Searching for specific students:\n";
foreach ($names as $name) {
    $stmt = $pdo->prepare("SELECT id, nama_lengkap, status, jalur_id, status_tahfidz, nilai_ujian FROM pendaftar WHERE nama_lengkap LIKE ?");
    $stmt->execute(["%$name%"]);
    $results = $stmt->fetchAll();
    echo "Results for $name:\n";
    print_r($results);
}
?>
