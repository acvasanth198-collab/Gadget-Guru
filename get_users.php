<?php
// --- 1. SETUP & SECURITY ---

// Start the session to access the logged-in user's data
session_start();

// Include the central database connection file
include 'config.php';

// Set the response type to JSON for the AJAX call
header('Content-Type: application/json');

// Security Check: Ensure the user is a logged-in admin.
// This prevents unauthorized users from accessing the user list.
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    // If not an admin, send an "Unauthorized" error and stop the script.
    http_response_code(401); 
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit();
}


// --- 2. FETCH USERS FROM DATABASE ---

// The SQL query to select all user data needed for the admin table.
$query = "SELECT uid, username, email, role, status FROM users";

// Execute the query
$result = $conn->query($query);

// Initialize an empty array to hold the user data
$users = [];

// Check if the query was successful
if ($result) {
    // Loop through each row of the result and add it to the $users array
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    // If the query fails, send a server error response
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch user data.']);
    exit();
}


// --- 3. SEND RESPONSE & CLEANUP ---

// Encode the final $users array into a JSON string and output it
echo json_encode($users);

// Close the database connection
$conn->close();
?>