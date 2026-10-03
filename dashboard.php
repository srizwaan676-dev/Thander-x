<?php
include("db.php");

// ========================================
// FUNCTIONS WITH ERROR HANDLING
// ========================================

// Get total users
function getTotalUsers($conn) {
    $sql = "SELECT * FROM contact";
    $result = mysqli_query($conn, $sql);
    if($result) {
        return mysqli_num_rows($result);
    }
    return 0;
}

// Get total products
function getTotalProducts($conn) {
    $sql = "SELECT * FROM products";
    $result = mysqli_query($conn, $sql);
    if($result) {
        return mysqli_num_rows($result);
    }
    return 0;
}

// Get total income
function getTotalIncome($conn) {
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'investments'");
    if($table_check && mysqli_num_rows($table_check) == 0) {
        return 0;
    }
    
    $sql = "SELECT SUM(amount) as total FROM investments";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

// Get total investment
function getTotalInvestment($conn) {
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'investments'");
    if($table_check && mysqli_num_rows($table_check) == 0) {
        return 0;
    }
    
    $sql = "SELECT SUM(investment_amount) as total FROM investments";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

// Get recent users
function getRecentUsers($conn, $limit = 10) {
    $sql = "SELECT * FROM contact ORDER BY id DESC LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    if($result) {
        return $result;
    }
    return null;
}

// Get monthly income data
function getMonthlyIncome($conn) {
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'investments'");
    if($table_check && mysqli_num_rows($table_check) == 0) {
        return array_fill(0, 12, 0);
    }
    
    $sql = "SELECT 
            MONTH(created_at) as month_num,
            SUM(amount) as total 
            FROM investments 
            WHERE YEAR(created_at) = YEAR(CURDATE())
            GROUP BY MONTH(created_at)
            ORDER BY MONTH(created_at)";
    $result = mysqli_query($conn, $sql);
    
    $data = array_fill(0, 12, 0);
    
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $monthIndex = $row['month_num'] - 1;
            $data[$monthIndex] = $row['total'];
        }
    }
    
    return $data;
}

// Get monthly users data
function getMonthlyUsers($conn) {
    $sql = "SELECT 
            MONTH(created_at) as month_num,
            COUNT(*) as total 
            FROM contact 
            WHERE YEAR(created_at) = YEAR(CURDATE())
            GROUP BY MONTH(created_at)
            ORDER BY MONTH(created_at)";
    $result = mysqli_query($conn, $sql);
    
    $data = array_fill(0, 12, 0);
    
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $monthIndex = $row['month_num'] - 1;
            $data[$monthIndex] = $row['total'];
        }
    }
    
    return $data;
}

// Get notifications
function getNotifications($conn) {
    $notifications = [];
    
    $sql = "SELECT COUNT(*) as count FROM contact WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if($row['count'] > 0) {
            $notifications[] = ["icon" => "fa-user-plus", "text" => $row['count'] . " new user(s) registered", "time" => "Just now", "type" => "success"];
        }
    }
    
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'investments'");
    if($table_check && mysqli_num_rows($table_check) > 0) {
        $sql = "SELECT COUNT(*) as count FROM investments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $result = mysqli_query($conn, $sql);
        if($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if($row['count'] > 0) {
                $notifications[] = ["icon" => "fa-coins", "text" => $row['count'] . " new investment(s) received", "time" => "1 hour ago", "type" => "info"];
            }
        }
    }
    
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'products'");
    if($table_check && mysqli_num_rows($table_check) > 0) {
        $sql = "SELECT COUNT(*) as count FROM products WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $result = mysqli_query($conn, $sql);
        if($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if($row['count'] > 0) {
                $notifications[] = ["icon" => "fa-box", "text" => $row['count'] . " new product(s) added", "time" => "2 hours ago", "type" => "warning"];
            }
        }
    }
    
    if(empty($notifications)) {
        $notifications[] = ["icon" => "fa-check-circle", "text" => "Everything is up to date!", "time" => "Now", "type" => "success"];
    }
    
    return $notifications;
}

