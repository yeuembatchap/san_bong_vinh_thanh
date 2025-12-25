<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Sân Bóng</title>
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ... (Giữ nguyên các CSS cũ) ... */
        .modal-overlay { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-container { background-color: #fff; padding: 25px; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); position: relative; animation: slideDown 0.3s ease; }
        @keyframes slideDown { from {top: -50px; opacity: 0;} to {top: 0; opacity: 1;} }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        .form-control:focus { border-color: #28a745; outline: none; }
        .modal-footer { text-align: right; margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; }
        .btn-cancel { background: #6c757d; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin-right: 5px; }
        .btn-save { background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
        .field-thumb { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; }

        /* --- CSS MỚI CHO TRẠNG THÁI --- */
        .badge-status { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; display: inline-block; }
        .status-active { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-maintenance { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* CSS cho nút trạng thái */
        .btn-toggle-on { background: #fff; border: 1px solid #28a745; color: #28a745; cursor: pointer; padding: 5px 8px; border-radius: 4px; }
        .btn-toggle-on:hover { background: #28a745; color: white; }
        
        .btn-toggle-off { background: #fff; border: 1px solid #dc3545; color: #dc3545; cursor: pointer; padding: 5px 8px; border-radius: 4px; }
        .btn-toggle-off:hover { background: #dc3545; color: white; }
    </style>
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-header">
            <h2 class="dashboard-title">Danh Sách Sân Bóng</h2>
            <button class="btn-save" onclick="openModal('add')">
                <i class="fas fa-plus"></i> Thêm Sân Mới
            </button>
        </div>

        <div class="content-wrapper">
            <div class="content-box">
                <div class="box-header">
                    <i class="fas fa-list"></i> Quản lý thông tin sân bãi
                </div>
                
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hình ảnh</th>
                                <th>Tên sân</th>
                                <th>Giá / Giờ</th>
                                <th>Trạng thái</th> <th>Mô tả</th>
                                <th style="min-width: 120px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="field_list"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="fieldModal" class="modal-overlay">
        <div class="modal-container">
            <h3 id="modal_title" style="margin-top:0; color:#333;">Thêm Sân Mới</h3>
            <form id="formField" onsubmit="saveField(event)" enctype="multipart/form-data">
                <input type="hidden" name="id" id="field_id">
                <input type="hidden" name="action" id="field_action">
                
                <div class="form-group">
                    <label>Tên Sân <span style="color:red">*</span></label>
                    <input type="text" name="name" id="field_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Giá tiền (VND/giờ) <span style="color:red">*</span></label>
                    <input type="number" name="price" id="field_price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="status" id="field_status" class="form-control">
                        <option value="ACTIVE">Hoạt động</option>
                        <option value="MAINTENANCE">Bảo trì</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mô tả tiện ích</label>
                    <textarea name="description" id="field_desc" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Hình ảnh</label>
                    <input type="file" name="image" id="field_image" class="form-control" accept="image/*">
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal()" class="btn-cancel">Hủy bỏ</button>
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Lưu lại</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let fieldsData = [];
        const formatMoney = (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);

        async function loadFields() {
            try {
                const res = await fetch('api_fields.php');
                const result = await res.json();
                const tbody = document.getElementById('field_list');
                tbody.innerHTML = '';

                if(result.status === 'success') {
                    fieldsData = result.data;
                    if(fieldsData.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:15px; color:#999;">Chưa có sân nào.</td></tr>';
                        return;
                    }

                    fieldsData.forEach(f => {
                        const imgShow = f.image ? `<img src="${f.image}" class="field-thumb">` : '<span style="color:#999; font-size:12px;">Không ảnh</span>';
                        
                        // XỬ LÝ TRẠNG THÁI (Active = 1, Maintenance = 0)
                        let is_active = (f.is_active == 1);
                        let badge = is_active 
                            ? '<span class="badge-status status-active">Hoạt động</span>' 
                            : '<span class="badge-status status-maintenance">Bảo trì</span>';
                        
                        // Nút hành động: Nếu đang active thì hiện nút Tắt (Đỏ), ngược lại hiện nút Bật (Xanh)
                        let toggleBtn = is_active 
                            ? `<button class="btn-toggle-off" onclick="toggleStatus(${f.id}, 0)" title="Chuyển sang bảo trì"><i class="fas fa-power-off"></i></button>`
                            : `<button class="btn-toggle-on" onclick="toggleStatus(${f.id}, 1)" title="Kích hoạt hoạt động"><i class="fas fa-power-off"></i></button>`;

                        const html = `
                            <tr>
                                <td><b>#${f.id}</b></td>
                                <td>${imgShow}</td>
                                <td style="font-weight:600; color:#28a745;">${f.name}</td>
                                <td>${formatMoney(f.price_per_hour)}</td>
                                <td>${badge}</td>
                                <td style="font-size:13px; color:#666; max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${f.description || '-'}</td>
                                <td>
                                    ${toggleBtn}
                                    
                                    <button class="btn-action btn-edit" style="background:#ffc107; color:#333; border:none; padding:5px 8px; border-radius:4px; cursor:pointer;" onclick="openEdit(${f.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-reject" style="background:#dc3545; color:white; border:none; padding:5px 8px; border-radius:4px; cursor:pointer;" onclick="deleteField(${f.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.innerHTML += html;
                    });
                }
            } catch(err) { console.error("Lỗi:", err); }
        }

        // --- HÀM MỚI: ĐỔI TRẠNG THÁI NHANH ---
        async function toggleStatus(id, newStatus) {
            const actionName = newStatus === 1 ? "KÍCH HOẠT" : "BẢO TRÌ";
            if(!confirm(`Bạn có chắc muốn chuyển sân này sang trạng thái ${actionName}?`)) return;

            const formData = new FormData();
            formData.append('action', 'toggle_status');
            formData.append('id', id);
            formData.append('status', newStatus);

            try {
                const res = await fetch('api_fields.php', { method: 'POST', body: formData });
                const data = await res.json();
                if(data.status === 'success') {
                    // alert("Đã cập nhật!"); // Có thể bỏ alert nếu muốn nhanh
                    loadFields(); // Load lại bảng
                } else {
                    alert("Lỗi: " + data.message);
                }
            } catch(e) { console.error(e); }
        }

        // --- CÁC HÀM CŨ GIỮ NGUYÊN ---
        function openModal(mode) {
            document.getElementById('fieldModal').style.display = 'flex';
            document.getElementById('field_action').value = mode;
            if(mode === 'add') {
                document.getElementById('modal_title').innerText = "Thêm Sân Mới";
                document.getElementById('formField').reset();
                document.getElementById('field_id').value = '';
                document.getElementById('field_status').value = 'ACTIVE';
            }
        }

        function openEdit(id) {
            const field = fieldsData.find(f => f.id == id);
            if (field) {
                openModal('update');
                document.getElementById('modal_title').innerText = "Cập Nhật Sân #" + id;
                document.getElementById('field_id').value = field.id;
                document.getElementById('field_name').value = field.name;
                document.getElementById('field_price').value = field.price_per_hour;
                document.getElementById('field_desc').value = field.description || '';
                document.getElementById('field_status').value = (field.is_active == 1) ? 'ACTIVE' : 'MAINTENANCE';
                document.getElementById('field_image').value = '';
            }
        }

        function closeModal() { document.getElementById('fieldModal').style.display = 'none'; }

        async function saveField(e) {
            e.preventDefault();
            const form = document.getElementById('formField');
            const formData = new FormData(form);
            try {
                const res = await fetch('api_fields.php', { method: 'POST', body: formData });
                const data = await res.json();
                if(data.status === 'success') { alert("Lưu thành công!"); closeModal(); loadFields(); } 
                else { alert("Lỗi: " + data.message); }
            } catch(err) { alert("Lỗi server"); }
        }

        async function deleteField(id) {
            if(!confirm("Xóa sân này?")) return;
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            try {
                const res = await fetch('api_fields.php', { method: 'POST', body: formData });
                const data = await res.json();
                if(data.status === 'success') loadFields();
                else alert("Lỗi xóa: " + data.message);
            } catch(e) {}
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('fieldModal')) closeModal();
        }

        loadFields();
    </script>
</body>
</html>