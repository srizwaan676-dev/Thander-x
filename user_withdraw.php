<?php
session_start();
include("../db.php");

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$sql = "SELECT * FROM contact WHERE id = '$user_id' AND role = 'user'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if(!$row) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// ========================================
// WITHDRAWAL FUNCTIONS
// ========================================

// Get wallet balance
function getWalletBalance($conn, $user_id) {
    $sql = "SELECT SUM(amount) as balance FROM wallet_transactions WHERE user_id = '$user_id' AND status = 'completed'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['balance'] ? $row['balance'] : 0;
    }
    return 0;
}

// Get pending withdrawals
function getPendingWithdrawals($conn, $user_id) {
    $sql = "SELECT SUM(amount) as total FROM wallet_transactions 
            WHERE user_id = '$user_id' AND type = 'withdrawal' AND status = 'pending'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

// Get total withdrawals
function getTotalWithdrawals($conn, $user_id) {
    $sql = "SELECT SUM(amount) as total FROM wallet_transactions 
            WHERE user_id = '$user_id' AND type = 'withdrawal' AND status = 'completed'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

// Get withdrawal history
function getWithdrawalHistory($conn, $user_id, $limit = 20) {
    $sql = "SELECT * FROM wallet_transactions 
            WHERE user_id = '$user_id' AND type = 'withdrawal' 
            ORDER BY id DESC LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    return $result;
}

// Request withdrawal
function requestWithdrawal($conn, $user_id, $amount, $method, $account) {
    $amount = floatval($amount);
    $method = mysqli_real_escape_string($conn, $method);
    $account_details = mysqli_real_escape_string($conn, $account);
    
    // Check balance
    $balance = getWalletBalance($conn, $user_id);
    if($amount > $balance) {
        return ['success' => false, 'message' => 'Insufficient balance! Available: ₹' . number_format($balance, 2)];
    }
    
    // Check minimum amount
    $min_withdrawal = 100;
    if($amount < $min_withdrawal) {
        return ['success' => false, 'message' => 'Minimum withdrawal amount is ₹' . $min_withdrawal . '!'];
    }
    
    // Check maximum amount
    $max_withdrawal = 50000;
    if($amount > $max_withdrawal) {
        return ['success' => false, 'message' => 'Maximum withdrawal amount is ₹' . $max_withdrawal . '!'];
    }
    
    // Insert withdrawal request
    $sql = "INSERT INTO wallet_transactions 
            (user_id, amount, type, method, account, status, created_at) 
            VALUES ('$user_id', '$amount', 'withdrawal', '$method', '$account', 'pending', NOW())";
    
    if(mysqli_query($conn, $sql)) {
        return ['success' => true, 'message' => 'Withdrawal request submitted successfully!'];
    } else {
        return ['success' => false, 'message' => 'Failed to submit request. Please try again!'];
    }
}

// Get settings from database
function getSettings($conn) {
    $settings = [];
    $sql = "SELECT * FROM settings";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings;
}

// ========================================
// PROCESS FORM SUBMISSIONS
// ========================================

$withdraw_success = '';
$withdraw_error = '';
$settings = getSettings($conn);

// Handle Withdrawal
if(isset($_POST['withdraw_submit'])) {
    $amount = floatval($_POST['withdraw_amount']);
    $method = mysqli_real_escape_string($conn, $_POST['withdraw_method']);
    $account_details = mysqli_real_escape_string($conn, $_POST['account_details']);
    
    $result = requestWithdrawal($conn, $user_id, $amount, $method, $account_details);
    if($result['success']) {
        $withdraw_success = $result['message'];
    } else {
        $withdraw_error = $result['message'];
    }
}

// Get wallet data
$balance = getWalletBalance($conn, $user_id);
$pending_withdrawals = getPendingWithdrawals($conn, $user_id);
$total_withdrawals = getTotalWithdrawals($conn, $user_id);
$withdrawal_history = getWithdrawalHistory($conn, $user_id, 20);

// Get min/max from settings or use defaults
$min_withdrawal = isset($settings['min_withdrawal']) ? $settings['min_withdrawal'] : 100;
$max_withdrawal = isset($settings['max_withdrawal']) ? $settings['max_withdrawal'] : 50000;
$currency = isset($settings['currency']) ? $settings['currency'] : '₹';
$processing_time = isset($settings['withdrawal_processing']) ? $settings['withdrawal_processing'] : '24-48 Hours';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Money | <?php echo htmlspecialchars($row['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ========================================
           ROOT VARIABLES
        ======================================== */
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #eef2ff;
            --primary-subtle: #f8f7ff;
            --primary-gradient: linear-gradient(135deg, #6366f1, #8b5cf6);
            
            --secondary: #f472b6;
            --success: #10b981;
            --success-light: #ecfdf5;
            --warning: #f59e0b;
            --warning-light: #fffbeb;
            --danger: #ef4444;
            --danger-light: #fef2f2;
            --purple: #a855f7;
            --purple-light: #f3e8ff;
            
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            
            --shadow-xs: 0 1px 2px rgba(0,0,0,0.03);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 15px rgba(99,102,241,0.10);
            --shadow-lg: 0 10px 30px rgba(99,102,241,0.12);
            --shadow-xl: 0 20px 50px rgba(99,102,241,0.15);
            --shadow-primary: 0 4px 15px rgba(99,102,241,0.25);
            
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-2xl: 24px;
            --radius-full: 9999px;
            
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: 
                radial-gradient(circle at 0% 0%, rgba(99,102,241,0.04) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(244,114,182,0.04) 0%, transparent 50%),
                var(--gray-50);
            padding: 20px;
            color: var(--gray-800);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .withdraw-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ========================================
           NAVBAR
        ======================================== */
        .navbar {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: var(--radius-2xl);
            padding: 12px 28px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 16px;
            border: 1px solid rgba(99,102,241,0.08);
            position: relative;
            animation: slideDown 0.5s ease;
        }

        .navbar::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 10%;
            right: 10%;
            height: 2px;
            background: var(--primary-gradient);
            border-radius: var(--radius-full);
            opacity: 0.3;
        }

        .navbar .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .navbar .logo .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            box-shadow: var(--shadow-primary);
        }

        .navbar .logo h2 {
            font-size: 20px;
            font-weight: 800;
            color: var(--gray-900);
            letter-spacing: -0.5px;
        }

        .navbar .logo h2 span {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar .nav-links {
            display: flex;
            gap: 2px;
            align-items: center;
            flex-wrap: wrap;
        }

        .navbar .nav-links a {
            text-decoration: none;
            color: var(--gray-500);
            font-weight: 600;
            font-size: 13px;
            padding: 8px 18px;
            border-radius: var(--radius-full);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navbar .nav-links a:hover {
            background: var(--primary-light);
            color: var(--primary);
            transform: translateY(-1px);
        }

        .navbar .nav-links a.active {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: var(--shadow-primary);
        }

        .navbar .nav-links a.logout-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
        }

        .navbar .nav-links a.logout-btn:hover {
            box-shadow: 0 4px 15px rgba(239,68,68,0.3);
            transform: translateY(-2px);
        }

        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .navbar .user-info .user-name {
            font-weight: 600;
            color: var(--gray-700);
            font-size: 13px;
        }

        .navbar .user-info .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            outline: 2px solid rgba(99,102,241,0.15);
            transition: var(--transition);
        }

        .navbar .user-info .user-avatar:hover {
            outline-color: var(--primary);
            transform: scale(1.05);
        }

        /* ========================================
           PAGE HEADER
        ======================================== */
        .page-header {
            margin-bottom: 28px;
            animation: fadeUp 0.6s ease;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.5px;
        }

        .page-header h1 i {
            background: var(--primary-gradient);
            color: #fff;
            padding: 10px;
            border-radius: var(--radius-md);
            font-size: 18px;
            box-shadow: var(--shadow-primary);
        }

        .page-header p {
            color: var(--gray-500);
            font-size: 14px;
            margin-top: 4px;
            padding-left: 52px;
        }

        /* ========================================
           WITHDRAWAL GRID
        ======================================== */
        .withdraw-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            animation: fadeUp 0.7s ease;
        }

        /* ========================================
           WITHDRAWAL FORM CARD
        ======================================== */
        .withdraw-card {
            background: #ffffff;
            border-radius: var(--radius-2xl);
            padding: 32px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .withdraw-card:hover {
            box-shadow: var(--shadow-md);
            border-color: rgba(99,102,241,0.12);
            transform: translateY(-2px);
        }

        .withdraw-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-gradient);
        }

        .withdraw-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--gray-200);
            flex-wrap: wrap;
            gap: 8px;
        }

        .withdraw-card .card-header h3 {
            font-size: 17px;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .withdraw-card .card-header h3 i {
            color: var(--primary);
        }

        .withdraw-card .card-header .balance-display {
            font-size: 13px;
            color: var(--gray-500);
            background: var(--gray-50);
            padding: 6px 16px;
            border-radius: var(--radius-full);
            border: 1px solid var(--gray-200);
        }

        .withdraw-card .card-header .balance-display strong {
            color: var(--success);
            font-size: 16px;
        }

        /* ========================================
           FORM
        ======================================== */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 5px;
            font-size: 13px;
        }

        .form-group label i {
            margin-right: 6px;
            color: var(--primary);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 14px;
            transition: var(--transition);
            background: var(--gray-50);
            color: var(--gray-800);
            font-family: 'Inter', sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.08);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-group .help-text {
            font-size: 12px;
            color: var(--gray-400);
            margin-top: 4px;
        }

        .form-group .help-text i {
            margin-right: 4px;
        }

        /* ========================================
           BUTTONS
        ======================================== */
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: var(--radius-full);
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: var(--shadow-primary);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(99,102,241,0.35);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* ========================================
           ALERTS
        ======================================== */
        .alert {
            padding: 14px 18px;
            border-radius: var(--radius-md);
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
            animation: slideDown 0.4s ease;
            border-left: 4px solid;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: var(--success-light);
            color: #065f46;
            border-left-color: var(--success);
        }

        .alert-success i {
            color: var(--success);
        }

        .alert-error {
            background: var(--danger-light);
            color: #991b1b;
            border-left-color: var(--danger);
        }

        .alert-error i {
            color: var(--danger);
        }

        .alert i {
            font-size: 18px;
        }

        /* ========================================
           WITHDRAWAL INFO CARD
        ======================================== */
        .info-card {
            background: #ffffff;
            border-radius: var(--radius-2xl);
            padding: 32px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
            align-self: start;
            position: relative;
            overflow: hidden;
        }

        .info-card:hover {
            box-shadow: var(--shadow-md);
            border-color: rgba(99,102,241,0.12);
            transform: translateY(-2px);
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--success));
        }

        .info-card h3 {
            font-size: 17px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card h3 i {
            color: var(--primary);
        }

        .info-card .info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .info-card .info-item:last-child {
            border-bottom: none;
        }

        .info-card .info-item .label {
            color: var(--gray-500);
            font-size: 13px;
        }

        .info-card .info-item .label i {
            margin-right: 6px;
            width: 18px;
        }

        .info-card .info-item .value {
            font-weight: 600;
            font-size: 14px;
            color: var(--gray-900);
        }

        .info-card .info-item .value.text-success {
            color: var(--success);
        }

        .info-card .info-item .value.text-warning {
            color: var(--warning);
        }

        .info-card .info-item .value.text-danger {
            color: var(--danger);
        }

        .info-card .info-item .value.text-primary {
            color: var(--primary);
        }

        /* Withdraw Preview */
        #withdrawPreview {
            margin-top: 16px;
            padding: 18px;
            background: var(--primary-subtle);
            border-radius: var(--radius-md);
            text-align: center;
            border: 2px dashed rgba(99,102,241,0.2);
            display: none;
        }

        #withdrawPreview .preview-label {
            font-size: 13px;
            color: var(--gray-500);
        }

        #withdrawPreview .preview-amount {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary-dark);
            margin-top: 4px;
        }

        #withdrawPreview .preview-info {
            font-size: 12px;
            color: var(--gray-400);
            margin-top: 2px;
        }

        /* ========================================
           WITHDRAWAL HISTORY
        ======================================== */
        .history-section {
            margin-top: 28px;
            background: #ffffff;
            border-radius: var(--radius-2xl);
            padding: 32px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            animation: fadeUp 0.8s ease;
        }

        .history-section:hover {
            box-shadow: var(--shadow-md);
            border-color: rgba(99,102,241,0.12);
        }

        .history-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--purple), var(--secondary));
        }

        .history-section .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .history-section .section-header h3 {
            font-size: 17px;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .history-section .section-header h3 i {
            color: var(--primary);
        }

        .history-section .section-header .badge-count {
            background: var(--gray-100);
            padding: 4px 16px;
            border-radius: var(--radius-full);
            font-size: 13px;
            color: var(--gray-600);
            font-weight: 500;
        }

        .history-section .section-header .badge-count i {
            margin-right: 4px;
        }

        /* ========================================
           TABLE
        ======================================== */
        .table-wrapper {
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
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--gray-200);
        }

        table td {
            padding: 12px 16px;
            color: var(--gray-700);
            font-size: 14px;
            border-bottom: 1px solid var(--gray-100);
        }

        table tbody tr:hover {
            background: var(--gray-50);
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ========================================
           BADGES
        ======================================== */
        .badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: var(--radius-full);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-success {
            background: var(--success-light);
            color: #065f46;
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

        .badge-purple {
            background: var(--purple-light);
            color: #6b21a8;
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

        /* ========================================
           NO TRANSACTIONS
        ======================================== */
        .no-transactions {
            text-align: center;
            padding: 50px 20px;
            color: var(--gray-400);
        }

        .no-transactions i {
            font-size: 56px;
            opacity: 0.2;
            display: block;
            margin-bottom: 16px;
        }

        .no-transactions p {
            font-size: 16px;
        }

        .no-transactions .sub-text {
            font-size: 13px;
            opacity: 0.6;
            margin-top: 4px;
        }

        /* ========================================
           PROCESSING INFO
        ======================================== */
        .processing-info {
            margin-top: 16px;
            padding: 14px 18px;
            background: var(--gray-50);
            border-radius: var(--radius-md);
            border: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .processing-info i {
            color: var(--primary);
            font-size: 16px;
        }

        .processing-info span {
            font-size: 13px;
            color: var(--gray-500);
        }

        .processing-info strong {
            color: var(--gray-700);
        }

        .processing-info .fee {
            margin-left: auto;
            color: var(--success);
            font-weight: 600;
        }

        /* ========================================
           SPINNER
        ======================================== */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ========================================
           ACCOUNT INPUT
        ======================================== */
        .account-input {
            width: 100%;
            padding: 12px 16px;
            font-size: 14px;
            color: var(--gray-800);
            background: var(--gray-50);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            outline: none;
            box-sizing: border-box;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
        }

        .account-input:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.08);
        }

        .account-input::placeholder {
            color: var(--gray-400);
        }

        /* ========================================
           RESPONSIVE
        ======================================== */
        @media (max-width: 992px) {
            .withdraw-grid {
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
                border-radius: var(--radius-xl);
            }

            .navbar .logo {
                justify-content: center;
            }

            .navbar .nav-links {
                justify-content: center;
                display: flex;
                flex-wrap: wrap;
            }

            .navbar .nav-links a {
                font-size: 11px;
                padding: 6px 12px;
            }

            .navbar .user-info {
                justify-content: center;
            }

            .page-header h1 {
                font-size: 22px;
            }

            .page-header p {
                padding-left: 0;
            }

            .withdraw-card,
            .info-card,
            .history-section {
                padding: 24px 20px;
                border-radius: var(--radius-xl);
            }

            .withdraw-card .card-header {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }

            .withdraw-card .card-header .balance-display {
                width: 100%;
                text-align: center;
            }

            .history-section .section-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .processing-info {
                flex-direction: column;
                text-align: center;
            }

            .processing-info .fee {
                margin-left: 0;
            }

            .account-input {
                padding: 10px 14px;
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 8px;
            }

            .navbar .nav-links {
                display: grid;
                grid-template-columns: 1fr 1fr;
                width: 100%;
                gap: 4px;
            }

            .navbar .nav-links a {
                justify-content: center;
                width: 100%;
                padding: 6px 8px;
                font-size: 10px;
            }

            .page-header h1 {
                font-size: 18px;
                gap: 8px;
            }

            .page-header h1 i {
                padding: 8px;
                font-size: 14px;
            }

            .page-header p {
                font-size: 12px;
            }

            .withdraw-card,
            .info-card,
            .history-section {
                padding: 18px 14px;
                border-radius: var(--radius-lg);
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 10px 14px;
                font-size: 13px;
            }

            .btn {
                font-size: 14px;
                padding: 12px;
            }

            #withdrawPreview .preview-amount {
                font-size: 22px;
            }

            table th,
            table td {
                padding: 8px 10px;
                font-size: 12px;
            }

            .account-input {
                padding: 10px 12px;
                font-size: 13px;
            }
        }

        /* ========================================
           SCROLLBAR
        ======================================== */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 20px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }
    </style>
