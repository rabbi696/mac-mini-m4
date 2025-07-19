<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

echo json_encode([
    'success' => true,
    'message' => 'PHP server is working correctly',
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
    'post_data' => $_POST,
    'get_data' => $_GET,
    'server_info' => [
        'PHP_VERSION' => PHP_VERSION,
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '',
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? ''
    ]
]);
?>
