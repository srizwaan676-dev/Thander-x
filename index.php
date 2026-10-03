<?php include("popup.php");?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>ThunderX — Premium Motorcycles</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* ==========================================
       THUNDERX — PROFESSIONAL EDITION
       Palette: Crimson Noir + Amber Gold
       ========================================== */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Bebas+Neue&display=swap');

    :root {
        /* ===== BRAND — CRIMSON ===== */
        --crimson-700: #8B0F2E;
        --crimson-600: #B91A3D;
        --crimson-500: #DC2449;
        --crimson-400: #E84866;
        --crimson-300: #F06B84;
        --crimson-glow: rgba(220, 36, 73, 0.35);

        /* ===== BRAND — AMBER ===== */
        --amber-600: #B87A0F;
        --amber-500: #E89B1F;
        --amber-400: #F2B340;
        --amber-300: #F8CE72;
        --amber-glow: rgba(232, 155, 31, 0.35);

        /* ===== NOIR ===== */
        --noir-950: #050506;
        --noir-900: #0A0A0D;
        --noir-800: #111116;
        --noir-700: #17171E;
        --noir-600: #1E1E28;
        --noir-500: #2A2A36;
        --noir-400: #3A3A48;

        /* ===== TEXT ===== */
        --text-primary: #FAFAFA;
        --text-secondary: #A1A1AA;
        --text-muted: #52525B;

        /* ===== SEMANTIC ===== */
        --success: #10B981;
        --danger: #EF4444;
        --warning: #F59E0B;

        /* ===== BORDERS ===== */
        --border-color: rgba(255, 255, 255, 0.06);
        --border-light: rgba(255, 255, 255, 0.1);
        --border-crimson: rgba(220, 36, 73, 0.25);

        /* ===== SHADOWS ===== */
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.5);
        --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.6);
        --shadow-lg: 0 24px 64px rgba(0, 0, 0, 0.8);
        --shadow-crimson: 0 8px 32px rgba(220, 36, 73, 0.28);

        /* ===== RADIUS ===== */
        --radius: 16px;
        --radius-sm: 10px;
        --radius-lg: 24px;
        --radius-full: 9999px;

        /* ===== TRANSITIONS ===== */
        --transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }
    html { scroll-behavior: smooth; }

    body {
        font-family: 'Inter', -apple-system, sans-serif;
        background: var(--noir-950);
        color: var(--text-primary);
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        overflow-x: hidden;
        position: relative;
    }

    /* Ambient professional glow */
    body::before {
        content: '';
        position: fixed;
        inset: 0;
        background:
            radial-gradient(ellipse 60% 40% at 15% 0%, rgba(220, 36, 73, 0.07), transparent 60%),
            radial-gradient(ellipse 50% 40% at 85% 100%, rgba(232, 155, 31, 0.05), transparent 60%);
        pointer-events: none;
        z-index: 0;
    }

    a { text-decoration: none; color: inherit; }
    img { max-width: 100%; display: block; }

    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: var(--noir-950); }
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, var(--crimson-500), var(--crimson-700));
        border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb:hover { background: var(--crimson-400); }

    .container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
        position: relative;
        z-index: 1;
    }

    /* ==========================================
       NAVBAR
       ========================================== */
    .navbar {
        position: sticky;
        top: 0;
        z-index: 1000;
        background: rgba(5, 5, 6, 0.85);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border-bottom: 1px solid var(--border-color);
        padding: 16px 0;
        transition: var(--transition);
    }
    .navbar.scrolled {
        box-shadow: 0 4px 40px rgba(0, 0, 0, 0.8);
        border-bottom-color: var(--border-crimson);
        padding: 12px 0;
    }
    .navbar .nav-wrap {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }
    .brand {
        display: flex;
        align-items: center;
        gap: 12px;
        font-family: 'Bebas Neue', sans-serif;
        font-size: 28px;
        font-weight: 400;
        letter-spacing: 1.5px;
    }
    .brand i {
        font-size: 26px;
        color: var(--crimson-500);
        filter: drop-shadow(0 0 12px var(--crimson-glow));
    }
    .brand span { color: var(--crimson-500); }

    #menu {
        display: flex;
        align-items: center;
        gap: 32px;
        list-style: none;
        flex-wrap: wrap;
    }
    #menu li a {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-secondary);
        transition: var(--transition);
        position: relative;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        padding: 6px 0;
    }
    #menu li a::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: linear-gradient(90deg, var(--crimson-500), var(--amber-500));
        transition: var(--transition);
        border-radius: 2px;
    }
    #menu li a:hover { color: var(--text-primary); }
    #menu li a:hover::after { width: 100%; }

    #menu .btn {
        padding: 11px 26px;
        border-radius: var(--radius-sm);
        font-weight: 600;
        font-size: 13px;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        background: linear-gradient(135deg, var(--crimson-500), var(--crimson-600));
        color: #fff;
        box-shadow: var(--shadow-crimson);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        position: relative;
        overflow: hidden;
    }
    #menu .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
        transition: left 0.6s ease;
    }
    #menu .btn:hover::before { left: 100%; }
    #menu .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px var(--crimson-glow);
    }
    #menu .btn-outline {
        background: transparent;
        border: 1px solid var(--border-light);
        color: var(--text-secondary);
        box-shadow: none;
    }
    #menu .btn-outline:hover {
        background: rgba(220, 36, 73, 0.08);
        border-color: var(--crimson-500);
        color: var(--crimson-400);
    }

    #btn-1 {
        display: none;
        background: transparent;
        border: none;
        color: var(--text-primary);
        font-size: 26px;
        cursor: pointer;
        padding: 6px;
    }

    @media (max-width: 768px) {
        #btn-1 { display: block; }
        #menu {
            display: none;
            flex-direction: column;
            width: 100%;
            padding: 16px 0 8px;
            gap: 6px;
            background: var(--noir-900);
            border-top: 1px solid var(--border-color);
            margin-top: 8px;
            border-radius: 0 0 var(--radius-sm) var(--radius-sm);
        }
        #menu.active { display: flex; }
        #menu li { width: 100%; }
        #menu li a {
            display: block;
            padding: 12px 4px;
            font-size: 14px;
            width: 100%;
            text-align: left;
        }
        #menu .btn {
            width: 100%;
            justify-content: center;
            padding: 12px;
            margin-top: 4px;
        }
    }

    /* ==========================================
       HERO
       ========================================== */
    .hero {
        position: relative;
        min-height: 100vh;
        overflow: hidden;
        display: flex;
        align-items: center;
        padding: 80px 0;
    }

    .hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            180deg,
            rgba(5, 5, 6, 0.85) 0%,
            rgba(5, 5, 6, 0.5) 40%,
            rgba(5, 5, 6, 0.85) 100%
        );
        z-index: 1;
    }

    .hero-text {
        position: relative;
        z-index: 2;
        max-width: 720px;
    }

    .hero-text h1 {
        font-family: 'Bebas Neue', sans-serif;
        font-size: clamp(48px, 8vw, 96px);
        font-weight: 400;
        line-height: 0.95;
        letter-spacing: 2px;
        margin: 16px 0 20px;
        background: linear-gradient(135deg, #FFFFFF 0%, #A1A1AA 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .hero-text h1 span {
        background: linear-gradient(135deg, var(--crimson-400), var(--crimson-600));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        filter: drop-shadow(0 0 30px var(--crimson-glow));
    }

    .hero-text p {
        color: var(--text-secondary);
        font-size: clamp(15px, 1.6vw, 18px);
        margin-bottom: 32px;
        max-width: 560px;
        line-height: 1.7;
    }

    .sub-heading {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2.5px;
        color: var(--crimson-400);
        margin-bottom: 8px;
        padding: 6px 14px;
        background: rgba(220, 36, 73, 0.08);
        border: 1px solid var(--border-crimson);
        border-radius: var(--radius-full);
    }

    .btn-group {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    .btn-group .btn {
        padding: 16px 36px;
        border-radius: var(--radius-sm);
        font-weight: 700;
        font-size: 14px;
        letter-spacing: 1px;
        text-transform: uppercase;
        border: none;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        position: relative;
        overflow: hidden;
    }
    .btn-primary {
        background: linear-gradient(135deg, var(--crimson-500), var(--crimson-700));
        color: #fff;
        box-shadow: var(--shadow-crimson);
    }
    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.6s ease;
    }
    .btn-primary:hover::before { left: 100%; }
    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 44px var(--crimson-glow);
    }

    #timer {
        display: flex;
        gap: 14px;
        margin-top: 40px;
        font-weight: 700;
        font-size: 22px;
        flex-wrap: wrap;
    }    /* ==========================================
       RESPONSIVE — MOBILE FIRST
       ========================================== */

    /* Tablet & Small Desktop */
    @media (max-width: 1024px) {
        .about-section,
        .tech-section {
            grid-template-columns: 1fr;
            gap: 40px;
            text-align: center;
            padding: 60px 0;
        }
        .tech-list { grid-template-columns: 1fr 1fr; }
        .section-sub {
            margin-left: auto;
            margin-right: auto;
        }
        .hero-text { max-width: 100%; }
    }

    /* Tablet */
    @media (max-width: 992px) {
        .container { padding: 0 22px; }
        
        .hero { 
            min-height: 90vh; 
            padding: 60px 0;
        }
        .hero-text h1 { font-size: 56px; }
        
        .about-section,
        .tech-section { padding: 60px 0; }
        
        .features-grid,
        .products-grid,
        .testimonials-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .footer-grid { 
            grid-template-columns: repeat(2, 1fr); 
            gap: 32px;
        }
    }

    /* Mobile — Main Breakpoint */
    @media (max-width: 768px) {
        body {
            font-size: 14px;
            line-height: 1.6;
        }
        
        .container { 
            padding: 0 18px; 
            width: 100%;
        }

        /* ===== NAVBAR ===== */
        .navbar { 
            padding: 12px 0;
            position: sticky;
        }
        .navbar.scrolled { padding: 10px 0; }
        
        .navbar .nav-wrap {
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .brand { 
            font-size: 22px; 
            gap: 10px;
        }
        .brand i { font-size: 22px; }
        
        #btn-1 { 
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            font-size: 22px;
            border-radius: 10px;
            background: rgba(220, 36, 73, 0.08);
            border: 1px solid var(--border-color);
        }
        #btn-1:active { 
            background: rgba(220, 36, 73, 0.15);
            transform: scale(0.95);
        }
        
        #menu {
            display: none;
            flex-direction: column;
            width: 100%;
            padding: 16px 0 12px;
            gap: 4px;
            background: var(--noir-900);
            border-top: 1px solid var(--border-color);
            margin-top: 4px;
            border-radius: 0 0 var(--radius) var(--radius);
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        #menu.active { display: flex; }
        
        #menu li { 
            width: 100%; 
            list-style: none;
        }
        #menu li a {
            display: block;
            padding: 14px 16px;
            font-size: 14px;
            width: 100%;
            text-align: left;
            border-radius: 10px;
            font-weight: 600;
        }
        #menu li a:hover {
            background: rgba(220, 36, 73, 0.08);
        }
        #menu li a::after { display: none; }
        
        #menu .btn {
            width: 100%;
            justify-content: center;
            padding: 14px 20px;
            margin-top: 6px;
            font-size: 13px;
            border-radius: 10px;
        }
        #menu .btn-outline { margin-top: 0; }

        /* ===== HERO ===== */
        .hero {
            min-height: auto;
            padding: 50px 0 70px;
        }
        .hero-text { 
            max-width: 100%; 
            text-align: center;
        }
        
        .sub-heading {
            font-size: 10px;
            letter-spacing: 2px;
            padding: 5px 12px;
            margin-bottom: 12px;
        }
        
        .hero-text h1 {
            font-size: clamp(36px, 10vw, 52px);
            line-height: 1;
            letter-spacing: 1px;
            margin: 12px 0 18px;
        }
        
        .hero-text p {
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 26px;
            padding: 0 4px;
        }
        
        .btn-group {
            justify-content: center;
            gap: 12px;
        }
        .btn-group .btn {
            padding: 14px 28px;
            font-size: 13px;
            border-radius: 10px;
        }
        
        #timer {
            justify-content: center;
            gap: 2px;
            margin-top: 32px;
            font-size: 20px;
        }
        #timer span {
            min-width: 68px;
            padding: 10px 14px;
            border-radius: 10px;
        }
        #timer small {
            font-size: 8px;
            letter-spacing: 1px;
        }

        /* ===== SECTION TITLES ===== */
        .section-title {
            font-size: clamp(30px, 8vw, 42px);
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .section-sub {
            font-size: 14px;
            margin-bottom: 32px;
            padding: 0 8px;
        }

        /* ===== ABOUT ===== */
        .about-section {
            grid-template-columns: 1fr;
            gap: 28px;
            padding: 50px 0;
            text-align: center;
        }
        .about-text p {
            font-size: 14px;
            line-height: 1.8;
            margin: 14px 0 22px;
        }
        .about-image {
            order: -1;
            border-radius: var(--radius);
        }
        .about-image img { 
            border-radius: var(--radius);
            max-height: 320px;
            object-fit: cover;
        }

        /* ===== FEATURES ===== */
        .features-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            padding: 24px 0 50px;
        }
        .feature-box {
            padding: 24px 16px;
            border-radius: var(--radius);
        }
        .feature-box i {
            font-size: 32px;
            margin-bottom: 14px;
        }
        .feature-box h3 {
            font-size: 15px;
            margin-bottom: 6px;
        }
        .feature-box p {
            font-size: 12.5px;
            line-height: 1.5;
        }

        /* ===== PRODUCTS ===== */
        .products-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            padding: 24px 0 50px;
        }
        .product-card {
            border-radius: var(--radius);
        }
        .product-card img {
            height: 160px;
            border-radius: var(--radius) var(--radius) 0 0;
        }
        .product-card .info {
            padding: 16px 14px 18px;
        }
        .product-card .info h3 {
            font-size: 16px;
            letter-spacing: -0.2px;
        }
        .product-card .info .price {
            font-size: 22px;
            margin: 4px 0 8px;
            gap: 8px;
            flex-wrap: wrap;
        }
        .product-card .info .price small {
            font-size: 11.5px;
        }
        .product-card .info p {
            font-size: 12px;
            margin-bottom: 14px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-card .info .btn-buy {
            padding: 11px 18px;
            font-size: 11.5px;
            width: 100%;
            justify-content: center;
            letter-spacing: 0.8px;
        }
        .product-card .wishlist {
            width: 34px;
            height: 34px;
            font-size: 14px;
            top: 10px;
            right: 10px;
        }

        /* ===== TECHNOLOGY ===== */
        .tech-section {
            grid-template-columns: 1fr;
            gap: 30px;
            padding: 50px 0;
            text-align: center;
        }
        .tech-list {
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 20px;
        }
        .tech-item {
            padding: 16px 14px;
            border-radius: var(--radius-sm);
            text-align: left;
        }
        .tech-item h3 {
            font-size: 13.5px;
            margin-bottom: 3px;
        }
        .tech-item p {
            font-size: 12px;
            line-height: 1.5;
        }
        .tech-right {
            order: -1;
        }
        .tech-right img {
            max-height: 300px;
            object-fit: cover;
            border-radius: var(--radius);
        }

        /* ===== TESTIMONIALS ===== */
        .testimonials-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            padding: 24px 0 50px;
        }
        .testimonial-card {
            padding: 20px 18px;
            border-radius: var(--radius);
        }
        .testimonial-card::before {
            font-size: 56px;
            top: 10px;
            right: 16px;
        }
        .testimonial-card .stars {
            font-size: 12px;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        .testimonial-card p {
            font-size: 12.5px;
            line-height: 1.6;
            margin-bottom: 14px;
            font-style: normal;
        }
        .testimonial-card .author {
            gap: 10px;
        }
        .testimonial-card .author .avatar {
            width: 38px;
            height: 38px;
            font-size: 13px;
        }
        .testimonial-card .author .name { font-size: 13px; }
        .testimonial-card .author .role { font-size: 11px; }

        /* ===== FOOTER ===== */
        .footer {
            padding: 40px 0 20px;
            margin-top: 30px;
        }
        .footer-grid {
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            padding-bottom: 28px;
        }
        .footer-grid h3 {
            font-size: 18px;
            margin-bottom: 12px;
        }
        .footer-grid ul li {
            font-size: 13px;
            margin-bottom: 8px;
        }
        .footer-grid ul li a { font-size: 13px; }
        .social-icons { gap: 8px; }
        .social-icons a {
            width: 38px;
            height: 38px;
            font-size: 14px;
        }
        .footer-bottom {
            padding-top: 20px;
            font-size: 12px;
        }

        /* ===== MODAL ===== */
        .modal-overlay {
            padding: 16px;
            align-items: flex-end;
        }
        .modal {
            padding: 32px 24px 28px;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            max-height: 92vh;
            width: 100%;
            max-width: 100%;
        }
        .modal::before {
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .modal h2 {
            font-size: 24px;
            letter-spacing: 1px;
        }
        .modal .subtitle {
            font-size: 13px;
            margin-bottom: 22px;
        }
        .modal .close {
            top: 16px;
            right: 16px;
            width: 32px;
            height: 32px;
            font-size: 14px;
        }
        .modal .product-preview {
            padding: 12px 14px;
            gap: 12px;
            margin-bottom: 20px;
        }
        .modal .product-preview img {
            width: 58px;
            height: 58px;
            border-radius: 10px;
        }
        .modal .product-preview .preview-info h4 {
            font-size: 14px;
        }
        .modal .product-preview .preview-info p {
            font-size: 18px;
        }
        .modal .form-group {
            margin-bottom: 14px;
        }
        .modal .form-group label {
            font-size: 11px;
            margin-bottom: 5px;
        }
        .modal .form-group input,
        .modal .form-group select,
        .modal .form-group textarea {
            padding: 12px 14px;
            font-size: 14px;
            border-radius: 10px;
        }
        .modal .order-summary {
            padding: 16px 18px;
            margin: 16px 0 20px;
            border-radius: 12px;
        }
        .modal .order-summary .row {
            font-size: 13px;
            padding: 5px 0;
        }
        .modal .order-summary .row.total {
            font-size: 15px;
            padding-top: 12px;
        }
        .modal .order-summary .row.total .amount {
            font-size: 22px;
        }
        .modal .btn-submit {
            padding: 15px;
            font-size: 13.5px;
            letter-spacing: 1.2px;
            border-radius: 12px;
        }

        /* ===== TOAST ===== */
        .toast {
            bottom: 90px;
            left: 16px;
            right: 16px;
            max-width: none;
            padding: 14px 18px;
            font-size: 13px;
            border-radius: 12px;
            text-align: center;
        }

        /* ===== BACK TO TOP ===== */
        .back-to-top {
            bottom: 90px;
            left: 16px;
            width: 42px;
            height: 42px;
            font-size: 16px;
        }

        /* ===== BOTTOM NAV ===== */
        .bottom-nav {
            width: calc(100% - 32px);
            max-width: 380px;
            height: 60px;
            bottom: 16px;
            padding: 7px 18px;
            border-radius: 30px;
        }
        .bottom-nav a {
            width: 42px;
            height: 42px;
            font-size: 17px;
        }
        .bottom-nav .center-btn {
            width: 54px;
            height: 54px;
            top: -22px;
            font-size: 20px;
        }

        /* ===== BODY BOTTOM PADDING (for bottom nav) ===== */
        body {
            padding-bottom: 90px;
        }
    }

    /* Small Mobile */
    @media (max-width: 480px) {
        .container { padding: 0 14px; }
        
        .brand { font-size: 20px; }
        .brand i { font-size: 20px; }
        
        .hero { padding: 40px 0 60px; }
        .hero-text h1 { 
            font-size: clamp(32px, 11vw, 42px);
            letter-spacing: 0.5px;
        }
        .hero-text p { 
            font-size: 14px;
            line-height: 1.6;
        }
        .btn-group .btn {
            padding: 13px 24px;
            font-size: 12px;
            width: 100%;
            max-width: 280px;
            justify-content: center;
        }
        
        #timer {
            gap: 2px;
            margin-top: 28px;
        }
        #timer span {
            min-width: 60px;
            padding: 8px 10px;
            font-size: 17px;
            border-radius: 10px;
            flex: 1;
            max-width: 75px;
        }
        #timer small {
            font-size: 7px;
            letter-spacing: 0.8px;
        }

        .section-title { 
            font-size: clamp(26px, 9vw, 34px);
            letter-spacing: 0.8px;
        }
        .section-sub { font-size: 13px; margin-bottom: 26px; }

        /* Features - single column on very small */
        .features-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .feature-box {
            padding: 20px 16px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .feature-box i {
            font-size: 28px;
            margin-bottom: 0;
            flex-shrink: 0;
        }
        .feature-box h3 { font-size: 15px; margin-bottom: 2px; }
        .feature-box p { font-size: 12px; }

        /* Products - single column */
        .products-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }
        .product-card img { height: 200px; }
        .product-card .info { padding: 16px 16px 20px; }
        .product-card .info h3 { font-size: 18px; }
        .product-card .info .price { font-size: 24px; }
        .product-card .info .price small { font-size: 12px; }
        .product-card .info p { 
            font-size: 13px;
            -webkit-line-clamp: 3;
        }
        .product-card .info .btn-buy {
            font-size: 13px;
            padding: 13px 20px;
            letter-spacing: 1px;
        }

        /* Tech - single column */
        .tech-list { grid-template-columns: 1fr; gap: 10px; }
        .tech-item { padding: 16px; }
        .tech-item h3 { font-size: 14px; }
        .tech-item p { font-size: 12.5px; }

        /* Testimonials - single column */
        .testimonials-grid { grid-template-columns: 1fr; gap: 12px; }
        .testimonial-card { padding: 20px 18px; }
        .testimonial-card p { font-size: 13px; }
        .testimonial-card .author .avatar { 
            width: 40px; 
            height: 40px; 
            font-size: 14px; 
        }
        .testimonial-card .author .name { font-size: 13.5px; }
        .testimonial-card .author .role { font-size: 11.5px; }

        /* Footer - single column */
        .footer { padding: 36px 0 20px; }
        .footer-grid {
            grid-template-columns: 1fr;
            gap: 24px;
            text-align: center;
            padding-bottom: 24px;
        }
        .footer-grid ul li { 
            justify-content: center; 
            font-size: 13px;
        }
        .social-icons { 
            justify-content: center; 
            gap: 10px;
        }
        .social-icons a {
            width: 40px;
            height: 40px;
            font-size: 15px;
        }

        /* Modal */
        .modal {
            padding: 28px 18px 24px;
        }
        .modal h2 { font-size: 22px; }
        .modal .subtitle { font-size: 12.5px; margin-bottom: 18px; }
        .modal .product-preview { padding: 10px 12px; }
        .modal .product-preview img { width: 52px; height: 52px; }
        .modal .product-preview .preview-info h4 { font-size: 13px; }
        .modal .product-preview .preview-info p { font-size: 16px; }
        .modal .form-group { margin-bottom: 12px; }
        .modal .form-group input,
        .modal .form-group select,
        .modal .form-group textarea {
            padding: 11px 13px;
            font-size: 13.5px;
        }
        .modal .order-summary { padding: 14px 16px; }
        .modal .order-summary .row { font-size: 12.5px; }
        .modal .order-summary .row.total { font-size: 14px; }
        .modal .order-summary .row.total .amount { font-size: 20px; }
        .modal .btn-submit {
            padding: 14px;
            font-size: 13px;
        }

        /* Bottom Nav */
        .bottom-nav {
            width: calc(100% - 20px);
            padding: 6px 14px;
            height: 58px;
        }
        .bottom-nav a {
            width: 38px;
            height: 38px;
            font-size: 16px;
        }
        .bottom-nav .center-btn {
            width: 50px;
            height: 50px;
            top: -20px;
            font-size: 18px;
            border-width: 2px;
        }

        .back-to-top {
            bottom: 84px;
            left: 14px;
            width: 40px;
            height: 40px;
            font-size: 15px;
        }

        .toast {
            bottom: 84px;
            left: 14px;
            right: 14px;
            padding: 12px 16px;
            font-size: 12.5px;
        }

        body { padding-bottom: 84px; }
    }

    /* Extra Small Mobile (iPhone SE, etc) */
    @media (max-width: 360px) {
        .container { padding: 0 12px; }
        
        .brand { font-size: 18px; }
        .brand i { font-size: 18px; }
        
        .hero-text h1 { font-size: 30px; }
        .hero-text p { font-size: 13px; }
        
        .section-title { font-size: 24px; }
        
        .feature-box { padding: 16px 14px; }
        .feature-box i { font-size: 24px; }
        .feature-box h3 { font-size: 14px; }
        .feature-box p { font-size: 11.5px; }

        .product-card .info h3 { font-size: 16px; }
        .product-card .info .price { font-size: 20px; }
        .product-card .info .btn-buy {
            font-size: 12px;
            padding: 11px 16px;
        }

        #timer span {
            min-width: 52px;
            padding: 7px 8px;
            font-size: 15px;
        }
        #timer small { font-size: 6.5px; }

        .bottom-nav {
            padding: 5px 10px;
            height: 54px;
        }
        .bottom-nav a {
            width: 34px;
            height: 34px;
            font-size: 14px;
        }
        .bottom-nav .center-btn {
            width: 46px;
            height: 46px;
            top: -18px;
            font-size: 16px;
        }

        .modal { padding: 24px 14px 20px; }
        .modal h2 { font-size: 20px; }
    }

    /* Landscape Mobile */
    @media (max-width: 768px) and (orientation: landscape) {
        .hero { 
            min-height: auto; 
            padding: 40px 0;
        }
        .hero-text h1 { font-size: 42px; }
        
        .bottom-nav {
            bottom: 10px;
            height: 54px;
        }
        .bottom-nav .center-btn {
            width: 48px;
            height: 48px;
            top: -18px;
        }
    }

    /* Safe area for iPhone notch */
    @supports (padding: max(0px)) {
        .bottom-nav {
            bottom: max(16px, env(safe-area-inset-bottom));
        }
        body {
            padding-bottom: max(90px, calc(env(safe-area-inset-bottom) + 70px));
        }
    }
    #timer span {
        background: rgba(17, 17, 22, 0.8);
        backdrop-filter: blur(12px);
        padding: 12px 20px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 76px;
        transition: var(--transition);
        font-family: 'Bebas Neue', sans-serif;
        letter-spacing: 1px;
    }
    #timer span:hover {
        border-color: var(--crimson-500);
        box-shadow: 0 0 24px var(--crimson-glow);
        transform: translateY(-2px);
    }
    #timer small {
        font-family: 'Inter', sans-serif;
        font-size: 9px;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 2px;
        letter-spacing: 1.5px;
        text-transform: uppercase;
    }

    /* ==========================================
       SECTION TITLES
       ========================================== */
    .section-title {
        font-family: 'Bebas Neue', sans-serif;
        font-size: clamp(32px, 4.5vw, 56px);
        font-weight: 400;
        line-height: 1;
        letter-spacing: 1.5px;
        margin-bottom: 12px;
        background: linear-gradient(135deg, #FFFFFF 0%, #71717A 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .section-sub {
        color: var(--text-secondary);
        font-size: 16px;
        margin-bottom: 48px;
        max-width: 640px;
    }

    /* ==========================================
       ABOUT
       ========================================== */
    .about-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        padding: 80px 0;
        align-items: center;
    }
    .about-text p {
        color: var(--text-secondary);
        font-size: 15px;
        line-height: 1.9;
        margin: 16px 0 24px;
    }
    .about-image {
        position: relative;
        border-radius: var(--radius-lg);
        overflow: hidden;
    }
    .about-image::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, transparent 50%, rgba(220, 36, 73, 0.15));
        pointer-events: none;
    }
    .about-image img {
        width: 100%;
        border-radius: var(--radius-lg);
        transition: var(--transition);
        box-shadow: var(--shadow-lg);
    }
    .about-image:hover img { transform: scale(1.03); }

    /* ==========================================
       FEATURES
       ========================================== */
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        padding: 40px 0 80px;
    }
    .feature-box {
        background: linear-gradient(145deg, var(--noir-800), var(--noir-900));
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 36px 28px;
        text-align: center;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }
    .feature-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--crimson-500), var(--amber-500));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.5s ease;
    }
    .feature-box:hover {
        transform: translateY(-8px);
        border-color: var(--border-crimson);
        box-shadow: 0 24px 60px rgba(220, 36, 73, 0.15);
    }
    .feature-box:hover::before { transform: scaleX(1); }
    .feature-box i {
        font-size: 44px;
        color: var(--crimson-500);
        margin-bottom: 20px;
        filter: drop-shadow(0 0 20px var(--crimson-glow));
        transition: var(--transition);
    }
    .feature-box:hover i {
        transform: scale(1.1) rotate(-5deg);
        color: var(--crimson-400);
    }
    .feature-box h3 {
        font-size: 19px;
        font-weight: 700;
        margin-bottom: 8px;
        letter-spacing: -0.3px;
    }
    .feature-box p { color: var(--text-secondary); font-size: 14px; line-height: 1.6; }

    /* ==========================================
       PRODUCTS
       ========================================== */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 28px;
        padding: 40px 0 80px;
    }
    .product-card {
        background: linear-gradient(145deg, var(--noir-800), var(--noir-900));
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: var(--transition);
        position: relative;
    }
    .product-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: var(--radius-lg);
        padding: 1px;
        background: linear-gradient(135deg, transparent, transparent, var(--crimson-500), transparent);
        -webkit-mask:
            linear-gradient(#fff 0 0) content-box,
            linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
                mask-composite: exclude;
        opacity: 0;
        transition: opacity 0.4s ease;
        pointer-events: none;
        z-index: 2;
    }
    .product-card:hover::before { opacity: 1; }
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 32px 80px rgba(220, 36, 73, 0.18);
    }
    .product-card img {
        width: 100%;
        height: 240px;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .product-card:hover img { transform: scale(1.08); }
    .product-card .info { padding: 24px 26px 28px; }
    .product-card .info h3 {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.4px;
    }
    .product-card .info .price {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 32px;
        letter-spacing: 1px;
        color: var(--crimson-400);
        margin: 6px 0 12px;
        display: flex;
        align-items: baseline;
        gap: 12px;
    }
    .product-card .info .price small {
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        color: var(--text-muted);
        text-decoration: line-through;
        font-weight: 500;
        letter-spacing: 0;
    }
    .product-card .info p {
        color: var(--text-secondary);
        font-size: 13.5px;
        margin-bottom: 20px;
        line-height: 1.6;
    }
    .product-card .info .btn-buy {
        display: inline-flex;
        align-items: center;
        border: none;
        border-radius: var(--radius-sm);
        padding: 13px 26px;
        background: linear-gradient(135deg, var(--crimson-500), var(--crimson-700));
        box-shadow: var(--shadow-crimson);
        cursor: pointer;
        transition: var(--transition);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        position: relative;
        overflow: hidden;
    }
    .product-card .info .btn-buy::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.6s ease;
    }
    .product-card .info .btn-buy:hover::before { left: 100%; }
    .product-card .info .btn-buy:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 36px var(--crimson-glow);
    }
    .product-card .info .btn-buy i { margin-right: 8px; }

    .product-card .wishlist {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: rgba(5, 5, 6, 0.75);
        backdrop-filter: blur(12px);
        border: 1px solid var(--border-light);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
        font-size: 17px;
        z-index: 3;
    }
    .product-card .wishlist:hover {
        color: var(--crimson-400);
        border-color: var(--crimson-500);
        background: rgba(220, 36, 73, 0.15);
        box-shadow: 0 0 24px var(--crimson-glow);
        transform: scale(1.1);
    }

    /* ==========================================
       TECHNOLOGY
       ========================================== */
    .tech-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        padding: 80px 0;
        align-items: center;
    }
    .tech-list {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-top: 28px;
    }
    .tech-item {
        background: linear-gradient(145deg, var(--noir-800), var(--noir-900));
        padding: 20px 22px;
        border-radius: var(--radius);
        border: 1px solid var(--border-color);
        transition: var(--transition);
    }
    .tech-item:hover {
        border-color: var(--border-crimson);
        transform: translateY(-4px);
        box-shadow: 0 16px 40px rgba(220, 36, 73, 0.12);
    }
    .tech-item h3 {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 4px;
        letter-spacing: -0.2px;
    }
    .tech-item p { color: var(--text-secondary); font-size: 13px; line-height: 1.5; }
    .tech-right img {
        width: 100%;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
    }

    /* ==========================================
       TESTIMONIALS
       ========================================== */
    .testimonials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        padding: 40px 0 80px;
    }
    .testimonial-card {
        background: linear-gradient(145deg, var(--noir-800), var(--noir-900));
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 28px 26px;
        transition: var(--transition);
        position: relative;
    }
    .testimonial-card::before {
        content: '"';
        position: absolute;
        top: 16px;
        right: 24px;
        font-family: 'Bebas Neue', sans-serif;
        font-size: 72px;
        color: var(--crimson-500);
        opacity: 0.15;
        line-height: 1;
    }
    .testimonial-card:hover {
        border-color: var(--border-crimson);
        transform: translateY(-6px);
        box-shadow: 0 20px 60px rgba(220, 36, 73, 0.12);
    }
    .testimonial-card .stars {
        color: var(--amber-500);
        font-size: 15px;
        margin-bottom: 12px;
        letter-spacing: 2px;
    }
    .testimonial-card p {
        color: var(--text-secondary);
        font-size: 14px;
        font-style: italic;
        margin-bottom: 20px;
        line-height: 1.7;
        position: relative;
        z-index: 1;
    }
    .testimonial-card .author {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .testimonial-card .author .avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--crimson-500), var(--crimson-700));
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 0 24px var(--crimson-glow);
        letter-spacing: 0.5px;
    }
    .testimonial-card .author .name { font-weight: 700; font-size: 14px; }
    .testimonial-card .author .role { font-size: 12px; color: var(--text-muted); }

    /* ==========================================
       FOOTER
       ========================================== */
    .footer {
        border-top: 1px solid var(--border-color);
        padding: 60px 0 24px;
        margin-top: 40px;
        background: linear-gradient(180deg, transparent, rgba(17, 17, 22, 0.4));
    }
    .footer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 40px;
        padding-bottom: 40px;
        border-bottom: 1px solid var(--border-color);
    }
    .footer-grid h3 {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 20px;
        font-weight: 400;
        letter-spacing: 1.5px;
        margin-bottom: 16px;
        color: var(--text-primary);
    }
    .footer-grid ul { list-style: none; }
    .footer-grid ul li {
        margin-bottom: 10px;
        color: var(--text-secondary);
        font-size: 13.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .footer-grid ul li a {
        color: var(--text-secondary);
        transition: var(--transition);
        font-size: 13.5px;
    }
    .footer-grid ul li a:hover {
        color: var(--crimson-400);
        padding-left: 4px;
    }
    .footer-grid ul li i { color: var(--crimson-500); }

    .social-icons { display: flex; gap: 10px; flex-wrap: wrap; }
    .social-icons a {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: var(--noir-800);
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        transition: var(--transition);
        font-size: 15px;
    }
    .social-icons a:hover {
        background: linear-gradient(135deg, var(--crimson-500), var(--crimson-700));
        color: #fff;
        border-color: var(--crimson-500);
        box-shadow: 0 0 28px var(--crimson-glow);
        transform: translateY(-3px);
    }
    .footer-bottom {
        text-align: center;
        padding-top: 24px;
        color: var(--text-muted);
        font-size: 12.5px;
        letter-spacing: 0.5px;
    }
    .footer-bottom span {
        color: var(--crimson-400);
        font-weight: 700;
    }

    /* ==========================================
       MODAL
       ========================================== */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(5, 5, 6, 0.9);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 24px;
        animation: fadeInModal 0.3s ease;
    }
    .modal-overlay.active { display: flex; }
    @keyframes fadeInModal {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .modal {
        background: linear-gradient(145deg, var(--noir-800), var(--noir-900));
        border: 1px solid var(--border-light);
        border-radius: var(--radius-lg);
        max-width: 540px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        padding: 40px 36px;
        box-shadow:
            0 40px 100px rgba(0, 0, 0, 0.9),
            0 0 0 1px rgba(220, 36, 73, 0.1);
        position: relative;
        animation: slideUpModal 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes slideUpModal {
        from { opacity: 0; transform: translateY(40px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .modal::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, var(--crimson-500), var(--amber-500), transparent);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }
    .modal .close {
        position: absolute;
        top: 20px;
        right: 22px;
        background: var(--noir-700);
        border: 1px solid var(--border-color);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        font-size: 16px;
        color: var(--text-secondary);
        cursor: pointer;
        transition: var(--transition);
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal .close:hover {
        color: #fff;
        background: var(--crimson-500);
        border-color: var(--crimson-500);
        transform: rotate(90deg);
        box-shadow: 0 0 20px var(--crimson-glow);
    }
    .modal h2 {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 32px;
        font-weight: 400;
        letter-spacing: 1.5px;
        margin-bottom: 4px;
    }
    .modal .subtitle {
        color: var(--text-secondary);
        font-size: 13.5px;
        margin-bottom: 28px;
    }
    .modal .product-preview {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 14px 18px;
        background: var(--noir-950);
        border-radius: var(--radius);
        border: 1px solid var(--border-color);
        margin-bottom: 24px;
    }
    .modal .product-preview img {
        width: 72px;
        height: 72px;
        border-radius: 12px;
        object-fit: cover;
    }
    .modal .product-preview .preview-info h4 {
        font-size: 16px;
        font-weight: 700;
        letter-spacing: -0.3px;
    }
    .modal .product-preview .preview-info p {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 20px;
        color: var(--crimson-400);
        letter-spacing: 1px;
        margin-top: 2px;
    }
    .modal .form-group { margin-bottom: 18px; }
    .modal .form-group label {
        display: block;
        font-size: 11.5px;
        font-weight: 700;
        color: var(--text-secondary);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .modal .form-group label i {
        margin-right: 6px;
        color: var(--crimson-500);
    }
    .modal .form-group input,
    .modal .form-group select,
    .modal .form-group textarea {
        width: 100%;
        padding: 13px 16px;
        background: var(--noir-950);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        color: var(--text-primary);
        font-size: 14px;
        transition: var(--transition);
        font-family: 'Inter', sans-serif;
        outline: none;
        resize: vertical;
    }
    .modal .form-group input:focus,
    .modal .form-group select:focus,
    .modal .form-group textarea:focus {
        border-color: var(--crimson-500);
        box-shadow: 0 0 0 3px var(--crimson-glow);
    }
    .modal .order-summary {
        background: var(--noir-950);
        border-radius: var(--radius);
        padding: 20px 22px;
        margin: 20px 0 24px;
        border: 1px solid var(--border-color);
    }
    .modal .order-summary .row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        font-size: 14px;
        color: var(--text-secondary);
    }
    .modal .order-summary .row.total {
        border-top: 1px solid var(--border-color);
        margin-top: 12px;
        padding-top: 16px;
        font-weight: 700;
        font-size: 17px;
        color: var(--text-primary);
    }
    .modal .order-summary .row.total .amount {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 24px;
        letter-spacing: 1px;
        color: var(--crimson-400);
    }
    .modal .btn-submit {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, var(--crimson-500), var(--crimson-700));
        color: #fff;
        border: none;
        border-radius: var(--radius-sm);
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: var(--shadow-crimson);
        font-family: 'Inter', sans-serif;
        position: relative;
        overflow: hidden;
    }
    .modal .btn-submit::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.6s ease;
    }
    .modal .btn-submit:hover::before { left: 100%; }
    .modal .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 44px var(--crimson-glow);
    }
    .modal .btn-submit:active { transform: scale(0.98); }

    /* ==========================================
       TOAST
       ========================================== */
    .toast {
        position: fixed;
        bottom: 100px;
        right: 30px;
        left: auto;
        background: linear-gradient(145deg, var(--noir-800), var(--noir-900));
        border: 1px solid var(--border-light);
        color: var(--text-primary);
        padding: 16px 26px;
        border-radius: var(--radius);
        font-size: 13.5px;
        font-weight: 500;
        box-shadow: var(--shadow-lg);
        z-index: 99999;
        transform: translateY(80px);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        pointer-events: none;
        border-left: 3px solid var(--crimson-500);
        backdrop-filter: blur(16px);
        max-width: 420px;
    }
    .toast.show { transform: translateY(0); opacity: 1; pointer-events: auto; }

    /* ==========================================
       BACK TO TOP
       ========================================== */
    .back-to-top {
        position: fixed;
        bottom: 100px;
        left: 30px;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--crimson-500), var(--crimson-700));
        color: #fff;
        border: none;
        font-size: 18px;
        cursor: pointer;
        box-shadow: var(--shadow-crimson);
        transition: var(--transition);
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
        z-index: 999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .back-to-top.visible {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .back-to-top:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px var(--crimson-glow);
    }

    /* ==========================================
       BOTTOM NAV (Floating Pill)
       ========================================== */
    .bottom-nav {
        position: fixed;
        left: 50%;
        bottom: 24px;
        transform: translateX(-50%);
        width: 340px;
        height: 64px;
        padding: 8px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: rgba(17, 17, 22, 0.85);
        backdrop-filter: blur(28px);
        -webkit-backdrop-filter: blur(28px);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-full);
        box-shadow:
            0 20px 60px rgba(0, 0, 0, 0.6),
            0 0 0 1px rgba(220, 36, 73, 0.08);
        z-index: 9998;
        transition: var(--transition);
    }
    .bottom-nav a {
        position: relative;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 18px;
        border-radius: 50%;
        transition: var(--transition);
    }
    .bottom-nav a:hover {
        color: var(--crimson-400);
        background: rgba(220, 36, 73, 0.1);
        transform: translateY(-3px);
    }
    .bottom-nav a.active {
        color: var(--crimson-400);
        background: rgba(220, 36, 73, 0.15);
    }
    .bottom-nav .center-btn {
        position: absolute;
        top: -26px;
        left: 50%;
        width: 60px;
        height: 60px;
        transform: translateX(-50%);
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--crimson-500), var(--crimson-700));
        color: #fff;
        font-size: 22px;
        border-radius: 50%;
        border: 3px solid var(--noir-950);
        box-shadow:
            0 12px 32px var(--crimson-glow),
            0 6px 16px rgba(0, 0, 0, 0.5);
        z-index: 10;
        cursor: pointer;
        transition: var(--transition);
    }
    .bottom-nav .center-btn:hover {
        transform: translateX(-50%) translateY(-3px) scale(1.08);
        box-shadow: 0 18px 48px var(--crimson-glow);
    }

    /* ==========================================
       RESPONSIVE
       ========================================== */
  /* ==========================================
       RESPONSIVE — MOBILE FIRST
       ========================================== */

    /* ===== TABLET & SMALL DESKTOP ===== */
    @media (max-width: 1024px) {
        .about-section,
        .tech-section {
            grid-template-columns: 1fr;
            gap: 40px;
            text-align: center;
            padding: 60px 0;
        }
        .tech-list { grid-template-columns: 1fr 1fr; }
        .section-sub {
            margin-left: auto;
            margin-right: auto;
        }
        .hero-text { max-width: 100%; }
    }

    /* ===== TABLET ===== */
    @media (max-width: 992px) {
        .container { padding: 0 22px; }
        
        .hero { 
            min-height: 90vh; 
            padding: 60px 0;
        }
        .hero-text h1 { font-size: 56px; }
        
        .about-section,
        .tech-section { padding: 60px 0; }
        
        .features-grid,
        .products-grid,
        .testimonials-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .footer-grid { 
            grid-template-columns: repeat(2, 1fr); 
            gap: 32px;
        }
    }

    /* ===== MOBILE — MAIN BREAKPOINT ===== */
    @media (max-width: 768px) {
        body {
            font-size: 14px;
            line-height: 1.6;
            padding-bottom: 90px;
        }
        
        .container { 
            padding: 0 18px; 
            width: 100%;
        }

        /* ===== NAVBAR ===== */
        .navbar { 
            padding: 12px 0;
            position: sticky;
        }
        .navbar.scrolled { padding: 10px 0; }
        
        .navbar .nav-wrap {
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .brand { 
            font-size: 22px; 
            gap: 10px;
        }
        .brand i { font-size: 22px; }
        
        #btn-1 { 
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            font-size: 22px;
            border-radius: 12px;
            background: rgba(220, 36, 73, 0.08);
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }
        #btn-1:active { 
            background: rgba(220, 36, 73, 0.15);
            transform: scale(0.95);
        }
        
        #menu {
            display: none;
            flex-direction: column;
            width: 100%;
            padding: 16px 0 12px;
            gap: 4px;
            background: var(--noir-900);
            border-top: 1px solid var(--border-color);
            margin-top: 4px;
            border-radius: 0 0 var(--radius) var(--radius);
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        #menu.active { display: flex; }
        
        #menu li { 
            width: 100%; 
            list-style: none;
        }
        #menu li a {
            display: block;
            padding: 14px 16px;
            font-size: 14px;
            width: 100%;
            text-align: left;
            border-radius: 10px;
            font-weight: 600;
        }
        #menu li a:hover {
            background: rgba(220, 36, 73, 0.08);
        }
        #menu li a::after { display: none; }
        
        #menu .btn {
            width: 100%;
            justify-content: center;
            padding: 14px 20px;
            margin-top: 6px;
            font-size: 13px;
            border-radius: 10px;
        }
        #menu .btn-outline { margin-top: 0; }

        /* ===== HERO ===== */
        .hero {
            min-height: auto;
            padding: 50px 0 70px;
        }
        .hero-text { 
            max-width: 100%; 
            text-align: center;
        }
        
        .sub-heading {
            font-size: 10px;
            letter-spacing: 2px;
            padding: 5px 12px;
            margin-bottom: 12px;
        }
        
        .hero-text h1 {
            font-size: clamp(36px, 10vw, 52px);
            line-height: 1;
            letter-spacing: 1px;
            margin: 12px 0 18px;
        }
        
        .hero-text p {
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 26px;
            padding: 0 4px;
        }
        
        .btn-group {
            justify-content: center;
            gap: 12px;
        }
        .btn-group .btn {
            padding: 14px 28px;
            font-size: 13px;
            border-radius: 10px;
        }
        
        #timer {
            justify-content: center;
            gap: 6px;
            margin-top: 32px;
            font-size: 20px;
        }
        #timer span {
            min-width: 68px;
            padding: 10px 14px;
            border-radius: 10px;
        }
        #timer small {
            font-size: 8px;
            letter-spacing: 1px;
        }

        /* ===== SECTION TITLES ===== */
        .section-title {
            font-size: clamp(30px, 8vw, 42px);
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .section-sub {
            font-size: 14px;
            margin-bottom: 32px;
            padding: 0 8px;
        }

        /* ===== ABOUT ===== */
        .about-section {
            grid-template-columns: 1fr;
            gap: 28px;
            padding: 50px 0;
            text-align: center;
        }
        .about-text p {
            font-size: 14px;
            line-height: 1.8;
            margin: 14px 0 22px;
        }
        .about-image {
            order: -1;
            border-radius: var(--radius);
        }
        .about-image img { 
            border-radius: var(--radius);
            max-height: 320px;
            object-fit: cover;
        }

        /* ===== FEATURES ===== */
        .features-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            padding: 24px 0 50px;
        }
        .feature-box {
            padding: 24px 16px;
            border-radius: var(--radius);
        }
        .feature-box i {
            font-size: 32px;
            margin-bottom: 14px;
        }
        .feature-box h3 {
            font-size: 15px;
            margin-bottom: 6px;
        }
        .feature-box p {
            font-size: 12.5px;
            line-height: 1.5;
        }

        /* ===== PRODUCTS ===== */
        .products-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            padding: 24px 0 50px;
        }
        .product-card {
            border-radius: var(--radius);
        }
        .product-card img {
            height: 160px;
            border-radius: var(--radius) var(--radius) 0 0;
        }
        .product-card .info {
            padding: 16px 14px 18px;
        }
        .product-card .info h3 {
            font-size: 16px;
            letter-spacing: -0.2px;
        }
        .product-card .info .price {
            font-size: 22px;
            margin: 4px 0 8px;
            gap: 8px;
            flex-wrap: wrap;
        }
        .product-card .info .price small {
            font-size: 11.5px;
        }
        .product-card .info p {
            font-size: 12px;
            margin-bottom: 14px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-card .info .btn-buy {
            padding: 11px 18px;
            font-size: 11.5px;
            width: 100%;
            justify-content: center;
            letter-spacing: 0.8px;
        }
        .product-card .wishlist {
            width: 34px;
            height: 34px;
            font-size: 14px;
            top: 10px;
            right: 10px;
        }

        /* ===== TECHNOLOGY ===== */
        .tech-section {
            grid-template-columns: 1fr;
            gap: 30px;
            padding: 50px 0;
            text-align: center;
        }
        .tech-list {
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 20px;
        }
        .tech-item {
            padding: 16px 14px;
            border-radius: var(--radius-sm);
            text-align: left;
        }
        .tech-item h3 {
            font-size: 13.5px;
            margin-bottom: 3px;
        }
        .tech-item p {
            font-size: 12px;
            line-height: 1.5;
        }
        .tech-right {
            order: -1;
        }
        .tech-right img {
            max-height: 300px;
            object-fit: cover;
            border-radius: var(--radius);
        }

        /* ===== TESTIMONIALS ===== */
        .testimonials-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            padding: 24px 0 50px;
        }
        .testimonial-card {
            padding: 20px 18px;
            border-radius: var(--radius);
        }
        .testimonial-card::before {
            font-size: 56px;
            top: 10px;
            right: 16px;
        }
        .testimonial-card .stars {
            font-size: 12px;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        .testimonial-card p {
            font-size: 12.5px;
            line-height: 1.6;
            margin-bottom: 14px;
            font-style: normal;
        }
        .testimonial-card .author {
            gap: 10px;
        }
        .testimonial-card .author .avatar {
            width: 38px;
            height: 38px;
            font-size: 13px;
        }
        .testimonial-card .author .name { font-size: 13px; }
        .testimonial-card .author .role { font-size: 11px; }

        /* ===== FOOTER ===== */
        .footer {
            padding: 40px 0 20px;
            margin-top: 30px;
        }
        .footer-grid {
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            padding-bottom: 28px;
        }
        .footer-grid h3 {
            font-size: 18px;
            margin-bottom: 12px;
        }
        .footer-grid ul li {
            font-size: 13px;
            margin-bottom: 8px;
        }
        .footer-grid ul li a { font-size: 13px; }
        .social-icons { gap: 8px; }
        .social-icons a {
            width: 38px;
            height: 38px;
            font-size: 14px;
        }
        .footer-bottom {
            padding-top: 20px;
            font-size: 12px;
        }

        /* ===== MODAL ===== */
        .modal-overlay {
            padding: 16px;
            align-items: flex-end;
        }
        .modal {
            padding: 32px 24px 28px;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            max-height: 92vh;
            width: 100%;
            max-width: 100%;
        }
        .modal::before {
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .modal h2 {
            font-size: 24px;
            letter-spacing: 1px;
        }
        .modal .subtitle {
            font-size: 13px;
            margin-bottom: 22px;
        }
        .modal .close {
            top: 16px;
            right: 16px;
            width: 32px;
            height: 32px;
            font-size: 14px;
        }
        .modal .product-preview {
            padding: 12px 14px;
            gap: 12px;
            margin-bottom: 20px;
        }
        .modal .product-preview img {
            width: 58px;
            height: 58px;
            border-radius: 10px;
        }
        .modal .product-preview .preview-info h4 {
            font-size: 14px;
        }
        .modal .product-preview .preview-info p {
            font-size: 18px;
        }
        .modal .form-group {
            margin-bottom: 14px;
        }
        .modal .form-group label {
            font-size: 11px;
            margin-bottom: 5px;
        }
        .modal .form-group input,
        .modal .form-group select,
        .modal .form-group textarea {
            padding: 12px 14px;
            font-size: 14px;
            border-radius: 10px;
        }
        .modal .order-summary {
            padding: 16px 18px;
            margin: 16px 0 20px;
            border-radius: 12px;
        }
        .modal .order-summary .row {
            font-size: 13px;
            padding: 5px 0;
        }
        .modal .order-summary .row.total {
            font-size: 15px;
            padding-top: 12px;
        }
        .modal .order-summary .row.total .amount {
            font-size: 22px;
        }
        .modal .btn-submit {
            padding: 15px;
            font-size: 13.5px;
            letter-spacing: 1.2px;
            border-radius: 12px;
        }

        /* ===== TOAST ===== */
        .toast {
            bottom: 90px;
            left: 16px;
            right: 16px;
            max-width: none;
            padding: 14px 18px;
            font-size: 13px;
            border-radius: 12px;
            text-align: center;
        }

        /* ===== BACK TO TOP ===== */
        .back-to-top {
            bottom: 90px;
            left: 16px;
            width: 42px;
            height: 42px;
            font-size: 16px;
        }

        /* ===== BOTTOM NAV ===== */
        .bottom-nav {
            width: calc(100% - 32px);
            max-width: 380px;
            height: 60px;
            bottom: 16px;
            padding: 7px 18px;
            border-radius: 30px;
        }
        .bottom-nav a {
            width: 42px;
            height: 42px;
            font-size: 17px;
        }
        .bottom-nav .center-btn {
            width: 54px;
            height: 54px;
            top: -22px;
            font-size: 20px;
        }
    }

    /* ===== SMALL MOBILE ===== */
    @media (max-width: 480px) {
        .container { padding: 0 14px; }
        
        .brand { font-size: 20px; }
        .brand i { font-size: 20px; }
        
        .hero { padding: 40px 0 60px; }
        .hero-text h1 { 
            font-size: clamp(32px, 11vw, 42px);
            letter-spacing: 0.5px;
        }
        .hero-text p { 
            font-size: 14px;
            line-height: 1.6;
        }
        .btn-group .btn {
            padding: 13px 24px;
            font-size: 12px;
            width: 100%;
            max-width: 280px;
            justify-content: center;
        }
        
        #timer {
            gap: 2px;
            margin-top: 28px;
        }
        #timer span {
            min-width: 60px;
            padding: 8px 10px;
            font-size: 17px;
            border-radius: 10px;
            flex: 1;
            max-width: 75px;
        }
        #timer small {
            font-size: 7px;
            letter-spacing: 0.8px;
        }

        .section-title { 
            font-size: clamp(26px, 9vw, 34px);
            letter-spacing: 0.8px;
        }
        .section-sub { font-size: 13px; margin-bottom: 26px; }

        /* Features - single column */
        .features-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .feature-box {
            padding: 20px 16px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .feature-box i {
            font-size: 28px;
            margin-bottom: 0;
            flex-shrink: 0;
        }
        .feature-box h3 { font-size: 15px; margin-bottom: 2px; }
        .feature-box p { font-size: 12px; }

        /* Products - single column */
        .products-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }
        .product-card img { height: 200px; }
        .product-card .info { padding: 16px 16px 20px; }
        .product-card .info h3 { font-size: 18px; }
        .product-card .info .price { font-size: 24px; }
        .product-card .info .price small { font-size: 12px; }
        .product-card .info p { 
            font-size: 13px;
            -webkit-line-clamp: 3;
        }
        .product-card .info .btn-buy {
            font-size: 13px;
            padding: 13px 20px;
            letter-spacing: 1px;
        }

        /* Tech - single column */
        .tech-list { grid-template-columns: 1fr; gap: 10px; }
        .tech-item { padding: 16px; }
        .tech-item h3 { font-size: 14px; }
        .tech-item p { font-size: 12.5px; }

        /* Testimonials - single column */
        .testimonials-grid { grid-template-columns: 1fr; gap: 12px; }
        .testimonial-card { padding: 20px 18px; }
        .testimonial-card p { font-size: 13px; }
        .testimonial-card .author .avatar { 
            width: 40px; 
            height: 40px; 
            font-size: 14px; 
        }
        .testimonial-card .author .name { font-size: 13.5px; }
        .testimonial-card .author .role { font-size: 11.5px; }

        /* Footer - single column */
        .footer { padding: 36px 0 20px; }
        .footer-grid {
            grid-template-columns: 1fr;
            gap: 24px;
            text-align: center;
            padding-bottom: 24px;
        }
        .footer-grid ul li { 
            justify-content: center; 
            font-size: 13px;
        }
        .social-icons { 
            justify-content: center; 
            gap: 10px;
        }
        .social-icons a {
            width: 40px;
            height: 40px;
            font-size: 15px;
        }

        /* Modal */
        .modal {
            padding: 28px 18px 24px;
        }
        .modal h2 { font-size: 22px; }
        .modal .subtitle { font-size: 12.5px; margin-bottom: 18px; }
        .modal .product-preview { padding: 10px 12px; }
        .modal .product-preview img { width: 52px; height: 52px; }
        .modal .product-preview .preview-info h4 { font-size: 13px; }
        .modal .product-preview .preview-info p { font-size: 16px; }
        .modal .form-group { margin-bottom: 12px; }
        .modal .form-group input,
        .modal .form-group select,
        .modal .form-group textarea {
            padding: 11px 13px;
            font-size: 13.5px;
        }
        .modal .order-summary { padding: 14px 16px; }
        .modal .order-summary .row { font-size: 12.5px; }
        .modal .order-summary .row.total { font-size: 14px; }
        .modal .order-summary .row.total .amount { font-size: 20px; }
        .modal .btn-submit {
            padding: 14px;
            font-size: 13px;
        }

        /* Bottom Nav */
        .bottom-nav {
            width: calc(100% - 20px);
            padding: 6px 14px;
            height: 58px;
        }
        .bottom-nav a {
            width: 38px;
            height: 38px;
            font-size: 16px;
        }
        .bottom-nav .center-btn {
            width: 50px;
            height: 50px;
            top: -20px;
            font-size: 18px;
            border-width: 2px;
        }

        .back-to-top {
            bottom: 84px;
            left: 14px;
            width: 40px;
            height: 40px;
            font-size: 15px;
        }

        .toast {
            bottom: 84px;
            left: 14px;
            right: 14px;
            padding: 12px 16px;
            font-size: 12.5px;
        }

        body { padding-bottom: 84px; }
    }

    /* ===== EXTRA SMALL MOBILE (iPhone SE) ===== */
    @media (max-width: 360px) {
        .container { padding: 0 12px; }
        
        .brand { font-size: 18px; }
        .brand i { font-size: 18px; }
        
        .hero-text h1 { font-size: 30px; }
        .hero-text p { font-size: 13px; }
        
        .section-title { font-size: 24px; }
        
        .feature-box { padding: 16px 14px; }
        .feature-box i { font-size: 24px; }
        .feature-box h3 { font-size: 14px; }
        .feature-box p { font-size: 11.5px; }

        .product-card .info h3 { font-size: 16px; }
        .product-card .info .price { font-size: 20px; }
        .product-card .info .btn-buy {
            font-size: 12px;
            padding: 11px 16px;
        }

        #timer span {
            min-width: 52px;
            padding: 7px 8px;
            font-size: 15px;
        }
        #timer small { font-size: 6.5px; }

        .bottom-nav {
            padding: 5px 10px;
            height: 54px;
        }
        .bottom-nav a {
            width: 34px;
            height: 34px;
            font-size: 14px;
        }
        .bottom-nav .center-btn {
            width: 46px;
            height: 46px;
            top: -18px;
            font-size: 16px;
        }

        .modal { padding: 24px 14px 20px; }
        .modal h2 { font-size: 20px; }
    }

    /* ===== LANDSCAPE MOBILE ===== */
    @media (max-width: 768px) and (orientation: landscape) {
        .hero { 
            min-height: auto; 
            padding: 40px 0;
        }
        .hero-text h1 { font-size: 42px; }
        
        .bottom-nav {
            bottom: 10px;
            height: 54px;
        }
        .bottom-nav .center-btn {
            width: 48px;
            height: 48px;
            top: -18px;
        }
    }

    /* ===== SAFE AREA (iPhone Notch) ===== */
    @supports (padding: max(0px)) {
        .bottom-nav {
            bottom: max(16px, env(safe-area-inset-bottom));
        }
        body {
            padding-bottom: max(90px, calc(env(safe-area-inset-bottom) + 70px));
        }
    }

    /* ===== FOCUS VISIBLE ===== */
    *:focus-visible {
        outline: 2px solid var(--crimson-500);
        outline-offset: 3px;
        border-radius: var(--radius-sm);
    }

    /* ===== REDUCED MOTION ===== */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>
