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

// Get all users with error handling
$sql = "SELECT * FROM contact ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if(!$result) {
    $error_msg = "Database Error: " . mysqli_error($conn);
    $result = null;
    $total_users = 0;
} else {
    $total_users = mysqli_num_rows($result);
}

// Get stats with error handling
$today_users = 0;
$admin_count = 0;
$user_count = 0;
$new_users_today = 0;
$new_this_week = 0;
$new_this_month = 0;
$new_this_year = 0;

// Today's new users
$sql_today = "SELECT COUNT(*) as today FROM contact WHERE DATE(created_at) = CURDATE()";
$today_result = mysqli_query($conn, $sql_today);
if($today_result && mysqli_num_rows($today_result) > 0) {
    $row = mysqli_fetch_assoc($today_result);
    $new_users_today = $row['today'] ?? 0;
}

// Admin count
$sql_admin = "SELECT COUNT(*) as admin FROM contact WHERE role = 'admin'";
$admin_result = mysqli_query($conn, $sql_admin);
if($admin_result && mysqli_num_rows($admin_result) > 0) {
    $row = mysqli_fetch_assoc($admin_result);
    $admin_count = $row['admin'] ?? 0;
}

// User count
$sql_user = "SELECT COUNT(*) as user FROM contact WHERE role = 'user' OR role IS NULL";
$user_result = mysqli_query($conn, $sql_user);
if($user_result && mysqli_num_rows($user_result) > 0) {
    $row = mysqli_fetch_assoc($user_result);
    $user_count = $row['user'] ?? 0;
}

// This week
$sql_week = "SELECT COUNT(*) as week FROM contact WHERE WEEK(created_at) = WEEK(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
$week_result = mysqli_query($conn, $sql_week);
if($week_result && mysqli_num_rows($week_result) > 0) {
    $row = mysqli_fetch_assoc($week_result);
    $new_this_week = $row['week'] ?? 0;
}

// This month
$sql_month = "SELECT COUNT(*) as month FROM contact WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
$month_result = mysqli_query($conn, $sql_month);
if($month_result && mysqli_num_rows($month_result) > 0) {
    $row = mysqli_fetch_assoc($month_result);
    $new_this_month = $row['month'] ?? 0;
}

// This year
$sql_year = "SELECT COUNT(*) as year FROM contact WHERE YEAR(created_at) = YEAR(CURDATE())";
$year_result = mysqli_query($conn, $sql_year);
if($year_result && mysqli_num_rows($year_result) > 0) {
    $row = mysqli_fetch_assoc($year_result);
    $new_this_year = $row['year'] ?? 0;
}

// Get chart data (last 7 days)
$chart_data = [];
for($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day_label = date('d M', strtotime($date));
    $sql_day = "SELECT COUNT(*) as count FROM contact WHERE DATE(created_at) = '$date'";
    $day_result = mysqli_query($conn, $sql_day);
    $count = 0;
    if($day_result && mysqli_num_rows($day_result) > 0) {
        $row = mysqli_fetch_assoc($day_result);
        $count = $row['count'] ?? 0;
    }
    $chart_data[] = [
        'label' => $day_label,
        'count' => $count
    ];
}

// Get recent users
$sql_recent = "SELECT * FROM contact ORDER BY created_at DESC LIMIT 5";
$recent_result = mysqli_query($conn, $sql_recent);
$recent_users = [];
if($recent_result && mysqli_num_rows($recent_result) > 0) {
    while($row = mysqli_fetch_assoc($recent_result)) {
        $recent_users[] = $row;
    }
}

