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
    $curlInfo = curl_getinfo($ch);
    curl_close($ch);
    
    // Enhanced error logging for debugging
    if ($curlErrNo !== 0) {
        error_log("cURL Error #{$curlErrNo}: {$curlErr}");
        error_log("cURL Info: " . json_encode($curlInfo, JSON_PRETTY_PRINT));
    }
    
    return ['http_code' => $httpCode, 'body' => $response, 'error' => $curlErr, 'curl_errno' => $curlErrNo];
}

// Log incoming request
error_log("========================================");
error_log("OTP Request Received");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST Data: " . json_encode($_POST));
error_log("========================================");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("ERROR: Invalid request method. Expected POST, got: " . $_SERVER['REQUEST_METHOD']);
    header('Location: forgot_admin_password.php');
    exit();
}

// Check database connection
if (!$conn || $conn->connect_error) {
    error_log("ERROR: Database connection error in admin_send_otp.php: " . ($conn->connect_error ?? 'Connection failed'));
    error_log("DEBUG: Check database environment variables: DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT");
    // Still redirect to success page for security
    header('Location: forgot_admin_password.php?sent=1');
    exit();
}

$identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
if ($identifier === '') {
    header('Location: forgot_admin_password.php?sent=1');
    exit();
}

// Lookup admin by username or email
$admin = null;
$dbError = null;
if ($stmt = $conn->prepare("SELECT id, email, username FROM admin_accounts WHERE username = ? OR email = ? LIMIT 1")) {
    $stmt->bind_param('ss', $identifier, $identifier);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $admin = $res->fetch_assoc();
        }
    } else {
        $dbError = $stmt->error;
        error_log("Database query error in admin_send_otp.php: " . $dbError);
    }
    $stmt->close();
} else {
    $dbError = $conn->error;
    error_log("Database prepare error in admin_send_otp.php: " . $dbError);
}

