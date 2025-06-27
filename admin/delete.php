    <?php
    // Start session and database connection
    session_start();

    // Include database configuration
    require_once '../config/db_config.php';

    // Disable display_errors in production and log errors
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_error.log'); // Log errors to a file in the same directory

    // Check if the user is logged in
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }

    // Check if 'id' and 'type' are set in the URL
    if (isset($_GET['id']) && isset($_GET['type'])) {
        $id = (int)$_GET['id']; // Cast to int for safety
        $type = $_GET['type'];

        if ($type == 'software') {
            // Delete software request
            $stmt = $conn->prepare("DELETE FROM software_requests WHERE id = ?");
            if ($stmt === false) {
                error_log("Delete: Prepare failed for software request: " . $conn->error);
                $_SESSION['error'] = "An internal error occurred.";
            } else {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Software request deleted successfully!";
                } else {
                    error_log("Delete: Execute failed for software request: " . $stmt->error);
                    $_SESSION['error'] = "Error deleting software request!";
                }
                $stmt->close();
            }
        } elseif ($type == 'contact') {
            // Delete contact message
            $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
            if ($stmt === false) {
                error_log("Delete: Prepare failed for contact message: " . $conn->error);
                $_SESSION['error'] = "An internal error occurred.";
            } else {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Contact message deleted successfully!";
                } else {
                    error_log("Delete: Execute failed for contact message: " . $stmt->error);
                    $_SESSION['error'] = "Error deleting contact message!";
                }
                $stmt->close();
            }
        } else {
            $_SESSION['error'] = "Invalid delete type specified.";
        }
    } else {
        $_SESSION['error'] = "Invalid delete request: ID or type missing.";
    }

    $conn->close();
    // Redirect back to dashboard
    header("Location: dashboard.php");
    exit();
    ?>
    