<?php
// Include your database connection file
require 'config.php';

// Set the header to indicate the response is JSON
header('Content-Type: application/json');

// The SQL query to get every column from every row
$sql = "SELECT * FROM smartphones";

$result = $conn->query($sql);

$phones = [];
if ($result && $result->num_rows > 0) {
    // Fetch all rows into an associative array
    while($row = $result->fetch_assoc()) {
        $phones[] = $row;
    }
}

// Close the database connection
$conn->close();

// Encode the full array into JSON and output it
echo json_encode($phones);
?>