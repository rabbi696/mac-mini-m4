<?php
// Debug script to test PipraPay integration
require_once '../config/db_config.php';
require_once '../config/donation_config.php';
require_once 'PipraPay.php';

// Function to log debug info
function debugLog($message) {
    echo "<pre>" . date('Y-m-d H:i:s') . " - " . $message . "</pre>\n";
}

debugLog("=== PipraPay Debug Test ===");

// Display current configuration
debugLog("Configuration:");
debugLog("API Key: " . substr(DonationConfig::$piprapay_api_key, 0, 10) . "..." . substr(DonationConfig::$piprapay_api_key, -10));
debugLog("Base URL: " . DonationConfig::$piprapay_base_url);
debugLog("Currency: " . DonationConfig::$currency);

try {
    // Initialize PipraPay
    $piprapay = new PipraPay(
        DonationConfig::$piprapay_api_key,
        DonationConfig::$piprapay_base_url,
        DonationConfig::$currency
    );
    
    debugLog("PipraPay instance created successfully");
    
    // Test payment data
    $test_data = [
        'full_name' => 'Test User',
        'email_mobile' => 'test@example.com',
        'amount' => 100,
        'metadata' => [
            'reference_id' => 'test_' . time(),
            'source' => 'debug_test'
        ],
        'redirect_url' => 'https://example.com/success',
        'cancel_url' => 'https://example.com/cancel',
        'webhook_url' => 'https://example.com/webhook'
    ];
    
    debugLog("Test data prepared:");
    debugLog(json_encode($test_data, JSON_PRETTY_PRINT));
    
    // Test the API call
    debugLog("Calling PipraPay API...");
    $response = $piprapay->createCharge($test_data);
    
    debugLog("Raw API Response:");
    debugLog(json_encode($response, JSON_PRETTY_PRINT));
    
    if (isset($response['success']) && $response['success']) {
        debugLog("✅ Payment creation successful!");
        debugLog("Payment URL: " . ($response['pp_url'] ?? 'Not provided'));
    } else {
        debugLog("❌ Payment creation failed!");
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
    
} catch (Exception $e) {
    debugLog("❌ Exception caught: " . $e->getMessage());
}

debugLog("=== Debug Test Complete ===");
?>
