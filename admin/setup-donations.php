<?php
// Database setup for donations
require_once '../config/db_config.php';

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Create donations table
    $create_table_sql = "
    CREATE TABLE IF NOT EXISTS donations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reference_id VARCHAR(255) UNIQUE NOT NULL,
        payment_id VARCHAR(255),
        amount DECIMAL(10, 2) NOT NULL,
        currency VARCHAR(3) DEFAULT 'USD',
        donor_name VARCHAR(255) DEFAULT 'Anonymous',
        donor_email VARCHAR(255),
        message TEXT,
        status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
        verification_data JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        verified_at TIMESTAMP NULL,
        INDEX idx_reference_id (reference_id),
        INDEX idx_payment_id (payment_id),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($create_table_sql);
    echo "✓ Donations table created successfully!\n";

    // Create payment_settings table for admin API management
    $create_settings_table_sql = "
    CREATE TABLE IF NOT EXISTS payment_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_name VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($create_settings_table_sql);
    echo "✓ Payment settings table created successfully!\n";

    // Insert default payment settings
    $default_settings = [
        ['piprapay_api_key', '1582434874687127321a07912670681911661600709687127321a07e1465171749'],
        ['piprapay_base_url', 'https://payment.solveez.com/api'],
        ['piprapay_create_endpoint', 'https://payment.solveez.com/api/create-charge'],
        ['piprapay_verify_endpoint', 'https://payment.solveez.com/api/verify-payments']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO payment_settings (setting_name, setting_value) VALUES (?, ?)");
    
    foreach ($default_settings as $setting) {
        $stmt->execute($setting);
    }

    echo "✓ Default payment settings inserted successfully!\n";
    echo "✓ Database setup completed!\n\n";

    // Show table structure
    echo "Donations table structure:\n";
    $result = $pdo->query("DESCRIBE donations");
    while ($row = $result->fetch()) {
        echo "- {$row['Field']}: {$row['Type']}\n";
    }

} catch (PDOException $e) {
    echo "❌ Database setup failed: " . $e->getMessage() . "\n";
}
?>
