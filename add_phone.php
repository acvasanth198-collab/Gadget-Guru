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
// This establishes the $conn variable by connecting to the database.
require_once 'config.php';

// --- Main Logic ---
try {
    // 3. Get the JSON data sent from the JavaScript fetch() call in admin-d.php.
    $data = json_decode(file_get_contents("php://input"), true);

    // Basic validation to ensure data was received.
    if (!$data) {
        http_response_code(400); // Bad Request
        throw new Exception("No data received or invalid JSON format.");
    }

    // 4. Prepare a single, comprehensive INSERT statement for the 'smartphones' table.
    // The column names must exactly match your phpMyAdmin screenshot.
    $sql = "INSERT INTO smartphones (
                brand_name, model_name, release_date, price, image_url, youtube_link,
                height_mm, width_mm, thickness_mm, weight_g,
                sim_slot, sim_type, network_support,
                display_type, screen_size_in, resolution, refresh_rate_hz, aspect_ratio,
                processor, chipset, ram_gb, storage_gb, battery_mah, fast_charging_w,
                os, os_update, ui, rear_camera, front_camera, fingerprint_sensor
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";

    $stmt = $conn->prepare($sql);

    // If the prepare statement fails, it indicates an SQL error (e.g., wrong column name).
    if ($stmt === false) {
        throw new Exception("SQL prepare statement failed: " . $conn->error);
    }

    // 5. Bind the values from the decoded JSON to the prepared statement.
    // The type string ('s', 'd', 'i') and the order of variables are critical.
    $stmt->bind_param(
        "sssdssddddssssdsissssiisssssss",
        $data['brand_name'],
        $data['model_name'],
        $data['release_date'],
        $data['price'],
        $data['links']['image'],        // Maps to the `image_url` column
        $data['links']['youtube'],      // Maps to the `youtube_link` column
        $data['body']['height'],        // Maps to the `height_mm` column
        $data['body']['width'],         // Maps to the `width_mm` column
        $data['body']['thickness'],     // Maps to the `thickness_mm` column
        $data['body']['weight'],        // Maps to the `weight_g` column
        $data['body']['sim_slot'],
        $data['body']['sim_type'],
        $data['body']['network_support'],
        $data['specs']['display_type'],
        $data['specs']['screen_size'],  // Maps to the `screen_size_in` column
        $data['specs']['resolution'],
        $data['specs']['refresh_rate'], // Maps to the `refresh_rate_hz` column
        $data['specs']['aspect_ratio'],
        $data['specs']['processor'],    // Note: The screenshot doesn't show a `processor` column. If you don't have it, remove it from the SQL and bind_param.
        $data['specs']['chipset'],
        $data['specs']['ram'],           // Maps to the `ram_gb` column
        $data['specs']['storage'],      // Maps to the `storage_gb` column
        $data['specs']['battery_capacity'], // Maps to the `battery_mah` column
        $data['specs']['fast_charging'], // Maps to the `fast_charging_w` column
        $data['features']['os'],
        $data['features']['os_update'],
        $data['features']['ui'],
        $data['features']['rear_camera'],
        $data['features']['front_camera'],
        $data['features']['fingerprint_sensor']
    );

    // 6. Execute the query and send a response.
    if ($stmt->execute()) {
        // Success
        echo json_encode(['status' => 'success', 'message' => 'Phone added successfully!']);
    } else {
        // Failure
        throw new Exception("Execute failed: " . $stmt->error);
    }

} catch (Exception $e) {
    // Catch any errors and send a detailed error message back to the client.
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Failed to add phone due to a server error.', 'error_detail' => $e->getMessage()]);

} finally {
    // 7. Always close the statement and connection to free up server resources.
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>