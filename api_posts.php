<?php
// api_posts.php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

// 1. CHECK ADMIN
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'ADMIN') {
    echo json_encode(['status' => 'error', 'message' => 'No permission']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// --- GET: LẤY DANH SÁCH BÀI VIẾT ---
if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// --- POST: THÊM BÀI VIẾT MỚI (CÓ UPLOAD ẢNH) ---
elseif ($method === 'POST') {
    // Kiểm tra nếu là hành động XÓA (Gửi qua JSON body hoặc Form param)
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Đã xóa bài viết']);
        exit;
    }

    // XỬ LÝ THÊM MỚI
    try {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $type = $_POST['type'];
        $imagePath = '';

        // Xử lý upload ảnh
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $targetDir = "uploads/";
            if (!file_exists($targetDir)) mkdir($targetDir, 0777, true); // Tạo folder nếu chưa có
            
            $fileName = time() . "_" . basename($_FILES["image"]["name"]);
            $targetFile = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $imagePath = $targetFile;
            }
        }

        $sql = "INSERT INTO posts (title, content, type, image) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $content, $type, $imagePath]);

        echo json_encode(['status' => 'success', 'message' => 'Đăng bài thành công!']);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}
?>