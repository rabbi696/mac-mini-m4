<?php
header('Content-Type: text/plain');

// Solveez API configuration
$api_url = 'https://payment.solveez.com/api/create-charge';
$api_key = '2108748469687b775b2b6ef1288790031302163742687b775b2b6f3757014442';
$domain = 'https://mac.golamrabbi.dev';

echo "=== Testing Solveez API Authentication Methods ===\n\n";

// Standard payload for testing
$payload = [
    'amount' => '100',
    'currency' => 'BDT',
    'success_url' => $domain . '/donation-success.html',
    'cancel_url' => $domain . '/donation-cancel.html'
];

// Test different authentication methods
$auth_methods = [
    'Bearer Token' => ['Authorization: Bearer ' . $api_key],
    'API Key Header' => ['Authorization: ' . $api_key],
    'X-API-Key Header' => ['X-API-Key: ' . $api_key],
    'API-Key Header' => ['API-Key: ' . $api_key],
    'X-Auth-Token' => ['X-Auth-Token: ' . $api_key],
    'Token Header' => ['Token: ' . $api_key],
    'Access-Token' => ['Access-Token: ' . $api_key],
    'Authorization Basic' => ['Authorization: Basic ' . base64_encode($api_key . ':')],
    'In Payload' => [] // API key in payload
];

foreach ($auth_methods as $method_name => $headers) {
    echo "--- Testing $method_name ---\n";
    
    $test_payload = $payload;
    
    // If testing "In Payload" method, add API key to payload
    if ($method_name === 'In Payload') {
        $test_payload['api_key'] = $api_key;
        $test_payload['key'] = $api_key; // Alternative field name
    }
    
    echo "Headers: " . json_encode($headers) . "\n";
    echo "Payload: " . json_encode($test_payload, JSON_PRETTY_PRINT) . "\n";
    
    $default_headers = [
        'Accept: application/json',
        'Content-Type: application/json'
    ];
    
    $all_headers = array_merge($default_headers, $headers);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($test_payload),
        CURLOPT_HTTPHEADER => $all_headers,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: $http_code\n";
    if ($curl_error) {
        echo "cURL Error: $curl_error\n";
    }
    echo "Response: $response\n";
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

// Also test form data instead of JSON
echo "--- Testing Form Data (POST) instead of JSON ---\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $api_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query(array_merge($payload, ['api_key' => $api_key])),
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded'
    ],
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $http_code\n";
if ($curl_error) {
    echo "cURL Error: $curl_error\n";
}
echo "Response: $response\n";

echo "\n=== Authentication Test Complete ===\n";
?>
