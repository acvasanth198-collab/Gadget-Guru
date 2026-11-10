<?php
// Set the content type to JSON for the response
header('Content-Type: application/json');

// 1. Include the database configuration file (Best Practice)
require_once 'config.php';

// 2. Use a try...catch block for robust error handling (Best Practice)
try {
    // 3. Prepare the SQL Query (Identical Logic)
    $sql = "
    SELECT
        *, 
        (
            (CASE WHEN ram_gb >= 12 THEN 4 WHEN ram_gb >= 8 THEN 3 WHEN ram_gb >= 6 THEN 2 ELSE 1 END) +
            (CASE WHEN storage_gb >= 512 THEN 4 WHEN storage_gb >= 256 THEN 3 WHEN storage_gb >= 128 THEN 2 ELSE 1 END) +
            (CASE WHEN battery_mah >= 5000 THEN 3 WHEN battery_mah >= 4000 THEN 2 ELSE 1 END) +
            (CASE WHEN refresh_rate_hz >= 120 THEN 3 WHEN refresh_rate_hz >= 90 THEN 2 ELSE 1 END)
        ) / price AS value_score
    FROM
        smartphones
    WHERE
        price > 0 
    ORDER BY
        value_score DESC
    LIMIT 20
    ";

    // 4. Use Prepared Statements for security and stability (Best Practice)
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("SQL prepare statement failed: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $phones = [];

    // 5. Fetch and translate data (Identical Logic)
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $phones[] = [
                'phone_id' => $row['phone_id'],
                'brand_name' => $row['brand_name'],
                'model_name' => $row['model_name'],
                'price' => $row['price'],
                'specs' => [
                    'processor' => $row['processor'],
                    'ram' => $row['ram_gb'],
                    'storage' => $row['storage_gb'],
                    'screen_size' => $row['screen_size_in']
                ],
                'features' => [
                    'battery' => $row['battery_mah'] . ' mAh'
                ],
                'links' => [
                    'image' => $row['image_url']
                ]
            ];
        }
    }

    // 6. Send the JSON response
    echo json_encode($phones);

} catch (Exception $e) {
    // Graceful error reporting
    http_response_code(500); 
    echo json_encode(['error' => 'A server error occurred.', 'error_detail' => $e->getMessage()]);

} finally {
    // Always close connections
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>