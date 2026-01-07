<?php
/**
 * OTP Email Diagnostic Tool
 * This tool helps diagnose why OTP emails are not being received
 * Access: https://your-app-url.ondigitalocean.app/otp_diagnostic.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'db_connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Email Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; border: 1px solid #ddd; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .status-info { color: #17a2b8; font-weight: bold; }
        h1 { color: #333; }
        h2 { color: #555; border-bottom: 2px solid #5DD62C; padding-bottom: 10px; }
        h3 { color: #666; margin-top: 20px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #5DD62C; color: white; }
        tr:hover { background: #f8f9fa; }
        .btn { padding: 10px 20px; background: #5DD62C; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #4bc01f; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .alert { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>🔍 OTP Email Diagnostic Tool</h1>
    
    <div class="box">
        <h2>1. Environment Variables Check</h2>
        <?php
        $apiKey = defined('SMTP2GO_API_KEY') ? SMTP2GO_API_KEY : getenv('SMTP2GO_API_KEY');
        $senderEmail = defined('SMTP2GO_SENDER_EMAIL') ? SMTP2GO_SENDER_EMAIL : getenv('SMTP2GO_SENDER_EMAIL');
        $senderName = defined('SMTP2GO_SENDER_NAME') ? SMTP2GO_SENDER_NAME : getenv('SMTP2GO_SENDER_NAME');
        
        $configOk = true;
        
        echo '<table>';
        echo '<tr><th>Variable</th><th>Status</th><th>Value</th><th>Source</th></tr>';
        
        // Check API Key
        echo '<tr>';
        echo '<td><code>SMTP2GO_API_KEY</code></td>';
        if (empty($apiKey) || $apiKey === 'your_smtp2go_api_key_here') {
            echo '<td><span class="status-error">✗ NOT SET</span></td>';
            echo '<td><em>Missing</em></td>';
            $configOk = false;
        } else {
            $masked = substr($apiKey, 0, 8) . '...' . substr($apiKey, -4);
            echo '<td><span class="status-ok">✓ SET</span></td>';
            echo '<td><code>' . htmlspecialchars($masked) . '</code></td>';
        }
        $source = defined('SMTP2GO_API_KEY') ? 'Constant' : (getenv('SMTP2GO_API_KEY') ? 'Environment' : 'None');
        echo '<td>' . $source . '</td>';
        echo '</tr>';
        
        // Check Sender Email
        echo '<tr>';
        echo '<td><code>SMTP2GO_SENDER_EMAIL</code></td>';
        if (empty($senderEmail) || $senderEmail === 'your_email@example.com') {
            echo '<td><span class="status-error">✗ NOT SET</span></td>';
            echo '<td><em>Missing</em></td>';
            $configOk = false;
        } else {
            echo '<td><span class="status-ok">✓ SET</span></td>';
            echo '<td><code>' . htmlspecialchars($senderEmail) . '</code></td>';
        }
        $source = defined('SMTP2GO_SENDER_EMAIL') ? 'Constant' : (getenv('SMTP2GO_SENDER_EMAIL') ? 'Environment' : 'None');
        echo '<td>' . $source . '</td>';
        echo '</tr>';
        
        // Check Sender Name
        echo '<tr>';
        echo '<td><code>SMTP2GO_SENDER_NAME</code></td>';
        if (empty($senderName)) {
            echo '<td><span class="status-warning">⚠ Not Set (using default)</span></td>';
            echo '<td><em>D\'Marsians Taekwondo Gym</em></td>';
        } else {
            echo '<td><span class="status-ok">✓ SET</span></td>';
            echo '<td><code>' . htmlspecialchars($senderName) . '</code></td>';
        }
        $source = defined('SMTP2GO_SENDER_NAME') ? 'Constant' : (getenv('SMTP2GO_SENDER_NAME') ? 'Environment' : 'Default');
        echo '<td>' . $source . '</td>';
        echo '</tr>';
        
        echo '</table>';
        
        if (!$configOk) {
            echo '<div class="alert alert-danger">';
            echo '<strong>❌ Configuration Missing!</strong><br>';
            echo 'Environment variables are not set. This is why OTP emails are not being sent.<br><br>';
            echo '<strong>To fix:</strong><br>';
            echo '1. Go to <a href="https://cloud.digitalocean.com/apps" target="_blank">Digital Ocean App Platform</a><br>';
            echo '2. Select your app: <strong>dmarsians-taekwondo</strong><br>';
            echo '3. Go to: <strong>Settings</strong> → <strong>App-Level Environment Variables</strong> → <strong>Edit</strong><br>';
            echo '4. Add these variables with <strong>RUN_TIME</strong> scope:<br>';
            echo '   - <code>SMTP2GO_API_KEY</code> = <code>api-DB88D1F1E4B74779BDB77FC2895D8325</code><br>';
            echo '   - <code>SMTP2GO_SENDER_EMAIL</code> = <code>helmandashelle.dacuma@sccpag.edu.ph</code><br>';
            echo '5. Click <strong>Save</strong> and wait for redeployment (2-5 minutes)<br>';
            echo '6. Refresh this page to verify';
            echo '</div>';
        } else {
            echo '<div class="alert alert-success">';
            echo '<strong>✓ Configuration looks good!</strong> Environment variables are set correctly.';
            echo '</div>';
        }
        ?>
    </div>
    
    <?php if ($configOk && $conn): ?>
    <div class="box">
        <h2>2. Recent OTP Attempts</h2>
        <?php
        $recentAttempts = [];
        if ($stmt = $conn->prepare("SELECT apr.*, aa.username, aa.email as admin_email FROM admin_password_resets apr LEFT JOIN admin_accounts aa ON apr.admin_id = aa.id ORDER BY apr.created_at DESC LIMIT 10")) {
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $recentAttempts[] = $row;
                }
            }
            $stmt->close();
        }
        
        if (empty($recentAttempts)) {
            echo '<div class="alert alert-info">';
            echo '<strong>No OTP attempts found.</strong> Try requesting an OTP from the forgot password page.';
            echo '</div>';
        } else {
            echo '<table>';
            echo '<tr><th>Date/Time</th><th>Email</th><th>Username</th><th>Expires At</th><th>Attempts</th><th>Status</th></tr>';
            foreach ($recentAttempts as $attempt) {
                $expired = strtotime($attempt['otp_expires_at']) < time();
                $consumed = $attempt['consumed'] == 1;
                $status = $consumed ? '<span class="status-info">Used</span>' : ($expired ? '<span class="status-warning">Expired</span>' : '<span class="status-ok">Active</span>');
                
                echo '<tr>';
                echo '<td>' . htmlspecialchars($attempt['last_sent_at']) . '</td>';
                echo '<td>' . htmlspecialchars($attempt['email']) . '</td>';
                echo '<td>' . htmlspecialchars($attempt['username'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($attempt['otp_expires_at']) . '</td>';
                echo '<td>' . $attempt['attempt_count'] . '</td>';
                echo '<td>' . $status . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        ?>
    </div>
    
    <div class="box">
        <h2>3. Test Email Sending</h2>
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
                
                $url = 'https://api.smtp2go.com/v3/email/send';
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                $curlErrNo = curl_errno($ch);
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
                        echo '<pre>' . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT)) . '</pre>';
                        
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
                                echo '1. Check your email inbox and spam folder<br>';
                                echo '2. If you received the test email, the configuration is working<br>';
                                echo '3. If OTP emails still don\'t work, check the SMTP2GO Activity dashboard<br>';
                                echo '4. Go to: <a href="https://app.smtp2go.com/reports/activity" target="_blank">SMTP2GO Activity</a>';
                                echo '</div>';
                            } else {
                                echo '<div class="alert alert-warning">';
                                echo 'Response received but no message_id. Check errors above.';
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
        <h2>4. Check SMTP2GO Activity</h2>
        <p>To verify if emails are actually being sent:</p>
        <ol>
            <li>Go to <a href="https://app.smtp2go.com/reports/activity" target="_blank">SMTP2GO Activity Dashboard</a></li>
            <li>Look for entries with subject "Your Admin OTP Code"</li>
            <li>If you see entries → Email is being sent (check spam folder)</li>
            <li>If you see NO entries → Email is NOT being sent (check environment variables)</li>
        </ol>
    </div>
    
    <div class="box">
        <h2>5. Check Digital Ocean Logs</h2>
        <p>To see detailed error messages:</p>
        <ol>
            <li>Go to <a href="https://cloud.digitalocean.com/apps" target="_blank">Digital Ocean Dashboard</a></li>
            <li>Select your app: <strong>dmarsians-taekwondo</strong></li>
            <li>Click on <strong>Runtime Logs</strong> tab</li>
            <li>Look for messages containing:
                <ul>
                    <li><code>OTP Email Debug</code></li>
                    <li><code>SMTP2GO_API_KEY</code></li>
                    <li><code>Failed to send OTP email</code></li>
                    <li><code>cURL Error</code></li>
                </ul>
            </li>
        </ol>
    </div>
    
    <div class="box" style="background: #fff3cd; border-color: #ffc107;">
        <h2>📋 Quick Checklist</h2>
        <ul>
            <li>✓ Environment variables set in Digital Ocean (RUN_TIME scope)</li>
            <li>✓ App redeployed after setting variables</li>
            <li>✓ Sender email verified in SMTP2GO account</li>
            <li>✓ Checked SMTP2GO Activity dashboard for email entries</li>
            <li>✓ Checked email spam/junk folder</li>
            <li>✓ Verified correct email address in admin account</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="forgot_admin_password.php" class="btn">Go to Forgot Password</a>
        <a href="admin_login.php" class="btn" style="background: #6c757d;">Back to Login</a>
    </div>
</body>
</html>

