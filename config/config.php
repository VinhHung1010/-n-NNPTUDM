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

// Thanh toán: demo = mô phỏng cổng (localhost); sau này có thể đổi sang vnpay/momo
define('THANH_TOAN_KENH', 'demo');

// Cấu hình timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Bắt đầu session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
