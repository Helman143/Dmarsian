<?php
/**
 * Migration: Add show_in_slider column to posts table
 * 
 * This migration adds a column to control post visibility in front-end sliders.
 * Posts with show_in_slider = 0 will be hidden from Achievement/Event sliders
 * but remain visible in archive.php and admin_post_management.php
 */

require_once 'db_connect.php';

try {
    $conn = connectDB();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Check if column already exists
    $checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM posts LIKE 'show_in_slider'");
    if (mysqli_num_rows($checkColumn) > 0) {
        echo "Column 'show_in_slider' already exists. Migration skipped.\n";
        mysqli_close($conn);
        exit(0);
    }
    
    // Add show_in_slider column
    $sql = "ALTER TABLE posts 
            ADD COLUMN show_in_slider TINYINT(1) NOT NULL DEFAULT 1 
            AFTER status";
    
    if (mysqli_query($conn, $sql)) {
        echo "Column 'show_in_slider' added successfully.\n";
        
        // Set all existing posts to visible (1) by default
        $updateSql = "UPDATE posts SET show_in_slider = 1 WHERE show_in_slider IS NULL OR show_in_slider = 0";
        if (mysqli_query($conn, $updateSql)) {
            echo "All existing posts set to show_in_slider = 1.\n";
        } else {
            echo "Warning: Failed to update existing posts: " . mysqli_error($conn) . "\n";
        }
        
        // Add index for performance
        $indexSql = "ALTER TABLE posts ADD INDEX idx_show_in_slider (show_in_slider)";
        if (mysqli_query($conn, $indexSql)) {
            echo "Index 'idx_show_in_slider' added successfully.\n";
        } else {
            echo "Warning: Failed to add index (may already exist): " . mysqli_error($conn) . "\n";
        }
        
        echo "\nMigration completed successfully!\n";
    } else {
        throw new Exception('Failed to add column: ' . mysqli_error($conn));
    }
    
    mysqli_close($conn);
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    if (isset($conn)) {
        mysqli_close($conn);
    }
    exit(1);
}
?>
