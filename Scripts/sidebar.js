document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    if (sidebar && toggleBtn) {
        const toggleIcon = toggleBtn.querySelector('i');
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('expanded');
            // Toggle arrow direction
            if (sidebar.classList.contains('expanded')) {
                toggleIcon.classList.remove('fa-angle-double-right');
                toggleIcon.classList.add('fa-angle-double-left');
            } else {
                toggleIcon.classList.remove('fa-angle-double-left');
                toggleIcon.classList.add('fa-angle-double-right');
            }
        });
    }
}); 