<?php
session_start();
include("../db.php");

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$sql = "SELECT * FROM contact WHERE id = '$user_id' AND role = 'user'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if(!$row) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// ========================================
// UPDATE SETTINGS
// ========================================

$success_message = '';
$error_message = '';
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';

// Update Profile Settings
if(isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    
    if(empty($name) || empty($email) || empty($mobile)) {
        $error_message = "Name, Email and Mobile are required!";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format!";
    } else {
        $update_sql = "UPDATE contact SET 
                       name = '$name', 
                       email = '$email', 
                       mobile = '$mobile',
                       bio = '$bio',
                       city = '$city',
                       country = '$country'
                       WHERE id = '$user_id'";
        if(mysqli_query($conn, $update_sql)) {
            $success_message = "Profile updated successfully!";
            $_SESSION['name'] = $name;
            $row['name'] = $name;
            $row['email'] = $email;
            $row['mobile'] = $mobile;
        } else {
            $error_message = "Update failed: " . mysqli_error($conn);
        }
    }
}

// Update Password
if(isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All password fields are required!";
    } elseif($current_password !== $row['password']) {
        $error_message = "Current password is incorrect!";
    } elseif(strlen($new_password) < 8) {
        $error_message = "New password must be at least 8 characters!";
    } elseif($new_password !== $confirm_password) {
        $error_message = "New password and confirm password do not match!";
    } else {
        $update_sql = "UPDATE contact SET password = '$new_password' WHERE id = '$user_id'";
        if(mysqli_query($conn, $update_sql)) {
            $success_message = "Password updated successfully!";
            $row['password'] = $new_password;
        } else {
            $error_message = "Update failed: " . mysqli_error($conn);
        }
    }
}

// Update Notification Settings
if(isset($_POST['update_notifications'])) {
    $email_notify = isset($_POST['email_notify']) ? 1 : 0;
    $sms_notify = isset($_POST['sms_notify']) ? 1 : 0;
    $push_notify = isset($_POST['push_notify']) ? 1 : 0;
    $investment_alerts = isset($_POST['investment_alerts']) ? 1 : 0;
    $withdrawal_alerts = isset($_POST['withdrawal_alerts']) ? 1 : 0;
    
    // Check if table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'user_settings'");
    if(mysqli_num_rows($table_check) == 0) {
        // Create table with all columns
        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS user_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            email_notify TINYINT(1) DEFAULT 1,
            sms_notify TINYINT(1) DEFAULT 1,
            push_notify TINYINT(1) DEFAULT 1,
            investment_alerts TINYINT(1) DEFAULT 1,
            withdrawal_alerts TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES contact(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user (user_id)
        )");
    } else {
        // Check if columns exist and add if missing
        $column_check = mysqli_query($conn, "SHOW COLUMNS FROM user_settings LIKE 'investment_alerts'");
        if(mysqli_num_rows($column_check) == 0) {
            mysqli_query($conn, "ALTER TABLE user_settings ADD COLUMN investment_alerts TINYINT(1) DEFAULT 1");
        }
        
        $column_check = mysqli_query($conn, "SHOW COLUMNS FROM user_settings LIKE 'withdrawal_alerts'");
        if(mysqli_num_rows($column_check) == 0) {
            mysqli_query($conn, "ALTER TABLE user_settings ADD COLUMN withdrawal_alerts TINYINT(1) DEFAULT 1");
        }
    }
    
    $update_sql = "INSERT INTO user_settings (user_id, email_notify, sms_notify, push_notify, investment_alerts, withdrawal_alerts) 
                   VALUES ('$user_id', '$email_notify', '$sms_notify', '$push_notify', '$investment_alerts', '$withdrawal_alerts')
                   ON DUPLICATE KEY UPDATE 
                   email_notify = '$email_notify', 
                   sms_notify = '$sms_notify', 
                   push_notify = '$push_notify',
                   investment_alerts = '$investment_alerts',
                   withdrawal_alerts = '$withdrawal_alerts'";
    
    if(mysqli_query($conn, $update_sql)) {
        $success_message = "Notification settings updated successfully!";
    } else {
        $error_message = "Update failed: " . mysqli_error($conn);
    }
}

