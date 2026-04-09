<?php
include 'core/warehouse_db.php';
include 'core/auth.php'; // Session is already started and checked

$current_user = $_SESSION['username'];
$selected_sector = $_GET['sector'] ?? 'Laptops';
$selected_loc = $_GET['loc'] ?? null;

// Handle Add/Edit Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_inventory' || $_POST['action'] === 'edit_inventory') {
        $brand = $_POST['brand'];
        $model = $_POST['model'];
        $loc = $_POST['location_code'];
        $qty = (int)$_POST['quantity'];
        $sector = $_POST['sector'];
        
        // Dynamic Specs mapping based on sector
        $specs = [];
        if ($sector === 'Laptops') {
            $specs = [
                'cpu' => $_POST['cpu'] ?? '', 
                'gpu' => $_POST['gpu'] ?? '',
                'ram' => $_POST['ram'] ?? '',
                'storage' => $_POST['storage'] ?? '',
                'battery' => $_POST['battery'] ?? '',
                'windows' => $_POST['windows'] ?? '',
                'series' => $_POST['series'] ?? '',
                'gen' => $_POST['gen'] ?? '',
                'condition' => $_POST['condition'] ?? '',
                'notes' => $_POST['notes'] ?? ''
            ];
        } elseif ($sector === 'Gaming') {
            $specs = [
                'category' => $_POST['gaming_category'] ?? 'Consoles', 'series' => $_POST['series'] ?? '',
                'condition' => $_POST['condition'] ?? '', 'notes' => $_POST['notes'] ?? '',
                'ram' => $_POST['ram'] ?? '', 'storage' => $_POST['storage'] ?? '', 'cpu' => $_POST['cpu'] ?? '', 'gpu' => $_POST['gpu'] ?? ''
            ];
        } else {
            $specs = ['condition' => $_POST['condition'] ?? '', 'notes' => $_POST['notes'] ?? ''];
        }

        $specs_json = json_encode($specs);

        if ($_POST['action'] === 'edit_inventory' && isset($_POST['item_id'])) {
            $stmt = $conn_wh->prepare("UPDATE inventory SET brand=?, model=?, specs_json=?, quantity=? WHERE id=?");
            $stmt->execute([$brand, $model, $specs_json, $qty, $_POST['item_id']]);
        } else {
            $stmt = $conn_wh->prepare("INSERT INTO inventory (user_owner, sector, location_code, brand, model, specs_json, quantity) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$current_user, $sector, $loc, $brand, $model, $specs_json, $qty]);
        }
        
        header("Location: index.php?view=warehouse&sector=" . urlencode($sector) . "&loc=" . urlencode($loc));
        exit();
    }
}

// Fetch All Unique Locations
$stmt_locs = $conn_wh->query("SELECT DISTINCT location_code FROM inventory ORDER BY location_code ASC");
$existing_locs = $stmt_locs->fetchAll(PDO::FETCH_COLUMN);

// Fetch Sectors
$sectors = $conn_wh->query("SELECT * FROM sectors")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Inventory for selected sector and LOCATION
$items = [];
if ($selected_loc) {
    if ($selected_loc === 'GLOBAL') {
        $stmt_i = $conn_wh->prepare("SELECT * FROM inventory WHERE sector = ? ORDER BY id DESC");
        $stmt_i->execute([$selected_sector]);
    } else {
        $stmt_i = $conn_wh->prepare("SELECT * FROM inventory WHERE sector = ? AND location_code = ? ORDER BY id DESC");
        $stmt_i->execute([$selected_sector, $selected_loc]);
    }
    $items = $stmt_i->fetchAll(PDO::FETCH_ASSOC);
}
?>

<script>
    // Global context for warehouse logic
    window.activeSector = "<?= htmlspecialchars($selected_sector) ?>";
</script>
<link rel="stylesheet" href="assets/styles/warehouse.css">

