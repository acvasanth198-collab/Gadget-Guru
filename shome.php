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

// User session data
$username = $_SESSION['username'] ?? "Guest";
$email = $_SESSION['email'] ?? "";
$role = $_SESSION['role'] ?? "guest";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gadget Guru - Find Your Perfect Smartphone</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- DARK THEME & GLOBAL STYLES --- */
        :root {
            --primary-color: #e74c3c;
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --text-primary: #ffffff;
            --text-secondary: #a0a0a0;
            --border-color: #333333;
            --font-family-body: 'Poppins', sans-serif;
            --font-family-headings: 'Roboto', sans-serif;
            --border-radius: 12px;
            --shadow: 0 8px 30px rgba(0,0,0,0.5);
            --transition: all 0.3s ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font-family-body); background-color: var(--bg-color); color: var(--text-primary); }

        /* --- FLOATING GLASSMORPHISM NAVBAR --- */
        .floating-navbar {
            position: fixed; top: 20px; left: 50%; width: 95%; max-width: 900px; z-index: 1000;
            background: rgba(20, 20, 20, 0.6); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            border-radius: 50px; border: 1px solid rgba(255, 255, 255, 0.1); padding: 0.6rem 1.5rem;
            display: grid; grid-template-columns: auto 1fr auto; align-items: center; gap: 1.5rem;
            opacity: 0; transform: translate(-50%, -30px); animation: slideDown 0.6s 0.2s ease-out forwards;
            transition: box-shadow 0.3s ease;
        }
        .floating-navbar.scrolled { box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37); }
        @keyframes slideDown { to { opacity: 1; transform: translate(-50%, 0); } }
        .floating-navbar .logo { display: flex; align-items: center; gap: 0.7rem; text-decoration: none; color: #fff; }
        .floating-navbar .logo-icon { font-size: 1.5rem; color: var(--primary-color); }
        .floating-navbar .logo-text { font-size: 1.2rem; font-weight: 700; }
        .search-container { position: relative; }
        .search-container .fa-search { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); }
        .search-bar { width: 100%; background: rgba(255, 255, 255, 0.1); border: 1px solid transparent; border-radius: 20px; padding: 0.6rem 1rem 0.6rem 40px; color: #fff; font-family: var(--font-family-body); transition: var(--transition); }
        .search-bar::placeholder { color: var(--text-secondary); }
        .search-bar:focus { background: rgba(255, 255, 255, 0.15); border-color: rgba(255, 255, 255, 0.2); outline: none; }
        .nav-right { display: flex; align-items: center; gap: 0.5rem; }
        .nav-icon-link { color: var(--text-secondary); text-decoration: none; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.1); transition: var(--transition); cursor: pointer; }
        .nav-icon-link:hover { color: #fff; background: var(--primary-color); transform: scale(1.1); }
        
        /* --- MAIN PAGE LAYOUT --- */
        .page-wrapper { display: flex; flex-direction: column; min-height: 100vh; }
        .content { flex-grow: 1; } /* Ensures content pushes footer down */
        .container { max-width: 1400px; width: 100%; margin: 160px auto 160px auto; padding: 0 2rem; }
        
        /* --- HERO SECTION --- */
        .hero-section { text-align: center; padding: 3rem 1rem; margin-bottom: 3rem; }
        .hero-section h1 { font-size: 3.5rem; font-weight: 900; font-family: var(--font-family-headings); margin-bottom: 1rem; }
        .hero-section h1 span { color: var(--primary-color); }
        .hero-section p { font-size: 1.2rem; color: var(--text-secondary); max-width: 600px; margin: 0 auto; }
        
        /* --- QUICK ACTIONS GRID --- */
        .actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 4rem; }
        .action-card { background: var(--card-bg); border: 1px solid var(--border-color); padding: 1.5rem; border-radius: var(--border-radius); text-align: center; text-decoration: none; color: var(--text-primary); transition: var(--transition); }
        .action-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.3); border-color: #444; }
        .action-card i { font-size: 2rem; color: var(--primary-color); margin-bottom: 1rem; display: block; }
        .action-card h3 { font-size: 1.2rem; margin-bottom: 0.25rem; }
        .action-card p { font-size: 0.9rem; color: var(--text-secondary); }
        #allFiltersBtn { cursor: pointer; }
        
        /* --- SMARTPHONES CONTENT --- */
        .section-title { font-size: 2.2rem; font-weight: 700; margin-bottom: 2rem; border-left: 4px solid var(--primary-color); padding-left: 1rem; }
        .featured-phones { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.8rem; }
        
        /* --- PHONE CARD STYLES --- */
        .phone-card { background-color: var(--card-bg); border-radius: var(--border-radius); overflow: hidden; border: 1px solid var(--border-color); position: relative; transition: var(--transition); }
        .phone-card:hover { transform: translateY(-8px); box-shadow: var(--shadow); border-color: #444;}
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
        
        .loader-container { grid-column: 1 / -1; display: flex; justify-content: center; padding: 5rem; }
        .loader { width: 50px; height: 50px; border: 5px solid var(--border-color); border-top: 5px solid var(--primary-color); border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .no-results { grid-column: 1 / -1; text-align: center; color: var(--text-secondary); font-size: 1.2rem; padding: 4rem; background-color: var(--card-bg); border-radius: var(--border-radius);}


    /* --- PAGINATION STYLES (NEW MINIMALIST THEME) --- */
#paginationContainer {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2.5rem 0 1rem 0;
    gap: 1.25rem; /* Increased spacing between items */
    font-family: var(--font-family-body);
    user-select: none;
}

#paginationContainer .pagination-item {
    color: var(--text-secondary); /* Dim color for inactive items */
    text-decoration: none;
    font-size: 1rem;
    font-weight: 500;
    padding: 0.25rem;
    transition: color 0.3s ease;
    cursor: pointer;
}

