<?php
// proxy_wilayah.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Izinkan localhost akses

if (isset($_GET['path'])) {
    $path = $_GET['path'];
    $url = "https://www.emsifa.com/api-wilayah-indonesia/api/" . $path . ".json";
    
    // Gunakan cURL sebagai ganti file_get_contents (karena allow_url_fopen sering di-disable di hosting)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Abaikan error SSL jika ada
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    
    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($data === FALSE || $http_code != 200) {
        http_response_code(404);
        echo json_encode(['error' => 'Data tidak ditemukan atau koneksi gagal']);
    } else {
        echo $data;
    }
} else {
    echo json_encode(['error' => 'Path tidak ditentukan']);
}
?>
