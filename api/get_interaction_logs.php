<?php
include '../core/auth.php';

header('Content-Type: application/json');

$customer_id = $_GET['customer_id'] ?? null;
if (!$customer_id) {
    echo json_encode([]);
    exit();
}

$db_dir = '../assets/db';
$db_file = $db_dir . '/customers.db';

try {
    $conn = new PDO("sqlite:" . $db_file);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM interaction_logs WHERE customer_id = ? ORDER BY contact_date DESC, created_at DESC");
    $stmt->execute([$customer_id]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($logs);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
