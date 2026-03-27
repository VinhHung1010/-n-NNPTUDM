<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/models/auth.php';

$auth = new Auth();
if (!$auth->laQuanTri()) {
    header('Location: ' . SITE_URL . '/views/tai-khoan/dang-nhap.php');
    exit;
}

$nguoi_dung_admin = $auth->layThongTinNguoiDung();
