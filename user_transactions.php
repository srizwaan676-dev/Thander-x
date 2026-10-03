<?php
session_start();
include("../db.php");

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get logged-in user ID
$user_id = $_SESSION['user_id'];

// ========================================
// FUNCTIONS
// ========================================

// Get user transactions with filters - MODIFIED for single user
function getUserTransactions($conn, $user_id, $limit = 50, $offset = 0, $type = '', $status = '', $search = '') {
    $sql = "SELECT t.*, u.name as user_name, u.Email as user_email, u.mobile as user_mobile 
            FROM wallet_transactions t 
            LEFT JOIN contact u ON t.user_id = u.id 
            WHERE t.user_id = '$user_id'";
    
    if($type) {
        $type = mysqli_real_escape_string($conn, $type);
        $sql .= " AND t.type = '$type'";
    }
    
    if($status) {
        $status = mysqli_real_escape_string($conn, $status);
        $sql .= " AND t.status = '$status'";
    }
    
    if($search) {
        $search = mysqli_real_escape_string($conn, $search);
        $sql .= " AND (u.name LIKE '%$search%' OR u.Email LIKE '%$search%' OR u.mobile LIKE '%$search%' OR t.id LIKE '%$search%')";
    }
    
    $sql .= " ORDER BY t.id DESC LIMIT $limit OFFSET $offset";
    
    $result = mysqli_query($conn, $sql);
    return $result;
}

// Get total user transactions count - MODIFIED for single user
function getTotalUserTransactions($conn, $user_id, $type = '', $status = '', $search = '') {
    $sql = "SELECT COUNT(*) as total FROM wallet_transactions t 
            LEFT JOIN contact u ON t.user_id = u.id 
            WHERE t.user_id = '$user_id'";
    
    if($type) {
        $type = mysqli_real_escape_string($conn, $type);
        $sql .= " AND t.type = '$type'";
    }
    
    if($status) {
        $status = mysqli_real_escape_string($conn, $status);
        $sql .= " AND t.status = '$status'";
    }
    
    if($search) {
        $search = mysqli_real_escape_string($conn, $search);
        $sql .= " AND (u.name LIKE '%$search%' OR u.Email LIKE '%$search%' OR u.mobile LIKE '%$search%' OR t.id LIKE '%$search%')";
    }
    
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

// Get user transaction statistics - MODIFIED for single user
function getUserTransactionStats($conn, $user_id) {
    $stats = [
        'total_transactions' => 0,
        'total_deposits' => 0,
        'total_withdrawals' => 0,
        'total_amount' => 0,
        'pending_count' => 0,
        'completed_count' => 0,
        'failed_count' => 0
    ];
    
    // Total transactions
    $sql = "SELECT COUNT(*) as total FROM wallet_transactions WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $stats['total_transactions'] = $row['total'];
    }
    
    // Total amount
    $sql = "SELECT SUM(amount) as total FROM wallet_transactions WHERE user_id = '$user_id' AND status = 'completed'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $stats['total_amount'] = $row['total'] ? $row['total'] : 0;
    }
    
    // Deposits
    $sql = "SELECT COUNT(*) as total FROM wallet_transactions WHERE user_id = '$user_id' AND type = 'deposit' AND status = 'completed'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $stats['total_deposits'] = $row['total'];
    }
    
    // Withdrawals
    $sql = "SELECT COUNT(*) as total FROM wallet_transactions WHERE user_id = '$user_id' AND type = 'withdrawal' AND status = 'completed'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $stats['total_withdrawals'] = $row['total'];
    }
    
    // Pending
    $sql = "SELECT COUNT(*) as total FROM wallet_transactions WHERE user_id = '$user_id' AND status = 'pending'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $stats['pending_count'] = $row['total'];
    }
    
    // Completed
    $sql = "SELECT COUNT(*) as total FROM wallet_transactions WHERE user_id = '$user_id' AND status = 'completed'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $stats['completed_count'] = $row['total'];
    }
    
    // Failed
    $sql = "SELECT COUNT(*) as total FROM wallet_transactions WHERE user_id = '$user_id' AND status = 'failed'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $stats['failed_count'] = $row['total'];
    }
    
    return $stats;
}

// Update transaction status
function updateTransactionStatus($conn, $id, $status) {
    $id = mysqli_real_escape_string($conn, $id);
    $status = mysqli_real_escape_string($conn, $status);
    
    $sql = "UPDATE wallet_transactions SET status = '$status', updated_at = NOW() WHERE id = '$id'";
    return mysqli_query($conn, $sql);
}

