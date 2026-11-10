<?php
// PHP logic to handle sessions and form errors
$activeView = $_SESSION['active_sidebar_view'] ?? 'sidebar-main-nav';
$sidebarOpen = isset($_SESSION['form_errors']);
$formErrors = $_SESSION['form_errors'] ?? [];
$oldInput = $_SESSION['old_input'] ?? [];

$toastMessage = $_SESSION['error_message'] ?? $_SESSION['success_message'] ?? '';
$toastType = isset($_SESSION['error_message']) ? 'error' : (isset($_SESSION['success_message']) ? 'success' : '');

unset($_SESSION['form_errors'], $_SESSION['old_input'], $_SESSION['active_sidebar_view'], $_SESSION['error_message'], $_SESSION['success_message']);
?>

<!-- ***** NEW PROFILE SIDEBAR STYLING IS HERE ***** -->
<style>
    :root {
        /* Re-declaring variables for component isolation */
        --primary-color: #e74c3c;
        --card-bg: #1e1e1e;
        --text-primary: #ffffff;
        --text-secondary: #a0a0a0;
        --border-color: #333333;
    }
    .profile-sidebar {
        position: fixed; top: 0; right: 0;
        width: 320px;
        height: 100%;
        background: rgba(20, 20, 20, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-left: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--text-primary);
        box-shadow: -10px 0 30px rgba(0,0,0,0.5);
        
        transform: translateX(100%);
        transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        z-index: 1100;
        
        display: flex;
        flex-direction: column;
    }
    .profile-sidebar.active { transform: translateX(0); }

    .sidebar-header {
        padding: 2rem;
        text-align: center;
        border-bottom: 1px solid var(--border-color);
    }
    .sidebar-header .user-avatar-large {
        font-size: 60px;
        margin-bottom: 1rem;
        color: var(--primary-color);
    }
    .sidebar-header h4 { font-size: 1.4rem; font-weight: 600; }
    .sidebar-header p { font-size: 0.9rem; color: var(--text-secondary); }

    .sidebar-content-wrapper { overflow: hidden; position: relative; flex-grow: 1; }
    .sidebar-view {
        position: absolute;
        width: 100%;
        height: 100%;
        transform: translateX(100%);
        transition: transform 0.3s ease-in-out;
        padding: 1.5rem;
    }
    .sidebar-view.active { transform: translateX(0); }

    .sidebar-navigation ul { list-style: none; padding: 0; margin: 0; }
    .sidebar-nav-item {
        display: block;
        padding: 1rem 1.5rem;
        text-decoration: none;
        color: var(--text-secondary);
        font-weight: 500;
        border-radius: 8px;
        transition: 0.3s ease;
        margin-bottom: 0.5rem;
    }
    .sidebar-nav-item i { margin-right: 15px; width: 20px; }
    .sidebar-nav-item:hover { background: rgba(255,255,255,0.05); color: #fff; }

    .back-button { margin-bottom: 1rem; color: #fff; }
    
    .edit-profile-form h4 { font-size: 1.2rem; margin-bottom: 1.5rem; }
    .form-group { margin-bottom: 1.5rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; color: var(--text-secondary); font-size: 0.9rem; }
    .form-group input { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); background-color: rgba(0,0,0,0.3); color: #fff; }
    .form-error-message { color: var(--primary-color); font-size: 0.8rem; margin-top: 0.5rem; }
    
    .password-wrapper { position: relative; }
    .toggle-password { position: absolute; top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer; color: var(--text-secondary); }

    .sidebar-footer { padding: 1.5rem; margin-top: auto; border-top: 1px solid var(--border-color); }
    .sidebar-btn { width: 100%; padding: 14px; border: none; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .sidebar-btn.btn-primary { background-color: var(--primary-color); color: #fff; }
    .sidebar-btn.btn-primary:hover { opacity: 0.8; }
    .sidebar-btn.btn-danger { background-color: var(--primary-color); color: #fff; }
    .sidebar-btn.btn-danger:hover { background: #d32f2f; }
    .sidebar-btn.btn-login { background-color: #38bdf8; color: #fff; } /* Light blue for login */
    .sidebar-btn.btn-login:hover { background: #0ea5e9; }
</style>

<div class="profile-sidebar <?php echo $sidebarOpen ? 'active' : ''; ?>" id="rightSidebar">
    <div class="sidebar-header">
        <div class="user-avatar-large"><i class="fas fa-user-circle"></i></div>
        <h4><?php echo htmlspecialchars($username); ?></h4>
        <p><?php echo htmlspecialchars($email); ?></p>
    </div>

    <div class="sidebar-content-wrapper">
        <div id="sidebar-main-nav" class="sidebar-view <?php echo ($activeView === 'sidebar-main-nav') ? 'active' : ''; ?>">
            <div class="sidebar-navigation">
                <ul>
                    <li><a href="w.php" class="sidebar-nav-item"><i class="fas fa-heart"></i> My Wishlist</a></li>
                    <li><a href="#" class="sidebar-nav-item" data-view="sidebar-settings-view"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>
        </div>

        <div id="sidebar-settings-view" class="sidebar-view <?php echo ($activeView === 'sidebar-settings-view') ? 'active' : ''; ?>">
            <div class="sidebar-navigation">
                <a href="#" class="sidebar-nav-item back-button" data-view="sidebar-main-nav"><i class="fas fa-arrow-left"></i> Back</a>
                <ul>
                    <li><a href="#" class="sidebar-nav-item" data-view="edit-username-view"><i class="fas fa-id-card"></i> Edit Username</a></li>
                    <li><a href="#" class="sidebar-nav-item" data-view="change-password-view"><i class="fas fa-key"></i> Change Password</a></li>
                </ul>
            </div>
        </div>

        <div id="edit-username-view" class="sidebar-view <?php echo ($activeView === 'edit-username-view') ? 'active' : ''; ?>">
     <a href="#" class="sidebar-nav-item back-button" data-view="sidebar-settings-view"><i class="fas fa-arrow-left"></i> Back to Settings</a>
    <div class="edit-profile-form">
        <h4>Edit Username</h4>
        <!-- MODIFIED FORM: Removed action/method, added id -->
        <form id="ajaxUsernameForm">
            <div class="form-group">
                <label for="new_username_sidebar">New Username</label>
                <input type="text" id="new_username_sidebar" name="new_username" value="<?php echo htmlspecialchars($oldInput['new_username'] ?? $username ?? ''); ?>" required>
                <!-- This div will be used by JS to show errors -->
                <div class="form-error-message" id="username-error"></div>
            </div>
            
            <div class="form-group">
                <label for="password_confirm_sidebar">Confirm with Current Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password_confirm_sidebar" name="password" required>
                    <i class="fas fa-eye-slash toggle-password"></i>
                </div>
                 <!-- This div will be used by JS to show errors -->
                <div class="form-error-message" id="password-error"></div>
            </div>

            <button type="submit" class="sidebar-btn btn-primary">Save Username</button>
        </form>
    </div>
</div>
        

        <div id="change-password-view" class="sidebar-view <?php echo ($activeView === 'change-password-view') ? 'active' : ''; ?>">
    <a href="#" class="sidebar-nav-item back-button" data-view="sidebar-settings-view"><i class="fas fa-arrow-left"></i> Back to Settings</a>
    <div class="edit-profile-form">
        <h4>Change Password</h4>
        <!-- MODIFIED FORM: Removed action/method, added id -->
        <form id="ajaxPasswordForm">
            <div class="form-group">
                <label for="current_password_sidebar">Current Password</label>
                <div class="password-wrapper">
                    <input type="password" id="current_password_sidebar" name="current_password" required>
                    <i class="fas fa-eye-slash toggle-password"></i>
                </div>
                <div class="form-error-message" id="current-password-error"></div>
            </div>
            <div class="form-group">
                <label for="new_password_sidebar">New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="new_password_sidebar" name="new_password" required>
                    <i class="fas fa-eye-slash toggle-password"></i>
                </div>
                <!-- This div will be used by JS to show validation errors -->
                <div class="form-error-message" id="new-password-error"></div>
            </div>
            <button type="submit" class="sidebar-btn btn-primary">Change Password</button>
        </form>
    </div>
</div>
    </div> 
    
    <div class="sidebar-footer">
        <?php if ($role === "guest"): ?>
            <button type="button" class="sidebar-btn btn-login" onclick="location.href='login.php'"><i class="fas fa-sign-in-alt"></i> Login</button>
        <?php else: ?>
            <form action="logout.php" method="POST" style="margin: 0;"><button type="submit" class="sidebar-btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</button></form>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // JavaScript logic for opening/closing the sidebar and view switching remains the same
    const userSection = document.querySelector('.user-section');
    const rightSidebar = document.getElementById('rightSidebar');
    if (userSection && rightSidebar) {
        userSection.addEventListener('click', function(event) { event.stopPropagation(); rightSidebar.classList.add('active'); });
        window.addEventListener('click', function(event) { if (rightSidebar.classList.contains('active') && !rightSidebar.contains(event.target) && event.target !== userSection && !userSection.contains(event.target)) { rightSidebar.classList.remove('active'); }});
    }

    const sidebarButtons = document.querySelectorAll('[data-view]');
    sidebarButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const targetViewId = this.getAttribute('data-view');
            const parentWrapper = this.closest('.sidebar-content-wrapper');
            if (parentWrapper) {
                parentWrapper.querySelectorAll('.sidebar-view').forEach(view => view.classList.remove('active'));
                const targetView = document.getElementById(targetViewId);
                if(targetView) targetView.classList.add('active');
            }
        });
    });

    // Password toggle remains the same...
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', function () { const passwordInput = this.previousElementSibling; const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password'; passwordInput.setAttribute('type', type); this.classList.toggle('fa-eye-slash'); this.classList.toggle('fa-eye'); });
    });
    // Toast logic is omitted as it's part of the main page script.

    const usernameForm = document.getElementById('ajaxUsernameForm');
    if (usernameForm) {
        usernameForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent the default page reload

            // Get form data
            const formData = new FormData(usernameForm);
            const button = usernameForm.querySelector('button[type="submit"]');
            button.disabled = true; // Disable button to prevent multiple clicks
            button.textContent = 'Saving...';
            
            // Clear previous errors
            document.getElementById('username-error').textContent = '';
            document.getElementById('password-error').textContent = '';

            // Send data to your existing change_username.php script
            fetch('change_username.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // The 'showToast' function must be available on the parent page (e.g., shome.php)
                if (typeof showToast === 'function') {
                    showToast(data.message, data.status);
                }

                if (data.status === 'success') {
                    // Update the username in the sidebar header immediately
                    const sidebarHeaderName = document.querySelector('.sidebar-header h4');
                    if(sidebarHeaderName) {
                        sidebarHeaderName.textContent = data.new_username;
                    }
                    // Optionally, you might want to switch back to the main settings view
                    document.querySelector('[data-view="sidebar-settings-view"]').click();
                } else {
                    // Display specific errors under the correct fields if available
                    if (data.message.toLowerCase().includes('password')) {
                        document.getElementById('password-error').textContent = data.message;
                    } else {
                        document.getElementById('username-error').textContent = data.message;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof showToast === 'function') {
                    showToast('A network error occurred. Please try again.', 'error');
                }
            })
            .finally(() => {
                // Re-enable the button
                button.disabled = false;
                button.textContent = 'Save Username';
            });
        });
    }

    // Place this inside your DOMContentLoaded listener, after the username form logic

// --- NEW JAVASCRIPT LOGIC FOR PASSWORD FORM ---
const passwordForm = document.getElementById('ajaxPasswordForm');
if (passwordForm) {
    passwordForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent the default page reload

        const formData = new FormData(passwordForm);
        const button = passwordForm.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = 'Saving...';

        // Clear previous errors
        document.getElementById('current-password-error').textContent = '';
        document.getElementById('new-password-error').textContent = '';

        // Send data to your existing change_password.php script
        fetch('change_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (typeof showToast === 'function') {
                showToast(data.message, data.status);
            }

            if (data.status === 'success') {
                passwordForm.reset(); // Clear the form fields
                // Go back to the settings menu
                document.querySelector('#change-password-view .back-button').click();
            } else {
                // Display specific errors
                if (data.message.toLowerCase().includes('current password')) {
                    document.getElementById('current-password-error').textContent = data.message;
                } else {
                    document.getElementById('new-password-error').textContent = data.message;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showToast === 'function') {
                showToast('A network error occurred. Please try again.', 'error');
            }
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = 'Change Password';
        });
    });
}

});
</script>