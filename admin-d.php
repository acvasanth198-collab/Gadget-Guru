<?php
session_start();

// 1. Check if the user is actually logged in.
if (!isset($_SESSION['uid'])) {
    // If not logged in, redirect them to the landing page
    header("Location: landing.html");
    exit();
}

// ===== Get the admin's username from the session =====
$admin_username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';

// 2. Send headers to prevent browser caching.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // A date in the past
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gadget Guru - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* --- THEME: Synced with landing.html --- */
        :root {
            --primary-color: #e74c3c;
            --primary-color-dark: #c0392b;
            --bg-color: #121212;
            --sidebar-bg: #181818;
            --card-bg: #1e1e1e;
            --text-primary: #ffffff;
            --text-secondary: #a0a0a0;
            --border-color: #333333;
            --success-color: #2ecc71;
            --success-color-dark: #27ae60;
            --shadow-md: 0 4px 15px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.5);
            --shadow-glow: 0 0 20px rgba(231, 76, 60, 0.3);
            --transition: all 0.3s ease-in-out;
            /* --- NEW: Glassmorphism Style Variables --- */
            --glass-bg: rgba(30, 30, 30, 0.6);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Poppins', sans-serif;
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- NEW: Staggered row animation --- */
        @keyframes tableRowFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        tbody tr.fade-in {
            animation: tableRowFadeIn 0.4s ease-out forwards;
        }


        .container-fluid {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            color: var(--text-secondary);
            width: 16rem;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            transition: width 0.3s ease;
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-header h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
        }

        .sidebar-nav {
            margin-top: 1rem;
            flex-grow: 1;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.85rem 1.5rem;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 500;
            transition: var(--transition);
            margin: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            border-left: 3px solid transparent;
        }

        .nav-link i {
            width: 1.25rem;
            text-align: center;
            font-size: 1rem;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
        }

        .nav-link.active-tab {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--primary-color);
            border-left: 3px solid var(--primary-color);
            font-weight: 600;
        }

        .sidebar-footer {
            padding: 1rem;
            margin-top: auto;
        }

        .logout-btn {
            width: 100%;
            background-color: var(--border-color);
            color: #fff;
            padding: 0.75rem;
            border-radius: 0.375rem;
            text-align: center;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            transition: var(--transition);
        }

        .logout-btn:hover {
            background-color: var(--primary-color);
        }

        .logout-btn i {
            margin-right: 0.5rem;
        }

        /* Main Content Area */
        .main-content {
            flex-grow: 1;
            overflow-y: auto;
        }

        header.top-bar {
            background: rgba(18, 18, 18, 0.8);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 1px solid var(--border-color);
        }

        header.top-bar h2 {
            font-family: 'Roboto', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-info span {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .admin-info .fa-user-circle {
            font-size: 2.25rem;
            color: var(--text-secondary);
        }

        main {
            padding: 2rem;
        }

        .content-section {
            animation: fadeInUp 0.5s ease-out;
        }

        .content-section.hidden {
            display: none;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-color);
        }

        .card-icon {
            padding: 1rem;
            border-radius: 50%;
            font-size: 1.5rem;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            background: var(--primary-color);
        }

        .card-info h3 {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }

        .card-info p {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        /* Tables */
        .table-container {
            background: var(--card-bg);
            border-radius: 0.75rem;
            overflow: hidden;
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
            transition: var(--transition);
        }

        .table-container:hover {
            box-shadow: var(--shadow-md);
        }

        .table-container-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .table-container-header h3 {
            font-family: 'Roboto', sans-serif;
            font-size: 1.25rem;
            font-weight: 600;
            margin-right: auto;
        }

        .table-container-body {
            padding: 0;
        }

        .table-scroll-wrapper {
            overflow-x: auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        thead {
            background-color: #121212;
        }

        th,
        td {
            padding: 1rem 1.5rem;
            text-align: left;
            font-size: 0.875rem;
            color: var(--text-secondary);
            white-space: nowrap;
            border-bottom: 1px solid var(--border-color);
        }

        td {
            color: var(--text-primary);
        }

        th {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tbody tr {
            transition: background-color 0.2s ease;
            opacity: 0;
            /* For animation */
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background-color: #2a2a2a;
        }

        /* Action Buttons */
        .action-buttons button {
            background: none;
            border: none;
            cursor: pointer;
            margin-left: 0.75rem;
            font-size: 1rem;
            transition: var(--transition);
        }

        .action-buttons button:hover {
            transform: scale(1.2);
        }

        .action-buttons .text-red-500 {
            color: var(--primary-color);
        }

        .action-buttons .text-blue-500 {
            color: #3498db;
        }

        .action-buttons .text-green-500 {
            color: var(--success-color);
        }

        .action-buttons .action-btn {
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.8rem;
            color: #fff;
            transition: var(--transition);
            margin: 0;
        }

        .action-buttons .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .action-buttons .action-btn.action-btn-block {
            background-color: var(--primary-color);
        }

        .action-buttons .action-btn.action-btn-block:hover {
            background-color: var(--primary-color-dark);
        }

        .action-buttons .action-btn.action-btn-unblock {
            background-color: var(--success-color);
        }

        .action-buttons .action-btn.action-btn-unblock:hover {
            background-color: var(--success-color-dark);
        }

        .add-new-btn {
            background-color: var(--primary-color);
            color: #fff;
            padding: 0.7rem 1.4rem;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
        }

        .add-new-btn:hover {
            box-shadow: var(--shadow-glow);
            transform: translateY(-2px);
        }

        .add-new-btn i {
            margin-right: 0.5rem;
        }

        .table-controls input,
        .table-controls select {
            border: 1px solid var(--border-color);
            background-color: var(--bg-color);
            color: var(--text-primary);
            padding: 0.6rem 0.8rem;
            border-radius: 0.375rem;
            transition: var(--transition);
        }

        .table-controls input:focus,
        .table-controls select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2);
        }

        .table-controls input {
            width: 250px;
        }

        /* --- MODIFIED: Modal Styles with Glassmorphism --- */
        .modal {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal.hidden {
            display: none;
        }

        .modal::before {
            /* Backdrop */
            content: '';
            position: absolute;
            inset: 0;
            background-color: rgba(18, 18, 18, 0.7);
            backdrop-filter: blur(8px);
        }

        .modal-content {
            position: relative;
            /* To sit above the backdrop */
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 48rem;
            max-height: 90vh;
            overflow-y: auto;
            animation: fadeInUp 0.4s;
        }

        .modal-content h2 {
            font-family: 'Roboto', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .modal-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form-grid {
            display: grid;
            gap: 1rem;
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .grid-cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .modal-form h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-top: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-form input[type="text"],
        .modal-form input[type="number"],
        .modal-form input[type="date"],
        .modal-form input[type="url"],
        .modal-form input[type="password"],
        .modal-form input[type="email"] {
            border: 1px solid var(--border-color);
            background-color: var(--bg-color);
            color: var(--text-primary);
            padding: 0.6rem 0.8rem;
            border-radius: 0.375rem;
            width: 100%;
            transition: var(--transition);
        }

        .modal-form input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.5rem;
            gap: 0.75rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }

        .modal-btn {
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-cancel {
            background-color: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }

        .btn-cancel:hover {
            background-color: var(--border-color);
            color: var(--text-primary);
        }

        .btn-save {
            background-color: var(--primary-color);
            color: #fff;
        }

        .btn-save:hover {
            background-color: var(--primary-color-dark);
        }

        /* Toast Notification Styles */
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: var(--shadow-lg);
            color: #fff;
            font-weight: 500;
            z-index: 100;
            opacity: 0;
            transform: translateY(20px);
            transition: var(--transition);
            pointer-events: none;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast.success {
            background-color: var(--success-color);
        }

        .toast.error {
            background-color: var(--primary-color);
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .logo-icon {
            font-size: 2.2rem;
            color: var(--primary-color);
            display: inline-block;
            transform: rotate(-10deg);
        }

        .logo-text span {
            color: var(--primary-color);
        }

        /* --- NEW: Professional Profile Layout --- */
        .profile-container {
            display: flex;
            gap: 2rem;
            min-height: 500px;
        }

        .profile-nav {
            flex-shrink: 0;
            width: 220px;
            border-right: 1px solid var(--border-color);
            padding-right: 2rem;
        }

        .profile-option-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            text-align: left;
            padding: 0.8rem 1rem;
            border: none;
            background-color: transparent;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-secondary);
            border-radius: 6px;
            margin-bottom: 0.5rem;
            transition: var(--transition);
        }

        .profile-option-btn i {
            width: 20px;
            font-size: 1rem;
        }

        .profile-option-btn:hover {
            background-color: var(--border-color);
            color: var(--text-primary);
        }

        .profile-option-btn.active {
            background-color: var(--primary-color);
            color: var(--text-primary);
            font-weight: 600;
        }

        .profile-content-pane {
            flex-grow: 1;
        }

        .profile-content {
            display: none;
        }

        .profile-content.active {
            display: block;
            animation: fadeInUp 0.4s ease;
        }

        .password-container {
            position: relative;
            width: 100%;
        }

        .password-container input {
            padding-right: 2.5rem;
        }

        .password-container .toggle-password {
            position: absolute;
            right: 0.8rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-secondary);
            transition: color 0.2s;
        }

        .password-container .toggle-password:hover {
            color: var(--primary-color);
        }

        .profile-form {
            max-width: 500px;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .profile-form h4 {
            font-family: 'Roboto', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .form-footer {
            display: flex;
            justify-content: flex-end;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
            margin-top: 1rem;
        }

        .profile-form input {
            border: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            width: 100%;
            transition: var(--transition);
            font-size: 1rem;
            background-color: var(--bg-color);
            color: var(--text-primary);
        }

        .profile-form input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2);
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <span class="logo-icon"><i class="fas fa-mobile-alt"></i></span>
                    <span class="logo-text"><span>G</span>adget Guru</span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="#" onclick="showSection('dashboard')" class="nav-link active-tab"> <i class="fas fa-chart-pie"></i> <span>Dashboard</span> </a>
                <a href="#" onclick="showSection('users')" class="nav-link"> <i class="fas fa-users"></i> <span>Manage Users</span> </a>
                <a href="#" onclick="showSection('phones')" class="nav-link"> <i class="fas fa-mobile-alt"></i> <span>Manage Phones</span> </a>
                <a href="#" onclick="showSection('profile')" class="nav-link"> <i class="fas fa-user-cog"></i> <span>Profile Settings</span> </a>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn" style="text-decoration: none;"> <i class="fas fa-sign-out-alt"></i>Logout </a>
            </div>
        </div>

        <div class="main-content">
            <header class="top-bar">
                <h2 id="section-title">Dashboard</h2>
                <div class="admin-info">
                    <span id="admin-username">Welcome, <?php echo $admin_username; ?></span>
                    <i class="fas fa-user-circle"></i>
                </div>
            </header>

            <main>
                <section id="dashboard-section" class="content-section">
                    <div class="dashboard-grid">
                        <div class="card">
                            <div class="card-icon users"> <i class="fas fa-users"></i> </div>
                            <div class="card-info">
                                <h3>Total Users</h3>
                                <p id="total-users-count">0</p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-icon phones"> <i class="fas fa-mobile-alt"></i> </div>
                            <div class="card-info">
                                <h3>Total Phones</h3>
                                <p id="total-phones-count">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="table-container">
                        <div class="table-container-header">
                            <h3>Database Overview</h3>
                        </div>
                        <div class="table-container-body" style="padding: 1.5rem; height: 350px; position: relative;"> <canvas id="performanceChart"></canvas> </div>
                    </div>
                    <div class="table-container">
                        <div class="table-container-header">
                            <h3>Phone Brands Distribution</h3>
                        </div>
                        <div class="table-container-body" style="padding: 1.5rem; height: 450px; position: relative;"> <canvas id="brandDistributionChart"></canvas> </div>
                    </div>
                </section>
                <section id="users-section" class="content-section hidden">
                    <div class="table-container">
                        <div class="table-container-header">
                            <h3>User Management</h3>
                        </div>
                        <div class="table-container-body">
                            <div class="table-scroll-wrapper">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="users-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
                <section id="phones-section" class="content-section hidden">
                    <div class="table-container">
                        <div class="table-container-header">
                            <h3>Phone Management</h3>
                            <div class="table-controls">
                                <input type="text" id="phoneSearchInput" placeholder="Search by brand or name...">
                                <select id="phoneSortSelect">
                                    <option value="default">Sort By</option>
                                    <option value="price-lth">Price: Low to High</option>
                                    <option value="price-htl">Price: High to Low</option>
                                    <option value="brand-az">Brand: A-Z</option>
                                    <option value="brand-za">Brand: Z-A</option>
                                    <option value="name-az">Name: A-Z</option>
                                    <option value="name-za">Name: Z-A</option>
                                </select>
                            </div>
                            <!-- REPLACE THE OLD BUTTON WITH THIS BLOCK -->
<!-- REPLACE THE OLD BLOCK WITH THIS CORRECTED BLOCK -->
<div style="display: flex; gap: 0.75rem;">
    <button onclick="openAddPhoneModal()" class="add-new-btn">
        <i class="fas fa-plus"></i>Add New Phone
    </button>
    <!-- Corrected onclick to call handleExport('csv') -->
    <button onclick="handleExport('csv')" class="add-new-btn" style="background-color: #27ae60;">
        <i class="fas fa-file-csv"></i>Export All (CSV)
    </button>
</div>
                        </div>
                        <div class="table-container-body">
                            <div class="table-scroll-wrapper">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Index</th>
                                            <th>Brand</th>
                                            <th>Name</th>
                                            <th>Price</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="phones-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- --- MODIFIED: Profile Section HTML Structure --- -->
                <section id="profile-section" class="content-section hidden">
                    <div class="table-container">
                        <div class="table-container-header">
                            <h3>Admin Profile Settings</h3>
                        </div>
                        <div class="table-container-body" style="padding: 1.5rem 2rem;">
                            <div class="profile-container">
                                <nav class="profile-nav">
                                    <button class="profile-option-btn active" onclick="toggleProfileOption('username', this)">
                                        <i class="fas fa-user"></i> Change Username
                                    </button>
                                    <button class="profile-option-btn" onclick="toggleProfileOption('email', this)">
                                        <i class="fas fa-envelope"></i> Change Email
                                    </button>
                                    <button class="profile-option-btn" onclick="toggleProfileOption('password', this)">
                                        <i class="fas fa-key"></i> Change Password
                                    </button>
                                </nav>
                                <div class="profile-content-pane">
                                    <!-- Username Form -->
                                    <div id="username-content" class="profile-content active">
                                        <form id="changeUsernameForm" class="profile-form">
                                            <h4>Update Your Username</h4>
                                            <div class="form-group">
                                                <label for="new_username">New Username</label>
                                                <input type="text" id="new_username" name="new_username" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="password_confirm_username">Confirm with Current Password</label>
                                                <div class="password-container">
                                                    <input type="password" id="password_confirm_username" name="password_confirm_username" required>
                                                    <span class="toggle-password"><i class="fas fa-eye"></i></span>
                                                </div>
                                            </div>
                                            <div class="form-footer">
                                                <button type="submit" class="modal-btn btn-save">Update Username</button>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- Email Form -->
                                    <div id="email-content" class="profile-content">
                                        <form id="changeEmailForm" class="profile-form">
                                            <h4>Update Your Email Address</h4>
                                            <div class="form-group">
                                                <label for="new_email">New Email Address</label>
                                                <input type="email" id="new_email" name="new_email" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="password_confirm_email">Confirm with Current Password</label>
                                                <div class="password-container">
                                                    <input type="password" id="password_confirm_email" name="password" required>
                                                    <span class="toggle-password"><i class="fas fa-eye"></i></span>
                                                </div>
                                            </div>
                                            <div class="form-footer">
                                                <button type="submit" class="modal-btn btn-save">Request Email Change</button>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- Password Form -->
                                    <div id="password-content" class="profile-content">
                                        <form id="changePasswordForm" class="profile-form">
                                            <h4>Update Your Password</h4>
                                            <div class="form-group">
                                                <label for="current_password">Current Password</label>
                                                <div class="password-container">
                                                    <input type="password" id="current_password" name="current_password" required>
                                                    <span class="toggle-password"><i class="fas fa-eye"></i></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="new_password">New Password</label>
                                                <div class="password-container">
                                                    <input type="password" id="new_password" name="new_password" required>
                                                    <span class="toggle-password"><i class="fas fa-eye"></i></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="confirm_new_password">Confirm New Password</label>
                                                <div class="password-container">
                                                    <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                                                    <span class="toggle-password"><i class="fas fa-eye"></i></span>
                                                </div>
                                            </div>
                                            <div class="form-footer">
                                                <button type="submit" class="modal-btn btn-save">Update Password</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <div id="addPhoneModal" class="modal hidden">
        <div class="modal-content">
            <h2>Add New Phone</h2>
            <form id="addPhoneForm" class="modal-form">
                <div class="form-grid grid-cols-2"> <input type="text" id="brand_name" placeholder="Brand Name" required> <input type="text" id="model_name" placeholder="Model Name" required> <input type="date" id="release_date" required> <input type="number" id="price" placeholder="Price (₹)" required> </div>
                <h3>Specifications</h3>
                <div class="form-grid grid-cols-3"> <input type="text" id="ram" placeholder="RAM"> <input type="text" id="storage" placeholder="Storage"> <input type="text" id="battery_capacity" placeholder="Battery Capacity"> <input type="text" id="fast_charging" placeholder="Fast Charging"> <input type="text" id="processor" placeholder="Processor"> <input type="text" id="chipset" placeholder="Chipset"> <input type="text" id="display_type" placeholder="Display Type"> <input type="text" id="screen_size" placeholder="Screen Size"> <input type="text" id="resolution" placeholder="Resolution"> <input type="text" id="refresh_rate" placeholder="Refresh Rate"> <input type="text" id="aspect_ratio" placeholder="Aspect Ratio"> </div>
                <h3>Features</h3>
                <div class="form-grid grid-cols-3"> <input type="text" id="rear_camera" placeholder="Rear Camera"> <input type="text" id="front_camera" placeholder="Front Camera"> <input type="text" id="os" placeholder="OS"> <input type="text" id="os_update" placeholder="OS Update"> <input type="text" id="ui" placeholder="UI"> <input type="text" id="fingerprint_sensor" placeholder="Fingerprint Sensor"> </div>
                <h3>Body</h3>
                <div class="form-grid grid-cols-3"> <input type="number" id="height" step="0.01" placeholder="Height (mm)"> <input type="number" id="width" step="0.01" placeholder="Width (mm)"> <input type="number" id="thickness" step="0.01" placeholder="Thickness (mm)"> <input type="number" id="weight" step="0.1" placeholder="Weight (g)"> <input type="text" id="sim_slot" placeholder="SIM Slot"> <input type="text" id="sim_type" placeholder="SIM Type"> <input type="text" id="network_support" placeholder="Network Support"> </div>
                <h3>Links</h3>
                <div class="form-grid grid-cols-2"> <input type="url" id="youtube" placeholder="YouTube Link"> <input type="url" id="image" placeholder="Image URL"> </div>
                <div class="modal-footer"> <button type="button" onclick="closeAddPhoneModal()" class="modal-btn btn-cancel">Cancel</button> <button type="submit" class="modal-btn btn-save">Save</button> </div>
            </form>
        </div>
    </div>
    <div id="editphonemodal" class="modal hidden">
    <div class="modal-content">
        <h2>Edit Phone Details</h2>
        <form id="editPhoneForm" class="modal-form">
            <!-- Hidden input to store the phone_id -->
            <input type="hidden" id="ephone_id" name="ephone_id" />

            <!-- Main Details -->
            <div class="form-grid grid-cols-2">
                <input type="text" id="ebrand_name" name="ebrand_name" placeholder="Brand Name" required>
                <input type="text" id="emodel_name" name="emodel_name" placeholder="Model Name" required>
                <input type="date" id="erelease_date" name="erelease_date" required>
                <input type="number" id="eprice" name="eprice" placeholder="Price (₹)" required>
            </div>

            <!-- Body Specifications -->
            <h3>Body</h3>
            <div class="form-grid grid-cols-3">
                <input type="number" id="eheight_mm" name="eheight_mm" step="0.01" placeholder="Height (mm)">
                <input type="number" id="ewidth_mm" name="ewidth_mm" step="0.01" placeholder="Width (mm)">
                <input type="number" id="ethickness_mm" name="ethickness_mm" step="0.01" placeholder="Thickness (mm)">
                <input type="number" id="eweight_g" name="eweight_g" step="0.1" placeholder="Weight (g)">
                <input type="text" id="esim_slot" name="esim_slot" placeholder="SIM Slot">
                <input type="text" id="esim_type" name="esim_type" placeholder="SIM Type">
            </div>
            
            <!-- Display & Performance -->
            <h3>Display & Performance</h3>
            <div class="form-grid grid-cols-3">
                <input type="text" id="edisplay_type" name="edisplay_type" placeholder="Display Type">
                <input type="text" id="escreen_size_in" name="escreen_size_in" placeholder="Screen Size (in)">
                <input type="text" id="eresolution" name="eresolution" placeholder="Resolution">
                <input type="text" id="erefresh_rate_hz" name="erefresh_rate_hz" placeholder="Refresh Rate (Hz)">
                <input type="text" id="easpect_ratio" name="easpect_ratio" placeholder="Aspect Ratio">
                <input type="text" id="eprocessor" name="eprocessor" placeholder="Processor">
                <input type="text" id="echipset" name="echipset" placeholder="Chipset">
                <input type="text" id="eram_gb" name="eram_gb" placeholder="RAM (GB)">
                <input type="text" id="estorage_gb" name="estorage_gb" placeholder="Storage (GB)">
            </div>
            
            <!-- Camera & Features -->
            <h3>Camera & Features</h3>
            <div class="form-grid grid-cols-3">
                <input type="text" id="erear_camera" name="erear_camera" placeholder="Rear Camera">
                <input type="text" id="efront_camera" name="efront_camera" placeholder="Front Camera">
                <input type="text" id="efingerprint_sensor" name="efingerprint_sensor" placeholder="Fingerprint Sensor">
                <input type="text" id="enetwork_support" name="enetwork_support" placeholder="Network Support">
            </div>

            <!-- Battery & OS -->
            <h3>Battery & OS</h3>
            <div class="form-grid grid-cols-3">
                <input type="text" id="ebattery_mah" name="ebattery_mah" placeholder="Battery (mAh)">
                <input type="text" id="efast_charging_w" name="efast_charging_w" placeholder="Fast Charging (W)">
                <input type="text" id="eos" name="eos" placeholder="Operating System">
                <input type="text" id="eos_update" name="eos_update" placeholder="OS Update Policy">
                <input type="text" id="eui" name="eui" placeholder="User Interface (UI)">
            </div>

            <!-- Links -->
            <h3>Links</h3>
            <div class="form-grid grid-cols-2">
                <input type="url" id="eimage_url" name="eimage_url" placeholder="Image URL">
                <input type="url" id="eyoutube_link" name="eyoutube_link" placeholder="YouTube Link">
            </div>

            <!-- Footer Buttons -->
            <div class="modal-footer">
                <button type="button" onclick="closeEditPhoneModal()" class="modal-btn btn-cancel">Cancel</button>
                <button type="submit" class="modal-btn btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>
    <div id="verifyEmailOtpModal" class="modal hidden">
        <div class="modal-content" style="max-width: 400px;">
            <h2>Verify New Email</h2>
            <p style="color: var(--text-secondary); margin-bottom: 1rem;">An OTP has been sent to the new email address. Please enter it below to complete the change.</p>
            <form id="verifyEmailOtpForm" class="modal-form">
                <input type="text" name="otp" placeholder="Enter 6-Digit OTP" required pattern="\d{6}" maxlength="6" style="font-size: 1.2rem; text-align: center; letter-spacing: 0.5rem;">
                <div class="modal-footer">
                    <button type="button" onclick="closeOtpModal()" class="modal-btn btn-cancel">Cancel</button>
                    <button type="submit" class="modal-btn btn-save">Verify & Update</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast-notification" class="toast"></div>
    <script>
        let performanceChart = null;
        let brandChart = null;
        let allPhones = [];

        // --- Charting Functions ---
        function initializePerformanceChart() {
            const ctx = document.getElementById('performanceChart').getContext('2d');
            const userGradient = ctx.createLinearGradient(0, 0, 0, 300);
            userGradient.addColorStop(0, 'rgba(231, 76, 60, 0.8)');
            userGradient.addColorStop(1, 'rgba(231, 76, 60, 0.2)');
            const phoneGradient = ctx.createLinearGradient(0, 0, 0, 300);
            phoneGradient.addColorStop(0, 'rgba(160, 160, 160, 0.8)');
            phoneGradient.addColorStop(1, 'rgba(160, 160, 160, 0.2)');
            performanceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Total Users', 'Total Phones'],
                    datasets: [{
                        label: 'Count',
                        data: [0, 0],
                        backgroundColor: [userGradient, phoneGradient],
                        borderColor: ['#e74c3c', '#a0a0a0'],
                        borderWidth: 2,
                        borderRadius: 5,
                        barThickness: 60
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: {
                                    family: "'Poppins', sans-serif"
                                },
                                color: '#a0a0a0'
                            },
                            grid: {
                                color: '#333333',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif",
                                    weight: '600'
                                },
                                color: '#ffffff'
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: '#121212',
                            titleFont: {
                                size: 16,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 14
                            },
                            padding: 12,
                            cornerRadius: 6,
                            displayColors: false
                        }
                    }
                }
            });
        }

        function initializeBrandChart() {
            const ctx = document.getElementById('brandDistributionChart').getContext('2d');
            brandChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Number of Phones',
                        data: [],
                        backgroundColor: 'rgba(231, 76, 60, 0.7)',
                        borderColor: '#e74c3c',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: {
                                    family: "'Poppins', sans-serif"
                                },
                                color: '#a0a0a0'
                            },
                            grid: {
                                color: '#333333',
                                drawBorder: false
                            }
                        },
                        y: {
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                },
                                color: '#ffffff'
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: '#121212',
                            titleFont: {
                                size: 16,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 14
                            },
                            padding: 12,
                            cornerRadius: 6,
                            displayColors: false
                        }
                    }
                }
            });
        }

        // --- NEW: Count-Up Animation Function ---
        function animateCountUp(element, finalValue) {
            let startValue = 0;
            const duration = 1500; // Animation duration in milliseconds
            const startTime = performance.now();

            function updateCount(currentTime) {
                const elapsedTime = currentTime - startTime;
                if (elapsedTime >= duration) {
                    element.textContent = finalValue;
                    return;
                }
                const progress = elapsedTime / duration;
                const currentCount = Math.floor(progress * finalValue);
                element.textContent = currentCount;
                requestAnimationFrame(updateCount);
            }
            requestAnimationFrame(updateCount);
        }

        // --- MODIFIED: Core Data Fetching ---
        function fetchDashboardCounts() {
            fetch('dashboard_counts.php')
                .then(response => response.ok ? response.json() : Promise.reject('Failed to load counts'))
                .then(data => {
                    animateCountUp(document.getElementById('total-users-count'), data.total_users);
                    animateCountUp(document.getElementById('total-phones-count'), data.total_phones);
                    if (performanceChart) {
                        updatePerformanceChart(data.total_users, data.total_phones);
                    }
                })
                .catch(error => console.error('Error fetching dashboard counts:', error));
        }

        // --- MODIFIED: Rendering Functions ---
        function renderUsersTable(users) {
            const tbody = document.getElementById('users-table-body');
            tbody.innerHTML = '';
            if (!users || users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6">No users found.</td></tr>';
                return;
            }
            users.forEach((user, index) => {
                const row = document.createElement('tr');
                const isBlocked = user.status == 1;
                const buttonClass = isBlocked ? 'action-btn-unblock' : 'action-btn-block';
                const buttonText = isBlocked ? 'Unblock' : 'Block';
                row.innerHTML = `<td>${user.uid}</td> <td>${user.username}</td> <td>${user.email}</td> <td>${user.role}</td> <td class="action-buttons"> <button onclick="toggleUserBlock(${user.uid}, ${isBlocked})" class="action-btn ${buttonClass}" title="${buttonText} User"> ${buttonText} </button> </td>`;
                row.style.animationDelay = `${index * 50}ms`;
                row.classList.add('fade-in');
                tbody.appendChild(row);
            });
        }

        function renderPhonesTable(phones) {
            const tbody = document.getElementById('phones-table-body');
            tbody.innerHTML = "";
            if (!phones || phones.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5">No matching phones found.</td></tr>';
                return;
            }
            phones.forEach((phone, index) => {
                const row = document.createElement("tr");
                row.innerHTML = `<td>${index + 1}</td> <td>${phone.brand_name}</td> <td>${phone.model_name}</td> <td>₹${phone.price}</td> <td class="action-buttons"> <button onclick="openEditPhoneModal(${phone.phone_id})" class="text-blue-500" title="Edit Phone"><i class="fas fa-edit"></i></button> <button onclick="deletePhone(${phone.phone_id})" class="text-red-500" title="Delete Phone"><i class="fas fa-trash"></i></button> </td>`;
                row.style.animationDelay = `${index * 50}ms`;
                row.classList.add('fade-in');
                tbody.appendChild(row);
            });
        }

        // --- Unchanged JavaScript Functions from here ---
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast-notification');
            toast.textContent = message;
            toast.className = `toast ${type} show`;
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function fetchUsers() {
            fetch('get_users.php')
                .then(response => response.ok ? response.json() : Promise.reject('Failed to load users'))
                .then(data => {
                    renderUsersTable(data);
                })
                .catch(error => {
                    console.error('Error fetching users:', error);
                    document.getElementById('users-table-body').innerHTML = `<tr><td colspan="6">Failed to load users.</td></tr>`;
                });
        }

        function fetchPhones() {
            fetch('get_phone.php')
                .then(response => response.ok ? response.json() : Promise.reject('Failed to load phones'))
                .then(data => {
                    allPhones = data;
                    applyFiltersAndSort();
                })
                .catch(error => {
                    console.error('Error fetching phones:', error);
                    allPhones = [];
                    document.getElementById('phones-table-body').innerHTML = `<tr><td colspan="5">Failed to load phones.</td></tr>`;
                });
        }

        function fetchBrandDistribution() {
            fetch('get_brand_distribution.php')
                .then(response => response.ok ? response.json() : Promise.reject('Failed to load brand data'))
                .then(data => {
                    if (data.labels && data.data && brandChart) {
                        updateBrandChart(data.labels, data.data);
                    }
                })
                .catch(error => console.error('Error fetching brand distribution:', error));
        }

        function updatePerformanceChart(userCount, phoneCount) {
            if (performanceChart) {
                performanceChart.data.datasets[0].data[0] = userCount;
                performanceChart.data.datasets[0].data[1] = phoneCount;
                performanceChart.update();
            }
        }

        function updateBrandChart(labels, data) {
            if (brandChart) {
                brandChart.data.labels = labels;
                brandChart.data.datasets[0].data = data;
                brandChart.update();
            }
        }

        function showSection(sectionId) {
            document.querySelectorAll('.content-section').forEach(section => section.classList.add('hidden'));
            document.getElementById(`${sectionId}-section`).classList.remove('hidden');
            const titleMap = {
                dashboard: 'Dashboard',
                users: 'User Management',
                phones: 'Phone Management',
                profile: 'Profile Settings'
            };
            document.getElementById('section-title').textContent = titleMap[sectionId] || 'Dashboard';
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.classList.remove('active-tab');
                if (link.getAttribute('onclick') ?.includes(`'${sectionId}'`)) {
                    link.classList.add('active-tab');
                }
            });
        }

        function toggleProfileOption(option, clickedButton) {
            document.querySelectorAll('.profile-option-btn').forEach(btn => btn.classList.remove('active'));
            clickedButton.classList.add('active');
            document.querySelectorAll('.profile-content').forEach(content => content.classList.remove('active'));
            document.getElementById(`${option}-content`).classList.add('active');
        }

        function applyFiltersAndSort() {
            const searchTerm = document.getElementById('phoneSearchInput').value.toLowerCase();
            const sortBy = document.getElementById('phoneSortSelect').value;
            let processedPhones = [...allPhones];
            if (searchTerm) {
                processedPhones = processedPhones.filter(phone => phone.brand_name.toLowerCase().includes(searchTerm) || phone.model_name.toLowerCase().includes(searchTerm));
            }
            processedPhones.sort((a, b) => {
                switch (sortBy) {
                    case 'price-lth':
                        return parseFloat(a.price) - parseFloat(b.price);
                    case 'price-htl':
                        return parseFloat(b.price) - parseFloat(a.price);
                    case 'brand-az':
                        return a.brand_name.localeCompare(b.brand_name);
                    case 'brand-za':
                        return b.brand_name.localeCompare(a.brand_name);
                    case 'name-az':
                        return a.model_name.localeCompare(b.model_name);
                    case 'name-za':
                        return b.model_name.localeCompare(a.model_name);
                    default:
                        return 0;
                }
            });
            renderPhonesTable(processedPhones);
        }

        function toggleUserBlock(userId, isCurrentlyBlocked) {
            const action = isCurrentlyBlocked ? 'unblock' : 'block';
            if (confirm(`Are you sure you want to ${action} this user?`)) {
                const formData = new FormData();
                formData.append('uid', userId);
                fetch('toggle_user_status.php', {
                        method: 'POST',
                        body: formData
                    }).then(response => response.json()).then(result => {
                        showToast(result.message, result.status);
                        if (result.status === 'success') {
                            fetchUsers();
                        }
                    }).catch(error => {
                        showToast('A critical error occurred.', 'error');
                        console.error(`Error toggling user status:`, error);
                    });
            }
        }

        function deletePhone(phoneId) {
            if (confirm('Are you sure you want to delete this phone? This action cannot be undone.')) {
                fetch(`delete_phone.php?phone_id=${phoneId}`, {
                    method: 'POST'
                }).then(response => response.json()).then(result => {
                    showToast(result.message, result.status);
                    if (result.status === 'success') {
                        fetchPhones();
                        fetchDashboardCounts();
                        fetchBrandDistribution();
                    }
                }).catch(error => {
                    showToast('Could not delete phone. See console.', 'error');
                    console.error('Error deleting phone:', error);
                });
            }
        }

        function openAddPhoneModal() {
            document.getElementById('addPhoneModal').classList.remove('hidden');
        }

        function closeAddPhoneModal() {
            document.getElementById('addPhoneForm').reset();
            document.getElementById('addPhoneModal').classList.add('hidden');
        }

        function openEditPhoneModal(phone_id) {
            fetch(`get_single_phone.php?phone_id=${phone_id}`).then(response => response.json()).then(data => {
                if (data.error) {
                    showToast(data.error, 'error');
                    return;
                }
                for (const key in data) {
                    if (document.getElementById(`e${key}`)) {
                        document.getElementById(`e${key}`).value = data[key];
                    }
                }
                document.getElementById('editphonemodal').classList.remove('hidden');
            }).catch(err => {
                showToast("Could not load phone details.", 'error');
                console.error("Error loading phone data for editing:", err);
            });
        }

        function closeEditPhoneModal() {
            document.getElementById('editPhoneForm').reset();
            document.getElementById('editphonemodal').classList.add('hidden');
        }

        function openOtpModal() {
            document.getElementById('verifyEmailOtpModal').classList.remove('hidden');
        }

        function closeOtpModal() {
            document.getElementById('verifyEmailOtpForm').reset();
            document.getElementById('verifyEmailOtpModal').classList.add('hidden');
        }

        function togglePasswordVisibility(e) {
            const container = e.target.closest('.password-container');
            const input = container.querySelector('input');
            const icon = container.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            showSection('dashboard');
            initializePerformanceChart();
            initializeBrandChart();
            fetchDashboardCounts();
            fetchUsers();
            fetchPhones();
            fetchBrandDistribution();

            document.getElementById('phoneSearchInput').addEventListener('input', applyFiltersAndSort);
            document.getElementById('phoneSortSelect').addEventListener('change', applyFiltersAndSort);

            document.getElementById('addPhoneForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;
                const data = {
                    brand_name: form.brand_name.value,
                    model_name: form.model_name.value,
                    release_date: form.release_date.value,
                    price: parseFloat(form.price.value),
                    specs: {
                        ram: form.ram.value,
                        storage: form.storage.value,
                        battery_capacity: form.battery_capacity.value,
                        fast_charging: form.fast_charging.value,
                        processor: form.processor.value,
                        chipset: form.chipset.value,
                        display_type: form.display_type.value,
                        screen_size: form.screen_size.value,
                        resolution: form.resolution.value,
                        refresh_rate: form.refresh_rate.value,
                        aspect_ratio: form.aspect_ratio.value
                    },
                    features: {
                        rear_camera: form.rear_camera.value,
                        front_camera: form.front_camera.value,
                        os: form.os.value,
                        os_update: form.os_update.value,
                        ui: form.ui.value,
                        fingerprint_sensor: form.fingerprint_sensor.value
                    },
                    body: {
                        height: form.height.value,
                        width: form.width.value,
                        thickness: form.thickness.value,
                        weight: form.weight.value,
                        sim_slot: form.sim_slot.value,
                        sim_type: form.sim_type.value,
                        network_support: form.network_support.value
                    },
                    links: {
                        youtube: form.youtube.value,
                        image: form.image.value
                    }
                };
                fetch('add_phone.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json()).then(res => {
                        showToast(res.message, res.status);
                        if (res.status === 'success') {
                            closeAddPhoneModal();
                            fetchPhones();
                            fetchDashboardCounts();
                            fetchBrandDistribution();
                        }
                    })
                    .catch(error => {
                        showToast("Failed to add phone. See console.", 'error');
                        console.error('Error adding phone:', error);
                    });
            });

            // EDIT PHONE FORM (CORRECTED to work with the OLD, UNCHANGED editphone.php)
