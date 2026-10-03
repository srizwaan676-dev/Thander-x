<?php
session_start();
include("../db.php");

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: user/user_profile.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$sql = "SELECT * FROM contact WHERE id = '$user_id' AND role = 'user'";
$result = mysqli_query($conn, $sql);

if(!$result) {
    die("Database Error: " . mysqli_error($conn));
}

$row = mysqli_fetch_assoc($result);

// If user not found
if(!$row) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Get user stats
$income_sql = "SELECT SUM(amount) as total FROM income WHERE user_id = '$user_id' AND status = 'paid'";
$income_result = mysqli_query($conn, $income_sql);
$income_data = mysqli_fetch_assoc($income_result);
$total_income = $income_data['total'] ?? 0;

$investment_sql = "SELECT SUM(amount) as total FROM investments WHERE user_id = '$user_id' AND status IN ('active', 'completed')";
$investment_result = mysqli_query($conn, $investment_sql);
$investment_data = mysqli_fetch_assoc($investment_result);
$total_investment = $investment_data['total'] ?? 0;

$wallet_sql = "SELECT SUM(amount) as balance FROM wallet_transactions WHERE user_id = '$user_id'";
$wallet_result = mysqli_query($conn, $wallet_sql);
$wallet_data = mysqli_fetch_assoc($wallet_result);
$wallet_balance = $wallet_data['balance'] ?? 0;

$active_sql = "SELECT COUNT(*) as count FROM investments WHERE user_id = '$user_id' AND status = 'active'";
$active_result = mysqli_query($conn, $active_sql);
$active_data = mysqli_fetch_assoc($active_result);
$active_count = $active_data['count'] ?? 0;

// ============================================
// Get last login
// ============================================
$last_login = 'Never';
$sql_last = "SELECT MAX(created_at) as created_at FROM contact WHERE user_id = '$user_id'";
$last_result = mysqli_query($conn, $sql_last);

if($last_result && mysqli_num_rows($last_result) > 0) {
    $last_data = mysqli_fetch_assoc($last_result);
    if($last_data['last_login'] && $last_data['last_login'] != '0000-00-00 00:00:00') {
        $last_login_timestamp = strtotime($last_data['last_login']);
        $last_login = date('d F Y, h:i A', $last_login_timestamp);
    }
}

if($last_login == 'Never') {
    $sql_alt = "SELECT last_login FROM contact WHERE id = '$user_id'";
    $alt_result = mysqli_query($conn, $sql_alt);
    if($alt_result && mysqli_num_rows($alt_result) > 0) {
        $alt_data = mysqli_fetch_assoc($alt_result);
        if(isset($alt_data['last_login']) && $alt_data['last_login'] && $alt_data['last_login'] != '0000-00-00 00:00:00') {
            $last_login_timestamp = strtotime($alt_data['last_login']);
            $last_login = date('d F Y, h:i A', $last_login_timestamp);
        }
    }
}

