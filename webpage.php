<?php
// webpage.php

// Load environment variables (for local development)
// Only load if file exists (graceful fallback for production)
if (file_exists(__DIR__ . '/env-loader.php')) {
    require_once __DIR__ . '/env-loader.php';
}

// Get hero video URL from environment variable, fallback to local file
$heroVideoUrl = getenv('HERO_VIDEO_URL');
if (empty($heroVideoUrl)) {
    // Fallback to local video for development
    $heroVideoUrl = 'Video/quality_restoration_20251105174029661.mp4';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D'MARSIANS TAEKWONDO GYM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Styles/webpage.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Updated typography: Orbitron/Teko for headings, Montserrat for body -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;700;800&family=Teko:wght@600;700&family=Montserrat:wght@400;500;600;700&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    /* Mobile top navigation customization */
    @media (max-width: 767.98px) {
        .mobile-topnav { background-color: #202020 !important; }
        .mobile-topnav .nav-link, .mobile-topnav .navbar-brand { transition: color .2s ease; }
        .mobile-topnav .nav-link:hover, .mobile-topnav .navbar-brand:hover { color: #00ff00 !important; }
        .mobile-topnav .navbar-toggler { border-color: #00ff00; }
        .mobile-topnav .navbar-toggler:hover, .mobile-topnav .navbar-toggler:focus { box-shadow: 0 0 0 .125rem rgba(0, 255, 0, .5); }
    }

    /* Post details modal */
    .postmodal-overlay { position: fixed; inset: 0; display: none; align-items: center; justify-content: center; background: transparent; z-index: 1050; }
    .postmodal-overlay.open { display: flex; }
    .postmodal-dialog { position: relative; max-width: 1000px; width: min(92vw, 1000px); max-height: 90vh; background: #fff; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,.15); overflow: hidden; }
    .postmodal-close { position: absolute; top: 8px; right: 12px; background: none; border: 0; font-size: 2rem; line-height: 1; color: #222; cursor: pointer; }
    .postmodal-body { display: grid; grid-template-columns: clamp(280px, 38vw, 420px) 1fr; align-items: stretch; gap: 0; height: 100%; }
    .postmodal-image { background: #f2f2f2; display: flex; align-items: center; justify-content: center; height: 100%; min-height: 280px; }
    .postmodal-image img { width: 100%; height: 100%; object-fit: contain; }
    .postmodal-content { padding: 20px 24px; overflow-y: auto; overflow-x: hidden; color: #111; min-width: 0; }
    .postmodal-content h3 { margin: 0 0 .25rem; color: #111; text-align: center; }
    .postmodal-meta { margin: 0 0 1rem; color: #666; }
    .postmodal-desc { color: #333; line-height: 1.5; white-space: pre-wrap; overflow-wrap: anywhere; word-break: break-word; text-align: center; font-size: 1.1rem; }
    @media (max-width: 768px) {
        .postmodal-body { grid-template-columns: 1fr; }
        .postmodal-dialog { width: 94vw; }
        .postmodal-image { height: 60vh; min-height: 260px; }
        .postmodal-image img { width: 100%; height: 100%; object-fit: contain; }
    }

    /* Slider card description clamp with See more toggle */
    .slide-card .card-text { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 3; overflow: hidden; }
    .slide-card .see-more { display: none; margin-top: 6px; background: none; border: 0; color: #198754; font-weight: 600; cursor: pointer; padding: 0; }
    .slide-card.has-more .see-more { display: inline; }
    .slide-card.expanded .card-text { -webkit-line-clamp: unset; display: block; }
    
    /* Larger fonts for Achievements and Events headings, and post modal text */
    .achievements-section h2,
    .events-section h2 { font-size: clamp(2rem, 3.2vw, 3rem); }
    .postmodal-content h3 { font-size: clamp(1.5rem, 2.8vw, 2.25rem); }
    .postmodal-meta { font-size: 1rem; }
    .postmodal-desc { font-size: 1.25rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark navbar-expand-md sticky-top d-md-none mobile-topnav">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#home">
                <img src="Picture/Logo2.png" alt="Logo" width="28" height="28" class="d-inline-block">
                D'MARSIANS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMainNav" aria-controls="mobileMainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mobileMainNav">
                <ul class="navbar-nav ms-auto mb-2 mb-md-0">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#offers">Offer</a></li>
                    <li class="nav-item"><a class="nav-link" href="#schedule">Schedule</a></li>
                    <li class="nav-item"><a class="nav-link" href="archive.php">Archive</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contacts">Contacts</a></li>
                </ul>
                <a class="btn btn-success ms-md-3 mt-2 mt-md-0" href="#register">Register Now</a>
            </div>
        </div>
    </nav>
    <!-- HEADER & HERO SECTION -->
    <header class="main-header glassy-nav">
        <div class="logo-section d-flex align-items-center gap-2 flex-wrap">
            <img src="Picture/Logo2.png" alt="Logo" class="logo img-fluid">
            <div class="gym-title">
                <h1 class="brand-glitch">D'MARSIANS<br>TAEKWONDO GYM</h1>
            </div>
        </div>
        <nav class="main-nav d-none d-md-flex flex-wrap gap-2 justify-content-center">
            <a href="#home">HOME</a>
            <a href="#about">ABOUT</a>
            <a href="#offers">OFFER</a>
            <a href="#schedule">SCHEDULE</a>
            <a href="archive.php">ARCHIVE</a>
            <a href="#contacts">CONTACTS</a>
        </nav>
        <a href="#register" class="register-btn d-none d-md-inline-block">REGISTER NOW!</a>
    </header>
    <section id="home" class="hero">
        <!-- Ambient light orb -->
        <div class="ambient-light-orb"></div>
        <!-- Grid pattern overlay -->
        <div class="grid-pattern-overlay"></div>
        
        <!-- Holographic Monitor Frame -->
        <div class="video-frame-container">
            <div class="video-frame">
                <video class="hero-video" aria-hidden="true" autoplay muted loop playsinline preload="auto">
                    <source src="<?php echo htmlspecialchars($heroVideoUrl); ?>" type="video/mp4">
                </video>
            </div>
            <!-- Floating Logo -->
            <img src="Picture/Logo2.png" alt="D'Marsians Logo" class="floating-logo">
        </div>
        
        <!-- Hero Content Below Video -->
        <div class="hero-content">
            <h3 class="sub-glitch">EMPOWERING STUDENTS THROUGH</h3>
            <h2 class="main-glitch" data-text="DISCIPLINE & STRENGTH">DISCIPLINE & STRENGTH</h2>
            <p class="hero-desc">Train with the best. Build confidence, respect, and physical power in a state-of-the-art environment.</p>
            <div class="cta-wrapper">
                <a href="#register" class="btn-reactor hero-btn">REGISTER NOW</a>
            </div>
        </div>
        <div class="stats-ticker">
            <div class="stat-item"><span>200+</span> STUDENTS</div>
            <div class="stat-item"><span>10+</span> YEARS EXP</div>
            <div class="stat-item"><span>5</span> BLACK BELT COACHES</div>
        </div>
    </section>

    <!-- ACHIEVEMENTS SLIDER -->
    <section id="achievements" class="achievements-section container">
        <h2>ACHIEVEMENTS</h2>
        <div class="post-slider" id="achievements-slider">
            <button class="arrow-btn prev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
            <div class="slider-track" data-slider-track></div>
            <button class="arrow-btn next" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <a href="archive.php?category=achievement" class="see-more-btn">SEE MORE</a>
    </section>

    <!-- EVENTS SLIDER -->
    <section id="events" class="events-section container">
        <h2>EVENTS</h2>
        <div class="post-slider" id="events-slider">
            <button class="arrow-btn prev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
            <div class="slider-track" data-slider-track></div>
            <button class="arrow-btn next" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <a href="archive.php?category=event" class="see-more-btn">SEE MORE</a>
    </section>

    <!-- INSTRUCTOR SECTION -->
    <section id="instructor" class="instructor-section container">
        <h2 class="section-title">MEET THE MASTER</h2>
        <div class="instructor-profile">
            <div class="instructor-card">
                <div class="instructor-img-wrapper">
                    <img src="Picture/sir-mars.png" alt="Instructor Mars" class="instructor-photo img-fluid">
                </div>
                <div class="instructor-info">
                    <div class="instructor-header">
                        <h3 class="instructor-title">
                            Marcelino <span class="mars-name">MARS</span> P. Maglinao Jr.
                        </h3>
                        <div class="rank-badge">
                            <span class="badge-text">HEAD COACH | 3RD DAN</span>
                        </div>
                    </div>
                    <p class="instructor-bio">
                        Head Coach Mars, a certified Taekwondo 3rd Dan Black Belt with 23 years of experience, dedicated to empowering students through discipline and strength.
                    </p>
                    <div class="instructor-stats">
                        <div class="stat-counter">
                            <div class="stat-number" data-target="23">0</div>
                            <div class="stat-label">Years Exp.</div>
                        </div>
                        <div class="stat-counter">
                            <div class="stat-number" data-target="3">0</div>
                            <div class="stat-label">Dan Rank</div>
                        </div>
                        <div class="stat-counter">
                            <div class="stat-number" data-target="100">0</div>
                            <div class="stat-label">Students</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WHAT WE OFFER -->
    <section id="offers" class="offers-section">
        <div class="container">
            <h2>WHAT WE OFFER</h2>
            <div class="offers-list row g-3 justify-content-center">
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="offer-card">
                        <img src="Picture/9.png" alt="Offer 1" class="img-fluid">
                        <div class="offer-text-wrapper">
                            <h3>Beginner to Advanced Taekwondo Training</h3>
                            <div class="offer-desc">Comprehensive classes for all skill levels, from new students to advanced practitioners.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="offer-card">
                        <img src="Picture/10.png" alt="Offer 2" class="img-fluid">
                        <div class="offer-text-wrapper">
                            <h3>Self-Defense Techniques</h3>
                            <div class="offer-desc">Practical self-defense skills for real-life situations, taught by experienced instructors.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="offer-card">
                        <img src="Picture/11.png" alt="Offer 3" class="img-fluid">
                        <div class="offer-text-wrapper">
                            <h3>Belt Promotion & Certification</h3>
                            <div class="offer-desc">Official belt testing and certification to recognize your progress and achievements.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="offer-card">
                        <img src="Picture/6.png" alt="Offer 4" class="img-fluid">
                        <div class="offer-text-wrapper">
                            <h3>Physical Fitness & Conditioning</h3>
                            <div class="offer-desc">Improve strength, flexibility, and endurance through dynamic martial arts workouts.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="offer-card">
                        <img src="Picture/7.png" alt="Offer 5" class="img-fluid">
                        <div class="offer-text-wrapper">
                            <h3>Sparring (Kyorugi)</h3>
                            <div class="offer-desc">Competitive and non-contact Taekwondo sparring to develop agility and strategy.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="offer-card">
                        <img src="Picture/8.png" alt="Offer 6" class="img-fluid">
                        <div class="offer-text-wrapper">
                            <h3>Patterns (Poomsae)</h3>
                            <div class="offer-desc">A series of choreographed movements to develop focus, discipline, and technique.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT US, SCHEDULE, MEMBERSHIP, HOURS -->
    <section id="about" class="about-section container">
        <div class="about-inner">
            <div class="about-header">
                <div class="row align-items-center gy-4">
                    <div class="col-12 col-md-4 d-flex justify-content-center">
                        <img src="Picture/Logo2.png" alt="About Icon" class="about-icon img-fluid">
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="about-text text-center text-md-start">
                        <h2 class="section-title">ABOUT US</h2>
                        <p>
                        At D’Marsians Taekwondo, we don’t just teach kicks and forms — we build <span class="highlight-green">discipline</span>, <span class="highlight-green">respect</span>, and <span class="highlight-green">confidence</span> in every student. Our program focuses on guiding students toward excellence both on and off the mat. We provide a safe, supportive environment where every child can grow stronger, sharper, and more self-assured.
                        </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="about-stats row g-3 mt-3">
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="stat-card h-100" id="schedule" data-icon="🗓">
                        <h3><span class="icon" style="color:#00D01D;">&#128197;</span> Rank's Schedule</h3>
                        <ul>
                            <li>Beginner: Tuesday, Thursday, & Friday<br>5:00 PM - 6:00 PM</li>
                            <li>Intermediate: Monday, Wednesday, & Friday<br>5:00 PM - 6:00 PM</li>
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="stat-card h-100" data-icon="💰">
                        <h3><span class="icon">&#128181;</span> Membership Price</h3>
                        <ul>
                            <li>Enrollment Fee: 700.00</li>
                            <li>Monthly Fee: 700.00</li>
                            <li>Trial Session: 150.00</li>
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="stat-card h-100" data-icon="⏰">
                        <h3><span class="icon">&#128337;</span> Opening Hours</h3>
                        <ul>
                            <li>Monday - Friday: 6:30 AM - 9:00 AM</li>
                            <li>Saturday: 5:30 PM - 9:00 PM</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- REGISTRATION FORM -->
    <section id="register" class="register-section">
        <div class="container">
            <div class="register-console">
                <h2 class="section-title">JOIN THE TEAM</h2>
                <p class="register-subtitle">Begin your journey to discipline &amp; strength.</p>
                <form class="register-form" id="registerForm" action="save_student.php" method="post">
                    <div class="form-row">
                        <div class="input-group">
                            <input type="text" name="student_name" placeholder=" " required>
                            <label>Student's Full Name</label>
                        </div>
                        <div class="input-group">
                            <input type="text" name="parent_name" placeholder=" " required>
                            <label>Parent's Full Name</label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="input-group">
                            <input type="text" name="address" placeholder=" " required>
                            <label>Address</label>
                        </div>
                        <div class="input-group">
                            <input type="text" name="school" placeholder=" " required>
                            <label>School</label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="input-group">
                            <input type="tel" name="phone" placeholder=" " required>
                            <label>Phone Number</label>
                        </div>
                        <div class="input-group">
                            <input type="email" name="email" placeholder=" " required>
                            <label>Email Address</label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="input-group">
                            <input type="text" name="parent_phone" placeholder=" " required>
                            <label>Parent's Phone Number</label>
                        </div>
                        <div class="input-group">
                            <input type="email" name="parent_email" placeholder=" " required>
                            <label>Parent's Email</label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="input-group">
                            <select name="class" required>
                                <option value="" disabled selected>Select Class</option>
                                <option value="Poomsae">Poomsae</option>
                                <option value="Kyorugi">Kyorugi</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <select name="belt_rank" required>
                                <option value="" disabled selected>Select Belt Rank</option>
                                <option value="White">White</option>
                                <option value="Yellow">Yellow</option>
                                <option value="Green">Green</option>
                                <option value="Blue">Blue</option>
                                <option value="Red">Red</option>
                                <option value="Black">Black</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="input-group">
                            <select name="enroll_type" required>
                                <option value="" disabled selected>Enroll or Trial Session</option>
                                <option value="Enroll">Enroll</option>
                                <option value="Trial Session">Trial Session</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <span></span>
                        </div>
                    </div>
                    <button class="submit-btn" type="submit">
                        CONFIRM REGISTRATION
                        <span class="btn-shine"></span>
                    </button>
                </form>
                <p class="register-disclaimer">*Notice: After submitting the form, please wait for a confirmation email from D'Marsians Taekwondo Gym to verify your successful registration.</p>
            </div>
        </div>
    </section>

    <!-- CONTACTS, MAP, FOOTER -->
    <section id="contacts" class="footer-section container-fluid">
        <div class="footer-map-bg"></div>
        <div class="footer-contact-bar">
            <div class="footer-contact-info">
                <div>
                    <span><i class="fa-solid fa-phone me-2"></i>CALL US</span><br>
                    <strong>0938-172-1987</strong>
                </div>
                <div>
                    <span><i class="fa-solid fa-location-dot me-2"></i>2nd floor Power Motors Fronting</span>
                    <strong>Imperial Appliance Rizal Avenue Pagadian City</strong>
                </div>
                <div>
                    <span><i class="fa-regular fa-clock me-2"></i>OPENING HOURS</span><br>
                    <strong>MON-SAT: 8AM - 9PM</strong>
                </div>
            </div>
        </div>
        <div class="footer-bg">
            <div class="footer-content container">
                <img src="Picture/Logo2.png" alt="Footer Logo" class="footer-logo img-fluid">
                <p>Thank you for visiting D'Marsians Taekwondo Team! We are committed to providing high-quality martial arts training for all ages, fostering discipline, confidence, and physical fitness in a safe and supportive environment. Join us and be part of our growing Taekwondo family!</p>
                <p class="footer-address">
                    <span><i class="fa-solid fa-location-dot me-2"></i>2nd floor Power Motors Fronting Imperial Appliance Rizal Avenue Pagadian City</span><br>
                    <span><i class="fa-solid fa-phone me-2"></i>8-172-1987</span><br>
                    <span><i class="fa-solid fa-envelope me-2"></i>dmarsians.taekwondo@gmail.com</span><br>
                    <span><i class="fa-brands fa-facebook me-2"></i>D' Marsians Taekwondo Gym</span>
                </p>
                <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap my-3">
                    <img src="Picture/SCC_NEW_LOGO 1.png" alt="SCC logo" class="img-fluid" style="height:64px">
                    <img src="Picture/Diskartech.png" alt="Diskartech logo" class="img-fluid" style="height:64px">
                    <img src="Picture/ccs.png" alt="CCS logo" class="img-fluid" style="height:64px">
                </div>
                <p class="copyright">&copy; 2024 D'MARSIANS TAEKWONDO GYM. All rights reserved.</p>
            </div>
        </div>
    </section>

    <!-- Popup Modal -->
    <div class="popup-overlay" id="popupOverlay">
        <div class="popup-modal">
            <div class="check-animation">
                <i class="fas fa-check check-icon"></i>
            </div>
            <h3>Registration Submitted!</h3>
            <p>Please proceed to D'Marsians Taekwondo Gym to continue your transaction.</p>
            <button class="popup-close-btn" onclick="closePopup()">OK</button>
        </div>
    </div>

    <!-- Post Details Modal -->
    <div class="postmodal-overlay" id="postModal" aria-hidden="true">
        <div class="postmodal-dialog" role="dialog" aria-modal="true" aria-labelledby="postModalTitle">
            <button class="postmodal-close" type="button" aria-label="Close" id="postModalClose">&times;</button>
            <div class="postmodal-body">
                <div class="postmodal-image">
                    <img id="postModalImg" alt="Post image">
                </div>
                <div class="postmodal-content">
                    <h3 id="postModalTitle"></h3>
                    <p class="postmodal-meta" id="postModalDate"></p>
                    <div class="postmodal-desc" id="postModalDesc"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Scripts/webpage.js"></script>
    <script>
    function renderSlider(posts, sliderId) {
        const slider = document.getElementById(sliderId);
        if (!slider) return;
        const track = slider.querySelector('[data-slider-track]');
        if (!track) return;

        const cardsHtml = posts.map((post) => {
            // Create SVG data URI placeholder that always works
            const placeholderSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='500'%3E%3Crect fill='%232d2d2d' width='400' height='500'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%23888' font-family='Arial, sans-serif' font-size='20' font-weight='bold'%3ENo Image%3C/text%3E%3C/svg%3E";
            
            let imageSrc = placeholderSvg;
            let hasImage = false;
            
            // Check if we have a valid image path
            if (post.image_path && post.image_path.trim() !== '') {
                // Ensure path starts with / for absolute path (if not already a full URL)
                if (!post.image_path.match(/^(https?:\/\/|data:)/)) {
                    imageSrc = '/' + post.image_path.replace(/^\//, '');
                } else {
                    imageSrc = post.image_path;
                }
                hasImage = true;
            }
            
            return (
                `<article class="slide-card post-card">`
              +   `<div class="image-wrap">`
              +     `<img src="${imageSrc}" alt="${post.title}" onerror="this.onerror=null; this.src='${placeholderSvg}'; this.style.backgroundColor='#2d2d2d';" loading="lazy" style="background-color: #2d2d2d;">`
              +     `${!hasImage ? '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #888; font-size: 18px; font-weight: bold; pointer-events: none; z-index: 1;">No Image</div>' : ''}`
              +     `<span class="hover-overlay"></span>`
              +   `</div>`
              +   `<div class="card-body">`
              +     `<h5 class="card-title">${post.title}</h5>`
              +     `<p class="card-text small mb-0">${post.description ?? ''}</p>`
              +     `<button type="button" class="see-more">See more</button>`
              +   `</div>`
              + `</article>`
            );
        }).join('');
        track.innerHTML = cardsHtml;

        // open modal when any part of a card is clicked (event delegation)
        if (!track._postClickBound) {
            track.addEventListener('click', (e) => {
                const card = e.target && e.target.closest ? e.target.closest('.slide-card') : null;
                if (!card || !track.contains(card)) return;
                const cards = Array.from(track.querySelectorAll('.slide-card'));
                const idx = cards.indexOf(card);
                if (idx >= 0 && posts[idx]) {
                    openPostModal(posts[idx]);
                }
            });
            track._postClickBound = true;
        }

        // Add see-more toggles only when text is overflowing
        Array.from(track.querySelectorAll('.slide-card')).forEach((card) => {
            const textEl = card.querySelector('.card-text');
            const btn = card.querySelector('.see-more');
            if (!textEl || !btn) return;
            // defer measurement until after layout
            requestAnimationFrame(() => {
                if (textEl.scrollHeight > textEl.clientHeight + 1) {
                    card.classList.add('has-more');
                    btn.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        card.classList.toggle('expanded');
                        btn.textContent = card.classList.contains('expanded') ? 'See less' : 'See more';
                    });
                }
            });
        });

        const prevBtn = slider.querySelector('.arrow-btn.prev');
        const nextBtn = slider.querySelector('.arrow-btn.next');

        function getStep() {
            const firstCard = track.querySelector('.slide-card');
            if (!firstCard) return 0;
            const styles = getComputedStyle(track);
            const gap = parseFloat(styles.columnGap || styles.gap || '0');
            const width = firstCard.getBoundingClientRect().width;
            return width + gap;
        }

        function updateButtons() {
            const maxScrollLeft = track.scrollWidth - track.clientWidth - 1; // tolerance
            prevBtn.disabled = track.scrollLeft <= 0;
            nextBtn.disabled = track.scrollLeft >= maxScrollLeft;
        }

        function scrollByStep(direction) {
            const step = getStep();
            if (!step) return;
            track.scrollBy({ left: direction * step, behavior: 'smooth' });
        }

        // Don't use scroll-based navigation for achievements or events (both use infinite carousel)
        if (sliderId !== 'achievements-slider' && sliderId !== 'events-slider') {
            // Function to update active card based on scroll position (for events only)
            function updateActiveCard() {
                const cards = Array.from(track.querySelectorAll('.slide-card'));
                if (cards.length === 0) return;
                
                const trackRect = track.getBoundingClientRect();
                const trackCenter = trackRect.left + trackRect.width / 2;
                
                let activeCard = null;
                let minDistance = Infinity;
                
                cards.forEach(card => {
                    const cardRect = card.getBoundingClientRect();
                    const cardCenter = cardRect.left + cardRect.width / 2;
                    const distance = Math.abs(cardCenter - trackCenter);
                    
                    if (distance < minDistance) {
                        minDistance = distance;
                        activeCard = card;
                    }
                });
                
                // Remove active class from all cards
                cards.forEach(card => card.classList.remove('active'));
                
                // Add active class to the center card
                if (activeCard) {
                    activeCard.classList.add('active');
                }
            }

            prevBtn.addEventListener('click', () => scrollByStep(-1));
            nextBtn.addEventListener('click', () => scrollByStep(1));
            track.addEventListener('scroll', () => {
                updateButtons();
                updateActiveCard();
            }, { passive: true });

            // Initialize state after layout
            requestAnimationFrame(() => {
                updateButtons();
                updateActiveCard();
            });
            window.addEventListener('resize', () => {
                requestAnimationFrame(() => {
                    updateButtons();
                    updateActiveCard();
                });
            });
        } else {
            // For achievements and events, don't attach scroll listeners - infinite carousel will handle it
            // Just ensure buttons are enabled
            prevBtn.disabled = false;
            nextBtn.disabled = false;
        }
    }

    // Fetch and render sliders
    // Global error handler for images
    window.addEventListener('error', function(e) {
        if (e.target && e.target.tagName === 'IMG') {
            const img = e.target;
            if (!img.src.includes('placeholder.com')) {
                img.src = 'https://via.placeholder.com/400x300.png/2d2d2d/ffffff?text=Image+Not+Found';
                img.onerror = null; // Prevent infinite loop
            }
        }
    }, true); // Use capture phase

    // Infinite Loop Coverflow Carousel for Achievements and Events
    function initCoverflowCarousel(sliderId) {
        const slider = document.getElementById(sliderId);
        if (!slider) return;
        
        const track = slider.querySelector('.slider-track');
        if (!track) return;
        
        // Wait for cards to be rendered
        const checkCards = setInterval(() => {
            const cards = Array.from(track.querySelectorAll('.slide-card'));
            if (cards.length > 0) {
                clearInterval(checkCards);
                setupCoverflowCarousel(slider, track, cards);
            }
        }, 100);
        
        // Stop checking after 5 seconds
        setTimeout(() => clearInterval(checkCards), 5000);
    }
    
    function setupCoverflowCarousel(slider, track, cards) {
        const nextBtn = slider.querySelector('.arrow-btn.next');
        const prevBtn = slider.querySelector('.arrow-btn.prev');
        
        if (!nextBtn || !prevBtn || cards.length === 0) {
            console.error('Coverflow carousel setup failed: buttons or cards not found');
            return;
        }
        
        // Check if already initialized
        if (slider.dataset.coverflowInitialized === 'true') {
            return;
        }
        
        // Mark as initialized
        slider.dataset.coverflowInitialized = 'true';
        
        // Ensure buttons are enabled
        nextBtn.disabled = false;
        prevBtn.disabled = false;
        nextBtn.style.pointerEvents = 'auto';
        prevBtn.style.pointerEvents = 'auto';
        
        let currentIndex = 0;
        
        // Function to update the carousel based on current index
        function updateCarousel() {
            const total = cards.length;
            if (total === 0) return;
            
            // Remove all state classes first
            cards.forEach(card => {
                card.classList.remove('active', 'prev', 'next', 'hidden');
                card.style.zIndex = '0';
            });
            
            // Calculate indexes for infinite loop
            const prevIndex = (currentIndex - 1 + total) % total;
            const nextIndex = (currentIndex + 1) % total;
            
            // Assign the center item (active)
            cards[currentIndex].classList.add('active');
            cards[currentIndex].style.zIndex = '10';
            
            // Assign the left item (previous)
            cards[prevIndex].classList.add('prev');
            cards[prevIndex].style.zIndex = '5';
            
            // Assign the right item (next)
            cards[nextIndex].classList.add('next');
            cards[nextIndex].style.zIndex = '5';
            
            // Hide all others
            cards.forEach((card, index) => {
                if (index !== currentIndex && index !== prevIndex && index !== nextIndex) {
                    card.classList.add('hidden');
                }
            });
        }
        
        // Remove any existing listeners by cloning buttons
        const nextBtnClone = nextBtn.cloneNode(true);
        const prevBtnClone = prevBtn.cloneNode(true);
        nextBtn.parentNode.replaceChild(nextBtnClone, nextBtn);
        prevBtn.parentNode.replaceChild(prevBtnClone, prevBtn);
        
        // Get fresh references
        const newNextBtn = slider.querySelector('.arrow-btn.next');
        const newPrevBtn = slider.querySelector('.arrow-btn.prev');
        
        // Ensure they're enabled
        newNextBtn.disabled = false;
        newPrevBtn.disabled = false;
        
        // Event listener for next button
        newNextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (cards.length > 0) {
                currentIndex = (currentIndex + 1) % cards.length;
                updateCarousel();
            }
        }, { once: false });
        
        // Event listener for previous button
        newPrevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (cards.length > 0) {
                currentIndex = (currentIndex - 1 + cards.length) % cards.length;
                updateCarousel();
            }
        }, { once: false });
        
        // Initialize the carousel on load
        updateCarousel();
        
        console.log('Coverflow carousel initialized with', cards.length, 'cards');
    }

    fetch('get_posts.php?category=achievement')
        .then(res => res.json())
        .then(posts => { 
            renderSlider(posts, 'achievements-slider');
            // Initialize coverflow carousel after rendering
            setTimeout(() => initCoverflowCarousel('achievements-slider'), 500);
        })
        .catch(err => console.error('Error loading achievements:', err));

    fetch('get_posts.php?category=event')
        .then(res => res.json())
        .then(posts => { 
            renderSlider(posts, 'events-slider');
            // Initialize coverflow carousel after rendering
            setTimeout(() => {
                initCoverflowCarousel('events-slider');
            }, 800);
        })
        .catch(err => console.error('Error loading events:', err));

    // Post modal helpers
    function normalizePostDate(post) {
        return post.posted_at || post.date || post.created_at || '';
    }
    function formatPostDate(dateString) {
        if (!dateString) return '';
        // Normalize common "YYYY-MM-DD HH:MM:SS" into ISO-like "YYYY-MM-DDTHH:MM:SS"
        const normalized = dateString.replace(' ', 'T');
        const date = new Date(normalized);
        if (isNaN(date)) return dateString; // Fallback if parsing fails
        return date.toLocaleString('en-PH', {
            month: 'long',
            day: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }
    function getPostDescription(post) {
        return post.long_description || post.description || post.details || '';
    }
    function getPostImageSrc(post) {
        const placeholderSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1200' height='800'%3E%3Crect fill='%232d2d2d' width='1200' height='800'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%23888' font-family='Arial, sans-serif' font-size='32' font-weight='bold'%3ENo Image%3C/text%3E%3C/svg%3E";
        
        let imgSrc = post.image_path || post.image || post.cover || '';
        if (!imgSrc || imgSrc.trim() === '') {
            return placeholderSvg;
        }
        // Ensure path starts with / for absolute path (if not already a full URL)
        if (!imgSrc.match(/^(https?:\/\/|data:)/)) {
            imgSrc = '/' + imgSrc.replace(/^\//, '');
        }
        return imgSrc;
    }
    function openPostModal(post) {
        const overlay = document.getElementById('postModal');
        const img = document.getElementById('postModalImg');
        const title = document.getElementById('postModalTitle');
        const date = document.getElementById('postModalDate');
        const desc = document.getElementById('postModalDesc');

        const placeholderSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1200' height='800'%3E%3Crect fill='%232d2d2d' width='1200' height='800'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%23888' font-family='Arial, sans-serif' font-size='32' font-weight='bold'%3ENo Image%3C/text%3E%3C/svg%3E";
        const imgSrc = getPostImageSrc(post) || placeholderSvg;
        img.src = imgSrc;
        img.alt = post.title || 'Post image';
        // Add error handler to fallback to placeholder if image fails to load
        img.onerror = function() {
            this.onerror = null; // Prevent infinite loop
            this.src = placeholderSvg;
            this.style.backgroundColor = '#2d2d2d';
        };
        title.textContent = post.title || '';
        date.textContent = formatPostDate(normalizePostDate(post));
        desc.textContent = getPostDescription(post);

        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
    }
    function closePostModal() {
        const overlay = document.getElementById('postModal');
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
    }
    (function initPostModalClosers(){
        const overlay = document.getElementById('postModal');
        const closeBtn = document.getElementById('postModalClose');
        if (overlay) {
            overlay.addEventListener('click', (e) => { if (e.target === overlay) closePostModal(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePostModal(); });
        }
        if (closeBtn) closeBtn.addEventListener('click', closePostModal);
    })();

    // Popup functions
    function showPopup() {
        const popup = document.getElementById('popupOverlay');
        popup.style.display = 'flex';
    }

    function closePopup() {
        const popup = document.getElementById('popupOverlay');
        popup.style.display = 'none';
    }

    // Close popup when clicking outside
    document.getElementById('popupOverlay').addEventListener('click', function(e) {
        if (e.target === this) {
            closePopup();
        }
    });

    // Animated Counter for Instructor Stats
    function animateCounter(element, target, duration = 2000) {
        let start = 0;
        const increment = target / (duration / 16);
        const timer = setInterval(() => {
            start += increment;
            if (start >= target) {
                element.textContent = target + (target === 100 ? '+' : '');
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(start) + (target === 100 ? '+' : '');
            }
        }, 16);
    }

    // Intersection Observer for Stats Animation
    function initStatsAnimation() {
        const statsSection = document.getElementById('instructor');
        if (!statsSection) return;

        const statNumbers = statsSection.querySelectorAll('.stat-number');
        if (statNumbers.length === 0) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statNumber = entry.target;
                    const target = parseInt(statNumber.getAttribute('data-target'));
                    if (!statNumber.classList.contains('animated')) {
                        statNumber.classList.add('animated');
                        animateCounter(statNumber, target);
                    }
                }
            });
        }, { threshold: 0.5 });

        statNumbers.forEach(stat => observer.observe(stat));
    }

    document.addEventListener('DOMContentLoaded', function () {
        initStatsAnimation();
        const form = document.getElementById('registerForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                const enrollType = form.elements['enroll_type'].value;
                const submitButton = form.querySelector('button[type="submit"]');
                
                if (enrollType === 'Enroll') {
                    e.preventDefault();
                    
                    // Add loading state to button
                    submitButton.classList.add('loading');
                    submitButton.textContent = 'SUBMITTING...';
                    
                    const formData = new FormData(form);
                    fetch('submit_enrollment_request.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(result => {
                        // Remove loading state
                        submitButton.classList.remove('loading');
                        submitButton.textContent = 'SUBMIT';
                        
                        if (result.status === 'success') {
                            // Show popup instead of alert
                            showPopup();
                            form.reset();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    })
                    .catch(error => {
                        // Remove loading state
                        submitButton.classList.remove('loading');
                        submitButton.textContent = 'SUBMIT';
                        alert('Error submitting form: ' + error.message);
                    });
                } else if (enrollType === 'Trial Session') {
                    e.preventDefault();
                    // Add loading state to button
                    submitButton.classList.add('loading');
                    submitButton.textContent = 'SUBMITTING...';
                    const formData = new FormData(form);
                    fetch('register_trial_session.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(result => {
                        // Remove loading state
                        submitButton.classList.remove('loading');
                        submitButton.textContent = 'SUBMIT';
                        if (result.status === 'success') {
                            // Show popup instead of alert
                            showPopup();
                            form.reset();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    })
                    .catch(error => {
                        // Remove loading state
                        submitButton.classList.remove('loading');
                        submitButton.textContent = 'SUBMIT';
                        alert('Error submitting form: ' + error.message);
                    });
                }
                // If no enroll type selected, let the form submit as normal
            });
        }
    });
    </script>
</body>
</html> 