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
    $software_name = $conn->real_escape_string(trim($_POST['software-name']));
    $software_version = $conn->real_escape_string(trim($_POST['software-version']));
    $additional_info = $conn->real_escape_string(trim($_POST['additional-info']));
    $visitor_email = $conn->real_escape_string(trim($_POST['visitor-email']));
    $date = date('Y-m-d H:i:s');  // current timestamp
    
    // Insert the software request into the database
    $stmt = $conn->prepare("INSERT INTO software_requests (software_name, software_version, additional_info, visitor_email, date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $software_name, $software_version, $additional_info, $visitor_email, $date);

    // Execute the query and check for errors
    if ($stmt->execute()) {
        // Data inserted successfully
        echo json_encode(["status" => "success", "message" => "Your software request has been submitted successfully!"]);
    } else {
        // Error inserting data
        echo json_encode(["status" => "error", "message" => "Error occurred while submitting your request. Please try again."]);
    }

    // Close the prepared statement and connection
    $stmt->close();
    $conn->close();
}
?>
