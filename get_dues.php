<?php
require_once 'db_connect.php';

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');
header('Cache-Control: no-store');
// Avoid breaking JSON with PHP warnings/notices in production UI
ini_set('display_errors', 0);
ob_start();

try {
    $conn = connectDB();
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit();
    }

    // Ensure reminder tracking tables exist (first run / legacy DBs)
    function ensureReminderTables(mysqli $conn): void {
        $sql1 = "CREATE TABLE IF NOT EXISTS `dues_reminders` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `jeja_no` varchar(20) NOT NULL,
            `due_month` date NOT NULL,
            `last_reminder_at` datetime DEFAULT NULL,
            `reminder_count` int(11) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_jeja_month` (`jeja_no`,`due_month`),
            KEY `idx_due_month` (`due_month`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $sql2 = "CREATE TABLE IF NOT EXISTS `reminder_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `jeja_no` varchar(20) NOT NULL,
            `due_month` date NOT NULL,
            `sent_to` varchar(255) NOT NULL,
            `status` enum('success','failed','skipped') NOT NULL,
            `provider_id` varchar(255) DEFAULT NULL,
            `error` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_jeja_no` (`jeja_no`),
            KEY `idx_due_month` (`due_month`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        // Best-effort; ignore errors to avoid breaking dues listing
        @$conn->query($sql1);
        @$conn->query($sql2);
    }

    ensureReminderTables($conn);

    // Ensure reminder tracking tables exist (first-time setups may not have run db.sql)
    $conn->query("CREATE TABLE IF NOT EXISTS `dues_reminders` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `jeja_no` varchar(20) NOT NULL,
      `due_month` date NOT NULL,
      `last_reminder_at` datetime DEFAULT NULL,
      `reminder_count` int(11) NOT NULL DEFAULT 0,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_jeja_month` (`jeja_no`,`due_month`),
      KEY `idx_due_month` (`due_month`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS `reminder_logs` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `jeja_no` varchar(20) NOT NULL,
      `due_month` date NOT NULL,
      `sent_to` varchar(255) NOT NULL,
      `status` enum('success','failed','skipped') NOT NULL,
      `provider_id` varchar(255) DEFAULT NULL,
      `error` text DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_jeja_no` (`jeja_no`),
      KEY `idx_due_month` (`due_month`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // Get the first day of next month (fallback)
    $firstDayNextMonth = date('Y-m-01', strtotime('first day of next month'));
    // Define current month window
    $startOfThisMonth = date('Y-m-01');
    $endOfThisMonth = date('Y-m-t');
    // Students should appear when their due date is near-expiry
    // Show dues if due date is within the next N days (or already overdue)
    $noticeWindowDays = 7; // configurable notice window
    $noticeWindowEnd = date('Y-m-d', strtotime('+' . $noticeWindowDays . ' days'));

    // Query students only (compute payment history per student in PHP to avoid join edge cases)
    $sql = "
        SELECT s.jeja_no, s.full_name, s.phone, s.parent_phone, s.discount, s.date_enrolled, s.status
        FROM students s
        WHERE (s.status IS NULL OR LOWER(s.status) != 'inactive')
        ORDER BY s.full_name ASC
    ";

    $result = $conn->query($sql);

    $dues = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $contact = !empty($row['parent_phone']) ? $row['parent_phone'] : $row['phone'];
            $discountPerMonth = isset($row['discount']) ? floatval($row['discount']) : 0.00;

            // Normalize student STD number suffix for matching historical payments
            $studentNumeric = preg_replace('/\D/', '', $row['jeja_no']);
            $studentSuffix = str_pad($studentNumeric, 5, '0', STR_PAD_LEFT);

            // Payment history: count and last paid date using suffix match
            $histSql = "SELECT COUNT(*) AS cnt, MAX(date_paid) AS last_paid FROM payments WHERE RIGHT(TRIM(REPLACE(jeja_no,'STD-','')), 5) = '$studentSuffix'";
            $hasEverPaid = false;
            $last_payment_date = null;
            if ($histRes = $conn->query($histSql)) {
                if ($histRow = $histRes->fetch_assoc()) {
                    $hasEverPaid = intval($histRow['cnt']) > 0;
                    $last_payment_date = $histRow['last_paid'] ? $histRow['last_paid'] : null;
                }
            }

            // Fetch last payment type precisely to decide due-date progression
            $last_payment_type = '';
            if ($hasEverPaid) {
                $lastRes = $conn->query("SELECT payment_type, date_paid FROM payments WHERE RIGHT(TRIM(REPLACE(jeja_no,'STD-','')), 5) = '$studentSuffix' ORDER BY date_paid DESC LIMIT 1");
                if ($lastRes && $lastRes->num_rows > 0) {
                    $lp = $lastRes->fetch_assoc();
                    $last_payment_type = isset($lp['payment_type']) ? $lp['payment_type'] : '';
                    // prefer the actual last row's date for consistency
                    $last_payment_date = $lp['date_paid'] ? $lp['date_paid'] : $last_payment_date;
                }
            }

            // Due date anchored to enrollment date (day-of-month), independent of payment date
            // Clamp to last day for shorter months
            $enrollment_date = isset($row['date_enrolled']) && $row['date_enrolled'] ? $row['date_enrolled'] : null;
            if ($enrollment_date) {
                $anchorDay = (int)date('j', strtotime($enrollment_date));
                // First due is one month after enrollment, clamped to month length
                $firstDueTs = strtotime($enrollment_date . ' +1 month');
                $firstDueDay = min($anchorDay, (int)date('t', $firstDueTs));
                $due_date = date('Y-m-', $firstDueTs) . str_pad($firstDueDay, 2, '0', STR_PAD_LEFT);
                // Roll forward in monthly steps until due date falls within or after start of this month
                while (strtotime($due_date) < strtotime($startOfThisMonth)) {
                    $nextTs = strtotime($due_date . ' +1 month');
                    $nextDay = min($anchorDay, (int)date('t', $nextTs));
                    $due_date = date('Y-m-', $nextTs) . str_pad($nextDay, 2, '0', STR_PAD_LEFT);
                }
            } else {
                $due_date = $firstDayNextMonth;
            }

            // Include only dues due in or before the current month (no next-month preview)
            $include = ($due_date <= $endOfThisMonth);
            $computeToEndOfThisMonth = $include;

            if ($include) {
                if ($computeToEndOfThisMonth) {
                    // Calculate how many months are due from the first unpaid month through the current month
                    $dueStart = new DateTime($due_date);
                    $monthEnd = new DateTime($endOfThisMonth);
                    // Normalize to month boundaries
                    $dueStart->modify('first day of this month');
                    $monthEnd->modify('first day of this month');
                    $months_due = (($monthEnd->format('Y') - $dueStart->format('Y')) * 12) + ($monthEnd->format('n') - $dueStart->format('n')) + 1;
                    if ($months_due < 1) { $months_due = 1; }
                } else {
                    // Near-expiry of next month: show the upcoming single month due
                    $months_due = 1;
                }

                // Compute base total for all missed months
                // First-ever month is 1800, subsequent months are 1500
                if ($hasEverPaid) {
                    $base_total = 1500.00 * $months_due;
                } else {
                    $base_total = 1800.00 + (1500.00 * max(0, $months_due - 1));
                }

                // Apply discount per month
                $total_discount = $discountPerMonth * $months_due;
                $total_payment = max($base_total - $total_discount, 0.00);

                // Remove 'STD-' prefix from jeja_no for display
                $display_jeja_no = preg_replace('/^STD-/', '', $row['jeja_no']);

                // If the student has already paid enough within the unpaid window, skip listing them
                $dueStartMonthStart = (new DateTime($due_date))->modify('first day of this month')->format('Y-m-d');
                $endRange = $computeToEndOfThisMonth ? $endOfThisMonth : date('Y-m-t', strtotime($due_date));
                $jejaEsc = $conn->real_escape_string($row['jeja_no']);
                // Some historical rows may be saved without STD- prefix; match both forms
                $numericPart = preg_replace('/\D/', '', $jejaEsc);
                $altJeja = $numericPart !== '' ? ('STD-' . str_pad($numericPart, 5, '0', STR_PAD_LEFT)) : $jejaEsc;
                $plainJeja = $numericPart; // e.g., 00048
                $sumSql = "SELECT COALESCE(SUM(amount_paid),0) AS total_paid FROM payments WHERE (jeja_no = '$jejaEsc' OR jeja_no = '$altJeja' OR jeja_no = '$plainJeja') AND date_paid >= '$dueStartMonthStart' AND date_paid <= '$endRange'";
                $sumPaid = 0.00;
                if ($sumRes = $conn->query($sumSql)) {
                    if ($sumRow = $sumRes->fetch_assoc()) {
                        $sumPaid = floatval($sumRow['total_paid']);
                    }
                }

                // Allocation-based payment application across months (includes advance payments)
                $allocStart = (new DateTime($dueStartMonthStart))->format('Y-m-01');
                $allocEnd = $endRange; // inclusive end date (Y-m-t)

                // Build per-month dues from allocStart through allocEnd
                $months = [];
                $cursor = new DateTime($allocStart);
                $endMonthObj = new DateTime($allocEnd);
                $endMonthObj->modify('first day of this month');
                $cursor->modify('first day of this month');
                $firstMonthPricingApplied = $hasEverPaid; // if already paid before, first month here is 1500
                while ($cursor <= $endMonthObj) {
                    $monthKey = $cursor->format('Y-m');
                    $baseForMonth = $firstMonthPricingApplied ? 1500.00 : 1800.00;
                    $firstMonthPricingApplied = true; // subsequent months are 1500
                    $dueForMonth = max($baseForMonth - $discountPerMonth, 0.00);
                    $months[$monthKey] = [
                        'due' => $dueForMonth,
                        'paid' => 0.00
                    ];
                    $cursor->modify('+1 month');
                }

                // Fetch all payments up to allocEnd and allocate to earliest unpaid months
                $paySql = "SELECT amount_paid, date_paid FROM payments WHERE (jeja_no = '$jejaEsc' OR jeja_no = '$altJeja' OR jeja_no = '$plainJeja') AND date_paid <= '$allocEnd' ORDER BY date_paid ASC";
                if ($payRes = $conn->query($paySql)) {
                    while ($p = $payRes->fetch_assoc()) {
                        $remaining = floatval($p['amount_paid']);
                        if ($remaining <= 0) { continue; }
                        // allocate across months in chronological order
                        foreach ($months as $mKey => &$mData) {
                            if ($remaining <= 0) { break; }
                            $need = $mData['due'] - $mData['paid'];
                            if ($need <= 0) { continue; }
                            $alloc = min($remaining, $need);
                            $mData['paid'] += $alloc;
                            $remaining -= $alloc;
                        }
                        unset($mData);
                    }
                }

                // Total allocated within the window (allocStart..allocEnd)
                $allocatedPaid = 0.00;
                foreach ($months as $mData) {
                    $allocatedPaid += $mData['paid'];
                }

                if ($allocatedPaid >= $total_payment) {
                    continue; // fully settled via in-window or advance allocations
                }

                // Reminder tracking lookup
                $due_month = (new DateTime($due_date))->modify('first day of this month')->format('Y-m-d');
                $last_reminder_at = null;
                $reminder_count = 0;
                $jejaEsc2 = $conn->real_escape_string($row['jeja_no']);
                $dueMonthEsc = $conn->real_escape_string($due_month);
                if ($remRes = @$conn->query("SELECT last_reminder_at, reminder_count FROM dues_reminders WHERE jeja_no = '$jejaEsc2' AND due_month = '$dueMonthEsc' LIMIT 1")) {
                    if ($remRow = $remRes->fetch_assoc()) {
                        $last_reminder_at = $remRow['last_reminder_at'];
                        $reminder_count = intval($remRow['reminder_count']);
                    }
                }

                $dues[] = [
                    'jeja_no' => $row['jeja_no'],
                    'due_date' => $due_date,
                    'due_month' => $due_month,
                    'id_name' => $display_jeja_no . ' - ' . $row['full_name'],
                    'amount' => number_format($base_total, 2),
                    'discount' => number_format($total_discount, 2),
                    'total_payment' => number_format($total_payment, 2),
                    // Show amount paid within the relevant window (this month or until end of due month)
                    'amount_paid' => number_format($sumPaid, 2),
                    'balance' => number_format(max($total_payment - $allocatedPaid, 0.00), 2),
                    'contact' => $contact,
                    'months_due' => $months_due,
                    'last_reminder_at' => $last_reminder_at,
                    'reminder_count' => $reminder_count
                ];
            }
        }
    }

    $conn->close();

    $response = json_encode([
        'status' => 'success',
        'dues' => $dues
    ]);
    // Clear any buffered warnings and output only pure JSON
    if (ob_get_length()) { ob_clean(); }
    echo $response;

} catch (Exception $e) {
    if (ob_get_length()) { ob_clean(); }
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
exit();
?>