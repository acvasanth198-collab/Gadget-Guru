<?php
session_start();
header('Content-Type: application/json');

// --- 1. VALIDATE SESSION ---
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid session. Please start the process again.']);
    exit();
}

// --- 2. VALIDATE INPUTS ---
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($new_password) || empty($confirm_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in both password fields.']);
    exit();
}

if ($new_password !== $confirm_password) {
    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
    exit();
}

// --- 3. PASSWORD STRENGTH VALIDATION ---
$errors = [];
if (strlen($new_password) < 8) {
    $errors[] = "Password must be at least 8 characters long.";
}
if (!preg_match('/[A-Z]/', $new_password)) {
    $errors[] = "Must contain at least one uppercase letter.";
}
if (!preg_match('/[0-9]/', $new_password)) {
    $errors[] = "Must contain at least one number.";
}
if (!preg_match('/[!@#$%]/', $new_password)) {
    $errors[] = "Must contain one of the special characters: !, @, #, $, %";
}

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit();
}

// --- 4. UPDATE PASSWORD IN DATABASE ---
require_once 'config.php';
$email = $_SESSION['reset_email'];
// Hash the new password securely
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed_password, $email);

if ($stmt->execute()) {
    // --- 5. SUCCESS: CLEAN UP SESSION AND RESPOND ---
    unset($_SESSION['reset_email']);
    unset($_SESSION['otp_verified']);
    session_destroy();
    
    echo json_encode(['status' => 'success', 'message' => 'Password has been updated successfully! Redirecting to login...']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update password. Please try again.']);
}

$stmt->close();
$conn->close();
?>