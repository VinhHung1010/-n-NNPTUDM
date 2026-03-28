<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/danh_gia.php';

header('Content-Type: application/json');

$auth = new Auth();
$danh_gia_model = new DanhGia();

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
    case 'submit':
        $diem_so = isset($_POST['diem_so']) ? (int)$_POST['diem_so'] : 0;
        $noi_dung = isset($_POST['noi_dung']) ? trim($_POST['noi_dung']) : '';

        // Validate
        if ($diem_so < 1 || $diem_so > 5) {
            echo json_encode([
                'success' => false,
                'message' => 'Điểm số phải từ 1 đến 5 sao.'
            ]);
            exit;
        }

        $result = $danh_gia_model->themHoacCapNhat($id_nguoi_dung, $id_khoa_hoc, $diem_so, $noi_dung);
        echo json_encode($result);
        break;

    case 'get':
        $danh_gia = $danh_gia_model->layTheoNguoiDung($id_nguoi_dung, $id_khoa_hoc);
        $thong_ke = $danh_gia_model->tinhDiemTrungBinh($id_khoa_hoc);
        $danh_sach = $danh_gia_model->layTheoKhoaHoc($id_khoa_hoc);

        echo json_encode([
            'success' => true,
            'data' => [
                'my_review' => $danh_gia,
                'stats' => $thong_ke,
                'reviews' => $danh_sach
            ]
        ]);
        break;

    case 'check':
        $danh_gia = $danh_gia_model->layTheoNguoiDung($id_nguoi_dung, $id_khoa_hoc);
        $thong_ke = $danh_gia_model->tinhDiemTrungBinh($id_khoa_hoc);
        echo json_encode([
            'success' => true,
            'data' => [
                'has_reviewed' => $danh_gia !== null,
                'my_review' => $danh_gia,
                'stats' => $thong_ke
            ]
        ]);
        break;

    case 'delete':
        $danh_gia = $danh_gia_model->layTheoNguoiDung($id_nguoi_dung, $id_khoa_hoc);
        if ($danh_gia && $danh_gia_model->xoa($danh_gia['id'])) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã xóa đánh giá.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể xóa đánh giá.'
            ]);
        }
        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Hành động không hợp lệ.'
        ]);
}
