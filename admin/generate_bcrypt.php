    <?php
    // Disable display_errors in production and log errors
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_error.log'); // Log errors to a file in the same directory

    // PHP code to generate bcrypt hash
    $password = 'MacWilson007*';  // The password you want to hash
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);  // Generate the bcrypt hash

    // Output the hashed password (for development use only, remove from production)
    echo "Generated bcrypt hash: " . htmlspecialchars($hashed_password);
    ?>
    