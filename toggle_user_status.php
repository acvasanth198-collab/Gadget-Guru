<?php
/**
 * =================================================================
 *   GadgetGuru | User Status Toggle Script (Block/Unblock)
 *   VERSION: Compatible with admin-d.php
 * =================================================================
 */

// --- 1. SETUP & SECURITY ---
session_start();
include 'config.php';
header('Content-Type: application/json');

// Security Check: Ensure the request is from a logged-in admin.
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'You do not have permission to perform this action.']);
    exit();
}


// --- 2. VALIDATE INPUT ---
// MODIFIED: Read the user ID from $_POST, which is used for FormData.
// This is the only change needed to make the file compatible.
if (!isset($_POST['uid'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'User ID not provided.']);
    exit;
}

// Sanitize the input to ensure it's an integer.
$uid = intval($_POST['uid']);


// --- 3. FETCH USER DATA ---
// (This section is already correct and requires no changes)
$stmt = $conn->prepare("SELECT status, role FROM users WHERE uid = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404); // Not Found
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    $stmt->close();
    $conn->close();
    exit;
}
$user = $result->fetch_assoc();
$stmt->close();


// --- 4. SECURITY CHECK: PREVENT BLOCKING ADMINS ---
// (This section is already correct and requires no changes)
if ($user['role'] === 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Admin users cannot be blocked.']);
    $conn->close();
    exit();
}


// --- 5. TOGGLE STATUS & UPDATE DATABASE ---
// (This section is already correct and requires no changes)
$currentStatus = $user['status'];
$newStatus = ($currentStatus == 1) ? 0 : 1; // Flip the status
$actionMessage = ($newStatus == 1) ? 'blocked' : 'unblocked';

$stmt = $conn->prepare("UPDATE users SET status = ? WHERE uid = ?");
$stmt->bind_param("ii", $newStatus, $uid);

if ($stmt->execute()) {
    http_response_code(200); // OK
    echo json_encode(['status' => 'success', 'message' => "User has been {$actionMessage} successfully."]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Failed to update user status.']);
}


// --- 6. CLEAN UP ---
$stmt->close();
$conn->close();
?>