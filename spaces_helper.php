<?php
/**
 * DigitalOcean Spaces Helper Functions
 * Handles image uploads to Spaces with fallback to local storage
 */

// Load environment variables if available
if (file_exists(__DIR__ . '/env-loader.php')) {
    require_once __DIR__ . '/env-loader.php';
}

/**
 * Check if Spaces is configured
 */
function isSpacesConfigured() {
    $key = getenv('SPACES_KEY');
    $secret = getenv('SPACES_SECRET');
    $name = getenv('SPACES_NAME');
    $region = getenv('SPACES_REGION');
    
    return !empty($key) && !empty($secret) && !empty($name) && !empty($region);
}

/**
 * Get Spaces S3 Client
 * @return \Aws\S3\S3Client|null
 */
function getSpacesClient() {
    if (!isSpacesConfigured()) {
        return null;
    }
    
    // Check if AWS SDK is available
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        return null;
    }
    
    require_once __DIR__ . '/vendor/autoload.php';
    
    try {
        $spacesKey = getenv('SPACES_KEY');
        $spacesSecret = getenv('SPACES_SECRET');
        $spacesRegion = getenv('SPACES_REGION') ?: 'nyc3';
        
        return new \Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => $spacesRegion,
            'endpoint' => "https://{$spacesRegion}.digitaloceanspaces.com",
            'credentials' => [
                'key'    => $spacesKey,
                'secret' => $spacesSecret,
            ],
        ]);
    } catch (Exception $e) {
        error_log("Failed to initialize Spaces client: " . $e->getMessage());
        return null;
    }
}

/**
 * Upload image to DigitalOcean Spaces or local storage
 * @param string $tmpFilePath Temporary file path from $_FILES
 * @param string $fileName Desired file name
 * @param string $folder Folder name in Spaces (e.g., 'posts')
 * @return array ['success' => bool, 'path' => string, 'error' => string]
 */
function uploadImageToSpaces($tmpFilePath, $fileName, $folder = 'posts') {
    $s3Client = getSpacesClient();
    $spacesName = getenv('SPACES_NAME');
    
    // Try Spaces first if configured
    if ($s3Client && $spacesName) {
        try {
            $key = $folder . '/' . $fileName;
            
            // Get MIME type
            $mimeType = mime_content_type($tmpFilePath);
            if (!$mimeType) {
                $mimeType = 'image/jpeg'; // Default
            }
            
            $result = $s3Client->putObject([
                'Bucket' => $spacesName,
                'Key'    => $key,
                'Body'   => fopen($tmpFilePath, 'rb'),
                'ACL'    => 'public-read',
                'ContentType' => $mimeType,
            ]);
            
            $publicUrl = $result['ObjectURL'];
            
            return [
                'success' => true,
                'path' => $publicUrl,
                'error' => null
            ];
        } catch (Exception $e) {
            error_log("Spaces upload failed: " . $e->getMessage());
            // Fall through to local storage
        }
    }
    
    // Fallback to local storage
    $uploadDir = 'uploads/' . $folder . '/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'Failed to create upload directory'
            ];
        }
    }
    
    $uploadPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($tmpFilePath, $uploadPath)) {
        return [
            'success' => true,
            'path' => $uploadPath,
            'error' => null
        ];
    }
    
    return [
        'success' => false,
        'path' => null,
        'error' => 'Failed to move uploaded file'
    ];
}

/**
 * Delete image from Spaces or local storage
 * @param string $imagePath Image path (can be Spaces URL or local path)
 * @return bool Success status
 */
function deleteImageFromSpaces($imagePath) {
    // If it's a Spaces URL, extract key and delete from Spaces
    if (preg_match('/digitaloceanspaces\.com\/(.+)$/', $imagePath, $matches)) {
        $s3Client = getSpacesClient();
        $spacesName = getenv('SPACES_NAME');
        
        if ($s3Client && $spacesName) {
            try {
                $s3Client->deleteObject([
                    'Bucket' => $spacesName,
                    'Key'    => $matches[1],
                ]);
                return true;
            } catch (Exception $e) {
                error_log("Failed to delete from Spaces: " . $e->getMessage());
                return false;
            }
        }
    }
    
    // Otherwise, try to delete local file
    if (file_exists($imagePath)) {
        return @unlink($imagePath);
    }
    
    return false;
}
?>








