<?php
/**
 * Digital Ocean Email Configuration Checker
 * This page helps diagnose email issues on Digital Ocean App Platform
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
    <title>Digital Ocean Email Config Check - D'MARSIANS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
        .config-value { font-family: monospace; background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
        .instruction-box { background: #e3f2fd; padding: 20px; border-left: 4px solid #2196F3; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>🌐 Digital Ocean Email Configuration Check</h1>
        <p class="text-muted">Checking your SMTP2GO email configuration on Digital Ocean App Platform...</p>
        
        <div class="alert alert-info">
            <strong>Note:</strong> On Digital Ocean App Platform, environment variables are set in the dashboard, not in .env files.
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h3>Current Configuration Status</h3>
            </div>
            <div class="card-body">
                <?php
                $allOk = true;
                $issues = [];
                
                // Check SMTP2GO_API_KEY
                echo '<div class="mb-3 p-3 border rounded">';
                echo '<h5>SMTP2GO_API_KEY</h5>';
                $apiKey = defined('SMTP2GO_API_KEY') ? SMTP2GO_API_KEY : '';
                $apiKeyEnv = getenv('SMTP2GO_API_KEY');
                
                if (empty($apiKey) || $apiKey === 'your_smtp2go_api_key_here') {
                    echo '<p class="status-error">✗ NOT CONFIGURED</p>';
                    echo '<p class="text-danger">The API key is missing or not set in Digital Ocean environment variables.</p>';
                    $allOk = false;
                    $issues[] = 'SMTP2GO_API_KEY';
                } else {
                    $masked = substr($apiKey, 0, 8) . '...' . substr($apiKey, -4);
                    echo '<p class="status-ok">✓ Configured</p>';
                    echo '<p>Value: <span class="config-value">' . htmlspecialchars($masked) . '</span></p>';
                    if ($apiKeyEnv) {
                        echo '<p class="text-success small">✓ Loaded from environment variable</p>';
                    }
                }
                echo '</div>';
                
                // Check SMTP2GO_SENDER_EMAIL
                echo '<div class="mb-3 p-3 border rounded">';
                echo '<h5>SMTP2GO_SENDER_EMAIL</h5>';
                $senderEmail = defined('SMTP2GO_SENDER_EMAIL') ? SMTP2GO_SENDER_EMAIL : '';
                $senderEmailEnv = getenv('SMTP2GO_SENDER_EMAIL');
                
                if (empty($senderEmail) || $senderEmail === 'your_email@example.com') {
                    echo '<p class="status-error">✗ NOT CONFIGURED</p>';
                    echo '<p class="text-danger">The sender email is missing or not set in Digital Ocean environment variables.</p>';
                    $allOk = false;
                    $issues[] = 'SMTP2GO_SENDER_EMAIL';
                } else {
                    echo '<p class="status-ok">✓ Configured</p>';
                    echo '<p>Value: <span class="config-value">' . htmlspecialchars($senderEmail) . '</span></p>';
                    if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
                        echo '<p class="status-error">⚠ Invalid email format!</p>';
                        $allOk = false;
                    } else {
                        echo '<p class="text-success small">✓ Valid email format</p>';
                    }
                    if ($senderEmailEnv) {
                        echo '<p class="text-success small">✓ Loaded from environment variable</p>';
                    }
                }
                echo '</div>';
                
                // Check SMTP2GO_SENDER_NAME
                echo '<div class="mb-3 p-3 border rounded">';
                echo '<h5>SMTP2GO_SENDER_NAME</h5>';
                $senderName = defined('SMTP2GO_SENDER_NAME') ? SMTP2GO_SENDER_NAME : '';
                if (empty($senderName)) {
                    echo '<p class="status-warning">⚠ Not set (using default)</p>';
                } else {
                    echo '<p class="status-ok">✓ Configured</p>';
                    echo '<p>Value: <span class="config-value">' . htmlspecialchars($senderName) . '</span></p>';
                }
                echo '</div>';
                
                // Check cURL
                echo '<div class="mb-3 p-3 border rounded">';
                echo '<h5>cURL Extension</h5>';
                if (!function_exists('curl_init')) {
                    echo '<p class="status-error">✗ NOT AVAILABLE</p>';
                    $allOk = false;
                } else {
                    echo '<p class="status-ok">✓ Available</p>';
                    $curlVersion = curl_version();
                    echo '<p class="text-muted small">Version: ' . htmlspecialchars($curlVersion['version']) . '</p>';
                }
                echo '</div>';
                ?>
            </div>
        </div>
        
        <?php if (!$allOk): ?>
        <div class="instruction-box mt-4">
            <h3>🔧 How to Fix: Set Environment Variables in Digital Ocean</h3>
            <ol>
                <li><strong>Go to Digital Ocean App Platform Dashboard</strong>
                    <ul>
                        <li>Visit: <a href="https://cloud.digitalocean.com/apps" target="_blank">https://cloud.digitalocean.com/apps</a></li>
                        <li>Select your app: <strong>dmarsians-taekwondo</strong></li>
                    </ul>
                </li>
                <li><strong>Navigate to Environment Variables</strong>
                    <ul>
                        <li>Click on <strong>Settings</strong> tab</li>
                        <li>Click on <strong>App-Level Environment Variables</strong></li>
                        <li>Click <strong>Edit</strong> button</li>
                    </ul>
                </li>
                <li><strong>Add Missing Variables</strong>
                    <p>Add these variables if they're missing:</p>
                    <table class="table table-bordered mt-2">
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Value</th>
                                <th>Scope</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (in_array('SMTP2GO_API_KEY', $issues)): ?>
                            <tr>
                                <td><code>SMTP2GO_API_KEY</code></td>
                                <td>Your SMTP2GO API key (e.g., <code>api-XXXXXXXX...</code>)</td>
                                <td>RUN_TIME</td>
                            </tr>
                            <?php endif; ?>
                            <?php if (in_array('SMTP2GO_SENDER_EMAIL', $issues)): ?>
                            <tr>
                                <td><code>SMTP2GO_SENDER_EMAIL</code></td>
                                <td>Your verified sender email (e.g., <code>your-email@example.com</code>)</td>
                                <td>RUN_TIME</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </li>
                <li><strong>Optional Variables</strong>
                    <ul>
                        <li><code>SMTP2GO_SENDER_NAME</code> = D'Marsians Taekwondo Gym</li>
                        <li><code>ADMIN_BCC_EMAIL</code> = Your admin email (optional)</li>
                    </ul>
                </li>
                <li><strong>Save and Redeploy</strong>
                    <ul>
                        <li>Click <strong>Save</strong></li>
                        <li>The app will automatically redeploy with new environment variables</li>
                        <li>Wait for deployment to complete (check the Deployments tab)</li>
                    </ul>
                </li>
                <li><strong>Verify</strong>
                    <ul>
                        <li>Refresh this page after deployment completes</li>
                        <li>All variables should show ✓ Configured</li>
                    </ul>
                </li>
            </ol>
        </div>
        <?php endif; ?>
        
        <?php if ($allOk): ?>
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h3>✅ Configuration Looks Good!</h3>
            </div>
            <div class="card-body">
                <p>Your email configuration appears to be set up correctly. Let's test it!</p>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="test_email" class="form-label">Test Email Address</label>
                        <input type="email" class="form-control" id="test_email" name="test_email" 
                               placeholder="your-email@gmail.com" required>
                        <small class="form-text text-muted">Enter your Gmail address to test email delivery</small>
                    </div>
                    <button type="submit" class="btn btn-success" name="send_test">Send Test Email</button>
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
                            'text_body' => "This is a test email from the D'MARSIANS Taekwondo System.\n\nIf you received this, your email configuration is working correctly!\n\nTime sent: " . date('Y-m-d H:i:s'),
                            'html_body' => '<div style="font-family:Arial,Helvetica,sans-serif;line-height:1.5;color:#222">'
                                         . '<h2 style="color:#5DD62C;">Test Email</h2>'
                                         . '<p>This is a test email from the <strong>D\'MARSIANS Taekwondo System</strong>.</p>'
                                         . '<p>If you received this, your email configuration is working correctly!</p>'
                                         . '<p><small>Time sent: ' . date('Y-m-d H:i:s') . '</small></p>'
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
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="card-header">
                <h3>📋 Troubleshooting Guide</h3>
            </div>
            <div class="card-body">
                <h5>Common Issues on Digital Ocean:</h5>
                <ol>
                    <li><strong>Environment variables not set</strong>
                        <ul>
                            <li>Variables must be set in App Platform Dashboard, not in .env file</li>
                            <li>After setting variables, app must redeploy</li>
                        </ul>
                    </li>
                    <li><strong>"Unknown error" message</strong>
                        <ul>
                            <li>Check server logs in Digital Ocean dashboard</li>
                            <li>Look for detailed error messages</li>
                            <li>Verify API key is correct</li>
                        </ul>
                    </li>
                    <li><strong>Emails not received</strong>
                        <ul>
                            <li>Check Spam/Junk folder</li>
                            <li>Verify sender email is verified in SMTP2GO</li>
                            <li>Check SMTP2GO dashboard for delivery logs</li>
                        </ul>
                    </li>
                </ol>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>






















