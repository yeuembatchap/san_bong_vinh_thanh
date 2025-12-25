<?php
// setup_password.php
require 'db_connect.php';

// Mật khẩu muốn đặt (Ví dụ: 123456)
$new_password = '123456';
// Mã hóa nó
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

try {
    // Cập nhật cho tài khoản có SĐT 0901234567 (Admin mẫu)
    $sql = "UPDATE users SET password_hash = ? WHERE phone_number = '0901234567'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hashed_password]);
    
    echo "✅ Đã cập nhật mật khẩu Admin thành công! <br>";
    echo "Tài khoản: 0901234567 <br>";
    echo "Mật khẩu: 123456 <br>";
    echo "Mã hash trong DB: " . $hashed_password;
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>