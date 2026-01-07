<?php
/**
 * Upload Video to DigitalOcean Spaces
 * 
 * This script uploads the hero video to DigitalOcean Spaces.
 * Run this once to upload your video file.
 * 
 * Usage:
 *   1. Set environment variables (SPACES_KEY, SPACES_SECRET, SPACES_NAME, SPACES_REGION)
 *   2. Run: php upload_video_to_spaces.php
 */

require_once __DIR__ . '/env-loader.php';

// Check if AWS SDK is available
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die("ERROR: Composer dependencies not installed. Run: composer install\n");
}

require_once __DIR__ . '/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Get configuration from environment variables
$spacesKey = getenv('SPACES_KEY');
$spacesSecret = getenv('SPACES_SECRET');
$spacesName = getenv('SPACES_NAME');
$spacesRegion = getenv('SPACES_REGION') ?: 'nyc3';

// Validate required variables
if (empty($spacesKey) || empty($spacesSecret) || empty($spacesName)) {
    die("ERROR: Missing required environment variables.\n" .
        "Please set: SPACES_KEY, SPACES_SECRET, SPACES_NAME\n" .
        "Optional: SPACES_REGION (defaults to nyc3)\n");
}

// Video file path
$videoFile = __DIR__ . '/Video/quality_restoration_20251105174029661.mp4';

if (!file_exists($videoFile)) {
    die("ERROR: Video file not found: $videoFile\n");
}

$fileSize = filesize($videoFile);
echo "Found video file: " . basename($videoFile) . " (" . round($fileSize / 1024 / 1024, 2) . " MB)\n";

// Initialize S3 client for DigitalOcean Spaces
try {
    $s3Client = new S3Client([
        'version' => 'latest',
        'region'  => $spacesRegion,
        'endpoint' => "https://{$spacesRegion}.digitaloceanspaces.com",
        'credentials' => [
            'key'    => $spacesKey,
            'secret' => $spacesSecret,
        ],
    ]);

    echo "Connecting to Spaces: {$spacesName} ({$spacesRegion})\n";

    // Upload the video
    $key = 'videos/' . basename($videoFile);
    
    echo "Uploading to: {$key}\n";
    echo "This may take a few minutes for large files...\n";

    $result = $s3Client->putObject([
        'Bucket' => $spacesName,
        'Key'    => $key,
        'Body'   => fopen($videoFile, 'rb'),
        'ACL'    => 'public-read',
        'ContentType' => 'video/mp4',
    ]);

    $publicUrl = $result['ObjectURL'];
    
    echo "\n✅ SUCCESS!\n";
    echo "Video uploaded successfully.\n";
    echo "Public URL: {$publicUrl}\n\n";
    echo "Next steps:\n";
    echo "1. Copy the URL above\n";
    echo "2. Set HERO_VIDEO_URL environment variable in App Platform:\n";
    echo "   {$publicUrl}\n";
    echo "3. Or add it to your .env file:\n";
    echo "   HERO_VIDEO_URL={$publicUrl}\n";

} catch (AwsException $e) {
    echo "\n❌ ERROR: Failed to upload video\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getAwsErrorCode() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}























