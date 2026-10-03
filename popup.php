<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes" />
    <meta name="theme-color" content="#050506" />
    <title>ThunderBike — Premium Motorcycle Popup</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Bebas+Neue&display=swap" rel="stylesheet" />

    <style>
        /* ============================================
           THUNDERBIKE — PROFESSIONAL POPUP
           Palette: Crimson Noir + Amber Gold
           ============================================ */
        :root {
            /* ===== BRAND — CRIMSON ===== */
            --crimson-700: #8B0F2E;
            --crimson-600: #B91A3D;
            --crimson-500: #DC2449;
            --crimson-400: #E84866;
            --crimson-300: #F06B84;
            --crimson-glow: rgba(220, 36, 73, 0.35);

            /* ===== BRAND — AMBER ===== */
            --amber-700: #A66908;
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
            --shadow-xl: 0 40px 100px rgba(0, 0, 0, 0.9);
            --shadow-crimson: 0 8px 32px rgba(220, 36, 73, 0.28);
            --shadow-crimson-lg: 0 16px 48px rgba(220, 36, 73, 0.45);

            /* ===== RADIUS ===== */
            --radius-xs: 8px;
            --radius-sm: 10px;
            --radius: 16px;
            --radius-md: 20px;
            --radius-lg: 24px;
            --radius-xl: 32px;
            --radius-full: 9999px;

            /* ===== EASING ===== */
            --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
            --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
            --transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html {
            -webkit-text-size-adjust: 100%;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: var(--noir-950);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11';
            overflow-x: hidden;
        }

        /* ==============================
           POPUP OVERLAY
           ============================== */
        .popup {
            position: fixed;
            inset: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 16px;
            background: rgba(5, 5, 6, 0.88);
            backdrop-filter: blur(28px) saturate(140%);
            -webkit-backdrop-filter: blur(28px) saturate(140%);
            z-index: 99999;
            animation: fadeIn 0.4s var(--ease-out);
            overflow-y: auto;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* ==============================
           POPUP BOX
           ============================== */
        .popup-box {
            position: relative;
            width: 100%;
            max-width: 480px;
            background: linear-gradient(145deg, #0D0D13 0%, #16161E 50%, #0A0A0F 100%);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow:
                0 0 0 1px rgba(220, 36, 73, 0.08),
                0 40px 100px rgba(0, 0, 0, 0.9),
                0 0 100px rgba(220, 36, 73, 0.08);
            animation: popupOpen 0.7s var(--ease-spring);
            max-height: 96vh;
            overflow-y: auto;
            isolation: isolate;
        }

        .popup-box::-webkit-scrollbar {
            width: 4px;
        }
        .popup-box::-webkit-scrollbar-track {
            background: transparent;
        }
        .popup-box::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--crimson-500), var(--crimson-700));
            border-radius: 10px;
        }

        @keyframes popupOpen {
            from {
                opacity: 0;
                transform: translateY(60px) scale(0.92);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Gold top border */
        .popup-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg,
                transparent 0%,
                var(--crimson-700) 15%,
                var(--crimson-500) 30%,
                var(--amber-500) 50%,
                var(--crimson-500) 70%,
                var(--crimson-700) 85%,
                transparent 100%);
            background-size: 200% 100%;
            animation: goldFlow 5s linear infinite;
            z-index: 10;
        }

        @keyframes goldFlow {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }

        /* ==============================
           IMAGE SECTION
           ============================== */
        .popup-img-box {
            position: relative;
            width: 100%;
            height: 320px;
            overflow: hidden;
            background: linear-gradient(135deg, #0B0B10, #1A1A24);
            flex-shrink: 0;
        }

        .popup-img-box::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 30%, rgba(220, 36, 73, 0.15) 0%, transparent 60%),
                radial-gradient(circle at 80% 70%, rgba(232, 155, 31, 0.1) 0%, transparent 60%);
            z-index: 1;
            pointer-events: none;
        }

        .popup-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 1s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 0;
            filter: brightness(0.95) contrast(1.05);
        }

        .popup-box:hover .popup-img {
            transform: scale(1.06);
        }

        /* Bottom fade */
        .popup-img-box::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 60%;
            background: linear-gradient(to top,
                rgba(13, 13, 19, 0.95) 0%,
                rgba(13, 13, 19, 0.6) 40%,
                transparent 100%);
            pointer-events: none;
            z-index: 2;
        }

        /* ==============================
           BADGE
           ============================== */
        .popup-badge-img {
            position: absolute;
            top: 20px;
            left: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 20px;
            border-radius: var(--radius-full);
            background: linear-gradient(135deg, var(--crimson-500), var(--crimson-700));
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.8px;
            box-shadow:
                0 8px 24px rgba(220, 36, 73, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.15) inset;
            z-index: 5;
            animation: pulseBadge 3s ease-in-out infinite;
            backdrop-filter: blur(8px);
        }

        @keyframes pulseBadge {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 8px 24px rgba(220, 36, 73, 0.4), 0 0 0 1px rgba(255,255,255,0.15) inset;
            }
            50% {
                transform: scale(1.04);
                box-shadow: 0 12px 36px rgba(220, 36, 73, 0.6), 0 0 0 1px rgba(255,255,255,0.2) inset;
            }
        }

        .popup-badge-img i {
            font-size: 13px;
            filter: drop-shadow(0 0 6px rgba(255, 255, 255, 0.5));
        }

        /* Floating Bike Icon */
        .float-bike {
            position: absolute;
            bottom: 30px;
            right: 25px;
            font-size: 44px;
            color: rgba(232, 155, 31, 0.15);
            z-index: 3;
            animation: floatBike 6s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(232, 155, 31, 0.3));
        }

        @keyframes floatBike {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.15;
            }
            50% {
                transform: translateY(-15px) rotate(8deg);
                opacity: 0.25;
            }
        }

        /* ==============================
           CLOSE BUTTON
           ============================== */
        .close {
            position: absolute;
            top: 18px;
            right: 18px;
            width: 44px;
            height: 44px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            font-size: 20px;
            color: var(--text-secondary);
            cursor: pointer;
            z-index: 20;
            transition: all 0.4s var(--ease-spring);
        }

        .close:hover {
            background: var(--danger);
            color: #ffffff;
            transform: rotate(90deg) scale(1.08);
            border-color: var(--danger);
            box-shadow: 0 8px 32px rgba(239, 68, 68, 0.5);
        }

        .close:active {
            transform: rotate(90deg) scale(0.95);
        }

        .close i {
            font-size: 18px;
            line-height: 1;
        }

        /* ==============================
           CONTENT
           ============================== */
        .popup-content {
            padding: 32px 36px 38px;
            text-align: center;
            background: transparent;
            position: relative;
            z-index: 3;
        }

        /* Icon */
        .popup-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: linear-gradient(135deg,
                rgba(220, 36, 73, 0.15),
                rgba(220, 36, 73, 0.04));
            border: 1.5px solid rgba(220, 36, 73, 0.2);
            font-size: 34px;
            color: var(--crimson-400);
            margin-bottom: 18px;
            transition: all 0.5s var(--ease-spring);
            position: relative;
        }

        .popup-icon::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(220, 36, 73, 0.2), transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: -1;
        }

        .popup-box:hover .popup-icon {
            transform: translateY(-4px) scale(1.06);
            border-color: rgba(220, 36, 73, 0.4);
            box-shadow: 0 12px 40px rgba(220, 36, 73, 0.2);
        }

        .popup-box:hover .popup-icon::before {
            opacity: 1;
        }

        /* Badge Text */
        .popup-badge-text {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 14px;
            padding: 6px 18px;
            border-radius: var(--radius-full);
            background: linear-gradient(135deg,
                rgba(232, 155, 31, 0.15),
                rgba(232, 155, 31, 0.08));
            border: 1px solid rgba(232, 155, 31, 0.25);
            color: var(--amber-400);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .popup-badge-text i {
            font-size: 12px;
            filter: drop-shadow(0 0 4px rgba(232, 155, 31, 0.6));
        }

        /* Heading */
        .popup-box h2 {
            margin-bottom: 14px;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 42px;
            line-height: 0.95;
            font-weight: 400;
            color: #ffffff;
            letter-spacing: 1.5px;
        }

        .popup-box h2 .highlight {
            background: linear-gradient(135deg, var(--crimson-400), var(--crimson-600));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 0 20px var(--crimson-glow));
        }

        .popup-box h2 .bike-symbol {
            display: inline-block;
            color: var(--amber-400);
            -webkit-text-fill-color: var(--amber-400);
            filter: drop-shadow(0 0 12px rgba(232, 155, 31, 0.5));
            margin-right: 4px;
        }

        /* Paragraph */
        .popup-box p {
            max-width: 380px;
            margin: 0 auto 26px;
            color: var(--text-secondary);
            font-size: 15px;
            line-height: 1.75;
            font-weight: 400;
        }

        .popup-box p strong {
            color: var(--amber-400);
            font-weight: 700;
        }

        /* ==============================
           CTA BUTTON
           ============================== */
        .btt {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 18px 32px;
            background: linear-gradient(135deg, var(--crimson-500), var(--crimson-700));
            color: #ffffff;
            text-decoration: none;
            border: none;
            border-radius: var(--radius-full);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow:
                0 8px 32px rgba(220, 36, 73, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.2),
                inset 0 -1px 0 rgba(139, 15, 46, 0.4);
            transition: all 0.4s var(--ease-spring);
            position: relative;
            overflow: hidden;
        }

        /* Shine sweep */
        .btt::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(120deg,
                transparent,
                rgba(255, 255, 255, 0.35),
                transparent);
            transform: skewX(-20deg);
            transition: left 0.7s ease;
        }

        .btt:hover::before {
            left: 150%;
        }

        .btt:hover {
            transform: translateY(-4px) scale(1.01);
            box-shadow:
                0 16px 48px rgba(220, 36, 73, 0.55),
                inset 0 1px 0 rgba(255, 255, 255, 0.3),
                inset 0 -1px 0 rgba(139, 15, 46, 0.5);
        }

        .btt:active {
            transform: translateY(-1px) scale(0.98);
        }

        .btt i {
            font-size: 15px;
            transition: transform 0.3s ease;
            position: relative;
            z-index: 1;
        }

        .btt span {
            position: relative;
            z-index: 1;
        }

        .btt:hover i:last-child {
            transform: translateX(4px);
        }

        .btt:hover i:first-child {
            transform: scale(1.15);
        }

        /* ==============================
           SECONDARY LINK
           ============================== */
        .popup-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
            color: var(--text-muted);
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: var(--radius-full);
        }

        .popup-link:hover {
            color: var(--crimson-400);
            background: rgba(220, 36, 73, 0.08);
        }

        .popup-link i {
            font-size: 12px;
        }

        /* ==============================
           TRUST BADGES
           ============================== */
        .trust {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 24px;
            padding-top: 22px;
            border-top: 1px solid var(--border-color);
            flex-wrap: wrap;
        }

        .trust span {
            display: flex;
            align-items: center;
            gap: 7px;
            color: var(--text-muted);
            font-size: 11.5px;
            font-weight: 600;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        .trust span i {
            color: var(--amber-500);
            font-size: 13px;
            filter: drop-shadow(0 0 6px rgba(232, 155, 31, 0.4));
        }

        /* ============================================
           RESPONSIVE — MOBILE FIRST
           ============================================ */

        /* Tablet */
        @media (max-width: 768px) {
            .popup-box {
                max-width: 440px;
                border-radius: var(--radius-lg);
            }

            .popup-img-box {
                height: 260px;
            }

            .popup-content {
                padding: 26px 26px 30px;
            }

            .popup-box h2 {
                font-size: 36px;
            }

            .popup-icon {
                width: 66px;
                height: 66px;
                font-size: 30px;
            }

            .float-bike {
                font-size: 36px;
                bottom: 20px;
                right: 18px;
            }
        }

        /* Mobile */
        @media (max-width: 500px) {
            .popup {
                padding: 12px;
                align-items: flex-end;
            }

            .popup-box {
                max-width: 100%;
                border-radius: var(--radius-lg) var(--radius-lg) 0 0;
                max-height: 94vh;
                animation: slideUpMobile 0.5s var(--ease-spring);
            }

            @keyframes slideUpMobile {
                from {
                    opacity: 0;
                    transform: translateY(100%);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .popup-box::before {
                border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            }

            .popup-img-box {
                height: 220px;
            }

            .popup-content {
                padding: 22px 20px 26px;
            }

            .popup-box h2 {
                font-size: 32px;
                margin-bottom: 10px;
            }

            .popup-box p {
                font-size: 14px;
                margin-bottom: 20px;
                max-width: 100%;
                line-height: 1.65;
            }

            .popup-icon {
                width: 58px;
                height: 58px;
                font-size: 26px;
                margin-bottom: 14px;
            }

            .close {
                width: 38px;
                height: 38px;
                top: 14px;
                right: 14px;
                font-size: 16px;
            }

            .close i {
                font-size: 15px;
            }

            .popup-badge-img {
                top: 14px;
                left: 14px;
                font-size: 10px;
                padding: 6px 14px;
                letter-spacing: 1.2px;
            }

            .popup-badge-img i {
                font-size: 11px;
            }

            .popup-badge-text {
                font-size: 10px;
                padding: 5px 14px;
                letter-spacing: 1.2px;
                margin-bottom: 10px;
            }

            .btt {
                font-size: 13px;
                padding: 15px 24px;
                border-radius: var(--radius-full);
                letter-spacing: 1px;
            }

            .btt i {
                font-size: 14px;
            }

            .popup-link {
                font-size: 12.5px;
                margin-top: 12px;
            }

            .trust {
                gap: 14px;
                margin-top: 18px;
                padding-top: 16px;
            }

            .trust span {
                font-size: 11px;
                gap: 5px;
            }

            .trust span i {
                font-size: 12px;
            }

            .float-bike {
                font-size: 28px;
                bottom: 16px;
                right: 14px;
            }
        }

        /* Small Mobile */
        @media (max-width: 380px) {
            .popup-img-box {
                height: 180px;
            }

            .popup-content {
                padding: 18px 16px 22px;
            }

            .popup-box h2 {
                font-size: 28px;
                letter-spacing: 1px;
            }

            .popup-box p {
                font-size: 13px;
                line-height: 1.6;
                margin-bottom: 18px;
            }

            .popup-icon {
                width: 50px;
                height: 50px;
                font-size: 22px;
                margin-bottom: 12px;
            }

            .close {
                width: 32px;
                height: 32px;
                top: 10px;
                right: 10px;
            }

            .close i {
                font-size: 13px;
            }

            .btt {
                font-size: 12.5px;
                padding: 13px 20px;
                gap: 8px;
            }

            .btt i {
                font-size: 13px;
            }

            .popup-badge-img {
                font-size: 9px;
                padding: 5px 12px;
                top: 10px;
                left: 10px;
                letter-spacing: 1px;
            }

            .popup-badge-img i {
                font-size: 10px;
            }

            .popup-badge-text {
                font-size: 9px;
                padding: 4px 12px;
            }

            .float-bike {
                font-size: 22px;
                bottom: 12px;
                right: 10px;
            }

            .trust {
                gap: 10px;
                margin-top: 14px;
                padding-top: 14px;
            }

            .trust span {
                font-size: 10px;
            }

            .trust span i {
                font-size: 11px;
            }
        }

        /* Landscape Mobile */
        @media (max-height: 600px) and (orientation: landscape) {
            .popup {
                padding: 12px;
                align-items: center;
            }

            .popup-box {
                max-height: 94vh;
                display: flex;
                flex-direction: row;
                max-width: 780px;
                border-radius: var(--radius-lg);
            }

            .popup-img-box {
                width: 45%;
                height: 100%;
                min-height: 340px;
                flex-shrink: 0;
            }

            .popup-content {
                width: 55%;
                padding: 24px 26px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                text-align: left;
            }

            .popup-box h2 {
                font-size: 28px;
                text-align: left;
            }

            .popup-box p {
                font-size: 13px;
                text-align: left;
                margin-left: 0;
                margin-right: 0;
                max-width: 100%;
            }

            .popup-icon {
                width: 48px;
                height: 48px;
                font-size: 22px;
                margin-bottom: 10px;
            }

            .popup-badge-text {
                margin-bottom: 8px;
            }

            .btt {
                padding: 12px 20px;
                font-size: 13px;
            }

            .trust {
                justify-content: flex-start;
                margin-top: 14px;
                padding-top: 12px;
                gap: 12px;
            }

            .float-bike {
                font-size: 28px;
                bottom: 14px;
                right: 14px;
            }
        }

        /* Safe Area for iPhone Notch */
        @supports (padding: max(0px)) {
            .popup {
                padding: max(16px, env(safe-area-inset-top)) max(16px, env(safe-area-inset-right)) max(16px, env(safe-area-inset-bottom)) max(16px, env(safe-area-inset-left));
            }
        }

        /* ==============================
           ACCESSIBILITY
           ============================== */
        *:focus-visible {
            outline: 2px solid var(--crimson-500);
            outline-offset: 3px;
            border-radius: var(--radius-sm);
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }

            .popup-box,
            .popup {
                animation: none !important;
            }

            .close:hover {
                transform: none !important;
            }

            .btt:hover {
                transform: none !important;
            }

            .btt::before {
                display: none;
            }

            .popup-img {
                transform: none !important;
            }

            .popup-badge-img {
                animation: none !important;
            }

            .float-bike {
                animation: none !important;
            }
        }
    </style>
</head>

<body>

    <!-- ==============================
         MOTORCYCLE PREMIUM POPUP
         ============================== -->
    <div id="popup" class="popup" role="dialog" aria-modal="true" aria-labelledby="popup-title">

        <div class="popup-box">

            <!-- Close Button -->
            <button class="close" onclick="closePopup()" aria-label="Close popup">
                <i class="fas fa-xmark"></i>
            </button>

            <!-- Image Section -->
            <div class="popup-img-box">

                <img src="https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=800&auto=format&fit=crop"
                     alt="ThunderBike Motorcycle"
                     class="popup-img"
                     loading="eager" />

                <!-- Badge -->
                <span class="popup-badge-img">
                    <i class="fas fa-bolt"></i> ThunderBike
                </span>

                <!-- Floating Bike Icon -->
                <div class="float-bike">
                    <i class="fas fa-motorcycle"></i>
                </div>

            </div>

            <!-- Content -->
            <div class="popup-content">

                <!-- Icon -->
                <div class="popup-icon">
                    <i class="fas fa-motorcycle"></i>
                </div>

                <!-- Badge -->
                <div class="popup-badge-text">
                    <i class="fas fa-fire"></i> Limited Edition
                </div>

                <!-- Heading -->
                <h2 id="popup-title">
                    <span class="bike-symbol">⚡</span> Unleash the <br />
                    <span class="highlight">Beast</span>
                </h2>

                <!-- Description -->
                <p>
                    Feel the thrill of pure power. Join <strong>10,000+</strong> riders
                    who choose ThunderBike for uncompromising performance &amp; style.
                </p>

                <!-- CTA Button -->
                <a href="#" class="btt">
                    <i class="fas fa-bolt"></i>
                    <span>Book a Test Ride</span>
                    <i class="fas fa-arrow-right"></i>
                </a>

                <!-- Secondary Link -->
                <button class="popup-link" onclick="closePopup()">
                    <i class="fas fa-clock"></i> Maybe later
                </button>

                <!-- Trust Badges -->
                <div class="trust">
                    <span><i class="fas fa-shield-halved"></i> Premium Build</span>
                    <span><i class="fas fa-user-group"></i> 10K+ Riders</span>
                    <span><i class="fas fa-star"></i> 4.9 Rated</span>
                </div>

            </div>

        </div>

    </div>

    <!-- ==============================
         JAVASCRIPT
         ============================== -->
    <script>
        function closePopup() {
            const popup = document.getElementById("popup");
            popup.style.opacity = "0";
            popup.style.transition = "opacity 0.3s ease";

            setTimeout(function () {
                popup.style.display = "none";
            }, 300);
        }

        /* Close when clicking outside popup */
        document.getElementById("popup").addEventListener("click", function (event) {
            if (event.target === this) {
                closePopup();
            }
        });

        /* Close with ESC key */
        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                closePopup();
            }
        });
    </script>

</body>
</html>