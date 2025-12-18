<?php
/**
 * Script to create a new admin account with your real email
 * This will create an account you can use to log in
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Admin Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .form-box {
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 15px;
        }
        button:hover {
            background: #0056b3;
        }
        .info-box {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 3px;
            margin: 15px 0;
        }
        .warning-box {
            background: #fff3cd;
            padding: 15px;
            border-radius: 3px;
            margin: 15px 0;
            color: #856404;
        }
        table {
            width: 100%;
            margin: 15px 0;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h1>Create New Admin Account</h1>
        
        <?php
        // Check database connection
        if (!isset($conn) || $conn === false || (isset($conn->connect_error) && $conn->connect_error)) {
            echo '<p class="error">✗ Database connection failed</p>';
            echo '<p>Please check your database configuration.</p>';
            exit;
        }
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Validation
            $errors = [];
            
            if (empty($email)) {
                $errors[] = "Email is required";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format";
            }
            
            if (empty($username)) {
                $errors[] = "Username is required";
            } elseif (strlen($username) < 3) {
                $errors[] = "Username must be at least 3 characters";
            }
            
            if (empty($password)) {
                $errors[] = "Password is required";
            } elseif (strlen($password) < 6) {
                $errors[] = "Password must be at least 6 characters";
            }
            
            if ($password !== $confirm_password) {
                $errors[] = "Passwords do not match";
            }
            
            if (empty($errors)) {
                // Check if email or username already exists
                $check_stmt = $conn->prepare("SELECT id, username, email FROM admin_accounts WHERE email = ? OR username = ?");
                $check_stmt->bind_param("ss", $email, $username);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    $existing = $check_result->fetch_assoc();
                    echo '<div class="warning-box">';
                    echo '<p class="warning">⚠ Account already exists!</p>';
                    echo '<p>An admin account with this email or username already exists:</p>';
                    echo '<ul>';
                    echo '<li>Username: ' . htmlspecialchars($existing['username']) . '</li>';
                    echo '<li>Email: ' . htmlspecialchars($existing['email']) . '</li>';
                    echo '</ul>';
                    echo '<p>Please use a different email or username.</p>';
                    echo '</div>';
                    $check_stmt->close();
                } else {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert the new admin account
                    $insert_stmt = $conn->prepare("INSERT INTO admin_accounts (email, username, password) VALUES (?, ?, ?)");
                    $insert_stmt->bind_param("sss", $email, $username, $hashed_password);
                    
                    if ($insert_stmt->execute()) {
                        $new_id = $conn->insert_id;
                        echo '<div class="info-box">';
                        echo '<p class="success">✓ Admin Account Created Successfully!</p>';
                        echo '<h3>Your Account Details:</h3>';
                        echo '<table>';
                        echo '<tr><th>Field</th><th>Value</th></tr>';
                        echo '<tr><td>ID</td><td>' . htmlspecialchars($new_id) . '</td></tr>';
                        echo '<tr><td>Email</td><td>' . htmlspecialchars($email) . '</td></tr>';
                        echo '<tr><td>Username</td><td>' . htmlspecialchars($username) . '</td></tr>';
                        echo '<tr><td>Password</td><td>•••••••• (hidden for security)</td></tr>';
                        echo '</table>';
                        echo '</div>';
                        echo '<div class="info-box">';
                        echo '<p><strong>You can now log in using:</strong></p>';
                        echo '<ul>';
                        echo '<li>Username: <strong>' . htmlspecialchars($username) . '</strong></li>';
                        echo '<li>OR Email: <strong>' . htmlspecialchars($email) . '</strong></li>';
                        echo '<li>Password: (the password you just set)</li>';
                        echo '</ul>';
                        echo '</div>';
                        echo '<p style="text-align:center;margin-top:20px">';
                        echo '<a href="admin_login.php" class="btn">Go to Login Page</a>';
                        echo '</p>';
                        $insert_stmt->close();
                    } else {
                        echo '<p class="error">✗ Error creating account: ' . htmlspecialchars($insert_stmt->error) . '</p>';
                    }
                }
            } else {
                echo '<div class="warning-box">';
                echo '<p class="error">Please fix the following errors:</p>';
                echo '<ul>';
                foreach ($errors as $error) {
                    echo '<li>' . htmlspecialchars($error) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
        }
        
        // Show form if not submitted or if there were errors
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !empty($errors) || (isset($check_result) && $check_result->num_rows > 0)) {
        ?>
        
        <div class="info-box">
            <p><strong>Create a new admin account with your real email address</strong></p>
            <p>This account will work with the "Forgot Password" feature since it uses a real email.</p>
        </div>
        
        <form method="POST">
            <label for="email">Email Address (Use your real email):</label>
            <input type="email" id="email" name="email" 
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                   required 
                   placeholder="your.email@example.com">
            <small style="color:#666">This must be a real email address to receive password reset emails</small>
            
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" 
                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                   required 
                   minlength="3"
                   placeholder="Choose a username">
            <small style="color:#666">Must be at least 3 characters</small>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" 
                   required 
                   minlength="6"
                   placeholder="Enter a secure password">
            <small style="color:#666">Must be at least 6 characters</small>
            
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" 
                   required 
                   minlength="6"
                   placeholder="Re-enter your password">
            
            <button type="submit">Create Admin Account</button>
        </form>
        
        <div style="margin-top:20px;text-align:center">
            <a href="admin_login.php" style="color:#666;text-decoration:none">← Back to Login</a>
        </div>
        
        <?php } ?>
    </div>
</body>
</html>

