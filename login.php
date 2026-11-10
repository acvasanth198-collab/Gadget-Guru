<?php
// --- 1. START SESSION ---
// This must be the very first line to access session variables.
session_start();

// --- 2. REDIRECT IF ALREADY LOGGED IN ---
// Check if the user's session ID exists. If it does, they are logged in.
if (isset($_SESSION['uid'])) {
    // Immediately redirect them to the homepage.
    header("Location: shome.php");
    // Stop the script from running any further.
    exit();
}

// --- 3. CACHE CONTROL HEADERS ---
// These headers tell the browser not to store a copy of this page.
// This prevents a user from clicking the "back" button after logging out
// and seeing a cached version of a logged-in page.
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Gadget Guru</title>
    
    <!-- All CSS and Fonts are the same -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* This CSS is exactly the same as the previous version. */
        :root { --primary-color: #e74c3c; --success-color: #27ae60; --bg-color: #121212; --card-bg: #1e1e1e; --text-primary: #ffffff; --text-secondary: #a0a0a0; --border-color: #333333; --font-family-headings: 'Roboto', sans-serif; --font-family-body: 'Poppins', sans-serif; --transition: all 0.3s ease-in-out; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font-family-body); background-color: var(--bg-color); color: var(--text-primary); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 40px 20px; }
        .login-container { width: 100%; max-width: 450px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4); padding: 40px; text-align: center; opacity: 0; transform: translateY(20px); animation: fadeInUp 0.6s 0.2s ease-out forwards; }
        @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
        .login-header .icon { font-size: 3rem; color: var(--primary-color); margin-bottom: 20px; }
        .login-header h1 { font-family: var(--font-family-headings); color: var(--text-primary); font-size: 2rem; font-weight: 700; margin-bottom: 8px; }
        .login-header p { color: var(--text-secondary); font-size: 1rem; margin-bottom: 30px; }
        .login-form { display: flex; flex-direction: column; gap: 18px; }
        .form-group { text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 0.9rem; font-weight: 500; color: var(--text-secondary); }
        .form-group input { width: 100%; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 1rem; font-family: var(--font-family-body); background-color: #2a2a2a; color: var(--text-primary); transition: border-color 0.3s, box-shadow 0.3s; }
        .form-group input::placeholder { color: #6b7280; }
        .form-group input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2); }
        .password-wrapper { position: relative; display: flex; align-items: center; }
        input[type="password"] { padding-right: 45px; }
        .toggle-password { position: absolute; right: 15px; cursor: pointer; color: var(--text-secondary); background: transparent; border: none; }
        .toggle-password svg { width: 20px; height: 20px; }
        .submit-btn { background-color: var(--primary-color); color: var(--white); border: none; padding: 14px; border-radius: 50px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 10px; transition: var(--transition); }
        .submit-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(231, 76, 60, 0.25); }
        .message { padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 5px; display: none; text-align: left; }
        .error { color: #f87171; background-color: #4c1d1d; border: 1px solid #7f1d1d; }
        .success { color: #4ade80; background-color: #164e2a; border: 1px solid #166534; }
        .links { margin-top: 25px; font-size: 14px; }
        .links p { margin-bottom: 12px; color: var(--text-secondary); }
        .links a { color: var(--primary-color); text-decoration: none; font-weight: 500; transition: color 0.3s; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <div class="icon"><i class="fas fa-sign-in-alt"></i></div>
            <h1>Welcome Back!</h1>
            <p>Login to your Gadget Guru account.</p>
        </div>
        <form class="login-form" id="loginForm" method="POST">
            <div id="messageBox" class="message"></div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="off">
                    <span class="toggle-password" data-target="password"></span>
                </div>
            </div>
            <button type="submit" class="submit-btn" id="submitBtn">Login</button>
        </form>
        <div class="links">
            <p>Don't have an account? <a href="signup.html">Sign Up</a></p>
            <p style="margin-top: 20px;"><a href="forgot-password.php">Forgot Password</a></p>
            <p style="margin-top: 20px;"><a href="landing.html">Back to Home</a></p>
        </div>
    </div>

    <!-- The JavaScript part is exactly the same -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                let messageBox = $('#messageBox');
                messageBox.hide().removeClass('success error');
                $.ajax({
                    url: 'login-process.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            messageBox.text(response.message).addClass('success').show();
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1500);
                        } else {
                            messageBox.text(response.message).addClass('error').show();
                        }
                    },
                    error: function() {
                        messageBox.text('An unknown server error occurred.').addClass('error').show();
                    }
                });
            });
            const eyeIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>`;
            const eyeSlashIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L6.228 6.228" /></svg>`;
            $('.toggle-password').each(function() {
                $(this).html(eyeIcon);
                $(this).on('click', function() {
                    let targetId = $(this).data('target');
                    let targetInput = $('#' + targetId);
                    let type = targetInput.attr('type') === 'password' ? 'text' : 'password';
                    targetInput.attr('type', type);
                    $(this).html(type === 'password' ? eyeIcon : eyeSlashIcon);
                });
            });
        });
    </script>

</body>
</html>