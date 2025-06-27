<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

// Fetch software requests
$sql_software_requests = "SELECT * FROM software_requests ORDER BY id DESC";
$result_software_requests = $conn->query($sql_software_requests);

// Fetch contact messages
$sql_contact_messages = "SELECT * FROM contact_messages ORDER BY id DESC";
$result_contact_messages = $conn->query($sql_contact_messages);

// Fetch software list for adding software
$sql_software_list = "SELECT * FROM software ORDER BY id DESC";
$result_software_list = $conn->query($sql_software_list);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            background-color: #212529;
            color: #ffffff;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 50px auto;
        }
        h1 {
            color: #62a92b;
            text-align: center;
            margin-bottom: 30px;
        }
        .dashboard-section {
            margin-bottom: 40px;
        }
        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .dashboard-table th, .dashboard-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #444;
        }
        .dashboard-table th {
            background-color: #333;
            color: #62a92b;
        }
        .dashboard-table tr:nth-child(even) {
            background-color: #2c2f33;
        }
        .dashboard-table tr:hover {
            background-color: #3b3e45;
        }
        .dashboard-table td {
            color: #bbb;
        }
        .button {
            background-color: #62a92b;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
        }
        .button:hover {
            background-color: #4e8b1f;
        }
        .form-container {
            margin-top: 20px;
            text-align: center;
        }
        .form-container input {
            margin: 10px;
            padding: 10px;
            width: 30%;
        }
        .form-container button {
            background-color: #62a92b;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
        }
        .form-container button:hover {
            background-color: #4e8b1f;
        }
    </style>
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
    <div class="form-container">
        <h2>Add New Software</h2>
        <form id="add-software-form" method="POST">
            <input type="text" name="software_name" placeholder="Software Name" required>
            <input type="text" name="software_version" placeholder="Software Version" required>
            <input type="text" name="download_link" placeholder="Download Link" required>
            <button type="submit">Add Software</button>
        </form>
        <div id="software-add-message"></div>
    </div>

    <!-- Logout Button -->
    <div style="text-align: center;">
        <a href="logout.php" class="button">Logout</a>
    </div>
</div>

<script>
    // Handle form submission with AJAX
    document.getElementById('add-software-form').addEventListener('submit', function(event) {
        event.preventDefault();

        var formData = new FormData(this);

        fetch('process_add_software.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('software-add-message').innerHTML = `<p style="color: green;">Software added successfully!</p>`;
                // Optionally, you can update the list of software dynamically without reloading the page
            } else {
                document.getElementById('software-add-message').innerHTML = `<p style="color: red;">Error: ${data.message}</p>`;
            }
        })
        .catch(error => {
            document.getElementById('software-add-message').innerHTML = `<p style="color: red;">An error occurred. Please try again.</p>`;
        });
    });
</script>

</body>
</html>

<?php
$conn->close();
?>