#paginationContainer .pagination-item:hover {
    color: var(--text-primary); /* Brighten on hover */
}

#paginationContainer .pagination-item.active {
    color: var(--text-primary); /* Bright white for the active page */
    font-weight: 700;
    pointer-events: none; /* Make the active page not clickable */
    border-bottom: 2px solid var(--primary-color); /* The red underline */
}

#paginationContainer .pagination-item.disabled {
    color: #444; /* Even dimmer color for disabled arrows */
    cursor: not-allowed;
    pointer-events: none;
}

#paginationContainer .pagination-dots {
    color: var(--text-secondary);
    cursor: default;
}

    </style>

    
</head>
<body>
<div class="page-wrapper">
    <nav class="floating-navbar" id="mainNav">
        <a href="shome.php" class="logo">
            <i class="fas fa-mobile-alt logo-icon"></i>
            <span class="logo-text">Gadget Guru</span>
        </a>
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchBar" class="search-bar" placeholder="Search Mobile...">
        </div>
        <div class="nav-right">
            <a href="compare.php" class="nav-icon-link" title="Compare"><i class="fas fa-exchange-alt"></i></a>
            <div class="user-section nav-icon-link" title="Profile"><i class="fas fa-user"></i></div>
        </div>
    </nav>

    <div class="container">
        <main class="content">
            <section class="hero-section">
                <h1>Find Your Next <span>Smartphone</span></h1>
                <p>Explore our curated lists, compare detailed specs, and use our powerful filters to discover the perfect phone for your needs.</p>
            </section>
            
            <section class="actions-grid">
                <a href="trp.php" class="action-card"><i class="fas fa-star"></i><h3>Top Rated</h3><p>Explore the best of the best</p></a>
                <a href="bvp.php" class="action-card"><i class="fas fa-dollar-sign"></i><h3>Best Value</h3><p>Get the most bang for your buck</p></a>
                <a href="bcp.php" class="action-card"><i class="fas fa-camera"></i><h3>Camera Kings</h3><p>Find phones with stunning cameras</p></a>
                <div class="action-card" id="allFiltersBtn"><i class="fas fa-sliders-h"></i><h3>All Filters</h3><p>Customize your perfect search</p></div>
            </section>

            <section>
                <h2 class="section-title">All Smartphones</h2>
                <?php require_once 'filters.php'; ?>
                <div class="featured-phones" id="phoneContainer"></div>
                <div id="paginationContainer"></div>
            </section>
        </main>
    </div>

    <?php require_once 'sidebars.php'; ?>

    <?php include 'footer.php'; ?>
    
    
</div>

<script>

    // ADD THESE THREE LINES AT THE TOP
    let allFetchedPhones = []; // This will store all phones from the server
    let currentPage = 1;
    const PHONES_PER_PAGE = 20;



    // REPLACE THE OLD FUNCTION WITH THIS NEW VERSION
function fetchPhones(filters = null) {
    const container = document.getElementById('phoneContainer');
    container.innerHTML = '<div class="loader-container"><div class="loader"></div></div>';
    document.getElementById('paginationContainer').innerHTML = ''; // Also clear pagination

    const fetchOptions = {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(filters || {})
    };
    fetch('filter.php', fetchOptions)
        .then(response => response.ok ? response.json() : Promise.reject('Network error'))
        .then(data => {
            allFetchedPhones = data; // Store the full list of phones
            currentPage = 1;         // Reset to the first page
            displayPage();           // Render the first page and pagination
        })
        .catch(error => { 
            console.error('Error fetching phones:', error); 
            container.innerHTML = '<div class="no-results">Could not load phone data. Please try again later.</div>'; 
        });
}


    // ... after the fetchPhones function ...

