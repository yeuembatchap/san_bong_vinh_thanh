<?php
// api_get_my_bookings.php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Lấy thông tin đơn đặt + Tên sân
    $sql = "SELECT b.id, f.name as field_name, b.start_time, b.end_time, b.total_price, b.status 
            FROM bookings b
            JOIN fields f ON b.field_id = f.id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $bookings]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>