<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/auth.php';
require_once __DIR__ . '/../models/goi_y.php';

$auth = new Auth();
$gy_model = new GoiY();

if (!$auth->kiemTraDangNhap()) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$nguoi_dung = $auth->layThongTinNguoiDung();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_recommendations':
        $limit = intval($_GET['limit'] ?? 12);
        $loai = $_GET['type'] ?? 'all';

        if ($loai === 'cung_danh_muc') {
            $data = $gy_model->goiYCungDanhMuc($nguoi_dung['id'], $limit);
        } elseif ($loai === 'cung_giao_vien') {
            $data = $gy_model->goiYCungGiaoVien($nguoi_dung['id'], $limit);
        } elseif ($loai === 'pho_bien') {
            $data = $gy_model->goiYPhoBien($nguoi_dung['id'], $limit);
        } elseif ($loai === 'moi_nhat') {
            $data = $gy_model->goiYMoiNhat($nguoi_dung['id'], $limit);
        } else {
            $data = $gy_model->goiYTongHop($nguoi_dung['id'], $limit);
        }

        echo json_encode([
            'success' => true,
            'recommendations' => $data,
            'count' => count($data)
        ]);
        break;

    case 'record_view':
        $id_khoa_hoc = intval($_POST['id_khoa_hoc'] ?? 0);
        $thoi_gian = intval($_POST['time_spent'] ?? 0);

        if ($id_khoa_hoc <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID khóa học không hợp lệ']);
            exit;
        }

        $gy_model->ghiNhanXem($nguoi_dung['id'], $id_khoa_hoc, $thoi_gian);
        echo json_encode(['success' => true, 'message' => 'Đã ghi nhận']);
        break;

    case 'rate_course':
        $id_khoa_hoc = intval($_POST['id_khoa_hoc'] ?? 0);
        $diem_so = intval($_POST['rating'] ?? 0);
        $binh_luan = trim($_POST['comment'] ?? '');

        if ($id_khoa_hoc <= 0 || $diem_so < 1 || $diem_so > 5) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        $gy_model->danhGia($nguoi_dung['id'], $id_khoa_hoc, $diem_so, $binh_luan);
        echo json_encode(['success' => true, 'message' => 'Cảm ơn bạn đã đánh giá!']);
        break;

    case 'get_reviews':
        $id_khoa_hoc = intval($_GET['course_id'] ?? 0);
        $danh_gia = $gy_model->layDanhGiaKhoaHoc($id_khoa_hoc, 20);
        $thong_ke = $gy_model->layThongKeDanhGia($id_khoa_hoc);

        echo json_encode([
            'success' => true,
            'reviews' => $danh_gia,
            'stats' => $thong_ke
        ]);
        break;

    case 'get_view_history':
        $lich_su = $gy_model->layKhoaHocDaXem($nguoi_dung['id'], 20);
        echo json_encode([
            'success' => true,
            'history' => $lich_su
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
        break;
}
?>
