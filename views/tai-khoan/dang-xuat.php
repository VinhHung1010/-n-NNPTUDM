<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';

$auth = new Auth();

// Kiểm tra đăng nhập
if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . BASE_PATH . '/tai-khoan/dang-nhap.php');
    exit;
}

// Đăng xuất
$auth->dangXuat();
header('Location: ' . HOME_URL);
exit;
?>
