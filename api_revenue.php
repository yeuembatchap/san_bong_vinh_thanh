<?php
// api_revenue.php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

// Check Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'ADMIN') {
    echo json_encode(['status' => 'error', 'message' => 'No permission']);
    exit;
}

$from = $_GET['from'] ?? date('Y-m-01'); // Mặc định từ đầu tháng
$to   = $_GET['to']   ?? date('Y-m-d');  // Đến hôm nay

try {
    // 1. LẤY DỮ LIỆU VẼ BIỂU ĐỒ (Gom nhóm theo ngày)
    // Chỉ tính đơn CONFIRMED (đã thu tiền)
    $sqlChart = "SELECT DATE(start_time) as date, SUM(total_price) as daily_total 
                 FROM bookings 
                 WHERE status = 'CONFIRMED' 
                 AND DATE(start_time) BETWEEN ? AND ?
                 GROUP BY DATE(start_time)
                 ORDER BY date ASC";
    $stmtChart = $pdo->prepare($sqlChart);
    $stmtChart->execute([$from, $to]);
    $chartData = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

    // 2. LẤY CHI TIẾT ĐƠN HÀNG (Để hiển thị bảng và xuất Excel)
    $sqlList = "SELECT b.id, u.full_name, f.name as field_name, b.start_time, b.total_price, b.payment_method
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN fields f ON b.field_id = f.id
                WHERE b.status = 'CONFIRMED'
                AND DATE(b.start_time) BETWEEN ? AND ?
                ORDER BY b.start_time DESC";
    $stmtList = $pdo->prepare($sqlList);
    $stmtList->execute([$from, $to]);
    $listData = $stmtList->fetchAll(PDO::FETCH_ASSOC);

    // 3. TÍNH TỔNG DOANH THU CẢ KỲ
    $totalRevenue = 0;
    foreach ($chartData as $day) {
        $totalRevenue += $day['daily_total'];
    }

    echo json_encode([
        'status' => 'success',
        'summary' => [
            'total_revenue' => $totalRevenue,
            'total_orders' => count($listData)
        ],
        'chart_data' => $chartData,
        'list_data' => $listData
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>