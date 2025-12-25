<?php
header('Content-Type: application/json');
require 'db_connect.php';

try {
    // Lấy danh sách các kèo đang OPEN, sắp xếp mới nhất lên đầu
    // JOIN với bảng users để lấy tên người đăng
    $sql = "SELECT m.*, u.full_name 
            FROM matches m 
            JOIN users u ON m.user_id = u.id 
            WHERE m.status = 'OPEN' AND m.match_date >= CURDATE()
            ORDER BY m.match_date ASC, m.match_time ASC";
            
    $stmt = $pdo->query($sql);
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $matches]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>