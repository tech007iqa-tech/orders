<?php include 'core/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Entry | IQA Metal</title>
    <meta name="description" content="IQA Metal Order Management and Warehouse Control System. Efficiently manage batches,      , and customer fulfillments.">

    <!-- Optimize Third-Party Connections (Non-blocking Fonts) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap">
    </noscript>

    <!-- Primary Stylesheet (LCP Priority) -->
    <link rel="stylesheet" href="assets/styles/style.css?v=<?= filemtime('assets/styles/style.css') ?>">

    <!-- Conditional Style Discovery -->
    <?php
        $view = $_GET['view'] ?? 'default';
        $is_new_order = isset($_GET['customer_id']);
        
        $routes = [
            'register'  => ['page' => 'pages/new_customer.php',     'css' => 'customer_registry.css'],
            'orders'    => ['page' => 'pages/orders.php',           'css' => 'orders.css'],
            'warehouse' => ['page' => 'pages/warehouse.php',        'css' => 'warehouse.css'],
            'import_warehouse' => ['page' => 'pages/import_warehouse.php', 'css' => 'warehouse.css'],
            'settings'  => ['page' => 'pages/settings.php',         'css' => 'style.css'],
            'default'   => ['page' => 'pages/customer_registry.php', 'css' => 'customer_registry.css'],
            'new_order' => ['page' => 'pages/new_order.php',         'css' => 'new_order.css']
        ];

        $active_key = $is_new_order ? 'new_order' : (isset($routes[$view]) ? $view : 'default');
        $active_route = $routes[$active_key];

        if ($active_route['css'] !== 'style.css') {
            $css_path = 'assets/styles/' . $active_route['css'];
            echo '<link rel="stylesheet" href="'.$css_path.'?v='.filemtime($css_path).'">';
        }
    ?>

    <link rel="icon" type="image/png" href="assets/icon/smart-home-sensor-wifi-black-outline-25276_1024.png">

    <!-- Logic Initialization (Deferred) -->
    <script src="assets/js/inventory_data.js?v=<?= filemtime('assets/js/inventory_data.js') ?>" defer></script>
</head>

<body>
    <div class="breadcrumb-container" role="banner" style="max-width: 800px; margin: 0 auto 20px auto; width: 100%; display: flex; justify-content: space-between; align-items: center;">
        <nav class="breadcrumbs">
            <a href="index.php"
                class="crumb <?= !isset($_GET['customer_id']) && !isset($_GET['view']) ? 'active' : '' ?>">
                <span class="step-num">1</span> Customers
            </a>

            <?php if (isset($_GET['view']) && $_GET['view'] === 'register'): ?>
            <span class="separator">/</span>
            <a href="#" class="crumb active">
                <span class="step-num">2</span> Register
            </a>
            <?php endif; ?>

            <?php if (isset($_GET['customer_id'])): ?>
            <span class="separator">/</span>
            <a href="#" class="crumb active">
                <span class="step-num">2</span> Order Entry
            </a>
            <?php endif; ?>

            <?php if (isset($_GET['view']) && $_GET['view'] === 'settings'): ?>
            <span class="separator">/</span>
            <a href="#" class="crumb active">
                <span class="step-num">⚙️</span> Settings
            </a>
            <?php endif; ?>
        </nav>

        <nav class="breadcrumbs" style="display: flex; gap: 20px; align-items: center;">
            <a href="index.php?view=warehouse" class="crumb <?= isset($_GET['view']) && $_GET['view'] === 'warehouse' ? 'active' : '' ?>" style="margin:0;">
                🏬 Warehouse
            </a>
            <a href="index.php?view=orders" class="crumb <?= isset($_GET['view']) && $_GET['view'] === 'orders' ? 'active' : '' ?>" style="margin:0;">
                📦 All Orders
            </a>
            <a href="index.php?view=settings" class="crumb icon-only <?= isset($_GET['view']) && $_GET['view'] === 'settings' ? 'active' : '' ?>" style="margin:0;">⚙️</a>
        </nav>
    </div>

    <div class="container <?= $is_new_order || $view === 'orders' || $view === 'warehouse' ? 'order-view' : '' ?>" role="main">
        <?php
        // Global State Initialization
        $selected_sector = $_GET['sector'] ?? 'Laptops';
        $selected_loc = $_GET['loc'] ?? null;

        // Order Creation Logic
        if (isset($_GET['action']) && $_GET['action'] === 'create_new_order' && isset($_GET['customer_id'])) {
            $db_dir = 'assets/db';
            $conn_o = new PDO("sqlite:" . $db_dir . "/orders.db");
            $new_order_id = 'ORD-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

            $stmt = $conn_o->prepare("INSERT INTO orders (order_id, customer_id) VALUES (?, ?)");
            $stmt->execute([$new_order_id, $_GET['customer_id']]);

            header("Location: index.php?customer_id=" . urlencode($_GET['customer_id']) . "&order_id=" . $new_order_id);
            exit();
        }

        if ($is_new_order) {
            $current_customer = $_GET['customer_id'];
            $current_order = $_GET['order_id'] ?? null;
        }

        include $active_route['page'];
        ?>
    </div>
    <footer class="footer" role="contentinfo">
        <?php if ($selected_loc): ?>
                <div class="active-loc-display">
                    <div class="loc-label">Active Location</div>
                    <a href="index.php?view=warehouse&sector=<?= urlencode($selected_sector) ?>" class="loc-active-badge">
                        <span class="loc-pin">📍</span>
                        <span class="loc-text"><?= htmlspecialchars($selected_loc) ?></span>
                        <span class="loc-change">Change</span>
                    </a>
                </div>
            <?php else: "";?>
            <?php endif; ?><hr />
    <nav class="breadcrumbs">
            <a href="../app/"
                class="crumb <?= !isset($_GET['customer_id']) && !isset($_GET['view']) ? 'active' : '' ?>">
                <span class="step-num">📦</span> Labels
            </a>
            <a href="index.php"
                class="crumb <?= !isset($_GET['customer_id']) && !isset($_GET['view']) ? 'active' : '' ?>">
                <span class="step-num">&#8507;</span> Customers
            </a>

            <?php if (isset($_GET['view']) && $_GET['view'] === 'register'): ?>
            <span class="separator">/</span>
            <a href="#" class="crumb active">
                <span class="step-num">2</span> Register
            </a>
            <?php endif; ?>

            <?php if (isset($_GET['customer_id'])): ?>
            <span class="separator">/</span>
            <a href="#" class="crumb active">
                <span class="step-num">2</span> Order Entry
            </a>
            <?php endif; ?>

            <?php if (isset($_GET['view']) && $_GET['view'] === 'settings'): ?>
            <span class="separator">/</span>
            <a href="#" class="crumb active">
                <span class="step-num">⚙️</span> Settings
            </a>
            <?php endif; ?>
        </nav>
    </footer>
    <!-- Load compiled JavaScript directly for performance/mobile compatibility -->
    <script src="assets/js/new_order.js?v=<?= filemtime('assets/js/new_order.js') ?>" defer></script>
    <script src="assets/js/warehouse.js?v=<?= filemtime('assets/js/warehouse.js') ?>" defer></script>
    <script src="assets/js/customer_registry.js?v=<?= filemtime('assets/js/customer_registry.js') ?>" defer></script>
</body>

</html>