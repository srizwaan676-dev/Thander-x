<?php
/**
 * ============================================================
 *  EDIT PROFILE  ·  User Account Settings
 * ============================================================
 *  @version 3.2.0
 * ============================================================
 */

session_start();
require_once "../db.php";

/* ------------------------------------------------------------
 |  1. AUTHENTICATION GUARD
 ------------------------------------------------------------ */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

/* ------------------------------------------------------------
 |  2. LOAD CURRENT USER
 ------------------------------------------------------------ */
$stmt = $conn->prepare(
    "SELECT * FROM contact WHERE id = ? AND role = 'user' LIMIT 1"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    session_destroy();
    header("Location: login.php");
    exit();
}

/* ------------------------------------------------------------
 |  3. HANDLE FORM SUBMISSION
 ------------------------------------------------------------ */
$success_message = "";
$error_message   = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name             = trim($_POST['name']             ?? '');
    $email            = trim($_POST['email']            ?? '');
    $mobile           = trim($_POST['mobile']           ?? '');
    $city             = trim($_POST['city']             ?? '');
    $profile_image    = trim($_POST['profile_image']    ?? '');
    $current_password = $_POST['current_password']      ?? '';
    $new_password     = $_POST['new_password']          ?? '';
    $confirm_password = $_POST['confirm_password']      ?? '';

    $password_change_requested =
        $current_password !== '' || $new_password !== '' || $confirm_password !== '';

    if ($name === '' || $email === '' || $mobile === '') {
        $error_message = "Name, email and mobile are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $error_message = "Mobile number must be exactly 10 digits.";
    } else {

        $stmt = $conn->prepare(
            "SELECT id FROM contact WHERE email = ? AND id != ? LIMIT 1"
        );
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $email_taken = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($email_taken) {
            $error_message = "This email is already registered to another account.";
        } else {

            $update_password = false;

            if ($password_change_requested) {
                if ($current_password === '' || $new_password === '' || $confirm_password === '') {
                    $error_message = "Please fill in all password fields to change your password.";
                } elseif ($current_password !== $row['password']) {
                    $error_message = "Your current password is incorrect.";
                } elseif (strlen($new_password) < 6) {
                    $error_message = "New password must be at least 6 characters long.";
                } elseif ($new_password !== $confirm_password) {
                    $error_message = "New password and confirmation do not match.";
                } else {
                    $update_password = true;
                }
            }

            if ($error_message === '') {

                if ($update_password) {
                    $stmt = $conn->prepare(
                        "UPDATE contact 
                         SET name=?, email=?, mobile=?, city=?, profile_image=?, password=?  
                         WHERE id=?"
                    );
                    $stmt->bind_param(
                        "ssssssi",
                        $name, $email, $mobile, $city, $profile_image, $new_password, $user_id
                    );
                } else {
                    $stmt = $conn->prepare(
                        "UPDATE contact 
                         SET name=?, email=?, mobile=?, city=?, profile_image=? 
                         WHERE id=?"
                    );
                    $stmt->bind_param(
                        "sssssi",
                        $name, $email, $mobile, $city, $profile_image, $user_id
                    );
                }

                if ($stmt->execute()) {
                    $success_message = "Your profile has been updated successfully.";
                    $row['name']   = $name;
                    $row['email']  = $email;
                    $row['mobile'] = $mobile;
                    $row['city']   = $city;
                    $row['profile_image']   = $profile_image;
                    if ($update_password) $row['password'] = $new_password;
                } else {
                    $error_message = "Database error: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

/* ------------------------------------------------------------
 |  4. PREPARE VIEW DATA
 ------------------------------------------------------------ */
$display_name = $row['name']  ?? 'User';
$display_role = ucfirst($row['role'] ?? 'user');
$avatar_url   = "https://ui-avatars.com/api/?name=" . urlencode($display_name)
              . "&background=06b6d4&color=fff&size=200&bold=true";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#06b6d4">
    <title>Edit Profile · Account Settings</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ============================================================
           DESIGN TOKENS — Neon Dark
        ============================================================ */
        :root {
            /* Brand — Cyan / Purple neon */
            --neon-cyan:   #06b6d4;
            --neon-cyan-2: #22d3ee;
            --neon-purple: #a855f7;
            --neon-pink:   #ec4899;

            /* Surfaces */
            --bg-900: #08080f;
            --bg-800: #0d0d1a;
            --bg-700: #12122a;
            --bg-600: #1a1a3a;
            --surface: rgba(20, 20, 45, .65);
            --surface-2: rgba(30, 30, 65, .5);
            --border: rgba(120, 120, 200, .15);
            --border-hi: rgba(6, 182, 212, .4);

            /* Text */
            --txt-hi: #f0f4ff;
            --txt-mid: #b8c1e0;
            --txt-lo: #7a83a8;

            /* Status */
            --success: #10b981;
            --danger:  #f43f5e;

            /* Radii */
            --radius-sm: 10px;
            --radius-md: 14px;
            --radius-lg: 22px;
            --radius-xl: 28px;

            /* Shadows & glows */
            --glow-cyan:  0 0 40px -8px rgba(6, 182, 212, .55);
            --glow-purple:0 0 40px -8px rgba(168, 85, 247, .55);
            --shadow-card: 0 30px 80px -25px rgba(0, 0, 0, .85),
                           0 0 0 1px rgba(120, 120, 200, .08);
            --shadow-focus: 0 0 0 4px rgba(6, 182, 212, .15);

            --transition: .28s cubic-bezier(.4, 0, .2, 1);
        }

        /* ============================================================
           RESET
        ============================================================ */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html { -webkit-text-size-adjust: 100%; }

        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 15px;
            line-height: 1.55;
            color: var(--txt-hi);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
            background-color: var(--bg-900);
            -webkit-font-smoothing: antialiased;
        }

        /* Grid overlay — futuristic feel */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(120, 120, 200, .04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(120, 120, 200, .04) 1px, transparent 1px);
            background-size: 44px 44px;
            pointer-events: none;
            z-index: 0;
            mask-image: radial-gradient(circle at center, #000 30%, transparent 75%);
            -webkit-mask-image: radial-gradient(circle at center, #000 30%, transparent 75%);
        }

        /* Neon orbs */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 15% 20%, rgba(6, 182, 212, .18), transparent 40%),
                radial-gradient(circle at 85% 75%, rgba(168, 85, 247, .18), transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(236, 72, 153, .08), transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* ============================================================
           CARD
        ============================================================ */
        .edit-container {
            width: 100%;
            max-width: 520px;
            position: relative;
            z-index: 1;
            animation: fadeUp .7s cubic-bezier(.22, 1, .36, 1) both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .edit-card {
            position: relative;
            background: var(--surface);
            backdrop-filter: blur(24px) saturate(160%);
            -webkit-backdrop-filter: blur(24px) saturate(160%);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-card);
            overflow: hidden;
            isolation: isolate;
        }

        /* Top neon edge */
        .edit-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg,
                transparent,
                var(--neon-cyan),
                var(--neon-purple),
                var(--neon-pink),
                transparent);
            background-size: 200% 100%;
            animation: neonSlide 5s linear infinite;
            z-index: 3;
        }

        @keyframes neonSlide {
            0%   { background-position: 0% 0; }
            100% { background-position: 200% 0; }
        }

        /* ============================================================
           HEADER
        ============================================================ */
        .edit-header {
            position: relative;
            padding: 40px 32px 66px;
            text-align: center;
            overflow: hidden;
            background:
                radial-gradient(circle at 50% 0%, rgba(6, 182, 212, .18), transparent 60%),
                radial-gradient(circle at 50% 100%, rgba(168, 85, 247, .12), transparent 60%);
        }

        .edit-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 10%; right: 10%;
            height: 1px;
            background: linear-gradient(90deg,
                transparent,
                rgba(6, 182, 212, .5),
                transparent);
        }

        .edit-header .icon-circle {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 84px; height: 84px;
            border-radius: 24px;
            font-size: 34px;
            color: var(--neon-cyan-2);
            background: linear-gradient(135deg,
                rgba(6, 182, 212, .15),
                rgba(168, 85, 247, .15));
            border: 1px solid rgba(6, 182, 212, .3);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, .15),
                var(--glow-cyan);
            margin-bottom: 16px;
            transition: transform var(--transition), box-shadow var(--transition);
        }

        .edit-header .icon-circle:hover {
            transform: rotate(-6deg) scale(1.06);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, .2),
                0 0 60px -5px rgba(6, 182, 212, .8);
        }

        .edit-header h1 {
            position: relative;
            z-index: 1;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -.4px;
            background: linear-gradient(135deg, #fff 30%, var(--neon-cyan-2));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .edit-header p {
            position: relative;
            z-index: 1;
            margin-top: 6px;
            font-size: 13.5px;
            font-weight: 400;
            color: var(--txt-lo);
            letter-spacing: .2px;
        }

        /* ============================================================
           AVATAR
        ============================================================ */
        .avatar-wrap {
            position: relative;
            z-index: 2;
            margin-top: -52px;
            text-align: center;
        }

        /* Animated ring around avatar */
        .avatar-wrap::before {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 116px; height: 116px;
            border-radius: 50%;
            background: conic-gradient(
                from 0deg,
                var(--neon-cyan),
                var(--neon-purple),
                var(--neon-pink),
                var(--neon-cyan)
            );
            animation: spin 6s linear infinite;
            z-index: -1;
            opacity: .8;
        }

        .avatar-wrap::after {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 140px; height: 140px;
            border-radius: 50%;
            background: radial-gradient(circle,
                rgba(6, 182, 212, .35), transparent 70%);
            filter: blur(20px);
            z-index: -2;
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .avatar {
            width: 104px; height: 104px;
            border-radius: 50%;
            border: 4px solid var(--bg-800);
            object-fit: cover;
            background: var(--bg-700);
            transition: transform var(--transition);
        }

        .avatar:hover { transform: scale(1.05); }

        /* ============================================================
           BODY
        ============================================================ */
        .edit-body {
            padding: 30px 36px 40px;
        }

        /* ============================================================
           ALERTS
        ============================================================ */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 18px;
            border-radius: var(--radius-md);
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 22px;
            animation: slideDown .4s cubic-bezier(.22, 1, .36, 1) both;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .alert i {
            font-size: 18px;
            margin-top: 1px;
            flex-shrink: 0;
        }

        .alert-success {
            background: rgba(16, 185, 129, .12);
            color: #6ee7b7;
            border: 1px solid rgba(16, 185, 129, .3);
            border-left: 4px solid var(--success);
            box-shadow: 0 0 30px -10px rgba(16, 185, 129, .5);
        }

        .alert-error {
            background: rgba(244, 63, 94, .12);
            color: #fda4af;
            border: 1px solid rgba(244, 63, 94, .3);
            border-left: 4px solid var(--danger);
            box-shadow: 0 0 30px -10px rgba(244, 63, 94, .5);
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ============================================================
           FORM
        ============================================================ */
        .form-group { margin-bottom: 16px; }

        .form-label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--txt-lo);
            margin-bottom: 7px;
        }

        .form-label i {
            color: var(--neon-cyan);
            margin-right: 6px;
            width: 14px;
            text-align: center;
        }

        .input-wrap { position: relative; }

        .input-wrap > i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--txt-lo);
            font-size: 15px;
            pointer-events: none;
            transition: color var(--transition);
        }

        .input-wrap input {
            width: 100%;
            padding: 14px 16px 14px 46px;
            font-family: inherit;
            font-size: 14.5px;
            font-weight: 400;
            color: var(--txt-hi);
            background: rgba(10, 10, 25, .55);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            transition: border-color var(--transition),
                        background var(--transition),
                        box-shadow var(--transition);
        }

        .input-wrap input::placeholder {
            color: var(--txt-lo);
            font-weight: 400;
        }

        .input-wrap input:hover:not(:disabled) {
            border-color: rgba(6, 182, 212, .35);
            background: rgba(10, 10, 25, .75);
        }

        .input-wrap input:focus {
            outline: none;
            background: rgba(10, 10, 25, .9);
            border-color: var(--neon-cyan);
            box-shadow: var(--shadow-focus);
        }

        .input-wrap:focus-within > i {
            color: var(--neon-cyan-2);
        }

        .input-wrap input:disabled {
            background: rgba(10, 10, 25, .35);
            color: var(--txt-lo);
            cursor: not-allowed;
            opacity: .8;
        }

        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 34px; height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            border-radius: var(--radius-sm);
            color: var(--txt-lo);
            font-size: 15px;
            cursor: pointer;
            transition: color var(--transition), background var(--transition);
        }

        .password-toggle:hover {
            color: var(--neon-cyan-2);
            background: rgba(6, 182, 212, .1);
        }

        /* ============================================================
           SECTION DIVIDER
        ============================================================ */
        .section-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 28px 0 22px;
            color: var(--txt-lo);
            font-size: 11.5px;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .section-divider::before,
        .section-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, transparent, var(--border-hi), transparent);
        }

        .section-divider i {
            color: var(--neon-cyan);
            margin-right: 6px;
        }

        /* ============================================================
           BUTTONS
        ============================================================ */
        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 15px 22px;
            font-family: inherit;
            font-size: 14.5px;
            font-weight: 600;
            letter-spacing: .3px;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            text-decoration: none;
            transition: transform var(--transition),
                        box-shadow var(--transition),
                        background var(--transition),
                        color var(--transition);
        }

        .btn:active { transform: scale(.98); }

        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--neon-cyan), var(--neon-purple));
            box-shadow: 0 8px 28px -10px rgba(6, 182, 212, .6),
                        0 0 0 1px rgba(6, 182, 212, .3);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--neon-cyan-2), var(--neon-pink));
            opacity: 0;
            transition: opacity var(--transition);
            z-index: -1;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 40px -8px rgba(6, 182, 212, .8),
                        0 14px 40px -8px rgba(168, 85, 247, .5);
        }

        .btn-primary:hover::before { opacity: .35; }

        .btn-secondary {
            color: var(--txt-mid);
            background: rgba(10, 10, 25, .6);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: rgba(10, 10, 25, .9);
            border-color: rgba(6, 182, 212, .35);
            color: var(--txt-hi);
            transform: translateY(-2px);
        }

        /* ============================================================
           RESPONSIVE
        ============================================================ */
        @media (max-width: 540px) {
            body { padding: 14px; }

            .edit-header { padding: 30px 20px 58px; }
            .edit-header h1 { font-size: 21px; }
            .edit-header .icon-circle { width: 68px; height: 68px; font-size: 26px; border-radius: 20px; }

            .edit-body { padding: 24px 20px 30px; }
            .avatar { width: 90px; height: 90px; }

            .avatar-wrap::before { width: 102px; height: 102px; }

            .input-wrap input { padding: 12px 14px 12px 42px; font-size: 14px; }
            .input-wrap > i { left: 14px; font-size: 14px; }

            .btn-group { flex-direction: column; }
            .btn { padding: 13px 16px; font-size: 14px; }
        }

        /* ============================================================
           LIGHT MODE (default is dark — respects user's OS preference)
           If you always want dark, delete this block.
        ============================================================ */
        @media (prefers-color-scheme: light) {
            body {
                background-color: #0d0d1a;
            }
            /* Intentionally still dark — the theme is dark by design */
        }

        /* ============================================================
           REDUCED MOTION
        ============================================================ */
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

