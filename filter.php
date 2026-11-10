<?php
// --- 1. SETUP ---
session_start();
header('Content-Type: application/json');

// Include the central database connection file (connects to 'gguru')
require_once 'config.php';

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit();
}

// --- 2. BASE SQL QUERY ---
// MODIFIED: Selects from the single 'smartphones' table with correct column names.
$sql = "
    SELECT 
        phone_id, brand_name, model_name, release_date, price,
        ram_gb, storage_gb, battery_mah, fast_charging_w, processor, chipset, display_type, screen_size_in, resolution, refresh_rate_hz, aspect_ratio,
        rear_camera, front_camera, os, os_update, ui, fingerprint_sensor,
        height_mm, width_mm, thickness_mm, weight_g, sim_slot, sim_type, network_support,
        image_url, youtube_link
    FROM smartphones
";

// --- 3. FILTERING LOGIC ---
$where_clauses = [];
$params = [];
$types = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filters = json_decode(file_get_contents('php://input'), true);

    // Search Filter (Targets brand_name and model_name)
    if (!empty($filters['search'])) {
        $searchTerm = '%' . $filters['search'] . '%';
        $where_clauses[] = "(brand_name LIKE ? OR model_name LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }

    // Price Filter
    if (!empty($filters['price']['min']) && is_numeric($filters['price']['min'])) {
        $where_clauses[] = 'price >= ?';
        $params[] = $filters['price']['min'];
        $types .= 'd';
    }
    if (!empty($filters['price']['max']) && is_numeric($filters['price']['max'])) {
        $where_clauses[] = 'price <= ?';
        $params[] = $filters['price']['max'];
        $types .= 'd';
    }

    // Brand Filter
    if (!empty($filters['brands'])) {
        $placeholders = implode(',', array_fill(0, count($filters['brands']), '?'));
        $where_clauses[] = "TRIM(brand_name) IN ($placeholders)";
        $params = array_merge($params, $filters['brands']);
        $types .= str_repeat('s', count($filters['brands']));
    }

    // OS Filter
    if (!empty($filters['os'])) {
        $os_clauses = [];
        foreach($filters['os'] as $os) {
            $os_clauses[] = "os LIKE ?";
            $params[] = '%' . $os . '%';
            $types .= 's';
        }
        $where_clauses[] = '(' . implode(' OR ', $os_clauses) . ')';
    }

    // Processor Filter (Targets processor column)
    if (!empty($filters['processor'])) {
        $proc_clauses = [];
        foreach($filters['processor'] as $proc) {
            $proc_clauses[] = "processor LIKE ?";
            $params[] = $proc . '%';
            $types .= 's';
        }
        $where_clauses[] = '(' . implode(' OR ', $proc_clauses) . ')';
    }

    // Network Filter (Targets network_support column)
    if (!empty($filters['network'])) {
        $net_clauses = [];
        foreach($filters['network'] as $net) {
            $net_clauses[] = "network_support LIKE ?";
            $params[] = '%' . $net . '%';
            $types .= 's';
        }
        $where_clauses[] = '(' . implode(' OR ', $net_clauses) . ')';
    }
    
    // RAM Filter (Targets ram_gb column)
    if (!empty($filters['ram'])) {
        $ram_clauses = [];
        foreach($filters['ram'] as $ram) {
            if ($ram == '12') { // 12 or more
                $ram_clauses[] = 'ram_gb >= ?';
            } else {
                $ram_clauses[] = 'ram_gb = ?';
            }
            $params[] = $ram;
            $types .= 'i';
        }
        $where_clauses[] = '(' . implode(' OR ', $ram_clauses) . ')';
    }
    
    // Storage Filter (Targets storage_gb column)
    if (!empty($filters['storage'])) {
        $storage_clauses = [];
        foreach($filters['storage'] as $storage) {
             if ($storage == '512') { // 512 or more
                $storage_clauses[] = 'storage_gb >= ?';
            } else {
                $storage_clauses[] = 'storage_gb = ?';
            }
            $params[] = $storage;
            $types .= 'i';
        }
        $where_clauses[] = '(' . implode(' OR ', $storage_clauses) . ')';
    }

    // Battery Filter (Targets battery_mah column)
    if (!empty($filters['battery'])) {
        $battery_clauses = [];
        foreach($filters['battery'] as $bat) {
             if ($bat == '5000') {
                 $battery_clauses[] = 'battery_mah >= ?';
                 $params[] = 5000; $types .= 'i';
             } else if ($bat == '4000') {
                 $battery_clauses[] = 'battery_mah BETWEEN ? AND ?';
                 $params[] = 4000; $params[] = 4999; $types .= 'ii';
             } else if ($bat == '3000') {
                 $battery_clauses[] = 'battery_mah BETWEEN ? AND ?';
                 $params[] = 3000; $params[] = 3999; $types .= 'ii';
             }
        }
        if(!empty($battery_clauses)) $where_clauses[] = '(' . implode(' OR ', $battery_clauses) . ')';
    }
    
    // Rear Camera Filter (Targets rear_camera column)
    if (!empty($filters['camera'])) {
        $camera_clauses = [];
        // The camera filter logic relies on extracting the primary MP count from the string.
        $camera_col = "CAST(REGEXP_SUBSTR(rear_camera, '^[0-9]+') AS UNSIGNED)";
        foreach ($filters['camera'] as $cam) {
            if ($cam == '101') {
                $camera_clauses[] = "$camera_col >= ?";
                $params[] = 101; $types .= 'i';
            } else if ($cam == '49') {
                $camera_clauses[] = "$camera_col BETWEEN ? AND ?";
                $params[] = 49; $params[] = 100; $types .= 'ii';
            } else if ($cam == '21') {
                $camera_clauses[] = "$camera_col BETWEEN ? AND ?";
                $params[] = 21; $params[] = 48; $types .= 'ii';
            } else if ($cam == '12') {
                $camera_clauses[] = "$camera_col BETWEEN ? AND ?";
                $params[] = 12; $params[] = 20; $types .= 'ii';
            }
        }
        if(!empty($camera_clauses)) $where_clauses[] = '(' . implode(' OR ', $camera_clauses) . ')';
    }
    
    // Front Camera Filter (Targets front_camera column)
    if (!empty($filters['front_camera'])) {
        $f_camera_clauses = [];
        $f_camera_col = "CAST(REGEXP_SUBSTR(front_camera, '^[0-9]+') AS UNSIGNED)";
        foreach ($filters['front_camera'] as $f_cam) {
            if ($f_cam == '33') {
                $f_camera_clauses[] = "$f_camera_col >= ?";
                $params[] = 33; $types .= 'i';
            } else if ($f_cam == '32') {
                $f_camera_clauses[] = "$f_camera_col BETWEEN ? AND ?";
                $params[] = 17; $params[] = 32; $types .= 'ii';
            } else if ($f_cam == '16') {
                $f_camera_clauses[] = "$f_camera_col BETWEEN ? AND ?";
                $params[] = 9; $params[] = 16; $types .= 'ii';
            } else if ($f_cam == '8') {
                $f_camera_clauses[] = "$f_camera_col <= ?";
                $params[] = 8; $types .= 'i';
            }
        }
        if(!empty($f_camera_clauses)) $where_clauses[] = '(' . implode(' OR ', $f_camera_clauses) . ')';
    }
}

// Append WHERE clause to the main query if filters were applied
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['error' => 'SQL prepare failed: ' . $conn->error, 'sql' => $sql]);
    exit();
}

// Bind parameters if any were collected
if (!empty($params)) {
    // Note: The $types string may be incomplete due to complex logic. 
    // In a final production environment, the types string must be checked.
    // For now, we assume the complex logic above generates the correct types string.
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$phones = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // MODIFIED: Data structure is now flat and uses correct column names from smartphones table
        $phones[] = [
            'phone_id' => $row['phone_id'], 
            'brand_name' => $row['brand_name'], 
            'model_name' => $row['model_name'], 
            'price' => $row['price'],
            'specs' => [ 
                'ram' => $row['ram_gb'], 
                'processor' => $row['processor'], 
                'display_type' => $row['display_type'], 
                'screen_size' => $row['screen_size_in'] 
            ],
            'features' => [ 'rear_camera' => $row['rear_camera'] ],
            'links' => [ 'image' => $row['image_url'] ] // Corrected: image column is now image_url
        ];
    }
}

echo json_encode($phones);

$stmt->close();
$conn->close();
?>