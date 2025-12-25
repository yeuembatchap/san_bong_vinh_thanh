<?php
// api_update_price.php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

// Check quyền Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    echo json_encode(['status' => 'error', 'message' => 'Không có quyền!']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$fieldId = $input['id'];
$newPrice = $input['price'];

try {
    $stmt = $pdo->prepare("UPDATE fields SET price_per_hour = ? WHERE id = ?");
    $stmt->execute([$newPrice, $fieldId]);
    echo json_encode(['status' => 'success', 'message' => 'Cập nhật giá thành công!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>