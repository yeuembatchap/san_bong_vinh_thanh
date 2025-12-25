<?php
// api_weather.php
// Tắt hiển thị lỗi PHP ra màn hình để tránh làm hỏng cấu trúc JSON
error_reporting(0); 
header('Content-Type: application/json');

// --- CẤU HÌNH ---
$apiKey = "2fc5c1e9715579dcfac367bd805a0ed0"; // Key của bạn (mình lấy từ log lỗi bạn gửi)
$city = "Nha Trang"; 
// ----------------

// QUAN TRỌNG: Mã hóa tên thành phố để xử lý dấu cách (Nha Trang -> Nha+Trang hoặc Nha%20Trang)
$cityEncoded = urlencode($city);

// URL gọi API
$apiUrl = "http://api.openweathermap.org/data/2.5/weather?q={$cityEncoded}&appid={$apiKey}&units=metric&lang=vi";

// Dùng @ để chặn dòng Warning in ra màn hình nếu có lỗi
$response = @file_get_contents($apiUrl);

if ($response === FALSE) {
    // Trả về JSON lỗi để Frontend xử lý
    echo json_encode(['cod' => 404, 'message' => 'Không lấy được dữ liệu thời tiết (Kiểm tra lại kết nối hoặc API Key)']);
} else {
    
    echo $response;
}
?>