<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli("localhost", "u273108828_mac", "MacWithWilson007*", "u273108828_mac");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$software_name = $_POST['software_name'];
$software_version = $_POST['software_version'];
$additional_info = $_POST['additional_info'];
$visitor_email = $_POST['visitor_email'];

// Insert data into software_requests table
$sql = "INSERT INTO software_requests (software_name, software_version, additional_info, visitor_email, date)
        VALUES ('$software_name', '$software_version', '$additional_info', '$visitor_email', NOW())";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo json_encode(['status' => 'success', 'message' => 'Your request has been sent successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again.']);
}

$conn->close();
?>
