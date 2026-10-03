<?php
session_start();
include("db.php");

// Get all products
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $total_products = mysqli_num_rows($result);
} else {
    $total_products = 0;
}

// Fetch all products into an array ONCE
$all_products = [];
$result = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $all_products[] = $row;
    }
    $total_products = count($all_products);
} else {
    $total_products = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Our Products | Thunder</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        /* ========================================
           ROOT VARIABLES
           ======================================== */
        :root {
            --primary: #7c3aed;
            --primary-light: #a78bfa;
            --primary-dark: #5b21b6;
            --primary-gradient: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            --primary-glow: rgba(124, 58, 237, 0.35);

            --gold: #f59e0b;
            --gold-light: #fbbf24;
            --gold-gradient: linear-gradient(135deg, #f59e0b, #d97706);

            --success: #10b981;
            --danger: #ef4444;

            --bg-primary: #0b0719;
            --bg-secondary: #120d28;
            --bg-card: #171232;
            --bg-card-hover: #1e1842;
            --bg-elevated: #231d4a;
            --bg-input: #1a1538;

            --text-primary: #ffffff;
            --text-secondary: #c4b5d4;
            --text-muted: #7a6a94;

            --border-color: #2d1f52;
            --border-purple: rgba(124, 58, 237, 0.2);

            --shadow-sm: 0 2px 12px rgba(0, 0, 0, 0.5);
            --shadow-lg: 0 12px 48px rgba(0, 0, 0, 0.7);
            --shadow-xl: 0 20px 64px rgba(0, 0, 0, 0.8);

            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
            --radius-xl: 18px;
            --radius-2xl: 24px;
            --radius-full: 50px;

            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: var(--bg-primary);
            color: var(--text-primary);
            background-image:
                radial-gradient(ellipse at 15% 20%, rgba(124, 58, 237, 0.06) 0%, transparent 55%),
                radial-gradient(ellipse at 85% 80%, rgba(124, 58, 237, 0.04) 0%, transparent 55%);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ========================================
           KEYFRAMES
           ======================================== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(32px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes glowPulse {
            0%, 100% { opacity: 0.5; box-shadow: 0 0 20px rgba(124, 58, 237, 0.15); }
            50% { opacity: 1; box-shadow: 0 0 40px rgba(124, 58, 237, 0.35); }
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* ========================================
           NAVBAR
           ======================================== */
        .navbar {
            background: rgba(18, 13, 40, 0.88);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            padding: 12px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 32px rgba(0, 0, 0, 0.5);
        }
        .navbar .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            flex-shrink: 0;
        }
        .navbar .logo .logo-icon {
            width: 42px;
            height: 42px;
            background: var(--primary-gradient);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            box-shadow: 0 4px 20px var(--primary-glow);
            animation: glowPulse 3s ease-in-out infinite;
        }
        .navbar .logo h2 {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--text-primary);
        }
        .navbar .logo h2 span {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .navbar .nav-links {
            display: flex;
            gap: 4px;
            align-items: center;
            flex-wrap: wrap;
        }
        .navbar .nav-links a {
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 13px;
            padding: 8px 18px;
            border-radius: var(--radius-full);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .navbar .nav-links a:hover {
            color: var(--text-primary);
            background: rgba(124, 58, 237, 0.08);
        }
        .navbar .nav-links a.active {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: 0 4px 20px var(--primary-glow);
        }

        /* ========================================
           PAGE HEADER
           ======================================== */
        .page-header {
            padding: 56px 32px 44px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-secondary);
        }
        .page-header::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -5%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.07) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .page-header::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .page-header h1 {
            font-size: 44px;
            font-weight: 900;
            position: relative;
            z-index: 1;
            letter-spacing: -1px;
        }
        .page-header h1 i {
            margin-right: 14px;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .page-header p {
            font-size: 16px;
            color: var(--text-secondary);
            margin-top: 8px;
            position: relative;
            z-index: 1;
        }
        .page-header .product-count {
            display: inline-block;
            margin-top: 14px;
            padding: 6px 28px;
            background: rgba(124, 58, 237, 0.08);
            border: 1px solid var(--border-purple);
            border-radius: var(--radius-full);
            font-size: 13px;
            color: var(--primary-light);
            position: relative;
            z-index: 1;
            backdrop-filter: blur(8px);
            font-weight: 600;
        }
        .page-header .product-count i {
            margin-right: 8px;
        }

        /* ========================================
           PRODUCTS SECTION
           ======================================== */
        .products-section {
            padding: 40px 32px 20px;
            max-width: 1440px;
            margin: 0 auto;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .section-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-header h2 i {
            color: var(--primary);
        }
        .section-header .filter-options {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .section-header .filter-options select,
        .section-header .filter-options input {
            padding: 10px 16px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 13px;
            background: var(--bg-input);
            color: var(--text-primary);
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
            min-width: 160px;
            outline: none;
        }
        .section-header .filter-options select:focus,
        .section-header .filter-options input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
        }
        .section-header .filter-options select option {
            background: var(--bg-secondary);
        }
        .section-header .filter-options input::placeholder {
            color: var(--text-muted);
        }

        /* ========================================
           PRODUCT GRID
           ======================================== */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 28px;
        }

        /* ========================================
           PRODUCT CARD
           ======================================== */
        .product-card {
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            position: relative;
            border: 1px solid var(--border-color);
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
            display: flex;
            flex-direction: column;
        }
        .product-card:nth-child(1) { animation-delay: 0.05s; }
        .product-card:nth-child(2) { animation-delay: 0.10s; }
        .product-card:nth-child(3) { animation-delay: 0.15s; }
        .product-card:nth-child(4) { animation-delay: 0.20s; }
        .product-card:nth-child(5) { animation-delay: 0.25s; }
        .product-card:nth-child(6) { animation-delay: 0.30s; }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg), 0 0 40px rgba(124, 58, 237, 0.06);
            border-color: var(--primary);
            background: var(--bg-card-hover);
        }

        /* Badge */
        .product-card .product-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            padding: 4px 16px;
            border-radius: var(--radius-full);
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #fff;
            z-index: 10;
            box-shadow: var(--shadow-sm);
        }
        .product-card .product-badge.hot {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            animation: glowPulse 2s ease-in-out infinite;
        }
        .product-card .product-badge.new {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        .product-card .product-badge.sale {
            background: var(--gold-gradient);
        }
        .product-card .product-badge.featured {
            background: var(--primary-gradient);
        }

        /* ========================================
           IMAGE SLIDER
           ======================================== */
        .product-card .slider {
            width: 100%;
            height: 280px;
            position: relative;
            background: var(--bg-secondary);
            overflow: hidden;
            touch-action: pan-y;
        }

        .product-card .slider .slides-track {
            display: flex;
            width: 100%;
            height: 100%;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform;
        }
        .product-card .slider .slides-track .slide {
            flex: 0 0 100%;
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        .product-card .slider .slides-track .slide img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
            -webkit-user-drag: none;
            pointer-events: none;
        }
        .product-card:hover .slider .slides-track .slide img {
            transform: scale(1.05);
        }

        /* Slider Arrows */
        .product-card .slider .slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 13px;
            z-index: 4;
            opacity: 0;
            transition: var(--transition);
            font-family: inherit;
            padding: 0;
        }
        .product-card:hover .slider .slider-arrow {
            opacity: 1;
        }
        .product-card .slider .slider-arrow:hover {
            background: var(--primary-gradient);
            border-color: transparent;
            box-shadow: 0 4px 20px var(--primary-glow);
            transform: translateY(-50%) scale(1.1);
        }
        .product-card .slider .slider-arrow.prev { left: 10px; }
        .product-card .slider .slider-arrow.next { right: 10px; }

        /* Slider Dots */
        .product-card .slider .slider-dots {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
            z-index: 4;
            padding: 5px 10px;
            background: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: var(--radius-full);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .product-card .slider .slider-dots .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            transition: var(--transition);
            border: none;
            padding: 0;
        }
        .product-card .slider .slider-dots .dot.active {
            width: 20px;
            border-radius: var(--radius-full);
            background: var(--gold);
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.6);
        }

        /* Image Counter */
        .product-card .slider .img-count {
            position: absolute;
            top: 14px;
            right: 14px;
            padding: 3px 10px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: #fff;
            border-radius: var(--radius-full);
            font-size: 10px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
            z-index: 4;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .product-card .slider .img-count i {
            font-size: 9px;
            color: var(--gold);
        }

        /* Quick View overlay */
        .product-card .slider .quick-view {
            position: absolute;
            bottom: -50px;
            left: 50%;
            transform: translateX(-50%);
            padding: 9px 22px;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: #fff;
            border-radius: var(--radius-full);
            font-size: 11px;
            font-weight: 600;
            transition: var(--transition);
            cursor: pointer;
            white-space: nowrap;
            border: 1px solid rgba(255, 255, 255, 0.06);
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.3px;
            z-index: 5;
        }
        .product-card:hover .slider .quick-view {
            bottom: 46px;
        }
        .product-card .slider .quick-view:hover {
            background: var(--primary-gradient);
            border-color: transparent;
            box-shadow: 0 4px 24px var(--primary-glow);
        }

        /* ========================================
           PRODUCT INFO
           ======================================== */
        .product-card .product-info {
            padding: 18px 20px 22px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .product-card .product-info .product-category {
            font-size: 11px;
            color: #f5c542;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 700;
            margin-bottom: 6px;
            display: block;
        }
        .product-card .product-info h3 {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 6px;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-card .product-info .product-rating {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
        }
        .product-card .product-info .product-rating .stars {
            color: var(--gold);
            font-size: 12px;
        }
        .product-card .product-info .product-rating .rating-text {
            font-size: 12px;
            color: var(--text-muted);
        }

        .product-card .product-info .product-price {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 8px 0 6px;
            flex-wrap: wrap;
        }
        .product-card .product-info .product-price .current-price {
            font-size: 22px;
            font-weight: 800;
            color: var(--success);
        }
        .product-card .product-info .product-price .current-price::before {
            content: '₹';
            margin-right: 2px;
        }
        .product-card .product-info .product-price .original-price {
            font-size: 14px;
            color: var(--text-muted);
            text-decoration: line-through;
        }
        .product-card .product-info .product-price .original-price::before {
            content: '₹';
            margin-right: 2px;
        }
        .product-card .product-info .product-price .discount {
            font-size: 11px;
            font-weight: 700;
            color: var(--danger);
            background: rgba(239, 68, 68, 0.12);
            padding: 2px 12px;
            border-radius: var(--radius-full);
        }

        .product-card .product-info p {
            color: var(--text-secondary);
            font-size: 12.5px;
            line-height: 1.6;
            margin-bottom: 14px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }

        .product-card .product-info .btn-group {
            display: flex;
            gap: 8px;
            margin-top: auto;
        }
        .product-card .product-info .btn-group .btn {
            flex: 1;
            padding: 11px 16px;
            border: none;
            border-radius: var(--radius-md);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
        }
        .product-card .product-info .btn-group .btn-primary {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: 0 4px 16px var(--primary-glow);
        }
        .product-card .product-info .btn-group .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 28px var(--primary-glow);
        }
        .product-card .product-info .btn-group .btn-wishlist {
            background: var(--bg-secondary);
            color: var(--text-secondary);
            width: 46px;
            flex: none;
            border: 1px solid var(--border-color);
            font-size: 15px;
        }
        .product-card .product-info .btn-group .btn-wishlist:hover {
            background: rgba(239, 68, 68, 0.10);
            color: var(--danger);
            border-color: var(--danger);
        }

        /* ========================================
           MODAL
           ======================================== */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(11, 7, 25, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 24px;
            animation: fadeInUp 0.35s ease;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal {
            background: var(--bg-card);
            border-radius: var(--radius-2xl);
            max-width: 1000px;
            width: 100%;
            max-height: 92vh;
            overflow-y: auto;
            box-shadow: var(--shadow-xl);
            animation: slideUp 0.4s ease;
            position: relative;
            border: 1px solid var(--border-purple);
            -webkit-overflow-scrolling: touch;
        }
        .modal::-webkit-scrollbar { width: 5px; }
        .modal::-webkit-scrollbar-track { background: var(--bg-secondary); }
        .modal::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 4px;
        }
        .modal .modal-close {
            position: absolute;
            top: 14px;
            right: 18px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--border-color);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 16px;
            cursor: pointer;
            color: var(--text-secondary);
            transition: var(--transition);
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-sm);
        }
        .modal .modal-close:hover {
            background: var(--danger);
            color: #fff;
            transform: rotate(90deg);
            border-color: var(--danger);
        }
        .modal-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }

        /* Modal Slider */
        .modal-content .modal-slider-wrap {
            background: var(--bg-secondary);
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            border-radius: var(--radius-2xl) 0 0 var(--radius-2xl);
        }
        .modal-content .modal-slider {
            position: relative;
            width: 100%;
            height: 360px;
            border-radius: var(--radius-md);
            overflow: hidden;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
        }
        .modal-content .modal-slider .modal-track {
            display: flex;
            width: 100%;
            height: 100%;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform;
        }
        .modal-content .modal-slider .modal-track .mslide {
            flex: 0 0 100%;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .modal-content .modal-slider .modal-track .mslide img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            user-select: none;
            -webkit-user-drag: none;
        }

        .modal-content .modal-slider .modal-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            z-index: 4;
            transition: var(--transition);
            padding: 0;
        }
        .modal-content .modal-slider .modal-arrow:hover {
            background: var(--primary-gradient);
            border-color: transparent;
            box-shadow: 0 4px 20px var(--primary-glow);
        }
        .modal-content .modal-slider .modal-arrow.prev { left: 12px; }
        .modal-content .modal-slider .modal-arrow.next { right: 12px; }

        .modal-content .modal-slider .modal-dots {
            position: absolute;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
            z-index: 4;
            padding: 5px 10px;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: var(--radius-full);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .modal-content .modal-slider .modal-dots .mdot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            transition: var(--transition);
            border: none;
            padding: 0;
        }
        .modal-content .modal-slider .modal-dots .mdot.active {
            width: 20px;
            border-radius: var(--radius-full);
            background: var(--gold);
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.6);
        }

        /* Modal Thumbnails */
        .modal-content .modal-slider-wrap .modal-thumbs {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .modal-content .modal-slider-wrap .modal-thumbs .mthumb {
            height: 70px;
            border-radius: var(--radius-sm);
            overflow: hidden;
            border: 2px solid var(--border-color);
            cursor: pointer;
            transition: var(--transition);
            background: var(--bg-card);
        }
        .modal-content .modal-slider-wrap .modal-thumbs .mthumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .modal-content .modal-slider-wrap .modal-thumbs .mthumb:hover,
        .modal-content .modal-slider-wrap .modal-thumbs .mthumb.active {
            border-color: var(--gold);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.4);
        }

        .modal-content .modal-details {
            padding: 36px 40px 32px 32px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .modal-content .modal-details .badge-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .modal-content .modal-details .badge-group .badge {
            padding: 3px 14px;
            border-radius: var(--radius-full);
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #fff;
            letter-spacing: 0.5px;
        }
        .modal-content .modal-details .badge-group .badge.hot {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        .modal-content .modal-details .badge-group .badge.new {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        .modal-content .modal-details .badge-group .badge.sale {
            background: var(--gold-gradient);
        }
        .modal-content .modal-details .badge-group .badge.featured {
            background: var(--primary-gradient);
        }
        .modal-content .modal-details .category {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
        }
        .modal-content .modal-details h2 {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1.2;
        }
        .modal-content .modal-details .rating {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .modal-content .modal-details .rating .stars {
            color: var(--gold);
            font-size: 14px;
        }
        .modal-content .modal-details .rating .text {
            color: var(--text-muted);
            font-size: 13px;
        }
        .modal-content .modal-details .price {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .modal-content .modal-details .price .current {
            font-size: 30px;
            font-weight: 900;
            color: var(--success);
        }
        .modal-content .modal-details .price .current::before {
            content: '₹';
            margin-right: 2px;
        }
        .modal-content .modal-details .price .original {
            font-size: 16px;
            color: var(--text-muted);
            text-decoration: line-through;
        }
        .modal-content .modal-details .price .original::before {
            content: '₹';
            margin-right: 2px;
        }
        .modal-content .modal-details .price .discount {
            font-size: 12px;
            font-weight: 700;
            color: var(--danger);
            background: rgba(239, 68, 68, 0.12);
            padding: 2px 14px;
            border-radius: var(--radius-full);
        }
        .modal-content .modal-details .description {
            color: var(--text-secondary);
            font-size: 14px;
            line-height: 1.8;
        }
        .modal-content .modal-details .seller-info {
            background: rgba(124, 58, 237, 0.04);
            border-radius: var(--radius-md);
            padding: 16px 20px;
            border: 1px solid var(--border-purple);
            margin: 4px 0;
        }
        .modal-content .modal-details .seller-info .seller-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--primary-light);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .modal-content .modal-details .seller-info .seller-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 0;
            flex-wrap: wrap;
        }
        .modal-content .modal-details .seller-info .seller-row i {
            color: var(--primary);
            width: 18px;
            font-size: 13px;
        }
        .modal-content .modal-details .seller-info .seller-row .label {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 500;
            min-width: 56px;
        }
        .modal-content .modal-details .seller-info .seller-row .value {
            color: var(--text-primary);
            font-size: 13px;
            font-weight: 600;
            word-break: break-word;
        }
        .modal-content .modal-details .seller-info .seller-row .value .phone-link {
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 700;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .modal-content .modal-details .seller-info .seller-row .value .phone-link:hover {
            color: var(--primary);
            text-decoration: underline;
        }
        .modal-content .modal-details .meta-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding: 14px 0;
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }
        .modal-content .modal-details .meta-info .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: var(--text-secondary);
            word-break: break-word;
        }
        .modal-content .modal-details .meta-info .meta-item i {
            color: var(--primary);
            width: 18px;
            flex-shrink: 0;
        }
        .modal-content .modal-details .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 6px;
        }
        .modal-content .modal-details .modal-actions .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
        }
        .modal-content .modal-details .modal-actions .btn-primary {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: 0 4px 20px var(--primary-glow);
        }
        .modal-content .modal-details .modal-actions .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 28px var(--primary-glow);
        }
        .modal-content .modal-details .modal-actions .btn-wishlist {
            background: var(--bg-secondary);
            color: var(--text-secondary);
            flex: none;
            width: 50px;
            border: 1px solid var(--border-color);
            font-size: 16px;
        }
        .modal-content .modal-details .modal-actions .btn-wishlist:hover {
            background: rgba(239, 68, 68, 0.10);
            color: var(--danger);
            border-color: var(--danger);
        }
        .modal-content .modal-details .modal-actions .btn-call {
            background: var(--success);
            color: #fff;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.25);
        }
        .modal-content .modal-details .modal-actions .btn-call:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 28px rgba(16, 185, 129, 0.4);
        }

        /* ========================================
           NO PRODUCTS
           ======================================== */
        .no-products {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-muted);
        }
        .no-products i {
            font-size: 56px;
            opacity: 0.15;
            display: block;
            margin-bottom: 16px;
        }
        .no-products h3 {
            font-size: 22px;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }
        .no-products p {
            font-size: 14px;
        }

        /* ========================================
           FOOTER
           ======================================== */
        .footer {
            background: var(--bg-secondary);
            color: var(--text-muted);
            padding: 28px 32px;
            text-align: center;
            margin-top: 40px;
            border-top: 1px solid var(--border-color);
        }
        .footer p {
            font-size: 13px;
        }
        .footer span {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }

        /* ========================================
           TOAST
           ======================================== */
        .toast-notification {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--bg-elevated);
            color: var(--text-primary);
            padding: 14px 28px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 500;
            box-shadow: var(--shadow-xl);
            z-index: 99999;
            animation: slideUp 0.4s ease;
            border-left: 4px solid var(--primary);
            font-family: 'Inter', sans-serif;
            max-width: 400px;
            border: 1px solid var(--border-color);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* ========================================
           SCROLLBAR
           ======================================== */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg-secondary); }
        ::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 4px;
        }

        /* ========================================
           TABLET (<=992px)
           ======================================== */
        @media (max-width: 992px) {
            .page-header h1 { font-size: 34px; }
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
                gap: 20px;
            }
            .product-card .slider { height: 240px; }

            .modal-content { grid-template-columns: 1fr; }
            .modal-content .modal-slider-wrap {
                border-radius: var(--radius-2xl) var(--radius-2xl) 0 0;
            }
            .modal-content .modal-slider { height: 320px; }
            .modal-content .modal-details { padding: 24px 28px 28px; }
            .modal-content .modal-details h2 { font-size: 22px; }
            .modal-content .modal-details .price .current { font-size: 24px; }
        }

        /* ========================================
           MOBILE (<=768px) — FIXED
           ======================================== */
        @media (max-width: 768px) {
            .navbar {
                padding: 10px 14px;
                gap: 8px;
            }
            .navbar .logo .logo-icon {
                width: 36px;
                height: 36px;
                font-size: 15px;
            }
            .navbar .logo h2 { font-size: 17px; }
            .navbar .nav-links { gap: 2px; }
            .navbar .nav-links a {
                font-size: 11px;
                padding: 6px 10px;
                gap: 4px;
            }
            .navbar .nav-links a i { font-size: 11px; }

            .page-header { padding: 30px 16px 26px; }
            .page-header h1 { font-size: 24px; letter-spacing: -0.5px; }
            .page-header h1 i { margin-right: 8px; }
            .page-header p { font-size: 13px; }
            .page-header .product-count {
                font-size: 11px;
                padding: 5px 16px;
                margin-top: 10px;
            }

            .products-section { padding: 20px 12px 10px; }
            .section-header {
                flex-direction: column;
                align-items: stretch;
                margin-bottom: 20px;
                gap: 12px;
            }
            .section-header h2 { font-size: 17px; }
            .section-header .filter-options {
                flex-direction: column;
                gap: 8px;
            }
            .section-header .filter-options select,
            .section-header .filter-options input {
                width: 100%;
                min-width: unset;
                font-size: 12px;
                padding: 9px 12px;
            }

            .product-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            /* Card adjustments */
            .product-card { border-radius: var(--radius-lg); }
            .product-card .product-badge {
                top: 10px;
                left: 10px;
                padding: 3px 10px;
                font-size: 8px;
            }
            .product-card .slider { height: 160px; }
            .product-card .slider .slider-arrow {
                width: 30px;
                height: 30px;
                font-size: 11px;
                opacity: 1;
            }
            .product-card .slider .slider-arrow.prev { left: 6px; }
            .product-card .slider .slider-arrow.next { right: 6px; }
            .product-card .slider .slider-dots {
                padding: 4px 8px;
                gap: 5px;
                bottom: 8px;
            }
            .product-card .slider .slider-dots .dot {
                width: 6px;
                height: 6px;
            }
            .product-card .slider .slider-dots .dot.active { width: 16px; }
            .product-card .slider .img-count {
                top: 10px;
                right: 10px;
                padding: 2px 8px;
                font-size: 9px;
            }
            .product-card .slider .quick-view {
                bottom: -50px;
                padding: 7px 16px;
                font-size: 10px;
            }
            .product-card:hover .slider .quick-view { bottom: 34px; }

            .product-card .product-info { padding: 12px 12px 14px; }
            .product-card .product-info .product-category {
                font-size: 9px;
                letter-spacing: 1px;
                margin-bottom: 4px;
            }
            .product-card .product-info h3 {
                font-size: 13px;
                margin-bottom: 4px;
            }
            .product-card .product-info .product-rating {
                gap: 4px;
                margin-bottom: 6px;
            }
            .product-card .product-info .product-rating .stars { font-size: 10px; }
            .product-card .product-info .product-rating .rating-text { font-size: 10px; }

            .product-card .product-info .product-price {
                gap: 6px;
                margin: 6px 0 4px;
            }
            .product-card .product-info .product-price .current-price { font-size: 15px; }
            .product-card .product-info .product-price .original-price { font-size: 11px; }
            .product-card .product-info .product-price .discount {
                font-size: 9px;
                padding: 1px 8px;
            }

            .product-card .product-info p {
                font-size: 11px;
                -webkit-line-clamp: 2;
                margin-bottom: 10px;
                line-height: 1.5;
            }

            .product-card .product-info .btn-group { gap: 6px; }
            .product-card .product-info .btn-group .btn {
                font-size: 10px;
                padding: 8px 10px;
                gap: 4px;
                border-radius: var(--radius-sm);
            }
            .product-card .product-info .btn-group .btn-primary i { display: none; }
            .product-card .product-info .btn-group .btn-wishlist {
                width: 34px;
                font-size: 13px;
            }

            /* Modal mobile */
            .modal-overlay { padding: 0; }
            .modal {
                max-height: 100vh;
                height: 100vh;
                border-radius: 0;
                max-width: 100%;
            }
            .modal .modal-close {
                top: 10px;
                right: 10px;
                width: 34px;
                height: 34px;
                font-size: 14px;
            }
            .modal-content { grid-template-columns: 1fr; }
            .modal-content .modal-slider-wrap {
                padding: 14px;
                gap: 8px;
            }
            .modal-content .modal-slider { height: 260px; }
            .modal-content .modal-slider .modal-arrow {
                width: 34px;
                height: 34px;
                font-size: 12px;
            }
            .modal-content .modal-slider .modal-arrow.prev { left: 8px; }
            .modal-content .modal-slider .modal-arrow.next { right: 8px; }
            .modal-content .modal-slider-wrap .modal-thumbs { gap: 6px; }
            .modal-content .modal-slider-wrap .modal-thumbs .mthumb { height: 54px; }

            .modal-content .modal-details {
                padding: 16px 16px 22px;
                gap: 8px;
            }
            .modal-content .modal-details h2 { font-size: 19px; }
            .modal-content .modal-details .category { font-size: 10px; }
            .modal-content .modal-details .rating { gap: 6px; }
            .modal-content .modal-details .rating .stars { font-size: 12px; }
            .modal-content .modal-details .rating .text { font-size: 11px; }
            .modal-content .modal-details .price .current { font-size: 20px; }
            .modal-content .modal-details .price .original { font-size: 13px; }
            .modal-content .modal-details .description { font-size: 12px; }

            .modal-content .modal-details .seller-info { padding: 12px 14px; }
            .modal-content .modal-details .seller-info .seller-row {
                gap: 6px;
                padding: 3px 0;
            }
            .modal-content .modal-details .seller-info .seller-row .label {
                min-width: 48px;
                font-size: 11px;
            }
            .modal-content .modal-details .seller-info .seller-row .value { font-size: 12px; }

            .modal-content .modal-details .meta-info {
                grid-template-columns: 1fr;
                gap: 8px;
                padding: 10px 0;
            }
            .modal-content .modal-details .meta-info .meta-item { font-size: 11px; }

            .modal-content .modal-details .modal-actions {
                flex-wrap: wrap;
                gap: 8px;
            }
            .modal-content .modal-details .modal-actions .btn {
                font-size: 12px;
                padding: 10px 14px;
                flex: 1 1 auto;
                min-width: calc(50% - 4px);
            }
            .modal-content .modal-details .modal-actions .btn-wishlist {
                flex: none;
                width: 46px;
                min-width: unset;
            }
            .modal-content .modal-details .modal-actions .btn-call {
                flex-basis: 100%;
            }

            .toast-notification {
                bottom: 14px;
                right: 14px;
                left: 14px;
                max-width: none;
                font-size: 12px;
                padding: 12px 16px;
            }
        }

        /* ========================================
           SMALL MOBILE (<=480px)
           ======================================== */
        @media (max-width: 480px) {
            .navbar .logo h2 { font-size: 15px; }
            .navbar .logo .logo-icon { width: 32px; height: 32px; font-size: 13px; }
            .navbar .nav-links a { font-size: 10px; padding: 5px 8px; }
            .navbar .nav-links a span { display: none; }

            .page-header h1 { font-size: 20px; }
            .page-header p { font-size: 11px; }
            .page-header .product-count { font-size: 10px; padding: 4px 12px; }

            .section-header h2 { font-size: 15px; }

            .product-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
            .product-card .slider { height: 140px; }
            .product-card .slider .slider-arrow {
                width: 26px;
                height: 26px;
                font-size: 10px;
            }
            .product-card .slider .slider-dots { padding: 3px 6px; gap: 4px; }
            .product-card .slider .slider-dots .dot { width: 5px; height: 5px; }
            .product-card .slider .slider-dots .dot.active { width: 14px; }

            .product-card .product-info { padding: 10px; }
            .product-card .product-info h3 { font-size: 12px; }
            .product-card .product-info .product-category { font-size: 8px; }
            .product-card .product-info .product-price .current-price { font-size: 14px; }
            .product-card .product-info .product-price .original-price { font-size: 10px; }
            .product-card .product-info .product-price .discount {
                font-size: 8px;
                padding: 1px 6px;
            }
            .product-card .product-info p {
                font-size: 10px;
                -webkit-line-clamp: 1;
            }
            .product-card .product-info .btn-group .btn {
                font-size: 9px;
                padding: 7px 8px;
            }
            .product-card .product-info .btn-group .btn-wishlist {
                width: 30px;
                font-size: 12px;
            }

            .modal-content .modal-slider { height: 220px; }
            .modal-content .modal-slider-wrap .modal-thumbs .mthumb { height: 48px; }
            .modal-content .modal-details h2 { font-size: 17px; }
            .modal-content .modal-details .price .current { font-size: 18px; }
            .modal-content .modal-details .modal-actions .btn { font-size: 11px; padding: 9px 12px; }
        }

        /* ========================================
           EXTRA SMALL (<=360px)
           ======================================== */
        @media (max-width: 360px) {
            .product-grid { gap: 6px; }
            .product-card .slider { height: 120px; }
            .product-card .product-info { padding: 8px; }
            .product-card .product-info h3 { font-size: 11px; }
            .product-card .product-info .btn-group .btn { font-size: 8px; padding: 6px; }
            .product-card .product-info .btn-group .btn-primary i { display: none; }
            .product-card .product-info .btn-group .btn-wishlist { width: 26px; font-size: 11px; }

            .navbar .nav-links a { padding: 4px 6px; font-size: 9px; }

            .modal-content .modal-slider { height: 180px; }
        }

        /* ========================================
           REDUCED MOTION
           ======================================== */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            .product-card { animation: none !important; opacity: 1 !important; }
            .product-card:hover { transform: none !important; }
            .product-card .slider .slides-track { transition: none !important; }
            .modal { animation: none !important; }
            .modal-overlay { animation: none !important; }
            .toast-notification { animation: none !important; }
        }
    </style>
