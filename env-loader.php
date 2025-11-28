<?php
/**
 * Simple .env file loader
 * Loads environment variables from .env file into $_ENV and $_SERVER
 * 
 * Usage: require_once 'env-loader.php';
 */

function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        // .env file doesn't exist, skip loading
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE format
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            
            // Trim whitespace
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Set environment variable if not already set
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
            if (!isset($_SERVER[$key])) {
                $_SERVER[$key] = $value;
            }
            
            // Also set via putenv for getenv() compatibility
            putenv("$key=$value");
        }
    }
}

// Load .env file from the same directory as this file
loadEnv(__DIR__ . '/.env');

