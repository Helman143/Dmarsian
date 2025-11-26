<?php
require_once 'db_connect.php';

echo "<h2>Database Connection Test</h2>";

try {
    $conn = connectDB();
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Test posts table
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'posts'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: green;'>✓ Posts table exists!</p>";
        
        // Show table structure
        $structure = mysqli_query($conn, "DESCRIBE posts");
        echo "<h3>Posts Table Structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = mysqli_fetch_assoc($structure)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Count posts
        $count = mysqli_query($conn, "SELECT COUNT(*) as total FROM posts");
        $total = mysqli_fetch_assoc($count)['total'];
        echo "<p>Total posts in database: <strong>$total</strong></p>";
        
    } else {
        echo "<p style='color: red;'>✗ Posts table does not exist!</p>";
    }
    
    mysqli_close($conn);
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

echo "<br><a href='admin_post_management.php'>Go to Post Management</a>";
?> 