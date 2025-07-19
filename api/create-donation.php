<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include configurations
require_once '../config/db_config.php';
require_once '../config/donation_config.php';

// Function to log errors
function logError($message) {
    $log_message = date('Y-m-d H:i:s') . " - " . $message . "\n";
    file_put_contents('../logs/donation_errors.log', $log_message, FILE_APPEND | LOCK_EX);
}

// Function to make HTTP requests
function makeHttpRequest($url, $data, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
        'Content-Type: application/json',
        'Authorization: Bearer ' . DonationConfig::$piprapay_api_key
    ], $headers));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception('cURL Error: ' . $error);
    }
    
    return ['response' => $response, 'http_code' => $httpCode];
}

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    // Validate and sanitize input
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $donor_name = filter_input(INPUT_POST, 'donor_name', FILTER_SANITIZE_STRING) ?: 'Anonymous';
    $donor_email = filter_input(INPUT_POST, 'donor_email', FILTER_VALIDATE_EMAIL);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING) ?: '';
    
    // Validate amount
    if (!$amount || $amount < 1) {
        throw new Exception('Invalid donation amount');
    }
    
    // For BDT, we don't need to convert to cents, use the amount as-is
    $amount_final = intval($amount);
    
    // Generate unique reference ID
    $reference_id = 'donation_' . uniqid() . '_' . time();
    
    // Prepare data for Piprapay API
    $payment_data = [
        'amount' => $amount_final,
        'currency' => DonationConfig::$currency,
        'description' => 'Donation to Mac M4 Software',
        'reference_id' => $reference_id,
        'customer' => [
            'name' => $donor_name,
            'email' => $donor_email ?: ''
        ],
        'metadata' => [
            'donor_name' => $donor_name,
            'message' => $message,
            'source' => 'mac_m4_website'
        ],
        'success_url' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . DonationConfig::$success_redirect_url . '?reference=' . $reference_id,
        'cancel_url' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . DonationConfig::$cancel_redirect_url,
        'webhook_url' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . DonationConfig::$webhook_url
    ];
    
    // Create charge with Piprapay
    $api_response = makeHttpRequest(DonationConfig::$piprapay_create_charge_endpoint, $payment_data);
    
    $payment_response = json_decode($api_response['response'], true);
    
    if ($api_response['http_code'] !== 200 && $api_response['http_code'] !== 201) {
        throw new Exception('Payment provider error: ' . ($payment_response['message'] ?? 'Unknown error'));
    }
    
    // Check if payment creation was successful
    if (!isset($payment_response['success']) || !$payment_response['success']) {
        throw new Exception('Failed to create payment: ' . ($payment_response['message'] ?? 'Unknown error'));
    }
    
    // Save donation record to database
    try {
        $stmt = $pdo->prepare("
            INSERT INTO " . DonationConfig::$donations_table . " 
            (reference_id, amount, currency, donor_name, donor_email, message, status, payment_id, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
        ");
        
        $stmt->execute([
            $reference_id,
            $amount,
            DonationConfig::$currency,
            $donor_name,
            $donor_email,
            $message,
            $payment_response['payment_id'] ?? null
        ]);
        
    } catch (PDOException $e) {
        // Log database error but don't fail the payment process
        logError('Database error saving donation: ' . $e->getMessage());
    }
    
    // Return success response with payment URL
    echo json_encode([
        'success' => true,
        'reference_id' => $reference_id,
        'payment_url' => $payment_response['payment_url'] ?? $payment_response['checkout_url'],
        'message' => 'Payment initiated successfully'
    ]);
    
} catch (Exception $e) {
    // Log error
    logError('Donation creation error: ' . $e->getMessage());
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
