<?php
// ================================
// PROFESSIONAL REGISTRATION
// ================================

session_start();
include("db.php");


// ================================
// CSRF TOKEN
// ================================

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


// ================================
// HANDLE FORM SUBMISSION
// ================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (
        !isset($_POST['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']
    ) {
        $_SESSION['errors'] = ['Invalid security token. Please try again.'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Get form data
    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $mobile     = trim($_POST['mobile'] ?? '');
    $password   = $_POST['password'] ?? '';

    // Validation
    $errors = [];

    if ($name === '')       $errors[] = 'Full name is required.';
    if ($email === '')      $errors[] = 'Email address is required.';
    if ($mobile === '')     $errors[] = 'Mobile number is required.';
    if ($password === '')   $errors[] = 'Password is required.';

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if ($mobile !== '' && !preg_match('/^[0-9]{10,15}$/', $mobile)) {
        $errors[] = 'Mobile number must be 10-15 digits.';
    }

    if ($password !== '') {
        if (strlen($password) < 7) {
            $errors[] = 'Password must be at least 7 characters.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }
    }

    // DB checks
    if (empty($errors)) {

        // Check email
        $stmt = mysqli_prepare($conn, "SELECT id FROM contact WHERE email = ? LIMIT 1");
        if (!$stmt) {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $errors[] = 'This email is already registered.';
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Create new referral ID
    if (empty($errors)) {

        function generateReferralID($length = 8)
        {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $referral_id = '';
            for ($i = 0; $i < $length; $i++) {
                $referral_id .= $characters[random_int(0, strlen($characters) - 1)];
            }
            return $referral_id;
        }

        do {
            $referral_id = generateReferralID(8);

            $stmt = mysqli_prepare(
                $conn,
                "SELECT id FROM contact WHERE referral_id = ? LIMIT 1"
            );
            mysqli_stmt_bind_param($stmt, "s", $referral_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $exists = mysqli_stmt_num_rows($stmt) > 0;
            mysqli_stmt_close($stmt);
        } while ($exists);

        // ------------------------------------------------------------
        // INSERT NEW USER  →  created_at is added automatically
        // ------------------------------------------------------------
        $plain_password = $password;
        $created_at     = date('Y-m-d H:i:s');   // registration date/time

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO contact
            (
                name,
                email,
                mobile,
                password,
                referral_id,
                created_at
            )
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param(
                $stmt,
                "ssssss",
                $name,
                $email,
                $mobile,
                $plain_password,
                $referral_id,
                $created_at
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                $_SESSION['success'] =
                    'Registration successful! Your Referral ID is '
                    . $referral_id;

                header("Location: register_popup.php");
                exit();
            } else {
                $errors[] = 'Database error: ' . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }

    // Store errors
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = [
            'name'       => $name,
            'email'      => $email,
            'mobile'     => $mobile
        ];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}


// ================================
// FLASH MESSAGES
// ================================

$error_messages  = $_SESSION['errors']  ?? [];
$success_message = $_SESSION['success'] ?? '';
$old_data        = $_SESSION['old']     ?? [];

unset($_SESSION['errors'], $_SESSION['success'], $_SESSION['old']);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thunder X – Professional Registration</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet">

    <style>
    /* =========================================================
   THUNDER X — PROFESSIONAL REGISTRATION UI
========================================================= */

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background:
            radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.12), transparent 30%),
            radial-gradient(circle at 90% 80%, rgba(34, 197, 94, 0.10), transparent 30%),
            #070b14;
        color: #f8fafc;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 35px 20px;
    }

    .register-card {
        position: relative;
        width: 100%;
        max-width: 1120px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 45px;
        padding: 45px;
        background: rgba(15, 23, 42, 0.78);
        backdrop-filter: blur(22px);
        -webkit-backdrop-filter: blur(22px);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 28px;
        box-shadow:
            0 35px 90px rgba(0, 0, 0, 0.65),
            inset 0 1px 0 rgba(255, 255, 255, 0.05);
        overflow: hidden;
    }

    .register-card::before {
        content: "";
        position: absolute;
        width: 260px;
        height: 260px;
        top: -140px;
        right: -100px;
        background: rgba(59, 130, 246, 0.16);
        filter: blur(70px);
        border-radius: 50%;
        pointer-events: none;
    }

    .register-card::after {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        bottom: -130px;
        left: -90px;
        background: rgba(34, 197, 94, 0.12);
        filter: blur(65px);
        border-radius: 50%;
        pointer-events: none;
    }

    .brand {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 15px 10px;
    }

    .brand h1 {
        font-size: clamp(2.2rem, 4vw, 3.4rem);
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -1.5px;
        margin-bottom: 18px;
        color: #f8fafc;
    }

    .brand h1 span {
        background: linear-gradient(135deg, #13e460, #3b82f6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .brand p {
        color: #94a3b8;
        font-size: 15px;
        line-height: 1.8;
        max-width: 480px;
        margin-bottom: 28px;
    }

    .brand ul {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 13px;
    }

    .brand ul li {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #dbeafe;
        font-size: 14px;
        font-weight: 500;
    }

    .brand ul li::before {
        content: "✓";
        width: 27px;
        height: 27px;
        min-width: 27px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        box-shadow: 0 5px 15px rgba(34, 197, 94, 0.22);
    }

    .form-container {
        position: relative;
        z-index: 2;
        padding: 30px;
        background: rgba(2, 6, 23, 0.48);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 22px;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.04),
            0 15px 45px rgba(0, 0, 0, 0.25);
    }

    .form-container h2 {
        text-align: center;
        font-size: 25px;
        font-weight: 700;
        margin-bottom: 25px;
        color: #f1f5f9;
        letter-spacing: -0.4px;
    }

    .flash-messages {
        margin-bottom: 20px;
    }

    .flash-error,
    .flash-success {
        padding: 12px 15px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 8px;
        backdrop-filter: blur(10px);
    }

    .flash-error {
        background: rgba(239, 68, 68, 0.10);
        border: 1px solid rgba(239, 68, 68, 0.25);
        border-left: 4px solid #ef4444;
        color: #fca5a5;
    }

    .flash-success {
        background: rgba(34, 197, 94, 0.10);
        border: 1px solid rgba(34, 197, 94, 0.25);
        border-left: 4px solid #22c55e;
        color: #86efac;
    }

    .form-group {
        margin-bottom: 17px;
    }

    .form-group label {
        display: block;
        margin-bottom: 7px;
        color: #cbd5e1;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    .form-group input {
        width: 100%;
        height: 48px;
        padding: 0 15px;
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 11px;
        outline: none;
        background: rgba(15, 23, 42, 0.72);
        color: #f8fafc;
        font-size: 14px;
        font-weight: 500;
        transition:
            border-color 0.25s ease,
            box-shadow 0.25s ease,
            background 0.25s ease,
            transform 0.2s ease;
    }

    .form-group input:hover {
        border-color: rgba(96, 165, 250, 0.35);
    }

    .form-group input:focus {
        border-color: #3b82f6;
        background: rgba(15, 23, 42, 0.95);
        box-shadow:
            0 0 0 3px rgba(59, 130, 246, 0.12),
            0 8px 25px rgba(0, 0, 0, 0.18);
    }

    .form-group input::placeholder {
        color: #64748b;
    }

    .btn {
        position: relative;
        overflow: hidden;
        width: 100%;
        height: 50px;
        margin-top: 7px;
        border: none;
        border-radius: 11px;
        background: linear-gradient(135deg, #2563eb, #16a34a);
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: 0.2px;
        cursor: pointer;
        box-shadow: 0 10px 25px rgba(37, 99, 235, 0.20);
        transition:
            transform 0.2s ease,
            box-shadow 0.25s ease,
            filter 0.25s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        filter: brightness(1.08);
        box-shadow: 0 15px 35px rgba(34, 197, 94, 0.25);
    }

    .btn:active {
        transform: translateY(0) scale(0.98);
    }

    .footer-text {
        text-align: center;
        margin-top: 20px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.6;
    }

    .footer-text a {
        color: #60a5fa;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s ease;
    }

    .footer-text a:hover {
        color: #93c5fd;
    }

    @media (max-width: 900px) {
        body {
            padding: 25px 15px;
        }

        .register-card {
            grid-template-columns: 1fr;
            max-width: 650px;
            gap: 25px;
            padding: 30px;
        }

        .brand {
            text-align: center;
            align-items: center;
        }

        .brand p {
            max-width: 600px;
        }

        .brand ul {
            width: fit-content;
            text-align: left;
        }
    }

    @media (max-width: 600px) {
        body {
            padding: 12px;
            align-items: flex-start;
        }

        .register-card {
            margin-top: 15px;
            padding: 20px 15px;
            border-radius: 20px;
            gap: 20px;
        }

        .brand {
            padding: 5px;
        }

        .brand h1 {
            font-size: 2rem;
            letter-spacing: -0.8px;
        }

        .brand p {
            font-size: 13px;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .brand ul {
            gap: 10px;
        }

        .brand ul li {
            font-size: 12px;
        }

        .brand ul li::before {
            width: 24px;
            height: 24px;
            min-width: 24px;
            font-size: 10px;
        }

        .form-container {
            padding: 22px 17px;
            border-radius: 17px;
        }

        .form-container h2 {
            font-size: 21px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            font-size: 12px;
        }

        .form-group input {
            height: 46px;
            font-size: 13px;
            border-radius: 9px;
        }

        .btn {
            height: 47px;
            font-size: 14px;
        }

        .footer-text {
            font-size: 11px;
        }
    }

    @media (max-width: 380px) {
        .register-card {
            padding: 15px 10px;
        }

        .form-container {
            padding: 18px 13px;
        }

        .brand h1 {
            font-size: 1.75rem;
        }
    }
    </style>
</head>


<body>

    <div class="register-card">

        <!-- LEFT SIDE -->
        <div class="brand">
            <h1>Thunder <span>X</span></h1>
            <p>
                Join the next generation of smart investing.
                Secure, fast, and trusted by thousands.
            </p>
            <ul>
                <li>Military-grade security</li>
                <li>Instant account activation</li>
                <li>24/7 expert support</li>
                <li>Transparent &amp; fair</li>
            </ul>
        </div>


        <!-- RIGHT SIDE -->
        <div class="form-container">

            <h2>Create Account</h2>

            <div class="flash-messages">

                <?php if ($success_message): ?>
                <div class="flash-success">
                    <?= htmlspecialchars($success_message) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($error_messages)): ?>
                <?php foreach ($error_messages as $msg): ?>
                <div class="flash-error">
                    <?= htmlspecialchars($msg) ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

            </div>

            <form method="POST" action="">

                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter Name"
                        value="<?= htmlspecialchars($old_data['name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter Email"
                        value="<?= htmlspecialchars($old_data['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="mobile">Mobile Number</label>
                    <input type="tel" id="mobile" name="mobile" placeholder="Enter number"
                        value="<?= htmlspecialchars($old_data['mobile'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="At least 7 chars, 1 uppercase"
                        required>
                </div>

                <button type="submit" class="btn">Create Account</button>

            </form>

            <div class="footer-text">
                Already have an account?
                <a href="login.php">Sign in</a>
            </div>

        </div>

    </div>

</body>

</html>