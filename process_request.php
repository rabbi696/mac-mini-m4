<?php
$conn = new mysqli("localhost", "u273108828_mac", "MacWithWilson007*", "u273108828_mac");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$name = $conn->real_escape_string($_POST['software-name']);
$version = $conn->real_escape_string($_POST['software-version']);
$info = $conn->real_escape_string($_POST['additional-info']);
$email = $conn->real_escape_string($_POST['visitor-email']);

$sql = "INSERT INTO software_requests (software_name, software_version, additional_info, visitor_email) 
        VALUES ('$name', '$version', '$info', '$email')";
$conn->query($sql);
$conn->close();

echo "Request submitted!";
?>