if($last_login == 'Never') {
    $last_login = date('d F Y, h:i A');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Thunder X</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* =========================================================
           DESIGN TOKENS — Luxury Dark / Gold
        ========================================================= */
        :root {
            /* Brand surfaces */
            --bg-950: #0a0a0f;
            --bg-900: #101018;
            --bg-850: #16161f;
            --bg-800: #1c1c26;
            --bg-750: #232330;
            --bg-700: #2a2a38;

            /* Glass */
            --glass:      rgba(28, 28, 38, .72);
            --glass-hi:   rgba(40, 40, 55, .82);
            --glass-line: rgba(255, 255, 255, .07);
            --glass-line-hi: rgba(255, 255, 255, .12);

            /* Gold — the signature accent */
            --gold-300: #f4d68a;
            --gold-400: #e8c264;
            --gold-500: #d4af37;
            --gold-600: #b8941f;
            --gold-soft: rgba(212, 175, 55, .12);
            --gold-line: rgba(212, 175, 55, .3);

            /* Emerald (for money/positive) */
            --emerald-400: #34d399;
            --emerald-500: #10b981;
            --emerald-soft: rgba(16, 185, 129, .12);

            /* Cyan (for info) */
            --cyan-400: #22d3ee;
            --cyan-soft: rgba(34, 211, 238, .12);

            /* Rose (for danger) */
            --rose-400: #fb7185;
            --rose-500: #f43f5e;
            --rose-soft: rgba(244, 63, 94, .12);

            /* Text */
            --txt-hi: #f5f5f7;
            --txt-mid: #a8a8b3;
            --txt-lo: #6b6b7a;
            --txt-dim: #4a4a5a;

            /* Radii */
            --r-sm: 8px;
            --r-md: 12px;
            --r-lg: 16px;
            --r-xl: 20px;
            --r-2xl: 24px;
            --r-full: 999px;

            /* Glows */
            --glow-gold:   0 0 40px -10px rgba(212, 175, 55, .5);
            --glow-emerald:0 0 40px -10px rgba(16, 185, 129, .5);
            --sh-card:     0 20px 50px -20px rgba(0, 0, 0, .7);
            --sh-focus:    0 0 0 4px rgba(212, 175, 55, .15);

            /* Motion */
            --ease: cubic-bezier(.4, 0, .2, 1);
            --t: .28s var(--ease);
        }

        /* =========================================================
           RESET
        ========================================================= */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html { -webkit-text-size-adjust: 100%; }

        /* =========================================================
           BODY
        ========================================================= */
        body {
            min-height: 100vh;
            font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 15px;
            line-height: 1.55;
            color: var(--txt-hi);
            background-color: var(--bg-950);
            background-image:
                radial-gradient(1200px 600px at 10% -10%, rgba(212, 175, 55, .08), transparent 60%),
                radial-gradient(900px 500px at 110% 110%, rgba(16, 185, 129, .06), transparent 60%),
                radial-gradient(800px 400px at 50% 50%, rgba(34, 211, 238, .03), transparent 70%);
            background-attachment: fixed;
            padding: 28px 24px;
            position: relative;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Subtle grid overlay */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, .02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .02) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
            z-index: 0;
            mask-image: radial-gradient(circle at center, #000 20%, transparent 80%);
            -webkit-mask-image: radial-gradient(circle at center, #000 20%, transparent 80%);
        }

        /* =========================================================
           CONTAINER
        ========================================================= */
        .profile-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        /* =========================================================
           NAVBAR
        ========================================================= */
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 14px 24px;
            background: var(--glass);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid var(--glass-line);
            border-radius: var(--r-xl);
            box-shadow: var(--sh-card);
            position: relative;
            overflow: hidden;
            animation: slideDown .55s var(--ease) both;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Gold shimmer line on top */
        .navbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg,
                transparent,
                var(--gold-500),
                var(--gold-300),
                var(--gold-500),
                transparent);
            background-size: 200% 100%;
            animation: shimmer 4s linear infinite;
        }

        @keyframes shimmer {
            0%   { background-position: 0% 0; }
            100% { background-position: 200% 0; }
        }

        /* Logo */
        .navbar .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .navbar .logo .logo-icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--r-md);
            background: linear-gradient(135deg, var(--gold-600), var(--gold-400));
            color: var(--bg-950);
            font-size: 18px;
            box-shadow:
                0 6px 20px -6px rgba(212, 175, 55, .5),
                inset 0 1px 0 rgba(255, 255, 255, .3);
            transition: transform var(--t);
        }

        .navbar .logo .logo-icon:hover {
            transform: rotate(-6deg) scale(1.06);
        }

        .navbar .logo h2 {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -.4px;
            color: var(--txt-hi);
        }

        .navbar .logo h2 span {
            background: linear-gradient(135deg, var(--gold-300), var(--gold-500));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* Nav links */
        .navbar .nav-links {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .navbar .nav-links a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            color: var(--txt-mid);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border-radius: var(--r-full);
            border: 1px solid transparent;
            transition: var(--t);
        }

        .navbar .nav-links a i { font-size: 13px; }

        .navbar .nav-links a:hover {
            color: var(--gold-300);
            background: var(--gold-soft);
            border-color: var(--gold-line);
        }

        .navbar .nav-links a.active {
            color: var(--bg-950);
            background: linear-gradient(135deg, var(--gold-400), var(--gold-500));
            border-color: var(--gold-500);
            box-shadow: var(--glow-gold);
        }

        .navbar .nav-links a.active i { color: var(--bg-950); }

        .navbar .nav-links a.logout-btn {
            color: var(--rose-400);
            background: var(--rose-soft);
            border-color: rgba(244, 63, 94, .25);
        }

        .navbar .nav-links a.logout-btn:hover {
            color: #fff;
            background: var(--rose-500);
            border-color: var(--rose-500);
        }

        /* User info */
        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-left: 20px;
            border-left: 1px solid var(--glass-line);
            flex-shrink: 0;
        }

        .navbar .user-info .user-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--txt-hi);
        }

        .navbar .user-info .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--bg-900);
            outline: 2px solid var(--gold-500);
            outline-offset: 2px;
            object-fit: cover;
            transition: var(--t);
        }

        .navbar .user-info .user-avatar:hover {
            outline-color: var(--gold-300);
            transform: scale(1.05);
            box-shadow: var(--glow-gold);
        }

        /* =========================================================
           PAGE HEADER
        ========================================================= */
        .page-header {
            margin: 32px 0 24px;
            animation: fadeUp .6s var(--ease) both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .page-header h1 {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -.5px;
            color: var(--txt-hi);
        }

        .page-header h1 i {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--r-md);
            background: linear-gradient(135deg, var(--gold-600), var(--gold-400));
            color: var(--bg-950);
            font-size: 18px;
            box-shadow: var(--glow-gold);
        }

        .page-header p {
            margin-top: 8px;
            padding-left: 60px;
            color: var(--txt-mid);
            font-size: 14px;
        }

        /* =========================================================
           PROFILE GRID
        ========================================================= */
        .profile-grid {
            display: grid;
            grid-template-columns: minmax(300px, 1fr) 1.85fr;
            gap: 24px;
            animation: fadeUp .7s var(--ease) both;
        }

        /* =========================================================
           COMMON CARD
        ========================================================= */
        .profile-card,
        .details-card {
            position: relative;
            background: var(--glass);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid var(--glass-line);
            border-radius: var(--r-2xl);
            box-shadow: var(--sh-card);
            overflow: hidden;
            transition: var(--t);
        }

        .profile-card:hover,
        .details-card:hover {
            border-color: var(--glass-line-hi);
        }

        /* =========================================================
           PROFILE CARD
        ========================================================= */
        .profile-card {
            padding: 36px 28px 28px;
            text-align: center;
        }

        /* Gold top bar */
        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg,
                transparent,
                var(--gold-500),
                var(--gold-300),
                var(--gold-500),
                transparent);
        }

        /* Subtle radial glow behind avatar */
        .profile-card::after {
            content: '';
            position: absolute;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(212, 175, 55, .15), transparent 70%);
            filter: blur(20px);
            pointer-events: none;
            z-index: 0;
        }

        .profile-card .profile-img {
            position: relative;
            z-index: 1;
            width: 128px;
            height: 128px;
            margin: 0 auto 18px;
            display: block;
            border-radius: 50%;
            border: 3px solid var(--bg-850);
            outline: 2px solid var(--gold-500);
            outline-offset: 3px;
            background: var(--bg-800);
            object-fit: cover;
            box-shadow:
                0 12px 40px -12px rgba(212, 175, 55, .55),
                inset 0 0 0 1px rgba(255, 255, 255, .05);
            transition: var(--t);
        }

        .profile-card .profile-img:hover {
            transform: scale(1.04);
            outline-color: var(--gold-300);
            box-shadow:
                0 16px 50px -12px rgba(212, 175, 55, .75),
                0 0 0 8px rgba(212, 175, 55, .08);
        }

        .profile-card h2 {
            position: relative;
            z-index: 1;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -.4px;
            color: var(--txt-hi);
            margin-bottom: 10px;
        }

        .profile-card .role-badge {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--gold-300);
            background: var(--gold-soft);
            border: 1px solid var(--gold-line);
            border-radius: var(--r-full);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .05);
        }

        .profile-card .member-since {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 14px;
            color: var(--txt-mid);
            font-size: 13px;
            font-weight: 500;
        }

        .profile-card .member-since i {
            color: var(--gold-400);
        }

        /* =========================================================
           STATS MINI
        ========================================================= */
        .stats-mini {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 26px;
            padding-top: 26px;
            border-top: 1px solid var(--glass-line);
        }

        .stats-mini .stat-mini {
            padding: 14px 12px;
            background: rgba(10, 10, 15, .5);
            border: 1px solid var(--glass-line);
            border-radius: var(--r-md);
            text-align: left;
            transition: var(--t);
            position: relative;
            overflow: hidden;
        }

        .stats-mini .stat-mini::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
        }

        .stats-mini .stat-mini:nth-child(1)::before { background: var(--gold-500); }
        .stats-mini .stat-mini:nth-child(2)::before { background: var(--emerald-500); }
        .stats-mini .stat-mini:nth-child(3)::before { background: var(--cyan-400); }
        .stats-mini .stat-mini:nth-child(4)::before { background: var(--rose-400); }

        .stats-mini .stat-mini:hover {
            background: var(--glass-hi);
            border-color: var(--glass-line-hi);
            transform: translateY(-2px);
        }

        .stats-mini .stat-mini .stat-mini-number {
            display: block;
            font-family: 'JetBrains Mono', monospace;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: -.3px;
            color: var(--txt-hi);
            margin-bottom: 4px;
        }

        .stats-mini .stat-mini:nth-child(1) .stat-mini-number { color: var(--gold-300); }
        .stats-mini .stat-mini:nth-child(2) .stat-mini-number { color: var(--emerald-400); }
        .stats-mini .stat-mini:nth-child(3) .stat-mini-number { color: var(--cyan-400); }
        .stats-mini .stat-mini:nth-child(4) .stat-mini-number { color: var(--rose-400); }

        .stats-mini .stat-mini .stat-mini-label {
            display: block;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--txt-lo);
        }

        /* =========================================================
           DETAILS CARD
        ========================================================= */
        .details-card {
            padding: 32px 32px 30px;
        }

        .details-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg,
                transparent,
                var(--cyan-400),
                var(--gold-400),
                var(--emerald-400),
                transparent);
        }

        .details-card .details-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--glass-line);
        }

        .details-card .details-header h3 {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 17px;
            font-weight: 700;
            letter-spacing: -.3px;
            color: var(--txt-hi);
        }

        .details-card .details-header h3 i {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--gold-soft);
            color: var(--gold-400);
            border: 1px solid var(--gold-line);
            border-radius: var(--r-sm);
            font-size: 14px;
        }

        .details-card .details-header .edit-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .2px;
            color: var(--bg-950);
            background: linear-gradient(135deg, var(--gold-300), var(--gold-500));
            border: none;
            border-radius: var(--r-full);
            text-decoration: none;
            cursor: pointer;
            box-shadow: var(--glow-gold);
            transition: var(--t);
        }

        .details-card .details-header .edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 50px -10px rgba(212, 175, 55, .7);
        }

        /* =========================================================
           INFO GRID
        ========================================================= */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .info-item {
            padding: 16px 18px;
            background: rgba(10, 10, 15, .5);
            border: 1px solid var(--glass-line);
            border-radius: var(--r-md);
            transition: var(--t);
        }

        .info-item:hover {
            background: var(--glass-hi);
            border-color: var(--glass-line-hi);
        }

        .info-item .info-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--txt-lo);
            margin-bottom: 6px;
        }

        .info-item .info-label i {
            color: var(--gold-400);
            font-size: 12px;
        }

        .info-item .info-value {
            font-size: 14.5px;
            font-weight: 600;
            color: var(--txt-hi);
            word-break: break-word;
            line-height: 1.45;
        }

        /* Last-login variant */
        .info-item.last-login {
            background: var(--gold-soft);
            border-color: var(--gold-line);
        }

        .info-item.last-login .info-label,
        .info-item.last-login .info-label i {
            color: var(--gold-300);
        }

        .info-item.last-login .info-value {
            color: var(--gold-300);
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
        }

        /* Badge */
        .info-item .info-value .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            border-radius: var(--r-full);
        }

        .badge-success {
            color: var(--emerald-400);
            background: var(--emerald-soft);
            border: 1px solid rgba(16, 185, 129, .3);
        }

        /* =========================================================
           BUTTONS
        ========================================================= */
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 26px;
            padding-top: 22px;
            border-top: 1px solid var(--glass-line);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .2px;
            text-decoration: none;
            border: 1px solid transparent;
            border-radius: var(--r-full);
            cursor: pointer;
            transition: var(--t);
        }

        .btn:active { transform: scale(.98); }

        .btn-primary {
            color: var(--bg-950);
            background: linear-gradient(135deg, var(--gold-300), var(--gold-500));
            box-shadow: var(--glow-gold);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 50px -10px rgba(212, 175, 55, .7);
        }

        .btn-success {
            color: #fff;
            background: linear-gradient(135deg, var(--emerald-400), var(--emerald-500));
            box-shadow: var(--glow-emerald);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 50px -10px rgba(16, 185, 129, .75);
        }

        .btn-danger {
            color: var(--rose-400);
            background: transparent;
            border-color: rgba(244, 63, 94, .35);
        }

        .btn-danger:hover {
            color: #fff;
            background: var(--rose-500);
            border-color: var(--rose-500);
            transform: translateY(-2px);
            box-shadow: 0 0 40px -10px rgba(244, 63, 94, .6);
        }

        /* =========================================================
           RESPONSIVE
        ========================================================= */
        @media (max-width: 992px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }

            .page-header p {
                padding-left: 0;
            }
        }

        @media (max-width: 768px) {
            body { padding: 16px 14px; }

            .navbar {
                flex-direction: column;
                align-items: stretch;
                gap: 14px;
                padding: 16px 18px;
            }

            .navbar .logo { justify-content: center; }

            .navbar .nav-links {
                justify-content: center;
                width: 100%;
            }

            .navbar .user-info {
                justify-content: center;
                padding-left: 0;
                padding-top: 12px;
                border-left: none;
                border-top: 1px solid var(--glass-line);
            }

            .page-header h1 { font-size: 22px; }
            .page-header h1 i { width: 40px; height: 40px; font-size: 16px; }
            .page-header p { font-size: 13px; }

            .profile-card,
            .details-card { padding: 24px 20px; }

            .profile-card .profile-img { width: 104px; height: 104px; }

            .details-card .details-header {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }

            .details-card .details-header h3 { justify-content: center; }

            .details-card .details-header .edit-btn {
                width: 100%;
                justify-content: center;
            }

            .info-grid { grid-template-columns: 1fr; }

            .btn-group { flex-direction: column; }
            .btn-group .btn { width: 100%; }
        }

        @media (max-width: 480px) {
            body { padding: 12px 10px; }

            .navbar .nav-links {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 6px;
            }

            .navbar .nav-links a {
                justify-content: center;
                padding: 8px 10px;
                font-size: 11px;
            }

            .page-header h1 { font-size: 20px; }

            .profile-card h2 { font-size: 19px; }

            .stats-mini .stat-mini .stat-mini-number { font-size: 14px; }
            .info-item .info-value { font-size: 13.5px; }
        }

        /* =========================================================
           SCROLLBAR
        ========================================================= */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg-900); }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--gold-500), var(--gold-600));
            border-radius: var(--r-full);
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--gold-400);
        }

        /* =========================================================
           REDUCED MOTION
        ========================================================= */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>

