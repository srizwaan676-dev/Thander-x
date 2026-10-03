<?php
session_start();
include("../db.php");

/* ============================================================
   1. AUTH CHECK
   ============================================================ */
$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
    die("Please login first.");
}

/* ============================================================
   2. INITIALIZE MESSAGES
   ============================================================ */
$message = '';
$message_type = '';

/* ============================================================
   3. HANDLE FORM SUBMISSION (POST)
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wallet_address'])) {

    $wallet_address = trim($_POST['wallet_address'] ?? '');
    $upi_number     = trim($_POST['upi_number'] ?? '');

    /* --- Validation --- */
    if (empty($wallet_address)) {
        $_SESSION['wallet_error'] = 'Wallet address is required.';
    } elseif (strlen($wallet_address) < 10) {
        $_SESSION['wallet_error'] = 'Wallet address must be at least 10 characters.';
    } elseif (strlen($wallet_address) > 255) {
        $_SESSION['wallet_error'] = 'Wallet address is too long (max 255 characters).';
    } else {

        /* --- Check user exists --- */
        $checkStmt = mysqli_prepare(
            $conn,
            "SELECT id FROM contact WHERE id = ? LIMIT 1"
        );

        if (!$checkStmt) {
            $_SESSION['wallet_error'] = 'Database error. Please try again.';
        } else {
            mysqli_stmt_bind_param($checkStmt, "i", $user_id);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);

            if (mysqli_stmt_num_rows($checkStmt) === 0) {
                $_SESSION['wallet_error'] = 'User account not found.';
            } else {

                /* --- Update wallet address + UPI number --- */
                $updateStmt = mysqli_prepare(
                    $conn,
                    "UPDATE contact 
                     SET wallet_address = ?, upi_number = ? 
                     WHERE id = ? LIMIT 1"
                );

                if (!$updateStmt) {
                    $_SESSION['wallet_error'] = 'Database error: ' . mysqli_error($conn);
                } else {
                    mysqli_stmt_bind_param(
                        $updateStmt,
                        "ssi",
                        $wallet_address,
                        $upi_number,
                        $user_id
                    );

                    if (mysqli_stmt_execute($updateStmt)) {
                        $_SESSION['wallet_success'] = 'Wallet details saved successfully!';
                    } else {
                        $_SESSION['wallet_error'] = 'Failed to save. Please try again.';
                    }

                    mysqli_stmt_close($updateStmt);
                }
            }

            mysqli_stmt_close($checkStmt);
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/* ============================================================
   4. FETCH WALLET + NAME + UPI FROM DATABASE
   ============================================================ */
$stmt = mysqli_prepare(
    $conn,
    "SELECT wallet_address, name, upi_number 
     FROM contact 
     WHERE id = ? LIMIT 1"
);

if (!$stmt) {
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

$wallet_address = trim($user['wallet_address'] ?? '');
$owner_name     = trim($user['name'] ?? '');
$upi_number     = trim($user['upi_number'] ?? '');

mysqli_stmt_close($stmt);

/* ============================================================
   5. PULL SESSION MESSAGES
   ============================================================ */
if (isset($_SESSION['wallet_success'])) {
    $message = $_SESSION['wallet_success'];
    $message_type = 'success';
    unset($_SESSION['wallet_success']);
}

if (isset($_SESSION['wallet_error'])) {
    $message = $_SESSION['wallet_error'];
    $message_type = 'error';
    unset($_SESSION['wallet_error']);
}

if (empty($message) && isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $message = 'Wallet details saved successfully!';
        $message_type = 'success';
    }
    if ($_GET['status'] === 'error') {
        $message = 'Failed to save. Please try again.';
        $message_type = 'error';
    }
}

/* ============================================================
   6. GENERATE QR CODE URL (UPI number preferred)
   ============================================================ */
$qrImage = '';

$qrData = '';
if (!empty($upi_number)) {
    $qrData = $upi_number;         // UPI ho to UPI ka QR
} elseif (!empty($wallet_address)) {
    $qrData = $wallet_address;     // warna wallet address
}

if (!empty($qrData)) {
    $qrImage =
        "https://api.qrserver.com/v1/create-qr-code/" .
        "?size=300x300&data=" .
        urlencode($qrData);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Wallet Address | Thunder</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --success: #10b981;
    --danger: #ef4444;
    --white: #ffffff;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    --gradient: linear-gradient(135deg, #6366f1, #8b5cf6);
    --shadow: 0 10px 40px rgba(99,102,241,0.12);
    --radius: 20px;
}

body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    font-family: 'Inter', sans-serif;
    background:
        radial-gradient(circle at top left, rgba(99,102,241,0.08), transparent 45%),
        var(--gray-50);
    color: var(--gray-800);
}