</head>
<body>

    <!-- ===== NAVBAR ===== -->
    <nav class="navbar" id="navbar">
        <div class="container nav-wrap">
            <div class="brand">
                <i class="fas fa-motorcycle"></i>
                Thunder<span>X</span>
            </div>
            <button id="btn-1" aria-label="Toggle navigation"><i class="fas fa-bars"></i></button>
            <ul id="menu">
                <li><a href="#home">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="blog.php">Models</a></li>
                <li><a href="#contact">Contact</a></li>
                <a href="register.php" class="btn btn-outline" >register</a>
                <a href="login.php" class="btn btn-outline">Login</a>
            </ul>
        </div>
    </nav>

    <!-- ===== HERO ===== -->
  <section class="container hero" id="home">

    <!-- Background Video -->
 <div class="about-image">
            <img src="https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=800&h=600&fit=crop&crop=center" alt="About ThunderX" loading="lazy">
        </div>
    <!-- Dark Overlay -->
    <div class="hero-overlay"></div>

    <div class="hero-text">
        <!-- <span class="sub-heading">🔥 2026 EDITION</span> -->

        <h1>Ride the Future <br>with <span>ThunderX</span></h1>

        <p>
            Experience unmatched power, style, and performance
            with our premium electric cruisers. Engineered for the bold.
        </p>

        <div class="btn-group">
            <a href="blog.php" class="btn btn-primary">
                <i class="fas fa-bolt"></i> Explore Models
            </a>
        </div>

        <div id="timer">
            <span id="days">00<small>Days</small></span>
            <span id="hours">00<small>Hours</small></span>
            <span id="minutes">00<small>Minutes</small></span>
            <span id="seconds">00<small>Seconds</small></span>
        </div>
    </div>

