<?php
session_start();
require 'db_connect.php';

// 1. Lấy ID sân
$id = $_GET['id'] ?? 0;

// 2. Truy vấn dữ liệu sân
$stmt = $pdo->prepare("SELECT * FROM fields WHERE id = ?");
$stmt->execute([$id]);
$field = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$field) { 
    echo "<div style='text-align:center; padding:50px;'><h3>Không tìm thấy sân!</h3><a href='booking_view.php'>Quay lại</a></div>"; 
    exit; 
}

// 3. Xử lý thông tin user cho Navbar đồng bộ
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$current_user_name = $is_logged_in ? $_SESSION['full_name'] : "Khách";
$user_role = $_SESSION['role'] ?? 'GUEST';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết <?php echo htmlspecialchars($field['name']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- CSS Riêng cho trang Chi tiết --- */
        
        /* Container chính nổi lên trên banner */
        .main-container {
            max-width: 1000px;
            margin: -60px auto 40px;
            position: relative;
            z-index: 10;
        }

        /* Card thông tin sân */
        .field-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-wrap: wrap; /* Để responsive */
        }

        .field-img-col {
            flex: 1;
            min-width: 350px;
            position: relative;
        }
        .field-img-col img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            min-height: 350px;
        }

        .field-info-col {
            flex: 1;
            padding: 40px;
            min-width: 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .price-tag {
            font-size: 28px;
            color: #dc3545; /* Màu đỏ nổi bật */
            font-weight: bold;
            margin-bottom: 20px;
            display: inline-block;
        }

        .btn-booking-lg {
            background: linear-gradient(to right, #28a745, #218838);
            color: white;
            text-align: center;
            padding: 15px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
            transition: 0.3s;
            display: block;
            margin-top: 20px;
        }
        .btn-booking-lg:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.6);
        }

        /* Phần đánh giá */
        .reviews-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 30px;
            margin-top: 30px;
        }

        /* Avatar người comment */
        .avatar-circle { 
            width: 45px; height: 45px; 
            background: #eee; color: #555;
            border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            font-weight: bold; font-size: 18px;
        }
        
        .star-rating i { font-size: 24px; color: #ddd; cursor: pointer; transition: 0.2s; margin-right: 5px; }
        .star-rating i.active { color: #ffc107; }

        .form-review textarea {
            width: 100%; padding: 15px;
            border: 1px solid #ddd; border-radius: 8px;
            margin-top: 10px; font-family: inherit;
            resize: vertical;
        }
        .form-review textarea:focus { border-color: var(--primary-color); outline: none; }

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

    <div class="hero-section" style="background: linear-gradient(to bottom, rgba(0,0,0,0.6), rgba(0,0,0,0.8)), url('https://images.unsplash.com/photo-1575361204480-aadea25e6e68?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'); background-size: cover; background-position: center; color: white; padding: 50px 20px 70px 20px; text-align: center;">
        <div class="hero-title">
            <span style="background: #ffc107; color: #333; padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: bold; text-transform: uppercase;">Sân bóng đá</span>
            <h1 style="margin: 10px 0 0 0; font-size: 3rem; text-shadow: 0 2px 10px rgba(0,0,0,0.5);"><?php echo htmlspecialchars($field['name']); ?></h1>
            <p style="opacity: 0.9; margin-top: 10px;"><i class="fas fa-map-marker-alt"></i> Sân bóng chất lượng cao - Hệ thống chiếu sáng tiêu chuẩn</p>
        </div>
    </div>

    <div class="main-container">
        
        <div class="field-card">
            <div class="field-img-col">
                <?php $img = !empty($field['image']) ? $field['image'] : 'https://via.placeholder.com/500x400?text=San+Bong'; ?>
                <img src="<?php echo $img; ?>" alt="Ảnh sân">
            </div>
            <div class="field-info-col">
                <div style="margin-bottom: 20px;">
                    <span style="color: #777; font-size: 14px;">Giá thuê sân</span><br>
                    <div class="price-tag">
                        <?php echo number_format($field['price_per_hour'], 0, ',', '.'); ?> đ <span style="font-size:16px; color:#999; font-weight:normal;">/ giờ</span>
                    </div>
                </div>

                <div style="flex: 1;">
                    <h4 style="color: #333; margin-bottom: 10px;"><i class="fas fa-info-circle" style="color: var(--primary-color);"></i> Mô tả tiện ích:</h4>
                    <p style="color: #555; line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($field['description'] ?: "Sân cỏ nhân tạo chất lượng cao, có căng tin, bãi giữ xe rộng rãi, wifi miễn phí.")); ?>
                    </p>
                </div>

                <a href="booking_view.php?field_id=<?php echo $field['id']; ?>#booking-form" class="btn-booking-lg">
                    <i class="far fa-calendar-check"></i> ĐẶT SÂN NGAY
                </a>
            </div>
        </div>

        <div class="reviews-section">
            <h3 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 15px; display:flex; justify-content:space-between; align-items:center;">
                <span><i class="fas fa-star" style="color:#ffc107"></i> Đánh giá từ khách hàng</span>
                <span id="avg_display" style="font-size:16px; font-weight:normal; color:#666;">(Loading...)</span>
            </h3>

            <div class="form-review" style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:30px;">
                <?php if($is_logged_in): ?>
                    <div style="margin-bottom:10px; font-weight:bold; color:#444;">Bạn cảm thấy sân này thế nào?</div>
                    
                    <div class="star-rating" style="margin-bottom: 10px;">
                        <i class="fas fa-star" data-value="1"></i>
                        <i class="fas fa-star" data-value="2"></i>
                        <i class="fas fa-star" data-value="3"></i>
                        <i class="fas fa-star" data-value="4"></i>
                        <i class="fas fa-star" data-value="5"></i>
                    </div>
                    <input type="hidden" id="rating_input" value="5">
                    
                    <textarea id="comment_input" rows="3" placeholder="Chia sẻ trải nghiệm của bạn (mặt sân, ánh sáng, thái độ phục vụ...)..."></textarea>
                    
                    <div style="text-align:right; margin-top:10px;">
                        <button onclick="submitReview()" style="background: var(--primary-color); color:white; border:none; padding:10px 25px; border-radius:4px; font-weight:bold; cursor:pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                            <i class="fas fa-paper-plane"></i> Gửi Đánh Giá
                        </button>
                    </div>
                <?php else: ?>
                    <div style="text-align:center; color:#666;">
                        <i class="fas fa-lock"></i> Vui lòng <a href="login.html" style="color:var(--primary-color); font-weight:bold;">đăng nhập</a> để viết đánh giá.
                    </div>
                <?php endif; ?>
            </div>

            <div id="review_list">
                <div style="text-align:center; color:#999;"><i class="fas fa-spinner fa-spin"></i> Đang tải đánh giá...</div>
            </div>
        </div>

    </div>

    <script>
        const fieldId = <?php echo $id; ?>;

        // --- Xử lý Sao ---
        const stars = document.querySelectorAll('.star-rating i');
        const ratingInput = document.getElementById('rating_input');

        if(stars.length > 0) {
            updateStars(5); // Mặc định 5 sao
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const val = this.getAttribute('data-value');
                    ratingInput.value = val;
                    updateStars(val);
                });
            });
        }

        function updateStars(val) {
            stars.forEach(s => {
                if(s.getAttribute('data-value') <= val) s.classList.add('active');
                else s.classList.remove('active');
            });
        }

        // --- Gửi đánh giá ---
        async function submitReview() {
            const rating = document.getElementById('rating_input').value;
            const comment = document.getElementById('comment_input').value;

            if(!comment.trim()) { alert("Vui lòng nhập nội dung đánh giá!"); return; }

            try {
                const res = await fetch('api_reviews.php', {
                    method: 'POST',
                    body: JSON.stringify({ field_id: fieldId, rating: rating, comment: comment })
                });
                const data = await res.json();
                
                if(data.status === 'success') {
                    alert("✅ " + data.message);
                    document.getElementById('comment_input').value = '';
                    loadReviews();
                } else {
                    alert("❌ " + data.message);
                }
            } catch(e) { console.error(e); }
        }

        // --- Tải danh sách ---
        async function loadReviews() {
            try {
                const res = await fetch(`api_reviews.php?field_id=${fieldId}`);
                const result = await res.json();
                const list = document.getElementById('review_list');
                const avgDisplay = document.getElementById('avg_display');

                if(result.status === 'success') {
                    // Update thống kê
                    const avg = result.stats.avg_rating ? parseFloat(result.stats.avg_rating).toFixed(1) : 0;
                    const total = result.stats.total;
                    avgDisplay.innerHTML = `Trung bình: <b>${avg}</b>/5 (${total} lượt)`;

                    // Render List
                    list.innerHTML = '';
                    if(result.data.length === 0) {
                        list.innerHTML = '<p style="text-align:center; color:#999; font-style:italic; padding:20px;">Chưa có đánh giá nào. Hãy là người đầu tiên!</p>';
                        return;
                    }

                    result.data.forEach(r => {
                        // Tạo HTML sao nhỏ
                        let starsHtml = '';
                        for(let i=1; i<=5; i++) {
                            starsHtml += i <= r.rating ? '<i class="fas fa-star" style="color:#ffc107; font-size:12px;"></i>' : '<i class="far fa-star" style="color:#ddd; font-size:12px;"></i>';
                        }
                        
                        const firstLetter = r.full_name ? r.full_name.charAt(0).toUpperCase() : 'U';

                        const html = `
                            <div style="border-bottom: 1px solid #eee; padding: 20px 0; display: flex; gap: 15px;">
                                <div class="avatar-circle">${firstLetter}</div>
                                <div style="flex:1;">
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <b style="color:#333; font-size:15px;">${r.full_name}</b>
                                        <small style="color:#999; font-size:12px;">${new Date(r.created_at).toLocaleDateString('vi-VN')}</small>
                                    </div>
                                    <div style="margin: 5px 0;">${starsHtml}</div>
                                    <p style="margin:0; color:#555; line-height:1.5;">${r.comment}</p>
                                </div>
                            </div>
                        `;
                        list.innerHTML += html;
                    });
                }
            } catch(e) { console.error(e); }
        }

        loadReviews();
    </script>
<?php include 'footer.php'; ?>
</body>
</html>