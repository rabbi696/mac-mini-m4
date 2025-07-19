<?php
header('Content-Type: text/plain');

$api_key = '2108748469687b775b2b6ef1288790031302163742687b775b2b6f3757014442';
$domain = 'https://mac.golamrabbi.dev';

echo "=== Testing Solveez API Endpoints and Key Validation ===\n\n";

// Test different possible endpoints
$endpoints = [
    'Create Charge (provided)' => 'https://payment.solveez.com/api/create-charge',
    'Create Payment' => 'https://payment.solveez.com/api/create-payment',
    'Charges' => 'https://payment.solveez.com/api/charges',
    'Payment' => 'https://payment.solveez.com/api/payment',
    'V1 Create Charge' => 'https://payment.solveez.com/api/v1/create-charge',
    'V2 Create Charge' => 'https://payment.solveez.com/api/v2/create-charge',
    'Base API' => 'https://payment.solveez.com/api',
    'Alternative domain' => 'https://api.solveez.com/create-charge',
    'Alternative V1' => 'https://api.solveez.com/v1/create-charge'
];

$payload = [
    'amount' => '100',
    'currency' => 'BDT',
    'success_url' => $domain . '/donation-success.html',
    'cancel_url' => $domain . '/donation-cancel.html'
];

foreach ($endpoints as $name => $url) {
    echo "--- Testing $name ---\n";
    echo "URL: $url\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    
    echo "HTTP Code: $http_code\n";
    echo "Effective URL: $effective_url\n";
    if ($curl_error) {
        echo "cURL Error: $curl_error\n";
    }
    echo "Response: " . substr($response, 0, 200) . (strlen($response) > 200 ? "..." : "") . "\n";
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

// Test GET request to base API to see if it's accessible
echo "--- Testing Base API with GET request ---\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://payment.solveez.com/api',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
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

// Test if the domain is accessible at all
echo "\n--- Testing Domain Accessibility ---\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://payment.solveez.com',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_NOBODY => true, // HEAD request
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "Domain HTTP Code: $http_code\n";
if ($curl_error) {
    echo "Domain cURL Error: $curl_error\n";
}

echo "\n=== Endpoint Test Complete ===\n";
echo "\nRECOMMENDATION:\n";
echo "If all endpoints return 'Invalid API key', this suggests either:\n";
echo "1. The API key is for a different environment (sandbox vs production)\n";
echo "2. The API key has expired or been revoked\n";
echo "3. Additional authentication parameters are required\n";
echo "4. The domain/endpoint has changed\n\n";
echo "Please verify the API key with Solveez support or check for updated documentation.\n";
?>