<div class="warehouse-container">
    <header class="warehouse-header">
        <div style="display:flex; justify-content:space-between; align-items:flex-end;">
            <div>
                <h1 style="font-weight: 900; letter-spacing: -1px; margin-bottom: 5px;">Warehouse Control Center</h1>
                <p class="subtitle">Managing stock and locations across all inventory sectors.</p>
            </div>
            <?php if ($selected_loc): ?>
                <div style="text-align:right;">
                    <div style="font-size:0.75rem; font-weight:800; color:var(--text-secondary); text-transform:uppercase;">Active Location</div>
                    <a href="index.php?view=warehouse&sector=<?= urlencode($selected_sector) ?>" class="loc-active-badge">📍 <?= htmlspecialchars($selected_loc) ?> <span style="margin-left:5px; opacity:0.5;">Change</span></a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <?php if (!$selected_loc): ?>
        <div class="location-gate">
            <div class="gate-options-container">
                <!-- OPTION 1: REGISTRATION / WORKING ZONE -->
                <div class="gate-card main-gate">
                    <h2 style="font-weight:900; margin-bottom:10px;">Select Working Zone</h2>
                    <p style="color:var(--text-secondary); margin-bottom:30px;">Choose a shelf to register or edit stock.</p>
                    
                    <div class="loc-grid">
                        <?php foreach ($existing_locs as $loc): ?>
                            <a href="index.php?view=warehouse&sector=<?= urlencode($selected_sector) ?>&loc=<?= urlencode($loc) ?>" class="loc-item">
                                <span class="loc-icon">📦</span>
                                <span class="loc-name"><?= htmlspecialchars($loc) ?></span>
                            </a>
                        <?php endforeach; ?>
                        
                        <div class="loc-item new-loc">
                            <form method="GET" action="index.php" style="width:100%;">
                                <input type="hidden" name="view" value="warehouse">
                                <input type="hidden" name="sector" value="<?= htmlspecialchars($selected_sector) ?>">
                                <input type="text" name="loc" placeholder="+ New Zone" required style="width:100%; border:none; background:transparent; text-align:center; font-weight:800; outline:none;">
                                <button type="submit" style="display:none;"></button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- OPTION 2: GLOBAL VIEWING -->
                <div class="gate-card">
                    <div style="font-size: 3.5rem; margin-bottom: 25px;">🌐</div>
                    <h2 style="font-weight:900; margin-bottom:10px;">Global Warehouse</h2>
                    <p style="color:var(--text-secondary); margin-bottom:30px;">View and search inventory across all zones at once without registration tools.</p>
                    <div style="flex: 1; display:flex; align-items:center; justify-content:center;">
                        <a href="index.php?view=warehouse&sector=<?= urlencode($selected_sector) ?>&loc=GLOBAL" 
                           style="display: block; width: 100%; padding: 20px; background: var(--text-main); color: white; border-radius: 16px; font-weight: 800; text-decoration: none; transition: 0.2s; font-size: 1.1rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
                           Enter Global View
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>

    <!-- Sector Navigation -->
    <div class="sector-nav">
        <?php foreach ($sectors as $s): ?>
            <a href="index.php?view=warehouse&sector=<?= urlencode($s['name']) ?>&loc=<?= urlencode($selected_loc) ?>" 
               class="sector-card <?= $selected_sector === $s['name'] ? 'active' : '' ?>" 
               data-sector="<?= htmlspecialchars($s['name']) ?>">
                <span class="sector-icon"><?= $s['icon'] ?></span>
                <span class="sector-name"><?= htmlspecialchars($s['name']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Main Content Grid -->
    <div class="warehouse-layout">
        
        <!-- Inventory List -->
        <section class="inventory-feed">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="font-size: 1.25rem; font-weight: 800;"><?= htmlspecialchars($selected_sector) ?> Inventory</h2>
                <div class="search-container">
                    <input type="text" id="wh-search" placeholder="Search items..." onkeyup="filterWarehouse()" class="search-input">
                </div>
            </div>

            <div class="inventory-grid" id="inventory-list">
                <?php if (empty($items)): ?>
                    <div style="grid-column: 1/-1; padding: 60px; text-align: center; background: white; border-radius: 20px; border: 2px dashed #eee;">
                        <p style="color: #94a3b8; font-weight: 600;">No items found in this sector.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($items as $item): 
                        $specs = json_decode($item['specs_json'], true) ?: [];
                    ?>
                        <div class="inventory-card" 
                             data-sector-theme="<?= htmlspecialchars($item['sector']) ?>"
                             data-search="<?= htmlspecialchars(strtolower($item['brand'] . ' ' . $item['model'] . ' ' . $item['location_code'])) ?>">
                            <span class="status-badge status-<?= $item['status'] ?>"><?= $item['status'] ?></span>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div class="location-tag"><?= htmlspecialchars($item['location_code']) ?></div>
                                <button type="button" class="btn-edit-item" onclick='editWarehouseItem(<?= json_encode($item) ?>)' style="background:none; border:none; cursor:pointer; font-size:1.1rem; opacity:0.6 hover:opacity:1;">📝</button>
                            </div>
                            <h3 style="margin: 10px 0 5px 0; font-weight: 800;"><?= htmlspecialchars($item['brand'] . ' ' . $item['model']) ?></h3>
                            <div style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 12px;">Added by @<?= htmlspecialchars($item['user_owner']) ?></div>
                            
                            <div class="spec-info">
                                <span class="spec-pill" style="background: var(--bg-main); font-weight: 800; color: var(--text-main);">Qty: <?= (int)$item['quantity'] ?></span>
                                <?php foreach ($specs as $key => $val): ?>
                                    <span class="spec-pill"><?= strtoupper($key) ?>: <?= htmlspecialchars($val) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Add Item Sidebar (Hidden in Global View) -->
        <?php if ($selected_loc !== 'GLOBAL'): ?>
        <aside class="warehouse-sidebar">
            <div style="background: white; padding: 25px; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); position: sticky; top: 20px;">
                <h3 id="wh-form-title" style="font-weight: 800; margin-bottom: 20px;">📥 Register Stock</h3>
                <form method="POST" action="" id="wh-main-form">
                    <input type="hidden" name="action" id="wh-form-action" value="add_inventory">
                    <input type="hidden" name="item_id" id="wh-edit-id" value="">
                    <input type="hidden" name="sector" value="<?= htmlspecialchars($selected_sector) ?>">
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Location Code (Zone/Shelf)</label>
                        <input type="text" name="location_code" value="<?= htmlspecialchars($selected_loc) ?>" readonly style="width:100%; height:42px; border-radius:10px; border:1px solid #ddd; padding: 0 12px; background:#f8fafc; color:#64748b; font-weight:700;">
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Brand</label>
                            <input type="text" name="brand" list="brand-options" id="wh-brand" placeholder="Dell" required style="width:100%; height:42px; border-radius:10px; border:1px solid #ddd; padding: 0 12px;">
                            <datalist id="brand-options"></datalist>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Model</label>
                            <input type="text" name="model" list="model-options" id="wh-model" placeholder="Latitude" required style="width:100%; height:42px; border-radius:10px; border:1px solid #ddd; padding: 0 12px;">
                            <datalist id="model-options"></datalist>
                        </div>
                    </div>

                    <!-- Sector Specific Fields -->
                    <div id="sector-specific-fields" style="border-top: 1px dashed #eee; padding-top: 15px; margin-bottom: 15px;">
                        <?php if ($selected_sector === 'Laptops'): ?>
                            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <div class="form-group" style="flex: 1;">
                                    <label>CPU</label>
                                    <input type="text" name="cpu" placeholder="Core i7-1185G7" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px;">
                                </div>
                                <div class="form-group" style="flex: 1;">
                                    <label>GPU</label>
                                    <input type="text" name="gpu" placeholder="Integrated / RTX" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px;">
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <div class="form-group" style="flex: 1;">
                                    <label>RAM</label>
                                    <input type="text" name="ram" placeholder="16GB DDR4" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px;">
                                </div>
                                <div class="form-group" style="flex: 1;">
                                    <label>Storage</label>
                                    <input type="text" name="storage" placeholder="512GB NVMe" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px;">
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <div class="form-group" style="flex: 1;">
                                    <label>Battery Health</label>
                                    <input type="text" name="battery" placeholder="85% Health" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px;">
                                </div>
                                <div class="form-group" style="flex: 1;">
                                    <label>Windows Version</label>
                                    <input type="text" name="windows" placeholder="Win 11 Pro" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px;">
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <div class="form-group" style="flex: 1;">
                                    <label>Model Number</label>
                                    <input type="text" name="series" placeholder="E7450" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px;">
                                </div>
                                <div class="form-group" style="flex: 1;">
                                    <label>Generation</label>
                                    <input type="text" name="gen" placeholder="11th Gen" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px;">
                                </div>
                            </div>
                        <?php elseif ($selected_sector === 'Gaming'): ?>
                            <div class="form-group" style="margin-bottom: 10px;">
                                <label>Category</label>
                                <select name="gaming_category" id="wh-gaming-cat" onchange="toggleGamingFields()" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px; font-weight:700;">
                                    <option value="PC">PC / Custom Build</option>
                                    <option value="Consoles">Consoles</option>
                                    <option value="Controllers">Controllers</option>
                                    <option value="Games">Games</option>
                                </select>
                            </div>

                            <!-- PC Specific -->
                            <div id="wh-gaming-pc-fields">
                                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                    <div class="form-group" style="flex: 1;">
                                        <label>CPU</label>
                                        <input type="text" name="cpu" placeholder="Ryzen 7" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px;">
                                    </div>
                                    <div class="form-group" style="flex: 1;">
                                        <label>GPU</label>
                                        <input type="text" name="gpu" placeholder="RTX 3070" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px;">
                                    </div>
                                </div>
                            </div>

                            <!-- Specific Specs for everything else -->
                            <div class="form-group" style="margin-bottom: 10px;">
                                <label id="wh-gaming-spec-label">Specs / Series</label>
                                <input type="text" name="series" list="series-options" id="wh-series" placeholder="Series / Edition" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px;">
                                <datalist id="series-options"></datalist>
                                <div id="wh-gaming-extra-specs" style="display: flex; gap: 10px; margin-top:5px;">
                                    <div class="form-group" style="flex: 1;">
                                        <input type="text" name="ram" id="wh-ram" placeholder="RAM / Color" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px;">
                                    </div>
                                    <div class="form-group" style="flex: 1;">
                                        <input type="text" name="storage" id="wh-storage" placeholder="Storage" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px;">
                                    </div>
                                </div>
                            </div>
                        <?php elseif ($selected_sector === 'Electronics'): ?>
                            <div class="form-group" style="margin-bottom: 10px;">
                                <label>Device Type</label>
                                <input type="text" name="type" placeholder="Charger / Hub" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px;">
                            </div>
                            <div class="form-group" style="margin-bottom: 10px;">
                                <label>Specs / Condition</label>
                                <input type="text" name="voltage" placeholder="65W / 19.5V" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px;">
                                <input type="text" name="condition" placeholder="New" style="width:100%; height:38px; border-radius:8px; border:1px solid #ddd; padding: 0 10px; margin-top:5px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Condition</label>
                            <select name="condition" style="width:100%; height:42px; border-radius:10px; border:1px solid #ddd; padding: 0 12px; font-weight:700;">
                                <option value="Used">A</option>
                                <option value="New">B</option>
                                <option value="Refurbished">C</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Initial Quantity</label>
                            <input type="number" name="quantity" value="1" min="1" required style="width:100%; height:42px; border-radius:10px; border:1px solid #ddd; padding: 0 12px; font-weight: 800;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Notes / Observations</label>
                        <textarea name="notes" placeholder="Any scratches or specifics..." style="width:100%; height:80px; border-radius:10px; border:1px solid #ddd; padding: 10px; font-family:inherit; resize:none;"></textarea>
                    </div>

                    <button type="submit" id="wh-submit-btn" style="width:100%; height:50px; background:var(--text-main); color:white; border:none; border-radius:14px; font-weight:800; cursor:pointer;">
                        📥 Add to Stock
                    </button>
                    <button type="button" id="wh-cancel-edit" onclick="resetWarehouseForm()" style="display:none; width:100%; margin-top:10px; background:none; border:none; color:#64748b; font-weight:700; cursor:pointer;">Cancel Edit</button>
                </form>
            </div>
        </aside>
        <?php else: ?>
        <aside class="warehouse-sidebar" style="background:#f8fafc; border:2px dashed #cbd5e1; border-radius:20px; padding:40px; text-align:center; color:#64748b;">
            <div style="font-size:2rem; margin-bottom:15px;">🚫</div>
            <h3 style="font-weight:800;">Registration Locked</h3>
            <p>You are in <b>Global View</b>. To add or edit specific stock, please select a specific <b>Working Zone</b> from the gate.</p>
            <a href="index.php?view=warehouse&sector=<?= urlencode($selected_sector) ?>" style="display:inline-block; margin-top:20px; color:var(--text-main); font-weight:800;">Back to Gate</a>
        </aside>
        <?php endif; ?>

    <?php endif; ?>
</div>

<script src="assets/js/warehouse.js"></script>
<script>
    // Ensure gaming fields are correctly toggled on load if Gaming is selected
    if (typeof toggleGamingFields === 'function') toggleGamingFields();
</script>