// Get today's users
$sql_today_users = "SELECT * FROM contact WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC LIMIT 5";
$today_users_result = mysqli_query($conn, $sql_today_users);
$today_users_list = [];
if($today_users_result && mysqli_num_rows($today_users_result) > 0) {
    while($row = mysqli_fetch_assoc($today_users_result)) {
        $today_users_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Thunder X</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
    /* ========================================
           ROOT VARIABLES
        ======================================== */
  /* ========================================
   ROOT VARIABLES - DARK PREMIUM THEME
======================================== */
:root {
    --primary: #f59e0b;
    --primary-dark: #d97706;
    --primary-light: #fbbf24;
    --primary-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    --primary-glow: 0 0 30px rgba(245, 158, 11, 0.15);
    
    --secondary: #8b5cf6;
    --secondary-gradient: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
    
    --success: #10b981;
    --success-dark: #065f46;
    --success-light: #d1fae5;
    --warning: #f59e0b;
    --danger: #ef4444;
    --danger-light: #fecaca;
    --purple: #8b5cf6;
    --pink: #ec4899;
    --cyan: #06b6d4;
    
    --bg-primary: #0a0a1a;
    --bg-secondary: #12122a;
    --bg-card: #1a1a3a;
    --bg-card-hover: #22224a;
    --bg-input: #2a2a4a;
    --bg-glass: rgba(26, 26, 58, 0.85);
    
    --text-primary: #ffffff;
    --text-secondary: #94a3b8;
    --text-muted: #64748b;
    --text-glow: #fbbf24;
    
    --border-color: rgba(255, 255, 255, 0.06);
    --border-light: rgba(255, 255, 255, 0.1);
    --border-glow: rgba(245, 158, 11, 0.2);
    
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.4);
    --shadow-md: 0 4px 20px rgba(0,0,0,0.5);
    --shadow-lg: 0 10px 40px rgba(0,0,0,0.6);
    --shadow-xl: 0 20px 60px rgba(0,0,0,0.7);
    --shadow-glow: 0 0 40px rgba(245, 158, 11, 0.05);
    
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
    background: var(--bg-primary);
    color: var(--text-primary);
    padding: 20px;
    background-image: 
        radial-gradient(ellipse at 10% 20%, rgba(139, 92, 246, 0.05) 0%, transparent 50%),
        radial-gradient(ellipse at 90% 80%, rgba(245, 158, 11, 0.05) 0%, transparent 50%),
        radial-gradient(ellipse at 50% 50%, rgba(139, 92, 246, 0.03) 0%, transparent 70%);
    position: relative;
}

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(2px 2px at 20% 30%, rgba(255,255,255,0.03), transparent),
        radial-gradient(2px 2px at 40% 70%, rgba(255,255,255,0.03), transparent),
        radial-gradient(2px 2px at 60% 20%, rgba(255,255,255,0.03), transparent),
        radial-gradient(2px 2px at 80% 60%, rgba(255,255,255,0.03), transparent);
    pointer-events: none;
    z-index: 0;
}

.admin-container {
    max-width: 1440px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

/* ========================================
   GLOW ANIMATIONS
======================================== */
@keyframes glowPulse {
    0%, 100% { 
        box-shadow: 0 0 20px rgba(245, 158, 11, 0.2);
    }
    50% { 
        box-shadow: 0 0 40px rgba(245, 158, 11, 0.4);
    }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
}

@keyframes shimmer {
    0% { background-position: -200% center; }
    100% { background-position: 200% center; }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* ========================================
   ERROR MESSAGE
======================================== */
.error-msg {
    background: rgba(239, 68, 68, 0.1);
    color: #fca5a5;
    padding: 16px 20px;
    border-radius: var(--radius-lg);
    border-left: 4px solid var(--danger);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(239, 68, 68, 0.1);
    animation: slideInLeft 0.4s ease;
}

.error-msg i {
    font-size: 20px;
}

/* ========================================
   HEADER / TITLE
======================================== */
.page-title {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    border-radius: var(--radius-xl);
    padding: 20px 28px;
    margin-bottom: 28px;
    box-shadow: var(--shadow-md), var(--shadow-glow);
    border: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    animation: fadeInUp 0.5s ease;
}

.page-title .title-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.page-title .title-left .icon {
    width: 50px;
    height: 50px;
    background: var(--primary-gradient);
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 22px;
    box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
    animation: glowPulse 3s ease-in-out infinite;
}

.page-title .title-left h2 {
    font-size: 24px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.5px;
}

.page-title .title-left h2 span {
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.page-title .title-left p {
    color: var(--text-secondary);
    font-size: 14px;
    margin-top: 2px;
}

.page-title .header-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.page-title .header-actions .admin-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    padding-right: 16px;
    border-right: 1px solid var(--border-color);
}

.page-title .header-actions .admin-profile img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--primary);
    box-shadow: 0 0 20px rgba(245, 158, 11, 0.2);
}

