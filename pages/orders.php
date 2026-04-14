<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_dir = 'assets/db';
$db_file = $db_dir . '/orders.db';
$db_cust = $db_dir . '/customers.db';

try {
    $conn = new PDO("sqlite:" . $db_file);
    $conn_c = new PDO("sqlite:" . $db_cust);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle Global Status Updates
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
        if ($_POST['action'] === 'update_order_status') {
            $ord_id = $_POST['order_id'];
            $new_status = $_POST['new_status'];
            $stmt_u = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
            $stmt_u->execute([$new_status, $ord_id]);

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['status' => 'success']);
                exit();
            }
        } elseif ($_POST['action'] === 'transfer_order') {
            $ord_id = $_POST['order_id'];
            $new_cust_id = $_POST['new_customer_id'];

            // Update orders
            $stmt_o = $conn->prepare("UPDATE orders SET customer_id = ? WHERE order_id = ?");
            $stmt_o->execute([$new_cust_id, $ord_id]);

            // Update items
            $stmt_i = $conn->prepare("UPDATE items SET customer_id = ? WHERE order_id = ?");
            $stmt_i->execute([$new_cust_id, $ord_id]);
        }
        
        header("Location: index.php?view=orders" . (isset($_GET['type']) ? "&type=".$_GET['type'] : ""));
        exit();
    }

    // Fetch Filtering parameters
    $show_type = $_GET['type'] ?? 'active'; // 'active' vs 'completed'

    // Fetch all orders with customer details
    if ($show_type === 'completed') {
        $stmt = $conn->query("SELECT o.* FROM orders o WHERE o.status IN ('finalized', 'paid', 'dispatched', 'canceled') ORDER BY o.created_at DESC");
    } else {
        $stmt = $conn->query("SELECT o.* FROM orders o WHERE o.status NOT IN ('finalized', 'paid', 'dispatched', 'canceled') OR o.status IS NULL ORDER BY o.created_at DESC");
    }
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all customers for transfer dropdown
    $stmt_c = $conn_c->query("SELECT customer_id, company_name FROM customers ORDER BY company_name ASC");
    $all_customers = $stmt_c->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}
?>

<!-- Load dedicated registry styles -->


<div class="orders-container">
    <header class="orders-header">
        <div>
            <h1>Global Batch Registry</h1>
            <p class="subtitle orders-subtitle">Review batches and manage fulfillment states across all accounts.</p>
        </div>
            <div class="orders-tabs">
                <a href="index.php?view=orders&type=active" class="orders-tab-link <?= $show_type === 'active' ? 'active' : 'inactive' ?>">Active Batches</a>
                <a href="index.php?view=orders&type=completed" class="orders-tab-link <?= $show_type === 'completed' ? 'active' : 'inactive' ?>">Finalized History</a>
            </div>
    </header>

    <!-- Live Search Input -->
    <div class="orders-search-wrapper">
        <i class="search-icon">🔍</i>
        <input type="text" id="order-search" placeholder="Search by Order ID, Company, or Customer ID..." aria-label="Search orders" onkeyup="filterOrders()">
    </div>

    <div class="orders-grid" id="orders-grid">
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): 
                $c_stmt = $conn_c->prepare("SELECT company_name FROM customers WHERE customer_id = ?");
                $c_stmt->execute([$order['customer_id']]);
                $company = $c_stmt->fetchColumn() ?: 'Unknown Account';
                $status = strtolower($order['status']);
                
                // Combine all searchable terms into a single attribute for efficiency
                $search_blob = strtolower($order['order_id'] . " " . $company . " " . $order['customer_id']);

                $status_class = "status-" . $status;
            ?>
            <div class="order-card" data-search="<?= htmlspecialchars($search_blob) ?>">
                <!-- Status Banner -->
                <div class="order-card-header">
                    <span class="order-badge <?= $status_class ?>">
                        <?= htmlspecialchars($status) ?>
                    </span>
                    <div class="order-timestamp">
                        <?= date('M d, Y', strtotime($order['created_at'])) ?>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="order-account-info">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap: 10px;">
                        <div class="order-company-title"><?= htmlspecialchars($company) ?></div>
                        <button type="button" class="btn-transfer-small no-print" onclick="openTransferModal('<?= htmlspecialchars($order['order_id']) ?>', '<?= htmlspecialchars($order['customer_id']) ?>')" title="Transfer to another customer" style="background:none; border:none; cursor:pointer; font-size: 0.8rem; opacity:0.5;">⇄</button>
                    </div>
                    <div class="order-id-meta"><?= htmlspecialchars($order['order_id']) ?></div>
                </div>

                <!-- Management & Action Area -->
                <div class="order-action-row">
                    <form method="POST" onchange="this.submit()" class="status-form">
                        <input type="hidden" name="action" value="update_order_status">
                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                        <div class="select-wrapper">
                            <select name="new_status" class="order-status-select">
                                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="dispatched" <?= $status === 'dispatched' ? 'selected' : '' ?>>Dispatched</option>
                                <option value="canceled" <?= $status === 'canceled' ? 'selected' : '' ?>>Canceled</option>
                                <option value="finalized" <?= $status === 'finalized' ? 'selected' : '' ?>>Finalized</option>
                            </select>
                        </div>
                    </form>
                    <a href="checkout.php?customer_id=<?= urlencode($order['customer_id']) ?>&order_id=<?= urlencode($order['order_id']) ?>" class="btn-order-view">
                        <span>View Details</span>
                        <i class="arrow-icon">→</i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Transfer Order Modal -->
<div id="transferOrderModal" class="modal-overlay no-print" onclick="if(event.target === this) closeTransferModal()" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:20px; width:90%; max-width:400px; padding:25px; box-shadow:var(--shadow-lg);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <h3 style="font-weight: 800; font-size: 1.15rem;">⇄ Transfer Order</h3>
            <button type="button" onclick="closeTransferModal()" style="background:none; border:none; cursor:pointer; font-size:1.5rem; opacity:0.5;">&times;</button>
        </div>
        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 20px;">Move this batch to a different customer account.</p>
        
        <form method="POST">
            <input type="hidden" name="action" value="transfer_order">
            <input type="hidden" name="order_id" id="transfer_order_id">
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display:block; font-size:0.7rem; font-weight:800; text-transform:uppercase; margin-bottom:5px; color:var(--text-secondary);">Select Target Customer</label>
                <select name="new_customer_id" id="transfer_new_customer_id" required style="width:100%; height:44px; border-radius:10px; border:1px solid var(--border-color); padding:0 10px; font-weight:600;">
                    <?php foreach($all_customers as $c): ?>
                        <option value="<?= htmlspecialchars($c['customer_id']) ?>">
                            <?= htmlspecialchars($c['company_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-main" style="width: 100%; border:none; cursor:pointer; height: 48px; background: var(--accent-color); color: white; border-radius: 12px; font-weight: 800;">
                Confirm Transfer
            </button>
        </form>
    </div>
</div>

<script>
    function openTransferModal(orderId, currentCustId) {
        document.getElementById('transfer_order_id').value = orderId;
        document.getElementById('transfer_new_customer_id').value = currentCustId;
        document.getElementById('transferOrderModal').style.display = 'flex';
    }
    function closeTransferModal() {
        document.getElementById('transferOrderModal').style.display = 'none';
    }
</script>

<script src="assets/js/orders.js"></script>
