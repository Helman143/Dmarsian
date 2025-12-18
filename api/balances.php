<?php
require_once __DIR__ . '/../db_connect.php';

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

    // month=YYYY-MM (defaults to current month)
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

    // Pull students
    $sql = "
        SELECT s.jeja_no, s.full_name, s.phone, s.parent_phone, s.discount, s.date_enrolled, s.status
        FROM students s
        ORDER BY s.full_name ASC
    ";
    $res = $conn->query($sql);

    $balances = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $discountPerMonth = isset($row['discount']) ? floatval($row['discount']) : 0.00;

            $studentNumeric = preg_replace('/\D/', '', $row['jeja_no']);
            $studentSuffix = str_pad($studentNumeric, 5, '0', STR_PAD_LEFT);

            // Payment history (to determine first-month pricing and due start)
            $lastPayment = null;
            $lastRes = $conn->query("SELECT date_paid FROM payments WHERE RIGHT(TRIM(REPLACE(jeja_no,'STD-','')), 5) = '{$studentSuffix}' ORDER BY date_paid DESC LIMIT 1");
            if ($lastRes && $lastRes->num_rows > 0) {
                $lastPayment = $lastRes->fetch_assoc();
            }
            $hasEverPaid = $lastPayment !== null;
            $last_payment_date = $hasEverPaid ? $lastPayment['date_paid'] : null;

            // Determine next due date anchored to enrollment day-of-month (independent of payment date)
            $enrollment_date = isset($row['date_enrolled']) && $row['date_enrolled'] ? $row['date_enrolled'] : null;
            if ($enrollment_date) {
                $anchorDay = (int)date('j', strtotime($enrollment_date));
                $firstDueTs = strtotime($enrollment_date . ' +1 month');
                $firstDueDay = min($anchorDay, (int)date('t', $firstDueTs));
                $due_date = date('Y-m-', $firstDueTs) . str_pad($firstDueDay, 2, '0', STR_PAD_LEFT);
            } else {
                // Fallback: use earliest payment month; if none, use current month
                $earliestPay = null;
                $epRes = $conn->query("SELECT MIN(date_paid) AS min_date FROM payments WHERE RIGHT(TRIM(REPLACE(jeja_no,'STD-','')), 5) = '$studentSuffix'");
                if ($epRes && $epRow = $epRes->fetch_assoc()) {
                    $earliestPay = $epRow['min_date'] ? $epRow['min_date'] : null;
                }
                if ($earliestPay) {
                    $due_date = date('Y-m-01', strtotime($earliestPay));
                } else {
                    $due_date = $startOfThisMonth;
                }
            }

            // Include only when due by end of target month (no next-month preview)
            $include = ($due_date <= $endOfThisMonth);
            $computeToEndOfThisMonth = $include;

            if (!$include) {
                continue;
            }

            // Months due
            if ($computeToEndOfThisMonth) {
                $dueStart = new DateTime($due_date);
                $monthEnd = new DateTime($endOfThisMonth);
                $dueStart->modify('first day of this month');
                $monthEnd->modify('first day of this month');
                $months_due = (($monthEnd->format('Y') - $dueStart->format('Y')) * 12) + ($monthEnd->format('n') - $dueStart->format('n')) + 1;
                if ($months_due < 1) { $months_due = 1; }
            } else {
                $months_due = 1;
            }

            // Allocation window: from first due month through requested month end
            $allocStart = (new DateTime($due_date))->modify('first day of this month')->format('Y-m-01');
            $allocEnd = $endOfThisMonth;

            // Build per-month dues
            $months = [];
            $cursor = new DateTime($allocStart);
            $endMonthObj = new DateTime($allocEnd);
            $endMonthObj->modify('first day of this month');
            $cursor->modify('first day of this month');
            $firstMonthPricingApplied = $hasEverPaid;
            while ($cursor <= $endMonthObj) {
                $mKey = $cursor->format('Y-m');
                $baseForMonth = $firstMonthPricingApplied ? 1500.00 : 1800.00;
                $firstMonthPricingApplied = true;
                $dueForMonth = max($baseForMonth - $discountPerMonth, 0.00);
                $months[$mKey] = ['due' => $dueForMonth, 'paid' => 0.00];
                $cursor->modify('+1 month');
            }

            // Allocate all payments up to endOfThisMonth into earliest unpaid months
            $jejaEsc = $conn->real_escape_string($row['jeja_no']);
            $numericPart = preg_replace('/\D/', '', $jejaEsc);
            $altJeja = $numericPart !== '' ? ('STD-' . str_pad($numericPart, 5, '0', STR_PAD_LEFT)) : $jejaEsc;
            $plainJeja = $numericPart;
            $paySql = "SELECT amount_paid, date_paid FROM payments WHERE (jeja_no = '$jejaEsc' OR jeja_no = '$altJeja' OR jeja_no = '$plainJeja') AND date_paid <= '$allocEnd' ORDER BY date_paid ASC";
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

            // Target month key is the requested month
            $targetKey = date('Y-m', strtotime($startOfThisMonth));
            if (!isset($months[$targetKey])) {
                // Not yet due by target month
                continue;
            }
            // Compute cumulative balance up to and including target month
            $totalDueThroughTarget = 0.0;
            $totalPaidThroughTarget = 0.0;
            foreach ($months as $mKey => $mData) {
                if ($mKey > $targetKey) { break; }
                $totalDueThroughTarget += $mData['due'];
                $totalPaidThroughTarget += $mData['paid'];
            }
            $targetBalance = max(0.0, $totalDueThroughTarget - $totalPaidThroughTarget);
            if ($targetBalance <= 0) { continue; }

            $balances[] = [
                'jeja_no' => $row['jeja_no'],
                'full_name' => $row['full_name'],
                'period' => $targetKey,
                'balance' => round($targetBalance, 2)
            ];
        }
    }

    if (ob_get_length()) { ob_clean(); }
    echo json_encode(['status' => 'success', 'balances' => $balances]);

} catch (Exception $e) {
    if (ob_get_length()) { ob_clean(); }
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
exit();
?>


