<?php
// Start session and database connection
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "u273108828_mac", "MacWithWilson007*", "u273108828_mac");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'id' and 'type' are set in the URL
if (isset($_GET['id']) && isset($_GET['type'])) {
    $id = $_GET['id'];
    $type = $_GET['type'];

    if ($type == 'software') {
        // Delete software request
        $stmt = $conn->prepare("DELETE FROM software_requests WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Software request deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting software request!";
        }
        $stmt->close();
    } elseif ($type == 'contact') {
        // Delete contact message
        $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Contact message deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting contact message!";
        }
        $stmt->close();
    }
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit();
?>
