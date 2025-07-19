<?php
   // db_config.php - CyberPanel Optimized Database Configuration
   
   // Load environment variables
   require_once __DIR__ . '/env_loader.php';
   
   // Environment detection
   $is_cyberpanel = file_exists('/usr/local/CyberCP') || file_exists('/home/cyberpanel');
   $is_local_dev = file_exists('/tmp/mysql_3306.sock') && !$is_cyberpanel;
   
   // Database credentials - Use environment variables if available, fallback to defaults
   define('DB_SERVER', $_ENV['DB_SERVER'] ?? 'localhost');
   define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'mac_software');
   define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? 'MacWithWilson007*');
   define('DB_NAME', $_ENV['DB_NAME'] ?? 'mac_software');
   define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
   
   // Socket path detection for different environments
   function detectSocketPath() {
       global $is_cyberpanel, $is_local_dev;
       
       if ($is_local_dev) {
           return '/tmp/mysql_3306.sock';
       }
       
       // Common CyberPanel/Linux socket paths
       $possible_sockets = [
           '/var/lib/mysql/mysql.sock',
           '/var/run/mysqld/mysqld.sock',
           '/tmp/mysql.sock',
           '/run/mysqld/mysqld.sock'
       ];
       
       foreach ($possible_sockets as $socket) {
           if (file_exists($socket)) {
               return $socket;
           }
       }
       
       return null; // Use TCP connection if no socket found
   }
   
   $socket_path = detectSocketPath();
   
   // Create MySQLi connection with environment-specific settings
   if ($socket_path && $is_local_dev) {
       // Local development with socket
       $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT, $socket_path);
   } else {
       // CyberPanel/Production - use TCP connection for reliability
       $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
   }
   
   // Check MySQLi connection
   if ($conn->connect_error) {
       error_log("Failed to connect to MySQL: " . $conn->connect_error);
       die("Connection failed. Please try again later.");
   }
   
   // Set charset for MySQLi
   $conn->set_charset("utf8mb4");
   
   // Create PDO connection with environment-specific DSN
   try {
       if ($socket_path && $is_local_dev) {
           // Local development with socket
           $dsn = "mysql:unix_socket={$socket_path};dbname=" . DB_NAME . ";charset=utf8mb4";
       } else {
           // CyberPanel/Production - TCP connection
           $dsn = "mysql:host=" . DB_SERVER . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
       }
       
       $pdo = new PDO(
           $dsn,
           DB_USERNAME,
           DB_PASSWORD,
           [
               PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
               PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
               PDO::ATTR_EMULATE_PREPARES => false,
               PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
           ]
       );
   } catch (PDOException $e) {
       error_log("PDO Connection failed: " . $e->getMessage());
       // For donation-related requests, we'll handle this in the individual files
   }
   ?>
