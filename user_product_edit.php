<?php
session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: user_dashboard.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$search = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $sql = "SELECT * FROM products
            WHERE user_id = '$user_id'
            AND product_name LIKE '%$search%'
            ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM products
            WHERE user_id = '$user_id'
            ORDER BY id DESC";
}

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// ✅ Fetch ALL products into array ONCE — fixes image not showing
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
$total_products = count($products);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Products</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<style>
/* =========================================================
   DESIGN TOKENS
========================================================= */
:root {
    --bg-950: #060912;
    --bg-900: #0a0f1c;
    --bg-850: #0f1524;
    --bg-800: #131b2e;
    --bg-750: #1a2339;
    --bg-700: #223047;

    --glass: rgba(19, 27, 46, .68);
    --glass-hi: rgba(26, 35, 57, .82);
    --glass-line: rgba(255, 255, 255, .06);
    --glass-line-hi: rgba(255, 255, 255, .12);

    --brand-400: #34d399;
    --brand-500: #10b981;
    --brand-600: #059669;

    --accent-400: #22d3ee;
    --accent-500: #06b6d4;
    --accent-600: #0891b2;

    --violet-400: #a78bfa;
    --violet-500: #8b5cf6;

    --amber-400: #fbbf24;
    --amber-500: #f59e0b;

    --rose-400: #fb7185;
    --rose-500: #f43f5e;

    --success: #22c55e;
    --warning: #f59e0b;
    --danger:  #ef4444;

    --txt-hi: #f8fafc;
    --txt-mid: #94a3b8;
    --txt-lo: #64748b;
    --txt-dim: #475569;

    --r-sm: 8px;
    --r-md: 12px;
    --r-lg: 16px;
    --r-xl: 20px;
    --r-2xl: 24px;
    --r-3xl: 32px;
    --r-full: 999px;

    --sh-lg: 0 30px 70px -25px rgba(0, 0, 0, .85);
    --sh-xl: 0 50px 120px -30px rgba(0, 0, 0, 1);

    --ease: cubic-bezier(.4, 0, .2, 1);
    --ease-bounce: cubic-bezier(.34, 1.56, .64, 1);
    --t: .25s var(--ease);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    -webkit-tap-highlight-color: transparent;
}

body {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    font-size: 15px;
    line-height: 1.55;
    color: var(--txt-hi);
    background: var(--bg-950);
    background-image:
        radial-gradient(1000px 500px at 0% 0%, rgba(16, 185, 129, .08), transparent 55%),
        radial-gradient(900px 500px at 100% 100%, rgba(6, 182, 212, .08), transparent 55%),
        radial-gradient(700px 400px at 50% 50%, rgba(139, 92, 246, .04), transparent 70%);
    background-attachment: fixed;
    padding: 30px;
    min-height: 100vh;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    overflow-x: hidden;
}

/* Main Container */
.wrapper {
    max-width: 1500px;
    margin: 0 auto;
    background: var(--glass);
    backdrop-filter: blur(24px) saturate(180%);
    -webkit-backdrop-filter: blur(24px) saturate(180%);
    border: 1px solid var(--glass-line);
    border-radius: var(--r-3xl);
    padding: 38px 40px;
    box-shadow: var(--sh-lg);
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s var(--ease);
}

.wrapper::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg,
        transparent,
        var(--brand-500),
        var(--accent-500),
        var(--violet-500),
        transparent);
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Heading */
h2 {
    text-align: center;
    font-size: 36px;
    margin-bottom: 8px;
    font-weight: 800;
    letter-spacing: -1px;
    background: linear-gradient(135deg, var(--brand-400), var(--accent-400), var(--violet-400));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.sub-heading {
    text-align: center;
    color: var(--txt-lo);
    font-size: 12px;
    margin-bottom: 30px;
    font-weight: 600;
    letter-spacing: .2em;
    text-transform: uppercase;
}

/* Stats Bar */
.stats-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--glass-hi);
    border: 1px solid var(--glass-line);
    padding: 16px 24px;
    border-radius: var(--r-xl);
    margin-bottom: 26px;
    flex-wrap: wrap;
    gap: 12px;
}

