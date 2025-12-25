<?php
// api_fields.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require 'db_connect.php';

// Check Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'ADMIN') {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        echo json_encode(['status' => 'error', 'message' => 'No permission']);
        exit;
    }
}

$method = $_SERVER['REQUEST_METHOD'];

// --- GET: LẤY DANH SÁCH / CHI TIẾT ---
if ($method === 'GET') {
    try {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM fields WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
        } else {
            // Lấy tất cả, sắp xếp theo ID
            $stmt = $pdo->query("SELECT * FROM fields ORDER BY id ASC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// --- POST: THÊM / SỬA / XÓA (ADMIN ONLY) ---
elseif ($method === 'POST') {
    
    // Xử lý XÓA (Giữ nguyên)
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM fields WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Đã xóa sân!']);
        exit;
    }

    // Xử lý THÊM / SỬA
    try {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $desc = isset($_POST['description']) ? $_POST['description'] : ''; 
        
        // --- XỬ LÝ TRẠNG THÁI (MỚI) ---
        // Giao diện gửi lên: name="status" giá trị 'ACTIVE' hoặc 'MAINTENANCE'
        // Database cần: is_active (1 hoặc 0)
        $statusInput = $_POST['status'] ?? 'ACTIVE'; 
        $isActive = ($statusInput === 'ACTIVE') ? 1 : 0;
        // -------------------------------

        $action = $_POST['action'];
        $id = $_POST['id'] ?? null;
        
        if (isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
        $id = $_POST['id'];
        $newStatus = $_POST['status']; // 0 hoặc 1
        
        $stmt = $pdo->prepare("UPDATE fields SET is_active = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
        
        echo json_encode(['status' => 'success', 'message' => 'Đã cập nhật trạng thái!']);
        exit;
        }

        // Upload ảnh
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $targetDir = "uploads/";
            if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
            $fileName = time() . "_" . basename($_FILES["image"]["name"]);
            $imagePath = $targetDir . $fileName;
            move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
        }

        if ($action === 'add') {
            // Cập nhật câu lệnh INSERT: Thêm is_active
            $sql = "INSERT INTO fields (name, description, image, price_per_hour, is_active) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            // Thêm biến $isActive vào cuối mảng
            $stmt->execute([$name, $desc, $imagePath, $price, $isActive]);
        } 
        elseif ($action === 'update') {
            // Trường hợp 1: Có thay đổi ảnh
            if ($imagePath) {
                // Cập nhật câu lệnh UPDATE: Thêm is_active
                $sql = "UPDATE fields SET name=?, description=?, image=?, price_per_hour=?, is_active=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $desc, $imagePath, $price, $isActive, $id]);
            } 
            // Trường hợp 2: Không thay đổi ảnh
            else {
                // Cập nhật câu lệnh UPDATE: Thêm is_active
                $sql = "UPDATE fields SET name=?, description=?, price_per_hour=?, is_active=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $desc, $price, $isActive, $id]);
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Lưu thành công!']);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}
?>