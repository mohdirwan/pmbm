<?php
require_once __DIR__ . '/config.php';

function send_wa_message($phone, $message, $ignore_status = false)
{
    $api_url = get_setting('wa_api_url', 'https://imsoftdev.my.id/wa/api/send-message.php');
    $api_key = get_setting('wa_api_key', 'wa_f573e575c636814260058dfc74f50f546784f78bed2b9fb7');
    $status_wa = get_setting('wa_status', 'nonaktif');

    if (!$ignore_status && $status_wa !== 'aktif') {
        return ['status' => false, 'message' => 'WhatsApp Gateway is disabled'];
    }

    // 1. Bersihkan semua karakter non-angka
    $phone = preg_replace('/[^0-9]/', '', $phone);

    // 2. Jika diawali '0', ubah jadi '62' (0812... -> 62812...)
    if (strpos($phone, '0') === 0) {
        $phone = '62' . substr($phone, 1);
    }
    // 3. Jika diawali '8', tambahkan '62' (812... -> 62812...)
    elseif (strpos($phone, '8') === 0) {
        $phone = '62' . $phone;
    }
    // 4. Jika diawali '620', ubah jadi '62' (620812... -> 62812...)
    if (strpos($phone, '620') === 0) {
        $phone = '62' . substr($phone, 3);
    }

    $payload = [
        'phone' => $phone,
        'message' => $message
    ];

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    // Tambahkan User-Agent, timeout, dan abaikan SSL verification untuk hosting
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return ['status' => true, 'response' => json_decode($response, true)];
    } else {
        return [
            'status' => false,
            'message' => 'HTTP Error ' . $http_code . ($curl_error ? ' - ' . $curl_error : ''),
            'response' => $response
        ];
    }
}

function get_wa_device_info()
{
    $api_url = get_setting('wa_api_url', 'https://imsoftdev.my.id/wa/api/send-message.php');
    $api_key = get_setting('wa_api_key', 'wa_f573e575c636814260058dfc74f50f546784f78bed2b9fb7');

    // Ubah URL dari send-message.php ke device-info.php
    $device_url = str_replace('send-message.php', 'device-info.php', $api_url);

    $ch = curl_init($device_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return json_decode($response, true);
}
