document.addEventListener('DOMContentLoaded', function () {
    // Dropdown Logic
    (function () {
        const dropdown = document.querySelector('.sidebar .dropdown');
        const toggle = dropdown ? dropdown.querySelector('.dropdown-toggle') : null;
        if (!dropdown || !toggle) return;

        function open() { dropdown.classList.add('open'); }
        function close() { dropdown.classList.remove('open'); }

        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            dropdown.classList.toggle('open');
        });
        toggle.addEventListener('touchstart', function (e) { e.preventDefault(); open(); }, { passive: false });
        dropdown.addEventListener('mouseenter', open);
        dropdown.addEventListener('mouseleave', close);
        document.addEventListener('click', function (e) { if (!dropdown.contains(e.target)) close(); });
    })();

    // Mobile Sidebar Toggle Logic
    (function () {
        const toggleBtn = document.getElementById('mobileSidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        function toggleSidebar() {
            if (!sidebar) return;
            sidebar.classList.toggle('active');
            if (backdrop) backdrop.classList.toggle('active');
        }

        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('active');
            if (backdrop) backdrop.classList.remove('active');
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleSidebar();
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', closeSidebar);
        }

        // Close sidebar when clicking a link inside it
        if (sidebar) {
            const links = sidebar.querySelectorAll('a');
            links.forEach(link => {
                // Don't close if it's the dropdown toggle (let dropdown logic handle that)
                if (!link.classList.contains('dropdown-toggle')) {
                    link.addEventListener('click', closeSidebar);
                }
            });
        }
    })();
}); 