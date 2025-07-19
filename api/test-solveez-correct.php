<?php
header('Content-Type: text/plain');

echo "=== Testing YOUR Solveez API with Correct Authentication ===\n\n";

// Your Solveez API configuration
$api_url = 'https://payment.solveez.com/api/create-charge';
$api_key = '2108748469687b775b2b6ef1288790031302163742687b775b2b6f3757014442';

// Test payload with correct PipraPay format + your Solveez endpoint
$payload = [
    'full_name' => 'Test Donor',
    'email_mobile' => 'test@mac.golamrabbi.dev',
    'amount' => '100',
    'metadata' => [
        'donation_type' => 'website_donation',
        'site' => 'mac_m4_software',
        'test' => 'true'
    ],
    'redirect_url' => 'https://mac.golamrabbi.dev/donation-success.html',
    'return_type' => 'GET',
    'cancel_url' => 'https://mac.golamrabbi.dev/donation-cancel.html',
    'webhook_url' => 'https://mac.golamrabbi.dev/api/webhook.php',
    'currency' => 'BDT'
];

echo "Testing your Solveez endpoints with correct authentication:\n";
echo "URL: $api_url\n";
echo "API Key: " . substr($api_key, 0, 20) . "...\n";
echo "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

// Test with correct PipraPay authentication header
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $api_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'accept: application/json',
        'content-type: application/json',
        'mh-piprapay-api-key: ' . $api_key  // Correct PipraPay authentication
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "=== RESULTS ===\n";
echo "HTTP Code: $http_code\n";
if ($curl_error) {
    echo "cURL Error: $curl_error\n";
}
echo "Raw Response: $response\n\n";

// Analyze the response
if ($response) {
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "Decoded Response:\n";
        echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n\n";
        
        if (isset($decoded['payment_url']) || isset($decoded['checkout_url']) || isset($decoded['url'])) {
            echo "✅ SUCCESS: Payment URL found!\n";
            $payment_url = $decoded['payment_url'] ?? $decoded['checkout_url'] ?? $decoded['url'];
            echo "Payment URL: $payment_url\n";
        } elseif (isset($decoded['status']) && $decoded['status'] === false) {
            echo "❌ ERROR: " . ($decoded['message'] ?? 'Unknown error') . "\n";
        } elseif (strpos($response, 'maintenance') !== false) {
            echo "🔧 MAINTENANCE: Solveez is currently under maintenance\n";
            echo "This is expected based on earlier tests. Try again later.\n";
        }
    } else {
        echo "JSON decode error: " . json_last_error_msg() . "\n";
        
        if (strpos($response, 'maintenance') !== false) {
            echo "🔧 MAINTENANCE: Response contains maintenance message\n";
        }
    }
}

echo "\n=== SUMMARY ===\n";
echo "• Using YOUR Solveez endpoint: https://payment.solveez.com/api/create-charge\n";
echo "• Using YOUR API key: " . substr($api_key, 0, 20) . "...\n";
echo "• Using CORRECT authentication: mh-piprapay-api-key header\n";
echo "• Including REQUIRED metadata parameter\n\n";

if (strpos($response, 'maintenance') !== false || $http_code === 200 && empty(trim($response))) {
    echo "Status: Solveez is under maintenance (as detected earlier)\n";
    echo "Action: Wait for maintenance to complete, then donations should work!\n";
} elseif ($http_code === 400 && strpos($response, 'Invalid API key') !== false) {
    echo "Status: Still getting API key errors - may need different auth method\n";
} elseif (isset($decoded) && isset($decoded['payment_url'])) {
    echo "Status: ✅ SUCCESS! API is working correctly!\n";
} else {
    echo "Status: Need further investigation\n";
}

echo "\n=== Test Complete ===\n";
?>
