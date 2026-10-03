<?php
session_start();
include("db.php");

// Check if admin is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check database connection
if(!isset($conn) || !$conn) {
    die("Database connection failed. Please check your configuration.");
}

// ========================================
// DATABASE FUNCTIONS
// ========================================

// Get all transactions with advanced filtering
function getTransactions($conn, $limit = 20, $offset = 0, $filters = []) {
    $sql = "SELECT t.*, 
                   u.name as user_name, 
                   u.Email as user_email, 
                   u.mobile as user_mobile,
                   u.role as user_role
            FROM wallet_transactions t 
            LEFT JOIN contact u ON t.user_id = u.id 
            WHERE 1=1";
    
    if(!empty($filters['type'])) {
        $type = mysqli_real_escape_string($conn, $filters['type']);
        $sql .= " AND t.type = '$type'";
    }
    
    if(!empty($filters['status'])) {
        $status = mysqli_real_escape_string($conn, $filters['status']);
        $sql .= " AND t.status = '$status'";
    }
    
    if(!empty($filters['date_from'])) {
        $date_from = mysqli_real_escape_string($conn, $filters['date_from']);
        $sql .= " AND DATE(t.created_at) >= '$date_from'";
    }
    
    if(!empty($filters['date_to'])) {
        $date_to = mysqli_real_escape_string($conn, $filters['date_to']);
        $sql .= " AND DATE(t.created_at) <= '$date_to'";
    }
    
    if(!empty($filters['search'])) {
        $search = mysqli_real_escape_string($conn, $filters['search']);
        $sql .= " AND (u.name LIKE '%$search%' 
                     OR u.Email LIKE '%$search%' 
                     OR u.mobile LIKE '%$search%' 
                     OR t.id LIKE '%$search%'
                     OR t.method LIKE '%$search%'
                     OR t.reference LIKE '%$search%')";
    }
    
    $sql .= " ORDER BY t.id DESC LIMIT $limit OFFSET $offset";
    
    $result = mysqli_query($conn, $sql);
    return $result;
}

// Get total transactions count
function getTotalTransactions($conn, $filters = []) {
    $sql = "SELECT COUNT(*) as total 
            FROM wallet_transactions t 
            LEFT JOIN contact u ON t.user_id = u.id 
            WHERE 1=1";
    
    if(!empty($filters['type'])) {
        $type = mysqli_real_escape_string($conn, $filters['type']);
        $sql .= " AND t.type = '$type'";
    }
    
    if(!empty($filters['status'])) {
        $status = mysqli_real_escape_string($conn, $filters['status']);
        $sql .= " AND t.status = '$status'";
    }
    
    if(!empty($filters['date_from'])) {
        $date_from = mysqli_real_escape_string($conn, $filters['date_from']);
        $sql .= " AND DATE(t.created_at) >= '$date_from'";
    }
    
    if(!empty($filters['date_to'])) {
        $date_to = mysqli_real_escape_string($conn, $filters['date_to']);
        $sql .= " AND DATE(t.created_at) <= '$date_to'";
    }
    
    if(!empty($filters['search'])) {
        $search = mysqli_real_escape_string($conn, $filters['search']);
        $sql .= " AND (u.name LIKE '%$search%' 
                     OR u.Email LIKE '%$search%' 
                     OR u.mobile LIKE '%$search%' 
                     OR t.id LIKE '%$search%')";
    }
    
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

// Get comprehensive transaction statistics
function getTransactionStats($conn) {
    $stats = [
        'total' => 0,
        'deposits' => 0,
        'withdrawals' => 0,
        'total_amount' => 0,
        'pending' => 0,
        'completed' => 0,
        'failed' => 0,
        'today' => 0,
        'this_week' => 0,
        'this_month' => 0
    ];
    
    $sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN type = 'deposit' AND status = 'completed' THEN 1 ELSE 0 END) as deposits,
            SUM(CASE WHEN type = 'withdrawal' AND status = 'completed' THEN 1 ELSE 0 END) as withdrawals,
            SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_amount,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
            SUM(CASE WHEN YEARWEEK(created_at) = YEARWEEK(CURDATE()) THEN 1 ELSE 0 END) as this_week,
            SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as this_month
            FROM wallet_transactions";
    
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $stats['total'] = $row['total'] ?? 0;
        $stats['deposits'] = $row['deposits'] ?? 0;
        $stats['withdrawals'] = $row['withdrawals'] ?? 0;
        $stats['total_amount'] = $row['total_amount'] ?? 0;
        $stats['pending'] = $row['pending'] ?? 0;
        $stats['completed'] = $row['completed'] ?? 0;
        $stats['failed'] = $row['failed'] ?? 0;
        $stats['today'] = $row['today'] ?? 0;
        $stats['this_week'] = $row['this_week'] ?? 0;
        $stats['this_month'] = $row['this_month'] ?? 0;
    }
    
    return $stats;
}