.page-title .header-actions .admin-profile .info h4 {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
}

.page-title .header-actions .admin-profile .info span {
    font-size: 12px;
    color: var(--text-muted);
}

.btn-logout {
    padding: 8px 20px;
    border: none;
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    box-shadow: 0 4px 20px rgba(239, 68, 68, 0.2);
}

.btn-logout:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 6px 30px rgba(239, 68, 68, 0.4);
}

/* ========================================
   STATS CARDS
======================================== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 28px;
}

.stat-card {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    border-radius: var(--radius-lg);
    padding: 20px 24px;
    box-shadow: var(--shadow-md), var(--shadow-glow);
    border: 1px solid var(--border-color);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.5s ease forwards;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--primary-gradient);
    opacity: 0;
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-lg), var(--shadow-glow);
    border-color: var(--border-glow);
}

.stat-card:hover::before {
    opacity: 1;
}

.stat-card .stat-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.stat-card .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    transition: var(--transition);
}

.stat-card:hover .stat-icon {
    transform: scale(1.1) rotate(-5deg);
}

.stat-card .stat-icon.blue {
    background: rgba(59, 130, 246, 0.15);
    color: #60a5fa;
}

.stat-card .stat-icon.green {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
}

.stat-card .stat-icon.purple {
    background: rgba(139, 92, 246, 0.15);
    color: #a78bfa;
}

.stat-card .stat-icon.orange {
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
}

.stat-card .stat-number {
    font-size: 28px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.2;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-card .stat-label {
    font-size: 14px;
    color: var(--text-secondary);
    font-weight: 500;
    margin-top: 4px;
}

.stat-card .stat-change {
    font-size: 12px;
    font-weight: 600;
    margin-top: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 12px;
    border-radius: 20px;
}

.stat-card .stat-change.positive {
    color: #34d399;
    background: rgba(16, 185, 129, 0.12);
}

.stat-card .stat-change.neutral {
    color: var(--text-muted);
    background: rgba(255, 255, 255, 0.05);
}

/* ========================================
   CHART & RECENT SECTION
======================================== */
.dashboard-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 28px;
}

.chart-card {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    border-radius: var(--radius-xl);
    padding: 24px;
    box-shadow: var(--shadow-md), var(--shadow-glow);
    border: 1px solid var(--border-color);
    animation: fadeInUp 0.5s ease forwards 0.15s;
}

.chart-card .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color);
}

.chart-card .card-header h3 {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.chart-card .card-header h3 i {
    color: var(--primary);
}

.chart-bars {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    height: 150px;
    padding-top: 10px;
    gap: 8px;
}

.chart-bar-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.chart-bar {
    width: 100%;
    max-width: 40px;
    min-height: 4px;
    background: var(--primary-gradient);
    border-radius: 4px 4px 0 0;
    transition: var(--transition);
    position: relative;
    cursor: pointer;
    box-shadow: 0 0 20px rgba(245, 158, 11, 0.1);
}

.chart-bar:hover {
    transform: scaleY(1.05);
    box-shadow: 0 0 40px rgba(245, 158, 11, 0.3);
}

.chart-bar .bar-value {
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 11px;
    font-weight: 600;
    color: var(--text-secondary);
    opacity: 0;
    transition: var(--transition);
}

.chart-bar:hover .bar-value {
    opacity: 1;
}

.chart-bar-label {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 500;
    text-align: center;
}

/* Quick Stats */
.quick-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.quick-stat-item {
    background: rgba(255, 255, 255, 0.03);
    padding: 16px;
    border-radius: var(--radius-md);
    text-align: center;
    transition: var(--transition);
    border: 1px solid var(--border-color);
    backdrop-filter: blur(10px);
}

.quick-stat-item:hover {
    border-color: var(--primary);
    background: rgba(245, 158, 11, 0.05);
    transform: translateY(-3px);
    box-shadow: var(--shadow-glow);
}

.quick-stat-item .number {
    font-size: 24px;
    font-weight: 800;
    color: var(--text-primary);
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.quick-stat-item .label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    margin-top: 2px;
}

.quick-stat-item .icon {
    font-size: 20px;
    margin-bottom: 4px;
}

.quick-stat-item:nth-child(1) .icon { color: #60a5fa; }
.quick-stat-item:nth-child(2) .icon { color: #34d399; }
.quick-stat-item:nth-child(3) .icon { color: #fbbf24; }
.quick-stat-item:nth-child(4) .icon { color: #a78bfa; }

/* ========================================
   RECENT USERS
======================================== */
.recent-users-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 28px;
}

.recent-card {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    border-radius: var(--radius-xl);
    padding: 24px;
    box-shadow: var(--shadow-md), var(--shadow-glow);
    border: 1px solid var(--border-color);
    animation: fadeInUp 0.5s ease forwards 0.2s;
}

.recent-card .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color);
}

.recent-card .card-header h3 {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.recent-card .card-header h3 i {
    color: var(--primary);
}

.recent-card .card-header .view-all {
    font-size: 13px;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
}

.recent-card .card-header .view-all:hover {
    color: var(--primary-light);
    text-decoration: underline;
}

.recent-user-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 10px 12px;
    border-radius: var(--radius-sm);
    transition: var(--transition);
    border-bottom: 1px solid var(--border-color);
}

.recent-user-item:last-child {
    border-bottom: none;
}

.recent-user-item:hover {
    background: rgba(255, 255, 255, 0.03);
    transform: translateX(4px);
}

.recent-user-item .avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border-color);
    transition: var(--transition);
}

