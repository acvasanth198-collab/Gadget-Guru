<?php
session_start();
header('Content-Type: application/json');

// --- 1. Include the Database Configuration ---
require_once 'config.php';

// --- 2. Check User Authentication ---
if (!isset($_SESSION['uid'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

// --- 3. Validate Input ---
if (!isset($_POST['new_username']) || !isset($_POST['password'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit;
}

// Get and sanitize POST data
$uid = $_SESSION['uid'];
$new_username = trim($_POST['new_username']);
$current_password = $_POST['password'];

// --- MODIFIED: Server-side validation to match signup-process.php ---
if (strlen($new_username) < 3 || !preg_match('/^[a-zA-Z0-9_ ]+$/', $new_username)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username must be at least 3 characters and contain only letters, numbers, spaces, or underscores.']);
    $conn->close();
    exit;
}

// --- 4. Verify Current Password ---
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

// Check if user exists and if the provided password is correct
if (!$user || !password_verify($current_password, $user['password'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'The password you entered is incorrect.']);
    $conn->close();
    exit;
}

// --- 5. Update the Username ---
// The password is correct, proceed with the update.
$stmt = $conn->prepare("UPDATE users SET username = ? WHERE uid = ?");
$stmt->bind_param("si", $new_username, $uid);

if ($stmt->execute()) {
    // On successful update, also update the session variable
    $_SESSION['username'] = $new_username;
    echo json_encode([
        'status' => 'success',
        'message' => 'Username updated successfully!',
        'new_username' => htmlspecialchars($new_username) // Send back the updated username to the frontend
    ]);
} else {
    // Handle potential database update errors
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update username. Please try again.']);
}

// --- 6. Close Connections ---
$stmt->close();
$conn->close();
?>