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
        // Sanitize and retrieve form inputs
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $message = trim($_POST['message']);
        $date = date('Y-m-d H:i:s');  // current timestamp

        // Insert data into the 'contact_messages' table
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, date) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            error_log("Process Contact: Prepare failed: " . $conn->error);
            echo json_encode(["status" => "error", "message" => "An internal error occurred."]);
            $conn->close();
            exit();
        }
        $stmt->bind_param("ssss", $name, $email, $message, $date);

        if ($stmt->execute()) {
            // Successfully inserted data
            echo json_encode(["status" => "success", "message" => "Your message has been sent successfully!"]);
        } else {
            // Error during insertion
            error_log("Process Contact: Execute failed: " . $stmt->error); // Log the actual error
            echo json_encode(["status" => "error", "message" => "An error occurred. Please try again."]);
        }

        // Close the prepared statement and connection
        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    }
    ?>
    