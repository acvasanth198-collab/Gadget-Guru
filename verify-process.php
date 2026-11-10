<?php
session_start();
header('Content-Type: application/json');

// --- 1. VALIDATE SESSION AND INPUT ---
if (!isset($_SESSION['reset_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired. Please start over.']);
    exit();
}

if (empty($_POST['otp']) || !is_numeric($_POST['otp']) || strlen($_POST['otp']) != 6) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid 6-digit code.']);
    exit();
}

require_once 'config.php';
$email = $_SESSION['reset_email'];
$otp = $_POST['otp'];

// --- 2. CHECK OTP AGAINST DATABASE ---
$stmt = $conn->prepare("SELECT otp FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again.']);
    exit();
}

$user = $result->fetch_assoc();
$db_otp = $user['otp'];

if ($db_otp == $otp) {
    // --- 3. OTP IS CORRECT ---
    // Set a session flag to confirm OTP verification
    $_SESSION['otp_verified'] = true;
    
    // Clear the OTP from the database for security
    $update_stmt = $conn->prepare("UPDATE users SET otp = NULL WHERE email = ?");
    $update_stmt->bind_param("s", $email);
    $update_stmt->execute();
    $update_stmt->close();
    
    echo json_encode(['status' => 'success', 'message' => 'Verification successful.']);
} else {
    // --- 4. OTP IS INCORRECT ---
    echo json_encode(['status' => 'error', 'message' => 'Invalid verification code.']);
}

$stmt->close();
$conn->close();
?>```

---

### File 3: `reset-password.php`

The final step. The user sets their new password here.

```php
<?php
session_start();
// User must have an email in session AND have verified their OTP
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: forgot-password.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | Gadget Guru</title>
    <!-- Use the same CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reusing the exact same CSS for consistent theme */
        :root { --primary-color: #e74c3c; --success-color: #27ae60; --bg-color: #121212; --card-bg: #1e1e1e; --text-primary: #ffffff; --text-secondary: #a0a0a0; --border-color: #333333; --font-family-headings: 'Roboto', sans-serif; --font-family-body: 'Poppins', sans-serif; --transition: all 0.3s ease-in-out; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font-family-body); background-color: var(--bg-color); color: var(--text-primary); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 40px 20px; }
        .form-container { width: 100%; max-width: 450px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4); padding: 40px; text-align: center; opacity: 0; transform: translateY(20px); animation: fadeInUp 0.6s 0.2s ease-out forwards; }
        @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
        .form-header .icon { font-size: 3rem; color: var(--primary-color); margin-bottom: 20px; }
        .form-header h1 { font-family: var(--font-family-headings); color: var(--text-primary); font-size: 2rem; font-weight: 700; margin-bottom: 8px; }
        .form-header p { color: var(--text-secondary); font-size: 1rem; margin-bottom: 30px; }
        .form-content { display: flex; flex-direction: column; gap: 18px; }
        .form-group { text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 0.9rem; font-weight: 500; color: var(--text-secondary); }
        .form-group input { width: 100%; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 1rem; font-family: var(--font-family-body); background-color: #2a2a2a; color: var(--text-primary); transition: border-color 0.3s, box-shadow 0.3s; }
        .form-group input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2); }
        .submit-btn { background-color: var(--primary-color); color: #fff; border: none; padding: 14px; border-radius: 50px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 10px; transition: var(--transition); }
        .submit-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(231, 76, 60, 0.25); }
        .cancel-btn { background-color: transparent; color: var(--text-secondary); border: 1px solid var(--border-color); padding: 14px; border-radius: 50px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 10px; transition: var(--transition); text-decoration: none; display: block; }
        .cancel-btn:hover { background-color: #2a2a2a; color: #fff; }
        .message { padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 15px; display: none; text-align: center; }
        .error { color: #f87171; background-color: #4c1d1d; border: 1px solid #7f1d1d; }
        .success { color: #4ade80; background-color: #164e2a; border: 1px solid #166534; }
    </style>
</head>
<body>

    <div class="form-container">
        <div class="form-header">
            <div class="icon"><i class="fas fa-lock"></i></div>
            <h1>Set New Password</h1>
            <p>Create a strong new password for your account.</p>
        </div>
        <form class="form-content" id="resetPasswordForm" method="POST">
            <div id="messageBox" class="message"></div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="submit-btn">Update Password</button>
            <a href="landing.html" class="cancel-btn">Cancel</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#resetPasswordForm').on('submit', function(e) {
                e.preventDefault();
                let messageBox = $('#messageBox');
                messageBox.hide().removeClass('success error');

                $.ajax({
                    url: 'reset-process.php', // Backend script to update password
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            messageBox.text(response.message).removeClass('error').addClass('success').show();
                            // Redirect to login page after 2 seconds
                            setTimeout(function() {
                                window.location.href = 'login.php';
                            }, 2000);
                        } else {
                            messageBox.text(response.message).addClass('error').show();
                        }
                    },
                    error: function() {
                        messageBox.text('An unknown server error occurred.').addClass('error').show();
                    }
                });
            });
        });
    </script>

</body>
</html>