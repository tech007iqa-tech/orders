<?php
include '../core/auth.php';

$db_dir = '../assets/db';
$db_file = $db_dir . '/orders.db';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

$ord_id = $_POST['order_id'] ?? null;
$new_cust_id = $_POST['new_customer_id'] ?? null;

if (!$ord_id || !$new_cust_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

try {
    $conn = new PDO("sqlite:" . $db_file);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->beginTransaction();

    // Update orders table
    $stmt_o = $conn->prepare("UPDATE orders SET customer_id = ? WHERE order_id = ?");
    $stmt_o->execute([$new_cust_id, $ord_id]);

    // Update items table (assuming it's in the same DB or handled)
    $stmt_i = $conn->prepare("UPDATE items SET customer_id = ? WHERE order_id = ?");
    $stmt_i->execute([$new_cust_id, $ord_id]);

    $conn->commit();

    echo json_encode(['status' => 'success', 'message' => 'Order transferred successfully']);
} catch (PDOException $e) {
    if ($conn) $conn->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
