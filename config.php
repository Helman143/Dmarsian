<?php
// Load environment variables (gracefully handle missing file)
if (file_exists(__DIR__ . '/env-loader.php')) {
    require_once(__DIR__ . '/env-loader.php');
}

// Get database credentials from environment variables (with safe fallbacks)
$servername = getenv('DB_HOST') ?: 'localhost';
$username   = getenv('DB_USER') ?: 'root';
$password   = getenv('DB_PASS') ?: '';
$database   = getenv('DB_NAME') ?: 'capstone_db';
$portStr    = getenv('DB_PORT') ?: '3306';
$port       = (int)$portStr;

// Check if template variables are unresolved (Digital Ocean App Platform issue)
// Template variables like ${db.HOSTNAME} should be resolved, but if they're not,
// we'll detect them and log an error
$templateVars = ['${db.HOSTNAME}', '${db.USERNAME}', '${db.PASSWORD}', '${db.DATABASE}', '${db.PORT}'];
$unresolved = [];

if (strpos($servername, '${') === 0) {
    $unresolved[] = 'DB_HOST';
    error_log("ERROR: DB_HOST contains unresolved template variable: {$servername}");
}
if (strpos($username, '${') === 0) {
    $unresolved[] = 'DB_USER';
    error_log("ERROR: DB_USER contains unresolved template variable: {$username}");
}
if (strpos($password, '${') === 0) {
    $unresolved[] = 'DB_PASS';
    error_log("ERROR: DB_PASS contains unresolved template variable");
}
if (strpos($database, '${') === 0) {
    $unresolved[] = 'DB_NAME';
    error_log("ERROR: DB_NAME contains unresolved template variable: {$database}");
}
if (strpos($portStr, '${') === 0) {
    $unresolved[] = 'DB_PORT';
    error_log("ERROR: DB_PORT contains unresolved template variable: {$portStr}");
    $port = 3306; // Use default port if unresolved
}

