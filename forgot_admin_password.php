<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Super Admin</title>
    <link rel="stylesheet" href="Styles/admin_login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Source+Serif+Pro:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles/typography.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <img src="Picture/Logo2.png" alt="Logo">
            </div>
            <h2>Forgot Password</h2>
            <?php if (isset($_GET['sent'])): ?>
                <div style="background:#e8f5e9;border:1px solid #4caf50;border-radius:4px;padding:12px;margin-bottom:16px">
                    <p class="error-message" style="color:#2e7d32;margin:0 0 8px 0">
                        <strong>✓ OTP Request Processed</strong>
                    </p>
                    <p style="font-size:13px;color:#555;margin:0">
                        If the account exists, an OTP has been sent to its registered email address.
                    </p>
                </div>
                <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:4px;padding:12px;margin-bottom:16px">
                    <p style="font-size:12px;color:#856404;margin:0 0 6px 0">
                        <strong>📧 Didn't receive the email?</strong>
                    </p>
                    <ul style="font-size:11px;color:#856404;margin:0;padding-left:20px;text-align:left">
                        <li>Check your <strong>spam/junk folder</strong> - emails may be filtered</li>
                        <li>Wait a few minutes - email delivery can take 2-5 minutes</li>
                        <li>Verify you entered the correct <strong>email or username</strong></li>
                        <li>Check if you have multiple email accounts</li>
                    </ul>
                    <p style="font-size:11px;color:#856404;margin:8px 0 0 0">
                        <strong>Note:</strong> You can request a new OTP after 60 seconds if needed.
                    </p>
                </div>
            <?php elseif (isset($_GET['error'])): ?>
                <div style="background:#ffebee;border:1px solid #f44336;border-radius:4px;padding:12px;margin-bottom:16px">
                    <p class="error-message" style="margin:0">Something went wrong. Please try again.</p>
                </div>
            <?php endif; ?>
            <form action="admin_send_otp.php" method="POST" id="otpForm">
                <div class="input-group">
                    <input id="identifier" type="text" name="identifier" required autocomplete="username" placeholder=" ">
                    <label>Email or Username</label>
                </div>
                <button type="submit" class="login-btn" id="submitBtn">
                    <span id="btnText">Send OTP</span>
                    <span id="btnLoader" style="display:none">Sending...</span>
                </button>
            </form>
            <script>
                // Add loading state to form submission
                document.getElementById('otpForm').addEventListener('submit', function(e) {
                    const btn = document.getElementById('submitBtn');
                    const btnText = document.getElementById('btnText');
                    const btnLoader = document.getElementById('btnLoader');
                    const identifier = document.getElementById('identifier').value.trim();
                    
                    // Validate input
                    if (!identifier) {
                        e.preventDefault();
                        alert('Please enter your email or username');
                        return false;
                    }
                    
                    // Show loading state
                    btn.disabled = true;
                    btnText.style.display = 'none';
                    btnLoader.style.display = 'inline';
                    
                    // Log for debugging (remove in production)
                    console.log('Form submitting to:', this.action);
                    console.log('Identifier:', identifier);
                    
                    // Allow form to submit normally
                    return true;
                });
                
                // Debug: Check if form exists
                window.addEventListener('DOMContentLoaded', function() {
                    const form = document.getElementById('otpForm');
                    const btn = document.getElementById('submitBtn');
                    if (!form) {
                        console.error('OTP form not found!');
                    } else {
                        console.log('OTP form found, action:', form.action);
                    }
                    if (!btn) {
                        console.error('Submit button not found!');
                    }
                });
            </script>
            <div style="margin-top:12px;text-align:center">
                <a href="admin_verify_otp.php" style="text-decoration:none;color:#1976d2">Already have an OTP? Verify here</a>
            </div>
            <div style="margin-top:8px;text-align:center">
                <a href="otp_diagnostic.php" style="text-decoration:none;color:#ff9800;font-size:13px">🔍 OTP Email Diagnostic Tool</a>
            </div>
            <div style="margin-top:8px;text-align:center">
                <a href="admin_login.php" style="text-decoration:none;color:#555">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>


