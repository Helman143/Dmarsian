<?php
/**
 * Email Configuration Test Page
 * This page helps diagnose email sending issues
 * DELETE THIS FILE AFTER TESTING IN PRODUCTION
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin','super_admin'])) {
    header("Location: index.php");
    exit();
}

require_once 'db_connect.php';
require_once 'config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Configuration Test - D'MARSIANS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Email Configuration Test</h1>
        <p class="text-muted">This page helps diagnose email sending issues with SMTP2GO</p>
        
        <div class="card mt-4">
            <div class="card-header">
                <h3>Configuration Check</h3>
            </div>
            <div class="card-body">
                <?php
                $configOk = true;
                
                // Check SMTP2GO_API_KEY
                echo '<div class="mb-3">';
                echo '<strong>SMTP2GO_API_KEY:</strong> ';
                if (empty(SMTP2GO_API_KEY) || SMTP2GO_API_KEY === 'your_smtp2go_api_key_here') {
                    echo '<span class="status-error">✗ NOT CONFIGURED</span>';
                    echo '<p class="text-danger">Please set SMTP2GO_API_KEY in your .env file or environment variables.</p>';
                    $configOk = false;
                } else {
                    $masked = substr(SMTP2GO_API_KEY, 0, 8) . '...' . substr(SMTP2GO_API_KEY, -4);
                    echo '<span class="status-ok">✓ Configured</span> (' . htmlspecialchars($masked) . ')';
                }
                echo '</div>';
                
                // Check SMTP2GO_SENDER_EMAIL
                echo '<div class="mb-3">';
                echo '<strong>SMTP2GO_SENDER_EMAIL:</strong> ';
                if (empty(SMTP2GO_SENDER_EMAIL) || SMTP2GO_SENDER_EMAIL === 'your_email@example.com') {
                    echo '<span class="status-error">✗ NOT CONFIGURED</span>';
                    echo '<p class="text-danger">Please set SMTP2GO_SENDER_EMAIL in your .env file or environment variables.</p>';
                    $configOk = false;
                } else {
                    echo '<span class="status-ok">✓ Configured</span> (' . htmlspecialchars(SMTP2GO_SENDER_EMAIL) . ')';
                    // Check if email is verified in SMTP2GO (we can't verify this, but we can check format)
                    if (!filter_var(SMTP2GO_SENDER_EMAIL, FILTER_VALIDATE_EMAIL)) {
                        echo '<p class="text-warning">⚠ Warning: Email format appears invalid</p>';
                    }
                }
                echo '</div>';
                
                // Check SMTP2GO_SENDER_NAME
                echo '<div class="mb-3">';
                echo '<strong>SMTP2GO_SENDER_NAME:</strong> ';
                if (empty(SMTP2GO_SENDER_NAME)) {
                    echo '<span class="status-warning">⚠ Not set (using default)</span>';
                } else {
                    echo '<span class="status-ok">✓ Configured</span> (' . htmlspecialchars(SMTP2GO_SENDER_NAME) . ')';
                }
                echo '</div>';
                
                // Check ADMIN_BCC_EMAIL
                echo '<div class="mb-3">';
                echo '<strong>ADMIN_BCC_EMAIL:</strong> ';
                if (empty(ADMIN_BCC_EMAIL)) {
                    echo '<span class="status-warning">⚠ Not set (optional)</span>';
                } else {
                    echo '<span class="status-ok">✓ Configured</span> (' . htmlspecialchars(ADMIN_BCC_EMAIL) . ')';
                }
                echo '</div>';
                
                // Check cURL extension
                echo '<div class="mb-3">';
                echo '<strong>cURL Extension:</strong> ';
                if (!function_exists('curl_init')) {
                    echo '<span class="status-error">✗ NOT AVAILABLE</span>';
                    echo '<p class="text-danger">cURL extension is required for SMTP2GO API calls.</p>';
                    $configOk = false;
                } else {
                    echo '<span class="status-ok">✓ Available</span>';
                }
                echo '</div>';
                ?>
            </div>
        </div>
        
        <?php if ($configOk): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Test Email Send</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="test_email" class="form-label">Test Email Address</label>
                        <input type="email" class="form-control" id="test_email" name="test_email" 
                               placeholder="your-email@gmail.com" required>
                        <small class="form-text text-muted">Enter your Gmail address to test email delivery</small>
                    </div>
                    <button type="submit" class="btn btn-primary" name="send_test">Send Test Email</button>
                </form>
                
                <?php
                if (isset($_POST['send_test']) && isset($_POST['test_email'])) {
                    $testEmail = filter_var($_POST['test_email'], FILTER_SANITIZE_EMAIL);
                    
                    if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                        echo '<div class="alert alert-danger mt-3">Invalid email address format.</div>';
                    } else {
                        echo '<div class="mt-3">';
                        echo '<h4>Test Results:</h4>';
                        
                        // Send test email using SMTP2GO
                        $payload = [
                            'api_key' => SMTP2GO_API_KEY,
                            'to' => [$testEmail],
                            'sender' => SMTP2GO_SENDER_EMAIL,
                            'sender_name' => SMTP2GO_SENDER_NAME ?: "D'Marsians Taekwondo Gym",
                            'subject' => 'Test Email from D\'MARSIANS System',
                            'text_body' => "This is a test email from the D'MARSIANS Taekwondo System.\n\nIf you received this, your email configuration is working correctly!",
                            'html_body' => '<div style="font-family:Arial,Helvetica,sans-serif;line-height:1.5;color:#222">'
                                         . '<h2>Test Email</h2>'
                                         . '<p>This is a test email from the <strong>D\'MARSIANS Taekwondo System</strong>.</p>'
                                         . '<p>If you received this, your email configuration is working correctly!</p>'
                                         . '</div>'
                        ];
                        
                        $url = 'https://api.smtp2go.com/v3/email/send';
                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        
                        $response = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $curlErr = curl_error($ch);
                        curl_close($ch);
                        
                        echo '<div class="alert alert-info">';
                        echo '<strong>HTTP Status Code:</strong> ' . $httpCode . '<br>';
                        
                        if ($curlErr) {
                            echo '<strong class="status-error">cURL Error:</strong> ' . htmlspecialchars($curlErr) . '<br>';
                        }
                        
                        if ($response) {
                            $decoded = json_decode($response, true);
                            if ($decoded) {
                                echo '<strong>API Response:</strong><br>';
                                echo '<pre>' . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT)) . '</pre>';
                                
                                if ($httpCode >= 200 && $httpCode < 300) {
                                    if (isset($decoded['data']['message_id'])) {
                                        echo '<div class="alert alert-success mt-3">';
                                        echo '<strong>✓ Email sent successfully!</strong><br>';
                                        echo 'Message ID: ' . htmlspecialchars($decoded['data']['message_id']) . '<br>';
                                        echo 'Please check your inbox (and spam folder) at: <strong>' . htmlspecialchars($testEmail) . '</strong>';
                                        echo '</div>';
                                        
                                        echo '<div class="alert alert-warning mt-3">';
                                        echo '<strong>Important Notes:</strong><br>';
                                        echo '1. If you don\'t see the email, check your <strong>Spam/Junk folder</strong><br>';
                                        echo '2. Gmail may take a few minutes to deliver emails<br>';
                                        echo '3. Make sure your SMTP2GO sender email is verified in your SMTP2GO account<br>';
                                        echo '4. Check SMTP2GO dashboard for delivery status';
                                        echo '</div>';
                                    } else {
                                        echo '<div class="alert alert-warning mt-3">';
                                        echo 'Response received but no message_id. Check SMTP2GO account status.';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<div class="alert alert-danger mt-3">';
                                    echo '<strong>✗ Email send failed</strong><br>';
                                    if (isset($decoded['errors']) && is_array($decoded['errors'])) {
                                        foreach ($decoded['errors'] as $error) {
                                            echo '- ' . htmlspecialchars($error['message'] ?? 'Unknown error') . '<br>';
                                        }
                                    }
                                    echo '</div>';
                                }
                            } else {
                                echo '<strong>Raw Response:</strong><br>';
                                echo '<pre>' . htmlspecialchars(substr($response, 0, 1000)) . '</pre>';
                            }
                        } else {
                            echo '<div class="alert alert-danger mt-3">No response from SMTP2GO API</div>';
                        }
                        
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-danger mt-4">
            <strong>Configuration incomplete!</strong> Please fix the configuration issues above before testing email sending.
        </div>
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="card-header">
                <h3>Troubleshooting Tips</h3>
            </div>
            <div class="card-body">
                <h5>If emails are not being received:</h5>
                <ol>
                    <li><strong>Check Spam/Junk folder</strong> - Gmail often filters emails from new senders</li>
                    <li><strong>Verify SMTP2GO sender email</strong> - Log into your SMTP2GO account and verify the sender email is verified</li>
                    <li><strong>Check SMTP2GO dashboard</strong> - View email logs and delivery status</li>
                    <li><strong>Check server error logs</strong> - Look for detailed error messages in PHP error logs</li>
                    <li><strong>Verify API key</strong> - Ensure your SMTP2GO API key is valid and active</li>
                    <li><strong>Check email format</strong> - Ensure student/parent emails are valid email addresses</li>
                </ol>
                
                <h5 class="mt-4">Common Issues:</h5>
                <ul>
                    <li><strong>"API key is not configured"</strong> - Set SMTP2GO_API_KEY in .env file</li>
                    <li><strong>"Sender email is not configured"</strong> - Set SMTP2GO_SENDER_EMAIL in .env file</li>
                    <li><strong>"No valid recipient emails"</strong> - Check that students have valid email addresses in the database</li>
                    <li><strong>Emails go to spam</strong> - This is normal for new senders. Ask recipients to mark as "Not Spam"</li>
                </ul>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>





