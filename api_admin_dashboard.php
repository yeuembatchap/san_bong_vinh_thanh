<?php
// api_admin_dashboard.php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *"); 
require 'db_connect.php'; 

try {
    $dateParam = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

    // --- SỬA ĐOẠN NÀY ---
    // Sử dụng CASE WHEN để đếm riêng từng loại trạng thái
    $sqlToday = "SELECT 
                    -- 1. Đếm số đơn ĐÃ CHỐT hoặc HOÀN THÀNH
                    COUNT(CASE WHEN status IN ('CONFIRMED', 'COMPLETED') THEN 1 END) as total_bookings,
                    
                    -- 2. Tính tổng tiền (chỉ tính đơn đã chốt)
                    COALESCE(SUM(CASE WHEN status IN ('CONFIRMED', 'COMPLETED') THEN total_price ELSE 0 END), 0) as daily_revenue,

                    -- 3. Đếm số đơn CHỜ DUYỆT (PENDING) - Đây là cái bạn đang thiếu
                    COUNT(CASE WHEN status = 'PENDING' THEN 1 END) as pending_count
                 FROM bookings 
                 WHERE DATE(start_time) = :d"; 
                 // Lưu ý: Đã bỏ điều kiện 'AND status IN...' ở cuối để lấy tất cả đơn
    // --------------------

    $stmtToday = $pdo->prepare($sqlToday);
    $stmtToday->execute([':d' => $dateParam]);
    $statsToday = $stmtToday->fetch(PDO::FETCH_ASSOC);

    // Các phần dưới giữ nguyên...
    $sqlRecent = "SELECT b.id, u.full_name, f.name as field_name, b.start_time, b.end_time, b.total_price, b.status, b.payment_method 
                  FROM bookings b
                  LEFT JOIN users u ON b.user_id = u.id
                  LEFT JOIN fields f ON b.field_id = f.id
                  WHERE DATE(b.start_time) = :d
                  ORDER BY b.created_at DESC";
    $stmtRecent = $pdo->prepare($sqlRecent);
    $stmtRecent->execute([':d' => $dateParam]);
    $recentBookings = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);

    // Phần Match và Fields giữ nguyên (rút gọn để bạn dễ copy)
    $matchesData = [];
    try {
        $matchesData = $pdo->query("SELECT m.id, u.full_name, m.type, m.match_date, m.match_time, m.message, m.level, m.contact_phone FROM matches m LEFT JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    $fieldsData = [];
    try {
        $fieldsData = $pdo->query("SELECT id, name, price_per_hour FROM fields ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    echo json_encode([
        'status'          => 'success',
        'stats'           => $statsToday,
        'recent_bookings' => $recentBookings,
        'matches'         => $matchesData,
        'fields'          => $fieldsData
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>