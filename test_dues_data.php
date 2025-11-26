<?php
require_once 'db_connect.php';

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

$conn = connectDB();

if (!$conn) {
    die("Database connection failed");
}

echo "<h2>Testing Dues Data</h2>";

// Query students and their latest payment
$sql = "
    SELECT s.jeja_no, s.full_name, s.phone, s.parent_phone, s.discount, s.date_enrolled, p.amount_paid, p.date_paid, p.status,
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

$result = $conn->query($sql);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Jeja No</th><th>Name</th><th>Discount (Raw)</th><th>Discount (Float)</th><th>Amount Paid</th><th>Contact</th></tr>";

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $contact = !empty($row['parent_phone']) ? $row['parent_phone'] : $row['phone'];
        $base_amount = ($row['payment_count'] == 1) ? 1800 : 1500;
        $discount_raw = $row['discount'];
        $discount_float = isset($row['discount']) ? floatval($row['discount']) : 0.00;
        $amount = $base_amount;
        $total_payment = max($amount - $discount_float, 0);
        $amount_paid = isset($row['amount_paid']) ? floatval($row['amount_paid']) : 0.00;

        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['jeja_no']) . "</td>";
        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($discount_raw) . "</td>";
        echo "<td>" . htmlspecialchars($discount_float) . "</td>";
        echo "<td>" . htmlspecialchars($amount_paid) . "</td>";
        echo "<td>" . htmlspecialchars($contact) . "</td>";
        echo "</tr>";
    }
}

echo "</table>";

$conn->close();
?> 