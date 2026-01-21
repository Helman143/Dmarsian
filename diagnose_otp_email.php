<?php
/**
 * OTP Email Diagnostic Tool
 * This script helps diagnose why OTP emails work on localhost but not on Digital Ocean
 * DELETE THIS FILE AFTER FIXING THE ISSUE
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
    <title>OTP Email Diagnostic - D'MARSIANS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
        .config-value { font-family: monospace; background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
        .test-result { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>🔍 OTP Email Diagnostic Tool</h1>
        <p class="text-muted">Diagnosing why OTP emails work on localhost but not on Digital Ocean</p>
        
        <div class="alert alert-warning">
            <strong>⚠️ Security Note:</strong> Delete this file after fixing the issue!
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h3>1. Environment Variables Check</h3>
            </div>
            <div class="card-body">
                <?php
                $issues = [];
                
                // Check SMTP2GO_API_KEY
                echo '<div class="mb-3 p-3 border rounded">';
                echo '<h5>SMTP2GO_API_KEY</h5>';
                $apiKeyEnv = getenv('SMTP2GO_API_KEY');
                $apiKeyConst = defined('SMTP2GO_API_KEY') ? SMTP2GO_API_KEY : '';
                
                if (empty($apiKeyEnv) && empty($apiKeyConst)) {
                    echo '<p class="status-error">✗ NOT FOUND</p>';
                    echo '<p class="text-danger">Environment variable is not set!</p>';
                    $issues[] = 'SMTP2GO_API_KEY not set';
                } else {
                    $apiKey = $apiKeyConst ?: $apiKeyEnv;
                    if (empty($apiKey) || $apiKey === 'your_smtp2go_api_key_here') {
                        echo '<p class="status-error">✗ INVALID VALUE</p>';
                        echo '<p class="text-danger">Value is empty or placeholder</p>';
                        $issues[] = 'SMTP2GO_API_KEY has invalid value';
                    } else {
                        $masked = substr($apiKey, 0, 8) . '...' . substr($apiKey, -4);
                        echo '<p class="status-ok">✓ Found</p>';
                        echo '<p>Value: <span class="config-value">' . htmlspecialchars($masked) . '</span></p>';
                        if ($apiKeyEnv) {
                            echo '<p class="text-success small">✓ Loaded from getenv()</p>';
                        } else {
                            echo '<p class="text-warning small">⚠ Not in getenv(), but found in constant</p>';
                        }
                    }
                }
                echo '</div>';
                
                // Check SMTP2GO_SENDER_EMAIL
                echo '<div class="mb-3 p-3 border rounded">';
                echo '<h5>SMTP2GO_SENDER_EMAIL</h5>';
                $senderEmailEnv = getenv('SMTP2GO_SENDER_EMAIL');
                $senderEmailConst = defined('SMTP2GO_SENDER_EMAIL') ? SMTP2GO_SENDER_EMAIL : '';
                
                if (empty($senderEmailEnv) && empty($senderEmailConst)) {
                    echo '<p class="status-error">✗ NOT FOUND</p>';
                    echo '<p class="text-danger">Environment variable is not set!</p>';
                    $issues[] = 'SMTP2GO_SENDER_EMAIL not set';
                } else {
                    $senderEmail = $senderEmailConst ?: $senderEmailEnv;
                    if (empty($senderEmail) || $senderEmail === 'your_email@example.com') {
                        echo '<p class="status-error">✗ INVALID VALUE</p>';
                        echo '<p class="text-danger">Value is empty or placeholder</p>';
                        $issues[] = 'SMTP2GO_SENDER_EMAIL has invalid value';
                    } else {
                        echo '<p class="status-ok">✓ Found</p>';
                        echo '<p>Value: <span class="config-value">' . htmlspecialchars($senderEmail) . '</span></p>';
                        if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
                            echo '<p class="status-error">⚠ Invalid email format!</p>';
                            $issues[] = 'SMTP2GO_SENDER_EMAIL has invalid format';
                        }
                        if ($senderEmailEnv) {
                            echo '<p class="text-success small">✓ Loaded from getenv()</p>';
                        } else {
                            echo '<p class="text-warning small">⚠ Not in getenv(), but found in constant</p>';
                        }
                    }
                }
                echo '</div>';
                
                // Check SMTP2GO_SENDER_NAME
                echo '<div class="mb-3 p-3 border rounded">';
                echo '<h5>SMTP2GO_SENDER_NAME</h5>';
                $senderNameEnv = getenv('SMTP2GO_SENDER_NAME');
                $senderNameConst = defined('SMTP2GO_SENDER_NAME') ? SMTP2GO_SENDER_NAME : '';
                $senderName = $senderNameConst ?: $senderNameEnv;
                if (empty($senderName)) {
                    echo '<p class="status-warning">⚠ Not set (using default)</p>';
                } else {
                    echo '<p class="status-ok">✓ Found</p>';
                    echo '<p>Value: <span class="config-value">' . htmlspecialchars($senderName) . '</span></p>';
                }
                echo '</div>';
                ?>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h3>2. System Capabilities Check</h3>
            </div>
            <div class="card-body">
                <?php
                // Check cURL
                echo '<div class="mb-3">';
                echo '<strong>cURL Extension:</strong> ';
                if (!function_exists('curl_init')) {
                    echo '<span class="status-error">✗ NOT AVAILABLE</span>';
                    $issues[] = 'cURL extension not available';
                } else {
                    echo '<span class="status-ok">✓ Available</span>';
                    $curlVersion = curl_version();
                    echo '<p class="text-muted small">Version: ' . htmlspecialchars($curlVersion['version']) . '</p>';
                    echo '<p class="text-muted small">SSL Version: ' . htmlspecialchars($curlVersion['ssl_version'] ?? 'N/A') . '</p>';
                }
                echo '</div>';
                
                // Check allow_url_fopen
                echo '<div class="mb-3">';
                echo '<strong>allow_url_fopen:</strong> ';
                if (!ini_get('allow_url_fopen')) {
                    echo '<span class="status-warning">⚠ Disabled (not required for cURL)</span>';
                } else {
                    echo '<span class="status-ok">✓ Enabled</span>';
                }
                echo '</div>';
                
                // Check SSL
                echo '<div class="mb-3">';
                echo '<strong>SSL Support:</strong> ';
                if (!extension_loaded('openssl')) {
                    echo '<span class="status-warning">⚠ OpenSSL not loaded (may affect HTTPS)</span>';
                } else {
                    echo '<span class="status-ok">✓ Available</span>';
                }
                echo '</div>';
                ?>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h3>3. Live API Test</h3>
            </div>
            <div class="card-body">
                <?php if (empty($issues)): ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="test_email" class="form-label">Test Email Address</label>
                            <input type="email" class="form-control" id="test_email" name="test_email" 
                                   value="helmandacuma5@gmail.com" required>
                            <small class="form-text text-muted">This will send a test OTP email to verify the configuration</small>
                        </div>
                        <button type="submit" class="btn btn-success" name="send_test">Send Test OTP Email</button>
                    </form>
                    
                    <?php
                    if (isset($_POST['send_test']) && isset($_POST['test_email'])) {
                        $testEmail = filter_var($_POST['test_email'], FILTER_SANITIZE_EMAIL);
                        
                        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                            echo '<div class="alert alert-danger mt-3">Invalid email address format.</div>';
                        } else {
                            echo '<div class="test-result">';
                            echo '<h4>Test Results:</h4>';
                            
                            // Get configuration values
                            $apiKey = defined('SMTP2GO_API_KEY') ? SMTP2GO_API_KEY : getenv('SMTP2GO_API_KEY');
                            $senderEmail = defined('SMTP2GO_SENDER_EMAIL') ? SMTP2GO_SENDER_EMAIL : getenv('SMTP2GO_SENDER_EMAIL');
                            $senderName = defined('SMTP2GO_SENDER_NAME') ? SMTP2GO_SENDER_NAME : (getenv('SMTP2GO_SENDER_NAME') ?: "D'Marsians Taekwondo Gym");
                            
                            // Generate test OTP
                            $testOtp = strval(random_int(100000, 999999));
                            
                            // Build payload exactly like admin_send_otp.php
                            $payload = [
                                'api_key' => $apiKey,
                                'to' => [$testEmail],
                                'sender' => $senderEmail,
                                'sender_name' => $senderName,
                                'subject' => 'Test OTP Email - Diagnostic',
                                'text_body' => "This is a test OTP email.\n\nYour test OTP code is: $testOtp\n\nThis code will expire in 5 minutes.",
                                'html_body' => '<div style="font-family:Arial,Helvetica,sans-serif;line-height:1.5;color:#222">'
                                             . '<h2 style="margin:0 0 12px">Password Reset OTP (Test)</h2>'
                                             . '<p>This is a test OTP email.</p>'
                                             . '<p>Your test OTP code is: <strong style="font-size:18px">' . htmlspecialchars($testOtp) . '</strong></p>'
                                             . '<p>This code will expire in 5 minutes.</p>'
                                             . '</div>'
                            ];
                            
                            echo '<div class="alert alert-info">';
                            echo '<strong>Payload being sent:</strong><br>';
                            echo '<pre>' . htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT)) . '</pre>';
                            echo '</div>';
                            
                            // Send email using the same function as admin_send_otp.php
                            $url = 'https://api.smtp2go.com/v3/email/send';
                            $jsonPayload = json_encode($payload);
                            
                            if ($jsonPayload === false) {
                                $jsonError = json_last_error_msg();
                                echo '<div class="alert alert-danger">';
                                echo '<strong>JSON Encoding Error:</strong> ' . htmlspecialchars($jsonError);
                                echo '</div>';
                            } else {
                                $ch = curl_init($url);
                                if ($ch === false) {
                                    echo '<div class="alert alert-danger">';
                                    echo '<strong>cURL Init Failed</strong>';
                                    echo '</div>';
                                } else {
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_POST, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
                                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                                    
                                    $response = curl_exec($ch);
                                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    $curlErr = curl_error($ch);
                                    $curlInfo = curl_getinfo($ch);
                                    curl_close($ch);
                                    
                                    echo '<div class="alert alert-info">';
                                    echo '<strong>HTTP Status Code:</strong> ' . $httpCode . '<br>';
                                    echo '<strong>cURL Info:</strong><br>';
                                    echo '<pre>' . htmlspecialchars(json_encode($curlInfo, JSON_PRETTY_PRINT)) . '</pre>';
                                    
                                    if ($curlErr) {
                                        echo '<strong class="status-error">cURL Error:</strong> ' . htmlspecialchars($curlErr) . '<br>';
                                    }
                                    echo '</div>';
                                    
                                    if ($response) {
                                        $decoded = json_decode($response, true);
                                        if ($decoded) {
                                            echo '<div class="alert alert-info">';
                                            echo '<strong>API Response:</strong><br>';
                                            echo '<pre>' . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT)) . '</pre>';
                                            echo '</div>';
                                            
                                            if ($httpCode >= 200 && $httpCode < 300) {
                                                if (isset($decoded['data']['message_id'])) {
                                                    echo '<div class="alert alert-success">';
                                                    echo '<strong>✓ Email sent successfully!</strong><br>';
                                                    echo 'Message ID: ' . htmlspecialchars($decoded['data']['message_id']) . '<br>';
                                                    echo 'Please check your inbox (and spam folder) at: <strong>' . htmlspecialchars($testEmail) . '</strong><br>';
                                                    echo 'Test OTP Code: <strong>' . htmlspecialchars($testOtp) . '</strong>';
                                                    echo '</div>';
                                                } else {
                                                    echo '<div class="alert alert-warning">';
                                                    echo '<strong>⚠ Response received but no message_id</strong><br>';
                                                    if (isset($decoded['errors']) && is_array($decoded['errors'])) {
                                                        echo '<strong>Errors:</strong><br>';
                                                        foreach ($decoded['errors'] as $err) {
                                                            echo '- ' . htmlspecialchars($err['message'] ?? 'Unknown error') . '<br>';
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
                                            echo '<div class="alert alert-warning">';
                                            echo '<strong>Raw Response (not JSON):</strong><br>';
                                            echo '<pre>' . htmlspecialchars(substr($response, 0, 1000)) . '</pre>';
                                            echo '</div>';
                                        }
                                    } else {
                                        echo '<div class="alert alert-danger">No response from SMTP2GO API</div>';
                                    }
                                }
                            }
                            
                            echo '</div>';
                        }
                    }
                    ?>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <strong>Configuration issues found!</strong> Please fix the issues above before testing.
                        <ul class="mt-2">
                            <?php foreach ($issues as $issue): ?>
                                <li><?php echo htmlspecialchars($issue); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h3>4. Digital Ocean Environment Variables Setup</h3>
            </div>
            <div class="card-body">
                <h5>If environment variables are missing, follow these steps:</h5>
                <ol>
                    <li><strong>Go to Digital Ocean App Platform Dashboard</strong>
                        <ul>
                            <li>URL: <a href="https://cloud.digitalocean.com/apps" target="_blank">https://cloud.digitalocean.com/apps</a></li>
                            <li>Select your app: <strong>dmarsians-taekwondo</strong></li>
                        </ul>
                    </li>
                    <li><strong>Navigate to Settings</strong>
                        <ul>
                            <li>Click on <strong>Settings</strong> tab</li>
                            <li>Scroll down to <strong>App-Level Environment Variables</strong></li>
                            <li>Click <strong>Edit</strong></li>
                        </ul>
                    </li>
                    <li><strong>Add/Update these variables:</strong>
                        <table class="table table-bordered mt-2">
                            <thead>
                                <tr>
                                    <th>Variable Name</th>
                                    <th>Value</th>
                                    <th>Scope</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>SMTP2GO_API_KEY</code></td>
                                    <td><code>api-DB88D1F1E4B74779BDB77FC2895D8325</code></td>
                                    <td>RUN_TIME</td>
                                </tr>
                                <tr>
                                    <td><code>SMTP2GO_SENDER_EMAIL</code></td>
                                    <td><code>helmandashelle.dacuma@sccpag.edu.ph</code></td>
                                    <td>RUN_TIME</td>
                                </tr>
                                <tr>
                                    <td><code>SMTP2GO_SENDER_NAME</code></td>
                                    <td><code>D'Marsians Taekwondo Gym</code></td>
                                    <td>RUN_TIME</td>
                                </tr>
                            </tbody>
                        </table>
                    </li>
                    <li><strong>Save and Redeploy</strong>
                        <ul>
                            <li>Click <strong>Save</strong> button</li>
                            <li>Wait for automatic redeployment (2-5 minutes)</li>
                            <li>Check the <strong>Deployments</strong> tab to see deployment status</li>
                        </ul>
                    </li>
                    <li><strong>Test Again</strong>
                        <ul>
                            <li>Refresh this page after deployment completes</li>
                            <li>Run the test again</li>
                        </ul>
                    </li>
                </ol>
                
                <div class="alert alert-info mt-3">
                    <strong>💡 Tip:</strong> After setting environment variables, you can verify they're loaded by checking:
                    <ul class="mt-2 mb-0">
                        <li><code>check_env.php</code> - Shows all environment variables</li>
                        <li><code>check_email_config.php</code> - Shows email-specific configuration</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h3>5. Common Issues & Solutions</h3>
            </div>
            <div class="card-body">
                <h5>Issue: Environment variables not loading</h5>
                <ul>
                    <li><strong>Solution:</strong> Make sure variables are set in App Platform Dashboard (not just in app.yaml)</li>
                    <li><strong>Solution:</strong> Ensure Scope is set to <code>RUN_TIME</code> (not BUILD_TIME)</li>
                    <li><strong>Solution:</strong> Wait for redeployment after adding variables</li>
                </ul>
                
                <h5 class="mt-3">Issue: cURL errors</h5>
                <ul>
                    <li><strong>Solution:</strong> Check if cURL extension is enabled in PHP</li>
                    <li><strong>Solution:</strong> Check firewall rules - SMTP2GO API should be accessible</li>
                    <li><strong>Solution:</strong> Check SSL certificate issues (see cURL info above)</li>
                </ul>
                
                <h5 class="mt-3">Issue: API returns error about sender email</h5>
                <ul>
                    <li><strong>Solution:</strong> Verify <code>helmandashelle.dacuma@sccpag.edu.ph</code> is verified in SMTP2GO dashboard</li>
                    <li><strong>Solution:</strong> Log into SMTP2GO and check sender verification status</li>
                </ul>
                
                <h5 class="mt-3">Issue: HTTP 401 or 403 errors</h5>
                <ul>
                    <li><strong>Solution:</strong> API key might be invalid or expired</li>
                    <li><strong>Solution:</strong> Check SMTP2GO dashboard for API key status</li>
                    <li><strong>Solution:</strong> Regenerate API key if needed</li>
                </ul>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            <a href="check_email_config.php" class="btn btn-primary">Email Config Check</a>
        </div>
    </div>
</body>
</html>

