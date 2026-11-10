<?php
session_start(); // <-- IMPORTANT: Start the session
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

if (!isset($_POST['email']) || !isset($_POST['otp'])) {
    echo json_encode(['status' => 'error', 'message' => 'Email and OTP are required.']);
    exit();
}

$email = trim($_POST['email']);
$otp_submitted = trim($_POST['otp']);

// Prepare to select the user's data
$stmt = $conn->prepare("SELECT uid, otp, username FROM users WHERE email = ? AND is_verified = 0");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stored_otp = $row['otp'];

    if ($stored_otp == $otp_submitted) {
        // OTP is correct, update the user to verified
        $update_stmt = $conn->prepare("UPDATE users SET is_verified = 1, otp = NULL WHERE email = ?");
        $update_stmt->bind_param("s", $email);

        if ($update_stmt->execute()) {
            // --- NEW: Automatically log the user in ---
            $_SESSION['uid'] = $row['uid'];
            $_SESSION['username'] = $row['username'];
            // ------------------------------------------

            echo json_encode(['status' => 'success', 'message' => 'Success! Redirecting to your homepage...']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Something went wrong during verification.']);
        }
        $update_stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid OTP. Please try again.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No pending verification found or account is already verified.']);
}

$stmt->close();
$conn->close();
?>