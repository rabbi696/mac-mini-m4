 <?php
    // db_config.php
    // Database credentials
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'u273108828_mac'); // Replace with your actual database username
    define('DB_PASSWORD', 'MacWithWilson007*'); // Replace with your actual database password
    define('DB_NAME', 'u273108828_mac');     // Replace with your actual database name
    // Attempt to connect to MySQL database
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    // Check connection
    if ($conn->connect_error) {
        // Log the connection error instead of displaying it directly
        error_log("Failed to connect to MySQL: " . $conn->connect_error);
        die("Connection failed. Please try again later."); // Generic message for users
    }
    ?>