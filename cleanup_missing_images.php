<?php
/**
 * Cleanup script to remove image_path entries for files that don't exist
 * Run this script via browser: http://localhost/Dmarsian/cleanup_missing_images.php
 */

// Set content type for browser display
header('Content-Type: text/html; charset=utf-8');

require_once 'db_connect.php';

$conn = connectDB();

if (!$conn) {
    die("<html><body><h1>Error</h1><p>Database connection failed. Please check your database configuration.</p></body></html>");
}

// Get all posts with image_path
$sql = "SELECT id, image_path FROM posts WHERE image_path IS NOT NULL AND image_path != ''";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("<html><body><h1>Error</h1><p>Database query failed: " . htmlspecialchars(mysqli_error($conn)) . "</p></body></html>");
}

$cleaned = 0;
$total = 0;
$cleaned_posts = [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cleanup Missing Images</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
        }
        .error {
            color: #f44336;
        }
        .info {
            color: #2196F3;
        }
        .post-item {
            padding: 8px;
            margin: 5px 0;
            background: #f9f9f9;
            border-left: 3px solid #4CAF50;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e9;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Cleanup Missing Images</h1>
        <p class="info">Checking posts for missing image files...</p>
        <hr>

<?php

while ($row = mysqli_fetch_assoc($result)) {
    $total++;
    $image_path = trim($row['image_path']);
    
    // Check if file exists (remove leading / if present)
    $file_path = $image_path;
    if (strpos($file_path, '/') === 0) {
        $file_path = substr($file_path, 1);
    }
    
    if (!file_exists($file_path)) {
        // File doesn't exist - set image_path to NULL
        $update_sql = "UPDATE posts SET image_path = NULL WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $row['id']);
            
            if (mysqli_stmt_execute($stmt)) {
                $cleaned++;
                $cleaned_posts[] = [
                    'id' => $row['id'],
                    'path' => $image_path
                ];
                echo "<div class='post-item'>";
                echo "<strong>Post ID {$row['id']}:</strong> Removed missing image path '<code>" . htmlspecialchars($image_path) . "</code>'";
                echo "</div>";
            } else {
                echo "<div class='post-item error'>";
                echo "<strong>Post ID {$row['id']}:</strong> Error updating - " . htmlspecialchars(mysqli_error($conn));
                echo "</div>";
            }
            
            mysqli_stmt_close($stmt);
        } else {
            echo "<div class='post-item error'>";
            echo "<strong>Post ID {$row['id']}:</strong> Error preparing statement - " . htmlspecialchars(mysqli_error($conn));
            echo "</div>";
        }
    }
}

mysqli_close($conn);

?>
        <div class="summary">
            <h2>Summary</h2>
            <p><strong>Total posts checked:</strong> <?php echo $total; ?></p>
            <p class="success"><strong>Posts cleaned (image_path set to NULL):</strong> <?php echo $cleaned; ?></p>
            <?php if ($cleaned > 0): ?>
                <p class="info">✅ The database has been cleaned. Missing image paths have been removed.</p>
                <p><a href="post_management.php">← Back to Post Management</a></p>
            <?php else: ?>
                <p class="info">✅ No missing images found. All image paths are valid.</p>
                <p><a href="post_management.php">← Back to Post Management</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

