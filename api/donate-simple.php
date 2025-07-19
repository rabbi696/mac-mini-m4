<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get form data
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $donor_name = filter_input(INPUT_POST, 'donor_name', FILTER_SANITIZE_STRING) ?? 'Anonymous';
    $donor_email = filter_input(INPUT_POST, 'donor_email', FILTER_VALIDATE_EMAIL) ?? 'anonymous@donor.com';
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING) ?? '';

    // Validate required fields
    if (!$amount || $amount < 1) {
        throw new Exception('Invalid donation amount');
    }

    // PipraPay API configuration - Based on official documentation
    $piprapay_url = 'https://sandbox.piprapay.com/api/create-charge';
    $api_key = '2108748469687b775b2b6ef1288790031302163742687b775b2b6f3757014442';
    
    // Get current domain for redirect URLs
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $domain = $protocol . '://' . $_SERVER['HTTP_HOST'];
    
    // Prepare the charge data (according to official PipraPay documentation)
    $charge_data = [
        'full_name' => $donor_name,
        'email_mobile' => $donor_email,
        'amount' => (string)$amount,
        'metadata' => [
            'donation_type' => 'website_donation',
            'donor_message' => $message,
            'site' => 'mac_m4_software'
        ],
        'redirect_url' => $domain . '/donation-success.html',
        'return_type' => 'GET',
        'cancel_url' => $domain . '/donation-cancel.html',
        'webhook_url' => $domain . '/api/webhook.php',
        'currency' => 'BDT'
    ];

    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options (based on PipraPay documentation)
    curl_setopt_array($ch, [
        CURLOPT_URL => $piprapay_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($charge_data),
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'mh-piprapay-api-key: ' . $api_key
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    
    // Execute the request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    
    curl_close($ch);
    
    // Check for cURL errors
    if ($curl_error) {
        throw new Exception('Payment service connection error: ' . $curl_error);
    }
    
    // Check HTTP response code
    if ($http_code !== 200) {
        // Handle different error scenarios
        if ($http_code === 400 && strpos($response, 'Invalid API key') !== false) {
            throw new Exception('Payment service is currently under maintenance. Please try again in a few minutes.');
        }
        if ($http_code === 200 && (empty($response) || strpos($response, 'maintenance') !== false)) {
            throw new Exception('Payment service is currently under maintenance. Please try again in a few minutes.');
        }
        throw new Exception('Payment service returned error code: ' . $http_code);
    }
    
    // Decode the response
    $result = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid response from payment service');
    }
    
    // Log the donation attempt
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'amount' => $amount,
        'donor_name' => $donor_name,
        'donor_email' => $donor_email,
        'message' => $message,
        'piprapay_response' => $result,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // Save to log file
    $log_dir = '../logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    file_put_contents($log_dir . '/donations.log', json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
    
    // Check if PipraPay returned a payment URL
    if (isset($result['payment_url']) || isset($result['checkout_url']) || isset($result['url'])) {
        $payment_url = $result['payment_url'] ?? $result['checkout_url'] ?? $result['url'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Donation payment created successfully',
            'payment_url' => $payment_url,
            'charge_id' => $result['id'] ?? $result['charge_id'] ?? null
        ]);
    } else {
        // Log the full response for debugging
        error_log('PipraPay Response: ' . json_encode($result));
        
        // If no payment URL is returned, check if there's an error message
        $error_message = $result['message'] ?? $result['error'] ?? 'Payment URL not provided';
        throw new Exception('Payment creation failed: ' . $error_message);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    // Log the error
    $error_log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $e->getMessage(),
        'request_data' => $_POST,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $log_dir = '../logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    file_put_contents($log_dir . '/donation_errors.log', json_encode($error_log) . "\n", FILE_APPEND | LOCK_EX);
}
?>