</head>
<body>

    <!-- ===== NAVBAR ===== -->
    <nav class="navbar">
        <a href="#" class="logo">
            <div class="logo-icon"><i class="fas fa-cubes"></i></div>
            <h2>Thunder<span>X</span></h2>
        </a>
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-home"></i> <span>Home</span></a>
            <a href="#" class="active"><i class="fas fa-box"></i> <span>Products</span></a>
            <a href="about.php"><i class="fas fa-info-circle"></i> <span>About</span></a>
        </div>
    </nav>

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header">
        <h1><i class="fas fa-box-open"></i> Our Products</h1>
        <p>Discover our premium collection — quality you can trust</p>
        <div class="product-count">
            <i class="fas fa-tag"></i> <?php echo $total_products; ?> Products Available
        </div>
    </div>

    <!-- ===== PRODUCTS SECTION ===== -->
    <section class="products-section">
        <div class="section-header">
            <h2><i class="fas fa-list"></i> All Products</h2>
            <div class="filter-options">
                <select>
                    <option value="">Sort by: Featured</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                    <option value="newest">Newest First</option>
                    <option value="popular">Most Popular</option>
                </select>
                <input type="text" placeholder="Search products..." id="searchProduct" onkeyup="searchProducts()">
            </div>
        </div>

        <div class="product-grid" id="productGrid">
            <?php if (count($all_products) > 0): ?>
                <?php foreach ($all_products as $row): ?>
                    <?php
                        $img1 = !empty($row['image'])  ? $row['image']  : 'placeholder.jpg';
                        $img2 = !empty($row['image1']) ? $row['image1'] : $img1;
                        $img3 = !empty($row['image2']) ? $row['image2'] : $img1;
                        $pid  = (int)$row['id'];
                    ?>
                    <div class="product-card"
                         data-name="<?php echo strtolower(htmlspecialchars($row['product_name'] ?? '')); ?>"
                         data-seller="<?php echo strtolower(htmlspecialchars($row['seller_name'] ?? '')); ?>"
                         data-category="<?php echo strtolower(htmlspecialchars($row['category'] ?? '')); ?>">

                        <?php if (!empty($row['badge'])): ?>
                            <span class="product-badge <?php echo htmlspecialchars($row['badge']); ?>">
                                <?php echo ucfirst(htmlspecialchars($row['badge'])); ?>
                            </span>
                        <?php endif; ?>

                        <!-- ===== IMAGE SLIDER ===== -->
                        <div class="slider" id="slider-<?php echo $pid; ?>" data-current="0">
                            <div class="slides-track">
                                <div class="slide">
                                    <img src="uploads/<?php echo htmlspecialchars($img1); ?>" alt="<?php echo htmlspecialchars($row['product_name'] ?? ''); ?>" loading="lazy">
                                </div>
                                <div class="slide">
                                    <img src="uploads/<?php echo htmlspecialchars($img2); ?>" alt="<?php echo htmlspecialchars($row['product_name'] ?? ''); ?>" loading="lazy">
                                </div>
                                <div class="slide">
                                    <img src="uploads/<?php echo htmlspecialchars($img3); ?>" alt="<?php echo htmlspecialchars($row['product_name'] ?? ''); ?>" loading="lazy">
                                </div>
                            </div>

                            <!-- Image Counter -->
                            <div class="img-count">
                                <i class="fas fa-images"></i> <span class="counter">1</span>/3
                            </div>

                            <!-- Arrows -->
                            <button type="button" class="slider-arrow prev" onclick="slidePrev(<?php echo $pid; ?>, event)" aria-label="Previous">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="button" class="slider-arrow next" onclick="slideNext(<?php echo $pid; ?>, event)" aria-label="Next">
                                <i class="fas fa-chevron-right"></i>
                            </button>

                            <!-- Dots -->
                            <div class="slider-dots">
                                <button type="button" class="dot active" onclick="goToSlide(<?php echo $pid; ?>, 0, event)" aria-label="Slide 1"></button>
                                <button type="button" class="dot" onclick="goToSlide(<?php echo $pid; ?>, 1, event)" aria-label="Slide 2"></button>
                                <button type="button" class="dot" onclick="goToSlide(<?php echo $pid; ?>, 2, event)" aria-label="Slide 3"></button>
                            </div>

                            <!-- Quick View -->
                            <div class="quick-view" onclick="openProductModal(<?php echo $pid; ?>)">Quick View</div>
                        </div>

                        <!-- ===== INFO ===== -->
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($row['category'] ?? 'General'); ?></div>
                            <h3><?php echo htmlspecialchars($row['product_name'] ?? 'Untitled'); ?></h3>

                            <div class="product-rating">
                                <span class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </span>
                                <span class="rating-text">(4.5)</span>
                            </div>

                            <div class="product-price">
                                <span class="current-price"><?php echo number_format((float)($row['price'] ?? 0), 1); ?></span>
                                <?php if (!empty($row['original_price']) && $row['original_price'] > 0): ?>
                                    <span class="original-price">₹<?php echo number_format((float)$row['original_price'], 2); ?></span>
                                    <span class="discount">-<?php echo round((($row['original_price'] - $row['price']) / $row['original_price']) * 100); ?>%</span>
                                <?php endif; ?>
                            </div>

                            <p>Modal<br><?php echo htmlspecialchars($row['description'] ?? 'No description'); ?></p>

                            <div class="btn-group">
                                <button class="btn btn-primary" onclick="openProductModal(<?php echo $pid; ?>)">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                                <button class="btn btn-wishlist" onclick="toggleWishlist(this)">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-products" style="grid-column: 1 / -1;">
                    <i class="fas fa-box-open"></i>
                    <h3>No Products Found</h3>
                    <p>Check back later for new arrivals</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ===== MODAL ===== -->
    <div class="modal-overlay" id="productModal">
        <div class="modal">
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-content" id="modalContent"></div>
        </div>
    </div>

    <!-- ===== FOOTER ===== -->
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> <span>Thunder</span> &mdash; All rights reserved</p>
    </footer>

    <script>
        // =============================
        // PRODUCT DATA (from PHP)
        // =============================
        const products = <?php echo json_encode($all_products, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const TOTAL_SLIDES = 3;

        // =============================
        // CARD SLIDER LOGIC
        // =============================
        function updateSlider(pid, index) {
            const slider = document.getElementById('slider-' + pid);
            if (!slider) return;

            // Wrap index
            if (index < 0) index = TOTAL_SLIDES - 1;
            if (index >= TOTAL_SLIDES) index = 0;

            slider.dataset.current = index;

            const track = slider.querySelector('.slides-track');
            track.style.transform = 'translateX(-' + (index * 100) + '%)';

            // Update dots
            const dots = slider.querySelectorAll('.slider-dots .dot');
            dots.forEach((d, i) => d.classList.toggle('active', i === index));

            // Update counter
            const counter = slider.querySelector('.img-count .counter');
            if (counter) counter.textContent = index + 1;
        }

        function slidePrev(pid, e) {
            if (e) { e.stopPropagation(); e.preventDefault(); }
            const slider = document.getElementById('slider-' + pid);
            if (!slider) return;
            const cur = parseInt(slider.dataset.current || 0);
            updateSlider(pid, cur - 1);
        }

        function slideNext(pid, e) {
            if (e) { e.stopPropagation(); e.preventDefault(); }
            const slider = document.getElementById('slider-' + pid);
            if (!slider) return;
            const cur = parseInt(slider.dataset.current || 0);
            updateSlider(pid, cur + 1);
        }

        function goToSlide(pid, index, e) {
            if (e) { e.stopPropagation(); e.preventDefault(); }
            updateSlider(pid, index);
        }

        // =============================
        // SWIPE SUPPORT (touch)
        // =============================
        (function enableSwipe() {
            let startX = 0;
            let startY = 0;
            let currentSlider = null;
            let isSwiping = false;

            document.addEventListener('touchstart', function(e) {
                const slider = e.target.closest('.slider');
                if (!slider) return;
                currentSlider = slider;
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                isSwiping = false;
            }, { passive: true });

            document.addEventListener('touchmove', function(e) {
                if (!currentSlider) return;
                const dx = e.touches[0].clientX - startX;
                const dy = e.touches[0].clientY - startY;

                // Horizontal swipe only
                if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 10) {
                    isSwiping = true;
                }
            }, { passive: true });

            document.addEventListener('touchend', function(e) {
                if (!currentSlider) return;
                const dx = e.changedTouches[0].clientX - startX;

                if (isSwiping && Math.abs(dx) > 40) {
                    const pid = currentSlider.id.replace('slider-', '');
                    if (dx < 0) slideNext(pid);
                    else slidePrev(pid);
                }

                currentSlider = null;
                isSwiping = false;
            }, { passive: true });
        })();

        // =============================
        // MODAL SLIDER LOGIC
        // =============================
        let modalSlideIndex = 0;

        function modalUpdateSlider(index) {
            const track = document.getElementById('modalTrack');
            if (!track) return;

            if (index < 0) index = TOTAL_SLIDES - 1;
            if (index >= TOTAL_SLIDES) index = 0;
            modalSlideIndex = index;

            track.style.transform = 'translateX(-' + (index * 100) + '%)';

            const dots = document.querySelectorAll('.modal-dots .mdot');
            dots.forEach((d, i) => d.classList.toggle('active', i === index));

            const thumbs = document.querySelectorAll('.modal-thumbs .mthumb');
            thumbs.forEach((t, i) => t.classList.toggle('active', i === index));
        }

        function modalPrev(e) { if (e) e.stopPropagation(); modalUpdateSlider(modalSlideIndex - 1); }
        function modalNext(e) { if (e) e.stopPropagation(); modalUpdateSlider(modalSlideIndex + 1); }
        function modalGoTo(i, e) { if (e) e.stopPropagation(); modalUpdateSlider(i); }

        // =============================
        // SEARCH PRODUCTS
        // =============================
        function searchProducts() {
            const input = document.getElementById('searchProduct');
            const filter = input.value.toLowerCase().trim();
            const grid = document.getElementById('productGrid');
            const cards = grid.getElementsByClassName('product-card');

            if (filter === '') {
                for (let i = 0; i < cards.length; i++) cards[i].style.display = '';
                return;
            }

            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                const cardText = card.textContent.toLowerCase();
                const seller = card.getAttribute('data-seller') || '';
                const category = card.getAttribute('data-category') || '';
                const name = card.getAttribute('data-name') || '';
                const searchable = cardText + ' ' + seller + ' ' + category + ' ' + name;
                card.style.display = searchable.includes(filter) ? '' : 'none';
            }
        }

        // =============================
        // TOGGLE WISHLIST
        // =============================
        function toggleWishlist(btn) {
            const icon = btn.querySelector('i');
            icon.classList.toggle('far');
            icon.classList.toggle('fas');

            if (icon.classList.contains('fas')) {
                btn.style.background = 'rgba(239, 68, 68, 0.12)';
                btn.style.color = '#ef4444';
                btn.style.borderColor = '#ef4444';
            } else {
                btn.style.background = '';
                btn.style.color = '';
                btn.style.borderColor = '';
            }
        }

        // =============================
        // OPEN PRODUCT MODAL
        // =============================
        function openProductModal(id) {
            const modal = document.getElementById('productModal');
            const content = document.getElementById('modalContent');

            const product = products.find(p => p.id == id);
            if (!product) {
                showToast('Product not found!');
                return;
            }

            const img1 = product.image  || 'placeholder.jpg';
            const img2 = product.image1 || img1;
            const img3 = product.image2 || img1;

            const sellerPhone   = product.seller_number  || 'N/A';
            const sellerName    = product.seller_name    || 'Unknown';
            const sellerAddress = product.seller_address || 'N/A';

            const badges = product.badge
                ? `<span class="badge ${product.badge}">${product.badge.charAt(0).toUpperCase() + product.badge.slice(1)}</span>`
                : '';
            const originalPrice = product.original_price
                ? `<span class="original">₹${parseFloat(product.original_price).toFixed(2)}</span>`
                : '';
            const discount = product.original_price
                ? `<span class="discount">-${Math.round(((product.original_price - product.price) / product.original_price) * 100)}%</span>`
                : '';

            content.innerHTML = `
                <div class="modal-slider-wrap">
                    <div class="modal-slider">
                        <div class="modal-track" id="modalTrack">
                            <div class="mslide"><img src="uploads/${img1}" alt="${product.product_name}"></div>
                            <div class="mslide"><img src="uploads/${img2}" alt="${product.product_name}"></div>
                            <div class="mslide"><img src="uploads/${img3}" alt="${product.product_name}"></div>
                        </div>
                        <button type="button" class="modal-arrow prev" onclick="modalPrev(event)"><i class="fas fa-chevron-left"></i></button>
                        <button type="button" class="modal-arrow next" onclick="modalNext(event)"><i class="fas fa-chevron-right"></i></button>
                        <div class="modal-dots">
                            <button type="button" class="mdot active" onclick="modalGoTo(0, event)"></button>
                            <button type="button" class="mdot" onclick="modalGoTo(1, event)"></button>
                            <button type="button" class="mdot" onclick="modalGoTo(2, event)"></button>
                        </div>
                    </div>
                    <div class="modal-thumbs">
                        <div class="mthumb active" onclick="modalGoTo(0, event)"><img src="uploads/${img1}" alt="view 1"></div>
                        <div class="mthumb" onclick="modalGoTo(1, event)"><img src="uploads/${img2}" alt="view 2"></div>
                        <div class="mthumb" onclick="modalGoTo(2, event)"><img src="uploads/${img3}" alt="view 3"></div>
                    </div>
                </div>
                <div class="modal-details">
                    <div class="badge-group">${badges}</div>
                    <div class="category">${product.category || 'General'}</div>
                    <h2>${product.product_name}</h2>
                    <div class="rating">
                        <span class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </span>
                        <span class="text">(4.5) 128 reviews</span>
                    </div>
                    <div class="price">
                        <span class="current">${parseFloat(product.price).toFixed(1)}</span>
                        ${originalPrice}
                        ${discount}
                    </div>
                    <div class="description">Modal<br>${product.description || 'No description available.'}</div>

                    <div class="seller-info">
                        <div class="seller-title"><i class="fas fa-store"></i> Seller Information</div>
                        <div class="seller-row">
                            <i class="fas fa-user"></i>
                            <span class="label">Seller:</span>
                            <span class="value">${sellerName}</span>
                        </div>
                        <div class="seller-row">
                            <i class="fas fa-phone"></i>
                            <span class="label">Phone:</span>
                            <span class="value">
                                <a href="tel:${sellerPhone.replace(/\s/g, '')}" class="phone-link">
                                    <i class="fas fa-phone-alt"></i> ${sellerPhone}
                                </a>
                            </span>
                        </div>
                        <div class="seller-row">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="label">Address:</span>
                            <span class="value">${sellerAddress}</span>
                        </div>
                    </div>

                    <div class="meta-info">
                        <div class="meta-item"><i class="fas fa-tag"></i><span>Category: ${product.category || 'General'}</span></div>
                        <div class="meta-item"><i class="fas fa-box"></i><span>user: ${product.user_id || 'General'}</span></div>
                        <div class="meta-item"><i class="fas fa-calendar"></i><span>price: ${product.price || 'General'}</span></div>
                        <div class="meta-item"><i class="fas fa-star"></i><span>modal: ${product.description || 'Null'}</span></div>
                    </div>

                    <div class="modal-actions">
                        <button class="btn btn-primary" onclick="buyNow(${product.id})">
                            <i class="fas fa-shopping-cart"></i> Buy Now
                        </button>
                        <button class="btn btn-wishlist" onclick="toggleWishlist(this)">
                            <i class="far fa-heart"></i>
                        </button>
                        <button class="btn btn-call" onclick="callSeller('${sellerPhone}')">
                            <i class="fas fa-phone"></i> Call Seller
                        </button>
                    </div>
                </div>
            `;

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            modalSlideIndex = 0;
        }

        // =============================
        // CALL SELLER
        // =============================
        function callSeller(phone) {
            showToast(`📞 Calling ${phone}...`);
            // window.location.href = `tel:${phone.replace(/\s/g, '')}`;
        }

        // =============================
        // CLOSE MODAL
        // =============================
        function closeModal() {
            const modal = document.getElementById('productModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
            if (e.key === 'ArrowLeft') { modalPrev(); }
            if (e.key === 'ArrowRight') { modalNext(); }
        });

        // =============================
        // BUY NOW
        // =============================
        function buyNow(id) {
            const product = products.find(p => p.id == id);
            showToast(`⚡ Proceeding to checkout for ${product.product_name}`);
        }

        // =============================
        // TOAST NOTIFICATION
        // =============================
        function showToast(message) {
            const existing = document.querySelector('.toast-notification');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // =============================
        // KEYBOARD SHORTCUTS
        // =============================
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.getElementById('searchProduct').focus();
            }
        });

        console.log('📦 ThunderX Products — Slider Loaded');
    </script>

</body>
</html>