document.getElementById('editPhoneForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const form = e.target;
    const getValueOrNull = (element) => element.value.trim() === '' ? null : element.value.trim();

    // Re-create the PRECISE nested JSON object that editphone.php and add_phone.php expect.
    // This reads from the new HTML IDs (e.g., 'ebattery_mah') and maps them to the old JSON keys (e.g., 'battery_capacity').
    const data = {
        phone_id: getValueOrNull(document.getElementById("ephone_id")),
        brand_name: getValueOrNull(form.ebrand_name),
        model_name: getValueOrNull(form.emodel_name),
        release_date: getValueOrNull(form.erelease_date),
        price: parseFloat(getValueOrNull(form.eprice) || 0),

        // This 'specs' object now has the EXACT keys your backend expects
        specs: {
            ram: getValueOrNull(form.eram_gb),
            storage: getValueOrNull(form.estorage_gb),
            battery_capacity: getValueOrNull(form.ebattery_mah), // <-- The critical mapping
            fast_charging: getValueOrNull(form.efast_charging_w), // <-- The critical mapping
            processor: getValueOrNull(form.eprocessor),
            chipset: getValueOrNull(form.echipset),
            display_type: getValueOrNull(form.edisplay_type),
            screen_size: getValueOrNull(form.escreen_size_in), // <-- The critical mapping
            resolution: getValueOrNull(form.eresolution),
            refresh_rate: getValueOrNull(form.erefresh_rate_hz), // <-- The critical mapping
            aspect_ratio: getValueOrNull(form.easpect_ratio)
        },

        // This 'features' object has the EXACT keys your backend expects
        features: {
            rear_camera: getValueOrNull(form.erear_camera),
            front_camera: getValueOrNull(form.efront_camera),
            os: getValueOrNull(form.eos),
            os_update: getValueOrNull(form.eos_update),
            ui: getValueOrNull(form.eui),
            fingerprint_sensor: getValueOrNull(form.efingerprint_sensor)
        },

        // This 'body' object has the EXACT keys your backend expects
        body: {
            height: parseFloat(getValueOrNull(form.eheight_mm) || 0),
            width: parseFloat(getValueOrNull(form.ewidth_mm) || 0),
            thickness: parseFloat(getValueOrNull(form.ethickness_mm) || 0),
            weight: parseFloat(getValueOrNull(form.eweight_g) || 0),
            sim_slot: getValueOrNull(form.esim_slot),
            sim_type: getValueOrNull(form.esim_type),
            network_support: getValueOrNull(form.enetwork_support)
        },

        // This 'links' object has the EXACT keys your backend expects
        links: {
            youtube: getValueOrNull(form.eyoutube_link), // <-- The critical mapping
            image: getValueOrNull(form.eimage_url)       // <-- The critical mapping
        }
    };

    // This fetch call will now send the correctly structured data to your unchanged PHP script.
    fetch('editphone.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(res => {
        showToast(res.message, res.status);
        if (res.status === 'success' && res.message.includes("updated successfully")) { // Check for the real success message
            closeEditPhoneModal();
            fetchPhones(); // Re-fetch and redraw the main phone list
            fetchBrandDistribution();
        }
    })
    .catch(error => {
        showToast("Failed to update phone. See console for details.", 'error');
        console.error('Error updating phone:', error);
    });
});

            document.querySelectorAll('.toggle-password').forEach(icon => {
                icon.addEventListener('click', togglePasswordVisibility);
            });

            document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;
                const newPassword = form.new_password.value;
                const confirmNewPassword = form.confirm_new_password.value;
                const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$]).{8,}$/;
                if (!passwordRegex.test(newPassword)) {
                    showToast('Password must be at least 8 characters, with 1 uppercase letter, 1 number, and one special character (!, @, #, $).', 'error');
                    return;
                }
                if (newPassword !== confirmNewPassword) {
                    showToast('New passwords do not match.', 'error');
                    return;
                }
                const formData = new FormData();
                formData.append('current_password', form.current_password.value);
                formData.append('new_password', newPassword);
                fetch('change_password.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json()).then(result => {
                    showToast(result.message, result.status);
                    if (result.status === 'success') {
                        form.reset();
                    }
                }).catch(error => {
                    showToast('A critical error occurred.', 'error');
                    console.error('Error changing password:', error);
                });
            });

            document.getElementById('changeUsernameForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;
                const newUsername = form.new_username.value;
                const usernameRegex = /^[a-zA-Z\s]{3,}$/;
                if (!usernameRegex.test(newUsername)) {
                    showToast('Username must be at least 3 letters long and cannot contain numbers and special charcters.', 'error');
                    return;
                }
                const formData = new FormData();
                formData.append('new_username', newUsername);
                formData.append('password', form.password_confirm_username.value);
                fetch('change_username.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json()).then(result => {
                    showToast(result.message, result.status);
                    if (result.status === 'success') {
                        form.reset();
                        document.getElementById('admin-username').textContent = `Welcome, ${result.new_username}`;
                    }
                }).catch(error => {
                    showToast('A critical error occurred.', 'error');
                    console.error('Error changing username:', error);
                });
            });

            document.getElementById('changeEmailForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                fetch('change_email_request.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json()).then(result => {
                    showToast(result.message, result.status);
                    if (result.status === 'success') {
                        openOtpModal();
                    }
                }).catch(error => {
                    showToast('A critical error occurred while requesting OTP.', 'error');
                    console.error('Error requesting email change:', error);
                });
            });

            document.getElementById('verifyEmailOtpForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                fetch('change_email_verify.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json()).then(result => {
                    showToast(result.message, result.status);
                    if (result.status === 'success') {
                        closeOtpModal();
                        document.getElementById('changeEmailForm').reset();
                    }
                }).catch(error => {
                    showToast('A critical error occurred during verification.', 'error');
                    console.error('Error verifying OTP:', error);
                });
            });
        });

