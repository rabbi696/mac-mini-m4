<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Function to log errors
function logError($message) {
    $log_message = date('Y-m-d H:i:s') . " - " . $message . "\n";
    if (!file_exists('../logs')) {
        mkdir('../logs', 0755, true);
    }
    file_put_contents('../logs/donation_errors.log', $log_message, FILE_APPEND | LOCK_EX);
}

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    // Validate and sanitize input
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $donor_name = filter_input(INPUT_POST, 'donor_name', FILTER_SANITIZE_STRING) ?: 'Anonymous';
    $donor_email = filter_input(INPUT_POST, 'donor_email', FILTER_VALIDATE_EMAIL) ?: 'anonymous@example.com';
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING) ?: '';
    
    // Validate amount
    if (!$amount || $amount < 1) {
        throw new Exception('Invalid donation amount');
    }
    
    // Check if API is under maintenance
    $api_status = MaintenanceChecker::checkApiStatus();
    if ($api_status['status'] === 'maintenance') {
        throw new Exception($api_status['message']);
    }
    
    // For BDT, use the amount as-is
    $amount_final = floatval($amount);
    
    // Generate unique reference ID
    $reference_id = 'donation_' . uniqid() . '_' . time();
    
    // Initialize PipraPay
    $piprapay = new PipraPay(
        DonationConfig::$piprapay_api_key,
        DonationConfig::$piprapay_base_url,
        DonationConfig::$currency
    );
    
    // Prepare data for PipraPay API
    $payment_data = [
        'full_name' => $donor_name,
        'email_mobile' => $donor_email,
        'amount' => $amount_final,
        'metadata' => [
            'reference_id' => $reference_id,
            'donor_name' => $donor_name,
            'message' => $message,
            'source' => 'mac_m4_website'
        ],
        'redirect_url' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . DonationConfig::$success_redirect_url . '?reference=' . $reference_id,
        'cancel_url' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . DonationConfig::$cancel_redirect_url,
        'webhook_url' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . DonationConfig::$webhook_url
    ];
    
    // Create charge with PipraPay
    $payment_response = $piprapay->createCharge($payment_data);
    
    // Log the full response for debugging
    logError('PipraPay API Response: ' . json_encode($payment_response));
    
    // Check for cURL errors
    if (isset($payment_response['status']) && $payment_response['status'] === false) {
        $error_msg = $payment_response['error'] ?? 'Connection error';
        logError('PipraPay cURL Error: ' . $error_msg);
        throw new Exception('Payment provider error: ' . $error_msg);
    }
    
    // Check if payment creation was successful (using 'status' as per GitHub documentation)
    if (!isset($payment_response['status']) || !$payment_response['status']) {
        $error_details = [];
        if (isset($payment_response['message'])) $error_details[] = $payment_response['message'];
        if (isset($payment_response['error'])) $error_details[] = $payment_response['error'];
        if (isset($payment_response['errors'])) $error_details[] = json_encode($payment_response['errors']);
        
        $error_msg = !empty($error_details) ? implode(', ', $error_details) : 'Unknown error';
        logError('PipraPay Payment Creation Failed: ' . $error_msg);
        throw new Exception('Failed to create payment: ' . $error_msg);
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
            $amount_final,
            DonationConfig::$currency,
            $donor_name,
            $donor_email,
            $message,
            $payment_response['pp_id'] ?? $payment_response['payment_id'] ?? null
        ]);
        
    } catch (PDOException $e) {
        // Log database error but don't fail the payment process
        logError('Database error saving donation: ' . $e->getMessage());
    }
    
    // Return success response with payment URL
    echo json_encode([
        'success' => true,
        'reference_id' => $reference_id,
        'payment_url' => $payment_response['pp_url'] ?? $payment_response['payment_url'] ?? $payment_response['checkout_url'],
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
