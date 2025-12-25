<?php
// Tên file: api_public_posts.php
header('Content-Type: application/json');
// Đảm bảo đường dẫn tới file db_connect.php là đúng
require 'db_connect.php'; 

try {
    // Lấy 6 bài mới nhất
    $stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 6");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $posts]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>