<div class="edit-container">
    <div class="edit-card">

        <!-- ===== HEADER ===== -->
        <header class="edit-header">
            <div class="icon-circle">
                <i class="fas fa-user-astronaut"></i>
            </div>
            <h1>Edit Profile</h1>
            <p>Update your personal information &amp; security</p>
        </header>

        <!-- ===== AVATAR ===== -->
        <div class="avatar-wrap">
            <img src="<?= htmlspecialchars($avatar_url) ?>" alt="Profile" class="avatar">
        </div>

        <!-- ===== BODY ===== -->
        <div class="edit-body">

            <?php if ($success_message !== ''): ?>
                <div class="alert alert-success" role="status">
                    <i class="fas fa-circle-check"></i>
                    <span><?= htmlspecialchars($success_message) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error_message !== ''): ?>
                <div class="alert alert-error" role="alert">
                    <i class="fas fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error_message) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="editForm" novalidate>

                <div class="form-group">
                    <label class="form-label" for="name">
                        <i class="fas fa-user"></i>Full Name
                    </label>
                    <div class="input-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name"
                               value="<?= htmlspecialchars($row['name'] ?? '') ?>"
                               placeholder="Enter your full name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="fas fa-envelope"></i>Email Address
                    </label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($row['email'] ?? '') ?>"
                               placeholder="you@example.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="mobile">
                        <i class="fas fa-phone"></i>Mobile Number
                    </label>
                    <div class="input-wrap">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="mobile" name="mobile"
                               value="<?= htmlspecialchars($row['mobile'] ?? '') ?>"
                               placeholder="10-digit mobile number"
                               maxlength="10" pattern="[0-9]{10}"
                               inputmode="numeric" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="city">
                        <i class="fas fa-city"></i>City
                    </label>
                    <div class="input-wrap">
                        <i class="fas fa-city"></i>
                        <input type="text" id="city" name="city"
                               value="<?= htmlspecialchars($row['city'] ?? '') ?>"
                               placeholder="Enter your city" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="role">
                        <i class="fas fa-user-tag"></i>Role
                    </label>
                    <div class="input-wrap">
                        <i class="fas fa-user-tag"></i>
                        <input type="text" id="role"
                               value="<?= htmlspecialchars($display_role) ?>"
                               disabled>
                    </div>
                </div>

                 <div class="form-group">
                    <label class="form-label" for="city">
                        <i class="fas fa-city"></i>image
                    </label>
                    <div class="input-wrap">
                        <i class="fas fa-city"></i>
                        <input type="file" id="profile_image" name="profile_image"
                               value="<?= htmlspecialchars($row['profile_image'] ?? '') ?>"
                               placeholder="Enter your city" required>
                    </div>
                </div>

                <!-- Password section -->
                <div class="section-divider">
                    <span><i class="fas fa-shield-halved"></i>Change Password</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="current_password">
                        <i class="fas fa-lock"></i>Current Password
                    </label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="current_password" name="current_password"
                               placeholder="Enter current password"
                               autocomplete="current-password">
                        <button type="button" class="password-toggle"
                                onclick="togglePassword('current_password', this)"
                                aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password">
                        <i class="fas fa-key"></i>New Password
                    </label>
                    <div class="input-wrap">
                        <i class="fas fa-key"></i>
                        <input type="password" id="new_password" name="new_password"
                               placeholder="Minimum 6 characters"
                               minlength="6" autocomplete="new-password">
                        <button type="button" class="password-toggle"
                                onclick="togglePassword('new_password', this)"
                                aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">
                        <i class="fas fa-check-double"></i>Confirm New Password
                    </label>
                    <div class="input-wrap">
                        <i class="fas fa-check-double"></i>
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="Re-enter new password"
                               minlength="6" autocomplete="new-password">
                        <button type="button" class="password-toggle"
                                onclick="togglePassword('confirm_password', this)"
                                aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-floppy-disk"></i> Save Changes
                    </button>
                    <a href="user_profile.php" class="btn btn-secondary">
                        <i class="fas fa-xmark"></i> Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const icon  = button.querySelector('i');
        const show  = input.type === 'password';

        input.type = show ? 'text' : 'password';
        icon.classList.toggle('fa-eye',       !show);
        icon.classList.toggle('fa-eye-slash',  show);
    }

    document.getElementById('editForm').addEventListener('submit', function (e) {
        const cur  = document.getElementById('current_password').value;
        const nw   = document.getElementById('new_password').value;
        const conf = document.getElementById('confirm_password').value;

        const anyFilled = cur || nw || conf;

        if (anyFilled) {
            if (!cur || !nw || !conf) {
                alert('Please fill in all password fields to change your password.');
                return e.preventDefault();
            }
            if (nw.length < 6) {
                alert('New password must be at least 6 characters long.');
                return e.preventDefault();
            }
            if (nw !== conf) {
                alert('New password and confirmation do not match.');
                return e.preventDefault();
            }
        }
    });

    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            el.style.transition = 'opacity .4s ease';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        });
    }, 5000);
</script>

</body>
</html>