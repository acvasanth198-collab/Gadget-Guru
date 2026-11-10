<?php
session_start();

// 1. Authentication Check: Ensure an admin is logged in.
if (!isset($_SESSION['uid'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit();
}

// Set the response header to indicate JSON content.
header('Content-Type: application/json');

// 2. Include the database configuration file.
// This establishes the $conn variable.
require_once 'config.php';

// --- Main Logic ---
try {
    // 3. Check if the phone_id was provided in the URL.
    // Your JavaScript `fetch` call sends it this way: `delete_phone.php?phone_id=...`
    if (!isset($_GET['phone_id'])) {
        http_response_code(400); // Bad Request
        throw new Exception("Phone ID was not provided.");
    }

    // Sanitize the input to ensure it's an integer.
    $phone_id = intval($_GET['phone_id']);

    // 4. Prepare a single DELETE statement for the 'smartphones' table.
    $sql = "DELETE FROM smartphones WHERE phone_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        throw new Exception("SQL prepare statement failed: " . $conn->error);
    }

    // 5. Bind the integer phone_id to the placeholder.
    $stmt->bind_param("i", $phone_id);

    // 6. Execute the query.
    if ($stmt->execute()) {
        // Check if a row was actually deleted.
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Phone deleted successfully!']);
        } else {
            // This case handles trying to delete a phone that doesn't exist.
            http_response_code(404); // Not Found
            echo json_encode(['status' => 'error', 'message' => 'Phone not found or already deleted.']);
        }
    } else {
        // If execution fails, throw an exception.
        throw new Exception("Execute failed: " . $stmt->error);
    }

} catch (Exception $e) {
    // Catch any errors and send a detailed error message.
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete phone.', 'error_detail' => $e->getMessage()]);

} finally {
    // 7. Always close the statement and the connection.
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>