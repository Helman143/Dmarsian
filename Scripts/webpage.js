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
    // No more carousel animation logic here
}); 