.wallet-container {
    width: 100%;
    max-width: 520px;
    animation: fadeUp .5s ease;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(25px); }
    to   { opacity: 1; transform: translateY(0); }
}

.wallet-card {
    position: relative;
    background: #fff;
    padding: 32px;
    border-radius: var(--radius);
    border: 1px solid var(--gray-200);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.wallet-card::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: var(--gradient);
}

.wallet-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 6px;
}

.icon-wrapper {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 13px;
    background: var(--gradient);
    color: white;
    font-size: 22px;
    box-shadow: 0 5px 20px rgba(99,102,241,.25);
}

.wallet-header h2 {
    font-size: 22px;
    font-weight: 800;
    color: var(--gray-900);
}

.wallet-header h2 span {
    background: var(--gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.wallet-subtitle {
    color: var(--gray-500);
    font-size: 13px;
    margin-left: 66px;
    margin-bottom: 20px;
}

.alert {
    padding: 13px 15px;
    border-radius: 12px;
    margin-bottom: 18px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 9px;
}

.alert-success {
    background: #ecfdf5;
    color: #047857;
    border-left: 4px solid var(--success);
}

.alert-error {
    background: #fef2f2;
    color: #b91c1c;
    border-left: 4px solid var(--danger);
}

.address-preview {
    padding: 14px;
    border-radius: 13px;
    background: #f8f7ff;
    border: 1px solid #e5e7eb;
    margin-bottom: 20px;
}

.preview-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--gray-500);
    margin-bottom: 8px;
}

.preview-address {
    display: block;
    padding: 10px;
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: 8px;
    font-size: 12px;
    word-break: break-all;
    color: var(--gray-700);
}

.form-group {
    margin-top: 18px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 7px;
    color: var(--gray-700);
}

.form-group label i {
    color: var(--primary);
    margin-right: 5px;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-400);
}

input {
    width: 100%;
    padding: 14px 14px 14px 43px;
    border: 2px solid var(--gray-200);
    border-radius: 12px;
    background: var(--gray-50);
    color: var(--gray-800);
    font-size: 14px;
    outline: none;
    transition: .3s;
}

input:focus {
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 4px rgba(99,102,241,.08);
}

.help-text {
    margin-top: 7px;
    font-size: 11px;
    color: var(--gray-400);
}

.btn {
    width: 100%;
    margin-top: 22px;
    padding: 14px;
    border: 0;
    border-radius: 999px;
    background: var(--gradient);
    color: white;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: .3s;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(99,102,241,.25);
}

.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.qr-section {
    margin-top: 25px;
    padding: 20px;
    text-align: center;
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
    border-radius: 16px;
}

.qr-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--gray-800);
    margin-bottom: 5px;
}

.qr-title i {
    color: var(--primary);
    margin-right: 5px;
}

.qr-subtitle {
    font-size: 12px;
    color: var(--gray-500);
    margin-bottom: 15px;
}

.qr-box {
    display: inline-block;
    padding: 12px;
    background: white;
    border-radius: 14px;
    border: 1px solid var(--gray-200);
    box-shadow: 0 5px 20px rgba(0,0,0,.05);
}

.qr-box img {
    width: 230px;
    height: 230px;
    display: block;
}

/* ============================================================
   OWNER NAME + UPI (QR ke niche)
   ============================================================ */
.owner-info {
    margin-top: 16px;
    padding: 14px 18px;
    background: white;
    border: 1px dashed var(--primary);
    border-radius: 12px;
    display: inline-block;
    min-width: 250px;
}

.owner-name {
    font-size: 16px;
    font-weight: 800;
    color: var(--gray-900);
    letter-spacing: .3px;
    margin-bottom: 6px;
}

.owner-name i {
    color: var(--primary);
    margin-right: 6px;
}

.owner-upi {
    font-size: 13px;
    font-weight: 600;
    color: var(--primary-dark);
    word-break: break-all;
}

.owner-upi i {
    color: var(--success);
    margin-right: 6px;
}

.qr-address {
    margin-top: 14px;
    font-size: 11px;
    color: var(--gray-500);
    word-break: break-all;
}

