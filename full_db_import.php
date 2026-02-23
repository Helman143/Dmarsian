<?php
/**
 * Script to import the full database schema from Database/db.sql
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // Increase timeout for large imports

require_once 'db_connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Importer</title>
    <style>
        body { font-family: sans-serif; max-width: 900px; margin: 40px auto; padding: 20px; background: #f4f7f9; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #e6f4ea; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 5px solid #28a745; }
        .error { color: #dc3545; background: #fce8e6; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 5px solid #dc3545; }
        .info { color: #004085; background: #cce5ff; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 5px solid #004085; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; border: 1px solid #dee2e6; max-height: 400px; overflow-y: auto; font-size: 12px; }
        .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; cursor: pointer; border: none; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Database Importer</h1>
        
        <?php
        if (!isset($conn) || $conn === false) {
            echo '<div class="error"><strong>Connection Failed:</strong> Could not connect to the database. Check your .env configuration.</div>';
            exit;
        }

        $current_db = defined('DB_NAME') ? DB_NAME : 'unknown';
        echo '<div class="info">Connected to database: <strong>' . $current_db . '</strong></div>';

        if (isset($_POST['run_import'])) {
            $sql_file = 'Database' . DIRECTORY_SEPARATOR . 'db.sql';
            if (!file_exists($sql_file)) {
                echo '<div class="error">SQL file not found at: ' . $sql_file . '</div>';
            } else {
                echo '<div class="info">Reading SQL file...</div>';
                $sql = file_get_contents($sql_file);
                
                // Remove CREATE DATABASE and USE lines to import into current DB
                $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS `[^`]+`[^;]*;/i', '', $sql);
                $sql = preg_replace('/USE `[^`]+`;/i', '', $sql);
                
                // Split by semicolon, but handle complex cases poorly (this is a simple script)
                // Better: Use multi_query
                
                echo '<p>Executing SQL commands...</p>';
                if ($conn->multi_query($sql)) {
                    $i = 0;
                    do {
                        $i++;
                        // Just iterate through results
                        if ($result = $conn->store_result()) {
                            $result->free();
                        }
                    } while ($conn->next_result());
                    
                    if ($conn->errno) {
                        echo '<div class="error">Error at step ' . $i . ': ' . $conn->error . '</div>';
                    } else {
                        echo '<div class="success"><strong>Success!</strong> All ' . $i . ' blocks of SQL executed successfully.</div>';
                        echo '<p>Created tables: ';
                        $tables = $conn->query("SHOW TABLES");
                        $table_names = [];
                        while($row = $tables->fetch_row()) $table_names[] = "<code>$row[0]</code>";
                        echo implode(", ", $table_names);
                        echo '</p>';
                    }
                } else {
                    echo '<div class="error">Initial query execution failed: ' . $conn->error . '</div>';
                }
            }
        } else {
            echo '<p>This script will import the standard database structure into your current database (<strong>' . $current_db . '</strong>).</p>';
            echo '<p>Existing data in these tables may be overwritten.</p>';
            echo '<form method="POST"><button type="submit" name="run_import" class="btn">Start Import Now</button></form>';
        }
        ?>
        
        <div style="margin-top: 30px;">
            <a href="admin_login.php" class="btn" style="background: #6c757d;">Back to Login</a>
        </div>
    </div>
</body>
</html>
