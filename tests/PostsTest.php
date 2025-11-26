<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../db_connect.php';

class PostsTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = connectDB();
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            mysqli_close($this->conn);
        }
    }

    /**
     * Test that posts table exists
     */
    public function testPostsTableExists()
    {
        $result = mysqli_query($this->conn, "SHOW TABLES LIKE 'posts'");
        $this->assertGreaterThan(0, mysqli_num_rows($result), "Posts table should exist");
    }

    /**
     * Test posts table structure
     */
    public function testPostsTableStructure()
    {
        $result = mysqli_query($this->conn, "DESCRIBE posts");
        $this->assertNotFalse($result, "Should be able to describe posts table");
        
        $columns = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $columns[] = $row['Field'];
        }
        
        // Check for essential columns (adjust based on your actual schema)
        $expectedColumns = ['id', 'title', 'content'];
        foreach ($expectedColumns as $column) {
            $this->assertContains(
                $column, 
                $columns, 
                "Posts table should have '$column' column"
            );
        }
    }

    /**
     * Test that we can count posts
     */
    public function testCanCountPosts()
    {
        $result = mysqli_query($this->conn, "SELECT COUNT(*) as total FROM posts");
        $this->assertNotFalse($result, "Should be able to count posts");
        
        $row = mysqli_fetch_assoc($result);
        $this->assertIsNumeric($row['total'], "Post count should be numeric");
        $this->assertGreaterThanOrEqual(0, (int)$row['total'], "Post count should be non-negative");
    }
}