// Always respond with success to avoid user enumeration
if (!$admin || empty($admin['email'])) {
    error_log("INFO: OTP request for identifier '{$identifier}' - account not found (security: showing success message)");
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
    error_log("INFO: OTP request throttled for {$adminEmail} - too soon (less than 60 seconds since last request)");
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

// Validate SMTP2GO configuration before attempting to send
$apiKey = defined('SMTP2GO_API_KEY') ? SMTP2GO_API_KEY : getenv('SMTP2GO_API_KEY');
$senderEmail = defined('SMTP2GO_SENDER_EMAIL') ? SMTP2GO_SENDER_EMAIL : getenv('SMTP2GO_SENDER_EMAIL');

// Enhanced logging for debugging
error_log("========================================");
error_log("OTP Email Debug - Starting OTP send process");
error_log("Admin Email: {$adminEmail}");
error_log("Admin ID: {$adminId}");
error_log("OTP Code: {$otp}");
error_log("SMTP2GO_API_KEY from constant: " . (defined('SMTP2GO_API_KEY') ? 'SET (length: ' . strlen(SMTP2GO_API_KEY) . ')' : 'NOT SET'));
error_log("SMTP2GO_API_KEY from getenv(): " . (getenv('SMTP2GO_API_KEY') ? 'SET (length: ' . strlen(getenv('SMTP2GO_API_KEY')) . ')' : 'NOT SET'));
error_log("SMTP2GO_SENDER_EMAIL from constant: " . (defined('SMTP2GO_SENDER_EMAIL') ? 'SET (' . SMTP2GO_SENDER_EMAIL . ')' : 'NOT SET'));
error_log("SMTP2GO_SENDER_EMAIL from getenv(): " . (getenv('SMTP2GO_SENDER_EMAIL') ? 'SET (' . getenv('SMTP2GO_SENDER_EMAIL') . ')' : 'NOT SET'));
error_log("========================================");

if (empty($apiKey) || $apiKey === '' || $apiKey === 'your_smtp2go_api_key_here') {
    error_log("ERROR: SMTP2GO_API_KEY is not configured. Cannot send OTP email to {$adminEmail}");
    error_log("DEBUG: apiKey value is: " . ($apiKey ?: 'empty string'));
    error_log("DEBUG: Check Digital Ocean App Platform -> Settings -> App-Level Environment Variables -> SMTP2GO_API_KEY");
    // Still redirect to success page for security (avoid user enumeration)
    header('Location: forgot_admin_password.php?sent=1');
    exit();
}

if (empty($senderEmail) || $senderEmail === '' || $senderEmail === 'your_email@example.com') {
    error_log("ERROR: SMTP2GO_SENDER_EMAIL is not configured. Cannot send OTP email to {$adminEmail}");
    error_log("Current value: " . ($senderEmail ?: 'empty'));
    error_log("DEBUG: Check Digital Ocean App Platform -> Settings -> App-Level Environment Variables -> SMTP2GO_SENDER_EMAIL");
    // Still redirect to success page for security (avoid user enumeration)
    header('Location: forgot_admin_password.php?sent=1');
    exit();
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
    'api_key' => $apiKey,
    'to' => [$adminEmail],
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

// Log the result for debugging
$emailSent = false;
$providerId = null;
$errorMessage = null;

if ($emailResult['http_code'] >= 200 && $emailResult['http_code'] < 300) {
    // HTTP success, but need to check API response
    $responseBody = json_decode($emailResult['body'], true);
    
    // Check for message_id which indicates successful send
    if (isset($responseBody['data']) && isset($responseBody['data']['message_id'])) {
        $providerId = $responseBody['data']['message_id'];
        $emailSent = true;
        error_log("OTP email sent successfully to {$adminEmail}. Message ID: {$providerId}");
    } elseif (isset($responseBody['message_id'])) {
        $providerId = $responseBody['message_id'];
        $emailSent = true;
        error_log("OTP email sent successfully to {$adminEmail}. Message ID: {$providerId}");
    } elseif (isset($responseBody['data']) && isset($responseBody['data']['error'])) {
        $errorMessage = $responseBody['data']['error'];
        error_log("SMTP2GO API Error for OTP to {$adminEmail}: " . $errorMessage);
        if (stripos($errorMessage, 'sender') !== false || stripos($errorMessage, 'verify') !== false || stripos($errorMessage, 'domain') !== false) {
            error_log("HINT: This error often means the sender email ({$senderEmail}) is not verified in SMTP2GO. Please verify it in your SMTP2GO account.");
        }
    } elseif (isset($responseBody['errors']) && is_array($responseBody['errors']) && count($responseBody['errors']) > 0) {
        foreach ($responseBody['errors'] as $err) {
            $errorMsg = $err['message'] ?? 'Unknown error';
            $errorMessage = $errorMsg;
            error_log("SMTP2GO API Error for OTP to {$adminEmail}: " . $errorMsg);
            if (stripos($errorMsg, 'sender') !== false || stripos($errorMsg, 'verify') !== false || stripos($errorMsg, 'domain') !== false) {
                error_log("HINT: The sender email ({$senderEmail}) may not be verified in SMTP2GO. Please verify it in your SMTP2GO account.");
            }
        }
    } elseif (isset($responseBody['error'])) {
        $errorMessage = is_string($responseBody['error']) ? $responseBody['error'] : json_encode($responseBody['error']);
        error_log("SMTP2GO API Error for OTP to {$adminEmail}: " . $errorMessage);
    } else {
        // No clear error, but also no message_id - log the full response for debugging
        error_log("SMTP2GO response unclear for OTP to {$adminEmail}. HTTP: {$emailResult['http_code']}, Response: " . substr($emailResult['body'], 0, 500));
        error_log("Full response: " . $emailResult['body']);
    }
} else {
    // HTTP error
    error_log("Failed to send OTP email to {$adminEmail}. HTTP Code: " . ($emailResult['http_code'] ?? 'N/A'));
    error_log("Response: " . ($emailResult['body'] ?? 'No response'));
    if (!empty($emailResult['error'])) {
        $errorMessage = $emailResult['error'];
        error_log("cURL Error: " . $errorMessage);
        if (isset($emailResult['curl_errno'])) {
            error_log("cURL Error Number: " . $emailResult['curl_errno']);
            // Common cURL error codes
            $curlErrorCodes = [
                6 => 'Could not resolve host (DNS issue)',
                7 => 'Failed to connect to host (network/firewall issue)',
                28 => 'Operation timeout (connection or read timeout)',
                35 => 'SSL connect error (SSL/TLS handshake failed)',
                60 => 'SSL certificate problem (certificate verification failed)'
            ];
            if (isset($curlErrorCodes[$emailResult['curl_errno']])) {
                error_log("cURL Error Meaning: " . $curlErrorCodes[$emailResult['curl_errno']]);
            }
        }
    }
    
    // Try to parse response for more details
    if (!empty($emailResult['body'])) {
        $responseBody = json_decode($emailResult['body'], true);
        if (isset($responseBody['errors']) && is_array($responseBody['errors'])) {
            foreach ($responseBody['errors'] as $err) {
                $errorMsg = $err['message'] ?? 'Unknown error';
                $errorMessage = $errorMsg;
                error_log("SMTP2GO Error Detail: " . $errorMsg);
                if (stripos($errorMsg, 'sender') !== false || stripos($errorMsg, 'verify') !== false || stripos($errorMsg, 'domain') !== false) {
                    error_log("HINT: The sender email ({$senderEmail}) may not be verified in SMTP2GO. Please verify it in your SMTP2GO account.");
                }
            }
        }
    }
}

// Log final status
if (!$emailSent) {
    error_log("WARNING: OTP email was NOT successfully sent to {$adminEmail}. Check SMTP2GO configuration and sender email verification.");
}

// Always redirect to success page for user privacy (avoid user enumeration)
// Even if email fails, we don't reveal this to the user
header('Location: forgot_admin_password.php?sent=1');
exit();