.stats-bar .count {
    color: var(--txt-mid);
    font-weight: 600;
    font-size: 14px;
}

.stats-bar .count i {
    margin-right: 8px;
    color: var(--brand-400);
}

.stats-bar .count span {
    background: linear-gradient(135deg, var(--brand-500), var(--brand-600));
    color: #fff;
    padding: 3px 14px;
    border-radius: var(--r-full);
    font-weight: 700;
    margin-left: 6px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
}

/* Search Form */
.search-form {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-bottom: 26px;
    flex-wrap: wrap;
}

.search-form input {
    width: 450px;
    padding: 14px 20px;
    border: 1px solid var(--glass-line);
    border-radius: var(--r-md);
    font-size: 14px;
    font-family: inherit;
    transition: var(--t);
    background: rgba(0, 0, 0, .3);
    color: var(--txt-hi);
}

.search-form input:focus {
    outline: none;
    border-color: var(--brand-500);
    box-shadow: 0 0 0 4px rgba(16, 185, 129, .15);
    background: rgba(0, 0, 0, .5);
}

.search-form input::placeholder { color: var(--txt-lo); }

.search-form button {
    padding: 14px 32px;
    background: linear-gradient(135deg, var(--brand-500), var(--brand-600));
    color: #fff;
    border: none;
    border-radius: var(--r-md);
    cursor: pointer;
    font-weight: 700;
    font-size: 14px;
    font-family: inherit;
    transition: var(--t);
    box-shadow: 0 8px 24px -8px rgba(16, 185, 129, .55);
}

.search-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px -8px rgba(16, 185, 129, .75);
}

.search-form button i { margin-right: 8px; }

.clear-btn {
    padding: 14px 22px;
    background: transparent;
    color: var(--txt-mid);
    border: 1px solid var(--glass-line);
    border-radius: var(--r-md);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: var(--t);
}

.clear-btn:hover {
    background: var(--glass-hi);
    border-color: var(--glass-line-hi);
    color: var(--txt-hi);
}

/* Add Product Button */
.add-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: linear-gradient(135deg, var(--brand-500), var(--brand-600));
    color: #fff;
    border-radius: var(--r-full);
    text-decoration: none;
    font-weight: 700;
    font-size: 13px;
    transition: var(--t);
    box-shadow: 0 8px 20px -8px rgba(16, 185, 129, .5);
}

.add-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px -8px rgba(16, 185, 129, .7);
}

/* Table */
.table-wrapper {
    overflow-x: auto;
    border-radius: var(--r-xl);
    border: 1px solid var(--glass-line);
    background: var(--glass);
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: transparent;
    border-radius: var(--r-xl);
    overflow: hidden;
}

table th {
    background: rgba(0, 0, 0, .35);
    color: var(--txt-mid);
    padding: 16px 15px;
    text-align: left;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    border-bottom: 1px solid var(--glass-line);
    white-space: nowrap;
}

table th i {
    margin-right: 6px;
    color: var(--brand-400);
}

table td {
    padding: 16px 15px;
    border-bottom: 1px solid var(--glass-line);
    font-size: 13.5px;
    color: var(--txt-mid);
    vertical-align: middle;
}

table tr:last-child td { border-bottom: none; }
table tbody tr { transition: var(--t); }
table tbody tr:hover {
    background: rgba(16, 185, 129, .04);
}

/* ✅ IMAGE THUMB — fixed */
td img {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: var(--r-md);
    border: 2px solid var(--glass-line);
    transition: var(--t);
    cursor: pointer;
    display: block;      /* 🔥 FIX: block display for consistent sizing */
    background: var(--bg-800);
}

td img:hover {
    transform: scale(1.08);
    border-color: var(--brand-500);
    box-shadow: 0 8px 24px -8px rgba(16, 185, 129, .55);
}

