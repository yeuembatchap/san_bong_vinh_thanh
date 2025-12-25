<?php
session_start();
require 'db_connect.php';
header('Content-Type: application/json');

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập']);
    exit;
}

// 2. Lấy dữ liệu gửi lên
$data = json_decode(file_get_contents('php://input'), true);
$booking_id = $data['booking_id'] ?? 0;
$reason = $data['reason'] ?? '';

if (empty($booking_id) || empty($reason)) {
    echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

try {
    // 3. Lấy thông tin đơn đặt
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đơn đặt sân này']);
        exit;
    }

    // 4. Kiểm tra trạng thái hiện tại
    if ($booking['status'] === 'CANCELLED') {
        echo json_encode(['status' => 'error', 'message' => 'Đơn này đã hủy rồi']);
        exit;
    }
    if ($booking['status'] === 'COMPLETED') {
        echo json_encode(['status' => 'error', 'message' => 'Đơn này đã hoàn thành, không thể hủy']);
        exit;
    }

    // 5. QUAN TRỌNG: Kiểm tra quy tắc 24h
    $startTime = strtotime($booking['start_time']); // Giờ đá
    $now = time(); // Giờ hiện tại server
    $hoursDiff = ($startTime - $now) / 3600; // Đổi ra giờ

    if ($hoursDiff < 24) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Bạn chỉ có thể hủy lịch trước giờ đá ít nhất 24 tiếng! (Còn ' . round($hoursDiff, 1) . ' tiếng)'
        ]);
        exit;
    }

    // 6. Thực hiện hủy
    $updateStmt = $pdo->prepare("UPDATE bookings SET status = 'CANCELLED', cancellation_reason = ? WHERE id = ?");
    $updateStmt->execute([$reason, $booking_id]);

    echo json_encode(['status' => 'success', 'message' => 'Đã hủy lịch thành công!']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>