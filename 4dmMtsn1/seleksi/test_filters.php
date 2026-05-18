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

echo "Checking students with scores against seleksi/index.php filters:\n";
$sql = "SELECT id, nama_lengkap, status, jalur_id, status_tahfidz, nilai_ujian 
        FROM pendaftar 
        WHERE nilai_ujian > 0";
$students = $pdo->query($sql)->fetchAll();

foreach ($students as $s) {
    $is_status_ok = in_array($s['status'], ['Terverifikasi', 'Diterima', 'Ditolak']);
    $is_jalur_ok = in_array($s['jalur_id'], [7, 9]) || ($s['jalur_id'] == 11 && $s['status_tahfidz'] == 'Tidak Lulus');
    
    if (!$is_status_ok || !$is_jalur_ok) {
        echo "Student {$s['nama_lengkap']} (ID: {$s['id']}) has score {$s['nilai_ujian']} but WON'T show up:\n";
        if (!$is_status_ok) echo " - Status is '{$s['status']}' (not in Terverifikasi, Diterima, Ditolak)\n";
        if (!$is_jalur_ok) echo " - Jalur ID is {$s['jalur_id']} or Tahfidz status '{$s['status_tahfidz']}' is not 'Tidak Lulus'\n";
    }
}
?>
