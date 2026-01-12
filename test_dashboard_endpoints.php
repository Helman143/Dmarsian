<?php
/**
 * Diagnostic endpoint to test dashboard API endpoints
 * Access this file directly in your browser to see what's happening
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Endpoints Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #1a1a1a; color: #fff; }
        .test { margin: 20px 0; padding: 15px; background: #2a2a2a; border-radius: 5px; }
        .success { border-left: 4px solid #5DD62C; }
        .error { border-left: 4px solid #ff4d4d; }
        .info { border-left: 4px solid #4a9eff; }
        pre { background: #1a1a1a; padding: 10px; overflow-x: auto; border-radius: 3px; }
        h2 { color: #5DD62C; }
        h3 { color: #4a9eff; }
    </style>
</head>
<body>
    <h1>🔍 Dashboard Endpoints Diagnostic</h1>
    <p>This page tests all dashboard API endpoints to identify issues.</p>

    <?php
    // Test 1: Database Connection
    echo '<div class="test info">';
    echo '<h3>Test 1: Database Connection</h3>';
    try {
        require_once 'db_connect.php';
        $conn = connectDB();
        if ($conn) {
            echo '<p class="success">✅ Database connection successful</p>';
            echo '<p>Host: ' . htmlspecialchars($conn->host_info) . '</p>';
            $conn->close();
        } else {
            echo '<p class="error">❌ Database connection failed</p>';
            echo '<p>Check your environment variables: DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT</p>';
        }
    } catch (Exception $e) {
        echo '<p class="error">❌ Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    echo '</div>';

    // Test 2: get_dashboard_stats.php
    echo '<div class="test info">';
    echo '<h3>Test 2: get_dashboard_stats.php</h3>';
    $statsUrl = 'get_dashboard_stats.php';
    echo '<p>Testing: <code>' . htmlspecialchars($statsUrl) . '</code></p>';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $statsUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo '<p class="error">❌ cURL Error: ' . htmlspecialchars($error) . '</p>';
    } else {
        echo '<p>HTTP Status: <strong>' . $httpCode . '</strong></p>';
        if ($httpCode === 200) {
            echo '<p class="success">✅ Endpoint accessible</p>';
            $json = json_decode($response, true);
            if ($json) {
                echo '<p class="success">✅ Valid JSON response</p>';
                echo '<pre>' . htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT)) . '</pre>';
            } else {
                echo '<p class="error">❌ Invalid JSON response</p>';
                echo '<pre>' . htmlspecialchars(substr($response, 0, 500)) . '</pre>';
            }
        } else {
            echo '<p class="error">❌ HTTP Error: ' . $httpCode . '</p>';
            echo '<pre>' . htmlspecialchars(substr($response, 0, 500)) . '</pre>';
        }
    }
    echo '</div>';

    // Test 3: api/dues.php
    echo '<div class="test info">';
    echo '<h3>Test 3: api/dues.php</h3>';
    $duesUrl = 'api/dues.php';
    echo '<p>Testing: <code>' . htmlspecialchars($duesUrl) . '</code></p>';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $duesUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo '<p class="error">❌ cURL Error: ' . htmlspecialchars($error) . '</p>';
    } else {
        echo '<p>HTTP Status: <strong>' . $httpCode . '</strong></p>';
        if ($httpCode === 200) {
            echo '<p class="success">✅ Endpoint accessible</p>';
            $json = json_decode($response, true);
            if ($json) {
                echo '<p class="success">✅ Valid JSON response</p>';
                if (isset($json['dues'])) {
                    echo '<p>Found ' . count($json['dues']) . ' dues records</p>';
                }
                echo '<pre>' . htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
            } else {
                echo '<p class="error">❌ Invalid JSON response</p>';
                echo '<pre>' . htmlspecialchars(substr($response, 0, 500)) . '</pre>';
            }
        } else {
            echo '<p class="error">❌ HTTP Error: ' . $httpCode . '</p>';
            echo '<pre>' . htmlspecialchars(substr($response, 0, 500)) . '</pre>';
        }
    }
    echo '</div>';

    // Test 4: get_active_inactive_counts.php
    echo '<div class="test info">';
    echo '<h3>Test 4: get_active_inactive_counts.php</h3>';
    $activeUrl = 'get_active_inactive_counts.php';
    echo '<p>Testing: <code>' . htmlspecialchars($activeUrl) . '</code></p>';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $activeUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo '<p class="error">❌ cURL Error: ' . htmlspecialchars($error) . '</p>';
    } else {
        echo '<p>HTTP Status: <strong>' . $httpCode . '</strong></p>';
        if ($httpCode === 200) {
            echo '<p class="success">✅ Endpoint accessible</p>';
            $json = json_decode($response, true);
            if ($json) {
                echo '<p class="success">✅ Valid JSON response</p>';
                echo '<pre>' . htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT)) . '</pre>';
            } else {
                echo '<p class="error">❌ Invalid JSON response</p>';
                echo '<pre>' . htmlspecialchars(substr($response, 0, 500)) . '</pre>';
            }
        } else {
            echo '<p class="error">❌ HTTP Error: ' . $httpCode . '</p>';
            echo '<pre>' . htmlspecialchars(substr($response, 0, 500)) . '</pre>';
        }
    }
    echo '</div>';

    // Test 5: Environment Variables
    echo '<div class="test info">';
    echo '<h3>Test 5: Environment Variables</h3>';
    $envVars = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME', 'DB_PORT'];
    $allSet = true;
    foreach ($envVars as $var) {
        $value = getenv($var);
        if ($value) {
            $displayValue = ($var === 'DB_PASS') ? '***hidden***' : htmlspecialchars($value);
            echo '<p>✅ ' . htmlspecialchars($var) . ' = ' . $displayValue . '</p>';
        } else {
            echo '<p class="error">❌ ' . htmlspecialchars($var) . ' is NOT set</p>';
            $allSet = false;
        }
    }
    if ($allSet) {
        echo '<p class="success">✅ All required environment variables are set</p>';
    } else {
        echo '<p class="error">❌ Some environment variables are missing. Set them in Digital Ocean App Platform → Settings → Environment Variables</p>';
    }
    echo '</div>';

    // Test 6: File Paths
    echo '<div class="test info">';
    echo '<h3>Test 6: File Paths</h3>';
    $files = [
        'get_dashboard_stats.php',
        'api/dues.php',
        'get_active_inactive_counts.php',
        'Scripts/admin_dashboard.js',
        'db_connect.php',
        'config.php'
    ];
    foreach ($files as $file) {
        if (file_exists($file)) {
            echo '<p class="success">✅ ' . htmlspecialchars($file) . ' exists</p>';
        } else {
            echo '<p class="error">❌ ' . htmlspecialchars($file) . ' NOT FOUND</p>';
        }
    }
    echo '</div>';

    // Test 7: PHP Configuration
    echo '<div class="test info">';
    echo '<h3>Test 7: PHP Configuration</h3>';
    echo '<p>PHP Version: <strong>' . phpversion() . '</strong></p>';
    echo '<p>Error Reporting: <strong>' . (ini_get('display_errors') ? 'ON' : 'OFF') . '</strong></p>';
    echo '<p>Timezone: <strong>' . date_default_timezone_get() . '</strong></p>';
    echo '<p>Memory Limit: <strong>' . ini_get('memory_limit') . '</strong></p>';
    echo '<p>Max Execution Time: <strong>' . ini_get('max_execution_time') . '</strong></p>';
    echo '</div>';
    ?>

    <div class="test">
        <h3>📋 Next Steps</h3>
        <ol>
            <li>Check the results above - any ❌ errors need to be fixed</li>
            <li>If database connection fails, check Digital Ocean → Database → Trusted Sources</li>
            <li>If endpoints return errors, check the Runtime Logs in App Platform</li>
            <li>If environment variables are missing, set them in App Platform → Settings → Environment Variables</li>
            <li>After fixing issues, refresh this page to verify</li>
        </ol>
    </div>
</body>
</html>
