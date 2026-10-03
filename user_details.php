<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Correct path to db.php - since this file is in the root directory
include("db.php");

// Check database connection
if(!$conn) {
    die("Database connection failed!");
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($user_id <= 0) {
    header("Location: admin_dashboard.php?error=Invalid User ID");
    exit();
}

$sql = "SELECT * FROM contact WHERE id = $user_id";
$result = mysqli_query($conn, $sql);

if(!$result || mysqli_num_rows($result) == 0) {
    header("Location: admin_dashboard.php?error=User not found");
    exit();
}

$user = mysqli_fetch_assoc($result);

// Get additional stats
$total_visits = 0;
$sql_visits = "SELECT COUNT(*) as visits FROM login_logs WHERE user_id = $user_id";
$visits_result = mysqli_query($conn, $sql_visits);
if($visits_result && mysqli_num_rows($visits_result) > 0) {
    $row = mysqli_fetch_assoc($visits_result);
    $total_visits = $row['visits'] ?? 0;
}

// Get last login
$last_login = 'Never';
$sql_last = "SELECT MAX(login_time) as last_login FROM login_logs WHERE user_id = $user_id";
$last_result = mysqli_query($conn, $sql_last);
if($last_result && mysqli_num_rows($last_result) > 0) {
    $row = mysqli_fetch_assoc($last_result);
    if($row['last_login']) {
        $last_login = date('d M Y, h:i A', strtotime($row['last_login']));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile | <?php echo htmlspecialchars($user['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        /* ============================================
           COLORFUL CSS VARIABLES
        ============================================ */
        :root {
            /* Primary Colors */
            --primary: #6C63FF;
            --primary-dark: #5A52D5;
            --primary-light: #E8E6FF;
            --primary-gradient: linear-gradient(135deg, #6C63FF 0%, #5A52D5 100%);
            
            /* Secondary Colors */
            --secondary: #FF6584;
            --secondary-light: #FFE6EC;
            --secondary-gradient: linear-gradient(135deg, #FF6584 0%, #FF3D6B 100%);
            
            /* Accent Colors */
            --accent-1: #00D2FF;
            --accent-1-light: #E6F9FF;
            --accent-2: #FFB800;
            --accent-2-light: #FFF8E6;
            --accent-3: #00E676;
            --accent-3-light: #E6F9EE;
            --accent-4: #FF6B6B;
            --accent-4-light: #FFE6E6;
            --accent-5: #A855F7;
            --accent-5-light: #F3E8FF;
            
            /* Status Colors */
            --success: #00E676;
            --success-light: #E6F9EE;
            --warning: #FFB800;
            --warning-light: #FFF8E6;
            --danger: #FF6B6B;
            --danger-light: #FFE6E6;
            --info: #00D2FF;
            --info-light: #E6F9FF;
            
            /* Neutral Colors */
            --white: #FFFFFF;
            --gray-50: #FAFAFA;
            --gray-100: #F5F5F5;
            --gray-200: #EEEEEE;
            --gray-300: #E0E0E0;
            --gray-400: #BDBDBD;
            --gray-500: #9E9E9E;
            --gray-600: #757575;
            --gray-700: #616161;
            --gray-800: #424242;
            --gray-900: #212121;
            
            /* Shadows */
            --shadow-sm: 0 2px 8px rgba(108, 99, 255, 0.08);
            --shadow-md: 0 4px 20px rgba(108, 99, 255, 0.12);
            --shadow-lg: 0 10px 40px rgba(108, 99, 255, 0.15);
            --shadow-xl: 0 20px 60px rgba(108, 99, 255, 0.2);
            --shadow-2xl: 0 30px 80px rgba(108, 99, 255, 0.25);
            --shadow-colored: 0 8px 30px rgba(108, 99, 255, 0.3);
            
            /* Radius */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-2xl: 24px;
            --radius-full: 9999px;
            
            /* Transitions */
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        /* ============================================
           RESET & BASE
        ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #F8F9FF 0%, #F0F1FF 50%, #F8F9FF 100%);
            color: var(--gray-800);
            min-height: 100vh;
            padding: 24px;
            line-height: 1.6;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 70% 30%, rgba(108, 99, 255, 0.05) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            bottom: -50%;
            left: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 70%, rgba(255, 101, 132, 0.05) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        /* ============================================
           BACK BUTTON
        ============================================ */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 28px;
            background: var(--white);
            border: 2px solid var(--primary-light);
            border-radius: var(--radius-full);
            color: var(--primary);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            box-shadow: var(--shadow-sm);
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }

        .back-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--primary-gradient);
            opacity: 0;
            transition: var(--transition);
            border-radius: var(--radius-full);
        }

        .back-btn:hover {
            transform: translateX(-6px) scale(1.02);
            box-shadow: var(--shadow-md);
            color: var(--white);
            border-color: var(--primary);
        }

        .back-btn:hover::before {
            opacity: 1;
        }

        .back-btn i,
        .back-btn span {
            position: relative;
            z-index: 1;
        }

        .back-btn i {
            font-size: 16px;
        }

        /* ============================================
           PROFILE CARD
        ============================================ */
        .profile-card {
            background: var(--white);
            border-radius: var(--radius-2xl);
            padding: 40px;
            box-shadow: var(--shadow-md);
            display: flex;
            gap: 40px;
            align-items: center;
            flex-wrap: wrap;
            border: 1px solid rgba(108, 99, 255, 0.08);
            backdrop-filter: blur(10px);
            transition: var(--transition-slow);
            position: relative;
            overflow: hidden;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: var(--primary-gradient);
        }

        .profile-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(108, 99, 255, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .profile-card:hover {
            box-shadow: var(--shadow-xl);
            transform: translateY(-4px);
            border-color: rgba(108, 99, 255, 0.15);
        }

        /* Avatar */
        .profile-avatar {
            position: relative;
            flex-shrink: 0;
        }

        .profile-avatar .avatar-wrapper {
            position: relative;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            padding: 4px;
            background: var(--primary-gradient);
            box-shadow: var(--shadow-colored);
            transition: var(--transition-slow);
            animation: avatarGlow 3s ease-in-out infinite;
        }

        @keyframes avatarGlow {
            0%, 100% { box-shadow: 0 8px 40px rgba(108, 99, 255, 0.25); }
            50% { box-shadow: 0 8px 60px rgba(108, 99, 255, 0.4); }
        }

        .profile-avatar .avatar-wrapper:hover {
            transform: scale(1.08) rotate(-5deg);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--white);
            background: var(--gray-100);
        }

        .profile-avatar .status-dot {
            position: absolute;
            bottom: 6px;
            right: 6px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--success);
            border: 3px solid var(--white);
            box-shadow: 0 2px 15px rgba(0, 230, 118, 0.4);
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { transform: scale(1); box-shadow: 0 2px 15px rgba(0, 230, 118, 0.4); }
            50% { transform: scale(1.15); box-shadow: 0 2px 25px rgba(0, 230, 118, 0.6); }
        }

        /* Profile Info */
        .profile-info {
            flex: 1;
            min-width: 200px;
            position: relative;
            z-index: 1;
        }

        .profile-info .name-section {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .profile-info h1 {
            font-size: 32px;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .profile-info .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 20px;
            border-radius: var(--radius-full);
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            animation: badgePulse 2s ease-in-out infinite;
        }

        @keyframes badgePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .profile-info .role-badge.admin {
            background: var(--primary-gradient);
            color: var(--white);
            box-shadow: 0 4px 20px rgba(108, 99, 255, 0.3);
        }

        .profile-info .role-badge.admin i {
            color: var(--white);
        }

        .profile-info .role-badge.user {
            background: var(--secondary-gradient);
            color: var(--white);
            box-shadow: 0 4px 20px rgba(255, 101, 132, 0.3);
        }

        .profile-info .role-badge.user i {
            color: var(--white);
        }

        .profile-info .user-meta {
            display: flex;
            gap: 12px;
            margin-top: 14px;
            flex-wrap: wrap;
        }

        .profile-info .user-meta .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--gray-600);
            background: var(--gray-50);
            padding: 6px 16px;
            border-radius: var(--radius-full);
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .profile-info .user-meta .meta-item:hover {
            background: var(--primary-light);
            color: var(--primary);
            border-color: var(--primary-light);
            transform: translateY(-2px) scale(1.02);
            box-shadow: var(--shadow-sm);
        }

        .profile-info .user-meta .meta-item i {
            color: var(--primary);
            font-size: 14px;
        }

        /* ============================================
           STATS GRID - COLORFUL
        ============================================ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin: 28px 0;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 22px 24px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition-bounce);
            border: 1px solid rgba(108, 99, 255, 0.06);
            position: relative;
            overflow: hidden;
            cursor: default;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            transition: var(--transition);
        }

        .stat-card:nth-child(1)::before { background: var(--primary-gradient); }
        .stat-card:nth-child(2)::before { background: var(--secondary-gradient); }
        .stat-card:nth-child(3)::before { background: linear-gradient(135deg, #00D2FF 0%, #00E676 100%); }
        .stat-card:nth-child(4)::before { background: linear-gradient(135deg, #FFB800 0%, #FF6B6B 100%); }

        .stat-card::after {
            content: '';
            position: absolute;
            bottom: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            opacity: 0.05;
            transition: var(--transition);
            pointer-events: none;
        }

        .stat-card:nth-child(1)::after { background: var(--primary); }
        .stat-card:nth-child(2)::after { background: var(--secondary); }
        .stat-card:nth-child(3)::after { background: var(--accent-1); }
        .stat-card:nth-child(4)::after { background: var(--accent-2); }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-lg);
            border-color: rgba(108, 99, 255, 0.1);
        }

        .stat-card:hover::after {
            transform: scale(1.5);
            opacity: 0.1;
        }

        .stat-card .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .stat-card .stat-number {
            font-size: 30px;
            font-weight: 800;
            color: var(--gray-900);
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .stat-card:nth-child(1) .stat-number { color: var(--primary); }
        .stat-card:nth-child(2) .stat-number { color: var(--secondary); }
        .stat-card:nth-child(3) .stat-number { color: var(--accent-1); }
        .stat-card:nth-child(4) .stat-number { color: var(--accent-2); }

        .stat-card .stat-label {
            font-size: 13px;
            color: var(--gray-500);
            font-weight: 500;
            margin-top: 2px;
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: var(--transition-bounce);
        }

        .stat-card:hover .stat-icon {
            transform: rotate(-10deg) scale(1.15);
        }

        .stat-card:nth-child(1) .stat-icon { background: var(--primary-light); color: var(--primary); }
        .stat-card:nth-child(2) .stat-icon { background: var(--secondary-light); color: var(--secondary); }
        .stat-card:nth-child(3) .stat-icon { background: var(--info-light); color: var(--info); }
        .stat-card:nth-child(4) .stat-icon { background: var(--warning-light); color: var(--warning); }

        .stat-card .stat-change {
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 14px;
            border-radius: var(--radius-full);
        }

        .stat-card .stat-change.positive {
            color: var(--success);
            background: var(--success-light);
        }

        .stat-card .stat-change.neutral {
            color: var(--gray-500);
            background: var(--gray-100);
        }

        .stat-card:nth-child(1) .stat-change { color: var(--primary); background: var(--primary-light); }
        .stat-card:nth-child(2) .stat-change { color: var(--secondary); background: var(--secondary-light); }
        .stat-card:nth-child(3) .stat-change { color: var(--info); background: var(--info-light); }
        .stat-card:nth-child(4) .stat-change { color: var(--warning); background: var(--warning-light); }

        /* ============================================
           DETAILS GRID
        ============================================ */
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 28px;
        }

        .detail-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 28px 32px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(108, 99, 255, 0.06);
            transition: var(--transition-slow);
            position: relative;
            overflow: hidden;
        }

        .detail-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .detail-card:nth-child(1)::before { background: var(--primary-gradient); }
        .detail-card:nth-child(2)::before { background: var(--secondary-gradient); }

        .detail-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-4px);
            border-color: rgba(108, 99, 255, 0.12);
        }

        .detail-card .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 2px solid var(--gray-100);
        }

        .detail-card .card-header .header-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .detail-card:nth-child(1) .card-header .header-icon { 
            background: var(--primary-light); 
            color: var(--primary); 
        }

        .detail-card:nth-child(2) .card-header .header-icon { 
            background: var(--secondary-light); 
            color: var(--secondary); 
        }

        .detail-card .card-header h3 {
            font-size: 17px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .detail-card .card-header h3 small {
            font-weight: 400;
            color: var(--gray-500);
            font-size: 13px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-100);
            transition: var(--transition);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-item:hover {
            padding-left: 8px;
            background: var(--gray-50);
            margin: 0 -12px;
            padding-left: 20px;
            padding-right: 12px;
            border-radius: var(--radius-sm);
        }

        .detail-item .label {
            font-size: 14px;
            color: var(--gray-500);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-item .label i {
            color: var(--primary);
            font-size: 14px;
            width: 18px;
        }

        .detail-item:nth-child(2) .label i { color: var(--secondary); }
        .detail-item:nth-child(3) .label i { color: var(--info); }
        .detail-item:nth-child(4) .label i { color: var(--warning); }
        .detail-item:nth-child(5) .label i { color: var(--accent-5); }

        .detail-item .value {
            font-size: 14px;
            color: var(--gray-800);
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }

        .detail-item .value .password-text {
            font-family: 'Courier New', monospace;
            font-weight: 400;
            background: var(--gray-50);
            padding: 4px 14px;
            border-radius: var(--radius-sm);
            border: 2px dashed var(--gray-300);
            font-size: 13px;
            transition: var(--transition);
            color: var(--primary);
        }

        .detail-item .value .password-text:hover {
            border-color: var(--primary);
            background: var(--primary-light);
            color: var(--primary-dark);
            transform: scale(1.02);
        }

        .detail-item .value .status-active {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--success);
            font-weight: 600;
            padding: 4px 16px;
            background: var(--success-light);
            border-radius: var(--radius-full);
        }

        .detail-item .value .status-active i {
            font-size: 10px;
            animation: pulse-dot 2s ease-in-out infinite;
        }

        /* Role badge in details */
        .detail-item .value .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 16px;
            border-radius: var(--radius-full);
            font-size: 12px;
            font-weight: 600;
        }

        .detail-item .value .role-badge.admin {
            background: var(--primary-gradient);
            color: var(--white);
        }

        .detail-item .value .role-badge.user {
            background: var(--secondary-gradient);
            color: var(--white);
        }

        /* ============================================
           ACTION BUTTONS
        ============================================ */
        .action-buttons {
            display: flex;
            gap: 14px;
            margin-top: 32px;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 32px;
            border-radius: var(--radius-full);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition-bounce);
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .action-buttons .btn i {
            font-size: 16px;
        }

        .action-buttons .btn-primary {
            background: var(--primary-gradient);
            color: var(--white);
            box-shadow: 0 4px 20px rgba(108, 99, 255, 0.3);
        }

        .action-buttons .btn-primary:hover {
            transform: translateY(-4px) scale(1.04);
            box-shadow: 0 8px 35px rgba(108, 99, 255, 0.4);
        }

        .action-buttons .btn-danger {
            background: var(--secondary-gradient);
            color: var(--white);
            box-shadow: 0 4px 20px rgba(255, 101, 132, 0.3);
        }

        .action-buttons .btn-danger:hover {
            transform: translateY(-4px) scale(1.04);
            box-shadow: 0 8px 35px rgba(255, 101, 132, 0.4);
        }

        .action-buttons .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }

        .action-buttons .btn-secondary:hover {
            transform: translateY(-4px) scale(1.04);
            box-shadow: var(--shadow-md);
            background: var(--gray-300);
        }

        .action-buttons .btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.2);
            transform: translateX(-100%) rotate(45deg);
            transition: var(--transition);
        }

        .action-buttons .btn:hover::after {
            transform: translateX(100%) rotate(45deg);
        }

        /* ============================================
           RESPONSIVE
        ============================================ */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 16px;
            }

            .profile-card {
                flex-direction: column;
                text-align: center;
                padding: 32px 24px;
            }

            .profile-info .name-section {
                justify-content: center;
            }

            .profile-info .user-meta {
                justify-content: center;
            }

            .profile-info .user-meta .meta-item {
                font-size: 12px;
                padding: 4px 14px;
            }

            .profile-avatar .avatar-wrapper {
                width: 110px;
                height: 110px;
            }

            .profile-info h1 {
                font-size: 26px;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 14px;
            }

            .stat-card {
                padding: 16px 18px;
            }

            .stat-card .stat-number {
                font-size: 24px;
            }

            .details-grid {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .detail-card {
                padding: 20px 22px;
            }

            .action-buttons {
                justify-content: center;
            }

            .action-buttons .btn {
                padding: 10px 24px;
                font-size: 13px;
                flex: 1;
                min-width: 120px;
                justify-content: center;
            }

            .back-btn {
                padding: 10px 20px;
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .profile-card {
                padding: 24px 16px;
            }

            .profile-info h1 {
                font-size: 22px;
            }

            .profile-avatar .avatar-wrapper {
                width: 90px;
                height: 90px;
            }

            .detail-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
                padding: 10px 0;
            }

            .detail-item .value {
                text-align: left;
                width: 100%;
            }

            .action-buttons .btn {
                flex: 1 1 100%;
            }
        }

        /* ============================================
           ANIMATIONS
        ============================================ */
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

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .profile-card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .stat-card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }

        .detail-card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .detail-card:nth-child(1) { animation-delay: 0.2s; }
        .detail-card:nth-child(2) { animation-delay: 0.3s; }

        .action-buttons .btn {
            animation: fadeInUp 0.6s ease forwards;
        }

        .action-buttons .btn:nth-child(1) { animation-delay: 0.4s; }
        .action-buttons .btn:nth-child(2) { animation-delay: 0.5s; }
        .action-buttons .btn:nth-child(3) { animation-delay: 0.6s; }

        /* ============================================
           SCROLLBAR
        ============================================ */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 4px;
            transition: var(--transition);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back Button -->
        <a href="admin.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>

        <!-- Profile Card -->
        <div class="profile-card">
            <div class="profile-avatar">
                <div class="avatar-wrapper">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&background=6C63FF&color=fff&size=140&bold=true&font-size=0.5" 
                         alt="<?php echo htmlspecialchars($user['name']); ?>">
                    <div class="status-dot"></div>
                </div>
            </div>
            <div class="profile-info">
                <div class="name-section">
                    <h1><?php echo htmlspecialchars($user['name']); ?></h1>
                    <span class="role-badge <?php echo ($user['role'] ?? 'user') == 'admin' ? 'admin' : 'user'; ?>">
                        <i class="fas <?php echo ($user['role'] ?? 'user') == 'admin' ? 'fa-shield-alt' : 'fa-user'; ?>"></i>
                        <?php echo ucfirst($user['role'] ?? 'User'); ?>
                    </span>
                </div>
                <div class="user-meta">
                    <span class="meta-item">
                        <i class="fas fa-envelope"></i>
                        <?php echo htmlspecialchars($user['Email']); ?>
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-phone"></i>
                        <?php echo htmlspecialchars($user['mobile'] ?? 'N/A'); ?>
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        Joined: <?php echo date('d M Y', strtotime($user['created_at'] ?? 'now')); ?>
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-id-badge"></i>
                        ID: #<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-number"><?php echo number_format($total_visits); ?></div>
                        <div class="stat-label">Total Visits</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-eye"></i></div>
                </div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up"></i> Active user
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-number" style="font-size: 18px;"><?php echo $last_login; ?></div>
                        <div class="stat-label">Last Login</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                </div>
                <div class="stat-change">
                    <i class="fas fa-minus"></i> Current session
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-number"><?php echo ucfirst($user['role'] ?? 'User'); ?></div>
                        <div class="stat-label">User Role</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-user-tag"></i></div>
                </div>
                <div class="stat-change">
                    <i class="fas fa-circle"></i> <?php echo ($user['role'] ?? 'user') == 'admin' ? 'Administrator' : 'Regular User'; ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <div class="stat-number" style="font-size: 18px;"><?php echo date('d M Y'); ?></div>
                        <div class="stat-label">Account Status</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
                </div>
                <div class="stat-change">
                    <i class="fas fa-check-circle"></i> Active
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="details-grid">
            <!-- Personal Information -->
            <div class="detail-card">
                <div class="card-header">
                    <div class="header-icon"><i class="fas fa-user-circle"></i></div>
                    <h3>Personal Information <small>Details</small></h3>
                </div>
                <div class="detail-item">
                    <span class="label"><i class="fas fa-user"></i> Full Name</span>
                    <span class="value"><?php echo htmlspecialchars($user['name']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label"><i class="fas fa-envelope"></i> Email Address</span>
                    <span class="value"><?php echo htmlspecialchars($user['Email']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label"><i class="fas fa-phone"></i> Mobile Number</span>
                    <span class="value"><?php echo htmlspecialchars($user['mobile'] ?? 'N/A'); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label"><i class="fas fa-lock"></i> Password</span>
                    <span class="value"><span class="password-text"><?php echo htmlspecialchars($user['password'] ?? 'N/A'); ?></span></span>
                </div>
                <div class="detail-item">
                    <span class="label"><i class="fas fa-id-card"></i> User ID</span>
                    <span class="value">#<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?></span>
                </div>
                  <div class="detail-item">
                    <span class="label"><i class="fas fa-id-card"></i> Sponsor_ID</span>
                    <span class="value"><?php echo str_pad($user['referral_id'], 4, '0', STR_PAD_LEFT); ?></span>
                </div>
            </div>

            <!-- Account Information -->
            <div class="detail-card">
                <div class="card-header">
                    <div class="header-icon"><i class="fas fa-cog"></i></div>
                    <h3>Account Information <small>Settings</small></h3>
                </div>
                <div class="detail-item">
                    <span class="label"><i class="fas fa-user-tag"></i> Role</span>
                    <span class="value">
                        <span class="role-badge <?php echo ($user['role'] ?? 'user') == 'admin' ? 'admin' : 'user'; ?>">
                            <i class="fas <?php echo ($user['role'] ?? 'user') == 'admin' ? 'fa-shield-alt' : 'fa-user'; ?>"></i>
                            <?php echo ucfirst($user['role'] ?? 'User'); ?>
                        </span>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="label"><i class="fas fa-calendar-plus"></i> Created At</span>
                    <span class="value"><?php echo date('d M Y, h:i A', strtotime($user['created_at'] ?? 'now')); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label"><i class="fas fa-clock"></i> Last Login</span>
                    <span class="value"><?php echo $last_login; ?></span>
                </div>
                <div class="detail-item">
                    <span class="label"><i class="fas fa-eye"></i> Total Visits</span>
                    <span class="value"><?php echo number_format($total_visits); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label"><i class="fas fa-circle"></i> Status</span>
                    <span class="value">
                        <span class="status-active">
                            <i class="fas fa-circle"></i> Active
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit User
            </a>
            <a href="delete.php?id=<?php echo $user['id']; ?>" class="btn btn-danger" 
               onclick="return confirm('⚠️ Are you sure you want to delete this user? This action cannot be undone!')">
                <i class="fas fa-trash-alt"></i> Delete User
            </a>
            <a href="admin.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</body>
</html>