<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/auth.php';
require_once __DIR__ . '/../models/hoi_dap.php';
require_once __DIR__ . '/../models/thong_bao.php';

$auth = new Auth();
$qd_model = new HoiDap();
$tb_model = new ThongBao();

if (!$auth->kiemTraDangNhap()) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$nguoi_dung = $auth->layThongTinNguoiDung();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create_question':
        $id_bai_hoc = intval($_POST['id_bai_hoc'] ?? 0);
        $tieu_de = trim($_POST['tieu_de'] ?? '');
        $noi_dung = trim($_POST['noi_dung'] ?? '');

        if ($id_bai_hoc <= 0 || empty($tieu_de) || empty($noi_dung)) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        $id_cau_hoi = $qd_model->taoCauHoi($nguoi_dung['id'], $id_bai_hoc, $tieu_de, $noi_dung);
        if ($id_cau_hoi) {
            echo json_encode(['success' => true, 'message' => 'Câu hỏi đã được gửi!', 'id' => $id_cau_hoi]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gửi câu hỏi thất bại']);
        }
        break;

    case 'create_answer':
        $id_cau_hoi = intval($_POST['id_cau_hoi'] ?? 0);
        $noi_dung = trim($_POST['noi_dung'] ?? '');

        if ($id_cau_hoi <= 0 || empty($noi_dung)) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        $id_tra_loi = $qd_model->themTraLoi($id_cau_hoi, $nguoi_dung['id'], $noi_dung);
        if ($id_tra_loi) {
            // Gửi thông báo cho người đặt câu hỏi
            $id_nguoi_hoi = $qd_model->layIdNguoiHoi($id_cau_hoi);
            $cau_hoi = $qd_model->layTheoId($id_cau_hoi);
            if ($id_nguoi_hoi && $id_nguoi_hoi != $nguoi_dung['id']) {
                $tb_model->guiThongBao(
                    $id_nguoi_hoi,
                    'Câu hỏi của bạn đã được trả lời',
                    $nguoi_dung['ho_ten'] . ' đã trả lời câu hỏi: "' . ($cau_hoi['tieu_de'] ?? '') . '"',
                    'cau_tra_loi',
                    VIEWS_URL . '/hoi-dap/chi-tiet.php?id=' . $id_cau_hoi
                );
            }
            // Kiểm tra badges Q&A
            require_once __DIR__ . '/../models/huy_hieu.php';
            $hh_model = new HuyHieu();
            $badges_moi = $hh_model->kiemTraVaTrao($nguoi_dung['id']);
            foreach ($badges_moi as $badge) {
                $tb_model->guiThongBao(
                    $nguoi_dung['id'],
                    'Bạn nhận được huy hiệu mới!',
                    'Chúc mừng bạn đã đạt được huy hiệu "' . $badge['ten'] . '"!',
                    'he_thong',
                    VIEWS_URL . '/huy-hieu/index.php'
                );
            }
            echo json_encode(['success' => true, 'message' => 'Câu trả lời đã được gửi!', 'id' => $id_tra_loi]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gửi câu trả lời thất bại']);
        }
        break;

    case 'like':
        $id = intval($_GET['id'] ?? 0);
        $type = $_GET['type'] ?? '';

        if ($id <= 0 || !in_array($type, ['question', 'answer'])) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        if ($type === 'question') {
            if ($qd_model->daThichCauHoi($nguoi_dung['id'], $id)) {
                $qd_model->boThichCauHoi($nguoi_dung['id'], $id);
                echo json_encode(['success' => true, 'liked' => false]);
            } else {
                $qd_model->thichCauHoi($nguoi_dung['id'], $id);
                echo json_encode(['success' => true, 'liked' => true]);
            }
        } else {
            if ($qd_model->daThichTraLoi($nguoi_dung['id'], $id)) {
                $qd_model->boThichTraLoi($nguoi_dung['id'], $id);
                echo json_encode(['success' => true, 'liked' => false]);
            } else {
                $qd_model->thichTraLoi($nguoi_dung['id'], $id);
                echo json_encode(['success' => true, 'liked' => true]);
            }
        }
        break;

    case 'mark_best_answer':
        $id_cau_hoi = intval($_POST['id_cau_hoi'] ?? 0);
        $id_tra_loi = intval($_POST['id_tra_loi'] ?? 0);

        // Chỉ người đặt câu hỏi mới được đánh dấu
        $cau_hoi = $qd_model->layTheoId($id_cau_hoi);
        if (!$cau_hoi || $cau_hoi['id_nguoi_hoi'] != $nguoi_dung['id']) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền']);
            exit;
        }

        $qd_model->danhDauTraLoiTotNhat($id_cau_hoi, $id_tra_loi);
        echo json_encode(['success' => true, 'message' => 'Đã đánh dấu câu trả lời hay nhất']);
        break;

    case 'delete_question':
        $id = intval($_GET['id'] ?? 0);
        $cau_hoi = $qd_model->layTheoId($id);
        if (!$cau_hoi || $cau_hoi['id_nguoi_hoi'] != $nguoi_dung['id']) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền xóa']);
            exit;
        }
        $qd_model->xoaCauHoi($id);
        echo json_encode(['success' => true, 'message' => 'Đã xóa câu hỏi']);
        break;

    case 'delete_answer':
        $id = intval($_GET['id'] ?? 0);
        $stmt = $GLOBALS['___SQL___'] ?? null;
        echo json_encode(['success' => false, 'message' => 'Chức năng đang phát triển']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
        break;
}
?>
