<?php
/**
 * Health Check Endpoint for DigitalOcean App Platform
 * 
 * This endpoint can be used for:
 * - App Platform health checks
 * - Monitoring services
 * - Load balancer health checks
 * 
 * Returns HTTP 200 if application is healthy
 * Returns HTTP 503 if application is unhealthy
 */

header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => []
];

$all_healthy = true;

// Check 1: Database Connection
try {
    require_once __DIR__ . '/config.php';
    
    if (isset($conn) && $conn && !$conn->connect_error) {
        // Test query
        $result = $conn->query("SELECT 1");
        if ($result) {
            $health['checks']['database'] = 'ok';
        } else {
            $health['checks']['database'] = 'error: query failed';
            $all_healthy = false;
        }
    } else {
        $health['checks']['database'] = 'error: connection failed';
        $all_healthy = false;
    }
} catch (Exception $e) {
    $health['checks']['database'] = 'error: ' . $e->getMessage();
    $all_healthy = false;
}

// Check 2: Required Directories
$required_dirs = [
    'uploads/posts' => 'uploads/posts',
];

foreach ($required_dirs as $name => $path) {
    if (is_dir($path) && is_writable($path)) {
        $health['checks']['directory_' . $name] = 'ok';
    } else {
        $health['checks']['directory_' . $name] = 'warning: not writable or missing';
        // Don't fail health check for this, just warn
    }
}

// Check 3: PHP Version
$php_version = phpversion();
$health['checks']['php_version'] = $php_version;
if (version_compare($php_version, '7.4.0', '<')) {
    $health['checks']['php_version'] = 'warning: version ' . $php_version . ' is below recommended 7.4+';
}

// Check 4: Required Extensions
$required_extensions = ['mysqli', 'mbstring', 'curl', 'gd', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        $health['checks']['extension_' . $ext] = 'ok';
    } else {
        $health['checks']['extension_' . $ext] = 'error: missing';
        $all_healthy = false;
    }
}

// Check 5: Environment Variables
$required_env_vars = ['DB_HOST', 'DB_USER', 'DB_NAME'];
foreach ($required_env_vars as $var) {
    if (getenv($var)) {
        $health['checks']['env_' . $var] = 'ok';
    } else {
        $health['checks']['env_' . $var] = 'error: not set';
        $all_healthy = false;
    }
}

// Set overall status
if (!$all_healthy) {
    $health['status'] = 'unhealthy';
    http_response_code(503);
} else {
    $health['status'] = 'healthy';
    http_response_code(200);
}

// Output JSON
echo json_encode($health, JSON_PRETTY_PRINT);



