<?php
// --- 1. SETUP & SECURITY ---

// Start the session to access the logged-in user's data
session_start();

// Include the central database connection file
include 'config.php';

// Set the response type to JSON for the AJAX call
header('Content-Type: application/json');

// Security Check: Ensure the user is a logged-in admin.
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    // If not an admin, send an "Unauthorized" error and stop the script.
    http_response_code(401); 
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit();
}


// --- 2. FETCH BRAND DATA ---

// This query counts the number of phones for each brand.
// MODIFIED: The table name is now 'smartphones'.
$sql = "SELECT brand_name, COUNT(*) as phone_count 
        FROM smartphones 
        GROUP BY brand_name 
        ORDER BY phone_count DESC";

$result = $conn->query($sql);


// --- 3. PROCESS RESULTS FOR CHART.JS ---

// Initialize empty arrays to hold the chart labels and data
$labels = [];
$data = [];

// Check if the query was successful and returned any rows
if ($result && $result->num_rows > 0) {
    // Loop through each row of the result
    while ($row = $result->fetch_assoc()) {
        // Add the brand name to the labels array
        $labels[] = $row['brand_name'];
        // Add the count to the data array
        $data[] = (int)$row['phone_count'];
    }
}


// --- 4. SEND RESPONSE & CLEANUP ---

// Close the database connection
$conn->close();

// Output the final JSON object in the format that Chart.js expects
echo json_encode(['labels' => $labels, 'data' => $data]);
?>