<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Lịch Đặt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="sidebar.css">
    <style>
        /* CSS Cũ giữ nguyên */
        body { font-family: sans-serif; background: #f4f4f4; display: flex; }
        .main-content { flex: 1; padding: 20px; }
        .filter-bar { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .filter-bar input, .filter-bar select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-search { background: #17a2b8; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
        .btn-add { background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin-left: auto; display: flex; align-items: center; gap: 5px;}
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #007bff; color: white; }
        tr:hover { background: #f9f9f9; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; color: white; }
        .bg-pending { background: #ffc107; color: #333; }
        .bg-confirmed { background: #28a745; }
        .bg-cancelled { background: #dc3545; }

        /* --- CSS CHO MODAL --- */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; padding: 25px; border-radius: 10px; width: 400px; max-width: 90%; box-shadow: 0 5px 15px rgba(0,0,0,0.3); animation: slideDown 0.3s ease; }
        @keyframes slideDown { from {transform: translateY(-50px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .modal-actions { text-align: right; margin-top: 20px; }
        .btn-cancel { background: #6c757d; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin-right: 5px;}
        .btn-save { background: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }

        /* --- CSS MỚI CHO PHÂN TRANG --- */
        .pagination-container { display: flex; justify-content: flex-end; align-items: center; margin-top: 15px; gap: 10px; }
        .btn-page { background: white; border: 1px solid #ddd; padding: 8px 12px; cursor: pointer; border-radius: 4px; transition: 0.2s; }
        .btn-page:hover { background: #eee; }
        .btn-page:disabled { background: #f4f4f4; color: #aaa; cursor: not-allowed; }
        .page-info { font-weight: bold; color: #555; }
    </style>
</head>
<body>
    
    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-header">
            <h2 class="dashboard-title">Quản lý lịch đặt</h2>
            <div style="font-size: 14px; color: #666;">
                <i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y'); ?>
            </div>
        </div>

        <div class="filter-bar">
            <label>Từ:</label> <input type="date" id="filter_from">
            <label>Đến:</label> <input type="date" id="filter_to">
            
            <select id="filter_status">
                <option value="">-- Trạng thái --</option>
                <option value="PENDING">Chờ duyệt</option>
                <option value="CONFIRMED">Đã cọc/Thanh toán</option>
                <option value="CANCELLED">Đã hủy</option>
            </select>

            <input type="text" id="filter_search" placeholder="Tìm tên, SĐT...">
            <button class="btn-search" onclick="loadBookings()"><i class="fas fa-search"></i> Tìm</button>

            <button class="btn-add" onclick="openModal()"><i class="fas fa-plus-circle"></i> Tạo lịch đặt</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách Hàng</th>
                    <th>Sân</th>
                    <th>Thời gian</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="booking_list"></tbody>
        </table>

        <div class="pagination-container" id="paginationControls" style="display:none;">
            <button class="btn-page" onclick="changePage(-1)" id="btnPrev"><i class="fas fa-chevron-left"></i> Trước</button>
            <span class="page-info" id="pageInfo">Trang 1 / 1</span>
            <button class="btn-page" onclick="changePage(1)" id="btnNext">Sau <i class="fas fa-chevron-right"></i></button>
        </div>

    </div>

    <div class="modal-overlay" id="addModal">
        <div class="modal-content">
            <h3 style="margin-top:0; color:#007bff;"><i class="fas fa-edit"></i> Tạo Đơn Đặt Sân</h3>
            
            <div class="form-group">
                <label>Tên khách hàng (*)</label>
                <input type="text" id="new_name" placeholder="Ví dụ: Anh Tuấn">
            </div>
            <div class="form-group">
                <label>Số điện thoại (*)</label>
                <input type="text" id="new_phone" placeholder="Để tạo tài khoản hoặc tìm User cũ">
            </div>
            <div class="form-group">
                <label>Chọn sân</label>
                <select id="new_field_id" onchange="autoCalculatePrice()">
                    <option value="" data-price="0">Đang tải sân...</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ngày đá</label>
                <input type="date" id="new_date">
            </div>

            <div style="display:flex; gap:10px;">
                <div class="form-group" style="flex:1">
                    <label>Giờ bắt đầu</label>
                    <input type="time" id="new_start" onchange="autoCalculatePrice()">
                </div>
                <div class="form-group" style="flex:1">
                    <label>Giờ kết thúc</label>
                    <input type="time" id="new_end" onchange="autoCalculatePrice()">
                </div>
            </div>

            <div class="form-group">
                <label>Tổng tiền (VND)</label>
                <input type="number" id="new_price" placeholder="Tự động tính...">
            </div>            
            <div class="form-group">
                <label>Thanh toán</label>
                <select id="new_payment">
                    <option value="CASH">Tiền mặt (TM)</option>
                    <option value="TRANSFER">Chuyển khoản (CK)</option>
                </select>
            </div>

            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeModal()">Hủy</button>
                <button class="btn-save" onclick="createBooking()">Lưu Đơn</button>
            </div>
        </div>
    </div>

<script>
    // --- BIẾN TOÀN CỤC CHO PHÂN TRANG ---
    let allBookingsData = []; // Chứa toàn bộ dữ liệu tải về
    let currentPage = 1;
    const itemsPerPage = 10; // 10 dòng mỗi trang

    // --- KHỞI TẠO ---
    const today = new Date();
    document.getElementById('filter_to').valueAsDate = today;
    document.getElementById('filter_from').value = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0,10);
    document.getElementById('new_date').valueAsDate = today;

    const formatMoney = (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);

    // 1. Load danh sách sân (Giữ nguyên code cũ đã sửa)
    async function loadFieldsForSelect() {
        try {
            const res = await fetch('api_admin_dashboard.php'); 
            const data = await res.json();
            const select = document.getElementById('new_field_id');
            select.innerHTML = '<option value="" data-price="0">-- Chọn sân --</option>';
            if(data.fields) {
                data.fields.forEach(f => {
                    select.innerHTML += `<option value="${f.id}" data-price="${f.price_per_hour}">${f.name} - ${formatMoney(f.price_per_hour)}/h</option>`;
                });
            }
        } catch(e) { console.error("Không tải được danh sách sân"); }
    }

    // 2. Tính tiền tự động (Giữ nguyên)
    function autoCalculatePrice() {
        const fieldSelect = document.getElementById('new_field_id');
        const selectedOption = fieldSelect.options[fieldSelect.selectedIndex];
        const pricePerHour = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const startTime = document.getElementById('new_start').value;
        const endTime = document.getElementById('new_end').value;

        if (startTime && endTime && pricePerHour > 0) {
            const startParts = startTime.split(':');
            const endParts = endTime.split(':');
            const startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
            const endMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);
            let durationMinutes = endMinutes - startMinutes;

            if (durationMinutes > 0) {
                const totalPrice = (durationMinutes / 60) * pricePerHour;
                document.getElementById('new_price').value = Math.round(totalPrice);
            } else {
                document.getElementById('new_price').value = 0;
            }
        }
    }
    loadFieldsForSelect(); 

    // --- LOGIC MODAL ---
    function openModal() { document.getElementById('addModal').style.display = 'flex'; }
    function closeModal() { document.getElementById('addModal').style.display = 'none'; }

    // --- LOGIC TẠO ĐƠN MỚI ---
    async function createBooking() {
        const payload = {
            full_name: document.getElementById('new_name').value,
            phone_number: document.getElementById('new_phone').value,
            field_id: document.getElementById('new_field_id').value,
            booking_date: document.getElementById('new_date').value,
            start_time: document.getElementById('new_start').value,
            end_time: document.getElementById('new_end').value,
            total_price: document.getElementById('new_price').value,
            payment_method: document.getElementById('new_payment').value
        };

        if(!payload.start_time || !payload.end_time || !payload.phone_number) {
            alert("Vui lòng nhập đầy đủ: Tên, SĐT, Giờ đá");
            return;
        }

        try {
            const res = await fetch('api_create_booking.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if(result.status === 'success') {
                alert("Thành công!");
                closeModal();
                loadBookings(); 
            } else {
                alert("Lỗi: " + result.message);
            }
        } catch(e) { alert("Lỗi kết nối server!"); }
    }

    // --- LOGIC LOAD DANH SÁCH (ĐÃ SỬA ĐỂ PHÂN TRANG) ---
    async function loadBookings() {
        const from = document.getElementById('filter_from').value;
        const to = document.getElementById('filter_to').value;
        const status = document.getElementById('filter_status').value;
        const search = document.getElementById('filter_search').value;
        const tbody = document.getElementById('booking_list');
        const paginationControls = document.getElementById('paginationControls');
        
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Đang tải...</td></tr>';
        paginationControls.style.display = 'none'; // Ẩn phân trang khi đang tải

        try {
            const res = await fetch(`api_booking_manager.php?from=${from}&to=${to}&status=${status}&search=${search}`);
            const result = await res.json();

            // Lưu dữ liệu vào biến toàn cục và reset về trang 1
            allBookingsData = result.data || [];
            currentPage = 1;

            // Gọi hàm hiển thị
            renderTable();

        } catch (e) { 
            console.error(e);
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Lỗi tải dữ liệu.</td></tr>';
        }
    }

    // --- HÀM RENDER (HIỂN THỊ DỮ LIỆU THEO TRANG) ---
    function renderTable() {
        const tbody = document.getElementById('booking_list');
        const paginationControls = document.getElementById('paginationControls');

        if (allBookingsData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px;">Không có dữ liệu.</td></tr>';
            paginationControls.style.display = 'none';
            return;
        }

        // Tính toán vị trí bắt đầu và kết thúc
        const totalPages = Math.ceil(allBookingsData.length / itemsPerPage);
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        
        // Cắt mảng dữ liệu để lấy 10 dòng
        const pageData = allBookingsData.slice(start, end);

        tbody.innerHTML = '';
        pageData.forEach(b => {
            // Xử lý trạng thái
            let badgeClass = b.status === 'PENDING' ? 'bg-pending' : (b.status === 'CONFIRMED' ? 'bg-confirmed' : 'bg-cancelled');
            let statusText = b.status === 'PENDING' ? 'Chờ duyệt' : (b.status === 'CONFIRMED' ? 'Đã cọc' : 'Đã hủy');

            // Xử lý CK/TM
            let pm = (b.payment_method || '').toUpperCase().trim();
            let isTransfer = pm.includes('TRANSFER') || pm === 'CK' || pm.includes('BANK');
            let payMethod = isTransfer 
                ? '<span style="color:#007bff; font-weight:bold; font-size:12px;">(CK)</span>' 
                : '<span style="color:#28a745; font-weight:bold; font-size:12px;">(TM)</span>';

            tbody.innerHTML += `
                <tr>
                    <td>#${b.id}</td>
                    <td>
                        <b>${b.full_name || 'Khách vãng lai'}</b><br>
                        <small>${b.phone_number || ''}</small>
                    </td>
                    <td>${b.field_name}</td>
                    <td>${b.start_time.substring(0,16)} <br>đến ${b.end_time.substring(11,16)}</td>
                    <td style="color:#d63384; font-weight:bold;">
                        ${formatMoney(b.total_price)} ${payMethod}
                    </td>
                    <td><span class="badge ${badgeClass}">${statusText}</span></td>
                    <td>
                        ${b.status !== 'CANCELLED' ? `<button onclick="updateStatus(${b.id}, 'CANCELLED')" style="color:red; border:1px solid red; background:white; cursor:pointer; border-radius:3px; padding:2px 5px;">Hủy</button>` : '-'}
                    </td>
                </tr>`;
        });

        // Cập nhật giao diện phân trang
        paginationControls.style.display = 'flex';
        document.getElementById('pageInfo').innerText = `Trang ${currentPage} / ${totalPages}`;
        
        // Vô hiệu hóa nút nếu ở trang đầu hoặc cuối
        document.getElementById('btnPrev').disabled = (currentPage === 1);
        document.getElementById('btnNext').disabled = (currentPage === totalPages);
    }

    // --- HÀM CHUYỂN TRANG ---
    function changePage(step) {
        const totalPages = Math.ceil(allBookingsData.length / itemsPerPage);
        const newPage = currentPage + step;

        if (newPage >= 1 && newPage <= totalPages) {
            currentPage = newPage;
            renderTable(); // Vẽ lại bảng
        }
    }

    async function updateStatus(id, status) {
        if(!confirm("Hủy đơn này?")) return;
        await fetch('api_update_booking.php', { method: 'POST', body: JSON.stringify({id, status}) });
        loadBookings();
    }

    // Chạy lần đầu
    loadBookings();
</script>

</body>
</html> 