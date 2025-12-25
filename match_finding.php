<?php
session_start();
// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in'])) { header('Location: login.html'); exit(); }

// Lấy thông tin user từ session
$current_user_name = $_SESSION['full_name'];
$user_role = $_SESSION['role'] ?? 'CUSTOMER';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sàn Cáp Kèo - Tìm Đối</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* CSS Riêng cho trang Cáp Kèo (Bổ sung thêm vào style chung) */
        .match-layout { display: flex; gap: 30px; flex-wrap: wrap; margin-top: 20px; }
        
        /* Cột bên trái: Danh sách */
        .match-list-col { flex: 7; min-width: 300px; }
        
        /* Cột bên phải: Form đăng tin */
        .match-form-col { 
            flex: 3; 
            min-width: 280px; 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            height: fit-content; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            position: sticky; top: 20px; /* Trượt theo khi cuộn */
        }

        /* Card hiển thị tin */
        .match-card {
            background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px;
            border-left: 5px solid #ddd; display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: 0.2s;
        }
        .match-card:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        
        /* Màu sắc phân loại */
        .type-TIM_DOI { border-left-color: #dc3545; }
        .type-TIM_NGUOI { border-left-color: #28a745; }

        .badge { padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; color: white; }
        .bg-red { background: #dc3545; }
        .bg-green { background: #28a745; }

        .btn-call {
            background: #fff; color: var(--primary-color, #28a745); border: 1px solid var(--primary-color, #28a745);
            padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: bold;
            transition: 0.2s;
        }
        .btn-call:hover { background: var(--primary-color, #28a745); color: white; }
        
        .section-title { border-left: 5px solid #ffc107; padding-left: 10px; margin-bottom: 20px; color: #333; }
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
            <a href="my_bookings.php" class="btn-link"><i class="fas fa-history"></i> Lịch sử</a>
            
            <a href="#" class="btn-link" style="color: #ffc107; font-weight: bold; border-bottom: 2px solid #ffc107;">
                <i class="fas fa-handshake"></i> Cáp Kèo
            </a>
            
            <?php if($user_role === 'ADMIN'): ?>
                <a href="admin_dashboard.php" class="btn-link"><i class="fas fa-cogs"></i> Admin</a>
            <?php endif; ?>
            
            <a href="logout.php" class="btn-logout">Đăng xuất</a>
        </div>
    </nav>

    <div class="hero-section" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
    url('https://images.unsplash.com/photo-1518091043644-c1d4457512c6?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'); 
        background-size: cover; 
        background-position: center; color: white; padding:20px 60px 50px 20px; text-align: center;">
        <div class="hero-title">
            <h1 style="margin: 0; font-size: 2.5rem;">Sàn Cáp Kèo Online</h1>
            <p style="margin-top: 10px; font-size: 1.1rem; opacity: 0.9;">Tìm đối giao lưu - Tìm đồng đội thiếu chân</p>
        </div>
    </div>

    <div class="container" style="margin-top: 40px; margin-bottom: 40px;">
        
        <div class="match-layout">
            
            <div class="match-list-col">
                <h3 class="section-title">🔥 Các kèo đang chờ giao lưu</h3>
                <div id="list_matches">
                    <div style="text-align:center; padding: 20px;">
                        <i class="fas fa-spinner fa-spin"></i> Đang tải dữ liệu...
                    </div>
                </div>
            </div>

            <div class="match-form-col">
                <h3 style="margin-top:0; text-align:center; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    <i class="fas fa-bullhorn" style="color:#ffc107;"></i> Đăng Tin Mới
                </h3>
                
                <div class="form-group">
                    <label>Loại tin:</label>
                    <select id="m_type" class="form-control">
                        <option value="TIM_DOI">⚔️ Tìm Đối Thủ</option>
                        <option value="TIM_NGUOI">🤝 Tìm Đồng Đội</option>
                    </select>
                </div>

                <div class="form-group" style="margin-top:10px;">
                    <label>Ngày đá:</label>
                    <input type="date" id="m_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group" style="margin-top:10px;">
                    <label>Giờ (Dự kiến):</label>
                    <input type="time" id="m_time" class="form-control" value="17:30">
                </div>

                <div class="form-group" style="margin-top:10px;">
                    <label>Trình độ:</label>
                    <select id="m_level" class="form-control">
                        <option value="Vui vẻ">😄 Vui vẻ / Yếu</option>
                        <option value="Trung bình">⚽ Trung bình</option>
                        <option value="Khá cứng">💪 Khá / Cứng</option>
                    </select>
                </div>

                <div class="form-group" style="margin-top:10px;">
                    <label>Lời nhắn:</label>
                    <textarea id="m_message" class="form-control" rows="3" placeholder="VD: Đã có sân 5A, cần tìm đối mềm..."></textarea>
                </div>

                <div class="form-group" style="margin-top:10px;">
                    <label>SĐT Liên hệ:</label>
                    <input type="text" id="m_phone" class="form-control" placeholder="Nhập SĐT..." value="<?php echo $_SESSION['phone_number'] ?? ''; ?>">
                </div>

                <button onclick="postMatch()" class="btn-search" style="width:100%; margin-top:20px; background: #ffc107; color: #333; font-weight: bold; border:none;">
                    <i class="fas fa-paper-plane"></i> Đăng Tin
                </button>
            </div>

        </div>
    </div>

    <script>
        // --- 1. TẢI DANH SÁCH ---
        async function loadMatches() {
            const listDiv = document.getElementById('list_matches');
            try {
                const res = await fetch('api_match_list.php');
                const result = await res.json();
                
                if(result.data.length === 0) {
                    listDiv.innerHTML = `
                        <div style="text-align:center; padding: 40px; background: #f9f9f9; border-radius: 8px;">
                            <i class="far fa-paper-plane" style="font-size: 40px; color: #ddd; margin-bottom: 10px;"></i>
                            <p style="color: #666;">Chưa có kèo nào. Hãy là người đầu tiên đăng tin!</p>
                        </div>`;
                    return;
                }

                listDiv.innerHTML = "";
                result.data.forEach(m => {
                    const isTimDoi = (m.type === 'TIM_DOI');
                    const badgeClass = isTimDoi ? 'bg-red' : 'bg-green';
                    const badgeText = isTimDoi ? 'Tìm Đối' : 'Tìm Người';
                    
                    const html = `
                        <div class="match-card type-${m.type}">
                            <div style="flex: 1;">
                                <div style="margin-bottom:8px;">
                                    <span class="badge ${badgeClass}">${badgeText}</span> 
                                    <span style="font-weight:bold; margin-left:8px; color: #333;">
                                        <i class="far fa-clock"></i> ${m.match_time.substring(0,5)} - ${m.match_date}
                                    </span>
                                </div>
                                <div style="font-size:14px; color:#555; line-height: 1.6;">
                                    <div><i class="fas fa-running"></i> <strong>Trình độ:</strong> ${m.level}</div>
                                    <div><i class="far fa-comment-dots"></i> <strong>Lời nhắn:</strong> "${m.message}"</div>
                                    <div style="margin-top: 5px; color:#888; font-size: 12px;">
                                        Đăng bởi: <strong>${m.full_name}</strong>
                                    </div>
                                </div>
                            </div>
                            <div style="margin-left: 15px;">
                                <a href="tel:${m.contact_phone}" class="btn-call">
                                    <i class="fas fa-phone-alt"></i> ${m.contact_phone}
                                </a>
                            </div>
                        </div>
                    `;
                    listDiv.innerHTML += html;
                });
            } catch (e) { console.error(e); }
        }

        // --- 2. ĐĂNG KÈO ---
        async function postMatch() {
            const data = {
                type: document.getElementById('m_type').value,
                date: document.getElementById('m_date').value,
                time: document.getElementById('m_time').value,
                level: document.getElementById('m_level').value,
                message: document.getElementById('m_message').value,
                phone: document.getElementById('m_phone').value
            };

            if(!data.phone || !data.message) {
                alert("Vui lòng nhập đầy đủ SĐT và Lời nhắn!");
                return;
            }

            try {
                const res = await fetch('api_match_create.php', {
                    method: 'POST', body: JSON.stringify(data)
                });
                const result = await res.json();
                
                if(result.status === 'success') {
                    alert("✅ Đăng tin thành công!");
                    loadMatches(); // Tải lại danh sách
                    document.getElementById('m_message').value = ''; // Xóa lời nhắn cũ
                } else {
                    alert("Lỗi: " + result.message);
                }
            } catch (e) { console.error(e); }
        }

        // Chạy khi tải trang
        loadMatches();
    </script>
<?php include 'footer.php'; ?>
</body>
</html>