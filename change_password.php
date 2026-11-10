<?php
session_start();
header('Content-Type: application/json');

// --- 1. Include the Database Configuration ---
require_once 'config.php';

// --- 2. Check User Authentication & Request Method ---
if (!isset($_SESSION['uid'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please log in again.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// --- 3. Validate Input ---
if (!isset($_POST['current_password']) || !isset($_POST['new_password'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Missing required password fields.']);
    exit;
}

$uid = $_SESSION['uid'];
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];

// --- Server-side validation for the NEW password (to match signup page) ---
if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$]).{8,}$/', $new_password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters, with 1 uppercase letter, 1 number, and one special character (!, @, #, $).']);
    $conn->close();
    exit;
}

// --- 4. Verify the CURRENT Password ---
// Prepare a statement to fetch the user's current password hash.
$stmt = $conn->prepare("SELECT password FROM users WHERE uid = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database query preparation failed.']);
    $conn->close();
    exit;
}

$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Check if user exists and if the provided current password is correct.
if (!$user || !password_verify($current_password, $user['password'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'The current password you entered is incorrect.']);
    $conn->close();
    exit;
}

// --- 5. Hash and Update the NEW Password ---
// The current password is correct, so proceed with hashing and updating the new one.
$new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE uid = ?");
$stmt->bind_param("si", $new_password_hashed, $uid);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Password changed successfully!']);
} else {
    // Handle potential database update errors.
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update password. Please try again.']);
}

// --- 6. Close Connections ---
$stmt->close();
$conn->close();
?>