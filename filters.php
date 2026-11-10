<?php
// --- DYNAMIC FILTERS PHP ---

// --- 1. SETUP & DATABASE CONNECTION ---
// Include the central database connection file (connects to 'gguru')
require_once 'config.php';
$conn = $GLOBALS['conn']; // Use the global connection object

// Initialize filter variables
$brands = []; $rams = []; $storages = []; $operating_systems = []; $processors = []; 
$price_range = ['min' => 0, 'max' => 150000]; // Default range as a fallback

if (!$conn->connect_error) {
    // --- 2. SQL QUERIES (MODIFIED FOR 'smartphones' TABLE) ---
    
    // Brand Names
    $raw_brands = [];
    $brand_result = $conn->query("SELECT DISTINCT TRIM(brand_name) as brand_name FROM smartphones WHERE brand_name IS NOT NULL AND TRIM(brand_name) != ''");
    if ($brand_result) while($row = $brand_result->fetch_assoc()) $raw_brands[] = ucwords(strtolower($row['brand_name']));
    $brands = array_unique($raw_brands); sort($brands);
    
    // RAM (Targets 'ram_gb' column)
    $ram_result = $conn->query("SELECT DISTINCT ram_gb FROM smartphones WHERE ram_gb IS NOT NULL ORDER BY ram_gb ASC");
    if ($ram_result) while($row = $ram_result->fetch_assoc()) $rams[] = $row['ram_gb'];
    
    // Storage (Targets 'storage_gb' column)
    $storage_result = $conn->query("SELECT DISTINCT storage_gb FROM smartphones WHERE storage_gb IS NOT NULL ORDER BY storage_gb ASC");
    if ($storage_result) while($row = $storage_result->fetch_assoc()) $storages[] = $row['storage_gb'];
    
    // OS (Targets 'os' column)
    $os_result = $conn->query("SELECT DISTINCT SUBSTRING_INDEX(os, ' ', 1) AS os_name FROM smartphones WHERE os IS NOT NULL AND os != '' ORDER BY os_name ASC");
    if ($os_result) while($row = $os_result->fetch_assoc()) $operating_systems[] = $row['os_name'];
    
    // Processor (Targets 'processor' column)
    $proc_result = $conn->query("SELECT DISTINCT SUBSTRING_INDEX(processor, ' ', 1) as proc_name FROM smartphones WHERE processor IS NOT NULL AND processor != '' ORDER BY proc_name ASC");
    if ($proc_result) while($row = $proc_result->fetch_assoc()) $processors[] = $row['proc_name'];
    
    // Price Range (Targets 'price' column)
    $price_result = $conn->query("SELECT MIN(price) AS min_price, MAX(price) AS max_price FROM smartphones");
    if ($price_result && $price_result->num_rows > 0) { 
        $row = $price_result->fetch_assoc(); 
        $price_range['min'] = floor($row['min_price'] ?? 0); 
        $price_range['max'] = ceil($row['max_price'] ?? 150000); 
    }
    
    $conn->close();
}
?>

