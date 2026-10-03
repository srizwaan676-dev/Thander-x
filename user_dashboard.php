<?php
session_start();
include("../db.php");
$count_sql = "SELECT COUNT(*) as total FROM contact WHERE LOWER(TRIM(status)) = 'active'";
$count_result = mysqli_query($conn, $count_sql);
$total_active = mysqli_fetch_assoc($count_result)['total'] ?? 0;
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// ========================================
// FETCH USER DATA
// ========================================
$sql = "SELECT * FROM contact WHERE id = '$user_id' AND role = 'user'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

// ========================================
// FUNCTIONS
// ========================================

function getWalletBalance($conn, $user_id) {
    $sql = "SELECT SUM(amount) as balance FROM wallet_transactions WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['balance'] ? $row['balance'] : 0;
    }
    return 0;
}

function getTotalInvestment($conn, $user_id) {
    $sql = "SELECT SUM(amount) as total FROM investments WHERE user_id = '$user_id' AND status = 'active'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

function getTotalIncome($conn, $user_id) {
    $sql = "SELECT SUM(amount) as total FROM income WHERE user_id = '$user_id' AND status = 'paid'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

function getTeamCount($conn, $user_id) {
    $sql = "SELECT COUNT(*) as count FROM team WHERE referrer_id = '$user_id' AND status = 'active'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    return 0;
}

function getRecentTransactions($conn, $user_id, $limit = 5) {
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'wallet_transactions'");
    if(mysqli_num_rows($table_check) == 0) {
        return null;
    }
    $sql = "SELECT * FROM wallet_transactions WHERE user_id = '$user_id' ORDER BY id DESC LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    return $result;
}

function getTotalProducts($conn, $user_id) {
    $sql = "SELECT COUNT(*) as count FROM products WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    return 0;
}

function getTotalTransactions($conn, $user_id) {
    $sql = "SELECT COUNT(*) as count FROM wallet_transactions WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    return 0;
}

function getUserWalletAddress($conn, $user_id) {
    $sql = "SELECT wallet_address FROM contact WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['wallet_address'] ?? '';
    }
    return '';
}

$wallet_balance = getWalletBalance($conn, $user_id);
$total_investment = getTotalInvestment($conn, $user_id);
$total_income = getTotalIncome($conn, $user_id);
$team_count = getTeamCount($conn, $user_id);
$recent_transactions = getRecentTransactions($conn, $user_id, 5);
$total_transactions = getTotalTransactions($conn, $user_id);
$total_products = getTotalProducts($conn, $user_id);
$wallet_address = getUserWalletAddress($conn, $user_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Thunder X</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
    /* =========================================================
           DESIGN TOKENS
        ========================================================= */
    :root {
        --bg-950: #060912;
        --bg-900: #0a0f1c;
        --bg-850: #0f1524;
        --bg-800: #131b2e;
        --bg-750: #1a2339;
        --bg-700: #223047;

        --glass: rgba(19, 27, 46, .68);
        --glass-hi: rgba(26, 35, 57, .8);
        --glass-line: rgba(255, 255, 255, .06);
        --glass-line-hi: rgba(255, 255, 255, .12);

        --brand-400: #34d399;
        --brand-500: #10b981;
        --brand-600: #059669;

        --accent-400: #22d3ee;
        --accent-500: #06b6d4;
        --accent-600: #0891b2;

        --success: #22c55e;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;

        --txt-hi: #f8fafc;
        --txt-mid: #94a3b8;
        --txt-lo: #64748b;
        --txt-dim: #475569;

        --r-sm: 8px;
        --r-md: 12px;
        --r-lg: 16px;
        --r-xl: 20px;
        --r-2xl: 24px;
        --r-3xl: 28px;
        --r-full: 999px;

        --sh-card: 0 20px 50px -20px rgba(0, 0, 0, .7), 0 4px 12px rgba(0, 0, 0, .4);
        --sh-glow: 0 0 40px -10px rgba(16, 185, 129, .45);

        --ease: cubic-bezier(.4, 0, .2, 1);
        --ease-bounce: cubic-bezier(.34, 1.56, .64, 1);
        --t: .25s var(--ease);
    }

    *,
    *::before,
    *::after {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html {
        -webkit-text-size-adjust: 100%;
    }

    body {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-size: 15px;
        line-height: 1.55;
        color: var(--txt-hi);
        min-height: 100vh;
        background: var(--bg-950);
        background-image:
            radial-gradient(1000px 500px at 0% 0%, rgba(16, 185, 129, .06), transparent 55%),
            radial-gradient(900px 500px at 100% 100%, rgba(6, 182, 212, .06), transparent 55%);
        background-attachment: fixed;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        padding-bottom: env(safe-area-inset-bottom);
        overflow-x: hidden;
    }

    /* =========================================================
           SIDEBAR
        ========================================================= */
    .sidebar {
        width: 260px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        padding: 22px 14px;
        background: var(--bg-900);
        border-right: 1px solid var(--glass-line);
        z-index: 1000;
        overflow-y: auto;
        overflow-x: hidden;
        transition: var(--t);
    }

    .sidebar::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: var(--bg-700);
        border-radius: var(--r-full);
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: var(--brand-500);
    }

    .sidebar .logo {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 64px;
        margin-bottom: 22px;
        border-bottom: 1px solid var(--glass-line);
    }

    .sidebar .logo h2 {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -.4px;
        background: linear-gradient(135deg, var(--brand-400), var(--accent-400));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .sidebar ul {
        list-style: none;
    }

    .sidebar ul li {
        margin: 2px 0;
    }

    .sidebar ul li a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 14px;
        border-radius: var(--r-md);
        color: var(--txt-mid);
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 600;
        letter-spacing: .1px;
        transition: var(--t);
        position: relative;
    }

    .sidebar ul li a i {
        width: 20px;
        font-size: 15px;
        color: var(--txt-lo);
        transition: var(--t);
        text-align: center;
    }

    .sidebar ul li a:hover {
        background: var(--glass-line);
        color: var(--txt-hi);
    }

    .sidebar ul li a:hover i {
        color: var(--brand-400);
    }

    .sidebar ul li.active a {
        color: var(--txt-hi);
        background: linear-gradient(135deg, rgba(16, 185, 129, .15), rgba(6, 182, 212, .1));
        border: 1px solid rgba(16, 185, 129, .25);
    }

    .sidebar ul li.active a i {
        color: var(--brand-400);
    }

    .sidebar ul li.active a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 22%;
        height: 56%;
        width: 3px;
        background: var(--brand-500);
        border-radius: 0 4px 4px 0;
        box-shadow: 0 0 12px rgba(16, 185, 129, .6);
    }

    .sidebar ul li.logout a {
        color: rgba(239, 68, 68, .75);
    }

    .sidebar ul li.logout a i {
        color: rgba(239, 68, 68, .55);
    }

    .sidebar ul li.logout a:hover {
        background: rgba(239, 68, 68, .1);
        color: #ef4444;
    }

    .sidebar ul li.logout a:hover i {
        color: #ef4444;
    }

    .income-submenu,
    .bike-submenu,
    .withdraw-submenu {
        display: none;
        list-style: none;
        padding: 4px 0 4px 30px;
        margin: 0;
    }

    .income-submenu.show,
    .bike-submenu.show,
    .withdraw-submenu.show {
        display: block;
    }

    .income-submenu li,
    .bike-submenu li,
    .withdraw-submenu li {
        margin: 2px 0;
    }

    .income-submenu li a,
    .bike-submenu li a,
    .withdraw-submenu li a {
        font-size: 13px;
        padding: 9px 12px !important;
        gap: 10px;
    }

    .income-submenu li a i,
    .bike-submenu li a i,
    .withdraw-submenu li a i {
        font-size: 12px;
    }

    .income-arrow,
    .bike-arrow,
    .withdraw-arrow {
        margin-left: auto;
        font-size: 11px !important;
        transition: transform var(--t);
        color: var(--txt-lo);
    }

    .income-menu.open .income-arrow,
    .bike-menu.open .bike-arrow,
    .withdraw-menu.open .withdraw-arrow {
        transform: rotate(180deg);
        color: var(--brand-400);
    }

    /* =========================================================
           MOBILE MENU BUTTON
        ========================================================= */
    .menu-btn {
        display: none;
        position: fixed;
        top: 18px;
        left: 18px;
        z-index: 1001;
        width: 42px;
        height: 42px;
        border-radius: var(--r-md);
        background: linear-gradient(135deg, var(--brand-500), var(--brand-600));
        color: #fff;
        border: none;
        font-size: 16px;
        cursor: pointer;
        box-shadow: var(--sh-glow);
        transition: var(--t);
    }

    .menu-btn:hover {
        transform: scale(1.06);
    }

    .menu-btn:active {
        transform: scale(.96);
    }

    /* =========================================================
           MAIN CONTENT
        ========================================================= */
    .main-content {
        margin-left: 260px;
        padding: 28px 32px 60px;
        min-height: 100vh;
    }

    /* =========================================================
           HEADER
        ========================================================= */
    .header {
        background: var(--glass);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid var(--glass-line);
        border-radius: var(--r-2xl);
        padding: 22px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        box-shadow: var(--sh-card);
        position: relative;
        overflow: hidden;
        animation: slideDown .5s var(--ease) both;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--brand-500), var(--accent-500), transparent);
    }

    /* =========================================================
           GREETING
        ========================================================= */
    .greeting {
        flex: 1;
        min-width: 240px;
    }

    .greeting h2 {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -.4px;
        color: var(--txt-hi);
        margin-bottom: 6px;
    }

    .greeting h2 span {
        background: linear-gradient(135deg, var(--brand-400), var(--accent-400));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        font-weight: 800;
    }

    .greeting p {
        display: inline-block;
        font-family: 'JetBrains Mono', monospace;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: .5px;
        color: var(--txt-lo);
        background: rgba(0, 0, 0, .3);
        padding: 4px 12px;
        border-radius: var(--r-full);
        border: 1px solid var(--glass-line);
    }

    .greeting marquee {
        display: none;
    }

    /* =========================================================
           WALLET ADDRESS
        ========================================================= */
    .wallet-address-section {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        padding: 8px 14px;
        background: rgba(6, 182, 212, .06);
        border: 1px solid rgba(6, 182, 212, .18);
        border-radius: var(--r-md);
        min-width: 200px;
    }

    .wallet-address-section .wallet-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--accent-400);
    }

    .wallet-address-section .wallet-label i {
        font-size: 12px;
    }

    .wallet-address-section .wallet-address {
        font-family: 'JetBrains Mono', monospace;
        font-size: 12px;
        font-weight: 500;
        color: var(--txt-mid);
        background: rgba(0, 0, 0, .3);
        padding: 4px 10px;
        border-radius: var(--r-sm);
        border: 1px solid var(--glass-line);
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .wallet-address-section .copy-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 600;
        color: var(--accent-400);
        background: rgba(6, 182, 212, .1);
        border: 1px solid rgba(6, 182, 212, .25);
        border-radius: var(--r-sm);
        cursor: pointer;
        transition: var(--t);
        text-decoration: none;
    }

    .wallet-address-section .copy-btn:hover {
        background: rgba(6, 182, 212, .2);
        color: #fff;
        border-color: var(--accent-500);
        transform: translateY(-1px);
    }

    .wallet-address-section .copy-btn:active {
        transform: scale(.97);
    }

    .wallet-address-section .copy-btn i {
        font-size: 12px;
    }

    /* =========================================================
           USER INFO
        ========================================================= */
    .header .user-info {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .header .user-info .status {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
        color: var(--success);
        background: rgba(34, 197, 94, .1);
        border: 1px solid rgba(34, 197, 94, .2);
        border-radius: var(--r-full);
    }

    .header .user-info .status .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--success);
        box-shadow: 0 0 8px rgba(34, 197, 94, .7);
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
            transform: scale(1);
        }

        50% {
            opacity: .4;
            transform: scale(.85);
        }
    }

    .header .user-info .avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--bg-800);
        box-shadow: 0 0 0 2px var(--brand-500), 0 8px 20px -8px rgba(16, 185, 129, .5);
        transition: var(--t);
    }

    .header .user-info .avatar:hover {
        transform: scale(1.05);
        box-shadow: 0 0 0 2px var(--accent-500), 0 12px 28px -8px rgba(6, 182, 212, .6);
    }

    /* =========================================================
           QUICK ACTIONS
        ========================================================= */
    .quick-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 24px;
        animation: fadeUp .55s var(--ease) both;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(14px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .quick-actions .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        color: var(--txt-mid);
        background: var(--glass);
        border: 1px solid var(--glass-line);
        border-radius: var(--r-full);
        text-decoration: none;
        transition: var(--t);
        backdrop-filter: blur(10px);
    }

    .quick-actions .action-btn i {
        color: var(--brand-400);
        font-size: 13px;
        transition: var(--t);
    }

    .quick-actions .action-btn:hover {
        color: var(--txt-hi);
        background: var(--glass-hi);
        border-color: rgba(16, 185, 129, .35);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px -8px rgba(16, 185, 129, .35);
    }

    .quick-actions .action-btn:hover i {
        color: var(--accent-400);
    }

    /* =========================================================
           STATS GRID
        ========================================================= */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 22px;
        margin-top: 28px;
    }

    .stat-card {
        position: relative;
        padding: 26px 22px 22px;
        background:
            linear-gradient(180deg,
                rgba(26, 35, 57, .9) 0%,
                rgba(19, 27, 46, .95) 100%);
        border: 1px solid var(--glass-line);
        border-radius: var(--r-3xl);
        transition: var(--t);
        overflow: hidden;
        animation: fadeUp .5s var(--ease) both;
        opacity: 0;
        isolation: isolate;
        box-shadow:
            0 1px 0 rgba(255, 255, 255, .04) inset,
            0 20px 40px -24px rgba(0, 0, 0, .9);
    }

    .stat-card:nth-child(1) {
        animation-delay: .05s;
    }

    .stat-card:nth-child(2) {
        animation-delay: .10s;
    }

    .stat-card:nth-child(3) {
        animation-delay: .15s;
    }

    .stat-card:nth-child(4) {
        animation-delay: .20s;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        border-radius: var(--r-3xl) var(--r-3xl) 0 0;
        background: var(--strip-color, var(--brand-500));
        transition: box-shadow var(--t);
    }

    .stat-card::after {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: radial-gradient(circle,
                var(--aura-color, rgba(16, 185, 129, .22)) 0%,
                transparent 70%);
        filter: blur(10px);
        opacity: .9;
        pointer-events: none;
        z-index: -1;
        transition: opacity var(--t);
    }

    .stat-card:nth-child(1) {
        --strip-color: linear-gradient(90deg, #0ea5e9, #06b6d4);
        --aura-color: rgba(6, 182, 212, .25);
    }

    .stat-card:nth-child(2) {
        --strip-color: linear-gradient(90deg, #6366f1, #8b5cf6);
        --aura-color: rgba(139, 92, 246, .25);
    }

    .stat-card:nth-child(3) {
        --strip-color: linear-gradient(90deg, #10b981, #059669);
        --aura-color: rgba(16, 185, 129, .25);
    }

    .stat-card:nth-child(4) {
        --strip-color: linear-gradient(90deg, #f43f5e, #e11d48);
        --aura-color: rgba(244, 63, 94, .25);
    }

    .stat-card:hover {
        transform: translateY(-6px);
        border-color: var(--glass-line-hi);
        box-shadow:
            0 1px 0 rgba(255, 255, 255, .06) inset,
            0 30px 60px -24px rgba(0, 0, 0, 1),
            0 0 40px -18px var(--aura-color);
    }

    .stat-card:hover::before {
        box-shadow: 0 0 24px var(--aura-color);
    }

    .stat-card:hover::after {
        opacity: 1;
    }

    .stat-card .card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .stat-card .icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 14px;
        font-size: 22px;
        color: #fff;
        transition: var(--t);
        position: relative;
    }

    .stat-card:hover .icon {
        transform: scale(1.08) rotate(-6deg);
    }

    .stat-card .icon.blue1 {
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        box-shadow: 0 8px 24px -8px rgba(14, 165, 233, .8), inset 0 1px 0 rgba(255, 255, 255, .3);
    }

    .stat-card .icon.blue2 {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        box-shadow: 0 8px 24px -8px rgba(99, 102, 241, .8), inset 0 1px 0 rgba(255, 255, 255, .3);
    }

    .stat-card .icon.cyan {
        background: linear-gradient(135deg, #10b981, #059669);
        box-shadow: 0 8px 24px -8px rgba(16, 185, 129, .8), inset 0 1px 0 rgba(255, 255, 255, .3);
    }

    .stat-card .icon.purple {
        background: linear-gradient(135deg, #f43f5e, #e11d48);
        box-shadow: 0 8px 24px -8px rgba(244, 63, 94, .8), inset 0 1px 0 rgba(255, 255, 255, .3);
    }

    .stat-card .card-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        border-radius: var(--r-full);
        background: rgba(255, 255, 255, .04);
        border: 1px solid var(--glass-line);
        color: var(--txt-lo);
    }

    .stat-card h3 {
        font-family: 'JetBrains Mono', monospace;
        font-size: 26px;
        font-weight: 600;
        letter-spacing: -1px;
        color: var(--txt-hi);
        line-height: 1.1;
        margin-bottom: 6px;
    }

    .stat-card p {
        font-size: 12.5px;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--txt-lo);
    }

    .stat-card .card-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px dashed var(--glass-line);
    }

    .stat-card .trend {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        border-radius: var(--r-full);
    }

    .stat-card .trend.up {
        color: var(--brand-400);
        background: rgba(16, 185, 129, .1);
        border: 1px solid rgba(16, 185, 129, .22);
    }

    .stat-card .trend.down {
        color: var(--danger);
        background: rgba(239, 68, 68, .1);
        border: 1px solid rgba(239, 68, 68, .22);
    }

    .stat-card .trend i {
        font-size: 9px;
    }

    .stat-card .bars {
        display: inline-flex;
        align-items: flex-end;
        gap: 2px;
        height: 18px;
    }

    .stat-card .bars span {
        display: inline-block;
        width: 3px;
        border-radius: 2px;
        background: var(--txt-dim);
        opacity: .5;
        transition: opacity var(--t);
    }

    .stat-card .bars span:nth-child(1) {
        height: 40%;
    }

    .stat-card .bars span:nth-child(2) {
        height: 65%;
    }

    .stat-card .bars span:nth-child(3) {
        height: 50%;
    }

    .stat-card .bars span:nth-child(4) {
        height: 85%;
    }

    .stat-card .bars span:nth-child(5) {
        height: 100%;
        background: var(--brand-400);
        opacity: 1;
    }

    .stat-card:hover .bars span {
        opacity: 1;
    }

    /* =========================================================
           TRANSACTIONS TABLE
        ========================================================= */
    .table-wrapper {
        margin-top: 30px;
        background: var(--glass);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid var(--glass-line);
        border-radius: var(--r-2xl);
        padding: 24px 26px;
        overflow-x: auto;
        animation: fadeUp .6s var(--ease) both;
        animation-delay: .25s;
        opacity: 0;
    }

    .table-wrapper .table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--glass-line);
        flex-wrap: wrap;
        gap: 12px;
    }

    .table-wrapper .table-header h3 {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: -.2px;
        color: var(--txt-hi);
    }

    .table-wrapper .table-header h3 i {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(16, 185, 129, .12);
        color: var(--brand-400);
        border-radius: var(--r-sm);
        font-size: 14px;
    }

    .table-wrapper .table-header a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--brand-400);
        text-decoration: none;
        padding: 8px 16px;
        border: 1px solid rgba(16, 185, 129, .25);
        border-radius: var(--r-full);
        transition: var(--t);
    }

    .table-wrapper .table-header a:hover {
        background: rgba(16, 185, 129, .1);
        color: var(--txt-hi);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }

    table thead th {
        padding: 12px 14px;
        text-align: left;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--txt-lo);
        border-bottom: 1px solid var(--glass-line);
    }

    table tbody td {
        padding: 14px;
        color: var(--txt-mid);
        border-bottom: 1px solid var(--glass-line);
        font-weight: 500;
    }

    table tbody tr {
        transition: background var(--t);
    }

    table tbody tr:hover {
        background: rgba(16, 185, 129, .04);
    }

    table tbody tr:last-child td {
        border-bottom: none;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 11px;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        border-radius: var(--r-full);
    }

    .badge-success {
        color: var(--success);
        background: rgba(34, 197, 94, .1);
        border: 1px solid rgba(34, 197, 94, .22);
    }

    .badge-pending {
        color: var(--warning);
        background: rgba(245, 158, 11, .1);
        border: 1px solid rgba(245, 158, 11, .22);
    }

    .badge-failed {
        color: var(--danger);
        background: rgba(239, 68, 68, .1);
        border: 1px solid rgba(239, 68, 68, .22);
    }

    .badge-primary {
        color: var(--info);
        background: rgba(59, 130, 246, .1);
        border: 1px solid rgba(59, 130, 246, .22);
    }

    .text-success {
        color: var(--success);
        font-weight: 600;
    }

    .text-danger {
        color: var(--danger);
        font-weight: 600;
    }

    .text-muted {
        color: var(--txt-lo);
    }

    .no-transactions {
        text-align: center;
        padding: 48px 20px;
        color: var(--txt-lo);
    }

    .no-transactions i {
        font-size: 42px;
        color: var(--bg-700);
        display: block;
        margin-bottom: 14px;
    }

    .no-transactions p {
        font-size: 13.5px;
        font-weight: 500;
    }

    /* =========================================================
           ============  NEW MOBILE BOTTOM NAV  ====================
           Floating pill with raised center FAB, animated ring
           around the active item, and spring entry.
        ========================================================= */
    .mobile-bottom-nav {
        display: none;
        position: fixed;
        bottom: 22px;
        left: 50%;
        transform: translateX(-50%) translateY(120px);
        z-index: 2000;
        padding: 8px;
        width: auto;
        min-width: 300px;
        max-width: 380px;
        background: linear-gradient(180deg,
                rgba(19, 27, 46, .96) 0%,
                rgba(10, 15, 28, .98) 100%);
        backdrop-filter: blur(24px) saturate(1.6);
        -webkit-backdrop-filter: blur(24px) saturate(1.6);
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: var(--r-full);
        box-shadow:
            0 20px 60px -12px rgba(0, 0, 0, .85),
            0 8px 24px rgba(0, 0, 0, .5),
            inset 0 1px 0 rgba(255, 255, 255, .06);
        align-items: center;
        justify-content: space-between;
        gap: 2px;
        animation: navEntry .7s var(--ease-bounce) .4s forwards;
    }

    @keyframes navEntry {
        from {
            transform: translateX(-50%) translateY(120px);
            opacity: 0;
        }

        to {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    }

    /* Nav item */
    .mobile-bottom-nav .nav-item {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        padding: 8px 12px;
        border-radius: var(--r-full);
        transition: var(--t);
        min-width: 48px;
        min-height: 44px;
        cursor: pointer;
        background: transparent;
        border: none;
        color: var(--txt-lo);
        -webkit-tap-highlight-color: transparent;
        isolation: isolate;
    }

    /* Animated ring around active item */
    .mobile-bottom-nav .nav-item::before {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: inherit;
        padding: 2px;
        background: conic-gradient(from var(--angle, 0deg),
                transparent 0%,
                var(--brand-400) 15%,
                var(--accent-400) 30%,
                transparent 45%,
                transparent 100%);
        -webkit-mask:
            linear-gradient(#000 0 0) content-box,
            linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0;
        transition: opacity var(--t);
        animation: spinRing 2.4s linear infinite;
        z-index: -1;
        pointer-events: none;
    }

    @property --angle {
        syntax: '<angle>';
        initial-value: 0deg;
        inherits: false;
    }

    @keyframes spinRing {
        to {
            --angle: 360deg;
        }
    }

    .mobile-bottom-nav .nav-item.active::before {
        opacity: 1;
    }

    /* Icon */
    .mobile-bottom-nav .nav-item i {
        font-size: 19px;
        line-height: 1;
        color: var(--txt-lo);
        transition: var(--t);
        z-index: 1;
        position: relative;
    }

    /* Tiny label that appears on active */
    .mobile-bottom-nav .nav-item .nav-label {
        position: absolute;
        top: -34px;
        left: 50%;
        transform: translateX(-50%) translateY(6px) scale(.85);
        padding: 4px 10px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
        color: var(--bg-950);
        background: var(--brand-400);
        border-radius: var(--r-full);
        opacity: 0;
        pointer-events: none;
        transition: var(--t);
        box-shadow: 0 6px 16px -4px rgba(16, 185, 129, .6);
    }

    /* Little pointer triangle */
    .mobile-bottom-nav .nav-item .nav-label::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 50%;
        transform: translateX(-50%) rotate(45deg);
        width: 6px;
        height: 6px;
        background: var(--brand-400);
        border-radius: 1px;
    }

    .mobile-bottom-nav .nav-item.active .nav-label {
        opacity: 1;
        transform: translateX(-50%) translateY(0) scale(1);
    }

    .mobile-bottom-nav .nav-item.active i {
        color: var(--brand-400);
        transform: translateY(-2px) scale(1.1);
        filter: drop-shadow(0 0 8px rgba(16, 185, 129, .9));
    }

    /* Hover (desktop preview) */
    .mobile-bottom-nav .nav-item:hover i {
        color: var(--txt-hi);
        transform: translateY(-1px);
    }

    /* Center FAB wrapper */
    .mobile-bottom-nav .add-btn-wrapper {
        position: relative;
        width: 56px;
        height: 56px;
        margin: -18px 4px 0;
        flex-shrink: 0;
    }

    /* FAB glow ring behind */
    .mobile-bottom-nav .add-btn-wrapper::before {
        content: '';
        position: absolute;
        inset: -6px;
        border-radius: 50%;
        background: radial-gradient(circle,
                rgba(16, 185, 129, .45) 0%,
                transparent 65%);
        filter: blur(8px);
        z-index: 0;
        animation: fabGlow 2.5s ease-in-out infinite;
    }

    @keyframes fabGlow {

        0%,
        100% {
            opacity: .6;
            transform: scale(1);
        }

        50% {
            opacity: 1;
            transform: scale(1.06);
        }
    }

    /* FAB button */
    .mobile-bottom-nav .add-btn {
        position: relative;
        z-index: 1;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--brand-400) 0%, var(--brand-500) 40%, var(--accent-500) 100%);
        color: #fff;
        border: 3px solid var(--bg-950);
        font-size: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow:
            0 10px 30px -6px rgba(16, 185, 129, .7),
            inset 0 2px 0 rgba(255, 255, 255, .35),
            inset 0 -2px 4px rgba(0, 0, 0, .15);
        transition: var(--t);
        text-decoration: none;
    }

    .mobile-bottom-nav .add-btn:hover {
        transform: scale(1.1) rotate(90deg);
        box-shadow:
            0 14px 40px -6px rgba(16, 185, 129, .9),
            inset 0 2px 0 rgba(255, 255, 255, .45),
            inset 0 -2px 4px rgba(0, 0, 0, .15);
    }

    .mobile-bottom-nav .add-btn:active {
        transform: scale(.92);
    }

    .mobile-bottom-nav .add-btn i {
        color: #fff;
    }

    /* Pulse dot on FAB — "new" indicator */
    .mobile-bottom-nav .add-btn::after {
        content: '';
        position: absolute;
        top: 4px;
        right: 4px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #fbbf24;
        border: 2px solid var(--bg-950);
        animation: dotPulse 2s ease-in-out infinite;
    }

    @keyframes dotPulse {

        0%,
        100% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.15);
            opacity: .7;
        }
    }

    /* =========================================================
           TOAST
        ========================================================= */
    .toast-message {
        position: fixed;
        bottom: 100px;
        left: 50%;
        transform: translateX(-50%) translateY(20px);
        padding: 12px 22px;
        color: #fff;
        font-size: 13.5px;
        font-weight: 600;
        background: linear-gradient(135deg, var(--brand-500), var(--brand-600));
        border-radius: var(--r-full);
        box-shadow: 0 12px 40px -8px rgba(16, 185, 129, .6);
        z-index: 9999;
        opacity: 0;
        pointer-events: none;
        transition: opacity .3s ease, transform .3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .toast-message.show {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    .toast-message i {
        font-size: 15px;
    }

    /* =========================================================
           RESPONSIVE
        ========================================================= */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 992px) {
        .sidebar {
            left: -280px;
        }

        .sidebar.active {
            left: 0;
        }

        .main-content {
            margin-left: 0;
            padding: 20px 18px 130px;
        }

        .menu-btn {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header {
            padding-top: 60px;
        }
    }

    @media (max-width: 768px) {
        .header {
            flex-direction: column;
            align-items: stretch;
            padding: 22px 20px;
        }

        .greeting {
            text-align: center;
        }

        .greeting h2 {
            font-size: 20px;
        }

        .wallet-address-section {
            justify-content: center;
            width: 100%;
        }

        .wallet-address-section .wallet-address {
            max-width: 100%;
        }

        .header .user-info {
            justify-content: center;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .stat-card h3 {
            font-size: 24px;
        }

        .quick-actions {
            justify-content: center;
        }

        .table-wrapper {
            padding: 18px 16px;
        }

        table {
            min-width: 520px;
        }

        .mobile-bottom-nav {
            display: flex;
        }
    }

    @media (max-width: 480px) {
        .main-content {
            padding: 14px 12px 130px;
        }

        .header {
            padding: 18px 16px;
        }

        .greeting h2 {
            font-size: 18px;
        }

        .greeting p {
            font-size: 11px;
        }

        .stat-card {
            padding: 22px 18px 18px;
        }

        .stat-card .icon {
            width: 46px;
            height: 46px;
            font-size: 20px;
            border-radius: 12px;
        }

        .stat-card h3 {
            font-size: 22px;
        }

        .stat-card p {
            font-size: 11px;
        }

        .stat-card .card-head {
            margin-bottom: 16px;
        }

        .header .user-info .avatar {
            width: 42px;
            height: 42px;
        }

        .wallet-address-section {
            padding: 8px 12px;
        }

        .wallet-address-section .wallet-address {
            font-size: 11px;
        }

        /* Compact bottom nav on tiny screens */
        .mobile-bottom-nav {
            bottom: 14px;
            padding: 6px;
            min-width: 270px;
            max-width: 320px;
        }

        .mobile-bottom-nav .nav-item {
            min-width: 42px;
            min-height: 40px;
            padding: 6px 8px;
        }

        .mobile-bottom-nav .nav-item i {
            font-size: 17px;
        }

        .mobile-bottom-nav .add-btn-wrapper {
            width: 48px;
            height: 48px;
            margin: -14px 2px 0;
        }

        .mobile-bottom-nav .add-btn {
            width: 48px;
            height: 48px;
            font-size: 18px;
            border-width: 2.5px;
        }

        .mobile-bottom-nav .add-btn::after {
            width: 8px;
            height: 8px;
            border-width: 1.5px;
        }

        .mobile-bottom-nav .nav-item .nav-label {
            top: -30px;
            font-size: 9px;
            padding: 3px 8px;
        }
    }

    /* =========================================================
           SCROLLBAR
        ========================================================= */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--bg-900);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--bg-700);
        border-radius: var(--r-full);
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--brand-500);
    }

    @media (prefers-reduced-motion: reduce) {

        *,
        *::before,
        *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
        }
    }
    </style>
