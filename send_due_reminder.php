<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once 'db_connect.php';
require_once 'config.php';

header('Content-Type: application/json');

// Admin/session guard similar to dashboard.php (allow admin and super_admin)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin','super_admin'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

date_default_timezone_set('Asia/Manila');

function getDb(): mysqli {
    $conn = connectDB();
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit();
    }
    // Ensure reminder tables exist (in case migration not applied)
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
    return $conn;
}

function firstDayOfMonth(string $dateYmd): string {
    $dt = new DateTime($dateYmd);
    $dt->modify('first day of this month');
    return $dt->format('Y-m-d');
}

function computeStudentDue(mysqli $conn, string $jejaNo): ?array {
    $firstDayNextMonth = date('Y-m-01', strtotime('first day of next month'));
    $endOfThisMonth = date('Y-m-t');

    $jejaEsc = $conn->real_escape_string($jejaNo);
    $sql = "
        SELECT s.jeja_no, s.full_name, s.phone, s.parent_phone, s.email, s.parent_email, s.discount, s.date_enrolled,
               p.amount_paid, p.date_paid, p.status
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
        WHERE s.jeja_no = '$jejaEsc'
          AND (p.status IS NULL OR LOWER(p.status) != 'inactive')
          AND (p.date_paid < '$firstDayNextMonth' OR p.date_paid IS NULL)
        LIMIT 1
    ";
    if (!$res = $conn->query($sql)) {
        return null;
    }
    if (!$row = $res->fetch_assoc()) {
        return null;
    }

    $discountPerMonth = isset($row['discount']) ? floatval($row['discount']) : 0.00;
    $studentNumeric = preg_replace('/\D/', '', $row['jeja_no']);
    $studentSuffix = str_pad($studentNumeric, 5, '0', STR_PAD_LEFT);

    $histSql = "SELECT COUNT(*) AS cnt, MAX(date_paid) AS last_paid FROM payments WHERE RIGHT(TRIM(REPLACE(jeja_no,'STD-','')), 5) = '$studentSuffix'";
    $hasEverPaid = false;
    $last_payment_date = null;
    if ($histRes = $conn->query($histSql)) {
        if ($histRow = $histRes->fetch_assoc()) {
            $hasEverPaid = intval($histRow['cnt']) > 0;
            $last_payment_date = $histRow['last_paid'] ? $histRow['last_paid'] : null;
        }
    }

    // Fetch last payment type to adjust due-date progression (advance/half/partial)
    $last_payment_type = '';
    if ($hasEverPaid) {
        $lastRes = $conn->query("SELECT payment_type, date_paid FROM payments WHERE RIGHT(TRIM(REPLACE(jeja_no,'STD-','')), 5) = '$studentSuffix' ORDER BY date_paid DESC LIMIT 1");
        if ($lastRes && $lastRes->num_rows > 0) {
            $lp = $lastRes->fetch_assoc();
            $last_payment_type = isset($lp['payment_type']) ? $lp['payment_type'] : '';
            if ($lp['date_paid']) { $last_payment_date = $lp['date_paid']; }
        }
    }

    // Determine first due month from enrollment (align with api/dues.php)
    $enrollment_date = isset($row['date_enrolled']) && $row['date_enrolled'] ? $row['date_enrolled'] : null;
    $startOfThisMonth = date('Y-m-01');
    if ($enrollment_date) {
        $anchorDay = (int)date('j', strtotime($enrollment_date));
        $firstDueTs = strtotime($enrollment_date . ' +1 month');
        $firstDueDay = min($anchorDay, (int)date('t', $firstDueTs));
        $due_date = date('Y-m-', $firstDueTs) . str_pad($firstDueDay, 2, '0', STR_PAD_LEFT);
    } else {
        // Fallback to current month if no enrollment date
        $due_date = $startOfThisMonth;
    }

    // Only process if first due is on or before this month
    $include = ($due_date <= $endOfThisMonth);
    $computeToEndOfThisMonth = $include;
    if (!$include) {
        return null;
    }

    // Months due calculation
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

    if ($hasEverPaid) {
        $base_total = 1500.00 * $months_due;
    } else {
        $base_total = 1800.00 + (1500.00 * max(0, $months_due - 1));
    }
    $total_discount = $discountPerMonth * $months_due;
    $total_payment = max($base_total - $total_discount, 0.00);

    $dueStartMonthStart = (new DateTime($due_date))->modify('first day of this month')->format('Y-m-d');
    $endRange = $computeToEndOfThisMonth ? $endOfThisMonth : date('Y-m-t', strtotime($due_date));
    $numericPart = preg_replace('/\D/', '', $jejaEsc);
    $altJeja = $numericPart !== '' ? ('STD-' . str_pad($numericPart, 5, '0', STR_PAD_LEFT)) : $jejaEsc;
    $plainJeja = $numericPart;
    // Build per-month dues and allocate payments across months to detect partial/half cases
    $allocStart = (new DateTime($dueStartMonthStart))->format('Y-m-01');
    $allocEnd = $endRange;

    // Build per-month dues from allocStart to allocEnd
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

    $allocatedPaid = 0.00;
    foreach ($months as $mData) { $allocatedPaid += $mData['paid']; }
    if ($allocatedPaid >= $total_payment) {
        return null; // settled
    }
    $remainingBalance = max($total_payment - $allocatedPaid, 0.00);

    return [
        'jeja_no' => $row['jeja_no'],
        'student_name' => $row['full_name'],
        'student_email' => $row['email'] ?? '',
        'parent_email' => $row['parent_email'] ?? '',
        'due_date' => $due_date,
        'due_month' => firstDayOfMonth($due_date),
        'months_due' => $months_due,
        'total_payment' => $total_payment,
        'remaining_balance' => $remainingBalance
    ];
}