</section>

    <!-- ===== ABOUT ===== -->
    <section class="container about-section" id="about">
        <div class="about-image">
            <img src="https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=800&h=600&fit=crop&crop=center" alt="About ThunderX" loading="lazy">
        </div>
        <div class="about-text">
            <span class="sub-heading">About ThunderX</span>
            <h2 class="section-title">Crafted for Performance,<br>Built to Last</h2>
            <p>ThunderX is a premium motorcycle brand dedicated to delivering cutting‑edge electric bikes that combine raw power, intelligent technology, and timeless design. Every bike is engineered to provide an exhilarating ride while being eco‑friendly and sustainable.</p>
            <p>With a focus on innovation and customer satisfaction, we are redefining the future of two‑wheeled mobility.</p>
            <a href="#" class="btn btn-primary" style="display:inline-block; padding:12px 28px; border-radius:var(--radius-sm); font-weight:600; background:linear-gradient(135deg,var(--primary),var(--primary-dark)); color:#fff; border:none; cursor:pointer; transition:var(--transition);">Learn More <i class="fas fa-arrow-right"></i></a>
        </div>
    </section>

    <!-- ===== FEATURES ===== -->
    <div class="container">
        <div style="text-align:center; padding-top:40px;">
            <span class="sub-heading">Why ThunderX</span>
            <h2 class="section-title">Built for the Bold</h2>
        </div>
        <div class="features-grid">
            <div class="feature-box">
                <i class="fas fa-bolt"></i>
                <h3>Instant Torque</h3>
                <p>Electric motors deliver 100% torque instantly for lightning‑fast acceleration.</p>
            </div>
            <div class="feature-box">
                <i class="fas fa-battery-full"></i>
                <h3>Long Range</h3>
                <p>Up to 200 km on a single charge with advanced battery management.</p>
            </div>
            <div class="feature-box">
                <i class="fas fa-shield-alt"></i>
                <h3>Safety First</h3>
                <p>Dual ABS, traction control, and smart braking systems ensure a safe ride.</p>
            </div>
            <div class="feature-box">
                <i class="fas fa-cloud-upload-alt"></i>
                <h3>Smart Connected</h3>
                <p>Real‑time diagnostics, GPS tracking, and over‑the‑air updates.</p>
            </div>
        </div>
    </div>

    <!-- ===== PRODUCTS ===== -->
    <div class="container" id="models">
        <div style="text-align:center; padding-top:40px;">
            <span class="sub-heading">Our Models</span>
            <h2 class="section-title">Choose Your Thunder</h2>
            <p class="section-sub">Find the perfect ride that matches your style and needs.</p>
        </div>
        <div class="products-grid">
            <!-- Product 1 -->
            <div class="product-card" data-name="ThunderX Sport" data-price="149999" data-image="uploads/bike_2.jpg">
                <img src="uploads/bike_2.jpg" alt="ThunderX Sport" loading="lazy">
                <div class="wishlist" onclick="toggleWishlist(this)"><i class="far fa-heart"></i></div>
                <div class="info">
                    <h3>ThunderX Sport</h3>
                    <div class="price">₹1,49,999 <small>₹1,99,999</small></div>
                    <p>Top speed 130 km/h, range 180 km, 0‑60 in 3.2s.</p>
                   <div class="btn-buy"> <a href="blog.php"><i class="fas fa-shopping-cart"></i> Buy Now</a></div>
                </div>
            </div>
            <!-- Product 2 -->
            <div class="product-card" data-name="ThunderX Cruiser" data-price="179999" data-image="https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=600&h=400&fit=crop&crop=center">
                <img src="https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=600&h=400&fit=crop&crop=center" alt="ThunderX Cruiser" loading="lazy">
                <div class="wishlist" onclick="toggleWishlist(this)"><i class="far fa-heart"></i></div>
                <div class="info">
                    <h3>ThunderX Cruiser</h3>
                    <div class="price">₹1,79,999 <small>₹2,29,999</small></div>
                    <p>Long‑range tourer, 200 km range, comfortable ergonomics.</p>
                <div class="btn-buy"> <a href="blog.php"><i class="fas fa-shopping-cart"></i> Buy Now</a></div>
                </div>
            </div>
            <!-- Product 3 -->
            <div class="product-card" data-name="ThunderX Urban" data-price="129999" data-image="uploads/1787227364_bike_3.jpg">
                <img src="uploads/1787227364_bike_3.jpg" alt="ThunderX Urban" loading="lazy">
                <div class="wishlist" onclick="toggleWishlist(this)"><i class="far fa-heart"></i></div>
                <div class="info">
                    <h3>ThunderX Urban</h3>
                    <div class="price">₹1,29,999 <small>₹1,59,999</small></div>
                    <p>Agile city commuter, lightweight, 140 km range.</p>
                 <div class="btn-buy"> <a href="blog.php"><i class="fas fa-shopping-cart"></i> Buy Now</a></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== TECHNOLOGY ===== -->
    <div class="container">
        <section class="tech-section">
            <div class="tech-left">
                <span class="sub-heading">Technology</span>
                <h2 class="section-title">Next‑Gen Engineering</h2>
                <p style="color:var(--text-secondary); margin-bottom:16px;">Our bikes are powered by advanced battery tech, AI‑driven performance tuning, and smart connectivity features that keep you ahead.</p>
                <div class="tech-list">
                    <div class="tech-item"><h3>⚡ High‑Performance Motor</h3><p>10kW hub motor with regenerative braking.</p></div>
                    <div class="tech-item"><h3>🔋 Smart Battery</h3><p>72V / 40Ah Li‑ion with fast charging (0‑80% in 2 hrs).</p></div>
                    <div class="tech-item"><h3>📱 Connected Dash</h3><p>7″ TFT display with navigation and ride analytics.</p></div>
                    <div class="tech-item"><h3>🛡️ Advanced Safety</h3><p>Cornering ABS, traction control, and hill‑hold assist.</p></div>
                </div>
            </div>
            <div class="tech-right">
                <img src="image/bike_op.png" alt="Technology" loading="lazy">
            </div>
        </section>
    </div>

    <!-- ===== TESTIMONIALS ===== -->
    <div class="container">
        <div style="text-align:center; padding-top:40px;">
            <span class="sub-heading">Testimonials</span>
            <h2 class="section-title">What Riders Say</h2>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p>"The ThunderX Sport is a beast! The acceleration is insane and the ride is so smooth. Best investment I've made."</p>
                <div class="author"><div class="avatar">AK</div><div><div class="name">Amit Kumar</div><div class="role">Verified Buyer</div></div></div>
            </div>
            <div class="testimonial-card">
                <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p>"I've been riding for 10 years and the ThunderX Cruiser is by far the most comfortable and reliable bike I've owned."</p>
                <div class="author"><div class="avatar">SR</div><div><div class="name">Sneha Reddy</div><div class="role">Daily Commuter</div></div></div>
            </div>
            <div class="testimonial-card">
                <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div>
                <p>"Excellent build quality and the smart dash is a game‑changer. Highly recommend ThunderX to anyone looking for an electric bike."</p>
                <div class="author"><div class="avatar">VM</div><div><div class="name">Vikram Malhotra</div><div class="role">Tech Enthusiast</div></div></div>
            </div>
        </div>
    </div>

    <!-- ===== FOOTER ===== -->
    <!-- ===== FOOTER ===== -->
    <footer class="container footer" id="contact">
        <div class="footer-grid">
            <div>
                <h3><i class="fas fa-motorcycle" style="color:var(--primary);"></i> ThunderX</h3>
                <p style="color:var(--text-secondary); font-size:14px; max-width:280px;">Premium electric motorcycles for the bold and adventurous. Ride with power, ride with style.</p>
            </div>
            <div>
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#models">Models</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div>
                <h3>Contact</h3>
                <ul>
                    <li><i class="fas fa-phone" style="color:var(--primary);"></i> +91 98765 43210</li>
                    <li><i class="fas fa-envelope" style="color:var(--primary);"></i> support@thunderx.com</li>
                    <li><i class="fas fa-map-marker-alt" style="color:var(--primary);"></i> Uttar Pradesh, India</li>
                </ul>
            </div>
            <div>
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2026 <span>ThunderX</span>. All Rights Reserved.
        </div>
    </footer>
    <!-- ==========================================
         PURCHASE MODAL
         ========================================== -->
    <div class="modal-overlay" id="purchaseModal">
        <div class="modal">
            <button class="close" onclick="closeModal()" aria-label="Close modal"><i class="fas fa-times"></i></button>
            <h2>Complete Your Order</h2>
            <p class="subtitle">Fill in your details and we'll get back to you shortly.</p>

            <div class="product-preview">
                <img id="modalProductImage" src="" alt="Product">
                <div class="preview-info">
                    <h4 id="modalProductName">ThunderX Sport</h4>
                    <p id="modalProductPrice">₹1,49,999</p>
                </div>
            </div>

            <form id="purchaseForm" onsubmit="submitOrder(event)">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" id="customerName" placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" id="customerEmail" placeholder="you@example.com" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="tel" id="customerPhone" placeholder="+91 98765 43210" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Delivery Address</label>
                    <textarea id="customerAddress" rows="2" placeholder="Enter your full address" required></textarea>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-credit-card"></i> Payment Method</label>
                    <select id="paymentMethod">
                        <option value="cod">Cash on Delivery</option>
                        <option value="card">Credit / Debit Card</option>
                        <option value="upi">UPI / Net Banking</option>
                        <option value="emi">EMI Financing</option>
                    </select>
                </div>

                <div class="order-summary">
                    <div class="row">
                        <span>Product</span>
                        <span id="summaryProduct">ThunderX Sport</span>
                    </div>
                    <div class="row">
                        <span>Price</span>
                        <span id="summaryPrice">₹1,49,999</span>
                    </div>
                    <div class="row">
                        <span>Delivery</span>
                        <span style="color:var(--success);">FREE</span>
                    </div>
                    <div class="row total">
                        <span>Total</span>
                        <span class="amount" id="summaryTotal">₹1,49,999</span>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-check-circle"></i> Place Order
                </button>
            </form>
        </div>
    </div>

    <!-- ===== TOAST ===== -->
    <div class="toast" id="toast"></div>

    <!-- ===== BACK TO TOP ===== -->
    <button class="back-to-top" id="backToTop" onclick="window.scrollTo({top:0, behavior:'smooth'})" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- ==========================================
         BOTTOM NAVBAR (Floating Pill)
         ========================================== -->
    <nav class="bottom-nav" id="bottomNav">
        <a href="#home" title="Home"><i class="fas fa-home"></i></a>
        <a href="admin_login.php" title="Profile"><i class="fas fa-user"></i></a>
        <button class="center-btn" onclick="toggleCenterBtn()" title="Menu"><i class="fas fa-xmark"></i></button>
        <a href="#models" title="Wishlist"><i class="fas fa-heart"></i></a>
        <a href="#contact" title="Notifications"><i class="fas fa-bell"></i></a>
    </nav>

    <!-- ==========================================
    JAVASCRIPT
    ========================================== -->
    <script>
        // ===== Mobile Menu =====
        const btn = document.getElementById("btn-1");
        const menu = document.getElementById("menu");
        btn.addEventListener("click", () => {
            menu.classList.toggle("active");
            const expanded = menu.classList.contains("active");
            btn.setAttribute("aria-expanded", expanded);
        });

        // ===== Navbar shadow on scroll =====
        window.addEventListener("scroll", function() {
            const navbar = document.getElementById("navbar");
            if (window.scrollY > 30) navbar.classList.add("scrolled");
            else navbar.classList.remove("scrolled");
            const btnTop = document.getElementById("backToTop");
            if (window.scrollY > 400) btnTop.classList.add("visible");
            else btnTop.classList.remove("visible");
        });

        // ===== Countdown =====
        function startCountdown() {
            let launchDate = localStorage.getItem("countdownEndTime");
            if (!launchDate) {
                launchDate = new Date().getTime() + (24 * 60 * 60 * 1000);
                localStorage.setItem("countdownEndTime", launchDate);
            }
            launchDate = parseInt(launchDate);
            setInterval(function() {
                const now = new Date().getTime();
                let distance = launchDate - now;
                if (distance <= 0) {
                    launchDate = new Date().getTime() + (24 * 60 * 60 * 1000);
                    localStorage.setItem("countdownEndTime", launchDate);
                    distance = launchDate - now;
                }
                const days = Math.floor(distance / (1000*60*60*24));
                const hours = Math.floor((distance % (1000*60*60*24)) / (1000*60*60));
                const minutes = Math.floor((distance % (1000*60*60)) / (1000*60));
                const seconds = Math.floor((distance % (1000*60)) / 1000);
                document.getElementById("days").innerHTML = days + "<small>Days</small>";
                document.getElementById("hours").innerHTML = hours + "<small>Hours</small>";
                document.getElementById("minutes").innerHTML = minutes + "<small>Minutes</small>";
                document.getElementById("seconds").innerHTML = seconds + "<small>Seconds</small>";
            }, 1000);
        }
        startCountdown();

        // ===== Wishlist =====
        function toggleWishlist(el) {
            const icon = el.querySelector('i');
            icon.classList.toggle('far');
            icon.classList.toggle('fas');
            if (icon.classList.contains('fas')) {
                el.style.color = '#ff2d55';
                el.style.borderColor = '#ff2d55';
                showToast('❤️ Added to wishlist');
            } else {
                el.style.color = '';
                el.style.borderColor = '';
                showToast('💔 Removed from wishlist');
            }
        }

        // ===== Toast =====
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.classList.add('show');
            clearTimeout(toast._timeout);
            toast._timeout = setTimeout(() => {
                toast.classList.remove('show');
            }, 3500);
        }

        // ===== Close mobile menu on link click =====
        document.querySelectorAll('#menu a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    menu.classList.remove('active');
                    btn.setAttribute('aria-expanded', 'false');
                }
            });
        });

        // ===== Center Bottom Nav Button (X) =====
        function toggleCenterBtn() {
            window.scrollTo({top: 0, behavior: 'smooth'});
            showToast('🚀 You clicked the center menu button!');
        }

        // ==========================================
        // MODAL LOGIC
        // ==========================================
        const modalOverlay = document.getElementById('purchaseModal');
        let currentProduct = { name: '', price: 0, image: '' };

        function openModal(buttonEl) {
            const card = buttonEl.closest('.product-card');
            if (!card) return;

            const name = card.getAttribute('data-name');
            const price = parseInt(card.getAttribute('data-price'), 10);
            const image = card.getAttribute('data-image') || card.querySelector('img').src;

            currentProduct = { name, price, image };

            // Fill product preview
            document.getElementById('modalProductImage').src = image;
            document.getElementById('modalProductName').textContent = name;
            document.getElementById('modalProductPrice').textContent = '₹' + price.toLocaleString('en-IN');

            // Fill order summary
            document.getElementById('summaryProduct').textContent = name;
            document.getElementById('summaryPrice').textContent = '₹' + price.toLocaleString('en-IN');
            document.getElementById('summaryTotal').textContent = '₹' + price.toLocaleString('en-IN');

            // Show modal
            modalOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modalOverlay.classList.remove('active');
            document.body.style.overflow = '';
            document.getElementById('purchaseForm').reset();
        }

        // Close modal when clicking outside
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) {
                closeModal();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modalOverlay.classList.contains('active')) {
                closeModal();
            }
        });

        // Submit order
        function submitOrder(event) {
            event.preventDefault();

            const name = document.getElementById('customerName').value.trim();
            const email = document.getElementById('customerEmail').value.trim();
            const phone = document.getElementById('customerPhone').value.trim();
            const address = document.getElementById('customerAddress').value.trim();
            const payment = document.getElementById('paymentMethod').value;

            if (!name || !email || !phone || !address) {
                showToast('⚠️ Please fill in all required fields.');
                return;
            }

            // Simulate order placement
            const orderId = 'TX' + Date.now().toString().slice(-8);
            showToast(`✅ Order ${orderId} placed successfully for ${currentProduct.name}!`);

            closeModal();

            // Optional: log order details
            console.log('Order Placed:', {
                orderId,
                product: currentProduct.name,
                price: currentProduct.price,
                customer: { name, email, phone, address },
                payment
            });
        }

        console.log('🏍️ ThunderX — Professional Bike Sales (Fully Responsive) Loaded');
        console.log('💡 Modal purchase system active.');
    </script>
</body>
</html>