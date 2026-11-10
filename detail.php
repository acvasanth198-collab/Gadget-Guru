<?php
session_start();
// --- GATEKEEPER ---
// First, check if the user is logged in by looking for a session UID.
// THIS IS THE MOST IMPORTANT PART.
if (!isset($_SESSION['uid'])) {
    // If they are NOT logged in, redirect them to the landing/login page.
    header("Location: landing.html");
    // Stop the script from running any further.
    exit();
}

// --- Cache Control ---
// If the user IS logged in, then we proceed and set the cache headers.
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once 'config.php';

$is_logged_in = isset($_SESSION['uid']);

$phone_id = filter_input(INPUT_GET, 'phone_id', FILTER_VALIDATE_INT);
if (!$phone_id) {
    die("Error: A valid Phone ID is required.");
}

$sql = "SELECT * FROM smartphones WHERE phone_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $phone_id);
$stmt->execute();
$result = $stmt->get_result();
$phone = $result->fetch_assoc();
$stmt->close();

if (!$phone) {
    die("Error: Phone with the specified ID was not found.");
}

$is_wishlisted = false;
if ($is_logged_in) {
    $user_uid = $_SESSION['uid'];
    $check_wishlist_sql = "SELECT 1 FROM wishlist WHERE uid = ? AND phone_id = ?";
    $check_stmt = $conn->prepare($check_wishlist_sql);
    $check_stmt->bind_param("ii", $user_uid, $phone_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    if ($check_stmt->num_rows > 0) {
        $is_wishlisted = true;
    }
    $check_stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($phone['brand_name'] ?? 'Phone') . ' ' . htmlspecialchars($phone['model_name'] ?? 'Details'); ?> | Gadget Guru</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- GLOBAL & PAGE-SPECIFIC STYLES --- */
        :root {
            --primary-color: #e74c3c; --success-color: #27ae60; --bg-color: #121212;
            --card-bg: #1e1e1e; --text-primary: #ffffff; --text-secondary: #a0a0a0;
            --border-color: #333333; --font-family-headings: 'Roboto', sans-serif;
            --font-family-body: 'Poppins', sans-serif; --border-radius: 12px;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.4); --transition: all 0.3s ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: var(--font-family-body); background-color: var(--bg-color); color: var(--text-primary); line-height: 1.6; }

        /* Styles for navbar, hero, specs, etc. (unchanged) */
        .floating-navbar {
            position: fixed; top: 20px; left: 50%; width: 95%; max-width: 600px; z-index: 1000;
            background: rgba(20, 20, 20, 0.6); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            border-radius: 50px; border: 1px solid rgba(255, 255, 255, 0.1); padding: 0.6rem 1rem;
            display: flex; justify-content: space-between; align-items: center;
            opacity: 0; transform: translate(-50%, -30px); animation: slideDown 0.6s 0.2s ease-out forwards;
            transition: box-shadow 0.3s ease;
        }
        .floating-navbar.scrolled { box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37); }
        @keyframes slideDown { to { opacity: 1; transform: translate(-50%, 0); } }
        .floating-navbar .logo { display: flex; align-items: center; gap: 0.7rem; text-decoration: none; color: #fff; }
        .floating-navbar .logo-icon { font-size: 1.5rem; color: var(--primary-color); }
        .floating-navbar .logo-text { font-size: 1.2rem; font-weight: 700; }
        .floating-navbar .nav-links { display: flex; align-items: center; gap: 0.5rem; }
        .floating-navbar .nav-icon-link {
            color: var(--text-secondary); text-decoration: none; width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }
        .floating-navbar .nav-icon-link:hover { color: #fff; background: var(--primary-color); transform: scale(1.1); }
        .page-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        main.page-container { margin-top: 160px; } /* Removed bottom margin to let footer handle spacing */
        .product-hero { display: grid; grid-template-columns: 1fr 1.2fr; gap: 50px; align-items: center; margin-bottom: 60px; }
        .hero-image-container { 
            background-color: #ffffff; border-radius: var(--border-radius); border: 1px solid var(--border-color); 
            padding: 2rem; display: flex; align-items: center; justify-content: center; aspect-ratio: 1 / 1; 
            box-shadow: 0 4px 30px rgba(255, 0, 0, 0.4); transition: var(--transition); 
        }
        .hero-image-container:hover { transform: translateY(-5px); }
        .hero-image { max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.4s ease; }       
        .hero-image-container:hover .hero-image { transform: scale(1.05); }
        .hero-info .release-date { font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 8px; font-weight: 500; }
        .hero-info .phone-title { font-family: var(--font-family-headings); font-size: 3rem; font-weight: 900; line-height: 1.1; margin-bottom: 16px; color: var(--text-primary); }
        .hero-info .price-tag { font-size: 2.8rem; font-weight: 700; color: var(--primary-color); margin-bottom: 30px; }
        .action-buttons { display: flex; }
        .action-btn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 12px 25px; font-size: 1rem; font-weight: 600; border-radius: 50px; cursor: pointer; transition: var(--transition); border: 2px solid; text-decoration: none; }
        .wishlist-btn { background-color: transparent; border-color: var(--primary-color); color: var(--primary-color); }
        .wishlist-btn:hover:not(:disabled) { background-color: var(--primary-color); border-color: var(--primary-color); color: #fff; transform: translateY(-3px); }
        .wishlist-btn.wishlisted { background-color: var(--primary-color); border-color: var(--primary-color); color: #fff; }
        .action-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; background-color: #333; border-color: #333; color: var(--text-secondary); }
        .wishlist-btn .fa-heart.animating { animation: heartBeat 0.6s ease-in-out; }
        @keyframes heartBeat { 0% { transform: scale(1); } 30% { transform: scale(1.3) rotate(-5deg); } 60% { transform: scale(1.1) rotate(5deg); } 100% { transform: scale(1); } }
        .specs-section, .video-section { margin-bottom: 60px; }
        .section-title { font-family: var(--font-family-headings); font-size: 2.2rem; font-weight: 700; margin-bottom: 30px; text-align: center; position: relative; padding-bottom: 15px; }
        .section-title::after { content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 80px; height: 4px; background: var(--primary-color); border-radius: 2px; }
        .specs-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; }
        .spec-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 25px; transition: var(--transition); }
        .spec-card:hover { transform: translateY(-5px); box-shadow: var(--shadow); border-color: #555; }
        .spec-card-title { font-size: 1.25rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; color: var(--text-primary); padding-bottom: 10px; border-bottom: 1px solid var(--border-color); }
        .spec-card-title i { color: var(--primary-color); }
        .spec-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; font-size: 0.95rem; }
        .spec-item:not(:last-child) { border-bottom: 1px solid #2c2c2c; }
        .spec-item .label { color: var(--text-secondary); padding-right: 15px; }
        .spec-item .value { font-weight: 500; text-align: right; }
        .video-wrapper { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: var(--border-radius); box-shadow: var(--shadow); border: 1px solid var(--border-color); }
        .video-wrapper iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        #message-area { position: fixed; bottom: 30px; right: 30px; z-index: 9999; }
        .message { padding: 12px 24px; border-radius: var(--border-radius); color: #fff; font-weight: 500; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); transform: translateX(calc(100% + 30px)); opacity: 0; transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55); }
        .message.show { transform: translateX(0); opacity: 1; }
        .message.success { background-color: var(--success-color, #27ae60); }
        .message.error { background-color: var(--primary-color); }
        @media (max-width: 992px) { .product-hero { grid-template-columns: 1fr; text-align: center; gap: 30px; } .hero-info { display: flex; flex-direction: column; align-items: center; } .hero-image-container { max-width: 400px; margin: 0 auto; } }
        @media (max-width: 768px) { main.page-container { margin-top: 100px; padding: 0 15px; } .hero-info .phone-title { font-size: 2.5rem; } .specs-grid { grid-template-columns: 1fr; } .floating-navbar { width: 90%; } }
        @media (max-width: 480px) { .hero-info .phone-title { font-size: 2rem; } .action-buttons { flex-direction: column; width: 100%; } .action-btn { width: 100%; } .floating-navbar .logo-text { display: none; } }
        
    </style>
</head>
<body>

    <nav class="floating-navbar" id="mainNav">
        <!-- Navbar content remains the same -->
        <a href="shome.php" class="logo">
            <i class="fas fa-mobile-alt logo-icon"></i>
            <span class="logo-text">Gadget Guru</span>
        </a>
        <div class="nav-links">
            <a href="shome.php" class="nav-icon-link" title="Home"><i class="fas fa-home"></i></a>
            <a href="w.php" class="nav-icon-link" title="My Wishlist"><i class="fas fa-heart"></i></a>
        </div>
    </nav>

    <div id="message-area"></div>

    <main class="page-container">
        <!-- Hero and Specs sections remain the same, with corrected variables -->
        <section class="product-hero">
            <div class="hero-image-container">
                <img src="<?php echo htmlspecialchars($phone['image_url'] ?? 'assets/default-image.png'); ?>" alt="<?php echo htmlspecialchars($phone['brand_name'] . ' ' . $phone['model_name']); ?>" class="hero-image" />
            </div>
            <div class="hero-info">
                <p class="release-date">Released: <?php echo htmlspecialchars(date("F d, Y", strtotime($phone['release_date'] ?? 'now'))); ?></p>
                <h1 class="phone-title"><?php echo htmlspecialchars($phone['brand_name'] ?? 'N/A') . ' ' . htmlspecialchars($phone['model_name'] ?? ''); ?></h1>
                <div class="price-tag">₹<?php echo number_format($phone['price'] ?? 0); ?></div>
                <div class="action-buttons">
                    <form id="wishlistForm" class="wishlist-form">
                        <input type="hidden" name="phone_id" value="<?php echo htmlspecialchars($phone['phone_id']); ?>">
                        <button type="submit" class="action-btn wishlist-btn <?php echo $is_wishlisted ? 'wishlisted' : ''; ?>" <?php echo !$is_logged_in ? 'disabled title="Please log in to add to wishlist"' : ''; ?>>
                            <i class="fas fa-heart"></i>
                            <span class="button-text"><?php echo $is_wishlisted ? 'In Wishlist' : 'Add to Wishlist'; ?></span>
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <section class="specs-section">
            <h2 class="section-title">Technical Specifications</h2>
            <div class="specs-grid">
                 <div class="spec-card"> <h3 class="spec-card-title"><i class="fas fa-microchip"></i> Performance</h3> <div class="spec-item"><span class="label">Processor</span><span class="value"><?php echo htmlspecialchars($phone['processor'] ?? 'N/A'); ?></span></div> <div class="spec-item"><span class="label">Chipset</span><span class="value"><?php echo htmlspecialchars($phone['chipset'] ?? 'N/A'); ?></span></div> <div class="spec-item"><span class="label">RAM</span><span class="value"><?php echo htmlspecialchars($phone['ram_gb'] ?? 'N/A'); ?> GB</span></div> <div class="spec-item"><span class="label">Storage</span><span class="value"><?php echo htmlspecialchars($phone['storage_gb'] ?? 'N/A'); ?> GB</span></div> <div class="spec-item"><span class="label">OS</span><span class="value"><?php echo htmlspecialchars($phone['os'] ?? 'N/A'); ?></span></div> <div class="spec-item"><span class="label">OS Update Policy</span><span class="value"><?php echo htmlspecialchars($phone['os_update'] ?? 'N/A'); ?></span></div> <div class="spec-item"><span class="label">Custom UI</span><span class="value"><?php echo htmlspecialchars($phone['ui'] ?? 'N/A'); ?></span></div> </div>
                <div class="spec-card"> <h3 class="spec-card-title"><i class="fas fa-mobile-screen-button"></i> Display</h3> <div class="spec-item"><span class="label">Display Type</span><span class="value"><?php echo htmlspecialchars($phone['display_type'] ?? 'N/A'); ?></span></div> <div class="spec-item"><span class="label">Screen Size</span><span class="value"><?php echo htmlspecialchars($phone['screen_size_in'] ?? 'N/A'); ?> inches</span></div> <div class="spec-item"><span class="label">Resolution</span><span class="value"><?php echo htmlspecialchars($phone['resolution'] ?? 'N/A'); ?></span></div> <div class="spec-item"><span class="label">Refresh Rate</span><span class="value"><?php echo htmlspecialchars($phone['refresh_rate_hz'] ?? 'N/A'); ?> Hz</span></div> <div class="spec-item"><span class="label">Aspect Ratio</span><span class="value"><?php echo htmlspecialchars($phone['aspect_ratio'] ?? 'N/A'); ?></span></div> </div>
                <div class="spec-card"> <h3 class="spec-card-title"><i class="fas fa-camera-retro"></i> Camera</h3> <div class="spec-item"><span class="label">Rear Camera</span><span class="value"><?php echo htmlspecialchars($phone['rear_camera'] ?? 'N/A'); ?></span></div> <div class="spec-item"><span class="label">Front Camera</span><span class="value"><?php echo htmlspecialchars($phone['front_camera'] ?? 'N/A'); ?></span></div> </div>
                <div class="spec-card"> <h3 class="spec-card-title"><i class="fas fa-battery-full"></i> Battery</h3> <div class="spec-item"><span class="label">Capacity</span><span class="value"><?php echo htmlspecialchars($phone['battery_mah'] ?? 'N/A'); ?> mAh</span></div> <div class="spec-item"><span class="label">Fast Charging</span><span class="value"><?php echo htmlspecialchars($phone['fast_charging_w'] ?? 'N/A'); ?>W Support</span></div> </div>
                <div class="spec-card"> <h3 class="spec-card-title"><i class="fas fa-ruler-combined"></i> Build & Design</h3> <div class="spec-item"><span class="label">Dimensions</span><span class="value"><?php echo htmlspecialchars($phone['height_mm'] ?? 'N/A'); ?> x <?php echo htmlspecialchars($phone['width_mm'] ?? 'N/A'); ?> x <?php echo htmlspecialchars($phone['thickness_mm'] ?? 'N/A'); ?> mm</span></div> <div class="spec-item"><span class="label">Weight</span><span class="value"><?php echo htmlspecialchars($phone['weight_g'] ?? 'N/A'); ?> grams</span></div> <div class="spec-item"><span class="label">Fingerprint Sensor</span><span class="value"><?php echo htmlspecialchars($phone['fingerprint_sensor'] ?? 'N/A'); ?></span></div> </div>
                <div class="spec-card"> <h3 class="spec-card-title"><i class="fas fa-signal"></i> Connectivity</h3> <div class="spec-item"><span class="label">SIM Slot(s)</span><span class="value"><?php echo htmlspecialchars($phone['sim_slot'] ?? 'N/A'); ?></span></div> <div class="spec-item"><span class="label">SIM Type</span><span class="value"><?php echo htmlspecialchars($phone['sim_type'] ?? 'N/A'); ?></span></div> <div class="spec-item"><span class="label">Network Support</span><span class="value"><?php echo htmlspecialchars($phone['network_support'] ?? 'N/A'); ?></span></div> </div>
            </div>
        </section>

        <?php if (!empty($phone['youtube_link'])): ?>
        <section class="video-section">
            <h2 class="section-title">Video Review</h2>
            <div class="video-wrapper">
                <iframe src="<?php echo htmlspecialchars($phone['youtube_link']); ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>

<script>
// ALL JAVASCRIPT FROM YOUR PROTOTYPE (UNCHANGED)
document.addEventListener('DOMContentLoaded', () => {
    const nav = document.getElementById('mainNav');
    if(nav) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 30) { nav.classList.add('scrolled'); } 
            else { nav.classList.remove('scrolled'); }
        });
    }

    const wishlistForm = document.getElementById('wishlistForm');
    if (wishlistForm) {
        wishlistForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const button = this.querySelector('.wishlist-btn');
            if (button.disabled || button.classList.contains('processing')) return;
            const icon = button.querySelector('.fas.fa-heart');
            button.classList.add('processing');
            if(icon) icon.classList.add('animating');
            const phoneId = this.querySelector('input[name="phone_id"]').value;
            fetch('add_wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `phone_id=${encodeURIComponent(phoneId)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'added') {
                    button.classList.add('wishlisted');
                    button.querySelector('.button-text').textContent = 'In Wishlist';
                    displayMessage(data.message, 'success');
                } else if (data.status === 'removed') {
                    button.classList.remove('wishlisted');
                    button.querySelector('.button-text').textContent = 'Add to Wishlist';
                    displayMessage(data.message, 'success');
                } else {
                    displayMessage(data.message || 'An error occurred', 'error');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                displayMessage('A network error occurred.', 'error');
            })
            .finally(() => {
                setTimeout(() => {
                    if(icon) icon.classList.remove('animating');
                    button.classList.remove('processing');
                }, 600);
            });
        });
    }
});

function displayMessage(msg, type = 'success') {
    const messageArea = document.getElementById('message-area');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = msg;
    messageArea.appendChild(messageDiv);

    setTimeout(() => messageDiv.classList.add('show'), 10);
    setTimeout(() => {
        messageDiv.classList.remove('show');
        setTimeout(() => messageDiv.remove(), 500);
    }, 3000);
}
</script>

</body>
</html>