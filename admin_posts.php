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
    <title>Quản Lý Tin Tức</title>
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS riêng cho trang này */
        .post-thumb { 
            width: 80px; height: 50px; object-fit: cover; 
            border-radius: 4px; border: 1px solid #eee; 
        }
        
        /* Badges cho loại tin */
        .tag { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .tag-news { background: #e3f2fd; color: #0d47a1; }     /* Xanh dương */
        .tag-promo { background: #ffebee; color: #b71c1c; }    /* Đỏ */
        .tag-event { background: #e8f5e9; color: #1b5e20; }    /* Xanh lá */

        /* Modal Styles */
        .modal-overlay {
            display: none; position: fixed; z-index: 1000; left: 0; top: 0;
            width: 100%; height: 100%; overflow: auto;
            background-color: rgba(0,0,0,0.5);
            align-items: center; justify-content: center;
        }
        .modal-container {
            background-color: #fff; padding: 25px; border-radius: 8px;
            width: 100%; max-width: 600px; /* Modal to hơn chút vì có soạn thảo */
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
        textarea.form-control { height: 120px; resize: vertical; font-family: inherit; }
        
        .modal-footer { text-align: right; margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; }
        .btn-cancel { background: #6c757d; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin-right: 5px; }
        .btn-save { background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
    </style>
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        
        <div class="dashboard-header">
            <h2 class="dashboard-title">Tin Tức & Sự Kiện</h2>
            <button class="btn-save" onclick="openModal('create')">
                <i class="fas fa-pen-nib"></i> Đăng Bài Mới
            </button>
        </div>

        <div class="content-wrapper">
            <div class="content-box">
                <div class="box-header">
                    <i class="fas fa-newspaper"></i> Danh sách bài viết
                </div>
                
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Ảnh</th>
                                <th style="width: 30%;">Tiêu đề</th>
                                <th>Phân loại</th>
                                <th>Nội dung tóm tắt</th>
                                <th>Ngày đăng</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="post_list">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="postModal" class="modal-overlay">
        <div class="modal-container">
            <h3 id="modal_title" style="margin-top:0; color:#333;">Đăng Bài Viết</h3>
            
            <form id="formPost" onsubmit="savePost(event)" enctype="multipart/form-data">
                <input type="hidden" name="id" id="post_id">
                <input type="hidden" name="action" id="post_action">

                <div class="form-group">
                    <label>Tiêu đề bài viết <span style="color:red">*</span></label>
                    <input type="text" name="title" id="post_title" class="form-control" required placeholder="Nhập tiêu đề...">
                </div>

                <div class="form-group">
                    <label>Loại tin</label>
                    <select name="type" id="post_type" class="form-control">
                        <option value="NEWS">📰 Tin tức chung</option>
                        <option value="PROMO">🎁 Khuyến mãi</option>
                        <option value="EVENT">🏆 Giải đấu / Sự kiện</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nội dung chi tiết <span style="color:red">*</span></label>
                    <textarea name="content" id="post_content" class="form-control" required placeholder="Nội dung bài viết..."></textarea>
                </div>

                <div class="form-group">
                    <label>Hình ảnh minh họa</label>
                    <input type="file" name="image" id="post_image" class="form-control" accept="image/*">
                    <small style="color:#666;">Chỉ chọn nếu muốn thay đổi ảnh (khi sửa) hoặc thêm mới.</small>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeModal()" class="btn-cancel">Hủy</button>
                    <button type="submit" class="btn-save"><i class="fas fa-paper-plane"></i> Lưu Bài</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let postsData = [];

        // --- 1. TẢI DANH SÁCH ---
        async function loadPosts() {
            try {
                const res = await fetch('api_posts.php');
                const result = await res.json();
                const tbody = document.getElementById('post_list');
                tbody.innerHTML = '';

                if (result.status === 'success') {
                    postsData = result.data; // Lưu dữ liệu để dùng cho Edit

                    if(postsData.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:20px; color:#999;">Chưa có bài viết nào.</td></tr>';
                        return;
                    }

                    postsData.forEach(p => {
                        // Xử lý loại tin
                        let badge = '<span class="tag tag-news">Tin tức</span>';
                        if(p.type === 'PROMO') badge = '<span class="tag tag-promo">Khuyến mãi</span>';
                        if(p.type === 'EVENT') badge = '<span class="tag tag-event">Sự kiện</span>';

                        // Xử lý ảnh
                        const imgSrc = p.image ? p.image : 'https://via.placeholder.com/80x50?text=No+Img';

                        const html = `
                            <tr>
                                <td><img src="${imgSrc}" class="post-thumb"></td>
                                <td style="font-weight:600; color:#333;">${p.title}</td>
                                <td>${badge}</td>
                                <td style="color:#666; font-size:13px; max-width:250px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    ${p.content}
                                </td>
                                <td style="font-size:13px;">${p.created_at}</td>
                                <td>
                                    <button class="btn-action btn-edit" style="background:#ffc107; color:#333;" onclick="openEdit(${p.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-reject" onclick="deletePost(${p.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.innerHTML += html;
                    });
                }
            } catch (e) { console.error(e); }
        }

        // --- 2. XỬ LÝ MODAL ---
        function openModal(mode) {
            document.getElementById('postModal').style.display = 'flex';
            document.getElementById('post_action').value = mode; // 'create' hoặc 'update'

            if(mode === 'create') {
                document.getElementById('modal_title').innerText = "Đăng Bài Viết Mới";
                document.getElementById('formPost').reset();
                document.getElementById('post_id').value = '';
            }
        }

        function openEdit(id) {
            const post = postsData.find(p => p.id == id);
            if(post) {
                openModal('update');
                document.getElementById('modal_title').innerText = "Chỉnh Sửa Bài Viết";
                
                document.getElementById('post_id').value = post.id;
                document.getElementById('post_title').value = post.title;
                document.getElementById('post_type').value = post.type;
                document.getElementById('post_content').value = post.content;
                document.getElementById('post_image').value = ''; // Reset file input
            }
        }

        function closeModal() {
            document.getElementById('postModal').style.display = 'none';
        }

        // --- 3. LƯU BÀI (THÊM / SỬA) ---
        async function savePost(e) {
            e.preventDefault();
            const form = document.getElementById('formPost');
            const formData = new FormData(form);

            try {
                const res = await fetch('api_posts.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status === 'success') {
                    alert("✅ Thành công!");
                    closeModal();
                    loadPosts();
                } else {
                    alert("❌ Lỗi: " + data.message);
                }
            } catch (e) { console.error(e); }
        }

        // --- 4. XÓA BÀI ---
        async function deletePost(id) {
            if(!confirm("⚠️ Bạn có chắc muốn xóa bài này? Hành động không thể hoàn tác!")) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            try {
                const res = await fetch('api_posts.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.status === 'success') loadPosts();
                else alert(data.message);
            } catch (e) { console.error(e); }
        }

        // Đóng modal khi click ra ngoài
        window.onclick = function(event) {
            if (event.target == document.getElementById('postModal')) {
                closeModal();
            }
        }

        loadPosts();
    </script>
</body>
</html>