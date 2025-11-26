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
    <title>Payment - D'MARSIANS Taekwondo System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="Styles/dashboard.css">
    <link rel="stylesheet" href="Styles/payment.css">
    <link rel="stylesheet" href="Styles/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Source+Serif+Pro:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles/typography.css">
</head>
<body>
    <div class="container-fluid">
        <!-- Sidebar -->
        <?php $active = 'payment'; include 'partials/sidebar.php'; ?>

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
            <!-- Payment Form Section -->
            <div class="payment-form-section">
                <h2>PAYMENT</h2>
                <form id="paymentForm" class="payment-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="jeja_no">STD No.</label>
                            <input type="text" id="jeja_no" name="jeja_no" required>
                        </div>
                        <div class="form-group">
                            <label for="payment_type">Payment Type</label>
                            <select id="payment_type" name="payment_type" required>
                                <option value="">Select Payment Type</option>
                                <option value="Full Payment">Full Payment</option>
                                <option value="Half Payment">Half Payment</option>
                                <option value="Advance Payment">Advance Payment</option>
                                <option value="Postponed Payment">Postponed Payment</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label for="date_paid">Date Paid</label>
                            <input type="date" id="date_paid" name="date_paid" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="amount_paid">Amount Paid</label>
                            <input type="number" id="amount_paid" name="amount_paid" step="0.01" value="0.00" required>
                        </div>
                        <div class="form-group">
                            <label for="discount">Discount</label>
                            <input type="number" id="discount" name="discount" step="0.01" value="0.00" readonly>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Freeze">Freeze</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i> SAVE
                        </button>
                    </div>
                </form>
            </div>

            <!-- Payment Records Section -->
            <div class="payment-records-section">
                <div class="records-header">
                    <h3>Payments Records</h3>
                    <div class="search-container">
                        <input type="text" id="searchPayment" placeholder="Search payments...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
                <div class="table-container">
                    <table class="payment-table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>STD No.</th>
                                <th>Fullname</th>
                                <th>Date Paid</th>
                                <th>Amount Paid</th>
                                <th>Payment Type</th>
                                <th>Discount</th>
                                <th>Date Enrolled</th>
                                <th>Balance</th>
                                <th>Status</th>
                                
                            </tr>
                        </thead>
                        <tbody id="paymentTableBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Non Discount Students Table -->
            <div class="payment-records-section">
                <div class="records-header">
                    <h3>Non Discount Students</h3>
                    <div class="search-container">
                        <input type="text" id="searchNonDiscount" placeholder="Search non-discount students...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
                <div class="table-container">
                    <table class="payment-table">
                        <thead>
                            <tr>
                                <th>STD No.</th>
                                <th>Fullname</th>
                                <th>Discount</th>
                                <th>Date Enrolled</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="nonDiscountTableBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Discount Students Table -->
            <div class="payment-records-section">
                <div class="records-header">
                    <h3>Discount Students</h3>
                    <div class="search-container">
                        <input type="text" id="searchDiscount" placeholder="Search discount students...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
                <div class="table-container">
                    <table class="payment-table">
                        <thead>
                            <tr>
                                <th>STD No.</th>
                                <th>Fullname</th>
                                <th>Discount</th>
                                <th>Date Enrolled</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="discountTableBody"></tbody>
                    </table>
                </div>
            </div>

            
        </div>
    </div>

    <script src="Scripts/payment.js"></script>
    <!-- Bootstrap 5 JS bundle (Popper included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
    // Enable hover-like behavior on touch: open dropdown on touchstart/mouseenter, close on mouseleave
    (function(){
        const dropdown = document.querySelector('.sidebar .dropdown');
        const toggle = dropdown ? dropdown.querySelector('.dropdown-toggle') : null;
        if(!dropdown || !toggle) return;

        function open(){ dropdown.classList.add('open'); }
        function close(){ dropdown.classList.remove('open'); }

        toggle.addEventListener('click', function(e){
            // Prevent navigation and just toggle open state on click
            e.preventDefault();
            dropdown.classList.toggle('open');
        });
        toggle.addEventListener('touchstart', function(e){ e.preventDefault(); open(); }, {passive:false});
        dropdown.addEventListener('mouseenter', open);
        dropdown.addEventListener('mouseleave', close);
        // Close when clicking outside
        document.addEventListener('click', function(e){ if(!dropdown.contains(e.target)) close(); });
    })();
    </script>
    <div id="paymentMessage" style="display:none;"></div>
</body>
</html> 