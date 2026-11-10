<?php
// Set the response header to indicate JSON content
header('Content-Type: application/json');

// 1. Include the database configuration file
require_once 'config.php';

try {
    // 2. Prepare the SQL Query for the new 'smartphones' table
    // The core logic of calculating a score from the camera text remains the same.
    $sql = "
    SELECT
        *, -- Select all columns
        -- Extract the first number from text like '108MP...' for sorting
        CAST(SUBSTRING_INDEX(rear_camera, ' ', 1) AS UNSIGNED) AS primary_camera_mp
    FROM
        smartphones
    WHERE
        -- Ensure the camera info is valid and starts with a number
        rear_camera IS NOT NULL AND rear_camera <> '' AND rear_camera REGEXP '^[0-9]'
    ORDER BY
        primary_camera_mp DESC, -- Order by the highest megapixel count first
        release_date DESC      -- Use release date as a tie-breaker
    LIMIT 20; -- Limit to a reasonable number for the page
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
            // This 'translation' step is crucial for frontend compatibility
            $phones[] = [
                'phone_id' => $row['phone_id'],
                'brand_name' => $row['brand_name'],
                'model_name' => $row['model_name'],
                'price' => $row['price'],
                'specs' => [
                    'processor' => $row['processor'],
                    'ram' => $row['ram_gb'], // Map new column name to old key
                    'screen_size' => $row['screen_size_in'] // Map new column name to old key
                ],
                'features' => [
                    'rear_camera' => $row['rear_camera']
                ],
                'links' => [
                    'image' => $row['image_url'] // Map new column name to old key
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