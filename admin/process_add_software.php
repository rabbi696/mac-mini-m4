<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "u273108828_mac", "MacWithWilson007*", "u273108828_mac");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$software_name = $_POST['software_name'];
$software_version = $_POST['software_version'];
$download_link = $_POST['download_link'];

// Insert software into the database
$sql = "INSERT INTO software (software_name, software_version, download_link) 
        VALUES ('$software_name', '$software_version', '$download_link')";

if ($conn->query($sql) === TRUE) {
    // Return success message as JSON
    echo json_encode(["success" => true, "message" => "Software added successfully"]);
} else {
    // Return error message as JSON
    echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
}

$conn->close();
?>
