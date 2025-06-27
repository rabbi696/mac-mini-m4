<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize the form inputs
    $software_name = isset($_POST['software_name']) ? htmlspecialchars($_POST['software_name']) : '';
    $software_version = isset($_POST['software_version']) ? htmlspecialchars($_POST['software_version']) : '';
    $download_link = isset($_POST['download_link']) ? htmlspecialchars($_POST['download_link']) : '';

    // Check if all fields are provided
    if (!empty($software_name) && !empty($software_version) && !empty($download_link)) {
        // Database connection
        $conn = new mysqli("localhost", "u273108828_mac", "MacWithWilson007*", "u273108828_mac");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Insert software into the database
        $stmt = $conn->prepare("INSERT INTO software (name, version, download_link, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $software_name, $software_version, $download_link);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Software added successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to add software"]);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
    }
}
?>
