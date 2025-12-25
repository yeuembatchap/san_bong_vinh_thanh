<?php
// api_register.php
header('Content-Type: application/json');
require 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

$fullName = $input['full_name'] ?? '';
$phone    = $input['phone'] ?? '';
$pass     = $input['password'] ?? '';

// Validate cơ bản
if (!$fullName || !$phone || !$pass) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin!']);
    exit;
}

try {
    // 1. Kiểm tra xem SĐT đã tồn tại chưa
    $check = $pdo->prepare("SELECT id FROM users WHERE phone_number = ?");
    $check->execute([$phone]);
    if ($check->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Số điện thoại này đã được đăng ký!']);
        exit;
    }

    // 2. Mã hóa mật khẩu
    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

    // 3. Tạo user mới (Mặc định role là CUSTOMER)
    $sql = "INSERT INTO users (full_name, phone_number, password_hash, role) VALUES (?, ?, ?, 'CUSTOMER')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fullName, $phone, $hashed_pass]);

    echo json_encode(['status' => 'success', 'message' => 'Đăng ký thành công! Hãy đăng nhập.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>