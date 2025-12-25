<?php
session_start();
require 'db_connect.php';

// 1. Lấy ID từ URL
$id = $_GET['id'] ?? 0;

// 2. Truy vấn dữ liệu bài viết
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Nếu không tìm thấy bài viết
if (!$post) {
    echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h3>Bài viết không tồn tại hoặc đã bị xóa!</h3>
            <a href='booking_view.php'>Quay lại trang chủ</a>
          </div>";
    exit;
}

// 4. Xử lý thông tin người dùng cho Navbar (Để đồng bộ với các trang khác)
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$current_user_name = $is_logged_in ? $_SESSION['full_name'] : "Khách";
$user_role = $_SESSION['role'] ?? 'GUEST';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* CSS Riêng cho trang bài viết */
        .post-container { 
            max-width: 800px; 
            margin: -50px auto 40px; /* Kéo lên đè banner một chút */
            padding: 40px; 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            position: relative;
            z-index: 10;
        }

        .post-header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        
        .post-tag { 
            display: inline-block; padding: 6px 14px; border-radius: 20px; 
            font-size: 12px; font-weight: bold; color: white; margin-bottom: 15px; 
            text-transform: uppercase; letter-spacing: 1px;
        }
        .tag-NEWS { background: #17a2b8; }
        .tag-PROMO { background: #dc3545; }
        .tag-EVENT { background: #28a745; }
        
        .post-title { font-size: 32px; margin: 10px 0; color: #222; line-height: 1.4; }
        .post-date { color: #888; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 5px; }
        
        .post-image { 
            width: 100%; 
            max-height: 450px; 
            object-fit: cover; 
            border-radius: 8px; 
            margin-bottom: 30px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .post-content { 
            font-size: 17px; 
            line-height: 1.8; 
            color: #444; 
            text-align: justify; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .btn-back { 
            display: inline-flex; align-items: center; gap: 8px;
            margin-bottom: 20px; color: #666; text-decoration: none; font-weight: 600; 
            transition: 0.2s;
        }
        .btn-back:hover { color: var(--primary-color); transform: translateX(-5px); }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-futbol" style="color: var(--primary-color);"></i>
            SÂN BÓNG VĨNH THẠNH
        </div>
        <div class="user-info">
            <?php if ($is_logged_in): ?>
                <span class="user-name">Chào, <?php echo htmlspecialchars($current_user_name); ?></span>
                
                <a href="booking_view.php" class="btn-link"><i class="fas fa-home"></i> Đặt sân</a>
                <a href="my_bookings.php" class="btn-link"><i class="fas fa-history"></i> Lịch sử</a>
                <a href="match_finding.php" class="btn-link"><i class="fas fa-handshake"></i> Cáp Kèo</a>
                
                <?php if($user_role === 'ADMIN'): ?>
                    <a href="admin_dashboard.php" class="btn-link"><i class="fas fa-cogs"></i> Admin</a>
                <?php endif; ?>
                
                <a href="logout.php" class="btn-logout">Đăng xuất</a>
            <?php else: ?>
                <a href="booking_view.php" class="btn-link">← Trang chủ</a>
                <a href="login.html" class="btn-logout" style="background:var(--primary-color);">Đăng nhập</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="hero-section" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://i.pinimg.com/1200x/e5/2b/85/e52b8544dde99e31fa3a40fc3d1a1dbb.jpg'); background-size: cover; background-position: center; color: white; padding: 20px 20px 50px 20px; text-align: center;">
        <div class="hero-title">
            <h1 style="margin: 0; font-size: 2.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">Tin Tức & Sự Kiện</h1>
            <p style="margin-top: 10px; font-size: 1.1rem; opacity: 0.9;">Cập nhật những thông tin mới nhất từ sân bóng</p>
        </div>
    </div>

    <div class="post-container">
        <a href="booking_view.php" class="btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>

        <div class="post-header">
            <span class="post-tag tag-<?php echo $post['type']; ?>">
                <?php 
                    if($post['type'] == 'PROMO') echo '<i class="fas fa-tags"></i> Khuyến Mãi';
                    elseif($post['type'] == 'EVENT') echo '<i class="fas fa-calendar-star"></i> Sự Kiện';
                    else echo '<i class="fas fa-newspaper"></i> Tin Tức';
                ?>
            </span>
            <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-date">
                <i class="far fa-clock"></i> Đăng ngày: <?php echo date("d/m/Y - H:i", strtotime($post['created_at'])); ?>
            </div>
        </div>

        <?php if (!empty($post['image'])): ?>
            <img src="<?php echo htmlspecialchars($post['image']); ?>" class="post-image" alt="Ảnh bài viết" onerror="this.style.display='none'">
        <?php endif; ?>

        <div class="post-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">
             <p style="font-weight: bold; color: #555;">Bạn muốn đặt sân ngay?</p>
             <a href="booking_view.php" style="background: var(--primary-color); color: white; padding: 10px 25px; text-decoration: none; border-radius: 30px; display: inline-block; font-weight: bold;">Đặt Sân Ngay</a>
        </div>
        
    </div>
<?php include 'footer.php'; ?>
</body>
</html>