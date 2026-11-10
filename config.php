<?php
/**
 * Database Configuration and Connection (MySQLi)
 */
// --- Database Credentials ---
$host    = 'localhost'; // Database host
$db_user = 'root';      // Database username
$db_pass = '';          // Database password
$db_name = 'gguru';     // Your database name

// --- Create the Database Connection ---
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// --- Check for Connection Errors ---
// If the connection fails, stop the script and display a detailed error message.
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// --- Set the Character Set (Good Practice) ---
// Ensures that data is stored and retrieved with the correct encoding.
$conn->set_charset("utf8mb4");

?>