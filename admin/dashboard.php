    <?php
    session_start();

    // Include database configuration
    require_once '../config/db_config.php';

    // Disable display_errors in production and log errors
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_error.log'); // Log errors to a file in the same directory

    // Check if the user is logged in, if not redirect to login page
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header("Location: login.php");  // Redirect to login page if not logged in
        exit();
    }

    // Pagination logic for Software Requests
    $limit = 10;  // Limit the number of software requests per page
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  // Get the page number from the URL, default to 1
    $offset = ($page - 1) * $limit;

    // Use prepared statements for fetching data
    $sql_software_requests = "SELECT * FROM software_requests ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt_software_requests = $conn->prepare($sql_software_requests);
    if ($stmt_software_requests === false) {
        error_log("Dashboard: Prepare failed for software requests: " . $conn->error);
        die("An error occurred. Please try again later.");
    }
    $stmt_software_requests->bind_param("ii", $limit, $offset);
    $stmt_software_requests->execute();
    $result_software_requests = $stmt_software_requests->get_result();

    // Pagination logic for Contact Messages
    $sql_contact_messages = "SELECT * FROM contact_messages ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt_contact_messages = $conn->prepare($sql_contact_messages);
    if ($stmt_contact_messages === false) {
        error_log("Dashboard: Prepare failed for contact messages: " . $conn->error);
        die("An error occurred. Please try again later.");
    }
    $stmt_contact_messages->bind_param("ii", $limit, $offset);
    $stmt_contact_messages->execute();
    $result_contact_messages = $stmt_contact_messages->get_result();

    // Get the total number of rows for both Software Requests and Contact Messages
    $total_software_requests_stmt = $conn->prepare("SELECT COUNT(*) AS count FROM software_requests");
    if ($total_software_requests_stmt === false) {
        error_log("Dashboard: Prepare failed for total software requests count: " . $conn->error);
        die("An error occurred. Please try again later.");
    }
    $total_software_requests_stmt->execute();
    $total_software_requests = $total_software_requests_stmt->get_result()->fetch_assoc()['count'];
    $total_software_requests_stmt->close();

    $total_contact_messages_stmt = $conn->prepare("SELECT COUNT(*) AS count FROM contact_messages");
    if ($total_contact_messages_stmt === false) {
        error_log("Dashboard: Prepare failed for total contact messages count: " . $conn->error);
        die("An error occurred. Please try again later.");
    }
    $total_contact_messages_stmt->execute();
    $total_contact_messages = $total_contact_messages_stmt->get_result()->fetch_assoc()['count'];
    $total_contact_messages_stmt->close();

    // Calculate total pages
    $total_pages_software = ceil($total_software_requests / $limit);
    $total_pages_contact = ceil($total_contact_messages / $limit);

    // Check for any submitted new software requests
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['software_name']) && isset($_POST['software_version'])) {
        $software_name = trim($_POST['software_name']);
        $software_version = trim($_POST['software_version']);
        $download_link = trim($_POST['download_link']);
        $date = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO software (software_name, software_version, download_link, date) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            error_log("Dashboard: Prepare failed for add software: " . $conn->error);
            die("An error occurred. Please try again later.");
        }
        $stmt->bind_param("ssss", $software_name, $software_version, $download_link, $date);
        if (!$stmt->execute()) {
            error_log("Dashboard: Execute failed for add software: " . $stmt->error);
        }
        $stmt->close();

        // Redirect back to dashboard after insert
        header("Location: dashboard.php");
        exit();
    }

    // Delete record logic (for both software requests and contact messages)
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['id']; // Cast to int for safety
        $type = $_GET['type'];

        if ($type == 'software') {
            // Delete software request
            $stmt = $conn->prepare("DELETE FROM software_requests WHERE id = ?");
            if ($stmt === false) {
                error_log("Dashboard: Prepare failed for delete software request: " . $conn->error);
                die("An error occurred. Please try again later.");
            }
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                error_log("Dashboard: Execute failed for delete software request: " . $stmt->error);
            }
            $stmt->close();
        } elseif ($type == 'contact') {
            // Delete contact message
            $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
            if ($stmt === false) {
                error_log("Dashboard: Prepare failed for delete contact message: " . $conn->error);
                die("An error occurred. Please try again later.");
            }
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                error_log("Dashboard: Execute failed for delete contact message: " . $stmt->error);
            }
            $stmt->close();
        }

        // Redirect back to dashboard
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_software_requests->num_rows > 0) {
                        while ($row = $result_software_requests->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["software_name"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["software_version"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["visitor_email"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["additional_info"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["date"]) . "</td>";
                            echo "<td><a href='dashboard.php?delete=true&id=" . htmlspecialchars($row["id"]) . "&type=software' class='button' style='background-color: red;'>Delete</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No software requests found</td></tr>";
                    }
                    $stmt_software_requests->close(); // Close statement after use
                    ?>
                </tbody>
            </table>

            <!-- Pagination for Software Requests -->
            <div class="pagination">
                <?php
                for ($i = 1; $i <= $total_pages_software; $i++) {
                    echo "<a href='dashboard.php?page=$i'>" . htmlspecialchars($i) . "</a>";
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_contact_messages->num_rows > 0) {
                        while ($row = $result_contact_messages->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["message"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["date"]) . "</td>";
                            echo "<td><a href='dashboard.php?delete=true&id=" . htmlspecialchars($row["id"]) . "&type=contact' class='button' style='background-color: red;'>Delete</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No contact messages found</td></tr>";
                    }
                    $stmt_contact_messages->close(); // Close statement after use
                    ?>
                </tbody>
            </table>

            <!-- Pagination for Contact Messages -->
            <div class="pagination">
                <?php
                for ($i = 1; $i <= $total_pages_contact; $i++) {
                    echo "<a href='dashboard.php?page=$i'>" . htmlspecialchars($i) . "</a>";
                }
                ?>
            </div>
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
    