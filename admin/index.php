<?php
// Database connection
$conn = new mysqli("localhost", "u273108828_mac", "MacWithWilson007*", "u273108828_mac");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all software from the database
$sql = "SELECT * FROM software ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Software Downloads for Mac Mini M4</title>
    <style>
        /* Styles omitted for brevity */
    </style>
</head>
<body>

<!-- Header -->
<header>
    <a href="https://mac.golamrabbi.dev">Home</a>
    <a href="https://mac.golamrabbi.dev/dmca.html">DMCA</a>
    <a href="https://mac.golamrabbi.dev/contact-us.html">Contact Us</a>
</header>

<div class="container">
    <!-- Software List -->
    <h1>Software for "Mac Mini M4"</h1>
    <div class="software-list">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='software-item'>";
                echo "<p class='name'>" . $row['name'] . "</p>";
                echo "<p class='version version-spacing'>Version: " . $row['version'] . "</p>";
                echo "<br>";
                echo "<a href='" . $row['download_link'] . "' class='download-btn' download target='_blank'>Download Now</a>";
                echo "<br>";
                echo "</div>";
            }
        } else {
            echo "<p>No software available</p>";
        }
        ?>
    </div>
</div>

<!-- Footer Menu -->
<footer>
    <p>&copy; 2025 Golam Rabbi. All Rights Reserved.</p>
    <a href="https://mac.golamrabbi.dev/terms-of-service.html">Terms of Service</a>
    <a href="https://mac.golamrabbi.dev/privacy-policy.html">Privacy Policy</a>
    <a href="https://mac.golamrabbi.dev/dmca.html">DMCA</a>
    <a href="https://mac.golamrabbi.dev/contact-us.html">Contact Us</a>
</footer>

</body>
</html>

<?php
$conn->close();
?>
