<?php
// DB connection
$conn = new mysqli("localhost", "u273108828_mac", "MacWithWilson007*", "u273108828_mac");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Sanitize inputs
$name = $conn->real_escape_string($_POST['name']);
$email = $conn->real_escape_string($_POST['email']);
$message = $conn->real_escape_string($_POST['message']);

// Insert into database
$sql = "INSERT INTO contact_messages (name, email, message) VALUES ('$name', '$email', '$message')";
$conn->query($sql);
$conn->close();

echo "Message sent successfully!";
?>
