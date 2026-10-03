<?php
session_start();
include("../db.php");

// Apne login system ke according session key change karein
$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
    die("Please login first.");
}

// User wallet address nikalna
$stmt = mysqli_prepare(
    $conn,
    "SELECT wallet_address FROM contact WHERE id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

$wallet_address = $user['wallet_address'] ?? '';

// Handle success/error messages from save
$message = '';
$message_type = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $message = 'Wallet address saved successfully!';
        $message_type = 'success';
    } elseif ($_GET['status'] == 'error') {
        $message = 'Failed to save wallet address. Please try again.';
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet Address | Thunder</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        /* ========================================
           ROOT VARIABLES
        ======================================== */
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #eef2ff;
            --primary-subtle: #f8f7ff;
            --primary-gradient: linear-gradient(135deg, #6366f1, #8b5cf6);

            --success: #10b981;
            --success-light: #ecfdf5;
            --danger: #ef4444;
            --danger-light: #fef2f2;

            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;

            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 20px rgba(99,102,241,0.10);
            --shadow-lg: 0 10px 40px rgba(99,102,241,0.12);
            --shadow-primary: 0 4px 20px rgba(99,102,241,0.25);

            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-2xl: 24px;
            --radius-full: 9999px;

            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ========================================
           RESET & BASE
        ======================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at 0% 0%, rgba(13, 17, 131, 0.94) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(92, 197, 246, 0.67) 0%, transparent 50%),
                var(--gray-50);
            padding: 20px;
            color: var(--gray-800);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ========================================
           MAIN CONTAINER
        ======================================== */
        .wallet-container {
            width: 100%;
            max-width: 520px;
            margin: 20px auto;
            animation: fadeUp 0.6s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ========================================
           CARD
        ======================================== */
        .wallet-card {
            background: #ffffff;
            border-radius: var(--radius-2xl);
            padding: 36px 32px 32px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .wallet-card:hover {
            box-shadow: var(--shadow-lg);
            border-color: rgba(99,102,241,0.12);
            transform: translateY(-2px);
        }

        .wallet-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        /* ========================================
           HEADER
        ======================================== */
        .wallet-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 6px;
        }

        .wallet-header .icon-wrapper {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-md);
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 22px;
            box-shadow: var(--shadow-primary);
            flex-shrink: 0;
        }

        .wallet-header h2 {
            font-size: 22px;
            font-weight: 800;
            color: var(--gray-900);
            letter-spacing: -0.3px;
        }

        .wallet-header h2 span {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .wallet-subtitle {
            color: var(--gray-500);
            font-size: 14px;
            margin-top: 2px;
            padding-left: 66px;
        }

        /* ========================================
           ALERT / MESSAGE
        ======================================== */
        .alert {
            padding: 14px 18px;
            border-radius: var(--radius-md);
            margin: 16px 0 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
            animation: slideDown 0.4s ease;
            border-left: 4px solid;
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

        .alert-success {
            background: var(--success-light);
            color: #065f46;
            border-left-color: var(--success);
        }

        .alert-success i {
            color: var(--success);
        }

        .alert-error {
            background: var(--danger-light);
            color: #991b1b;
            border-left-color: var(--danger);
        }

        .alert-error i {
            color: var(--danger);
        }

        .alert i {
            font-size: 18px;
            flex-shrink: 0;
        }

        /* ========================================
           FORM
        ======================================== */
        .form-group {
            margin-top: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-group label i {
            margin-right: 6px;
            color: var(--primary);
        }

        .form-group .input-wrapper {
            position: relative;
        }

        .form-group .input-wrapper .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 16px;
            transition: var(--transition);
            pointer-events: none;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 14px;
            transition: var(--transition);
            background: var(--gray-50);
            color: var(--gray-800);
            font-family: 'Inter', sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.08);
        }

        .form-group input:focus + .input-icon,
        .form-group input:focus ~ .input-icon {
            color: var(--primary);
        }

        .form-group input::placeholder {
            color: var(--gray-400);
        }

        .form-group .help-text {
            font-size: 12px;
            color: var(--gray-400);
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .form-group .help-text i {
            font-size: 12px;
        }

        /* ========================================
           BUTTON
        ======================================== */
        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: var(--radius-full);
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-family: 'Inter', sans-serif;
            margin-top: 24px;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: var(--shadow-primary);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transform: skewX(-25deg);
            transition: 0.6s;
        }

        .btn-primary:hover::after {
            left: 150%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(99,102,241,0.35);
        }

        .btn-primary:active {
            transform: scale(0.97);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-primary i {
            font-size: 16px;
        }

        /* ========================================
           SPINNER
        ======================================== */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ========================================
           ADDRESS PREVIEW (if exists)
        ======================================== */
        .address-preview {
            margin-top: 20px;
            padding: 14px 18px;
            background: var(--primary-subtle);
            border-radius: var(--radius-md);
            border: 1px solid rgba(99,102,241,0.12);
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            animation: fadeUp 0.5s ease;
        }

        .address-preview .preview-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .address-preview .preview-label i {
            color: var(--success);
        }

        .address-preview .preview-address {
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-700);
            font-family: 'Inter', monospace;
            background: #fff;
            padding: 4px 12px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-200);
            flex: 1;
            min-width: 120px;
            word-break: break-all;
        }

        .address-preview .preview-status {
            font-size: 12px;
            font-weight: 600;
            color: var(--success);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .address-preview .preview-status i {
            font-size: 14px;
        }

        /* ========================================
           RESPONSIVE
        ======================================== */
        @media (max-width: 600px) {
            body {
                padding: 12px;
            }

            .wallet-card {
                padding: 24px 20px;
                border-radius: var(--radius-xl);
            }

            .wallet-header h2 {
                font-size: 18px;
            }

            .wallet-header .icon-wrapper {
                width: 44px;
                height: 44px;
                font-size: 18px;
            }

            .wallet-subtitle {
                padding-left: 58px;
                font-size: 13px;
            }

            .form-group input {
                padding: 12px 14px 12px 40px;
                font-size: 13px;
            }

            .btn {
                font-size: 14px;
                padding: 13px;
            }

            .address-preview {
                padding: 12px 14px;
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }

            .address-preview .preview-address {
                font-size: 12px;
                padding: 6px 10px;
            }

            .alert {
                padding: 12px 14px;
                font-size: 13px;
            }
        }

        @media (max-width: 400px) {
            .wallet-card {
                padding: 18px 14px;
                border-radius: var(--radius-lg);
            }

            .wallet-header h2 {
                font-size: 16px;
            }

            .wallet-header .icon-wrapper {
                width: 38px;
                height: 38px;
                font-size: 16px;
            }

            .wallet-subtitle {
                padding-left: 52px;
                font-size: 12px;
            }

            .form-group input {
                padding: 10px 12px 10px 36px;
                font-size: 12px;
            }

            .btn {
                font-size: 13px;
                padding: 12px;
            }
        }

        /* ========================================
           SCROLLBAR
        ======================================== */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: var(--gray-100);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 20px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }
    </style>
</head>

<body>

<div class="wallet-container">

    <div class="wallet-card">

        <!-- Header -->
        <div class="wallet-header">
            <div class="icon-wrapper">
                <i class="fas fa-wallet"></i>
            </div>
            <h2>Wallet <span>Address</span></h2>
        </div>

        <p class="wallet-subtitle">
            <i class="fas fa-info-circle" style="color: var(--primary);"></i>
            Add your wallet address to receive payments
        </p>

        <!-- Alert Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Address Preview (if exists) -->
        <?php if (!empty($wallet_address)): ?>
            <div class="address-preview">
                <span class="preview-label">
                    <i class="fas fa-check-circle"></i> Current Address
                </span>
                <span class="preview-address" id="previewAddress">
                    <?php echo htmlspecialchars($wallet_address); ?>
                </span>
                <span class="preview-status">
                    <i class="fas fa-circle" style="font-size: 8px;"></i> Active
                </span>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="wallet_save.php" method="POST" id="walletForm">

            <div class="form-group">
                <label for="wallet_address">
                    <i class="fas fa-qrcode"></i> Wallet Address
                </label>
                <div class="input-wrapper">
                    <i class="fas fa-link input-icon"></i>
                    <input
                        type="text"
                        id="wallet_address"
                        name="wallet_address"
                        value="<?= htmlspecialchars($wallet_address, ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="Enter your wallet address (e.g., 0x... or bc1...)"
                        required
                        autocomplete="off"
                    >
                </div>
                <div class="help-text">
                    <i class="fas fa-info-circle"></i>
                    Enter your wallet address for receiving payments. Make sure it's correct.
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="saveBtn">
                <i class="fas fa-save"></i>
                <span id="btnText">Save Wallet Address</span>
            </button>

        </form>

    </div>

</div>

<!-- ========================================
     JAVASCRIPT
======================================== -->
<script>
    (function() {
        'use strict';

        const form = document.getElementById('walletForm');
        const btn = document.getElementById('saveBtn');
        const btnText = document.getElementById('btnText');
        const input = document.getElementById('wallet_address');

        // ========================================
        // FORM SUBMISSION HANDLING
        // ========================================
        form.addEventListener('submit', function(e) {
            const address = input.value.trim();

            // Validate: minimum length check
            if (address.length < 10) {
                e.preventDefault();
                alert('Please enter a valid wallet address (minimum 10 characters).');
                input.focus();
                return false;
            }

            // Show loading state
            btn.innerHTML = '<span class="spinner"></span> Saving...';
            btn.disabled = true;

            // Re-enable after 10s if stuck (fallback)
            setTimeout(function() {
                if (btn.disabled) {
                    btn.innerHTML = '<i class="fas fa-save"></i> Save Wallet Address';
                    btn.disabled = false;
                }
            }, 10000);
        });

        // ========================================
        // AUTO-TRIM & VALIDATION ON BLUR
        // ========================================
        input.addEventListener('blur', function() {
            this.value = this.value.trim();
        });

        // ========================================
        // KEYBOARD SHORTCUT: Ctrl+Enter to submit
        // ========================================
        input.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                form.submit();
            }
        });

        // ========================================
        // AUTO-HIDE ALERTS AFTER 6 SECONDS
        // ========================================
        document.querySelectorAll('.alert').forEach(function(alert) {
            setTimeout(function() {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-12px)';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 400);
            }, 6000);
        });

        // ========================================
        // PASTE EVENT: Trim after paste
        // ========================================
        input.addEventListener('paste', function() {
            setTimeout(function() {
                input.value = input.value.trim();
            }, 10);
        });

        console.log('✅ Wallet Address page loaded successfully');
    })();
</script>

</body>
</html>