// ADD THIS NEW FUNCTION
function displayPage() {
    const startIndex = (currentPage - 1) * PHONES_PER_PAGE;
    const endIndex = startIndex + PHONES_PER_PAGE;
    const phonesForCurrentPage = allFetchedPhones.slice(startIndex, endIndex);

    renderPhones(phonesForCurrentPage); // Your existing function renders the cards
    renderPagination();                 // This new function builds the page links
    window.scrollTo({ top: 0, behavior: 'smooth' }); // Scroll to top on page change
}

// REPLACE your old renderPagination function with this new one.

function renderPagination() {
    const paginationContainer = document.getElementById('paginationContainer');
    paginationContainer.innerHTML = ''; // Clear previous pagination
    const totalPages = Math.ceil(allFetchedPhones.length / PHONES_PER_PAGE);

    if (totalPages <= 1) return; // Don't show anything if there's only one page

    let paginationHTML = '';

    // Helper to create a page link
    const createPageLink = (page, text = page, extraClass = '') => {
        const isActive = (page === currentPage);
        // Add the 'active' class if it's the current page, or use other classes like 'disabled'
        const finalClass = `pagination-item ${isActive ? 'active' : ''} ${extraClass}`;
        
        // Return a span for active/disabled items, otherwise an anchor tag
        if (isActive || extraClass.includes('disabled')) {
            return `<span class="${finalClass}">${text}</span>`;
        } else {
            // Use onclick to call the displayPage function
            return `<a class="${finalClass}" onclick="currentPage=${page}; displayPage();">${text}</a>`;
        }
    };

    // Previous Arrow
    paginationHTML += createPageLink(currentPage - 1, '&larr;', currentPage === 1 ? 'disabled' : '');

    // Page Numbers Logic: Show first, last, and a few pages around the current one
    if (totalPages <= 7) {
        // If 7 or fewer pages, show all of them
        for (let i = 1; i <= totalPages; i++) {
            paginationHTML += createPageLink(i);
        }
    } else {
        // More than 7 pages, use ellipsis logic
        paginationHTML += createPageLink(1); // Always show page 1
        if (currentPage > 3) {
            paginationHTML += '<span class="pagination-dots">...</span>'; // Ellipsis
        }
        
        let start = Math.max(2, currentPage - 1);
        let end = Math.min(totalPages - 1, currentPage + 1);

        for (let i = start; i <= end; i++) {
            paginationHTML += createPageLink(i);
        }

        if (currentPage < totalPages - 2) {
            paginationHTML += '<span class="pagination-dots">...</span>'; // Ellipsis
        }
        paginationHTML += createPageLink(totalPages); // Always show last page
    }

    // Next Arrow
    paginationHTML += createPageLink(currentPage + 1, '&rarr;', currentPage === totalPages ? 'disabled' : '');

    paginationContainer.innerHTML = paginationHTML;
}


    
    function renderPhones(phonesToRender) {
        const container = document.getElementById('phoneContainer');
        container.innerHTML = '';
        if (!phonesToRender || phonesToRender.length === 0) { container.innerHTML = '<div class="no-results">No phones found matching your criteria.</div>'; return; }
        
        phonesToRender.forEach(phone => {
            const card = document.createElement('div');
            card.className = 'phone-card';
            const priceFormatted = new Intl.NumberFormat('en-IN').format(phone.price);
            const phoneFullName = `${phone.brand_name || ''} ${phone.model_name || ''}`;
            
            card.innerHTML = `
                <div class="phone-image" onclick="location.href='detail.php?phone_id=${phone.phone_id}'">
                    <img src="${phone.links?.image || 'placeholder.jpg'}" alt="${phoneFullName}">
                </div>
                <div class="phone-details">
                    <div class="phone-model">${phoneFullName}</div>
                    <div class="phone-specs">
                        <div class="spec-item"><i class="fas fa-microchip"></i><span>${phone.specs?.processor || 'N/A'}</span></div>
                        <div class="spec-item"><i class="fas fa-memory"></i><span>${phone.specs?.ram || 'N/A'} GB RAM</span></div>
                        <div class="spec-item"><i class="fas fa-mobile-alt"></i><span>${phone.specs?.screen_size || 'N/A'}"</span></div>
                    </div>
                    <div class="phone-footer">
                        <span class="price">₹${priceFormatted}</span>
                        <button class="view-button" onclick="location.href='detail.php?phone_id=${phone.phone_id}'">Details</button>
                    </div>
                </div>`;
            container.appendChild(card);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetchPhones(); // Initial load of all phones
        const nav = document.getElementById('mainNav');
        if (nav) {
            window.addEventListener('scroll', () => {
                nav.classList.toggle('scrolled', window.scrollY > 30);
            });
        }
    });
</script>
</body>
</html>