function sendEmailViaSMTP2GO(array $payload): array {
    $url = 'https://api.smtp2go.com/v3/email/send';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);
    return ['http_code' => $httpCode, 'body' => $response, 'error' => $curlErr];
}

function buildEmailBodies(string $studentName, float $amountToShow, string $dueMonthYmd, bool $isPartial, float $fullAmount): array {
	$dueDate = new DateTime($dueMonthYmd);
	$amount = number_format($amountToShow, 2);
	$full = number_format($fullAmount, 2);
	$same = abs($amountToShow - $fullAmount) < 0.009;

	// Always communicate Total Dues (accumulated)
	$subject = 'Payment Reminder: ' . $studentName . ' Total Dues ' . $dueDate->format('F Y');

	$text = "Hello,\n\nThis is a friendly reminder that the total dues for $studentName for " . $dueDate->format('F Y') . " is ₱$amount.\n\nPlease settle at your earliest convenience.\n\nThank you.";

	$html = '<div style="font-family:Arial,Helvetica,sans-serif;line-height:1.5;color:#222">'
		  . '<h2 style="margin:0 0 12px">Payment Reminder</h2>'
		  . '<p>Hello,</p>'
		  . '<p>This is a friendly reminder that the total dues for <strong>' . htmlspecialchars($studentName) . '</strong> for '
		  . htmlspecialchars($dueDate->format('F Y')) . ' is <strong>₱' . $amount . '</strong>.</p>'
		  . '<p>Please settle at your earliest convenience.</p>'
		  . '<p>Thank you.</p>'
		  . '</div>';
	return [$subject, $text, $html];
}

