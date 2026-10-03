<?php
session_start();
include("db.php");

if(isset($_POST['login'])){

    $email = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM contact WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) > 0){

    $user = mysqli_fetch_assoc($result);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    if($user['role'] == "admin"){
        header("Location: Dashboard.php");
    }else{
        header("Location: user/user_dashboard.php");
    }
    exit();

}else{
        $msg = "Invalid Email or Password!";
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Login | Cowork</title>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
        -webkit-tap-highlight-color: transparent;
    }

    html, body {
        overflow-x: hidden; /* prevent horizontal scroll on mobile */
    }

    body {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        background: linear-gradient(135deg, #020617, #0f172a, #1e293b, #111827);
    }

    /* Animated Background */
    body::before {
        content: "";
        position: absolute;
        width: 600px;
        height: 600px;
        background: linear-gradient(#2563eb, #06b6d4);
        border-radius: 50%;
        top: -250px;
        left: -220px;
        filter: blur(170px);
        opacity: .55;
        animation: move1 8s ease-in-out infinite alternate;
        z-index: -1;
        pointer-events: none;
    }

    body::after {
        content: "";
        position: absolute;
        width: 800px;
        height: 500px;
        background: linear-gradient(#22c55e, #14b8a6);
        border-radius: 50%;
        right: -180px;
        bottom: -180px;
        filter: blur(160px);
        opacity: .45;
        animation: move2 10s ease-in-out infinite alternate;
        z-index: -1;
        pointer-events: none;
    }

    @keyframes move1 {
        from { transform: translate(0, 0); }
        to { transform: translate(70px, 60px); }
    }

    @keyframes move2 {
        from { transform: translate(0, 0); }
        to { transform: translate(-60px, -50px); }
    }

    /* Login Card */
    .login-box {
        position: relative;
        z-index: 10;
        width: 430px;
        padding: 45px;
        border-radius: 28px;
        background: rgba(255, 255, 255, .08);
        backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, .15);
        box-shadow: 0 30px 70px rgba(0, 0, 0, .45), inset 0 1px 1px rgba(255, 255, 255, .15);
        overflow: hidden;
        margin: 20px; /* for small screens */
    }

    .login-box::before {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        background: #2563eb;
        border-radius: 50%;
        top: -120px;
        right: -120px;
        opacity: .20;
        pointer-events: none;
    }

    .login-box::after {
        content: "";
        position: absolute;
        width: 180px;
        height: 180px;
        background: #22c55e;
        border-radius: 50%;
        bottom: -90px;
        left: -90px;
        opacity: .20;
        pointer-events: none;
    }

    /* Welcome Text */
    .login-box .welcome-text {
        position: relative;
        z-index: 5;
        color: #fff;
        text-align: center;
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .login-box .welcome-text span {
        color: #60a5fa;
    }

    .login-box .sub-text {
        position: relative;
        z-index: 5;
        color: #94a3b8;
        text-align: center;
        margin-bottom: 30px;
        font-size: 14px;
    }

    /* ==========================
           FORM
        ========================== */
    .login-box form {
        position: relative;
        z-index: 5;
    }

    /* ==========================
           INPUT
        ========================== */
    .login-box input {
        width: 100%;
        height: 58px;
        margin-bottom: 18px;
        padding: 0 18px;
        border: 1px solid rgba(255, 255, 255, .15);
        border-radius: 14px;
        background: rgba(255, 255, 255, .08);
        color: #fff;
        font-size: 15px;
        outline: none;
        transition: .35s;
    }

    .login-box input::placeholder {
        color: #94a3b8;
    }

    .login-box input:focus {
        border-color: #3b82f6;
        background: rgba(255, 255, 255, .12);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, .18);
        transform: translateY(-2px);
    }

    /* Input with icon */
    .input-group {
        position: relative;
        margin-bottom: 18px;
    }

    .input-group .input-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 16px;
        transition: .35s;
    }

    .input-group input {
        padding-left: 48px;
        margin-bottom: 0;
    }

    .input-group input:focus + .input-icon {
        color: #60a5fa;
    }

    /* ==========================
           BUTTON
        ========================== */
    .login-box button {
        width: 100%;
        height: 58px;
        border: none;
        border-radius: 14px;
        margin-top: 10px;
        cursor: pointer;
        font-size: 17px;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        transition: .35s;
        box-shadow: 0 15px 35px rgba(37, 99, 235, .35);
    }

    .login-box button:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 45px rgba(37, 99, 235, .45);
    }

    .login-box button:active {
        transform: scale(.98);
    }

    /* ==========================
           FORGOT PASSWORD LINK
        ========================== */
    .forgot-link {
        text-align: right;
        margin-bottom: 20px;
    }

    .forgot-link a {
        color: #94a3b8;
        text-decoration: none;
        font-size: 13px;
        transition: .35s;
    }

    .forgot-link a:hover {
        color: #60a5fa;
    }

    /* ==========================
           DIVIDER
        ========================== */
    .divider {
        display: flex;
        align-items: center;
        gap: 16px;
        margin: 22px 0;
    }

    .divider hr {
        flex: 1;
        border: none;
        height: 1px;
        background: rgba(255, 255, 255, .1);
    }

    .divider span {
        color: #64748b;
        font-size: 13px;
    }

    /* ==========================
           SOCIAL BUTTONS
        ========================== */
    .social-btns {
        display: flex;
        gap: 12px;
    }

    .social-btns button {
        flex: 1;
        height: 48px;
        background: rgba(255, 255, 255, .06);
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 12px;
        color: #cbd5e1;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: .35s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: none;
        margin-top: 0;
    }

    .social-btns button:hover {
        background: rgba(255, 255, 255, .12);
        transform: translateY(-2px);
    }

    .social-btns button i {
        font-size: 18px;
    }

    .social-btns .google-btn i {
        color: #ea4335;
    }

    .social-btns .github-btn i {
        color: #fff;
    }

    /* ==========================
           REGISTER LINK
        ========================== */
    .register-link {
        text-align: center;
        margin-top: 22px;
        color: #94a3b8;
        font-size: 14px;
    }

    .register-link a {
        color: #60a5fa;
        text-decoration: none;
        font-weight: 600;
        transition: .35s;
    }

    .register-link a:hover {
        color: #93bbfc;
        text-decoration: underline;
    }

    /* ==========================
           ERROR MESSAGE
        ========================== */
    .error {
        background: rgba(239, 68, 68, .15);
        border: 1px solid rgba(239, 68, 68, .35);
        color: #fecaca;
        padding: 12px 16px;
        margin-bottom: 18px;
        border-radius: 10px;
        text-align: center;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .error i {
        font-size: 16px;
    }

    /* ==========================
           CARD ANIMATION
        ========================== */
    .login-box {
        animation: cardShow .8s ease;
    }

    @keyframes cardShow {
        from {
            opacity: 0;
            transform: translateY(40px) scale(.96);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* ==========================
           ACTIVATE WINDOWS BANNER (Hidden on mobile)
        ========================== */
    .activate-windows {
        position: fixed;
        bottom: 20px;
        right: 30px;
        color: rgba(255, 255, 255, 0.2);
        font-size: 11px;
        letter-spacing: 0.5px;
        z-index: 5;
        text-transform: uppercase;
        pointer-events: none;
    }

    .activate-windows a {
        color: rgba(255, 255, 255, 0.2);
        text-decoration: none;
    }

    .activate-windows a:hover {
        color: rgba(255, 255, 255, 0.4);
    }

    /* ==========================
           RESPONSIVE
        ========================== */
    @media(max-width:768px) {
        body {
            padding: 20px;
            align-items: flex-start;
        }

        .login-box {
            width: 100%;
            max-width: 440px;
            margin: auto;
            padding: 30px 22px;
            border-radius: 24px;
        }

        .login-box .welcome-text {
            font-size: 28px;
        }

        .login-box input,
        .login-box button {
            height: 54px;
        }

        .login-box input {
            font-size: 14px;
        }

        .activate-windows {
            display: none !important;
        }
    }

    @media(max-width:480px) {
        .login-box {
            border-radius: 20px;
            padding: 25px 18px;
        }

        .login-box .welcome-text {
            font-size: 24px;
        }

        .login-box .sub-text {
            font-size: 13px;
            margin-bottom: 22px;
        }

        .login-box input {
            height: 52px;
            font-size: 14px;
            border-radius: 12px;
        }

        .login-box button {
            height: 52px;
            font-size: 16px;
            border-radius: 12px;
        }

        .social-btns {
            flex-direction: column;
        }

        .social-btns button {
            height: 46px;
        }

        .forgot-link {
            font-size: 12px;
        }

        .register-link {
            font-size: 13px;
        }

        .error {
            font-size: 13px;
        }
    }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <div class="login-box">

        <div class="welcome-text">
            Welcome <span>Login</span>
        </div>
        <p class="sub-text">Enter your credentials to access your account</p>

        <?php if(isset($msg)): ?>
        <div class="error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $msg; ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <input type="text" name="username" placeholder="Email" required>
                <i class="fas fa-envelope input-icon"></i>
            </div>

            <div class="input-group">
                <input type="password" name="password" placeholder="Password " required>
                <i class="fas fa-lock input-icon"></i>
            </div>

            <div class="forgot-link">
                <a href="#"><i class="fas fa-key"></i> Forgot password?</a>
            </div>

            <button type="submit" name="login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <!-- Divider -->
        <div class="divider">
            <hr>
            <span>or</span>
            <hr>
        </div>

        <!-- Social Buttons -->
        <div class="social-btns">
            <button class="google-btn">
                <i class="fab fa-google"></i> Google
            </button>
            <button class="github-btn">
                <i class="fab fa-github"></i> GitHub
            </button>
        </div>

        <!-- Register Link -->
        <div class="register-link">
            Don't have an account? <a href="login.php">Register</a>
        </div>

    </div>

    <!-- Activate Windows (Hidden on mobile) -->
    <div class="activate-windows">
        <i class="fas fa-windows"></i> <a href="#">Activate Windows</a>
        <span style="margin:0 6px;opacity:0.3;">|</span>
        <a href="#">Go to Settings to activate Windows</a>
    </div>

</body>

</html>