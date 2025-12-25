<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') { header('Location: login.html'); exit(); }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="sidebar.css">   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php include 'admin_sidebar.php'; ?>
    <!-- <nav class="navbar" style="background: #2c3e50;">
        <div class="logo"><i class="fas fa-user-shield"></i> ADMIN MANAGER</div>
        <div class="user-info">
            <a href="booking_view.php" class="btn-link" target="_blank"><i class="fas fa-eye"></i> Trang khách</a>
            <a href="admin_users.php" class="btn-link" style="background:#17a2b8; color:white; padding:5px 10px; border-radius:4px; margin-right:10px;">
                <i class="fas fa-users"></i> Quản lý Users
            </a>
            <a href="admin_fields.php" class="btn-link" style="background:#28a745; color:white; padding:5px 10px; border-radius:4px; margin-right:10px;">
                <i class="fas fa-futbol"></i> Quản lý Sân
            </a>
            <a href="admin_revenue.php" class="btn-link" style="background:#6f42c1; color:white; padding:5px 10px; border-radius:4px;">
                <i class="fas fa-chart-bar"></i> Báo Cáo Doanh Thu
            </a>
            <a href="admin_posts.php" class="btn-link" style="background:#fd7e14; color:white; padding:5px 10px; border-radius:4px; margin-right:10px;">
                <i class="fas fa-newspaper"></i> Quản lý Bài viết
            </a>
            <a href="logout.php" class="btn-logout">Đăng xuất</a>
        </div>
    </nav> -->
<div class="main-content">
    
    <div class="dashboard-header">
        <h2 class="dashboard-title">Tổng quan hệ thống</h2>
        <div style="font-size: 14px; color: #666;">
            <i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y'); ?>
        </div>
    </div>

    <div class="content-wrapper">
        
        <div class="filter-box">
            <i class="fas fa-filter" style="color: #28a745;"></i>
            <strong>Xem ngày:</strong>
            <input type="date" id="admin_date" value="<?php echo date('Y-m-d'); ?>" onchange="loadDashboard()">
        </div>

        <div class="admin-stats-grid">
            <div class="stat-card card-revenue">
                <div>
                    <h3 id="stat_revenue">0 đ</h3>
                    <p>Doanh thu</p>
                </div>
                <i class="fas fa-coins stat-icon" style="color:#28a745;"></i>
            </div>
            
            <div class="stat-card card-orders">
                <div>
                    <h3 id="stat_orders">0</h3>
                    <p>Đơn đặt sân</p>
                </div>
                <i class="fas fa-calendar-check stat-icon" style="color:#17a2b8;"></i>
            </div>
            
            <div class="stat-card card-pending">
                <div>
                    <h3 id="stat_pending">0</h3>
                    <p>Chờ duyệt</p>
                </div>
                <i class="fas fa-hourglass-half stat-icon" style="color:#ffc107;"></i>
            </div>
        </div>

        <div class="dashboard-grid-row">
            
            <div class="left-col">
                <div class="content-box">
                    <div class="box-header">
                        <i class="fas fa-list"></i> Danh sách đặt sân <span id="label_date" style="margin-left:5px; color:#007bff;"></span>
                    </div>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Khách hàng</th>
                                    <th>Sân</th>
                                    <th>Giờ đá</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="table_bookings">
                                </tbody>
                        </table>
                    </div>
                </div>

                <div class="content-box">
                    <div class="box-header" style="color: #e91e63;">
                        <i class="fas fa-futbol"></i> Tin Cáp Kèo Mới Nhất
                    </div>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Người đăng</th>
                                    <th>Loại tin</th>
                                    <th>Thời gian</th>
                                    <th>Sân</th>
                                    <th>Lời nhắn</th>
                                    <th>Liên hệ</th>
                                    <th>Xóa</th>
                                </tr>
                            </thead>
                            <tbody id="table_matches">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="right-col">
                <div class="content-box">
                    <div class="box-header" style="color: #ff9800;">
                        <i class="fas fa-tags"></i> Cấu hình giá sân
                    </div>
                    <div id="price_list">
                        Loading...
                    </div>
                    <p style="font-size: 12px; color: #999; margin-top: 10px; font-style: italic;">
                        * Giá được áp dụng cho mỗi giờ thuê.
                    </p>
                </div>
            </div>

        </div> </div> </div>