</head>
<body>

<div class="withdraw-container">

    <!-- ===== NAVBAR ===== -->
    <nav class="navbar">
        <a href="profile_dashboard.php" class="logo">
            <div class="logo-icon"><i class="fas fa-bolt"></i></div>
            <h2>Thunder<span>X</span></h2>
        </a>
        
        <div class="nav-links">
            <a href="profile_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="user_profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="user_wallet.php"><i class="fas fa-wallet"></i> Wallet</a>
            <a href="user_withdraw.php" class="active"><i class="fas fa-arrow-up"></i> Withdraw</a>
            <a href="user_income.php"><i class="fas fa-money-bill-wave"></i> Income</a>
            <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($row['name']); ?></span>
            <?php 
            $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($row['name']) . "&background=6366f1&color=fff&size=38&bold=true";
            ?>
            <img src="<?php echo $avatar_url; ?>" alt="Avatar" class="user-avatar">
        </div>
    </nav>

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header">
        <h1>
            <i class="fas fa-arrow-up"></i>
            Withdraw Money
        </h1>
        <p>Request a withdrawal from your wallet balance to your preferred account</p>
    </div>

    <!-- ===== WITHDRAWAL GRID ===== -->
    <div class="withdraw-grid">

        <!-- ===== WITHDRAWAL FORM ===== -->
        <div class="withdraw-card">
            <div class="card-header">
                <h3><i class="fas fa-arrow-up-right-from-square"></i> Withdrawal Request</h3>
                <div class="balance-display">
                    <i class="fas fa-wallet" style="color: var(--primary);"></i>
                    Available: <strong><?php echo $currency . number_format($balance, 2); ?></strong>
                </div>
            </div>

            <?php if($withdraw_success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $withdraw_success; ?>
                </div>
            <?php endif; ?>

            <?php if($withdraw_error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $withdraw_error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="withdrawForm">
                <div class="form-group">
                    <label><i class="fas fa-coins"></i> Amount (<?php echo $currency; ?>)</label>
                    <input type="number" name="withdraw_amount" id="withdraw_amount" 
                           min="<?php echo $min_withdrawal; ?>" 
                           max="<?php echo $max_withdrawal; ?>" 
                           placeholder="Enter amount to withdraw" 
                           required 
                           step="0.01"
                           oninput="updateWithdrawPreview(this.value)">
                    <div class="help-text">
                        <i class="fas fa-info-circle"></i> 
                        Min: <?php echo $currency . number_format($min_withdrawal); ?> | 
                        Max: <?php echo $currency . number_format($max_withdrawal); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-university"></i> Withdrawal Method</label>
                    <select name="withdraw_method" id="withdraw_method" required>
                        <option value="">Select method</option>
                        <option value="bank_transfer">🏦 Bank Transfer</option>
                        <option value="upi">📱 UPI</option>
                        <option value="paypal">💳 PayPal</option>
                        <option value="crypto">🪙 Cryptocurrency</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-user"></i> Account Details</label>
                    <input type="text" 
                           name="account_details" 
                           class="account-input"
                           placeholder="Enter account number / UPI ID / Email"
                           required>
                </div>

                <button type="submit" name="withdraw_submit" class="btn btn-primary" id="withdrawBtn">
                    <i class="fas fa-arrow-up"></i> Submit Withdrawal Request
                </button>
            </form>

            <div class="processing-info">
                <i class="fas fa-clock"></i>
                <span>Processing time: <strong><?php echo $processing_time; ?></strong></span>
                <span class="fee"><i class="fas fa-percent"></i> Fee: 0%</span>
            </div>
        </div>

        <!-- ===== WITHDRAWAL INFO ===== -->
        <div class="info-card">
            <h3><i class="fas fa-chart-simple"></i> Withdrawal Summary</h3>

            <div class="info-item">
                <span class="label"><i class="fas fa-wallet"></i> Available Balance</span>
                <span class="value text-success"><?php echo $currency . number_format($balance, 2); ?></span>
            </div>

            <div class="info-item">
                <span class="label"><i class="fas fa-clock"></i> Pending Withdrawals</span>
                <span class="value text-warning"><?php echo $currency . number_format($pending_withdrawals, 2); ?></span>
            </div>

            <div class="info-item">
                <span class="label"><i class="fas fa-check-circle"></i> Total Withdrawn</span>
                <span class="value text-primary"><?php echo $currency . number_format($total_withdrawals, 2); ?></span>
            </div>

            <div class="info-item">
                <span class="label"><i class="fas fa-arrow-down"></i> Minimum Withdrawal</span>
                <span class="value"><?php echo $currency . number_format($min_withdrawal); ?></span>
            </div>

            <div class="info-item">
                <span class="label"><i class="fas fa-arrow-up"></i> Maximum Withdrawal</span>
                <span class="value"><?php echo $currency . number_format($max_withdrawal); ?></span>
            </div>

            <div class="info-item" style="border-bottom: none; padding-bottom: 0;">
                <span class="label"><i class="fas fa-percent"></i> Processing Fee</span>
                <span class="value text-success">0% (Free)</span>
            </div>

            <!-- Live Preview -->
            <div id="withdrawPreview">
                <div class="preview-label"><i class="fas fa-arrow-up"></i> You are about to withdraw</div>
                <div class="preview-amount" id="previewAmount"><?php echo $currency; ?>0.00</div>
                <div class="preview-info">Amount will be sent to your provided account</div>
            </div>
        </div>

    </div>

    <!-- ===== WITHDRAWAL HISTORY ===== -->
    <div class="history-section">
        <div class="section-header">
            <h3><i class="fas fa-history"></i> Withdrawal History</h3>
            <span class="badge-count">
                <i class="fas fa-list"></i> 
                <?php 
                $count = $withdrawal_history ? mysqli_num_rows($withdrawal_history) : 0;
                echo $count; 
                ?> transactions
            </span>
        </div>

        <?php if($withdrawal_history && mysqli_num_rows($withdrawal_history) > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($txn = mysqli_fetch_assoc($withdrawal_history)): ?>
                            <tr>
                                <td><span style="font-weight:600;">#<?php echo str_pad($txn['id'], 6, '0', STR_PAD_LEFT); ?></span></td>
                                <td>
                                    <span class="text-danger">-<?php echo $currency . number_format($txn['amount'], 2); ?></span>
                                </td>
                                <td><span style="text-transform:capitalize; font-weight:500;"><?php echo str_replace('_', ' ', $txn['method']); ?></span></td>
                                <td>
                                    <?php if($txn['status'] == 'completed'): ?>
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Completed</span>
                                    <?php elseif($txn['status'] == 'pending'): ?>
                                        <span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>
                                    <?php elseif($txn['status'] == 'failed'): ?>
                                        <span class="badge badge-danger"><i class="fas fa-times"></i> Failed</span>
                                    <?php else: ?>
                                        <span class="badge badge-primary"><?php echo ucfirst($txn['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted" style="font-size:13px;"><?php echo date('d M Y, h:i A', strtotime($txn['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-transactions">
                <i class="fas fa-receipt"></i>
                <p>No withdrawal history found</p>
                <p class="sub-text">You haven't made any withdrawals yet</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
// ========================================
// WITHDRAWAL FORM HANDLING
// ========================================

// Update preview
function updateWithdrawPreview(amount) {
    const preview = document.getElementById('withdrawPreview');
    const previewAmount = document.getElementById('previewAmount');
    const currency = '<?php echo $currency; ?>';
    
    if (amount > 0) {
        preview.style.display = 'block';
        previewAmount.textContent = currency + parseFloat(amount).toFixed(2);
    } else {
        preview.style.display = 'none';
    }
}

// Form submission handling
document.getElementById('withdrawForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('withdrawBtn');
    const amount = parseFloat(document.getElementById('withdraw_amount').value);
    const balance = <?php echo $balance; ?>;
    const minWithdrawal = <?php echo $min_withdrawal; ?>;
    const maxWithdrawal = <?php echo $max_withdrawal; ?>;
    
    // Validate amount
    if (!amount || amount <= 0) {
        e.preventDefault();
        alert('Please enter a valid amount.');
        return false;
    }
    
    if (amount < minWithdrawal) {
        e.preventDefault();
        alert('Minimum withdrawal amount is ₹' + minWithdrawal);
        return false;
    }
    
    if (amount > maxWithdrawal) {
        e.preventDefault();
        alert('Maximum withdrawal amount is ₹' + maxWithdrawal);
        return false;
    }
    
    if (amount > balance) {
        e.preventDefault();
        alert('Insufficient balance! Available: ₹' + balance.toFixed(2));
        return false;
    }
    
    // Confirm withdrawal
    if (!confirm('Are you sure you want to withdraw ₹' + amount.toFixed(2) + '?')) {
        e.preventDefault();
        return false;
    }
    
    // Show loading state
    btn.innerHTML = '<span class="spinner"></span> Processing...';
    btn.disabled = true;
});

// ========================================
// AUTO-HIDE ALERTS
// ========================================
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-12px)';
        setTimeout(() => {
            alert.style.display = 'none';
        }, 400);
    }, 6000);
});

// ========================================
// KEYBOARD SHORTCUTS
// ========================================
document.addEventListener('keydown', function(e) {
    // Ctrl+Enter to submit form
    if (e.ctrlKey && e.key === 'Enter') {
        const form = document.getElementById('withdrawForm');
        if (form) {
            form.submit();
        }
    }
});

// ========================================
// FORMAT CURRENCY INPUT
// ========================================
document.getElementById('withdraw_amount').addEventListener('blur', function() {
    const val = parseFloat(this.value);
    if (val > 0) {
        this.value = val.toFixed(2);
    }
});

// ========================================
// SHOW PREVIEW ON PAGE LOAD IF HAS VALUE
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('withdraw_amount');
    if (amountInput.value > 0) {
        updateWithdrawPreview(amountInput.value);
    }
});
</script>

</body>
</html>