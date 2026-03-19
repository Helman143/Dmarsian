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
    <title>Admin Student Management - D'MARSIANS Taekwondo System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="Styles/admin_dashboard.css">
    <link rel="stylesheet" href="Styles/admin_student_management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Source+Serif+Pro:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles/typography.css">
    <link rel="stylesheet" href="Styles/sidebar.css">
</head>
<body>
    <div class="container-fluid">
        <!-- Sidebar -->
        <?php $active = 'student'; include 'partials/admin_sidebar.php'; ?>

        <!-- Sidebar Backdrop (Mobile) -->
        <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

        <!-- Main Content -->
        <!-- Mobile fixed topbar -->
        <div class="mobile-topbar d-flex d-md-none align-items-center justify-content-between p-2 mb-3">
            <button id="mobileSidebarToggle" class="neon-menu-btn btn btn-outline-primary d-md-none mb-3" type="button" aria-label="Toggle sidebar">
                <span class="neon-hamburger"></span>
            </button>
            <h1 class="page-title m-0 fs-4">STUDENT MANAGEMENT</h1>
        </div>

        <div class="main-content">
            <h1 class="page-title d-none d-md-block">STUDENT MANAGEMENT</h1>
            
            <div class="student-form-container">
                <form class="student-form" id="studentForm" onsubmit="return handleFormSubmit(event)">
                    <div class="form-grid">
                        <!-- Left Column -->
                        <div class="form-column">
                            <div class="form-group">
                                <label>STD No.</label>
                                <input type="text" name="jeja_no" readonly placeholder="Auto-generated" style="background:#eee;cursor:not-allowed;">
                            </div>
                            
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="full_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="address" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Phone No.</label>
                                <input type="tel" name="phone" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" inputmode="email" autocomplete="email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender" required>
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>School</label>
                                <select name="school" required>
                                    <option value="">Select</option>
                                    <option value="SCC">SCC</option>
                                    <option value="ZSSAT">ZSSAT</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Class</label>
                                <select name="class" required>
                                    <option value="">Select</option>
                                    <option value="Poomsae">Poomsae</option>
                                    <option value="Kyorugi">Kyorugi</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="form-column">
                            <div class="form-group">
                                <label>Parent's Name</label>
                                <input type="text" name="parent_name">
                            </div>
                            
                            <div class="form-group">
                                <label>Parent's Phone</label>
                                <input type="tel" name="parent_phone">
                            </div>
                            
                            <div class="form-group">
                                <label>Parent's Email</label>
                                <input type="email" inputmode="email" autocomplete="email" name="parent_email">
                            </div>
                            
                            <div class="form-group">
                                <label>Belt Rank</label>
                                <div class="belt-rank-container">
                                    <select name="belt_rank" required>
                                        <option value="">Select</option>
                                        <option value="White">White</option>
                                        <option value="Yellow">Yellow</option>
                                        <option value="Green">Green</option>
                                        <option value="Blue">Blue</option>
                                        <option value="Red">Red</option>
                                        <option value="Black">Black</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Discount</label>
                                <div class="discount-container">
                                    <input type="number" name="discount" value="0.00" step="0.01">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Schedule</label>
                                <select name="schedule" required>
                                    <option value="">Select</option>
                                    <option value="MWF-PM">MWF Afternoon</option>
                                    <option value="TTS-PM">TTS Afternoon</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" required>
                                    <option value="">Select</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Freeze">Freeze</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-save">
                            <span class="btn-content"><i class="fas fa-save"></i><span>SAVE</span></span>
                        </button>       
                        <button type="submit" class="btn btn-update">
                            <span class="btn-content"><i class="fas fa-sync-alt"></i><span>UPDATE</span></span>
                        </button>
                        <button type="reset" class="btn btn-clear">
                            <span class="btn-content"><i class="fas fa-eraser"></i><span>CLEAR</span></span>
                        </button>
                        <button type="button" class="btn btn-export">
                            <span class="btn-content"><i class="fas fa-file-export"></i><span>EXPORT</span></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Student Table -->
            <!-- Mobile toolbar (hidden on md and up) -->
			<div class="d-flex gap-2 align-items-center mb-2 d-md-none" id="enrolleesToolbar">
				<input class="form-control form-control-sm" id="enrolleesSearch" placeholder="Search...">
				<select class="form-select form-select-sm" id="enrolleesFilter">
					<option value="">All</option>
					<option value="Active">Active</option>
					<option value="Inactive">Inactive</option>
                    <option value="Freeze">Freeze</option>
				</select>
			</div>

            <!-- Mobile card list (visible on xs/sm only) -->
            <div id="adminStudentCardList" class="student-card-list d-md-none"></div>
 
            <!-- Table Header with Search -->
            <div class="table-header-section d-none d-md-flex justify-content-end align-items-center mb-3">
                <div class="search-container">
                    <input type="text" id="studentSearchBox" placeholder="Search students...">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>

            <div class="table-container table-responsive enrollees-card">
                <table class="student-table table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>STD No.</th>
                            <th class="d-none d-md-table-cell">Date Enrolled</th>
                            <th>Fullname</th>
                            <th class="d-none d-md-table-cell">Address</th>
                            <th>Phone No.</th>
                            <th class="d-none d-md-table-cell">Email</th>
                            <th class="d-none d-md-table-cell">Gender</th>
                            <th class="d-none d-md-table-cell">School</th>
                            <th class="d-none d-md-table-cell">Parent's Name</th>
                            <th class="d-none d-md-table-cell">Parent's Phone</th>
                            <th class="d-none d-md-table-cell">Parent's Email</th>
                            <th class="d-none d-md-table-cell">Belt Rank</th>
                            <th class="d-none d-md-table-cell">Discount</th>
                            <th class="d-none d-md-table-cell">Schedule</th>
                            <th class="d-none d-md-table-cell">Class</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        <!-- Data will be loaded dynamically -->
                    </tbody>
                </table>
            </div>
            
            <div id="studentPagination" class="pagination-container mt-3"></div>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Scripts/sidebar.js?v=2"></script>
    <script src="Scripts/admin_student_management.js?v=2"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html> 