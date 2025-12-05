<?php
/**
 * Environment Variables Diagnostic Tool
 * 
 * This script helps identify database configuration issues.
 * Access via: https://your-app.ondigitalocean.app/check_env.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Environment Variables Check</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 800px;
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
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
        }
        .error {
            color: #f44336;
            font-weight: bold;
        }
        .warning {
            color: #ff9800;
            font-weight: bold;
        }
        .info {
            color: #2196F3;
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
            background-color: #4CAF50;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 0.9em;
        }
        .instructions {
            background: #e3f2fd;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 20px 0;
        }
        .instructions ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Environment Variables Diagnostic</h1>
        
        <h2>Database Environment Variables</h2>
        <table>
            <thead>
                <tr>
                    <th>Variable</th>
                    <th>Value</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $dbVars = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME', 'DB_PORT'];
                $hasIssues = false;
                
                foreach ($dbVars as $var) {
                    $value = getenv($var);
                    $isUnresolved = ($value !== false && strpos($value, '${') === 0);
                    $isSet = ($value !== false && $value !== '');
                    $isTemplate = $isUnresolved;
                    
                    if ($isTemplate) {
                        $hasIssues = true;
                    }
                    
                    echo "<tr>";
                    echo "<td><strong>{$var}</strong></td>";
                    
                    // Show value (mask sensitive ones)
                    if (in_array($var, ['DB_PASS'])) {
                        if ($isSet) {
                            echo "<td><span class='code'>" . ($isTemplate ? htmlspecialchars($value) : "***hidden***") . "</span></td>";
                        } else {
                            echo "<td><span class='code'>Not set</span></td>";
                        }
                    } else {
                        if ($isSet) {
                            echo "<td><span class='code'>" . htmlspecialchars($value) . "</span></td>";
                        } else {
                            echo "<td><span class='code'>Not set</span></td>";
                        }
                    }
                    
                    // Status
                    if ($isTemplate) {
                        echo "<td><span class='error'>❌ Template Variable Not Resolved</span></td>";
                    } elseif ($isSet) {
                        echo "<td><span class='success'>✅ Set</span></td>";
                    } else {
                        echo "<td><span class='warning'>⚠️ Not Set</span></td>";
                        $hasIssues = true;
                    }
                    
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        
        <?php if ($hasIssues): ?>
        <div class="instructions">
            <h2>⚠️ Issues Detected</h2>
            <p><strong>Problem:</strong> Your database environment variables are either not set or contain unresolved template variables (like <code>${db.HOSTNAME}</code>).</p>
            
            <h3>Solution: Set Environment Variables Manually</h3>
            <p>Since you're using an <strong>external database</strong> (not an App Platform component), you need to set these variables manually:</p>
            
            <ol>
                <li>Go to <strong>Digital Ocean App Platform Dashboard</strong></li>
                <li>Select your app: <strong>dmarsians-taekwondo</strong></li>
                <li>Navigate to: <strong>Settings → App-Level Environment Variables</strong></li>
                <li>Click <strong>Edit</strong></li>
                <li><strong>Remove</strong> any variables showing <code>${db.*}</code> (template variables)</li>
                <li><strong>Add</strong> these variables with your actual database connection details:
                    <ul>
                        <li><code>DB_HOST</code> = Your database hostname (e.g., <code>db-mysql-nyc1-12345.db.ondigitalocean.com</code>)</li>
                        <li><code>DB_USER</code> = Your database username</li>
                        <li><code>DB_PASS</code> = Your database password</li>
                        <li><code>DB_NAME</code> = Your database name (e.g., <code>capstone_db</code>)</li>
                        <li><code>DB_PORT</code> = Your database port (usually <code>25060</code> for managed databases)</li>
                    </ul>
                </li>
                <li>Click <strong>Save</strong></li>
                <li><strong>Redeploy</strong> your app (push a commit or trigger deployment)</li>
            </ol>
            
            <h3>Where to Get Database Connection Details:</h3>
            <ol>
                <li>Go to <strong>Digital Ocean Dashboard → Databases</strong></li>
                <li>Select your MySQL database</li>
                <li>Click on <strong>Connection Details</strong> or <strong>Connection Parameters</strong></li>
                <li>Copy the hostname, port, username, password, and database name</li>
            </ol>
        </div>
        <?php else: ?>
        <div style="background: #e8f5e9; padding: 15px; border-left: 4px solid #4CAF50; margin: 20px 0;">
            <h2 class="success">✅ All Database Variables Are Set Correctly</h2>
            <p>Your database environment variables are properly configured. If you're still experiencing connection issues, check:</p>
            <ul>
                <li>Database firewall/trusted sources settings</li>
                <li>Database credentials are correct</li>
                <li>Database is running and accessible</li>
            </ul>
        </div>
        <?php endif; ?>
        
        <h2>Other Environment Variables</h2>
        <table>
            <thead>
                <tr>
                    <th>Variable</th>
                    <th>Value</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $otherVars = ['APP_ENV', 'SMTP2GO_API_KEY', 'SMTP2GO_SENDER_EMAIL', 'SMTP2GO_SENDER_NAME', 'ADMIN_BCC_EMAIL'];
                
                foreach ($otherVars as $var) {
                    $value = getenv($var);
                    $isSet = ($value !== false && $value !== '');
                    
                    echo "<tr>";
                    echo "<td><strong>{$var}</strong></td>";
                    
                    if (in_array($var, ['SMTP2GO_API_KEY'])) {
                        echo "<td><span class='code'>" . ($isSet ? "***hidden***" : "Not set") . "</span></td>";
                    } else {
                        echo "<td><span class='code'>" . ($isSet ? htmlspecialchars($value) : "Not set") . "</span></td>";
                    }
                    
                    echo "<td>" . ($isSet ? "<span class='success'>✅ Set</span>" : "<span class='warning'>⚠️ Not Set</span>") . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        
        <h2>PHP Configuration</h2>
        <table>
            <tr>
                <td><strong>PHP Version</strong></td>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <td><strong>Server Time</strong></td>
                <td><?php echo date('Y-m-d H:i:s T'); ?></td>
            </tr>
            <tr>
                <td><strong>Memory Limit</strong></td>
                <td><?php echo ini_get('memory_limit'); ?></td>
            </tr>
        </table>
        
        <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;">
            <p><strong>Note:</strong> This diagnostic page helps identify configuration issues. Remove or secure this file in production.</p>
        </div>
    </div>
</body>
</html>

