<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, just log them
ini_set('log_errors', 1);

header('Content-Type: application/json');
// Prevent caching of API responses - ensure no browser or proxy caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');
// Add ETag prevention
header('ETag: ' . md5(uniqid()));

try {
    // Load database connection
    require_once 'db_connect.php';
    
    // Get connection - use global $conn if available, otherwise create new
    if (!isset($conn) || (isset($conn->connect_error) && $conn->connect_error)) {
        $conn = connectDB();
    }
    
    // Check if connection is valid
    if (!$conn || (isset($conn->connect_error) && $conn->connect_error)) {
        throw new Exception('Database connection failed');
    }
    
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

    // Show posts from current year and previous year (last 2 years)
    // Status is ENUM('active','archived'), so it cannot be NULL - only check for 'active'
    $currentYear = date('Y');
    $previousYear = $currentYear - 1;
    
    // Check if show_in_slider column exists
    $columnExists = false;
    $checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM posts LIKE 'show_in_slider'");
    if ($checkColumn && mysqli_num_rows($checkColumn) > 0) {
        $columnExists = true;
    }
    
    // Build base query - only filter by show_in_slider if column exists
    if ($columnExists) {
        $sql = "SELECT * FROM posts WHERE (YEAR(post_date) = ? OR YEAR(post_date) = ?) AND status = 'active' AND (show_in_slider = 1 OR show_in_slider IS NULL)";
    } else {
        // Column doesn't exist yet - show all active posts (restore images)
        $sql = "SELECT * FROM posts WHERE (YEAR(post_date) = ? OR YEAR(post_date) = ?) AND status = 'active'";
    }
    $params = [$currentYear, $previousYear];
    $types = "ii";

    if ($category) {
        if ($category === 'achievement') {
            $sql .= " AND (category = 'achievement' OR category = 'achievement_event')";
        } elseif ($category === 'event') {
            $sql .= " AND (category = 'event' OR category = 'achievement_event')";
        } else {
            $sql .= " AND category = ?";
            $params[] = $category;
            $types .= "s";
        }
    }

    $sql .= " ORDER BY post_date DESC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to execute statement: ' . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        throw new Exception('Failed to get result: ' . mysqli_error($conn));
    }
    
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
    if ($posts === false) {
        $posts = [];
    }
    
    // Don't validate file existence in API - let browser handle missing images
    // The file_exists() check can fail on production even when files exist
    // Browser onerror handlers will gracefully handle 404s
    // Only normalize null/empty values
    foreach ($posts as &$post) {
        if (empty($post['image_path']) || trim($post['image_path']) === '') {
            $post['image_path'] = null;
        } else {
            // Keep the path as-is, just trim whitespace
            $post['image_path'] = trim($post['image_path']);
        }
    }
    unset($post); // Break reference

    mysqli_stmt_close($stmt);
    
    // Don't close the global connection if it exists
    if (!isset($GLOBALS['conn']) || $GLOBALS['conn'] !== $conn) {
        mysqli_close($conn);
    }

    echo json_encode($posts);
    
} catch (Exception $e) {
    error_log("get_posts.php error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch posts',
        'message' => $e->getMessage()
    ]);
}
?> 