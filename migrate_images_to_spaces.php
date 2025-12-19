<?php
/**
 * Migrate Existing Images to DigitalOcean Spaces
 * 
 * This script uploads existing images from uploads/posts/ to Spaces
 * and updates the database with Spaces URLs.
 * 
 * Usage: php migrate_images_to_spaces.php
 */

require_once __DIR__ . '/env-loader.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/spaces_helper.php';

// Check if Spaces is configured
if (!isSpacesConfigured()) {
    die("ERROR: Spaces not configured. Please set SPACES_KEY, SPACES_SECRET, SPACES_NAME, and SPACES_REGION environment variables.\n");
}

$s3Client = getSpacesClient();
if (!$s3Client) {
    die("ERROR: Failed to initialize Spaces client. Make sure AWS SDK is installed (composer install).\n");
}

$spacesName = getenv('SPACES_NAME');
$uploadDir = __DIR__ . '/uploads/posts/';

if (!is_dir($uploadDir)) {
    die("ERROR: Upload directory not found: $uploadDir\n");
}

echo "Starting migration of images to Spaces...\n";
echo "Space: $spacesName\n";
echo "Directory: $uploadDir\n\n";

// Connect to database
$conn = connectDB();

// Get all posts with local image paths
$sql = "SELECT id, image_path FROM posts WHERE image_path IS NOT NULL AND image_path != '' AND image_path NOT LIKE 'https://%'";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("ERROR: Failed to query database: " . mysqli_error($conn) . "\n");
}

$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
$total = count($posts);
$success = 0;
$failed = 0;

echo "Found $total posts with local images.\n\n";

foreach ($posts as $post) {
    $postId = $post['id'];
    $localPath = $post['image_path'];
    
    // Remove leading slash if present
    $localPath = ltrim($localPath, '/');
    $fullPath = __DIR__ . '/' . $localPath;
    
    echo "Processing post ID $postId: $localPath\n";
    
    if (!file_exists($fullPath)) {
        echo "  ⚠️  File not found, skipping...\n";
        $failed++;
        continue;
    }
    
    $fileName = basename($localPath);
    
    try {
        // Upload to Spaces
        $key = 'posts/' . $fileName;
        $mimeType = mime_content_type($fullPath) ?: 'image/jpeg';
        
        $uploadResult = $s3Client->putObject([
            'Bucket' => $spacesName,
            'Key'    => $key,
            'Body'   => fopen($fullPath, 'rb'),
            'ACL'    => 'public-read',
            'ContentType' => $mimeType,
        ]);
        
        $spacesUrl = $uploadResult['ObjectURL'];
        
        // Update database
        $updateSql = "UPDATE posts SET image_path = ? WHERE id = ?";
        $updateStmt = mysqli_prepare($conn, $updateSql);
        mysqli_stmt_bind_param($updateStmt, "si", $spacesUrl, $postId);
        
        if (mysqli_stmt_execute($updateStmt)) {
            echo "  ✅ Uploaded and updated: $spacesUrl\n";
            $success++;
        } else {
            echo "  ❌ Failed to update database: " . mysqli_error($conn) . "\n";
            $failed++;
        }
        
        mysqli_stmt_close($updateStmt);
        
    } catch (Exception $e) {
        echo "  ❌ Upload failed: " . $e->getMessage() . "\n";
        $failed++;
    }
    
    echo "\n";
}

mysqli_close($conn);

echo "Migration complete!\n";
echo "Success: $success\n";
echo "Failed: $failed\n";
echo "Total: $total\n";
?>
