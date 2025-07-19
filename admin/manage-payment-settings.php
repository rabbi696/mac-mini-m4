<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../config/db_config.php';
require_once '../config/donation_config.php';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $api_key = trim($_POST['api_key']);
    $base_url = trim($_POST['base_url']);
    $create_endpoint = trim($_POST['create_endpoint']);
    $verify_endpoint = trim($_POST['verify_endpoint']);
    
    try {
        // Check if payment_settings table exists and has correct structure before updating
        $table_exists = false;
        $has_correct_structure = false;
        
        try {
            $result = $pdo->query("SHOW TABLES LIKE 'payment_settings'");
            $table_exists = $result->rowCount() > 0;
            
            if ($table_exists) {
                $columns = $pdo->query("SHOW COLUMNS FROM payment_settings")->fetchAll();
                $column_names = array_column($columns, 'Field');
                $has_correct_structure = in_array('setting_name', $column_names) && in_array('setting_value', $column_names);
            }
        } catch (PDOException $e) {
            $table_exists = false;
        }
        
        if (!$table_exists || !$has_correct_structure) {
            $error_message = "Cannot update settings: Payment settings table is missing or has incorrect structure. Please <a href='setup-donations.php' style='color: #62a92b;'>run the database setup script</a> first.";
        } else {
            // Update settings in database
            $settings = [
                'piprapay_api_key' => $api_key,
                'piprapay_base_url' => $base_url,
                'piprapay_create_endpoint' => $create_endpoint,
                'piprapay_verify_endpoint' => $verify_endpoint
            ];
            
            $stmt = $pdo->prepare("INSERT INTO payment_settings (setting_name, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            
            foreach ($settings as $name => $value) {
                $stmt->execute([$name, $value]);
            }
            
            // Update configuration class
            DonationConfig::updateApiSettings($api_key, $base_url, $create_endpoint, $verify_endpoint);
            
            $success_message = "Payment settings updated successfully!";
        }
        
    } catch (Exception $e) {
        $error_message = "Error updating settings: " . $e->getMessage();
    }
}

// Get current settings
try {
    // First check if payment_settings table exists and has correct structure
    $table_exists = false;
    $has_correct_structure = false;
    
    try {
        $result = $pdo->query("SHOW TABLES LIKE 'payment_settings'");
        $table_exists = $result->rowCount() > 0;
        
        if ($table_exists) {
            // Check if the table has the correct structure
            $columns = $pdo->query("SHOW COLUMNS FROM payment_settings")->fetchAll();
            $column_names = array_column($columns, 'Field');
            $has_correct_structure = in_array('setting_name', $column_names) && in_array('setting_value', $column_names);
        }
    } catch (PDOException $e) {
        $table_exists = false;
    }
    
    $current_settings = [];
    
    if ($table_exists && $has_correct_structure) {
        // Table exists with correct structure, load settings
        $stmt = $pdo->query("SELECT setting_name, setting_value FROM payment_settings WHERE setting_name LIKE 'piprapay_%'");
        while ($row = $stmt->fetch()) {
            $current_settings[$row['setting_name']] = $row['setting_value'];
        }
    } else {
        // Table doesn't exist or has wrong structure
        if ($table_exists && !$has_correct_structure) {
            $error_message = "Payment settings table exists but has incorrect structure. Please <a href='setup-donations.php' style='color: #62a92b;'>run the database setup script</a> to fix this issue.";
        } elseif (!$table_exists) {
            $error_message = "Payment settings table not found. Please <a href='setup-donations.php' style='color: #62a92b;'>run the database setup script</a> first.";
        }
    }
    
} catch (Exception $e) {
    $error_message = "Error loading settings: " . $e->getMessage();
    $current_settings = [];
}

// Use default values if not found in database
$current_api_key = $current_settings['piprapay_api_key'] ?? DonationConfig::$piprapay_api_key;
$current_base_url = $current_settings['piprapay_base_url'] ?? DonationConfig::$piprapay_base_url;
$current_create_endpoint = $current_settings['piprapay_create_endpoint'] ?? DonationConfig::$piprapay_create_charge_endpoint;
$current_verify_endpoint = $current_settings['piprapay_verify_endpoint'] ?? DonationConfig::$piprapay_verify_payment_endpoint;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Settings - Admin</title>
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
            max-width: 800px;
            margin: 50px auto;
        }
        h1 {
            color: #62a92b;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #ffffff;
            font-weight: bold;
        }
        input[type="text"], input[type="url"] {
            width: 100%;
            padding: 12px;
            background-color: #2c2f33;
            color: #ffffff;
            border: 1px solid #444;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="text"]:focus, input[type="url"]:focus {
            border-color: #62a92b;
            outline: none;
        }
        .button {
            background-color: #62a92b;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
            display: inline-block;
            margin-right: 10px;
        }
        .button:hover {
            background-color: #4e8b1f;
        }
        .button.secondary {
            background-color: #6c757d;
        }
        .button.secondary:hover {
            background-color: #5a6268;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f1b0b7;
        }
        .form-section {
            background-color: #2c2f33;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .section-title {
            color: #62a92b;
            margin-bottom: 20px;
            font-size: 1.2em;
        }
        .help-text {
            font-size: 14px;
            color: #adb5bd;
            margin-top: 5px;
        }
        .nav-links {
            text-align: center;
            margin-bottom: 30px;
        }
        .nav-links a {
            color: #62a92b;
            text-decoration: none;
            margin: 0 15px;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Payment Settings Management</h1>
        
        <div class="nav-links">
            <a href="dashboard.php">← Back to Dashboard</a>
            <a href="donation-dashboard.php">Donation Dashboard</a>
        </div>

        <?php if ($success_message): ?>
            <div class="alert success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-section">
                <h3 class="section-title">Piprapay API Configuration</h3>
                
                <div class="form-group">
                    <label for="api_key">API Key</label>
                    <input type="text" id="api_key" name="api_key" value="<?php echo htmlspecialchars($current_api_key); ?>" required>
                    <div class="help-text">Your Piprapay API key for authentication</div>
                </div>

                <div class="form-group">
                    <label for="base_url">Base URL</label>
                    <input type="url" id="base_url" name="base_url" value="<?php echo htmlspecialchars($current_base_url); ?>" required>
                    <div class="help-text">Root API endpoint (e.g., https://payment.solveez.com/api)</div>
                </div>

                <div class="form-group">
                    <label for="create_endpoint">Create Charge Endpoint</label>
                    <input type="url" id="create_endpoint" name="create_endpoint" value="<?php echo htmlspecialchars($current_create_endpoint); ?>" required>
                    <div class="help-text">Full URL for creating payment charges</div>
                </div>

                <div class="form-group">
                    <label for="verify_endpoint">Verify Payment Endpoint</label>
                    <input type="url" id="verify_endpoint" name="verify_endpoint" value="<?php echo htmlspecialchars($current_verify_endpoint); ?>" required>
                    <div class="help-text">Full URL for verifying payment status</div>
                </div>
            </div>

            <div style="text-align: center;">
                <button type="submit" class="button">Update Settings</button>
                <a href="dashboard.php" class="button secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