// Get additional stats
function getTodayUsers($conn) {
    $sql = "SELECT COUNT(*) as count FROM contact WHERE DATE(created_at) = CURDATE()";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    return 0;
}

function getTotalWithdrawals($conn) {
    $sql = "SELECT SUM(amount) as total FROM wallet_transactions WHERE type = 'withdrawal' AND status = 'completed'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

function getPendingWithdrawals($conn) {
    $sql = "SELECT COUNT(*) as count FROM wallet_transactions WHERE type = 'withdrawal' AND status = 'pending'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    return 0;
}

function getWalletBalance($conn) {
    $sql = "SELECT SUM(amount) as balance FROM wallet_transactions WHERE status = 'completed'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['balance'] ? $row['balance'] : 0;
    }
    return 0;
}

// ========================================
// EXECUTE FUNCTIONS
// ========================================

$total_users = getTotalUsers($conn);
$total_products = getTotalProducts($conn);
$total_income = getTotalIncome($conn);
$total_investment = getTotalInvestment($conn);
$recent_users = getRecentUsers($conn, 10);
$monthly_income = getMonthlyIncome($conn);
$monthly_users = getMonthlyUsers($conn);
$notifications = getNotifications($conn);
$today_users = getTodayUsers($conn);
$total_withdrawals = getTotalWithdrawals($conn);
$pending_withdrawals = getPendingWithdrawals($conn);
$wallet_balance = getWalletBalance($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IncomeCoin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    /* ========================================
           ROOT VARIABLES
        ======================================== */
    :root {
        --primary: #2ebf3f;
        --primary-dark: #1a8a2a;
        --primary-light: #66ff66;
        --primary-gradient: linear-gradient(135deg, #2ebf3f 0%, #1a8a2a 100%);
        --primary-glow: rgba(46, 191, 63, 0.3);

        --secondary: #8b5cf6;
        --secondary-gradient: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);

        --success: #22c55e;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #06b6d4;

        --bg-dark: #0a0f1f;
        --bg-card: #111827;
        --bg-sidebar: #0d1528;
        --bg-hover: #1a2744;
        --bg-input: rgba(255, 255, 255, 0.05);

        --text-primary: #f1f5f9;
        --text-secondary: #94a3b8;
        --text-muted: #64748b;

        --border-color: rgba(255, 255, 255, 0.06);
        --border-light: rgba(255, 255, 255, 0.1);

        --shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.5);
        --shadow-glow: 0 0 40px rgba(46, 191, 63, 0.1);

        --radius: 16px;
        --radius-sm: 10px;
        --radius-lg: 20px;

        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: var(--bg-dark);
        color: var(--text-primary);
        min-height: 100vh;
        overflow-x: hidden;
    }

    .container {
        display: flex;
        min-height: 100vh;
    }

    /* ========================================
           SCROLLBAR
        ======================================== */
    ::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }

    ::-webkit-scrollbar-track {
        background: var(--bg-dark);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--primary-dark);
    }

    /* ========================================
           SIDEBAR
        ======================================== */
    .sidebar {
        width: 280px;
        background: var(--bg-sidebar);
        border-right: 1px solid var(--border-color);
        position: fixed;
        height: 100%;
        padding: 24px 20px;
        overflow-y: auto;
        z-index: 1000;
        transition: var(--transition);
    }

    .sidebar::-webkit-scrollbar {
        width: 3px;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: var(--primary);
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 35px;
        padding: 0 8px;
    }

    .logo-img {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background: var(--primary-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #fff;
        font-weight: 800;
        box-shadow: 0 4px 20px var(--primary-glow);
    }

    .logo h2 {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.5px;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .logo p {
        color: var(--text-muted);
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 1px;
        margin-top: -2px;
        -webkit-text-fill-color: var(--text-muted);
    }

    .sidebar ul {
        list-style: none;
        margin-top: 10px;
    }

    .sidebar ul li {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        margin-bottom: 4px;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: var(--transition);
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 14px;
        position: relative;
    }

    .sidebar ul li i {
        width: 20px;
        font-size: 16px;
        text-align: center;
        transition: var(--transition);
    }

    .sidebar ul li:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
    }

    .sidebar ul li:hover i {
        color: var(--primary);
    }

    .sidebar .active {
        background: var(--primary-gradient);
        color: #fff;
        box-shadow: 0 4px 20px var(--primary-glow);
    }

    .sidebar .active i {
        color: #fff;
    }

    .sidebar ul li .badge-sidebar {
        margin-left: auto;
        background: var(--danger);
        color: #fff;
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 20px;
        font-weight: 600;
    }

    .sidebar ul li a {
        display: flex;
        align-items: center;
        gap: 14px;
        width: 100%;
        color: inherit;
        text-decoration: none;
    }

    .sidebar .logout {
        margin-top: 20px;
        border-top: 1px solid var(--border-color);
        padding-top: 20px;
        color: var(--danger);
    }

    .sidebar .logout:hover {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .sidebar .logout i {
        color: var(--danger);
    }

    /* ========================================
           MAIN
        ======================================== */
    .main {
        flex: 1;
        margin-left: 280px;
        padding: 24px 30px;
        width: calc(100% - 280px);
        transition: var(--transition);
        min-height: 100vh;
    }

    /* ========================================
           NAVBAR
        ======================================== */
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
        padding: 12px 24px;
        background: var(--bg-card);
        border-radius: var(--radius);
        border: 1px solid var(--border-color);
        backdrop-filter: blur(10px);
    }

    .navbar-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    #menu-btn {
        width: 42px;
        height: 42px;
        border: none;
        border-radius: var(--radius-sm);
        background: var(--primary-gradient);
        color: #fff;
        cursor: pointer;
        font-size: 18px;
        display: none;
        transition: var(--transition);
    }

    #menu-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 20px var(--primary-glow);
    }

    .page-title {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-secondary);
    }

    .page-title span {
        color: var(--text-primary);
        font-weight: 600;
    }

    .search {
        width: 35%;
        position: relative;
    }

    .search input {
        width: 100%;
        background: var(--bg-input);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        padding: 10px 45px 10px 18px;
        border-radius: var(--radius-sm);
        outline: none;
        font-size: 14px;
        transition: var(--transition);
    }

    .search input::placeholder {
        color: var(--text-muted);
    }

    .search input:focus {
        border-color: var(--primary);
        background: rgba(255, 255, 255, 0.08);
        box-shadow: 0 0 0 3px var(--primary-glow);
    }

    .search i {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }

    .navbar-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .navbar-right .icon-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 1px solid var(--border-color);
        background: var(--bg-input);
        color: var(--text-secondary);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        position: relative;
    }

    .navbar-right .icon-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: rgba(46, 191, 63, 0.1);
    }

    .navbar-right .icon-btn .dot {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--danger);
        border: 2px solid var(--bg-card);
    }

    .profile {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-left: 16px;
        border-left: 1px solid var(--border-color);
    }

    .profile img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary);
        cursor: pointer;
        transition: var(--transition);
    }

    .profile img:hover {
        transform: scale(1.05);
        box-shadow: 0 0 20px var(--primary-glow);
    }

    .profile-info h4 {
        font-size: 14px;
        font-weight: 600;
    }

    .profile-info span {
        font-size: 12px;
        color: var(--text-muted);
    }

    /* ========================================
           WELCOME
        ======================================== */
    .welcome {
        margin-bottom: 28px;
        padding: 20px 0;
    }

    .welcome h1 {
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .welcome h1 .highlight {
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .welcome .welcome-date {
        color: var(--text-muted);
        font-size: 14px;
        margin-top: 4px;
    }

    /* ========================================
           STATS CARDS
        ======================================== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 18px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: var(--bg-card);
        border-radius: var(--radius);
        padding: 22px 20px;
        border: 1px solid var(--border-color);
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

    .stat-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
        opacity: 0;
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-6px);
        border-color: var(--primary);
        box-shadow: var(--shadow);
    }

    .stat-card:hover::after {
        opacity: 0.1;
    }

    .stat-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 12px;
        position: relative;
        z-index: 1;
    }

    .stat-card .stat-icon.green {
        background: rgba(46, 191, 63, 0.15);
        color: var(--primary);
    }

    .stat-card .stat-icon.blue {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }

    .stat-card .stat-icon.purple {
        background: rgba(139, 92, 246, 0.15);
        color: var(--secondary);
    }

    .stat-card .stat-icon.orange {
        background: rgba(245, 158, 11, 0.15);
        color: var(--warning);
    }

    .stat-card .stat-icon.pink {
        background: rgba(236, 72, 153, 0.15);
        color: #ec4899;
    }

    .stat-card .stat-icon.cyan {
        background: rgba(6, 182, 212, 0.15);
        color: var(--info);
    }

    .stat-card.green::before {
        background: var(--primary-gradient);
    }

    .stat-card.blue::before {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .stat-card.purple::before {
        background: var(--secondary-gradient);
    }

    .stat-card.orange::before {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .stat-card.pink::before {
        background: linear-gradient(135deg, #ec4899, #db2777);
    }

    .stat-card.cyan::before {
        background: linear-gradient(135deg, #06b6d4, #0891b2);
    }

    .stat-card .stat-number {
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.5px;
        line-height: 1.2;
        position: relative;
        z-index: 1;
    }

    .stat-card .stat-label {
        font-size: 13px;
        color: var(--text-secondary);
        font-weight: 500;
        margin-top: 4px;
        position: relative;
        z-index: 1;
    }

    .stat-card .stat-change {
        font-size: 12px;
        font-weight: 600;
        margin-top: 8px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 10px;
        border-radius: 20px;
        position: relative;
        z-index: 1;
    }

    .stat-card .stat-change.positive {
        color: var(--success);
        background: rgba(34, 197, 94, 0.15);
    }

    .stat-card .stat-change.negative {
        color: var(--danger);
        background: rgba(239, 68, 68, 0.15);
    }

    .stat-card .stat-change.neutral {
        color: var(--text-muted);
        background: rgba(255, 255, 255, 0.05);
    }

    /* ========================================
           CHARTS SECTION
        ======================================== */
    .charts-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 28px;
    }

    .chart-card {
        background: var(--bg-card);
        border-radius: var(--radius);
        padding: 22px 24px;
        border: 1px solid var(--border-color);
        transition: var(--transition);
    }

    .chart-card:hover {
        border-color: var(--primary);
    }

    .chart-card .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .chart-card .chart-header h3 {
        font-size: 16px;
        font-weight: 600;
    }

    .chart-card .chart-header .chart-tag {
        font-size: 12px;
        color: var(--text-muted);
        padding: 4px 12px;
        border-radius: 20px;
        background: var(--bg-input);
        border: 1px solid var(--border-color);
    }

    .chart-card canvas {
        width: 100% !important;
        height: 280px !important;
    }

    /* ========================================
           DASHBOARD BOTTOM
        ======================================== */
    .dashboard-bottom {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-top: 28px;
    }

    .table-card,
    .side-card {
        background: var(--bg-card);
        border-radius: var(--radius);
        padding: 22px 24px;
        border: 1px solid var(--border-color);
        transition: var(--transition);
    }

    .table-card:hover,
    .side-card:hover {
        border-color: var(--primary);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .card-header h3 {
        font-size: 17px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-header a {
        color: var(--primary);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: var(--transition);
    }

    .card-header a:hover {
        color: var(--primary-light);
    }

    /* Table */
    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table th {
        background: rgba(255, 255, 255, 0.03);
        color: var(--text-secondary);
        padding: 10px 14px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--border-color);
    }

    table td {
        padding: 10px 14px;
        color: var(--text-secondary);
        font-size: 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        transition: var(--transition);
    }

    table tbody tr {
        transition: var(--transition);
    }

    table tbody tr:hover {
        background: rgba(255, 255, 255, 0.03);
    }

    table tbody tr:last-child td {
        border-bottom: none;
    }

    .user-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-avatar-sm {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid var(--border-color);
    }

    .status-badge {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.active {
        background: rgba(34, 197, 94, 0.15);
        color: var(--success);
    }

    .status-badge.pending {
        background: rgba(245, 158, 11, 0.15);
        color: var(--warning);
    }

    .status-badge.inactive {
        background: rgba(239, 68, 68, 0.15);
        color: var(--danger);
    }

    /* Side Cards */
    .side-cards {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Quick Actions */
    .quick-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .quick-actions .q-btn {
        padding: 12px 16px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        background: var(--bg-input);
        color: var(--text-secondary);
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-weight: 500;
        font-size: 13px;
        font-family: inherit;
    }

    .quick-actions .q-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: rgba(46, 191, 63, 0.05);
        transform: translateY(-2px);
    }

    .quick-actions .q-btn.primary {
        background: var(--primary-gradient);
        color: #fff;
        border-color: var(--primary);
    }

    .quick-actions .q-btn.primary:hover {
        box-shadow: 0 4px 20px var(--primary-glow);
        transform: translateY(-2px);
    }

    /* Notifications */
    .notification-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: var(--radius-sm);
        background: var(--bg-input);
        margin-bottom: 8px;
        transition: var(--transition);
        border-left: 3px solid var(--primary);
    }

    .notification-item:hover {
        background: rgba(255, 255, 255, 0.06);
    }

    .notification-item .notif-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 14px;
    }

    .notification-item .notif-icon.success {
        background: rgba(34, 197, 94, 0.15);
        color: var(--success);
    }

    .notification-item .notif-icon.info {
        background: rgba(6, 182, 212, 0.15);
        color: var(--info);
    }

    .notification-item .notif-icon.warning {
        background: rgba(245, 158, 11, 0.15);
        color: var(--warning);
    }

    .notification-item .notif-content {
        flex: 1;
    }

    .notification-item .notif-content .text {
        font-size: 13px;
        color: var(--text-secondary);
    }

    .notification-item .notif-content .time {
        font-size: 11px;
        color: var(--text-muted);
    }

    /* ========================================
           FOOTER
        ======================================== */
    footer {
        margin-top: 35px;
        padding: 18px 24px;
        background: var(--bg-card);
        border-radius: var(--radius);
        text-align: center;
        color: var(--text-muted);
        font-size: 13px;
        border: 1px solid var(--border-color);
    }

    footer .heart {
        color: var(--danger);
    }

    /* ========================================
           RESPONSIVE
        ======================================== */
    @media(max-width:1200px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media(max-width:992px) {
        .sidebar {
            left: -280px;
        }

        .sidebar.active {
            left: 0;
        }

        .main {
            margin-left: 0;
            width: 100%;
            padding: 16px 18px;
        }

        #menu-btn {
            display: block;
        }

        .dashboard-bottom {
            grid-template-columns: 1fr;
        }

        .search {
            width: 50%;
        }

        .charts-section {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width:768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .navbar {
            padding: 12px 16px;
        }

        .search {
            display: none;
        }

        .profile-info {
            display: none;
        }

        .quick-actions {
            grid-template-columns: 1fr 1fr;
        }

        .welcome h1 {
            font-size: 22px;
        }

        .stat-card .stat-number {
            font-size: 22px;
        }

        .chart-card canvas {
            height: 200px !important;
        }

        .table-card {
            overflow-x: auto;
        }

        table {
            min-width: 500px;
        }
    }

    @media(max-width:480px) {
        .main {
            padding: 12px;
        }

        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .stat-card {
            padding: 16px;
        }

        .stat-card .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }

        .stat-card .stat-number {
            font-size: 18px;
        }

        .stat-card .stat-label {
            font-size: 11px;
        }

        .quick-actions {
            grid-template-columns: 1fr;
        }

        .navbar-right .icon-btn {
            width: 36px;
            height: 36px;
            font-size: 14px;
        }

        .profile img {
            width: 34px;
            height: 34px;
        }

        #menu-btn {
            width: 36px;
            height: 36px;
            font-size: 14px;
        }

        .welcome h1 {
            font-size: 18px;
        }
    }

    /* ========================================
           LIGHT MODE
        ======================================== */
    body.light-mode {
        --bg-dark: #f1f5f9;
        --bg-card: #ffffff;
        --bg-sidebar: #ffffff;
        --bg-hover: #f1f5f9;
        --bg-input: #f1f5f9;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --border-color: rgba(0, 0, 0, 0.08);
        --shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.1);
        --primary-glow: rgba(46, 191, 63, 0.2);
    }

    body.light-mode .sidebar {
        border-right-color: var(--border-color);
    }

    body.light-mode .stat-card .stat-change.neutral {
        background: rgba(0, 0, 0, 0.05);
    }

    body.light-mode .notification-item {
        background: #f8fafc;
    }

    body.light-mode .quick-actions .q-btn {
        background: #f8fafc;
    }

    body.light-mode .quick-actions .q-btn:hover {
        background: #f1f5f9;
    }

    body.light-mode .navbar-right .icon-btn .dot {
        border-color: #fff;
    }

    body.light-mode .logo p {
        -webkit-text-fill-color: var(--text-muted);
    }

    body.light-mode .welcome h1 .highlight {
        -webkit-text-fill-color: transparent;
    }
    </style>
