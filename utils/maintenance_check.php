<?php
// Maintenance Check Utility for PipraPay API
class MaintenanceChecker {
    
    public static function isApiUnderMaintenance($url = null) {
        if (!$url) {
            require_once __DIR__ . '/../config/donation_config.php';
            $url = DonationConfig::$piprapay_base_url . '/api';
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Check if response contains maintenance message
        if ($response && (
            stripos($response, 'maintenance') !== false ||
            stripos($response, 'under maintenance') !== false ||
            $httpCode >= 500
        )) {
            return true;
        }
        
        return false;
    }
    
    public static function getMaintenanceMessage() {
        return [
            'status' => 'maintenance',
            'message' => 'Payment system is currently under maintenance. Please try again later.',
            'code' => 'SYSTEM_MAINTENANCE'
        ];
    }
    
    public static function checkApiStatus($url = null) {
        if (self::isApiUnderMaintenance($url)) {
            return self::getMaintenanceMessage();
        }
        
        return [
            'status' => 'available',
            'message' => 'Payment system is operational',
            'code' => 'SYSTEM_OK'
        ];
    }
}
?>
