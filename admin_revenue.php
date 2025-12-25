<?php
session_start();
// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'ADMIN') {
    header("Location: login.html"); exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo Cáo Doanh Thu</title>
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* CSS riêng cho nút Excel */
        .btn-excel {
            background-color: #17a2b8; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-size: 13px; margin-left: auto; display: flex; align-items: center; gap: 5px;
        }
        .btn-excel:hover { background-color: #138496; }
        
        .btn-filter {
            background-color: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;
        }

        /* --- CSS MỚI CHO PHÂN TRANG (Giống trang Booking) --- */
        .pagination-container { display: flex; justify-content: flex-end; align-items: center; margin-top: 15px; gap: 10px; }
        .btn-page { background: white; border: 1px solid #ddd; padding: 8px 12px; cursor: pointer; border-radius: 4px; transition: 0.2s; }
        .btn-page:hover { background: #eee; }
        .btn-page:disabled { background: #f4f4f4; color: #aaa; cursor: not-allowed; }
        .page-info { font-weight: bold; color: #555; font-size: 14px; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        
        <div class="dashboard-header">
            <h2 class="dashboard-title">Báo Cáo & Thống Kê</h2>
            <div style="font-size: 14px; color: #666;">
                <i class="fas fa-chart-line"></i> Theo dõi hiệu quả kinh doanh
            </div>
        </div>

        <div class="content-wrapper">

            <div class="filter-box" style="width: 100%; box-sizing: border-box; justify-content: flex-start;">
                <i class="fas fa-filter" style="color: #007bff;"></i>
                
                <div style="display: flex; gap: 10px; align-items: center;">
                    <label>Từ:</label>
                    <input type="date" id="from_date" value="<?php echo date('Y-m-01'); ?>">
                    
                    <label>Đến:</label>
                    <input type="date" id="to_date" value="<?php echo date('Y-m-d'); ?>">
                    
                    <button onclick="loadRevenue()" class="btn-filter"><i class="fas fa-search"></i> Xem</button>
                </div>

                <button onclick="exportExcelAll()" class="btn-excel"><i class="fas fa-file-excel"></i> Xuất Excel</button>
            </div>

            <div class="admin-stats-grid">
                <div class="stat-card card-revenue">
                    <div>
                        <h3 id="total_revenue">0 đ</h3>
                        <p>Tổng doanh thu thực tế</p>
                    </div>
                    <i class="fas fa-coins stat-icon" style="color:#28a745;"></i>
                </div>

                <div class="stat-card card-orders">
                    <div>
                        <h3 id="total_orders">0</h3>
                        <p>Tổng đơn hoàn thành</p>
                    </div>
                    <i class="fas fa-check-circle stat-icon" style="color:#17a2b8;"></i>
                </div>
            </div>

            <div class="content-box">
                <div class="box-header">
                    <i class="fas fa-chart-bar" style="color: #666;"></i> Biểu đồ doanh thu theo ngày
                </div>
                <div style="height: 350px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="content-box">
                <div class="box-header">
                    <i class="fas fa-list" style="color: #666;"></i> Chi tiết danh sách đơn hàng
                </div>
                <div style="overflow-x: auto;">
                    <table id="table_report">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Thời gian</th>
                                <th>Khách hàng</th>
                                <th>Sân bóng</th>
                                <th>Hình thức TT</th>
                                <th>Tổng tiền</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_list">
                            </tbody>
                    </table>
                </div>

                <div class="pagination-container" id="paginationControls" style="display:none;">
                    <button class="btn-page" onclick="changePage(-1)" id="btnPrev"><i class="fas fa-chevron-left"></i> Trước</button>
                    <span class="page-info" id="pageInfo">Trang 1 / 1</span>
                    <button class="btn-page" onclick="changePage(1)" id="btnNext">Sau <i class="fas fa-chevron-right"></i></button>
                </div>
            </div>

        </div> 
    </div> 

<script>
    // --- BIẾN TOÀN CỤC ---
    let myChart = null; 
    let allRevenueData = []; // Biến chứa toàn bộ dữ liệu tải về để phân trang
    let currentPage = 1;
    const itemsPerPage = 10; // Số dòng mỗi trang

    const formatMoney = (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);

    // --- HÀM TẢI DỮ LIỆU ---
    async function loadRevenue() {
        const from = document.getElementById('from_date').value;
        const to = document.getElementById('to_date').value;

        if (from > to) { alert("Ngày bắt đầu không được lớn hơn ngày kết thúc!"); return; }

        try {
            const res = await fetch(`api_revenue.php?from=${from}&to=${to}`);
            const data = await res.json();

            if (data.status === 'success') {
                // 1. Cập nhật số liệu tổng
                document.getElementById('total_revenue').innerText = formatMoney(data.summary.total_revenue);
                document.getElementById('total_orders').innerText = data.summary.total_orders + " đơn";

                // 2. Vẽ lại biểu đồ
                renderChart(data.chart_data);

                // 3. Xử lý dữ liệu bảng & Phân trang
                allRevenueData = data.list_data || []; // Lưu toàn bộ data vào biến global
                currentPage = 1; // Reset về trang 1
                renderTable();   // Vẽ bảng trang đầu tiên
            }
        } catch (e) { console.error("Lỗi tải dữ liệu:", e); }
    }

    // --- HÀM VẼ BIỂU ĐỒ (Giữ nguyên) ---
    function renderChart(chartData) {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        if (myChart) myChart.destroy(); 
        const labels = chartData.map(item => item.date);
        const values = chartData.map(item => item.daily_total);

        myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: values,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: '#28a745',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // --- HÀM RENDER BẢNG THEO TRANG ---
    function renderTable() {
        const tbody = document.getElementById('tbody_list');
        const paginationControls = document.getElementById('paginationControls');
        
        if (allRevenueData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px; color:#888;">Không có dữ liệu nào.</td></tr>';
            paginationControls.style.display = 'none';
            return;
        }

        // Tính toán cắt mảng dữ liệu
        const totalPages = Math.ceil(allRevenueData.length / itemsPerPage);
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const pageData = allRevenueData.slice(start, end);

        tbody.innerHTML = '';

        pageData.forEach(item => {
            const methodBadge = item.payment_method === 'TRANSFER' 
                ? '<span style="color:#007bff; font-weight:600; font-size:12px;"><i class="fas fa-university"></i> CK</span>' 
                : '<span style="color:#28a745; font-weight:600; font-size:12px;"><i class="fas fa-money-bill"></i> TM</span>';

            const html = `
                <tr>
                    <td><b>#${item.id}</b></td>
                    <td>${item.start_time}</td>
                    <td>${item.full_name || 'Khách vãng lai'}</td>
                    <td>${item.field_name}</td>
                    <td>${methodBadge}</td>
                    <td style="font-weight:bold; color:#333;">${formatMoney(item.total_price)}</td>
                </tr>
            `;
            tbody.innerHTML += html;
        });

        // Cập nhật giao diện nút phân trang
        paginationControls.style.display = 'flex';
        document.getElementById('pageInfo').innerText = `Trang ${currentPage} / ${totalPages}`;
        document.getElementById('btnPrev').disabled = (currentPage === 1);
        document.getElementById('btnNext').disabled = (currentPage === totalPages);
    }

    // --- HÀM CHUYỂN TRANG ---
    function changePage(step) {
        const totalPages = Math.ceil(allRevenueData.length / itemsPerPage);
        const newPage = currentPage + step;
        if (newPage >= 1 && newPage <= totalPages) {
            currentPage = newPage;
            renderTable(); // Vẽ lại bảng
        }
    }

    // --- HÀM XUẤT EXCEL NÂNG CAO (Xuất TOÀN BỘ dữ liệu) ---
    function exportExcelAll() {
        if (allRevenueData.length === 0) {
            alert("Không có dữ liệu để xuất!");
            return;
        }

        // Tạo tiêu đề bảng
        let tableHTML = `
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td>ID</td>
                <td>Thời gian</td>
                <td>Khách hàng</td>
                <td>Sân bóng</td>
                <td>Hình thức TT</td>
                <td>Tổng tiền</td>
            </tr>
        `;

        // Lặp qua TOÀN BỘ dữ liệu (allRevenueData) thay vì chỉ HTML đang hiển thị
        allRevenueData.forEach(item => {
            let pm = item.payment_method === 'TRANSFER' ? 'Chuyển khoản' : 'Tiền mặt';
            tableHTML += `
                <tr>
                    <td>#${item.id}</td>
                    <td>${item.start_time}</td>
                    <td>${item.full_name || 'Khách vãng lai'}</td>
                    <td>${item.field_name}</td>
                    <td>${pm}</td>
                    <td>${item.total_price}</td>
                </tr>
            `;
        });

        // Tạo file Excel
        const fullHTML = `<meta charset="UTF-8"><table border="1">${tableHTML}</table>`;
        const url = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(fullHTML);
        
        const downloadLink = document.createElement("a");
        document.body.appendChild(downloadLink);
        downloadLink.href = url;
        downloadLink.download = `Doanh_thu_${document.getElementById('from_date').value}_den_${document.getElementById('to_date').value}.xls`;
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }

    // Tự động tải khi vào trang
    loadRevenue();
</script>
</body>
</html> #