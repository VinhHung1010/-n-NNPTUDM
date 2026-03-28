<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/auth.php';
require_once __DIR__ . '/../models/huy_hieu.php';
require_once __DIR__ . '/../models/thong_bao.php';

$auth = new Auth();
$hh_model = new HuyHieu();
$tb_model = new ThongBao();

if (!$auth->kiemTraDangNhap()) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$nguoi_dung = $auth->layThongTinNguoiDung();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'check_and_award':
        // Kiểm tra và trao badges tự động
        $badges_trao = $hh_model->kiemTraVaTrao($nguoi_dung['id']);
        
        // Gửi thông báo cho mỗi badge mới
        foreach ($badges_trao as $badge) {
            $tb_model->guiThongBao(
                $nguoi_dung['id'],
                'Bạn nhận được huy hiệu mới!',
                'Chúc mừng bạn đã đạt được huy hiệu "' . $badge['ten'] . '"! ' . ($badge['mo_ta'] ?? ''),
                'he_thong',
                VIEWS_URL . '/huy-hieu/index.php'
            );
        }
        
        echo json_encode([
            'success' => true,
            'badges_awarded' => count($badges_trao),
            'badges' => $badges_trao
        ]);
        break;

    case 'get_my_badges':
        $badges = $hh_model->layCuaNguoiDung($nguoi_dung['id']);
        $so_badges = $hh_model->demCuaNguoiDung($nguoi_dung['id']);
        $tong_badges = count($hh_model->layTatCa());
        
        echo json_encode([
            'success' => true,
            'badges' => $badges,
            'count' => $so_badges,
            'total' => $tong_badges
        ]);
        break;

    case 'get_leaderboard':
        $leaderboard = $hh_model->layLeaderboard(20);
        echo json_encode([
            'success' => true,
            'leaderboard' => $leaderboard
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
        break;
}
?>
