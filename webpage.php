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

// Get DigitalOcean Spaces base URL for images
// Format: https://[SPACES_NAME].[SPACES_REGION].digitaloceanspaces.com/posts/
$spacesBaseUrl = null;
$spacesName = getenv('SPACES_NAME');
$spacesRegion = getenv('SPACES_REGION') ?: 'nyc3';
if (!empty($spacesName) && !empty($spacesRegion)) {
    $spacesBaseUrl = "https://{$spacesName}.{$spacesRegion}.digitaloceanspaces.com/posts/";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D'MARSIANS TAEKWONDO GYM</title>
    <link rel="icon" type="image/png" href="Picture/Logo2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Styles/webpage.css?v=<?php echo time(); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Updated typography: Orbitron/Teko for headings, Montserrat for body -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;700;800&family=Teko:wght@600;700&family=Montserrat:wght@400;500;600;700&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    /* Slider card description clamp with See more toggle */
    .slide-card .card-text { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 3; overflow: hidden; }
    .slide-card .see-more { display: none; margin-top: 6px; background: none; border: 0; color: #198754; font-weight: 600; cursor: pointer; padding: 0; }
    .slide-card.has-more .see-more { display: inline; }
    .slide-card.expanded .card-text { -webkit-line-clamp: unset; display: block; }
    
    /* Larger fonts for Achievements and Events headings */
    .achievements-section h2,
    .events-section h2 { font-size: clamp(2rem, 3.2vw, 3rem); }
    </style>
</head>
<body>
    <!-- MOBILE COMMAND STRIP (Offcanvas Shutter) -->
    <nav class="navbar mobile-command fixed-top d-xl-none" aria-label="Mobile navigation">
        <div class="container-fluid px-3">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#home">
                <img src="Picture/Logo2.png" alt="Logo" width="28" height="28" class="d-inline-block">
                D'MARSIANS TAEKWONDO GYM
            </a>

            <button id="mobileMenuToggle" class="neon-burger" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#mobileOffcanvas"
                    aria-controls="mobileOffcanvas" aria-label="Open menu">
                <span class="burger-line"></span>
                <span class="burger-line"></span>
                <span class="burger-line"></span>
            </button>
        </div>
    </nav>

    <div class="offcanvas offcanvas-end offcanvas-holo d-xl-none" tabindex="-1" id="mobileOffcanvas" aria-labelledby="mobileOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileOffcanvasLabel">MENU</h5>
            <button type="button" class="btn-close holo-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <nav class="offcanvas-nav" aria-label="Mobile menu links">
                <a class="offcanvas-link" href="#home">HOME</a>
                <a class="offcanvas-link" href="#about">ABOUT</a>
                <a class="offcanvas-link" href="#offers">OFFER</a>
                <a class="offcanvas-link" href="#schedule">SCHEDULE</a>
                <a class="offcanvas-link" href="archive.php">ARCHIVE</a>
                <a class="offcanvas-link" href="#contacts">CONTACTS</a>
                <a class="offcanvas-cta" href="#register">REGISTER NOW</a>
            </nav>
        </div>
    </div>
    <!-- HEADER & HERO SECTION -->
    <header class="main-header glassy-nav d-none d-xl-flex">
        <div class="logo-section d-flex align-items-center gap-2">
            <img src="Picture/Logo2.png" alt="Logo" class="logo img-fluid">
            <div class="gym-title">
                <h1 class="brand-glitch">D'MARSIANS<br>TAEKWONDO GYM</h1>
            </div>
        </div>
        <nav class="main-nav d-none d-xl-flex justify-content-center">
            <a href="#home">HOME</a>
            <a href="#about">ABOUT</a>
            <a href="#offers">OFFER</a>
            <a href="#schedule">SCHEDULE</a>
            <a href="archive.php">ARCHIVE</a>
            <a href="#contacts">CONTACTS</a>
        </nav>
        <a href="#register" class="register-btn d-none d-lg-inline-block">REGISTER NOW!</a>
    </header>
    <section id="home" class="hero hero-boot boot-start">
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
    <section id="instructor" class="instructor-section">
        <div class="container">
            <h2 class="section-title">MEET THE MASTER</h2>
            <div class="instructor-profile">
                <div class="instructor-card instructor-holo" data-holo>
                    <div class="instructor-flash" aria-hidden="true"></div>

                    <div class="row align-items-center g-4 instructor-row">
                        <!-- Left: Image (col-lg-5) -->
                        <div class="col-12 col-lg-5 d-flex justify-content-center justify-content-lg-start">
                            <div class="instructor-img-wrapper instructor-photo-frame">
                                <img src="Picture/sir-mars.png" alt="Head Coach Mars" class="instructor-photo img-fluid">
                            </div>
                        </div>

                        <!-- Right: Glass Panel (col-lg-7) -->
                        <div class="col-12 col-lg-7">
                            <div class="instructor-info instructor-panel text-center text-lg-start">
                                <div class="mars-signature" aria-hidden="true">Mars</div>

                                <div class="instructor-header">
                                    <h3 class="instructor-title">
                                        <span class="instructor-first">Marcelino</span>
                                        <span class="mars-name">MARS</span>
                                        <span class="instructor-rest">P. Maglinao Jr.</span>
                                    </h3>
                                    <div class="rank-badge">
                                        <span class="badge-text">HEAD COACH | 3RD DAN</span>
                                    </div>
                                </div>

                                <p class="instructor-bio">
                                    Certified Taekwondo 3rd Dan Black Belt with 23 years of experience — building discipline, confidence, and champions on and off the mat.
                                </p>

                                <!-- Stats Row (nested grid) -->
                                <div class="row g-0 instructor-stats-grid">
                                    <div class="col-4 stat-col stat-col-divider">
                                        <div class="stat-counter">
                                            <div class="stat-number" data-target="23">0</div>
                                            <div class="stat-label">Years Exp.</div>
                                        </div>
                                    </div>
                                    <div class="col-4 stat-col stat-col-divider">
                                        <div class="stat-counter">
                                            <div class="stat-number" data-target="3">0</div>
                                            <div class="stat-label">Dan Rank</div>
                                        </div>
                                    </div>
                                    <div class="col-4 stat-col">
                                        <div class="stat-counter">
                                            <div class="stat-number" data-target="5">0</div>
                                            <div class="stat-label">Black Belts</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                            <li><span class="stat-label">Beginner:</span> Tuesday, Thursday, &amp; Friday<br>5:00 PM - 6:00 PM</li>
                            <li><span class="stat-label">Intermediate:</span> Monday, Wednesday, &amp; Friday<br>5:00 PM - 6:00 PM</li>
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="stat-card h-100" data-icon="💰">
                        <h3><span class="icon">&#128181;</span> Membership Price</h3>
                        <ul>
                            <li><span class="stat-label">Enrollment Fee:</span> 700.00</li>
                            <li><span class="stat-label">Monthly Fee:</span> 700.00</li>
                            <li><span class="stat-label">Trial Session:</span> 150.00</li>
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="stat-card h-100" data-icon="⏰">
                        <h3><span class="icon">&#128337;</span> Opening Hours</h3>
                        <ul>
                            <li><span class="stat-label">Monday - Friday:</span> 6:30 AM - 9:00 AM</li>
                            <li><span class="stat-label">Saturday:</span> 5:30 PM - 9:00 PM</li>
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
    <section id="contacts" class="footer-section container-fluid" style="background-color: #FFFFFF !important; background-image: none !important;">
        <!-- <div class="footer-map-bg">
            <img class="footer-map-img" src="Picture/3.png" alt="Map showing D'Marsians Taekwondo location">
        </div> -->
        <div class="footer-contact-bar">
            <div class="container footer-console">
                <div class="row gy-5">
                    <!-- Column 1: Brand Identity -->
                    <div class="col-12 col-md-4 text-center text-md-start">
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3 mb-3">
                            <img src="Picture/Logo2.png" alt="D'Marsians logo" class="footer-console-logo img-fluid">
                            <div>
                                <div class="footer-console-title">D'Marsians</div>
                                <div class="footer-console-subtitle">Taekwondo Gym</div>
                            </div>
                        </div>
                        <p class="footer-console-mission mb-0">
                            Thank you for visiting D'Marsians Taekwondo Team!
                        </p>
                    </div>

                    <!-- Column 2: Contact Data -->
                    <div class="col-12 col-md-4 text-center">
                        <h4 class="footer-console-heading mb-3">CONTACT</h4>
                        <div class="d-flex align-items-center justify-content-center gap-3 mb-3 footer-console-row">
                            <i class="fa-solid fa-phone footer-console-icon"></i>
                            <span class="footer-console-value">0938-172-1987</span>
                        </div>
                        <div class="d-flex align-items-start justify-content-center gap-3 mb-3 footer-console-row">
                            <i class="fa-solid fa-location-dot footer-console-icon mt-1"></i>
                            <span class="footer-console-value">
                                2nd floor Power Motors Fronting<br>
                                Imperial Appliance Rizal Avenue Pagadian City
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center gap-3 footer-console-row">
                            <i class="fa-regular fa-clock footer-console-icon"></i>
                            <span class="footer-console-value">MON-SAT: 8AM - 9PM</span>
                        </div>
                    </div>

                    <!-- Column 3: Badges & Socials -->
                    <div class="col-12 col-md-4 text-center text-md-end">
                        <h4 class="footer-console-heading mb-3">CONNECT</h4>
                        <div class="footer-social justify-content-center justify-content-md-end mb-3">
                            <a class="social-btn" href="tel:+639381721987" aria-label="Call us">
                                <i class="fa-solid fa-phone"></i>
                            </a>
                            <a class="social-btn" href="mailto:dmarsians.taekwondo@gmail.com" aria-label="Email us">
                                <i class="fa-solid fa-envelope"></i>
                            </a>
                            <a class="social-btn" href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                                <i class="fa-brands fa-facebook-f"></i>
                            </a>
                        </div>

                        <div class="footer-badges justify-content-center justify-content-md-end gap-3">
                            <img src="Picture/SCC_NEW_LOGO 1.png" alt="SCC logo" class="footer-badge img-fluid">
                            <img src="Picture/Diskartech.png" alt="Diskartech logo" class="footer-badge img-fluid clickable-logo" id="diskartech-logo" role="button" tabindex="0">
                            <img src="Picture/ccs.png" alt="CCS logo" class="footer-badge img-fluid">
                        </div>
                    </div>
                </div>

                <hr class="footer-console-divider my-4">
                <div class="text-center footer-console-copy">
                    &copy; 2025 D'Marsians Taekwondo Gym. All rights reserved.
                </div>
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

    <!-- D'Marsians Team Modal -->
    <div class="team-modal-overlay" id="teamModal" aria-hidden="true">
        <div class="team-modal-dialog">
            <button class="team-modal-close" id="teamModalClose" aria-label="Close">&times;</button>
            <div class="team-modal-content">
                <div class="team-header">
                    <img src="Picture/Diskartech.png" alt="DiskarTech Logo" class="team-logo-large">
                    <h2 class="team-name">DISKAR-TECH</h2>
                    <p class="team-description">Innovators behind the D'Marsians Taekwondo System</p>
                </div>

                <div class="team-grid">
                    <!-- HELMAN DASHELLE DACUMA -->
                    <div class="team-member-card">
                        <div class="member-avatar">
                            <!-- Placeholder: Replace with actual image path if available -->
                            <img src="Picture/a1.png" alt="Helman Dashelle Dacuma" class="member-img">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">HELMAN DASHELLE DACUMA</h3>
                            <div class="member-role highlight-text">System Programmer</div>
                            <p class="member-desc">Handles system logic and technical structure</p>
                        </div>
                    </div>

                    <!-- HARRA LOU RAMOS -->
                    <div class="team-member-card">
                        <div class="member-avatar">
                            <img src="Picture/a2.png" alt="Harra Lou Ramos" class="member-img">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">HARRA LOU RAMOS</h3>
                            <div class="member-role highlight-text">UI/UX Designer / QA</div>
                            <p class="member-desc">Designs flow, layout & ensures system quality</p>
                        </div>
                    </div>

                    <!-- HADJARA SALEM -->
                    <div class="team-member-card">
                        <div class="member-avatar">
                            <img src="Picture/a3.png" alt="Hadjara Salem" class="member-img">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">HADJARA SALEM</h3>
                            <div class="member-role highlight-text">Project Manager</div>
                            <p class="member-desc">Oversees workflow and team coordination</p>
                        </div>
                    </div>

                    <!-- JONEL 'SEI' EBOL -->
                    <div class="team-member-card mentor-card">
                        <div class="member-avatar">
                            <img src="Picture/a4.png" alt="Jonel 'Sei' Ebol" class="member-img">
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">JONEL 'SEI' EBOL</h3>
                            <div class="member-role highlight-text">Capstone Adviser</div>
                            <p class="member-desc">Guides and mentors the team throughout the capstone project</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Scripts/webpage.js"></script>
    <script>
    // DigitalOcean Spaces base URL for images (set by PHP)
    const SPACES_BASE_URL = <?php echo $spacesBaseUrl ? json_encode($spacesBaseUrl) : 'null'; ?>;
    
    // Base URL helper function - handles both Spaces URLs and local paths
    // Priority: Full URL > Spaces URL > Local relative path
    function getImageUrl(imagePath) {
        if (!imagePath || imagePath.trim() === '') {
            return null;
        }
        
        // If it's already a full URL (Spaces/CDN) or data URI, use it directly
        if (imagePath.match(/^(https?:\/\/|data:)/)) {
            return imagePath;
        }
        
        // Extract filename from path (handles "uploads/posts/file.jpg" or just "file.jpg")
        const fileName = imagePath.split('/').pop();
        
        // If Spaces is configured, use Spaces URL
        if (SPACES_BASE_URL) {
            return SPACES_BASE_URL + fileName;
        }
        
        // Fallback to local relative path (no leading slash for compatibility)
        return `uploads/posts/${fileName}`;
    }
    
    // Detect base path for image URLs (handles subdirectory installations like /Dmarsian/)
    // On production (DigitalOcean), app is at root, so base path should be empty
    const basePath = (function() {
        // Check if we're on DigitalOcean App Platform (production)
        const isProduction = window.location.hostname.includes('ondigitalocean.app');
        
        // On production, always use root (no base path)
        if (isProduction) {
            return '';
        }
        
        // On localhost, detect subdirectory if present
        // Method 1: Try to detect from current page path
        const path = window.location.pathname;
        const pathParts = path.split('/').filter(p => p && p !== 'index.php' && p !== 'webpage.php');
        
        // If we're in a subdirectory (e.g., /Dmarsian/webpage.php), extract it
        if (pathParts.length > 0) {
            const potentialBase = '/' + pathParts[0];
            return potentialBase;
        }
        
        // Method 2: Try to detect from script src
        const scripts = document.getElementsByTagName('script');
        for (let script of scripts) {
            if (script.src && script.src.includes(window.location.hostname)) {
                const match = script.src.match(/^(https?:\/\/[^\/]+)(\/[^\/]+)/);
                if (match && match[2] && match[2] !== '/') {
                    return match[2];
                }
            }
        }
        
        return '';
    })();
    
    function renderSlider(posts, sliderId) {
        const slider = document.getElementById(sliderId);
        if (!slider) return;
        const track = slider.querySelector('[data-slider-track]');
        if (!track) return;

        // Get button references once (used throughout function)
        const prevBtn = slider.querySelector('.arrow-btn.prev');
        const nextBtn = slider.querySelector('.arrow-btn.next');

        // Handle empty state - show message if no posts
        if (!posts || posts.length === 0) {
            const categoryName = sliderId === 'achievements-slider' ? 'achievements' : 'events';
            track.innerHTML = `<div class="slider-empty-state" style="text-align: center; padding: 40px 20px; color: #888; font-size: 1.1rem;">
                <p style="margin: 0;">No ${categoryName} available at this time.</p>
            </div>`;
            // Hide navigation arrows for empty state
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            return;
        }

        // Show navigation arrows if they were hidden
        if (prevBtn) prevBtn.style.display = '';
        if (nextBtn) nextBtn.style.display = '';

        const cardsHtml = posts.map((post, index) => {
            // Create SVG data URI placeholder that always works
            const placeholderSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='500'%3E%3Crect fill='%232d2d2d' width='400' height='500'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%23888' font-family='Arial, sans-serif' font-size='20' font-weight='bold'%3ENo Image%3C/text%3E%3C/svg%3E";
            // Fallback placeholder image paths - use relative paths
            const placeholderImagePath = 'Picture/placeholder.png';
            const fallbackPlaceholderPath = 'Picture/Logo2.png';
            
            let imageSrc = placeholderSvg;
            let hasImage = false;
            
            // Check if we have a valid image path
            if (post.image_path && post.image_path !== null && post.image_path.trim() !== '') {
                // Use the helper function to get the correct image URL
                const resolvedUrl = getImageUrl(post.image_path);
                if (resolvedUrl) {
                    imageSrc = resolvedUrl;
                    hasImage = true;
                    // Debug logging
                    console.log(`Image path construction: original="${post.image_path}", resolved="${resolvedUrl}"`);
                }
            }
            
            // Determine initial slider class based on index (for 3D Coverflow effect)
            // First card: active, Second card: next, Others: hidden
            let sliderClass = '';
            if (index === 0) {
                sliderClass = 'active';
            } else if (index === 1) {
                sliderClass = 'next';
            } else {
                sliderClass = 'hidden';
            }
            
            // Image error handler: try placeholder.png, then Logo2.png, then SVG
            // Multi-tier fallback: try placeholder images, then SVG
            const imageErrorHandler = `(function(img){img.onerror=null;var tries=parseInt(img.dataset.tries||'0');if(tries==0){img.src='${placeholderImagePath}';img.dataset.tries='1';}else if(tries==1){img.src='${fallbackPlaceholderPath}';img.dataset.tries='2';}else{img.src='${placeholderSvg}';img.style.backgroundColor='#2d2d2d';img.onerror=null;}})`;
            
            // Debug: log image URL construction
            console.log("Image URL resolved:", imageSrc);
            
            return (
                `<article class="slide-card post-card ${sliderClass}">`
              +   `<div class="image-wrap">`
              +     `<img src="${imageSrc}" alt="${post.title || 'Post image'}" onerror="${imageErrorHandler}(this)" loading="lazy" style="background-color: #2d2d2d;">`
              +     `${!hasImage ? '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #888; font-size: 18px; font-weight: bold; pointer-events: none; z-index: 1;">No Image</div>' : ''}`
              +     `<span class="hover-overlay"></span>`
              +   `</div>`
              +   `<div class="card-body">`
              +     `<h5 class="card-title">${post.title || 'Untitled'}</h5>`
              +     `<p class="card-text small mb-0">${post.description || ''}</p>`
              +     `<button type="button" class="see-more">See more</button>`
              +   `</div>`
              + `</article>`
            );
        }).join('');
        track.innerHTML = cardsHtml;

        // Set initial z-index values for 3D Coverflow effect (for achievements and events sliders)
        // Wait for DOM to update before querying
        if (sliderId === 'achievements-slider' || sliderId === 'events-slider') {
            requestAnimationFrame(() => {
                const cards = Array.from(track.querySelectorAll('.slide-card'));
                cards.forEach((card, index) => {
                    if (card.classList.contains('active')) {
                        card.style.zIndex = '10';
                    } else if (card.classList.contains('next')) {
                        card.style.zIndex = '5';
                    } else {
                        card.style.zIndex = '0';
                    }
                });
            });
        }

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
    // Global error handler for images (fallback for any images that fail to load)
    window.addEventListener('error', function(e) {
        if (e.target && e.target.tagName === 'IMG') {
            const img = e.target;
            // Only handle if it's not already a placeholder (SVG or local placeholder)
            if (!img.src.includes('data:image/svg') && !img.src.includes('Logo2.png')) {
                // Use local placeholder instead of external service
                const placeholderSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='500'%3E%3Crect fill='%232d2d2d' width='400' height='500'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%23888' font-family='Arial, sans-serif' font-size='20' font-weight='bold'%3ENo Image%3C/text%3E%3C/svg%3E";
                img.src = placeholderSvg;
                img.onerror = null; // Prevent infinite loop
            }
        }
    }, true); // Use capture phase

    // Infinite Loop Coverflow Carousel for Achievements and Events
    function setupCoverflowCarousel(slider, track, cards) {
        const nextBtn = slider.querySelector('.arrow-btn.next');
        const prevBtn = slider.querySelector('.arrow-btn.prev');
        
        if (!nextBtn || !prevBtn || cards.length === 0) {
            console.error('Coverflow carousel setup failed: buttons or cards not found');
            return;
        }
        
        // Check if already initialized - prevent duplicate initialization
        if (slider.dataset.coverflowInitialized === 'true') {
            console.warn('Coverflow carousel already initialized for', sliderId);
            return;
        }
        
        // Mark as initialized immediately to prevent race conditions
        slider.dataset.coverflowInitialized = 'true';
        
        // Handle Single Post Scenario
        if (cards.length === 1) {
            cards[0].classList.add('active');
            cards[0].style.zIndex = '10';
            nextBtn.style.display = 'none';
            prevBtn.style.display = 'none';
            return;
        }

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
        
        // Remove any existing listeners by cloning buttons (preserves all classes and attributes)
        const nextBtnClone = nextBtn.cloneNode(true);
        const prevBtnClone = prevBtn.cloneNode(true);
        // Replace buttons to remove old event listeners
        nextBtn.parentNode.replaceChild(nextBtnClone, nextBtn);
        prevBtn.parentNode.replaceChild(prevBtnClone, prevBtn);
        
        // Get fresh references to the cloned buttons
        const finalNextBtn = slider.querySelector('.arrow-btn.next');
        const finalPrevBtn = slider.querySelector('.arrow-btn.prev');
        
        // Ensure they're enabled and visible
        if (finalNextBtn) {
            finalNextBtn.disabled = false;
            finalNextBtn.style.pointerEvents = 'auto';
            finalNextBtn.style.opacity = '1';
        }
        if (finalPrevBtn) {
            finalPrevBtn.disabled = false;
            finalPrevBtn.style.pointerEvents = 'auto';
            finalPrevBtn.style.opacity = '1';
        }
        
        // Event listener for next button
        if (finalNextBtn) {
            finalNextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (cards.length > 0) {
                    currentIndex = (currentIndex + 1) % cards.length;
                    updateCarousel();
                }
            }, { once: false });
        }
        
        // Event listener for previous button
        if (finalPrevBtn) {
            finalPrevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (cards.length > 0) {
                    currentIndex = (currentIndex - 1 + cards.length) % cards.length;
                    updateCarousel();
                }
            }, { once: false });
        }
        
        // Initialize the carousel on load - ensure classes are applied
        updateCarousel();
        
        console.log('Coverflow carousel initialized with', cards.length, 'cards');
    }

    fetch(`get_posts.php?category=achievement&t=${Date.now()}`, {
        cache: 'no-store',
        headers: {
            'Cache-Control': 'no-cache'
        }
    })
        .then(res => res.json())
        .then(posts => { 
            if (!posts || posts.length === 0) {
                renderSlider([], 'achievements-slider');
                return;
            }
            renderSlider(posts, 'achievements-slider');
            // Initialize coverflow carousel AFTER renderSlider has finished DOM injection
            // Use setTimeout to ensure renderSlider's innerHTML and all DOM updates are complete
            setTimeout(() => {
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        const slider = document.getElementById('achievements-slider');
                        const track = slider ? slider.querySelector('.slider-track') : null;
                        if (track) {
                        const cards = Array.from(track.querySelectorAll('.slide-card'));
                        if (cards.length > 0) {
                            // Double-check slider hasn't been initialized by another call
                            if (slider.dataset.coverflowInitialized !== 'true') {
                                setupCoverflowCarousel(slider, track, cards);
                            }
                        }
                        }
                    });
                });
            }, 100); // Small delay to ensure DOM is fully updated
        })
        .catch(err => {
            console.error('Error loading achievements:', err);
            renderSlider([], 'achievements-slider');
        });

    fetch(`get_posts.php?category=event&t=${Date.now()}`, {
        cache: 'no-store',
        headers: {
            'Cache-Control': 'no-cache',
            'Pragma': 'no-cache'
        }
    })
        .then(res => res.json())
        .then(posts => { 
            if (!posts || posts.length === 0) {
                renderSlider([], 'events-slider');
                return;
            }
            renderSlider(posts, 'events-slider');
            // Initialize coverflow carousel AFTER renderSlider has finished DOM injection
            // Use setTimeout to ensure renderSlider's innerHTML and all DOM updates are complete
            setTimeout(() => {
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        const slider = document.getElementById('events-slider');
                        const track = slider ? slider.querySelector('.slider-track') : null;
                        if (track) {
                        const cards = Array.from(track.querySelectorAll('.slide-card'));
                        if (cards.length > 0) {
                            // Double-check slider hasn't been initialized by another call
                            if (slider.dataset.coverflowInitialized !== 'true') {
                                setupCoverflowCarousel(slider, track, cards);
                            }
                        }
                        }
                    });
                });
            }, 100); // Small delay to ensure DOM is fully updated
        })
        .catch(err => {
            console.error('Error loading events:', err);
            renderSlider([], 'events-slider');
        });

    // Real-time post updates via BroadcastChannel
    // Function to refresh a specific slider by fetching fresh data
    function refreshSlider(sliderId, category) {
        // Determine the API category parameter based on slider
        let categoryParam;
        if (sliderId === 'achievements-slider') {
            categoryParam = 'achievement'; // get_posts.php will return both 'achievement' and 'achievement_event'
        } else if (sliderId === 'events-slider') {
            categoryParam = 'event'; // get_posts.php will return both 'event' and 'achievement_event'
        } else {
            console.error('Unknown slider ID:', sliderId);
            return;
        }
        
        // Use cache-busting timestamp and prevent caching
        const timestamp = Date.now();
        fetch(`get_posts.php?category=${categoryParam}&t=${timestamp}`, {
            cache: 'no-store',
            headers: {
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            }
        })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(posts => {
                // Handle both array and object responses
                const postArray = Array.isArray(posts) ? posts : (posts.posts || []);
                
                if (!postArray || postArray.length === 0) {
                    // Render empty state
                    renderSlider([], sliderId);
                    return;
                }
                
                // get_posts.php already filters correctly, so we can use posts directly
                renderSlider(postArray, sliderId);
                
                // Re-initialize coverflow carousel after rendering
                setTimeout(() => {
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            const slider = document.getElementById(sliderId);
                            const track = slider ? slider.querySelector('.slider-track') : null;
                            if (track) {
                                const cards = Array.from(track.querySelectorAll('.slide-card'));
                                if (cards.length > 0) {
                                    // Reset initialization flag to allow re-initialization
                                    slider.dataset.coverflowInitialized = 'false';
                                    setupCoverflowCarousel(slider, track, cards);
                                } else {
                                    // No cards, ensure empty state is shown
                                    renderSlider([], sliderId);
                                }
                            }
                        });
                    });
                }, 100);
            })
            .catch(err => {
                console.error(`Error refreshing ${sliderId}:`, err);
                // On error, render empty state to prevent stale data
                renderSlider([], sliderId);
            });
    }

    // Initialize BroadcastChannel listener for real-time updates
    if (typeof BroadcastChannel !== 'undefined') {
        const postUpdateChannel = new BroadcastChannel('post-updates');
        
        postUpdateChannel.addEventListener('message', (event) => {
            const { type, category, postId } = event.data;
            
            if (!category) {
                console.warn('Received post update without category:', event.data);
                return;
            }
            
            console.log('Received post update:', type, category, postId);
            
            // Determine which slider(s) to refresh based on category
            // For delete/archive operations, always refresh to ensure deleted posts are removed
            if (type === 'post-archived' || type === 'post-deleted') {
                // Force refresh both sliders to ensure deleted posts are removed
                if (category === 'achievement' || category === 'achievement_event') {
                    refreshSlider('achievements-slider', category);
                }
                if (category === 'event' || category === 'achievement_event') {
                    refreshSlider('events-slider', category);
                }
                // If achievement_event, refresh both
                if (category === 'achievement_event') {
                    refreshSlider('achievements-slider', category);
                    refreshSlider('events-slider', category);
                }
            } else if (category === 'achievement') {
                // Refresh achievements slider only
                refreshSlider('achievements-slider', category);
            } else if (category === 'event') {
                // Refresh events slider only
                refreshSlider('events-slider', category);
            } else if (category === 'achievement_event') {
                // Refresh both sliders
                refreshSlider('achievements-slider', category);
                refreshSlider('events-slider', category);
            } else {
                // Unknown category, refresh both to be safe
                console.warn('Unknown category, refreshing both sliders:', category);
                refreshSlider('achievements-slider', 'achievement');
                refreshSlider('events-slider', 'event');
            }
        });
    } else {
        console.warn('BroadcastChannel not supported. Real-time updates disabled.');
    }

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
        
        // Use the helper function to get the correct image URL
        const resolvedUrl = getImageUrl(imgSrc);
        return resolvedUrl || placeholderSvg;
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

    document.addEventListener('DOMContentLoaded', function () {
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