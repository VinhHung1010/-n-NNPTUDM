<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/binh_luan.php';

$auth = new Auth();
$nguoi_dung = $auth->layThongTinNguoiDung();

if (!$nguoi_dung) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để bình luận!']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ!']);
    exit;
}

$bai_hoc_id = isset($_POST['bai_hoc_id']) ? (int)$_POST['bai_hoc_id'] : 0;
$noi_dung = isset($_POST['noi_dung']) ? trim($_POST['noi_dung']) : '';

if ($bai_hoc_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID bài học không hợp lệ!']);
    exit;
}

if (empty($noi_dung)) {
    echo json_encode(['success' => false, 'message' => 'Nội dung bình luận không được trống!']);
    exit;
}

$bl_model = new BinhLuan();
$result = $bl_model->them($bai_hoc_id, $nguoi_dung['id'], $noi_dung);

echo json_encode($result);
?>
