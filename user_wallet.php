<?php
session_start();
include("../db.php");

// ─── Check login ────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// ─── Fetch user data ────────────────────────────────────────────
$stmt = $conn->prepare("SELECT * FROM contact WHERE id = ? AND role = 'user'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    session_destroy();
    header("Location: login.php");
    exit();
}
$stmt->close();

// ─── Bank accounts (unchanged) ─────────────────────────────────
$bank_accounts = [
    'bank_transfer' => [
        'name'             => 'Bank Transfer',
        'icon'             => '🏦',
        'account_holder'   => 'IncomeCoin Private Limited',
        'account_number'   => '1234567890123456',
        'ifsc'             => 'INDB0001234',
        'bank_name'        => 'State Bank of India',
        'branch'           => 'Main Branch, Mumbai',
        'upi_id'           => '',
        'email'            => '',
        'qr_code'          => ''
    ],
    'upi' => [
        'name'             => 'UPI Payment',
        'icon'             => '📱',
        'account_holder'   => 'IncomeCoin',
        'upi_id'           => 'incomecoin@paytm',
        'qr_code'          => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=incomecoin@paytm',
        'account_number'   => '',
        'ifsc'             => '',
        'bank_name'        => '',
        'branch'           => '',
        'email'            => ''
    ],
    'paypal' => [
        'name'             => 'PayPal',
        'icon'             => '💳',
        'account_holder'   => 'IncomeCoin',
        'email'            => 'payments@incomecoin.com',
        'account_number'   => '',
        'ifsc'             => '',
        'bank_name'        => '',
        'branch'           => '',
        'upi_id'           => '',
        'qr_code'          => ''
    ],
    'credit_card' => [
        'name'             => 'Credit Card',
        'icon'             => '💳',
        'account_holder'   => 'IncomeCoin Private Limited',
        'account_number'   => '1234 5678 9012 3456',
        'bank_name'        => 'HDFC Bank',
        'branch'           => 'Credit Card Department',
        'ifsc'             => '',
        'upi_id'           => '',
        'email'            => '',
        'qr_code'          => ''
    ],
    'debit_card' => [
        'name'             => 'Debit Card',
        'icon'             => '💳',
        'account_holder'   => 'IncomeCoin Private Limited',
        'account_number'   => '9876 5432 1098 7654',
        'bank_name'        => 'ICICI Bank',
        'branch'           => 'Debit Card Department',
        'ifsc'             => '',
        'upi_id'           => '',
        'email'            => '',
        'qr_code'          => ''
    ]
];

// ─── WALLET FUNCTIONS (all rewritten with prepared statements) ──

