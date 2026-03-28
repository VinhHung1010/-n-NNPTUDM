<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/yeu_thich.php';

header('Content-Type: application/json');

$auth = new Auth();
$yeu_thich_model = new YeuThich();

// Kiểm tra đăng nhập
if (!$auth->kiemTraDangNhap()) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn cần đăng nhập để thực hiện chức năng này.'
    ]);
    exit;
}

$id_nguoi_dung = $_SESSION['nguoi_dung']['id'];
$action = $_GET['action'] ?? '';
$id_khoa_hoc = isset($_GET['id_khoa_hoc']) ? (int)$_GET['id_khoa_hoc'] : 0;

if ($id_khoa_hoc <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID khóa học không hợp lệ.'
    ]);
    exit;
}

switch ($action) {
    case 'toggle':
        $result = $yeu_thich_model->toggle($id_nguoi_dung, $id_khoa_hoc);
        echo json_encode([
            'success' => true,
            'data' => $result
        ]);
        break;

    case 'check':
        $is_favorited = $yeu_thich_model->daYeuThich($id_nguoi_dung, $id_khoa_hoc);
        echo json_encode([
            'success' => true,
            'data' => ['is_favorited' => $is_favorited]
        ]);
        break;

    case 'add':
        $yeu_thich_model->them($id_nguoi_dung, $id_khoa_hoc);
        echo json_encode([
            'success' => true,
            'message' => 'Đã thêm vào danh sách yêu thích.'
        ]);
        break;

    case 'remove':
        $yeu_thich_model->xoa($id_nguoi_dung, $id_khoa_hoc);
        echo json_encode([
            'success' => true,
            'message' => 'Đã xóa khỏi danh sách yêu thích.'
        ]);
        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Hành động không hợp lệ.'
        ]);
}
