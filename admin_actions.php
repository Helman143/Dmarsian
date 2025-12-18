<?php
require_once 'db_connect.php';

function password_is_strong($pw) {
    return strlen($pw) >= 8
        && preg_match('/[a-z]/', $pw)
        && preg_match('/[A-Z]/', $pw)
        && preg_match('/\d/', $pw)
        && preg_match('/[^A-Za-z0-9]/', $pw)
        && !preg_match('/\s/', $pw);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_admin') {
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $name = $_POST['name'] ?? ''; // Assuming a name field will be added later or defaulted

        // Basic validation
        if (empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
            exit();
        }

        if ($password !== $confirm_password) {
            echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
            exit();
        }

        if (!password_is_strong($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be 8+ chars with upper, lower, number and special.']);
            exit();
        }

        // Store password in plain text (for admin visibility)
        $plain_password = $password;

        $conn = connectDB();

        // Check if email or username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email or Username already exists.']);
            $stmt->close();
            $conn->close();
            exit();
        }
        $stmt->close();

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, username, password, user_type) VALUES (?, ?, ?, ?, 'admin')");
        // For now, let's default name if not provided in the form or assume it's equal to username
        $user_display_name = !empty($name) ? $name : $username;
        $stmt->bind_param("ssss", $user_display_name, $email, $username, $plain_password);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Admin account created successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error creating account: ' . $stmt->error]);
        }

        $stmt->close();
        $conn->close();
    } elseif ($action === 'update_admin') {
        $id = $_POST['id'] ?? '';
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $name = $_POST['name'] ?? ''; // Assuming a name field will be added later or defaulted

        // Basic validation
        if (empty($id) || empty($email) || empty($username)) {
            echo json_encode(['status' => 'error', 'message' => 'ID, Email, and Username are required for update.']);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
            exit();
        }

        if (!empty($password) && $password !== $confirm_password) {
            echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
            exit();
        }

        if (!empty($password) && !password_is_strong($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be 8+ chars with upper, lower, number and special.']);
            exit();
        }

        if (!empty($password) && !password_is_strong($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be 8+ chars with upper, lower, number and special.']);
            exit();
        }

        $conn = connectDB();

        // Check for duplicate email or username (excluding the current user being updated)
        $stmt = $conn->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
        $stmt->bind_param("ssi", $email, $username, $id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email or Username already exists for another account.']);
            $stmt->close();
            $conn->close();
            exit();
        }
        $stmt->close();

        // Get current password to check if it needs hashing
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_user = $result->fetch_assoc();
        $stmt->close();

        // Construct update query dynamically based on whether password is provided
        $sql = "UPDATE users SET email = ?, username = ?";
        $params = "ss";
        $values = [&$email, &$username];

        if (!empty($password)) {
            // Store password as plain text
            $sql .= ", password = ?";
            $params .= "s";
            $values[] = &$password;
        }
        $sql .= " WHERE id = ?";
        $params .= "i";
        $values[] = &$id;

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
            $conn->close();
            exit();
        }
        
        call_user_func_array([$stmt, 'bind_param'], array_merge([$params], $values));

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Admin account updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating account: ' . $stmt->error]);
        }

        $stmt->close();
        $conn->close();
    } elseif ($action === 'super_admin_update') {
        // Special action for super admin to update passwords without hashing
        $id = $_POST['id'] ?? '';
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Basic validation
        if (empty($id) || empty($email) || empty($username)) {
            echo json_encode(['status' => 'error', 'message' => 'ID, Email, and Username are required for update.']);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
            exit();
        }

        if (!empty($password) && $password !== $confirm_password) {
            echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
            exit();
        }

        $conn = connectDB();

        // Check for duplicate email or username (excluding the current user being updated)
        $stmt = $conn->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
        $stmt->bind_param("ssi", $email, $username, $id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email or Username already exists for another account.']);
            $stmt->close();
            $conn->close();
            exit();
        }
        $stmt->close();

        // Construct update query dynamically based on whether password is provided
        $sql = "UPDATE users SET email = ?, username = ?";
        $params = "ss";
        $values = [&$email, &$username];

        if (!empty($password)) {
            // For super admin, store password as plain text
            $sql .= ", password = ?";
            $params .= "s";
            $values[] = &$password;
        }
        $sql .= " WHERE id = ?";
        $params .= "i";
        $values[] = &$id;

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
            $conn->close();
            exit();
        }
        
        call_user_func_array([$stmt, 'bind_param'], array_merge([$params], $values));

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Admin account updated successfully by super admin.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating account: ' . $stmt->error]);
        }

        $stmt->close();
        $conn->close();
    } elseif ($action === 'reset_admin_password') {
        // Action to reset admin password to a default password
        $id = $_POST['id'] ?? '';

        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'User ID is required for password reset.']);
            exit();
        }

        $conn = connectDB();
        
        // Generate a default password (complies with policy)
        $default_password = 'Adm!n123';
        $plain_password = $default_password;

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $plain_password, $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Password reset successfully.', 'new_password' => $default_password]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error resetting password: ' . $stmt->error]);
        }

        $stmt->close();
        $conn->close();
    } elseif ($action === 'delete_admin') {
        $id = $_POST['id'] ?? '';

        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'User ID is required for deletion.']);
            exit();
        }

        $conn = connectDB();
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Admin account deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error deleting account: ' . $stmt->error]);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?> 