<body>

    <div class="profile-container">

        <!-- ============================================
             NAVBAR
        ============================================ -->
        <nav class="navbar">
            <div class="logo">
                <div class="logo-icon"><i class="fas fa-crown"></i></div>
                <h2>Thunder<span>X</span></h2>
            </div>

            <div class="nav-links">
                <a href="user_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="user_profile.php" class="active"><i class="fas fa-user"></i> Profile</a>
            </div>

            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($row['name']); ?></span>
                <?php
                $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($row['name']) . "&background=D4AF37&color=0a0a0f&size=42&bold=true";
                ?>
                <img src="<?php echo $avatar_url; ?>" alt="Avatar" class="user-avatar">
            </div>
        </nav>

        <!-- ============================================
             PAGE HEADER
        ============================================ -->
        <div class="page-header">
            <h1><i class="fas fa-user-circle"></i> My Profile</h1>
            <p>Manage your personal information and view your account details</p>
        </div>

        <!-- ============================================
             PROFILE GRID
        ============================================ -->
        <div class="profile-grid">

            <!-- ===== PROFILE CARD ===== -->
            <div class="profile-card">
                <?php
                $name = isset($row['name']) ? $row['name'] : 'User';
                $profile_image = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=D4AF37&color=0a0a0f&size=140&bold=true";
                ?>
                <img src="<?php echo  $profile_image; ?>" alt="Profile" class="profile-img">

                <h2><?php echo htmlspecialchars($row['name']); ?></h2>
                <span class="role-badge">
                    <i class="fas fa-user-check"></i>
                    <?php echo isset($row['role']) ? ucfirst(htmlspecialchars($row['role'])) : 'User'; ?>
                </span>
                <div class="member-since">
                    <i class="fas fa-calendar-alt"></i>
                    Member since
                    <?php echo isset($row['created_at']) ? date('F Y', strtotime($row['created_at'])) : 'N/A'; ?>
                </div>

                <div class="stats-mini">
                    <div class="stat-mini">
                        <div class="stat-mini-number">₹<?php echo number_format($total_income, 0); ?></div>
                        <div class="stat-mini-label">Total Income</div>
                    </div>
                    <div class="stat-mini">
                        <div class="stat-mini-number">₹<?php echo number_format($total_investment, 0); ?></div>
                        <div class="stat-mini-label">Investment</div>
                    </div>
                    <div class="stat-mini">
                        <div class="stat-mini-number">₹<?php echo number_format($wallet_balance, 0); ?></div>
                        <div class="stat-mini-label">Wallet Balance</div>
                    </div>
                    <div class="stat-mini">
                        <div class="stat-mini-number"><?php echo $active_count; ?></div>
                        <div class="stat-mini-label">Active Plans</div>
                    </div>
                </div>
            </div>

            <!-- ===== DETAILS CARD ===== -->
            <div class="details-card">
                <div class="details-header">
                    <h3><i class="fas fa-info-circle"></i> Personal Information</h3>
                    <a href="user_profile_edit.php?id=<?php echo $row['id']; ?>" class="edit-btn">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-id-card"></i> User ID</div>
                        <div class="info-value">#<?php echo str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-hashtag"></i> sponsor ID</div>
                        <div class="info-value"><?php echo str_pad($row['referral_id'], 6, '0', STR_PAD_LEFT); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-user"></i> Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($row['name']); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-envelope"></i> Email Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($row['Email'] ?? $row['email'] ?? 'N/A'); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-phone"></i> Phone Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($row['mobile'] ?? $row['phone'] ?? 'Not provided'); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-location-dot"></i> Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($row['city'] ?? 'Not provided'); ?></div>
                    </div>

                    <!-- ===== LAST LOGIN ===== -->
                    <div class="info-item last-login">
                        <div class="info-label"><i class="fas fa-clock"></i> Last Login</div>
                        <div class="info-value">
                            <i class="fas fa-circle" style="font-size: 6px; vertical-align: middle;"></i>
                            <?php
                            echo !empty($row['created_at'])
                                ? date('d M Y, h:i A', strtotime($row['created_at']))
                                : 'N/A';
                            ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-user-tag"></i> Role</div>
                        <div class="info-value">
                            <span class="badge badge-success">
                                <i class="fas fa-check-circle"></i>
                                <?php echo isset($row['role']) ? ucfirst(htmlspecialchars($row['role'])) : 'User'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ===== BUTTON GROUP ===== -->
                <div class="btn-group">
                    <a href="user_dashboard.php" class="btn btn-primary">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                    <a href="user_wallet.php" class="btn btn-success">
                        <i class="fas fa-wallet"></i> Wallet
                    </a>
                    <a href="../logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

        </div>

    </div>

</body>

</html>