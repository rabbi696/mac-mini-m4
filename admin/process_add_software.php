<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");  // Redirect to login page if not logged in
    exit();
}

// Database connection (use your actual credentials)
$conn = new mysqli("localhost", "u273108828_mac", "MacWithWilson007*", "u273108828_mac");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the data is posted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape special characters to prevent SQL injection
    $software_name = $conn->real_escape_string($_POST['software_name']);
    $software_version = $conn->real_escape_string($_POST['software_version']);
    $download_link = $conn->real_escape_string($_POST['download_link']);

    // SQL Insert query
    $sql = "INSERT INTO software (name, version, download_link, created_at) 
            VALUES ('$software_name', '$software_version', '$download_link', NOW())";

    if ($conn->query($sql) === TRUE) {
        // Success response
        echo json_encode(['status' => 'success', 'message' => 'Software added successfully']);
    } else {
        // Error response
        echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again.']);
    }
}

$conn->close();
?>
