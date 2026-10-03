<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | IncomeCoin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        .about {
            padding: 100px 8%;
            background: linear-gradient(135deg, #0f172a, #111827, #1e293b);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .about-container {
            max-width: 1300px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 70px;
            flex-wrap: wrap;
        }

        /* ===== ABOUT IMAGE ===== */
        .about-image {
            flex: 1;
            text-align: center;
            animation: float 3s ease-in-out infinite;
            cursor: pointer;
            position: relative;
        }

        .about-image .click-hint {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 13px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0;
            transition: 0.4s;
            border: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 10;
        }

        .about-image:hover .click-hint {
            opacity: 1;
            bottom: 30px;
        }

        @keyframes float {
            0%,
            100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-12px);
            }
        }

        .about-image img {
            width: 80%;
            max-width: 520px;
            border-radius: 25px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
            transition: 0.5s;
            border: 3px solid rgba(0, 208, 132, 0.2);
        }

        .about-image img:hover {
            transform: scale(1.04);
            border-color: #00d084;
            box-shadow: 0 20px 60px rgba(0, 208, 132, 0.2);
        }

        /* ===== ABOUT CONTENT ===== */
        .about-content {
            flex: 1;
        }

        .sub-title {
            color: #00d084;
            font-size: 16px;
            letter-spacing: 3px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .about-content h2 {
            font-size: 48px;
            margin: 20px 0;
            line-height: 1.2;
        }

        .about-content h2 span {
            color: #00d084;
        }

        .about-content p {
            color: #d1d5db;
            font-size: 17px;
            line-height: 1.8;
            margin-bottom: 35px;
        }

        .about-features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 18px;
            padding: 30px;
            text-align: center;
            transition: 0.4s;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            background: #00d084;
        }

        .feature-card h3 {
            font-size: 34px;
            margin-bottom: 10px;
        }

        .feature-card span {
            font-size: 15px;
        }

        .btn {
            display: inline-block;
            padding: 16px 40px;
            background: #00d084;
            color: #fff;
            text-decoration: none;
            border-radius: 50px;
            font-size: 17px;
            font-weight: 600;
            transition: 0.4s;
        }

        .btn:hover {
            background: #00b56d;
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0, 208, 132, 0.4);
        }

        /* ============================================
           IMAGE POPUP SLIDER
        ============================================ */
        .popup-slider {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.92);
            backdrop-filter: blur(20px);
            z-index: 99999;
            justify-content: center;
            align-items: center;
            padding: 30px;
            animation: popupFade 0.4s ease;
        }

        .popup-slider.active {
            display: flex;
        }

        @keyframes popupFade {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .popup-slider .popup-content {
            position: relative;
            max-width: 90%;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .popup-slider .popup-content img {
            max-width: 100%;
            max-height: 80vh;
            border-radius: 16px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.8);
            border: 4px solid rgba(0, 208, 132, 0.3);
            object-fit: contain;
            animation: imageZoom 0.5s ease;
        }

        @keyframes imageZoom {
            from {
                opacity: 0;
                transform: scale(0.7);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* ===== Close Button ===== */
        .popup-slider .close-btn {
            position: absolute;
            top: -70px;
            right: -10px;
            background: rgba(255, 0, 0, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            font-size: 28px;
            cursor: pointer;
            transition: 0.4s;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            backdrop-filter: blur(10px);
            z-index: 10;
        }

        .popup-slider .close-btn:hover {
            transform: rotate(90deg);
            background: rgba(255, 0, 0, 0.5);
            border-color: #ff6b6b;
        }

        /* ===== Navigation Buttons ===== */
        .popup-slider .nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 28px;
            padding: 18px 24px;
            border-radius: 50%;
            cursor: pointer;
            transition: 0.4s;
            backdrop-filter: blur(10px);
            z-index: 10;
        }

        .popup-slider .nav-btn:hover {
            background: rgba(0, 208, 132, 0.3);
            border-color: #00d084;
            transform: translateY(-50%) scale(1.1);
        }

        .popup-slider .nav-btn.prev {
            left: -80px;
        }

        .popup-slider .nav-btn.next {
            right: -80px;
        }

        /* ===== Counter ===== */
        .popup-slider .counter {
            position: absolute;
            top: -70px;
            left: 0;
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            font-weight: 300;
            background: rgba(0, 0, 0, 0.4);
            padding: 8px 20px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            z-index: 10;
        }

        /* ===== Image Caption ===== */
        .popup-slider .caption {
            color: #fff;
            text-align: center;
            margin-top: 18px;
            background: rgba(0, 0, 0, 0.5);
            padding: 12px 30px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            font-size: 16px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            width: 100%;
            max-width: 600px;
        }

        .popup-slider .caption i {
            color: #00d084;
            margin-right: 8px;
        }

        /* ===== Progress Bar ===== */
        .popup-slider .progress-bar {
            width: 100%;
            max-width: 600px;
            height: 3px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            margin-top: 15px;
            overflow: hidden;
            position: relative;
        }

        .popup-slider .progress-bar .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #00d084, #00b56d);
            border-radius: 10px;
            width: 0%;
            transition: width 0.1s linear;
            box-shadow: 0 0 20px rgba(0, 208, 132, 0.3);
        }

        /* ===== Dots Indicator ===== */
        .popup-slider .dots {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            justify-content: center;
        }

        .popup-slider .dots .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transition: 0.4s;
            cursor: pointer;
            border: none;
        }

        .popup-slider .dots .dot.active {
            background: #00d084;
            transform: scale(1.3);
            box-shadow: 0 0 20px rgba(0, 208, 132, 0.4);
        }

        .popup-slider .dots .dot:hover {
            background: rgba(0, 208, 132, 0.5);
        }

        /* ============================================
           RESPONSIVE
        ============================================ */
        @media (max-width: 991px) {
            .about-container {
                flex-direction: column;
                text-align: center;
            }

            .about-content h2 {
                font-size: 38px;
            }

            .popup-slider .nav-btn.prev {
                left: 5px;
            }

            .popup-slider .nav-btn.next {
                right: 5px;
            }

            .popup-slider .nav-btn {
                padding: 12px 16px;
                font-size: 20px;
            }

            .popup-slider .close-btn {
                top: -55px;
                right: 0;
                width: 40px;
                height: 40px;
                font-size: 20px;
            }

            .popup-slider .counter {
                top: -55px;
                font-size: 12px;
                padding: 5px 14px;
            }

            .popup-slider .caption {
                font-size: 14px;
                padding: 10px 18px;
            }
        }

        @media (max-width: 576px) {
            .about {
                padding: 70px 20px;
            }

            .about-content h2 {
                font-size: 30px;
            }

            .about-features {
                grid-template-columns: 1fr;
            }

            .feature-card {
                padding: 22px;
            }

            .btn {
                width: 100%;
                text-align: center;
            }

            .about-image img {
                width: 80%;
                max-width: 320px;
            }

            .popup-slider .popup-content img {
                max-height: 55vh;
            }

            .popup-slider {
                padding: 15px;
            }

            .popup-slider .nav-btn {
                padding: 10px 14px;
                font-size: 16px;
            }

            .popup-slider .close-btn {
                top: -45px;
                width: 36px;
                height: 36px;
                font-size: 16px;
            }

            .popup-slider .counter {
                top: -45px;
                font-size: 11px;
                padding: 4px 12px;
            }

            .popup-slider .caption {
                font-size: 13px;
                padding: 8px 14px;
                margin-top: 12px;
            }

            .popup-slider .dots .dot {
                width: 8px;
                height: 8px;
            }
        }

        /* ============================================
           SCROLLBAR
        ============================================ */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #0f172a;
        }

        ::-webkit-scrollbar-thumb {
            background: #00d084;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #00b56d;
        }
    </style>
