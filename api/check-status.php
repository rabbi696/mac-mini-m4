<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Check Solveez API status - Updated to working endpoint
$api_url = 'https://payment.webservicebd.com/api';
$create_charge_url = 'https://payment.webservicebd.com/api/create-charge';
$api_key = '199925457168aa0351687ba1834362078106468701568aa0351687bc666452695';

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => [
        'base_api' => checkEndpoint($api_url),
        'create_charge' => checkEndpoint($create_charge_url, $api_key)
    ]
]);

function checkEndpoint($url, $api_key = null) {
    $ch = curl_init();
    
    $headers = ['Accept: application/json'];
    if ($api_key) {
        $headers[] = 'Authorization: Bearer ' . $api_key;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'amount' => '100',
            'currency' => 'BDT',
            'success_url' => 'https://mac.golamrabbi.dev/donation-success.html',
            'cancel_url' => 'https://mac.golamrabbi.dev/donation-cancel.html'
        ]));
    }
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    $status = 'unknown';
    if ($curl_error) {
        $status = 'connection_error';
    } elseif ($http_code === 200) {
        if (strpos($response, 'maintenance') !== false) {
            $status = 'maintenance';
        } elseif (empty($response)) {
            $status = 'maintenance';
        } else {
            $status = 'online';
        }
    } elseif ($http_code === 400) {
        if (strpos($response, 'Invalid API key') !== false) {
            $status = 'maintenance'; // During maintenance, API key errors are common
        } else {
            $status = 'online'; // API is responding with validation errors (normal)
        }
    } else {
        $status = 'error';
    }
    
    return [
        'url' => $url,
        'http_code' => $http_code,
        'status' => $status,
        'response_preview' => substr($response, 0, 100),
        'curl_error' => $curl_error ?: null
    ];
}
?>
