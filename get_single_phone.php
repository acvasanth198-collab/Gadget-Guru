<?php
// Set the content type to JSON for the response
header('Content-Type: application/json');

// 1. Include the database configuration file
require_once 'config.php';

// 2. Validate that the phone_id is provided in the URL
if (!isset($_GET['phone_id']) || empty($_GET['phone_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Phone ID is required."]);
    exit;
}

// 3. Sanitize the input to ensure it's an integer
$phone_id = intval($_GET['phone_id']);
if ($phone_id <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Invalid Phone ID provided."]);
    exit;
}

// 4. Prepare and execute the database query using prepared statements for security
// The query is now much simpler as it only targets the 'smartphones' table.
$query = "SELECT * FROM smartphones WHERE phone_id = ?";

$stmt = $conn->prepare($query);
if (!$stmt) {
    // If the statement preparation fails, it's a server error
    http_response_code(500);
    echo json_encode(["error" => "Failed to prepare the database query."]);
    $conn->close();
    exit;
}

// Bind the integer phone_id to the placeholder
$stmt->bind_param("i", $phone_id);
$stmt->execute();
$result = $stmt->get_result();

// 5. Check the result and send the JSON response
if ($result->num_rows > 0) {
    // Fetch the single row of phone data
    $phone_data = $result->fetch_assoc();
    // Encode the associative array into a JSON object and output it
    echo json_encode($phone_data);
} else {
    // If no rows are returned, the phone was not found
    http_response_code(404); // Not Found
    echo json_encode(["error" => "Phone with the specified ID was not found."]);
}

// 6. Close the statement and the database connection
$stmt->close();
$conn->close();
?>