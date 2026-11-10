<?php
header('Content-Type: application/json');
require_once 'config.php';

// --- Helper function to fetch a single phone from the new 'smartphones' table ---
function getPhoneDetails($conn, $id = 0, $brand = '', $model = '') {
    // The query is now much simpler
    $sql = "SELECT * FROM smartphones";
    
    if ($id > 0) {
        $sql .= " WHERE phone_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
    } elseif (!empty($brand) && !empty($model)) {
        $sql .= " WHERE brand_name = ? AND model_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $brand, $model);
    } else {
        return null; // Not enough info to search
    }

    if (!$stmt) return null;

    $stmt->execute();
    $result = $stmt->get_result();
    $phoneData = $result->fetch_assoc();
    $stmt->close();

    // The data is already in the correct "flat" format, so no translation is needed.
    return $phoneData;
}

// --- Main Script Logic ---
try {
    // Get all possible inputs from the URL
    $id1 = filter_input(INPUT_GET, 'id1', FILTER_VALIDATE_INT);
    $id2 = filter_input(INPUT_GET, 'id2', FILTER_VALIDATE_INT);
    $brand1 = $_GET['brand1'] ?? '';
    $model1 = $_GET['model1'] ?? '';
    $brand2 = $_GET['brand2'] ?? '';
    $model2 = $_GET['model2'] ?? '';

    // Fetch details for both phones
    $phone1_details = getPhoneDetails($conn, $id1, $brand1, $model1);
    $phone2_details = getPhoneDetails($conn, $id2, $brand2, $model2);

    // Check if both phones were successfully found
    if ($phone1_details && $phone2_details) {
        $response = [
            'status' => 'success',
            'data' => [
                'phone1' => $phone1_details,
                'phone2' => $phone2_details
            ]
        ];
        echo json_encode($response);
    } else {
        // If one or both are missing, throw an error
        throw new Exception("Could not find details for one or both of the selected phones.");
    }

} catch (Exception $e) {
    http_response_code(404); // Not Found or other error
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>