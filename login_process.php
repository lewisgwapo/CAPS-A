<?php
// login_process.php
session_start();

// Get form data
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Basic validation (in production, validate against database)
if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Email and password are required.';
    header('Location: login.php');
    exit;
}

// Basic email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email format.';
    header('Location: login.php');
    exit;
}

// TODO: Validate credentials against database
// For now, allow any valid email/password to proceed
// In production: hash password and verify against database

// Set session variables
$_SESSION['user_email'] = $email;
$_SESSION['logged_in'] = true;

// Redirect to dashboard
header('Location: dashboard.php');
exit;
?>
