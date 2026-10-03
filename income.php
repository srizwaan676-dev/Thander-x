<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection - FIXED PATH
include("db.php");

// Check database connection
if(!$conn) {
    die("Database connection failed!");
}

// Now proceed with the rest of the code
$sql = "SELECT SUM(amount) AS total_income
        FROM income
        WHERE status='paid'";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

$total_income = $row['total_income'] ?? 0;

$admin_id = $_SESSION['user_id'];
$admin_sql = "SELECT * FROM contact WHERE id = '$admin_id' AND role = 'admin'";
$admin_result = mysqli_query($conn, $admin_sql);
$admin = mysqli_fetch_assoc($admin_result);

if(!$admin) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// ========================================
// ADMIN INCOME FUNCTIONS
// ========================================

// Get total income from all users
function getTotalIncomeAll($conn) {
    $sql = "SELECT SUM(amount) as total FROM income WHERE status = 'paid'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

// Get total pending income
function getTotalPendingIncome($conn) {
    $sql = "SELECT SUM(amount) as total FROM income WHERE status = 'pending'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

// Get total income count
function getTotalIncomeCount($conn) {
    $sql = "SELECT COUNT(*) as count FROM income WHERE status = 'paid'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    return 0;
}

// Get today's total income
function getTodayTotalIncome($conn) {
    $sql = "SELECT SUM(amount) as total FROM income WHERE DATE(created_at) = CURDATE() AND status = 'paid'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

// Get this month's total income
function getMonthTotalIncome($conn) {
    $sql = "SELECT SUM(amount) as total FROM income 
            WHERE MONTH(created_at) = MONTH(CURDATE()) 
            AND YEAR(created_at) = YEAR(CURDATE())
            AND status = 'paid'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

// Get last month's total income
function getLastMonthTotalIncome($conn) {
    $sql = "SELECT SUM(amount) as total FROM income 
            WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
            AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
            AND status = 'paid'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

// Get income by source (all users)
function getIncomeBySourceAll($conn) {
    $sql = "SELECT source, SUM(amount) as total, COUNT(*) as count 
            FROM income WHERE status = 'paid' 
            GROUP BY source";
    $result = mysqli_query($conn, $sql);
    $data = [];
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $data[$row['source']] = [
                'total' => $row['total'],
                'count' => $row['count']
            ];
        }
    }
    return $data;
}

// Get monthly income data (all users)
function getMonthlyIncomeDataAll($conn) {
    $sql = "SELECT 
            MONTH(created_at) as month_num,
            YEAR(created_at) as year,
            SUM(amount) as total 
            FROM income 
            WHERE status = 'paid'
            AND YEAR(created_at) = YEAR(CURDATE())
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

// Get income by user
function getIncomeByUser($conn) {
    $sql = "SELECT 
                u.id as user_id,
                u.name,
                u.Email as email,
                u.mobile,
                COALESCE(SUM(i.amount), 0) as total_income,
                COUNT(i.id) as income_count,
                MAX(i.created_at) as last_income,
                MIN(i.created_at) as first_income
            FROM contact u
            LEFT JOIN income i ON u.id = i.user_id AND i.status = 'paid'
            WHERE u.role = 'user' OR u.role IS NULL
            GROUP BY u.id
            ORDER BY total_income DESC";
    $result = mysqli_query($conn, $sql);
    return $result;
}

// Get user income details
function getUserIncomeDetails($conn, $user_id) {
    $sql = "SELECT * FROM income WHERE user_id = '$user_id' ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);
    return $result;
}

// Get recent income all users
function getRecentIncomeAll($conn, $limit = 20) {
    $sql = "SELECT i.*, u.name as user_name, u.Email as user_email 
            FROM income i 
            JOIN contact u ON i.user_id = u.id 
            ORDER BY i.created_at DESC 
            LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    return $result;
}

// Get income by status
function getIncomeByStatus($conn, $status) {
    $sql = "SELECT COUNT(*) as count, SUM(amount) as total FROM income WHERE status = '$status'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return ['count' => 0, 'total' => 0];
}

// Calculate growth percentage
function calculateGrowthAdmin($current, $previous) {
    if($previous == 0) {
        return $current > 0 ? 100 : 0;
    }
    return round((($current - $previous) / $previous) * 100, 1);
}

// ========================================
// GET DATA
// ========================================

$total_income = getTotalIncomeAll($conn);
$pending_income = getTotalPendingIncome($conn);
$today_income = getTodayTotalIncome($conn);
$month_income = getMonthTotalIncome($conn);
$last_month_income = getLastMonthTotalIncome($conn);
$total_income_count = getTotalIncomeCount($conn);
$income_sources = getIncomeBySourceAll($conn);
$monthly_data = getMonthlyIncomeDataAll($conn);
$income_by_user = getIncomeByUser($conn);
$recent_income = getRecentIncomeAll($conn);

// Status counts
$paid_stats = getIncomeByStatus($conn, 'paid');
$pending_stats = getIncomeByStatus($conn, 'pending');
$cancelled_stats = getIncomeByStatus($conn, 'cancelled');

// Calculate growth
$growth = calculateGrowthAdmin($month_income, $last_month_income);

// Source colors and labels
$source_colors = [
    'investment' => '#6C63FF',
    'referral' => '#22c55e',
    'bonus' => '#f59e0b',
    'commission' => '#8b5cf6',
    'dividend' => '#ec4899',
    'other' => '#64748b'
];

$source_labels = [
    'investment' => 'Investment Returns',
    'referral' => 'Referral Bonus',
    'bonus' => 'Bonus',
    'commission' => 'Commission',
    'dividend' => 'Dividend',
    'other' => 'Other Income'
];

// Total users
$users_sql = "SELECT COUNT(*) as count FROM contact WHERE role = 'user' OR role IS NULL";
$users_result = mysqli_query($conn, $users_sql);
$users_data = mysqli_fetch_assoc($users_result);
$total_users = $users_data['count'] ?? 0;

// Active users with income
$active_users_sql = "SELECT COUNT(DISTINCT user_id) as count FROM income WHERE status = 'paid'";
$active_users_result = mysqli_query($conn, $active_users_sql);
$active_users_data = mysqli_fetch_assoc($active_users_result);
$active_users = $active_users_data['count'] ?? 0;

// User income details for modal
$user_income_details = [];
$user_details = null;
if(isset($_GET['view_user']) && !empty($_GET['view_user'])) {
    $view_user_id = intval($_GET['view_user']);
    $user_income_details = getUserIncomeDetails($conn, $view_user_id);
    $user_details_sql = "SELECT * FROM contact WHERE id = '$view_user_id'";
    $user_details_result = mysqli_query($conn, $user_details_sql);
    $user_details = mysqli_fetch_assoc($user_details_result);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Dashboard | IncomeCoin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ========================================
           ROOT VARIABLES
        ======================================== */
        :root {
            --primary: #6C63FF;
            --primary-dark: #5A52D5;
            --primary-light: #E8E6FF;
            --primary-gradient: linear-gradient(135deg, #6C63FF 0%, #5A52D5 100%);
            
            --secondary: #FF6584;
            --secondary-light: #FFE6EC;
            
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
            --shadow-colored: 0 8px 30px rgba(108, 99, 255, 0.35);
            
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-2xl: 24px;
            
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: 
                radial-gradient(circle at 20% 20%, rgba(108, 99, 255, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 101, 132, 0.04) 0%, transparent 50%),
                var(--gray-100);
            padding: 20px;
            min-height: 100vh;
        }

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ========================================
           TOP BAR
        ======================================== */
        .top-bar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--radius-xl);
            padding: 14px 28px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
            border: 1px solid rgba(255,255,255,0.8);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .top-bar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-gradient);
        }

        .top-bar:hover {
            box-shadow: var(--shadow-md);
        }

        .top-bar .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .top-bar .brand .brand-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 16px;
            font-weight: 800;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
        }

        .top-bar .brand h2 {
            font-size: 20px;
            font-weight: 800;
            color: var(--gray-900);
        }

        .top-bar .brand h2 span {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .top-bar .nav-links {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        .top-bar .nav-links a {
            text-decoration: none;
            color: var(--gray-500);
            font-weight: 500;
            font-size: 13px;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .top-bar .nav-links a:hover {
            background: var(--primary-light);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .top-bar .nav-links a.active {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.35);
        }

        .top-bar .nav-links a.logout {
            background: var(--secondary);
            color: #fff;
        }

        .top-bar .nav-links a.logout:hover {
            background: #dc2626;
            box-shadow: 0 4px 15px rgba(255, 101, 132, 0.4);
        }

        .top-bar .admin-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .top-bar .admin-info .admin-name {
            font-weight: 600;
            color: var(--gray-700);
            font-size: 14px;
        }

        .top-bar .admin-info .admin-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
        }

        /* ========================================
           PAGE HEADER
        ======================================== */
        .page-header {
            background: var(--primary-gradient);
            border-radius: var(--radius-2xl);
            padding: 32px 40px;
            color: #fff;
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: var(--shadow-colored);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -5%;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }

        .page-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }

        .page-header .header-content {
            position: relative;
            z-index: 1;
        }

        .page-header h1 {
            font-size: 26px;
            font-weight: 800;
        }

        .page-header h1 i {
            margin-right: 12px;
            background: rgba(255,255,255,0.15);
            padding: 8px;
            border-radius: var(--radius-sm);
        }

        .page-header .header-content p {
            opacity: 0.85;
            margin-top: 4px;
            font-size: 14px;
        }

        .page-header .header-stats {
            display: flex;
            gap: 30px;
            background: rgba(255,255,255,0.1);
            padding: 12px 24px;
            border-radius: var(--radius-md);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            position: relative;
            z-index: 1;
        }

        .page-header .header-stats .hs-item {
            text-align: center;
        }

        .page-header .header-stats .hs-item .hs-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }

        .page-header .header-stats .hs-item .hs-value {
            font-size: 18px;
            font-weight: 700;
        }

        /* ========================================
           STATS GRID
        ======================================== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            margin-bottom: 28px;
            animation: fadeInUp 0.6s ease 0.1s both;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--radius-md);
            padding: 18px 20px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition-bounce);
            border: 1px solid rgba(255,255,255,0.8);
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

        .stat-card:nth-child(1)::before { background: var(--primary); }
        .stat-card:nth-child(2)::before { background: var(--warning); }
        .stat-card:nth-child(3)::before { background: var(--success); }
        .stat-card:nth-child(4)::before { background: var(--purple); }
        .stat-card:nth-child(5)::before { background: var(--pink); }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .stat-card .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            margin-bottom: 8px;
            transition: var(--transition-bounce);
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(-5deg);
        }

        .stat-card:nth-child(1) .stat-icon { background: var(--primary-light); color: var(--primary); }
        .stat-card:nth-child(2) .stat-icon { background: var(--warning-light); color: var(--warning); }
        .stat-card:nth-child(3) .stat-icon { background: var(--success-light); color: var(--success); }
        .stat-card:nth-child(4) .stat-icon { background: var(--purple-light); color: var(--purple); }
        .stat-card:nth-child(5) .stat-icon { background: var(--pink-light); color: var(--pink); }

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

        .stat-card .stat-change {
            font-size: 12px;
            font-weight: 600;
            margin-top: 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 10px;
            border-radius: 20px;
        }

        .stat-card .stat-change.positive { 
            color: var(--success); 
            background: var(--success-light);
        }

        .stat-card .stat-change.negative { 
            color: var(--danger); 
            background: var(--danger-light);
        }

        /* ========================================
           CHARTS ROW
        ======================================== */
        .charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 28px;
            animation: fadeInUp 0.6s ease 0.2s both;
        }

        .chart-box {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(255,255,255,0.8);
            transition: var(--transition);
        }

        .chart-box:hover {
            box-shadow: var(--shadow-md);
        }

        .chart-box .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .chart-box .chart-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chart-box .chart-header h3 i {
            color: var(--primary);
        }

        .chart-box .chart-header .chart-badge {
            font-size: 12px;
            color: var(--gray-400);
            background: var(--gray-100);
            padding: 4px 12px;
            border-radius: 20px;
        }

        .chart-box canvas {
            max-height: 280px;
            max-width: 100%;
        }

        /* ========================================
           SOURCE DISTRIBUTION
        ======================================== */
        .source-list {
            list-style: none;
        }

        .source-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-100);
            font-size: 14px;
            transition: var(--transition);
        }

        .source-list li:hover {
            padding-left: 8px;
            background: var(--gray-50);
            margin: 0 -4px;
            padding-left: 12px;
            padding-right: 4px;
            border-radius: var(--radius-sm);
        }

        .source-list li:last-child {
            border-bottom: none;
        }

        .source-list .source-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .source-list .source-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
            transition: var(--transition);
        }

        .source-list li:hover .source-dot {
            transform: scale(1.3);
        }

        .source-list .source-name {
            font-weight: 500;
            color: var(--gray-700);
        }

        .source-list .source-stats {
            color: var(--gray-500);
        }

        .source-list .source-stats strong {
            color: var(--gray-800);
        }

        .source-list .source-count {
            font-size: 12px;
            color: var(--gray-400);
            margin-left: 8px;
        }

        /* ========================================
           TABLES
        ======================================== */
        .table-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(255,255,255,0.8);
            margin-bottom: 28px;
            overflow-x: auto;
            animation: fadeInUp 0.6s ease 0.3s both;
        }

        .table-container:hover {
            box-shadow: var(--shadow-md);
        }

        .table-container .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .table-container .table-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-container .table-header h3 i {
            color: var(--primary);
        }

        .table-container .table-header .view-all {
            color: var(--primary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
        }

        .table-container .table-header .view-all:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .table-container .table-header .search-box {
            display: flex;
            gap: 8px;
        }

        .table-container .table-header .search-box input {
            padding: 8px 14px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 13px;
            outline: none;
            transition: var(--transition);
            background: var(--gray-50);
            color: var(--gray-800);
        }

        .table-container .table-header .search-box input:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.1);
        }

        .table-container .table-header .search-box button {
            padding: 8px 16px;
            background: var(--primary-gradient);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .table-container .table-header .search-box button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(108, 99, 255, 0.3);
        }

        /* ========================================
           TABLE STYLING
        ======================================== */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: var(--gray-50);
        }

        table th {
            color: var(--gray-600);
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--gray-200);
        }

        table td {
            padding: 10px 14px;
            color: var(--gray-700);
            font-size: 13px;
            border-bottom: 1px solid var(--gray-100);
            transition: var(--transition);
        }

        table tbody tr {
            transition: var(--transition);
        }

        table tbody tr:hover {
            background: var(--gray-50);
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        .badge {
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-success { background: var(--success-light); color: #166534; }
        .badge-warning { background: var(--warning-light); color: #92400e; }
        .badge-danger { background: var(--danger-light); color: #991b1b; }
        .badge-primary { background: var(--primary-light); color: var(--primary-dark); }
        .badge-cyan { background: var(--cyan-light); color: #0e7490; }
        .badge-purple { background: var(--purple-light); color: #5b21b6; }
        .badge-pink { background: var(--pink-light); color: #9d174d; }

        .text-success { color: var(--success); font-weight: 600; }
        .text-muted { color: var(--gray-400); font-size: 12px; }
        .text-primary { color: var(--primary); }

        .no-data {
            text-align: center;
            padding: 30px 20px;
            color: var(--gray-400);
        }

        .no-data i {
            font-size: 32px;
            opacity: 0.3;
            display: block;
            margin-bottom: 8px;
        }

        /* ========================================
           USER DETAIL MODAL
        ======================================== */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(8px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
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
            border-radius: var(--radius-xl);
            padding: 32px;
            max-width: 800px;
            width: 95%;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: var(--shadow-xl);
            animation: modalIn 0.3s ease;
        }

        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal::-webkit-scrollbar {
            width: 6px;
        }

        .modal::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: 3px;
        }

        .modal::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 3px;
        }

        .modal .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--gray-100);
        }

        .modal .modal-header h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal .modal-header h3 i {
            color: var(--primary);
        }

        .modal .modal-header .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--gray-400);
            cursor: pointer;
            transition: var(--transition);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal .modal-header .close-btn:hover {
            color: var(--gray-600);
            background: var(--gray-100);
            transform: rotate(90deg);
        }

        .modal .user-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .modal .user-summary-grid .us-item {
            background: var(--gray-50);
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            text-align: center;
            border: 1px solid var(--gray-200);
        }

        .modal .user-summary-grid .us-item .us-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .modal .user-summary-grid .us-item .us-label {
            font-size: 11px;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .modal table th {
            background: var(--gray-50);
            font-size: 10px;
        }

        .modal table td {
            font-size: 12px;
            padding: 8px 12px;
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
            .charts-row {
                grid-template-columns: 1fr;
            }
            .page-header {
                flex-direction: column;
                text-align: center;
            }
            .page-header .header-stats {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .modal .user-summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            body { padding: 12px; }
            .top-bar { 
                flex-direction: column; 
                align-items: stretch;
                padding: 12px 18px;
            }
            .top-bar .brand { justify-content: center; }
            .top-bar .nav-links { justify-content: center; }
            .top-bar .admin-info { justify-content: center; }
            .page-header { padding: 20px; }
            .page-header h1 { font-size: 22px; }
            .page-header .header-stats { flex-wrap: wrap; gap: 15px; }
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
            .stat-card { padding: 16px; }
            .stat-card .stat-number { font-size: 20px; }
            .chart-box { padding: 16px; }
            .table-container { padding: 16px; }
            table { min-width: 600px; }
            .top-bar .nav-links a { font-size: 12px; padding: 6px 12px; }
            .modal { padding: 20px; }
            .modal .user-summary-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .page-header .header-stats { flex-direction: column; gap: 8px; }
            .top-bar .nav-links a { font-size: 11px; padding: 4px 10px; }
            .table-container .table-header { flex-direction: column; align-items: stretch; }
            .table-container .table-header .search-box { flex-direction: column; }
            .table-container .table-header .search-box input { width: 100%; }
            .modal .user-summary-grid { grid-template-columns: 1fr; }
        }

        /* ========================================
           DARK MODE
        ======================================== */
        @media (prefers-color-scheme: dark) {
            :root {
                --gray-50: #1e293b;
                --gray-100: #0f172a;
                --gray-200: #334155;
                --gray-400: #94a3b8;
                --gray-500: #94a3b8;
                --gray-600: #cbd5e1;
                --gray-700: #e2e8f0;
                --gray-800: #f1f5f9;
                --gray-900: #ffffff;
            }

            body {
                background: 
                    radial-gradient(circle at 20% 20%, rgba(108, 99, 255, 0.08) 0%, transparent 50%),
                    radial-gradient(circle at 80% 80%, rgba(255, 101, 132, 0.06) 0%, transparent 50%),
                    #0f172a;
            }

            .top-bar,
            .stat-card,
            .chart-box,
            .table-container,
            .modal {
                background: rgba(30, 41, 59, 0.95);
                border-color: rgba(51, 65, 85, 0.5);
            }

            .top-bar .brand h2 { color: #f1f5f9; }
            .top-bar .admin-info .admin-name { color: #e2e8f0; }
            .top-bar .nav-links a { color: #94a3b8; }
            .top-bar .nav-links a:hover { background: #334155; color: #60a5fa; }
            .top-bar .nav-links a.active { background: var(--primary-gradient); color: #fff; }
            
            .stat-card .stat-number { color: #f1f5f9; }
            .stat-card .stat-label { color: #94a3b8; }
            .stat-card:nth-child(1) .stat-icon { background: #1e3a5f; color: #60a5fa; }
            .stat-card:nth-child(2) .stat-icon { background: #3a2a1a; color: #fbbf24; }
            .stat-card:nth-child(3) .stat-icon { background: #1a3a2a; color: #4ade80; }
            .stat-card:nth-child(4) .stat-icon { background: #2e1a4a; color: #a78bfa; }
            .stat-card:nth-child(5) .stat-icon { background: #3a1a3a; color: #f472b6; }

            .chart-box .chart-header h3 { color: #f1f5f9; }
            .chart-box .chart-header .chart-badge { background: #334155; color: #94a3b8; }
            
            .table-container .table-header h3 { color: #f1f5f9; }
            .table-container .table-header .search-box input {
                background: #334155;
                border-color: #475569;
                color: #f1f5f9;
            }
            .table-container .table-header .search-box input:focus {
                background: #1e293b;
                border-color: var(--primary);
            }

            table thead { background: #334155; }
            table th { color: #94a3b8; border-bottom-color: #475569; }
            table td { color: #e2e8f0; border-bottom-color: #334155; }
            table tbody tr:hover { background: #334155; }

            .source-list li { border-bottom-color: #334155; }
            .source-list .source-name { color: #e2e8f0; }
            .source-list .source-stats { color: #94a3b8; }
            .source-list .source-stats strong { color: #f1f5f9; }
            .source-list li:hover { background: #334155; }

            .badge-success { background: #064e3b; color: #86efac; }
            .badge-warning { background: #78350f; color: #fbbf24; }
            .badge-danger { background: #7f1d1d; color: #fca5a5; }
            .badge-primary { background: #1e3a5f; color: #60a5fa; }
            .badge-cyan { background: #0a3d4a; color: #22d3ee; }
            .badge-purple { background: #3b1e6e; color: #a78bfa; }
            .badge-pink { background: #5b1d3e; color: #f472b6; }

            .text-muted { color: #94a3b8; }
            .text-success { color: #4ade80; }

            .no-data { color: #94a3b8; }

            .modal .modal-header {
                border-bottom-color: #334155;
            }
            .modal .modal-header h3 { color: #f1f5f9; }
            .modal .user-summary-grid .us-item {
                background: #334155;
                border-color: #475569;
            }
            .modal .user-summary-grid .us-item .us-value { color: #f1f5f9; }
            .modal .user-summary-grid .us-item .us-label { color: #94a3b8; }
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
            background: var(--primary);
            border-radius: 3px;
            transition: var(--transition);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>

<div class="admin-container">

    <!-- ===== TOP BAR ===== -->
    <div class="top-bar">
        <div class="brand">
            <div class="brand-icon">IC</div>
            <h2>Income<span>Coin</span></h2>
        </div>
        
        <div class="nav-links">
            <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="users.php"><i class="fas fa-users"></i> Users</a>
            <a href="wallet.php"><i class="fas fa-wallet"></i> Wallet</a>
            <a href="investment.php"><i class="fas fa-chart-line"></i> Investments</a>
            <a href="income.php" class="active"><i class="fas fa-money-bill-wave"></i> Income</a>
         
            
        </div>

        <div class="admin-info">
            <span class="admin-name"><?php echo htmlspecialchars($admin['name'] ?? 'Admin'); ?></span>
            <div class="admin-avatar"><?php echo strtoupper(substr($admin['name'] ?? 'A', 0, 1)); ?></div>
        </div>
    </div>

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-money-bill-wave"></i> Income Dashboard</h1>
            <p>Complete overview of all user income across the platform</p>
        </div>
        <div class="header-stats">
            <div class="hs-item">
                <div class="hs-label">Total Income</div>
                <div class="hs-value">₹<?php echo number_format($total_income, 2); ?></div>
            </div>
            <div class="hs-item">
                <div class="hs-label">This Month</div>
                <div class="hs-value">₹<?php echo number_format($month_income, 2); ?></div>
            </div>
            <div class="hs-item">
                <div class="hs-label">Active Users</div>
                <div class="hs-value"><?php echo $active_users; ?></div>
            </div>
        </div>
    </div>

    <!-- ===== STATS CARDS ===== -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-number">₹<?php echo number_format($total_income, 2); ?></div>
            <div class="stat-label">Total Income</div>
            <div class="stat-change <?php echo $growth >= 0 ? 'positive' : 'negative'; ?>">
                <i class="fas fa-<?php echo $growth >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                <?php echo abs($growth); ?>% this month
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-number">₹<?php echo number_format($pending_income, 2); ?></div>
            <div class="stat-label">Pending Income</div>
            <div class="stat-change positive">
                <i class="fas fa-hourglass-half"></i> Awaiting approval
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-number">₹<?php echo number_format($today_income, 2); ?></div>
            <div class="stat-label">Today's Income</div>
            <div class="stat-change positive">
                <i class="fas fa-calendar-check"></i> <?php echo date('d M Y'); ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-number"><?php echo $active_users; ?></div>
            <div class="stat-label">Active Earners</div>
            <div class="stat-change positive">
                <i class="fas fa-user-check"></i> Out of <?php echo $total_users; ?> users
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-number"><?php echo $total_income_count; ?></div>
            <div class="stat-label">Total Transactions</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> Lifetime
            </div>
        </div>
    </div>

    <!-- ===== CHARTS ROW ===== -->
    <div class="charts-row">

        <!-- ===== MONTHLY CHART ===== -->
        <div class="chart-box">
            <div class="chart-header">
                <h3><i class="fas fa-chart-area"></i> Monthly Income Trend</h3>
                <span class="chart-badge"><?php echo date('Y'); ?></span>
            </div>
            <canvas id="incomeChart"></canvas>
        </div>

        <!-- ===== SOURCE DISTRIBUTION ===== -->
        <div class="chart-box">
            <div class="chart-header">
                <h3><i class="fas fa-pie-chart"></i> Income Sources</h3>
                <span class="chart-badge">All Time</span>
            </div>
            <?php if(!empty($income_sources)): ?>
                <ul class="source-list">
                    <?php foreach($income_sources as $source => $data): 
                        $color = isset($source_colors[$source]) ? $source_colors[$source] : '#64748b';
                        $label = isset($source_labels[$source]) ? $source_labels[$source] : ucfirst($source);
                    ?>
                        <li>
                            <div class="source-left">
                                <span class="source-dot" style="background:<?php echo $color; ?>;"></span>
                                <span class="source-name"><?php echo $label; ?></span>
                            </div>
                            <div class="source-stats">
                                <strong>₹<?php echo number_format($data['total'], 2); ?></strong>
                                <span class="source-count">(<?php echo $data['count']; ?> entries)</span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-info-circle"></i>
                    <p>No income data available</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ===== INCOME BY USER ===== -->
    <div class="table-container">
        <div class="table-header">
            <h3><i class="fas fa-users"></i> Income by User</h3>
            <div class="search-box">
                <input type="text" id="searchUser" placeholder="Search user..." onkeyup="filterUsers()">
                <button onclick="exportCSV()"><i class="fas fa-download"></i> Export</button>
            </div>
        </div>
        <div id="usersTable">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Income</th>
                        <th>Transactions</th>
                        <th>Last Income</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sno = 1;
                    if($income_by_user && mysqli_num_rows($income_by_user) > 0): 
                        while($user = mysqli_fetch_assoc($income_by_user)): 
                    ?>
                        <tr class="user-row">
                            <td><?php echo $sno++; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['mobile'] ?? 'N/A'); ?></td>
                            <td class="text-success">₹<?php echo number_format($user['total_income'], 2); ?></td>
                            <td><?php echo $user['income_count']; ?></td>
                            <td class="text-muted"><?php echo $user['last_income'] ? date('d M Y', strtotime($user['last_income'])) : 'Never'; ?></td>
                            <td>
                                <a href="?view_user=<?php echo $user['user_id']; ?>" class="badge badge-primary" style="text-decoration:none;">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </td>
                        </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="8" style="text-align:center;padding:30px;color:var(--gray-400);">
                                <i class="fas fa-info-circle" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                                No income data available
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== RECENT INCOME ===== -->
    <div class="table-container">
        <div class="table-header">
            <h3><i class="fas fa-clock"></i> Recent Income Transactions</h3>
            <a href="admin_income_list.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php if($recent_income && mysqli_num_rows($recent_income) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Source</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($inc = mysqli_fetch_assoc($recent_income)): 
                        $badge_class = 'badge-primary';
                        if($inc['source'] == 'investment') $badge_class = 'badge-success';
                        elseif($inc['source'] == 'referral') $badge_class = 'badge-cyan';
                        elseif($inc['source'] == 'bonus') $badge_class = 'badge-warning';
                        elseif($inc['source'] == 'commission') $badge_class = 'badge-purple';
                        elseif($inc['source'] == 'dividend') $badge_class = 'badge-pink';
                        
                        $status_badge = 'badge-warning';
                        if($inc['status'] == 'paid') $status_badge = 'badge-success';
                        elseif($inc['status'] == 'cancelled') $status_badge = 'badge-danger';
                    ?>
                        <tr>
                            <td>#<?php echo str_pad($inc['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($inc['user_name']); ?></td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($inc['source']); ?></span></td>
                            <td class="text-success">+₹<?php echo number_format($inc['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($inc['description'] ?? '-'); ?></td>
                            <td><span class="badge <?php echo $status_badge; ?>"><?php echo ucfirst($inc['status']); ?></span></td>
                            <td class="text-muted"><?php echo date('d M Y, h:i A', strtotime($inc['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <i class="fas fa-inbox"></i>
                <p>No income transactions yet</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ========================================
USER DETAIL MODAL
======================================== -->
<?php if(isset($_GET['view_user']) && !empty($_GET['view_user']) && isset($user_details)): ?>
<div class="modal-overlay active" id="userModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-user-circle"></i> Income Details: <?php echo htmlspecialchars($user_details['name']); ?></h3>
            <button class="close-btn" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>

        <?php 
        // Calculate user stats
        $total_user_income = 0;
        $total_user_count = 0;
        $total_pending = 0;
        $user_income_sources = [];
        
        if($user_income_details && mysqli_num_rows($user_income_details) > 0) {
            while($inc = mysqli_fetch_assoc($user_income_details)) {
                $total_user_income += $inc['amount'];
                $total_user_count++;
                if($inc['status'] == 'pending') $total_pending++;
                if(isset($user_income_sources[$inc['source']])) {
                    $user_income_sources[$inc['source']] += $inc['amount'];
                } else {
                    $user_income_sources[$inc['source']] = $inc['amount'];
                }
            }
            // Reset pointer
            mysqli_data_seek($user_income_details, 0);
        }
        ?>

        <div class="user-summary-grid">
            <div class="us-item">
                <div class="us-value" style="color:var(--primary);">₹<?php echo number_format($total_user_income, 2); ?></div>
                <div class="us-label">Total Income</div>
            </div>
            <div class="us-item">
                <div class="us-value" style="color:var(--success);"><?php echo $total_user_count; ?></div>
                <div class="us-label">Transactions</div>
            </div>
            <div class="us-item">
                <div class="us-value" style="color:var(--warning);"><?php echo $total_pending; ?></div>
                <div class="us-label">Pending</div>
            </div>
            <div class="us-item">
                <div class="us-value" style="color:var(--purple);"><?php echo count($user_income_sources); ?></div>
                <div class="us-label">Income Sources</div>
            </div>
        </div>

        <?php if($user_income_details && mysqli_num_rows($user_income_details) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Source</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $sno = 1; while($inc = mysqli_fetch_assoc($user_income_details)): 
                        $status_badge = 'badge-warning';
                        if($inc['status'] == 'paid') $status_badge = 'badge-success';
                        elseif($inc['status'] == 'cancelled') $status_badge = 'badge-danger';
                    ?>
                        <tr>
                            <td><?php echo $sno++; ?></td>
                            <td><span class="badge badge-primary"><?php echo ucfirst($inc['source']); ?></span></td>
                            <td class="text-success">₹<?php echo number_format($inc['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($inc['description'] ?? '-'); ?></td>
                            <td><span class="badge <?php echo $status_badge; ?>"><?php echo ucfirst($inc['status']); ?></span></td>
                            <td class="text-muted"><?php echo date('d M Y', strtotime($inc['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <i class="fas fa-inbox"></i>
                <p>No income records found for this user</p>
            </div>
        <?php endif; ?>

        <div style="margin-top:16px;text-align:right;">
            <a href="income.php" class="badge badge-primary" style="text-decoration:none;padding:8px 20px;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<script>
    function closeModal() {
        window.location.href = 'income.php';
    }
    // Close modal on outside click
    document.getElementById('userModal').addEventListener('click', function(e) {
        if(e.target === this) {
            closeModal();
        }
    });
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if(e.key === 'Escape') {
            closeModal();
        }
    });
</script>
<?php endif; ?>

<!-- ========================================
JAVASCRIPT
======================================== -->
<script>
    // ========================================
    // INCOME CHART
    // ========================================
    const monthlyData = <?php echo json_encode($monthly_data); ?>;
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    const ctx = document.getElementById('incomeChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Income (₹)',
                data: monthlyData,
                backgroundColor: 'rgba(108, 99, 255, 0.6)',
                borderColor: '#6C63FF',
                borderWidth: 2,
                borderRadius: 6,
                barPercentage: 0.6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // ========================================
    // USER SEARCH FILTER
    // ========================================
    function filterUsers() {
        const input = document.getElementById('searchUser');
        const filter = input.value.toLowerCase();
        const rows = document.querySelectorAll('.user-row');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    }

    // ========================================
    // EXPORT CSV
    // ========================================
    function exportCSV() {
        const rows = document.querySelectorAll('#usersTable table tbody tr');
        let csv = 'S.No,User,Email,Phone,Total Income,Transactions,Last Income\n';
        
        rows.forEach((row, index) => {
            if(row.querySelector('td')) {
                const cols = row.querySelectorAll('td');
                const name = cols[1]?.textContent.trim() || '';
                const email = cols[2]?.textContent.trim() || '';
                const phone = cols[3]?.textContent.trim() || '';
                const income = cols[4]?.textContent.trim() || '';
                const count = cols[5]?.textContent.trim() || '';
                const last = cols[6]?.textContent.trim() || '';
                csv += `${index+1},"${name}","${email}","${phone}","${income}","${count}","${last}"\n`;
            }
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'income_by_user.csv';
        link.click();
    }

    console.log('💰 Income Dashboard loaded successfully!');
</script>

</body>
</html>