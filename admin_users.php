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
    <title>Quản Lý Người Dùng</title>
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS riêng cho trang này */
        .search-box {
            display: flex; align-items: center; background: white;
            border: 1px solid #ddd; border-radius: 4px; padding: 0 10px;
            width: 300px;
        }
        .search-box input {
            border: none; outline: none; padding: 8px; width: 100%; font-size: 14px;
        }
        .search-box i { color: #888; }

        /* Modal Styles (Giống trang Fields) */
        .modal-overlay {
            display: none; position: fixed; z-index: 1000; left: 0; top: 0;
            width: 100%; height: 100%; overflow: auto;
            background-color: rgba(0,0,0,0.5);
            align-items: center; justify-content: center;
        }
        .modal-container {
            background-color: #fff; padding: 25px; border-radius: 8px;
            width: 100%; max-width: 450px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown { from {top: -50px; opacity: 0;} to {top: 0; opacity: 1;} }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
        .form-control {
            width: 100%; padding: 10px; border: 1px solid #ddd;
            border-radius: 4px; box-sizing: border-box; font-size: 14px;
        }
        .form-control:focus { border-color: #28a745; outline: none; }
        
        .modal-footer { text-align: right; margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; }
        .btn-cancel { background: #6c757d; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin-right: 5px; }
        .btn-save { background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }

        /* Badges */
        .badge-admin { background: #ffeeba; color: #856404; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-user { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
    </style>
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        
        <div class="dashboard-header">
            <h2 class="dashboard-title">Danh Sách Thành Viên</h2>
            <button class="btn-save" onclick="openModal('create')">
                <i class="fas fa-user-plus"></i> Thêm User
            </button>
        </div>

        <div class="content-wrapper">
            
            <div class="filter-box" style="justify-content: flex-start;">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="keyword" placeholder="Tìm tên, email hoặc SĐT..." onkeyup="if(event.key === 'Enter') loadUsers()">
                </div>
                <button onclick="loadUsers()" class="btn-cancel" style="background: var(--primary-color);">Tìm kiếm</button>
            </div>

            <div class="content-box">
                <div class="box-header">
                    <i class="fas fa-users"></i> Tài khoản hệ thống
                </div>
                
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Họ Tên</th>
                                <th>Thông tin liên hệ</th>
                                <th>Vai trò</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="user_list">
                            </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <div id="userModal" class="modal-overlay">
        <div class="modal-container">
            <h3 id="modal_title" style="margin-top:0; color:#333;">Thêm Người Dùng</h3>
            
            <input type="hidden" id="u_id">
            <input type="hidden" id="u_action"> <div class="form-group">
                <label>Họ và Tên <span style="color:red">*</span></label>
                <input type="text" id="u_name" class="form-control" placeholder="Nguyễn Văn A">
            </div>
            
            <div class="form-group">
                <label>Email (Tài khoản) <span style="color:red">*</span></label>
                <input type="email" id="u_email" class="form-control" placeholder="email@example.com">
            </div>

            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" id="u_phone" class="form-control" placeholder="0909...">
            </div>

            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" id="u_pass" class="form-control" placeholder="Để trống nếu không muốn đổi pass">
                <small style="color:#666; font-size:11px; font-style: italic;">* Bắt buộc nhập khi tạo mới</small>
            </div>

            <div class="form-group">
                <label>Phân quyền</label>
                <select id="u_role" class="form-control">
                    <option value="CUSTOMER">Khách hàng (Customer)</option>
                    <option value="ADMIN">Quản trị viên (Admin)</option>
                </select>
            </div>

            <div class="modal-footer">
                <button onclick="closeModal()" class="btn-cancel">Đóng</button>
                <button onclick="saveUser()" class="btn-save"><i class="fas fa-save"></i> Lưu lại</button>
            </div>
        </div>
    </div>

    <script>
        let usersData = []; // Biến lưu dữ liệu

        // --- 1. TẢI DANH SÁCH USER ---
        async function loadUsers() {
            const keyword = document.getElementById('keyword').value;
            try {
                // Đảm bảo file api_users.php tồn tại và trả về JSON chuẩn
                const res = await fetch(`api_users.php?keyword=${encodeURIComponent(keyword)}`);
                const result = await res.json();
                
                const tbody = document.getElementById('user_list');
                tbody.innerHTML = '';

                if(result.status === 'success') {
                    usersData = result.data; // Lưu lại để dùng khi edit

                    if(usersData.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:15px; color:#999;">Không tìm thấy người dùng nào.</td></tr>';
                        return;
                    }
                    
                    usersData.forEach(u => {
                        const roleBadge = u.role === 'ADMIN' 
                            ? `<span class="badge-admin">ADMIN</span>` 
                            : `<span class="badge-user">CUSTOMER</span>`;
                        
                        const html = `
                            <tr>
                                <td><b>#${u.id}</b></td>
                                <td style="font-weight:600; color:#333;">${u.full_name}</td>
                                <td>
                                    <div style="font-size:13px; color:#555;"><i class="fas fa-envelope"></i> ${u.email}</div>
                                    <div style="font-size:13px; color:#888; margin-top:2px;"><i class="fas fa-phone"></i> ${u.phone_number || '---'}</div>
                                </td>
                                <td>${roleBadge}</td>
                                <td>
                                    <button class="btn-action btn-edit" style="background:#17a2b8;" onclick="openEdit(${u.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-reject" onclick="deleteUser(${u.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.innerHTML += html;
                    });
                }
            } catch(e) { console.error("Lỗi tải user:", e); }
        }

        // --- 2. XỬ LÝ MODAL ---
        function openModal(mode) {
            document.getElementById('userModal').style.display = 'flex';
            document.getElementById('u_action').value = mode;

            if(mode === 'create') {
                document.getElementById('modal_title').innerText = "Thêm Người Dùng Mới";
                // Reset form
                document.getElementById('u_id').value = '';
                document.getElementById('u_name').value = '';
                document.getElementById('u_email').value = '';
                document.getElementById('u_phone').value = '';
                document.getElementById('u_pass').value = '';
                document.getElementById('u_role').value = 'CUSTOMER';
            }
        }

        function openEdit(id) {
            const user = usersData.find(u => u.id == id);
            if(user) {
                openModal('update_info'); // Server cần xử lý action này
                document.getElementById('modal_title').innerText = "Cập Nhật: " + user.full_name;
                
                document.getElementById('u_id').value = user.id;
                document.getElementById('u_name').value = user.full_name;
                document.getElementById('u_email').value = user.email;
                document.getElementById('u_phone').value = user.phone_number;
                document.getElementById('u_role').value = user.role;
                document.getElementById('u_pass').value = ''; // Luôn để trống pass khi sửa
            }
        }

        function closeModal() {
            document.getElementById('userModal').style.display = 'none';
        }

        // --- 3. LƯU DỮ LIỆU ---
        async function saveUser() {
            const action = document.getElementById('u_action').value;
            const payload = {
                action: action,
                user_id: document.getElementById('u_id').value,
                full_name: document.getElementById('u_name').value,
                email: document.getElementById('u_email').value,
                phone: document.getElementById('u_phone').value,
                password: document.getElementById('u_pass').value,
                role: document.getElementById('u_role').value
            };

            // Validate cơ bản
            if(!payload.full_name || !payload.email) { alert("Vui lòng nhập tên và email!"); return; }
            if(action === 'create' && !payload.password) { alert("Tạo mới bắt buộc phải nhập mật khẩu!"); return; }

            try {
                const res = await fetch('api_users.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                
                if(data.status === 'success') {
                    alert("✅ Lưu thành công!");
                    closeModal();
                    loadUsers();
                } else {
                    alert("❌ Lỗi: " + data.message);
                }
            } catch(e) { console.error(e); alert("Lỗi kết nối server"); }
        }

        // --- 4. XÓA USER ---
        async function deleteUser(id) {
            if(!confirm("⚠️ Bạn có chắc chắn muốn xóa tài khoản này?")) return;
            try {
                const res = await fetch('api_users.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'delete', user_id: id })
                });
                const data = await res.json();
                if(data.status === 'success') {
                    loadUsers();
                } else {
                    alert("❌ " + data.message);
                }
            } catch(e) { console.error(e); }
        }

        // Đóng modal khi click ra ngoài
        window.onclick = function(event) {
            if (event.target == document.getElementById('userModal')) {
                closeModal();
            }
        }

        // Chạy lần đầu
        loadUsers();
    </script>
</body>
</html>