<?php
// Set the content type to JSON for the response
header('Content-Type: application/json');

// 1. Include the database configuration file
require_once 'config.php';

try {
    // 2. Prepare the SQL Query for the new 'smartphones' table
    // The logic remains the same, but the column names are updated.
    $sql = "
    SELECT
        *, -- Select all columns from the smartphones table
        (
            (CASE WHEN ram_gb >= 12 THEN 4 WHEN ram_gb >= 8 THEN 3 WHEN ram_gb >= 6 THEN 2 ELSE 1 END) +
            (CASE WHEN storage_gb >= 512 THEN 4 WHEN storage_gb >= 256 THEN 3 WHEN storage_gb >= 128 THEN 2 ELSE 1 END) +
            (CASE WHEN battery_mah >= 5000 THEN 3 WHEN battery_mah >= 4000 THEN 2 ELSE 1 END) +
            (CASE WHEN refresh_rate_hz >= 120 THEN 3 WHEN refresh_rate_hz >= 90 THEN 2 ELSE 1 END)
        ) AS performance_score
    FROM
        smartphones
    ORDER BY
        performance_score DESC, release_date DESC
    LIMIT 20; -- Changed to 20 to provide a richer list, you can change this back to 5 if you prefer
    ";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("SQL prepare statement failed: " . $conn->error);
    }

    // 3. Execute the query
    $stmt->execute();
    $result = $stmt->get_result();
    $phones = [];

    // 4. Fetch and format the results into the nested structure the JS expects
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $phones[] = [
                'phone_id' => $row['phone_id'],
                'brand_name' => $row['brand_name'],
                'model_name' => $row['model_name'],
                'price' => $row['price'],
                'release_date' => $row['release_date'],
                'specs' => [
                    'processor' => $row['processor'],
                    'ram' => $row['ram_gb'],
                    'screen_size' => $row['screen_size_in'],
                    'display_type' => $row['display_type']
                    // Note: Other fields from the prototype aren't strictly needed by the card,
                    // but they could be added here if you expand the card view.
                ],
                'links' => [
                    'image' => $row['image_url']
                ]
            ];
        }
    }

    // 5. Send the JSON response
    echo json_encode($phones);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'A server error occurred.', 'error_detail' => $e->getMessage()]);
} finally {
    // 6. Always close connections
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>