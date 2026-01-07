<?php
/**
 * Quick OTP Email Test Script
 * This script tests if OTP emails can be sent successfully
 * Access: http://localhost/Dmarsian/test_otp_email.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Email Test - D'MARSIANS</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; border: 1px solid #ddd; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; border: 1px solid #ddd; }
        .btn { padding: 10px 20px; background: #5DD62C; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #4bc01f; }
        .alert { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    </style>
</head>
<body>
    <h1>🔍 OTP Email Test Tool</h1>
    
    <div class="box">
        <h2>1. Configuration Check</h2>
        <?php
        $apiKey = defined('SMTP2GO_API_KEY') ? SMTP2GO_API_KEY : getenv('SMTP2GO_API_KEY');
        $senderEmail = defined('SMTP2GO_SENDER_EMAIL') ? SMTP2GO_SENDER_EMAIL : getenv('SMTP2GO_SENDER_EMAIL');
        $senderName = defined('SMTP2GO_SENDER_NAME') ? SMTP2GO_SENDER_NAME : getenv('SMTP2GO_SENDER_NAME');
        
        $configOk = true;
        
        echo '<table style="width:100%; border-collapse: collapse;">';
        echo '<tr style="background:#f8f9fa;"><th style="padding:10px; text-align:left; border:1px solid #ddd;">Variable</th><th style="padding:10px; text-align:left; border:1px solid #ddd;">Status</th><th style="padding:10px; text-align:left; border:1px solid #ddd;">Value</th></tr>';
        
        // Check API Key
        echo '<tr>';
        echo '<td style="padding:10px; border:1px solid #ddd;"><code>SMTP2GO_API_KEY</code></td>';
        if (empty($apiKey) || $apiKey === 'your_smtp2go_api_key_here') {
            echo '<td style="padding:10px; border:1px solid #ddd;"><span class="status-error">✗ NOT SET</span></td>';
            echo '<td style="padding:10px; border:1px solid #ddd;"><em>Missing</em></td>';
            $configOk = false;
        } else {
            $masked = substr($apiKey, 0, 8) . '...' . substr($apiKey, -4);
            echo '<td style="padding:10px; border:1px solid #ddd;"><span class="status-ok">✓ SET</span></td>';
            echo '<td style="padding:10px; border:1px solid #ddd;"><code>' . htmlspecialchars($masked) . '</code></td>';
        }
        echo '</tr>';
        
        // Check Sender Email
        echo '<tr>';
        echo '<td style="padding:10px; border:1px solid #ddd;"><code>SMTP2GO_SENDER_EMAIL</code></td>';
        if (empty($senderEmail) || $senderEmail === 'your_email@example.com') {
            echo '<td style="padding:10px; border:1px solid #ddd;"><span class="status-error">✗ NOT SET</span></td>';
            echo '<td style="padding:10px; border:1px solid #ddd;"><em>Missing</em></td>';
            $configOk = false;
        } else {
            echo '<td style="padding:10px; border:1px solid #ddd;"><span class="status-ok">✓ SET</span></td>';
            echo '<td style="padding:10px; border:1px solid #ddd;"><code>' . htmlspecialchars($senderEmail) . '</code></td>';
        }
        echo '</tr>';
        
        // Check Sender Name
        echo '<tr>';
        echo '<td style="padding:10px; border:1px solid #ddd;"><code>SMTP2GO_SENDER_NAME</code></td>';
        if (empty($senderName)) {
            echo '<td style="padding:10px; border:1px solid #ddd;"><span class="status-warning">⚠ Not Set (using default)</span></td>';
            echo '<td style="padding:10px; border:1px solid #ddd;"><em>D\'Marsians Taekwondo Gym</em></td>';
        } else {
            echo '<td style="padding:10px; border:1px solid #ddd;"><span class="status-ok">✓ SET</span></td>';
            echo '<td style="padding:10px; border:1px solid #ddd;"><code>' . htmlspecialchars($senderName) . '</code></td>';
        }
        echo '</tr>';
        
        echo '</table>';
        
        if (!$configOk) {
            echo '<div class="alert alert-danger">';
            echo '<strong>❌ Configuration Missing!</strong><br>';
            echo 'Please check your .env file and ensure SMTP2GO credentials are set.';
            echo '</div>';
        } else {
            echo '<div class="alert alert-success">';
            echo '<strong>✓ Configuration looks good!</strong>';
            echo '</div>';
        }
        ?>
    </div>
    
    <?php if ($configOk): ?>
    <div class="box">
        <h2>2. Send Test Email</h2>
        <form method="POST">
            <p>
                <label><strong>Test Email Address:</strong></label><br>
                <input type="email" name="test_email" value="helmandacuma5@gmail.com" style="width: 300px; padding: 8px; margin-top: 5px;" required>
            </p>
            <button type="submit" name="send_test" class="btn">Send Test OTP Email</button>
        </form>
        
        <?php
        if (isset($_POST['send_test']) && isset($_POST['test_email'])) {
            $testEmail = filter_var($_POST['test_email'], FILTER_SANITIZE_EMAIL);
            
            if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                echo '<div class="alert alert-danger">Invalid email address format.</div>';
            } else {
                echo '<h3>Test Results:</h3>';
                
                $testOtp = strval(random_int(100000, 999999));
                
                $payload = [
                    'api_key' => $apiKey,
                    'to' => [$testEmail],
                    'sender' => $senderEmail,
                    'sender_name' => $senderName ?: "D'Marsians Taekwondo Gym",
                    'subject' => 'Test OTP Email - Diagnostic',
                    'text_body' => "Your test OTP code is: $testOtp\nThis code will expire in 5 minutes.",
                    'html_body' => '<div style="font-family:Arial,Helvetica,sans-serif;line-height:1.5;color:#222">'
                                 . '<h2>Test OTP Email</h2>'
                                 . '<p>Your test OTP code is: <strong style="font-size:18px">' . htmlspecialchars($testOtp) . '</strong></p>'
                                 . '<p>This code will expire in 5 minutes.</p>'
                                 . '</div>'
                ];
                
                echo '<div class="alert alert-info">';
                echo '<strong>Payload being sent:</strong><br>';
                echo '<pre>' . htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . '</pre>';
                echo '</div>';
                
                $url = 'https://api.smtp2go.com/v3/email/send';
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                $curlErrNo = curl_errno($ch);
                $curlInfo = curl_getinfo($ch);
                curl_close($ch);
                
                echo '<div class="alert alert-info">';
                echo '<strong>HTTP Status Code:</strong> ' . $httpCode . '<br>';
                
                if ($curlErr) {
                    echo '<strong class="status-error">cURL Error:</strong> ' . htmlspecialchars($curlErr) . ' (Error #' . $curlErrNo . ')<br>';
                }
                echo '</div>';
                
                if ($response) {
                    $decoded = json_decode($response, true);
                    if ($decoded) {
                        echo '<h4>API Response:</h4>';
                        echo '<pre>' . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . '</pre>';
                        
                        if ($httpCode >= 200 && $httpCode < 300) {
                            if (isset($decoded['data']['message_id'])) {
                                echo '<div class="alert alert-success">';
                                echo '<strong>✓ Email sent successfully!</strong><br>';
                                echo 'Message ID: ' . htmlspecialchars($decoded['data']['message_id']) . '<br>';
                                echo 'Check your inbox (and spam folder) at: <strong>' . htmlspecialchars($testEmail) . '</strong><br>';
                                echo 'Test OTP Code: <strong style="font-size: 20px; color: #5DD62C;">' . htmlspecialchars($testOtp) . '</strong>';
                                echo '</div>';
                                
                                echo '<div class="alert alert-info">';
                                echo '<strong>Next Steps:</strong><br>';
                                echo '1. Check your Gmail inbox and spam folder<br>';
                                echo '2. If you received the test email, the configuration is working<br>';
                                echo '3. If you still don\'t receive OTP emails, check:<br>';
                                echo '   - SMTP2GO Activity Dashboard: <a href="https://app.smtp2go.com/reports/activity" target="_blank">https://app.smtp2go.com/reports/activity</a><br>';
                                echo '   - Verify sender email is verified in SMTP2GO<br>';
                                echo '   - Check if Gmail is blocking the emails';
                                echo '</div>';
                            } else {
                                echo '<div class="alert alert-warning">';
                                echo 'Response received but no message_id. Check errors above.';
                                if (isset($decoded['errors']) && is_array($decoded['errors'])) {
                                    echo '<br><strong>Errors:</strong><br>';
                                    foreach ($decoded['errors'] as $error) {
                                        echo '- ' . htmlspecialchars($error['message'] ?? 'Unknown error') . '<br>';
                                    }
                                }
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="alert alert-danger">';
                            echo '<strong>✗ Email send failed</strong><br>';
                            if (isset($decoded['errors']) && is_array($decoded['errors'])) {
                                foreach ($decoded['errors'] as $error) {
                                    echo '- ' . htmlspecialchars($error['message'] ?? 'Unknown error') . '<br>';
                                }
                            }
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="alert alert-danger">';
                        echo 'Invalid JSON response:<br>';
                        echo '<pre>' . htmlspecialchars(substr($response, 0, 500)) . '</pre>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">No response from SMTP2GO API</div>';
                }
            }
        }
        ?>
    </div>
    <?php endif; ?>
    
    <div class="box">
        <h2>3. Troubleshooting Tips</h2>
        <ul>
            <li><strong>Check Gmail Spam Folder:</strong> Sometimes emails are filtered as spam</li>
            <li><strong>Wait 2-5 minutes:</strong> Email delivery can take time</li>
            <li><strong>Verify Sender Email:</strong> Make sure <code><?php echo htmlspecialchars($senderEmail); ?></code> is verified in your SMTP2GO account</li>
            <li><strong>Check SMTP2GO Activity:</strong> Visit <a href="https://app.smtp2go.com/reports/activity" target="_blank">SMTP2GO Activity Dashboard</a> to see if emails are being sent</li>
            <li><strong>Check PHP Error Logs:</strong> Look in XAMPP error logs for any error messages</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="forgot_admin_password.php" class="btn">Go to Forgot Password</a>
        <a href="otp_diagnostic.php" class="btn" style="background: #6c757d;">Full Diagnostic Tool</a>
    </div>
</body>
</html>







