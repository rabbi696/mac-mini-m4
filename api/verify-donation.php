<?php
header('Content-Type: application/json');

// Include configurations
require_once '../config/db_config.php';
require_once '../config/donation_config.php';

// Function to log activities
function logActivity($message) {
    $log_message = date('Y-m-d H:i:s') . " - " . $message . "\n";
    file_put_contents('../logs/donation_webhooks.log', $log_message, FILE_APPEND | LOCK_EX);
}

// Function to verify payment with Piprapay
function verifyPaymentWithPiprapay($payment_id) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, DonationConfig::$piprapay_verify_payment_endpoint . '/' . $payment_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . DonationConfig::$piprapay_api_key
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception('cURL Error: ' . $error);
    }
    
    return ['response' => json_decode($response, true), 'http_code' => $httpCode];
}

try {
    // Get the raw POST data
    $raw_payload = file_get_contents('php://input');
    $webhook_data = json_decode($raw_payload, true);
    
    if (!$webhook_data) {
        throw new Exception('Invalid webhook payload');
    }
    
    logActivity('Webhook received: ' . $raw_payload);
    
    // Extract payment information
    $payment_id = $webhook_data['payment_id'] ?? null;
    $reference_id = $webhook_data['reference_id'] ?? null;
    $status = $webhook_data['status'] ?? null;
    $amount = $webhook_data['amount'] ?? null;
    
    if (!$payment_id || !$reference_id) {
        throw new Exception('Missing required webhook data');
    }
    
    // Verify payment with Piprapay API
    $verification_result = verifyPaymentWithPiprapay($payment_id);
    
    if ($verification_result['http_code'] !== 200) {
        throw new Exception('Failed to verify payment with Piprapay');
    }
    
    $verified_payment = $verification_result['response'];
    
    // Update donation status in database
    $stmt = $pdo->prepare("
        UPDATE " . DonationConfig::$donations_table . " 
        SET status = ?, verified_at = NOW(), verification_data = ?
        WHERE reference_id = ? OR payment_id = ?
    ");
    
    $verification_data = json_encode($verified_payment);
    $final_status = ($verified_payment['status'] === 'completed' || $verified_payment['status'] === 'paid') ? 'completed' : $status;
    
    $stmt->execute([
        $final_status,
        $verification_data,
        $reference_id,
        $payment_id
    ]);
    
    $updated_rows = $stmt->rowCount();
    
    if ($updated_rows > 0) {
        logActivity("Payment verified and updated: Reference ID: $reference_id, Status: $final_status");
        
        // Send notification email if payment is completed (optional)
        if ($final_status === 'completed') {
            // You can implement email notification here
            logActivity("Donation completed: $reference_id - Amount: ৳" . $amount);
        }
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Payment verified and updated'
        ]);
    } else {
        logActivity("No donation record found for: Reference ID: $reference_id, Payment ID: $payment_id");
        echo json_encode([
            'success' => false,
            'message' => 'Donation record not found'
        ]);
    }
    
} catch (Exception $e) {
    logActivity('Webhook error: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