function getWalletBalance($conn, $user_id) {
    $stmt = $conn->prepare("SELECT SUM(amount) as balance FROM wallet_transactions WHERE user_id = ? AND status = 'completed'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['balance'] ? (float)$row['balance'] : 0.00;
}

function getTotalDeposits($conn, $user_id) {
    $stmt = $conn->prepare("SELECT SUM(amount) as total FROM wallet_transactions WHERE user_id = ? AND type = 'deposit' AND status = 'completed'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total'] ? (float)$row['total'] : 0.00;
}

function getTotalWithdrawals($conn, $user_id) {
    $stmt = $conn->prepare("SELECT SUM(amount) as total FROM wallet_transactions WHERE user_id = ? AND type = 'withdrawal' AND status = 'completed'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total'] ? (float)$row['total'] : 0.00;
}

function getPendingWithdrawals($conn, $user_id) {
    $stmt = $conn->prepare("SELECT SUM(amount) as total FROM wallet_transactions WHERE user_id = ? AND type = 'withdrawal' AND status = 'pending'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total'] ? (float)$row['total'] : 0.00;
}

function getPendingDeposits($conn) {
    $sql = "SELECT w.*, c.name, c.email, c.phone 
            FROM wallet_transactions w 
            JOIN contact c ON c.id = w.user_id 
            WHERE w.type = 'deposit' AND w.status = 'pending' 
            ORDER BY w.id DESC";
    return mysqli_query($conn, $sql);
}

function getTransactionHistory($conn, $user_id, $limit = 10) {
    $stmt = $conn->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY id DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

function addToWallet($conn, $user_id, $amount, $method, $reference = '') {
    $amount = (float)$amount;
    $method = trim($method);
    $reference = trim($reference);
    $stmt = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, method, reference, status, created_at) 
                            VALUES (?, ?, 'deposit', ?, ?, 'pending', NOW())");
    $stmt->bind_param("idss", $user_id, $amount, $method, $reference);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

function approveDeposit($conn, $txn_id) {
    $txn_id = (int)$txn_id;
    $stmt = $conn->prepare("UPDATE wallet_transactions SET status = 'completed' WHERE id = ? AND type = 'deposit'");
    $stmt->bind_param("i", $txn_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

function rejectDeposit($conn, $txn_id) {
    $txn_id = (int)$txn_id;
    $stmt = $conn->prepare("UPDATE wallet_transactions SET status = 'failed' WHERE id = ? AND type = 'deposit'");
    $stmt->bind_param("i", $txn_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

function getPendingDepositCount($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM wallet_transactions WHERE type = 'deposit' AND status = 'pending'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['count'] ? (int)$row['count'] : 0;
}

function requestWithdrawal($conn, $user_id, $amount, $method) {
    $amount = (float)$amount;
    $method = trim($method);
    $balance = getWalletBalance($conn, $user_id);
    if ($amount > $balance) {
        return ['success' => false, 'message' => 'Insufficient balance!'];
    }
    if ($amount < 100) {
        return ['success' => false, 'message' => 'Minimum withdrawal amount is ₹100!'];
    }
    $stmt = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, method, status, created_at) 
                            VALUES (?, ?, 'withdrawal', ?, 'pending', NOW())");
    $stmt->bind_param("ids", $user_id, $amount, $method);
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Withdrawal request submitted successfully!'];
    } else {
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to submit request: ' . $error];
    }
}

// ─── Process form submissions ────────────────────────────────────

$deposit_success = $deposit_error = '';
$withdraw_success = $withdraw_error = '';
$admin_msg = '';

// ── DEPOSIT (only one handler) ──
if (isset($_POST['deposit_submit'])) {
    $amount = (float)$_POST['amount'];
    $method = trim($_POST['payment_method']);
    if ($amount < 100) {
        $deposit_error = 'Minimum deposit amount is ₹100!';
    } elseif ($amount > 50000) {
        $deposit_error = 'Maximum deposit amount is ₹50,000!';
    } else {
        if (addToWallet($conn, $user_id, $amount, $method)) {
            $deposit_success = '₹' . number_format($amount) . ' deposit request submitted! Please transfer the amount to the account details shown below.';
        } else {
            $deposit_error = 'Failed to add money. Please try again!';
        }
    }
    // 🔄 PRG: redirect to clear POST data
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=deposit");
    exit();
}

// ── WITHDRAWAL (added) ──
if (isset($_POST['withdraw_submit'])) {
    $amount = (float)$_POST['withdraw_amount'];
    $method = trim($_POST['withdraw_method']);
    $result = requestWithdrawal($conn, $user_id, $amount, $method);
    if ($result['success']) {
        $withdraw_success = $result['message'];
    } else {
        $withdraw_error = $result['message'];
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=withdraw");
    exit();
}

// Admin approval/rejection
if (isset($_GET['approve']) && isset($_GET['txn_id'])) {
    $txn_id = (int)$_GET['approve'];
    if (approveDeposit($conn, $txn_id)) {
        $admin_msg = "Deposit #$txn_id approved successfully!";
    } else {
        $admin_msg = "Failed to approve deposit!";
    }
}
if (isset($_GET['reject']) && isset($_GET['txn_id'])) {
    $txn_id = (int)$_GET['reject'];
    if (rejectDeposit($conn, $txn_id)) {
        $admin_msg = "Deposit #$txn_id rejected!";
    } else {
        $admin_msg = "Failed to reject deposit!";
    }
}

// ─── Fetch all wallet data ──────────────────────────────────────

$balance              = getWalletBalance($conn, $user_id);
$total_deposits       = getTotalDeposits($conn, $user_id);
$total_withdrawals    = getTotalWithdrawals($conn, $user_id);
$pending_withdrawals  = getPendingWithdrawals($conn, $user_id);
$transactions         = getTransactionHistory($conn, $user_id, 10);
$pending_deposits     = getPendingDeposits($conn);
$pending_count        = getPendingDepositCount($conn);

// ─── Fix undefined $_SESSION['is_admin'] ────────────────────────
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// ─── Show success/error messages from redirect ──────────────────
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'deposit') {
        // messages already set in session? We can use session flash instead, but for simplicity we keep as is.
        // Since we redirect, the variables are lost. We'll use session flash to persist messages.
        // Better to store messages in session and display after redirect.
        // For quick fix, we can use session variables.
        session_start(); // already started
        if ($deposit_success) {
            $_SESSION['flash_success'] = $deposit_success;
        } elseif ($deposit_error) {
            $_SESSION['flash_error'] = $deposit_error;
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif ($_GET['status'] == 'withdraw') {
        if ($withdraw_success) {
            $_SESSION['flash_success'] = $withdraw_success;
        } elseif ($withdraw_error) {
            $_SESSION['flash_error'] = $withdraw_error;
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// ── Retrieve flash messages ──
$flash_success = isset($_SESSION['flash_success']) ? $_SESSION['flash_success'] : '';
$flash_error = isset($_SESSION['flash_error']) ? $_SESSION['flash_error'] : '';
unset($_SESSION['flash_success']);
unset($_SESSION['flash_error']);

// Use flash messages if present, else use variables from processing (which may be empty after redirect)
if ($flash_success) {
    $deposit_success = $flash_success;
    $withdraw_success = $flash_success;
} elseif ($flash_error) {
    $deposit_error = $flash_error;
    $withdraw_error = $flash_error;
}

// ─── End of PHP, HTML starts ────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet | Thunder x</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ========================================
           ROOT VARIABLES – DARK THEME
        ======================================== */
        :root {
            --primary: #3b82f6;
            --primary-dark: #1d4ed8;
            --primary-light: #60a5fa;
            --primary-glow: rgba(59, 130, 246, 0.35);

            --gold: #D4AF37;
            --gold-dark: #B8960F;
            --gold-light: #F0D060;
            --gold-glow: rgba(212, 175, 55, 0.25);

            --bg-primary: #070b14;
            --bg-secondary: #0f172a;
            --bg-card: rgba(255, 255, 255, 0.03);
            --bg-card-hover: rgba(255, 255, 255, 0.06);

            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #475569;

            --border-color: rgba(59, 130, 246, 0.12);
            --shadow-card: 0 8px 32px rgba(0, 0, 0, 0.6);

            --success: #22c55e;
            --success-light: rgba(34, 197, 94, 0.12);
            --warning: #f59e0b;
            --warning-light: rgba(245, 158, 11, 0.12);
            --danger: #ef4444;
            --danger-light: rgba(239, 68, 68, 0.12);

            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-2xl: 24px;

            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* ========================================
           RESET & BASE
        ======================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: var(--bg-primary);
            color: var(--text-primary);
            background-image:
                radial-gradient(ellipse at 10% 20%, rgba(59, 130, 246, 0.06) 0%, transparent 60%),
                radial-gradient(ellipse at 90% 80%, rgba(212, 175, 55, 0.04) 0%, transparent 60%);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            padding-bottom: env(safe-area-inset-bottom);
        }

        .wallet-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* ========================================
           NAVBAR (same as dashboard)
        ======================================== */
        .navbar {
            background: var(--bg-secondary);
            border-radius: var(--radius-xl);
            padding: 14px 28px;
            box-shadow: var(--shadow-card);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 16px;
            border: 1px solid var(--border-color);
        }

        .navbar .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar .logo .logo-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
            box-shadow: 0 4px 12px var(--gold-glow);
        }

        .navbar .logo h2 {
            font-size: 22px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--gold-light), var(--gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar .nav-links {
            display: flex;
            gap: 4px;
            align-items: center;
            flex-wrap: wrap;
        }

        .navbar .nav-links a {
            text-decoration: none;
            color: var(--text-secondary);
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
            background: rgba(59, 130, 246, 0.08);
            color: var(--primary-light);
        }

        .navbar .nav-links a.active {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #fff;
            box-shadow: 0 4px 15px var(--gold-glow);
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
            color: var(--text-primary);
            font-size: 14px;
        }

        .navbar .user-info .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2.5px solid var(--gold);
        }

        .pending-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 30px;
            padding: 6px 16px;
            color: #ef4444;
            font-size: 13px;
            font-weight: 600;
        }

        .pending-badge .count {
            background: #ef4444;
            color: #fff;
            border-radius: 50%;
            padding: 0 8px;
            font-size: 11px;
            min-width: 20px;
            text-align: center;
        }

        /* ========================================
           WALLET HEADER
        ======================================== */
        .wallet-header {
            background: linear-gradient(135deg, #1a2a4a, #0a0e1a);
            border-radius: var(--radius-2xl);
            padding: 40px 48px;
            color: #fff;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 50px rgba(0,0,0,0.35);
            border: 1px solid rgba(212, 175, 55, 0.1);
        }

        .wallet-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -5%;
            width: 300px;
            height: 300px;
            background: rgba(212, 175, 55, 0.05);
            border-radius: 50%;
        }

        .wallet-header .wallet-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            position: relative;
            z-index: 1;
        }

        .wallet-header h1 {
            font-size: 28px;
            font-weight: 800;
        }

        .wallet-header h1 i {
            margin-right: 12px;
            color: var(--gold);
        }

        .wallet-header .balance-box {
            background: rgba(255,255,255,0.06);
            padding: 16px 28px;
            border-radius: var(--radius-md);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(212, 175, 55, 0.15);
            text-align: center;
            min-width: 200px;
        }

        .wallet-header .balance-box .label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.6;
        }

        .wallet-header .balance-box .amount {
            font-size: 36px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--gold-light), var(--gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ========================================
           ADMIN PANEL
        ======================================== */
        .admin-panel {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 24px;
            backdrop-filter: blur(8px);
            margin-bottom: 24px;
            border-left: 4px solid var(--gold);
        }

        .admin-panel .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .admin-panel .admin-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-panel .admin-header h3 i {
            color: var(--gold);
        }

        .admin-panel .admin-header .badge-count {
            background: #ef4444;
            color: #fff;
            border-radius: 50%;
            padding: 2px 10px;
            font-size: 12px;
            font-weight: 700;
        }

        .admin-panel table td .btn-approve {
            background: var(--success);
            color: #fff;
            border: none;
            padding: 4px 12px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }

        .admin-panel table td .btn-approve:hover {
            background: #16a34a;
            transform: scale(1.05);
        }

        .admin-panel table td .btn-reject {
            background: var(--danger);
            color: #fff;
            border: none;
            padding: 4px 12px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }

        .admin-panel table td .btn-reject:hover {
            background: #dc2626;
            transform: scale(1.05);
        }

        /* ========================================
           STATS CARDS
        ======================================== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 20px 22px;
            transition: var(--transition);
            backdrop-filter: blur(8px);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: var(--gold);
            box-shadow: 0 0 30px var(--gold-glow);
            background: var(--bg-card-hover);
        }

        .stat-card .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .stat-card .stat-icon.green {
            background: var(--success-light);
            color: var(--success);
        }
        .stat-card .stat-icon.orange {
            background: var(--warning-light);
            color: var(--warning);
        }
        .stat-card .stat-icon.gold {
            background: rgba(212, 175, 55, 0.12);
            color: var(--gold);
        }
        .stat-card .stat-icon.purple {
            background: rgba(139, 92, 246, 0.12);
            color: #8b5cf6;
        }

        .stat-card .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-card .stat-label {
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* ========================================
           CONTENT GRID
        ======================================== */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        /* ========================================
           FORMS
        ======================================== */
        .form-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 28px;
            backdrop-filter: blur(8px);
        }

        .form-box h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-box h3 i {
            color: var(--gold);
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 6px;
            font-size: 13px;
        }

        .form-group label i {
            margin-right: 6px;
            color: var(--gold);
        }

     .form-group input,
.form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-sm);
    font-size: 15px;
    transition: var(--transition);

    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;

    outline: none;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #ff3333;
    background: rgba(255, 255, 255, 0.12);
    box-shadow: 0 0 0 3px rgba(255, 51, 51, 0.15);
}

.form-group input::placeholder {
    color: rgba(255, 255, 255, 0.55);
}

.form-group select option {
    background: #1a1a1a;
    color: #ffffff;
}

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--gold);
            background: rgba(255, 255, 255, 0.06);
            box-shadow: 0 0 0 4px var(--gold-glow);
        }

        /* ========================================
           ACCOUNT DETAILS CARD
        ======================================== */
        .account-details-card {
            background: rgba(212, 175, 55, 0.05);
            border: 1px solid rgba(212, 175, 55, 0.15);
            border-radius: var(--radius-md);
            padding: 16px 20px;
            margin-top: 14px;
            display: none;
            animation: slideDown 0.4s ease;
        }

        .account-details-card.active {
            display: block;
        }

        .account-details-card .account-title {
            font-weight: 700;
            color: var(--gold);
            font-size: 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .account-details-card .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px dashed rgba(212, 175, 55, 0.1);
            font-size: 13px;
        }

        .account-details-card .detail-row:last-child {
            border-bottom: none;
        }

        .account-details-card .detail-label {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .account-details-card .detail-value {
            color: var(--text-primary);
            font-weight: 600;
        }

        .account-details-card .detail-value.copyable {
            cursor: pointer;
            transition: var(--transition);
        }

        .account-details-card .detail-value.copyable:hover {
            color: var(--gold);
        }

        .account-details-card .upi-qr {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 10px;
        }

        .account-details-card .upi-qr img {
            width: 100px;
            height: 100px;
            border-radius: var(--radius-sm);
            border: 2px solid rgba(212, 175, 55, 0.2);
        }

        .copy-btn {
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: var(--radius-sm);
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 600;
            color: var(--gold);
            cursor: pointer;
            transition: var(--transition);
        }

        .copy-btn:hover {
            background: rgba(212, 175, 55, 0.2);
        }

        /* ========================================
           BUTTONS
        ======================================== */
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #fff;
            box-shadow: 0 4px 16px var(--gold-glow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px var(--gold-glow);
        }

        .btn-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            box-shadow: 0 4px 16px rgba(34, 197, 94, 0.2);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(34, 197, 94, 0.35);
        }

        /* ========================================
           ALERTS
        ======================================== */
        .alert {
            padding: 12px 16px;
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
            color: #86efac;
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background: var(--danger-light);
            color: #fca5a5;
            border-left: 4px solid var(--danger);
        }

        .alert-info {
            background: rgba(212, 175, 55, 0.1);
            color: var(--gold);
            border-left: 4px solid var(--gold);
        }

        /* ========================================
           TRANSACTIONS TABLE
        ======================================== */
        .transactions-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 28px;
            backdrop-filter: blur(8px);
            grid-column: 1 / -1;
        }

        .transactions-box .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .transactions-box .section-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .transactions-box .section-header h3 i {
            color: var(--gold);
        }

        .transactions-box .section-header a {
            color: var(--gold);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-secondary);
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }

        table td {
            padding: 12px 16px;
            color: var(--text-secondary);
            font-size: 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }

        table tbody tr:hover {
            background: rgba(59, 130, 246, 0.04);
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: var(--success-light);
            color: #86efac;
        }

        .badge-warning {
            background: var(--warning-light);
            color: #fbbf24;
        }

        .badge-danger {
            background: var(--danger-light);
            color: #fca5a5;
        }

        .badge-primary {
            background: rgba(59, 130, 246, 0.12);
            color: var(--primary-light);
        }

        .text-success {
            color: var(--success);
        }
        .text-danger {
            color: var(--danger);
        }
        .text-muted {
            color: var(--text-muted);
        }

        .no-transactions {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .no-transactions i {
            font-size: 48px;
            opacity: 0.2;
            display: block;
            margin-bottom: 12px;
        }

        .info-note {
            margin-top: 12px;
            font-size: 12px;
            color: var(--text-secondary);
            text-align: center;
            padding: 8px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: var(--radius-sm);
        }

        .info-note i {
            color: var(--gold);
        }

        /* ============================================================
           MOBILE BOTTOM NAVIGATION – ONLY ON PHONES (max-width: 768px)
           ============================================================ */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2000;

            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(18px) saturate(1.2);
            -webkit-backdrop-filter: blur(18px) saturate(1.2);
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: var(--radius-2xl);
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.7), inset 0 1px 0 rgba(255, 255, 255, 0.04);

            padding: 8px 10px;
            width: 92%;
            max-width: 420px;

            align-items: center;
            justify-content: space-around;
            gap: 2px;

            transition: var(--transition-bounce);
            animation: navSlideUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        @keyframes navSlideUp {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0) scale(1);
            }
        }

        .mobile-bottom-nav .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            padding: 6px 10px;
            border-radius: var(--radius-lg);
            position: relative;
            transition: var(--transition-bounce);
            min-width: 54px;
            cursor: pointer;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            -webkit-tap-highlight-color: transparent;
        }

        .mobile-bottom-nav .nav-item i {
            font-size: 22px;
            transition: var(--transition-bounce);
            color: var(--text-muted);
            line-height: 1;
        }

        .mobile-bottom-nav .nav-item .nav-label {
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 0.3px;
            margin-top: 3px;
            opacity: 0;
            transform: scale(0.8);
            transition: var(--transition-bounce);
            color: var(--primary-light);
            text-transform: uppercase;
            pointer-events: none;
            white-space: nowrap;
        }

        .mobile-bottom-nav .nav-item.active {
            background: rgba(59, 130, 246, 0.10);
            border: 1px solid rgba(59, 130, 246, 0.12);
        }
        .mobile-bottom-nav .nav-item.active i {
            color: var(--primary-light);
            transform: translateY(-2px) scale(1.05);
            filter: drop-shadow(0 0 10px var(--primary-glow));
        }
        .mobile-bottom-nav .nav-item.active .nav-label {
            opacity: 1;
            transform: scale(1);
        }

        .mobile-bottom-nav .nav-item:not(.active):hover i {
            color: var(--primary-light);
            transform: translateY(-1px);
        }

        .mobile-bottom-nav .add-btn-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 2px;
        }

        .mobile-bottom-nav .add-btn {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #fff;
            border: none;
            font-size: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 24px var(--gold-glow), inset 0 1px 0 rgba(255, 255, 255, 0.2);
            cursor: pointer;
            transition: var(--transition-bounce);
            text-decoration: none;
            position: relative;
        }

        .mobile-bottom-nav .add-btn::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: var(--gold-glow);
            opacity: 0.3;
            filter: blur(12px);
            z-index: -1;
            transition: var(--transition);
        }

        .mobile-bottom-nav .add-btn:hover {
            transform: scale(1.08) rotate(90deg);
            box-shadow: 0 6px 32px var(--gold-glow);
        }
        .mobile-bottom-nav .add-btn:active {
            transform: scale(0.94);
        }

        .mobile-bottom-nav .nav-item .indicator-dot {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--primary);
            margin-top: 3px;
            opacity: 0;
            transform: scale(0.5);
            transition: var(--transition-bounce);
            box-shadow: 0 0 8px var(--primary-glow);
        }
        .mobile-bottom-nav .nav-item.active .indicator-dot {
            opacity: 1;
            transform: scale(1);
        }

        /* ========================================
           RESPONSIVE
        ======================================== */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .wallet-container {
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

            .wallet-header {
                padding: 24px 20px;
                border-radius: var(--radius-lg);
            }

            .wallet-header .wallet-top {
                flex-direction: column;
                text-align: center;
            }

            .wallet-header h1 {
                font-size: 22px;
            }

            .wallet-header .balance-box .amount {
                font-size: 28px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .stat-card {
                padding: 16px;
            }

            .stat-card .stat-number {
                font-size: 20px;
            }

            .form-box {
                padding: 20px;
            }

            .transactions-box {
                padding: 20px;
                overflow-x: auto;
            }

            .admin-panel {
                padding: 16px;
                overflow-x: auto;
            }

            table {
                min-width: 600px;
            }

            .account-details-card .upi-qr {
                flex-direction: column;
                text-align: center;
            }

            .account-details-card .detail-row {
                flex-direction: column;
                gap: 2px;
            }

            /* Show bottom nav only on phones */
            .mobile-bottom-nav {
                display: flex !important;
                padding: 6px 8px;
                max-width: 360px;
            }
            .mobile-bottom-nav .nav-item {
                min-width: 46px;
                padding: 4px 6px;
            }
            .mobile-bottom-nav .nav-item i {
                font-size: 20px;
            }
            .mobile-bottom-nav .add-btn {
                width: 46px;
                height: 46px;
                font-size: 22px;
            }
            .mobile-bottom-nav .nav-item .nav-label {
                font-size: 8px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .wallet-header .balance-box {
                width: 100%;
            }

            .navbar .nav-links a {
                font-size: 11px;
                padding: 4px 10px;
            }

            .mobile-bottom-nav {
                padding: 5px 6px;
                max-width: 320px;
                bottom: 14px;
                border-radius: var(--radius-xl);
            }
            .mobile-bottom-nav .nav-item {
                min-width: 40px;
                padding: 4px 4px;
            }
            .mobile-bottom-nav .nav-item i {
                font-size: 18px;
            }
            .mobile-bottom-nav .add-btn {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
            .mobile-bottom-nav .nav-item .nav-label {
                font-size: 7px;
                margin-top: 2px;
            }
            .mobile-bottom-nav .nav-item .indicator-dot {
                width: 3px;
                height: 3px;
                margin-top: 2px;
            }
        }

        /* ========================================
           SCROLLBAR
        ======================================== */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--gold);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--gold-dark);
        }
    </style>
