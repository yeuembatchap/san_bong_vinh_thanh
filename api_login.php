<?php
// api_login.php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$phone = $input['phone'] ?? '';
$pass  = $input['password'] ?? '';

try {
    // 1. Tìm user theo SĐT
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone_number = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Kiểm tra mật khẩu
    if ($user && password_verify($pass, $user['password_hash'])) {
        
        // --- ĐÃ XÓA ĐOẠN KIỂM TRA QUYỀN ADMIN Ở ĐÂY ---
        // Cho phép cả CUSTOMER và ADMIN đăng nhập

        // 3. Lưu Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role']; // Lưu quyền hạn để dùng sau này
        $_SESSION['logged_in'] = true;

        echo json_encode([
            'status' => 'success', 
            'message' => 'Đăng nhập thành công!',
            'role' => $user['role'] // Trả về role cho Frontend biết
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sai số điện thoại hoặc mật khẩu!']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>