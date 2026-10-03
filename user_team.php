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
// TEAM FUNCTIONS
// ========================================

// Get team members count
function getTeamCount($conn, $user_id) {
    $sql = "SELECT COUNT(*) as count FROM team WHERE referrer_id = '$user_id' AND status = 'active'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    return 0;
}

// Get pending team requests
function getPendingTeamCount($conn, $user_id) {
    $sql = "SELECT COUNT(*) as count FROM team WHERE referrer_id = '$user_id' AND status = 'pending'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    return 0;
}

// Get total team earnings
function getTeamEarnings($conn, $user_id) {
    $sql = "SELECT SUM(earnings) as total FROM team WHERE referrer_id = '$user_id' AND status = 'active'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

// Get team members with details
function getTeamMembers($conn, $user_id, $limit = 20) {
    $sql = "SELECT t.*, u.name, u.email, u.mobile, u.created_at as joined_date 
            FROM team t 
            JOIN contact u ON t.member_id = u.id 
            WHERE t.referrer_id = '$user_id' 
            ORDER BY t.created_at DESC 
            LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    return $result;
}

// Get team levels
function getTeamLevels($conn, $user_id) {
    $levels = [
        'level1' => 0,
        'level2' => 0,
        'level3' => 0
    ];
    
    // Level 1 - Direct referrals
    $sql = "SELECT COUNT(*) as count FROM team WHERE referrer_id = '$user_id' AND status = 'active' AND level = 1";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $levels['level1'] = $row['count'];
    }
    
    // Level 2 - Indirect referrals
    $sql = "SELECT COUNT(*) as count FROM team WHERE referrer_id = '$user_id' AND status = 'active' AND level = 2";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $levels['level2'] = $row['count'];
    }
    
    // Level 3 - Third level
    $sql = "SELECT COUNT(*) as count FROM team WHERE referrer_id = '$user_id' AND status = 'active' AND level = 3";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $levels['level3'] = $row['count'];
    }
    
    return $levels;
}