.recent-user-item:hover .avatar {
    border-color: var(--primary);
}

.recent-user-item .user-info {
    flex: 1;
}

.recent-user-item .user-info .name {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 14px;
}

.recent-user-item .user-info .email {
    font-size: 12px;
    color: var(--text-muted);
}

.recent-user-item .user-time {
    font-size: 12px;
    color: var(--text-muted);
    text-align: right;
}

.recent-user-item .user-time .badge-new {
    display: inline-block;
    padding: 2px 10px;
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
    margin-top: 2px;
}

/* ========================================
   TABLE
======================================== */
.table-wrapper {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    border-radius: var(--radius-xl);
    padding: 10px;
    box-shadow: var(--shadow-md), var(--shadow-glow);
    border: 1px solid var(--border-color);
    overflow-x: auto;
    animation: fadeInUp 0.5s ease forwards 0.25s;
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
    color: var(--text-muted);
}

.table-header .info strong {
    color: var(--text-primary);
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
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    font-size: 13px;
    transition: var(--transition);
    background: var(--bg-input);
    color: var(--text-primary);
    width: 200px;
    font-family: 'Inter', sans-serif;
}

.table-header .table-actions .search-box input::placeholder {
    color: var(--text-muted);
}

.table-header .table-actions .search-box input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
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
    box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
}

.btn-add {
    padding: 8px 18px;
    border: none;
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #fff;
    cursor: pointer;
    font-weight: 600;
    transition: var(--transition);
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    box-shadow: 0 4px 20px rgba(16, 185, 129, 0.2);
}

.btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 30px rgba(16, 185, 129, 0.4);
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
    background: rgba(255, 255, 255, 0.03);
}

table th {
    color: var(--text-muted);
    padding: 12px 16px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    border-bottom: 1px solid var(--border-color);
    white-space: nowrap;
}

table td {
    padding: 12px 16px;
    color: var(--text-secondary);
    font-size: 14px;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
    transition: var(--transition);
}

table tbody tr {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    animation: fadeInUp 0.3s ease forwards;
}

table tbody tr:hover {
    background: rgba(245, 158, 11, 0.04);
}

table tbody tr:last-child td {
    border-bottom: none;
}

/* Row animation delays */
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

/* ========================================
   USER CELL
======================================== */
.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-cell .avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border-color);
    transition: var(--transition);
}

table tbody tr:hover .user-cell .avatar {
    border-color: var(--primary);
    box-shadow: 0 0 20px rgba(245, 158, 11, 0.2);
}

.user-cell .user-name {
    font-weight: 600;
    color: var(--text-primary);
}

.user-cell .user-email {
    font-size: 12px;
    color: var(--text-muted);
}

/* ========================================
   BADGES
======================================== */
.badge {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    transition: var(--transition);
    letter-spacing: 0.3px;
}

