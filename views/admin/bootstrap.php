<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/auth.php';

$auth = new Auth();
if (!$auth->laQuanTri()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$nguoi_dung_admin = $auth->layThongTinNguoiDung();
