<?php
// Donation Configuration
class DonationConfig {
    // Piprapay API Configuration
    public static $piprapay_api_key = '1582434874687127321a07912670681911661600709687127321a07e1465171749';
    public static $piprapay_base_url = 'https://sandbox.piprapay.com';
    public static $piprapay_create_charge_endpoint = 'https://sandbox.piprapay.com/api/create-charge';
    public static $piprapay_verify_payment_endpoint = 'https://sandbox.piprapay.com/api/verify-payments';
    
    // Donation Settings
    public static $currency = 'BDT';
    public static $success_redirect_url = 'donation-success.html';
    public static $cancel_redirect_url = 'donation-cancel.html';
    public static $webhook_url = 'api/verify-donation.php';
    
    // Database table name for donations
    public static $donations_table = 'donations';
    
    // Admin settings for API management
    public static function updateApiSettings($api_key, $base_url, $create_endpoint, $verify_endpoint) {
        // In a production environment, you would save this to database or secure config file
        // For now, we'll use a simple file-based approach
        $config_data = [
            'api_key' => $api_key,
            'base_url' => $base_url,
            'create_endpoint' => $create_endpoint,
            'verify_endpoint' => $verify_endpoint,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents(__DIR__ . '/piprapay_config.json', json_encode($config_data, JSON_PRETTY_PRINT));
        
        // Update static properties
        self::$piprapay_api_key = $api_key;
        self::$piprapay_base_url = $base_url;
        self::$piprapay_create_charge_endpoint = $create_endpoint;
        self::$piprapay_verify_payment_endpoint = $verify_endpoint;
        
        return true;
    }
    
    public static function loadApiSettings() {
        $config_file = __DIR__ . '/piprapay_config.json';
        if (file_exists($config_file)) {
            $config_data = json_decode(file_get_contents($config_file), true);
            if ($config_data) {
                self::$piprapay_api_key = $config_data['api_key'] ?? self::$piprapay_api_key;
                self::$piprapay_base_url = $config_data['base_url'] ?? self::$piprapay_base_url;
                self::$piprapay_create_charge_endpoint = $config_data['create_endpoint'] ?? self::$piprapay_create_charge_endpoint;
                self::$piprapay_verify_payment_endpoint = $config_data['verify_endpoint'] ?? self::$piprapay_verify_payment_endpoint;
            }
        }
    }
}

// Load API settings on include
DonationConfig::loadApiSettings();
?>
