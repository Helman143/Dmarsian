<?php
header('Content-Type: application/json');
require_once 'config.php';

function sendEmailViaSMTP2GO(array $payload): array {
    $ch = curl_init('https://api.smtp2go.com/v3/email/send');
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

// File to store pending trial session requests
$file = 'trial_requests.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $request = [
        'student_name' => $_POST['student_name'] ?? '',
        'address' => $_POST['address'] ?? '',
        'parents_name' => $_POST['parent_name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'email' => $_POST['email'] ?? '',
        'parent_phone' => $_POST['parent_phone'] ?? '',
        'school' => $_POST['school'] ?? '',
        'class' => $_POST['class'] ?? '',
        'parent_email' => $_POST['parent_email'] ?? '',
        'belt_rank' => $_POST['belt_rank'] ?? '',
        'enroll_type' => $_POST['enroll_type'] ?? '',
        'date_requested' => date('Y-m-d H:i:s')
    ];
    if ($request['enroll_type'] !== 'Trial Session') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid enrollment type.']);
        exit();
    }
    // Read existing requests
    $requests = [];
    if (file_exists($file)) {
        $json = file_get_contents($file);
        $requests = json_decode($json, true) ?: [];
    }
    // Add new request
    $requests[] = $request;
    // Save back to file
    if (file_put_contents($file, json_encode($requests, JSON_PRETTY_PRINT))) {
        // Notify Super Admin via SMTP2Go (non-blocking: ignore response)
        $adminTo = (defined('ADMIN_BCC_EMAIL') && ADMIN_BCC_EMAIL) ? ADMIN_BCC_EMAIL : SMTP2GO_SENDER_EMAIL;
        $subject = 'New Trial Session Request Submitted';
        $textBody =
            "A new trial session request has been submitted.\n\n" .
            "Student: {$request['student_name']}\n" .
            "Parent: {$request['parents_name']}\n" .
            "Class: {$request['class']} | Belt: {$request['belt_rank']}\n" .
            "Student Email: {$request['email']} | Parent Email: {$request['parent_email']}\n" .
            "Student Phone: {$request['phone']} | Parent Phone: {$request['parent_phone']}\n" .
            "School: {$request['school']}\n" .
            "Address: {$request['address']}\n" .
            "Requested At: {$request['date_requested']}";
        $htmlBody =
            '<div style="font-family:Arial,Helvetica,sans-serif;line-height:1.5;color:#222">' .
                '<h3 style="margin:0 0 10px">New Trial Session Request</h3>' .
                '<p><strong>Student:</strong> ' . htmlspecialchars($request['student_name']) . '</p>' .
                '<p><strong>Parent:</strong> ' . htmlspecialchars($request['parents_name']) . '</p>' .
                '<p><strong>Class/Belt:</strong> ' . htmlspecialchars($request['class']) . ' | ' . htmlspecialchars($request['belt_rank']) . '</p>' .
                '<p><strong>Student Email:</strong> ' . htmlspecialchars($request['email']) . '<br>' .
                   '<strong>Parent Email:</strong> ' . htmlspecialchars($request['parent_email']) . '</p>' .
                '<p><strong>Student Phone:</strong> ' . htmlspecialchars($request['phone']) . '<br>' .
                   '<strong>Parent Phone:</strong> ' . htmlspecialchars($request['parent_phone']) . '</p>' .
                '<p><strong>School:</strong> ' . htmlspecialchars($request['school']) . '</p>' .
                '<p><strong>Address:</strong> ' . htmlspecialchars($request['address']) . '</p>' .
                '<p><em>Requested At: ' . htmlspecialchars($request['date_requested']) . '</em></p>' .
            '</div>';

        $payload = [
            'api_key'     => SMTP2GO_API_KEY,
            'to'          => [$adminTo],
            'sender'      => SMTP2GO_SENDER_EMAIL,
            'sender_name' => SMTP2GO_SENDER_NAME ?: "D'Marsians Taekwondo Gym",
            'subject'     => $subject,
            'text_body'   => $textBody,
            'html_body'   => $htmlBody
        ];
        if (defined('ADMIN_BCC_EMAIL') && ADMIN_BCC_EMAIL && ADMIN_BCC_EMAIL !== $adminTo) {
            $payload['bcc'] = [ADMIN_BCC_EMAIL];
        }
        sendEmailViaSMTP2GO($payload);
        echo json_encode(['status' => 'success', 'message' => 'Trial session request submitted!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save request.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}