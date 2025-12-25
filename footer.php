<style>
    .site-footer {
        background-color: #1a1a1a; /* Màu nền tối */
        color: #b0b0b0; /* Màu chữ xám nhạt */
        padding: 50px 0 20px;
        margin-top: 60px; /* Cách phần nội dung bên trên */
        font-family: sans-serif;
    }

    .footer-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        justify-content: space-between;
    }

    .footer-col {
        flex: 1;
        min-width: 250px;
    }

    .footer-col h3 {
        color: white;
        font-size: 18px;
        margin-bottom: 20px;
        position: relative;
        padding-bottom: 10px;
    }

    /* Gạch chân dưới tiêu đề */
    .footer-col h3::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 2px;
        background-color: var(--primary-color, #28a745);
    }

    .footer-links a {
        display: block;
        color: #b0b0b0;
        text-decoration: none;
        margin-bottom: 10px;
        transition: 0.2s;
    }

    .footer-links a:hover {
        color: white;
        padding-left: 5px; /* Hiệu ứng dịch chuyển khi hover */
        color: var(--primary-color, #28a745);
    }

    .footer-contact li {
        list-style: none;
        margin-bottom: 15px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    
    .social-icons a {
        display: inline-flex;
        width: 35px;
        height: 35px;
        background: #333;
        color: white;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 10px;
        text-decoration: none;
        transition: 0.3s;
    }
    .social-icons a:hover {
        background: var(--primary-color, #28a745);
        transform: translateY(-3px);
    }

    .footer-bottom {
        text-align: center;
        border-top: 1px solid #333;
        margin-top: 40px;
        padding-top: 20px;
        font-size: 14px;
    }
</style>

<footer class="site-footer">
    <div class="footer-container">
        
        <div class="footer-col">
            <h3>SÂN BÓNG VĨNH THẠNH</h3>
            <p style="line-height: 1.6;">
                Hệ thống sân cỏ nhân tạo tiêu chuẩn chất lượng cao. Nơi thỏa mãn đam mê bóng đá của bạn với dịch vụ chuyên nghiệp và giá cả hợp lý nhất khu vực.
            </p>
            <div class="social-icons" style="margin-top: 20px;">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
                <a href="#"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>

        <div class="footer-col">
            <h3>LIÊN KẾT NHANH</h3>
            <div class="footer-links">
                <a href="booking_view.php"><i class="fas fa-angle-right"></i> Trang Chủ</a>
                <a href="booking_view.php#booking-form"><i class="fas fa-angle-right"></i> Đặt Sân Ngay</a>
                <a href="match_finding.php"><i class="fas fa-angle-right"></i> Tìm Đối / Cáp Kèo</a>
                <a href="my_bookings.php"><i class="fas fa-angle-right"></i> Lịch Sử Đặt Sân</a>
                <a href="policy.php"><i class="fas fa-angle-right"></i> Chính Sách & Quy Định</a>
            </div>
        </div>

        <div class="footer-col">
            <h3>LIÊN HỆ</h3>
            <ul class="footer-contact" style="padding: 0;">
                <li>
                    <i class="fas fa-map-marker-alt" style="color: var(--primary-color, #28a745); margin-top: 5px;"></i>
                    <span>Số 123 Đường Vĩnh Thạnh, TP. Nha Trang, Khánh Hòa</span>
                </li>
                <li>
                    <i class="fas fa-phone-alt" style="color: var(--primary-color, #28a745); margin-top: 5px;"></i>
                    <span>Hotline: 0905.123.456</span>
                </li>
                <li>
                    <i class="fas fa-envelope" style="color: var(--primary-color, #28a745); margin-top: 5px;"></i>
                    <span>lienhe@sanbongvinhthanh.vn</span>
                </li>
                <li>
                    <i class="fas fa-clock" style="color: var(--primary-color, #28a745); margin-top: 5px;"></i>
                    <span>Mở cửa: 06:00 - 22:00 (Hàng ngày)</span>
                </li>
            </ul>
        </div>

    </div>
</footer>