<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-futbol"></i> CHỦ SÂN
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="booking_view.php" target="_blank">
                <i class="fas fa-home"></i> Xem Trang Chủ
            </a>
        </li>
        
        <li class="menu-label">QUẢN LÝ</li>
        <li>
            <a href="admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Thống kê
            </a>
        </li>
        <li>
            <a href="admin_revenue.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_revenue.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i> Doanh thu
             </a>   
        </li>

        <li>
            <a href="admin_bookings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_bookings.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Lịch Đặt
            </a>
        </li>
        <li>
            <a href="admin_fields.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_fields.php' ? 'active' : ''; ?>">
                <i class="fas fa-map-marker-alt"></i> Sân Bóng
            </a>
        </li>
        <li>
            <a href="admin_users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Người dùng
            </a>
        </li>
        <li>
            <a href="admin_posts.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_posts.php' ? 'active' : ''; ?>">
                <i class="fas fa-newspaper"></i> Tin tức
            </a>
        </li>

        <li class="menu-label">HỆ THỐNG</li>
        
        <li>
            <a href="logout.php" class="btn-logout-menu">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </a>
        </li>
    </ul>
</div>