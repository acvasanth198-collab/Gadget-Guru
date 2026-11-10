<?php
// --- 1. SETUP ---
session_start();
include 'config.php';
header('Content-Type: application/json');

// --- 2. INPUT VALIDATION ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}
if (empty($_POST['email']) || empty($_POST['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
    exit();
}

// --- 3. DATA PREPARATION ---
$email = trim($_POST['email']);
$password = $_POST['password'];

// --- 4. DATABASE QUERY ---
$stmt = $conn->prepare("SELECT uid, username, password, role, status, is_verified FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// --- 5. LOGIC CHECKS ---
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        if ($user['is_verified'] == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Please verify your email address before logging in.']);
            exit();
        }
        if ($user['status'] == 1) {
            echo json_encode(['status' => 'error', 'message' => 'Your account has been suspended. Please contact support.']);
            exit();
        }

        // --- ALL CHECKS PASSED: LOGIN SUCCESS ---
        $_SESSION['uid'] = $user['uid'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Setting the role in session is good practice

        // Determine the correct redirect URL based on role
        if ($user['role'] === 'admin') {
            // THIS IS THE ONLY LINE THAT CHANGED
            $redirect_url = 'admin-d.php'; 
        } else {
            $redirect_url = 'shome.php';
        }
        
        // Send a success response with the redirect URL
        echo json_encode([
            'status' => 'success', 
            'message' => 'Login successful! Redirecting...',
            'redirect' => $redirect_url
        ]);
        
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
}

// --- 6. CLEANUP ---
$stmt->close();
$conn->close();
?>