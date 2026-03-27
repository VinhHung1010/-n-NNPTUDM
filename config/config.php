<?php
// Cấu hình kết nối database
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'elearning_db');

// Cấu hình site
define('SITE_NAME', 'E-LEARNING Việt Nam');
define('SITE_URL', 'http://localhost/DANNPTUDM');

// Cấu hình timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Bắt đầu session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
