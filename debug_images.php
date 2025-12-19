<?php
/**
 * Diagnostic endpoint for image loading issues
 * Access: https://your-app.ondigitalocean.app/debug_images.php
 * Remove this file after debugging is complete
 */

header('Content-Type: application/json');

$debug_info = [
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => [
        'is_production' => strpos($_SERVER['HTTP_HOST'] ?? '', 'ondigitalocean.app') !== false,
        'http_host' => $_SERVER['HTTP_HOST'] ?? 'unknown',
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    ],
    'base_path_detection' => [
        'detected_base_path' => getBasePath(),
    ],
    'uploads_directory' => [
        'exists' => is_dir(__DIR__ . '/uploads/posts'),
        'path' => __DIR__ . '/uploads/posts',
        'readable' => is_readable(__DIR__ . '/uploads/posts'),
        'file_count' => is_dir(__DIR__ . '/uploads/posts') ? count(glob(__DIR__ . '/uploads/posts/*')) : 0,
    ],
    'sample_files' => [],
    'database_posts' => [],
];

// Get base path function
function getBasePath() {
    $isProduction = getenv('APP_ENV') === 'production' || 
                    getenv('APP_ENV') === 'prod' || 
                    strpos($_SERVER['HTTP_HOST'] ?? '', 'ondigitalocean.app') !== false ||
                    strpos($_SERVER['SERVER_NAME'] ?? '', 'ondigitalocean.app') !== false;
    
    if ($isProduction) {
        return '';
    }
    
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir = dirname($scriptName);
    
    if ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') {
        return '';
    }
    
    return rtrim($scriptDir, '/\\');
}

// Check sample files in uploads
if (is_dir(__DIR__ . '/uploads/posts')) {
    $files = glob(__DIR__ . '/uploads/posts/*.{jpg,jpeg,png,gif,jfif}', GLOB_BRACE);
    $debug_info['sample_files'] = array_slice(array_map(function($file) {
        return [
            'name' => basename($file),
            'path' => $file,
            'relative_path' => 'uploads/posts/' . basename($file),
            'exists' => file_exists($file),
            'readable' => is_readable($file),
            'size' => file_exists($file) ? filesize($file) : 0,
            'url_path' => '/' . 'uploads/posts/' . basename($file),
        ];
    }, $files), 0, 5);
}

// Get sample posts from database
try {
    require_once 'db_connect.php';
    $conn = connectDB();
    
    $sql = "SELECT id, title, image_path FROM posts WHERE image_path IS NOT NULL AND image_path != '' LIMIT 5";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $img_path = $row['image_path'];
            $file_path = ltrim($img_path, '/');
            $absolute_path = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file_path);
            
            $debug_info['database_posts'][] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'image_path_db' => $img_path,
                'file_path_relative' => $file_path,
                'file_path_absolute' => $absolute_path,
                'file_exists' => file_exists($absolute_path) || file_exists($file_path),
                'constructed_url' => (getBasePath() === '' ? '/' : getBasePath() . '/') . ltrim($img_path, '/'),
            ];
        }
    }
    
    mysqli_close($conn);
} catch (Exception $e) {
    $debug_info['database_error'] = $e->getMessage();
}

echo json_encode($debug_info, JSON_PRETTY_PRINT);
?>
