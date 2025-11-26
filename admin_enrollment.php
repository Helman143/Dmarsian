<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Enrollment - D'MARSIANS Taekwondo System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="Styles/admin_dashboard.css">
    <link rel="stylesheet" href="Styles/admin_enrollment.css">
    <link rel="stylesheet" href="Styles/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Source+Serif+Pro:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles/typography.css">
    <style>
        /* Enrollment page font scaling (admin) */
        body { font-size: 1.2rem; }
        h2 { font-size: 1.8rem; }
        h3 { font-size: 1.4rem; }
        .enrollment-table { font-size: 1.1em; }
        .search-container input { font-size: 1.1rem; }
        .btn, button, .form-control { font-size: 1.05rem; }
        @media (min-width: 1200px) {
            body { font-size: 1.3rem; }
            h2 { font-size: 2rem; }
            h3 { font-size: 1.5rem; }
            .enrollment-table { font-size: 1.15em; }
            .search-container input { font-size: 1.15rem; }
            .btn, button, .form-control { font-size: 1.1rem; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Sidebar -->
        <?php $active = 'enroll'; include 'partials/admin_sidebar.php'; ?>

        <!-- Mobile topbar with toggle button -->
        <div class="mobile-topbar d-flex d-md-none align-items-center justify-content-between p-2">
            <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Open sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <span class="text-success fw-bold">D'MARSIANS</span>
            <span></span>
        </div>
        <!-- Main Content -->
        <div class="main-content">
            <h2>ENROLLMENT</h2>
            <!-- Pending Enrollments Section -->
            <div class="enrollment-section">
                <div class="section-header">
                    <h3>Pending Enrollments</h3>
                    <div class="search-container">
                        <input type="text" id="searchPending" placeholder="Search pending...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
                <div class="table-container">
                    <table class="enrollment-table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Date Applied</th>
                                <th>Full Name</th>
                                <th>Address</th>
                                <th>Phone No.</th>
                                <th>Email</th>
                                <th>School</th>
                                <th>Parent's Name</th>
                                <th>Parent's Phone</th>
                                <th>Parent's Email</th>
                                <th>Rank</th>
                                <th>Belt Rank</th>
                                <th>Class</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="pendingTableBody">
                            <!-- Pending enrollments will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Approved Enrollments Section -->
            <div class="enrollment-section">
                <div class="section-header">
                    <h3>Approved Enrollments</h3>
                    <div class="search-container">
                        <input type="text" id="searchApproved" placeholder="Search approved...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
                <div class="table-container">
                    <table class="enrollment-table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>STD No.</th>
                                <th>Date Enrolled</th>
                                <th>Full Name</th>
                                <th>Contact Phone</th>
                            </tr>
                        </thead>
                        <tbody id="approvedTableBody">
                            <!-- Approved enrollments will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="Scripts/admin_enrollment.js"></script>
    <!-- Bootstrap 5 JS bundle (Popper included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
    // Mobile-safe dropdown: avoid double-trigger (touchstart then click)
    (function(){
        const dropdown = document.querySelector('.sidebar .dropdown');
        const toggle = dropdown ? dropdown.querySelector('.dropdown-toggle') : null;
        if(!dropdown || !toggle) return;

        function open(){ dropdown.classList.add('open'); }
        function close(){ dropdown.classList.remove('open'); }

        let touchedRecently = false;

        toggle.addEventListener('click', function(e){
            // Ignore the synthetic click that follows a touchstart
            if (touchedRecently) { e.preventDefault(); touchedRecently = false; return; }
            e.preventDefault();
            dropdown.classList.toggle('open');
        });
        toggle.addEventListener('touchstart', function(e){
            e.preventDefault();
            touchedRecently = true;
            open();
            setTimeout(function(){ touchedRecently = false; }, 300);
        }, {passive:false});

        dropdown.addEventListener('mouseenter', open);
        dropdown.addEventListener('mouseleave', close);
        // Close when clicking outside
        document.addEventListener('click', function(e){ if(!dropdown.contains(e.target)) close(); });
    })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html> 