<script>
    // 1. Hàm định dạng tiền tệ chuẩn
    const formatMoney = (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);

    async function loadDashboard() {
        const dateInput = document.getElementById('admin_date');
        const dateValue = dateInput ? dateInput.value : new Date().toISOString().slice(0,10);
        
        // Cập nhật text ngày
        const labelDate = document.getElementById('label_date');
        if(labelDate) labelDate.innerText = `(${dateValue})`;

        try {
            // Gọi API
            const res = await fetch(`api_admin_dashboard.php?date=${dateValue}`);
            
            // Kiểm tra nếu API lỗi HTML (do php in lỗi ra màn hình)
            if (!res.ok) throw new Error("Lỗi kết nối Server");
            
            const data = await res.json();
            console.log("Dữ liệu nhận được:", data); 

            // A. THỐNG KÊ
            if (data.stats) {
                document.getElementById('stat_revenue').innerText = formatMoney(data.stats.daily_revenue || 0);
                document.getElementById('stat_orders').innerText = data.stats.total_bookings || 0;
                document.getElementById('stat_pending').innerText = data.stats.pending_count || 0;
            }

            // B. BẢNG ĐẶT SÂN (Sửa lỗi method is not defined)
            const tbodyBooking = document.getElementById('table_bookings');
            tbodyBooking.innerHTML = '';
            
            const listBookings = data.recent_bookings || [];
            
            if (listBookings.length === 0) {
                tbodyBooking.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px; color:#999;">Không có đơn hàng nào.</td></tr>';
            } else {
                listBookings.forEach(b => {
                    // Xử lý Status
                    let statusBadge = '';
                    let buttons = '';
                    
                    if (b.status === 'PENDING') {
                        statusBadge = '<span class="badge badge-warning">Chờ duyệt</span>';
                        buttons = `<button onclick="updateStatus(${b.id}, 'CONFIRMED')" style="color:green"><i class="fas fa-check"></i></button> 
                                   <button onclick="updateStatus(${b.id}, 'CANCELLED')" style="color:red"><i class="fas fa-times"></i></button>`;
                    } else if (b.status === 'CONFIRMED') {
                        statusBadge = '<span class="badge badge-success">Đã cọc</span>';
                        buttons = `<button onclick="updateStatus(${b.id}, 'CANCELLED')"><i class="fas fa-undo"></i></button>`;
                    } else {
                        statusBadge = '<span class="badge badge-danger">Hủy</span>';
                        buttons = '-';
                    }

                    // Xử lý Payment Method (Sửa lỗi logic cũ)
                    let methodBadge = '';
                    let pm = (b.payment_method || '').toUpperCase();
                    if (pm.includes('TRANSFER') || pm.includes('CK')) {
                        methodBadge = '<span style="color:#007bff; font-size:11px; font-weight:bold;"><i class="fas fa-university"></i> CK</span>';
                    } else {
                        methodBadge = '<span style="color:#28a745; font-size:11px; font-weight:bold;"><i class="fas fa-money-bill"></i> TM</span>';
                    }

                    // Render
                    tbodyBooking.innerHTML += `
                        <tr style="border-bottom:1px solid #eee;">
                            <td><b>#${b.id}</b></td>
                            <td>
                                <div>${b.full_name || 'Khách vãng lai'}</div>
                                <div>${methodBadge}</div> 
                            </td>
                            <td>${b.field_name}</td>
                            <td>${b.start_time.substring(11,16)} - ${b.end_time.substring(11,16)}</td>
                            <td style="color:#d63384; font-weight:bold;">${formatMoney(b.total_price)}</td>
                            <td>${statusBadge}</td>
                            <td>${buttons}</td>
                        </tr>`;
                });
            }

            // C. BẢNG TIN CÁP KÈO (Cập nhật theo database mới)
            const tbodyMatch = document.getElementById('table_matches');
            if(tbodyMatch) {
                tbodyMatch.innerHTML = '';
                const listMatches = data.matches || [];

                if(listMatches.length === 0) {
                    tbodyMatch.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#999;">Chưa có tin nào.</td></tr>';
                } else {
                    listMatches.forEach(m => {
                        let typeColor = m.type === 'TIM_DOI' ? 'red' : 'green';
                        let typeName = m.type === 'TIM_DOI' ? 'Tìm Đối' : 'Tìm Người';

                        tbodyMatch.innerHTML += `
                            <tr style="border-bottom:1px dashed #eee;">
                                <td style="font-weight:bold;">${m.full_name || 'Ẩn danh'}</td>
                                <td style="color:${typeColor}; font-weight:bold;">${typeName}</td>
                                <td>${m.match_date} <span style="color:#666">(${m.match_time.substring(0,5)})</span></td>
                                <td>${m.message}</td>
                                <td>${m.level || '-'}</td>
                                <td><a href="tel:${m.contact_phone}" style="color:#007bff;">${m.contact_phone}</a></td>
                                <td><button onclick="deleteMatch(${m.id})" style="color:red; border:none; background:none;"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        `;
                    });
                }
            }

            // D. BẢNG GIÁ SÂN
            const priceDiv = document.getElementById('price_list');
            if(priceDiv) {
                priceDiv.innerHTML = '';
                (data.fields || []).forEach(f => {
                    priceDiv.innerHTML += `
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; padding-bottom:5px; border-bottom:1px dashed #ddd;">
                            <span>${f.name}</span>
                            <div>
                                <input type="number" id="price_${f.id}" value="${f.price_per_hour}" style="width:70px;">
                                <button onclick="savePrice(${f.id})" style="font-size:12px; background:green; color:white; border:none; padding:2px 5px;">Lưu</button>
                            </div>
                        </div>`;
                });
            }

        } catch (e) {
            console.error("Lỗi JS:", e);
        }
    }

    // Các hàm Action
    async function updateStatus(id, status) {
        if(!confirm('Cập nhật trạng thái đơn?')) return;
        await fetch('api_update_booking.php', { method:'POST', body:JSON.stringify({id, status}) });
        loadDashboard();
    }
    async function deleteMatch(id) {
        if(!confirm('Xóa tin này?')) return;
        await fetch('api_delete_match.php', { method:'POST', body:JSON.stringify({id}) });
        loadDashboard();
    }
    async function savePrice(id) {
        const price = document.getElementById('price_'+id).value;
        await fetch('api_update_price.php', { method:'POST', body:JSON.stringify({id, price}) });
        alert('Đã lưu!');
    }

    // Chạy khi tải trang
    loadDashboard();
</script>
</body>
</html>