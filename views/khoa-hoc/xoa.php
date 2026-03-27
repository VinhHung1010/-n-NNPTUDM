<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';

$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . SITE_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

if ($_SESSION['nguoi_dung']['vai_tro'] !== 'quan_tri') {
    header('Location: ' . SITE_URL . '/khoa-hoc/index.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$khoa_hoc_model = new KhoaHoc();
$result = $khoa_hoc_model->xoa($id);

if ($result['success']) {
    header('Location: index.php');
    exit;
} else {
    header('Location: chi-tiet.php?id=' . $id);
    exit;
}
?>
