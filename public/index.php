<?php
/**
 * Entry point for DigitalOcean App Platform
 * Routes requests to appropriate PHP files
 * 
 * LANDING PAGE: webpage.php is the default landing page for all visitors
 * Root URL (/) and index requests are automatically routed to webpage.php
 */

// ============================================
// CRITICAL: Handle root requests FIRST
// ============================================
// Check if this is a root request BEFORE any other processing
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedPath = parse_url($requestUri, PHP_URL_PATH);
$requestPath = ($parsedPath !== false && $parsedPath !== null) ? $parsedPath : '/';

// If root request, serve webpage.php IMMEDIATELY
if ($requestPath === '/' || trim($requestPath, '/') === '') {
    $webpagePath = __DIR__ . '/../webpage.php';
    if (file_exists($webpagePath)) {
        chdir(dirname($webpagePath));
        require $webpagePath;
        exit;
    }
}
// ============================================

// Enable error reporting for debugging (disable in production after fixing)
// Check if we're in production mode
$isProduction = (getenv('APP_ENV') === 'production' || getenv('APP_ENV') === 'prod');

if (!$isProduction) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', '/tmp/php_errors.log');
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Process non-root requests
// If we reach here, it's not a root request, so process normally
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedPath = parse_url($requestUri, PHP_URL_PATH);
$requestPath = ($parsedPath !== false && $parsedPath !== null) ? $parsedPath : '/';

// Remove leading slash for processing
$requestPath = ltrim($requestPath, '/');

// Remove query string from path for file checking
$requestPath = strtok($requestPath, '?');

// Additional check: catch any remaining root/index requests
if (empty($requestPath) || 
    $requestPath === 'index.html' ||
    $requestPath === 'index') {
    $requestPath = 'webpage.php';
}

// Build file path (go up one directory from public/)
$filePath = __DIR__ . '/../' . $requestPath;

// SAFETY CHECK: If somehow index.php is being requested for root, redirect to webpage.php
if ($requestPath === 'index.php' && ($_SERVER['REQUEST_URI'] ?? '/') === '/') {
    $requestPath = 'webpage.php';
    $filePath = __DIR__ . '/../' . $requestPath;
}

// Normalize path to prevent directory traversal
$filePath = realpath($filePath);
$rootPath = realpath(__DIR__ . '/..');

// Security check: ensure file is within project root
if ($filePath === false || strpos($filePath, $rootPath) !== 0) {
    http_response_code(403);
    header('Content-Type: text/html');
    echo '<!DOCTYPE html><html><head><title>403 - Forbidden</title></head><body><h1>403 - Forbidden</h1><p>Access denied.</p></body></html>';
    exit;
}

// Check if file exists
if (file_exists($filePath) && is_file($filePath)) {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    // Handle PHP files
    if ($extension === 'php') {
        try {
            // Change to file's directory for relative includes
            $originalDir = getcwd();
            chdir(dirname($filePath));
            
            // Capture any output before requiring
            ob_start();
            require $filePath;
            $output = ob_get_clean();
            
            // Restore original directory
            chdir($originalDir);
            
            // Output the file's content
            echo $output;
            exit;
        } catch (Throwable $e) {
            // Restore directory on error
            if (isset($originalDir)) {
                chdir($originalDir);
            }
            
            http_response_code(500);
            header('Content-Type: text/html');
            
            if (!$isProduction) {
                echo '<!DOCTYPE html><html><head><title>500 - Internal Server Error</title></head><body>';
                echo '<h1>500 - Internal Server Error</h1>';
                echo '<h2>Error Details:</h2>';
                echo '<pre style="background:#f0f0f0;padding:10px;border:1px solid #ccc;">';
                echo 'Message: ' . htmlspecialchars($e->getMessage()) . "\n";
                echo 'File: ' . htmlspecialchars($e->getFile()) . "\n";
                echo 'Line: ' . $e->getLine() . "\n";
                echo 'Stack Trace:' . "\n" . htmlspecialchars($e->getTraceAsString());
                echo '</pre>';
                echo '</body></html>';
            } else {
                echo '<!DOCTYPE html><html><head><title>500 - Internal Server Error</title></head><body>';
                echo '<h1>500 - Internal Server Error</h1>';
                echo '<p>The server encountered an error. Please try again later.</p>';
                echo '</body></html>';
            }
            
            // Log the error
            error_log("PHP Error in {$filePath}: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            exit;
        }
    }
    
    // Handle static files
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'jfif' => 'image/jpeg',
        'svg' => 'image/svg+xml',
        'json' => 'application/json',
        'txt' => 'text/plain',
        'html' => 'text/html',
        'xml' => 'application/xml',
        'pdf' => 'application/pdf',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
    ];
    
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    header('Content-Type: ' . $mimeType);
    
    // Set cache headers for static files
    if (in_array($extension, ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'woff', 'woff2', 'ttf', 'eot'])) {
        header('Cache-Control: public, max-age=31536000');
    }
    
    readfile($filePath);
    exit;
}

// 404 Not Found
http_response_code(404);
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
    <title>404 - Not Found</title>
</head>
<body>
    <h1>404 - File Not Found</h1>
    <p>The requested file could not be found.</p>
    <?php if (!$isProduction): ?>
    <p><small>Requested path: <?php echo htmlspecialchars($requestPath); ?></small></p>
    <?php endif; ?>
</body>
</html>

