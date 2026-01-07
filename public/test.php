<?php
/**
 * Diagnostic Test File for App Platform
 * This will help identify the 500 error
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnostic Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f0f0f0; padding: 10px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h1>Diagnostic Test - App Platform</h1>
    
    <h2>1. PHP Information</h2>
    <p class="success">✓ PHP Version: <?php echo phpversion(); ?></p>
    <p class="success">✓ Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
    
    <h2>2. File System Checks</h2>
    <?php
    $rootDir = __DIR__ . '/..';
    $filesToCheck = [
        'config.php',
        'env-loader.php',
        'webpage.php',
        'db_connect.php',
        'composer.json',
    ];
    
    foreach ($filesToCheck as $file) {
        $path = $rootDir . '/' . $file;
        if (file_exists($path)) {
            echo "<p class='success'>✓ {$file} exists</p>";
        } else {
            echo "<p class='error'>✗ {$file} NOT FOUND at: {$path}</p>";
        }
    }
    ?>
    
    <h2>3. Environment Variables</h2>
    <?php
    $envVars = ['DB_HOST', 'DB_USER', 'DB_NAME', 'DB_PORT', 'APP_ENV'];
    foreach ($envVars as $var) {
        $value = getenv($var);
        if ($value !== false) {
            // Mask sensitive values
            if (in_array($var, ['DB_PASS', 'DB_USER'])) {
                echo "<p class='success'>✓ {$var} is set (value hidden)</p>";
            } else {
                echo "<p class='success'>✓ {$var} = " . htmlspecialchars($value) . "</p>";
            }
        } else {
            echo "<p class='warning'>⚠ {$var} is NOT set</p>";
        }
    }
    ?>
    
    <h2>4. Database Connection Test</h2>
    <?php
    try {
        $configPath = $rootDir . '/config.php';
        if (file_exists($configPath)) {
            require_once $configPath;
            
            if (isset($conn)) {
                if ($conn && !$conn->connect_error) {
                    echo "<p class='success'>✓ Database connection successful</p>";
                    echo "<p>Database: " . htmlspecialchars(getenv('DB_NAME') ?: 'Not set') . "</p>";
                } else {
                    echo "<p class='error'>✗ Database connection failed</p>";
                    if (isset($conn->connect_error)) {
                        echo "<p>Error: " . htmlspecialchars($conn->connect_error) . "</p>";
                    }
                }
            } else {
                echo "<p class='error'>✗ \$conn variable not set after loading config.php</p>";
            }
        } else {
            echo "<p class='error'>✗ config.php not found</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
    ?>
    
    <h2>5. Required PHP Extensions</h2>
    <?php
    $extensions = ['mysqli', 'mbstring', 'curl', 'gd', 'json', 'xml'];
    foreach ($extensions as $ext) {
        if (extension_loaded($ext)) {
            echo "<p class='success'>✓ {$ext} extension loaded</p>";
        } else {
            echo "<p class='error'>✗ {$ext} extension NOT loaded</p>";
        }
    }
    ?>
    
    <h2>6. Composer Dependencies</h2>
    <?php
    $vendorAutoload = $rootDir . '/vendor/autoload.php';
    if (file_exists($vendorAutoload)) {
        echo "<p class='success'>✓ vendor/autoload.php exists</p>";
        try {
            require_once $vendorAutoload;
            echo "<p class='success'>✓ Composer autoloader loaded</p>";
        } catch (Exception $e) {
            echo "<p class='error'>✗ Failed to load autoloader: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p class='warning'>⚠ vendor/autoload.php not found (run: composer install)</p>";
    }
    ?>
    
    <h2>7. Test Loading webpage.php</h2>
    <?php
    $webpagePath = $rootDir . '/webpage.php';
    if (file_exists($webpagePath)) {
        echo "<p class='success'>✓ webpage.php exists</p>";
        echo "<p>Attempting to load webpage.php...</p>";
        
        try {
            ob_start();
            $oldDir = getcwd();
            chdir(dirname($webpagePath));
            $result = @include $webpagePath;
            $output = ob_get_clean();
            chdir($oldDir);
            
            if ($result !== false) {
                echo "<p class='success'>✓ webpage.php loaded without fatal errors</p>";
                if (!empty($output)) {
                    echo "<p class='warning'>⚠ webpage.php produced output (this is normal for HTML pages)</p>";
                    echo "<p>Output length: " . strlen($output) . " bytes</p>";
                }
            } else {
                echo "<p class='error'>✗ Failed to include webpage.php</p>";
            }
        } catch (Throwable $e) {
            echo "<p class='error'>✗ Exception loading webpage.php:</p>";
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "\n";
            echo "File: " . htmlspecialchars($e->getFile()) . "\n";
            echo "Line: " . $e->getLine() . "\n";
            echo "\nStack Trace:\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
    } else {
        echo "<p class='error'>✗ webpage.php NOT FOUND</p>";
    }
    ?>
    
    <h2>8. Directory Permissions</h2>
    <?php
    $dirsToCheck = [
        'uploads' => $rootDir . '/uploads',
        'uploads/posts' => $rootDir . '/uploads/posts',
    ];
    
    foreach ($dirsToCheck as $name => $path) {
        if (is_dir($path)) {
            $writable = is_writable($path);
            if ($writable) {
                echo "<p class='success'>✓ {$name} directory exists and is writable</p>";
            } else {
                echo "<p class='warning'>⚠ {$name} directory exists but is NOT writable</p>";
            }
        } else {
            echo "<p class='warning'>⚠ {$name} directory does NOT exist</p>";
        }
    }
    ?>
    
    <h2>9. Server Information</h2>
    <pre>
Server Software: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>

Document Root: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Not set'; ?>

Script Filename: <?php echo __FILE__; ?>

Current Working Directory: <?php echo getcwd(); ?>

Root Directory: <?php echo $rootDir; ?>
    </pre>
    
    <hr>
    <p><a href="/">← Back to Home</a></p>
    <p><small>Delete this file after fixing the issue!</small></p>
</body>
</html>



