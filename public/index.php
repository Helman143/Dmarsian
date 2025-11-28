<?php
/**
 * Entry point for DigitalOcean App Platform
 * Routes requests to appropriate PHP files
 */

// Get the requested URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$requestPath = ltrim($requestPath, '/');

// Default to webpage.php for root
if (empty($requestPath) || $requestPath === '/') {
    $requestPath = 'webpage.php';
}

// Build file path (go up one directory from public/)
$filePath = __DIR__ . '/../' . $requestPath;

// Check if file exists
if (file_exists($filePath) && is_file($filePath)) {
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    
    // Handle PHP files
    if ($extension === 'php') {
        // Change to file's directory for relative includes
        chdir(dirname($filePath));
        require $filePath;
        exit;
    }
    
    // Handle static files
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'json' => 'application/json',
        'txt' => 'text/plain',
        'html' => 'text/html',
        'xml' => 'application/xml',
        'pdf' => 'application/pdf',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
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
</body>
</html>

