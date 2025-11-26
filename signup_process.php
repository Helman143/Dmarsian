<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    // Generate username from email (everything before @)
    $username = strstr($email, '@', true);
    
    // Store password in plain text (for admin visibility)
    $plain_password = $password;
    
    $conn = connectDB();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        header("Location: signup.php?error=email_exists");
        exit();
    }
    
    // Insert new user with plain text password
    $stmt = $conn->prepare("INSERT INTO users (name, email, username, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $username, $plain_password);
    
    if ($stmt->execute()) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $stmt->insert_id;
        header("Location: dashboard.php");
        exit();
    } else {
        header("Location: signup.php?error=registration_failed");
        exit();
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: signup.php");
    exit();
}
?> 