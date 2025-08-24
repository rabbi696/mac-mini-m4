<?php
// Simple test donation to bypass API issues temporarily
header('Content-Type: application/json');

// Include required files
require_once '../config/db_config.php';
require_once '../config/donation_config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    // Validate and sanitize input
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $donor_name = trim(filter_input(INPUT_POST, 'donor_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'Anonymous');
    $donor_email = filter_input(INPUT_POST, 'donor_email', FILTER_VALIDATE_EMAIL) ?: 'anonymous@example.com';
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '');
    
    // Validate amount
    if (!$amount || $amount < 1) {
        throw new Exception('Invalid donation amount');
    }
    
    // Generate unique reference ID
    $reference_id = 'donation_' . uniqid() . '_' . time();
    
    // Save donation record to database (as pending)
    $stmt = $pdo->prepare("
        INSERT INTO " . DonationConfig::$donations_table . " 
        (reference_id, amount, currency, donor_name, donor_email, message, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $stmt->execute([
        $reference_id,
        $amount,
        DonationConfig::$currency,
        $donor_name,
        $donor_email,
        $message
    ]);
    
    // For now, return a simulated payment URL since the API is down
    // In production, you would redirect to a "Payment system temporarily unavailable" page
    echo json_encode([
        'success' => true,
        'reference_id' => $reference_id,
        'payment_url' => 'donation-cancel.html?reason=api_maintenance&reference=' . $reference_id,
        'message' => 'Payment system is temporarily unavailable. Your donation request has been saved. Please try again later.'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