<!-- HTML and Styling for the Filter Sidebar -->
<style>
    /* STYLING FOR MODERN "APP-STYLE" FILTER SIDEBAR */
    :root {
        --text-primary: #f0f0f0; --text-secondary: #a0a0a0; --border-color-light: rgba(255, 255, 255, 0.1); --sidebar-bg: #181818; --sidebar-hover-bg: #282828; --sidebar-active-bg: #333333; --primary-color: #e74c3c;
    }
    .filter-sidebar { position: fixed; top: 0; left: 0; width: 340px; height: 100%; background: var(--sidebar-bg); border-right: 1px solid var(--border-color-light); z-index: 1200; transform: translateX(-100%); transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94); display: flex; flex-direction: column; }
    .filter-sidebar.active { transform: translateX(0); }
    .filter-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); opacity: 0; visibility: hidden; transition: opacity 0.4s ease, visibility 0s 0.4s; z-index: 1199; }
    .filter-overlay.active { opacity: 1; visibility: visible; transition: opacity 0.4s ease; }
    .filter-sidebar-header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 1rem 1.5rem 1.5rem; border-bottom: 1px solid var(--border-color-light); }
    .filter-sidebar-header h3 { font-size: 1.5rem; color: var(--text-primary); }
    .close-btn { background: var(--sidebar-hover-bg); border: 1px solid var(--border-color-light); color: var(--text-secondary); font-size: 1rem; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; }
    .close-btn:hover { color: #fff; background-color: var(--primary-color); border-color: var(--primary-color); transform: scale(1.1); }
    .filter-sidebar-content { flex-grow: 1; overflow-y: auto; padding: 1rem; }
    .filter-sidebar-content::-webkit-scrollbar { width: 6px; }
    .filter-sidebar-content::-webkit-scrollbar-track { background: var(--sidebar-bg); }
    .filter-sidebar-content::-webkit-scrollbar-thumb { background: #444; border-radius: 3px; }
    .filter-section summary { list-style: none; }
    .filter-section summary::-webkit-details-marker { display: none; }
    .filter-title { cursor: pointer; display: flex; justify-content: space-between; align-items: center; padding: 1rem; font-size: 1rem; font-weight: 500; color: var(--text-primary); border-radius: 8px; transition: background-color 0.2s ease; }
    .filter-title:hover { background-color: var(--sidebar-hover-bg); }
    .filter-section[open] > .filter-title { background-color: var(--sidebar-active-bg); font-weight: 600; }
    .filter-title i { margin-right: 10px; color: var(--text-secondary); width: 20px;}
    .filter-title .arrow-icon { color: var(--text-secondary); transition: transform 0.3s ease; }
    .filter-section[open] > .filter-title .arrow-icon { transform: rotate(90deg); }
    .filter-content { padding: 1rem 1rem 1.5rem 1rem; border-top: 1px solid var(--border-color-light); margin: 0 0.5rem; }
    .checkbox-grid-sidebar { display: grid; grid-template-columns: 1fr; gap: 0.5rem; }
    .checkbox-grid-sidebar.two-col { grid-template-columns: 1fr 1fr; }
    .checkbox-grid-sidebar div { position: relative; }
    .checkbox-grid-sidebar input[type="checkbox"] { display: none; }
    .checkbox-grid-sidebar label { cursor: pointer; display: block; padding: 0.8rem 1rem 0.8rem 2.5rem; user-select: none; border-radius: 6px; transition: background-color 0.2s ease; }
    .checkbox-grid-sidebar label:hover { background-color: var(--sidebar-hover-bg); }
    .checkbox-grid-sidebar label::before { content: ''; position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; border: 2px solid #555; background: #222; border-radius: 4px; transition: 0.2s; }
    .checkbox-grid-sidebar input:checked + label { color: var(--text-primary); font-weight: 500; }
    .checkbox-grid-sidebar input:checked + label::before { background-color: var(--primary-color); border-color: var(--primary-color); }
    .checkbox-grid-sidebar input:checked + label::after { content: '✓'; position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--sidebar-bg); font-size: 14px; font-weight: bold; }
    .price-input-container { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: 0.5rem 0 1.5rem 0; }
    .price-field { display: flex; align-items: center; background: #111; border: 1px solid var(--border-color-light); padding: 5px 10px; border-radius: 6px; }
    .price-field span { color: var(--text-secondary); margin-right: 5px; }
    .price-field input { width: 80px; background: none; border: none; color: #fff; font-size: 1rem; outline: none; -moz-appearance: textfield; }
    .price-field input::-webkit-inner-spin-button { -webkit-appearance: none; }
    .slider-container { position: relative; height: 5px; background: #444; border-radius: 5px; margin-bottom: 20px; }
    .price-slider-progress { position: absolute; height: 5px; background: var(--primary-color); border-radius: 5px; }
    .range-input-container { position: relative; }
    .range-input-container input { position: absolute; width: 100%; height: 5px; top: -5px; background: none; pointer-events: none; -webkit-appearance: none; -moz-appearance: none; appearance: none; }
    input[type="range"]::-webkit-slider-thumb { height: 18px; width: 18px; border-radius: 50%; background: #fff; pointer-events: auto; -webkit-appearance: none; appearance: none; border: none; cursor: pointer; }
    input[type="range"]::-moz-range-thumb { height: 18px; width: 18px; border-radius: 50%; background: #fff; pointer-events: auto; border: none; cursor: pointer; }
    .filter-sidebar-footer { padding: 1.5rem; margin-top: auto; border-top: 1px solid var(--border-color-light); }
    .reset-btn-sidebar { width: 100%; padding: 14px; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: 0.3s ease; border: 1px solid var(--text-secondary); background: transparent; color: var(--text-secondary); }
    .reset-btn-sidebar:hover { background: var(--text-secondary); color: #000; }
</style>

<form id="filterForm">
    <div id="filterSidebar" class="filter-sidebar">
        <div class="filter-sidebar-header">
            <h3>Filters</h3>
            <button type="button" id="closeFilterSidebarBtn" class="close-btn"><i class="fas fa-times"></i></button>
        </div>
        <div class="filter-sidebar-content">
            <!-- Price -->
            <details class="filter-section" open><summary class="filter-title"><span><i class="fas fa-rupee-sign"></i> Price</span> <i class="fas fa-chevron-right arrow-icon"></i></summary><div class="filter-content price-filter"><div class="price-input-container"><div class="price-field"><span>Min</span><input type="number" id="minPrice" value="<?php echo $price_range['min']; ?>"></div><div class="price-field"><span>Max</span><input type="number" id="maxPrice" value="<?php echo $price_range['max']; ?>"></div></div><div class="slider-container"><div class="price-slider-progress"></div></div><div class="range-input-container"><input type="range" id="minPriceRange" min="<?php echo $price_range['min']; ?>" max="<?php echo $price_range['max']; ?>" value="<?php echo $price_range['min']; ?>" step="1000"><input type="range" id="maxPriceRange" min="<?php echo $price_range['min']; ?>" max="<?php echo $price_range['max']; ?>" value="<?php echo $price_range['max']; ?>" step="1000"></div></div></details>
            <!-- Brand -->
            <details class="filter-section" open><summary class="filter-title"><span><i class="fas fa-copyright"></i> Brand</span> <i class="fas fa-chevron-right arrow-icon"></i></summary><div class="filter-content"><div class="checkbox-grid-sidebar two-col"><?php foreach ($brands as $brand): $id = 'brand_'.htmlspecialchars(strtolower(str_replace(' ', '', $brand)));?><div><input type="checkbox" name="brands" value="<?= htmlspecialchars($brand) ?>" id="<?= $id ?>"><label for="<?= $id ?>"><?= htmlspecialchars($brand) ?></label></div><?php endforeach; ?></div></div></details>
            <!-- Processor -->
            <details class="filter-section"><summary class="filter-title"><span><i class="fas fa-microchip"></i> Processor</span><i class="fas fa-chevron-right arrow-icon"></i></summary><div class="filter-content"><div class="checkbox-grid-sidebar two-col"><?php foreach ($processors as $processor): $id='proc_'.htmlspecialchars(strtolower($processor));?><div><input type="checkbox" name="processor" value="<?= htmlspecialchars($processor) ?>" id="<?= $id ?>_side"><label for="<?= $id ?>_side"><?= htmlspecialchars($processor) ?></label></div><?php endforeach; ?></div></div></details>
            <!-- RAM -->
            <details class="filter-section"><summary class="filter-title"><span><i class="fas fa-memory"></i> RAM</span> <i class="fas fa-chevron-right arrow-icon"></i></summary><div class="filter-content"><div class="checkbox-grid-sidebar two-col"><?php foreach ($rams as $ram): $id = 'ram_'.$ram; ?><div><input type="checkbox" name="ram" value="<?= $ram ?>" id="<?= $id ?>_side"><label for="<?= $id ?>_side"><?= $ram ?> GB</label></div><?php endforeach; ?></div></div></details>
            <!-- Storage -->
            <details class="filter-section"><summary class="filter-title"><span><i class="fas fa-hdd"></i> Storage</span><i class="fas fa-chevron-right arrow-icon"></i></summary><div class="filter-content"><div class="checkbox-grid-sidebar two-col"><?php foreach ($storages as $storage): $id='storage_'.$storage;?><div><input type="checkbox" name="storage" value="<?= $storage ?>" id="<?= $id ?>_side"><label for="<?= $id ?>_side"><?= $storage ?> GB</label></div><?php endforeach; ?></div></div></details>
            <!-- Rear Camera -->
            <details class="filter-section"><summary class="filter-title"><span><i class="fas fa-camera-retro"></i> Rear Camera</span><i class="fas fa-chevron-right arrow-icon"></i></summary><div class="filter-content"><div class="checkbox-grid-sidebar"><div><input type="checkbox" name="camera" value="12" id="cam_12_side"><label for="cam_12_side">12-20 MP</label></div><div><input type="checkbox" name="camera" value="21" id="cam_21_side"><label for="cam_21_side">21-48 MP</label></div><div><input type="checkbox" name="camera" value="49" id="cam_49_side"><label for="cam_49_side">49-100 MP</label></div><div><input type="checkbox" name="camera" value="101" id="cam_101_side"><label for="cam_101_side">101 MP & above</label></div></div></div></details>
            <!-- Front Camera -->
            <details class="filter-section"><summary class="filter-title"><span><i class="fas fa-camera-rotate"></i> Front Camera</span><i class="fas fa-chevron-right arrow-icon"></i></summary><div class="filter-content"><div class="checkbox-grid-sidebar"><div><input type="checkbox" name="front_camera" value="8" id="fcam_8_side"><label for="fcam_8_side">Up to 8 MP</label></div><div><input type="checkbox" name="front_camera" value="16" id="fcam_16_side"><label for="fcam_16_side">9-16 MP</label></div><div><input type="checkbox" name="front_camera" value="32" id="fcam_32_side"><label for="fcam_32_side">17-32 MP</label></div><div><input type="checkbox" name="front_camera" value="33" id="fcam_33_side"><label for="fcam_33_side">Above 32 MP</label></div></div></div></details>
            <!-- Battery -->
            <details class="filter-section"><summary class="filter-title"><span><i class="fas fa-battery-full"></i> Battery</span><i class="fas fa-chevron-right arrow-icon"></i></summary><div class="filter-content"><div class="checkbox-grid-sidebar"><div><input type="checkbox" name="battery" value="3000" id="bat_3000_side"><label for="bat_3000_side">3000-3999 mAh</label></div><div><input type="checkbox" name="battery" value="4000" id="bat_4000_side"><label for="bat_4000_side">4000-4999 mAh</label></div><div><input type="checkbox" name="battery" value="5000" id="bat_5000_side"><label for="bat_5000_side">5000 mAh & above</label></div></div></div></details>
            <!-- OS -->
            <details class="filter-section"><summary class="filter-title"><span><i class="fab fa-android"></i> OS</span><i class="fas fa-chevron-right arrow-icon"></i></summary><div class="filter-content"><div class="checkbox-grid-sidebar two-col"><?php foreach ($operating_systems as $os): $id='os_'.htmlspecialchars(strtolower($os));?><div><input type="checkbox" name="os" value="<?= htmlspecialchars($os) ?>" id="<?= $id ?>_side"><label for="<?= $id ?>_side"><?= htmlspecialchars($os) ?></label></div><?php endforeach; ?></div></div></details>
            <!-- Network -->
            <details class="filter-section"><summary class="filter-title"><span><i class="fas fa-broadcast-tower"></i> Network</span><i class="fas fa-chevron-right arrow-icon"></i></summary><div class="filter-content"><div class="checkbox-grid-sidebar two-col"><div><input type="checkbox" name="network" value="5G" id="net_5g_side"><label for="net_5g_side">5G</label></div><div><input type="checkbox" name="network" value="4G" id="net_4g_side"><label for="net_4g_side">4G</label></div></div></div></details>
        </div>
        <div class="filter-sidebar-footer">
            <button type="button" id="resetFiltersBtn" class="reset-btn-sidebar"><i class="fas fa-undo"></i> Clear All</button>
        </div>
    </div>
</form>

<div id="filter-overlay" class="filter-overlay"></div> 

<script>
    // Make reset function globally accessible so shome.php can call it
    function resetAllFilters(clearSearch = true) {
        const filterForm = document.getElementById('filterForm');
        if (filterForm) filterForm.reset();
        
        if(clearSearch) {
            const searchBar = document.getElementById('searchBar');
            if (searchBar) searchBar.value = '';
        }
        
        const minPriceRange = document.getElementById('minPriceRange'), maxPriceRange = document.getElementById('maxPriceRange');
        const minPriceInput = document.getElementById('minPrice'), maxPriceInput = document.getElementById('maxPrice');
        const progress = document.querySelector('.slider-container .price-slider-progress');
        if(minPriceInput && maxPriceInput && minPriceRange && maxPriceRange && progress) {
            minPriceInput.value = minPriceRange.min;
            maxPriceInput.value = maxPriceRange.max;
            minPriceRange.value = minPriceRange.min;
            maxPriceRange.value = maxPriceRange.max;
            progress.style.left = '0%';
            progress.style.right = '0%';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        function debounce(func, delay) { 
            let timeout; 
            return function(...args) { 
                clearTimeout(timeout); 
                timeout = setTimeout(() => func.apply(this, args), delay); 
            }; 
        }
    
        function applyFilters() {
            const form = document.getElementById('filterForm');
            const filters = {
                search: document.getElementById('searchBar').value,
                price: { min: form.querySelector('#minPrice').value, max: form.querySelector('#maxPrice').value },
                brands: Array.from(form.querySelectorAll('input[name="brands"]:checked')).map(el => el.value),
                ram: Array.from(form.querySelectorAll('input[name="ram"]:checked')).map(el => el.value),
                storage: Array.from(form.querySelectorAll('input[name="storage"]:checked')).map(el => el.value),
                battery: Array.from(form.querySelectorAll('input[name="battery"]:checked')).map(el => el.value),
                os: Array.from(form.querySelectorAll('input[name="os"]:checked')).map(el => el.value),
                network: Array.from(form.querySelectorAll('input[name="network"]:checked')).map(el => el.value),
                camera: Array.from(form.querySelectorAll('input[name="camera"]:checked')).map(el => el.value),
                processor: Array.from(form.querySelectorAll('input[name="processor"]:checked')).map(el => el.value),
                front_camera: Array.from(form.querySelectorAll('input[name="front_camera"]:checked')).map(el => el.value),
            };
            if (typeof fetchPhones === 'function') {
                fetchPhones(filters);
            }
        }
    
        const filterForm = document.getElementById('filterForm');
        const searchBar = document.getElementById('searchBar');

        // *** CHANGE START: New handlers for search vs. filter actions ***

        // Handler for when the user types in the search bar
        function handleSearchInput() {
            resetAllFilters(false); // Reset sidebar filters but DON'T clear the search bar
            applyFilters();
        }
        const debouncedSearchHandler = debounce(handleSearchInput, 500);

        // Handler for when the user changes a filter in the sidebar
        function handleFilterChange() {
            if (searchBar && searchBar.value !== '') {
                searchBar.value = ''; // Clear the search bar if it has text
            }
            applyFilters();
        }
        const debouncedFilterChangeHandler = debounce(handleFilterChange, 500);
        
        // Apply the correct handlers to the elements
        if (searchBar) {
            searchBar.addEventListener('input', debouncedSearchHandler);
        }
        if(filterForm) {
            // For checkboxes and range sliders (on release)
            filterForm.addEventListener('change', handleFilterChange);
        }
        // *** CHANGE END ***
    
        const resetBtn = document.getElementById('resetFiltersBtn');
        if(resetBtn) {
            resetBtn.addEventListener('click', () => {
                resetAllFilters(true); // 'true' clears the search bar as well
                applyFilters();
            });
        }
    
        // Full Price Slider Logic
        const priceInputs = document.querySelectorAll('.price-input-container input');
        const rangeInputs = document.querySelectorAll('.range-input-container input');
        const progress = document.querySelector('.slider-container .price-slider-progress');
        const priceGap = 5000;

        function updateProgress() {
            if (!progress) return;
            let minVal = parseInt(rangeInputs[0].value);
            let maxVal = parseInt(rangeInputs[1].value);
            progress.style.left = ((minVal - rangeInputs[0].min) / (rangeInputs[0].max - rangeInputs[0].min)) * 100 + '%';
            progress.style.right = 100 - ((maxVal - rangeInputs[1].min) / (rangeInputs[1].max - rangeInputs[1].min)) * 100 + '%';
        }

        rangeInputs.forEach(input => {
            input.addEventListener('input', e => {
                let minVal = parseInt(rangeInputs[0].value), maxVal = parseInt(rangeInputs[1].value);
                if (maxVal - minVal < priceGap) {
                    if (e.target.id === "minPriceRange") { rangeInputs[0].value = maxVal - priceGap; } 
                    else { rangeInputs[1].value = minVal + priceGap; }
                } else {
                    priceInputs[0].value = minVal;
                    priceInputs[1].value = maxVal;
                    updateProgress();
                }
            });
            // The 'change' event on the slider will be captured by the form's change listener
        });

        priceInputs.forEach(input => {
            input.addEventListener('input', e => {
                let minVal = parseInt(priceInputs[0].value), maxVal = parseInt(priceInputs[1].value);
                if ((maxVal - minVal >= priceGap) && maxVal <= rangeInputs[1].max && minVal >= rangeInputs[0].min) {
                    if (e.target.id === "minPrice") { rangeInputs[0].value = minVal; updateProgress(); }
                    else { rangeInputs[1].value = maxVal; updateProgress(); }
                }
            });
            // Use the new debounced filter change handler for the price text inputs
            input.addEventListener('input', debouncedFilterChangeHandler);
        });
        
        // Logic for opening and closing the filter sidebar
        const allFiltersBtn = document.getElementById('allFiltersBtn');
        const filterSidebar = document.getElementById('filterSidebar');
        const closeFilterSidebarBtn = document.getElementById('closeFilterSidebarBtn');
        const overlay = document.getElementById('filter-overlay');
        const openFilterSidebar = () => { if(filterSidebar) filterSidebar.classList.add('active'); if(overlay) overlay.classList.add('active'); };
        const closeFilterSidebar = () => { if(filterSidebar) filterSidebar.classList.remove('active'); if(overlay) overlay.classList.remove('active'); };
        
        if(allFiltersBtn) allFiltersBtn.addEventListener('click', openFilterSidebar);
        if(closeFilterSidebarBtn) closeFilterSidebarBtn.addEventListener('click', closeFilterSidebar);
        if(overlay) overlay.addEventListener('click', closeFilterSidebar);
    });
</script>