// Get team investment
function getTeamInvestment($conn, $user_id) {
    $sql = "SELECT SUM(investment) as total FROM team WHERE referrer_id = '$user_id' AND status = 'active'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

// Get top team members
function getTopTeamMembers($conn, $user_id, $limit = 5) {
    $sql = "SELECT t.*, u.name, u.email, u.mobile, 
            (SELECT SUM(amount) FROM investments WHERE user_id = t.member_id) as total_investment
            FROM team t 
            JOIN contact u ON t.member_id = u.id 
            WHERE t.referrer_id = '$user_id' AND t.status = 'active'
            ORDER BY t.earnings DESC 
            LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    return $result;
}
$user_id = intval($_SESSION['user_id']);

$query = mysqli_query($conn, "
    SELECT referral_id
    FROM contact
    WHERE id = '$user_id'
    LIMIT 1
");

$data = mysqli_fetch_assoc($query);
$referral_id = $data['referral_id'];
// Get referral code
function getReferralCode($conn, $user_id) {
    $sql = "SELECT referral_id FROM contact WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['referral_id'] ? $row['referral_id'] : 'REF' . str_pad($user_id, 6, '0', STR_PAD_LEFT);
    }
    return 'REF' . str_pad($user_id, 6, '0', STR_PAD_LEFT);
}

// Generate referral link
function getReferralLink($user_id) {
    $base_url = "" . $_SERVER['HTTP_HOST'] . "";
    return $base_url . "?ref=" . getReferralCode($conn, $user_id);
}

// ========================================
// GET DATA
// ========================================

$team_count = getTeamCount($conn, $user_id);
$pending_count = getPendingTeamCount($conn, $user_id);
$team_earnings = getTeamEarnings($conn, $user_id);
$team_investment = getTeamInvestment($conn, $user_id);
$team_members = getTeamMembers($conn, $user_id, 20);
$top_members = getTopTeamMembers($conn, $user_id, 5);
$levels = getTeamLevels($conn, $user_id);
$referral_id = getReferralCode($conn, $user_id);
$referral_link = "https://" . $_SERVER['HTTP_HOST'] . "/register.php?ref=" . urlencode($referral_id);

// Calculate conversion rate
$conversion_rate = 0;
if($team_count > 0) {
    $conversion_rate = round(($team_count / ($team_count + $pending_count)) * 100);
}
https://localhost/mywebsites/Alfra/login.php?ref=REF000086
// Level colors
$level_colors = [
    'level1' => '#2563eb',
    'level2' => '#8b5cf6',
    'level3' => '#ec4899'
];

$level_icons = [
    'level1' => 'fa-crown',
    'level2' => 'fa-star',
    'level3' => 'fa-gem'
];

$level_labels = [
    'level1' => 'Level 1 (Direct)',
    'level2' => 'Level 2 (Indirect)',
    'level3' => 'Level 3 (Team)'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Team | Cowork</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

        /* ========================================
                   RESET
                ======================================== */
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

        .team-container {
            max-width: 1200px;
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
                   HEADER
                ======================================== */
        .team-header {
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 50%, #0f766e 100%);
            border-radius: var(--radius-2xl);
            padding: 40px 48px;
            color: #fff;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 50px rgba(37, 99, 235, 0.35);
        }

        .team-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -5%;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            pointer-events: none;
        }

        .team-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: 10%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.03);
            border-radius: 50%;
            pointer-events: none;
        }

        .team-header .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            position: relative;
            z-index: 1;
        }

        .team-header h1 {
            font-size: 28px;
            font-weight: 800;
        }

        .team-header h1 i {
            margin-right: 12px;
        }

        .team-header .header-stats {
            display: flex;
            gap: 30px;
            background: rgba(255,255,255,0.1);
            padding: 16px 28px;
            border-radius: var(--radius-md);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .team-header .header-stats .stat-item {
            text-align: center;
        }

        .team-header .header-stats .stat-item .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }

        .team-header .header-stats .stat-item .value {
            font-size: 24px;
            font-weight: 700;
        }

        /* ========================================
                   REFERRAL SECTION
                ======================================== */
        .referral-box {
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 24px 28px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0,0,0,0.04);
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .referral-box .referral-info {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .referral-box .referral-code {
            background: var(--gray-50);
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-family: monospace;
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: 2px;
            border: 2px dashed var(--primary-light);
        }

        .referral-box .referral-link {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--gray-50);
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            max-width: 400px;
            flex: 1;
        }

        .referral-box .referral-link input {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 13px;
            color: var(--gray-600);
            padding: 6px 0;
            outline: none;
        }

        .referral-box .referral-link .copy-btn {
            background: var(--primary-gradient);
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
        }

        .referral-box .referral-link .copy-btn:hover {
            transform: scale(1.05);
        }

        .referral-box .share-btns {
            display: flex;
            gap: 10px;
        }

        .referral-box .share-btns .share-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
        }

        .referral-box .share-btns .share-btn:hover {
            transform: scale(1.1);
        }

        .referral-box .share-btns .share-btn.whatsapp {
            background: #25d366;
        }

        .referral-box .share-btns .share-btn.facebook {
            background: #1877f2;
        }

        .referral-box .share-btns .share-btn.twitter {
            background: #000000;
        }

        .referral-box .share-btns .share-btn.linkedin {
            background: #0a66c2;
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
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 22px 24px;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.04);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
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
        .stat-card .stat-icon.blue {
            background: var(--primary-light);
            color: var(--primary);
        }
        .stat-card .stat-icon.purple {
            background: var(--purple-light);
            color: var(--purple);
        }

        .stat-card .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .stat-card .stat-label {
            font-size: 13px;
            color: var(--gray-500);
            font-weight: 500;
        }

        .stat-card .stat-sub {
            font-size: 12px;
            color: var(--gray-400);
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid var(--gray-100);
        }

        /* ========================================
                   LEVELS SECTION
                ======================================== */
        .levels-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .level-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0,0,0,0.04);
            text-align: center;
            transition: var(--transition);
        }

        .level-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .level-card .level-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 12px;
            color: #fff;
        }

        .level-card .level-icon.level1 {
            background: var(--primary-gradient);
        }

        .level-card .level-icon.level2 {
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
        }

        .level-card .level-icon.level3 {
            background: linear-gradient(135deg, #ec4899, #be185d);
        }

        .level-card h3 {
            font-size: 16px;
            font-weight: 600;
            color: var(--gray-800);
        }

        .level-card .level-count {
            font-size: 32px;
            font-weight: 800;
            color: var(--gray-900);
            margin: 8px 0;
        }

        .level-card .level-label {
            font-size: 13px;
            color: var(--gray-500);
        }

        /* ========================================
                   CONTENT GRID
                ======================================== */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        /* ========================================
                   TEAM TABLE
                ======================================== */
        .team-box {
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 28px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0,0,0,0.04);
        }

        .team-box .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .team-box .section-header h3 {
            font-size: 17px;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .team-box .section-header h3 i {
            color: var(--primary);
        }

        .team-box .section-header a {
            color: var(--primary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
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

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: var(--success-light);
            color: #166534;
        }

        .badge-warning {
            background: var(--warning-light);
            color: #92400e;
        }

        .badge-primary {
            background: var(--primary-light);
            color: var(--primary-dark);
        }

        .badge-pink {
            background: var(--pink-light);
            color: #9d174d;
        }

        .badge-purple {
            background: var(--purple-light);
            color: #5b21b6;
        }

        .text-success {
            color: var(--success);
        }

        .text-muted {
            color: var(--gray-400);
        }

        .no-team {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray-400);
        }

        .no-team i {
            font-size: 48px;
            opacity: 0.3;
            display: block;
            margin-bottom: 12px;
        }

        /* ========================================
                   TOP PERFORMERS
                ======================================== */
        .top-box {
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 28px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0,0,0,0.04);
        }

        .top-box .section-header {
            margin-bottom: 18px;
        }

        .top-box .section-header h3 {
            font-size: 17px;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .top-box .section-header h3 i {
            color: var(--warning);
        }

        .top-member {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .top-member:last-child {
            border-bottom: none;
        }

        .top-member .rank {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .top-member .rank.gold {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .top-member .rank.silver {
            background: linear-gradient(135deg, #94a3b8, #64748b);
        }

        .top-member .rank.bronze {
            background: linear-gradient(135deg, #d97706, #92400e);
        }

        .top-member .rank.normal {
            background: var(--primary-light);
            color: var(--primary-dark);
        }

        .top-member .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .top-member .member-info {
            flex: 1;
        }

        .top-member .member-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-800);
        }

        .top-member .member-info p {
            font-size: 12px;
            color: var(--gray-500);
        }

        .top-member .member-earnings {
            font-size: 15px;
            font-weight: 700;
            color: var(--success);
        }

        .no-performers {
            text-align: center;
            padding: 20px;
            color: var(--gray-400);
            font-size: 14px;
        }

        /* ========================================
                   RESPONSIVE
                ======================================== */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .levels-section {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 992px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            .team-header .header-top {
                flex-direction: column;
                text-align: center;
            }
            .team-header .header-stats {
                width: 100%;
                justify-content: center;
            }
            .referral-box {
                flex-direction: column;
                align-items: stretch;
            }
            .referral-box .referral-info {
                justify-content: center;
            }
            .referral-box .share-btns {
                justify-content: center;
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

            .team-header {
                padding: 24px 20px;
                border-radius: var(--radius-lg);
            }

            .team-header h1 {
                font-size: 22px;
            }

            .team-header .header-stats .stat-item .value {
                font-size: 20px;
            }

            .team-header .header-stats {
                flex-wrap: wrap;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .stat-card {
                padding: 16px;
            }

            .stat-card .stat-number {
                font-size: 22px;
            }

            .levels-section {
                grid-template-columns: 1fr;
            }

            .referral-box .referral-link {
                max-width: 100%;
                flex-wrap: wrap;
            }

            .referral-box .referral-code {
                font-size: 16px;
                padding: 8px 16px;
            }

            .team-box,
            .top-box {
                padding: 18px;
            }

            .team-box {
                overflow-x: auto;
            }

            table {
                min-width: 600px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .team-header .header-stats {
                flex-direction: column;
                gap: 10px;
                padding: 12px 20px;
            }

            .navbar .nav-links a {
                font-size: 11px;
                padding: 4px 10px;
            }

            .referral-box .referral-info {
                flex-direction: column;
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
            .level-card,
            .referral-box,
            .team-box,
            .top-box {
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

            .stat-card .stat-number {
                color: #f1f5f9;
            }

            .stat-card .stat-label {
                color: #94a3b8;
            }

            .stat-card .stat-sub {
                border-top-color: #334155;
                color: #64748b;
            }

            .level-card h3 {
                color: #f1f5f9;
            }

            .level-card .level-count {
                color: #f1f5f9;
            }

            .level-card .level-label {
                color: #94a3b8;
            }

            .referral-box .referral-code {
                background: #334155;
                color: #60a5fa;
                border-color: #1e3a5f;
            }

            .referral-box .referral-link {
                background: #334155;
            }

            .referral-box .referral-link input {
                color: #e2e8f0;
            }

            .team-box .section-header h3 {
                color: #f1f5f9;
            }

            .top-box .section-header h3 {
                color: #f1f5f9;
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

            .top-member {
                border-bottom-color: #334155;
            }

            .top-member .member-info h4 {
                color: #f1f5f9;
            }

            .no-team {
                color: #94a3b8;
            }

            .no-performers {
                color: #94a3b8;
            }

            .badge-success {
                background: #064e3b;
                color: #86efac;
            }

            .badge-warning {
                background: #78350f;
                color: #fbbf24;
            }

            .badge-primary {
                background: #1e3a5f;
                color: #60a5fa;
            }

            .badge-pink {
                background: #5b1d3e;
                color: #f472b6;
            }

            .badge-purple {
                background: #3b1e6e;
                color: #a78bfa;
            }
        }

        /* ========================================
                   COPY TOAST
                ======================================== */
        .toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: var(--gray-900);
            color: #fff;
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            box-shadow: var(--shadow-lg);
            opacity: 0;
            transition: all 0.5s ease;
            z-index: 999;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    </style>
</head>
<body>

<div class="team-container">

    <!-- ===== NAVBAR ===== -->
    <nav class="navbar">
        <div class="logo">
            <div class="logo-icon"><i class="fas fa-cubes"></i></div>
            <h2>Co<span>Work</span></h2>
        </div>
        
        <div class="nav-links">
            <a href="profile_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="user_profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="user_wallet.php"><i class="fas fa-wallet"></i> Wallet</a>
            <a href="user_investment.php"><i class="fas fa-chart-line"></i> Investment</a>
            <a href="user_income.php"><i class="fas fa-money-bill-wave"></i> Income</a>
            <a href="user_team.php" class="active"><i class="fas fa-users"></i> Team</a>
            <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($row['name']); ?></span>
            <?php 
            $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($row['name']) . "&background=2563eb&color=fff&size=42&bold=true";
            ?>
            <img src="<?php echo $avatar_url; ?>" alt="Avatar" class="user-avatar">
        </div>
    </nav>

    <!-- ===== HEADER ===== -->
    <div class="team-header">
        <div class="header-top">
            <div>
                <h1><i class="fas fa-users"></i> My Team</h1>
                <p style="opacity:0.8;margin-top:4px;">Build and grow your network</p>
            </div>
            <div class="header-stats">
                <div class="stat-item">
                    <div class="label">Total Team</div>
                    <div class="value"><?php echo $team_count; ?></div>
                </div>
                <div class="stat-item">
                    <div class="label">Pending</div>
                    <div class="value"><?php echo $pending_count; ?></div>
                </div>
                <div class="stat-item">
                    <div class="label">Conversion</div>
                    <div class="value"><?php echo $conversion_rate; ?>%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== REFERRAL SECTION ===== -->
    <div class="referral-box">
        <div class="referral-info">
            <div>
                <div style="font-size:12px;color:var(--gray-500);font-weight:500;margin-bottom:4px;">
                    <i class="fas fa-code"></i> Your Referral Code
                </div>
                <span class="referral-code">
    <?php echo htmlspecialchars($referral_id); ?>
</span>
            </div>
            <div class="referral-link">
                <i class="fas fa-link" style="color:var(--gray-400);"></i>
                <input type="text" id="referralLink" value="<?php echo $referral_link; ?>" readonly>
                <button class="copy-btn" onclick="copyLink()">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
        </div>
        <div class="share-btns">
            <button class="share-btn whatsapp" onclick="shareWhatsApp()">
                <i class="fab fa-whatsapp"></i>
            </button>
            <button class="share-btn facebook" onclick="shareFacebook()">
                <i class="fab fa-facebook-f"></i>
            </button>
            <button class="share-btn twitter" onclick="shareTwitter()">
                <i class="fab fa-twitter"></i>
            </button>
            <button class="share-btn linkedin" onclick="shareLinkedIn()">
                <i class="fab fa-linkedin-in"></i>
            </button>
        </div>
    </div>

    <!-- ===== STATS CARDS ===== -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-users"></i></div>
            <div class="stat-number"><?php echo $team_count; ?></div>
            <div class="stat-label">Team Members</div>
            <div class="stat-sub">
                <i class="fas fa-user-plus" style="color:var(--success);"></i> 
                <?php echo $team_count + 2; ?> active members
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div class="stat-number"><?php echo $pending_count; ?></div>
            <div class="stat-label">Pending Requests</div>
            <div class="stat-sub">
                <i class="fas fa-hourglass-half" style="color:var(--warning);"></i> 
                Awaiting confirmation
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-coins"></i></div>
            <div class="stat-number">₹<?php echo number_format($team_earnings, 2); ?></div>
            <div class="stat-label">Team Earnings</div>
            <div class="stat-sub">
                <i class="fas fa-arrow-up" style="color:var(--success);"></i> 
                +<?php echo $team_earnings > 0 ? '12%' : '0%'; ?> this month
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-chart-pie"></i></div>
            <div class="stat-number">₹<?php echo number_format($team_investment, 2); ?></div>
            <div class="stat-label">Team Investment</div>
            <div class="stat-sub">
                <i class="fas fa-users" style="color:var(--purple);"></i> 
                Total team portfolio
            </div>
        </div>
    </div>

    <!-- ===== LEVELS SECTION ===== -->
    <div class="levels-section">
        <?php foreach(['level1', 'level2', 'level3'] as $level): ?>
            <div class="level-card">
                <div class="level-icon <?php echo $level; ?>">
                    <i class="fas <?php echo $level_icons[$level]; ?>"></i>
                </div>
                <h3><?php echo $level_labels[$level]; ?></h3>
                <div class="level-count"><?php echo $levels[$level]; ?></div>
                <div class="level-label">Members</div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ===== CONTENT GRID ===== -->
    <div class="content-grid">

        <!-- ===== TEAM TABLE ===== -->
        <div class="team-box">
            <div class="section-header">
                <h3><i class="fas fa-list"></i> Team Members</h3>
                <a href="#">View All <i class="fas fa-arrow-right"></i></a>
            </div>

            <?php if($team_members && mysqli_num_rows($team_members) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Level</th>
                            <th>Earnings</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($member = mysqli_fetch_assoc($team_members)): 
                            $level_badge = 'badge-primary';
                            if($member['level'] == 1) $level_badge = 'badge-success';
                            elseif($member['level'] == 2) $level_badge = 'badge-purple';
                            elseif($member['level'] == 3) $level_badge = 'badge-pink';
                        ?>
                            <tr>
                                <td>#<?php echo str_pad($member['member_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                                <td><span class="badge <?php echo $level_badge; ?>">Level <?php echo $member['level']; ?></span></td>
                                <td class="text-success">+₹<?php echo number_format($member['earnings'], 2); ?></td>
                                <td>
                                    <?php if($member['status'] == 'active'): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?php echo date('d M Y', strtotime($member['joined_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-team">
                    <i class="fas fa-users-slash"></i>
                    <p>No team members yet</p>
                    <p style="font-size:13px;opacity:0.6;">Share your referral link to start building your team</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- ===== TOP PERFORMERS ===== -->
        <div class="top-box">
            <div class="section-header">
                <h3><i class="fas fa-trophy"></i> Top Performers</h3>
            </div>

            <?php if($top_members && mysqli_num_rows($top_members) > 0): 
                $rank_classes = ['gold', 'silver', 'bronze'];
                $rank_icons = ['🥇', '🥈', '🥉'];
                $rank = 0;
            ?>
                <?php while($top = mysqli_fetch_assoc($top_members)): 
                    $rank_class = isset($rank_classes[$rank]) ? $rank_classes[$rank] : 'normal';
                    $rank_icon = isset($rank_icons[$rank]) ? $rank_icons[$rank] : $rank + 1;
                ?>
                    <div class="top-member">
                        <div class="rank <?php echo $rank_class; ?>">
                            <?php echo $rank_icon; ?>
                        </div>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($top['name']); ?>&background=2563eb&color=fff&size=40&bold=true" alt="Avatar" class="member-avatar">
                        <div class="member-info">
                            <h4><?php echo htmlspecialchars($top['name']); ?></h4>
                            <p>Level <?php echo $top['level']; ?> · Investment: ₹<?php echo number_format($top['total_investment'] ?? 0, 2); ?></p>
                        </div>
                        <div class="member-earnings">₹<?php echo number_format($top['earnings'], 2); ?></div>
                    </div>
                <?php 
                    $rank++;
                endwhile; ?>
            <?php else: ?>
                <div class="no-performers">
                    <i class="fas fa-info-circle" style="font-size:24px;display:block;margin-bottom:8px;opacity:0.3;"></i>
                    No team members yet
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<!-- ===== TOAST ===== -->
<div class="toast" id="toast">Link copied to clipboard! ✅</div>

<script>
    // ========================================
    // COPY LINK
    // ========================================
    function copyLink() {
        const input = document.getElementById('referralLink');
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value);
        
        const toast = document.getElementById('toast');
        toast.textContent = '✅ Link copied to clipboard!';
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    // ========================================
    // SHARE FUNCTIONS
    // ========================================
    const shareText = "Join me on Cowork! 🚀 Use my referral code: <?php echo $referral_id; ?>";
    const shareUrl = "<?php echo $referral_link; ?>";

    function shareWhatsApp() {
        window.open(`https://wa.me/?text=${encodeURIComponent(shareText + ' ' + shareUrl)}`, '_blank');
    }

    function shareFacebook() {
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}&quote=${encodeURIComponent(shareText)}`, '_blank');
    }

    function shareTwitter() {
        window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(shareUrl)}`, '_blank');
    }

    function shareLinkedIn() {
        window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareUrl)}`, '_blank');
    }
</script>

</body>
</html>