/* Small thumbnails row (3 images per product in table) */
.thumb-row {
    display: flex;
    gap: 6px;
    align-items: center;
}
.thumb-row img {
    width: 52px;
    height: 52px;
    border-radius: var(--r-sm);
    flex-shrink: 0;
}
.thumb-row img.active-thumb {
    border-color: var(--brand-500);
    box-shadow: 0 0 0 2px rgba(16, 185, 129, .35);
}

/* Price/Name styling (table) */
.price {
    color: var(--brand-400);
    font-weight: 700;
    font-size: 15px;
    font-family: 'JetBrains Mono', monospace;
    white-space: nowrap;
}
.price i { margin-right: 3px; }
.product-name { font-weight: 600; color: var(--txt-hi); }
.seller-info { font-size: 13px; color: var(--txt-mid); }
.seller-info i { margin-right: 5px; color: var(--brand-400); }

/* Table action buttons */
.action-buttons { display: flex; gap: 8px; }

.edit-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 14px;
    background: rgba(6, 182, 212, .1);
    color: var(--accent-400);
    border: 1px solid rgba(6, 182, 212, .25);
    border-radius: var(--r-full);
    text-decoration: none;
    font-size: 12.5px;
    font-weight: 600;
    transition: var(--t);
    white-space: nowrap;
}

.edit-btn:hover {
    background: var(--accent-500);
    color: #fff;
    border-color: var(--accent-500);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px -8px rgba(6, 182, 212, .5);
}

.delete-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 14px;
    background: rgba(244, 63, 94, .1);
    color: var(--rose-400);
    border: 1px solid rgba(244, 63, 94, .25);
    border-radius: var(--r-full);
    text-decoration: none;
    font-size: 12.5px;
    font-weight: 600;
    transition: var(--t);
    white-space: nowrap;
}

.delete-btn:hover {
    background: var(--rose-500);
    color: #fff;
    border-color: var(--rose-500);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px -8px rgba(244, 63, 94, .55);
}

/* No products */
.no-products {
    text-align: center;
    padding: 60px 20px;
    color: var(--txt-lo);
}
.no-products i {
    font-size: 48px;
    color: var(--bg-700);
    margin-bottom: 15px;
    display: block;
}
.no-products a {
    color: var(--brand-400);
    font-weight: 600;
    text-decoration: none;
}
.no-products a:hover { text-decoration: underline; }

/* =========================================================
   DARK CINEMATIC MODAL
========================================================= */
.image-popup {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(3, 7, 18, .82);
    backdrop-filter: blur(24px) saturate(1.3);
    -webkit-backdrop-filter: blur(24px) saturate(1.3);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    padding: 40px 20px;
    opacity: 0;
    transition: opacity .35s ease;
}

.image-popup.active {
    display: flex;
    animation: popupFade .35s ease forwards;
}

@keyframes popupFade {
    from { opacity: 0; }
    to   { opacity: 1; }
}

.image-popup::before,
.image-popup::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    pointer-events: none;
    z-index: 0;
}
.image-popup::before {
    width: 480px; height: 480px;
    top: -140px; left: -140px;
    background: radial-gradient(circle, rgba(16, 185, 129, .35), transparent 65%);
    animation: orbFloat 16s ease-in-out infinite;
}
.image-popup::after {
    width: 420px; height: 420px;
    bottom: -140px; right: -140px;
    background: radial-gradient(circle, rgba(139, 92, 246, .3), transparent 65%);
    animation: orbFloat 20s ease-in-out infinite reverse;
}

@keyframes orbFloat {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50%      { transform: translate(60px, -50px) scale(1.15); }
}

.image-popup .popup-content {
    position: relative;
    max-width: 900px;
    width: 100%;
    max-height: 92vh;
    display: flex;
    flex-direction: column;
    background: linear-gradient(180deg,
        rgba(19, 27, 46, .96) 0%,
        rgba(10, 15, 28, .98) 100%);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border: 1px solid var(--glass-line-hi);
    border-radius: var(--r-3xl);
    box-shadow:
        0 50px 120px -30px rgba(0, 0, 0, 1),
        0 0 0 1px rgba(255, 255, 255, .04) inset,
        0 0 90px -30px rgba(16, 185, 129, .35);
    overflow: hidden;
    animation: popupEnter .55s var(--ease-bounce);
    z-index: 1;
    isolation: isolate;
}

