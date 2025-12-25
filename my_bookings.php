<?php
session_start();
// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in'])) { header('Location: login.html'); exit(); }

// Lấy thông tin user
$current_user_name = $_SESSION['full_name'];
$user_role = $_SESSION['role'] ?? 'CUSTOMER';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đặt sân</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS Riêng cho Card Lịch sử */
        .history-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-left: 5px solid #ccc; /* Màu mặc định */
            transition: 0.2s;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap; /* Responsive */
            gap: 15px;
        }
        .history-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Màu viền theo trạng thái */
        .border-PENDING { border-left-color: #ffc107; }   /* Vàng */
        .border-CONFIRMED { border-left-color: #28a745; } /* Xanh lá */
        .border-CANCELLED { border-left-color: #dc3545; } /* Đỏ */
        .border-COMPLETED { border-left-color: #17a2b8; } /* Xanh dương */

        /* Badge trạng thái */
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            color: white;
            display: inline-block;
            min-width: 90px;
            text-align: center;
        }
        .bg-PENDING { background-color: #ffc107; color: #333; }
        .bg-CONFIRMED { background-color: #28a745; }
        .bg-CANCELLED { background-color: #dc3545; }
        .bg-COMPLETED { background-color: #17a2b8; }

        /* Nút Hủy */
        .btn-cancel-booking {
            background: #fff;
            color: #dc3545;
            border: 1px solid #dc3545;
            padding: 5px 15px;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-cancel-booking:hover {
            background: #dc3545;
            color: white;
        }

        /* Thanh tìm kiếm đẹp hơn */
        .search-wrapper {
            position: relative;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .search-input {
            width: 100%;
            border: 1px solid #eee;
            padding: 10px 15px;
            border-radius: 5px;
            outline: none;
            font-size: 14px;
        }
        .search-input:focus { border-color: var(--primary-color, #28a745); }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-futbol" style="color: var(--primary-color, #28a745);"></i>
            SÂN BÓNG VĨNH THẠNH
        </div>
        <div class="user-info">
            <span class="user-name">Chào, <?php echo htmlspecialchars($current_user_name); ?></span>
            <a href="booking_view.php" class="btn-link"><i class="fas fa-home"></i> Đặt sân</a>
            <a href="#" class="btn-link" style="color: #ffc107; font-weight: bold; border-bottom: 2px solid #ffc107;">
                <i class="fas fa-history"></i> Lịch sử
            </a>
            <a href="match_finding.php" class="btn-link"><i class="fas fa-handshake"></i> Cáp Kèo</a>
            <?php if($user_role === 'ADMIN'): ?>
                <a href="admin_dashboard.php" class="btn-link"><i class="fas fa-cogs"></i> Admin</a>
            <?php endif; ?>
            <a href="logout.php" class="btn-logout">Đăng xuất</a>
        </div>
    </nav>

    <div class="hero-section" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1551958219-acbc608c6377?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'); background-size: cover; background-position: center; color: white; padding: 60px 20px; text-align: center;">
        <div class="hero-title">
            <h1 style="margin: 0; font-size: 2.5rem;">Quản Lý Đặt Sân</h1>
            <p style="margin-top: 10px; font-size: 1.1rem; opacity: 0.9;">Xem lại lịch sử và trạng thái các trận đấu của bạn</p>
        </div>
    </div>

    <div class="container" style="max-width: 900px; margin-top: -30px; position: relative; z-index: 10;">
        
        <div class="search-wrapper">
            <i class="fas fa-search" style="color: #999;"></i>
            <input type="text" id="searchInput" class="search-input" placeholder="Tìm theo tên sân, ngày đá, giá tiền..." onkeyup="filterBookings()">
            <span id="count_badge" style="white-space: nowrap; font-weight: bold; color: #555; background: #eee; padding: 5px 10px; border-radius: 4px;">0 đơn</span>
        </div>

        <div id="list">
            <div style="text-align:center; padding: 40px; background: white; border-radius: 8px;">
                <i class="fas fa-spinner fa-spin fa-2x" style="color: var(--primary-color);"></i>
                <p style="margin-top: 10px;">Đang tải dữ liệu...</p>
            </div>
        </div>

    </div>

    <div id="cancelModal" class="modal">
        <div class="modal-content" style="max-width: 450px;">
            <h3 style="color: #dc3545; margin-top: 0;"><i class="fas fa-exclamation-circle"></i> Xác nhận hủy sân</h3>
            <p style="color: #666; font-size: 14px;">Bạn chỉ có thể hủy trước giờ đá <strong>24 tiếng</strong>. Hành động này không thể hoàn tác.</p>
            
            <div class="form-group" style="margin-top: 20px;">
                <label style="font-weight: bold;">Lý do hủy:</label>
                <select id="cancel_reason_select" class="form-control" onchange="toggleOtherReason()">
                    <option value="">-- Chọn lý do --</option>
                    <option value="Bận việc đột xuất">Bận việc đột xuất</option>
                    <option value="Thời tiết xấu">Thời tiết xấu / Mưa to</option>
                    <option value="Tìm được sân khác">Tìm được sân khác</option>
                    <option value="Thiếu người đá">Thiếu người đá</option>
                    <option value="Khác">Lý do khác...</option>
                </select>
            </div>

            <div class="form-group" id="other_reason_div" style="display: none; margin-top: 10px;">
                <textarea id="cancel_reason_text" class="form-control" rows="2" placeholder="Nhập lý do cụ thể..."></textarea>
            </div>
            
            <input type="hidden" id="cancel_booking_id">

            <div style="margin-top: 25px; text-align: right; display: flex; gap: 10px;">
                <button onclick="closeCancelModal()" style="flex: 1; padding: 10px; background: #eee; border: none; border-radius: 4px; cursor: pointer;">Đóng</button>
                <button onclick="submitCancel()" style="flex: 1; padding: 10px; background: #dc3545; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Hủy Sân Ngay</button>
            </div>
        </div>
    </div>

    <script>
        const formatMoney = (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        let allBookings = []; 

        async function loadMyBookings() {
            const listDiv = document.getElementById('list');
            try {
                const res = await fetch('api_get_my_bookings.php');
                const data = await res.json();

                if (data.status !== 'success') {
                    listDiv.innerHTML = `<p style="color:red; text-align:center;">${data.message || 'Lỗi tải dữ liệu'}</p>`;
                    return;
                }

                allBookings = data.data; 
                document.getElementById('count_badge').innerText = allBookings.length + " đơn";
                renderList(allBookings);

            } catch (e) { console.error(e); }
        }

        function renderList(items) {
            const listDiv = document.getElementById('list');
            
            if (items.length === 0) {
                listDiv.innerHTML = `
                    <div style="text-align:center; padding:40px; background: white; border-radius: 8px; color:#777;">
                        <i class="far fa-calendar-times" style="font-size: 40px; margin-bottom:15px; color: #ccc;"></i>
                        <p>Không tìm thấy lịch đặt sân nào.</p>
                        <a href="booking_view.php" style="color: var(--primary-color); font-weight: bold;">Đặt sân ngay &rarr;</a>
                    </div>`;
                return;
            }

            let html = "";
            const now = new Date(); // Thời gian hiện tại ở trình duyệt

            items.forEach(item => {
                // Xử lý text trạng thái
                let statusText = item.status;
                if(item.status === 'PENDING') statusText = 'Đang chờ duyệt';
                if(item.status === 'CONFIRMED') statusText = 'Đã đặt thành công';
                if(item.status === 'CANCELLED') statusText = 'Đã hủy';
                if(item.status === 'COMPLETED') statusText = 'Hoàn thành';

                // --- LOGIC HIỂN THỊ NÚT HỦY ---
                // 1. Phải là đơn chưa hủy và chưa hoàn thành
                // 2. Phải cách giờ đá ít nhất 24 tiếng
                let cancelButtonHtml = '';
                
                if (item.status === 'PENDING' || item.status === 'CONFIRMED') {
                    // Chuyển start_time (string) thành đối tượng Date
                    // Lưu ý: format server trả về thường là 'YYYY-MM-DD HH:MM:SS'
                    const startTime = new Date(item.start_time.replace(' ', 'T')); // Hack nhỏ để Safari/iOS hiểu format
                    const diffTime = startTime - now;
                    const diffHours = diffTime / (1000 * 60 * 60); // Đổi ra giờ

                    if (diffHours >= 24) {
                        cancelButtonHtml = `
                            <button onclick="openCancelModal(${item.id})" class="btn-cancel-booking">
                                <i class="fas fa-times-circle"></i> Hủy lịch
                            </button>
                        `;
                    }
                }

                html += `
                    <div class="history-card border-${item.status}">
                        <div style="flex: 1; min-width: 200px;">
                            <h4 style="margin: 0 0 8px 0; color: #333; font-size: 18px;">
                                <i class="fas fa-map-marker-alt" style="color: var(--primary-color);"></i> ${item.field_name}
                            </h4>
                            <div style="color: #555; font-size: 14px; line-height: 1.6;">
                                <div><i class="far fa-calendar-alt" style="width: 20px;"></i> Bắt đầu: <strong>${item.start_time}</strong></div>
                                <div><i class="fas fa-tag" style="width: 20px;"></i> Tổng tiền: <strong style="color: #d63384;">${formatMoney(item.total_price)}</strong></div>
                            </div>
                        </div>
                        
                        <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
                            <span class="status-badge bg-${item.status}">${statusText}</span>
                            ${cancelButtonHtml}
                            <div style="font-size: 12px; color: #999;">ID: #${item.id}</div>
                        </div>
                    </div>
                `;
            });
            listDiv.innerHTML = html;
        }

        // --- CÁC HÀM XỬ LÝ HỦY ---
        function openCancelModal(bookingId) {
            document.getElementById('cancel_booking_id').value = bookingId;
            document.getElementById('cancel_reason_select').value = "";
            document.getElementById('cancel_reason_text').value = "";
            document.getElementById('other_reason_div').style.display = 'none';
            document.getElementById('cancelModal').style.display = 'flex';
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }

        function toggleOtherReason() {
            const val = document.getElementById('cancel_reason_select').value;
            document.getElementById('other_reason_div').style.display = (val === 'Khác') ? 'block' : 'none';
        }

        async function submitCancel() {
            const bookingId = document.getElementById('cancel_booking_id').value;
            let reason = document.getElementById('cancel_reason_select').value;
            
            if (!reason) {
                alert("Vui lòng chọn lý do hủy!");
                return;
            }
            if (reason === 'Khác') {
                reason = document.getElementById('cancel_reason_text').value;
                if (!reason.trim()) {
                    alert("Vui lòng nhập lý do cụ thể!");
                    return;
                }
            }

            if(!confirm("Bạn có chắc chắn muốn hủy không?")) return;

            try {
                const res = await fetch('api_cancel_booking.php', {
                    method: 'POST',
                    body: JSON.stringify({ booking_id: bookingId, reason: reason })
                });
                const data = await res.json();

                if (data.status === 'success') {
                    alert("✅ " + data.message);
                    closeCancelModal();
                    loadMyBookings(); // Tải lại danh sách
                } else {
                    alert("❌ " + data.message);
                }
            } catch (e) {
                console.error(e);
                alert("Lỗi kết nối hệ thống!");
            }
        }

        // Đóng modal khi click ra ngoài
        window.onclick = function(event) {
            if (event.target == document.getElementById('cancelModal')) closeCancelModal();
        }

        function filterBookings() {
            const keyword = document.getElementById('searchInput').value.toLowerCase();
            const filtered = allBookings.filter(item => {
                const content = `${item.field_name} ${item.start_time} ${item.total_price} ${item.status}`.toLowerCase();
                return content.includes(keyword);
            });
            renderList(filtered);
        }

        loadMyBookings();
    </script>
<?php include 'footer.php'; ?>
</body>
</html>