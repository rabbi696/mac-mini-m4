<?php
// Database connection
$conn = new mysqli("localhost", "db_user", "db_password", "db_name");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$software_name = $_POST['software-name'];
$software_version = $_POST['software-version'];
$download_link = $_POST['download-link'];

// Prepare and execute the SQL query to insert software
$sql = "INSERT INTO software (name, version, download_link) VALUES ('$software_name', '$software_version', '$download_link')";
if ($conn->query($sql) === TRUE) {
    // Redirect to the admin dashboard with a success message
    header('Location: dashboard.php?success=1');
} else {
    // Redirect with an error message if the query fails
    header('Location: dashboard.php?error=1');
}

$conn->close();
?>
