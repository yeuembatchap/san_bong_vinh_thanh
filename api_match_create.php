<?php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $sql = "INSERT INTO matches (user_id, match_date, match_time, level, type, message, contact_phone) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_SESSION['user_id'],
        $input['date'],
        $input['time'],
        $input['level'],
        $input['type'],
        $input['message'],
        $input['phone']
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Đăng tin tìm kèo thành công!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>