// Get transaction by ID with full details
function getTransactionById($conn, $id) {
    $id = mysqli_real_escape_string($conn, $id);
    $sql = "SELECT t.*, 
                   u.name as user_name, 
                   u.Email as user_email, 
                   u.mobile as user_mobile,
                   u.role as user_role,
                   u.created_at as user_joined
            FROM wallet_transactions t 
            LEFT JOIN contact u ON t.user_id = u.id 
            WHERE t.id = '$id'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

// Update transaction status
function updateTransactionStatus($conn, $id, $status, $note = '') {
    $id = mysqli_real_escape_string($conn, $id);
    $status = mysqli_real_escape_string($conn, $status);
    $note = mysqli_real_escape_string($conn, $note);
    
    $sql = "UPDATE wallet_transactions 
            SET status = '$status', 
                admin_note = '$note',
                updated_at = NOW() 
            WHERE id = '$id'";
    return mysqli_query($conn, $sql);
}

// Delete transaction
function deleteTransaction($conn, $id) {
    $id = mysqli_real_escape_string($conn, $id);
    $sql = "DELETE FROM wallet_transactions WHERE id = '$id'";
    return mysqli_query($conn, $sql);
}

// Get monthly transaction data for chart
function getMonthlyData($conn, $year = null) {
    if(!$year) $year = date('Y');
    $year = mysqli_real_escape_string($conn, $year);
    
    $sql = "SELECT 
                MONTH(created_at) as month,
                COUNT(*) as total,
                SUM(CASE WHEN type = 'deposit' AND status = 'completed' THEN 1 ELSE 0 END) as deposits,
                SUM(CASE WHEN type = 'withdrawal' AND status = 'completed' THEN 1 ELSE 0 END) as withdrawals,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as amount
            FROM wallet_transactions 
            WHERE YEAR(created_at) = '$year'
            GROUP BY MONTH(created_at)
            ORDER BY MONTH(created_at)";
    
    $result = mysqli_query($conn, $sql);
    $data = array_fill(0, 12, ['total' => 0, 'deposits' => 0, 'withdrawals' => 0, 'amount' => 0]);
    
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $idx = $row['month'] - 1;
            $data[$idx] = [
                'total' => $row['total'],
                'deposits' => $row['deposits'],
                'withdrawals' => $row['withdrawals'],
                'amount' => $row['amount']
            ];
        }
    }
    
    return $data;
}

// ========================================
// PROCESS REQUESTS
// ========================================

// Get filters from request
$filters = [
    'type' => isset($_GET['type']) ? $_GET['type'] : '',
    'status' => isset($_GET['status']) ? $_GET['status'] : '',
    'search' => isset($_GET['search']) ? $_GET['search'] : '',
    'date_from' => isset($_GET['date_from']) ? $_GET['date_from'] : '',
    'date_to' => isset($_GET['date_to']) ? $_GET['date_to'] : ''
];

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get data
$transactions = getTransactions($conn, $limit, $offset, $filters);
$total_transactions = getTotalTransactions($conn, $filters);
$stats = getTransactionStats($conn);
$monthly_data = getMonthlyData($conn);

// Handle POST requests
$success_msg = '';
$error_msg = '';
$txn_details = null;

if(isset($_POST['update_status'])) {
    $txn_id = $_POST['txn_id'];
    $status = $_POST['status'];
    $note = isset($_POST['admin_note']) ? $_POST['admin_note'] : '';
    
    if(updateTransactionStatus($conn, $txn_id, $status, $note)) {
        $success_msg = 'Transaction status updated successfully!';
    } else {
        $error_msg = 'Failed to update transaction status!';
    }
}

if(isset($_POST['delete_transaction'])) {
    $txn_id = $_POST['txn_id'];
    
    if(deleteTransaction($conn, $txn_id)) {
        $success_msg = 'Transaction deleted successfully!';
    } else {
        $error_msg = 'Failed to delete transaction!';
    }
}

