<?php
// api_get_dashboard.php
header('Content-Type: application/json');
require 'db_connect.php';

$filterDate = $_GET['date'] ?? date('Y-m-d');

try {
    // 1. Thống kê & 2. Đếm số đơn & 3. Đơn hàng (GIỮ NGUYÊN CODE CŨ)
    $stmt1 = $pdo->prepare("SELECT COUNT(*) as total_orders, SUM(total_price) as revenue FROM bookings WHERE DATE(created_at) = ? AND status != 'CANCELLED'");
    $stmt1->execute([$filterDate]);
    $statsToday = $stmt1->fetch(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'PENDING'");
    $pendingCount = $stmt2->fetchColumn();

    $stmt3 = $pdo->prepare("SELECT b.id, u.full_name, f.name as field_name, b.start_time, b.end_time, b.total_price, b.status 
                            FROM bookings b JOIN users u ON b.user_id = u.id JOIN fields f ON b.field_id = f.id
                            WHERE DATE(b.start_time) = ? ORDER BY b.created_at DESC");
    $stmt3->execute([$filterDate]);
    $bookings = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    $stmt4 = $pdo->query("SELECT id, name, price_per_hour FROM fields");
    $fields = $stmt4->fetchAll(PDO::FETCH_ASSOC);

    // --- PHẦN MỚI THÊM VÀO ĐÂY ---
    // 5. Lấy danh sách tin Cáp kèo (Mới nhất lên đầu)
    $stmt5 = $pdo->query("SELECT m.*, u.full_name 
                          FROM matches m 
                          JOIN users u ON m.user_id = u.id 
                          ORDER BY m.created_at DESC LIMIT 10");
    $matches = $stmt5->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'stats' => [
            'revenue' => $statsToday['revenue'] ?? 0,
            'orders' => $statsToday['total_orders'] ?? 0,
            'pending' => $pendingCount
        ],
        'bookings' => $bookings,
        'fields' => $fields,
        'matches' => $matches // Trả thêm cái này
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>