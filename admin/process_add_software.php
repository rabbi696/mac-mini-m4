    <?php
    session_start();

    // Include database configuration
    require_once '../config/db_config.php';

    // Disable display_errors in production and log errors
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_error.log'); // Log errors to a file in the same directory

    // Ensure this script is only accessible by logged-in admins if it's meant for admin use
    // If this script is called via AJAX from the dashboard, the dashboard itself should handle authentication.
    // If it's a standalone script that could be accessed directly, uncomment the following:
    /*
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
        exit();
    }
    */

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate and sanitize the form inputs
        $software_name = isset($_POST['software_name']) ? trim($_POST['software_name']) : '';
        $software_version = isset($_POST['software_version']) ? trim($_POST['software_version']) : '';
        $download_link = isset($_POST['download_link']) ? trim($_POST['download_link']) : '';

        // Check if all fields are provided
        if (!empty($software_name) && !empty($software_version) && !empty($download_link)) {
            // Insert software into the database
            // Note: The table name in your original code was 'software', but the dashboard inserts into 'software'.
            // I'm assuming 'software' is the correct table for adding new software.
            $stmt = $conn->prepare("INSERT INTO software (name, version, download_link, created_at) VALUES (?, ?, ?, NOW())");
            if ($stmt === false) {
                error_log("Process Add Software: Prepare failed: " . $conn->error);
                echo json_encode(["status" => "error", "message" => "An internal error occurred."]);
                $conn->close();
                exit();
            }
            $stmt->bind_param("sss", $software_name, $software_version, $download_link);

            if ($stmt->execute()) {
                $response = [
                    "status" => "success",
                    "message" => "Software added successfully",
                    "id" => $stmt->insert_id, // Fetch inserted ID
                    "software_name" => htmlspecialchars($software_name), // Escape for JSON output
                    "software_version" => htmlspecialchars($software_version),
                    "download_link" => htmlspecialchars($download_link),
                    "created_at" => date('Y-m-d H:i:s')
                ];
                echo json_encode($response);
            } else {
                error_log("Process Add Software: Execute failed: " . $stmt->error); // Log the actual error
                echo json_encode(["status" => "error", "message" => "Failed to add software"]);
            }

            $stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "All fields are required"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    }
    $conn->close();
    ?>
    