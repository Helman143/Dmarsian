<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../db_connect.php';

class DuesDataTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        date_default_timezone_set('Asia/Manila');
        $this->conn = connectDB();
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    /**
     * Test that students table exists
     */
    public function testStudentsTableExists()
    {
        $result = $this->conn->query("SHOW TABLES LIKE 'students'");
        $this->assertGreaterThan(0, $result->num_rows, "Students table should exist");
    }

    /**
     * Test that payments table exists
     */
    public function testPaymentsTableExists()
    {
        $result = $this->conn->query("SHOW TABLES LIKE 'payments'");
        $this->assertGreaterThan(0, $result->num_rows, "Payments table should exist");
    }

    /**
     * Test dues query execution
     */
    public function testDuesQueryExecution()
    {
        $sql = "
            SELECT s.jeja_no, s.full_name, s.phone, s.parent_phone, s.discount, s.date_enrolled, 
                   p.amount_paid, p.date_paid, p.status,
                   (SELECT COUNT(*) FROM payments WHERE jeja_no = s.jeja_no) as payment_count
            FROM students s
            LEFT JOIN (
                SELECT p1.*
                FROM payments p1
                INNER JOIN (
                    SELECT jeja_no, MAX(id) as max_id
                    FROM payments
                    GROUP BY jeja_no
                ) p2 ON p1.jeja_no = p2.jeja_no AND p1.id = p2.max_id
            ) p ON s.jeja_no = p.jeja_no
            WHERE (p.status IS NULL OR LOWER(p.status) != 'inactive')
              AND (p.date_paid < '2025-09-01' OR p.date_paid IS NULL)
            ORDER BY s.full_name ASC
            LIMIT 5
        ";

        $result = $this->conn->query($sql);
        $this->assertNotFalse($result, "Dues query should execute successfully");
    }

    /**
     * Test discount calculation
     */
    public function testDiscountCalculation()
    {
        $discountRaw = "100";
        $discountFloat = floatval($discountRaw);
        $baseAmount = 1500;
        $totalPayment = max($baseAmount - $discountFloat, 0);

        $this->assertEquals(1400, $totalPayment, "Discount should be calculated correctly");
    }
}

