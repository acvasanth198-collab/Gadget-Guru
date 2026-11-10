<?php
session_start();

// --- 1. Include the Database Configuration ---
require_once 'config.php';

header('Content-Type: application/json');

// --- 2. Authentication & Session Validation ---
if (!isset($_SESSION['uid']) || !isset($_SESSION['email_change_otp']) || !isset($_SESSION['new_email_pending'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Invalid session or request. Please start over.']);
    exit();
}

// --- 3. Request Method and Input Validation ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

if (!isset($_POST['otp'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'OTP field is missing.']);
    exit();
}

// --- 4. Verify OTP ---
$submitted_otp = trim($_POST['otp']);
$stored_otp = $_SESSION['email_change_otp'];
$new_email = $_SESSION['new_email_pending'];

if ($submitted_otp != $stored_otp) {
    // OTP is incorrect
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid OTP. Please try again.']);
    exit();
}

// --- 5. Update Email in Database ---
// OTP is correct, update the user's email
$stmt = $conn->prepare("UPDATE users SET email = ? WHERE uid = ?");
$stmt->bind_param("si", $new_email, $_SESSION['uid']);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Email address updated successfully!']);
    
    // IMPORTANT: Clear session variables after successful update to prevent reuse
    unset($_SESSION['email_change_otp']);
    unset($_SESSION['new_email_pending']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update email address. Please try again.']);
}

// --- 6. Close Connections ---
$stmt->close();
$conn->close();
?>