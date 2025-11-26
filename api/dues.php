<?php
require_once __DIR__ . '/../db_connect.php';

// Timezone and headers
date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json');
header('Cache-Control: no-store');
ini_set('display_errors', 0);
ob_start();

try {
    $conn = connectDB();
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit();
    }

    // Optional month filter: YYYY-MM. If provided, we use that as the current context month
    $monthParam = isset($_GET['month']) ? $_GET['month'] : null;
    if ($monthParam && preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
        $startOfThisMonth = date('Y-m-01', strtotime($monthParam . '-01'));
        $endOfThisMonth = date('Y-m-t', strtotime($monthParam . '-01'));
    } else {
        $startOfThisMonth = date('Y-m-01');
        $endOfThisMonth = date('Y-m-t');
    }

    $firstDayNextMonth = date('Y-m-01', strtotime($startOfThisMonth . ' +1 month'));
    $noticeWindowDays = 7;
    $noticeWindowEnd = date('Y-m-d', strtotime($endOfThisMonth . ' +' . $noticeWindowDays . ' days'));

    // Ensure reminder tracking tables exist (best-effort, mirrored from legacy endpoint)
    @$conn->query("CREATE TABLE IF NOT EXISTS `dues_reminders` (
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

    @$conn->query("CREATE TABLE IF NOT EXISTS `reminder_logs` (
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

    // Include all students (including inactive) per policy
    $sql = "
        SELECT s.jeja_no, s.full_name, s.phone, s.parent_phone, s.discount, s.date_enrolled, s.status
        FROM students s
        ORDER BY s.full_name ASC
    ";

    $result = $conn->query($sql);
    $dues = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $contact = !empty($row['parent_phone']) ? $row['parent_phone'] : $row['phone'];
            $discountPerMonth = isset($row['discount']) ? floatval($row['discount']) : 0.00;

            $studentNumeric = preg_replace('/\D/', '', $row['jeja_no']);
            $studentSuffix = str_pad($studentNumeric, 5, '0', STR_PAD_LEFT);

            // Last payment info including type
            $lastPayment = null;
            $lastRes = $conn->query("SELECT payment_type, date_paid, amount_paid FROM payments WHERE RIGHT(TRIM(REPLACE(jeja_no,'STD-','')), 5) = '{$studentSuffix}' ORDER BY date_paid DESC LIMIT 1");
            if ($lastRes && $lastRes->num_rows > 0) {
                $lastPayment = $lastRes->fetch_assoc();
            }

            $hasEverPaid = $lastPayment !== null;
            $last_payment_date = $hasEverPaid ? $lastPayment['date_paid'] : null;
            $last_payment_type = $hasEverPaid ? ($lastPayment['payment_type'] ?? '') : '';

            // Determine anchor day and first due month
            $enrollment_date = isset($row['date_enrolled']) && $row['date_enrolled'] ? $row['date_enrolled'] : null;
            $anchorDay = null;
            $due_date = null; // first due date (month) to start accounting from

            if ($enrollment_date) {
                $anchorDay = (int)date('j', strtotime($enrollment_date));
                $firstDueTs = strtotime($enrollment_date . ' +1 month');
                $firstDueDay = min($anchorDay, (int)date('t', $firstDueTs));
                $due_date = date('Y-m-', $firstDueTs) . str_pad($firstDueDay, 2, '0', STR_PAD_LEFT);
            } else {
                // Fallback: use earliest payment month; if none, use current month
                $earliestPay = null;
                $epRes = $conn->query("SELECT MIN(date_paid) AS min_date FROM payments WHERE RIGHT(TRIM(REPLACE(jeja_no,'STD-','')), 5) = '{$studentSuffix}'");
                if ($epRes && $epRow = $epRes->fetch_assoc()) {
                    $earliestPay = $epRow['min_date'] ? $epRow['min_date'] : null;
                }
                if ($earliestPay) {
                    $anchorDay = (int)date('j', strtotime($earliestPay));
                    $due_date = date('Y-m-01', strtotime($earliestPay));
                } else {
                    $anchorDay = (int)date('j');
                    $due_date = $startOfThisMonth; // start from current month
                }
            }

            // Include only dues due in or before the current month (no next-month preview)
            $include = ($due_date <= $endOfThisMonth);
            $computeToEndOfThisMonth = $include;

            if (!$include) {
                continue;
            }

            // How many months covered in this window (from first due through target month)
            $dueStart = new DateTime($due_date);
            $monthEnd = new DateTime($endOfThisMonth);
            $dueStart->modify('first day of this month');
            $monthEnd->modify('first day of this month');
            $months_due = (($monthEnd->format('Y') - $dueStart->format('Y')) * 12) + ($monthEnd->format('n') - $dueStart->format('n')) + 1;
            if ($months_due < 1) { $months_due = 1; }

            // Build per-month dues from first due to target month
            $dueStartMonthStart = $dueStart->format('Y-m-d');
            $endRange = $endOfThisMonth;
            $jejaEsc = $conn->real_escape_string($row['jeja_no']);
            $numericPart = preg_replace('/\D/', '', $jejaEsc);
            $altJeja = $numericPart !== '' ? ('STD-' . str_pad($numericPart, 5, '0', STR_PAD_LEFT)) : $jejaEsc;
            $plainJeja = $numericPart;

            $months = [];
            $cursor = new DateTime($dueStartMonthStart);
            $endMonthObj = new DateTime($endRange);
            $endMonthObj->modify('first day of this month');
            $cursor->modify('first day of this month');
            $firstMonthPricingApplied = $hasEverPaid; // if already paid before, first month here is 1500
            while ($cursor <= $endMonthObj) {
                $mKey = $cursor->format('Y-m');
                $baseForMonth = $firstMonthPricingApplied ? 1500.00 : 1800.00;
                $firstMonthPricingApplied = true;
                $dueForMonth = max($baseForMonth - $discountPerMonth, 0.00);
                $months[$mKey] = ['due' => $dueForMonth, 'paid' => 0.00];
                $cursor->modify('+1 month');
            }

            // Allocate all payments up to endRange into earliest unpaid months
            $paySql = "SELECT amount_paid, date_paid FROM payments WHERE (jeja_no = '$jejaEsc' OR jeja_no = '$altJeja' OR jeja_no = '$plainJeja') AND date_paid <= '$endRange' ORDER BY date_paid ASC";
            if ($payRes = $conn->query($paySql)) {
                while ($p = $payRes->fetch_assoc()) {
                    $remaining = floatval($p['amount_paid']);
                    if ($remaining <= 0) { continue; }
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

            // Compute totals
            $windowDue = 0.00;
            $allocatedPaid = 0.00;
            foreach ($months as $mData) { $windowDue += $mData['due']; $allocatedPaid += $mData['paid']; }
            $total_discount = $discountPerMonth * count($months);
            $total_payment = max($windowDue, 0.00);

            if ($allocatedPaid >= $total_payment - 0.009) {
                continue; // fully settled (full/advance covered)
            }

            $due_month = $startOfThisMonth; // reminder key for current month

            // Reminder tracking (best effort)
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

            $display_jeja_no = preg_replace('/^STD-/', '', $row['jeja_no']);

            // Compute display due date for the target month anchored to anchorDay
            $targetMonthDays = (int)date('t', strtotime($startOfThisMonth));
            $displayDay = max(1, min(($anchorDay ?: 1), $targetMonthDays));
            $due_date_display = date('Y-m-', strtotime($startOfThisMonth)) . str_pad($displayDay, 2, '0', STR_PAD_LEFT);

            $dues[] = [
                'jeja_no' => $row['jeja_no'],
                'due_date' => $due_date_display,
                'due_month' => $due_month,
                'id_name' => $display_jeja_no . ' - ' . $row['full_name'],
                'amount' => round($windowDue + $total_discount, 2),
                'discount' => round($total_discount, 2),
                'total_payment' => round($total_payment, 2),
                'amount_paid' => round($allocatedPaid, 2),
                'balance' => round(max($total_payment - $allocatedPaid, 0.00), 2),
                'contact' => $contact,
                'months_due' => $months_due,
                'last_reminder_at' => $last_reminder_at,
                'reminder_count' => $reminder_count
            ];
        }
    }

    $conn->close();

    if (ob_get_length()) { ob_clean(); }
    echo json_encode(['status' => 'success', 'dues' => $dues]);

} catch (Exception $e) {
    if (ob_get_length()) { ob_clean(); }
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
exit();
?>


