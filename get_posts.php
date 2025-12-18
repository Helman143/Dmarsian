<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, just log them
ini_set('log_errors', 1);

header('Content-Type: application/json');

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

    $sql = "SELECT * FROM posts WHERE YEAR(post_date) = ? AND (status = 'active' OR status IS NULL)";
    $params = [$year];
    $types = "i";

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
    
    // Validate and fix image paths - check if files exist
    foreach ($posts as &$post) {
        if (!empty($post['image_path']) && trim($post['image_path']) !== '') {
            $img_path = trim($post['image_path']);
            // Check if file exists (use original path format)
            $file_path = $img_path;
            // Remove leading / for file system check if present
            if (strpos($file_path, '/') === 0) {
                $file_path = substr($file_path, 1);
            }
            if (!file_exists($file_path)) {
                // File doesn't exist - set to null so client can use placeholder
                $post['image_path'] = null;
            }
        } else {
            $post['image_path'] = null;
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