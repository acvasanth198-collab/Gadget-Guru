<?php
// Set the header to output JSON
header('Content-Type: application/json');

// 1. Include your database configuration
require_once 'config.php';

// 2. Get the search term from the URL
$query = $_GET['query'] ?? '';

// 3. We won't search if the user has typed less than 2 characters
if (strlen($query) < 2) {
    echo json_encode([]); // Return an empty JSON array
    exit();
}

try {
    // 4. Prepare the search term with wildcards for a partial match
    $searchTerm = "%" . $query . "%";

    // 5. CORRECTED QUERY: Use the 'smartphones' table
    $sql = "SELECT brand_name, model_name 
            FROM smartphones 
            WHERE CONCAT(brand_name, ' ', model_name) LIKE ?
            LIMIT 10"; // Limit results to prevent overwhelming the user

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database query preparation failed: " . $conn->error);
    }

    // 6. Bind the search term to the query to prevent SQL injection
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    // 7. Fetch all matching rows into an array
    $phones = [];
    while ($row = $result->fetch_assoc()) {
        $phones[] = $row;
    }

    // 8. Send the array of found phones back to the JavaScript as a JSON string
    echo json_encode($phones);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'A server error occurred.', 'error_detail' => $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>