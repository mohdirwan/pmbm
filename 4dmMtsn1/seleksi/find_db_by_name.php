<?php
define('DB_NAME', 'ppdb');
$dbs = [
    'akm_cbt', 'autobiz_db', 'backup_cbt', 'db_apotek', 'db_dp', 'db_gate_anpr', 'db_ontracklance', 'db_umkm', 
    'digimonetize', 'elvitati_sman13', 'erp_db', 'graduation_system', 'ibms_db', 'ivopxyz_data99', 'jadiinduit', 
    'klinik_db', 'lulus_db', 'nurfa_cargo', 'nurfa_cargo_v2', 'op99', 'optikal99', 'portal_berita', 'ppdb', 
    'ppdb_mts1', 'ppdb_mtsn1', 'running', 'sedekah_hub', 'selfie_absen', 'sistem_akm_absensi', 'smanpeka_akm', 
    'smanpeka_akm_pelatihan', 'smanpeka_akm_simulasi', 'smanpeka_invoice', 'smanpkuo_official_web', 'smart_ar_menu', 
    'softdev_db', 'synara_ams', 'test', 'tourism_gateway', 'u914642035_pmbm2026', 'u987233607_cbt', 'umkm_frozen', 
    'wa_api_sender', 'website_sma5pku', 'websma5', 'websman5pku'
];

$names = ['35235235235235', 'YUSUF ANWAR', 'DOREMI', 'SUSI SUSANTI'];

foreach ($dbs as $db) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=$db", "root", "");
        foreach ($names as $name) {
            $stmt = $pdo->prepare("SELECT id, nama_lengkap, status, jalur_id, status_tahfidz, nilai_ujian FROM pendaftar WHERE nama_lengkap LIKE ?");
            $stmt->execute(["%$name%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($results)) {
                echo "FOUND $name in database: $db\n";
                print_r($results);
            }
        }
    } catch (Exception $e) {
        // Skip
    }
}
?>
