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

    // Validate that we received data and, most importantly, the phone_id.
    if (!$data || !isset($data['phone_id'])) {
        http_response_code(400); // Bad Request
        throw new Exception("Invalid request: Missing phone data or phone_id.");
    }
    
    $phone_id = $data['phone_id'];

    // 4. Prepare a single, comprehensive UPDATE statement for the 'smartphones' table.
    // This query will update all columns for a specific phone entry.
    $sql = "UPDATE smartphones SET
                brand_name = ?, model_name = ?, release_date = ?, price = ?, image_url = ?, youtube_link = ?,
                height_mm = ?, width_mm = ?, thickness_mm = ?, weight_g = ?,
                sim_slot = ?, sim_type = ?, network_support = ?,
                display_type = ?, screen_size_in = ?, resolution = ?, refresh_rate_hz = ?, aspect_ratio = ?,
                processor = ?, chipset = ?, ram_gb = ?, storage_gb = ?, battery_mah = ?, fast_charging_w = ?,
                os = ?, os_update = ?, ui = ?, rear_camera = ?, front_camera = ?, fingerprint_sensor = ?
            WHERE phone_id = ?"; // The WHERE clause is essential to update only one record!

    $stmt = $conn->prepare($sql);

    // If the prepare statement fails, it indicates an SQL error (e.g., wrong column name).
    if ($stmt === false) {
        throw new Exception("SQL prepare statement failed: " . $conn->error);
    }

    // 5. Bind all 31 parameters from the decoded JSON to the prepared statement.
    // The final 'i' in the type string is for the integer phone_id in the WHERE clause.
    $stmt->bind_param(
        "sssdssddddssssdsissssiisssssssi", // 30 'set' columns + 1 'where' column
        $data['brand_name'],
        $data['model_name'],
        $data['release_date'],
        $data['price'],
        $data['links']['image'],
        $data['links']['youtube'],
        $data['body']['height'],
        $data['body']['width'],
        $data['body']['thickness'],
        $data['body']['weight'],
        $data['body']['sim_slot'],
        $data['body']['sim_type'],
        $data['body']['network_support'],
        $data['specs']['display_type'],
        $data['specs']['screen_size'],
        $data['specs']['resolution'],
        $data['specs']['refresh_rate'],
        $data['specs']['aspect_ratio'],
        $data['specs']['processor'],
        $data['specs']['chipset'],
        $data['specs']['ram'],
        $data['specs']['storage'],
        $data['specs']['battery_capacity'],
        $data['specs']['fast_charging'],
        $data['features']['os'],
        $data['features']['os_update'],
        $data['features']['ui'],
        $data['features']['rear_camera'],
        $data['features']['front_camera'],
        $data['features']['fingerprint_sensor'],
        $phone_id // This is the last parameter for the "WHERE phone_id = ?" part
    );

    // 6. Execute the query and send an appropriate response.
    if ($stmt->execute()) {
        // Check if any rows were actually changed by the update.
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Phone updated successfully!']);
        } else {
            // This happens if the user clicks "Save Changes" without altering any data.
            echo json_encode(['status' => 'success', 'message' => 'No changes were detected.']);
        }
    } else {
        // If execution fails, throw an exception with the error details.
        throw new Exception("Execute failed: " . $stmt->error);
    }

} catch (Exception $e) {
    // Catch any errors and send a detailed error message back to the client.
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Failed to update phone.', 'error_detail' => $e->getMessage()]);

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