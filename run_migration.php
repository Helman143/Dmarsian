<?php
/**
 * Web-accessible migration runner for show_in_slider column
 * 
 * This page allows running the migration from the browser.
 * It includes admin authentication for security.
 */

session_start();
require_once 'db_connect.php';

// Check if user is logged in as admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

$message = '';
$success = false;
$error = '';

// Handle migration execution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
    try {
        $conn = connectDB();
        
        if (!$conn) {
            throw new Exception('Database connection failed');
        }
        
        // Check if column already exists
        $checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM posts LIKE 'show_in_slider'");
        if (mysqli_num_rows($checkColumn) > 0) {
            $message = "Column 'show_in_slider' already exists. Migration not needed.";
            $success = true;
            mysqli_close($conn);
        } else {
            // Add show_in_slider column
            $sql = "ALTER TABLE posts 
                    ADD COLUMN show_in_slider TINYINT(1) NOT NULL DEFAULT 1 
                    AFTER status";
            
            if (mysqli_query($conn, $sql)) {
                $message = "Column 'show_in_slider' added successfully.\n";
                
                // Set all existing posts to visible (1) by default
                $updateSql = "UPDATE posts SET show_in_slider = 1 WHERE show_in_slider IS NULL OR show_in_slider = 0";
                if (mysqli_query($conn, $updateSql)) {
                    $message .= "All existing posts set to show_in_slider = 1.\n";
                } else {
                    $message .= "Warning: Failed to update existing posts: " . mysqli_error($conn) . "\n";
                }
                
                // Add index for performance
                $indexSql = "ALTER TABLE posts ADD INDEX idx_show_in_slider (show_in_slider)";
                if (mysqli_query($conn, $indexSql)) {
                    $message .= "Index 'idx_show_in_slider' added successfully.\n";
                } else {
                    $message .= "Warning: Failed to add index (may already exist): " . mysqli_error($conn) . "\n";
                }
                
                $message .= "\nMigration completed successfully!";
                $success = true;
            } else {
                throw new Exception('Failed to add column: ' . mysqli_error($conn));
            }
            
            mysqli_close($conn);
        }
    } catch (Exception $e) {
        $error = "Migration failed: " . $e->getMessage();
        if (isset($conn)) {
            mysqli_close($conn);
        }
    }
}

// Check current status
$columnExists = false;
try {
    $conn = connectDB();
    if ($conn) {
        $checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM posts LIKE 'show_in_slider'");
        $columnExists = mysqli_num_rows($checkColumn) > 0;
        mysqli_close($conn);
    }
} catch (Exception $e) {
    $error = "Error checking column status: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Run Migration - Add show_in_slider Column</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #1a1a1a;
            color: #fff;
            padding: 40px 20px;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 255, 106, 0.3);
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 20px;
        }
        .btn-primary {
            background: #00ff6a;
            color: #000;
            border: none;
            padding: 12px 30px;
            font-weight: bold;
        }
        .btn-primary:hover {
            background: #00cc55;
        }
        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: rgba(0, 255, 106, 0.2);
            border: 1px solid #00ff6a;
            color: #00ff6a;
        }
        .alert-danger {
            background: rgba(255, 51, 51, 0.2);
            border: 1px solid #ff3333;
            color: #ff3333;
        }
        .alert-info {
            background: rgba(0, 150, 255, 0.2);
            border: 1px solid #0096ff;
            color: #0096ff;
        }
        pre {
            background: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 5px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-left: 10px;
        }
        .status-exists {
            background: rgba(0, 255, 106, 0.3);
            color: #00ff6a;
        }
        .status-missing {
            background: rgba(255, 165, 0, 0.3);
            color: #ffa500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1 class="mb-4">Database Migration: Add show_in_slider Column</h1>
            <p class="text-muted mb-3"><small>If you're accessing this page, the file is working correctly. The migration will add the <code>show_in_slider</code> column to enable the "Remove from Slider" feature.</small></p>
            
            <div class="mb-4">
                <h3>Current Status: 
                    <?php if ($columnExists): ?>
                        <span class="status-badge status-exists">Column Exists</span>
                    <?php else: ?>
                        <span class="status-badge status-missing">Column Missing</span>
                    <?php endif; ?>
                </h3>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <strong>Error:</strong><br>
                    <pre><?php echo htmlspecialchars($error); ?></pre>
                </div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="alert <?php echo $success ? 'alert-success' : 'alert-info'; ?>">
                    <strong><?php echo $success ? 'Success!' : 'Info:'; ?></strong><br>
                    <pre><?php echo htmlspecialchars($message); ?></pre>
                </div>
            <?php endif; ?>
            
            <?php if (!$columnExists || isset($_GET['force'])): ?>
                <div class="alert alert-info">
                    <strong>Migration Required</strong><br>
                    The <code>show_in_slider</code> column does not exist in the <code>posts</code> table.
                    Click the button below to run the migration.
                </div>
                
                <form method="POST" onsubmit="return confirm('Are you sure you want to run this migration? This will modify the database structure.');">
                    <button type="submit" name="run_migration" class="btn btn-primary">
                        Run Migration
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-success">
                    <strong>Migration Already Completed</strong><br>
                    The <code>show_in_slider</code> column already exists in the database.
                    No action is needed.
                </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <h4>What This Migration Does:</h4>
                <ul>
                    <li>Adds <code>show_in_slider</code> column to the <code>posts</code> table</li>
                    <li>Sets default value to <code>1</code> (visible in sliders)</li>
                    <li>Updates all existing posts to <code>show_in_slider = 1</code></li>
                    <li>Adds an index for better query performance</li>
                </ul>
            </div>
            
            <div class="mt-4">
                <a href="admin_post_management.php" class="btn btn-secondary">Back to Post Management</a>
            </div>
        </div>
    </div>
</body>
</html>
