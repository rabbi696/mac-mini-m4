<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "u273108828_mac", "MacWithWilson007*", "u273108828_mac");
?>
<h2>Software Requests</h2>
<table border="1">
    <tr><th>ID</th><th>Name</th><th>Version</th><th>Email</th><th>Info</th><th>Date</th></tr>
    <?php
    $result = $conn->query("SELECT * FROM software_requests ORDER BY submitted_at DESC");
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['software_name']}</td><td>{$row['software_version']}</td>
        <td>{$row['visitor_email']}</td><td>{$row['additional_info']}</td><td>{$row['submitted_at']}</td></tr>";
    }
    ?>
</table>

<h2>Contact Messages</h2>
<table border="1">
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Date</th></tr>
    <?php
    $result = $conn->query("SELECT * FROM contact_messages ORDER BY submitted_at DESC");
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['email']}</td>
        <td>{$row['message']}</td><td>{$row['submitted_at']}</td></tr>";
    }
    $conn->close();
    ?>
</table>
