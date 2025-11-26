<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// Check if user is logged in and is super admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'super_admin') {
    header("Location: index.php");
    exit();
}
require_once 'db_connect.php';
// Get user's name from session
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';
$firstDayThisMonth = date('Y-m-01');
$lastDayThisMonth = date('Y-m-t');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D'MARSIANS Taekwondo System - Admin Dashboard</title>
    <!-- Bootstrap 5 CSS for responsive utilities and grid -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="Styles/admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Source+Serif+Pro:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles/typography.css">
    <link rel="stylesheet" href="Styles/sidebar.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <!-- Sidebar -->
        <?php $active = 'dashboard'; include 'partials/admin_sidebar.php'; ?>
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
            <div class="welcome-header">
                <h1>Welcome, <?php echo htmlspecialchars($userName); ?></h1>
            </div>
            <!-- Stats Cards -->
            <div class="stats-container row g-3">
                <div class="stat-card col-12 col-sm-6 col-lg-3">
                    <div class="stat-header">
                        <i class="fas fa-user-plus"></i>
                        Today's Enrollees
                    </div>
                    <div class="stat-value" id="today-enrollees">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
                <div class="stat-card col-12 col-sm-6 col-lg-3">
                    <div class="stat-header">
                        <i class="fas fa-users"></i>
                        Weekly Enrollees
                    </div>
                    <div class="stat-value" id="weekly-enrollees">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
                <div class="stat-card col-12 col-sm-6 col-lg-3">
                    <div class="stat-header">
                        <i class="fas fa-peso-sign"></i>
                        Today's Collected Amount
                    </div>
                    <div class="stat-value" id="today-collected">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
                <div class="stat-card col-12 col-sm-6 col-lg-3">
                    <div class="stat-header">
                        <i class="fas fa-peso-sign"></i>
                        Weekly's Collected Amount
                    </div>
                    <div class="stat-value" id="weekly-collected">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
            </div>
            <!-- Charts Section -->
            <div class="charts-section row g-3 mt-2">
                <div class="chart-container col-12">
                    <h3>Collected vs. Uncollected Payments</h3>
                    <div class="chart-wrapper">
                        <canvas id="paymentsChart"></canvas>
                    </div>
                    <div class="filter-container d-flex flex-wrap align-items-center gap-2 mt-2">
                        <label for="from-date" class="form-label m-0">From:</label>
                        <input type="date" id="from-date" name="from-date" class="form-control form-control-sm" style="max-width: 180px;" value="<?php echo $firstDayThisMonth; ?>">
                        <label for="to-date" class="form-label m-0">To:</label>
                        <input type="date" id="to-date" name="to-date" class="form-control form-control-sm" style="max-width: 180px;" value="<?php echo $lastDayThisMonth; ?>">
                    </div>
                </div>
                <div class="chart-container col-12 col-lg-6">
                    <h3>Student Overview</h3>
                    <div class="chart-wrapper">
                        <canvas id="studentChart"></canvas>
                    </div>
                </div>
                <div class="chart-container col-12 col-lg-6">
                    <h3>Active vs. Inactive Students</h3>
                    <div class="chart-wrapper">
                        <canvas id="activeInactiveChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Dues Table -->
            <div class="dues-container">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="m-0">This Month Dues</h3>
                    <div>
                        <button id="sendAllRemindersBtn" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-paper-plane"></i> Send All Reminders
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="dues-table table table-dark table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Due Date</th>
                                <th>ID/Name</th>
                                <th>Total Amount</th>
                                <th>Discount</th>
                                <th>Total Payment</th>
                                <th>Amount Paid</th>
                                <th>Balance</th>
                                <th>Contact</th>
                                <th>Last Reminder</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" class="text-center">
                                    <i class="fas fa-spinner fa-spin"></i> Loading dues...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="Scripts/admin_dashboard.js"></script>
    <!-- Bootstrap 5 JS bundle (Popper included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html> 