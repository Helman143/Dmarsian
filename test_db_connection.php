<?php
/**
 * Database Connection Test Script
 * 
 * This script tests the database connection and provides detailed diagnostics.
 * Access via: https://your-app.ondigitalocean.app/test_db_connection.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
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
            background: #e8f5e9;
            padding: 10px;
            border-left: 4px solid #4CAF50;
            margin: 10px 0;
        }
        .error {
            color: #f44336;
            font-weight: bold;
            background: #ffebee;
            padding: 10px;
            border-left: 4px solid #f44336;
            margin: 10px 0;
        }
        .warning {
            color: #ff9800;
            font-weight: bold;
            background: #fff3e0;
            padding: 10px;
            border-left: 4px solid #ff9800;
            margin: 10px 0;
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
            background-color: #4CAF50;
            color: white;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔌 Database Connection Test</h1>
        
        <h2>1. Environment Variables</h2>
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
                $dbVars = [
                    'DB_HOST' => getenv('DB_HOST'),
                    'DB_USER' => getenv('DB_USER'),
                    'DB_PASS' => getenv('DB_PASS'),
                    'DB_NAME' => getenv('DB_NAME'),
                    'DB_PORT' => getenv('DB_PORT')
                ];
                
                foreach ($dbVars as $key => $value) {
                    $isSet = ($value !== false && $value !== '');
                    $isTemplate = ($isSet && strpos($value, '${') === 0);
                    
                    echo "<tr>";
                    echo "<td><strong>{$key}</strong></td>";
                    
                    if ($key === 'DB_PASS') {
                        echo "<td><span class='code'>" . ($isSet ? "***hidden***" : "Not set") . "</span></td>";
                    } else {
                        echo "<td><span class='code'>" . ($isSet ? htmlspecialchars($value) : "Not set") . "</span></td>";
                    }
                    
                    if ($isTemplate) {
                        echo "<td><span class='error'>❌ Template Variable</span></td>";
                    } elseif ($isSet) {
                        echo "<td><span class='success'>✅ Set</span></td>";
                    } else {
                        echo "<td><span class='warning'>⚠️ Not Set</span></td>";
                    }
                    
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        
        <h2>2. Connection Test</h2>
        <?php
        $servername = getenv('DB_HOST') ?: 'localhost';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASS') ?: '';
        $database = getenv('DB_NAME') ?: 'capstone_db';
        $port = (int)(getenv('DB_PORT') ?: 3306);
        
        echo "<p><strong>Attempting to connect to:</strong> <code>{$servername}:{$port}</code></p>";
        echo "<p><strong>Database:</strong> <code>{$database}</code></p>";
        echo "<p><strong>Username:</strong> <code>{$username}</code></p>";
        
        $startTime = microtime(true);
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
            
            $connectionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            echo "<div class='success'>";
            echo "<h3>✅ Connection Successful!</h3>";
            echo "<p>Connected in {$connectionTime}ms</p>";
            echo "<p><strong>Server Info:</strong> " . htmlspecialchars($conn->server_info) . "</p>";
            echo "<p><strong>Host Info:</strong> " . htmlspecialchars($conn->host_info) . "</p>";
            echo "</div>";
            
            // Test query
            $result = $conn->query("SELECT 1 as test");
            if ($result) {
                echo "<div class='success'>";
                echo "<p>✅ Test query executed successfully</p>";
                echo "</div>";
            }
            
            // Check database
            $result = $conn->query("SELECT DATABASE() as db");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "<p><strong>Current Database:</strong> " . htmlspecialchars($row['db']) . "</p>";
            }
            
            // List tables
            $result = $conn->query("SHOW TABLES");
            if ($result) {
                echo "<h3>Tables in Database:</h3>";
                echo "<ul>";
                while ($row = $result->fetch_array()) {
                    echo "<li>" . htmlspecialchars($row[0]) . "</li>";
                }
                echo "</ul>";
            }
            
            $conn->close();
            
        } catch (mysqli_sql_exception $e) {
            $connectionTime = round((microtime(true) - $startTime) * 1000, 2);
            $errno = $e->getCode();
            $error = $e->getMessage();
            
            echo "<div class='error'>";
            echo "<h3>❌ Connection Failed</h3>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($error) . "</p>";
            echo "<p><strong>Error Code:</strong> {$errno}</p>";
            echo "<p><strong>Time:</strong> {$connectionTime}ms</p>";
            echo "</div>";
            
            // Provide specific guidance
            echo "<div class='info'>";
            echo "<h3>🔧 Troubleshooting Guide</h3>";
            
            if ($errno == 2002 || strpos($error, 'timed out') !== false || strpos($error, 'Connection timed out') !== false) {
                echo "<h4>Connection Timeout - Most Common Causes:</h4>";
                echo "<ol>";
                echo "<li><strong>Database Firewall Blocking Access</strong>";
                echo "<ul>";
                echo "<li>Go to: <strong>Digital Ocean Dashboard → Databases → Your Database → Settings → Trusted Sources</strong></li>";
                echo "<li>Add <strong>App Platform</strong> to trusted sources, OR</li>";
                echo "<li>Enable <strong>'Allow connections from App Platform'</strong> option</li>";
                echo "</ul>";
                echo "</li>";
                echo "<li><strong>Wrong Hostname/Port</strong>";
                echo "<ul>";
                echo "<li>Verify hostname and port in database connection details</li>";
                echo "<li>Check environment variables are set correctly</li>";
                echo "</ul>";
                echo "</li>";
                echo "<li><strong>Network Connectivity</strong>";
                echo "<ul>";
                echo "<li>Database might be in a different region</li>";
                echo "<li>Check if database is running and accessible</li>";
                echo "</ul>";
                echo "</li>";
                echo "</ol>";
            } elseif ($errno == 1045) {
                echo "<h4>Access Denied - Authentication Failed</h4>";
                echo "<ul>";
                echo "<li>Check username and password are correct</li>";
                echo "<li>Verify database user has proper permissions</li>";
                echo "<li>Check if password contains special characters that need escaping</li>";
                echo "</ul>";
            } elseif ($errno == 1049) {
                echo "<h4>Database Not Found</h4>";
                echo "<ul>";
                echo "<li>Check <code>DB_NAME</code> environment variable</li>";
                echo "<li>Verify database exists</li>";
                echo "<li>Create database if it doesn't exist</li>";
                echo "</ul>";
            } else {
                echo "<h4>General Connection Issues</h4>";
                echo "<ul>";
                echo "<li>Verify all environment variables are set correctly</li>";
                echo "<li>Check database is running and accessible</li>";
                echo "<li>Review database logs for more details</li>";
                echo "</ul>";
            }
            
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<h3>❌ Error</h3>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
        ?>
        
        <h2>3. Network Diagnostics</h2>
        <div class="info">
            <p><strong>Note:</strong> For connection timeout issues, the most common cause is database firewall settings.</p>
            <p><strong>Quick Fix:</strong> Go to your database settings and add App Platform to trusted sources.</p>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;">
            <p><strong>⚠️ Security Note:</strong> Remove or secure this file in production after testing.</p>
        </div>
    </div>
</body>
</html>

