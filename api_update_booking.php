<?php
// api_update_booking.php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

// 1. Bảo mật: Chỉ Admin mới được thực hiện
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền này!']);
    exit;
}

// 2. Nhận dữ liệu
$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;
$new_status = $input['status'] ?? null; // 'CONFIRMED' hoặc 'CANCELLED'

if (!$id || !$new_status) {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu dữ liệu']);
    exit;
}

try {
    // 3. Cập nhật trạng thái
    $sql = "UPDATE bookings SET status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_status, $id]);

    echo json_encode(['status' => 'success', 'message' => 'Cập nhật thành công!']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Lỗi Server: ' . $e->getMessage()]);
}
?>