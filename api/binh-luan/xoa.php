<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/binh_luan.php';

$auth = new Auth();
$nguoi_dung = $auth->layThongTinNguoiDung();

if (!$nguoi_dung) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để xóa bình luận!']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ!']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID bình luận không hợp lệ!']);
    exit;
}

// Kiểm tra quyền xóa
$bl_model = new BinhLuan();
$binh_luan = $bl_model->layTheoId($id);

if (!$binh_luan) {
    echo json_encode(['success' => false, 'message' => 'Bình luận không tồn tại!']);
    exit;
}

if ($binh_luan['id_nguoi_dung'] != $nguoi_dung['id'] && $nguoi_dung['vai_tro'] !== 'quan_tri') {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa bình luận này!']);
    exit;
}

$result = $bl_model->xoa($id);
echo json_encode($result);
?>
