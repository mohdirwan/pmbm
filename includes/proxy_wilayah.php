<?php
// proxy_wilayah.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Izinkan localhost akses

if (isset($_GET['path'])) {
    $path = $_GET['path'];
    $url = "https://www.emsifa.com/api-wilayah-indonesia/api/" . $path . ".json";
    
    // Gunakan file_get_contents untuk mengambil data
    $data = @file_get_contents($url);
    
    if ($data === FALSE) {
        http_response_code(404);
        echo json_encode(['error' => 'Data tidak ditemukan']);
    } else {
        echo $data;
    }
} else {
    echo json_encode(['error' => 'Path tidak ditentukan']);
}
?>
