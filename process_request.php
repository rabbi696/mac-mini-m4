<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli("localhost", "u273108828_mac", "MacWithWilson007*", "u273108828_mac");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$software_name = $_POST['software-name'];
$software_version = $_POST['software-version'];
$additional_info = $_POST['additional-info'];
$visitor_email = $_POST['visitor-email'];

// Collect reCAPTCHA response
$recaptcha_response = $_POST['g-recaptcha-response'];

// Secret Key from Google reCAPTCHA
$secret_key = "YOUR_SECRET_KEY"; // Replace with your Secret Key

// Google reCAPTCHA Verification URL
$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = [
    'secret' => $secret_key,
    'response' => $recaptcha_response
];

// Send the POST request to Google's reCAPTCHA verification API
$options = [
    'http' => [
        'method' => 'POST',
        'content' => http_build_query($data),
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);
$response_keys = json_decode($response, true);

// Check if reCAPTCHA is valid
if (intval($response_keys["success"]) !== 1) {
    echo "Please complete the reCAPTCHA.";
} else {
    // reCAPTCHA verified, process the form
    $sql = "INSERT INTO software_requests (software_name, software_version, additional_info, visitor_email) 
            VALUES ('$software_name', '$software_version', '$additional_info', '$visitor_email')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Software request submitted successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