// Get transaction details for AJAX (JSON response)
if(isset($_GET['ajax']) && $_GET['ajax'] == '1' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $txn = getTransactionById($conn, $id);
    if($txn) {
        echo json_encode(['success' => true, 'transaction' => $txn]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Transaction not found']);
    }
    exit();
}

// Pagination
$total_pages = ceil($total_transactions / $limit);

// Months for chart
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Build query string for pagination
function buildQueryString($params) {
    $parts = [];
    foreach($params as $key => $value) {
        if($value !== '') {
            $parts[] = urlencode($key) . '=' . urlencode($value);
        }
    }
    return implode('&', $parts);
}
$query_string = buildQueryString($filters);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Management | Thunder X Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            --primary-gradient: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            --success: #22c55e;
            --success-light: #dcfce7;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --purple: #8b5cf6;
            --purple-light: #ede9fe;
            --pink: #ec4899;
            --pink-light: #fce7f3;
            --cyan: #06b6d4;
            --cyan-light: #cffafe;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.1);
            --shadow-xl: 0 20px 60px rgba(0,0,0,0.15);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-2xl: 24px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: var(--gray-100);
            padding: 20px;
            color: var(--gray-800);
        }

        .container {
            max-width: 1440px;
            margin: 0 auto;
        }

        .navbar {
            background: #ffffff;
            border-radius: var(--radius-xl);
            padding: 14px 28px;
            box-shadow: var(--shadow-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 16px;
            border: 1px solid rgba(255,255,255,0.8);
        }

        .navbar .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar .logo .logo-icon {
            width: 42px;
            height: 42px;
            background: var(--primary-gradient);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .navbar .logo h2 {
            font-size: 22px;
            font-weight: 800;
            color: var(--gray-900);
        }

        .navbar .logo h2 span {
            color: var(--primary);
        }

        .navbar .nav-links {
            display: flex;
            gap: 4px;
            align-items: center;
            flex-wrap: wrap;
        }

        .navbar .nav-links a {
            text-decoration: none;
            color: var(--gray-500);
            font-weight: 500;
            font-size: 13px;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navbar .nav-links a:hover {
            background: var(--gray-100);
            color: var(--primary);
        }

        .navbar .nav-links a.active {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.35);
        }

        .navbar .nav-links a.logout-btn {
            background: var(--danger);
            color: #fff;
        }

        .navbar .nav-links a.logout-btn:hover {
            background: #dc2626;
        }

        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .navbar .user-info .user-name {
            font-weight: 600;
            color: var(--gray-800);
            font-size: 14px;
        }

        .navbar .user-info .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2.5px solid var(--primary);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 28px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header h1 i {
            color: var(--primary);
        }

        .page-header p {
            color: var(--gray-500);
            font-size: 15px;
            margin-top: 4px;
        }

        .page-header .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(37, 99, 235, 0.4);
        }

        .btn-success {
            background: var(--success);
            color: #fff;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(34, 197, 94, 0.4);
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(239, 68, 68, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: var(--gray-600);
            border: 2px solid var(--gray-200);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-sm {
            padding: 6px 14px;
            font-size: 12px;
        }

        .btn-xs {
            padding: 4px 10px;
            font-size: 11px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: var(--radius-md);
            padding: 16px 18px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0,0,0,0.04);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card .stat-icon {
            font-size: 20px;
            margin-bottom: 4px;
        }

        .stat-card .stat-number {
            font-size: 22px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .stat-card .stat-label {
            font-size: 11px;
            color: var(--gray-500);
            font-weight: 500;
        }

        .stat-card.green::before { background: var(--success); }
        .stat-card.blue::before { background: var(--primary); }
        .stat-card.orange::before { background: var(--warning); }
        .stat-card.red::before { background: var(--danger); }
        .stat-card.purple::before { background: var(--purple); }
        .stat-card.pink::before { background: var(--pink); }
        .stat-card.cyan::before { background: var(--cyan); }

        .stat-card .stat-icon.green { color: var(--success); }
        .stat-card .stat-icon.blue { color: var(--primary); }
        .stat-card .stat-icon.orange { color: var(--warning); }
        .stat-card .stat-icon.red { color: var(--danger); }
        .stat-card .stat-icon.purple { color: var(--purple); }
        .stat-card .stat-icon.pink { color: var(--pink); }
        .stat-card .stat-icon.cyan { color: var(--cyan); }

        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 28px;
        }

        .chart-box {
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0,0,0,0.04);
        }

        .chart-box h3 {
            font-size: 16px;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-box h3 i {
            color: var(--primary);
        }

        .chart-box canvas {
            width: 100% !important;
            height: 280px !important;
        }

        .filters-section {
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0,0,0,0.04);
        }

        .filters-form {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        .filter-group {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group label {
            font-weight: 500;
            font-size: 13px;
            color: var(--gray-600);
            white-space: nowrap;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 14px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 13px;
            background: var(--gray-50);
            transition: var(--transition);
            color: var(--gray-800);
            min-width: 120px;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .filter-group input[type="date"] {
            min-width: 140px;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            margin-left: auto;
        }

        .table-container {
            background: #ffffff;
            border-radius: var(--radius-xl);
            padding: 24px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0,0,0,0.04);
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .table-header .info {
            font-size: 14px;
            color: var(--gray-500);
        }

        .table-header .info strong {
            color: var(--gray-800);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: var(--gray-50);
            color: var(--gray-600);
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--gray-200);
            white-space: nowrap;
        }

        table td {
            padding: 12px 16px;
            color: var(--gray-700);
            font-size: 14px;
            border-bottom: 1px solid var(--gray-100);
            vertical-align: middle;
        }

        table tbody tr {
            transition: var(--transition);
            cursor: pointer;
        }

        table tbody tr:hover {
            background: var(--gray-50);
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-success {
            background: var(--success-light);
            color: #166534;
        }

        .badge-warning {
            background: var(--warning-light);
            color: #92400e;
        }

        .badge-danger {
            background: var(--danger-light);
            color: #991b1b;
        }

        .badge-primary {
            background: var(--primary-light);
            color: var(--primary-dark);
        }

        .text-success { color: var(--success); }
        .text-danger { color: var(--danger); }
        .text-muted { color: var(--gray-400); }

        .no-transactions {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray-400);
        }

        .no-transactions i {
            font-size: 48px;
            opacity: 0.3;
            display: block;
            margin-bottom: 12px;
        }

        .action-btns {
            display: flex;
            gap: 4px;
            justify-content: center;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-200);
            text-decoration: none;
            color: var(--gray-600);
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
        }

        .pagination a:hover {
            background: var(--gray-100);
            border-color: var(--primary);
            color: var(--primary);
        }

        .pagination .active {
            background: var(--primary-gradient);
            color: #fff;
            border-color: var(--primary);
        }

        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ========================================
                   BEAUTIFUL MODAL POPUP
                ======================================== */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
            backdrop-filter: blur(8px);
            animation: fadeIn 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal {
            background: #ffffff;
            border-radius: var(--radius-2xl);
            max-width: 700px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-xl);
            animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
        }

        @keyframes slideUp {
            from { transform: translateY(40px) scale(0.95); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        .modal::-webkit-scrollbar {
            width: 4px;
        }

        .modal::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: 2px;
        }

        .modal::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 2px;
        }

        .modal-header {
            background: var(--primary-gradient);
            padding: 24px 32px;
            border-radius: var(--radius-2xl) var(--radius-2xl) 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modal-header h3 i {
            font-size: 22px;
        }

        .modal-header .close-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            color: #fff;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-header .close-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 28px 32px;
        }

        .modal-body .status-badge-large {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .modal-body .status-badge-large.completed {
            background: var(--success-light);
            color: #166534;
        }

        .modal-body .status-badge-large.pending {
            background: var(--warning-light);
            color: #92400e;
        }

        .modal-body .status-badge-large.failed {
            background: var(--danger-light);
            color: #991b1b;
        }

        .modal-body .status-badge-large i {
            font-size: 16px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .detail-item {
            background: var(--gray-50);
            padding: 14px 18px;
            border-radius: var(--radius-md);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .detail-item:hover {
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.08);
        }

        .detail-item .label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }

        .detail-item .label i {
            font-size: 12px;
        }

        .detail-item .value {
            font-size: 15px;
            font-weight: 600;
            color: var(--gray-900);
            word-break: break-word;
        }

        .detail-item .value.amount-positive {
            color: var(--success);
            font-size: 18px;
        }

        .detail-item .value.amount-negative {
            color: var(--danger);
            font-size: 18px;
        }

        .detail-item.full-width {
            grid-column: 1 / -1;
        }

        .account-details-section {
            background: var(--gray-50);
            border-radius: var(--radius-md);
            padding: 16px 18px;
            margin-bottom: 20px;
            border: 1px solid var(--gray-200);
        }

        .account-details-section .title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gray-500);
            margin-bottom: 6px;
        }

        .account-details-section .content {
            font-size: 14px;
            color: var(--gray-800);
            word-break: break-all;
        }

        .admin-note-section {
            background: var(--primary-light);
            border-radius: var(--radius-md);
            padding: 16px 18px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary);
        }

        .admin-note-section .title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--primary-dark);
            margin-bottom: 4px;
        }

        .admin-note-section .content {
            font-size: 14px;
            color: var(--gray-700);
        }

        .status-update {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid var(--gray-200);
        }

        .status-update h4 {
            font-size: 15px;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-update form {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .status-update select {
            padding: 10px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            background: var(--gray-50);
            flex: 1;
            min-width: 150px;
            transition: var(--transition);
        }

        .status-update select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .status-update textarea {
            width: 100%;
            padding: 10px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            background: var(--gray-50);
            resize: vertical;
            min-height: 60px;
            font-family: inherit;
            transition: var(--transition);
            margin-top: 8px;
        }

        .status-update textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .status-update .btn-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            width: 100%;
            margin-top: 8px;
        }

        .alert {
            padding: 12px 18px;
            border-radius: var(--radius-sm);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: var(--success-light);
            color: #166534;
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background: var(--danger-light);
            color: #991b1b;
            border-left: 4px solid var(--danger);
        }

        .alert i {
            font-size: 18px;
        }

        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .filter-actions {
                margin-left: 0;
                width: 100%;
            }
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 12px;
            }
            .navbar {
                flex-direction: column;
                align-items: stretch;
                padding: 14px 18px;
            }
            .navbar .nav-links {
                justify-content: center;
            }
            .navbar .nav-links a {
                font-size: 12px;
                padding: 6px 12px;
            }
            .navbar .user-info {
                justify-content: center;
            }
            .page-header h1 {
                font-size: 22px;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            .stat-card .stat-number {
                font-size: 18px;
            }
            .filters-section {
                padding: 16px;
            }
            .filters-form {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-group {
                flex-wrap: wrap;
            }
            .filter-group select,
            .filter-group input {
                flex: 1;
                min-width: 100px;
            }
            .table-container {
                padding: 16px;
            }
            table {
                min-width: 700px;
            }
            .modal {
                margin: 10px;
                max-height: 95vh;
            }
            .modal-header {
                padding: 18px 20px;
            }
            .modal-body {
                padding: 20px;
            }
            .pagination a,
            .pagination span {
                padding: 6px 12px;
                font-size: 12px;
            }
            .status-update form {
                flex-direction: column;
            }
            .status-update select {
                width: 100%;
            }
            .status-update .btn-group {
                flex-direction: column;
            }
            .status-update .btn-group .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
            .stat-card {
                padding: 12px 14px;
            }
            .stat-card .stat-number {
                font-size: 16px;
            }
            .stat-card .stat-label {
                font-size: 10px;
            }
            .navbar .nav-links a {
                font-size: 11px;
                padding: 4px 10px;
            }
            .chart-box canvas {
                height: 200px !important;
            }
            .modal-header h3 {
                font-size: 16px;
            }
            .detail-item .value {
                font-size: 13px;
            }
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --gray-50: #1e293b;
                --gray-100: #0f172a;
                --gray-200: #334155;
                --gray-800: #f1f5f9;
                --gray-900: #ffffff;
            }
            body {
                background: #0f172a;
            }
            .navbar,
            .stat-card,
            .filters-section,
            .table-container,
            .chart-box,
            .modal {
                background: #1e293b;
                border-color: #334155;
            }
            .navbar .logo h2 {
                color: #f1f5f9;
            }
            .navbar .user-info .user-name {
                color: #f1f5f9;
            }
            .navbar .nav-links a {
                color: #94a3b8;
            }
            .navbar .nav-links a:hover {
                background: #334155;
                color: #60a5fa;
            }
            .page-header h1 {
                color: #f1f5f9;
            }
            .stat-card .stat-number {
                color: #f1f5f9;
            }
            .filters-section select,
            .filters-section input {
                background: #334155;
                border-color: #475569;
                color: #f1f5f9;
            }
            .filters-section select:focus,
            .filters-section input:focus {
                background: #1e293b;
                border-color: var(--primary);
            }
            table th {
                background: #334155;
                color: #94a3b8;
                border-bottom-color: #475569;
            }
            table td {
                color: #e2e8f0;
                border-bottom-color: #334155;
            }
            table tbody tr:hover {
                background: #334155;
            }
            .no-transactions {
                color: #94a3b8;
            }
            .modal {
                background: #1e293b;
            }
            .modal .detail-item {
                background: #334155;
                border-color: #475569;
            }
            .modal .detail-item .value {
                color: #f1f5f9;
            }
            .modal .detail-item .label {
                color: #94a3b8;
            }
            .modal .account-details-section {
                background: #334155;
                border-color: #475569;
            }
            .modal .account-details-section .content {
                color: #f1f5f9;
            }
            .modal .admin-note-section {
                background: #1e3a5f;
                border-left-color: #60a5fa;
            }
            .modal .admin-note-section .content {
                color: #93c5fd;
            }
            .modal .admin-note-section .title {
                color: #60a5fa;
            }
            .modal .status-update {
                border-top-color: #334155;
            }
            .modal .status-update select,
            .modal .status-update textarea {
                background: #334155;
                border-color: #475569;
                color: #f1f5f9;
            }
            .modal .status-update select:focus,
            .modal .status-update textarea:focus {
                background: #1e293b;
                border-color: var(--primary);
            }
            .alert-success {
                background: #064e3b;
                color: #86efac;
            }
            .alert-error {
                background: #7f1d1d;
                color: #fca5a5;
            }
            .badge-success {
                background: #064e3b;
                color: #86efac;
            }
            .badge-warning {
                background: #78350f;
                color: #fbbf24;
            }
            .badge-danger {
                background: #7f1d1d;
                color: #fca5a5;
            }
            .badge-primary {
                background: #1e3a5f;
                color: #60a5fa;
            }
            .pagination a,
            .pagination span {
                background: #334155;
                border-color: #475569;
                color: #94a3b8;
            }
            .pagination a:hover {
                background: #475569;
            }
            .pagination .active {
                background: var(--primary-gradient);
                color: #fff;
            }
            .btn-outline {
                border-color: #475569;
                color: #94a3b8;
            }
            .btn-outline:hover {
                border-color: var(--primary);
                color: #60a5fa;
            }
            .chart-box h3 {
                color: #f1f5f9;
            }
            .modal .status-badge-large.completed {
                background: #064e3b;
                color: #86efac;
            }
            .modal .status-badge-large.pending {
                background: #78350f;
                color: #fbbf24;
            }
            .modal .status-badge-large.failed {
                background: #7f1d1d;
                color: #fca5a5;
            }
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--gray-400);
        }

        .counter-animate {
            animation: counterPop 0.5s ease;
        }

        @keyframes counterPop {
            0% { transform: scale(0.8); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.6s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<div class="container">

    <!-- ===== NAVBAR ===== -->
    <nav class="navbar">
        <div class="logo">
            <div class="logo-icon"><i class="fas fa-cubes"></i></div>
            <h2>Thunder<span>X</span></h2>
        </div>
        
        <div class="nav-links">
            <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="admin.php"><i class="fas fa-users"></i> Users</a>
            <a href="investment.php"><i class="fas fa-wallet"></i> Investments</a>
            <a href="transactions.php" class="active"><i class="fas fa-exchange-alt"></i> Transactions</a>
            <a href="setting.php"><i class="fas fa-cog"></i> Settings</a>
            <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="user-info">
            <span class="user-name">Admin</span>
            <img src="https://ui-avatars.com/api/?name=Admin&background=2563eb&color=fff&size=42&bold=true" alt="Avatar" class="user-avatar">
        </div>
    </nav>

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header">
        <div>
            <h1><i class="fas fa-exchange-alt"></i> Transaction Management</h1>
            <p>Monitor and manage all financial transactions across the platform</p>
        </div>
        <div class="actions">
            <button class="btn btn-primary" onclick="window.location.reload()">
                <i class="fas fa-sync"></i> Refresh
            </button>
            <a href="export_transactions.php" class="btn btn-success">
                <i class="fas fa-download"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- ===== ALERTS ===== -->
    <?php if($success_msg): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <?php if($error_msg): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <!-- ===== STATS CARDS ===== -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-icon blue"><i class="fas fa-exchange-alt"></i></div>
            <div class="stat-number counter-animate"><?php echo number_format($stats['total']); ?></div>
            <div class="stat-label">Total Transactions</div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-arrow-down"></i></div>
            <div class="stat-number counter-animate"><?php echo number_format($stats['deposits']); ?></div>
            <div class="stat-label">Deposits</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-icon orange"><i class="fas fa-arrow-up"></i></div>
            <div class="stat-number counter-animate"><?php echo number_format($stats['withdrawals']); ?></div>
            <div class="stat-label">Withdrawals</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon purple"><i class="fas fa-coins"></i></div>
            <div class="stat-number counter-animate">₹<?php echo number_format($stats['total_amount']); ?></div>
            <div class="stat-label">Total Volume</div>
        </div>
        <div class="stat-card pink">
            <div class="stat-icon pink"><i class="fas fa-clock"></i></div>
            <div class="stat-number counter-animate"><?php echo number_format($stats['pending']); ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card cyan">
            <div class="stat-icon cyan"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-number counter-animate"><?php echo number_format($stats['today']); ?></div>
            <div class="stat-label">Today</div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-number counter-animate"><?php echo number_format($stats['failed']); ?></div>
            <div class="stat-label">Failed</div>
        </div>
    </div>

    <!-- ===== CHARTS ===== -->
    <div class="charts-grid">
        <div class="chart-box">
            <h3><i class="fas fa-chart-bar"></i> Monthly Transaction Overview</h3>
            <canvas id="monthlyChart"></canvas>
        </div>
        <div class="chart-box">
            <h3><i class="fas fa-chart-pie"></i> Transaction Status Distribution</h3>
            <canvas id="distributionChart"></canvas>
        </div>
    </div>

    <!-- ===== FILTERS ===== -->
    <div class="filters-section">
        <form method="GET" action="" class="filters-form" id="filterForm">
            <div class="filter-group">
                <label><i class="fas fa-tag"></i> Type</label>
                <select name="type" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="deposit" <?php echo $filters['type'] == 'deposit' ? 'selected' : ''; ?>>Deposit</option>
                    <option value="withdrawal" <?php echo $filters['type'] == 'withdrawal' ? 'selected' : ''; ?>>Withdrawal</option>
                </select>
            </div>

            <div class="filter-group">
                <label><i class="fas fa-check-circle"></i> Status</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="completed" <?php echo $filters['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="pending" <?php echo $filters['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="failed" <?php echo $filters['status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                </select>
            </div>

            <div class="filter-group">
                <label><i class="fas fa-calendar"></i> From</label>
                <input type="date" name="date_from" value="<?php echo $filters['date_from']; ?>" onchange="this.form.submit()">
            </div>

            <div class="filter-group">
                <label><i class="fas fa-calendar"></i> To</label>
                <input type="date" name="date_to" value="<?php echo $filters['date_to']; ?>" onchange="this.form.submit()">
            </div>

            <div class="filter-group" style="flex:1;min-width:200px;">
                <label><i class="fas fa-search"></i></label>
                <input type="text" name="search" placeholder="Search by user, ID, method..." value="<?php echo htmlspecialchars($filters['search']); ?>" style="flex:1;">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> Filter
                </button>
                <?php if(!empty(array_filter($filters))): ?>
                    <a href="transactions.php" class="btn btn-outline btn-sm">
                        <i class="fas fa-times"></i> Clear All
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ===== TABLE ===== -->
    <div class="table-container">
        <div class="table-header">
            <div class="info">
                <i class="fas fa-list"></i> Showing 
                <strong><?php echo ($offset + 1); ?></strong> to 
                <strong><?php echo min($offset + $limit, $total_transactions); ?></strong> of 
                <strong><?php echo number_format($total_transactions); ?></strong> transactions
            </div>
            <div class="info">
                <i class="fas fa-clock"></i> Last updated: <?php echo date('d M Y, h:i A'); ?>
            </div>
        </div>

        <?php if($transactions && mysqli_num_rows($transactions) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($txn = mysqli_fetch_assoc($transactions)): ?>
                        <tr onclick="openModal(<?php echo $txn['id']; ?>)">
                            <td>
                                <strong>#<?php echo str_pad($txn['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($txn['user_name'] ?? 'N/A'); ?></strong>
                                <?php if(!empty($txn['user_email'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($txn['user_email']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($txn['type'] == 'deposit'): ?>
                                    <span class="text-success"><i class="fas fa-arrow-down"></i> Deposit</span>
                                <?php else: ?>
                                    <span class="text-danger"><i class="fas fa-arrow-up"></i> Withdrawal</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($txn['type'] == 'deposit'): ?>
                                    <span class="text-success">+₹<?php echo number_format($txn['amount'], 2); ?></span>
                                <?php else: ?>
                                    <span class="text-danger">-₹<?php echo number_format($txn['amount'], 2); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><span style="text-transform:capitalize;"><?php echo $txn['method']; ?></span></td>
                            <td>
                                <?php if($txn['status'] == 'completed'): ?>
                                    <span class="badge badge-success">Completed</span>
                                <?php elseif($txn['status'] == 'pending'): ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php elseif($txn['status'] == 'failed'): ?>
                                    <span class="badge badge-danger">Failed</span>
                                <?php else: ?>
                                    <span class="badge badge-primary"><?php echo ucfirst($txn['status']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted" style="font-size:12px;">
                                <?php echo date('d M Y', strtotime($txn['created_at'])); ?>
                                <br><small><?php echo date('h:i A', strtotime($txn['created_at'])); ?></small>
                            </td>
                            <td style="text-align:center;">
                                <div class="action-btns">
                                    <button onclick="event.stopPropagation(); openModal(<?php echo $txn['id']; ?>)" 
                                            class="btn btn-primary btn-xs" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>&<?php echo $query_string; ?>">
                            <i class="fas fa-chevron-left"></i> Prev
                        </a>
                    <?php else: ?>
                        <span class="disabled"><i class="fas fa-chevron-left"></i> Prev</span>
                    <?php endif; ?>

                    <?php 
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    if($start > 1): ?>
                        <a href="?page=1&<?php echo $query_string; ?>">1</a>
                        <?php if($start > 2): ?><span>...</span><?php endif; ?>
                    <?php endif; ?>

                    <?php for($i = $start; $i <= $end; $i++): ?>
                        <?php if($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&<?php echo $query_string; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if($end < $total_pages): ?>
                        <?php if($end < $total_pages - 1): ?><span>...</span><?php endif; ?>
                        <a href="?page=<?php echo $total_pages; ?>&<?php echo $query_string; ?>">
                            <?php echo $total_pages; ?>
                        </a>
                    <?php endif; ?>

                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?>&<?php echo $query_string; ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="disabled">Next <i class="fas fa-chevron-right"></i></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-transactions">
                <i class="fas fa-receipt"></i>
                <p style="font-size:18px;font-weight:600;color:var(--gray-600);">No transactions found</p>
                <p style="font-size:13px;">Try adjusting your filters or search terms</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ========================================
BEAUTIFUL TRANSACTION DETAIL MODAL
======================================== -->
<div class="modal-overlay" id="transactionModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-receipt"></i> Transaction Details</h3>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <div style="text-align:center;padding:40px 0;">
                <div class="spinner"></div>
                <p style="margin-top:12px;color:var(--gray-500);">Loading transaction details...</p>
            </div>
        </div>
    </div>
</div>

<!-- ========================================
CHARTS
======================================== -->
<script>
    // Monthly Transaction Chart
    const monthlyData = <?php echo json_encode($monthly_data); ?>;
    const months = <?php echo json_encode($months); ?>;
    
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Deposits',
                    data: monthlyData.map(d => d.deposits),
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                    borderColor: '#22c55e',
                    borderWidth: 2,
                    borderRadius: 4,
                },
                {
                    label: 'Withdrawals',
                    data: monthlyData.map(d => d.withdrawals),
                    backgroundColor: 'rgba(239, 68, 68, 0.7)',
                    borderColor: '#ef4444',
                    borderWidth: 2,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 12, weight: '500' }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { stepSize: 1 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Distribution Chart
    const distCtx = document.getElementById('distributionChart').getContext('2d');
    new Chart(distCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending', 'Failed'],
            datasets: [{
                data: [
                    <?php echo $stats['completed']; ?>,
                    <?php echo $stats['pending']; ?>,
                    <?php echo $stats['failed']; ?>
                ],
                backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                borderWidth: 3,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 12, weight: '500' }
                    }
                }
            },
            cutout: '65%'
        }
    });
</script>

<!-- ========================================
MODAL FUNCTIONALITY - BEAUTIFUL POPUP
======================================== -->
<script>
    let currentTransactionId = null;

    function openModal(id) {
        currentTransactionId = id;
        const modal = document.getElementById('transactionModal');
        const body = document.getElementById('modalBody');
        
        // Show loading
        body.innerHTML = `
            <div style="text-align:center;padding:40px 0;">
                <div class="spinner"></div>
                <p style="margin-top:12px;color:var(--gray-500);">Loading transaction details...</p>
            </div>
        `;
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Fetch transaction details using the same page with ajax parameter
        fetch(`?ajax=1&id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if(data.success) {
                    renderTransactionDetails(data.transaction);
                } else {
                    body.innerHTML = `
                        <div style="text-align:center;padding:40px 0;color:var(--danger);">
                            <i class="fas fa-exclamation-circle" style="font-size:48px;margin-bottom:12px;"></i>
                            <p>${data.message || 'Failed to load transaction details.'}</p>
                            <p style="font-size:13px;margin-top:8px;color:var(--gray-500);">Please try refreshing the page.</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                body.innerHTML = `
                    <div style="text-align:center;padding:40px 0;color:var(--danger);">
                        <i class="fas fa-exclamation-circle" style="font-size:48px;margin-bottom:12px;"></i>
                        <p>Error loading transaction details. Please try again.</p>
                        <p style="font-size:13px;margin-top:8px;color:var(--gray-500);">${error.message}</p>
                    </div>
                `;
                console.error('Error:', error);
            });
    }

    function renderTransactionDetails(txn) {
        const body = document.getElementById('modalBody');
        
        // Status badge class
        let statusClass = 'pending';
        let statusIcon = 'fa-clock';
        let statusText = 'Pending';
        
        if(txn.status === 'completed') {
            statusClass = 'completed';
            statusIcon = 'fa-check-circle';
            statusText = 'Completed';
        } else if(txn.status === 'failed') {
            statusClass = 'failed';
            statusIcon = 'fa-times-circle';
            statusText = 'Failed';
        }
        
        // Build HTML
        let html = `
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
                <span class="status-badge-large ${statusClass}">
                    <i class="fas ${statusIcon}"></i> ${statusText}
                </span>
                <span style="font-size:13px;color:var(--gray-500);">
                    <i class="fas fa-calendar-alt"></i> ${formatDate(txn.created_at)}
                </span>
            </div>
            
            <div style="text-align:center;margin-bottom:20px;">
                <div style="font-size:32px;font-weight:800;color:${txn.type === 'deposit' ? 'var(--success)' : 'var(--danger)'};">
                    ${txn.type === 'deposit' ? '+' : '-'}₹${Number(txn.amount).toFixed(2)}
                </div>
                <div style="font-size:13px;color:var(--gray-500);text-transform:capitalize;">
                    <i class="fas fa-credit-card"></i> ${txn.method}
                </div>
            </div>
            
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="label"><i class="fas fa-hashtag"></i> Transaction ID</div>
                    <div class="value">#${String(txn.id).padStart(6, '0')}</div>
                </div>
                
                <div class="detail-item">
                    <div class="label"><i class="fas fa-user"></i> User</div>
                    <div class="value">${escapeHtml(txn.user_name || 'N/A')}</div>
                </div>
                
                <div class="detail-item">
                    <div class="label"><i class="fas fa-envelope"></i> Email</div>
                    <div class="value">${escapeHtml(txn.user_email || 'N/A')}</div>
                </div>
                
                <div class="detail-item">
                    <div class="label"><i class="fas fa-phone"></i> Mobile</div>
                    <div class="value">${escapeHtml(txn.user_mobile || 'N/A')}</div>
                </div>
                
                <div class="detail-item">
                    <div class="label"><i class="fas fa-user-tag"></i> User Role</div>
                    <div class="value">${escapeHtml(txn.user_role || 'N/A')}</div>
                </div>
                
                <div class="detail-item">
                    <div class="label"><i class="fas fa-tag"></i> Type</div>
                    <div class="value" style="color:${txn.type === 'deposit' ? 'var(--success)' : 'var(--danger)'};">
                        <i class="fas fa-${txn.type === 'deposit' ? 'arrow-down' : 'arrow-up'}"></i>
                        ${txn.type.charAt(0).toUpperCase() + txn.type.slice(1)}
                    </div>
                </div>
            </div>
        `;
        
        // Account details
        if(txn.account_details) {
            html += `
                <div class="account-details-section">
                    <div class="title"><i class="fas fa-address-card"></i> Account Details</div>
                    <div class="content">${escapeHtml(txn.account_details)}</div>
                </div>
            `;
        }
        
        // Reference
        if(txn.reference) {
            html += `
                <div class="account-details-section" style="background:var(--gray-50);border-color:var(--gray-200);border-left-color:var(--primary);">
                    <div class="title"><i class="fas fa-hashtag"></i> Reference</div>
                    <div class="content">${escapeHtml(txn.reference)}</div>
                </div>
            `;
        }
        
        // Admin note
        if(txn.admin_note) {
            html += `
                <div class="admin-note-section">
                    <div class="title"><i class="fas fa-sticky-note"></i> Admin Note</div>
                    <div class="content">${escapeHtml(txn.admin_note)}</div>
                </div>
            `;
        }
        
        // Status update form
        html += `
            <div class="status-update">
                <h4><i class="fas fa-edit"></i> Update Status</h4>
                <form method="POST" action="" onsubmit="return handleStatusUpdate(event)">
                    <input type="hidden" name="txn_id" value="${txn.id}">
                    <select name="status" required>
                        <option value="pending" ${txn.status === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="completed" ${txn.status === 'completed' ? 'selected' : ''}>Completed</option>
                        <option value="failed" ${txn.status === 'failed' ? 'selected' : ''}>Failed</option>
                    </select>
                    <textarea name="admin_note" placeholder="Add a note (optional)..."></textarea>
                    <div class="btn-group">
                        <button type="submit" name="update_status" class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i> Update Status
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(${txn.id})">
                            <i class="fas fa-trash"></i> Delete Transaction
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="closeModal()">
                            <i class="fas fa-times"></i> Close
                        </button>
                    </div>
                </form>
            </div>
        `;
        
        body.innerHTML = html;
    }

    function closeModal() {
        document.getElementById('transactionModal').classList.remove('active');
        document.body.style.overflow = 'auto';
        currentTransactionId = null;
    }

    function escapeHtml(text) {
        if(!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    function handleStatusUpdate(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        // Show loading
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner"></span> Updating...';
        btn.disabled = true;
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            // Refresh the modal content
            if(currentTransactionId) {
                openModal(currentTransactionId);
            }
            // Show success message
            showNotification('Transaction status updated successfully!', 'success');
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to update status. Please try again.', 'error');
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
        
        return false;
    }

    function confirmDelete(id) {
        if(confirm('⚠️ Are you sure you want to delete this transaction? This action cannot be undone!')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="txn_id" value="${id}">
                <input type="hidden" name="delete_transaction" value="1">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function showNotification(message, type) {
        const existing = document.querySelector('.alert');
        if(existing) existing.remove();
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;
        
        const container = document.querySelector('.container');
        container.insertBefore(alert, container.firstChild);
        
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    }

    // Close modal on background click
    document.getElementById('transactionModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });

    // Auto-hide alerts
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 400);
        }, 5000);
    });
</script>

</body>
</html>

