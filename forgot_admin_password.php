<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | D'Marsians Taekwondo Gym</title>
    <link rel="icon" type="image/png" href="Picture/Logo2.png">
    <link rel="apple-touch-icon" href="Picture/Logo2.png">
    <link rel="stylesheet" href="Styles/admin_login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Source+Serif+Pro:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles/typography.css">
    <style>
        details.otp-help {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(0, 255, 106, 0.2);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            margin-top: 16px;
            text-align: left;
        }
        details.otp-help summary {
            color: #00ff6a;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            outline: none;
            display: flex;
            align-items: center;
            list-style: none; /* remove default arrow */
        }
        details.otp-help summary::-webkit-details-marker {
            display: none;
        }
        details.otp-help summary::before {
            content: '►';
            font-size: 10px;
            margin-right: 8px;
            transition: transform 0.2s ease;
        }
        details.otp-help[open] summary::before {
            transform: rotate(90deg);
        }
        details.otp-help .help-content {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid rgba(0, 255, 106, 0.2);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <img src="Picture/Logo2.png" alt="Logo">
            </div>
            <h2>Forgot Password</h2>
            <?php if (isset($_GET['error'])): ?>
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
            <div style="margin-top:20px;display:flex;flex-direction:column;gap:10px;">
                <a href="admin_verify_otp.php" class="secondary-btn">Already have an OTP? Verify here</a>
                <a href="admin_login.php" class="secondary-btn">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>


