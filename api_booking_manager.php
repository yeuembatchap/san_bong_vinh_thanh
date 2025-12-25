<?php
// api_booking_manager.php
header('Content-Type: application/json; charset=utf-8');

// Tắt báo lỗi ra màn hình để tránh làm hỏng JSON, chỉ log lỗi
error_reporting(0); 
ini_set('display_errors', 0);

require 'db_connect.php';

try {
    // 1. Nhận tham số
    $dateFrom = $_GET['from'] ?? date('Y-m-01');
    $dateTo   = $_GET['to'] ?? date('Y-m-t');
    $status   = $_GET['status'] ?? '';
    $search   = trim($_GET['search'] ?? ''); // Xóa khoảng trắng thừa

    // 2. Query cơ bản
    // Lưu ý: Dùng start_time >= ... 00:00:00 để chính xác hơn và tận dụng index (tốt hơn dùng hàm DATE())
    $sql = "SELECT 
                b.id, 
                u.full_name, 
                u.phone_number,
                f.name as field_name, 
                b.start_time, 
                b.end_time, 
                b.total_price, 
                b.status, 
                b.payment_method,
                b.created_at
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN fields f ON b.field_id = f.id
            WHERE b.start_time >= :dateFrom AND b.start_time <= :dateTo";

    $params = [
        ':dateFrom' => $dateFrom . ' 00:00:00', // Bắt đầu ngày
        ':dateTo'   => $dateTo . ' 23:59:59'    // Kết thúc ngày
    ];

    // 3. Lọc theo trạng thái
    if (!empty($status)) {
        $sql .= " AND b.status = :status";
        $params[':status'] = $status;
    }

    // 4. Xử lý tìm kiếm (Đã sửa lỗi ID)
    if (!empty($search)) {
        // Mở ngoặc đơn để gom nhóm điều kiện OR
        $sql .= " AND (u.full_name LIKE :searchName OR u.phone_number LIKE :searchPhone";
        
        $params[':searchName'] = "%$search%";
        $params[':searchPhone'] = "%$search%";

        // Chỉ tìm theo ID nếu từ khóa là số
        if (is_numeric($search)) {
            $sql .= " OR b.id = :searchId";
            $params[':searchId'] = $search;
        }

        $sql .= ")"; // Đóng ngoặc đơn quan trọng
    }

    $sql .= " ORDER BY b.start_time DESC";

    // 5. Thực thi
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $bookings]);

} catch (Exception $e) {
    // Trả về JSON lỗi để JS hiển thị alert
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>