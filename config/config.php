<?php
// Cấu hình kết nối database
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'elearning_db');

// Cấu hình site
define('SITE_NAME', 'E-LEARNING Việt Nam');
define('SITE_URL', 'http://localhost/-n-NNPTUDM');
define('VIEWS_URL', SITE_URL . '/views');

// ─── Thanh toán qua VietQR (chuyển khoản ngân hàng) ───
// Cấu hình tài khoản nhận tiền — dùng cho VietQR.io
define('QR_BANK_ID',   '970403');               // Mã ngân hàng Techcombank
define('QR_BANK_NAME', 'TECHCOMBANK');           // Tên ngân hàng viết HOA
define('QR_STK',       '123456789');            // Số tài khoản nhận tiền
define('QR_CHU_TK',    'NGUYEN VAN A');         // Tên chủ tài khoản
// Ảnh mã QR cố định (MoMo / ZaloPay / ví…), file đặt tại public/img/thanh-toan-qr.png
define('QR_CO_DINH_URL', SITE_URL . '/public/img/thanh-toan-qr.png');
// ─────────────────────────────────────────────────────────

// Thanh toán: demo = mô phỏng cổng (localhost); sau này có thể đổi sang vnpay/momo
define('THANH_TOAN_KENH', 'demo');

// Cấu hình timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Bắt đầu session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
