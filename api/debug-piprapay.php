<?php
// Enhanced debug script to test PipraPay integration
require_once '../config/db_config.php';
require_once '../config/donation_config.php';
require_once 'PipraPay.php';

// Function to log debug info
function debugLog($message) {
    echo "<pre>" . date('Y-m-d H:i:s') . " - " . $message . "</pre>\n";
}

debugLog("=== Enhanced PipraPay Debug Test ===");

// Display current configuration
debugLog("Configuration:");
debugLog("API Key: " . substr(DonationConfig::$piprapay_api_key, 0, 10) . "..." . substr(DonationConfig::$piprapay_api_key, -10));
debugLog("Base URL: " . DonationConfig::$piprapay_base_url);
debugLog("Currency: " . DonationConfig::$currency);

// Test URL accessibility first
debugLog("\n=== Testing URL Accessibility ===");
$test_url = DonationConfig::$piprapay_base_url . '/api/create-charge';
debugLog("Testing URL: " . $test_url);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $test_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_NOBODY => true, // HEAD request
    CURLOPT_HEADER => true
]);

$head_response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    debugLog("❌ cURL Error accessing URL: " . $curl_error);
} else {
    debugLog("✅ URL accessible, HTTP Code: " . $http_code);
}

try {
    debugLog("\n=== Testing PipraPay Class ===");
    
    // Initialize PipraPay
    $piprapay = new PipraPay(
        DonationConfig::$piprapay_api_key,
        DonationConfig::$piprapay_base_url,
        DonationConfig::$currency
    );
    
    debugLog("PipraPay instance created successfully");
    
    // Test payment data (minimal required fields)
    $test_data = [
        'full_name' => 'Test User',
        'email_mobile' => 'test@example.com',
        'amount' => 100
    ];
    
    debugLog("Test data (minimal):");
    debugLog(json_encode($test_data, JSON_PRETTY_PRINT));
    
    // Test the API call
    debugLog("\n=== Calling PipraPay API ===");
    $response = $piprapay->createCharge($test_data);
    
    debugLog("Raw API Response Type: " . gettype($response));
    debugLog("Raw API Response:");
    
    if ($response === null) {
        debugLog("❌ Response is NULL - API might be down or unreachable");
    } elseif ($response === false) {
        debugLog("❌ Response is FALSE - cURL error occurred");
    } else {
        debugLog(json_encode($response, JSON_PRETTY_PRINT));
        
        // Check different possible success indicators
        if (isset($response['status']) && $response['status']) {
            debugLog("✅ Payment creation successful (status=true)!");
            debugLog("Payment URL: " . ($response['pp_url'] ?? 'Not provided'));
        } elseif (isset($response['success']) && $response['success']) {
            debugLog("✅ Payment creation successful (success=true)!");
            debugLog("Payment URL: " . ($response['pp_url'] ?? 'Not provided'));
        } else {
            debugLog("❌ Payment creation failed!");
            debugLog("Available response keys: " . implode(', ', array_keys($response)));
            
            if (isset($response['message'])) {
                debugLog("Error message: " . $response['message']);
            }
            if (isset($response['error'])) {
                debugLog("Error details: " . $response['error']);
            }
            if (isset($response['errors'])) {
                debugLog("Validation errors: " . json_encode($response['errors']));
            }
        }
    }
    
} catch (Exception $e) {
    debugLog("❌ Exception caught: " . $e->getMessage());
    debugLog("Exception trace: " . $e->getTraceAsString());
}

// Test with different API key format (in case it needs to be different)
debugLog("\n=== Testing with Raw cURL ===");
$test_payload = json_encode([
    'full_name' => 'Test User',
    'email_mobile' => 'test@example.com',
    'amount' => 100,
    'currency' => 'BDT'
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => DonationConfig::$piprapay_base_url . '/api/create-charge',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $test_payload,
    CURLOPT_HTTPHEADER => [
        'accept: application/json',
        'content-type: application/json',
        'mh-piprapay-api-key: ' . DonationConfig::$piprapay_api_key
    ],
    CURLOPT_TIMEOUT => 30
]);

$raw_response = curl_exec($ch);
$raw_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$raw_error = curl_error($ch);
curl_close($ch);

debugLog("Raw cURL Response:");
debugLog("HTTP Code: " . $raw_http_code);
if ($raw_error) {
    debugLog("cURL Error: " . $raw_error);
} else {
    debugLog("Response Body: " . $raw_response);
    $decoded = json_decode($raw_response, true);
    if ($decoded) {
        debugLog("Decoded Response: " . json_encode($decoded, JSON_PRETTY_PRINT));
    }
}

debugLog("\n=== Debug Test Complete ===");
?>
