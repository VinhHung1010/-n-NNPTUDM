<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/auth.php';
require_once __DIR__ . '/../models/thong_bao.php';

$auth = new Auth();
$thong_bao = new ThongBao();

if (!$auth->kiemTraDangNhap()) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$nguoi_dung = $auth->layThongTinNguoiDung();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'mark_read':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            exit;
        }
        $tb = $thong_bao->layTheoId($id);
        if (!$tb || $tb['id_nguoi_nhan'] != $nguoi_dung['id']) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền']);
            exit;
        }
        $thong_bao->danhDauDaDoc($id);
        echo json_encode(['success' => true, 'message' => 'Đã đánh dấu đã đọc']);
        break;

    case 'mark_all_read':
        $thong_bao->danhDauTatCaDaDoc($nguoi_dung['id']);
        echo json_encode(['success' => true, 'message' => 'Đã đánh dấu tất cả đã đọc']);
        break;

    case 'delete':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            exit;
        }
        $tb = $thong_bao->layTheoId($id);
        if (!$tb || $tb['id_nguoi_nhan'] != $nguoi_dung['id']) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền']);
            exit;
        }
        $thong_bao->xoa($id);
        echo json_encode(['success' => true, 'message' => 'Đã xóa thông báo']);
        break;

    case 'get_unread_count':
        $count = $thong_bao->demChuaDoc($nguoi_dung['id']);
        echo json_encode(['success' => true, 'count' => $count]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
        break;
}
?>
