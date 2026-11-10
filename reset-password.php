<?php
session_start();
// User must have an email in session AND have verified their OTP
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    // If not, send them back to the beginning
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
        
        /* --- ADDED CSS FOR PASSWORD ICON --- */
        .password-wrapper { position: relative; display: flex; align-items: center; }
        .password-wrapper input { padding-right: 45px; } /* Ensures text doesn't go under the icon */
        .toggle-password { position: absolute; right: 15px; cursor: pointer; color: var(--text-secondary); background: transparent; border: none; }
        .toggle-password svg { width: 20px; height: 20px; }
        /* --- END OF ADDED CSS --- */

        .submit-btn { background-color: var(--primary-color); color: #fff; border: none; padding: 14px; border-radius: 50px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 10px; transition: var(--transition); }
        .submit-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(231, 76, 60, 0.25); }
        .cancel-btn { background-color: transparent; color: var(--text-secondary); border: 1px solid var(--border-color); padding: 14px; border-radius: 50px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 10px; transition: var(--transition); text-decoration: none; display: block; }
        .cancel-btn:hover { background-color: #2a2a2a; color: #fff; }
        .message { padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 15px; display: none; text-align: left; }
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
            
            <!-- MODIFIED HTML FOR NEW PASSWORD -->
            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="new_password" name="new_password" required>
                    <span class="toggle-password" data-target="new_password"></span>
                </div>
            </div>

            <!-- MODIFIED HTML FOR CONFIRM PASSWORD -->
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <span class="toggle-password" data-target="confirm_password"></span>
                </div>
            </div>

            <button type="submit" class="submit-btn">Update Password</button>
            <a href="landing.html" class="cancel-btn">Cancel</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Your existing AJAX form submission logic
            $('#resetPasswordForm').on('submit', function(e) {
                e.preventDefault();
                let messageBox = $('#messageBox');
                messageBox.hide().removeClass('success error');

                $.ajax({
                    url: 'reset-process.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            messageBox.html(response.message).removeClass('error').addClass('success').show();
                            setTimeout(function() {
                                window.location.href = 'login.php';
                            }, 2000);
                        } else {
                            messageBox.html(response.message).addClass('error').show();
                        }
                    },
                    error: function() {
                        messageBox.text('An unknown server error occurred.').addClass('error').show();
                    }
                });
            });

            // --- ADDED JAVASCRIPT FOR PASSWORD TOGGLE ---
            const eyeIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>`;
            const eyeSlashIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L6.228 6.228" /></svg>`;
            
            // This function finds every ".toggle-password" span and makes it work
            $('.toggle-password').each(function() {
                $(this).html(eyeIcon); // Set the initial icon
                $(this).on('click', function() {
                    let targetId = $(this).data('target');
                    let targetInput = $('#' + targetId);
                    let type = targetInput.attr('type') === 'password' ? 'text' : 'password';
                    targetInput.attr('type', type);
                    // Change the icon based on the new type
                    $(this).html(type === 'password' ? eyeIcon : eyeSlashIcon);
                });
            });
            // --- END OF ADDED JAVASCRIPT ---
        });
    </script>

</body>
</html>