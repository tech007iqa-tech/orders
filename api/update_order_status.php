<?php
include '../core/auth.php';
include '../core/warehouse_db.php'; // We might need this for orders too, but let's check which DB is used

$db_dir = '../assets/db';
$db_file = $db_dir . '/orders.db';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

$ord_id = $_POST['order_id'] ?? null;
$new_status = $_POST['new_status'] ?? null;

if (!$ord_id || !$new_status) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

try {
    $conn = new PDO("sqlite:" . $db_file);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt_u = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt_u->execute([$new_status, $ord_id]);

    echo json_encode(['status' => 'success', 'message' => 'Order status updated']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
