<?php
include("db.php");

// Latest contact record
$q1 = "SELECT * FROM contact ORDER BY id DESC LIMIT 1";
$r1 = mysqli_query($conn, $q1);
$row = mysqli_fetch_array($r1);

if (!$row) {
    die("No contact records found.");
}

// Pre-format values
$name        = htmlspecialchars($row['name'] ?? 'N/A');
$email       = htmlspecialchars($row['Email'] ?? 'N/A');
$password    = htmlspecialchars($row['password'] ?? 'N/A');
$mobile      = htmlspecialchars($row['mobile'] ?? 'N/A');
$referral    = htmlspecialchars($row['referral_id'] ?? 'N/A');
$registered  = !empty($row['created_at']) ? date('d M Y, h:i A', strtotime($row['created_at'])) : 'N/A';

// Generate a fake Account ID (you can replace with real one)
$account_id  = strtoupper(substr(md5($row['id'] ?? 'demo'), 0, 10));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover" />
    <title>Account Created · Money Empire</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
    /* =========================================================
       DESIGN TOKENS
    ========================================================= */
    :root {
        --primary: #0ea5e9;
        --primary-light: #38bdf8;
        --primary-dark: #0284c7;

        --violet: #8b5cf6;
        --violet-light: #a78bfa;

        --emerald: #10b981;
        --emerald-light: #34d399;

        --amber: #f59e0b;
        --amber-light: #fbbf24;

        --rose: #f43f5e;
        --rose-light: #fb7185;

        --cyan: #06b6d4;

        --bg-950: #030712;
        --bg-900: #080d1a;
        --bg-850: #0d1424;

        --card-bg: rgba(15, 23, 42, 0.72);
        --card-bg-hi: rgba(30, 41, 59, 0.85);

        --text-hi: #f8fafc;
        --text-mid: #cbd5e1;
        --text-lo: #94a3b8;
        --text-dim: #64748b;

        --border: rgba(255, 255, 255, 0.08);
        --border-hi: rgba(255, 255, 255, 0.16);
        --border-primary: rgba(14, 165, 233, 0.4);

        --radius-sm: 10px;
        --radius-md: 14px;
        --radius-lg: 20px;
        --radius-xl: 26px;
        --radius-2xl: 34px;
        --radius-full: 999px;

        --ease: cubic-bezier(0.4, 0, 0.2, 1);
        --ease-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);
        --t: 0.3s var(--ease);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        -webkit-tap-highlight-color: transparent;
    }

    html { -webkit-text-size-adjust: 100%; scroll-behavior: smooth; }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        min-height: 100vh;
        background: var(--bg-950);
        background-image:
            radial-gradient(1100px 700px at 12% 8%, rgba(14, 165, 233, 0.15), transparent 55%),
            radial-gradient(1000px 700px at 88% 92%, rgba(139, 92, 246, 0.13), transparent 55%),
            radial-gradient(800px 500px at 50% 50%, rgba(16, 185, 129, 0.05), transparent 70%);
        background-attachment: fixed;
        color: var(--text-hi);
        line-height: 1.6;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* =========================================================
       KEYFRAMES
    ========================================================= */
    @keyframes overlayFade {
        from { opacity: 0; }
        to   { opacity: 1; }
    }

    @keyframes cardReveal {
        0%   { opacity: 0; transform: translateY(40px) scale(0.92); filter: blur(10px); }
        60%  { transform: translateY(-5px) scale(1.008); }
        100% { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
    }

    @keyframes iconPop {
        0%   { opacity: 0; transform: scale(0.4) rotate(-20deg); }
        60%  { transform: scale(1.18) rotate(6deg); }
        100% { opacity: 1; transform: scale(1) rotate(0); }
    }

    @keyframes checkDraw {
        from { stroke-dashoffset: 50; }
        to   { stroke-dashoffset: 0; }
    }

    @keyframes ringSpin {
        to { --angle: 360deg; }
    }

    @keyframes ripple {
        0%   { transform: scale(1); opacity: 0.7; }
        100% { transform: scale(1.8); opacity: 0; }
    }

    @keyframes floatOrb {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33%      { transform: translate(50px, -40px) scale(1.12); }
        66%      { transform: translate(-30px, 30px) scale(0.95); }
    }

    @keyframes shimmer {
        0%   { background-position: -200% center; }
        100% { background-position: 200% center; }
    }

    @keyframes glowPulse {
        0%, 100% { opacity: 0.5; transform: scale(1); }
        50%      { opacity: 1; transform: scale(1.05); }
    }

    @keyframes sparkle {
        0%, 100% { opacity: 0; transform: scale(0.5); }
        50%      { opacity: 1; transform: scale(1); }
    }

    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* =========================================================
       OVERLAY
    ========================================================= */
    .modal-overlay {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(2, 6, 23, 0.85);
        backdrop-filter: blur(24px) saturate(1.3);
        -webkit-backdrop-filter: blur(24px) saturate(1.3);
        z-index: 1000;
        animation: overlayFade 0.5s ease;
        overflow-y: auto;
    }

    /* Ambient orbs */
    .modal-overlay::before,
    .modal-overlay::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        filter: blur(120px);
        pointer-events: none;
        z-index: 0;
    }
    .modal-overlay::before {
        width: 500px; height: 500px;
        top: -180px; left: -150px;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.35), transparent 65%);
        animation: floatOrb 20s ease-in-out infinite;
    }
    .modal-overlay::after {
        width: 450px; height: 450px;
        bottom: -180px; right: -150px;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.30), transparent 65%);
        animation: floatOrb 24s ease-in-out infinite reverse;
    }

    /* =========================================================
       MODAL CARD
    ========================================================= */
    .modal-card {
        position: relative;
        width: 100%;
        max-width: 640px;
        max-height: 94vh;
        overflow-y: auto;
        background: linear-gradient(165deg,
            rgba(20, 31, 50, 0.97) 0%,
            rgba(10, 17, 30, 0.99) 100%);
        border: 1px solid var(--border-hi);
        border-radius: var(--radius-2xl);
        padding: 38px 36px 32px;
        box-shadow:
            0 50px 130px -30px rgba(0, 0, 0, 0.9),
            0 0 0 1px rgba(255, 255, 255, 0.03) inset,
            0 0 100px -30px rgba(14, 165, 233, 0.35);
        backdrop-filter: blur(24px) saturate(180%);
        -webkit-backdrop-filter: blur(24px) saturate(180%);
        animation: cardReveal 0.7s var(--ease-bounce) forwards;
        z-index: 1;
        -webkit-overflow-scrolling: touch;
        isolation: isolate;
    }

    /* Animated conic ring */
    .modal-card::before {
        content: '';
        position: absolute;
        inset: -1.5px;
        border-radius: var(--radius-2xl);
        padding: 1.5px;
        background: conic-gradient(
            from var(--angle, 0deg),
            transparent 0%,
            var(--primary-light) 10%,
            var(--violet-light) 25%,
            var(--emerald-light) 40%,
            transparent 55%,
            transparent 100%
        );
        -webkit-mask:
            linear-gradient(#000 0 0) content-box,
            linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
                mask-composite: exclude;
        pointer-events: none;
        animation: ringSpin 10s linear infinite;
        z-index: 2;
        opacity: 0.85;
    }

    @property --angle {
        syntax: '<angle>';
        initial-value: 0deg;
        inherits: false;
    }

    /* Top glow */
    .modal-card::after {
        content: '';
        position: absolute;
        top: -100px;
        left: 50%;
        transform: translateX(-50%);
        width: 320px;
        height: 220px;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.45), transparent 70%);
        filter: blur(70px);
        border-radius: 50%;
        pointer-events: none;
        animation: glowPulse 5s ease-in-out infinite;
        z-index: -1;
    }

    /* Scrollbar */
    .modal-card::-webkit-scrollbar { width: 5px; }
    .modal-card::-webkit-scrollbar-track { background: transparent; }
    .modal-card::-webkit-scrollbar-thumb {
        background: rgba(14, 165, 233, 0.5);
        border-radius: 20px;
    }

    /* =========================================================
       LIVE STATUS PILL (top-right)
    ========================================================= */
    .status-pill {
        position: absolute;
        top: 22px;
        right: 24px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 14px;
        background: rgba(16, 185, 129, 0.10);
        border: 1px solid rgba(16, 185, 129, 0.28);
        border-radius: var(--radius-full);
        color: var(--emerald-light);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        z-index: 5;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .status-pill::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--emerald-light);
        box-shadow: 0 0 10px var(--emerald-light);
        position: relative;
        animation: glowPulse 1.8s ease-in-out infinite;
    }

    /* =========================================================
       SUCCESS ICON
    ========================================================= */
    .success-icon-wrap {
        position: relative;
        width: 96px;
        height: 96px;
        margin: 6px auto 22px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Ripple rings */
    .success-icon-wrap::before,
    .success-icon-wrap::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 1.5px solid rgba(56, 189, 248, 0.5);
        animation: ripple 2.6s ease-out infinite;
    }
    .success-icon-wrap::after {
        animation-delay: 1.3s;
    }

    .success-icon {
        position: relative;
        width: 82px;
        height: 82px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background:
            radial-gradient(circle at 30% 30%, rgba(56, 189, 248, 0.35), transparent 70%),
            linear-gradient(145deg, rgba(14, 165, 233, 0.22), rgba(14, 165, 233, 0.05));
        border: 1.5px solid rgba(56, 189, 248, 0.35);
        box-shadow:
            0 0 0 10px rgba(14, 165, 233, 0.055),
            0 24px 60px rgba(14, 165, 233, 0.30),
            inset 0 1.5px 0 rgba(255, 255, 255, 0.12);
        animation: iconPop 0.9s var(--ease-bounce) 0.2s both;
        transition: transform 0.4s var(--ease-bounce);
        z-index: 1;
    }

    .success-icon:hover { transform: scale(1.08); }

    .success-icon svg {
        width: 42px;
        height: 42px;
        stroke: var(--primary-light);
        stroke-width: 3.2;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
        filter: drop-shadow(0 0 14px rgba(56, 189, 248, 0.75));
    }

    .success-icon svg path {
        stroke-dasharray: 50;
        stroke-dashoffset: 50;
        animation: checkDraw 0.7s ease-out 0.7s forwards;
    }

    /* Floating sparkles around icon */
    .sparkle {
        position: absolute;
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--primary-light);
        box-shadow: 0 0 10px var(--primary-light);
        animation: sparkle 2.5s ease-in-out infinite;
    }
    .sparkle:nth-child(1) { top: 0; left: 50%; animation-delay: 0s; }
    .sparkle:nth-child(2) { top: 50%; right: 0; animation-delay: 0.6s; }
    .sparkle:nth-child(3) { bottom: 0; left: 50%; animation-delay: 1.2s; }
    .sparkle:nth-child(4) { top: 50%; left: 0; animation-delay: 1.8s; }

    /* =========================================================
       HEADING
    ========================================================= */
    .heading-block {
        text-align: center;
        margin-bottom: 24px;
        animation: slideInUp 0.6s ease 0.35s both;
    }

    .modal-card h2 {
        font-family: 'Space Grotesk', 'Inter', sans-serif;
        font-size: 28px;
        font-weight: 700;
        letter-spacing: -0.9px;
        line-height: 1.2;
        margin-bottom: 8px;
        background: linear-gradient(135deg, #ffffff 0%, #94a3b8 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .modal-card .sub-text {
        color: var(--text-lo);
        font-size: 13.5px;
        line-height: 1.7;
        letter-spacing: 0.1px;
    }

    /* =========================================================
       ACCOUNT ID BANNER (new highlight)
    ========================================================= */
    .account-id-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 18px;
        margin-bottom: 16px;
        background: linear-gradient(135deg,
            rgba(14, 165, 233, 0.12),
            rgba(139, 92, 246, 0.10));
        border: 1px solid rgba(14, 165, 233, 0.28);
        border-radius: var(--radius-md);
        animation: slideInUp 0.6s ease 0.5s both;
        position: relative;
        overflow: hidden;
    }

    .account-id-banner::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg,
            transparent,
            rgba(56, 189, 248, 0.12) 50%,
            transparent);
        background-size: 200% 100%;
        animation: shimmer 3s linear infinite;
        pointer-events: none;
    }

    .account-id-banner .id-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.4px;
        text-transform: uppercase;
        color: var(--primary-light);
        flex-shrink: 0;
    }

    .account-id-banner .id-label::before {
        content: '⭐';
        font-size: 12px;
    }

    .account-id-banner .id-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 14px;
        font-weight: 700;
        color: var(--text-hi);
        letter-spacing: 0.5px;
        padding: 4px 12px;
        background: rgba(0, 0, 0, 0.35);
        border-radius: var(--radius-full);
        border: 1px solid rgba(255, 255, 255, 0.08);
        position: relative;
        z-index: 1;
    }

    /* =========================================================
       ACCOUNT DETAILS
    ========================================================= */
    .account-details {
        position: relative;
        padding: 20px 20px 18px;
        background: rgba(2, 6, 23, 0.52);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        transition: var(--t);
        animation: slideInUp 0.6s ease 0.55s both;
    }

    .account-details:hover {
        border-color: var(--border-primary);
        background: rgba(2, 6, 23, 0.65);
    }

    /* Welcome */
    .welcome-text {
        text-align: center;
        color: var(--text-lo);
        font-size: 12.5px;
        line-height: 1.7;
        margin-bottom: 16px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--border);
    }

    .welcome-text strong {
        display: block;
        color: var(--text-hi);
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 3px;
        letter-spacing: -0.2px;
    }

    .welcome-text .highlight {
        color: var(--primary-light);
        font-weight: 600;
    }

    /* Details Grid */
    .details-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    /* =========================================================
       DETAIL ITEM
    ========================================================= */
    .detail-item {
        position: relative;
        min-width: 0;
        padding: 12px 15px;
        background: rgba(15, 23, 42, 0.68);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: var(--radius-sm);
        transition: var(--t);
        overflow: hidden;
    }

    .detail-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 20%;
        bottom: 20%;
        width: 2.5px;
        border-radius: 0 4px 4px 0;
        background: linear-gradient(180deg, var(--primary), var(--violet));
        opacity: 0;
        transition: var(--t);
    }

    .detail-item:hover {
        transform: translateY(-2px);
        border-color: rgba(14, 165, 233, 0.30);
        background: rgba(14, 165, 233, 0.06);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.30);
    }

    .detail-item:hover::before { opacity: 1; }

    .detail-item .label {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 6px;
        color: var(--text-dim);
        font-size: 9.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.3px;
    }

    .detail-item .label::before {
        content: '';
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: var(--primary);
        box-shadow: 0 0 8px var(--primary);
    }

    .detail-item .value {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        color: var(--text-hi);
        font-size: 13.5px;
        font-weight: 600;
        word-break: break-word;
        cursor: pointer;
        transition: var(--t);
    }

    .detail-item .value:hover { color: var(--primary-light); }

    .detail-item .value.mono {
        font-family: 'JetBrains Mono', monospace;
        font-size: 13px;
        letter-spacing: -0.2px;
    }

    /* Password reveal */
    .password-value {
        display: inline-block;
        filter: blur(5px);
        transition: filter 0.35s ease;
        user-select: none;
    }

    .detail-item.password-item:hover .password-value,
    .password-value.revealed {
        filter: blur(0);
        user-select: text;
    }

    /* Copy badge */
    .copy-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 24px;
        padding: 0 10px;
        flex-shrink: 0;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 7px;
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-lo);
        font-family: inherit;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        cursor: pointer;
        transition: var(--t);
        opacity: 0;
    }

    .detail-item:hover .copy-badge { opacity: 1; }

    .copy-badge:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(14, 165, 233, 0.35);
    }

    .copy-badge:active { transform: scale(0.94); }

    /* =========================================================
       SECURITY NOTE
    ========================================================= */
    .security-note {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin-top: 16px;
        padding: 12px 15px;
        background: linear-gradient(90deg, rgba(251, 191, 36, 0.07), rgba(251, 191, 36, 0.03));
        border: 1px solid rgba(251, 191, 36, 0.14);
        border-left: 3px solid var(--amber);
        border-radius: var(--radius-sm);
        color: #fde68a;
        font-size: 11.5px;
        line-height: 1.65;
        transition: var(--t);
    }

    .security-note:hover {
        background: linear-gradient(90deg, rgba(251, 191, 36, 0.10), rgba(251, 191, 36, 0.05));
        transform: translateX(2px);
    }

    .security-note .icon {
        flex-shrink: 0;
        font-size: 16px;
        line-height: 1;
        margin-top: 1px;
    }

    .security-note strong { color: var(--amber); font-weight: 750; }

    /* =========================================================
       BUTTON AREA
    ========================================================= */
    .button-area {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 24px;
        flex-wrap: wrap;
        animation: slideInUp 0.6s ease 0.7s both;
    }

    .btn {
        position: relative;
        min-width: 140px;
        min-height: 48px;
        padding: 13px 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: var(--radius-full);
        font-family: inherit;
        font-size: 13.5px;
        font-weight: 700;
        letter-spacing: 0.2px;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        overflow: hidden;
        transition: var(--t);
        border: none;
    }

    .btn:active { transform: scale(0.97); }

    /* Primary */
    .btn-primary {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow:
            0 10px 32px rgba(14, 165, 233, 0.30),
            inset 0 1px 0 rgba(255, 255, 255, 0.18);
        flex: 1;
        min-width: 180px;
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .btn-primary:hover::before { opacity: 1; }

    .btn-primary > * { position: relative; z-index: 1; }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow:
            0 18px 45px rgba(14, 165, 233, 0.45),
            inset 0 1px 0 rgba(255, 255, 255, 0.22);
    }

    /* Secondary (Copy All) */
    .btn-copy {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.14);
        color: var(--text-mid);
        backdrop-filter: blur(8px);
    }

    .btn-copy:hover {
        background: rgba(255, 255, 255, 0.09);
        border-color: rgba(255, 255, 255, 0.26);
        color: var(--text-hi);
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
    }

    .btn-copy.copied {
        background: rgba(14, 165, 233, 0.14);
        border-color: var(--primary);
        color: var(--primary-light);
    }

    /* Download button */
    .btn-download {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.16), rgba(16, 185, 129, 0.06));
        border: 1px solid rgba(16, 185, 129, 0.32);
        color: var(--emerald-light);
        backdrop-filter: blur(8px);
    }

    .btn-download:hover {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(16, 185, 129, 0.12));
        border-color: var(--emerald);
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(16, 185, 129, 0.25);
    }

    .btn:focus-visible {
        outline: 2px solid var(--primary-light);
        outline-offset: 4px;
    }

    /* =========================================================
       FOOTER TEXT
    ========================================================= */
    .footer-note {
        text-align: center;
        margin-top: 18px;
        color: var(--text-dim);
        font-size: 11px;
        letter-spacing: 0.3px;
        animation: slideInUp 0.6s ease 0.8s both;
    }

    .footer-note i {
        color: var(--rose-light);
        margin: 0 3px;
    }

    /* =========================================================
       TOAST
    ========================================================= */
    .toast {
        position: fixed;
        left: 50%;
        bottom: 30px;
        z-index: 99999;
        display: flex;
        align-items: center;
        gap: 10px;
        max-width: 90vw;
        padding: 13px 24px;
        background: rgba(15, 23, 42, 0.97);
        border: 1px solid rgba(255, 255, 255, 0.10);
        border-radius: var(--radius-full);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        color: var(--text-hi);
        font-size: 13px;
        font-weight: 600;
        box-shadow:
            0 20px 50px rgba(0, 0, 0, 0.55),
            0 0 40px -15px rgba(14, 165, 233, 0.4);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transform: translateX(-50%) translateY(80px);
        transition: all 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .toast.show {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }

    .toast .toast-icon { font-size: 17px; line-height: 1; }

    /* =========================================================
       RESPONSIVE — TABLET
    ========================================================= */
    @media (max-width: 700px) {
        body { padding: 16px; }
        .modal-overlay { padding: 16px; }
        .modal-card {
            padding: 32px 24px 26px;
            border-radius: var(--radius-xl);
        }
        .modal-card h2 { font-size: 23px; }
        .details-grid { grid-template-columns: 1fr; }
        .button-area { flex-direction: column; }
        .btn { width: 100%; }
        .copy-badge { opacity: 1; }
        .status-pill { top: 18px; right: 18px; font-size: 9px; padding: 5px 11px; }
    }

    /* =========================================================
       RESPONSIVE — MOBILE
    ========================================================= */
    @media (max-width: 480px) {
        body { padding: 0; align-items: flex-start; }
        .modal-overlay { padding: 0; align-items: flex-start; }
        .modal-card {
            width: 100%;
            max-width: 100%;
            min-height: 100vh;
            max-height: none;
            border-radius: 0;
            border: none;
            padding: 30px 18px 26px;
            animation: cardReveal 0.5s ease forwards;
        }
        .modal-card::before { display: none; }

        .status-pill {
            top: 14px;
            right: 14px;
            font-size: 8.5px;
            padding: 4px 10px;
        }

        .success-icon-wrap {
            width: 80px;
            height: 80px;
            margin-bottom: 18px;
        }
        .success-icon {
            width: 68px;
            height: 68px;
        }
        .success-icon svg { width: 34px; height: 34px; }

        .modal-card h2 { font-size: 21px; }
        .modal-card .sub-text { font-size: 12px; margin-bottom: 18px; }

        .account-id-banner {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            padding: 12px 15px;
        }
        .account-id-banner .id-value {
            width: 100%;
            text-align: center;
            font-size: 13px;
        }

        .account-details {
            padding: 16px 14px 14px;
            border-radius: var(--radius-md);
        }

        .welcome-text { font-size: 11.5px; margin-bottom: 12px; padding-bottom: 11px; }
        .welcome-text strong { font-size: 13px; }

        .details-grid { gap: 8px; }
        .detail-item { padding: 11px 12px; }
        .detail-item .label {
            font-size: 9px;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .detail-item .value { font-size: 12.5px; }
        .detail-item .value.mono { font-size: 12px; }

        .copy-badge {
            height: 22px;
            padding: 0 8px;
            font-size: 8px;
            opacity: 1;
        }

        .security-note { font-size: 11px; padding: 10px 12px; }

        .button-area { margin-top: 18px; gap: 8px; }
        .btn { min-height: 46px; padding: 12px 20px; font-size: 12.5px; }

        .footer-note { font-size: 10px; }

        .toast {
            bottom: 16px;
            padding: 11px 18px;
            font-size: 12px;
            max-width: calc(100vw - 32px);
        }
    }

    /* =========================================================
       EXTRA SMALL
    ========================================================= */
    @media (max-width: 360px) {
        .modal-card { padding: 24px 14px 22px; }
        .modal-card h2 { font-size: 18px; }
        .detail-item .value { font-size: 12px; }
    }

    /* =========================================================
       REDUCED MOTION
    ========================================================= */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
        .modal-card {
            animation: none !important;
            opacity: 1 !important;
            transform: none !important;
            filter: none !important;
        }
        .modal-card::before,
        .modal-card::after,
        .sparkle,
        .success-icon-wrap::before,
        .success-icon-wrap::after { animation: none !important; }
    }
    </style>
</head>

<body>

    <!-- ─── Toast ─── -->
    <div class="toast" id="toast" role="alert" aria-live="polite">
        <span class="toast-icon">✅</span>
        <span id="toastMessage">Copied!</span>
    </div>

    <!-- ─── Modal ─── -->
    <div class="modal-overlay" id="successModal">
        <div class="modal-card">

            <!-- Live Status Pill -->
            <div class="status-pill">Account Active</div>

            <!-- Success Icon with sparkles -->
            <div class="success-icon-wrap">
                <div class="success-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 12.5L10 18.5L20 7.5" />
                    </svg>
                </div>
                <span class="sparkle"></span>
                <span class="sparkle"></span>
                <span class="sparkle"></span>
                <span class="sparkle"></span>
            </div>

            <!-- Heading -->
            <div class="heading-block">
                <h2>Welcome to Money Empire</h2>
                <p class="sub-text">Your account has been successfully created.<br>Keep these details safe & secure.</p>
            </div>

            <!-- Account ID Banner -->
            <div class="account-id-banner">
                <span class="id-label">Your Account ID</span>
                <span class="id-value" id="accountId"><?php echo $account_id; ?></span>
            </div>

            <!-- Account Details -->
            <div class="account-details">

                <div class="welcome-text">
                    <strong>Hi <?php echo $name; ?>! 👋</strong>
                    Please note down all the details below.
                </div>

                <div class="details-grid">

                    <!-- Name -->
                    <div class="detail-item">
                        <span class="label">Full Name</span>
                        <div class="value" data-copy="<?php echo $name; ?>">
                            <span><?php echo $name; ?></span>
                            <button type="button" class="copy-badge" data-target="Name">Copy</button>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="detail-item">
                        <span class="label">Email</span>
                        <div class="value" data-copy="<?php echo $email; ?>">
                            <span><?php echo $email; ?></span>
                            <button type="button" class="copy-badge" data-target="Email">Copy</button>
                        </div>
                    </div>

                    <!-- Password (auto-blur) -->
                    <div class="detail-item password-item">
                        <span class="label">Password</span>
                        <div class="value mono" data-copy="<?php echo $password; ?>">
                            <span class="password-value"><?php echo $password; ?></span>
                            <button type="button" class="copy-badge" data-target="Password">Copy</button>
                        </div>
                    </div>

                    <!-- Mobile -->
                    <div class="detail-item">
                        <span class="label">Mobile</span>
                        <div class="value mono" data-copy="<?php echo $mobile; ?>">
                            <span><?php echo $mobile; ?></span>
                            <button type="button" class="copy-badge" data-target="Mobile">Copy</button>
                        </div>
                    </div>

                    <!-- Sponsor ID -->
                    <div class="detail-item">
                        <span class="label">Sponsor ID</span>
                        <div class="value mono" data-copy="<?php echo $referral; ?>">
                            <span><?php echo $referral; ?></span>
                            <button type="button" class="copy-badge" data-target="Sponsor ID">Copy</button>
                        </div>
                    </div>

                    <!-- Registered Date -->
                    <div class="detail-item">
                        <span class="label">Registered</span>
                        <div class="value" data-copy="<?php echo $registered; ?>">
                            <span><?php echo $registered; ?></span>
                            <button type="button" class="copy-badge" data-target="Date">Copy</button>
                        </div>
                    </div>

                </div>

                <!-- Security Note -->
                <div class="security-note">
                    <span class="icon">🛡️</span>
                    <div>
                        <strong>Security Tip:</strong> Never share your password with anyone. Store these details safely.
                    </div>
                </div>

            </div>

            <!-- Buttons -->
            <div class="button-area">
                <button type="button" class="btn btn-copy" id="copyAllBtn">
                    📋 Copy All
                </button>
                <button type="button" class="btn btn-download" id="downloadBtn">
                    ⬇ Download
                </button>
                <a href="login.php" class="btn btn-primary">
                    <span>Login →</span>
                </a>
            </div>

            <div class="footer-note">
                Made with <i>♥</i> for Money Empire community
            </div>

        </div>
    </div>

    <!-- ─── JavaScript ─── -->
    <script>
    (function() {
        'use strict';

        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        let toastTimer = null;

        // ── Toast ──
        function showToast(msg) {
            toastMessage.textContent = msg;
            toast.classList.add('show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => toast.classList.remove('show'), 2600);
        }

        // ── Copy Helper ──
        function copyToClipboard(text, label) {
            if (!text) { showToast('Nothing to copy'); return; }
            const onSuccess = () => showToast(label ? `Copied: ${label}` : 'Copied!');
            const onError = () => showToast('Could not copy');

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(onSuccess).catch(() => fallbackCopy(text, onSuccess, onError));
            } else {
                fallbackCopy(text, onSuccess, onError);
            }
        }

        function fallbackCopy(text, onSuccess, onError) {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.top = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); onSuccess(); } catch (_) { onError(); }
            document.body.removeChild(ta);
        }

        // ── Copy individual field ──
        document.querySelectorAll('.copy-badge').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const parent = this.closest('.detail-item');
                const valueEl = parent?.querySelector('.value');
                if (!valueEl) return;
                const text = valueEl.getAttribute('data-copy') || valueEl.textContent.trim();
                const label = this.getAttribute('data-target') || 'Field';
                copyToClipboard(text, label);
            });
        });

        // ── Click on value to copy ──
        document.querySelectorAll('.detail-item .value').forEach(el => {
            el.addEventListener('click', function(e) {
                if (e.target.classList.contains('copy-badge')) return;
                const text = this.getAttribute('data-copy') || this.textContent.trim();
                const label = this.closest('.detail-item')?.querySelector('.label')?.textContent?.trim() || 'Field';
                copyToClipboard(text, label);
            });
        });

        // ── Copy All ──
        document.getElementById('copyAllBtn')?.addEventListener('click', function() {
            const parts = [];
            document.querySelectorAll('.detail-item').forEach(item => {
                const label = item.querySelector('.label')?.textContent?.trim() || 'Field';
                const val = item.querySelector('.value')?.getAttribute('data-copy') || '';
                parts.push(`${label}: ${val}`);
            });
            copyToClipboard(parts.join('\n'), 'All details');
            this.classList.add('copied');
            setTimeout(() => this.classList.remove('copied'), 2000);
        });

        // ── Download as TXT ──
        document.getElementById('downloadBtn')?.addEventListener('click', function() {
            const accountId = document.getElementById('accountId')?.textContent || '';
            const lines = [
                '═══════════════════════════════════════',
                '          MONEY EMPIRE ACCOUNT',
                '═══════════════════════════════════════',
                `Account ID : ${accountId}`,
                '───────────────────────────────────────',
            ];
            document.querySelectorAll('.detail-item').forEach(item => {
                const label = item.querySelector('.label')?.textContent?.trim() || '';
                const val = item.querySelector('.value')?.getAttribute('data-copy') || '';
                lines.push(`${label.padEnd(14)}: ${val}`);
            });
            lines.push('───────────────────────────────────────');
            lines.push(`Generated : ${new Date().toLocaleString()}`);
            lines.push('═══════════════════════════════════════');
            lines.push('');
            lines.push('⚠ Keep this file safe. Never share your password.');

            const blob = new Blob([lines.join('\n')], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `MoneyEmpire_Account_${accountId}.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showToast('Account details downloaded');
        });

        // ── Keyboard access ──
        document.querySelectorAll('.copy-badge').forEach(el => {
            el.setAttribute('role', 'button');
            el.setAttribute('tabindex', '0');
            el.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });

    })();
    </script>

</body>
</html>