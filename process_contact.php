<?php
// Start the session
session_start();

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection (use your actual credentials)
$conn = new mysqli("localhost", "u273108828_mac", "MacWithWilson007*", "u273108828_mac");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve form inputs
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $message = $conn->real_escape_string(trim($_POST['message']));
    $date = date('Y-m-d H:i:s');  // current timestamp

    // Insert data into the 'contact_messages' table
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $message, $date);

    if ($stmt->execute()) {
        // Successfully inserted data
        echo json_encode(["status" => "success", "message" => "Your request has been sent successfully!"]);
    } else {
        // Error during insertion
        echo json_encode(["status" => "error", "message" => "An error occurred. Please try again."]);
    }

    // Close the prepared statement and connection
    $stmt->close();
    $conn->close();
}
?>
