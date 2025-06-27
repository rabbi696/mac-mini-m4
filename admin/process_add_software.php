<?php
// Database connection (use your actual credentials)
$conn = new mysqli("localhost", "u273108828_mac", "MacWithWilson007*", "u273108828_mac");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the POST data
$software_name = $_POST['software_name'];
$software_version = $_POST['software_version'];
$download_link = $_POST['download_link'];

// Prepare the SQL statement
$sql = "INSERT INTO software_requests (software_name, software_version, download_link, date) VALUES (?, ?, ?, NOW())";

// Prepare and bind the statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $software_name, $software_version, $download_link);

// Execute the statement
if ($stmt->execute()) {
    echo "Software added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

// Close the connection
$stmt->close();
$conn->close();
?>
