<?php
// --- 1. SETUP & SECURITY ---
// Start the session to ensure only logged-in users can access this data
session_start();

// Include the central database connection file
include 'config.php';

// Set the response type to JSON
header('Content-Type: application/json');

// Security Check: If the user is not logged in, block access.
if (!isset($_SESSION['uid'])) {
    http_response_code(401); // Unauthorized status
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit();
}

// --- 2. DATABASE QUERIES ---
// Query for the total number of users
$user_result = $conn->query("SELECT COUNT(*) AS total_users FROM users");
$total_users = $user_result->fetch_assoc()['total_users'] ?? 0;

// Query for the total number of smartphones (using the correct table name)
$phone_result = $conn->query("SELECT COUNT(*) AS total_phones FROM smartphones");
$total_phones = $phone_result->fetch_assoc()['total_phones'] ?? 0;

// --- 3. SEND RESPONSE ---
// Close the database connection
$conn->close();

// Output the data as a JSON object
echo json_encode([
    'total_users' => $total_users,
    'total_phones' => $total_phones
]);
?>