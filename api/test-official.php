<?php
header('Content-Type: text/plain');

echo "=== Testing PipraPay with Official Documentation Format ===\n\n";

// Based on official PipraPay documentation
$api_url = 'https://sandbox.piprapay.com/api/create-charge';
$api_key = '2108748469687b775b2b6ef1288790031302163742687b775b2b6f3757014442';

// Test payload exactly as shown in documentation
$payload = [
    'full_name' => 'Demo',
    'email_mobile' => 'demo@gmail.com',
    'amount' => '10',
    'metadata' => [
        'test' => 'donation',
        'site' => 'mac_m4_software'
    ],
    'redirect_url' => 'https://mac.golamrabbi.dev/donation-success.html',
    'return_type' => 'GET',
    'cancel_url' => 'https://mac.golamrabbi.dev/donation-cancel.html',
    'webhook_url' => 'https://mac.golamrabbi.dev/api/webhook.php',
    'currency' => 'BDT'
];

echo "Testing with official documentation format:\n";
echo "URL: $api_url\n";
echo "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

// Test with official header format
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $api_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'accept: application/json',
        'content-type: application/json',
        'mh-piprapay-api-key: ' . $api_key
    ],
    CURLOPT_TIMEOUT => 30,
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
echo "Raw Response: $response\n\n";

// Try to decode the response
if ($response) {
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "Decoded Response:\n";
        echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
        
        if (isset($decoded['payment_url']) || isset($decoded['checkout_url']) || isset($decoded['url'])) {
            echo "\n✅ SUCCESS: Payment URL found!\n";
            $payment_url = $decoded['payment_url'] ?? $decoded['checkout_url'] ?? $decoded['url'];
            echo "Payment URL: $payment_url\n";
        } elseif (isset($decoded['status']) && $decoded['status'] === false) {
            echo "\n❌ ERROR: " . ($decoded['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "JSON decode error: " . json_last_error_msg() . "\n";
    }
}

echo "\n=== Test Complete ===\n";
?>
