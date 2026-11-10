<?php
session_start();

// Include dependencies
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once 'config.php'; // Use your config file for the connection
require_once 'mail-config.php'; // Use your mail config file

header('Content-Type: application/json');

// --- 1. Authentication & Request Validation ---
if (!isset($_SESSION['uid'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

// --- 2. Input Validation ---
if (!isset($_POST['new_email']) || !isset($_POST['password'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit();
}

$new_email = trim($_POST['new_email']);
$password = $_POST['password'];
$admin_id = $_SESSION['uid'];

if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format provided.']);
    exit();
}

// --- 3. Database Checks ---
// First, get the current user's details to perform all checks
$stmt = $conn->prepare("SELECT email, password FROM users WHERE uid = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    http_response_code(404); // Not Found
    echo json_encode(['status' => 'error', 'message' => 'Could not find your admin account.']);
    $conn->close();
    exit();
}

// Check if the new email is the same as the current email
if (strtolower($new_email) === strtolower($user['email'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'This is already your current email address.']);
    $conn->close();
    exit();
}

// Check if the new email is already in use by ANOTHER user
$stmt = $conn->prepare("SELECT uid FROM users WHERE email = ?");
$stmt->bind_param("s", $new_email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    http_response_code(409); // Conflict
    echo json_encode(['status' => 'error', 'message' => 'This email address is already registered to another account.']);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// --- 4. Verify Password ---
if (!password_verify($password, $user['password'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Incorrect password. Please try again.']);
    $conn->close();
    exit();
}

// --- 5. Generate and Send OTP ---
// All checks passed.
$otp = rand(100000, 999999);
$_SESSION['email_change_otp'] = $otp;
$_SESSION['new_email_pending'] = $new_email;

$mail = new PHPMailer(true);
try {
    // Server settings from mail-config.php
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = MAIL_ENCRYPTION;
    $mail->Port       = MAIL_PORT;

    // Recipients
    $mail->setFrom(MAIL_USERNAME, 'Gadget Guru Support');
    $mail->addAddress($new_email); // Send to the new email for verification

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Verify Your New Gadget Guru Email Address';
    $mail->Body    = "<h1>Gadget Guru Email Verification</h1><p>Your 6-digit verification code to confirm your new email address is: <b>$otp</b></p><p>This code is valid for 10 minutes.</p>";
    $mail->AltBody = "Your Gadget Guru email verification code is: $otp";

    $mail->send();
    echo json_encode(['status' => 'success', 'message' => 'An OTP has been sent to your new email address.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => "OTP could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}

$conn->close();
?>