</head>
<body>

<div class="wallet-container">

    <!-- ===== NAVBAR ===== -->
    <nav class="navbar">
        <div class="logo">
            <div class="logo-icon"><i class="fas fa-cubes"></i></div>
            <h2>Thunder X</h2>
        </div>
        
        <div class="nav-links">
            <a href="profile_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="user_profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="user_wallet.php" class="active"><i class="fas fa-wallet"></i> Wallet</a>
            <?php if($pending_count > 0): ?>
                <span class="pending-badge">
                    <i class="fas fa-bell"></i>
                    <span class="count"><?php echo $pending_count; ?></span> Pending Deposits
                </span>
            <?php endif; ?>
            <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($row['name']); ?></span>
            <?php 
            $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($row['name']) . "&background=D4AF37&color=fff&size=42&bold=true";
            ?>
            <img src="<?php echo $avatar_url; ?>" alt="Avatar" class="user-avatar">
        </div>
    </nav>

    <!-- ===== ADMIN PANEL - PENDING DEPOSITS ===== -->
    <?php if($pending_count > 0 && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
    <div class="admin-panel">
        <div class="admin-header">
            <h3><i class="fas fa-clock"></i> Pending Deposits</h3>
            <span class="badge-count"><?php echo $pending_count; ?></span>
        </div>

        <?php if(isset($admin_msg)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $admin_msg; ?>
            </div>
        <?php endif; ?>

        <?php if($pending_deposits && mysqli_num_rows($pending_deposits) > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($dep = mysqli_fetch_assoc($pending_deposits)): ?>
                            <tr>
                                <td>#<?php echo str_pad($dep['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($dep['name']); ?></strong><br>
                                    <small style="color:var(--text-muted);font-size:11px;"><?php echo $dep['email']; ?></small>
                                </td>
                                <td><strong>₹<?php echo number_format($dep['amount'], 2); ?></strong></td>
                                <td><span style="text-transform:capitalize;"><?php echo str_replace('_', ' ', $dep['method']); ?></span></td>
                                <td><?php echo date('d M Y', strtotime($dep['created_at'])); ?></td>
                                <td>
                                    <a href="?approve=<?php echo $dep['id']; ?>" class="btn-approve" onclick="return confirm('Approve this deposit?')">
                                        <i class="fas fa-check"></i> Approve
                                    </a>
                                    <a href="?reject=<?php echo $dep['id']; ?>" class="btn-reject" onclick="return confirm('Reject this deposit?')">
                                        <i class="fas fa-times"></i> Reject
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-transactions">
                <i class="fas fa-check-circle" style="color:var(--success);"></i>
                <p>No pending deposits</p>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ===== WALLET HEADER ===== -->
    <div class="wallet-header">
        <div class="wallet-top">
            <div>
                <h1><i class="fas fa-wallet"></i> My Wallet</h1>
                <p style="opacity:0.6;margin-top:4px;">Manage your funds and transactions</p>
            </div>
            <div class="balance-box">
                <div class="label">Available Balance</div>
                <div class="amount">₹<?php echo number_format($balance, 2); ?></div>
            </div>
        </div>
    </div>

    <!-- ===== STATS CARDS ===== -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-arrow-down"></i></div>
            <div class="stat-number">₹<?php echo number_format($total_deposits, 2); ?></div>
            <div class="stat-label">Total Deposits</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-arrow-up"></i></div>
            <div class="stat-number">₹<?php echo number_format($total_withdrawals, 2); ?></div>
            <div class="stat-label">Total Withdrawals</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-clock"></i></div>
            <div class="stat-number">₹<?php echo number_format($pending_withdrawals, 2); ?></div>
            <div class="stat-label">Pending Withdrawals</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-exchange-alt"></i></div>
            <div class="stat-number"><?php echo mysqli_num_rows($transactions); ?></div>
            <div class="stat-label">Total Transactions</div>
        </div>
    </div>

    <!-- ===== CONTENT GRID ===== -->
    <div class="content-grid">

        <!-- ===== DEPOSIT FORM ===== -->
        <div class="form-box">
            <h3><i class="fas fa-plus-circle"></i> Add Money</h3>

            <?php if($deposit_success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $deposit_success; ?>
                </div>
            <?php endif; ?>

            <?php if($deposit_error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $deposit_error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="depositForm">
                <div class="form-group">
                    <label><i class="fas fa-coins"></i> Amount (₹)</label>
                    <input type="number" name="amount" id="depositAmount" min="100" max="50000" placeholder="Enter amount" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-credit-card"></i> Payment Method</label>
                    <select name="payment_method" id="paymentMethod" required onchange="showAccountDetails()">
                        <option value="">Select Payment Method</option>
                        <option value="bank_transfer">🏦 Bank Transfer</option>
                        <option value="upi">📱 UPI</option>
                        <option value="paypal">💰 PayPal</option>
                        <option value="credit_card">💳 Credit Card</option>
                        <option value="debit_card">💳 Debit Card</option>
                    </select>
                </div>

                <!-- ===== BANK ACCOUNT DETAILS ===== -->
                <div id="accountDetails" class="account-details-card">
                    <div class="account-title">
                        <i class="fas fa-university"></i>
                        <span id="accountTitle">Bank Account Details</span>
                    </div>
                    <div id="accountContent"></div>
                </div>

                <button type="submit" name="deposit_submit" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Money
                </button>
            </form>
        </div>

        <!-- ===== WITHDRAWAL FORM ===== -->
        <div class="form-box">
            <h3><i class="fas fa-arrow-up-right-from-square"></i> Withdraw Money</h3>

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

            <form method="POST" action="">
                <div class="form-group">
                    <label><i class="fas fa-coins"></i> Amount (₹)</label>
                    <input type="number" name="withdraw_amount" min="100" placeholder="Enter amount" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-university"></i> Withdraw Method</label>
                    <select name="withdraw_method" required>
                        <option value="bank_transfer">🏦 Bank Transfer</option>
                        <option value="upi">📱 UPI</option>
                        <option value="paypal">💰 PayPal</option>
                    </select>
                </div>

                <button type="submit" name="withdraw_submit" class="btn btn-primary">
                    <i class="fas fa-arrow-up"></i> Withdraw Money
                </button>
            </form>

            <div class="info-note">
                <i class="fas fa-info-circle"></i> Minimum withdrawal: ₹100 | Processing time: 24-48 hours
            </div>
        </div>

    </div>

    <!-- ===== TRANSACTIONS TABLE ===== -->
    <div class="transactions-box">
        <div class="section-header">
            <h3><i class="fas fa-history"></i> Transaction History</h3>
            <a href="#">View All <i class="fas fa-arrow-right"></i></a>
        </div>

        <?php if($transactions && mysqli_num_rows($transactions) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
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
                            <td><span style="text-transform:capitalize;"><?php echo str_replace('_', ' ', $txn['method']); ?></span></td>
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
                            <td class="text-muted"><?php echo date('d M Y, h:i A', strtotime($txn['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-transactions">
                <i class="fas fa-receipt"></i>
                <p>No transactions found</p>
                <p style="font-size:13px;opacity:0.6;">Start by adding money to your wallet</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ============================================================
     MOBILE BOTTOM NAVIGATION (only on phones)
     ============================================================ -->
<nav class="mobile-bottom-nav" id="bottomNav" role="navigation" aria-label="Main Navigation">

    <!-- 1. Home -->
    <a href="user_dashboard.php" class="nav-item" data-tooltip="Home">
        <i class="fas fa-home"></i>
        <span class="nav-label">Home</span>
        <span class="indicator-dot"></span>
    </a>

    <!-- 2. Wallet (active) -->
    <a href="user_wallet.php" class="nav-item active" data-tooltip="Wallet">
        <i class="fas fa-wallet"></i>
        <span class="nav-label">Wallet</span>
        <span class="indicator-dot"></span>
    </a>

    <!-- 3. FAB – New Investment (opens investment page) -->
    <div class="add-btn-wrapper">
        <a href="user_investment.php" class="add-btn" aria-label="New Investment">
            <i class="fas fa-plus"></i>
        </a>
    </div>

    <!-- 4. Products -->
    <a href="../product.php" class="nav-item" data-tooltip="Products">
        <i class="fas fa-motorcycle"></i>
        <span class="nav-label">Products</span>
        <span class="indicator-dot"></span>
    </a>

    <!-- 5. Profile -->
    <a href="user_profile.php" class="nav-item" data-tooltip="Profile">
        <i class="fas fa-user"></i>
        <span class="nav-label">Profile</span>
        <span class="indicator-dot"></span>
    </a>

</nav>

<!-- ===== JAVASCRIPT ===== -->
<script>
    // ========================================
    // SHOW ACCOUNT DETAILS BASED ON PAYMENT METHOD
    // ========================================
    const bankAccounts = <?php echo json_encode($bank_accounts); ?>;

    function showAccountDetails() {
        const method = document.getElementById('paymentMethod').value;
        const detailsDiv = document.getElementById('accountDetails');
        const contentDiv = document.getElementById('accountContent');
        const titleSpan = document.getElementById('accountTitle');

        if (!method || method === '') {
            detailsDiv.classList.remove('active');
            return;
        }

        const account = bankAccounts[method];
        if (!account) {
            detailsDiv.classList.remove('active');
            return;
        }

        detailsDiv.classList.add('active');
        titleSpan.textContent = account.name + ' Details';

        let html = '';

        // Bank Transfer
        if (method === 'bank_transfer') {
            html = `
                <div class="detail-row">
                    <span class="detail-label">Account Holder</span>
                    <span class="detail-value">${account.account_holder}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Account Number</span>
                    <span class="detail-value copyable" onclick="copyText('${account.account_number}')">
                        ${account.account_number}
                        <button class="copy-btn" onclick="event.stopPropagation(); copyText('${account.account_number}')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">IFSC Code</span>
                    <span class="detail-value copyable" onclick="copyText('${account.ifsc}')">
                        ${account.ifsc}
                        <button class="copy-btn" onclick="event.stopPropagation(); copyText('${account.ifsc}')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Bank Name</span>
                    <span class="detail-value">${account.bank_name}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Branch</span>
                    <span class="detail-value">${account.branch}</span>
                </div>
                <div class="alert alert-info" style="margin-top:10px;font-size:12px;padding:8px 12px;">
                    <i class="fas fa-info-circle"></i> 
                    Please transfer the exact amount and use your User ID <strong>#<?php echo $user_id; ?></strong> as reference.
                </div>
            `;
        }

        // UPI
        else if (method === 'upi') {
            html = `
                <div class="upi-qr">
                    <img src="${account.qr_code}" alt="UPI QR Code">
                    <div class="upi-info">
                        <div class="detail-row">
                            <span class="detail-label">UPI ID</span>
                            <span class="detail-value copyable" onclick="copyText('${account.upi_id}')">
                                ${account.upi_id}
                                <button class="copy-btn" onclick="event.stopPropagation(); copyText('${account.upi_id}')">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Account Holder</span>
                            <span class="detail-value">${account.account_holder}</span>
                        </div>
                        <div class="alert alert-info" style="margin-top:10px;font-size:12px;padding:8px 12px;">
                            <i class="fas fa-info-circle"></i> 
                            Scan QR code or pay to UPI ID. Use User ID <strong>#<?php echo $user_id; ?></strong> as reference.
                        </div>
                    </div>
                </div>
            `;
        }

        // PayPal
        else if (method === 'paypal') {
            html = `
                <div class="detail-row">
                    <span class="detail-label">PayPal Email</span>
                    <span class="detail-value copyable" onclick="copyText('${account.email}')">
                        ${account.email}
                        <button class="copy-btn" onclick="event.stopPropagation(); copyText('${account.email}')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Account Holder</span>
                    <span class="detail-value">${account.account_holder}</span>
                </div>
                <div class="alert alert-info" style="margin-top:10px;font-size:12px;padding:8px 12px;">
                    <i class="fas fa-info-circle"></i> 
                    Send payment to the above PayPal email. Use User ID <strong>#<?php echo $user_id; ?></strong> as reference.
                </div>
            `;
        }

        // Credit Card / Debit Card
        else if (method === 'credit_card' || method === 'debit_card') {
            html = `
                <div class="detail-row">
                    <span class="detail-label">Account Holder</span>
                    <span class="detail-value">${account.account_holder}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Card Number</span>
                    <span class="detail-value">${account.account_number}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Bank Name</span>
                    <span class="detail-value">${account.bank_name}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Department</span>
                    <span class="detail-value">${account.branch}</span>
                </div>
                <div class="alert alert-info" style="margin-top:10px;font-size:12px;padding:8px 12px;">
                    <i class="fas fa-info-circle"></i> 
                    Payment will be processed through ${account.name}. Use User ID <strong>#<?php echo $user_id; ?></strong> as reference.
                </div>
            `;
        }

        contentDiv.innerHTML = html;
    }

    // ========================================
    // COPY TEXT FUNCTION
    // ========================================
    function copyText(text) {
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.querySelector('.copy-btn');
            if (btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                }, 2000);
            }
        }).catch(() => {
            // Fallback
            const input = document.createElement('input');
            input.value = text;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            alert('Copied: ' + text);
        });
    }

    // ========================================
    // AUTO SHOW ACCOUNT DETAILS ON PAGE LOAD
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        const methodSelect = document.getElementById('paymentMethod');
        if (methodSelect && methodSelect.value) {
            showAccountDetails();
        }
    });

    // ========================================
    // BOTTOM NAV ACTIVE STATE (optional)
    // ========================================
    const navItems = document.querySelectorAll('.mobile-bottom-nav .nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            navItems.forEach(n => n.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>

</body>
</html>