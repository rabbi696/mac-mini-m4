<?php
// API Status Test Script
echo "<h2>PipraPay API Status Test</h2>";

require_once '../config/donation_config.php';
require_once '../utils/maintenance_check.php';

echo "<h3>Current Configuration:</h3>";
echo "<p><strong>Base URL:</strong> " . DonationConfig::$piprapay_base_url . "</p>";
echo "<p><strong>API Key:</strong> " . substr(DonationConfig::$piprapay_api_key, 0, 20) . "..." . "</p>";
echo "<p><strong>Create Charge Endpoint:</strong> " . DonationConfig::$piprapay_create_charge_endpoint . "</p>";
echo "<p><strong>Verify Payment Endpoint:</strong> " . DonationConfig::$piprapay_verify_payment_endpoint . "</p>";

echo "<h3>API Status Check:</h3>";
$api_status = MaintenanceChecker::checkApiStatus();

if ($api_status['status'] === 'maintenance') {
    echo "<div style='color: red; padding: 10px; background: #ffebee; border: 1px solid red;'>";
    echo "<strong>Status:</strong> Under Maintenance<br>";
    echo "<strong>Message:</strong> " . $api_status['message'] . "<br>";
    echo "<strong>Code:</strong> " . $api_status['code'];
    echo "</div>";
} else {
    echo "<div style='color: green; padding: 10px; background: #e8f5e8; border: 1px solid green;'>";
    echo "<strong>Status:</strong> Available<br>";
    echo "<strong>Message:</strong> " . $api_status['message'] . "<br>";
    echo "<strong>Code:</strong> " . $api_status['code'];
    echo "</div>";
}

echo "<h3>Direct API Check:</h3>";
$api_url = DonationConfig::$piprapay_base_url . '/api';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p><strong>URL:</strong> $api_url</p>";
echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($response) . "</pre>";

if ($error) {
    echo "<p><strong>cURL Error:</strong> $error</p>";
}

echo "<h3>Next Steps:</h3>";
if ($api_status['status'] === 'maintenance') {
    echo "<ul>";
    echo "<li>The API is currently under maintenance</li>";
    echo "<li>Donations will show an appropriate error message to users</li>";
    echo "<li>Check back later when maintenance is complete</li>";
    echo "<li>Consider switching to sandbox environment for testing: https://sandbox.piprapay.com</li>";
    echo "</ul>";
} else {
    echo "<ul>";
    echo "<li>API appears to be available</li>";
    echo "<li>You can proceed with testing donations</li>";
    echo "<li>Make sure to check the actual create-charge endpoint</li>";
    echo "</ul>";
}

echo "<p><em>Test completed at: " . date('Y-m-d H:i:s') . "</em></p>";
?>
