<?php
// api_delete_match.php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

// Chỉ Admin mới được xóa
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    echo json_encode(['status' => 'error', 'message' => 'Không có quyền!']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

try {
    $stmt = $pdo->prepare("DELETE FROM matches WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success', 'message' => 'Đã xóa tin cáp kèo!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>