// If we have unresolved template variables, don't attempt connection
if (!empty($unresolved)) {
    error_log("CRITICAL: Database environment variables not resolved. Unresolved: " . implode(', ', $unresolved));
    error_log("SOLUTION: You are using an external database (not an App Platform component).");
    error_log("You MUST manually set these environment variables in App Platform Dashboard:");
    error_log("1. Go to: App Platform -> Your App -> Settings -> App-Level Environment Variables");
    error_log("2. Click 'Edit'");
    error_log("3. REMOVE any variables showing \${db.*} (template variables)");
    error_log("4. ADD these variables with your ACTUAL database connection details:");
    error_log("   - DB_HOST = your-database-hostname.db.ondigitalocean.com");
    error_log("   - DB_USER = your-database-username");
    error_log("   - DB_PASS = your-database-password");
    error_log("   - DB_NAME = your-database-name");
    error_log("   - DB_PORT = 25060 (or your database port)");
    error_log("5. Click 'Save' and redeploy your app");
    $conn = false;
} else {
    // Create connection using the same logic as db_connect.php
    // Disable mysqli exceptions to handle errors manually
    $oldReport = mysqli_report(MYSQLI_REPORT_OFF);
    
    try {
        $conn = mysqli_init();
        if ($conn === false) {
            error_log('mysqli_init failed');
            $conn = false;
        } else {
            // Set connection timeout to prevent long hangs
            mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 5);
            mysqli_options($conn, MYSQLI_OPT_READ_TIMEOUT, 5);
            
            // Managed DBs typically require SSL. Allow connection even if CA cert isn't provided
            mysqli_ssl_set($conn, null, null, null, null, null);
            $flags = MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;

            // Attempt connection with error handling
            $connected = @$conn->real_connect($servername, $username, $password, $database, $port, null, $flags);
            
            if (!$connected) {
                $errno = mysqli_connect_errno();
                $error = mysqli_connect_error();
                
                // Log detailed error information
                error_log("Database connection failed ({$servername}:{$port})");
                error_log("Error Code: {$errno}");
                error_log("Error Message: {$error}");
                
                // Provide specific guidance based on error
                if ($errno == 2002 || strpos($error, 'timed out') !== false || strpos($error, 'Connection timed out') !== false) {
                    error_log("CONNECTION TIMEOUT - This usually means:");
                    error_log("1. Database firewall is blocking App Platform IPs");
                    error_log("2. Database hostname/port is incorrect");
                    error_log("3. Database is not accessible from App Platform network");
                    error_log("SOLUTION: Check database 'Trusted Sources' settings and allow App Platform access");
                    error_log("Go to: Digital Ocean -> Databases -> Your Database -> Settings -> Trusted Sources");
                    error_log("Enable 'Allow connections from App Platform' or add App Platform to trusted sources");
                } elseif ($errno == 1045) {
                    error_log("ACCESS DENIED - Authentication failed");
                    error_log("User: {$username}");
                    error_log("This usually means:");
                    error_log("1. Database password is incorrect in environment variables");
                    error_log("2. Database username is incorrect");
                    error_log("3. User doesn't have permission to connect from App Platform IP");
                    error_log("SOLUTION:");
                    error_log("1. Go to Digital Ocean -> Databases -> Your Database -> Users");
                    error_log("2. Verify the username and reset password if needed");
                    error_log("3. Copy the correct username and password");
                    error_log("4. Update DB_USER and DB_PASS in App Platform environment variables");
                    error_log("5. Ensure user has proper permissions");
                } elseif ($errno == 1049) {
                    error_log("DATABASE NOT FOUND - Check DB_NAME environment variable");
                }
            



                
                $conn = false;
            } else {
                mysqli_set_charset($conn, 'utf8mb4');
                error_log("Database connection successful ({$servername}:{$port})");
            }
        }
    } catch (Exception $e) {
        error_log("Database connection exception: " . $e->getMessage());
        error_log("Exception Code: " . $e->getCode());
        
        if (strpos($e->getMessage(), 'timed out') !== false || strpos($e->getMessage(), 'Connection timed out') !== false) {
            error_log("CONNECTION TIMEOUT DETECTED");
            error_log("SOLUTION: Check database firewall/trusted sources settings");
            error_log("Go to: Digital Ocean -> Databases -> Your Database -> Settings -> Trusted Sources");
            error_log("Add App Platform or allow all App Platform IPs");
        }
        
        $conn = false;
    } finally {
        // Restore previous mysqli_report setting
        mysqli_report($oldReport);
    }
}

// Legacy constants for backward compatibility (if needed by other files)
// Only set constants if we have valid values (not unresolved template variables)
if (!defined('DB_SERVER')) {
    // Use empty string if template variable unresolved, otherwise use actual value
    $serverValue = (!empty($unresolved) && in_array('DB_HOST', $unresolved)) ? '' : $servername;
    $userValue = (!empty($unresolved) && in_array('DB_USER', $unresolved)) ? '' : $username;
    $passValue = (!empty($unresolved) && in_array('DB_PASS', $unresolved)) ? '' : $password;
    $dbValue = (!empty($unresolved) && in_array('DB_NAME', $unresolved)) ? '' : $database;
    
    define('DB_SERVER', $serverValue);
    define('DB_USERNAME', $userValue);
    define('DB_PASSWORD', $passValue);
    define('DB_NAME', $dbValue);
}

// Ensure port constant is available everywhere (db_connect.php, legacy helpers, etc.)
if (!defined('DB_PORT')) {
    $portValue = (!empty($unresolved) && in_array('DB_PORT', $unresolved)) ? 3306 : $port;
    define('DB_PORT', $portValue);
}

// SMTP2GO HTTP API credentials and email settings
if (!defined('SMTP2GO_API_KEY')) {
    define('SMTP2GO_API_KEY', getenv('SMTP2GO_API_KEY') ?: '');
    define('SMTP2GO_SENDER_EMAIL', getenv('SMTP2GO_SENDER_EMAIL') ?: '');
    define('SMTP2GO_SENDER_NAME', getenv('SMTP2GO_SENDER_NAME') ?: "D'Marsians Taekwondo Gym");
    define('ADMIN_BCC_EMAIL', getenv('ADMIN_BCC_EMAIL') ?: '');
}
?>
