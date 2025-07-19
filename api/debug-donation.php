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

    // Solveez API configuration
    $api_url = 'https://payment.solveez.com/api/create-charge';
    $api_key = '2108748469687b775b2b6ef1288790031302163742687b775b2b6f3757014442';
    
    // Get current domain for redirect URLs
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $domain = $protocol . '://' . $_SERVER['HTTP_HOST'];
    
    // Try different payload formats based on common API patterns
    $charge_data = [
        'amount' => (string)$amount,
        'currency' => 'BDT',
        'customer_name' => $donor_name,
        'customer_email' => $donor_email,
        'success_url' => $domain . '/donation-success.html',
        'cancel_url' => $domain . '/donation-cancel.html',
        'webhook_url' => $domain . '/api/webhook.php',
        'description' => 'Donation to Mac M4 Software: ' . $message
    ];

    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt_array($ch, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($charge_data),
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
            'User-Agent: Mac-M4-Software/1.0'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_VERBOSE => false
    ]);
    
    // Execute the request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $curl_info = curl_getinfo($ch);
    
    curl_close($ch);
    
    // Log detailed debug information
    $debug_log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'request_url' => $api_url,
        'request_data' => $charge_data,
        'http_code' => $http_code,
        'curl_error' => $curl_error,
        'curl_info' => $curl_info,
        'raw_response' => $response,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // Save debug log
    $log_dir = '../logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    file_put_contents($log_dir . '/debug_donations.log', json_encode($debug_log, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND | LOCK_EX);
    
    // Check for cURL errors
    if ($curl_error) {
        throw new Exception('Payment service connection error: ' . $curl_error);
    }
    
    // Decode response for analysis
    $result = json_decode($response, true);
    
    // Return detailed error information for debugging
    if ($http_code !== 200) {
        $error_details = [
            'http_code' => $http_code,
            'raw_response' => $response,
            'decoded_response' => $result,
            'api_url' => $api_url,
            'request_payload' => $charge_data
        ];
        
        throw new Exception('API Error - Code: ' . $http_code . ' | Response: ' . substr($response, 0, 500));
    }
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response from payment service: ' . $response);
    }
    
    // Check for successful response
    if (isset($result['payment_url']) || isset($result['checkout_url']) || isset($result['url'])) {
        $payment_url = $result['payment_url'] ?? $result['checkout_url'] ?? $result['url'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Donation payment created successfully',
            'payment_url' => $payment_url,
            'charge_id' => $result['id'] ?? $result['charge_id'] ?? null,
            'debug_info' => 'Check logs/debug_donations.log for details'
        ]);
    } else {
        // Return the full response for debugging
        echo json_encode([
            'success' => false,
            'message' => 'Payment creation failed',
            'debug_response' => $result,
            'http_code' => $http_code,
            'debug_info' => 'Check logs/debug_donations.log for full details'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => 'Check logs/debug_donations.log for full details'
    ]);
}
?>
