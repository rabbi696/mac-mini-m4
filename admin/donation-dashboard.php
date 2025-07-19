<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../config/db_config.php';
require_once '../config/donation_config.php';

// Filter and search logic
$status_filter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
$search_query = isset($_GET['search']) && $_GET['search'] !== '' ? trim($_GET['search']) : null;
$date_from = isset($_GET['date_from']) && $_GET['date_from'] !== '' ? $_GET['date_from'] : null;
$date_to = isset($_GET['date_to']) && $_GET['date_to'] !== '' ? $_GET['date_to'] : null;

// Pagination logic
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

try {
    // Build WHERE clause for filters
    $where_conditions = [];
    $params = [];
    
    if ($status_filter) {
        $where_conditions[] = "status = ?";
        $params[] = $status_filter;
    }
    
    if ($search_query) {
        $where_conditions[] = "(donor_name LIKE ? OR donor_email LIKE ? OR reference_id LIKE ?)";
        $search_param = "%" . $search_query . "%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if ($date_from) {
        $where_conditions[] = "DATE(created_at) >= ?";
        $params[] = $date_from;
    }
    
    if ($date_to) {
        $where_conditions[] = "DATE(created_at) <= ?";
        $params[] = $date_to;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Get donations with filters and pagination
    $sql = "
        SELECT id, reference_id, payment_id, amount, currency, donor_name, donor_email, 
               message, status, created_at, verified_at 
        FROM donations 
        {$where_clause}
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $donations = $stmt->fetchAll();

    // Get total count for pagination with filters
    $count_sql = "SELECT COUNT(*) as count FROM donations {$where_clause}";
    $count_stmt = $pdo->prepare($count_sql);
    $count_params = array_slice($params, 0, -2); // Remove limit and offset
    $count_stmt->execute($count_params);
    $total_donations = $count_stmt->fetch()['count'];
    $total_pages = ceil($total_donations / $limit);

    // Get summary statistics
    $stats_stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_count,
            SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_completed,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
            COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count,
            COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count,
            AVG(CASE WHEN status = 'completed' THEN amount END) as avg_donation
        FROM donations
    ");
    $stats = $stats_stmt->fetch();
    
    // Get recent donations for quick stats
    $recent_stmt = $pdo->query("
        SELECT COUNT(*) as recent_count,
               SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as recent_amount
        FROM donations 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $recent_stats = $recent_stmt->fetch();

} catch (Exception $e) {
    $error_message = "Error loading donations: " . $e->getMessage();
    $donations = [];
    $stats = [];
    $recent_stats = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Dashboard - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            margin: 50px auto;
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
            background-color: #62a92b;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            margin: 0 10px;
        }
        .nav-links a:hover {
            background-color: #4e8b1f;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background-color: #333;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #62a92b;
            margin-bottom: 10px;
        }
        .stat-label {
            color: #bbb;
            font-size: 0.9rem;
        }
        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .dashboard-table th, .dashboard-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #444;
        }
        .dashboard-table th {
            background-color: #333;
            color: #62a92b;
        }
        .dashboard-table tr:nth-child(even) {
            background-color: #2c2f33;
        }
        .dashboard-table tr:hover {
            background-color: #3b3e45;
        }
        .dashboard-table td {
            color: #bbb;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-completed {
            background-color: #10b981;
            color: white;
        }
        .status-pending {
            background-color: #fbbf24;
            color: #1f2937;
        }
        .status-failed {
            background-color: #ef4444;
            color: white;
        }
        .status-cancelled {
            background-color: #6b7280;
            color: white;
        }
        .reference-id {
            font-family: monospace;
            font-size: 0.85rem;
            background-color: #444;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .amount {
            font-weight: bold;
            color: #62a92b;
            font-size: 1.1rem;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            color: #62a92b;
            margin: 0 5px;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #444;
        }
        .pagination a:hover, .pagination a.active {
            background-color: #62a92b;
            color: white;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
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
                <div class="stat-value">৳<?php echo number_format($stats['total_completed'] ?? 0, 2); ?></div>
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
                                ৳<?php echo number_format($donation['amount'], 2); ?> <?php echo htmlspecialchars($donation['currency']); ?>
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