// DELETE the old export functions and ADD these three new ones.

/**
 * --- 1. The Main Export Handler ---
 * This function is called by the buttons. It fetches the full data
 * from the new PHP script and then calls the appropriate generator.
 */
// REPLACE your old handleExport function with this simplified version
function handleExport(format) {
    const downloadButton = event.target.closest('button');
    if (!downloadButton) return;

    const originalButtonText = downloadButton.innerHTML;
    downloadButton.disabled = true;
    downloadButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fetching data...';

    fetch('get_all_phone_data.php')
        .then(response => response.json())
        .then(fullData => {
            if (fullData.length === 0) {
                showToast("No data to export.", "error");
                return;
            }
            // Directly call the CSV generator
            generateCSV(fullData);
        })
        .catch(error => {
            console.error("Export Error:", error);
            showToast("Failed to fetch data for export.", "error");
        })
        .finally(() => {
            downloadButton.disabled = false;
            downloadButton.innerHTML = originalButtonText;
        });
}


/**
 * --- 3. The CSV Generation Logic ---
 * This function now takes data as a parameter.
 */
function generateCSV(data) {
    const headers = Object.keys(data[0]);
    let csvContent = headers.join(",") + "\n";

    data.forEach(phone => {
        const row = headers.map(header => {
            let cell = phone[header] === null || phone[header] === undefined ? '' : phone[header];
            cell = String(cell);
            // Escape quotes by doubling them, and wrap the cell in quotes if it contains a comma or quote
            if (cell.includes('"') || cell.includes(",")) {
                cell = `"${cell.replace(/"/g, '""')}"`;
            }
            return cell;
        });
        csvContent += row.join(",") + "\n";
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", "full_phone_export.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showToast("Full data exported to CSV!", 'success');
}
    </script>
</body>
</html>