<?php
session_start(); // start the session

// database configuration
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    // Return a JSON response for AJAX
    echo json_encode(['status' => 'error', 'message' => 'Please log in to manage your wishlist.']);
    exit();
}

$user_uid = $_SESSION['uid']; // Gets the logged-in user's ID
$phone_id = $_POST['phone_id'] ?? null; // Gets the phone_id from the POST request

// Basic validation for phone_id
if (!$phone_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid phone selected.']);
    exit();
}

// 1. Check if the item is already in the wishlist
$check_sql = "SELECT * FROM wishlist WHERE uid = ? AND phone_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $user_uid, $phone_id); // 'ii' for two integers
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // 2. If it exists, remove it (UN-WISHLIST)
    $delete_sql = "DELETE FROM wishlist WHERE uid = ? AND phone_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $user_uid, $phone_id);

    if ($delete_stmt->execute()) {
        echo json_encode(['status' => 'removed', 'message' => 'Phone removed from wishlist!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to remove from wishlist: ' . $delete_stmt->error]);
    }
    $delete_stmt->close();
} else {
    // 3. If it doesn't exist, add it (ADD TO WISHLIST)
    $insert_sql = "INSERT INTO wishlist (uid, phone_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);

    if ($insert_stmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
        exit();
    }

    $insert_stmt->bind_param("ii", $user_uid, $phone_id); // 'ii' indicates both are integers

    if ($insert_stmt->execute()) {
        echo json_encode(['status' => 'added', 'message' => 'Phone added to wishlist!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add to wishlist: ' . $insert_stmt->error]);
    }
    $insert_stmt->close();
}

$check_stmt->close();
$conn->close();
?>