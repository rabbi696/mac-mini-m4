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

// Pagination logic for Software Requests
$limit = 10;  // Limit the number of software requests per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;  // Get the page number from the URL, default to 1
$offset = ($page - 1) * $limit;

$sql_software_requests = "SELECT * FROM software_requests ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result_software_requests = $conn->query($sql_software_requests);

// Pagination logic for Contact Messages
$sql_contact_messages = "SELECT * FROM contact_messages ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result_contact_messages = $conn->query($sql_contact_messages);

// Get the total number of rows for both Software Requests and Contact Messages
$total_software_requests = $conn->query("SELECT COUNT(*) AS count FROM software_requests")->fetch_assoc()['count'];
$total_contact_messages = $conn->query("SELECT COUNT(*) AS count FROM contact_messages")->fetch_assoc()['count'];

// Calculate total pages
$total_pages_software = ceil($total_software_requests / $limit);
$total_pages_contact = ceil($total_contact_messages / $limit);

// Check for any submitted new software requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['software_name']) && isset($_POST['software_version'])) {
    $software_name = $_POST['software_name'];
    $software_version = $_POST['software_version'];
    $download_link = $_POST['download_link'];
    $date = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO software (software_name, software_version, download_link, date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $software_name, $software_version, $download_link, $date);
    $stmt->execute();
    $stmt->close();

    // Redirect back to dashboard after insert
    header("Location: dashboard.php");
    exit();
}
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
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            color: #62a92b;
            margin: 0 5px;
            text-decoration: none;
        }
        .pagination a:hover {
            text-decoration: underline;
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

        <!-- Pagination for Software Requests -->
        <div class="pagination">
            <?php
            for ($i = 1; $i <= $total_pages_software; $i++) {
                echo "<a href='dashboard.php?page=$i'>$i</a>";
            }
            ?>
        </div>
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

        <!-- Pagination for Contact Messages -->
        <div class="pagination">
            <?php
            for ($i = 1; $i <= $total_pages_contact; $i++) {
                echo "<a href='dashboard.php?page=$i'>$i</a>";
            }
            ?>
        </div>
    </div>

    <!-- Add New Software Section -->
    <div class="dashboard-section">
        <h2>Add New Software</h2>
        <form action="dashboard.php" method="POST">
            <input type="text" name="software_name" placeholder="Software Name" required>
            <input type="text" name="software_version" placeholder="Version" required>
            <input type="text" name="download_link" placeholder="Download Link" required>
            <button type="submit" class="button">Add Software</button>
        </form>
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
