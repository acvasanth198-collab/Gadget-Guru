<?php
session_start();
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$username = $_SESSION['username'] ?? "Guest";
$email = $_SESSION['email'] ?? "";
$role = $_SESSION['role'] ?? "guest";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gadget Guru - Best Camera Phones</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- STYLES FOR CONSISTENT UI --- */
        :root { --primary-color: #e74c3c; --bg-color: #121212; --card-bg: #1e1e1e; --text-primary: #ffffff; --text-secondary: #a0a0a0; --border-color: #333333; --font-family-body: 'Poppins', sans-serif; --font-family-headings: 'Roboto', sans-serif; --border-radius: 12px; --shadow: 0 8px 30px rgba(0,0,0,0.5); --transition: all 0.3s ease; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font-family-body); background-color: var(--bg-color); color: var(--text-primary); }
        .floating-navbar {
            position: fixed; top: 20px; left: 50%; width: 95%; max-width: 750px; 
            z-index: 1000; background: rgba(20, 20, 20, 0.6); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border-radius: 50px; border: 1px solid rgba(255, 255, 255, 0.1); padding: 0.6rem 1.5rem; display: grid; grid-template-columns: auto 1fr auto; align-items: center; gap: 1.5rem; opacity: 0; transform: translate(-50%, -30px); animation: slideDown 0.6s 0.2s ease-out forwards; transition: box-shadow 0.3s ease;
        }
        .floating-navbar.scrolled { box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37); }
        @keyframes slideDown { to { opacity: 1; transform: translate(-50%, 0); } }
        .floating-navbar .logo { display: flex; align-items: center; gap: 0.7rem; text-decoration: none; color: #fff; }
        .floating-navbar .logo-icon { font-size: 1.5rem; color: var(--primary-color); }
        .floating-navbar .logo-text { font-size: 1.2rem; font-weight: 700; }
        .search-container { position: relative; }
        .search-container .fa-search { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); }
        .search-bar { width: 100%; background: rgba(255, 255, 255, 0.1); border: 1px solid transparent; border-radius: 20px; padding: 0.6rem 1rem 0.6rem 40px; color: #fff; font-family: var(--font-family-body); transition: var(--transition); }
        .search-bar:focus { background: rgba(255, 255, 255, 0.15); border-color: rgba(255, 255, 255, 0.2); outline: none; }
        .nav-right { display: flex; align-items: center; gap: 0.5rem; }
        .nav-icon-link { color: var(--text-secondary); text-decoration: none; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.1); transition: var(--transition); cursor: pointer; }
        .nav-icon-link:hover { color: #fff; background: var(--primary-color); transform: scale(1.1); }
        .page-wrapper { display: flex; flex-direction: column; min-height: 100vh; }
        .content { flex-grow: 1; }
        .container { max-width: 1400px; width: 100%; margin: 160px auto 160px auto; padding: 0 2rem; }
        .hero-section { text-align: center; padding: 3rem 1rem; margin-bottom: 3rem; }
        .hero-section h1 { font-size: 3.5rem; font-weight: 900; font-family: var(--font-family-headings); margin-bottom: 1rem; }
        .hero-section h1 span { color: var(--primary-color); }
        .hero-section p { font-size: 1.2rem; color: var(--text-secondary); max-width: 600px; margin: 0 auto; }
        .section-title { font-size: 2.2rem; font-weight: 700; margin-bottom: 2rem; border-left: 4px solid var(--primary-color); padding-left: 1rem; }
        .featured-phones { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.8rem; }
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
            <input type="text" id="searchBar" class="search-bar" placeholder="Search Camera Phones...">
        </div>
        <div class="nav-right">
            <a href="shome.php" class="nav-icon-link" title="Home"><i class="fas fa-home"></i></a>
        </div>
    </nav>

    <div class="container">
        <main class="content">
            <section class="hero-section">
                <h1>Best <span>Camera</span> Phones</h1>
                <p>Discover our curated list of smartphones with the most stunning cameras for capturing your perfect moments.</p>
            </section>
            
            <section>
                <h2 class="section-title">Camera Kings</h2>
                <div class="featured-phones" id="phoneContainer"></div>
            </section>
        </main>
    </div>

    <?php require_once 'footer.php'; ?>

</div>
<script>
    let pageSpecificPhones = [];
    function debounce(func, delay) { let timeout; return function(...args) { clearTimeout(timeout); timeout = setTimeout(() => func.apply(this, args), delay); }; }
    function fetchBestCameraPhones() {
        const container = document.getElementById('phoneContainer');
        container.innerHTML = '<div class="loader-container"><div class="loader"></div></div>';
        fetch('get_best_camera_phones.php') 
            .then(response => { if (!response.ok) throw new Error('Network response was not ok'); return response.json(); })
            .then(data => {
                pageSpecificPhones = data;
                renderPhones(pageSpecificPhones);
            })
            .catch(error => { console.error('Error fetching best camera phones:', error); container.innerHTML = '<div class="no-results">Could not load camera phone data. Please try again later.</div>'; });
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
                        <div class="spec-item"><i class="fas fa-camera"></i><span>${phone.features?.rear_camera || 'N/A'}</span></div>
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
        fetchBestCameraPhones(); 
        const nav = document.getElementById('mainNav');
        if (nav) { window.addEventListener('scroll', () => { nav.classList.toggle('scrolled', window.scrollY > 30); }); }
        const searchBar = document.getElementById('searchBar');
        if(searchBar) {
            searchBar.addEventListener('input', debounce((event) => {
                const query = event.target.value.toLowerCase().trim();
                if (!query) {
                    renderPhones(pageSpecificPhones);
                    return;
                }
                const filteredPhones = pageSpecificPhones.filter(phone => {
                    const fullName = `${phone.brand_name || ''} ${phone.model_name || ''}`.toLowerCase();
                    return fullName.includes(query);
                });
                renderPhones(filteredPhones);
            }, 300));
        }
    });
    window.addEventListener('pageshow', function(event) { if (event.persisted) { window.location.reload(); } });
</script>
</body>
</html>