    <?php
    // Start the session
    session_start();

    // Include database configuration
    require_once '../config/db_config.php';

    // Disable display_errors in production and log errors
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_error.log'); // Log errors to a file in the same directory

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Validate required fields
        if (empty($_POST['software-name']) || empty($_POST['visitor-email'])) {
            echo json_encode(["status" => "error", "message" => "Software name and email are required."]);
            exit();
        }
        
        // Validate email format
        if (!filter_var($_POST['visitor-email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["status" => "error", "message" => "Please enter a valid email address."]);
            exit();
        }
        
        // Sanitize and retrieve form inputs
        $software_name = trim($_POST['software-name']);
        $software_version = trim($_POST['software-version']);
        $additional_info = trim($_POST['additional-info']);
        $visitor_email = trim($_POST['visitor-email']);
        $date = date('Y-m-d H:i:s');  // current timestamp

        // Insert the software request into the database
        $stmt = $conn->prepare("INSERT INTO software_requests (software_name, software_version, additional_info, visitor_email, date) VALUES (?, ?, ?, ?, ?)");
        if ($stmt === false) {
            error_log("Process Request: Prepare failed: " . $conn->error);
            echo json_encode(["status" => "error", "message" => "An internal error occurred."]);
            $conn->close();
            exit();
        }
        $stmt->bind_param("sssss", $software_name, $software_version, $additional_info, $visitor_email, $date);

        // Execute the query and check for errors
        if ($stmt->execute()) {
            // Data inserted successfully
            echo json_encode(["status" => "success", "message" => "Your software request has been submitted successfully!"]);
        } else {
            // Error inserting data
            error_log("Process Request: Execute failed: " . $stmt->error); // Log the actual error
            echo json_encode(["status" => "error", "message" => "Error occurred while submitting your request. Please try again."]);
        }

        // Close the prepared statement and connection
        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    }
    ?>
    