.badge-admin {
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
    border: 1px solid rgba(245, 158, 11, 0.2);
}

.badge-user {
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
}

.badge-new {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

table tbody tr:hover .badge-admin {
    background: var(--primary-gradient);
    color: #fff;
    border-color: transparent;
}

table tbody tr:hover .badge-user {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
    border-color: var(--border-light);
}

/* ========================================
   PASSWORD TEXT
======================================== */
.password-text {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: var(--text-secondary);
    background: rgba(255, 255, 255, 0.03);
    padding: 2px 8px;
    border-radius: 4px;
    border: 1px dashed var(--border-color);
    transition: var(--transition);
}

table tbody tr:hover .password-text {
    border-color: var(--primary);
    background: rgba(245, 158, 11, 0.05);
    color: var(--primary-light);
}

/* ========================================
   ACTION BUTTONS
======================================== */
.action-btns {
    display: flex;
    gap: 7px;
    flex-wrap: wrap;
}

.action-btns a {
    padding: 6px 20px;
    border-radius: var(--radius-sm);
    text-decoration: none;
    font-size: 11px;
    font-weight: 600;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 3px;
    letter-spacing: 0.3px;
}

.action-btns .edit-btn {
    background: rgba(59, 130, 246, 0.12);
    color: #60a5fa;
    border: 1px solid rgba(59, 130, 246, 0.1);
}

.action-btns .edit-btn:hover {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);
    border-color: transparent;
}

.action-btns .delete-btn {
    background: rgba(239, 68, 68, 0.12);
    color: #fca5a5;
    border: 1px solid rgba(239, 68, 68, 0.1);
}

.action-btns .delete-btn:hover {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(239, 68, 68, 0.3);
    border-color: transparent;
}

.action-btns .view-btn {
    background: rgba(6, 182, 212, 0.12);
    color: #67e8f9;
    border: 1px solid rgba(6, 182, 212, 0.1);
}

.action-btns .view-btn:hover {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(6, 182, 212, 0.3);
    border-color: transparent;
}

.no-data {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-muted);
}

.no-data i {
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
    border: 1px solid var(--border-color);
    text-decoration: none;
    color: var(--text-secondary);
    font-weight: 500;
    font-size: 14px;
    transition: var(--transition);
    background: var(--bg-glass);
}

.pagination a:hover {
    background: rgba(245, 158, 11, 0.08);
    border-color: var(--primary);
    color: var(--primary-light);
    transform: translateY(-2px);
}

.pagination .active {
    background: var(--primary-gradient);
    color: #fff;
    border-color: var(--primary);
    box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
}

.pagination .disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ========================================
   RESPONSIVE