.image-popup .popup-content::before {
    content: '';
    position: absolute;
    inset: -1px;
    border-radius: var(--r-3xl);
    padding: 1px;
    background: conic-gradient(
        from var(--angle, 0deg),
        transparent 0%,
        var(--brand-400) 12%,
        var(--accent-400) 24%,
        var(--violet-400) 36%,
        transparent 50%,
        transparent 100%
    );
    -webkit-mask:
        linear-gradient(#000 0 0) content-box,
        linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
            mask-composite: exclude;
    pointer-events: none;
    animation: spinRing 8s linear infinite;
    z-index: 2;
    opacity: .85;
}

@property --angle {
    syntax: '<angle>';
    initial-value: 0deg;
    inherits: false;
}

@keyframes spinRing {
    to { --angle: 360deg; }
}

@keyframes popupEnter {
    0%   { opacity: 0; transform: scale(.9) translateY(24px); }
    60%  { transform: scale(1.015) translateY(-4px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
}

/* Image area */
.image-popup .popup-image-area {
    position: relative;
    background:
        radial-gradient(500px 340px at 50% 40%, rgba(16, 185, 129, .08), transparent 65%),
        radial-gradient(400px 300px at 80% 90%, rgba(139, 92, 246, .07), transparent 60%),
        rgba(0, 0, 0, .35);
    padding: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 240px;
    max-height: 60vh;
    overflow: hidden;
}

.image-popup .popup-image-area::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255, 255, 255, .025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, .025) 1px, transparent 1px);
    background-size: 32px 32px;
    mask-image: radial-gradient(circle at center, #000 20%, transparent 75%);
    -webkit-mask-image: radial-gradient(circle at center, #000 20%, transparent 75%);
    pointer-events: none;
}

.image-popup .popup-content > .popup-image-area > img {
    position: relative;
    z-index: 1;
    max-width: 100%;
    max-height: 56vh;
    border-radius: var(--r-lg);
    object-fit: contain;
    box-shadow:
        0 30px 70px -20px rgba(0, 0, 0, .85),
        0 0 0 1px rgba(255, 255, 255, .06),
        0 0 60px -10px rgba(16, 185, 129, .25);
    animation: imgFadeIn .4s ease;
    background: var(--bg-800);
}

@keyframes imgFadeIn {
    from { opacity: 0; transform: scale(.97); }
    to   { opacity: 1; transform: scale(1); }
}

/* Thumbnails inside modal */
.image-popup .popup-thumbs {
    position: relative;
    z-index: 3;
    padding: 12px 20px;
    background: rgba(0, 0, 0, .35);
    border-top: 1px solid var(--glass-line);
    display: flex;
    gap: 8px;
    justify-content: center;
    flex-wrap: wrap;
}

.image-popup .popup-thumbs .pthumb {
    width: 60px;
    height: 60px;
    border-radius: var(--r-sm);
    overflow: hidden;
    border: 2px solid var(--glass-line);
    cursor: pointer;
    transition: var(--t);
    background: var(--bg-800);
    flex-shrink: 0;
}

.image-popup .popup-thumbs .pthumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.image-popup .popup-thumbs .pthumb:hover,
.image-popup .popup-thumbs .pthumb.active {
    border-color: var(--brand-500);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px -8px rgba(16, 185, 129, .6);
}

/* Info bar */
.image-popup .popup-info {
    position: relative;
    z-index: 1;
    padding: 22px 28px 24px;
    background: rgba(10, 15, 28, .75);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-top: 1px solid var(--glass-line);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.image-popup .popup-info .info-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
    flex: 1;
}

.image-popup .popup-info .info-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: var(--brand-400);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.image-popup .popup-info .info-label::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--brand-400);
    box-shadow: 0 0 8px var(--brand-400);
}

