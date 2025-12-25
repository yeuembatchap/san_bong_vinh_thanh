<?php
// api_get_schedule.php
header('Content-Type: application/json');
require 'db_connect.php'; // Đảm bảo đường dẫn đúng

try {
    if (!isset($_GET['field_id']) || !isset($_GET['date'])) {
        throw new Exception("Thiếu field_id hoặc date");
    }

    $field_id = $_GET['field_id'];
    $date = $_GET['date']; // Định dạng: YYYY-MM-DD

    // Lấy tất cả đơn đặt chưa bị hủy trong ngày đó
    $sql = "SELECT start_time, end_time 
            FROM bookings 
            WHERE field_id = ? 
            AND DATE(start_time) = ? 
            AND status IN ('CONFIRMED', 'PENDING', 'COMPLETED')";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$field_id, $date]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $bookings]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>