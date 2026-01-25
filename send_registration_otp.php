<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';
require_once 'config.php';

date_default_timezone_set('Asia/Manila');

// Safety net: ensure registration_otps table exists
@$conn->query("CREATE TABLE IF NOT EXISTS `registration_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
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
    // Validate required fields
    if (empty($payload['api_key']) || empty($payload['to']) || empty($payload['sender'])) {
        return [
            'http_code' => 0,
            'body' => json_encode(['error' => 'Missing required email parameters']),
            'error' => 'Missing required email parameters'
        ];
    }
    
    $url = 'https://api.smtp2go.com/v3/email/send';
    $jsonPayload = json_encode($payload);
    
    // Check for JSON encoding errors
    if ($jsonPayload === false) {
        $jsonError = json_last_error_msg();
        error_log("JSON encoding error in sendEmailViaSMTP2GO: " . $jsonError);
        return [
            'http_code' => 0,
            'body' => json_encode(['error' => 'JSON encoding failed: ' . $jsonError]),
            'error' => 'JSON encoding failed: ' . $jsonError
        ];
    }
    
    $ch = curl_init($url);
    if ($ch === false) {
        error_log("Failed to initialize cURL for SMTP2GO");
        return [
            'http_code' => 0,
            'body' => json_encode(['error' => 'Failed to initialize cURL']),
            'error' => 'Failed to initialize cURL'
        ];
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 second timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 second connection timeout
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    $curlErrNo = curl_errno($ch);
    curl_close($ch);
    
    // Enhanced error logging for debugging
    if ($curlErrNo !== 0) {
        error_log("cURL Error #{$curlErrNo}: {$curlErr}");
    }
    
    return ['http_code' => $httpCode, 'body' => $response, 'error' => $curlErr, 'curl_errno' => $curlErrNo];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

$email = isset($_POST['email']) ? trim(strtolower($_POST['email'])) : '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Valid email address is required']);
    exit();
}

// Check database connection
if (!$conn || $conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

// Basic throttle: avoid sending more than once every 60 seconds
$tooSoon = false;
if ($stmt = $conn->prepare("SELECT last_sent_at FROM registration_otps WHERE LOWER(email) = LOWER(?) AND consumed = 0 ORDER BY id DESC LIMIT 1")) {
    $stmt->bind_param('s', $email);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $lastSent = strtotime($row['last_sent_at']);
            if ($lastSent && (time() - $lastSent) < 60) {
                $tooSoon = true;
            }
        }
    }
    $stmt->close();
}

if ($tooSoon) {
    echo json_encode(['status' => 'error', 'message' => 'Please wait 60 seconds before requesting another OTP']);
    exit();
}

// Generate 6-digit OTP and store hashed
$otp = strval(random_int(100000, 999999));
$otpHash = password_hash($otp, PASSWORD_DEFAULT);
$expiresAt = date('Y-m-d H:i:s', time() + 5 * 60);
$now = date('Y-m-d H:i:s');

// Invalidate previous active OTPs for this email (case-insensitive)
if ($stmt = $conn->prepare("UPDATE registration_otps SET consumed = 1 WHERE LOWER(email) = LOWER(?) AND consumed = 0")) {
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->close();
}

// Insert new OTP record
if ($stmt = $conn->prepare("INSERT INTO registration_otps (email, otp_hash, otp_expires_at, attempt_count, last_sent_at, consumed) VALUES (?,?,?,0,?,0)")) {
    $stmt->bind_param('ssss', $email, $otpHash, $expiresAt, $now);
    $stmt->execute();
    $stmt->close();
}

// Validate SMTP2GO configuration before attempting to send
$apiKey = defined('SMTP2GO_API_KEY') ? SMTP2GO_API_KEY : getenv('SMTP2GO_API_KEY');
$senderEmail = defined('SMTP2GO_SENDER_EMAIL') ? SMTP2GO_SENDER_EMAIL : getenv('SMTP2GO_SENDER_EMAIL');