// Update Privacy Settings
if(isset($_POST['update_privacy'])) {
    $profile_visibility = mysqli_real_escape_string($conn, $_POST['profile_visibility']);
    $show_email = isset($_POST['show_email']) ? 1 : 0;
    $show_phone = isset($_POST['show_phone']) ? 1 : 0;
    $activity_status = isset($_POST['activity_status']) ? 1 : 0;
    
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'user_privacy'");
    if(mysqli_num_rows($table_check) == 0) {
        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS user_privacy (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            profile_visibility VARCHAR(20) DEFAULT 'public',
            show_email TINYINT(1) DEFAULT 1,
            show_phone TINYINT(1) DEFAULT 1,
            activity_status TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES contact(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user (user_id)
        )");
    }
    
    $update_sql = "INSERT INTO user_privacy (user_id, profile_visibility, show_email, show_phone, activity_status) 
                   VALUES ('$user_id', '$profile_visibility', '$show_email', '$show_phone', '$activity_status')
                   ON DUPLICATE KEY UPDATE 
                   profile_visibility = '$profile_visibility',
                   show_email = '$show_email',
                   show_phone = '$show_phone',
                   activity_status = '$activity_status'";
    
    if(mysqli_query($conn, $update_sql)) {
        $success_message = "Privacy settings updated successfully!";
    } else {
        $error_message = "Update failed: " . mysqli_error($conn);
    }
}

// ========================================
// FETCH SETTINGS WITH ERROR HANDLING
// ========================================

// Default values
$email_notify = 1;
$sms_notify = 1;
$push_notify = 1;
$investment_alerts = 1;
$withdrawal_alerts = 1;

// Fetch notification settings
$settings_sql = "SELECT * FROM user_settings WHERE user_id = '$user_id'";
$settings_result = mysqli_query($conn, $settings_sql);
if($settings_result && mysqli_num_rows($settings_result) > 0) {
    $settings = mysqli_fetch_assoc($settings_result);
    $email_notify = isset($settings['email_notify']) ? $settings['email_notify'] : 1;
    $sms_notify = isset($settings['sms_notify']) ? $settings['sms_notify'] : 1;
    $push_notify = isset($settings['push_notify']) ? $settings['push_notify'] : 1;
    $investment_alerts = isset($settings['investment_alerts']) ? $settings['investment_alerts'] : 1;
    $withdrawal_alerts = isset($settings['withdrawal_alerts']) ? $settings['withdrawal_alerts'] : 1;
}

// Fetch privacy settings
$profile_visibility = 'public';
$show_email = 1;
$show_phone = 1;
$activity_status = 1;

$privacy_sql = "SELECT * FROM user_privacy WHERE user_id = '$user_id'";
$privacy_result = mysqli_query($conn, $privacy_sql);
if($privacy_result && mysqli_num_rows($privacy_result) > 0) {
    $privacy = mysqli_fetch_assoc($privacy_result);
    $profile_visibility = isset($privacy['profile_visibility']) ? $privacy['profile_visibility'] : 'public';
    $show_email = isset($privacy['show_email']) ? $privacy['show_email'] : 1;
    $show_phone = isset($privacy['show_phone']) ? $privacy['show_phone'] : 1;
    $activity_status = isset($privacy['activity_status']) ? $privacy['activity_status'] : 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | IncomeCoin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        /* ========================================
                   ROOT VARIABLES
                ======================================== */
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
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
            background: radial-gradient(circle at top, #16345a, #07111f 45%);
            color: #fff;
            overflow-x: hidden;
        }

        /* ========================================
                   SIDEBAR
                ======================================== */
        .sidebar {
            width: 280px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 25px 18px;
            background: linear-gradient(180deg, #081521, #06101c);
            border-right: 1px solid rgba(255,255,255,.08);
            box-shadow: 10px 0 40px rgba(0,0,0,.5);
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
            transition: var(--transition);
        }

        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--success); border-radius: 10px; }

        .logo {
            height: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 35px;
        }

        .logo h2 {
            font-size: 30px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #fff;
        }

        .logo h2::first-letter { color: #22c55e; }

        .sidebar ul { list-style: none; }
        .sidebar ul li { margin: 8px 0; }
        .sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 14px 18px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 16px;
            transition: .35s;
            font-size: 14px;
            font-weight: 500;
        }

        .sidebar ul li a i { width: 25px; font-size: 18px; }
        .sidebar ul li a:hover {
            background: linear-gradient(90deg, #16a34a, #22c55e);
            color: white;
            transform: translateX(8px);
            box-shadow: 0 10px 30px rgba(34,197,94,.35);
        }

        .sidebar ul li.active a {
            background: linear-gradient(90deg, #16a34a, #22c55e);
            color: white;
            box-shadow: 0 10px 30px rgba(34,197,94,.35);
        }

        .sidebar ul li.logout a { color: #ff6b6b !important; }
        .sidebar ul li.logout a:hover {
            background: linear-gradient(90deg, #dc2626, #ef4444);
            color: white !important;
            box-shadow: 0 10px 30px rgba(239,68,68,.35);
        }

        /* ========================================
                   MENU BUTTON
                ======================================== */
        .menu-btn {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--success);
            border: none;
            color: #fff;
            width: 45px;
            height: 45px;
            border-radius: var(--radius-sm);
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(34,197,94,.4);
            transition: var(--transition);
        }

        .menu-btn:hover { transform: scale(1.05); }

        /* ========================================
                   MAIN CONTENT
                ======================================== */
        .main-content {
            margin-left: 280px;
            padding: 30px 35px;
            min-height: 100vh;
        }

        /* ========================================
                   HEADER
                ======================================== */
        .header {
            background: rgba(255,255,255,.08);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: var(--radius-xl);
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 20px 40px rgba(0,0,0,.35);
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
        }

        .header .welcome h2 {
            font-size: 28px;
            font-weight: 700;
        }

        .header .welcome h2 span { color: #22c55e; }
        .header .welcome p { color: #94a3b8; margin-top: 4px; font-size: 14px; }

        .header .profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header .profile img {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #22c55e;
            box-shadow: 0 0 25px rgba(34,197,94,.4);
        }

        /* ========================================
                   TAB NAVIGATION
                ======================================== */
        .tab-nav {
            display: flex;
            gap: 6px;
            background: rgba(255,255,255,.06);
            padding: 6px;
            border-radius: var(--radius-lg);
            margin-bottom: 30px;
            flex-wrap: wrap;
            border: 1px solid rgba(255,255,255,.08);
        }

        .tab-nav .tab-btn {
            padding: 12px 24px;
            border: none;
            border-radius: var(--radius-md);
            background: transparent;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Inter', sans-serif;
        }

        .tab-nav .tab-btn i { font-size: 16px; }

        .tab-nav .tab-btn:hover {
            color: #fff;
            background: rgba(255,255,255,.05);
        }

        .tab-nav .tab-btn.active {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: 0 4px 20px rgba(37,99,235,.35);
        }

        /* ========================================
                   SETTINGS CARD
                ======================================== */
        .settings-card {
            background: rgba(255,255,255,.08);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: var(--radius-xl);
            padding: 32px 35px;
            transition: var(--transition);
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .settings-card.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .settings-card:hover {
            border-color: rgba(34,197,94,.3);
        }

        .settings-card .card-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .settings-card .card-header .icon-box {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: #fff;
            box-shadow: 0 4px 15px rgba(34,197,94,.3);
        }

        .settings-card .card-header .icon-box.blue {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            box-shadow: 0 4px 15px rgba(37,99,235,.3);
        }

        .settings-card .card-header .icon-box.purple {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            box-shadow: 0 4px 15px rgba(139,92,246,.3);
        }

        .settings-card .card-header .icon-box.pink {
            background: linear-gradient(135deg, #ec4899, #db2777);
            box-shadow: 0 4px 15px rgba(236,72,153,.3);
        }

        .settings-card .card-header .icon-box.orange {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 4px 15px rgba(245,158,11,.3);
        }

        .settings-card .card-header .header-text h3 {
            font-size: 20px;
            font-weight: 700;
        }

        .settings-card .card-header .header-text p {
            font-size: 14px;
            color: #94a3b8;
        }

        /* ========================================
                   FORM ELEMENTS
                ======================================== */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: #e2e8f0;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .form-group label i {
            margin-right: 8px;
            color: #22c55e;
        }

        .form-group label .required {
            color: #ef4444;
            margin-left: 4px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid rgba(255,255,255,.15);
            border-radius: var(--radius-sm);
            font-size: 14px;
            transition: var(--transition);
            background: rgba(255,255,255,.06);
            color: #e2e8f0;
            font-family: 'Inter', sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #22c55e;
            background: rgba(255,255,255,.1);
            box-shadow: 0 0 0 4px rgba(34,197,94,.1);
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #64748b;
        }

        .form-group input:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-group .helper-text {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }

        /* ========================================
                   BUTTONS
                ======================================== */
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: #fff;
            box-shadow: 0 4px 15px rgba(34,197,94,.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34,197,94,.4);
        }

        .btn-primary:active {
            transform: scale(0.97);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: #fff;
            box-shadow: 0 4px 15px rgba(239,68,68,.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239,68,68,.4);
        }

        .btn-secondary {
            background: rgba(255,255,255,.1);
            color: #e2e8f0;
            border: 1px solid rgba(255,255,255,.15);
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,.2);
            transform: translateY(-2px);
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 5px;
            flex-wrap: wrap;
        }

        /* ========================================
                   TOGGLE SWITCH
                ======================================== */
        .toggle-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .toggle-group:last-child {
            border-bottom: none;
        }

        .toggle-group .toggle-info h4 {
            font-size: 14px;
            font-weight: 500;
        }

        .toggle-group .toggle-info p {
            font-size: 12px;
            color: #94a3b8;
        }

        .toggle {
            position: relative;
            width: 52px;
            height: 28px;
            background: rgba(255,255,255,.15);
            border-radius: 30px;
            cursor: pointer;
            transition: var(--transition);
            flex-shrink: 0;
        }

        .toggle.active {
            background: #22c55e;
        }

        .toggle .toggle-slider {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 22px;
            height: 22px;
            background: #fff;
            border-radius: 50%;
            transition: var(--transition);
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
        }

        .toggle.active .toggle-slider {
            left: 27px;
        }

        .toggle input {
            display: none;
        }

        /* ========================================
                   ALERTS
                ======================================== */
        .alert {
            padding: 14px 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: rgba(34,197,94,.15);
            border-left: 4px solid #22c55e;
            color: #86efac;
        }

        .alert-error {
            background: rgba(239,68,68,.15);
            border-left: 4px solid #ef4444;
            color: #fca5a5;
        }

        .alert i {
            font-size: 18px;
        }

        .alert .alert-close {
            margin-left: auto;
            background: none;
            border: none;
            color: inherit;
            font-size: 18px;
            cursor: pointer;
            opacity: 0.6;
            transition: var(--transition);
        }

        .alert .alert-close:hover {
            opacity: 1;
        }

        /* ========================================
                   ACCOUNT INFO ITEMS
                ======================================== */
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-item .info-label {
            font-size: 14px;
            color: #94a3b8;
        }

        .info-item .info-value {
            font-size: 14px;
            font-weight: 500;
            color: #e2e8f0;
        }

        .info-item .info-value .badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success { background: #22c55e; color: #fff; }
        .badge-warning { background: #f59e0b; color: #fff; }
        .badge-danger { background: #ef4444; color: #fff; }
        .badge-primary { background: #2563eb; color: #fff; }

        /* ========================================
                   RESPONSIVE
                ======================================== */
        @media(max-width: 991px) {
            .sidebar {
                left: -280px;
            }
            .sidebar.active {
                left: 0;
            }
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .menu-btn {
                display: block;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .tab-nav .tab-btn {
                padding: 10px 16px;
                font-size: 13px;
            }
        }

        @media(max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
                padding: 20px;
            }
            .header .welcome h2 {
                font-size: 22px;
            }
            .header .profile {
                flex-direction: column;
            }
            .settings-card {
                padding: 20px;
            }
            .settings-card .card-header {
                flex-direction: column;
                text-align: center;
            }
            .tab-nav {
                justify-content: center;
            }
            .tab-nav .tab-btn {
                padding: 8px 14px;
                font-size: 12px;
            }
            .tab-nav .tab-btn span {
                display: none;
            }
            .menu-btn {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }
            .btn-group {
                flex-direction: column;
            }
            .btn-group .btn {
                width: 100%;
                justify-content: center;
            }
            .toggle-group {
                flex-wrap: wrap;
                gap: 10px;
            }
        }

        @media(max-width: 480px) {
            .main-content {
                padding: 15px;
            }
            .header .welcome h2 {
                font-size: 19px;
            }
            .settings-card {
                padding: 16px;
            }
            .settings-card .card-header .icon-box {
                width: 44px;
                height: 44px;
                font-size: 18px;
            }
            .settings-card .card-header .header-text h3 {
                font-size: 17px;
            }
        }

        /* ========================================
                   SCROLLBAR
                ======================================== */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: rgba(255,255,255,.05); }
        ::-webkit-scrollbar-thumb { background: #22c55e; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #16a34a; }
    </style>
</head>
<body>

<!-- ===== MENU BUTTON ===== -->
<button class="menu-btn" onclick="toggleMenu()">
    <i class="fa-solid fa-bars"></i>
</button>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar" id="sidebar">
    <div class="logo">
        <h2>IncomeCoin</h2>
    </div>
    <ul>
        <li><a href="user_dashboard.php"><i class="fa-solid fa-table-columns"></i> Dashboard</a></li>
        <li><a href="user_profile.php"><i class="fa-solid fa-user"></i> My Profile</a></li>
        <li><a href="user_wallet.php"><i class="fa-solid fa-wallet"></i> Wallet</a></li>
        <li><a href="user_investment.php"><i class="fa-solid fa-chart-line"></i> Investment</a></li>
        <li><a href="user_income.php"><i class="fa-solid fa-money-bill-wave"></i> Income</a></li>
        <li><a href="user_team.php"><i class="fa-solid fa-users"></i> My Team</a></li>
        <li><a href="withdraw.php"><i class="fa-solid fa-money-check-dollar"></i> Withdraw</a></li>
        <li><a href="transactions.php"><i class="fa-solid fa-clock-rotate-left"></i> Transactions</a></li>
        <li class="active"><a href="user_settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
        <li class="logout"><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">

    <!-- ===== HEADER ===== -->
    <div class="header">
        <div class="welcome">
            <h2><i class="fas fa-gear" style="color:#22c55e;"></i> Settings</h2>
            <p>Manage your account preferences and security</p>
        </div>
        <div class="profile">
            <?php 
            $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($row['name']) . "&background=22c55e&color=fff&size=55&bold=true";
            ?>
            <img src="<?php echo $avatar_url; ?>" alt="Profile">
        </div>
    </div>

    <!-- ===== ALERTS ===== -->
    <?php if($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $success_message; ?>
            <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <?php if($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error_message; ?>
            <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <!-- ===== TAB NAVIGATION ===== -->
    <div class="tab-nav">
        <button class="tab-btn <?php echo $active_tab == 'profile' ? 'active' : ''; ?>" onclick="switchTab('profile')">
            <i class="fas fa-user"></i> <span>Profile</span>
        </button>
        <button class="tab-btn <?php echo $active_tab == 'security' ? 'active' : ''; ?>" onclick="switchTab('security')">
            <i class="fas fa-lock"></i> <span>Security</span>
        </button>
        <button class="tab-btn <?php echo $active_tab == 'notifications' ? 'active' : ''; ?>" onclick="switchTab('notifications')">
            <i class="fas fa-bell"></i> <span>Notifications</span>
        </button>
        <button class="tab-btn <?php echo $active_tab == 'privacy' ? 'active' : ''; ?>" onclick="switchTab('privacy')">
            <i class="fas fa-shield-alt"></i> <span>Privacy</span>
        </button>
        <button class="tab-btn <?php echo $active_tab == 'account' ? 'active' : ''; ?>" onclick="switchTab('account')">
            <i class="fas fa-cog"></i> <span>Account</span>
        </button>
    </div>

    <!-- ========================================
    TAB 1: PROFILE SETTINGS
    ======================================== -->
    <div class="settings-card <?php echo $active_tab == 'profile' ? 'active' : ''; ?>" id="tab-profile">
        <div class="card-header">
            <div class="icon-box"><i class="fas fa-user-edit"></i></div>
            <div class="header-text">
                <h3>Profile Settings</h3>
                <p>Update your personal information</p>
            </div>
        </div>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name <span class="required">*</span></label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email Address <span class="required">*</span></label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($row['Email']); ?>" placeholder="Enter your email" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Mobile Number <span class="required">*</span></label>
                    <input type="text" name="mobile" value="<?php echo htmlspecialchars($row['mobile']); ?>" maxlength="10" placeholder="Enter 10-digit mobile" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-city"></i> City</label>
                    <input type="text" name="city" value="<?php echo isset($row['city']) ? htmlspecialchars($row['city']) : ''; ?>" placeholder="Enter your city">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-globe"></i> Country</label>
                    <input type="text" name="country" value="<?php echo isset($row['country']) ? htmlspecialchars($row['country']) : ''; ?>" placeholder="Enter your country">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-info-circle"></i> Bio</label>
                    <textarea name="bio" placeholder="Tell us about yourself"><?php echo isset($row['bio']) ? htmlspecialchars($row['bio']) : ''; ?></textarea>
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" name="update_profile" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Profile
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </form>
    </div>

    <!-- ========================================
    TAB 2: SECURITY SETTINGS
    ======================================== -->
    <div class="settings-card <?php echo $active_tab == 'security' ? 'active' : ''; ?>" id="tab-security">
        <div class="card-header">
            <div class="icon-box blue"><i class="fas fa-lock"></i></div>
            <div class="header-text">
                <h3>Security Settings</h3>
                <p>Update your account password</p>
            </div>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label><i class="fas fa-key"></i> Current Password <span class="required">*</span></label>
                <input type="password" name="current_password" placeholder="Enter your current password" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-key"></i> New Password <span class="required">*</span></label>
                    <input type="password" name="new_password" placeholder="Enter new password (min 8 characters)" required>
                    <div class="helper-text"><i class="fas fa-info-circle"></i> Password must be at least 8 characters</div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-check-circle"></i> Confirm Password <span class="required">*</span></label>
                    <input type="password" name="confirm_password" placeholder="Confirm your new password" required>
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" name="update_password" class="btn btn-primary">
                    <i class="fas fa-key"></i> Change Password
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </form>

        <div style="margin-top:25px;padding:20px;background:rgba(255,255,255,.05);border-radius:var(--radius-md);border:1px solid rgba(255,255,255,.08);">
            <h4 style="font-size:14px;font-weight:600;margin-bottom:10px;"><i class="fas fa-shield-alt" style="color:#22c55e;"></i> Security Tips</h4>
            <ul style="list-style:none;font-size:13px;color:#94a3b8;line-height:2;">
                <li><i class="fas fa-check-circle" style="color:#22c55e;font-size:12px;"></i> Use a strong password with at least 8 characters</li>
                <li><i class="fas fa-check-circle" style="color:#22c55e;font-size:12px;"></i> Include uppercase, lowercase, numbers and special characters</li>
                <li><i class="fas fa-check-circle" style="color:#22c55e;font-size:12px;"></i> Don't use the same password for multiple accounts</li>
                <li><i class="fas fa-check-circle" style="color:#22c55e;font-size:12px;"></i> Change your password regularly for better security</li>
            </ul>
        </div>
    </div>

    <!-- ========================================
    TAB 3: NOTIFICATION SETTINGS
    ======================================== -->
    <div class="settings-card <?php echo $active_tab == 'notifications' ? 'active' : ''; ?>" id="tab-notifications">
        <div class="card-header">
            <div class="icon-box purple"><i class="fas fa-bell"></i></div>
            <div class="header-text">
                <h3>Notification Settings</h3>
                <p>Choose how you want to receive notifications</p>
            </div>
        </div>

        <form method="POST" action="">
            <div class="toggle-group">
                <div class="toggle-info">
                    <h4><i class="fas fa-envelope" style="color:#22c55e;width:20px;"></i> Email Notifications</h4>
                    <p>Receive updates and alerts via email</p>
                </div>
                <label class="toggle <?php echo $email_notify ? 'active' : ''; ?>">
                    <input type="checkbox" name="email_notify" value="1" <?php echo $email_notify ? 'checked' : ''; ?> onchange="this.checked ? this.parentElement.classList.add('active') : this.parentElement.classList.remove('active')">
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="toggle-group">
                <div class="toggle-info">
                    <h4><i class="fas fa-sms" style="color:#22c55e;width:20px;"></i> SMS Notifications</h4>
                    <p>Receive updates via SMS on your phone</p>
                </div>
                <label class="toggle <?php echo $sms_notify ? 'active' : ''; ?>">
                    <input type="checkbox" name="sms_notify" value="1" <?php echo $sms_notify ? 'checked' : ''; ?> onchange="this.checked ? this.parentElement.classList.add('active') : this.parentElement.classList.remove('active')">
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="toggle-group">
                <div class="toggle-info">
                    <h4><i class="fas fa-mobile-alt" style="color:#22c55e;width:20px;"></i> Push Notifications</h4>
                    <p>Receive push notifications on your device</p>
                </div>
                <label class="toggle <?php echo $push_notify ? 'active' : ''; ?>">
                    <input type="checkbox" name="push_notify" value="1" <?php echo $push_notify ? 'checked' : ''; ?> onchange="this.checked ? this.parentElement.classList.add('active') : this.parentElement.classList.remove('active')">
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="toggle-group">
                <div class="toggle-info">
                    <h4><i class="fas fa-chart-line" style="color:#22c55e;width:20px;"></i> Investment Alerts</h4>
                    <p>Get notified about your investments</p>
                </div>
                <label class="toggle <?php echo $investment_alerts ? 'active' : ''; ?>">
                    <input type="checkbox" name="investment_alerts" value="1" <?php echo $investment_alerts ? 'checked' : ''; ?> onchange="this.checked ? this.parentElement.classList.add('active') : this.parentElement.classList.remove('active')">
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="toggle-group">
                <div class="toggle-info">
                    <h4><i class="fas fa-money-bill-wave" style="color:#22c55e;width:20px;"></i> Withdrawal Alerts</h4>
                    <p>Get notified about your withdrawals</p>
                </div>
                <label class="toggle <?php echo $withdrawal_alerts ? 'active' : ''; ?>">
                    <input type="checkbox" name="withdrawal_alerts" value="1" <?php echo $withdrawal_alerts ? 'checked' : ''; ?> onchange="this.checked ? this.parentElement.classList.add('active') : this.parentElement.classList.remove('active')">
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <button type="submit" name="update_notifications" class="btn btn-primary" style="margin-top:10px;">
                <i class="fas fa-save"></i> Save Notification Settings
            </button>
        </form>
    </div>

    <!-- ========================================
    TAB 4: PRIVACY SETTINGS
    ======================================== -->
    <div class="settings-card <?php echo $active_tab == 'privacy' ? 'active' : ''; ?>" id="tab-privacy">
        <div class="card-header">
            <div class="icon-box pink"><i class="fas fa-shield-alt"></i></div>
            <div class="header-text">
                <h3>Privacy Settings</h3>
                <p>Control who can see your information</p>
            </div>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label><i class="fas fa-eye"></i> Profile Visibility</label>
                <select name="profile_visibility">
                    <option value="public" <?php echo $profile_visibility == 'public' ? 'selected' : ''; ?>>Public - Everyone can see</option>
                    <option value="private" <?php echo $profile_visibility == 'private' ? 'selected' : ''; ?>>Private - Only you can see</option>
                    <option value="team" <?php echo $profile_visibility == 'team' ? 'selected' : ''; ?>>Team - Only team members can see</option>
                </select>
                <div class="helper-text"><i class="fas fa-info-circle"></i> Choose who can view your profile information</div>
            </div>

            <div class="toggle-group">
                <div class="toggle-info">
                    <h4><i class="fas fa-envelope" style="color:#22c55e;width:20px;"></i> Show Email</h4>
                    <p>Display your email address on your profile</p>
                </div>
                <label class="toggle <?php echo $show_email ? 'active' : ''; ?>">
                    <input type="checkbox" name="show_email" value="1" <?php echo $show_email ? 'checked' : ''; ?> onchange="this.checked ? this.parentElement.classList.add('active') : this.parentElement.classList.remove('active')">
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="toggle-group">
                <div class="toggle-info">
                    <h4><i class="fas fa-phone" style="color:#22c55e;width:20px;"></i> Show Phone Number</h4>
                    <p>Display your phone number on your profile</p>
                </div>
                <label class="toggle <?php echo $show_phone ? 'active' : ''; ?>">
                    <input type="checkbox" name="show_phone" value="1" <?php echo $show_phone ? 'checked' : ''; ?> onchange="this.checked ? this.parentElement.classList.add('active') : this.parentElement.classList.remove('active')">
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="toggle-group">
                <div class="toggle-info">
                    <h4><i class="fas fa-circle" style="color:#22c55e;width:20px;"></i> Activity Status</h4>
                    <p>Show when you're online and active</p>
                </div>
                <label class="toggle <?php echo $activity_status ? 'active' : ''; ?>">
                    <input type="checkbox" name="activity_status" value="1" <?php echo $activity_status ? 'checked' : ''; ?> onchange="this.checked ? this.parentElement.classList.add('active') : this.parentElement.classList.remove('active')">
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <button type="submit" name="update_privacy" class="btn btn-primary" style="margin-top:10px;">
                <i class="fas fa-save"></i> Save Privacy Settings
            </button>
        </form>
    </div>

    <!-- ========================================
    TAB 5: ACCOUNT SETTINGS
    ======================================== -->
    <div class="settings-card <?php echo $active_tab == 'account' ? 'active' : ''; ?>" id="tab-account">
        <div class="card-header">
            <div class="icon-box orange"><i class="fas fa-cog"></i></div>
            <div class="header-text">
                <h3>Account Settings</h3>
                <p>Manage your account and preferences</p>
            </div>
        </div>

        <div class="info-item">
            <span class="info-label"><i class="fas fa-user" style="color:#22c55e;width:20px;"></i> Account Status</span>
            <span class="info-value"><span class="badge badge-success">Active</span></span>
        </div>

        <div class="info-item">
            <span class="info-label"><i class="fas fa-calendar-alt" style="color:#22c55e;width:20px;"></i> Member Since</span>
            <span class="info-value"><?php echo isset($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : 'N/A'; ?></span>
        </div>

        <div class="info-item">
            <span class="info-label"><i class="fas fa-id-card" style="color:#22c55e;width:20px;"></i> Account ID</span>
            <span class="info-value" style="font-family:monospace;">#<?php echo str_pad($user_id, 6, '0', STR_PAD_LEFT); ?></span>
        </div>

        <div class="info-item">
            <span class="info-label"><i class="fas fa-code" style="color:#22c55e;width:20px;"></i> Referral Code</span>
            <span class="info-value" style="color:#22c55e;font-weight:700;font-family:monospace;">
                <?php 
                $ref_code = isset($row['referral_code']) ? $row['referral_code'] : 'REF' . str_pad($user_id, 6, '0', STR_PAD_LEFT);
                echo $ref_code; 
                ?>
            </span>
        </div>

        <div class="info-item">
            <span class="info-label"><i class="fas fa-users" style="color:#22c55e;width:20px;"></i> Team Members</span>
            <span class="info-value"><?php 
                $team_sql = "SELECT COUNT(*) as count FROM team WHERE referrer_id = '$user_id' AND status = 'active'";
                $team_result = mysqli_query($conn, $team_sql);
                $team_count = $team_result && mysqli_num_rows($team_result) > 0 ? mysqli_fetch_assoc($team_result)['count'] : 0;
                echo $team_count;
            ?></span>
        </div>

        <div style="margin-top:25px;padding-top:20px;border-top:1px solid rgba(255,255,255,.08);">
            <h4 style="font-size:15px;font-weight:600;color:#ff6b6b;margin-bottom:12px;">
                <i class="fas fa-exclamation-triangle"></i> Danger Zone
            </h4>
            <p style="font-size:13px;color:#94a3b8;margin-bottom:16px;">
                Once you delete your account, there is no going back. All your data will be permanently removed.
            </p>
            <button class="btn btn-danger" onclick="deleteAccount()">
                <i class="fas fa-trash-alt"></i> Delete Account
            </button>
        </div>
    </div>

</div>

<!-- ===== JAVASCRIPT ===== -->
<script>
    // ========================================
    // TOGGLE SIDEBAR
    // ========================================
    function toggleMenu() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
    }

    // Close sidebar on outside click (mobile)
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const menuBtn = document.querySelector('.menu-btn');
        
        if (window.innerWidth <= 991) {
            if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

    // ========================================
    // TAB SWITCHING
    // ========================================
    function switchTab(tab) {
        // Update URL without reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.pushState({}, '', url);

        // Hide all tabs
        document.querySelectorAll('.settings-card').forEach(el => {
            el.classList.remove('active');
        });

        // Show selected tab
        document.getElementById('tab-' + tab).classList.add('active');

        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('active');
        });

        document.querySelectorAll('.tab-btn').forEach(el => {
            if (el.textContent.toLowerCase().includes(tab)) {
                el.classList.add('active');
            }
        });
    }

    // ========================================
    // DELETE ACCOUNT CONFIRMATION
    // ========================================
    function deleteAccount() {
        if (confirm('⚠️ Are you sure you want to delete your account?\n\nThis action is IRREVERSIBLE and all your data will be permanently removed!')) {
            if (confirm('Type "DELETE" to confirm:')) {
                window.location.href = 'delete_account.php';
            }
        }
    }

    // ========================================
    // AUTO-HIDE ALERTS
    // ========================================
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        });
    }, 5000);
</script>

</body>
</html>