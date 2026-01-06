<?php
/**
 * Diagnostic Script for Deployment Troubleshooting
 * Access this file via browser to check system configuration
 * DELETE THIS FILE AFTER TROUBLESHOOTING FOR SECURITY
 */

// Enable error reporting for diagnostics
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>System Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #f44336; font-weight: bold; }
        .warning { color: #ff9800; font-weight: bold; }
        .info { color: #2196F3; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #4CAF50; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 System Diagnostic Report</h1>
        <p class="info">Generated: <?php echo date('Y-m-d H:i:s'); ?></p>

        <h2>1. PHP Configuration</h2>
        <table>
            <tr>
                <th>Setting</th>
                <th>Value</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>PHP Version</td>
                <td><?php echo phpversion(); ?></td>
                <td class="success">✓</td>
            </tr>
            <tr>
                <td>Server API</td>
                <td><?php echo php_sapi_name(); ?></td>
                <td class="success">✓</td>
            </tr>
            <tr>
                <td>Document Root</td>
                <td><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Not set'; ?></td>
                <td class="info">-</td>
            </tr>
            <tr>
                <td>Script Path</td>
                <td><?php echo __FILE__; ?></td>
                <td class="info">-</td>
            </tr>
        </table>

        <h2>2. Required PHP Extensions</h2>
        <table>
            <tr>
                <th>Extension</th>
                <th>Status</th>
            </tr>
            <?php
            $required_extensions = ['mysqli', 'mbstring', 'curl', 'gd', 'zip', 'xml', 'json'];
            foreach ($required_extensions as $ext) {
                $loaded = extension_loaded($ext);
                echo "<tr>";
                echo "<td>{$ext}</td>";
                echo "<td class='" . ($loaded ? 'success' : 'error') . "'>" . ($loaded ? '✓ Installed' : '✗ Missing') . "</td>";
                echo "</tr>";
            }
            ?>
        </table>

        <h2>3. File System Checks</h2>
        <table>
            <tr>
                <th>File/Directory</th>
                <th>Status</th>
                <th>Permissions</th>
            </tr>
            <?php
            $files_to_check = [
                'config.php',
                'env-loader.php',
                '.env',
                'composer.json',
                'vendor/autoload.php',
                'uploads',
                '.htaccess'
            ];
            
            foreach ($files_to_check as $file) {
                $exists = file_exists($file);
                $readable = $exists ? is_readable($file) : false;
                $perms = $exists ? substr(sprintf('%o', fileperms($file)), -4) : 'N/A';
                
                echo "<tr>";
                echo "<td>{$file}</td>";
                if ($exists) {
                    echo "<td class='success'>✓ Exists" . ($readable ? '' : ' (Not readable)') . "</td>";
                    echo "<td>{$perms}</td>";
                } else {
                    echo "<td class='error'>✗ Missing</td>";
                    echo "<td>-</td>";
                }
                echo "</tr>";
            }
            ?>
        </table>

        <h2>4. Environment Variables</h2>
        <table>
            <tr>
                <th>Variable</th>
                <th>Value</th>
                <th>Status</th>
            </tr>
            <?php
            $env_vars = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME', 'DB_PORT'];
            foreach ($env_vars as $var) {
                $value = getenv($var);
                $set = $value !== false && $value !== '';
                echo "<tr>";
                echo "<td>{$var}</td>";
                echo "<td>" . ($set ? (strpos($var, 'PASS') !== false ? '***hidden***' : $value) : 'Not set') . "</td>";
                echo "<td class='" . ($set ? 'success' : 'error') . "'>" . ($set ? '✓ Set' : '✗ Missing') . "</td>";
                echo "</tr>";
            }
            ?>
        </table>

        <h2>5. Config.php Test</h2>
        <?php
        try {
            if (file_exists('config.php')) {
                ob_start();
                $config_loaded = @include 'config.php';
                $output = ob_get_clean();
                
                if ($config_loaded !== false) {
                    echo "<p class='success'>✓ config.php loaded successfully</p>";
                    if (isset($conn)) {
                        if ($conn->connect_error) {
                            echo "<p class='error'>✗ Database connection failed: " . htmlspecialchars($conn->connect_error) . "</p>";
                        } else {
                            echo "<p class='success'>✓ Database connection successful</p>";
                        }
                    }
                } else {
                    echo "<p class='error'>✗ Failed to load config.php</p>";
                    if ($output) {
                        echo "<pre>" . htmlspecialchars($output) . "</pre>";
                    }
                }
            } else {
                echo "<p class='error'>✗ config.php file not found</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>

        <h2>6. Composer Autoload</h2>
        <?php
        if (file_exists('vendor/autoload.php')) {
            try {
                require_once 'vendor/autoload.php';
                echo "<p class='success'>✓ Composer autoload loaded successfully</p>";
                
                if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                    echo "<p class='success'>✓ PHPMailer class available</p>";
                } else {
                    echo "<p class='warning'>⚠ PHPMailer class not found (may need composer install)</p>";
                }
            } catch (Exception $e) {
                echo "<p class='error'>✗ Error loading autoload: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p class='warning'>⚠ vendor/autoload.php not found. Run: <span class='code'>composer install</span></p>";
        }
        ?>

        <h2>7. Apache/.htaccess</h2>
        <?php
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            $required_modules = ['mod_rewrite'];
            echo "<table>";
            echo "<tr><th>Module</th><th>Status</th></tr>";
            foreach ($required_modules as $module) {
                $loaded = in_array($module, $modules);
                echo "<tr>";
                echo "<td>{$module}</td>";
                echo "<td class='" . ($loaded ? 'success' : 'error') . "'>" . ($loaded ? '✓ Loaded' : '✗ Not loaded') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='info'>Apache module check not available (may be using different server)</p>";
        }
        ?>

        <h2>8. Error Log Location</h2>
        <table>
            <tr>
                <th>Log Type</th>
                <th>Location</th>
            </tr>
            <tr>
                <td>PHP Error Log</td>
                <td><?php echo ini_get('error_log') ?: 'Not configured'; ?></td>
            </tr>
            <tr>
                <td>Apache Error Log</td>
                <td>/var/log/apache2/error.log (typical location)</td>
            </tr>
        </table>

        <h2>9. Quick Fixes</h2>
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <h3>Common Issues & Solutions:</h3>
            <ol>
                <li><strong>Missing vendor folder:</strong> Run <span class="code">composer install --no-dev</span></li>
                <li><strong>Missing .env file:</strong> Copy <span class="code">env.example</span> to <span class="code">.env</span> and configure</li>
                <li><strong>Permission issues:</strong> Run <span class="code">chmod -R 755 .</span> and <span class="code">chmod -R 777 uploads/</span></li>
                <li><strong>Database connection:</strong> Check .env file has correct DB credentials</li>
                <li><strong>Apache errors:</strong> Check <span class="code">tail -f /var/log/apache2/error.log</span></li>
            </ol>
        </div>

        <div style="background: #f8d7da; padding: 15px; border-radius: 5px; margin-top: 20px;">
            <p class="error"><strong>⚠️ SECURITY WARNING:</strong></p>
            <p>Delete this diagnostic.php file after troubleshooting for security reasons!</p>
            <p>Run: <span class="code">rm diagnostic.php</span></p>
        </div>
    </div>
</body>
</html>




























