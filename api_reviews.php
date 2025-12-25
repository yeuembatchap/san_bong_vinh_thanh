<?php
// api_reviews.php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

$method = $_SERVER['REQUEST_METHOD'];

// --- GET: LẤY DANH SÁCH ĐÁNH GIÁ CỦA 1 SÂN ---
if ($method === 'GET') {
    $field_id = $_GET['field_id'] ?? 0;
    try {
        $sql = "SELECT r.*, u.full_name 
                FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.field_id = ? 
                ORDER BY r.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$field_id]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Tính điểm trung bình
        $avgSql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE field_id = ?";
        $stmtAvg = $pdo->prepare($avgSql);
        $stmtAvg->execute([$field_id]);
        $stats = $stmtAvg->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success', 
            'data' => $reviews,
            'stats' => $stats
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// --- POST: GỬI ĐÁNH GIÁ MỚI ---
elseif ($method === 'POST') {
    // 1. Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập để đánh giá!']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    // 2. Validate dữ liệu
    if (empty($input['field_id']) || empty($input['rating'])) {
        echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin đánh giá']);
        exit;
    }

    try {
        $sql = "INSERT INTO reviews (user_id, field_id, rating, comment) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_SESSION['user_id'],
            $input['field_id'],
            $input['rating'],
            $input['comment'] ?? ''
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Cảm ơn bạn đã đánh giá!']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}
?>