function upsertReminderState(mysqli $conn, string $jejaNo, string $dueMonth): void {
    $jejaEsc = $conn->real_escape_string($jejaNo);
    $dueMonthEsc = $conn->real_escape_string($dueMonth);
    $conn->query("INSERT INTO dues_reminders (jeja_no, due_month, last_reminder_at, reminder_count) VALUES ('$jejaEsc', '$dueMonthEsc', NOW(), 1)
                  ON DUPLICATE KEY UPDATE last_reminder_at = NOW(), reminder_count = reminder_count + 1");
}

function logReminder(mysqli $conn, string $jejaNo, string $dueMonth, string $recipient, string $status, ?string $providerId, ?string $error): void {
    $jejaEsc = $conn->real_escape_string($jejaNo);
    $dueMonthEsc = $conn->real_escape_string($dueMonth);
    $toEsc = $conn->real_escape_string($recipient);
    $provEsc = $providerId ? ("'" . $conn->real_escape_string($providerId) . "'") : 'NULL';
    $errEsc = $error ? ("'" . $conn->real_escape_string($error) . "'") : 'NULL';
    $conn->query("INSERT INTO reminder_logs (jeja_no, due_month, sent_to, status, provider_id, error) VALUES ('$jejaEsc', '$dueMonthEsc', '$toEsc', '$status', $provEsc, $errEsc)");
}

function sendReminderForStudent(mysqli $conn, array $dueItem): array {
    $jejaNo = $dueItem['jeja_no'];
    $studentName = $dueItem['student_name'];
    $studentEmail = trim($dueItem['student_email'] ?? '');
    $parentEmail = trim($dueItem['parent_email'] ?? '');
    // Use the current month for email subject/body and reminder tracking
    $dueMonth = date('Y-m-01');
    $totalPayment = floatval($dueItem['total_payment']);

    $recipients = [];
    if ($studentEmail !== '') { $recipients[] = $studentEmail; }
    if ($parentEmail !== '' && $parentEmail !== $studentEmail) { $recipients[] = $parentEmail; }
    if (empty($recipients)) {
        return ['status' => 'skipped', 'message' => 'No recipient emails for student'];
    }

    // Email the accumulated total dues (Total Payment), regardless of partial payments
    $amountToEmail = $totalPayment;
    $isPartial = false;
    list($subject, $textBody, $htmlBody) = buildEmailBodies($studentName, $amountToEmail, $dueMonth, $isPartial, $totalPayment);

    $payload = [
        'api_key' => SMTP2GO_API_KEY,
        'to' => $recipients,
        'sender' => SMTP2GO_SENDER_EMAIL,
        'sender_name' => SMTP2GO_SENDER_NAME ?: "D'Marsians Taekwondo Gym",
        'subject' => $subject,
        'text_body' => $textBody,
        'html_body' => $htmlBody
    ];
    if (defined('ADMIN_BCC_EMAIL') && ADMIN_BCC_EMAIL) {
        $payload['bcc'] = [ADMIN_BCC_EMAIL];
    }

    $resp = sendEmailViaSMTP2GO($payload);
    $providerId = null;
    $err = $resp['error'] ?: null;
    $status = ($resp['http_code'] >= 200 && $resp['http_code'] < 300) ? 'success' : 'failed';
    if ($resp['body']) {
        $decoded = json_decode($resp['body'], true);
        if (isset($decoded['data']) && isset($decoded['data']['message_id'])) {
            $providerId = $decoded['data']['message_id'];
        } elseif (isset($decoded['message_id'])) {
            $providerId = $decoded['message_id'];
        }
        if ($status !== 'success' && isset($decoded['errors'][0]['message'])) {
            $err = $decoded['errors'][0]['message'];
        }
    }

    foreach ($recipients as $rcpt) {
        logReminder($conn, $jejaNo, $dueMonth, $rcpt, $status, $providerId, $err);
    }
    if ($status === 'success') {
        upsertReminderState($conn, $jejaNo, $dueMonth);
    }

    return ['status' => $status, 'provider_id' => $providerId, 'error' => $err];
}

function getAllCurrentDues(mysqli $conn): array {
    $dues = [];
    $firstDayNextMonth = date('Y-m-01', strtotime('first day of next month'));
    $endOfThisMonth = date('Y-m-t');
    $sql = "
        SELECT s.jeja_no
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
          AND (p.date_paid < '$firstDayNextMonth' OR p.date_paid IS NULL)
        ORDER BY s.full_name ASC
    ";
    if ($res = $conn->query($sql)) {
        while ($r = $res->fetch_assoc()) {
            $due = computeStudentDue($conn, $r['jeja_no']);
            if ($due !== null) {
                $dues[] = $due;
            }
        }
    }
    return $dues;
}

$conn = getDb();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'POST required']);
    exit();
}

$raw = file_get_contents('php://input');
$payload = [];
if (!empty($raw)) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) { $payload = $decoded; }
}
// Fallback to form-encoded
if (empty($payload)) { $payload = $_POST; }

$mode = isset($payload['mode']) ? $payload['mode'] : 'single';

if ($mode === 'bulk') {
    $dues = getAllCurrentDues($conn);
    $results = [];
    foreach ($dues as $item) {
        $results[] = [
            'jeja_no' => $item['jeja_no'],
            'result' => sendReminderForStudent($conn, $item)
        ];
    }
    echo json_encode(['status' => 'success', 'count' => count($results), 'results' => $results]);
    exit();
}

// single mode
$jejaNo = isset($payload['jeja_no']) ? trim($payload['jeja_no']) : '';
if ($jejaNo === '') {
    echo json_encode(['status' => 'error', 'message' => 'jeja_no is required']);
    exit();
}

$due = computeStudentDue($conn, $jejaNo);
if ($due === null) {
    echo json_encode(['status' => 'error', 'message' => 'No outstanding dues for this student this month']);
    exit();
}

$res = sendReminderForStudent($conn, $due);
echo json_encode(['status' => $res['status'], 'provider_id' => $res['provider_id'], 'error' => $res['error']]);
exit();
?>


