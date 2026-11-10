<?php
session_start();

// --- Cache Control ---
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// User session data
$username = $_SESSION['username'] ?? "Guest";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compare Smartphones - Gadget Guru</title>
    
    <!-- Fonts and Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* All CSS remains the same as your previous correct version. No changes needed here. */
        :root { --primary-color: #e74c3c; --success-color: #27ae60; --bg-color: #121212; --card-bg: #1e1e1e; --text-primary: #ffffff; --text-secondary: #a0a0a0; --border-color: #333333; --font-family-body: 'Poppins', sans-serif; --font-family-headings: 'Roboto', sans-serif; --border-radius: 12px; --shadow: 0 8px 30px rgba(0,0,0,0.5); --transition: all 0.3s ease; }
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
        .comparison-container { max-width: 1400px; margin: 160px auto 160px auto; padding: 0 2rem; }
        .selection-hub { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 2.5rem; margin-bottom: 2rem; box-shadow: var(--shadow); }
        .selection-hub h1 { text-align: center; font-size: 2.5rem; font-family: var(--font-family-headings); margin-bottom: 2rem; }
        .selection-area { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; }
        .phone-selector { position: relative; }
        .phone-selector h3 { font-size: 1.2rem; color: var(--text-secondary); margin-bottom: 0.8rem; text-align: center;}
        .search-input { width: 100%; padding: 12px 15px; font-size: 1rem; border: 1px solid var(--border-color); border-radius: 8px; background-color: #111; color: var(--text-primary); transition: var(--transition); }
        .search-input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2); }
        .suggestions-list { position: absolute; top: 100%; left: 0; right: 0; background: #2a2a2a; border: 1px solid var(--border-color); border-top: none; border-radius: 0 0 8px 8px; max-height: 250px; overflow-y: auto; z-index: 1000; display: none; }
        .suggestions-list div { padding: 12px 15px; cursor: pointer; transition: background-color 0.2s; }
        .suggestions-list div:hover { background-color: var(--primary-color); color: #fff; }
        .compare-button-container { text-align: center; }
        #compare-btn { padding: 0.8rem 2.5rem; font-size: 1.1rem; cursor: pointer; background-color: var(--primary-color); color: white; border: none; border-radius: 50px; transition: var(--transition); font-weight: 600; }
        #compare-btn:hover { background-color: #fff; color: #000; transform: translateY(-2px); }
        #compare-btn:disabled { background-color: #555; color: #888; cursor: not-allowed; }
        .comparison-display { margin-top: 3rem; min-height: 300px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .initial-message, .error-message { text-align: center; color: var(--text-secondary); font-size: 1.2rem; padding: 4rem; }
        .error-message { color: var(--primary-color); }
        .comparison-header { width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; opacity: 0; transform: translateY(20px); transition: opacity 0.5s ease, transform 0.5s ease; }
        .comparison-header.visible { opacity: 1; transform: translateY(0); }
        .phone-summary-card { display:flex; flex-direction: column; align-items: center; text-align: center; padding: 2rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); background-color: var(--card-bg); box-shadow: var(--shadow); }
        .phone-summary-card img { max-height: 220px; width: auto; max-width: 100%; object-fit: contain; margin-bottom: 1.5rem; background-color: #fff; padding: 1rem; border-radius: 8px;}
        .phone-summary-card h3 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .phone-summary-card .price { font-size: 1.5rem; color: var(--primary-color); font-weight: 700; }
        .rating-display { margin-top: 1rem; border: 1px solid var(--border-color); background-color: rgba(0,0,0,0.2); padding: 0.6rem 1rem; border-radius: 8px; text-align: center;}
        .rating-display span { font-size: 1.5rem; font-weight: 700; color: #fff; }
        .rating-display .fa-star { color: var(--accent-color, #f39c12); }
        .rating-display small { font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; display: block; }
        .compare-actions { display: flex; justify-content: flex-end; align-items: center; margin-bottom: 1.5rem; gap: 10px; font-weight: 500;}
        .toggle-switch { position: relative; display: inline-block; width: 50px; height: 28px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #333; border-radius: 34px; transition: .4s; }
        .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; border-radius: 50%; transition: .4s; }
        input:checked + .slider { background-color: var(--primary-color); }
        input:checked + .slider:before { transform: translateX(22px); }
        .spec-details-grid { width: 100%; display: grid; grid-template-columns: 1fr; gap: 2rem; opacity: 0; transform: translateY(20px); transition: opacity 0.5s ease 0.2s, transform 0.5s ease 0.2s;}
        .spec-details-grid.visible { opacity: 1; transform: translateY(0); }
        .spec-category-card { background-color: var(--card-bg); padding: 2rem; border-radius: var(--border-radius); border: 1px solid var(--border-color); }
        .spec-category-card h3 { display: flex; align-items: center; gap: 10px; font-size: 1.4rem; color: var(--text-primary); margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;}
        .spec-category-card h3 i { color: var(--primary-color); }
        .specs-table { width: 100%; border-collapse: separate; border-spacing: 0 1rem; }
        .specs-table td { padding: 1rem 0.5rem; vertical-align: middle; }
        .specs-table td:first-child { font-weight: 600; color: var(--text-secondary); width: 25%; }
        .specs-table tr.highlighted { background-color: rgba(231, 76, 60, 0.05); }
        .specs-table tr.highlighted td:first-child { border-left: 3px solid var(--primary-color); padding-left: 10px; }
        .video-button { background: transparent; color: var(--primary-color); border: 1px solid var(--primary-color); padding: 0.5rem 1rem; border-radius: 20px; cursor: pointer; transition: var(--transition); display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 500; }
        .video-button:hover { background: var(--primary-color); color: #fff; }
        .video-button.disabled { color: var(--text-secondary); border-color: var(--border-color); background: transparent; pointer-events: none; }
        #toast-container { position: fixed; bottom: 2rem; right: 2rem; z-index: 1050; display: flex; flex-direction: column; gap: 0.5rem; }
        .toast { background-color: var(--card-bg); color: var(--text-primary); padding: 1rem 1.5rem; border-radius: 8px; border-left: 5px solid var(--success-color); box-shadow: var(--shadow); display: flex; align-items: center; gap: 1rem; transform: translateX(calc(100% + 2rem)); transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .toast.show { transform: translateX(0); }
        .toast.error { border-left-color: var(--primary-color); }
        .toast .toast-icon { font-size: 1.2rem; }
        .toast.error .toast-icon { color: var(--primary-color); }
    </style>
</head>
<body>
    <nav class="floating-navbar" id="mainNav">
        <a href="shome.php" class="logo">
            <i class="fas fa-mobile-alt logo-icon"></i>
            <span class="logo-text">Gadget Guru</span>
        </a>
        <div class="nav-links">
            <a href="shome.php" class="nav-icon-link" title="Home"><i class="fas fa-home"></i></a>
            <a href="w.php" class="nav-icon-link" title="My Wishlist"><i class="fas fa-heart"></i></a>
        </div>
    </nav>

    <div class="comparison-container">
        <!-- Selection Hub and other HTML remains unchanged -->
        <div class="selection-hub"> <h1>Compare Smartphones</h1> <div class="selection-area"> <div class="phone-selector"> <h3><i class="fas fa-mobile-alt"></i> Phone 1</h3> <input type="text" id="phone-search-1" class="search-input" placeholder="e.g., iPhone 15 Pro" autocomplete="off"> <div id="suggestions-1" class="suggestions-list"></div> </div> <div class="phone-selector"> <h3><i class="fas fa-mobile-alt"></i> Phone 2</h3> <input type="text" id="phone-search-2" class="search-input" placeholder="e.g., Galaxy S24 Ultra" autocomplete="off"> <div id="suggestions-2" class="suggestions-list"></div> </div> </div> <div class="compare-button-container"> <button id="compare-btn"><i class="fas fa-exchange-alt"></i> Compare Now</button> </div> </div>
        <div class="comparison-display" id="comparison-display">
            <p class="initial-message">Select two phones from the hub above to view a detailed comparison.</p>
        </div>
    </div>
    
    <div id="toast-container"></div>
      <?php require_once 'footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const nav = document.getElementById('mainNav');
    if(nav) { window.addEventListener('scroll', () => { nav.classList.toggle('scrolled', window.scrollY > 30); }); }
    let selectedPhone1 = null; let selectedPhone2 = null;
    const searchInput1 = document.getElementById('phone-search-1');
    const suggestions1 = document.getElementById('suggestions-1');
    const searchInput2 = document.getElementById('phone-search-2');
    const suggestions2 = document.getElementById('suggestions-2');
    const compareBtn = document.getElementById('compare-btn');
    const comparisonDisplay = document.getElementById('comparison-display');

    function showToast(message, type = 'error') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        toast.innerHTML = `<i class="toast-icon ${iconClass}"></i><span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => { toast.classList.remove('show'); toast.addEventListener('transitionend', () => toast.remove()); }, 4000);
    }
    
    // Unchanged functions for searching and displaying suggestions...
    function debounce(func, delay) { let timeout; return function(...args) { clearTimeout(timeout); timeout = setTimeout(() => func.apply(this, args), delay); }; }
    const handleSearchInput = async (event, suggestionBox, phoneIndex) => { const query = event.target.value; if (query.length < 2) { suggestionBox.style.display = 'none'; if (phoneIndex === 1) selectedPhone1 = null; if (phoneIndex === 2) selectedPhone2 = null; return; } try { const response = await fetch(`phone_suggestions.php?query=${encodeURIComponent(query)}`); const suggestions = await response.json(); displaySuggestions(suggestions, suggestionBox, phoneIndex); } catch (error) { console.error('Error fetching suggestions:', error); } };
    function displaySuggestions(suggestions, suggestionBox, phoneIndex) { suggestionBox.innerHTML = ''; if (suggestions.length === 0) { suggestionBox.innerHTML = `<div class="no-results">No results found</div>`; } else { suggestions.forEach(phone => { const suggestionItem = document.createElement('div'); suggestionItem.textContent = `${phone.brand_name} ${phone.model_name}`; suggestionItem.addEventListener('click', () => { const selection = { brand: phone.brand_name, model: phone.model_name }; if (phoneIndex === 1) { selectedPhone1 = selection; searchInput1.value = suggestionItem.textContent; } else { selectedPhone2 = selection; searchInput2.value = suggestionItem.textContent; } suggestionBox.style.display = 'none'; }); suggestionBox.appendChild(suggestionItem); }); } suggestionBox.style.display = 'block'; }
    searchInput1.addEventListener('input', debounce((e) => handleSearchInput(e, suggestions1, 1), 300));
    searchInput2.addEventListener('input', debounce((e) => handleSearchInput(e, suggestions2, 2), 300));
    document.addEventListener('click', (e) => { if (!e.target.closest('.phone-selector')) { suggestions1.style.display = 'none'; suggestions2.style.display = 'none'; } });

    async function fetchAndDisplayComparison(params) {
        let fetchURL = '';
        if (params.id1 && params.id2) { fetchURL = `fetch_comparison.php?id1=${params.id1}&id2=${params.id2}`; } 
        else if (params.phone1 && params.phone2) { fetchURL = `fetch_comparison.php?brand1=${encodeURIComponent(params.phone1.brand)}&model1=${encodeURIComponent(params.phone1.model)}&brand2=${encodeURIComponent(params.phone2.brand)}&model2=${encodeURIComponent(params.phone2.model)}`; } 
        else { showToast('Please select two phones to compare.', 'error'); return; }

        comparisonDisplay.innerHTML = `<p class="initial-message">Fetching comparison...</p>`;
        compareBtn.disabled = true;
        try {
            const response = await fetch(fetchURL);
            const result = await response.json();
            if (result.status === 'error') throw new Error(result.message);
            if (searchInput1.value === '' && result.data.phone1) { searchInput1.value = `${result.data.phone1.brand_name} ${result.data.phone1.model_name}`; selectedPhone1 = { brand: result.data.phone1.brand_name, model: result.data.phone1.model_name }; }
            if (searchInput2.value === '' && result.data.phone2) { searchInput2.value = `${result.data.phone2.brand_name} ${result.data.phone2.model_name}`; selectedPhone2 = { brand: result.data.phone2.brand_name, model: result.data.phone2.model_name }; }
            updateComparisonUI(result.data.phone1, result.data.phone2);
        } catch (error) { console.error('Error fetching comparison data:', error); comparisonDisplay.innerHTML = `<div class="error-message">Error: ${error.message}. Please try again.</div>`; } 
        finally { compareBtn.disabled = false; }
    }
    
    function formatFeatureName(key) { return key.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase()); }
    function calculateRating(phone) {
    const weights = { battery_mah: 20, rear_camera: 20, release_year: 20, ram_gb: 15, os: 10, refresh_rate_hz: 10, storage_gb: 5 };
    const parseValue = (str) => parseFloat(String(str).match(/[\d\.]+/)?.[0] || '0');
    const normalize = (value, min, max) => { if (value <= min) return 0; if (value >= max) return 10; return ((value - min) / (max - min)) * 10; };
    const scoreOS = (osString) => { if (!osString || typeof osString !== 'string') return 5; const os = osString.toLowerCase(); const version = parseValue(os); if (os.includes('android')) { if (version >= 14) return 10; if (version >= 13) return 8; if (version >= 12) return 6; return 4; } else if (os.includes('ios')) { if (version >= 17) return 10; if (version >= 16) return 8; if (version >= 15) return 6; return 4; } return 5; };
    
    // --- CORRECTED PART ---
    // Using the new database column names like 'ram_gb', 'battery_mah', etc.
    const scores = { 
        ram_gb: normalize(parseValue(phone.ram_gb), 4, 16), 
        battery_mah: normalize(parseValue(phone.battery_mah), 3500, 6000), 
        refresh_rate_hz: normalize(parseValue(phone.refresh_rate_hz), 60, 165), 
        storage_gb: normalize(parseValue(phone.storage_gb), 64, 512), 
        release_year: normalize(new Date(phone.release_date).getFullYear(), new Date().getFullYear() - 3, new Date().getFullYear()), 
        rear_camera: normalize(parseValue(phone.rear_camera), 12, 200), 
        os: scoreOS(phone.os) 
    };
    
    let totalScore = 0;
    let totalWeight = 0;
    for (const key in weights) {
        if (scores[key] !== undefined && scores[key] > 0) {
            totalScore += scores[key] * weights[key];
            totalWeight += weights[key];
        }
    }
    if (totalWeight === 0) return 0;
    return (totalScore / totalWeight).toFixed(1);
}

function convertEmbedToWatchLink(embedUrl) {
    if (typeof embedUrl !== 'string' || !embedUrl.includes('/embed/')) {
        return embedUrl; // Return original if it's not a valid embed link
    }
    return embedUrl.replace('/embed/', '/watch?v=');
}

    function updateComparisonUI(phone1, phone2) {
    const rating1 = calculateRating(phone1);
    const rating2 = calculateRating(phone2);
    const getRatingHTML = (rating) => `<div class="rating-display"><i class="fas fa-star"></i><span>${rating}</span><small>/ 10 Guru Score</small></div>`;
    
    comparisonDisplay.innerHTML = `
        <div class="comparison-header">
            <div class="phone-summary-card">
                <img src="${phone1.image_url || 'placeholder.jpg'}" alt="${phone1.model_name}">
                <h3>${phone1.brand_name} ${phone1.model_name}</h3>
                <p class="price">₹${parseFloat(phone1.price).toLocaleString('en-IN')}</p>
                ${getRatingHTML(rating1)}
            </div>
            <div class="phone-summary-card">
                <img src="${phone2.image_url || 'placeholder.jpg'}" alt="${phone2.model_name}">
                <h3>${phone2.brand_name} ${phone2.model_name}</h3>
                <p class="price">₹${parseFloat(phone2.price).toLocaleString('en-IN')}</p>
                ${getRatingHTML(rating2)}
            </div>
        </div>
        <div class="compare-actions">
            <span>Highlight Differences</span>
            <label class="toggle-switch">
                <input type="checkbox" id="highlight-diff-new">
                <span class="slider"></span>
            </label>
        </div>
        <div class="spec-details-grid" id="spec-grid"></div>`;
    
    const specGrid = document.getElementById('spec-grid');
    
    // --- CORRECTED PART ---
    // Using the new database column names in the 'keys' array for each category
    const categoryMap = {
        'Performance': { icon: 'fa-microchip', keys: ['processor', 'chipset', 'ram_gb', 'storage_gb'] },
        'Display': { icon: 'fa-mobile-screen-button', keys: ['display_type', 'screen_size_in', 'resolution', 'refresh_rate_hz'] },
        'Camera': { icon: 'fa-camera-retro', keys: ['rear_camera', 'front_camera'] },
        'Battery': { icon: 'fa-battery-full', keys: ['battery_mah', 'fast_charging_w'] },
        'Software': { icon: 'fa-code', keys: ['os', 'ui', 'os_update'] },
        'Body': { icon: 'fa-ruler-combined', keys: ['height_mm', 'width_mm', 'thickness_mm', 'weight_g'] },
        'Features': { icon: 'fa-star', keys: ['network_support', 'sim_slot', 'sim_type', 'fingerprint_sensor', 'youtube_link'] }
    };
    
    for (const category in categoryMap) {
        const card = document.createElement('div');
        card.className = 'spec-category-card';
        let tableHTML = `<h3><i class="fas ${categoryMap[category].icon}"></i> ${category}</h3><table class="specs-table"><tbody>`;
        
        categoryMap[category].keys.forEach(key => {
            let value1 = phone1[key] ?? 'N/A';
            let value2 = phone2[key] ?? 'N/A';
            if (key === 'youtube_link') {
    // Convert embed URLs to watchable URLs first
    const watchLink1 = convertEmbedToWatchLink(value1);
    const watchLink2 = convertEmbedToWatchLink(value2);
    
    // Now create the buttons using the corrected links
    value1 = value1 !== 'N/A' && value1 ? `<a href="${watchLink1}" target="_blank" rel="noopener noreferrer" class="video-button"><i class="fab fa-youtube"></i> Watch</a>` : `<button class="video-button disabled" disabled>N/A</button>`;
    value2 = value2 !== 'N/A' && value2 ? `<a href="${watchLink2}" target="_blank" rel="noopener noreferrer" class="video-button"><i class="fab fa-youtube"></i> Watch</a>` : `<button class="video-button disabled" disabled>N/A</button>`;
}
            const isDifferent = String(phone1[key] ?? '').trim() !== String(phone2[key] ?? '').trim();
            tableHTML += `<tr data-diff="${isDifferent}"><td>${formatFeatureName(key)}</td><td>${value1}</td><td>${value2}</td></tr>`;
        });
        
        tableHTML += `</tbody></table>`;
        card.innerHTML = tableHTML;
        specGrid.appendChild(card);
    }
    
    document.getElementById('highlight-diff-new')?.addEventListener('change', toggleHighlight);
    setTimeout(() => {
        document.querySelector('.comparison-header')?.classList.add('visible');
        specGrid.classList.add('visible');
    }, 100);
}
    function toggleHighlight() { const isChecked = document.getElementById('highlight-diff-new')?.checked; document.querySelectorAll('.specs-table tr[data-diff]').forEach(row => { const isDifferent = row.getAttribute('data-diff') === 'true'; row.classList.toggle('highlighted', isChecked && isDifferent); }); }
    
    // --- *** MODIFICATION: Added logic to prevent self-comparison *** ---
    compareBtn.addEventListener('click', () => {
        // First, check if both phones are selected
        if (!selectedPhone1 || !selectedPhone2) {
            showToast('Please select two phones to compare.', 'error');
            return;
        }

        // Second, check if they are the same phone
        if (selectedPhone1.brand === selectedPhone2.brand && selectedPhone1.model === selectedPhone2.model) {
            showToast('Please select two different phones to compare.', 'error');
            return; // Stop the function here
        }

        // If checks pass, proceed with fetching data
        fetchAndDisplayComparison({ phone1: selectedPhone1, phone2: selectedPhone2 });
    });
    
    // Auto-comparison from URL (Unchanged)
    const urlParams = new URLSearchParams(window.location.search);
    const id1 = urlParams.get('id1');
    const id2 = urlParams.get('id2');
    if (id1 && id2) { fetchAndDisplayComparison({ id1: id1, id2: id2 }); }
});
</script>

</body>
</html>