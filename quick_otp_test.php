<?php
/**
 * Quick OTP Test - Simple diagnostic without login requirement
 * This script helps quickly diagnose OTP email issues
 * DELETE THIS FILE AFTER FIXING THE ISSUE
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple security - use a secret key in URL
$SECRET_KEY = 'OTP_TEST_2024_SECRET_KEY_CHANGE_THIS';
$providedKey = $_GET['key'] ?? '';

if ($providedKey !== $SECRET_KEY) {
    die('Access denied. Use: ?key=YOUR_SECRET_KEY');
}

require_once 'config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick OTP Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .box { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Quick OTP Email Test</h1>
    
    <div class="box">
        <h2>1. Environment Variables Check</h2>
        <?php
        $apiKeyEnv = getenv('SMTP2GO_API_KEY');
        $apiKeyConst = defined('SMTP2GO_API_KEY') ? SMTP2GO_API_KEY : '';
        $apiKey = $apiKeyConst ?: $apiKeyEnv;
        
        $senderEmailEnv = getenv('SMTP2GO_SENDER_EMAIL');
        $senderEmailConst = defined('SMTP2GO_SENDER_EMAIL') ? SMTP2GO_SENDER_EMAIL : '';
        $senderEmail = $senderEmailConst ?: $senderEmailEnv;
        
        echo '<p><strong>SMTP2GO_API_KEY:</strong> ';
        if (empty($apiKey) || $apiKey === 'your_smtp2go_api_key_here') {
            echo '<span class="status-error">✗ NOT SET</span></p>';
            echo '<p class="status-error">This is the problem! Set it in Digital Ocean App Platform.</p>';
        } else {
            $masked = substr($apiKey, 0, 8) . '...' . substr($apiKey, -4);
            echo '<span class="status-ok">✓ SET</span> (' . htmlspecialchars($masked) . ')</p>';
        }
        
        echo '<p><strong>SMTP2GO_SENDER_EMAIL:</strong> ';
        if (empty($senderEmail) || $senderEmail === 'your_email@example.com') {
            echo '<span class="status-error">✗ NOT SET</span></p>';
            echo '<p class="status-error">This is the problem! Set it in Digital Ocean App Platform.</p>';
        } else {
            echo '<span class="status-ok">✓ SET</span> (' . htmlspecialchars($senderEmail) . ')</p>';
        }
        
        echo '<p><strong>Loaded from:</strong> ';
        if ($apiKeyEnv) {
            echo '<span class="status-ok">getenv() ✓</span>';
        } elseif ($apiKeyConst) {
            echo '<span class="status-warning">Constant only (getenv() failed)</span>';
        } else {
            echo '<span class="status-error">NOT FOUND</span>';
        }
        echo '</p>';
        ?>
    </div>
    
    <?php if (!empty($apiKey) && !empty($senderEmail) && $apiKey !== 'your_smtp2go_api_key_here'): ?>
    <div class="box">
        <h2>2. Send Test Email</h2>
        <form method="POST">
            <p>
                <label>Test Email Address:</label><br>
                <input type="email" name="test_email" value="helmandacuma5@gmail.com" style="width: 300px; padding: 5px;" required>
            </p>
            <button type="submit" name="send" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Send Test OTP Email
            </button>
        </form>
        
        <?php
        if (isset($_POST['send']) && isset($_POST['test_email'])) {
            $testEmail = filter_var($_POST['test_email'], FILTER_SANITIZE_EMAIL);
            
            if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                echo '<p class="status-error">Invalid email address.</p>';
            } else {
                echo '<h3>Test Results:</h3>';
                
                $testOtp = strval(random_int(100000, 999999));
                
                $payload = [
                    'api_key' => $apiKey,
                    'to' => [$testEmail],
                    'sender' => $senderEmail,
                    'sender_name' => defined('SMTP2GO_SENDER_NAME') ? SMTP2GO_SENDER_NAME : "D'Marsians Taekwondo Gym",
                    'subject' => 'Test OTP Email',
                    'text_body' => "Your test OTP code is: $testOtp\nThis code will expire in 5 minutes.",
                    'html_body' => '<div style="font-family:Arial,Helvetica,sans-serif;line-height:1.5;color:#222">'
                                 . '<h2>Password Reset OTP (Test)</h2>'
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
                
                echo '<p><strong>HTTP Status:</strong> ' . $httpCode . '</p>';
                
                if ($curlErr) {
                    echo '<p class="status-error"><strong>cURL Error:</strong> ' . htmlspecialchars($curlErr) . ' (Error #' . $curlErrNo . ')</p>';
                }
                
                if ($response) {
                    $decoded = json_decode($response, true);
                    if ($decoded) {
                        echo '<pre>' . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT)) . '</pre>';
                        
                        if ($httpCode >= 200 && $httpCode < 300) {
                            if (isset($decoded['data']['message_id'])) {
                                echo '<p class="status-ok"><strong>✓ Email sent successfully!</strong></p>';
                                echo '<p>Message ID: ' . htmlspecialchars($decoded['data']['message_id']) . '</p>';
                                echo '<p>Check your inbox at: <strong>' . htmlspecialchars($testEmail) . '</strong></p>';
                                echo '<p>Test OTP Code: <strong>' . htmlspecialchars($testOtp) . '</strong></p>';
                            } else {
                                echo '<p class="status-warning">Response received but no message_id. Check errors above.</p>';
                            }
                        } else {
                            echo '<p class="status-error"><strong>✗ Email send failed</strong></p>';
                            if (isset($decoded['errors']) && is_array($decoded['errors'])) {
                                foreach ($decoded['errors'] as $error) {
                                    echo '<p class="status-error">- ' . htmlspecialchars($error['message'] ?? 'Unknown error') . '</p>';
                                }
                            }
                        }
                    } else {
                        echo '<p class="status-error">Invalid JSON response:</p>';
                        echo '<pre>' . htmlspecialchars(substr($response, 0, 500)) . '</pre>';
                    }
                } else {
                    echo '<p class="status-error">No response from SMTP2GO API</p>';
                }
            }
        }
        ?>
    </div>
    <?php else: ?>
    <div class="box" style="background: #fff3cd; border-color: #ffc107;">
        <h2>⚠️ Configuration Missing</h2>
        <p><strong>To fix this issue:</strong></p>
        <ol>
            <li>Go to <a href="https://cloud.digitalocean.com/apps" target="_blank">Digital Ocean App Platform</a></li>
            <li>Select your app: <strong>dmarsians-taekwondo</strong></li>
            <li>Go to: <strong>Settings</strong> → <strong>App-Level Environment Variables</strong> → <strong>Edit</strong></li>
            <li>Add these variables:
                <ul>
                    <li><code>SMTP2GO_API_KEY</code> = <code>api-DB88D1F1E4B74779BDB77FC2895D8325</code> (Scope: RUN_TIME)</li>
                    <li><code>SMTP2GO_SENDER_EMAIL</code> = <code>helmandashelle.dacuma@sccpag.edu.ph</code> (Scope: RUN_TIME)</li>
                </ul>
            </li>
            <li>Click <strong>Save</strong> and wait for redeployment (2-5 minutes)</li>
            <li>Refresh this page and test again</li>
        </ol>
    </div>
    <?php endif; ?>
    
    <div class="box">
        <h2>3. Check Digital Ocean Logs</h2>
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
    
    <div class="box" style="background: #f8d7da; border-color: #dc3545;">
        <p><strong>⚠️ Security:</strong> Delete this file after fixing the issue!</p>
        <p>Change the SECRET_KEY in the code before using in production.</p>
    </div>
</body>
</html>

