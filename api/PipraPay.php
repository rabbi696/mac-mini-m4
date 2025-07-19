<?php
/**
 * PipraPay PHP Integration Class
 * Official version from PipraPay documentation
 */
class PipraPay {
    private $api_key;
    private $base_url;
    private $currency;

    public function __construct($api_key, $base_url, $currency = 'BDT') {
        $this->api_key = $api_key;
        $this->base_url = rtrim($base_url, '/');
        $this->currency = $currency;
    }

    public function createCharge($data = []) {
        $data['currency'] = $this->currency;
        return $this->post('/api/create-charge', $data);
    }

    public function verifyPayment($pp_id) {
        return $this->post('/api/verify-payments', ['pp_id' => $pp_id]);
    }

    public function handleWebhook($expected_api_key) {
        $headers = getallheaders();
        $received_key = $headers['mh-piprapay-api-key'] ?? $headers['Mh-Piprapay-Api-Key'] ?? $_SERVER['HTTP_MH_PIPRAPAY_API_KEY'] ?? '';
        if ($received_key !== $expected_api_key) return ['status' => false, 'message' => 'Unauthorized'];
        return ['status' => true, 'data' => json_decode(file_get_contents('php://input'), true)];
    }

    private function post($endpoint, $data) {
        $ch = curl_init($this->base_url . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'content-type: application/json',
                'mh-piprapay-api-key: ' . $this->api_key
            ]
        ]);
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        return $err ? ['status' => false, 'error' => $err] : json_decode($res, true);
    }
}
?>
