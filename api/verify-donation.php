<?php
header('Content-Type: application/json');

// Include configurations
require_once '../config/db_config.php';
require_once '../config/donation_config.php';
require_once 'PipraPay.php';

// Function to log activities
function logActivity($message) {
    $log_message = date('Y-m-d H:i:s') . " - " . $message . "\n";
    if (!file_exists('../logs')) {
        mkdir('../logs', 0755, true);
    }
    file_put_contents('../logs/donation_webhooks.log', $log_message, FILE_APPEND | LOCK_EX);
}

try {
    // Initialize PipraPay for webhook handling
    $piprapay = new PipraPay(
        DonationConfig::$piprapay_api_key,
        DonationConfig::$piprapay_base_url,
        DonationConfig::$currency
    );
    
    // Handle webhook with API key verification
    $webhook_result = $piprapay->handleWebhook(DonationConfig::$piprapay_api_key);
    
    if (!$webhook_result['status']) {
        throw new Exception('Webhook verification failed: ' . $webhook_result['message']);
    }
    
    $webhook_data = $webhook_result['data'];
    logActivity('Webhook received: ' . json_encode($webhook_data));
    
    // Extract payment information (adjust field names based on actual PipraPay response)
    $payment_id = $webhook_data['pp_id'] ?? $webhook_data['payment_id'] ?? null;
    $reference_id = $webhook_data['metadata']['reference_id'] ?? $webhook_data['reference_id'] ?? null;
    $status = $webhook_data['status'] ?? null;
    $amount = $webhook_data['amount'] ?? null;
    
    if (!$payment_id) {
        throw new Exception('Missing payment ID in webhook data');
    }
    
    // Verify payment with PipraPay API
    $verified_payment = $piprapay->verifyPayment($payment_id);
    
    if (isset($verified_payment['status']) && $verified_payment['status'] === false) {
        throw new Exception('Failed to verify payment with PipraPay: ' . ($verified_payment['error'] ?? 'Unknown error'));
    }
    
    // Update donation status in database
    $stmt = $pdo->prepare("
        UPDATE " . DonationConfig::$donations_table . " 
        SET status = ?, verified_at = NOW(), verification_data = ?
        WHERE reference_id = ? OR payment_id = ?
    ");
    
    $verification_data = json_encode($verified_payment);
    $final_status = ($verified_payment['status'] === 'completed' || $verified_payment['status'] === 'paid' || $verified_payment['status'] === 'success') ? 'completed' : ($status ?: 'pending');
    
    $stmt->execute([
        $final_status,
        $verification_data,
        $reference_id,
        $payment_id
    ]);
    
    $updated_rows = $stmt->rowCount();
    
    if ($updated_rows > 0) {
        logActivity("Payment verified and updated: Reference ID: $reference_id, Payment ID: $payment_id, Status: $final_status");
        
        // Send notification email if payment is completed (optional)
        if ($final_status === 'completed') {
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
