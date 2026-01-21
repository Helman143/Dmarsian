// Smooth scrolling for anchor links (robust against overflowed containers)
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            const targetTop = target.getBoundingClientRect().top + window.pageYOffset;
            window.scrollTo({ top: targetTop, behavior: 'smooth' });
        }
    });
});

// Simple form validation for registration form
document.addEventListener('DOMContentLoaded', function () {
    /**
     * Navbar glass morphing (desktop header + mobile command strip)
     */
    (function initNavMorphing() {
        const desktopHeader = document.querySelector('header.main-header.glassy-nav');
        const mobileBar = document.querySelector('nav.mobile-command');

        let ticking = false;
        const update = () => {
            ticking = false;
            const scrolled = window.scrollY > 50;
            if (desktopHeader) desktopHeader.classList.toggle('is-scrolled', scrolled);
            if (mobileBar) mobileBar.classList.toggle('is-scrolled', scrolled);
        };

        update();
        window.addEventListener('scroll', () => {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(update);
        }, { passive: true });
    })();

    /**
     * Mobile offcanvas: burger -> X, close on link click, glitch tap feedback.
     */
    (function initMobileOffcanvas() {
        const offcanvasEl = document.getElementById('mobileOffcanvas');
        const toggleBtn = document.getElementById('mobileMenuToggle');
        const mobileBar = document.querySelector('nav.mobile-command');
        if (!offcanvasEl || !toggleBtn) return;

        const getInstance = () => {
            try {
                if (window.bootstrap?.Offcanvas) return window.bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
            } catch {
                // ignore
            }
            return null;
        };

        // Hide the fixed mobile bar while the shutter is open so it doesn't overlay the menu header
        offcanvasEl.addEventListener('show.bs.offcanvas', () => mobileBar?.classList.add('is-offcanvas-open'));
        offcanvasEl.addEventListener('hidden.bs.offcanvas', () => mobileBar?.classList.remove('is-offcanvas-open'));

        // Close offcanvas on in-page navigation (and delay smooth scroll until close)
        const instance = getInstance();
        const links = offcanvasEl.querySelectorAll('a[href^="#"]');
        links.forEach((link) => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href') || '';
                const target = document.querySelector(href);
                if (!target) return;

                e.preventDefault();
                e.stopImmediatePropagation();

                link.classList.add('link-glitch');
                setTimeout(() => link.classList.remove('link-glitch'), 260);

                // Close first, then scroll
                const doScroll = () => {
                    const top = target.getBoundingClientRect().top + window.pageYOffset;
                    window.scrollTo({ top, behavior: 'smooth' });
                };

                if (instance) {
                    offcanvasEl.addEventListener('hidden.bs.offcanvas', doScroll, { once: true });
                    instance.hide();
                } else {
                    doScroll();
                }
            });
        });
    })();

    /**
     * HERO "System Boot-Up" sequence:
     * - Video deploy (CSS)
     * - Headline impact slam (CSS)
     * - Logo slide (CSS)
     * - Stats count-up (JS)
     */
    (function initHeroBootUp() {
        const hero = document.querySelector('#home.hero.hero-boot');
        if (!hero) return;

        const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const video = hero.querySelector('video.hero-video');
        const statSpans = hero.querySelectorAll('.stats-ticker .stat-item span');

        // Try to sync video start with the deploy reveal (muted autoplay should be allowed)
        if (video && !reducedMotion) {
            try {
                video.pause();
                video.currentTime = 0;
            } catch {
                // ignore
            }
            setTimeout(() => {
                try { video.play(); } catch { /* ignore */ }
            }, 900);
        }

        // Count-up for the stats ticker
        const parseStat = (text) => {
            const m = String(text).match(/(\d+)/);
            if (!m) return { target: 0, suffix: '' };
            const target = parseInt(m[1], 10);
            const suffix = String(text).replace(m[1], '');
            return { target, suffix };
        };

        const animateCount = (el, target, durationMs, suffix) => {
            const start = performance.now();
            const from = 0;
            const to = target;

            const step = (now) => {
                const t = Math.min(1, (now - start) / durationMs);
                // easeOutCubic
                const eased = 1 - Math.pow(1 - t, 3);
                const val = Math.round(from + (to - from) * eased);
                el.textContent = `${val}${suffix}`;
                if (t < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        };

        const animateTicks = (el, target, durationMs, suffix) => {
            const start = performance.now();
            const step = (now) => {
                const t = Math.min(1, (now - start) / durationMs);
                const val = Math.min(target, Math.floor(t * (target + 0.999)));
                el.textContent = `${val}${suffix}`;
                if (t < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        };

        if (statSpans.length) {
            // Save original values and reset to 0 immediately (ticker is faded-in by CSS)
            const parsed = Array.from(statSpans).map((s) => {
                const original = s.textContent.trim();
                const p = parseStat(original);
                s.dataset.target = String(p.target);
                s.dataset.suffix = p.suffix;
                s.textContent = `0${p.suffix}`;
                return p;
            });

            if (reducedMotion) {
                // Snap to final values
                statSpans.forEach((s) => {
                    s.textContent = `${s.dataset.target || '0'}${s.dataset.suffix || ''}`;
                });
                return;
            }

            // Start counting after the cinematic intro (matches ~1.2s spec)
            setTimeout(() => {
                statSpans.forEach((s, idx) => {
                    const target = parseInt(s.dataset.target || '0', 10);
                    const suffix = s.dataset.suffix || '';
                    if (idx === 0) {
                        animateCount(s, target, 1500, suffix); // 200+ students: fast roll
                    } else if (idx === 1) {
                        animateCount(s, target, 1400, suffix); // 10+ years: quick roll
                    } else {
                        animateTicks(s, target, 1900, suffix); // 5: slow ticks
                    }
                });
            }, 1200);
        }
    })();

    /**
     * Instructor "Tactical Profile Load" + parallax + synced count-up.
     */
    (function initInstructorHologram() {
        const section = document.getElementById('instructor');
        if (!section) return;

        const holo = section.querySelector('.instructor-holo');
        if (!holo) return;

        const statNumbers = Array.from(section.querySelectorAll('.stat-number'));

        const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const animateCount = (el, target, durationMs) => {
            const start = performance.now();
            const step = (now) => {
                const t = Math.min(1, (now - start) / durationMs);
                const eased = 1 - Math.pow(1 - t, 3); // easeOutCubic
                const val = Math.round(target * eased);
                el.textContent = String(val);
                if (t < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        };

        const startStats = () => {
            if (section.dataset.statsAnimated === '1') return;
            section.dataset.statsAnimated = '1';

            if (reducedMotion) {
                statNumbers.forEach((el) => {
                    const target = parseInt(el.getAttribute('data-target') || '0', 10);
                    el.textContent = String(target);
                });
                return;
            }

            // Start after the "clash & flash" moment (matches spec ~0.8s)
            setTimeout(() => {
                statNumbers.forEach((el, idx) => {
                    const target = parseInt(el.getAttribute('data-target') || '0', 10);
                    // Years: fast, Dan: very fast, Black belts: slightly slower tick feel
                    const duration = idx === 2 ? 1600 : 1100;
                    animateCount(el, target, duration);
                });
            }, 800);
        };

        const reveal = () => {
            section.classList.add('is-visible');
            startStats();
        };

        // Trigger only once when scrolled into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    reveal();
                    observer.disconnect();
                }
            });
        }, { threshold: 0.35 });

        observer.observe(section);

        // Parallax (only on hover-capable devices)
        const canHover = window.matchMedia && window.matchMedia('(hover: hover)').matches;
        if (!canHover || reducedMotion) return;

        const setParallax = (x, y) => {
            holo.style.setProperty('--photo-x', `${x}px`);
            holo.style.setProperty('--photo-y', `${y}px`);
            holo.style.setProperty('--panel-x', `${-x}px`);
            holo.style.setProperty('--panel-y', `${-y}px`);
        };

        holo.addEventListener('mousemove', (e) => {
            const r = holo.getBoundingClientRect();
            const nx = ((e.clientX - r.left) / r.width) * 2 - 1;  // -1..1
            const ny = ((e.clientY - r.top) / r.height) * 2 - 1; // -1..1
            const x = Math.max(-1, Math.min(1, nx)) * 10; // px
            const y = Math.max(-1, Math.min(1, ny)) * 6;  // px
            // Reverse movement: photo left, panel right
            setParallax(-x, -y);
        }, { passive: true });

        holo.addEventListener('mouseleave', () => setParallax(0, 0));
    })();

    const form = document.querySelector('.register-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            let valid = true;
            form.querySelectorAll('input[required]').forEach(input => {
                if (!input.value.trim()) {
                    input.style.border = '2px solid #f00';
                    valid = false;
                } else {
                    input.style.border = 'none';
                }
            });
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    }

    // Staggered Entrance Animation for Offer Cards
    const offersSection = document.querySelector('.offers-section');
    if (offersSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Find all cards inside the section
                    const cards = entry.target.querySelectorAll('.offer-card');
                    cards.forEach((card, index) => {
                        // Add delay based on index (0ms, 150ms, 300ms...)
                        setTimeout(() => {
                            card.classList.add('show-card');
                        }, index * 150);
                    });
                    // Unobserve after animation to prevent re-triggering
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.2, // Trigger when 20% of section is visible
            rootMargin: '0px 0px -50px 0px' // Trigger slightly before section enters viewport
        });

        observer.observe(offersSection);
    }

    // Make entire card clickable - navigate to registration or show details
    document.querySelectorAll('.offer-card').forEach(card => {
        card.addEventListener('click', function () {
            // You can customize this action - navigate to registration, show modal, etc.
            const cardTitle = this.querySelector('h3').textContent;
            console.log('Selected:', cardTitle);
            // Example: Scroll to registration form or open enrollment modal
            // window.location.href = '#register';
        });
    });

    /**
     * Footer map pulse: keep the pulse "stuck" to the pin across all screen sizes.
     * We model the map as an <img> with object-fit: cover and compute the cover transform.
     *
     * Seed: If no saved pin is available, we derive the natural pin coordinate from the current
     * CSS vars (--pulse-x/--pulse-y) so you can place it once and it becomes responsive forever.
     *
     * Optional: Alt+Click on the map to re-calibrate the pin (saves to localStorage).
     */
    (function initFooterMapPulse() {
        const map = document.querySelector('.footer-map-bg');
        if (!map) return;

        const pulse = map.querySelector('.map-pulse');
        const img = map.querySelector('.footer-map-img');
        if (!pulse || !img) return;

        const STORAGE_KEY = 'dmarsians.footerMapPin.v1';

        const readVarPx = (name, fallback) => {
            const raw = getComputedStyle(map).getPropertyValue(name).trim();
            if (!raw) return fallback;
            const n = parseFloat(raw.replace('px', ''));
            return Number.isFinite(n) ? n : fallback;
        };

        const getCoverTransform = (cw, ch, nw, nh) => {
            // object-fit: cover + object-position: center center
            const scale = Math.max(cw / nw, ch / nh);
            const sw = nw * scale;
            const sh = nh * scale;
            const ox = (cw - sw) / 2;
            const oy = (ch - sh) / 2;
            return { scale, ox, oy };
        };

        const containerToNatural = (cx, cy, cw, ch, nw, nh) => {
            const { scale, ox, oy } = getCoverTransform(cw, ch, nw, nh);
            return {
                x: (cx - ox) / scale,
                y: (cy - oy) / scale
            };
        };

        const naturalToContainer = (nx, ny, cw, ch, nw, nh) => {
            const { scale, ox, oy } = getCoverTransform(cw, ch, nw, nh);
            return {
                x: ox + nx * scale,
                y: oy + ny * scale
            };
        };

        const loadSavedPin = () => {
            try {
                const raw = localStorage.getItem(STORAGE_KEY);
                if (!raw) return null;
                const parsed = JSON.parse(raw);
                if (!parsed || !Number.isFinite(parsed.x) || !Number.isFinite(parsed.y)) return null;
                return parsed;
            } catch {
                return null;
            }
        };

        const savePin = (x, y) => {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify({ x, y }));
            } catch {
                // ignore
            }
        };

        const ensurePinNatural = () => {
            const saved = loadSavedPin();
            if (saved) return saved;

            // Derive natural coordinate from the current pulse position (seed)
            const rect = map.getBoundingClientRect();
            const cw = rect.width;
            const ch = rect.height;
            const nw = img.naturalWidth || 1;
            const nh = img.naturalHeight || 1;

            const seedX = readVarPx('--pulse-x', cw / 2);
            const seedY = readVarPx('--pulse-y', ch / 2);

            const natural = containerToNatural(seedX, seedY, cw, ch, nw, nh);
            // Clamp within the image bounds
            natural.x = Math.max(0, Math.min(nw, natural.x));
            natural.y = Math.max(0, Math.min(nh, natural.y));
            savePin(natural.x, natural.y);
            return natural;
        };

        const updatePulse = (pin) => {
            const rect = map.getBoundingClientRect();
            const cw = rect.width;
            const ch = rect.height;
            const nw = img.naturalWidth || 1;
            const nh = img.naturalHeight || 1;

            const p = naturalToContainer(pin.x, pin.y, cw, ch, nw, nh);
            map.style.setProperty('--pulse-x', `${p.x}px`);
            map.style.setProperty('--pulse-y', `${p.y}px`);
        };

        const ready = () => {
            const pin = ensurePinNatural();
            updatePulse(pin);

            const onResize = () => updatePulse(pin);
            window.addEventListener('resize', onResize, { passive: true });

            // Re-calibrate: Alt+Click anywhere on the map
            map.addEventListener('click', (e) => {
                if (!e.altKey) return;
                const rect = map.getBoundingClientRect();
                const cx = e.clientX - rect.left;
                const cy = e.clientY - rect.top;
                const nw = img.naturalWidth || 1;
                const nh = img.naturalHeight || 1;
                const natural = containerToNatural(cx, cy, rect.width, rect.height, nw, nh);
                natural.x = Math.max(0, Math.min(nw, natural.x));
                natural.y = Math.max(0, Math.min(nh, natural.y));
                pin.x = natural.x;
                pin.y = natural.y;
                savePin(pin.x, pin.y);
                updatePulse(pin);
            });
        };

        if (img.complete && img.naturalWidth) {
            ready();
        } else {
            img.addEventListener('load', ready, { once: true });
        }
    })();
    /**
     * Team Profile Modal Logic (Diskar-Tech)
     */
    (function initTeamModal() {
        const logo = document.getElementById('diskartech-logo');
        const modal = document.getElementById('teamModal');
        const closeBtn = document.getElementById('teamModalClose');

        if (!logo || !modal) return;

        const openModal = () => {
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        };

        const closeModal = () => {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            // Return focus to the trigger to fix aria-hidden warning and improve a11y
            logo.focus();
        };

        // Open triggers
        logo.addEventListener('click', (e) => {
            e.preventDefault();
            openModal();
        });

        logo.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openModal();
            }
        });

        // Close triggers
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('open')) {
                closeModal();
            }

            /**
             * Custom Cursor Logic (Premium Feel)
             */
            (function initCustomCursor() {
                const cursor = document.getElementById('customCursor');
                if (!cursor) return;

                // Check if device supports hover (disable on touch)
                const isTouchDevice = window.matchMedia("(pointer: coarse)").matches;
                if (isTouchDevice) {
                    cursor.style.display = 'none';
                    return;
                }

                let mouseX = 0;
                let mouseY = 0;
                let cursorX = 0;
                let cursorY = 0;
                let isMoving = false;

                // Add text element to cursor dynamically
                const cursorText = document.createElement('span');
                cursorText.className = 'cursor-text';
                cursor.appendChild(cursorText);

                // Smooth follow (Linear Interpolation)
                const lerp = (start, end, factor) => start + (end - start) * factor;

                document.addEventListener('mousemove', (e) => {
                    mouseX = e.clientX;
                    mouseY = e.clientY;
                    isMoving = true;
                    // Ensure cursor is visible when moving
                    cursor.style.opacity = '1';
                });

                const animate = () => {
                    if (isMoving) {
                        // Adjust speed factor (0.1 = smooth/slow, 0.2 = faster)
                        cursorX = lerp(cursorX, mouseX, 0.15);
                        cursorY = lerp(cursorY, mouseY, 0.15);
                        cursor.style.transform = `translate3d(${cursorX}px, ${cursorY}px, 0)`;
                    }
                    requestAnimationFrame(animate);
                };
                animate();

                // Hover Interactions
                const interactiveElements = document.querySelectorAll('a, button, .clickable-logo, .team-member-card, input, textarea');

                interactiveElements.forEach(el => {
                    el.addEventListener('mouseenter', () => {
                        cursor.classList.add('hovered');

                        // Contextual Text
                        if (el.classList.contains('team-member-card')) {
                            cursorText.textContent = 'View Profile';
                        } else if (el.classList.contains('clickable-logo')) {
                            cursorText.textContent = 'Our Team';
                        } else if (el.tagName === 'A' || el.tagName === 'BUTTON') {
                            // cursorText.textContent = 'Click'; // Optional: minimal is better
                        }
                    });

                    el.addEventListener('mouseleave', () => {
                        cursor.classList.remove('hovered');
                        cursorText.textContent = '';
                        cursor.classList.remove('clicking');
                    });
                });

                // Click Effect
                document.addEventListener('mousedown', () => {
                    cursor.classList.add('clicking');
                });

                document.addEventListener('mouseup', () => {
                    cursor.classList.remove('clicking');
                });

                // Hide cursor when leaving window
                document.addEventListener('mouseleave', () => {
                    cursor.style.opacity = '0';
                });

                document.addEventListener('mouseenter', () => {
                    cursor.style.opacity = '1';
                });
            })();
        });
    })();
}); 