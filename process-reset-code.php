<?php
session_start();
header('Content-Type: application/json');

// --- 1. VALIDATE SESSION AND INPUT ---
if (!isset($_SESSION['reset_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired. Please start over.']);
    exit();
}

if (empty($_POST['otp']) || !is_numeric($_POST['otp']) || strlen($_POST['otp']) != 6) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid 6-digit code.']);
    exit();
}

require_once 'config.php';
$email = $_SESSION['reset_email'];
$otp = $_POST['otp'];

// --- 2. CHECK OTP AGAINST DATABASE ---
$stmt = $conn->prepare("SELECT otp FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again.']);
    exit();
}

$user = $result->fetch_assoc();
$db_otp = $user['otp'];

if ($db_otp == $otp) {
    // --- 3. OTP IS CORRECT ---
    // Set a session flag to confirm OTP verification
    $_SESSION['otp_verified'] = true;
    
    // Clear the OTP from the database for security
    $update_stmt = $conn->prepare("UPDATE users SET otp = NULL WHERE email = ?");
    $update_stmt->bind_param("s", $email);
    $update_stmt->execute();
    $update_stmt->close();
    
    echo json_encode(['status' => 'success', 'message' => 'Verification successful.']);
} else {
    // --- 4. OTP IS INCORRECT ---
    echo json_encode(['status' => 'error', 'message' => 'Invalid verification code.']);
}

$stmt->close();
$conn->close();
?>