// Get transaction details
function getTransactionDetails($conn, $id) {
    $id = mysqli_real_escape_string($conn, $id);
    $sql = "SELECT t.*, u.name as user_name, u.Email as user_email, u.mobile as user_mobile 
            FROM wallet_transactions t 
            LEFT JOIN contact u ON t.user_id = u.id 
            WHERE t.id = '$id'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

// Get user info
function getUserInfo($conn, $user_id) {
    $sql = "SELECT name, Email FROM contact WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

// ========================================
// PROCESS REQUESTS
// ========================================

// Handle status update
if(isset($_POST['update_status'])) {
    $txn_id = $_POST['txn_id'];
    $status = $_POST['status'];
    
    if(updateTransactionStatus($conn, $txn_id, $status)) {
        $success_msg = 'Transaction status updated successfully!';
    } else {
        $error_msg = 'Failed to update transaction status!';
    }
}

// Get user info
$user_info = getUserInfo($conn, $user_id);
$user_name = $user_info['name'] ?? 'User';

// Get filters
$type = isset($_GET['type']) ? $_GET['type'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get data - MODIFIED to use user_id
$transactions = getUserTransactions($conn, $user_id, $limit, $offset, $type, $status, $search);
$total_transactions = getTotalUserTransactions($conn, $user_id, $type, $status, $search);
$stats = getUserTransactionStats($conn, $user_id);

// Get transaction details for modal
$txn_details = null;
if(isset($_GET['view'])) {
    $txn_details = getTransactionDetails($conn, $_GET['view']);
}

// Pagination
$total_pages = ceil($total_transactions / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Transactions | Thunder X</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        /* ========================================
                   ROOT VARIABLES
                ======================================== */
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
            
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            
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
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ========================================
                   NAVBAR
                ======================================== */
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

        /* ========================================
                   PAGE HEADER
                ======================================== */
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

        .btn-warning {
            background: var(--warning);
            color: #fff;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(245, 158, 11, 0.4);
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

        /* ========================================
                   STATS CARDS
                ======================================== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: var(--radius-md);
            padding: 18px 20px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0,0,0,0.04);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card .stat-icon {
            font-size: 22px;
            margin-bottom: 6px;
        }

        .stat-card .stat-number {
            font-size: 22px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .stat-card .stat-label {
            font-size: 12px;
            color: var(--gray-500);
            font-weight: 500;
        }

        .stat-card .stat-icon.green { color: var(--success); }
        .stat-card .stat-icon.blue { color: var(--primary); }
        .stat-card .stat-icon.orange { color: var(--warning); }
        .stat-card .stat-icon.red { color: var(--danger); }
        .stat-card .stat-icon.purple { color: var(--purple); }

        /* ========================================
                   FILTERS
                ======================================== */
        .filters-section {
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0,0,0,0.04);
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
        }

        .filters-section .filter-group {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filters-section .filter-group label {
            font-weight: 500;
            font-size: 13px;
            color: var(--gray-600);
        }

        .filters-section select,
        .filters-section input {
            padding: 8px 14px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 13px;
            background: var(--gray-50);
            transition: var(--transition);
            color: var(--gray-800);
        }

        .filters-section select:focus,
        .filters-section input:focus {
            outline: none;
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .filters-section .search-group {
            display: flex;
            gap: 8px;
            margin-left: auto;
        }

        /* ========================================
                   TABLE
                ======================================== */
        .table-container {
            background: #ffffff;
            border-radius: var(--radius-xl);
            padding: 24px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0,0,0,0.04);
            overflow-x: auto;
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

        .text-success {
            color: var(--success);
        }

        .text-danger {
            color: var(--danger);
        }

        .text-muted {
            color: var(--gray-400);
        }

        .text-center {
            text-align: center;
        }

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

        /* ========================================
                   PAGINATION
                ======================================== */
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
                   MODAL
                ======================================== */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: #ffffff;
            border-radius: var(--radius-xl);
            max-width: 600px;
            width: 100%;
            padding: 32px;
            box-shadow: var(--shadow-lg);
            animation: slideUp 0.3s ease;
            max-height: 90vh;
            overflow-y: auto;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--gray-200);
        }

        .modal .modal-header h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .modal .modal-header .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray-400);
            transition: var(--transition);
        }

        .modal .modal-header .close-btn:hover {
            color: var(--gray-800);
        }

        .modal .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .modal .detail-row .label {
            color: var(--gray-500);
            font-weight: 500;
        }

        .modal .detail-row .value {
            color: var(--gray-900);
            font-weight: 600;
        }

        .modal .detail-row:last-child {
            border-bottom: none;
        }

        .modal .status-update {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid var(--gray-200);
        }

        .modal .status-update form {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .modal .status-update select {
            padding: 10px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            background: var(--gray-50);
            flex: 1;
            min-width: 150px;
        }

        /* ========================================
                   ALERT
                ======================================== */
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

        /* ========================================
                   RESPONSIVE
                ======================================== */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .filters-section .search-group {
                margin-left: 0;
                width: 100%;
            }
            .filters-section .search-group input {
                flex: 1;
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
                flex-direction: column;
                align-items: stretch;
            }

            .filters-section .filter-group {
                flex-wrap: wrap;
            }

            .filters-section select,
            .filters-section input {
                flex: 1;
                min-width: 120px;
            }

            .table-container {
                padding: 16px;
            }

            table {
                min-width: 700px;
            }

            .modal {
                padding: 20px;
                margin: 10px;
            }

            .pagination a,
            .pagination span {
                padding: 6px 12px;
                font-size: 12px;
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

            .modal .status-update form {
                flex-direction: column;
            }

            .modal .status-update select {
                width: 100%;
            }
        }

        /* ========================================
                   DARK MODE
                ======================================== */
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

            .modal .modal-header {
                border-bottom-color: #334155;
            }

            .modal .modal-header h3 {
                color: #f1f5f9;
            }

            .modal .detail-row {
                border-bottom-color: #334155;
            }

            .modal .detail-row .value {
                color: #f1f5f9;
            }

            .modal .status-update {
                border-top-color: #334155;
            }

            .modal .status-update select {
                background: #334155;
                border-color: #475569;
                color: #f1f5f9;
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
        }

        /* ========================================
                   SCROLLBAR
                ======================================== */
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
            <a href=" user_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="user_profile.php"><i class="fas fa-users"></i> Users</a>
            <a href="user_investment.php"><i class="fas fa-wallet"></i> Investments</a>
            <a href="#" class="active"><i class="fas fa-exchange-alt"></i> Transactions</a>
            <a href="user_setting.php"><i class="fas fa-cog"></i> Settings</a>
            <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
            <?php 
            $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=2563eb&color=fff&size=42&bold=true";
            ?>
            <img src="<?php echo $avatar_url; ?>" alt="Avatar" class="user-avatar">
        </div>
    </nav>

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header">
        <div>
            <h1><i class="fas fa-exchange-alt"></i> My Transactions</h1>
            <p>View your transaction history</p>
        </div>
        <div class="actions">
            <button class="btn btn-primary" onclick="window.location.reload()">
                <i class="fas fa-sync"></i> Refresh
            </button>
        </div>
    </div>

    <!-- ===== ALERTS ===== -->
    <?php if(isset($success_msg)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <?php if(isset($error_msg)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <!-- ===== STATS CARDS ===== -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-exchange-alt"></i></div>
            <div class="stat-number"><?php echo number_format($stats['total_transactions']); ?></div>
            <div class="stat-label">Total Transactions</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-arrow-down"></i></div>
            <div class="stat-number"><?php echo number_format($stats['total_deposits']); ?></div>
            <div class="stat-label">Total Deposits</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-arrow-up"></i></div>
            <div class="stat-number"><?php echo number_format($stats['total_withdrawals']); ?></div>
            <div class="stat-label">Total Withdrawals</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-coins"></i></div>
            <div class="stat-number">₹<?php echo number_format($stats['total_amount']); ?></div>
            <div class="stat-label">Total Amount</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div class="stat-number"><?php echo number_format($stats['pending_count']); ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="stat-number"><?php echo number_format($stats['completed_count']); ?></div>
            <div class="stat-label">Completed</div>
        </div>
    </div>

    <!-- ===== FILTERS ===== -->
    <div class="filters-section">
        <form method="GET" action="" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center; width: 100%;">
            <div class="filter-group">
                <label><i class="fas fa-filter"></i> Type</label>
                <select name="type" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="deposit" <?php echo $type == 'deposit' ? 'selected' : ''; ?>>Deposit</option>
                    <option value="withdrawal" <?php echo $type == 'withdrawal' ? 'selected' : ''; ?>>Withdrawal</option>
                </select>
            </div>

            <div class="filter-group">
                <label><i class="fas fa-tag"></i> Status</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="failed" <?php echo $status == 'failed' ? 'selected' : ''; ?>>Failed</option>
                </select>
            </div>

            <div class="search-group">
                <input type="text" name="search" placeholder="Search by ID..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i>
                </button>
                <?php if($search || $type || $status): ?>
                    <a href="user_transactions.php" class="btn btn-outline btn-sm">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ===== TABLE ===== -->
    <div class="table-container">
        <?php if($transactions && mysqli_num_rows($transactions) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($txn = mysqli_fetch_assoc($transactions)): ?>
                        <tr>
                            <td>#<?php echo str_pad($txn['id'], 6, '0', STR_PAD_LEFT); ?></td>
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
                            <td class="text-muted"><?php echo date('d M Y', strtotime($txn['created_at'])); ?></td>
                            <td>
                                <a href="?view=<?php echo $txn['id']; ?>" class="btn btn-primary btn-sm" style="padding:4px 12px;font-size:12px;">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>&type=<?php echo $type; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                            <i class="fas fa-chevron-left"></i> Prev
                        </a>
                    <?php else: ?>
                        <span class="disabled"><i class="fas fa-chevron-left"></i> Prev</span>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&type=<?php echo $type; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?>&type=<?php echo $type; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
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
                <p>No transactions found</p>
                <p style="font-size:13px;opacity:0.6;">Try adjusting your filters or search terms</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ===== TRANSACTION DETAIL MODAL ===== -->
<?php if($txn_details): ?>
<div class="modal-overlay active" id="transactionModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-info-circle" style="color:var(--primary);"></i> Transaction Details</h3>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>

        <div class="detail-row">
            <span class="label">Transaction ID</span>
            <span class="value">#<?php echo str_pad($txn_details['id'], 6, '0', STR_PAD_LEFT); ?></span>
        </div>

        <div class="detail-row">
            <span class="label">User</span>
            <span class="value"><?php echo htmlspecialchars($txn_details['user_name'] ?? 'N/A'); ?></span>
        </div>

        <div class="detail-row">
            <span class="label">Email</span>
            <span class="value"><?php echo htmlspecialchars($txn_details['user_email'] ?? 'N/A'); ?></span>
        </div>

        <div class="detail-row">
            <span class="label">Mobile</span>
            <span class="value"><?php echo htmlspecialchars($txn_details['user_mobile'] ?? 'N/A'); ?></span>
        </div>

        <div class="detail-row">
            <span class="label">Type</span>
            <span class="value">
                <?php if($txn_details['type'] == 'deposit'): ?>
                    <span class="text-success"><i class="fas fa-arrow-down"></i> Deposit</span>
                <?php else: ?>
                    <span class="text-danger"><i class="fas fa-arrow-up"></i> Withdrawal</span>
                <?php endif; ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="label">Amount</span>
            <span class="value" style="font-size:20px;">
                <?php if($txn_details['type'] == 'deposit'): ?>
                    <span class="text-success">+₹<?php echo number_format($txn_details['amount'], 2); ?></span>
                <?php else: ?>
                    <span class="text-danger">-₹<?php echo number_format($txn_details['amount'], 2); ?></span>
                <?php endif; ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="label">Method</span>
            <span class="value" style="text-transform:capitalize;"><?php echo $txn_details['method']; ?></span>
        </div>

        <div class="detail-row">
            <span class="label">Status</span>
            <span class="value">
                <?php if($txn_details['status'] == 'completed'): ?>
                    <span class="badge badge-success">Completed</span>
                <?php elseif($txn_details['status'] == 'pending'): ?>
                    <span class="badge badge-warning">Pending</span>
                <?php elseif($txn_details['status'] == 'failed'): ?>
                    <span class="badge badge-danger">Failed</span>
                <?php else: ?>
                    <span class="badge badge-primary"><?php echo ucfirst($txn_details['status']); ?></span>
                <?php endif; ?>
            </span>
        </div>

        <?php if(!empty($txn_details['account_details'])): ?>
            <div class="detail-row">
                <span class="label">Account Details</span>
                <span class="value" style="font-size:13px;"><?php echo htmlspecialchars($txn_details['account_details']); ?></span>
            </div>
        <?php endif; ?>

        <div class="detail-row">
            <span class="label">Date & Time</span>
            <span class="value"><?php echo date('d M Y, h:i A', strtotime($txn_details['created_at'])); ?></span>
        </div>

        <?php if($txn_details['updated_at'] && $txn_details['updated_at'] != $txn_details['created_at']): ?>
            <div class="detail-row">
                <span class="label">Last Updated</span>
                <span class="value text-muted"><?php echo date('d M Y, h:i A', strtotime($txn_details['updated_at'])); ?></span>
            </div>
        <?php endif; ?>

        <div class="status-update">
            <h4 style="margin-bottom:12px;font-size:15px;color:var(--gray-700);">
                <i class="fas fa-edit"></i> Update Status
            </h4>
            <form method="POST" action="">
                <input type="hidden" name="txn_id" value="<?php echo $txn_details['id']; ?>">
                <select name="status" required>
                    <option value="pending" <?php echo $txn_details['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="completed" <?php echo $txn_details['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="failed" <?php echo $txn_details['status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                </select>
                <button type="submit" name="update_status" class="btn btn-primary btn-sm">
                    <i class="fas fa-save"></i> Update
                </button>
                <a href="#" class="btn btn-outline btn-sm">
                    <i class="fas fa-times"></i> Close
                </a>
            </form>
        </div>
    </div>
</div>

<script>
    function closeModal() {
        document.getElementById('transactionModal').classList.remove('active');
        window.location.href = '#';
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
</script>
<?php endif; ?>

<!-- ========================================
AUTO-HIDE ALERTS
======================================== -->
<script>
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