======================================== */
@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .dashboard-row {
        grid-template-columns: 1fr;
    }
    .recent-users-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    body {
        padding: 12px;
    }

    .page-title {
        flex-direction: column;
        align-items: stretch;
        padding: 16px 18px;
    }

    .page-title .header-actions {
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .stat-card {
        padding: 16px 18px;
    }

    .stat-card .stat-number {
        font-size: 22px;
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
        min-width: 650px;
    }

    .page-title .title-left h2 {
        font-size: 20px;
    }

    .page-title .header-actions .admin-profile .info {
        display: none;
    }

    .page-title .header-actions .admin-profile {
        border-right: none;
        padding-right: 0;
    }

    .recent-card {
        padding: 16px;
    }

    .quick-stats {
        grid-template-columns: 1fr 1fr;
    }

    .chart-card {
        padding: 16px;
    }

    .chart-bars {
        height: 120px;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .btn-logout span {
        display: none;
    }

    .action-btns a {
        font-size: 10px;
        padding: 4px 10px;
    }

    .password-text {
        font-size: 11px;
    }

    .quick-stats {
        grid-template-columns: 1fr;
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
    background: var(--bg-secondary);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: var(--primary-gradient);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--primary-dark);
}
    </style>
</head>

<body>

    <div class="admin-container">

        <!-- ===== HEADER ===== -->
        <div class="page-title">
            <div class="title-left">
                <div class="icon"><i class="fas fa-users"></i></div>
                <div>
                    <h2>Admin <span>Dashboard</span></h2>
                    <p>Manage all registered users from here</p>
                </div>
            </div>

            <div class="header-actions">
                <div class="admin-profile">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=2563eb&color=fff&size=40&bold=true"
                        alt="Admin">
                    <div class="info">
                        <h4>Admin</h4>
                        <span>Super Admin</span>
                    </div>
                </div>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- ===== ERROR MESSAGE ===== -->
        <?php if(isset($error_msg)): ?>
        <div class="error-msg">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo $error_msg; ?>
        </div>
        <?php endif; ?>

        <!-- ===== STATS CARDS ===== -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-top">
                    <div>
                        <div class="stat-number"><?php echo number_format($total_users); ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                </div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +<?php echo $new_users_today; ?> today
                </div>
            </div>

            <div class="stat-card green">
                <div class="stat-top">
                    <div>
                        <div class="stat-number"><?php echo number_format($user_count); ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
                </div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +<?php echo $new_this_week; ?> this week
                </div>
            </div>

            <div class="stat-card purple">
                <div class="stat-top">
                    <div>
                        <div class="stat-number"><?php echo number_format($admin_count); ?></div>
                        <div class="stat-label">Admins</div>
                    </div>
                    <div class="stat-icon purple"><i class="fas fa-user-tie"></i></div>
                </div>
                <div class="stat-change neutral"><i class="fas fa-minus"></i> Stable</div>
            </div>

            <div class="stat-card orange">
                <div class="stat-top">
                    <div>
                        <div class="stat-number"><?php echo number_format($new_users_today); ?></div>
                        <div class="stat-label">New Today</div>
                    </div>
                    <div class="stat-icon orange"><i class="fas fa-user-plus"></i></div>
                </div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> New users today
                </div>
            </div>
        </div>

        <!-- ===== CHART & QUICK STATS ===== -->
        <div class="dashboard-row">
            <div class="chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-bar"></i> Registration Overview (Last 7 Days)</h3>
                    <span style="font-size: 13px; color: var(--gray-400);">
                        <i class="fas fa-calendar-alt"></i> <?php echo date('d M Y'); ?>
                    </span>
                </div>
                <div class="chart-bars">
                    <?php 
                    $max_count = max(array_column($chart_data, 'count'));
                    if($max_count == 0) $max_count = 1;
                    foreach($chart_data as $item): 
                        $height = ($item['count'] / $max_count) * 100;
                    ?>
                    <div class="chart-bar-item">
                        <div class="chart-bar" style="height: <?php echo max($height, 5); ?>%;">
                            <span class="bar-value"><?php echo $item['count']; ?></span>
                        </div>
                        <span class="chart-bar-label"><?php echo $item['label']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="recent-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-simple"></i> Quick Stats</h3>
                </div>
                <div class="quick-stats">
                    <div class="quick-stat-item">
                        <div class="icon"><i class="fas fa-user-plus"></i></div>
                        <div class="number"><?php echo $new_users_today; ?></div>
                        <div class="label">Today</div>
                    </div>
                    <div class="quick-stat-item">
                        <div class="icon"><i class="fas fa-calendar-week"></i></div>
                        <div class="number"><?php echo $new_this_week; ?></div>
                        <div class="label">This Week</div>
                    </div>
                    <div class="quick-stat-item">
                        <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                        <div class="number"><?php echo $new_this_month; ?></div>
                        <div class="label">This Month</div>
                    </div>
                    <div class="quick-stat-item">
                        <div class="icon"><i class="fas fa-calendar-year"></i></div>
                        <div class="number"><?php echo $new_this_year; ?></div>
                        <div class="label">This Year</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== RECENT USERS ===== -->
        <!-- <div class="recent-users-row">
            <div class="recent-card">
                <div class="card-header">
                    <h3><i class="fas fa-users"></i> Recent Users</h3>
                    <a href="#" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <?php if(!empty($recent_users)): ?>
                    <?php foreach($recent_users as $recent): ?>
                    <div class="recent-user-item">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($recent['name']); ?>&background=2563eb&color=fff&size=42&bold=true"
                            alt="avatar" class="avatar">
                        <div class="user-info">
                            <div class="name"><?php echo htmlspecialchars($recent['name']); ?></div>
                            <div class="email"><?php echo htmlspecialchars($recent['Email'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="user-time">
                            <?php 
                            $created = strtotime($recent['created_at'] ?? 'now');
                            $is_today = date('Y-m-d', $created) == date('Y-m-d');
                            ?>
                            <?php if($is_today): ?>
                                <span class="badge-new"><i class="fas fa-star"></i> New</span>
                            <?php endif; ?>
                            <div style="font-size: 11px; margin-top: 2px;">
                                <?php echo date('d M, h:i A', $created); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center; padding: 30px; color: var(--gray-400);">
                        <i class="fas fa-users-slash" style="font-size: 30px; display:block; margin-bottom:10px;"></i>
                        No recent users found
                    </div>
                <?php endif; ?>
            </div>

            <div class="recent-card">
                <div class="card-header">
                    <h3><i class="fas fa-clock"></i> Today's Registrations</h3>
                    <span style="font-size: 13px; color: var(--gray-400);">
                        <i class="fas fa-calendar-check"></i> <?php echo date('d M Y'); ?>
                    </span>
                </div>
                <?php if(!empty($today_users_list)): ?>
                    <?php foreach($today_users_list as $today_user): ?>
                    <div class="recent-user-item">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($today_user['name']); ?>&background=2563eb&color=fff&size=42&bold=true"
                            alt="avatar" class="avatar">
                        <div class="user-info">
                            <div class="name"><?php echo htmlspecialchars($today_user['name']); ?></div>
                            <div class="email"><?php echo htmlspecialchars($today_user['Email'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="user-time">
                            <span class="badge-new"><i class="fas fa-clock"></i> Today</span>
                            <div style="font-size: 11px; margin-top: 2px;">
                                <?php echo date('h:i A', strtotime($today_user['created_at'] ?? 'now')); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center; padding: 30px; color: var(--gray-400);">
                        <i class="fas fa-user-plus" style="font-size: 30px; display:block; margin-bottom:10px;"></i>
                        No registrations today
                    </div>
                <?php endif; ?>
            </div>
        </div> -->

        <!-- ===== TABLE ===== -->
        <div class="table-wrapper">
            <div class="table-header">
                <div class="info">
                    <i class="fas fa-list"></i> Showing <strong><?php echo $total_users; ?></strong> users
                </div>
                <div class="table-actions">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search by name, email..."
                            onkeyup="searchTable()">
                        <button onclick="searchTable()"><i class="fas fa-search"></i></button>
                    </div>
                    <a href="#" class="btn-add">
                        <i class="fas fa-user-plus"></i> Add User
                    </a>
                </div>
            </div>

            <table id="userTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>sponsor_id</th>
                        <th>referral_id</th>
                        <th>User</th>
                        <th>Number</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        $is_new = isset($row['created_at']) && date('Y-m-d', strtotime($row['created_at'])) == date('Y-m-d');
                    ?>
                    <tr>
                        <td>#<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></td>
                          <td><?php echo str_pad($row['referral_id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo str_pad($row['sponsor_id'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td>
                            <div class="user-cell">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($row['name']); ?>&background=2563eb&color=fff&size=38&bold=true"
                                    alt="avatar" class="avatar">
                                <div>
                                    <div class="user-name"><?php echo htmlspecialchars($row['name']); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($row['Email'] ?? 'N/A'); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($row['mobile'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['Email'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="password-text"><?php echo htmlspecialchars($row['password'] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <span class="badge <?php echo ($row['role'] ?? 'user') == 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                <?php echo ucfirst($row['role'] ?? 'User'); ?>
                            </span>
                        </td>
                        <td>
                            <?php if($is_new): ?>
                                <span class="badge badge-new">
                                    <i class="fas fa-star"></i> New
                                </span>
                            <?php else: ?>
                                <span class="badge badge-user">
                                    <i class="fas fa-check"></i> Active
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-btns">
                             
                                <a href="user_details.php?id=<?php echo $row['id']; ?>" class="view-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="delete-btn"
                                    onclick="return confirm('⚠️ Are you sure you want to delete this user? This action cannot be undone!')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data">
                                <i class="fas fa-users-slash"></i>
                                <p>No users found in the database</p>
                                <p style="font-size:13px;color:var(--gray-400);">Click "Add User" to create your first
                                    user</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
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
        var table = document.getElementById('userTable');
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