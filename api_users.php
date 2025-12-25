<?php
// api_users.php
session_start();
header('Content-Type: application/json');
require 'db_connect.php';

// 1. CHẶN QUYỀN (Chỉ Admin mới được vào)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'ADMIN') {
    echo json_encode(['status' => 'error', 'message' => 'Không có quyền truy cập']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// --- GET: LẤY DANH SÁCH ---
if ($method === 'GET') {
    try {
        $keyword = $_GET['keyword'] ?? '';
        $sql = "SELECT id, full_name, email, phone_number, role, created_at 
                FROM users 
                WHERE full_name LIKE ? OR email LIKE ? OR phone_number LIKE ?
                ORDER BY id DESC";     
        $stmt = $pdo->prepare($sql);
        $search = "%$keyword%";
        $stmt->execute([$search, $search, $search]);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// --- POST: THÊM / SỬA / XÓA ---
elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? ''; 

    try {
        // 1. XÓA USER
        if ($action === 'delete') {
            if ($input['user_id'] == $_SESSION['user_id']) {
                echo json_encode(['status' => 'error', 'message' => 'Không thể tự xóa chính mình!']); exit;
            }
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$input['user_id']]);
            echo json_encode(['status' => 'success', 'message' => 'Đã xóa người dùng!']);
        }

        // 2. TẠO USER MỚI
        elseif ($action === 'create') {
            // Kiểm tra email trùng
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$input['email']]);
            if ($check->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Email này đã tồn tại!']); exit;
            }

            $passHash = password_hash($input['password'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (full_name, email, password_hash, phone_number, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$input['full_name'], $input['email'], $passHash, $input['phone'], $input['role']]);
            
            echo json_encode(['status' => 'success', 'message' => 'Thêm thành viên thành công!']);
        }

        // 3. SỬA THÔNG TIN (UPDATE)
        elseif ($action === 'update_info') {
            $id = $input['user_id'];
            $pass = $input['password']; // Nếu rỗng thì không đổi pass

            if (!empty($pass)) {
                // Có đổi mật khẩu
                $passHash = password_hash($pass, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET full_name=?, email=?, phone_number=?, role=?, password_hash=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$input['full_name'], $input['email'], $input['phone'], $input['role'], $passHash, $id]);
            } else {
                // Không đổi mật khẩu
                $sql = "UPDATE users SET full_name=?, email=?, phone_number=?, role=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$input['full_name'], $input['email'], $input['phone'], $input['role'], $id]);
            }
            echo json_encode(['status' => 'success', 'message' => 'Cập nhật thành công!']);
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}
?>