<?php
session_start();
session_destroy(); // Xóa sạch session
header('Location: booking_view.php'); // Quay về trang đăng nhập
?>