</head>
<body>

    <!-- ============================================
    ABOUT SECTION
    ============================================ -->
    <section class="about" id="about">

        <div class="about-container">

            <!-- Image with Click Hint -->
            <div class="about-image" onclick="openSlider(0)">
                <img src="image/about-image-1-metal.jpg" alt="About IncomeCoin">
                <div class="click-hint">
                    <i class="fas fa-expand"></i> Click to View Gallery
                </div>
            </div>

            <div class="about-content">

                <span class="sub-title">ABOUT US</span>

                <h2>Build Your <span>Financial Future</span> With IncomeCoin</h2>

                <p>
                    IncomeCoin is a trusted investment platform focused on helping
                    people build long-term wealth through secure, transparent,
                    and profitable investment opportunities.
                </p>

                <div class="about-features">

                    <div class="feature-card">
                        <h3>100%</h3>
                        <span>Secure Platform</span>
                    </div>

                    <div class="feature-card">
                        <h3>24/7</h3>
                        <span>Live Support</span>
                    </div>

                    <div class="feature-card">
                        <h3>10K+</h3>
                        <span>Happy Investors</span>
                    </div>

                    <div class="feature-card">
                        <h3>99%</h3>
                        <span>Success Rate</span>
                    </div>

                </div>

                <a href="#contact" class="btn">Start Investing</a>

            </div>

        </div>

    </section>

    <!-- ============================================
    IMAGE POPUP SLIDER WITH AUTO-SLIDE
    ============================================ -->
    <div class="popup-slider" id="popupSlider" onclick="closeSlider(event)">
        <div class="popup-content" onclick="event.stopPropagation();">

            <!-- Close Button -->
            <button class="close-btn" onclick="closeSlider()">
                <i class="fas fa-times"></i>
            </button>

            <!-- Counter -->
            <div class="counter" id="counter">1 / 4</div>

            <!-- Previous Button -->
            <button class="nav-btn prev" onclick="changeSlide(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>

            <!-- Image -->
            <img id="sliderImage" src="" alt="Gallery Image">

            <!-- Next Button -->
            <button class="nav-btn next" onclick="changeSlide(1)">
                <i class="fas fa-chevron-right"></i>
            </button>

            <!-- Caption -->
            <div class="caption" id="caption">
                <i class="fas fa-image"></i> <span id="captionText">About Image 1</span>
            </div>

            <!-- Progress Bar -->
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>

            <!-- Dots Indicator -->
            <div class="dots" id="dotsContainer"></div>

        </div>
    </div>

    <!-- ============================================
    JAVASCRIPT
    ============================================ -->
    <script>
        // ===== Image Gallery Data =====
        const galleryImages = [
            {
                src: 'image/about-image-1-metal.jpg',
                caption: '🏢 About IncomeCoin - Our Story'
            },
            {
                src: 'image/about-image-2.jpg',
                caption: '💰 Build Your Financial Future'
            },
            {
                src: 'image/about-image-3.jpg',
                caption: '📈 Trusted Investment Platform'
            },
            {
                src: 'image/about-image-4.jpg',
                caption: '🌟 10K+ Happy Investors'
            }
        ];

        let currentIndex = 0;
        let autoSlideTimer = null;
        const SLIDE_INTERVAL = 3000; // 3 seconds

        // ===== Initialize Dots =====
        function initDots() {
            const container = document.getElementById('dotsContainer');
            container.innerHTML = '';
            galleryImages.forEach((_, index) => {
                const dot = document.createElement('button');
                dot.className = 'dot' + (index === 0 ? ' active' : '');
                dot.onclick = () => goToSlide(index);
                container.appendChild(dot);
            });
        }

        // ===== Go to Specific Slide =====
        function goToSlide(index) {
            currentIndex = index;
            updateSlider(currentIndex);
            resetAutoSlide();
        }

        // ===== Open Slider =====
        function openSlider(index) {
            currentIndex = index;
            updateSlider(currentIndex);
            document.getElementById('popupSlider').classList.add('active');
            document.body.style.overflow = 'hidden';
            startAutoSlide();
        }

        // ===== Close Slider =====
        function closeSlider(e) {
            if (e && e.target !== e.currentTarget) return;
            document.getElementById('popupSlider').classList.remove('active');
            document.body.style.overflow = 'auto';
            stopAutoSlide();
        }

        // ===== Change Slide =====
        function changeSlide(direction) {
            currentIndex += direction;
            if (currentIndex < 0) currentIndex = galleryImages.length - 1;
            if (currentIndex >= galleryImages.length) currentIndex = 0;
            updateSlider(currentIndex);
            resetAutoSlide();
        }

        // ===== Update Slider =====
        function updateSlider(index) {
            const image = galleryImages[index];
            if (!image) return;

            document.getElementById('sliderImage').src = image.src;
            document.getElementById('sliderImage').alt = image.caption;
            document.getElementById('captionText').textContent = image.caption;
            document.getElementById('counter').textContent = (index + 1) + ' / ' + galleryImages.length;

            // Update dots
            const dots = document.querySelectorAll('.dot');
            dots.forEach((dot, i) => {
                dot.className = 'dot' + (i === index ? ' active' : '');
            });

            // Reset progress
            document.getElementById('progressFill').style.width = '0%';

            // Re-trigger animation
            const img = document.getElementById('sliderImage');
            img.style.animation = 'none';
            setTimeout(() => {
                img.style.animation = 'imageZoom 0.5s ease';
            }, 10);
        }

        // ===== Auto Slide Functions =====
        function startAutoSlide() {
            stopAutoSlide();
            let progress = 0;
            const fill = document.getElementById('progressFill');
            
            autoSlideTimer = setInterval(() => {
                changeSlide(1);
            }, SLIDE_INTERVAL);

            // Progress bar animation
            let progressInterval = setInterval(() => {
                if (document.getElementById('popupSlider').classList.contains('active')) {
                    progress += 1;
                    if (progress <= 100) {
                        fill.style.width = progress + '%';
                    }
                }
            }, SLIDE_INTERVAL / 100);

            // Store interval for cleanup
            autoSlideTimer._progress = progressInterval;
        }

        function stopAutoSlide() {
            if (autoSlideTimer) {
                clearInterval(autoSlideTimer);
                if (autoSlideTimer._progress) {
                    clearInterval(autoSlideTimer._progress);
                }
                autoSlideTimer = null;
            }
        }

        function resetAutoSlide() {
            if (document.getElementById('popupSlider').classList.contains('active')) {
                stopAutoSlide();
                startAutoSlide();
            }
        }

        // ===== Keyboard Navigation =====
        document.addEventListener('keydown', function(e) {
            const slider = document.getElementById('popupSlider');
            if (slider.classList.contains('active')) {
                if (e.key === 'Escape') {
                    closeSlider();
                    e.preventDefault();
                }
                if (e.key === 'ArrowLeft') {
                    changeSlide(-1);
                    e.preventDefault();
                }
                if (e.key === 'ArrowRight') {
                    changeSlide(1);
                    e.preventDefault();
                }
            }
        });

        // ===== Touch Support for Mobile Swipe =====
        let touchStartX = 0;
        let touchEndX = 0;

        document.getElementById('popupSlider').addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });

        document.getElementById('popupSlider').addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            const diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) {
                    changeSlide(1);
                } else {
                    changeSlide(-1);
                }
            }
        });

        // ===== Mouse hover pause auto-slide =====
        document.getElementById('popupSlider').addEventListener('mouseenter', function() {
            if (this.classList.contains('active')) {
                stopAutoSlide();
            }
        });

        document.getElementById('popupSlider').addEventListener('mouseleave', function() {
            if (this.classList.contains('active')) {
                startAutoSlide();
            }
        });

        // ===== Initialize =====
        initDots();

        console.log('📸 Image Gallery with Auto-Slide Loaded!');
        console.log('📌 Click on image to open slider');
        console.log('⏱️ Auto-slide every 3 seconds');
        console.log('⌨️ Use Arrow keys or swipe to navigate');
        console.log('🖱️ Hover to pause auto-slide');
    </script>

</body>
</html>