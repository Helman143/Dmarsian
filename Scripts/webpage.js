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
        card.addEventListener('click', function() {
            // You can customize this action - navigate to registration, show modal, etc.
            const cardTitle = this.querySelector('h3').textContent;
            console.log('Selected:', cardTitle);
            // Example: Scroll to registration form or open enrollment modal
            // window.location.href = '#register';
        });
    });
}); 