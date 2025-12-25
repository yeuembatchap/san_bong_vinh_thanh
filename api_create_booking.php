<?php
// api_create_booking.php
header('Content-Type: application/json; charset=utf-8');
require 'db_connect.php'; 

try {
    // 1. Nhận dữ liệu JSON từ Admin gửi lên
    $input = json_decode(file_get_contents('php://input'), true);

    $name = $input['full_name'] ?? 'Khách vãng lai';
    $phone = $input['phone_number'] ?? '';
    $field_id = $input['field_id'];
    $date = $input['booking_date'];
    $start = $input['start_time'];
    $end = $input['end_time'];
    $price = $input['total_price'];
    $payment = $input['payment_method'];

    // Validate cơ bản
    if(empty($field_id) || empty($date) || empty($start) || empty($end)) {
        throw new Exception("Vui lòng nhập đầy đủ thông tin sân và giờ đá.");
    }

    // 2. Xử lý User (Khách hàng)
    // Kiểm tra xem SĐT này đã có user chưa
    $userId = null;
    if (!empty($phone)) {
        $stmtUser = $pdo->prepare("SELECT id FROM users WHERE phone_number = ?");
        $stmtUser->execute([$phone]);
        $existingUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $userId = $existingUser['id'];
        } else {
            // Chưa có -> Tạo user mới tự động
            $sqlCreateUser = "INSERT INTO users (full_name, phone_number, password_hash, role) VALUES (?, ?, ?, 'customer')";
            // Mật khẩu mặc định là 123456 (đã hash hoặc để text tùy hệ thống của bạn)
            $defaultPass = password_hash('123456', PASSWORD_DEFAULT); 
            $stmtCreate = $pdo->prepare($sqlCreateUser);
            $stmtCreate->execute([$name, $phone, $defaultPass]);
            $userId = $pdo->lastInsertId();
        }
    }

    // 3. Format thời gian
    $startTimeFull = "$date $start"; // Ví dụ: 2024-12-20 17:00:00
    $endTimeFull = "$date $end";

    // 4. Kiểm tra TRÙNG LỊCH (Quan trọng)
    $sqlCheck = "SELECT COUNT(*) FROM bookings 
                 WHERE field_id = ? 
                 AND status IN ('CONFIRMED', 'PENDING')
                 AND (
                    (start_time < ? AND end_time > ?) -- Giờ bắt đầu nằm trong khoảng đã đặt
                 )";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$field_id, $endTimeFull, $startTimeFull]);
    
    if ($stmtCheck->fetchColumn() > 0) {
        throw new Exception("Giờ này sân đã có người đặt rồi! Vui lòng chọn giờ khác.");
    }

    // 5. Tạo Booking
    $sqlBooking = "INSERT INTO bookings (user_id, field_id, start_time, end_time, total_price, status, payment_method) 
                   VALUES (?, ?, ?, ?, ?, 'CONFIRMED', ?)";
    $stmtBooking = $pdo->prepare($sqlBooking);
    $stmtBooking->execute([$userId, $field_id, $startTimeFull, $endTimeFull, $price, $payment]);

    echo json_encode(['status' => 'success', 'message' => 'Tạo lịch đặt thành công!']);

} catch (Exception $e) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>