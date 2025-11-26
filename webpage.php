<?php
// webpage.php
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
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700;800&family=Source+Serif+Pro:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">
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
    <header class="main-header">
        <div class="logo-section d-flex align-items-center gap-2 flex-wrap">
            <img src="Picture/Logo2.png" alt="Logo" class="logo img-fluid">
            <div class="gym-title">
                <h1>D'MARSIANS<br>TAEKWONDO GYM</h1>
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
        <video class="hero-video" aria-hidden="true" autoplay muted loop playsinline preload="auto">
            <source src="Video/quality_restoration_20251105174029661.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h2>D'MARSIANS<br>TAEKWONDO GYM</h2>
            <p>Empowering Students Through Discipline & Strength</p>
            <a href="#register" class="hero-register-btn">REGISTER NOW!</a>
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
        <h2 class="section-title">MEET OUR INSTRUCTOR</h2>
        <div class="instructor-profile">
            <div class="row align-items-center justify-content-center gy-4">
                <div class="col-12 col-md-4 d-flex justify-content-center">
                    <img src="Picture/1.png" alt="Instructor" class="instructor-photo img-fluid">
                </div>
                <div class="col-12 col-md-7 text-center text-md-start">
                    <div class="instructor-info">
                        <h3>
                            Mr. Marcelino <span class="highlight">"Mars"</span> Pescadera Maglinao Jr.
                        </h3>
                        <p>
                            Head Coach Mars, a certified Taekwondo <span class="rank-highlight">3rd Dan Black Belt</span><br>
                            with 23 years of experience
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WHAT WE OFFER -->
    <section id="offers" class="offers-section container">
        <h2>WHAT WE OFFER</h2>
        <div class="offers-list row g-3 justify-content-center">
            <div class="col-12 col-sm-6 col-md-4">
                <div class="offer-card">
                    <img src="Picture/9.png" alt="Offer 1" class="img-fluid">
                    <h3>Beginner to Advanced Taekwondo Training</h3>
                    <span class="offer-accent"></span>
                    <div class="offer-desc">Comprehensive classes for all skill levels, from new students to advanced practitioners.</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="offer-card">
                    <img src="Picture/10.png" alt="Offer 2" class="img-fluid">
                    <h3>Self-Defense Techniques</h3>
                    <span class="offer-accent"></span>
                    <div class="offer-desc">Practical self-defense skills for real-life situations, taught by experienced instructors.</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="offer-card">
                    <img src="Picture/11.png" alt="Offer 3" class="img-fluid">
                    <h3>Belt Promotion & Certification</h3>
                    <span class="offer-accent"></span>
                    <div class="offer-desc">Official belt testing and certification to recognize your progress and achievements.</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="offer-card">
                    <img src="Picture/6.png" alt="Offer 4" class="img-fluid">
                    <h3>Physical Fitness & Conditioning</h3>
                    <span class="offer-accent"></span>
                    <div class="offer-desc">Improve strength, flexibility, and endurance through dynamic martial arts workouts.</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="offer-card">
                    <img src="Picture/7.png" alt="Offer 5" class="img-fluid">
                    <h3>Sparring (Kyorugi)</h3>
                    <span class="offer-accent"></span>
                    <div class="offer-desc">Competitive and non-contact Taekwondo sparring to develop agility and strategy.</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="offer-card">
                    <img src="Picture/8.png" alt="Offer 6" class="img-fluid">
                    <h3>Patterns (Poomsae)</h3>
                    <span class="offer-accent"></span>
                    <div class="offer-desc">A series of choreographed movements to develop focus, discipline, and technique.</div>
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
                            At D’Marsians Taekwondo, we don’t just teach kicks and forms — we build discipline, respect, and confidence in every student. Our program focuses on guiding students toward excellence both on and off the mat. We provide a safe, supportive environment where every child can grow stronger, sharper, and more self-assured
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="about-stats row g-3 mt-3">
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="stat-card h-100" id="schedule">
                        <h3><span class="icon">&#128197;</span> Rank's Schedule</h3>
                        <ul>
                            <li>Beginner: Tuesday, Thursday, & Friday<br>5:00 PM - 6:00 PM</li>
                            <li>Intermediate: Monday, Wednesday, & Friday<br>5:00 PM - 6:00 PM</li>
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="stat-card h-100">
                        <h3><span class="icon">&#128181;</span> Membership Price</h3>
                        <ul>
                            <li>Enrollment Fee: 700.00</li>
                            <li>Monthly Fee: 700.00</li>
                            <li>Trial Session: 150.00</li>
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="stat-card h-100">
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
    <section id="register" class="register-section container">
        <h2>REGISTER NOW</h2>
        <p class="register-note">Parent/Guardians must pre-enroll their child(ren) by filling in the registration form below.</p>
        <form class="register-form" id="registerForm" action="save_student.php" method="post">
            <input class="w-100" type="text" name="student_name" placeholder="Student's Full Name" required>
            <input class="w-100" type="text" name="address" placeholder="Address" required>
            <input class="w-100" type="text" name="parent_name" placeholder="Parent's Full Name" required>
            <input class="w-100" type="text" name="phone" placeholder="Phone Number" required>
            <input class="w-100" type="email" name="email" placeholder="Email" required>
            <input class="w-100" type="text" name="parent_phone" placeholder="Parent's Phone Number" required>
            <input class="w-100" type="text" name="school" placeholder="School" required>
            <select class="w-100" name="class" required style="background: rgba(20, 111, 20, 0.14); color: #fff; border-radius: 6px; border: 1.5px solid #fff; padding: 12px 16px; font-size: 1rem; box-shadow: none;">
                <option value="" disabled selected>Class</option>
                <option value="Poomsae">Poomsae</option>
                <option value="Kyorugi">Kyorugi</option>
            </select>
            <input class="w-100" type="email" name="parent_email" placeholder="Parent's Email" required>
            <select class="w-100" name="belt_rank" required style="background: rgba(20, 111, 20, 0.14); color: #fff; border-radius: 6px; border: 1.5px solid #fff; padding: 12px 16px; font-size: 1rem; box-shadow: none;">
                <option value="" disabled selected>Belt Rank</option>
                <option value="White">White</option>
                <option value="Yellow">Yellow</option>
                <option value="Green">Green</option>
                <option value="Blue">Blue</option>
                <option value="Red">Red</option>
                <option value="Black">Black</option>
            </select>
            <select class="w-100" name="enroll_type" required style="background: rgba(20, 111, 20, 0.14); color: #fff; border-radius: 6px; border: 1.5px solid #fff; padding: 12px 16px; font-size: 1rem; box-shadow: none;">
                <option value="" disabled selected>Enroll or Trial Session</option>
                <option value="Enroll">Enroll</option>
                <option value="Trial Session">Trial Session</option>
            </select>
            <button class="w-100" type="submit">SUBMIT</button>
        </form>
        <p class="register-disclaimer">*Notice: After submitting the form, please wait for a confirmation email from D'Marsians Taekwondo Gym to verify your successful registration.</p>
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
            const imageSrc = post.image_path ? post.image_path : 'https://via.placeholder.com/400x300.png/2d2d2d/ffffff?text=No+Image';
            return (
                `<article class="slide-card post-card">`
              +   `<div class="image-wrap">`
              +     `<img src="${imageSrc}" alt="${post.title}">`
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

        prevBtn.addEventListener('click', () => scrollByStep(-1));
        nextBtn.addEventListener('click', () => scrollByStep(1));
        track.addEventListener('scroll', updateButtons, { passive: true });

        // Initialize state after layout
        requestAnimationFrame(updateButtons);
        window.addEventListener('resize', () => requestAnimationFrame(updateButtons));
    }

    // Fetch and render sliders
    fetch('get_posts.php?category=achievement')
        .then(res => res.json())
        .then(posts => { renderSlider(posts, 'achievements-slider'); });

    fetch('get_posts.php?category=event')
        .then(res => res.json())
        .then(posts => { renderSlider(posts, 'events-slider'); });

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
        return post.image_path || post.image || post.cover || '';
    }
    function openPostModal(post) {
        const overlay = document.getElementById('postModal');
        const img = document.getElementById('postModalImg');
        const title = document.getElementById('postModalTitle');
        const date = document.getElementById('postModalDate');
        const desc = document.getElementById('postModalDesc');

        img.src = getPostImageSrc(post) || 'https://via.placeholder.com/1200x800.png/2d2d2d/ffffff?text=No+Image';
        img.alt = post.title || 'Post image';
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