<?php
header('Content-Type: text/plain');

// Solveez API configuration
$api_url = 'https://payment.solveez.com/api/create-charge';
$api_key = '2108748469687b775b2b6ef1288790031302163742687b775b2b6f3757014442';
$domain = 'https://mac.golamrabbi.dev';

echo "=== Testing Solveez API Formats ===\n\n";

// Test different payload formats
$test_formats = [
    'Format 1 - Standard' => [
        'amount' => '100',
        'currency' => 'BDT',
        'customer_name' => 'Test User',
        'customer_email' => 'test@example.com',
        'success_url' => $domain . '/donation-success.html',
        'cancel_url' => $domain . '/donation-cancel.html',
        'webhook_url' => $domain . '/api/webhook.php',
        'description' => 'Test donation'
    ],
    'Format 2 - Minimal' => [
        'amount' => '100',
        'currency' => 'BDT',
        'success_url' => $domain . '/donation-success.html',
        'cancel_url' => $domain . '/donation-cancel.html'
    ],
    'Format 3 - PipraPay Style' => [
        'full_name' => 'Test User',
        'email_mobile' => 'test@example.com',
        'amount' => '100',
        'redirect_url' => $domain . '/donation-success.html',
        'return_type' => 'GET',
        'cancel_url' => $domain . '/donation-cancel.html',
        'webhook_url' => $domain . '/api/webhook.php',
        'currency' => 'BDT'
    ],
    'Format 4 - Alternative' => [
        'amount' => 100,  // numeric instead of string
        'currency' => 'BDT',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'return_url' => $domain . '/donation-success.html',
        'cancel_url' => $domain . '/donation-cancel.html',
        'notify_url' => $domain . '/api/webhook.php'
    ]
];

foreach ($test_formats as $format_name => $payload) {
    echo "--- Testing $format_name ---\n";
    echo "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
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
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

// Also test without Bearer prefix
echo "--- Testing without Bearer prefix ---\n";
$payload = [
    'amount' => '100',
    'currency' => 'BDT',
    'success_url' => $domain . '/donation-success.html',
    'cancel_url' => $domain . '/donation-cancel.html'
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $api_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: ' . $api_key  // Without "Bearer"
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

echo "\n=== Test Complete ===\n";
?>
