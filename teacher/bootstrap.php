<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/models/auth.php';

$auth = new Auth();
if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$vai_tro = $_SESSION['nguoi_dung']['vai_tro'] ?? '';
if ($vai_tro !== 'giao_vien' && $vai_tro !== 'quan_tri') {
    header('Location: ' . SITE_URL . '/views/home/index.php');
    exit;
}

$nguoi_dung = $auth->layThongTinNguoiDung();
?>
