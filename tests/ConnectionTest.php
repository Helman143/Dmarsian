<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db_connect.php';

class ConnectionTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        // Setup runs before each test method
    }

    protected function tearDown(): void
    {
        // Cleanup runs after each test method
        if ($this->conn) {
            mysqli_close($this->conn);
        }
    }

    /**
     * Test database connection
     */
    public function testDatabaseConnection()
    {
        $this->conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        $this->assertNotFalse($this->conn, "Database connection should succeed");
    }

    /**
     * Test that config constants are defined
     */
    public function testConfigConstants()
    {
        $this->assertTrue(defined('DB_SERVER'), "DB_SERVER should be defined");
        $this->assertTrue(defined('DB_USERNAME'), "DB_USERNAME should be defined");
        $this->assertTrue(defined('DB_NAME'), "DB_NAME should be defined");
    }

    /**
     * Test that required tables exist
     */
    public function testRequiredTablesExist()
    {
        $this->conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        $this->assertNotFalse($this->conn, "Database connection should succeed");

        $tables = ['users', 'admin_accounts', 'students', 'posts', 'payments'];
        
        foreach ($tables as $table) {
            $result = mysqli_query($this->conn, "SHOW TABLES LIKE '$table'");
            $this->assertGreaterThan(
                0, 
                mysqli_num_rows($result), 
                "Table '$table' should exist"
            );
        }
    }

    /**
     * Test connectDB function
     */
    public function testConnectDBFunction()
    {
        $conn = connectDB();
        $this->assertNotNull($conn, "connectDB() should return a connection");
        $this->assertInstanceOf('mysqli', $conn, "connectDB() should return mysqli object");
    }
}

