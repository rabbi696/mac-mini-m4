<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../config/db_config.php';
require_once '../config/donation_config.php';

// Pagination logic
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

try {
    // Get donations with pagination
    $stmt = $pdo->prepare("
        SELECT id, reference_id, amount, currency, donor_name, donor_email, 
               status, created_at, verified_at 
        FROM donations 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $donations = $stmt->fetchAll();

    // Get total count for pagination
    $total_stmt = $pdo->query("SELECT COUNT(*) as count FROM donations");
    $total_donations = $total_stmt->fetch()['count'];
    $total_pages = ceil($total_donations / $limit);

    // Get summary statistics
    $stats_stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_count,
            SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_completed,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
            COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count,
            COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count
        FROM donations
    ");
    $stats = $stats_stmt->fetch();

} catch (Exception $e) {
    $error_message = "Error loading donations: " . $e->getMessage();
    $donations = [];
    $stats = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Dashboard - Admin</title>
    <style>
        body {
            background-color: #212529;
            color: #ffffff;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 95%;
            margin: 20px auto;
        }
        h1 {
            color: #62a92b;
            text-align: center;
            margin-bottom: 30px;
        }
        .nav-links {
            text-align: center;
            margin-bottom: 30px;
        }
        .nav-links a {
            color: #62a92b;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: #2c2f33;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #62a92b;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #adb5bd;
            font-size: 0.9em;
        }
        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #2c2f33;
        }
        .dashboard-table th, .dashboard-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #444;
        }
        .dashboard-table th {
            background-color: #333;
            color: #62a92b;
            font-weight: bold;
        }
        .dashboard-table tr:hover {
            background-color: #3b3e45;
        }
        .dashboard-table td {
            color: #bbb;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-completed {
            background-color: #28a745;
            color: white;
        }
        .status-pending {
            background-color: #ffc107;
            color: black;
        }
        .status-failed {
            background-color: #dc3545;
            color: white;
        }
        .status-cancelled {
            background-color: #6c757d;
            color: white;
        }
        .pagination {
            text-align: center;
            margin-top: 30px;
        }
        .pagination a {
            color: #62a92b;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #444;
            margin: 0 4px;
            border-radius: 4px;
        }
        .pagination a:hover {
            background-color: #444;
        }
        .pagination a.active {
            background-color: #62a92b;
            color: white;
        }
        .amount {
            font-weight: bold;
            color: #28a745;
        }
        .reference-id {
            font-family: monospace;
            font-size: 0.9em;
            color: #adb5bd;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f1b0b7;
        }
        .filters {
            background-color: #2c2f33;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        .filters select {
            background-color: #444;
            color: white;
            border: 1px solid #666;
            border-radius: 4px;
            padding: 8px 12px;
        }
        .button {
            background-color: #62a92b;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #4e8b1f;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Donation Dashboard</h1>
        
        <div class="nav-links">
            <a href="dashboard.php">← Main Dashboard</a>
            <a href="manage-payment-settings.php">Payment Settings</a>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">$<?php echo number_format($stats['total_completed'] ?? 0, 2); ?></div>
                <div class="stat-label">Total Raised</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['completed_count'] ?? 0; ?></div>
                <div class="stat-label">Completed Donations</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['pending_count'] ?? 0; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total_count'] ?? 0; ?></div>
                <div class="stat-label">Total Donations</div>
            </div>
        </div>

        <!-- Donations Table -->
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Reference ID</th>
                    <th>Donor</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Verified</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($donations)): ?>
                    <?php foreach ($donations as $donation): ?>
                        <tr>
                            <td>
                                <div class="reference-id"><?php echo htmlspecialchars($donation['reference_id']); ?></div>
                            </td>
                            <td>
                                <div><strong><?php echo htmlspecialchars($donation['donor_name']); ?></strong></div>
                                <?php if ($donation['donor_email']): ?>
                                    <div style="font-size: 0.85em; color: #adb5bd;"><?php echo htmlspecialchars($donation['donor_email']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="amount">
                                $<?php echo number_format($donation['amount'], 2); ?> <?php echo htmlspecialchars($donation['currency']); ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo htmlspecialchars($donation['status']); ?>">
                                    <?php echo htmlspecialchars($donation['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo date('M j, Y g:i A', strtotime($donation['created_at'])); ?>
                            </td>
                            <td>
                                <?php if ($donation['verified_at']): ?>
                                    <?php echo date('M j, Y g:i A', strtotime($donation['verified_at'])); ?>
                                <?php else: ?>
                                    <span style="color: #6c757d;">Not verified</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #adb5bd; padding: 40px;">
                            No donations found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="donation-dashboard.php?page=<?php echo $i; ?>" <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
