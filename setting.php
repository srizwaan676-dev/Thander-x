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

// Get total income (from investments table)
function getTotalIncome($conn) {
    // Check if table exists first
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
    // Check if table exists first
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

// Get recent users (limit 10)
function getRecentUsers($conn, $limit = 10) {
    $sql = "SELECT * FROM contact ORDER BY id DESC LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    if($result) {
        return $result;
    }
    return null;
}

// Get monthly income data for chart
function getMonthlyIncome($conn) {
    // Check if table exists first
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

// Get monthly users data for chart
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

// Get recent notifications
function getNotifications($conn) {
    $notifications = [];
    
    // Check for new users in last 24 hours
    $sql = "SELECT COUNT(*) as count FROM contact WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if($row['count'] > 0) {
            $notifications[] = "✅ " . $row['count'] . " new user(s) registered in last 24 hours.";
        }
    }
    
    // Check if investments table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'investments'");
    if($table_check && mysqli_num_rows($table_check) > 0) {
        // Check for new investments
        $sql = "SELECT COUNT(*) as count FROM investments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $result = mysqli_query($conn, $sql);
        if($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if($row['count'] > 0) {
                $notifications[] = "💰 " . $row['count'] . " new investment(s) received.";
            }
        }
    }
    
    // Check if products table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'products'");
    if($table_check && mysqli_num_rows($table_check) > 0) {
        // Check for new products
        $sql = "SELECT COUNT(*) as count FROM products WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $result = mysqli_query($conn, $sql);
        if($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if($row['count'] > 0) {
                $notifications[] = "📦 " . $row['count'] . " new product(s) added.";
            }
        }
    }
    
    if(empty($notifications)) {
        $notifications[] = "🔔 No new notifications. Everything is up to date!";
    }
    
    return $notifications;
}

// ========================================
// SETTINGS FUNCTIONS
// ========================================

// Get all settings
function getSettings($conn) {
    $settings = [];
    
    // Check if settings table exists first
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
    if(!$table_check || mysqli_num_rows($table_check) == 0) {
        return getDefaultSettings();
    }
    
    $sql = "SELECT * FROM settings";
    $result = mysqli_query($conn, $sql);
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    // Merge with defaults for any missing keys
    $defaults = getDefaultSettings();
    foreach($defaults as $key => $value) {
        if(!isset($settings[$key])) {
            $settings[$key] = $value;
        }
    }
    
    return $settings;
}

// Update settings
function updateSettings($conn, $settings) {
    // Check if settings table exists, if not create it
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
    if(!$table_check || mysqli_num_rows($table_check) == 0) {
        // Create settings table
        $create_sql = "CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        if(!mysqli_query($conn, $create_sql)) {
            return false;
        }
    }
    
    foreach($settings as $key => $value) {
        $key = mysqli_real_escape_string($conn, $key);
        $value = mysqli_real_escape_string($conn, $value);
        
        // Check if setting exists
        $check = mysqli_query($conn, "SELECT * FROM settings WHERE setting_key = '$key'");
        if($check && mysqli_num_rows($check) > 0) {
            $sql = "UPDATE settings SET setting_value = '$value' WHERE setting_key = '$key'";
        } else {
            $sql = "INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value')";
        }
        
        if(!mysqli_query($conn, $sql)) {
            return false;
        }
    }
    return true;
}

// Get default settings
function getDefaultSettings() {
    return [
        'site_name' => 'IncomeCoin',
        'site_url' => 'https://incomecoin.example.com',
        'timezone' => 'Asia/Kolkata',
        'currency' => '₹',
        'platform_fee' => '2.5',
        'min_withdrawal' => '100',
        'max_withdrawal' => '50000',
        'withdrawal_processing' => '24-48 Hours',
        'registration' => 'open',
        'email_verification' => 'required',
        '2fa_enabled' => '1',
        'session_timeout' => '1',
        'ssl_enforcement' => '1',
        'password_policy' => 'strong',
        'password_expiry' => '90',
        'theme' => 'dark',
        'primary_color' => '#2ebf3f',
        'font_family' => 'Poppins'
    ];
}

// ========================================
// PROCESS SETTINGS FORM
// ========================================
$settings_success = '';
$settings_error = '';

// Get settings (will return defaults if table doesn't exist)
$settings = getSettings($conn);