.image-popup .popup-info .product-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--txt-hi);
    letter-spacing: -.3px;
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 500px;
}

.image-popup .popup-info .info-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}

.image-popup .popup-info .product-price {
    font-family: 'JetBrains Mono', monospace;
    font-size: 19px;
    font-weight: 600;
    color: var(--brand-400);
    background: rgba(16, 185, 129, .12);
    padding: 9px 20px;
    border-radius: var(--r-full);
    border: 1px solid rgba(16, 185, 129, .3);
    letter-spacing: -.3px;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .05);
    white-space: nowrap;
}

/* Close button */
.image-popup .popup-close {
    position: absolute;
    top: 18px;
    right: 18px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255, 255, 255, .06);
    color: var(--txt-hi);
    font-size: 16px;
    border: 1px solid var(--glass-line-hi);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--t);
    z-index: 10;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.image-popup .popup-close:hover {
    background: var(--rose-500);
    color: #fff;
    border-color: var(--rose-500);
    transform: rotate(90deg) scale(1.08);
    box-shadow: 0 0 30px -6px rgba(244, 63, 94, .8);
}

.image-popup .popup-close i { font-size: 15px; }

/* Navigation buttons */
.image-popup .popup-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, .05);
    border: 1px solid var(--glass-line-hi);
    color: var(--txt-hi);
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--t);
    z-index: 10;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, .5);
}

.image-popup .popup-nav:hover {
    background: linear-gradient(135deg, var(--brand-500), var(--accent-500));
    color: #fff;
    border-color: transparent;
    transform: translateY(-50%) scale(1.1);
    box-shadow:
        0 12px 36px -8px rgba(16, 185, 129, .8),
        0 0 0 6px rgba(16, 185, 129, .15);
}

.image-popup .popup-nav.prev { left: 18px; }
.image-popup .popup-nav.next { right: 18px; }

/* Counter badge */
.image-popup .popup-counter {
    position: absolute;
    top: 18px;
    left: 18px;
    padding: 7px 16px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .1em;
    color: var(--txt-hi);
    background: rgba(0, 0, 0, .55);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--glass-line-hi);
    border-radius: var(--r-full);
    z-index: 10;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.image-popup .popup-counter::before {
    content: '';
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--brand-400);
    box-shadow: 0 0 8px var(--brand-400);
    animation: dotPulse 2s ease-in-out infinite;
}

@keyframes dotPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%      { opacity: .5; transform: scale(.8); }
}

/* =========================================================
   RESPONSIVE
========================================================= */
@media(max-width: 768px) {
    body { padding: 15px; }
    .wrapper { padding: 22px 18px; border-radius: var(--r-2xl); }
    h2 { font-size: 26px; }
    .search-form input { width: 100%; }
    .search-form { flex-direction: column; }
    .stats-bar { flex-direction: column; text-align: center; }
    table { min-width: 900px; }
    .action-buttons { flex-direction: column; }
    .edit-btn, .delete-btn { justify-content: center; }

    .image-popup { padding: 20px 12px; }
    .image-popup .popup-content { max-width: 100%; border-radius: var(--r-2xl); }
    .image-popup .popup-image-area { padding: 16px; min-height: 180px; max-height: 55vh; }
    .image-popup .popup-content > .popup-image-area > img { max-height: 48vh; }
    .image-popup .popup-nav { width: 40px; height: 40px; font-size: 14px; }
    .image-popup .popup-nav.prev { left: 10px; }
    .image-popup .popup-nav.next { right: 10px; }
    .image-popup .popup-close { top: 12px; right: 12px; width: 38px; height: 38px; }
    .image-popup .popup-counter { top: 12px; left: 12px; font-size: 10px; padding: 5px 11px; }
    .image-popup .popup-thumbs { padding: 10px 14px; gap: 6px; }
    .image-popup .popup-thumbs .pthumb { width: 48px; height: 48px; }
    .image-popup .popup-info { padding: 16px 20px 20px; }
    .image-popup .popup-info .product-title { font-size: 17px; max-width: 100%; }
    .image-popup .popup-info .product-price { font-size: 15px; padding: 6px 14px; }
}

