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

// Fetch software requests (ordering by 'id' or 'date' if added)
$sql_software_requests = "SELECT * FROM software_requests ORDER BY id DESC";  // Or use 'date' if added
$result_software_requests = $conn->query($sql_software_requests);

// Fetch contact messages (ordering by 'id' or 'date' if added)
$sql_contact_messages = "SELECT * FROM contact_messages ORDER BY id DESC";  // Or use 'date' if added
$result_contact_messages = $conn->query($sql_contact_messages);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* Your existing CSS */
    </style>

    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Handle form submission for adding software
            $('#add-software-form').submit(function(e) {
                e.preventDefault();

                // Collect form data
                var softwareData = $(this).serialize();

                // AJAX call to submit data
                $.ajax({
                    url: 'process_add_software.php',
                    type: 'POST',
                    data: softwareData,
                    success: function(response) {
                        // On success, display success message and reload software list
                        $('#message').text('Software added successfully!');
                        $('#message').css('color', 'green');

                        // Reset the form after successful submission
                        $('#add-software-form')[0].reset();
                    },
                    error: function() {
                        // On failure, display error message
                        $('#message').text('There was an error. Please try again.');
                        $('#message').css('color', 'red');
                    }
                });
            });
        });
    </script>
</head>
<body>

<div class="container">
    <h1>Admin Dashboard</h1>

    <!-- Software Requests Section -->
    <div class="dashboard-section">
        <h2>Software Requests</h2>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Version</th>
                    <th>Email</th>
                    <th>Info</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_software_requests->num_rows > 0) {
                    while ($row = $result_software_requests->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["software_name"] . "</td>";
                        echo "<td>" . $row["software_version"] . "</td>";
                        echo "<td>" . $row["visitor_email"] . "</td>";
                        echo "<td>" . $row["additional_info"] . "</td>";
                        echo "<td>" . $row["date"] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No software requests found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Contact Messages Section -->
    <div class="dashboard-section">
        <h2>Contact Messages</h2>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_contact_messages->num_rows > 0) {
                    while ($row = $result_contact_messages->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["name"] . "</td>";
                        echo "<td>" . $row["email"] . "</td>";
                        echo "<td>" . $row["message"] . "</td>";
                        echo "<td>" . $row["date"] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No contact messages found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Add Software Section -->
    <div class="add-software-container">
        <h2>Add New Software</h2>
        <form id="add-software-form">
            <label for="software_name">Software Name</label>
            <input type="text" id="software_name" name="software_name" required>

            <label for="software_version">Version</label>
            <input type="text" id="software_version" name="software_version" required>

            <label for="download_link">Download Link</label>
            <input type="url" id="download_link" name="download_link" required>

            <button type="submit">Add Software</button>
        </form>
        <div id="message"></div> <!-- Success or error message -->
    </div>

    <!-- Logout Button -->
    <div style="text-align: center;">
        <a href="logout.php" class="button">Logout</a>
    </div>
</div>

</body>
</html>

<?php
$conn->close();
?>
