<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    // Collect reCAPTCHA response
    $recaptcha_response = $_POST['g-recaptcha-response'];

    // Secret Key from Google reCAPTCHA
    $secret_key = "6LeJ-G4rAAAAAIZ3PXBJQga9dTvqtDnI5Kkd_bVP"; // Replace with your Secret Key

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
        $to = "rabbi@solveez.com"; // Replace with your email address
        $subject = "Contact Us Message from $name";
        $email_content = "Name: $name\nEmail: $email\nMessage:\n$message\n";
        $headers = "From: $email";

        if (mail($to, $subject, $email_content, $headers)) {
            echo "Message sent successfully!";
        } else {
            echo "Failed to send the message. Please try again later.";
        }
    }
}
?>
