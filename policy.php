<?php
session_start();

// 1. Logic kiểm tra đăng nhập (Đồng bộ với các trang khác)
$is_logged_in = isset($_SESSION['user_id']); 
$current_user_name = $is_logged_in ? ($_SESSION['full_name'] ?? 'Người dùng') : "Khách";
$user_role = $_SESSION['role'] ?? 'GUEST';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chính Sách & Quy Định - Sân Bóng Vĩnh Thạnh</title>
    
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- CSS RIÊNG CHO TRANG POLICY (Bổ sung thêm vào style.css gốc) --- */
        
        /* Container đè lên Hero (Giống ô lọc sân ở trang chủ) */
        .policy-container {
            margin-top: -60px; 
            position: relative; 
            z-index: 10;
        }

        /* Thiết kế thẻ Card chính sách */
        .policy-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); /* Shadow giống .filter-box */
            border-left: 5px solid var(--primary-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .policy-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        /* Card cảnh báo (Màu vàng) */
        .policy-card.warning {
            border-left-color: var(--accent-color); /* Lấy màu vàng từ style.css */
        }
        .policy-card.warning i {
            color: var(--accent-color) !important;
        }

        /* Tiêu đề của từng mục */
        .policy-header {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 12px;
            text-transform: uppercase;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        /* Danh sách nội dung */
        .policy-list { padding-left: 20px; margin: 0; }
        .policy-list li { margin-bottom: 12px; line-height: 1.6; color: #555; font-size: 15px; }
        .policy-list li strong { color: #333; }

        /* Badge nổi bật */
        .badge-highlight { 
            background-color: var(--primary-color); 
            color: white; 
            padding: 3px 10px; 
            border-radius: 4px; 
            font-size: 12px; 
            font-weight: 600; 
        }

        /* Nút quay lại ở Hero */
        .btn-hero-back {
            display: inline-flex; align-items: center; gap: 8px;
            color: white; text-decoration: none; font-weight: 500;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px; border-radius: 30px;
            margin-bottom: 15px; transition: 0.2s;
            font-size: 14px;
        }
        .btn-hero-back:hover { background: rgba(255,255,255,0.4); }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-futbol" style="color: var(--primary-color);"></i>
            <span>SÂN BÓNG VĨNH THẠNH</span>
        </div>
        
        <div class="user-info">
            <?php if ($is_logged_in): ?>
                <span class="user-name" style="display:none; @media(min-width:768px){display:inline;}">
                    Chào, <?php echo htmlspecialchars($current_user_name); ?>
                </span>
                
                <a href="booking_view.php" class="btn-link"><i class="fas fa-home"></i> Đặt sân</a>
                <a href="my_bookings.php" class="btn-link"><i class="fas fa-history"></i> Lịch sử</a>
                <a href="match_finding.php" class="btn-link"><i class="fas fa-handshake"></i> Cáp Kèo</a>
                <?php if($user_role === 'ADMIN'): ?>
                    <a href="admin/dashboard.php" class="btn-link"><i class="fas fa-cogs"></i> Admin</a>
                <?php endif; ?>
                
                <a href="logout.php" class="btn-logout">Đăng xuất</a>
            <?php else: ?>
                <a href="booking_view.php" class="btn-link">Trang chủ</a>
                <a href="login.html" class="btn-logout" style="background:var(--primary-color);">Đăng nhập</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="hero-section" style="height: 300px; align-items: center; flex-direction: column; padding-top: 40px;">
        <div class="hero-title">
            <h1 style="margin-top: 10px;">CHÍNH SÁCH & QUY ĐỊNH</h1>
            <p>Quy định chung áp dụng cho tất cả khách hàng</p>
        </div>
    </div>

    <div class="container policy-container">
        
        <div class="policy-card">
            <div class="policy-header">
                <i class="fas fa-calendar-check" style="color:var(--primary-color)"></i> 
                1. Quy Định Đặt Sân
            </div>
            <ul class="policy-list">
                <li><strong>Thông tin đặt sân:</strong> Khách hàng vui lòng cung cấp Số điện thoại và Họ tên thực để đảm bảo quyền lợi khi xảy ra tranh chấp.</li>
                <li><strong>Thời gian giữ sân:</strong> Hệ thống chỉ giữ lịch đặt sân trong vòng <strong>30 phút</strong> nếu chưa thanh toán cọc.</li>
                <li><strong>Xác nhận đơn hàng:</strong> Đơn đặt sân được coi là thành công khi trạng thái chuyển sang <span class="badge-highlight">ĐÃ XÁC NHẬN</span>.</li>
            </ul>
        </div>

        <div class="policy-card warning">
            <div class="policy-header">
                <i class="fas fa-exclamation-triangle"></i> 
                2. Chính Sách Hủy Sân
            </div>
            <p style="margin-bottom: 15px; font-style: italic; color: #666;">
                Vui lòng đọc kỹ các mốc thời gian sau để tránh mất phí:
            </p>
            <ul class="policy-list">
                <li><strong>Thời hạn hủy miễn phí:</strong> Quý khách cần hủy trước giờ đá ít nhất <strong>24 tiếng</strong> để được hoàn cọc 100%.</li>
                <li><strong>Cách thức hủy:</strong> Vào mục <a href="my_bookings.php" style="color:var(--primary-color); font-weight:bold;">Lịch sử</a> > Chọn đơn hàng > Nhấn nút "Hủy".</li>
                <li><strong>Phí phạt:</strong> Nếu hủy sát giờ (dưới 6 tiếng) hoặc không đến đá, quý khách sẽ <strong>không được hoàn lại tiền cọc</strong>.</li>
            </ul>
        </div>

        <div class="policy-card">
            <div class="policy-header">
                <i class="fas fa-shoe-prints" style="color:var(--primary-color)"></i> 
                3. Nội Quy Sân Bãi
            </div>
            <ul class="policy-list">
                <li><strong>Giày thi đấu:</strong> Chỉ sử dụng giày đinh dăm (TF) hoặc giày đế bằng (IC). <strong>Nghiêm cấm</strong> sử dụng giày đinh cao (FG/AG) để bảo vệ mặt cỏ.</li>
                <li><strong>Vệ sinh chung:</strong> Vui lòng không xả rác, chai nhựa bừa bãi. Không hút thuốc trong khu vực thi đấu.</li>
                <li><strong>Văn hóa sân cỏ:</strong> Tuyệt đối không gây gổ, đánh nhau hoặc tổ chức cá độ dưới mọi hình thức. Ban quản lý có quyền từ chối phục vụ vĩnh viễn nếu vi phạm.</li>
            </ul>
        </div>

    </div>
<?php include 'footer.php'; ?>
</body>
</html>