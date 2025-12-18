<?php
/**
 * Simple PHP Test File
 * This file tests if PHP is working correctly
 * DELETE THIS FILE AFTER TESTING
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<!DOCTYPE html><html><head><title>PHP Test</title></head><body>";
echo "<h1>PHP is Working!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Time: " . date('Y-m-d H:i:s') . "</p>";

// Test 1: Basic PHP
echo "<h2>Test 1: Basic PHP</h2>";
echo "<p style='color:green;'>✓ PHP is executing</p>";

// Test 2: File system
echo "<h2>Test 2: File System</h2>";
if (file_exists('config.php')) {
    echo "<p style='color:green;'>✓ config.php exists</p>";
} else {
    echo "<p style='color:red;'>✗ config.php NOT FOUND</p>";
}

if (file_exists('env-loader.php')) {
    echo "<p style='color:green;'>✓ env-loader.php exists</p>";
} else {
    echo "<p style='color:red;'>✗ env-loader.php NOT FOUND</p>";
}

if (file_exists('.env')) {
    echo "<p style='color:green;'>✓ .env file exists</p>";
} else {
    echo "<p style='color:orange;'>⚠ .env file NOT FOUND (may be normal if using defaults)</p>";
}

// Test 3: Load config.php
echo "<h2>Test 3: Loading config.php</h2>";
try {
    ob_start();
    $result = @include 'config.php';
    $output = ob_get_clean();
    
    if ($result !== false) {
        echo "<p style='color:green;'>✓ config.php loaded successfully</p>";
        if (!empty($output)) {
            echo "<p style='color:orange;'>⚠ Output: " . htmlspecialchars($output) . "</p>";
        }
    } else {
        echo "<p style='color:red;'>✗ Failed to load config.php</p>";
        if (!empty($output)) {
            echo "<pre style='background:#f0f0f0;padding:10px;'>" . htmlspecialchars($output) . "</pre>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error loading config.php: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Error $e) {
    echo "<p style='color:red;'>✗ Fatal error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

// Test 4: Database connection
echo "<h2>Test 4: Database Connection</h2>";
if (isset($conn)) {
    if ($conn->connect_error) {
        echo "<p style='color:red;'>✗ Database connection failed: " . htmlspecialchars($conn->connect_error) . "</p>";
    } else {
        echo "<p style='color:green;'>✓ Database connection successful</p>";
        $result = $conn->query("SELECT 1");
        if ($result) {
            echo "<p style='color:green;'>✓ Database query successful</p>";
        } else {
            echo "<p style='color:red;'>✗ Database query failed: " . htmlspecialchars($conn->error) . "</p>";
        }
    }
} else {
    echo "<p style='color:orange;'>⚠ \$conn variable not set (config.php may not have loaded)</p>";
}

// Test 5: Required extensions
echo "<h2>Test 5: Required PHP Extensions</h2>";
$extensions = ['mysqli', 'mbstring', 'curl', 'gd', 'zip', 'xml', 'json'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color:green;'>✓ $ext</p>";
    } else {
        echo "<p style='color:red;'>✗ $ext (MISSING)</p>";
    }
}

// Test 6: Environment variables
echo "<h2>Test 6: Environment Variables</h2>";
$env_vars = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME'];
foreach ($env_vars as $var) {
    $value = getenv($var);
    if ($value !== false && $value !== '') {
        echo "<p style='color:green;'>✓ $var = " . (strpos($var, 'PASS') !== false ? '***' : htmlspecialchars($value)) . "</p>";
    } else {
        echo "<p style='color:orange;'>⚠ $var not set (using default)</p>";
    }
}

// Test 7: Composer autoload
echo "<h2>Test 7: Composer Autoload</h2>";
if (file_exists('vendor/autoload.php')) {
    try {
        require_once 'vendor/autoload.php';
        echo "<p style='color:green;'>✓ Composer autoload loaded</p>";
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            echo "<p style='color:green;'>✓ PHPMailer class available</p>";
        } else {
            echo "<p style='color:orange;'>⚠ PHPMailer class not found</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red;'>✗ Error loading autoload: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color:orange;'>⚠ vendor/autoload.php not found. Run: composer install</p>";
}

echo "</body></html>";
?>












