<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once 'db_connect.php';
require_once 'config.php';

date_default_timezone_set('Asia/Manila');

// Safety net: ensure reset table exists (in case migration hasn't run yet)
@$conn->query("CREATE TABLE IF NOT EXISTS `admin_password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `otp_expires_at` datetime NOT NULL,
  `attempt_count` int(11) NOT NULL DEFAULT 0,
  `last_sent_at` datetime NOT NULL,
  `consumed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_active` (`email`,`consumed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot_admin_password.php');
    exit();
}

$identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
if ($identifier === '') {
    header('Location: forgot_admin_password.php?sent=1');
    exit();
}

// Lookup admin by username or email
$admin = null;
if ($stmt = $conn->prepare("SELECT id, email, username FROM admin_accounts WHERE username = ? OR email = ? LIMIT 1")) {
    $stmt->bind_param('ss', $identifier, $identifier);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $admin = $res->fetch_assoc();
        }
    }
    $stmt->close();
}

// Always respond with success to avoid user enumeration
if (!$admin || empty($admin['email'])) {
    header('Location: forgot_admin_password.php?sent=1');
    exit();
}

$adminEmail = $admin['email'];
$adminId = intval($admin['id']);

// Basic throttle: avoid sending more than once every 60 seconds
$tooSoon = false;
if ($stmt = $conn->prepare("SELECT last_sent_at FROM admin_password_resets WHERE email = ? AND consumed = 0 ORDER BY id DESC LIMIT 1")) {
    $stmt->bind_param('s', $adminEmail);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            $lastSent = strtotime($row['last_sent_at']);
            if ($lastSent && (time() - $lastSent) < 60) {
                $tooSoon = true;
            }
        }
    }
    $stmt->close();
}

if ($tooSoon) {
    header('Location: forgot_admin_password.php?sent=1');
    exit();
}

// Generate 6-digit OTP and store hashed
$otp = strval(random_int(100000, 999999));
$otpHash = password_hash($otp, PASSWORD_DEFAULT);
$expiresAt = date('Y-m-d H:i:s', time() + 5 * 60);
$now = date('Y-m-d H:i:s');

// Invalidate previous active resets for this email
if ($stmt = $conn->prepare("UPDATE admin_password_resets SET consumed = 1 WHERE email = ? AND consumed = 0")) {
    $stmt->bind_param('s', $adminEmail);
    $stmt->execute();
    $stmt->close();
}

// Insert new reset record
if ($stmt = $conn->prepare("INSERT INTO admin_password_resets (email, admin_id, otp_hash, otp_expires_at, attempt_count, last_sent_at, consumed) VALUES (?,?,?,?,0,?,0)")) {
    $stmt->bind_param('sisss', $adminEmail, $adminId, $otpHash, $expiresAt, $now);
    $stmt->execute();
    $stmt->close();
}

// Build email content
$subject = 'Your Admin OTP Code';
$text = "Your OTP code is: $otp\nThis code will expire in 5 minutes.";
$html = '<div style="font-family:Arial,Helvetica,sans-serif;line-height:1.5;color:#222">'
      . '<h2 style="margin:0 0 12px">Password Reset OTP</h2>'
      . '<p>Your OTP code is: <strong style="font-size:18px">' . htmlspecialchars($otp) . '</strong></p>'
      . '<p>This code will expire in 5 minutes.</p>'
      . '</div>';

$payload = [
    'api_key' => SMTP2GO_API_KEY,
    'to' => [$adminEmail],
    'sender' => SMTP2GO_SENDER_EMAIL,
    'sender_name' => SMTP2GO_SENDER_NAME ?: "D'Marsians Taekwondo Gym",
    'subject' => $subject,
    'text_body' => $text,
    'html_body' => $html
];
if (defined('ADMIN_BCC_EMAIL') && ADMIN_BCC_EMAIL) {
    $payload['bcc'] = [ADMIN_BCC_EMAIL];
}

// Send email (ignore response for user privacy)
sendEmailViaSMTP2GO($payload);

header('Location: forgot_admin_password.php?sent=1');
exit();


