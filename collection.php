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
    <title>D'MARSIANS Taekwondo System - Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="Styles/collection.css">
    <link rel="stylesheet" href="Styles/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Source+Serif+Pro:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles/typography.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <?php $active = 'collection'; include 'partials/sidebar.php'; ?>

        <!-- Sidebar Backdrop (Mobile) -->
        <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

        <!-- Mobile topbar with toggle button -->
        <div class="mobile-topbar d-flex d-md-none align-items-center justify-content-between p-2">
            <button id="mobileSidebarToggle" class="btn btn-sm btn-outline-success" type="button" aria-label="Toggle sidebar">
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
                    <div class="amount"></div>
                </div>
                <div class="stat-box yearly">
                    <h3>Yearly Collected Amount</h3>
                    <div class="amount"></div>
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
                    <button id="exportChartBtn" type="button" class="btn btn-sm btn-outline-success" title="Export chart"
                        style="position: absolute; right: 12px; bottom: 12px; z-index: 1;">
                        <i class="fa-solid fa-download"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="Scripts/collection.js"></script>
    <!-- Bootstrap 5 JS bundle (Popper included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
    // Mobile-safe dropdown: avoid touch+click double-trigger, mirror admin_payment sidebar
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
    // Export the Payment Transaction History and Summary Stats to CSV (Excel compatible)
    document.addEventListener('DOMContentLoaded', function(){
        const exportBtn = document.getElementById('exportChartBtn');
        if(!exportBtn) return;
        
        exportBtn.addEventListener('click', function(){
            try {
                // Ensure paymentsData is available directly or via window
                // 'paymentsData' is defined in Scripts/collection.js in the global scope
                const dataToExport = (typeof paymentsData !== 'undefined') ? paymentsData : [];
                
                if (!dataToExport || dataToExport.length === 0) {
                    alert('No payment transaction history available to export.');
                    return;
                }
                
                // Calculate Monthly and Yearly Stats (Mirroring logic in collection.js)
                const now = new Date();
                let monthlyTotal = 0;
                let yearlyTotal = 0;
                
                dataToExport.forEach(payment => {
                    if (payment.date_paid) {
                        const paidDate = new Date(payment.date_paid);
                        if (!isNaN(paidDate.getTime()) && paidDate.getFullYear() === now.getFullYear()) {
                            yearlyTotal += parseFloat(payment.amount_paid || 0);
                            if (paidDate.getMonth() === now.getMonth()) {
                                monthlyTotal += parseFloat(payment.amount_paid || 0);
                            }
                        }
                    }
                });

                // Construct CSV content with BOM for Excel UTF-8 recognition
                let csvContent = "\uFEFF";
                
                // Add Summary Stats at the top
                csvContent += "COLLECTION SUMMARY\n";
                csvContent += `Monthly Collected Amount (PHP),${monthlyTotal.toFixed(2)}\n`;
                csvContent += `Yearly Collected Amount (PHP),${yearlyTotal.toFixed(2)}\n`;
                csvContent += "\n"; // Spacer line
                
                // Add Table Headers
                csvContent += "PAYMENT TRANSACTION HISTORY\n";
                csvContent += "Transaction ID,Date,Reference,Total Paid,Amount Paid,Payment Type,Discount,Total Amount,Status\n"; 
                
                // Add Table Data
                dataToExport.forEach(payment => {
                    const amountPaid = parseFloat(payment.amount_paid || 0);
                    const discount = parseFloat(payment.discount || 0);
                    const totalAmount = amountPaid - discount;
                    
                    const escape = (val) => {
                        if (val === null || val === undefined) return '';
                        const str = String(val);
                        if (str.includes(',') || str.includes('"') || str.includes('\n')) {
                            return `"${str.replace(/"/g, '""')}"`;
                        }
                        return str;
                    };

                    csvContent += [
                        escape(payment.id),
                        escape(payment.date_paid),
                        escape(payment.jeja_no),
                        amountPaid.toFixed(2),
                        amountPaid.toFixed(2),
                        escape(payment.payment_type),
                        discount.toFixed(2),
                        totalAmount.toFixed(2),
                        escape(payment.status)
                    ].join(',') + "\n";
                });
                
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                const date = new Date().toISOString().slice(0,10);
                
                link.setAttribute('href', url);
                link.setAttribute('download', `collection_report_${date}.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
                
            } catch (err) {
                console.error(err);
                alert('Unable to export the report.');
            }
        });
    });
    </script>
    <script src="Scripts/sidebar.js?v=2"></script>
</body>
</html> 