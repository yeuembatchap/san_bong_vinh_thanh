<?php
// booking_view.php
session_start();
require 'db_connect.php'; // Kết nối CSDL
$fieldsList = [];
try {
    $stmt = $pdo->query("SELECT id, name, price_per_hour, is_active FROM fields ORDER BY id ASC");
    $fieldsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $fieldsList = [];
}
// Kiểm tra đăng nhập
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

if ($is_logged_in) {
    $current_user_id = $_SESSION['user_id'];
    $current_user_name = $_SESSION['full_name'];
    $user_role = $_SESSION['role'] ?? 'CUSTOMER';
} else {
    $current_user_id = 0;
    $current_user_name = "Khách";
    $user_role = 'GUEST';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Sân Bóng Online</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="booking.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <style>
        /* CSS CHO SLIDER */
        .swiper {
            width: 100%;
            padding-bottom: 50px !important; /* Chừa chỗ cho nút pagination nếu cần */
            padding-top: 10px;
        }
        .swiper-slide {
            height: auto; /* Để các thẻ cao bằng nhau */
            display: flex;
            justify-content: center;
        }
        .custom-card {
            width: 100%;
            height: 100%; /* Card giãn hết chiều cao slide */
            display: flex;
            flex-direction: column;
        }
        /* Chỉnh màu mũi tên sang màu vàng/cam chủ đạo */
        .swiper-button-next, .swiper-button-prev {
            color: var(--primary-color, #ffc107);
            background: rgba(255, 255, 255, 0.8);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .swiper-button-next:after, .swiper-button-prev:after {
            font-size: 18px;
            font-weight: bold;
        }
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
                <a href="my_bookings.php" class="btn-link"><i class="fas fa-history"></i> Lịch sử</a>
                <a href="match_finding.php" class="btn-link" style="color: #ffc107; font-weight: bold;">
                    <i class="fas fa-handshake"></i> Cáp Kèo
                </a>
                <?php if($user_role === 'ADMIN'): ?>
                    <a href="admin_dashboard.php" class="btn-link"><i class="fas fa-cogs"></i> Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="btn-logout">Đăng xuất</a>
            <?php else: ?>
                <a href="login.html" class="btn-link" style="background: white; color: #333; padding: 5px 10px; border-radius: 4px; font-weight: bold;">Đăng nhập</a>
                <a href="register.html" class="btn-logout" style="background: #ffc107; color: black;">Đăng ký</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="hero-section">
        <div id="weather_box" class="weather-widget" style="display: none;">
            <img id="w_icon" src="" width="40" height="40" alt="Weather">
            <div style="text-align: left;">
                <div class="weather-temp" id="w_temp">--°C</div>
                <div class="weather-desc" id="w_desc">Đang tải...</div>
            </div>
        </div>

        <div class="hero-title" style="margin-top: 40px;">
            <h1 style="font-size: 3rem; text-shadow: 0 2px 10px rgba(0,0,0,0.5); margin-bottom: 10px;">ĐẶT SÂN NHANH CHÓNG</h1>
            <p style="font-size: 1.2rem; opacity: 0.9;">Thỏa đam mê - Sân cỏ chuyên nghiệp - Dịch vụ tận tâm</p>
        </div>
    </div>

    <div class="booking-bar-container">
        <div class="booking-bar">
            <div class="booking-group">
                <label><i class="far fa-calendar-alt" style="color: var(--primary-color); margin-right: 5px;"></i> Chọn ngày đá</label>
                <input type="date" id="selected_date" class="booking-control" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="booking-group">
                <label><i class="fas fa-map-marker-alt" style="color: var(--primary-color); margin-right: 5px;"></i> Chọn sân</label>
                <select id="field_id" class="booking-control" onchange="calculateTotal()">
                    <option value="" data-price="0">-- Vui lòng chọn sân --</option>
                    <?php if (!empty($fieldsList)): ?>
                        <?php foreach ($fieldsList as $field): ?>
                            <?php 
                                $status = isset($field['is_active']) ? (int)$field['is_active'] : 1;
                                $isMaintenance = ($status === 0);
                            ?>
                            <option 
                                value="<?php echo $field['id']; ?>" 
                                data-price="<?php echo $field['price_per_hour']; ?>"
                                <?php if ($isMaintenance) echo 'disabled'; ?>
                                style="<?php echo $isMaintenance ? 'background-color: #f8d7da; color: #dc3545;' : ''; ?>"
                            >
                                <?php echo $field['name']; ?> 
                                <?php if ($isMaintenance): ?> -- ⛔ ĐANG BẢO TRÌ
                                <?php else: ?> (<?php echo number_format($field['price_per_hour'], 0, ',', '.'); ?>đ/h)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>Không có dữ liệu sân!</option>
                    <?php endif; ?>
                </select>
            </div>

            <button onclick="loadSchedule()" class="btn-search-main">
                <i class="fas fa-search"></i> Xem Lịch Trống
            </button>
        </div>
        
        <div style="margin-top: 30px;">
            <h3 style="color: var(--text-dark); margin-bottom: 15px; font-size: 18px;">
                <i class="fas fa-clock" style="color: var(--primary-color);"></i> Khung giờ có sẵn:
            </h3>
            <div id="schedule_grid" class="schedule-grid">
                <p style="grid-column: 1/-1; text-align: center; color: #777;">Vui lòng chọn ngày và sân, sau đó nhấn "Xem Lịch Trống".</p>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        
        <div class="section-header">
            <div class="section-title">Hệ Thống Sân Bãi</div>
            <div class="section-subtitle">Chất lượng cỏ nhân tạo tiêu chuẩn FIFA</div>
        </div>
        
        <div class="swiper mySwiperFields">
            <div class="swiper-wrapper" id="fields_grid_wrapper">
                <div class="swiper-slide"><p>Đang tải danh sách sân...</p></div>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>


        <div class="section-header">
            <div class="section-title">Tin Tức & Sự Kiện</div>
            <div class="section-subtitle">Cập nhật những thông tin mới nhất từ chúng tôi</div>
        </div>
        
        <div class="swiper mySwiperNews">
            <div class="swiper-wrapper" id="news_grid_wrapper">
                <div class="swiper-slide"><p>Đang tải tin tức...</p></div>
            </div>
             <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>

    </div>

    <div id="bookingModal" class="modal">
        <div class="modal-content" style="max-width: 500px; border-radius: 15px; overflow: hidden; padding: 0;">
            <div style="background: var(--primary-color); color: white; padding: 20px; text-align: center;">
                <h3 style="margin: 0; font-size: 20px;"><i class="fas fa-check-circle"></i> Xác nhận đặt sân</h3>
            </div>
            
            <div style="padding: 25px;">
                <div class="form-group">
                    <label style="font-weight: bold; color: #555;">Thời gian bắt đầu:</label>
                    <input type="text" id="modal_start_time" readonly class="form-control" style="background: #f1f3f5; border: none; font-weight: bold; color: #333;">
                </div>
                
                <div class="form-group" style="margin-top: 15px;">
                    <label style="font-weight: bold; color: #555;">Thời lượng đá:</label>
                    <select id="modal_duration" class="form-control" onchange="updatePricePreview()" style="border: 1px solid #ddd; padding: 8px; border-radius: 6px; width: 100%;">
                        <option value="60">60 Phút (1 Tiếng)</option>
                        <option value="90">90 Phút (1.5 Tiếng)</option>
                        <option value="120">120 Phút (2 Tiếng)</option>
                    </select>
                </div>

                <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center; background: #fff3cd; padding: 10px 15px; border-radius: 8px;">
                    <span style="color: #856404; font-weight: bold;">Tổng cộng:</span>
                    <span id="preview_price" style="font-size: 20px; color: #d9534f; font-weight: 800;">0 đ</span>
                </div>

                <hr style="margin: 20px 0; border: 0; border-top: 1px dashed #ddd;">

                <div class="form-group">
                    <label style="font-weight: bold; margin-bottom: 10px; display: block;">Phương thức thanh toán:</label>
                    <div style="display: flex; gap: 15px;">
                        <label style="cursor: pointer; display: flex; align-items: center; background: #f8f9fa; padding: 10px; border-radius: 8px; border: 1px solid #eee; flex: 1;">
                            <input type="radio" name="payment_method" value="CASH" checked onclick="toggleQR(false)"> 
                            <span style="margin-left: 8px; font-weight: 500;">💵 Tiền mặt</span>
                        </label>
                        <label style="cursor: pointer; display: flex; align-items: center; background: #f8f9fa; padding: 10px; border-radius: 8px; border: 1px solid #eee; flex: 1;">
                            <input type="radio" name="payment_method" value="TRANSFER" onclick="toggleQR(true)"> 
                            <span style="margin-left: 8px; font-weight: 500;">🏦 Chuyển khoản</span>
                        </label>
                    </div>
                </div>

                <div id="qr_section" style="display: none; text-align: center; margin-top: 20px; background: #f0fff4; padding: 20px; border-radius: 12px; border: 1px dashed #28a745;">
                    <p style="margin: 0 0 10px 0; font-size: 14px; color: #28a745; font-weight: bold;">QUÉT MÃ VIETQR ĐỂ THANH TOÁN</p>
                    <img id="vietqr_img" src="" style="width: 180px; height: 180px; object-fit: contain; border-radius: 8px;">
                    <p style="font-size: 13px; color: #666; margin-top: 10px;">Nội dung: <b id="qr_content" style="color: #333;">...</b></p>
                </div>
                
                <input type="hidden" id="modal_user_id" value="<?php echo $current_user_id; ?>">

                <div style="margin-top: 25px; display: flex; gap: 10px;">
                    <button class="btn-close" onclick="closeModal()" style="flex: 1; padding: 12px; background: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer;">Đóng</button>
                    <button id="btn_submit" onclick="submitBooking()" style="flex: 1; padding: 12px; background: var(--primary-color); color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s;">Xác nhận đặt</button>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
    const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
    let currentTotalPrice = 0;

    document.addEventListener("DOMContentLoaded", function() {
        loadNews();
        loadFieldsDisplay();
    });

    // --- CẤU HÌNH SLIDER (ĐÃ CẬP NHẬT TỰ ĐỘNG CHẠY & LOOP) ---
    function initSwiper(selector) {
        new Swiper(selector, {
            slidesPerView: 1, 
            spaceBetween: 20,
            loop: true, // <--- 1. Bật tính năng lặp lại vô tận
            autoplay: { // <--- 2. Bật tính năng tự động chạy
                delay: 3000, // Thời gian chờ: 3000ms = 3 giây
                disableOnInteraction: false, // Tiếp tục tự chạy kể cả khi người dùng đã bấm mũi tên
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                640: {
                    slidesPerView: 2, 
                    spaceBetween: 20,
                },
                1024: {
                    slidesPerView: 3, 
                    spaceBetween: 30,
                },
            },
        });
    }

    // --- RENDER TIN TỨC ---
    async function loadNews() {
        const wrapper = document.getElementById('news_grid_wrapper');
        try {
            const res = await fetch('api_public_posts.php');
            if (!res.ok) { wrapper.innerHTML = '<div class="swiper-slide"><p>Lỗi kết nối API.</p></div>'; return; }
            const result = await res.json();

            if(result.status === 'success' && result.data.length > 0) {
                wrapper.innerHTML = ''; 
                result.data.forEach(post => {
                    const imgSrc = post.image && post.image !== '' ? post.image : 'https://via.placeholder.com/400x250?text=News';
                    
                    let badgeColor = '#17a2b8'; let typeName = 'Tin tức';
                    if(post.type === 'PROMO') { badgeColor = '#dc3545'; typeName = 'Khuyến mãi'; }
                    if(post.type === 'EVENT') { badgeColor = '#28a745'; typeName = 'Sự kiện'; }

                    const html = `
                        <div class="swiper-slide">
                            <a href="post_detail.php?id=${post.id}" class="custom-card">
                                <div class="card-img-wrapper">
                                    <img src="${imgSrc}" class="card-img">
                                    <span class="card-badge" style="background:${badgeColor}">${typeName}</span>
                                </div>
                                <div class="card-body">
                                    <h4 class="card-title">${post.title}</h4>
                                    <p class="card-text">${post.content}</p>
                                    <span style="color: var(--primary-color); font-weight: 600; font-size: 14px; margin-top: auto;">Xem chi tiết <i class="fas fa-arrow-right"></i></span>
                                </div>
                            </a>
                        </div>
                    `;
                    wrapper.innerHTML += html;
                });
                initSwiper(".mySwiperNews");
            } else {
                wrapper.innerHTML = '<div class="swiper-slide"><p>Hiện chưa có tin tức nào.</p></div>';
            }
        } catch(e) { console.error(e); }
    }

    // --- RENDER DANH SÁCH SÂN ---
    async function loadFieldsDisplay() {
        const wrapper = document.getElementById('fields_grid_wrapper');
        try {
            const res = await fetch('api_fields.php');
            const data = await res.json();
            
            if(data.status === 'success') {
                wrapper.innerHTML = '';
                
                data.data.forEach(f => {
                    const img = f.image ? f.image : 'https://via.placeholder.com/400x300?text=San+Bong';
                    const price = new Intl.NumberFormat('vi-VN').format(f.price_per_hour);
                    
                    const html = `
                        <div class="swiper-slide">
                            <div class="custom-card">
                                <div class="card-img-wrapper">
                                    <img src="${img}" class="card-img">
                                </div>
                                <div class="card-body" style="text-align: center;">
                                    <h3 class="card-title" style="font-size: 20px;">${f.name}</h3>
                                    <p style="color:#dc3545; font-weight:bold; font-size: 18px; margin-bottom: 15px;">${price} đ/giờ</p>
                                    <a href="field_detail.php?id=${f.id}" style="display:inline-block; width:100%; padding:10px 0; background: var(--bg-light); color: var(--text-dark); border-radius: 8px; font-weight: 600; transition: 0.3s; text-decoration: none;">
                                        Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                    wrapper.innerHTML += html;
                });
                initSwiper(".mySwiperFields");
            }
        } catch(e) { console.error(e); }
    }


    // --- LOGIC LỊCH (GIỮ NGUYÊN) ---
    async function loadSchedule() {
        const date = document.getElementById('selected_date').value;
        const fieldId = document.getElementById('field_id').value;
        const grid = document.getElementById('schedule_grid');

        if(!fieldId) {
            grid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: red;">⚠️ Vui lòng chọn sân trước!</p>';
            return;
        }
        
        grid.innerHTML = '<p style="grid-column: 1/-1; text-align: center;">Đang tải dữ liệu...</p>';

        try {
            const res = await fetch(`api_get_schedule.php?field_id=${fieldId}&date=${date}`);
            const result = await res.json();
            
            grid.innerHTML = ""; 

            if (result.status !== 'success') {
                grid.innerHTML = "Lỗi tải dữ liệu!";
                return;
            }
            
            const bookedSlots = result.data || [];

            for (let hour = 5; hour < 23; hour++) {
                const timeStr = `${hour.toString().padStart(2, '0')}:00`;
                const nextHourStr = `${(hour+1).toString().padStart(2, '0')}:00`;
                
                const slotDiv = document.createElement('div');
                slotDiv.className = 'time-slot';
                
                slotDiv.innerHTML = `
                    <div style="font-weight: bold; font-size: 16px; margin-bottom: 4px;">${timeStr}</div>
                    <div style="font-size: 12px; color: #777;">đến ${nextHourStr}</div>
                `;

                const slotDate = new Date(`${date}T${timeStr}:00`);
                let isBooked = bookedSlots.some(b => {
                    const start = new Date(b.start_time);
                    const end = new Date(b.end_time);
                    return slotDate >= start && slotDate < end;
                });

                if (isBooked) {
                    slotDiv.classList.add('booked');
                } else {
                    slotDiv.onclick = () => openModal(date, timeStr);
                    slotDiv.innerHTML += `<div style="margin-top:5px; font-size:11px; color:var(--primary-color); font-weight:600;">Trống</div>`;
                }
                grid.appendChild(slotDiv);
            }

        } catch (e) {
            console.error(e);
            grid.innerHTML = "Lỗi kết nối server!";
        }
    }

    // --- CÁC HÀM MODAL & THANH TOÁN (GIỮ NGUYÊN) ---
    function openModal(date, timeStr) {
        if (!isLoggedIn) {
            if(confirm("🔒 Bạn cần đăng nhập để thực hiện đặt sân!\n\nNhấn OK để đến trang đăng nhập.")) {
                window.location.href = 'login.html';
            }
            return;
        }
        document.getElementById('modal_start_time').value = `${date} ${timeStr}:00`;
        document.getElementById('modal_duration').value = "60";
        document.querySelector('input[name="payment_method"][value="CASH"]').checked = true;
        toggleQR(false);
        document.getElementById('bookingModal').style.display = 'flex';
        updatePricePreview();
    }

    function closeModal() {
        document.getElementById('bookingModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target == document.getElementById('bookingModal')) closeModal();
    }

    function updatePricePreview() {
        const fieldSelect = document.getElementById('field_id');
        const pricePerHour = fieldSelect.options[fieldSelect.selectedIndex].getAttribute('data-price');
        const durationMinutes = parseInt(document.getElementById('modal_duration').value);
        currentTotalPrice = (durationMinutes / 60) * parseInt(pricePerHour);
        document.getElementById('preview_price').innerText = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(currentTotalPrice);
        
        const isTransfer = document.querySelector('input[name="payment_method"]:checked').value === 'TRANSFER';
        if(isTransfer) toggleQR(true);
    }

    function toggleQR(show) {
        const qrSection = document.getElementById('qr_section');
        const btnSubmit = document.getElementById('btn_submit');
        if (show) {
            qrSection.style.display = 'block';
            btnSubmit.innerText = "Đã chuyển khoản xong";
            const bankId = "VCB"; 
            const accountNo = "1027969285"; 
            const content = "DATSAN " + document.getElementById('modal_user_id').value;
            const qrUrl = `https://img.vietqr.io/image/${bankId}-${accountNo}-compact.jpg?amount=${currentTotalPrice}&addInfo=${content}`;
            document.getElementById('vietqr_img').src = qrUrl;
            document.getElementById('qr_content').innerText = content;
        } else {
            qrSection.style.display = 'none';
            btnSubmit.innerText = "Xác nhận đặt";
        }
    }

    async function submitBooking() {
        const startTime = document.getElementById('modal_start_time').value;
        const duration = parseInt(document.getElementById('modal_duration').value);
        const userId = document.getElementById('modal_user_id').value;
        const fieldId = document.getElementById('field_id').value;
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

        let d = new Date(startTime);
        d.setMinutes(d.getMinutes() + duration);
        const endTime = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0') + ' ' + String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0') + ':00';

        try {
            const res = await fetch('api_booking.php', {
                method: 'POST',
                body: JSON.stringify({ user_id: userId, field_id: fieldId, start_time: startTime, end_time: endTime, payment_method: paymentMethod })
            });
            const data = await res.json();
            if(data.status === 'success') {
                alert(paymentMethod === 'TRANSFER' ? "✅ Đã ghi nhận thanh toán! Đặt sân thành công." : "✅ Đặt sân thành công! Vui lòng thanh toán tại sân.");
                closeModal();
                loadSchedule(); 
            } else {
                alert("❌ Lỗi: " + data.message);
            }
        } catch (e) { console.error(e); alert("Lỗi hệ thống!"); }
    }

    // --- THỜI TIẾT ---
    async function loadWeather() {
        try {
            const res = await fetch('api_weather.php');
            const data = await res.json();
            if (data.cod == 200) {
                const temp = Math.round(data.main.temp);
                document.getElementById('w_temp').innerText = temp + "°C";
                document.getElementById('w_desc').innerText = data.weather[0].description;
                document.getElementById('w_icon').src = `https://openweathermap.org/img/wn/${data.weather[0].icon}@2x.png`;
                document.getElementById('weather_box').style.display = 'flex';
                analyzeWeatherAndAlert(data.weather[0].id, temp);
            }
        } catch (e) { console.error("Lỗi thời tiết:", e); }
    }
    function analyzeWeatherAndAlert(conditionId, temp) {
        let message = ''; let type = 'info'; let icon = '📢';
        if (conditionId >= 200 && conditionId < 600) { message = "Trời đang có mưa! Sân ướt, hãy cân nhắc đặt sân trong nhà."; type = 'warning'; icon = '🌧️'; } 
        else if (temp >= 33) { message = "Trời nắng nóng! Nhớ mang đủ nước."; type = 'danger'; icon = '☀️'; }
        else if (temp > 18 && temp < 30 && conditionId >= 800) { message = "Thời tiết đẹp, chốt kèo ngay!"; type = 'success'; icon = '⚽'; }

        if (message !== '') displayAlertBox(message, type, icon);
    }
    function displayAlertBox(msg, type, icon) {
        const oldAlert = document.getElementById('weather-alert-box');
        if (oldAlert) oldAlert.remove();
        const styles = {
            warning: { bg: '#fff3cd', color: '#856404' },
            danger:  { bg: '#f8d7da', color: '#721c24' },
            info:    { bg: '#d1ecf1', color: '#0c5460' },
            success: { bg: '#d4edda', color: '#155724' }
        };
        const style = styles[type] || styles.info;
        const alertDiv = document.createElement('div');
        alertDiv.id = 'weather-alert-box';
        alertDiv.style.cssText = `background: ${style.bg}; color: ${style.color}; padding: 15px; border-radius: 8px; margin-top: 20px; display: flex; align-items: center; gap: 10px; max-width: 1000px; margin: 20px auto; box-shadow: 0 4px 6px rgba(0,0,0,0.05);`;
        alertDiv.innerHTML = `<span style="font-size: 20px;">${icon}</span> <span>${msg}</span>`;
        
        const bar = document.querySelector('.booking-bar-container');
        bar.parentNode.insertBefore(alertDiv, bar.nextSibling);
    }
    
    loadWeather();
</script>

<?php include 'footer.php'; ?>
</body>
</html>