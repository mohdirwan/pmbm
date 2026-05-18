<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'u914642035_pmbm2026');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

$nos = ['202605090004', '202605080003', '202605080002', '202605080001'];
echo "Checking specific No Pendaftaran:\n";
foreach ($nos as $no) {
    $stmt = $pdo->prepare("SELECT id, nama_lengkap, status, jalur_id, nilai_ujian FROM pendaftar WHERE no_pendaftaran = ?");
    $stmt->execute([$no]);
    $result = $stmt->fetch();
    if ($result) {
        echo "FOUND $no: {$result['nama_lengkap']}, Status: {$result['status']}, Nilai: {$result['nilai_ujian']}\n";
    } else {
        echo "NOT FOUND $no\n";
    }
}
?>
