<?php
session_start();
// --- Caching Headers ---
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// --- Authentication & Database ---
if (!isset($_SESSION['uid'])) {
    header('Location: login.php?redirect=w.php');
    exit();
}

require_once 'config.php';

$user_uid = $_SESSION['uid'];
$wishlist_items = [];

// --- NEW DATABASE-COMPATIBLE QUERY ---
// Fetches all details for phones present in the user's wishlist.
$sql = "SELECT s.* 
        FROM wishlist w
        JOIN smartphones s ON w.phone_id = s.phone_id
        WHERE w.uid = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_uid);
$stmt->execute();
$result = $stmt->get_result();

// --- DATA TRANSLATION for JAVASCRIPT ---
// Build a nested array to match the structure the JS prototype expects.
while ($row = $result->fetch_assoc()) {
    $wishlist_items[] = [
        'phone_id' => $row['phone_id'],
        'brand_name' => $row['brand_name'],
        'model_name' => $row['model_name'],
        'price' => $row['price'],
        'release_date' => $row['release_date'],
        'specs' => [
            'processor' => $row['processor'],
            'ram' => $row['ram_gb'],             // Map ram_gb to ram
            'storage' => $row['storage_gb'],       // Map storage_gb to storage
            'battery_capacity' => $row['battery_mah'] // Map battery_mah to battery_capacity
        ],
        // The prototype only uses the image, so we only need to map that.
        'links' => [
            'image' => $row['image_url']         // Map image_url to image
        ]
    ];
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist | Gadget Guru</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* All CSS from your prototype remains the same */
        :root { --primary-color: #e74c3c; --success-color: #27ae60; --bg-color: #121212; --text-primary: #ffffff; --text-secondary: #a0a0a0; --border-color: #333333; --font-family-body: 'Poppins', sans-serif; --font-family-headings: 'Roboto', sans-serif; --border-radius: 12px; --shadow: 0 8px 30px rgba(0,0,0,0.5); --transition: all 0.3s ease; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font-family-body); background-color: var(--bg-color); color: var(--text-primary); }
        .floating-navbar { position: fixed; top: 20px; left: 50%; width: 95%; max-width: 600px; z-index: 1000; background: rgba(20, 20, 20, 0.6); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border-radius: 50px; border: 1px solid rgba(255, 255, 255, 0.1); padding: 0.6rem 1rem; display: flex; justify-content: space-between; align-items: center; opacity: 0; transform: translate(-50%, -30px); animation: slideDown 0.6s 0.2s ease-out forwards; transition: box-shadow 0.3s ease; }
        .floating-navbar.scrolled { box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37); }
        @keyframes slideDown { to { opacity: 1; transform: translate(-50%, 0); } }
        .floating-navbar .logo { display: flex; align-items: center; gap: 0.7rem; text-decoration: none; color: #fff; }
        .floating-navbar .logo-icon { font-size: 1.5rem; color: var(--primary-color); }
        .floating-navbar .logo-text { font-size: 1.2rem; font-weight: 700; }
        .floating-navbar .nav-links { display: flex; align-items: center; gap: 0.5rem; }
        .floating-navbar .nav-icon-link { color: var(--text-secondary); text-decoration: none; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.1); transition: var(--transition); }
        .floating-navbar .nav-icon-link:hover { color: #fff; background: var(--primary-color); transform: scale(1.1); }
        .wishlist-container { max-width: 1400px; margin: 160px auto 160px auto; padding: 0 2rem; }
        .wishlist-header { margin-bottom: 2.5rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; }
        .section-title { font-family: var(--font-family-headings); font-size: 2.5rem; font-weight: 700; display: flex; align-items: center; gap: 1rem; }
        .section-title i { color: var(--primary-color); }
        .item-count { font-size: 1.2rem; background: var(--card-bg); color: var(--text-secondary); padding: 0.3rem 0.8rem; border-radius: 20px; font-weight: 600; margin-left: 0.5rem; }
        .wishlist-controls { display: flex; gap: 1rem; align-items: center; }
        .sort-control select { background-color: var(--card-bg); color: var(--text-primary); border: 1px solid var(--border-color); padding: 0.7rem 1rem; border-radius: 8px; font-family: var(--font-family-body); font-size: 0.9rem; -webkit-appearance: none; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23a0a0a0' class='bi bi-chevron-down' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem; }
        .compare-button { padding: 0.7rem 1.5rem; background-color: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 0.9rem; display: none; align-items: center; gap: 0.5rem; transition: var(--transition); }
        .compare-button:hover { background-color: #fff; color: #000; }
        .compare-button.visible { display: inline-flex; }
        .phone-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.8rem; }
        .phone-card { background-color: var(--card-bg); border-radius: var(--border-radius); overflow: hidden; border: 1px solid var(--border-color); position: relative; transition: opacity 0.4s ease, transform 0.4s ease; }
        .phone-card.removing { opacity: 0; transform: scale(0.9); }
        .phone-card:hover { transform: translateY(-8px); box-shadow: var(--shadow); }
        .card-actions { position: absolute; top: 1rem; right: 1rem; z-index: 2; display: flex; flex-direction: column; gap: 0.5rem; }
        .action-btn { width: 38px; height: 38px; border: none; border-radius: 50%; cursor: pointer; background-color: rgba(0,0,0,0.5); color: #fff; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; transition: var(--transition); }
        .action-btn:hover { background-color: var(--primary-color); transform: scale(1.1); }
        .compare-checkbox { position: absolute; top: 1rem; left: 1rem; z-index: 2; }
        .compare-checkbox input { display: none; }
        .compare-checkbox label { width: 38px; height: 38px; background: rgba(0,0,0,0.5); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: var(--transition); }
        .compare-checkbox input:checked + label { background-color: var(--success-color); color: #fff; }
        .phone-image { height: 240px; padding: 1rem; display: flex; align-items: center; justify-content: center; cursor: pointer; background-color: #ffffff; border-bottom: 1px solid var(--border-color); }
        .phone-image img { max-height: 90%; max-width: 90%; object-fit: contain; transition: transform 0.3s ease; }
        .phone-card:hover .phone-image img { transform: scale(1.05); }
        .phone-details { padding: 1.5rem; }
        .phone-model { font-size: 1.2rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.8rem; line-height: 1.3; }
        .phone-specs { display: grid; grid-template-columns: 1fr; gap: 0.8rem; font-size: 0.85rem; margin-bottom: 1.2rem; }
        .spec-item { display: flex; align-items: center; gap: 0.8rem; color: var(--text-secondary); }
        .spec-item i { color: var(--primary-color); font-size: 1rem; width: 18px; text-align: center; }
        .phone-footer { display: flex; justify-content: space-between; align-items: center; }
        .price { font-size: 1.4rem; font-weight: 700; color: var(--primary-color); }
        .view-button { padding: 0.6rem 1.2rem; background: transparent; color: #fff; border: 1px solid var(--border-color); border-radius: 8px; cursor: pointer; transition: var(--transition); font-weight: 600; }
        .view-button:hover { background: var(--primary-color); border-color: var(--primary-color); }
        .no-results-container { text-align: center; padding: 4rem 2rem; background-color: var(--card-bg); border-radius: var(--border-radius); border: 1px solid var(--border-color); grid-column: 1 / -1; margin-top: 2rem; }
        .no-results-container .fa-heart-crack { font-size: 4rem; color: var(--primary-color); opacity: 0.7; margin-bottom: 1.5rem; }
        .no-results-container p { font-size: 1.3rem; font-weight: 600; color: var(--text-primary); margin-bottom: 2rem; }
        .browse-button { display: inline-flex; align-items: center; gap: 0.7rem; padding: 0.8rem 2rem; background: var(--primary-color); color: white; text-decoration: none; border-radius: 30px; font-weight: 600; transition: var(--transition); box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4); }
        .browse-button:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(231, 76, 60, 0.5); }
        #toast-container { position: fixed; bottom: 2rem; right: 2rem; z-index: 1050; display: flex; flex-direction: column; gap: 0.5rem; }
        .toast { background-color: var(--card-bg); color: var(--text-primary); padding: 1rem 1.5rem; border-radius: 8px; border-left: 5px solid var(--success-color); box-shadow: var(--shadow); display: flex; align-items: center; gap: 1rem; transform: translateX(calc(100% + 2rem)); transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .toast.show { transform: translateX(0); }
        .toast.error { border-left-color: var(--primary-color); }
        .toast .toast-icon { font-size: 1.2rem; }
        .toast.success .toast-icon { color: var(--success-color); }
        .toast.error .toast-icon { color: var(--primary-color); }
        @media (max-width: 768px) { .wishlist-container { padding: 0 1rem; margin-top: 100px; } .wishlist-header { flex-direction: column; align-items: flex-start; } .section-title { font-size: 2rem; } #toast-container { right: 1rem; left: 1rem; bottom: 1rem; } .floating-navbar { width: 90%; } }
        @media (max-width: 480px) { .floating-navbar .logo-text { display: none; } }
    </style>
</head>
<body>
    <nav class="floating-navbar" id="mainNav">
        <a href="shome.php" class="logo">
            <i class="fas fa-mobile-alt logo-icon"></i>
            <span class="logo-text">Gadget Guru</span>
        </a>
        <div class="nav-links">
            <a href="shome.php" class="nav-icon-link" title="Home">
                <i class="fas fa-home"></i>
            </a>
            <a href="w.php" class="nav-icon-link" title="My Wishlist">
                <i class="fas fa-heart"></i>
            </a>
        </div>
    </nav>

    <div class="wishlist-container">
        <header class="wishlist-header">
            <h1 class="section-title">
                <i class="fas fa-heart"></i>
                My Wishlist
                <span class="item-count" id="itemCount">0 items</span>
            </h1>
            <div class="wishlist-controls">
                <div class="sort-control">
                    <select id="sortSelector" aria-label="Sort phones by">
                        <option value="default">Sort by...</option>
                        <option value="price-desc">Price: High to Low</option>
                        <option value="price-asc">Price: Low to High</option>
                        <option value="date-desc">Newest First</option>
                    </select>
                </div>
                <button class="compare-button" id="compareBtn">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Compare Selected</span>
                </button>
            </div>
        </header>

        <main class="phone-grid" id="phoneContainer"></main>
    </div>
    
    <div id="toast-container"></div>
    
    <?php require_once 'footer.php'; ?>
    
    <script>
    // --- JAVASCRIPT IS MODIFIED ---
    let originalPhoneList = <?php echo json_encode($wishlist_items); ?>;
    let selectedForCompare = [];
    
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        toast.innerHTML = `<i class="toast-icon ${iconClass}"></i><span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            toast.addEventListener('transitionend', () => toast.remove());
        }, 4000);
    }

    // fetchWishlist function is no longer needed.

    function renderPhones(phoneArray) {
        const container = document.getElementById('phoneContainer');
        const itemCountEl = document.getElementById('itemCount');
        container.innerHTML = '';
        const count = phoneArray ? phoneArray.length : 0;
        itemCountEl.textContent = `${count} ${count === 1 ? 'item' : 'items'}`;
        if (count === 0) {
            container.innerHTML = ` <div class="no-results-container"> <i class="fas fa-heart-crack"></i> <p>Your wishlist is a blank canvas. Let's fill it!</p> <a href="shome.php" class="browse-button"> <i class="fas fa-search"></i> Find Your Next Phone </a> </div>`;
            return;
        }
        phoneArray.forEach(phone => {
            const card = document.createElement('div');
            card.className = 'phone-card';
            card.id = `phone-card-${phone.phone_id}`;
            const priceFormatted = new Intl.NumberFormat('en-IN').format(phone.price);
            const isSelected = selectedForCompare.includes(phone.phone_id.toString());
            const phoneFullName = `${phone.brand_name} ${phone.model_name}`;
            card.innerHTML = ` <div class="compare-checkbox"> <input type="checkbox" id="compare-${phone.phone_id}" value="${phone.phone_id}" ${isSelected ? 'checked' : ''}> <label for="compare-${phone.phone_id}" title="Select for Comparison"><i class="fas fa-check"></i></label> </div> <div class="card-actions"> <button class="action-btn" title="Remove from Wishlist" onclick="removeWishlistItem(${phone.phone_id}, '${phoneFullName.replace(/'/g, "\\'")}')"> <i class="fas fa-trash-alt"></i> </button> </div> <div class="phone-image" onclick="location.href='detail.php?phone_id=${phone.phone_id}'"> <img src="${phone.links.image}" alt="${phoneFullName}"> </div> <div class="phone-details"> <div class="phone-model">${phoneFullName}</div> <div class="phone-specs"> <div class="spec-item"><i class="fas fa-microchip"></i><span>${phone.specs.processor || 'N/A'}</span></div> <div class="spec-item"><i class="fas fa-memory"></i><span>${phone.specs.ram || 'N/A'} GB RAM &bull; ${phone.specs.storage || 'N/A'} GB</span></div> <div class="spec-item"><i class="fas fa-battery-full"></i><span>${phone.specs.battery_capacity || 'N/A'} mAh</span></div> </div> <div class="phone-footer"> <span class="price">₹${priceFormatted}</span> <button class="view-button" onclick="location.href='detail.php?phone_id=${phone.phone_id}'">Details</button> </div> </div>`;
            container.appendChild(card);
        });
    }

    function sortPhones(sortBy) {
        let sortedList = [...originalPhoneList];
        switch(sortBy) {
            case 'price-desc': sortedList.sort((a, b) => b.price - a.price); break;
            case 'price-asc': sortedList.sort((a, b) => a.price - b.price); break;
            case 'date-desc': sortedList.sort((a, b) => new Date(b.release_date) - new Date(a.release_date)); break;
            default: break;
        }
        renderPhones(sortedList);
    }
    
    function removeWishlistItem(phoneId, phoneName) {
        const card = document.getElementById(`phone-card-${phoneId}`);
        if(card) { card.classList.add('removing'); }
        fetch('add_wishlist.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `phone_id=${encodeURIComponent(phoneId)}` })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'removed') {
                card.addEventListener('transitionend', () => { 
                    originalPhoneList = originalPhoneList.filter(p => p.phone_id != phoneId);
                    sortPhones(document.getElementById('sortSelector').value); 
                });
                showToast(`${phoneName} was removed.`, 'success');
            } else { showToast(data.message || 'Could not remove item.', 'error'); if(card) card.classList.remove('removing'); }
        })
        .catch(error => { console.error('Error removing item:', error); showToast('A network error occurred. Please try again.', 'error'); if(card) card.classList.remove('removing'); });
    }
    
    function updateCompareState(checkbox) {
        if (checkbox.checked) {
            if (selectedForCompare.length >= 2) {
                showToast('You can only compare 2 phones at a time.', 'error');
                checkbox.checked = false; 
                return;
            }
            if (!selectedForCompare.includes(checkbox.value)) {
                selectedForCompare.push(checkbox.value);
            }
        } else {
            selectedForCompare = selectedForCompare.filter(id => id !== checkbox.value);
        }
        const compareBtn = document.getElementById('compareBtn');
        compareBtn.classList.toggle('visible', selectedForCompare.length === 2);
    }

    document.addEventListener('DOMContentLoaded', () => {
        // --- MODIFIED LOGIC: Render phones immediately ---
        renderPhones(originalPhoneList);
        
        const nav = document.getElementById('mainNav');
        if(nav) { window.addEventListener('scroll', () => { if (window.scrollY > 30) nav.classList.add('scrolled'); else nav.classList.remove('scrolled'); }); }
        
        document.getElementById('sortSelector').addEventListener('change', (e) => sortPhones(e.target.value));
        document.getElementById('phoneContainer').addEventListener('change', (e) => { if(e.target.matches('input[type="checkbox"]')) updateCompareState(e.target); });
        
        document.getElementById('compareBtn').addEventListener('click', () => { 
            if(selectedForCompare.length !== 2) return;
            window.location.href = `compare.php?id1=${selectedForCompare[0]}&id2=${selectedForCompare[1]}`; 
        });
    });
    </script>
</body>
</html>