<?php
session_start();
header('Content-Type: application/json');

// --- 1. INCLUDE NECESSARY FILES ---
require_once 'config.php';       // Database connection
require_once 'mail-config.php';  // Mailer constants
require 'vendor/autoload.php';   // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- 2. VALIDATE INPUT ---
if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
    exit();
}

$email = $_POST['email'];

// --- 3. CHECK IF EMAIL EXISTS IN DATABASE ---
$stmt = $conn->prepare("SELECT uid, username FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Email not found, but we give a generic message for security
    echo json_encode(['status' => 'error', 'message' => 'If this email is registered, a code has been sent.']);
    exit();
}

$user = $result->fetch_assoc();
$username = $user['username'];

// --- 4. GENERATE AND STORE OTP ---
$otp = rand(100000, 999999); // Generate a 6-digit OTP

// Store the email in the session to use on the next page
$_SESSION['reset_email'] = $email;

// Update the user's record with the new OTP
$update_stmt = $conn->prepare("UPDATE users SET otp = ? WHERE email = ?");
$update_stmt->bind_param("is", $otp, $email);
$update_stmt->execute();

// --- 5. SEND OTP EMAIL WITH PHPMailer ---
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = MAIL_ENCRYPTION;
    $mail->Port       = MAIL_PORT;

    // Recipients
    $mail->setFrom(MAIL_USERNAME, 'Gadget Guru Support');
    $mail->addAddress($email, $username);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your Password Reset Code';
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <h2>Password Reset Request</h2>
            <p>Hello {$username},</p>
            <p>We received a request to reset your password. Use the following code to proceed:</p>
            <p style='font-size: 24px; font-weight: bold; letter-spacing: 2px; color: #e74c3c;'>{$otp}</p>
            <p>If you did not request this, please ignore this email.</p>
            <br>
            <p>Thanks,</p>
            <p>The Gadget Guru Team</p>
        </div>";

    $mail->send();
    echo json_encode(['status' => 'success', 'message' => 'OTP sent successfully.']);

} catch (Exception $e) {
    // Log the detailed error instead of showing it to the user
    error_log("Mailer Error: {$mail->ErrorInfo}");
    echo json_encode(['status' => 'error', 'message' => 'Could not send the email. Please try again later.']);
}

$stmt->close();
$update_stmt->close();
$conn->close();
?>