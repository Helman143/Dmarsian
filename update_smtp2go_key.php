<?php
/**
 * Helper script to update SMTP2GO API key in .env file
 * Usage: Run this script in browser and follow the form
 */

$envFile = __DIR__ . DIRECTORY_SEPARATOR . '.env';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_api_key'])) {
    $newApiKey = trim($_POST['new_api_key']);
    
    if (empty($newApiKey)) {
        $error = "API key cannot be empty";
    } elseif (!file_exists($envFile)) {
        $error = ".env file not found at: $envFile";
    } else {
        // Read the .env file
        $content = file_get_contents($envFile);
        
        // Replace the API key line
        $pattern = '/^SMTP2GO_API_KEY=.*$/m';
        $replacement = 'SMTP2GO_API_KEY=' . $newApiKey;
        
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
            
            // Write back to file
            if (file_put_contents($envFile, $content) !== false) {
                $success = true;
                $message = "API key updated successfully!";
            } else {
                $error = "Failed to write to .env file. Please check file permissions.";
            }
        } else {
            $error = "SMTP2GO_API_KEY line not found in .env file";
        }
    }
}

// Read current API key (masked)
$currentKey = '';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'SMTP2GO_API_KEY=') === 0) {
            $currentKey = substr($line, strlen('SMTP2GO_API_KEY='));
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update SMTP2GO API Key</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; border: 1px solid #ddd; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .alert { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        input[type="text"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 20px; background: #5DD62C; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #4bc01f; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Update SMTP2GO API Key</h1>
        
        <?php if (isset($success) && $success): ?>
            <div class="alert alert-success">
                <strong>✓ Success!</strong> <?php echo htmlspecialchars($message); ?>
                <br><br>
                <a href="test_otp_email.php">Test the new API key →</a>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger">
                <strong>✗ Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="alert alert-info">
            <strong>Current API Key:</strong><br>
            <?php if ($currentKey): ?>
                <code><?php echo htmlspecialchars(substr($currentKey, 0, 8) . '...' . substr($currentKey, -4)); ?></code>
            <?php else: ?>
                <em>Not found in .env file</em>
            <?php endif; ?>
        </div>
        
        <h3>Steps to Get Your API Key:</h3>
        <ol>
            <li>Go to <a href="https://app.smtp2go.com/settings/api_keys" target="_blank">SMTP2GO API Keys</a></li>
            <li>Log in to your SMTP2GO account</li>
            <li>Copy your API key (it starts with <code>api-</code>)</li>
            <li>Paste it in the form below</li>
        </ol>
        
        <form method="POST">
            <label><strong>New SMTP2GO API Key:</strong></label>
            <input type="text" name="new_api_key" placeholder="api-XXXXXXXXXXXXX" required>
            <button type="submit">Update API Key</button>
        </form>
        
        <div class="alert alert-info" style="margin-top: 20px;">
            <strong>Note:</strong> After updating, test the email sending at:
            <a href="test_otp_email.php">test_otp_email.php</a>
        </div>
    </div>
</body>
</html>





