<?php
// api_booking.php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

// Kiểm tra dữ liệu đầu vào
if (!isset($input['user_id'], $input['field_id'], $input['start_time'], $input['end_time'], $input['payment_method'])) {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu dữ liệu đầu vào']);
    exit;
}

// 1. Kiểm tra xem giờ đó đã có ai đặt chưa (trừ những đơn đã Hủy)
$sqlCheck = "SELECT COUNT(*) FROM bookings 
             WHERE field_id = ? 
             AND status != 'CANCELLED'
             AND (
                (start_time < ? AND end_time > ?) OR
                (start_time >= ? AND start_time < ?)
             )";
$stmtCheck = $pdo->prepare($sqlCheck);
$stmtCheck->execute([
    $input['field_id'],
    $input['end_time'], $input['start_time'],
    $input['start_time'], $input['end_time']
]);

if ($stmtCheck->fetchColumn() > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Giờ này đã có người đặt rồi!']);
    exit;
}

// 2. Tính tiền (Lấy giá từ DB để bảo mật, không tin dữ liệu từ Client gửi lên)
$stmtPrice = $pdo->prepare("SELECT price_per_hour FROM fields WHERE id = ?");
$stmtPrice->execute([$input['field_id']]);
$pricePerHour = $stmtPrice->fetchColumn();

$start = strtotime($input['start_time']);
$end = strtotime($input['end_time']);
$hours = ($end - $start) / 3600;
$totalPrice = $hours * $pricePerHour;

// 3. Xử lý Trạng thái dựa trên Phương thức thanh toán
$paymentMethod = $input['payment_method']; // 'CASH' hoặc 'TRANSFER'
// Nếu chuyển khoản -> Tự động xác nhận (CONFIRMED). Nếu tiền mặt -> Chờ duyệt (PENDING)
$status = ($paymentMethod === 'TRANSFER') ? 'CONFIRMED' : 'PENDING';

// 4. Lưu vào DB
try {
    $sqlInsert = "INSERT INTO bookings (user_id, field_id, start_time, end_time, total_price, status, payment_method) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sqlInsert);
    $stmt->execute([
        $input['user_id'],
        $input['field_id'],
        $input['start_time'],
        $input['end_time'],
        $totalPrice,
        $status,
        $paymentMethod
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Đặt sân thành công!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>