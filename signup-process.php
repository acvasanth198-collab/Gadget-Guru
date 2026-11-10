<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';
include 'config.php';
include 'mail-config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm = $_POST['confirm'];

if (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_ ]+$/', $username)) {
    echo json_encode(['status' => 'error', 'message' => 'Username must be at least 3 characters and contain only letters, numbers, spaces, or underscores.']);
    exit();
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
    exit();
}
if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$]).{8,}$/', $password)) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters, with 1 uppercase letter, 1 number, and one special character (!, @, #, $).']);
    exit();
}
if ($password !== $confirm) {
    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
    exit();
}

$check = $conn->prepare("SELECT uid FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'This email address is already registered.']);
    exit();
}
$check->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$role = "user";
$otp = rand(100000, 999999);

// This is the original, simpler INSERT query.
$insert = $conn->prepare("INSERT INTO users (username, email, password, role, otp, is_verified) VALUES (?, ?, ?, ?, ?, 0)");
$insert->bind_param("ssssi", $username, $email, $hashedPassword, $role, $otp);

if ($insert->execute()) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port       = MAIL_PORT;
        $mail->setFrom(MAIL_USERNAME, 'Gadget Guru');
        $mail->addAddress($email, $username);
        $mail->isHTML(true);
        $mail->Subject = 'Your Gadget Guru Account Verification Code';
        $mail->Body    = "<h1>Welcome to Gadget Guru!</h1><p>Your 6-digit verification code is: <b>$otp</b></p>";
        $mail->AltBody = "Your Gadget Guru verification code is: $otp";
        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'Registration successful! An OTP has been sent to your email.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Account created, but OTP could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Something went wrong during registration. Please try again.']);
}
$insert->close();
$conn->close();
?>