@media(max-width:600px) {
    body { padding: 12px; }
    .wallet-card { padding: 23px 18px; }
    .wallet-header h2 { font-size: 18px; }
    .wallet-subtitle { margin-left: 58px; font-size: 12px; }
    .qr-box img { width: 200px; height: 200px; }
    .owner-name { font-size: 15px; }
    .owner-upi { font-size: 12px; }
}

@media(max-width:380px) {
    .wallet-card { padding: 18px 14px; }
    .qr-box img { width: 175px; height: 175px; }
}

</style>

</head>

<body>

<div class="wallet-container">
<div class="wallet-card">

    <!-- HEADER -->
    <div class="wallet-header">
        <div class="icon-wrapper">
            <i class="fas fa-wallet"></i>
        </div>
        <h2>Wallet <span>Address</span></h2>
    </div>

    <p class="wallet-subtitle">
        <i class="fas fa-info-circle"></i>
        Add your wallet address to receive payments
    </p>

    <!-- MESSAGE -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <i class="fas <?php echo ($message_type === 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- CURRENT WALLET + QR -->
    <?php if (!empty($wallet_address)): ?>

        <div class="address-preview">
            <span class="preview-label">
                <i class="fas fa-check-circle"></i>
                Current Wallet Address
            </span>
            <span class="preview-address">
                <?php echo htmlspecialchars($wallet_address, ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </div>

        <div class="qr-section">
            <div class="qr-title">
                <i class="fas fa-qrcode"></i>
                Wallet QR Code
            </div>
            <div class="qr-subtitle">
                Scan this QR code to copy the wallet address
            </div>

            <div class="qr-box">
                <img
                    src="<?php echo htmlspecialchars($qrImage, ENT_QUOTES, 'UTF-8'); ?>"
                    alt="Wallet QR Code"
                >
            </div>

            <!-- ============================================================
                 OWNER NAME + UPI NUMBER (QR ke niche)
                 ============================================================ -->
            <div class="owner-info">

                <?php if (!empty($owner_name)): ?>
                    <div class="owner-name">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($owner_name, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($upi_number)): ?>
                    <div class="owner-upi">
                        <i class="fas fa-mobile-alt"></i>
                        <?php echo htmlspecialchars($upi_number, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

            </div>

            <div class="qr-address">
                <?php echo htmlspecialchars($wallet_address, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </div>

    <?php endif; ?>

    <!-- FORM -->
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="walletForm">

        <div class="form-group">
            <label for="wallet_address">
                <i class="fas fa-link"></i>
                Wallet Address
            </label>

            <div class="input-wrapper">
                <i class="fas fa-wallet input-icon"></i>
                <input
                    type="text"
                    id="wallet_address"
                    name="wallet_address"
                    value="<?php echo htmlspecialchars($wallet_address, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="Enter wallet address"
                    required
                    autocomplete="off"
                    minlength="10"
                    maxlength="255"
                >
            </div>

            <div class="help-text">
                <i class="fas fa-info-circle"></i>
                Make sure your wallet address is correct.
            </div>
        </div>

        <!-- 🆕 UPI NUMBER INPUT -->
        <div class="form-group">
            <label for="upi_number">
                <i class="fas fa-mobile-alt"></i>
                UPI Number
            </label>

            <div class="input-wrapper">
                <i class="fas fa-mobile-alt input-icon"></i>
                <input
                    type="text"
                    id="upi_number"
                    name="upi_number"
                    value="<?php echo htmlspecialchars($upi_number, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="e.g. 9876543210@paytm"
                    autocomplete="off"
                    maxlength="100"
                >
            </div>

            <div class="help-text">
                <i class="fas fa-info-circle"></i>
                Optional — used for UPI payments.
            </div>
        </div>

        <button type="submit" class="btn" id="saveBtn">
            <i class="fas fa-save"></i>
            Save Details
        </button>

    </form>

</div>
</div>

<script>

const form = document.getElementById('walletForm');
const input = document.getElementById('wallet_address');
const btn = document.getElementById('saveBtn');

form.addEventListener('submit', function(e) {

    const address = input.value.trim();

    if (address.length < 10) {
        e.preventDefault();
        alert('Please enter a valid wallet address.');
        input.focus();
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

});

input.addEventListener('blur', function() {
    this.value = this.value.trim();
});

</script>

</body>
</html>