if (empty($apiKey) || $apiKey === '' || $apiKey === 'your_smtp2go_api_key_here') {
    error_log("ERROR: SMTP2GO_API_KEY is not configured. Cannot send OTP email to {$email}");
    echo json_encode(['status' => 'error', 'message' => 'Email service not configured']);
    exit();
}

if (empty($senderEmail) || $senderEmail === '' || $senderEmail === 'your_email@example.com') {
    error_log("ERROR: SMTP2GO_SENDER_EMAIL is not configured. Cannot send OTP email to {$email}");
    echo json_encode(['status' => 'error', 'message' => 'Email service not configured']);
    exit();
}

// Build email content
$subject = 'Your Registration OTP Code - D\'Marsians Taekwondo';
$text = "Your OTP code is: $otp\nThis code will expire in 5 minutes.\n\nPlease enter this code to verify your email and complete your registration.";
$html = '<div style="font-family:Arial,Helvetica,sans-serif;line-height:1.5;color:#222">'
      . '<h2 style="margin:0 0 12px">Registration Email Verification</h2>'
      . '<p>Your OTP code is: <strong style="font-size:18px;color:#00D01D;">' . htmlspecialchars($otp) . '</strong></p>'
      . '<p>This code will expire in 5 minutes.</p>'
      . '<p>Please enter this code to verify your email and complete your registration.</p>'
      . '</div>';

$payload = [
    'api_key' => $apiKey,
    'to' => [$email],
    'sender' => $senderEmail,
    'sender_name' => (defined('SMTP2GO_SENDER_NAME') && SMTP2GO_SENDER_NAME) ? SMTP2GO_SENDER_NAME : "D'Marsians Taekwondo Gym",
    'subject' => $subject,
    'text_body' => $text,
    'html_body' => $html
];
if (defined('ADMIN_BCC_EMAIL') && ADMIN_BCC_EMAIL) {
    $payload['bcc'] = [ADMIN_BCC_EMAIL];
}

// Send email and check response
$emailResult = sendEmailViaSMTP2GO($payload);
$emailSent = false;
$providerId = null;

if ($emailResult['http_code'] >= 200 && $emailResult['http_code'] < 300) {
    // HTTP success, but need to check API response
    $responseBody = json_decode($emailResult['body'], true);
    
    // Check for message_id which indicates successful send (multiple possible structures)
    if (isset($responseBody['data']) && isset($responseBody['data']['message_id'])) {
        $providerId = $responseBody['data']['message_id'];
        $emailSent = true;
        error_log("OTP email sent successfully to {$email}. Message ID: {$providerId}");
    } elseif (isset($responseBody['message_id'])) {
        $providerId = $responseBody['message_id'];
        $emailSent = true;
        error_log("OTP email sent successfully to {$email}. Message ID: {$providerId}");
    } elseif (isset($responseBody['data']) && isset($responseBody['data']['error'])) {
        // API returned an error
        $errorMsg = $responseBody['data']['error'] ?? 'Unknown error';
        error_log("SMTP2GO API error for {$email}: {$errorMsg}");
    } else {
        // HTTP 200 but unclear response - log for debugging but assume success if HTTP was 200
        // This handles cases where email was sent but response structure is unexpected
        error_log("OTP email HTTP success for {$email}, but response structure unclear. Response: " . substr($emailResult['body'], 0, 200));
        // If HTTP 200-299, assume email was sent (user will receive it)
        $emailSent = true;
    }
} else {
    // HTTP error
    error_log("Failed to send OTP email to {$email}. HTTP: " . ($emailResult['http_code'] ?? 'N/A') . ", Error: " . ($emailResult['error'] ?? 'Unknown'));
    if ($emailResult['body']) {
        $responseBody = json_decode($emailResult['body'], true);
        if (isset($responseBody['data']['error'])) {
            error_log("SMTP2GO error: " . $responseBody['data']['error']);
        }
    }
}

if ($emailSent) {
    echo json_encode(['status' => 'success', 'message' => 'OTP sent to your email. Please check your inbox.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send OTP. Please try again.']);
}
?>