// Handle form submission
if(isset($_POST['save_settings'])) {
    $updated_settings = [
        'site_name' => mysqli_real_escape_string($conn, $_POST['site_name'] ?? 'IncomeCoin'),
        'site_url' => mysqli_real_escape_string($conn, $_POST['site_url'] ?? 'https://incomecoin.example.com'),
        'timezone' => mysqli_real_escape_string($conn, $_POST['timezone'] ?? 'Asia/Kolkata'),
        'currency' => mysqli_real_escape_string($conn, $_POST['currency'] ?? '₹'),
        'platform_fee' => mysqli_real_escape_string($conn, $_POST['platform_fee'] ?? '2.5'),
        'min_withdrawal' => mysqli_real_escape_string($conn, $_POST['min_withdrawal'] ?? '100'),
        'max_withdrawal' => mysqli_real_escape_string($conn, $_POST['max_withdrawal'] ?? '50000'),
        'withdrawal_processing' => mysqli_real_escape_string($conn, $_POST['withdrawal_processing'] ?? '24-48 Hours'),
        'registration' => mysqli_real_escape_string($conn, $_POST['registration'] ?? 'open'),
        'email_verification' => mysqli_real_escape_string($conn, $_POST['email_verification'] ?? 'required'),
        '2fa_enabled' => isset($_POST['2fa_enabled']) ? '1' : '0',
        'session_timeout' => isset($_POST['session_timeout']) ? '1' : '0',
        'ssl_enforcement' => isset($_POST['ssl_enforcement']) ? '1' : '0',
        'password_policy' => mysqli_real_escape_string($conn, $_POST['password_policy'] ?? 'strong'),
        'password_expiry' => mysqli_real_escape_string($conn, $_POST['password_expiry'] ?? '90'),
        'theme' => mysqli_real_escape_string($conn, $_POST['theme'] ?? 'dark'),
        'primary_color' => mysqli_real_escape_string($conn, $_POST['primary_color'] ?? '#2ebf3f'),
        'font_family' => mysqli_real_escape_string($conn, $_POST['font_family'] ?? 'Poppins')
    ];
    
    if(updateSettings($conn, $updated_settings)) {
        $settings_success = 'Settings updated successfully!';
        $settings = getSettings($conn); // Refresh settings
    } else {
        $settings_error = 'Failed to update settings. Please try again!';
    }
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

// Safe array access helper
function safe_val($array, $key, $default = '') {
    return isset($array[$key]) ? $array[$key] : $default;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | IncomeCoin Dashboard</title>

    <style>
    /* Google Font */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        background: #07111f;
        color: #fff;
    }

    .container {
        display: flex;
        min-height: 100vh;
    }

    /* ==========================
    SIDEBAR
    ========================== */

    .sidebar {
        width: 270px;
        background: #081521;
        border-right: 1px solid rgba(255, 255, 255, .08);
        position: fixed;
        height: 100%;
        padding: 20px;
        overflow-y: auto;
        z-index: 1000;
        transition: .3s;
    }
    
    .logo {
        display: flex;
        align-items: center;
        gap: 1px;
        margin-bottom: 35px;
    }

    .logo img {
        width: 60px;
        border-radius: 50%;
    }

    .logo h2 {
        color: #fff;
        font-size: 22px;
    }

    .logo span {
        color: #f9c63d;
    }

    .logo p {
        color: #aaa;
        font-size: 14px;
    }

    .sidebar ul {
        list-style: none;
    }

    .sidebar ul li {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px 18px;
        margin-bottom: 10px;
        border-radius: 14px;
        cursor: pointer;
        transition: .3s;
        color: #ddd;
    }

    .sidebar ul li:hover {
        background: #15324d;
        color: #fff;
    }

    .sidebar .active {
        background: linear-gradient(90deg, #2ebf3f, #66ff66);
        color: #fff;
    }

    .sidebar .logout {
        color: #ff5959;
    }

    .sidebar ul li a {
        display: flex;
        align-items: center;
        gap: 15px;
        width: 100%;
        color: #ddd;
        text-decoration: none;
    }

    .sidebar ul li:hover a {
        color: #fff;
    }

    /* ==========================
    MAIN
    ========================== */

    .main {
        flex: 1;
        margin-left: 270px;
        padding: 20px 25px;
        overflow: hidden;
        width: calc(100% - 270px);
        transition: .3s;
    }

    /* ==========================
    NAVBAR
    ========================== */

    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }

    #menu-btn {
        width: 45px;
        height: 45px;
        border: none;
        border-radius: 10px;
        background: #1bb53b;
        color: #fff;
        cursor: pointer;
        font-size: 18px;
        display: none;
    }

    .search {
        width: 45%;
        position: relative;
    }

    .search input {
        width: 100%;
        background: #101d2f;
        border: none;
        color: #fff;
        padding: 14px 45px 14px 20px;
        border-radius: 12px;
        outline: none;
    }

    .search input::placeholder {
        color: #666;
    }

    .search i {
        position: absolute;
        right: 18px;
        top: 16px;
        color: #666;
    }

    .top-right {
        display: flex;
        align-items: center;
        gap: 25px;
    }

    .top-right i {
        font-size: 20px;
        cursor: pointer;
        transition: .3s;
    }

    .top-right i:hover {
        color: #2ebf3f;
    }

    .profile {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profile img {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
    }

    .profile h4 {
        font-size: 14px;
    }

    .profile span {
        font-size: 12px;
        color: #888;
    }

    /* ==========================
    PAGE HEADER
    ========================== */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .page-header h1 {
        font-size: 28px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-header h1 i {
        color: #2ebf3f;
    }

    .page-header p {
        color: #bbb;
        font-size: 14px;
        margin-top: 4px;
    }

    .save-btn {
        background: linear-gradient(90deg, #2ebf3f, #66ff66);
        color: #fff;
        border: none;
        padding: 12px 32px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: .3s;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 20px rgba(46, 191, 63, 0.25);
    }

    .save-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(46, 191, 63, 0.4);
    }

    /* ==========================
    SETTINGS GRID
    ========================== */
    .settings-grid {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 25px;
        margin-bottom: 30px;
    }

    /* ==========================
    SIDEBAR NAV
    ========================== */
    .settings-sidebar {
        background: #101d2f;
        border-radius: 16px;
        padding: 16px 12px;
        border: 1px solid rgba(255,255,255,0.08);
        align-self: start;
        position: sticky;
        top: 20px;
    }

    .settings-sidebar .sidebar-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #888;
        padding: 8px 14px 6px;
    }

    .settings-sidebar a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border-radius: 10px;
        text-decoration: none;
        color: #bbb;
        font-weight: 500;
        font-size: 14px;
        transition: .2s;
    }

    .settings-sidebar a i {
        width: 20px;
        text-align: center;
        color: #888;
        font-size: 15px;
    }

    .settings-sidebar a:hover {
        background: #1a2a3f;
        color: #fff;
    }

    .settings-sidebar a:hover i {
        color: #2ebf3f;
    }

    .settings-sidebar a.active {
        background: linear-gradient(90deg, #2ebf3f, #66ff66);
        color: #fff;
    }

    .settings-sidebar a.active i {
        color: #fff;
    }

    .sidebar-divider {
        height: 1px;
        background: rgba(255,255,255,0.08);
        margin: 8px 14px;
    }

    /* ==========================
    SETTINGS MAIN
    ========================== */
    .settings-main {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* ==========================
    SETTING CARDS
    ========================== */
    .setting-card {
        background: #101d2f;
        border-radius: 16px;
        padding: 25px 28px;
        border: 1px solid rgba(255,255,255,0.08);
    }

    .setting-card .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .setting-card .card-header h3 {
        font-size: 17px;
        font-weight: 600;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .setting-card .card-header h3 i {
        color: #2ebf3f;
    }

    .setting-card .card-header .subtitle {
        font-size: 13px;
        color: #888;
        font-weight: 400;
    }

    .badge-status {
        background: #1a3a2a;
        color: #66ff66;
        padding: 4px 16px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid rgba(46, 191, 63, 0.2);
    }

    .badge-status.off {
        background: #3a1a1a;
        color: #ff6666;
        border-color: rgba(255, 68, 68, 0.2);
    }

    /* ==========================
    FORM GROUPS
    ========================== */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 14px;
    }

    .form-row.three {
        grid-template-columns: 1fr 1fr 1fr;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-group label {
        font-weight: 500;
        font-size: 13px;
        color: #ccc;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-group label i {
        color: #888;
        font-size: 13px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 10px 14px;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        font-size: 14px;
        background: #0a1628;
        transition: .2s;
        color: #fff;
        font-family: 'Poppins', sans-serif;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #2ebf3f;
        box-shadow: 0 0 0 3px rgba(46, 191, 63, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }

    .form-group .help-text {
        font-size: 12px;
        color: #666;
        margin-top: 2px;
    }

    .form-group input[type="color"] {
        padding: 4px;
        height: 48px;
        cursor: pointer;
        width: 80px;
    }

    /* ==========================
    TOGGLE SWITCH
    ========================== */
    .toggle-group {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 4px 0;
    }

    .toggle {
        position: relative;
        width: 48px;
        height: 26px;
        background: #2a3a4a;
        border-radius: 40px;
        cursor: pointer;
        transition: .25s;
        flex-shrink: 0;
    }

    .toggle.active {
        background: #2ebf3f;
    }

    .toggle .toggle-dot {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        background: #fff;
        border-radius: 50%;
        transition: .25s;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    .toggle.active .toggle-dot {
        left: 25px;
    }

    .toggle-label {
        font-weight: 500;
        font-size: 14px;
        color: #ddd;
    }

    .toggle-desc {
        font-size: 12px;
        color: #666;
    }

    /* ==========================
    COLOR PICKER
    ========================== */
    .color-picker-group {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .color-option {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 3px solid transparent;
        cursor: pointer;
        transition: .15s;
    }

    .color-option:hover {
        transform: scale(1.08);
    }

    .color-option.active {
        border-color: #fff;
        box-shadow: 0 0 0 2px #2ebf3f;
    }

    .color-option input[type="radio"] {
        display: none;
    }

    /* ==========================
    ALERT
    ========================== */
    .alert {
        padding: 12px 18px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        animation: slideDown 0.35s ease;
        margin-bottom: 20px;
    }

    @keyframes slideDown {
        0% {
            opacity: 0;
            transform: translateY(-10px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background: rgba(46, 191, 63, 0.15);
        color: #66ff66;
        border-left: 4px solid #2ebf3f;
        border: 1px solid rgba(46, 191, 63, 0.2);
    }

    .alert-error {
        background: rgba(255, 68, 68, 0.15);
        color: #ff6666;
        border-left: 4px solid #ff4444;
        border: 1px solid rgba(255, 68, 68, 0.2);
    }

    .alert i {
        font-size: 18px;
    }

    /* ==========================
    FOOTER
    ========================== */
    footer {
        margin-top: 35px;
        background: #09141f;
        border: 1px solid rgba(255, 196, 0, .35);
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        color: #bbb;
        font-size: 14px;
    }

    /* ==========================
    RESPONSIVE
    ========================== */
    @media(max-width:1100px) {
        .settings-grid {
            grid-template-columns: 1fr;
        }
        .settings-sidebar {
            position: static;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            padding: 12px 16px;
        }
        .settings-sidebar .sidebar-label,
        .sidebar-divider {
            display: none;
        }
        .settings-sidebar a {
            padding: 8px 14px;
            font-size: 13px;
        }
    }

    @media(max-width:992px) {
        .sidebar {
            left: -270px;
        }
        .sidebar.active {
            left: 0;
        }
        .main {
            margin-left: 0;
            width: 100%;
        }
        #menu-btn {
            display: block;
        }
        .search {
            width: 50%;
        }
        .form-row {
            grid-template-columns: 1fr;
        }
        .form-row.three {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width:768px) {
        body {
            overflow-x: hidden;
        }
        .navbar {
            flex-wrap: wrap;
        }
        .search {
            display: none;
        }
        .top-right {
            margin-left: auto;
        }
        .profile h4,
        .profile span {
            display: none;
        }
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .page-header h1 {
            font-size: 22px;
        }
        .save-btn {
            width: 100%;
            justify-content: center;
        }
        .setting-card {
            padding: 18px 16px;
        }
        .settings-sidebar {
            overflow-x: auto;
            flex-wrap: nowrap;
            padding: 10px 14px;
            gap: 2px;
        }
        .settings-sidebar a {
            white-space: nowrap;
            font-size: 12px;
            padding: 6px 12px;
        }
        .setting-card .card-header {
            flex-direction: column;
            align-items: flex-start;
        }
        footer {
            font-size: 12px;
            padding: 15px;
        }
    }

    @media(max-width:480px) {
        .main {
            padding: 15px;
        }
        #menu-btn {
            width: 40px;
            height: 40px;
        }
        .top-right {
            gap: 15px;
        }
        .profile img {
            width: 35px;
            height: 35px;
        }
        .page-header h1 {
            font-size: 18px;
        }
        .form-group input,
        .form-group select {
            font-size: 13px;
            padding: 8px 12px;
        }
    }

    /* ==========================
    LIGHT MODE
    ========================== */
    body.light-mode {
        background: #f0f2f5;
        color: #1a1a2e;
    }

    body.light-mode .sidebar {
        background: #ffffff;
        border-right: 1px solid #ddd;
    }

    body.light-mode .logo h2 {
        color: #1a1a2e;
    }

    body.light-mode .sidebar ul li {
        color: #333;
    }

    body.light-mode .sidebar ul li:hover {
        background: #e8f0fe;
    }

    body.light-mode .setting-card,
    body.light-mode .settings-sidebar {
        background: #ffffff;
        border-color: #ddd;
    }

    body.light-mode .settings-sidebar a {
        color: #555;
    }

    body.light-mode .settings-sidebar a:hover {
        background: #f0f2f5;
        color: #1a1a2e;
    }

    body.light-mode .settings-sidebar a.active {
        background: linear-gradient(90deg, #2ebf3f, #66ff66);
        color: #fff;
    }

    body.light-mode .setting-card .card-header h3 {
        color: #1a1a2e;
    }

    body.light-mode .form-group label {
        color: #444;
    }

    body.light-mode .form-group input,
    body.light-mode .form-group select,
    body.light-mode .form-group textarea {
        background: #f0f2f5;
        border-color: #ddd;
        color: #1a1a2e;
    }

    body.light-mode .form-group input:focus,
    body.light-mode .form-group select:focus,
    body.light-mode .form-group textarea:focus {
        border-color: #2ebf3f;
    }

    body.light-mode .badge-status {
        background: #e8f5e9;
        color: #2e7d32;
    }

    body.light-mode .badge-status.off {
        background: #fce4ec;
        color: #c62828;
    }

    body.light-mode .toggle {
        background: #ccc;
    }

    body.light-mode .toggle.active {
        background: #2ebf3f;
    }

    body.light-mode .toggle-label {
        color: #333;
    }

    body.light-mode .page-header {
        border-bottom-color: #ddd;
    }

    body.light-mode .page-header p {
        color: #666;
    }

    body.light-mode .alert-success {
        background: #e8f5e9;
        color: #2e7d32;
        border-color: #a5d6a7;
    }

    body.light-mode .alert-error {
        background: #fce4ec;
        color: #c62828;
        border-color: #ef9a9a;
    }

    body.light-mode .search input {
        background: #f0f2f5;
        color: #1a1a2e;
    }

    body.light-mode .search input::placeholder {
        color: #999;
    }

    body.light-mode footer {
        background: #ffffff;
        border-color: #ddd;
        color: #666;
    }

    body.light-mode .profile span {
        color: #888;
    }

    body.light-mode .sidebar .logout {
        color: #d32f2f;
    }

    body.light-mode .sidebar .logout a {
        color: #d32f2f;
    }

    body.light-mode .color-option.active {
        border-color: #1a1a2e;
        box-shadow: 0 0 0 2px #2ebf3f;
    }

    body.light-mode .form-group .help-text {
        color: #999;
    }

    body.light-mode .toggle-desc {
        color: #999;
    }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>

    <div class="container">

        <!-- Sidebar -->
        <aside class="sidebar">

            <div class="logo">
                <img src="image/images-0.jpg" alt="Logo">
                <div>
                    <h2>Income<span>Coin</span></h2>
                    <p>Admin Panel</p>
                </div>
            </div>

            <ul>
                <li>
                    <a href="dashboard.php">
                        <i class="fa-solid fa-table-columns"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="admin.php">
                        <i class="fa-solid fa-users"></i>
                        <span>Members</span>
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
                    </a>
                </li>
                <li>
                    <a href="view_product.php">
                        <i class="fa-solid fa-box-open"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li>
                    <a href="income.php">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Income Report</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fa-solid fa-file-lines"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="active">
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

        <!-- Main -->
        <main class="main">

            <!-- Navbar -->
            <header class="navbar">

                <button id="menu-btn">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <div class="search">
                    <input type="text" placeholder="Search here...">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>

                <div class="top-right">

                    <i class="fa-regular fa-bell"></i>
                    <i class="fa-solid fa-moon" id="themeToggle"></i>

                    <div class="profile">
                        <img src="image/images-0.jpg" alt="Admin">
                        <div>
                            <h4>Admin</h4>
                            <span>Super Admin</span>
                        </div>
                    </div>

                </div>

            </header>

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1><i class="fa-solid fa-gear"></i> Settings</h1>
                    <p>Manage your platform configuration and preferences</p>
                </div>
                <form method="POST" style="display:inline;">
                    <button type="submit" name="save_settings" class="save-btn">
                        <i class="fa-solid fa-save"></i> Save Changes
                    </button>
                </form>
            </div>

            <!-- Alert Messages -->
            <?php if($settings_success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <?php echo $settings_success; ?>
                </div>
            <?php endif; ?>

            <?php if($settings_error): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <?php echo $settings_error; ?>
                </div>
            <?php endif; ?>

            <!-- Settings Grid -->
            <div class="settings-grid">

                <!-- Sidebar Navigation -->
                <div class="settings-sidebar">
                    <div class="sidebar-label">Settings</div>
                    <a href="#general" class="active"><i class="fa-solid fa-sliders-h"></i> General</a>
                    <a href="#payment"><i class="fa-solid fa-wallet"></i> Payment</a>
                    <a href="#security"><i class="fa-solid fa-shield-alt"></i> Security</a>
                    <a href="#appearance"><i class="fa-solid fa-palette"></i> Appearance</a>
                    <div class="sidebar-divider"></div>
                    <a href="#backup"><i class="fa-solid fa-database"></i> Backup</a>
                    <a href="#api"><i class="fa-solid fa-code"></i> API</a>
                </div>

                <!-- Main Content -->
                <div class="settings-main">

                    <form method="POST" action="">

                        <!-- ===== GENERAL SETTINGS ===== -->
                        <div class="setting-card" id="general">
                            <div class="card-header">
                                <h3><i class="fa-solid fa-sliders-h"></i> General Settings</h3>
                                <span class="badge-status">Live</span>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fa-solid fa-globe"></i> Site Name</label>
                                    <input type="text" name="site_name" value="<?php echo htmlspecialchars(safe_val($settings, 'site_name', 'IncomeCoin')); ?>" />
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-solid fa-link"></i> Site URL</label>
                                    <input type="text" name="site_url" value="<?php echo htmlspecialchars(safe_val($settings, 'site_url', 'https://incomecoin.example.com')); ?>" />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fa-regular fa-clock"></i> Timezone</label>
                                    <select name="timezone">
                                        <option value="Asia/Kolkata" <?php echo (safe_val($settings, 'timezone') == 'Asia/Kolkata') ? 'selected' : ''; ?>>Asia/Kolkata (UTC +5:30)</option>
                                        <option value="America/New_York" <?php echo (safe_val($settings, 'timezone') == 'America/New_York') ? 'selected' : ''; ?>>America/New_York (UTC -5:00)</option>
                                        <option value="Europe/London" <?php echo (safe_val($settings, 'timezone') == 'Europe/London') ? 'selected' : ''; ?>>Europe/London (UTC +0:00)</option>
                                        <option value="Asia/Dubai" <?php echo (safe_val($settings, 'timezone') == 'Asia/Dubai') ? 'selected' : ''; ?>>Asia/Dubai (UTC +4:00)</option>
                                        <option value="Australia/Sydney" <?php echo (safe_val($settings, 'timezone') == 'Australia/Sydney') ? 'selected' : ''; ?>>Australia/Sydney (UTC +10:00)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-solid fa-language"></i> Currency</label>
                                    <select name="currency">
                                        <option value="₹" <?php echo (safe_val($settings, 'currency') == '₹') ? 'selected' : ''; ?>>₹ INR</option>
                                        <option value="$" <?php echo (safe_val($settings, 'currency') == '$') ? 'selected' : ''; ?>>$ USD</option>
                                        <option value="€" <?php echo (safe_val($settings, 'currency') == '€') ? 'selected' : ''; ?>>€ EUR</option>
                                        <option value="£" <?php echo (safe_val($settings, 'currency') == '£') ? 'selected' : ''; ?>>£ GBP</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fa-solid fa-users"></i> Registration</label>
                                    <select name="registration">
                                        <option value="open" <?php echo (safe_val($settings, 'registration') == 'open') ? 'selected' : ''; ?>>Open</option>
                                        <option value="invite" <?php echo (safe_val($settings, 'registration') == 'invite') ? 'selected' : ''; ?>>Invite Only</option>
                                        <option value="closed" <?php echo (safe_val($settings, 'registration') == 'closed') ? 'selected' : ''; ?>>Closed</option>
                                    </select>
                                    <span class="help-text">Allow new user registrations</span>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-solid fa-user-check"></i> Email Verification</label>
                                    <select name="email_verification">
                                        <option value="required" <?php echo (safe_val($settings, 'email_verification') == 'required') ? 'selected' : ''; ?>>Required</option>
                                        <option value="optional" <?php echo (safe_val($settings, 'email_verification') == 'optional') ? 'selected' : ''; ?>>Optional</option>
                                        <option value="disabled" <?php echo (safe_val($settings, 'email_verification') == 'disabled') ? 'selected' : ''; ?>>Disabled</option>
                                    </select>
                                    <span class="help-text">Require email verification for new accounts</span>
                                </div>
                            </div>
                        </div>

                        <!-- ===== PAYMENT SETTINGS ===== -->
                        <div class="setting-card" id="payment">
                            <div class="card-header">
                                <h3><i class="fa-solid fa-wallet"></i> Payment Settings</h3>
                                <span class="badge-status">Active</span>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fa-solid fa-percent"></i> Platform Fee (%)</label>
                                    <input type="number" step="0.5" name="platform_fee" value="<?php echo htmlspecialchars(safe_val($settings, 'platform_fee', '2.5')); ?>" />
                                    <span class="help-text">Fee charged per transaction</span>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-solid fa-arrow-down"></i> Min Withdrawal</label>
                                    <input type="number" name="min_withdrawal" value="<?php echo htmlspecialchars(safe_val($settings, 'min_withdrawal', '100')); ?>" />
                                    <span class="help-text">Minimum withdrawal amount (<?php echo htmlspecialchars(safe_val($settings, 'currency', '₹')); ?>)</span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fa-solid fa-arrow-up"></i> Max Withdrawal</label>
                                    <input type="number" name="max_withdrawal" value="<?php echo htmlspecialchars(safe_val($settings, 'max_withdrawal', '50000')); ?>" />
                                    <span class="help-text">Maximum withdrawal amount (<?php echo htmlspecialchars(safe_val($settings, 'currency', '₹')); ?>)</span>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-regular fa-clock"></i> Processing Time</label>
                                    <select name="withdrawal_processing">
                                        <option value="24-48 Hours" <?php echo (safe_val($settings, 'withdrawal_processing') == '24-48 Hours') ? 'selected' : ''; ?>>24-48 Hours</option>
                                        <option value="12-24 Hours" <?php echo (safe_val($settings, 'withdrawal_processing') == '12-24 Hours') ? 'selected' : ''; ?>>12-24 Hours</option>
                                        <option value="1-3 Business Days" <?php echo (safe_val($settings, 'withdrawal_processing') == '1-3 Business Days') ? 'selected' : ''; ?>>1-3 Business Days</option>
                                        <option value="Instant" <?php echo (safe_val($settings, 'withdrawal_processing') == 'Instant') ? 'selected' : ''; ?>>Instant</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- ===== SECURITY SETTINGS ===== -->
                        <div class="setting-card" id="security">
                            <div class="card-header">
                                <h3><i class="fa-solid fa-shield-alt"></i> Security</h3>
                                <span class="badge-status <?php echo (safe_val($settings, '2fa_enabled', '1') == '1') ? '' : 'off'; ?>">
                                    <?php echo (safe_val($settings, '2fa_enabled', '1') == '1') ? 'Secure' : 'Insecure'; ?>
                                </span>
                            </div>

                            <div class="form-row">
                                <div class="form-group full">
                                    <div class="toggle-group">
                                        <div class="toggle <?php echo (safe_val($settings, '2fa_enabled', '1') == '1') ? 'active' : ''; ?>" onclick="toggleSwitch(this)">
                                            <div class="toggle-dot"></div>
                                        </div>
                                        <div>
                                            <div class="toggle-label">Two-Factor Authentication</div>
                                            <div class="toggle-desc">Require 2FA for all admin accounts</div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="2fa_enabled" value="<?php echo (safe_val($settings, '2fa_enabled', '1') == '1') ? '1' : '0'; ?>" />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group full">
                                    <div class="toggle-group">
                                        <div class="toggle <?php echo (safe_val($settings, 'session_timeout', '1') == '1') ? 'active' : ''; ?>" onclick="toggleSwitch(this)">
                                            <div class="toggle-dot"></div>
                                        </div>
                                        <div>
                                            <div class="toggle-label">Session Timeout</div>
                                            <div class="toggle-desc">Automatically log out inactive users after 30 minutes</div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="session_timeout" value="<?php echo (safe_val($settings, 'session_timeout', '1') == '1') ? '1' : '0'; ?>" />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group full">
                                    <div class="toggle-group">
                                        <div class="toggle <?php echo (safe_val($settings, 'ssl_enforcement', '1') == '1') ? 'active' : ''; ?>" onclick="toggleSwitch(this)">
                                            <div class="toggle-dot"></div>
                                        </div>
                                        <div>
                                            <div class="toggle-label">SSL Enforcement</div>
                                            <div class="toggle-desc">Force HTTPS for all connections</div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="ssl_enforcement" value="<?php echo (safe_val($settings, 'ssl_enforcement', '1') == '1') ? '1' : '0'; ?>" />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fa-solid fa-key"></i> Password Policy</label>
                                    <select name="password_policy">
                                        <option value="strong" <?php echo (safe_val($settings, 'password_policy') == 'strong') ? 'selected' : ''; ?>>Strong (8+ chars, mixed case, numbers, symbols)</option>
                                        <option value="medium" <?php echo (safe_val($settings, 'password_policy') == 'medium') ? 'selected' : ''; ?>>Medium (8+ chars, mixed case, numbers)</option>
                                        <option value="basic" <?php echo (safe_val($settings, 'password_policy') == 'basic') ? 'selected' : ''; ?>>Basic (6+ chars)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-regular fa-clock"></i> Password Expiry</label>
                                    <select name="password_expiry">
                                        <option value="never" <?php echo (safe_val($settings, 'password_expiry') == 'never') ? 'selected' : ''; ?>>Never</option>
                                        <option value="90" <?php echo (safe_val($settings, 'password_expiry') == '90') ? 'selected' : ''; ?>>90 Days</option>
                                        <option value="180" <?php echo (safe_val($settings, 'password_expiry') == '180') ? 'selected' : ''; ?>>180 Days</option>
                                        <option value="365" <?php echo (safe_val($settings, 'password_expiry') == '365') ? 'selected' : ''; ?>>1 Year</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- ===== APPEARANCE ===== -->
                        <div class="setting-card" id="appearance">
                            <div class="card-header">
                                <h3><i class="fa-solid fa-palette"></i> Appearance</h3>
                                <span class="badge-status">Custom</span>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fa-solid fa-paint-bucket"></i> Primary Color</label>
                                    <div class="color-picker-group">
                                        <?php 
                                        $colors = ['#2ebf3f', '#2563eb', '#8b5cf6', '#ec4899', '#f59e0b', '#ef4444'];
                                        $selected_color = safe_val($settings, 'primary_color', '#2ebf3f');
                                        foreach($colors as $color): 
                                        ?>
                                        <label class="color-option <?php echo $selected_color == $color ? 'active' : ''; ?>" style="background:<?php echo $color; ?>;">
                                            <input type="radio" name="primary_color" value="<?php echo $color; ?>" <?php echo $selected_color == $color ? 'checked' : ''; ?> />
                                        </label>
                                        <?php endforeach; ?>
                                        <input type="color" name="primary_color_custom" value="<?php echo $selected_color; ?>" style="width:38px;height:38px;border:none;padding:0;border-radius:50%;cursor:pointer;background:transparent;" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-regular fa-moon"></i> Default Theme</label>
                                    <select name="theme">
                                        <option value="dark" <?php echo (safe_val($settings, 'theme') == 'dark') ? 'selected' : ''; ?>>Dark</option>
                                        <option value="light" <?php echo (safe_val($settings, 'theme') == 'light') ? 'selected' : ''; ?>>Light</option>
                                        <option value="system" <?php echo (safe_val($settings, 'theme') == 'system') ? 'selected' : ''; ?>>System Default</option>
                                    </select>
                                    <span class="help-text">Default theme for new users</span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fa-solid fa-font"></i> Font Family</label>
                                    <select name="font_family">
                                        <option value="Poppins" <?php echo (safe_val($settings, 'font_family') == 'Poppins') ? 'selected' : ''; ?>>Poppins</option>
                                        <option value="Inter" <?php echo (safe_val($settings, 'font_family') == 'Inter') ? 'selected' : ''; ?>>Inter</option>
                                        <option value="Roboto" <?php echo (safe_val($settings, 'font_family') == 'Roboto') ? 'selected' : ''; ?>>Roboto</option>
                                        <option value="Open Sans" <?php echo (safe_val($settings, 'font_family') == 'Open Sans') ? 'selected' : ''; ?>>Open Sans</option>
                                        <option value="system" <?php echo (safe_val($settings, 'font_family') == 'system') ? 'selected' : ''; ?>>System Default</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-solid fa-text-height"></i> Font Size</label>
                                    <select>
                                        <option selected>Medium (16px)</option>
                                        <option>Small (14px)</option>
                                        <option>Large (18px)</option>
                                    </select>
                                    <span class="help-text">Default font size for content</span>
                                </div>
                            </div>
                        </div>

                        <!-- ===== BACKUP ===== -->
                        <div class="setting-card" id="backup">
                            <div class="card-header">
                                <h3><i class="fa-solid fa-database"></i> Backup</h3>
                                <span class="badge-status">Auto</span>
                            </div>

                            <div class="form-row">
                                <div class="form-group full">
                                    <div class="toggle-group">
                                        <div class="toggle active" onclick="toggleSwitch(this)">
                                            <div class="toggle-dot"></div>
                                        </div>
                                        <div>
                                            <div class="toggle-label">Automatic Database Backup</div>
                                            <div class="toggle-desc">Automatically backup database every 24 hours</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fa-regular fa-clock"></i> Backup Frequency</label>
                                    <select>
                                        <option selected>Daily</option>
                                        <option>Weekly</option>
                                        <option>Monthly</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-regular fa-folder"></i> Backup Location</label>
                                    <select>
                                        <option selected>Server</option>
                                        <option>External Storage</option>
                                        <option>Cloud Storage</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group full">
                                    <button type="button" class="save-btn" style="background:linear-gradient(90deg,#f59e0b,#fbbf24);width:auto;padding:10px 24px;font-size:13px;">
                                        <i class="fa-solid fa-download"></i> Backup Now
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ===== API ===== -->
                        <div class="setting-card" id="api">
                            <div class="card-header">
                                <h3><i class="fa-solid fa-code"></i> API Settings</h3>
                                <span class="badge-status">Enabled</span>
                            </div>

                            <div class="form-row">
                                <div class="form-group full">
                                    <div class="toggle-group">
                                        <div class="toggle active" onclick="toggleSwitch(this)">
                                            <div class="toggle-dot"></div>
                                        </div>
                                        <div>
                                            <div class="toggle-label">Enable API Access</div>
                                            <div class="toggle-desc">Allow third-party applications to access your data via API</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fa-solid fa-key"></i> API Key</label>
                                    <input type="text" value="sk_live_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" readonly style="background:#0a1628;color:#666;cursor:not-allowed;" />
                                    <span class="help-text">Your secret API key. Keep this secure!</span>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-regular fa-clock"></i> Rate Limit</label>
                                    <select>
                                        <option selected>100 requests/minute</option>
                                        <option>500 requests/minute</option>
                                        <option>1000 requests/minute</option>
                                        <option>Unlimited</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group full">
                                    <button type="button" class="save-btn" style="background:linear-gradient(90deg,#2563eb,#60a5fa);width:auto;padding:10px 24px;font-size:13px;">
                                        <i class="fa-solid fa-rotate"></i> Regenerate API Key
                                    </button>
                                </div>
                            </div>
                        </div>

                    </form>

                </div>
            </div>

            <!-- Footer -->
            <footer>
                <p>© <?php echo date('Y'); ?> IncomeCoin | Developed By Rizwan Saifi</p>
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

    // =============================
    // THEME TOGGLE (DARK/LIGHT)
    // =============================
    const themeBtn = document.getElementById("themeToggle");

    themeBtn.addEventListener("click", function() {
        document.body.classList.toggle("light-mode");

        if (document.body.classList.contains("light-mode")) {
            this.classList.remove("fa-moon");
            this.classList.add("fa-sun");
        } else {
            this.classList.remove("fa-sun");
            this.classList.add("fa-moon");
        }
    });

    // =============================
    // TOGGLE SWITCH FUNCTION
    // =============================
    function toggleSwitch(element) {
        element.classList.toggle('active');
        
        // Update hidden input
        const parent = element.closest('.toggle-group');
        if (parent) {
            const formGroup = parent.parentElement;
            if (formGroup) {
                const hiddenInput = formGroup.querySelector('input[type="hidden"]');
                if (hiddenInput) {
                    hiddenInput.value = element.classList.contains('active') ? '1' : '0';
                }
            }
        }
    }

    // =============================
    // COLOR PICKER
    // =============================
    document.querySelectorAll('.color-option').forEach(option => {
        option.addEventListener('click', function() {
            const parent = this.closest('.color-picker-group');
            parent.querySelectorAll('.color-option').forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            this.querySelector('input[type="radio"]').checked = true;
            
            // Update custom color picker
            const customPicker = parent.querySelector('input[type="color"]');
            if (customPicker) {
                customPicker.value = this.style.backgroundColor;
            }
        });
    });

    // Custom color picker sync
    document.querySelectorAll('input[type="color"]').forEach(picker => {
        picker.addEventListener('input', function() {
            const parent = this.closest('.color-picker-group');
            parent.querySelectorAll('.color-option').forEach(o => o.classList.remove('active'));
            const radio = parent.querySelector('input[type="radio"][value="' + this.value + '"]');
            if (radio) {
                radio.checked = true;
                radio.closest('.color-option').classList.add('active');
            }
        });
    });

    // =============================
    // SIDEBAR NAVIGATION SCROLL
    // =============================
    document.querySelectorAll('.settings-sidebar a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('href');
            if (target && target.startsWith('#')) {
                const element = document.querySelector(target);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                }
            }
            document.querySelectorAll('.settings-sidebar a').forEach(a => a.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // =============================
    // AUTO-HIDE ALERTS
    // =============================
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 400);
        }, 5000);
    });
    </script>

</body>
</html>