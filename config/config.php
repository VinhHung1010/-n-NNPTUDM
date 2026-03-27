<?php
// Cấu hình kết nối database
define('DB_HOST', 'localhost');
define('DB_PORT', 3307);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'elearning_db');

// Cấu hình site
define('SITE_NAME', 'E-LEARNING Việt Nam');
// '' = php -S hoặc VirtualHost trỏ thẳng vào thư mục project; '/DANNPTUDM' khi đặt trong htdocs/DANNPTUDM
define('APP_BASE', '');
define('HOME_URL', APP_BASE . '/index.php');
// URL tới thư mục views (các trang PHP nằm trong views/khoa-hoc, views/tai-khoan, ...)
define('BASE_PATH', APP_BASE . '/views');

// Cấu hình timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Bắt đầu session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