/* Scrollbar */
::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: var(--bg-900); }
::-webkit-scrollbar-thumb {
    background: var(--bg-700);
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover { background: var(--brand-500); }
</style>

<body>
    <div class="wrapper">
        <h2>Product Management</h2>
        <div class="sub-heading">Manage all your listed products</div>

        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="count">
                <i class="fas fa-boxes"></i> Total Products: <span><?php echo $total_products; ?></span>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="#" class="add-btn">
                    <i class="fas fa-plus-circle"></i> Add New
                </a>
            </div>
        </div>

        <!-- Search Form -->
        <form class="search-form" method="GET" action="">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
            <?php if (!empty($search)): ?>
                <a href="?" class="clear-btn"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>

        <!-- Table -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> User</th>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-image"></i> Images</th>
                        <th><i class="fas fa-box-open"></i> Product Name</th>
                        <th><i class="fas fa-tag"></i> Price</th>
                        <th><i class="fas fa-info-circle"></i> Model</th>
                        <th><i class="fas fa-user"></i> Seller</th>
                        <th><i class="fas fa-phone"></i> Number</th>
                        <th><i class="fas fa-map-marker-alt"></i> Address</th>
                        <th><i class="fas fa-cogs"></i> Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_products > 0): ?>
                        <?php foreach ($products as $row): ?>
                        <?php
                            $img1 = !empty($row['image'])  ? $row['image']  : 'placeholder.jpg';
                            $img2 = !empty($row['image1']) ? $row['image1'] : $img1;
                            $img3 = !empty($row['image2']) ? $row['image2'] : $img1;
                        ?>
                        <tr>
                            <td>#<?php echo str_pad($row['user_id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                            <td>
                                <!-- ✅ 3 thumbnails, first active -->
                                <div class="thumb-row">
                                    <img src="../uploads/<?php echo htmlspecialchars($img1); ?>"
                                         alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                         class="active-thumb"
                                         onclick="openPopup(<?php echo $row['id']; ?>, 0)"
                                         title="View image 1">
                                    <img src="../uploads/<?php echo htmlspecialchars($img2); ?>"
                                         alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                         onclick="openPopup(<?php echo $row['id']; ?>, 1)"
                                         title="View image 2">
                                    <img src="../uploads/<?php echo htmlspecialchars($img3); ?>"
                                         alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                         onclick="openPopup(<?php echo $row['id']; ?>, 2)"
                                         title="View image 3">
                                </div>
                            </td>
                            <td class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td class="price"><i class="fas fa-rupee-sign"></i> <?php echo number_format((float)$row['price']); ?></td>
                            <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                            <td class="seller-info"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($row['seller_name'] ?? ''); ?></td>
                            <td><i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['seller_number'] ?? ''); ?></td>
                            <td><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['seller_address'] ?? ''); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a class="edit-btn" href="../edit_product.php?id=<?php echo (int)$row['id']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a class="delete-btn" href="delete_product.php?id=<?php echo (int)$row['id']; ?>"
                                        onclick="return confirm('Are you sure you want to delete this product?');">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="10" class="no-products">
                            <i class="fas fa-box-open"></i>
                            No products found! <br>
                            <a href="add_product.php">Click here to add your first product</a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============================================================
         MODAL — shows 3 images per product with slider
         ============================================================ -->
    <div class="image-popup" id="imagePopup" onclick="closePopup(event)">
        <div class="popup-content" onclick="event.stopPropagation();">

            <button class="popup-close" onclick="closePopup()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>

            <div class="popup-counter" id="popupCounter">1 / 3</div>

            <button class="popup-nav prev" onclick="changeImage(-1)" aria-label="Previous">
                <i class="fas fa-chevron-left"></i>
            </button>

            <div class="popup-image-area">
                <img id="popupImage" src="" alt="Product Image">
            </div>

            <button class="popup-nav next" onclick="changeImage(1)" aria-label="Next">
                <i class="fas fa-chevron-right"></i>
            </button>

            <!-- ✅ Modal thumbnails -->
            <div class="popup-thumbs" id="popupThumbs">
                <div class="pthumb active" onclick="goToImage(0, event)"><img id="thumb1" src="" alt="thumb 1"></div>
                <div class="pthumb" onclick="goToImage(1, event)"><img id="thumb2" src="" alt="thumb 2"></div>
                <div class="pthumb" onclick="goToImage(2, event)"><img id="thumb3" src="" alt="thumb 3"></div>
            </div>

            <div class="popup-info">
                <div class="info-left">
                    <span class="info-label">Product</span>
                    <span class="product-title" id="popupTitle">Product Name</span>
                </div>
                <div class="info-right">
                    <span class="product-price" id="popupPrice">₹0</span>
                </div>
            </div>

        </div>
    </div>

    <script>
        const productsData = <?php echo json_encode($products, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        let currentProductIndex = 0;
        let currentImageIndex = 0;
        let currentImages = [];

        function getImages(product) {
            const fallback = product.image || 'placeholder.jpg';
            return [
                product.image  || fallback,
                product.image1 || fallback,
                product.image2 || fallback
            ];
        }

        function openPopup(id, imageIdx) {
            currentProductIndex = productsData.findIndex(p => p.id == id);
            if (currentProductIndex === -1) currentProductIndex = 0;

            const product = productsData[currentProductIndex];
            currentImages = getImages(product);
            currentImageIndex = imageIdx || 0;

            updatePopup();
            document.getElementById('imagePopup').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closePopup(e) {
            if (e && e.target !== e.currentTarget) return;
            document.getElementById('imagePopup').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function changeImage(direction) {
            // First, slide within current product's images
            const nextImg = currentImageIndex + direction;

            if (nextImg >= 0 && nextImg < currentImages.length) {
                // Move within same product
                currentImageIndex = nextImg;
            } else {
                // Move to next/prev product
                currentProductIndex += direction;
                if (currentProductIndex < 0) currentProductIndex = productsData.length - 1;
                if (currentProductIndex >= productsData.length) currentProductIndex = 0;

                const product = productsData[currentProductIndex];
                currentImages = getImages(product);
                currentImageIndex = direction > 0 ? 0 : currentImages.length - 1;
            }

            updatePopup();
        }

        function goToImage(index, e) {
            if (e) e.stopPropagation();
            if (index >= 0 && index < currentImages.length) {
                currentImageIndex = index;
                updatePopup();
            }
        }

        function updatePopup() {
            const product = productsData[currentProductIndex];
            if (!product) return;

            // Update main image
            const imgSrc = '../uploads/' + currentImages[currentImageIndex];
            const popupImg = document.getElementById('popupImage');
            popupImg.src = imgSrc;
            popupImg.alt = product.product_name;

            // Update info
            document.getElementById('popupTitle').textContent = product.product_name;
            document.getElementById('popupPrice').textContent = '₹' + parseFloat(product.price).toFixed(2);

            // Update counter — show product # and image #
            document.getElementById('popupCounter').textContent =
                (currentProductIndex + 1) + ' / ' + productsData.length +
                '  •  ' + (currentImageIndex + 1) + '/' + currentImages.length;

            // Update thumbnails
            for (let i = 0; i < 3; i++) {
                const thumbImg = document.getElementById('thumb' + (i + 1));
                const thumbWrap = thumbImg.parentElement;
                if (currentImages[i]) {
                    thumbImg.src = '../uploads/' + currentImages[i];
                    thumbWrap.style.display = 'block';
                    thumbWrap.classList.toggle('active', i === currentImageIndex);
                } else {
                    thumbWrap.style.display = 'none';
                }
            }
        }

        document.addEventListener('keydown', function(e) {
            if (document.getElementById('imagePopup').classList.contains('active')) {
                if (e.key === 'Escape') closePopup();
                if (e.key === 'ArrowLeft') changeImage(-1);
                if (e.key === 'ArrowRight') changeImage(1);
            }
        });
    </script>
</body>
</html>