</head>

<body>

    <div class="container">

        <!-- ===== SIDEBAR ===== -->
        <aside class="sidebar">
            <div class="logo">
                <div class="logo-img">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <h2>Thunder X</h2>
                    <p>Admin Panel</p>
                </div>
            </div>

            <ul>
                <li class="active">
                    <i class="fa-solid fa-table-columns"></i>
                    <span>Dashboard</span>
                </li>
                <li>
                    <a href="admin.php">
                        <i class="fa-solid fa-users"></i>
                        <span>Members</span>
                        <span class="badge-sidebar"><?php echo $total_users; ?></span>
                    </a>
                </li>
                <li>
                    <a href="investment.php">
                        <i class="fa-solid fa-wallet"></i>
                        <span>Investment</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                        <span>Withdraw</span>
                        <span class="badge-sidebar"
                            style="background:var(--warning);"><?php echo $pending_withdrawals; ?></span>
                    </a>
                </li>
                <li>
                    <a href="product.php">
                        <i class="fa-solid fa-box-open"></i>
                        <span>Products add</span>
                    </a>
                </li>

                <li>
                    <a href="view_product.php">
                        <i class="fa-solid fa-box-open"></i>
                        <span>Products member</span>
                    </a>
                </li>


                <li>
                    <a href="blog.php">
                        <i class="fa-solid fa-box-open"></i>
                        <span>Products view</span>
                    </a>
                </li>
                <li>
                    <a href="income.php">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Income Report</span>
                    </a>
                </li>

                <li>
                    <a href="approve.php">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>pending report</span>
                    </a>
                </li>

                <li>
                    <a href="transactions.php">
                        <i class="fa-solid fa-file-lines"></i>
                        <span>Transactions</span>
                    </a>
                </li>
                <li>
                    <a href="setting.php">
                        <i class="fa-solid fa-gear"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="logout">
                    <a href="logout.php">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- ===== MAIN ===== -->
        <main class="main">

            <!-- ===== NAVBAR ===== -->
            <header class="navbar">
                <div class="navbar-left">
                    <button id="menu-btn">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="page-title">
                        <span>Dashboard</span> / Overview
                    </div>
                </div>

                <div class="search">
                    <input type="text" placeholder="Search anything...">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>

                <div class="navbar-right">
                    <button class="icon-btn" title="Notifications">
                        <i class="fa-regular fa-bell"></i>
                        <span class="dot"></span>
                    </button>
                    <button class="icon-btn" id="themeToggle" title="Toggle Theme">
                        <i class="fa-solid fa-moon"></i>
                    </button>
                    <div class="profile">
                        <img src="https://ui-avatars.com/api/?name=Admin&background=2ebf3f&color=fff&size=40&bold=true"
                            alt="Admin">
                        <div class="profile-info">
                            <h4>Admin</h4>
                            <span>Super Admin</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- ===== WELCOME ===== -->
            <section class="welcome">
                <h1>Welcome Back. <span class="highlight">Admin</span> </h1>
                <div class="welcome-date">
                    <i class="fa-regular fa-calendar"></i>
                    <?php echo date('l, F j, Y'); ?> |
                    <i class="fa-regular fa-clock"></i>
                    <?php echo date('h:i A'); ?>
                </div>
            </section>

            <!-- ===== STATS CARDS ===== -->
            <section class="stats-grid">

                <div class="stat-card green">
                    <div class="stat-icon green"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-number"><?php echo number_format($total_users); ?></div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-change positive"><i class="fa-solid fa-arrow-up"></i> +<?php echo $today_users; ?>
                        today</div>
                </div>

                <div class="stat-card blue">
                    <div class="stat-icon blue"><i class="fa-solid fa-dollar-sign"></i></div>
                    <div class="stat-number">₹<?php echo number_format($total_income); ?></div>
                    <div class="stat-label">Total Income</div>
                    <div class="stat-change positive"><i class="fa-solid fa-arrow-up"></i> +12.5%</div>
                </div>

                <div class="stat-card purple">
                    <div class="stat-icon purple"><i class="fa-solid fa-box"></i></div>
                    <div class="stat-number"><?php echo number_format($total_products); ?></div>
                    <div class="stat-label">Total Products</div>
                    <div class="stat-change positive"><i class="fa-solid fa-arrow-up"></i> +5 new</div>
                </div>



                <div class="stat-card orange">
                    <div class="stat-icon orange"><i class="fa-solid fa-wallet"></i></div>
                    <div class="stat-number">₹<?php echo number_format($total_investment); ?></div>
                    <div class="stat-label">Total Investment</div>
                    <div class="stat-change positive"><i class="fa-solid fa-arrow-up"></i> +8.2%</div>
                </div>

                <div class="stat-card pink">
                    <div class="stat-icon pink"><i class="fa-solid fa-arrow-up"></i></div>
                    <div class="stat-number">₹<?php echo number_format($total_withdrawals); ?></div>
                    <div class="stat-label">Total Withdrawals</div>
                    <div class="stat-change positive"><i class="fa-solid fa-arrow-up"></i> +3.1%</div>
                </div>

                <div class="stat-card cyan">
                    <div class="stat-icon cyan"><i class="fa-solid fa-coins"></i></div>
                    <div class="stat-number">₹<?php echo number_format($wallet_balance); ?></div>
                    <div class="stat-label">Wallet Balance</div>
                    <div class="stat-change neutral"><i class="fa-solid fa-minus"></i> Stable</div>
                </div>

            </section>

            <!-- ===== CHARTS ===== -->
            <section class="charts-section">

                <div class="chart-card">
                    <div class="chart-header">
                        <h3>📈 Monthly Income</h3>
                        <span class="chart-tag">This Year</span>
                    </div>
                    <canvas id="incomeChart"></canvas>
                </div>

                <div class="chart-card">
                    <div class="chart-header">
                        <h3>👥 Monthly Users</h3>
                        <span class="chart-tag">This Year</span>
                    </div>
                    <canvas id="userChart"></canvas>
                </div>

            </section>

            <!-- ===== DASHBOARD BOTTOM ===== -->
            <div class="dashboard-bottom">

                <!-- Recent Users Table -->
                <div class="table-card">
                    <div class="card-header">
                        <h3><i class="fa-regular fa-user"></i> Recent Users</h3>
                        <a href="admin.php">View All →</a>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($recent_users && mysqli_num_rows($recent_users) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($recent_users)) { ?>
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($row['name']); ?>&background=2ebf3f&color=fff&size=32&bold=true"
                                                alt="avatar" class="user-avatar-sm">
                                            <span><?php echo htmlspecialchars($row['name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                                    <td><span class="status-badge active">Active</span></td>
                                </tr>
                                <?php } ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align:center;padding:30px;color:var(--text-muted);">No
                                        users found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right Panel -->
                <div class="side-cards">

                    <!-- Quick Actions -->
                    <div class="side-card">
                        <div class="card-header">
                            <h3><i class="fa-solid fa-bolt"></i> Quick Actions</h3>
                        </div>
                        <div class="quick-actions">
                            <button class="q-btn primary" onclick="location.href='#'">
                                <i class="fa-solid fa-user-plus"></i> Add User
                            </button>
                            <button class="q-btn primary" onclick="location.href='#'">
                                <i class="fa-solid fa-plus"></i> Add Product
                            </button>
                            <button class="q-btn" onclick="location.href='#'">
                                <i class="fa-solid fa-chart-simple"></i> Reports
                            </button>
                            <button class="q-btn" onclick="location.href='setting.php'">
                                <i class="fa-solid fa-gear"></i> Settings
                            </button>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="side-card">
                        <div class="card-header">
                            <h3><i class="fa-regular fa-bell"></i> Notifications</h3>
                            <span style="font-size:12px;color:var(--text-muted);">New</span>
                        </div>
                        <?php foreach($notifications as $notif) { ?>
                        <div class="notification-item">
                            <div class="notif-icon <?php echo $notif['type']; ?>">
                                <i class="fa-solid <?php echo $notif['icon']; ?>"></i>
                            </div>
                            <div class="notif-content">
                                <div class="text"><?php echo $notif['text']; ?></div>
                                <div class="time"><?php echo $notif['time']; ?></div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>

                </div>

            </div>

            <!-- ===== FOOTER ===== -->
            <footer>
                <p>© <?php echo date('Y'); ?> <strong>Thunder X</strong> | Developed with <span class="heart">❤️</span>
                    by Rizwan Saifi</p>
            </footer>

        </main>

    </div>

    <script>
    // =============================
    // SIDEBAR TOGGLE
    // =============================
    const menuBtn = document.getElementById("menu-btn");
    const sidebar = document.querySelector(".sidebar");

    menuBtn.addEventListener("click", function() {
        sidebar.classList.toggle("active");
    });

    // Close sidebar on outside click (mobile)
    document.addEventListener("click", function(e) {
        if (window.innerWidth <= 992) {
            if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                sidebar.classList.remove("active");
            }
        }
    });

    // =============================
    // THEME TOGGLE
    // =============================
    const themeBtn = document.getElementById("themeToggle");

    themeBtn.addEventListener("click", function() {
        document.body.classList.toggle("light-mode");

        if (document.body.classList.contains("light-mode")) {
            this.innerHTML = '<i class="fa-solid fa-sun"></i>';
        } else {
            this.innerHTML = '<i class="fa-solid fa-moon"></i>';
        }
    });

    // =============================
    // INCOME CHART
    // =============================
    const incomeData = <?php echo json_encode($monthly_income); ?>;
    const userData = <?php echo json_encode($monthly_users); ?>;
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    const ctx = document.getElementById('incomeChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Income (₹)',
                data: incomeData,
                borderColor: '#2ebf3f',
                backgroundColor: 'rgba(46,191,63,0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#2ebf3f',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 8,
                borderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255,255,255,0.05)'
                    },
                    ticks: {
                        color: '#888',
                        font: {
                            size: 11
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#888',
                        font: {
                            size: 11
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // =============================
    // USER CHART
    // =============================
    const ctx2 = document.getElementById('userChart').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Users',
                data: userData,
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139,92,246,0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#8b5cf6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 8,
                borderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255,255,255,0.05)'
                    },
                    ticks: {
                        color: '#888',
                        font: {
                            size: 11
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#888',
                        font: {
                            size: 11
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
    </script>

</body>

</html>