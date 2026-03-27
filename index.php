<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/auth.php';

$auth = new Auth();

if ($auth->laQuanTri()) {
    header('Location: ./admin/index.php');
    exit;
}

if ($auth->laGiaoVien()) {
    header('Location: ./teacher/index.php');
    exit;
}

if ($auth->kiemTraDangNhap()) {
    header('Location: ./views/home/index.php');
    exit;
}

header('Location: ./views/khoa-hoc/index.php');
exit;
?>
