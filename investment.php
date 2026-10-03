<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include("db.php");

// Check database connection
if(!$conn) {
    die("Database connection failed!");
}

// Get all investments with user details
$sql = "SELECT 
            i.*,
            c.name as user_name,
            c.Email as user_email,
            p.name as plan_name
        FROM investments i
        LEFT JOIN contact c ON i.user_id = c.id
        LEFT JOIN investment_plans p ON i.plan_id = p.id
        ORDER BY i.created_at DESC";
$result = mysqli_query($conn, $sql);

if(!$result) {
    $error_msg = "Database Error: " . mysqli_error($conn);
    $result = null;
    $total_investments = 0;
} else {
    $total_investments = mysqli_num_rows($result);
}

// Get summary stats
$total_invested = 0;
$total_returns = 0;
$active_count = 0;
$completed_count = 0;
$pending_count = 0;

$summary_sql = "SELECT 
                    COALESCE(SUM(CASE WHEN status IN ('active', 'completed') THEN amount END), 0) as total_invested,
                    COALESCE(SUM(CASE WHEN status IN ('active', 'completed') THEN returns END), 0) as total_returns,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_count,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count
                FROM investments";
$summary_result = mysqli_query($conn, $summary_sql);
if($summary_result && mysqli_num_rows($summary_result) > 0) {
    $summary = mysqli_fetch_assoc($summary_result);
    $total_invested = $summary['total_invested'] ?? 0;
    $total_returns = $summary['total_returns'] ?? 0;
    $active_count = $summary['active_count'] ?? 0;
    $completed_count = $summary['completed_count'] ?? 0;
    $pending_count = $summary['pending_count'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investments | IncomeCoin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        /* ========================================
           ROOT VARIABLES
        ======================================== */
        :root {
            --primary: #6C63FF;
            --primary-dark: #5A52D5;
            --primary-light: #E8E6FF;
            --primary-gradient: linear-gradient(135deg, #6C63FF 0%, #5A52D5 100%);

            --success: #22c55e;
            --success-light: #dcfce7;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --info: #06b6d4;
            --info-light: #cffafe;
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

            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.1);

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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: var(--gray-100);
            color: var(--gray-800);
            padding: 20px;
        }

        .container {
            max-width: 1440px;
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
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: var(--transition);
        }

        .navbar:hover {
            box-shadow: var(--shadow-lg);
        }

        .navbar .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .navbar .brand .logo {
            width: 42px;
            height: 42px;
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

        .navbar .brand h2 {
            font-size: 20px;
            font-weight: 800;
            color: var(--gray-900);
        }

        .navbar .brand h2 span {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar .nav-links {
            display: flex;
            gap: 6px;
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
            background: var(--gray-50);
            color: var(--primary);
        }

        .navbar .nav-links a.active {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.35);
        }

        .navbar .nav-links a.logout {
            background: var(--danger);
            color: #fff;
        }

        .navbar .nav-links a.logout:hover {
            background: #dc2626;
        }

        .navbar .admin-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar .admin-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }

        .navbar .admin-profile .info h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-900);
        }

        .navbar .admin-profile .info span {
            font-size: 12px;
            color: var(--gray-500);
        }

        /* ========================================
           PAGE HEADER
        ======================================== */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-header .left h1 {
            font-size: 26px;
            font-weight: 800;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header .left h1 i {
            color: var(--primary);
        }

        .page-header .left p {
            color: var(--gray-500);
            font-size: 14px;
            margin-top: 2px;
        }

        /* ========================================
           STATS CARDS
        ======================================== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 18px 20px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0, 0, 0, 0.04);
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
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card .stat-number {
            font-size: 24px;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1.2;
        }

        .stat-card .stat-label {
            font-size: 13px;
            color: var(--gray-500);
            font-weight: 500;
            margin-top: 2px;
        }

        .stat-card .stat-icon {
            float: right;
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .stat-card.blue::before { background: var(--primary); }
        .stat-card.green::before { background: var(--success); }
        .stat-card.orange::before { background: var(--warning); }
        .stat-card.purple::before { background: var(--purple); }
        .stat-card.pink::before { background: var(--danger); }

        .stat-card .stat-icon.blue { background: var(--primary-light); color: var(--primary); }
        .stat-card .stat-icon.green { background: var(--success-light); color: var(--success); }
        .stat-card .stat-icon.orange { background: var(--warning-light); color: var(--warning); }
        .stat-card .stat-icon.purple { background: var(--purple-light); color: var(--purple); }
        .stat-card .stat-icon.pink { background: var(--danger-light); color: var(--danger); }

        /* ========================================
           TABLE WRAPPER
        ======================================== */
        .table-wrapper {
            background: #ffffff;
            border-radius: var(--radius-xl);
            padding: 24px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0, 0, 0, 0.04);
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

        .table-header .table-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .table-header .table-actions .search-box {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .table-header .table-actions .search-box input {
            padding: 8px 14px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 13px;
            transition: var(--transition);
            background: var(--gray-50);
            color: var(--gray-800);
            width: 200px;
        }

        .table-header .table-actions .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.1);
        }

        .table-header .table-actions .search-box button {
            padding: 8px 14px;
            border: none;
            border-radius: var(--radius-sm);
            background: var(--primary-gradient);
            color: #fff;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            font-size: 13px;
        }

        .table-header .table-actions .search-box button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(108, 99, 255, 0.3);
        }

        .btn-add {
            padding: 8px 18px;
            border: none;
            border-radius: var(--radius-sm);
            background: var(--success);
            color: #fff;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(34, 197, 94, 0.3);
        }

        /* ========================================
           TABLE STYLING
        ======================================== */
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        table thead {
            background: var(--gray-50);
        }

        table th {
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
            transition: var(--transition);
        }

        table tbody tr {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        table tbody tr:hover {
            background: var(--gray-50);
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Row Animation */
        table tbody tr {
            animation: rowFadeIn 0.3s ease forwards;
        }

        table tbody tr:nth-child(1) { animation-delay: 0.05s; }
        table tbody tr:nth-child(2) { animation-delay: 0.1s; }
        table tbody tr:nth-child(3) { animation-delay: 0.15s; }
        table tbody tr:nth-child(4) { animation-delay: 0.2s; }
        table tbody tr:nth-child(5) { animation-delay: 0.25s; }
        table tbody tr:nth-child(6) { animation-delay: 0.3s; }
        table tbody tr:nth-child(7) { animation-delay: 0.35s; }
        table tbody tr:nth-child(8) { animation-delay: 0.4s; }
        table tbody tr:nth-child(9) { animation-delay: 0.45s; }
        table tbody tr:nth-child(10) { animation-delay: 0.5s; }

        @keyframes rowFadeIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* ========================================
           BADGES
        ======================================== */
        .badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            transition: var(--transition);
        }

        .badge-success {
            background: var(--success-light);
            color: var(--success);
        }

        .badge-warning {
            background: var(--warning-light);
            color: var(--warning);
        }

        .badge-primary {
            background: var(--primary-light);
            color: var(--primary-dark);
        }

        .badge-danger {
            background: var(--danger-light);
            color: var(--danger);
        }

        .badge-info {
            background: var(--info-light);
            color: var(--info);
        }

        table tbody tr:hover .badge-success { background: var(--success); color: #fff; }
        table tbody tr:hover .badge-warning { background: var(--warning); color: #fff; }
        table tbody tr:hover .badge-primary { background: var(--primary); color: #fff; }
        table tbody tr:hover .badge-danger { background: var(--danger); color: #fff; }
        table tbody tr:hover .badge-info { background: var(--info); color: #fff; }

        /* ========================================
           TEXT COLORS
        ======================================== */
        .text-success { color: var(--success); font-weight: 600; }
        .text-muted { color: var(--gray-400); font-size: 13px; }
        .text-primary { color: var(--primary); }

        /* ========================================
           NO DATA
        ======================================== */
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray-500);
        }

        .no-data i {
            font-size: 56px;
            opacity: 0.3;
            display: block;
            margin-bottom: 12px;
        }

        .no-data h3 {
            font-size: 20px;
            color: var(--gray-700);
            margin-bottom: 4px;
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
           RESPONSIVE
        ======================================== */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 12px;
            }

            .navbar {
                flex-direction: column;
                align-items: stretch;
            }

            .navbar .brand {
                justify-content: center;
            }

            .navbar .nav-links {
                justify-content: center;
            }

            .navbar .nav-links a {
                font-size: 12px;
                padding: 6px 12px;
            }

            .navbar .admin-profile {
                justify-content: center;
            }

            .navbar .admin-profile .info {
                display: none;
            }

            .page-header {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }

            .stat-card .stat-number {
                font-size: 20px;
            }

            .table-wrapper {
                padding: 16px;
            }

            .table-header {
                flex-direction: column;
                align-items: stretch;
            }

            .table-header .table-actions {
                flex-direction: column;
            }

            .table-header .table-actions .search-box input {
                width: 100%;
            }

            table {
                min-width: 750px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .page-header .left h1 {
                font-size: 20px;
            }

            .action-btns a {
                font-size: 11px;
                padding: 4px 10px;
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
                --gray-500: #94a3b8;
                --gray-600: #94a3b8;
                --gray-700: #cbd5e1;
                --gray-800: #f1f5f9;
                --gray-900: #ffffff;
            }

            body {
                background: #0f172a;
            }

            .navbar,
            .stat-card,
            .table-wrapper {
                background: #1e293b;
                border-color: #334155;
            }

            .navbar .brand h2 {
                color: #f1f5f9;
            }

            .navbar .nav-links a {
                color: #94a3b8;
            }

            .navbar .nav-links a:hover {
                background: #334155;
                color: #60a5fa;
            }

            .navbar .nav-links a.active {
                background: var(--primary-gradient);
                color: #fff;
            }

            .navbar .admin-profile .info h4 {
                color: #f1f5f9;
            }

            .page-header .left h1 {
                color: #f1f5f9;
            }

            .stat-card .stat-number {
                color: #f1f5f9;
            }

            .stat-card .stat-icon.blue { background: #1e3a5f; color: #60a5fa; }
            .stat-card .stat-icon.green { background: #1a3a2a; color: #4ade80; }
            .stat-card .stat-icon.orange { background: #3a2a1a; color: #fbbf24; }
            .stat-card .stat-icon.purple { background: #2e1a4a; color: #a78bfa; }
            .stat-card .stat-icon.pink { background: #3a1a1a; color: #f87171; }

            table thead {
                background: #334155;
            }

            table th {
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

            .text-muted {
                color: #94a3b8;
            }

            .table-header .table-actions .search-box input {
                background: #334155;
                border-color: #475569;
                color: #f1f5f9;
            }

            .table-header .table-actions .search-box input:focus {
                background: #1e293b;
                border-color: var(--primary);
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

            .table-header .info {
                color: #94a3b8;
            }

            .table-header .info strong {
                color: #f1f5f9;
            }

            .badge-success { background: #1a3a2a; color: #4ade80; }
            .badge-warning { background: #3a2a1a; color: #fbbf24; }
            .badge-primary { background: #1e3a5f; color: #60a5fa; }
            .badge-danger { background: #3a1a1a; color: #f87171; }
            .badge-info { background: #1a2a3a; color: #22d3ee; }

            table tbody tr:hover .badge-success { background: var(--success); color: #fff; }
            table tbody tr:hover .badge-warning { background: var(--warning); color: #fff; }
            table tbody tr:hover .badge-primary { background: var(--primary); color: #fff; }
            table tbody tr:hover .badge-danger { background: var(--danger); color: #fff; }
            table tbody tr:hover .badge-info { background: var(--info); color: #fff; }

            .text-success { color: #4ade80; }

            .no-data {
                color: #94a3b8;
            }

            .no-data h3 {
                color: #e2e8f0;
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

        /* ========================================
           ANIMATIONS
        ======================================== */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card {
            animation: fadeIn 0.5s ease forwards;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card:nth-child(5) { animation-delay: 0.5s; }

        .table-wrapper {
            animation: fadeIn 0.5s ease forwards 0.2s;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- ========================================
    NAVBAR
    ======================================== -->
    <nav class="navbar">
        <div class="brand">
            <div class="logo">IC</div>
            <h2>Income<span>Coin</span></h2>
        </div>

        <div class="nav-links">
            <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="view_product.php"><i class="fas fa-box"></i> Products</a>
            <a href="investments.php" class="active"><i class="fas fa-chart-line"></i> Investments</a>
                <a href="#"><i class="fas fa-users"></i> Users</a>
               
      
        </div>

        <div class="admin-profile">
            <div class="info">
                <h4>Admin</h4>
                <span>Super Admin</span>
            </div>
            <img src="https://ui-avatars.com/api/?name=Admin&background=6C63FF&color=fff&size=40&bold=true" alt="Admin">
        </div>
    </nav>

    <!-- ========================================
    PAGE HEADER
    ======================================== -->
    <div class="page-header">
        <div class="left">
            <h1><i class="fas fa-chart-line"></i> Investments</h1>
            <p>Track and manage all user investments</p>
        </div>
        <div class="right">
            <a href="investment.php" class="btn-add">
                <i class="fas fa-plus"></i> Add Investment
            </a>
        </div>
    </div>

    <!-- ========================================
    STATS CARDS
    ======================================== -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-icon blue"><i class="fas fa-coins"></i></div>
            <div class="stat-number">₹<?php echo number_format($total_invested, 2); ?></div>
            <div class="stat-label">Total Invested</div>
        </div>

        <div class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-arrow-up"></i></div>
            <div class="stat-number" style="color: var(--success);">₹<?php echo number_format($total_returns, 2); ?></div>
            <div class="stat-label">Total Returns</div>
        </div>

        <div class="stat-card orange">
            <div class="stat-icon orange"><i class="fas fa-spinner"></i></div>
            <div class="stat-number" style="color: var(--warning);"><?php echo $active_count; ?></div>
            <div class="stat-label">Active</div>
        </div>

        <div class="stat-card purple">
            <div class="stat-icon purple"><i class="fas fa-check-circle"></i></div>
            <div class="stat-number" style="color: var(--purple);"><?php echo $completed_count; ?></div>
            <div class="stat-label">Completed</div>
        </div>

        <div class="stat-card pink">
            <div class="stat-icon pink"><i class="fas fa-clock"></i></div>
            <div class="stat-number" style="color: var(--danger);"><?php echo $pending_count; ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>

    <!-- ========================================
    TABLE WRAPPER
    ======================================== -->
    <div class="table-wrapper">
        <div class="table-header">
            <div class="info">
                <i class="fas fa-list"></i> Showing <strong><?php echo $total_investments; ?></strong> investments
            </div>
            <div class="table-actions">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search by user, plan..." onkeyup="searchTable()">
                    <button onclick="searchTable()"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </div>

        <?php if($total_investments > 0): ?>
        <table id="investmentTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Returns</th>
                    <th>Rate</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while($inv = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo str_pad($inv['id'], 6, '0', STR_PAD_LEFT); ?></td>
                    <td>
                        <div>
                            <div style="font-weight:600;color:var(--gray-900);">
                                <?php echo htmlspecialchars($inv['user_name'] ?? 'N/A'); ?>
                            </div>
                            <div style="font-size:12px;color:var(--gray-400);">
                                <?php echo htmlspecialchars($inv['user_email'] ?? 'N/A'); ?>
                            </div>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($inv['plan_name'] ?? 'N/A'); ?></td>
                    <td><strong>₹<?php echo number_format($inv['amount'], 2); ?></strong></td>
                    <td class="text-success">₹<?php echo number_format($inv['returns'], 2); ?></td>
                    <td><?php echo $inv['rate']; ?>%</td>
                    <td><?php echo $inv['duration']; ?>M</td>
                    <td>
                        <span class="badge badge-<?php echo $inv['status'] == 'active' ? 'success' : ($inv['status'] == 'pending' ? 'warning' : ($inv['status'] == 'completed' ? 'primary' : 'danger')); ?>">
                            <?php echo ucfirst($inv['status']); ?>
                        </span>
                    </td>
                    <td class="text-muted"><?php echo date('d M Y', strtotime($inv['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-data">
            <i class="fas fa-chart-line"></i>
            <h3>No Investments Found</h3>
            <p>Start by adding your first investment</p>
        </div>
        <?php endif; ?>

        <!-- ===== PAGINATION ===== -->
        <div class="pagination">
            <span class="disabled"><i class="fas fa-chevron-left"></i> Prev</span>
            <span class="active">1</span>
            <a href="#">2</a>
            <a href="#">3</a>
            <a href="#">4</a>
            <a href="#">5</a>
            <a href="#">Next <i class="fas fa-chevron-right"></i></a>
        </div>
    </div>

</div>

<script>
    // =============================
    // SEARCH FUNCTIONALITY
    // =============================
    function searchTable() {
        var input = document.getElementById('searchInput');
        var filter = input.value.toUpperCase();
        var table = document.getElementById('investmentTable');
        var tr = table.getElementsByTagName('tr');

        for (var i = 1; i < tr.length; i++) {
            var td = tr[i].getElementsByTagName('td');
            var found = false;
            for (var j = 0; j < td.length; j++) {
                if (td[j]) {
                    var textValue = td[j].textContent || td[j].innerText;
                    if (textValue.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            tr[i].style.display = found ? '' : 'none';
        }
    }

    // Enter key support for search
    document.getElementById('searchInput').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            searchTable();
        }
    });
</script>

</body>
</html>