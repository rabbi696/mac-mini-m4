<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $software_name = $_POST['software-name'];
    $software_version = $_POST['software-version'];
    $additional_info = $_POST['additional-info'];
    $visitor_email = $_POST['visitor-email']; // Collect the visitor's email

    // Email configuration
    $to = "rabbi@solveez.com"; // Replace with your actual email address
    $subject = "New Software Request: $software_name";
    
    // Create the email message content
    $message = "
        <h1>New Software Request</h1>
        <p><strong>Software Name:</strong> $software_name</p>
        <p><strong>Version:</strong> $software_version</p>
        <p><strong>Additional Info:</strong><br> $additional_info</p>
        <p><strong>Visitor's Email:</strong> $visitor_email</p>
    ";
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: rabbi@solveez.com"; // Optional, change to your email

    // Send the email
    if (mail($to, $subject, $message, $headers)) {
        // Success message after sending the email
        echo "<p>Thank you for your request! We will get back to you shortly.</p>";
    } else {
        // Error message if the email fails to send
        echo "<p>Sorry, there was an error processing your request. Please try again later.</p>";
    }
}
?>
