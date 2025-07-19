<?php
// Test script using exact PipraPay GitHub example
require_once '../config/db_config.php';
require_once '../config/donation_config.php';
require_once 'PipraPay.php';

echo "<h2>PipraPay GitHub Example Test</h2>";
echo "<hr>";

// Using the exact example from GitHub
$pipra = new PipraPay(
    DonationConfig::$piprapay_api_key, 
    'https://sandbox.piprapay.com', 
    'BDT'
);

$response = $pipra->createCharge([
    'full_name' => 'John Doe',
    'email_mobile' => 'john@example.com',
    'amount' => 50,
    'metadata' => ['invoiceid' => 'INV-0001'],
    'redirect_url' => 'success.php',
    'cancel_url' => 'cancel.php',
    'webhook_url' => 'ipn.php'
]);

echo "<h3>Response:</h3>";
echo "<pre>";
print_r($response);
echo "</pre>";

if (isset($response['status']) && $response['status']) {
    echo "<p style='color: green;'>✅ Success! Payment URL: " . ($response['pp_url'] ?? 'Not provided') . "</p>";
} else {
    echo "<p style='color: red;'>❌ Failed!</p>";
    echo "<p>Error details:</p>";
    echo "<pre>";
    print_r($response);
    echo "</pre>";
}

// Also test with our current API key format
echo "<hr>";
echo "<h3>Current Configuration:</h3>";
echo "API Key: " . substr(DonationConfig::$piprapay_api_key, 0, 10) . "..." . substr(DonationConfig::$piprapay_api_key, -10) . "<br>";
echo "Base URL: " . DonationConfig::$piprapay_base_url . "<br>";
echo "Currency: " . DonationConfig::$currency . "<br>";
?>
