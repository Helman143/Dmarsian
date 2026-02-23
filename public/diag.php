<?php
/**
 * Database Connection Diagnostic Tool (Public Version)
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>DigitalOcean Database Diagnostic</h1>";

// 1. Check Environment Variables
echo "<h2>1. Environment Variables Check</h2>";
$env_keys = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME', 'DB_PORT'];
foreach ($env_keys as $key) {
    $val = getenv($key);
    if ($key === 'DB_PASS') {
        $display_val = empty($val) ? '<span style="color:red">EMPTY</span>' : '<span style="color:green">SET (HIDDEN)</span>';
    } else {
        $display_val = $val ? '<span style="color:green">' . htmlspecialchars($val) . '</span>' : '<span style="color:red">NOT SET</span>';
    }
    echo "<strong>$key</strong>: $display_val<br>";
}

// 2. Load Config
echo "<h2>2. Loading Project Config</h2>";
$config_path = __DIR__ . '/../config.php';
if (file_exists($config_path)) {
    echo "Found config.php... loading.<br>";
    require_once $config_path;
} else {
    echo "❌ Could not find config.php at $config_path<br>";
}

// 3. Connection Test
echo "<h2>3. Connection Test</h2>";
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db = getenv('DB_NAME') ?: 'capstone_db';
$port = getenv('DB_PORT') ?: 3306;

echo "Target: <strong>$host:$port</strong><br>";

$mysqli = mysqli_init();
mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, 5);

// Required for DO Managed DB
mysqli_ssl_set($mysqli, null, null, null, null, null);
$flags = MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;

echo "Connecting...<br>";
if (@$mysqli->real_connect($host, $user, $pass, $db, (int)$port, null, $flags)) {
    echo "<h3 style='color:green'>✅ SUCCESS: Database is connected!</h3>";
    echo "Server version: " . $mysqli->server_info . "<br>";
    
    // Check tables
    $res = $mysqli->query("SHOW TABLES LIKE 'admin_accounts'");
    if ($res && $res->num_rows > 0) {
        echo "✅ Table 'admin_accounts' exists.<br>";
    } else {
        echo "❌ Table 'admin_accounts' is MISSING. You need to run the import script.<br>";
    }
} else {
    echo "<h3 style='color:red'>❌ FAILED to connect.</h3>";
    echo "<strong>Error No:</strong> " . mysqli_connect_errno() . "<br>";
    echo "<strong>Error Message:</strong> " . mysqli_connect_error() . "<br>";
    
    if (mysqli_connect_errno() == 2002) {
        echo "<div style='background:#fff3f3; padding:15px; border:1px solid red; margin-top:10px;'>";
        echo "<strong>ACTION REQUIRED:</strong> This is a Timeout error.<br>";
        echo "1. Go to your DigitalOcean Database dashboard.<br>";
        echo "2. Find 'Settings' -> 'Trusted Sources'.<br>";
        echo "3. Add your 'dmarsians-taekwondo' App Platform resource to the list.<br>";
        echo "Without this, DigitalOcean blocks the website from talking to the database.";
        echo "</div>";
    }
}
?>
