<?php
/**
 * db_connect.php
 * Kết nối CSDL MySQL bằng PDO
 */

$host = 'localhost';
$dbname = 'san';
$user = 'root';
$pass = '';

try {
    // Chuỗi kết nối
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

    // Tùy chọn PDO
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Báo lỗi Exception
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch dạng mảng
        PDO::ATTR_EMULATE_PREPARES   => false,                   // Dùng prepare thật
    ];

    // Tạo kết nối
    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {
    // Nếu kết nối lỗi → trả JSON
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Lỗi kết nối database',
        'error'   => $e->getMessage()
    ]);
    exit;
}
