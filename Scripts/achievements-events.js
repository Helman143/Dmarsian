/**
 * Achievement and Event Slider JavaScript
 * 
 * This file contains all JavaScript functionality for:
 * - Achievements slider (3D Coverflow carousel)
 * - Events slider (3D Coverflow carousel)
 * - Post modal display
 * - Image loading and error handling
 */

// Base path detection for image loading
const basePath = (function() {
    // Method 1: Try to detect from current path
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

/**
 * Render slider with posts
 * @param {Array} posts - Array of post objects
 * @param {string} sliderId - ID of the slider container ('achievements-slider' or 'events-slider')
 */
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
        // Fallback placeholder image path - try Picture/placeholder.png first, then Logo2.png
        const base = (typeof basePath !== 'undefined' ? basePath : '');
        const placeholderImagePath = base + '/Picture/placeholder.png';
        const fallbackPlaceholderPath = base + '/Picture/Logo2.png';
        
        let imageSrc = placeholderSvg;
        let hasImage = false;
        
        // Check if we have a valid image path
        if (post.image_path && post.image_path !== null && post.image_path.trim() !== '') {
            // Handle full URLs (Spaces/CDN)
            if (post.image_path.match(/^(https?:\/\/|data:)/)) {
                imageSrc = post.image_path;
                hasImage = true;
            } else {
                // Handle local paths (uploads/posts/filename.png)
                // Paths from database might be:
                // - "uploads/posts/filename.png" (root level)
                // - "admin/uploads/posts/filename.png" (admin folder)
                // - "/uploads/posts/filename.png" (with leading slash)
                let cleanPath = post.image_path.trim();
                
                // Remove leading slash if present (normalize)
                if (cleanPath.startsWith('/')) {
                    cleanPath = cleanPath.substring(1);
                }
                
                // Check if path already includes 'admin/' prefix
                const hasAdminPrefix = cleanPath.startsWith('admin/');
                
                // If path starts with 'uploads/', transform to 'admin/uploads/'
                if (cleanPath.startsWith('uploads/')) {
                    cleanPath = 'admin/' + cleanPath;
                } else if (!cleanPath.startsWith('admin/')) {
                    // If it's just a filename, prepend admin/uploads/posts/
                    cleanPath = 'admin/uploads/posts/' + cleanPath;
                }
                
                // Build the full path with basePath if needed
                if (basePath && basePath !== '') {
                    // Remove trailing slash from basePath if present
                    const base = basePath.replace(/\/$/, '');
                    imageSrc = base + '/' + cleanPath;
                } else {
                    // Root-relative path (starts with /)
                    imageSrc = '/' + cleanPath;
                }
                hasImage = true;
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
        
        // Image error handler: try admin path, then placeholder.png, then Logo2.png, then SVG
        // Build admin path if original is root/uploads/
        let adminPath = '';
        if (hasImage && imageSrc.includes('/uploads/') && !imageSrc.includes('/admin/')) {
            adminPath = imageSrc.replace('/uploads/', '/admin/uploads/');
        }
        
        // Create error handler with proper escaping
        const imageErrorHandler = `(function(img){img.onerror=null;var tries=parseInt(img.dataset.tries||'0');if(tries==0&&'${adminPath}'){img.src='${adminPath}';img.dataset.tries='1';}else if(tries<=1){img.src='${placeholderImagePath}';img.dataset.tries='2';}else if(tries==2){img.src='${fallbackPlaceholderPath}';img.dataset.tries='3';}else{img.src='${placeholderSvg}';img.style.backgroundColor='#2d2d2d';img.onerror=null;}})`;
        
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

    // Open modal when any part of a card is clicked (event delegation)
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
        // Function to update active card based on scroll position (for other sliders)
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

/**
 * Infinite Loop Coverflow Carousel for Achievements and Events
 * @param {HTMLElement} slider - The slider container element
 * @param {HTMLElement} track - The track element containing cards
 * @param {Array} cards - Array of card elements
 */
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

// Fetch and render Achievements slider
fetch('get_posts.php?category=achievement')
    .then(res => res.json())
    .then(posts => { 
        if (!posts || posts.length === 0) {
            renderSlider([], 'achievements-slider');
            return;
        }
        renderSlider(posts, 'achievements-slider');
        // Initialize coverflow carousel after DOM is fully updated
        // Use triple requestAnimationFrame to ensure all rendering is complete
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    const slider = document.getElementById('achievements-slider');
                    const track = slider ? slider.querySelector('.slider-track') : null;
                    if (track) {
                        const cards = Array.from(track.querySelectorAll('.slide-card'));
                        if (cards.length > 0) {
                            setupCoverflowCarousel(slider, track, cards);
                        }
                    }
                });
            });
        });
    })
    .catch(err => {
        console.error('Error loading achievements:', err);
        renderSlider([], 'achievements-slider');
    });

// Fetch and render Events slider
fetch('get_posts.php?category=event')
    .then(res => res.json())
    .then(posts => { 
        if (!posts || posts.length === 0) {
            renderSlider([], 'events-slider');
            return;
        }
        renderSlider(posts, 'events-slider');
        // Initialize coverflow carousel after DOM is fully updated
        // Use triple requestAnimationFrame to ensure all rendering is complete
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    const slider = document.getElementById('events-slider');
                    const track = slider ? slider.querySelector('.slider-track') : null;
                    if (track) {
                        const cards = Array.from(track.querySelectorAll('.slide-card'));
                        if (cards.length > 0) {
                            setupCoverflowCarousel(slider, track, cards);
                        }
                    }
                });
            });
        });
    })
    .catch(err => {
        console.error('Error loading events:', err);
        renderSlider([], 'events-slider');
    });

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
        // Use base path if detected, otherwise use root-relative path
        const cleanPath = imgSrc.replace(/^\//, '');
        imgSrc = (typeof basePath !== 'undefined' ? basePath : '') + '/' + cleanPath;
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

// Initialize post modal closers
(function initPostModalClosers(){
    const overlay = document.getElementById('postModal');
    const closeBtn = document.getElementById('postModalClose');
    if (overlay) {
        overlay.addEventListener('click', (e) => { if (e.target === overlay) closePostModal(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePostModal(); });
    }
    if (closeBtn) closeBtn.addEventListener('click', closePostModal);
})();

