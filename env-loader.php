<?php
/**
 * Simple Environment Variables Loader
 * Loads .env file and makes variables available via getenv() and $_ENV
 */

function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        error_log("Warning: .env file not found at: $filePath");
        return false;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Set environment variable
            if (!getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
    return true;
}

// Load .env file from the same directory
$envPath = __DIR__ . DIRECTORY_SEPARATOR . '.env';
loadEnv($envPath);
?>