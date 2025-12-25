<?php
// api_users.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Tắt hiển thị lỗi PHP ra màn hình để tránh làm hỏng cấu trúc JSON
error_reporting(0);
ini_set('display_errors', 0);

require 'db_connect.php'; 

// 1. KIỂM TRA QUYỀN ADMIN
// Nếu không phải admin, trả về lỗi JSON để JS xử lý (thay vì chuyển hướng header location gây lỗi cú pháp)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'ADMIN') {
    echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền truy cập API này']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    // =================================================================
    // XỬ LÝ GET REQUEST (Lấy dữ liệu)
    // =================================================================
    if ($method === 'GET') {
        
        // --- CASE 1: LẤY LỊCH SỬ ĐẶT SÂN (Quan trọng) ---
        if (isset($_GET['action']) && $_GET['action'] === 'get_history') {
            $userId = $_GET['user_id'] ?? 0;
            
            // Query lấy dữ liệu từ bảng bookings và fields
            // Sắp xếp theo start_time giảm dần (mới nhất lên đầu)
            $sql = "SELECT b.*, f.name as field_name 
                    FROM bookings b 
                    LEFT JOIN fields f ON b.field_id = f.id 
                    WHERE b.user_id = ? 
                    ORDER BY b.start_time DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Xử lý dữ liệu để khớp với Javascript của bạn
            foreach ($history as &$h) {
                // 1. Xử lý tên sân nếu sân đã bị xóa khỏi database
                if (empty($h['field_name'])) {
                    $h['field_name'] = "Sân (ID: " . ($h['field_id'] ?? '?') . ") - Đã xóa";
                }

                // 2. TẠO KEY 'booking_date' CHO JS
                // JS của bạn dùng: new Date(h.booking_date)
                // DB của bạn có: start_time
                // -> Gán start_time vào booking_date
                if (empty($h['booking_date']) && !empty($h['start_time'])) {
                    $h['booking_date'] = $h['start_time']; 
                }

                // 3. ĐẢM BẢO SỐ TIỀN LÀ SỐ (NUMBER)
                // JS của bạn dùng: Intl.NumberFormat(...).format(h.deposit_amount)
                // Nên ta phải trả về số thực/nguyên, không trả về chuỗi text
                $h['deposit_amount'] = (float)($h['deposit_amount'] ?? 0);
                $h['total_price']    = (float)($h['total_price'] ?? 0);
            }

            echo json_encode(['status' => 'success', 'history' => $history]);
            exit;
        }

        // --- CASE 2: TÌM KIẾM USER (Cho hàm loadUsers) ---
        $keyword = $_GET['keyword'] ?? '';
        $sql = "SELECT id, full_name, email, phone_number, role, created_at 
                FROM users 
                WHERE full_name LIKE ? OR email LIKE ? OR phone_number LIKE ?
                ORDER BY id DESC";     
        $stmt = $pdo->prepare($sql);
        $search = "%$keyword%";
        $stmt->execute([$search, $search, $search]);
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $users]);
        exit;
    }

    // =================================================================
    // XỬ LÝ POST REQUEST (Thêm / Sửa / Xóa User)
    // =================================================================
    elseif ($method === 'POST') {
        // Nhận dữ liệu JSON từ Javascript gửi lên
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? ''; 

        // 1. XÓA USER
        if ($action === 'delete') {
            if ($input['user_id'] == $_SESSION['user_id']) {
                throw new Exception('Không thể tự xóa tài khoản đang đăng nhập!');
            }
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$input['user_id']]);
            echo json_encode(['status' => 'success', 'message' => 'Đã xóa người dùng thành công!']);
        }
        
        // 2. TẠO USER MỚI
        elseif ($action === 'create') {
            // Kiểm tra email trùng
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$input['email']]);
            if ($check->rowCount() > 0) throw new Exception('Email này đã tồn tại trong hệ thống!');

            // Mã hóa mật khẩu
            $passHash = password_hash($input['password'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (full_name, email, password_hash, phone_number, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $input['full_name'], 
                $input['email'], 
                $passHash, 
                $input['phone'], 
                $input['role']
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Thêm thành viên mới thành công!']);
        }
        
        // 3. CẬP NHẬT THÔNG TIN
        elseif ($action === 'update_info') {
            $id = $input['user_id'];
            $pass = $input['password'];
            
            // Nếu có nhập pass mới thì cập nhật cả pass
            if (!empty($pass)) {
                $passHash = password_hash($pass, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET full_name=?, email=?, phone_number=?, role=?, password_hash=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $input['full_name'], 
                    $input['email'], 
                    $input['phone'], 
                    $input['role'], 
                    $passHash, 
                    $id
                ]);
            } else {
                // Không đổi pass
                $sql = "UPDATE users SET full_name=?, email=?, phone_number=?, role=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $input['full_name'], 
                    $input['email'], 
                    $input['phone'], 
                    $input['role'], 
                    $id
                ]);
            }
            echo json_encode(['status' => 'success', 'message' => 'Cập nhật thông tin thành công!']);
        }
        else {
            throw new Exception('Hành động không hợp lệ (Invalid Action)');
        }
    }

} catch (Exception $e) {
    // Bắt mọi lỗi và trả về JSON để JS hiển thị alert
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>