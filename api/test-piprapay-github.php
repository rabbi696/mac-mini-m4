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
var_dump($response);
echo "</pre>";

echo "<h3>Response Analysis:</h3>";
echo "Response type: " . gettype($response) . "<br>";
if (is_array($response)) {
    echo "Response keys: " . implode(', ', array_keys($response)) . "<br>";
}

if (isset($response['status']) && $response['status']) {
    echo "<p style='color: green;'>✅ Success! Payment URL: " . ($response['pp_url'] ?? 'Not provided') . "</p>";
} elseif ($response === null) {
    echo "<p style='color: red;'>❌ Response is NULL - API might be unreachable</p>";
} elseif (isset($response['status']) && $response['status'] === false) {
    echo "<p style='color: red;'>❌ API returned status false</p>";
    if (isset($response['error'])) {
        echo "<p>Error: " . $response['error'] . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Failed or unexpected response!</p>";
    echo "<p>Full response details:</p>";
    echo "<pre>";
    var_dump($response);
    echo "</pre>";
}

// Also test with our current API key format
echo "<hr>";
echo "<h3>Current Configuration:</h3>";
echo "API Key: " . substr(DonationConfig::$piprapay_api_key, 0, 10) . "..." . substr(DonationConfig::$piprapay_api_key, -10) . "<br>";
echo "Base URL: " . DonationConfig::$piprapay_base_url . "<br>";
echo "Currency: " . DonationConfig::$currency . "<br>";
?>
