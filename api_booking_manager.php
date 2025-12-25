<?php
// api_booking_manager.php
header('Content-Type: application/json; charset=utf-8');
require 'db_connect.php'; // Kết nối DB

try {
    // 1. Nhận các tham số lọc từ Client gửi lên
    $dateFrom = isset($_GET['from']) ? $_GET['from'] : date('Y-m-01'); // Mặc định từ đầu tháng
    $dateTo   = isset($_GET['to']) ? $_GET['to'] : date('Y-m-t');     // Mặc định đến cuối tháng
    $status   = isset($_GET['status']) ? $_GET['status'] : '';        // Lọc theo trạng thái
    $search   = isset($_GET['search']) ? $_GET['search'] : '';        // Tìm theo tên/sđt

    // 2. Xây dựng câu Query động
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
            WHERE DATE(b.start_time) BETWEEN :dateFrom AND :dateTo";

    $params = [
        ':dateFrom' => $dateFrom,
        ':dateTo'   => $dateTo
    ];

    // Nếu có lọc trạng thái
    if (!empty($status)) {
        $sql .= " AND b.status = :status";
        $params[':status'] = $status;
    }

    // Nếu có tìm kiếm từ khóa
    if (!empty($search)) {
        $sql .= " AND (u.full_name LIKE :search OR u.phone_number LIKE :search OR b.id = :idSearch)";
        $params[':search'] = "%$search%";
        $params[':idSearch'] = $search; // Tìm theo mã đơn
    }

    $sql .= " ORDER BY b.start_time DESC"; // Đơn mới nhất lên đầu

    // 3. Thực thi
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $bookings]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>