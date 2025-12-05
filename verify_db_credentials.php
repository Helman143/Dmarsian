<?php
/**
 * Database Credentials Verification Tool
 * 
 * This script helps verify database credentials are correct.
 * Access via: https://your-app.ondigitalocean.app/verify_db_credentials.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Credentials Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 900px;
            margin: 0 auto;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #f44336;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
        }
        .error {
            color: #f44336;
            font-weight: bold;
            background: #ffebee;
            padding: 15px;
            border-left: 4px solid #f44336;
            margin: 15px 0;
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
            background: #e8f5e9;
            padding: 15px;
            border-left: 4px solid #4CAF50;
            margin: 15px 0;
        }
        .warning {
            color: #ff9800;
            font-weight: bold;
            background: #fff3e0;
            padding: 15px;
            border-left: 4px solid #ff9800;
            margin: 15px 0;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 15px 0;
        }
        .code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 0.9em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f44336;
            color: white;
        }
        .step {
            background: #f9f9f9;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #2196F3;
        }
        .step-number {
            font-weight: bold;
            color: #2196F3;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Database Credentials Verification</h1>
        
        <h2>Current Environment Variables</h2>
        <table>
            <thead>
                <tr>
                    <th>Variable</th>
                    <th>Current Value</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $dbVars = [
                    'DB_HOST' => getenv('DB_HOST'),
                    'DB_USER' => getenv('DB_USER'),
                    'DB_PASS' => getenv('DB_PASS'),
                    'DB_NAME' => getenv('DB_NAME'),
                    'DB_PORT' => getenv('DB_PORT')
                ];
                
                foreach ($dbVars as $key => $value) {
                    $isSet = ($value !== false && $value !== '');
                    
                    echo "<tr>";
                    echo "<td><strong>{$key}</strong></td>";
                    
                    if ($key === 'DB_PASS') {
                        echo "<td><span class='code'>" . ($isSet ? "***hidden*** (" . strlen($value) . " chars)" : "Not set") . "</span></td>";
                    } else {
                        echo "<td><span class='code'>" . ($isSet ? htmlspecialchars($value) : "Not set") . "</span></td>";
                    }
                    
                    echo "<td>" . ($isSet ? "<span class='success'>✅ Set</span>" : "<span class='error'>❌ Not Set</span>") . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        
        <h2>Connection Test</h2>
        <?php
        $servername = getenv('DB_HOST') ?: 'localhost';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASS') ?: '';
        $database = getenv('DB_NAME') ?: 'capstone_db';
        $port = (int)(getenv('DB_PORT') ?: 3306);
        
        echo "<p><strong>Testing connection to:</strong> <code>{$servername}:{$port}</code></p>";
        echo "<p><strong>Username:</strong> <code>{$username}</code></p>";
        echo "<p><strong>Database:</strong> <code>{$database}</code></p>";
        
        $conn = null;
        $error = null;
        $errno = 0;
        
        try {
            $conn = mysqli_init();
            if ($conn === false) {
                throw new Exception("mysqli_init() failed");
            }
            
            mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 5);
            mysqli_options($conn, MYSQLI_OPT_READ_TIMEOUT, 5);
            mysqli_ssl_set($conn, null, null, null, null, null);
            $flags = MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
            
            if (!$conn->real_connect($servername, $username, $password, $database, $port, null, $flags)) {
                $errno = mysqli_connect_errno();
                $error = mysqli_connect_error();
                throw new Exception("Connection failed: {$error} (Error {$errno})");
            }
            
            echo "<div class='success'>";
            echo "<h3>✅ Connection Successful!</h3>";
            echo "<p>Your database credentials are correct!</p>";
            echo "</div>";
            
            $conn->close();
            
        } catch (Exception $e) {
            $errno = mysqli_connect_errno();
            $error = mysqli_connect_error();
            
            echo "<div class='error'>";
            echo "<h3>❌ Connection Failed</h3>";
            echo "<p><strong>Error Code:</strong> {$errno}</p>";
            echo "<p><strong>Error Message:</strong> " . htmlspecialchars($error ?: $e->getMessage()) . "</p>";
            echo "</div>";
            
            if ($errno == 1045) {
                echo "<div class='info'>";
                echo "<h3>🔧 How to Fix Authentication Error (1045)</h3>";
                
                echo "<div class='step'>";
                echo "<p class='step-number'>Step 1: Get Correct Credentials</p>";
                echo "<ol>";
                echo "<li>Go to <strong>Digital Ocean Dashboard → Databases</strong></li>";
                echo "<li>Select your MySQL database</li>";
                echo "<li>Go to <strong>Users</strong> tab (or <strong>Connection Details</strong>)</li>";
                echo "<li>Find the user: <code>{$username}</code></li>";
                echo "<li><strong>Click to reveal/copy the password</strong></li>";
                echo "</ol>";
                echo "</div>";
                
                echo "<div class='step'>";
                echo "<p class='step-number'>Step 2: Reset Password (If Needed)</p>";
                echo "<ol>";
                echo "<li>In the <strong>Users</strong> tab, find user <code>{$username}</code></li>";
                echo "<li>Click <strong>Reset Password</strong> or <strong>Change Password</strong></li>";
                echo "<li>Set a new password (copy it immediately!)</li>";
                echo "<li>Save the new password</li>";
                echo "</ol>";
                echo "</div>";
                
                echo "<div class='step'>";
                echo "<p class='step-number'>Step 3: Update Environment Variables</p>";
                echo "<ol>";
                echo "<li>Go to <strong>App Platform → Your App → Settings → App-Level Environment Variables</strong></li>";
                echo "<li>Click <strong>Edit</strong></li>";
                echo "<li>Update <code>DB_USER</code> with exact username from database</li>";
                echo "<li>Update <code>DB_PASS</code> with exact password (copy-paste, no spaces!)</li>";
                echo "<li>Click <strong>Save</strong></li>";
                echo "</ol>";
                echo "</div>";
                
                echo "<div class='step'>";
                echo "<p class='step-number'>Step 4: Verify and Test</p>";
                echo "<ol>";
                echo "<li>Wait 1-2 minutes after saving</li>";
                echo "<li>Refresh this page to test again</li>";
                echo "<li>Check Runtime Logs for success message</li>";
                echo "</ol>";
                echo "</div>";
                
                echo "<div class='warning'>";
                echo "<h4>⚠️ Common Mistakes:</h4>";
                echo "<ul>";
                echo "<li><strong>Extra spaces:</strong> Make sure password has no spaces before/after</li>";
                echo "<li><strong>Special characters:</strong> Copy password exactly, or reset to simpler password for testing</li>";
                echo "<li><strong>Wrong username:</strong> Verify username matches exactly (case-sensitive)</li>";
                echo "<li><strong>Old password:</strong> If you reset password, make sure you updated environment variables</li>";
                echo "</ul>";
                echo "</div>";
                
                echo "</div>";
            }
        }
        ?>
        
        <h2>Quick Reference</h2>
        <div class="info">
            <p><strong>Where to find database credentials:</strong></p>
            <p>Digital Ocean Dashboard → Databases → Your Database → <strong>Users</strong> tab</p>
            <p>OR</p>
            <p>Digital Ocean Dashboard → Databases → Your Database → <strong>Connection Details</strong></p>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;">
            <p><strong>⚠️ Security Note:</strong> Remove or secure this file in production after fixing the issue.</p>
        </div>
    </div>
</body>
</html>



