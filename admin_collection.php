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
    <title>D'MARSIANS Taekwondo System - Admin Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="Styles/admin_collection.css">
    <link rel="stylesheet" href="Styles/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Source+Serif+Pro:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles/typography.css">
    <style>
        .transaction-table table th:first-child,
        .transaction-table table td:first-child {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Sidebar -->
        <?php $active = 'collection'; include 'partials/admin_sidebar.php'; ?>

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
            <div class="collection-header">
                <h1>Collection</h1>
            </div>

            <!-- Collection Stats -->
            <div class="collection-stats">
                <div class="stat-box monthly">
                    <h3>Monthly Collected Amount</h3>
                    <div class="amount">₱135,654</div>
                </div>
                <div class="stat-box yearly">
                    <h3>Yearly Collected Amount</h3>
                    <div class="amount">₱433,076</div>
                </div>
            </div>

            <!-- Payment Transaction History -->
            <div class="transaction-section">
                <h2>Payment Transaction History</h2>
                <div class="transaction-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Total Paid</th>
                                <th>Amount Paid</th>
                                <th>Payment Type</th>
                                <th>Discount</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="transactionTableBody">
                            <!-- Table rows will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Collection Trend Chart -->
            <div class="trend-section">
                <div class="trend-header">
                    <h2>Collection Trend</h2>
                    <select id="trendPeriod">
                        <option value="yearly">Yearly</option>
                        <option value="monthly">Monthly</option>
                        <option value="weekly">Weekly</option>
                    </select>
                </div>
            <div class="chart-container" style="position: relative;">
                <canvas id="collectionTrendChart"></canvas>
                <button id="exportChartBtn" type="button" class="btn btn-sm btn-outline-success" title="Export chart" style="position: absolute; right: 12px; bottom: 12px; z-index: 1;">
                    <i class="fa-solid fa-download"></i> Export
                </button>
            </div>
            </div>
        </div>
    </div>

    <script src="Scripts/admin_collection.js"></script>
    <!-- Bootstrap 5 JS bundle (Popper included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
    // Mobile-safe dropdown: avoid touch+click double-trigger
    (function(){
        const dropdown = document.querySelector('.sidebar .dropdown');
        const toggle = dropdown ? dropdown.querySelector('.dropdown-toggle') : null;
        if(!dropdown || !toggle) return;

        function open(){ dropdown.classList.add('open'); }
        function close(){ dropdown.classList.remove('open'); }

        let touched = false;
        toggle.addEventListener('click', function(e){
            if (touched) { e.preventDefault(); touched = false; return; }
            e.preventDefault();
            dropdown.classList.toggle('open');
        });
        toggle.addEventListener('touchstart', function(e){
            e.preventDefault();
            touched = true;
            open();
            setTimeout(function(){ touched = false; }, 300);
        }, {passive:false});

        dropdown.addEventListener('mouseenter', open);
        dropdown.addEventListener('mouseleave', close);
        document.addEventListener('click', function(e){ if(!dropdown.contains(e.target)) close(); });
    })();
    </script>
    <script>
    // Export the Collection Trend chart as PNG
    document.addEventListener('DOMContentLoaded', function(){
        const exportBtn = document.getElementById('exportChartBtn');
        const canvas = document.getElementById('collectionTrendChart');
        if(!exportBtn || !canvas) return;
        exportBtn.addEventListener('click', function(){
            try {
                const dataUrl = canvas.toDataURL('image/png', 1.0);
                const link = document.createElement('a');
                const date = new Date().toISOString().slice(0,10);
                link.download = 'collection-trend-' + date + '.png';
                link.href = dataUrl;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (err) {
                console.error(err);
                alert('Unable to export the chart.');
            }
        });
    });
    </script>
</body>
</html> 