<?php
session_start();
include("db.php");

// Check if there's a success flash message
$show_success = false;
$success_name = '';
if (isset($_SESSION['product_success'])) {
    $show_success = true;
    $success_name = $_SESSION['product_success'];
    unset($_SESSION['product_success']); // clear so it doesn't repeat
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product </title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        /* ========================================
           PAGE BACKGROUND
        ======================================== */
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #050810;
            padding: 30px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(59,130,246,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59,130,246,0.05) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: 0;
            pointer-events: none;
            mask-image: radial-gradient(ellipse at center, black 30%, transparent 80%);
            -webkit-mask-image: radial-gradient(ellipse at center, black 30%, transparent 80%);
            animation: gridScroll 20s linear infinite;
        }
        @keyframes gridScroll {
            0%   { background-position: 0 0; }
            100% { background-position: 50px 50px; }
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.35) 0%, transparent 35%),
                radial-gradient(circle at 90% 80%, rgba(34, 197, 94, 0.28) 0%, transparent 35%),
                radial-gradient(circle at 50% 50%, rgba(168, 85, 247, 0.15) 0%, transparent 50%);
            filter: blur(60px);
            z-index: 0;
            pointer-events: none;
            animation: auroraShift 15s ease-in-out infinite;
        }
        @keyframes auroraShift {
            0%, 100% { transform: translate(0,0) scale(1); opacity: 0.9; }
            33%      { transform: translate(30px,-20px) scale(1.08); opacity: 1; }
            66%      { transform: translate(-20px,30px) scale(0.96); opacity: 0.85; }
        }

        /* ========================================
           MODAL FORM WRAPPER
        ======================================== */
        .form-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 590px;
            padding: 2px;
            border-radius: 26px;
            background: conic-gradient(
                from 0deg,
                #2563eb,
                #22c55e,
                #f5c518,
                #a855f7,
                #2563eb
            );
            animation: spinBorder 8s linear infinite,
                       modalEnter 0.75s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow:
                0 50px 120px rgba(0, 0, 0, 0.8),
                0 0 90px rgba(37, 99, 235, 0.35),
                0 0 160px rgba(34, 197, 94, 0.15);
        }
        @keyframes spinBorder {
            to { --angle: 360deg; }
        }
        @keyframes modalEnter {
            0%   { opacity: 0; transform: translateY(60px) scale(0.9) rotateX(-10deg); }
            100% { opacity: 1; transform: translateY(0)    scale(1)   rotateX(0); }
        }

        .form-container::before {
            content: '';
            position: absolute;
            inset: 2px;
            background:
                radial-gradient(circle at 20% 0%, rgba(59,130,246,0.12) 0%, transparent 45%),
                radial-gradient(circle at 80% 100%, rgba(34,197,94,0.08) 0%, transparent 45%),
                linear-gradient(180deg, #0d1729 0%, #080f1e 100%);
            border-radius: 24px;
            z-index: -1;
        }

        .form-container::after {
            content: '';
            position: absolute;
            inset: 2px;
            border-radius: 24px;
            background-image:
                radial-gradient(2px 2px at 15% 25%, rgba(255,255,255,0.5), transparent),
                radial-gradient(2px 2px at 80% 15%, rgba(59,130,246,0.7), transparent),
                radial-gradient(2px 2px at 45% 70%, rgba(34,197,94,0.7), transparent),
                radial-gradient(2px 2px at 90% 85%, rgba(255,255,255,0.4), transparent),
                radial-gradient(2px 2px at 25% 90%, rgba(168,85,247,0.6), transparent);
            pointer-events: none;
            z-index: 0;
            animation: particlesFloat 8s ease-in-out infinite;
            opacity: 0.85;
        }
        @keyframes particlesFloat {
            0%, 100% { background-position: 0 0; }
            50%      { background-position: 20px -20px; }
        }

        /* ========================================
           HEADER
        ======================================== */
        .form-container h2 {
            position: relative;
            z-index: 1;
            color: #fff;
            text-align: center;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.4px;
            padding: 28px 32px 22px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: rgba(0, 0, 0, 0.15);
        }
        .form-container h2::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 20%;
            right: 20%;
            height: 2px;
            background: linear-gradient(90deg,
                transparent, #2563eb, #22c55e, #f5c518, transparent);
            background-size: 200% 100%;
            border-radius: 2px;
            animation: lineShimmer 3s linear infinite;
        }
        @keyframes lineShimmer {
            0%   { background-position: -100% 0; }
            100% { background-position: 200% 0; }
        }
        .form-container h2 i {
            color: #22c55e;
            background: linear-gradient(135deg, rgba(34,197,94,0.25), rgba(37,99,235,0.15));
            border: 1px solid rgba(34, 197, 94, 0.35);
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow:
                0 0 25px rgba(34, 197, 94, 0.4),
                inset 0 0 20px rgba(34, 197, 94, 0.15);
            position: relative;
            animation: iconPulse 2.5s ease-in-out infinite;
        }
        .form-container h2 i::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 18px;
            border: 1px solid rgba(34, 197, 94, 0.3);
            animation: iconRing 2.5s ease-out infinite;
        }
        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50%      { transform: scale(1.05); }
        }
        @keyframes iconRing {
            0%   { transform: scale(1);   opacity: 0.7; }
            100% { transform: scale(1.5); opacity: 0; }
        }

        /* ========================================
           FORM
        ======================================== */
        form {
            position: relative;
            z-index: 1;
            background: transparent;
            padding: 26px 32px 12px;
            max-height: 70vh;
            overflow-y: auto;
        }

        form .form-group {
            margin-bottom: 16px;
        }

        form .form-group label {
            display: block;
            color: rgba(255, 255, 255, 0.65);
            font-size: 12.5px;
            font-weight: 600;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        form .form-group label i {
            margin-right: 6px;
            color: #3b82f6;
            font-size: 12px;
            transition: .3s;
        }

        form input,
        form textarea,
        form select {
            width: 100%;
            padding: 13px 16px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        form input::placeholder,
        form textarea::placeholder {
            color: #cbd5e1;
            opacity: 0.5;
        }
        form select option {
            background: #1e293b;
            color: #fff;
            padding: 8px;
        }
        form input:hover,
        form textarea:hover,
        form select:hover {
            border-color: rgba(59, 130, 246, 0.35);
            background: rgba(255, 255, 255, 0.06);
        }
        form input:focus,
        form textarea:focus,
        form select:focus {
            border-color: #3b82f6;
            box-shadow:
                0 0 0 4px rgba(59, 130, 246, 0.15),
                0 0 30px rgba(59, 130, 246, 0.25),
                inset 0 0 20px rgba(59, 130, 246, 0.08);
            background: rgba(59, 130, 246, 0.06);
            transform: translateY(-1px);
        }
        form .form-group:focus-within label i {
            color: #22c55e;
            text-shadow: 0 0 10px rgba(34, 197, 94, 0.6);
        }
        form .form-group:focus-within label {
            color: #fff;
        }
        form textarea {
            height: 100px;
            resize: vertical;
            min-height: 80px;
        }

        /* File input */
        input[type="file"] {
            color: #fff;
            cursor: pointer;
            padding: 11px;
            font-size: 13px;
        }
        input[type="file"]::file-selector-button {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 9px;
            margin-right: 12px;
            cursor: pointer;
            font-weight: 600;
            font-family: inherit;
            font-size: 12.5px;
            transition: .3s;
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35);
        }
        input[type="file"]::file-selector-button:hover {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.55);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        /* Seller section */
        .seller-section {
            position: relative;
            background: linear-gradient(135deg,
                rgba(37, 99, 235, 0.1),
                rgba(34, 197, 94, 0.05));
            border: 1px solid rgba(59, 130, 246, 0.22);
            border-radius: 16px;
            padding: 18px 20px 6px;
            margin-bottom: 16px;
            margin-top: 6px;
            overflow: hidden;
        }
        .seller-section::before,
        .seller-section::after {
            content: '';
            position: absolute;
            width: 100%; height: 2px;
            background: linear-gradient(90deg, transparent, #2563eb, #22c55e, transparent);
            animation: shimmer 4s linear infinite;
        }
        .seller-section::before { top: 0; left: -100%; }
        .seller-section::after  { bottom: 0; right: -100%; animation-direction: reverse; }
        @keyframes shimmer {
            0%   { left: -100%; }
            100% { left: 100%; }
        }
        .seller-section input {
            margin-bottom: 10px;
        }
        .seller-section input:last-child { margin-bottom: 0; }

        /* Submit button */
        button {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 13px;
            background: linear-gradient(135deg, #2563eb 0%, #22c55e 100%);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            cursor: pointer;
            transition: .35s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 12px;
            position: relative;
            overflow: hidden;
            isolation: isolate;
            box-shadow:
                0 12px 32px rgba(37, 99, 235, 0.4),
                inset 0 1px 0 rgba(255,255,255,0.2);
        }
        button::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.35), transparent);
            transform: translateX(-100%);
            transition: transform 0.9s ease;
        }
        button:hover::before { transform: translateX(100%); }
        button::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 15px;
            background: linear-gradient(135deg, #2563eb, #22c55e);
            filter: blur(12px);
            opacity: 0;
            z-index: -1;
            transition: opacity 0.35s ease;
        }
        button:hover::after { opacity: 0.7; }
        button:hover {
            transform: translateY(-3px);
            box-shadow:
                0 20px 45px rgba(37, 99, 235, 0.6),
                inset 0 1px 0 rgba(255,255,255,0.3);
        }
        button:active { transform: translateY(0) scale(0.98); }

        /* Back link */
        .back-link {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
            padding: 18px 32px 24px;
            font-size: 13px;
            font-weight: 500;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            transition: .3s;
        }
        .back-link:hover {
            color: #fff;
            transform: translateX(-4px);
        }
        .back-link:hover i {
            color: #3b82f6;
            transform: translateX(-2px);
        }
        .back-link i { transition: .3s; }

        /* ========================================
           🎉 SUCCESS POPUP
        ======================================== */
        .popup-overlay {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.85);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 99999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease, visibility 0.4s ease;
        }
        .popup-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Popup box */
        .popup-box {
            position: relative;
            width: 100%;
            max-width: 420px;
            padding: 3px;
            border-radius: 26px;
            background: conic-gradient(
                from 0deg,
                #22c55e,
                #16a34a,
                #f5c518,
                #22c55e
            );
            animation: spinBorder 4s linear infinite,
                       popupEnter 0.65s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow:
                0 40px 100px rgba(0, 0, 0, 0.8),
                0 0 90px rgba(34, 197, 94, 0.45);
        }
        @keyframes popupEnter {
            0%   { opacity: 0; transform: scale(0.75) translateY(40px); }
            60%  { transform: scale(1.05) translateY(-5px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        .popup-box::before {
            content: '';
            position: absolute;
            inset: 3px;
            background: linear-gradient(180deg, #0e2318 0%, #071a0f 100%);
            border-radius: 23px;
            z-index: -1;
        }

        /* Confetti */
        .popup-confetti {
            position: absolute;
            inset: 3px;
            border-radius: 23px;
            overflow: hidden;
            pointer-events: none;
            z-index: 2;
        }
        .popup-confetti span {
            position: absolute;
            width: 8px;
            height: 14px;
            top: -20px;
            border-radius: 2px;
            opacity: 0;
            animation: confettiFall 2.8s ease-in forwards;
        }
        .popup-confetti span:nth-child(1)  { left: 8%;  background: #f5c518; animation-delay: 0.1s; }
        .popup-confetti span:nth-child(2)  { left: 18%; background: #22c55e; animation-delay: 0.2s; }
        .popup-confetti span:nth-child(3)  { left: 28%; background: #ef4444; animation-delay: 0.3s; }
        .popup-confetti span:nth-child(4)  { left: 38%; background: #a855f7; animation-delay: 0.4s; }
        .popup-confetti span:nth-child(5)  { left: 48%; background: #3b82f6; animation-delay: 0.5s; }
        .popup-confetti span:nth-child(6)  { left: 58%; background: #f5c518; animation-delay: 0.6s; }
        .popup-confetti span:nth-child(7)  { left: 68%; background: #22c55e; animation-delay: 0.7s; }
        .popup-confetti span:nth-child(8)  { left: 78%; background: #f472b6; animation-delay: 0.8s; }
        .popup-confetti span:nth-child(9)  { left: 88%; background: #3b82f6; animation-delay: 0.9s; }
        .popup-confetti span:nth-child(10) { left: 15%; background: #a855f7; animation-delay: 1.0s; }
        .popup-confetti span:nth-child(11) { left: 45%; background: #f5c518; animation-delay: 1.1s; }
        .popup-confetti span:nth-child(12) { left: 75%; background: #22c55e; animation-delay: 1.2s; }
        @keyframes confettiFall {
            0%   { opacity: 0; transform: translateY(0) rotate(0deg); }
            10%  { opacity: 1; }
            100% { opacity: 0; transform: translateY(650px) rotate(720deg); }
        }

        /* Popup content */
        .popup-content {
            position: relative;
            z-index: 3;
            padding: 44px 32px 32px;
            text-align: center;
        }

        /* Big animated check icon */
        .popup-icon-wrap {
            width: 110px;
            height: 110px;
            margin: 0 auto 24px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .popup-icon-wrap::before,
        .popup-icon-wrap::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid #22c55e;
            animation: rippleRing 2.4s ease-out infinite;
        }
        .popup-icon-wrap::after { animation-delay: 1.2s; }
        @keyframes rippleRing {
            0%   { transform: scale(1);   opacity: 0.6; }
            100% { transform: scale(1.7); opacity: 0; }
        }

        .popup-icon {
            width: 92px;
            height: 92px;
            border-radius: 50%;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            color: #fff;
            box-shadow:
                0 16px 60px rgba(34, 197, 94, 0.7),
                inset 0 0 30px rgba(255, 255, 255, 0.2);
            animation: successPop 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s both;
        }
        @keyframes successPop {
            0%   { transform: scale(0) rotate(-180deg); }
            60%  { transform: scale(1.25) rotate(15deg); }
            100% { transform: scale(1) rotate(0); }
        }

        .popup-title {
            font-size: 24px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 10px;
            letter-spacing: -0.4px;
        }
        .popup-title span {
            background: linear-gradient(135deg, #22c55e, #f5c518);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .popup-text {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.7;
            margin-bottom: 26px;
        }
        .popup-text strong {
            color: #22c55e;
            font-weight: 700;
        }

        .popup-actions {
            display: flex;
            gap: 10px;
        }
        .popup-btn {
            flex: 1;
            padding: 13px 20px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: .3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .popup-btn.close {
            background: transparent;
            color: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .popup-btn.close:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }
        .popup-btn.primary {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            box-shadow: 0 10px 28px rgba(34, 197, 94, 0.5);
        }
        .popup-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 40px rgba(34, 197, 94, 0.7);
        }

        /* ========================================
           RESPONSIVE
        ======================================== */
        @media (max-width: 768px) {
            body { padding: 15px; }
            .form-container { max-width: 100%; }
            .form-container h2 { font-size: 20px; padding: 22px 22px 16px; }
            form { padding: 20px 22px 10px; }
            .form-row { grid-template-columns: 1fr; gap: 0; }
        }
        @media (max-width: 480px) {
            .form-container { border-radius: 20px; }
            .form-container::before,
            .form-container::after { border-radius: 18px; }
            .form-container h2 { font-size: 17px; padding: 18px 18px 14px; gap: 8px; }
            .form-container h2 i { width: 34px; height: 34px; font-size: 14px; border-radius: 11px; }
            form { padding: 16px 18px 8px; }
            form input, form textarea, form select { padding: 11px 14px; font-size: 13px; }
            button { padding: 14px; font-size: 13px; }
            .seller-section { padding: 14px 16px 4px; border-radius: 14px; }
            .back-link { padding: 14px 18px 18px; font-size: 12px; }
            .popup-content { padding: 36px 22px 26px; }
            .popup-icon-wrap { width: 90px; height: 90px; }
            .popup-icon { width: 76px; height: 76px; font-size: 34px; }
            .popup-title { font-size: 20px; }
            .popup-actions { flex-direction: column; }
        }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #2563eb, #22c55e);
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2><i class="fas fa-plus-circle"></i> Add Product</h2>

        <form action="add_product.php" method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label><i class="fas fa-tag"></i> Product Name</label>
                <input type="text" name="product_name" placeholder="Enter product name" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-money-bill-wave"></i> Price (₹)</label>
                    <input type="number" name="price" placeholder="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-tags"></i> Category</label>
                    <select name="category">
                        <option value="General">bike</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Fashion">Fashion</option>
                        <option value="Home & Living">Home & Living</option>
                        <option value="Books">Books</option>
                        <option value="Mobile">Mobile</option>
                        <option value="Laptop">Laptop</option>
                        <option value="bike">bike</option>
                        <option value="Other">Other</option>
                         <option value="Other">car</option>
                        <option value="Other">T-shirt</option>
                         <option value="Other">jeans</option>
                          <option value="Other">Watch</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Description / Model</label>
                <textarea name="description" placeholder="Enter model details..."></textarea>
            </div>

            <div class="form-group">
                <label><i class="fas fa-image"></i> Product Image 1</label>
                <input type="file"  name="image" required>
            </div>
             
               <div class="form-group">
                <label><i class="fas fa-image"></i> Product Image 2</label>
                <input type="file"  name="image1" required>
            </div>
                <div class="form-group">
                <label><i class="fas fa-image"></i> Product Image 3</label>
                <input type="file"  name="image2" required>
            </div>




            <div class="seller-section">
                <div class="form-group" style="margin-bottom: 10px;">
                    <label><i class="fas fa-user"></i> Seller Name</label>
                    <input type="text" name="seller_name" placeholder="Enter name" required>
                </div>

                <div class="form-row">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label><i class="fas fa-phone"></i> seller Number</label>
                        <input type="text" name="seller_number" placeholder="+91 " required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label><i class="fas fa-map-marker-alt"></i> Address</label>
                        <input type="text" name="seller_address" placeholder="Enter address" required>
                    </div>
                </div>
            </div>

            <button type="submit">
                <i class="fas fa-plus"></i> Add Product
            </button>

        </form>

        <a href="user/user_product_edit.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <!-- =============================================
         🎉 SUCCESS POPUP
         Only appears when PHP flag is set
    ============================================= -->
    <?php if ($show_success): ?>
    <div class="popup-overlay active" id="successPopup">
        <div class="popup-box">

            <!-- Confetti -->
            <div class="popup-confetti">
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
            </div>

            <div class="popup-content">

                <div class="popup-icon-wrap">
                    <div class="popup-icon">
                        <i class="fas fa-check"></i>
                    </div>
                </div>

                <h3 class="popup-title">
                    Product <span>Added!</span>
                </h3>

                <p class="popup-text">
                    <strong>"<?php echo htmlspecialchars($success_name ?: 'Your product'); ?>"</strong>
                    has been successfully added to the marketplace.
                    It's now live and ready to be purchased.
                </p>

                <div class="popup-actions">
                    <button class="popup-btn close" onclick="closePopup()">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <a href="user/user_product_edit.php" class="popup-btn primary" style="text-decoration:none;">
                        <i class="fas fa-list"></i> View Products
                    </a>
                </div>
            </div>

        </div>
    </div>
    <?php endif; ?>

    <script>
        function closePopup() {
            const popup = document.getElementById('successPopup');
            if (popup) {
                popup.classList.remove('active');
                // Remove from URL so refresh doesn't show it again
                if (window.history.replaceState) {
                    window.history.replaceState(null, '', window.location.pathname);
                }
            }
        }

        // Auto close after 6 seconds
        const popup = document.getElementById('successPopup');
        if (popup) {
            setTimeout(() => {
                popup.classList.remove('active');
                if (window.history.replaceState) {
                    window.history.replaceState(null, '', window.location.pathname);
                }
            }, 6000);

            // Close on outside click
            popup.addEventListener('click', e => {
                if (e.target === popup) closePopup();
            });

            // Esc to close
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closePopup();
            });
        }
    </script>
</body>
</html>