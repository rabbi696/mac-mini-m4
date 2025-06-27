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
$software_name = isset($_POST['software_name']) ? trim($_POST['software_name']) : '';
$software_version = isset($_POST['software_version']) ? trim($_POST['software_version']) : '';
$download_link = isset($_POST['download_link']) ? trim($_POST['download_link']) : '';

// Validate the input fields
if (empty($software_name) || empty($software_version) || empty($download_link)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required!']);
    exit();
}

// Debug: Check the data before insertion
error_log("Inserting data: Software Name: $software_name, Version: $software_version, Download Link: $download_link");

// Insert data into software table
$sql = "INSERT INTO software (name, version, download_link, created_at)
        VALUES ('$software_name', '$software_version', '$download_link', NOW())";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo json_encode(['status' => 'success', 'message' => 'Software added successfully!']);
} else {
    // If there's an error, output the error message
    echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again.']);
}

$conn->close();
?>