</head>

<body>

    <button class="menu-btn" onclick="toggleMenu()" aria-label="Menu">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="logo">
            <h2>Thunder X</h2>
        </div>
        <ul>
            <li class="active"><a href="user_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="user_profile.php"><i class="fas fa-user"></i> My Profile</a></li>
            <li><a href="user_wallet.php"><i class="fas fa-wallet"></i> Wallet</a></li>
            <!-- <li><a href="user_investment.php"><i class="fas fa-chart-line"></i> Investment</a></li> -->
            <li class="income-menu">
                <a href="javascript:void(0);" onclick="toggleIncomeMenu()">
                    <i class="fas fa-money-bill-wave"></i>
                    Income
                    <i class="fas fa-chevron-down income-arrow"></i>
                </a>
                <ul class="income-submenu" id="incomeSubmenu">
                    <li><a href="user_active.php"><i class="fas fa-user-check"></i><span>Active Team</span></a></li>
                    <li><a href="user_direct_team.php"><i class="fas fa-users"></i><span>Direct Team</span></a></li>
                    <li><a href="user_income.php"><i class="fas fa-user-plus"></i>Direct Income</a></li>

                </ul>
            </li>

            <li class="withdraw-menu">
                <a href="javascript:void(0);" onclick="toggleWithdrawMenu()">
                    <i class="fas fa-hand-holding-dollar"></i>
                    Withdraw
                    <i class="fas fa-chevron-down withdraw-arrow"></i>
                </a>
                <ul class="withdraw-submenu" id="withdrawSubmenu">
                    <li><a href="wellet_address.php"><i class="fas fa-file-invoice-dollar"></i> Wallet Address</a></li>
                    <li><a href="user_withdraw.php"><i class="fas fa-file-invoice-dollar"></i> Withdraw Debit</a></li>
                    <li><a href="user_withdraw_report.php"><i class="fas fa-money-bill-transfer"></i> Withdraw
                            Report</a></li>
                </ul>
            </li>

            <li class="bike-menu">
                <a href="javascript:void(0);" onclick="toggleBikeMenu()">
                    <i class="fas fa-motorcycle"></i>
                    My Bike
                    <i class="fas fa-chevron-down bike-arrow"></i>
                </a>
                <ul class="bike-submenu" id="bikeSubmenu">
                    <li><a href="../product.php"><i class="fas fa-plus-circle"></i> My Bike Add</a></li>
                    <li><a href="user_product_edit.php"><i class="fas fa-edit"></i> Product Seller</a></li>
                    <li><a href="../blog.php"><i class="fas fa-eye"></i> My Bike View</a></li>
                </ul>
            </li>

            <li><a href="massages.php"><i class="fas fa-comments"></i> My Chat</a></li>
            <li><a href="user_transactions.php"><i class="fas fa-clock-rotate-left"></i> Transactions</a></li>
            <li><a href="user_settings.php"><i class="fas fa-gear"></i> Settings</a></li>
            <li class="logout"><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">

        <div class="header">
            <div class="greeting">
                <h2>Welcome, <span><?php echo htmlspecialchars($name); ?></span></h2>
                <p><i class="fas fa-id-badge"></i> <?php echo str_pad($row['referral_id'], 6, '0', STR_PAD_LEFT); ?></p>
            </div>

            <div class="wallet-address-section">
                <span class="wallet-label">
                    <i class="fas fa-qrcode"></i> Wallet
                </span>
                <span class="wallet-address" id="walletAddress">
                    <?php echo !empty($wallet_address) ? htmlspecialchars($wallet_address) : 'Not Set'; ?>
                </span>
                <?php if(!empty($wallet_address)): ?>
                <button class="copy-btn" onclick="copyWalletAddress()">
                    <i class="fas fa-copy"></i> Copy
                </button>
                <?php else: ?>
                <a href="user_profile.php" class="copy-btn">
                    <i class="fas fa-plus-circle"></i> Add
                </a>
                <?php endif; ?>
            </div>

            <div class="user-info">
                <div class="status">
                    <span class="dot"></span> Online
                </div>
                <?php
            $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=10b981&color=fff&size=60&bold=true";
            ?>
                <img src="<?php echo $avatar_url; ?>" alt="Avatar" class="avatar">
            </div>
        </div>

        <div class="quick-actions">
            <a href="#" class="action-btn"><i class="fas fa-plus-circle"></i> Active View</a>
            <a href="user_wallet.php" class="action-btn"><i class="fas fa-hand-holding-usd"></i> Deposit</a>
            <a href="user_profile.php" class="action-btn"><i class="fas fa-user-edit"></i> Edit Profile</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="card-head">
                    <div class="icon blue1"><i class="fas fa-wallet"></i></div>
                    <span class="card-tag">Live</span>
                </div>
                <h3>₹<?php echo number_format($wallet_balance, 2); ?></h3>
                <p>Wallet Balance</p>
                <div class="card-foot">
                    <span class="trend up"><i class="fas fa-arrow-up"></i> Available</span>
                    <span class="bars"><span></span><span></span><span></span><span></span><span></span></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="card-head">
                    <div class="icon blue2"><i class="fas fa-chart-line"></i></div>
                    <span class="card-tag">Active</span>
                </div>
                <h3>₹<?php echo number_format($total_investment, 2); ?></h3>
                <p>Total Investment</p>
                <div class="card-foot">
                    <span class="trend up"><i class="fas fa-arrow-up"></i> Running</span>
                    <span class="bars"><span></span><span></span><span></span><span></span><span></span></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="card-head">
                    <div class="icon cyan"><i class="fas fa-money-bill-wave"></i></div>
                    <span class="card-tag">All Time</span>
                </div>
                <h3>₹<?php echo number_format($total_income, 2); ?></h3>
                <p>Total Income</p>
                <div class="card-foot">
                    <span class="trend up"><i class="fas fa-arrow-up"></i> Lifetime</span>
                    <span class="bars"><span></span><span></span><span></span><span></span><span></span></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="card-head">
                    <div class="icon purple"><i class="fas fa-box-open"></i></div>
                    <span class="card-tag">Items</span>
                </div>
                <h3><?php echo $total_products; ?></h3>
                <p>My Products</p>
                <div class="card-foot">
                    <span class="trend up"><i class="fas fa-box"></i> Total</span>
                    <span class="bars"><span></span><span></span><span></span><span></span><span></span></span>
                </div>
            </div>
        </div>

        <div class="table-wrapper">
            <div class="table-header">
                <h3><i class="fas fa-clock-rotate-left"></i> Recent Transactions</h3>
                <a href="user_transactions.php">View All <i class="fas fa-arrow-right"></i></a>
            </div>

            <?php if($recent_transactions && mysqli_num_rows($recent_transactions) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($txn = mysqli_fetch_assoc($recent_transactions)):
                        $badge_class = 'badge-success';
                        if($txn['status'] == 'pending') $badge_class = 'badge-pending';
                        elseif($txn['status'] == 'failed') $badge_class = 'badge-failed';
                        $type_icon = $txn['type'] == 'deposit' ? '↓' : '↑';
                        $type_color = $txn['type'] == 'deposit' ? 'text-success' : 'text-danger';
                    ?>
                    <tr>
                        <td><?php echo date('d-m-Y', strtotime($txn['created_at'])); ?></td>
                        <td>
                            <span class="<?php echo $type_color; ?>">
                                <?php echo $type_icon; ?> <?php echo ucfirst($txn['type']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="<?php echo $type_color; ?>">
                                <?php echo $txn['type'] == 'deposit' ? '+' : '-'; ?>₹<?php echo number_format($txn['amount'], 2); ?>
                            </span>
                        </td>
                        <td><span
                                class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($txn['status']); ?></span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-transactions">
                <i class="fas fa-receipt"></i>
                <p>No transactions found</p>
                <p style="font-size:12.5px;opacity:.65;margin-top:6px;">Start investing to see your transactions here
                </p>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ============================================================
     NEW MOBILE BOTTOM NAVIGATION
     ============================================================ -->
    <nav class="mobile-bottom-nav" id="bottomNav" role="navigation" aria-label="Main Navigation">
        <a href="#" class="nav-item active" aria-label="Home">
            <i class="fas fa-home"></i>
            <span class="nav-label">Home</span>
        </a>

        <a href="user_wallet.php" class="nav-item" aria-label="Wallet">
            <i class="fas fa-wallet"></i>
            <span class="nav-label">Wallet</span>
        </a>

        <div class="add-btn-wrapper">
            <a href="user_investment.php" class="add-btn" aria-label="New Investment">
                <i class="fas fa-plus"></i>
            </a>
        </div>

        <a href="../product.php" class="nav-item" aria-label="Products">
            <i class="fas fa-motorcycle"></i>
            <span class="nav-label">Products</span>
        </a>

        <a href="user_profile.php" class="nav-item" aria-label="Profile">
            <i class="fas fa-user"></i>
            <span class="nav-label">Profile</span>
        </a>
    </nav>

    <div class="toast-message" id="toastMessage">
        <i class="fas fa-check-circle"></i>
        <span>Wallet address copied!</span>
    </div>

    <script>
    function toggleMenu() {
        document.getElementById('sidebar').classList.toggle('active');
    }

    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const menuBtn = document.querySelector('.menu-btn');
        if (window.innerWidth <= 992) {
            if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('sidebar').classList.remove('active');
        }
    });

    const navItems = document.querySelectorAll('.mobile-bottom-nav .nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            navItems.forEach(n => n.classList.remove('active'));
            this.classList.add('active');
        });
    });

    (function highlightCurrent() {
        const currentPath = window.location.pathname;
        const links = document.querySelectorAll('.mobile-bottom-nav .nav-item');
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href.replace(/^\.\.\//, ''))) {
                links.forEach(n => n.classList.remove('active'));
                link.classList.add('active');
            }
        });
    })();

    function copyWalletAddress() {
        const addressElement = document.getElementById('walletAddress');
        const address = addressElement.textContent.trim();

        if (address && address !== 'Not Set') {
            navigator.clipboard.writeText(address).then(() => {
                showToast('Wallet address copied!');
            }).catch(() => {
                const temp = document.createElement('input');
                temp.value = address;
                document.body.appendChild(temp);
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
                showToast('Wallet address copied!');
            });
        }
    }

    function showToast(message) {
        const toast = document.getElementById('toastMessage');
        toast.querySelector('span').textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    function toggleIncomeMenu() {
        document.getElementById("incomeSubmenu").classList.toggle("show");
        document.querySelector(".income-menu").classList.toggle("open");
    }

    function toggleBikeMenu() {
        document.getElementById("bikeSubmenu").classList.toggle("show");
        document.querySelector(".bike-menu").classList.toggle("open");
    }

    function toggleWithdrawMenu() {
        document.getElementById("withdrawSubmenu").classList.toggle("show");
        document.querySelector(".withdraw-menu").classList.toggle("open");
    }
    </script>

</body>

</html>