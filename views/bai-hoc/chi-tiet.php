<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/bai_hoc.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';
require_once __DIR__ . '/../../models/tien_do.php';
require_once __DIR__ . '/../../models/chung_chi.php';
require_once __DIR__ . '/../../models/thong_bao.php';

$page_title = 'Chi tiết Bài học - ' . SITE_NAME;
$auth = new Auth();
$bai_hoc_model = new BaiHoc();
$khoa_hoc_model = new KhoaHoc();
$td_model = new TienDo();
$cc_model = new ChungChi();
$thong_bao_model = new ThongBao();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . VIEWS_URL . '/khoa-hoc/index.php');
    exit;
}

$bai_hoc = $bai_hoc_model->layTheoId($id);

if (!$bai_hoc) {
    header('Location: ' . VIEWS_URL . '/khoa-hoc/index.php');
    exit;
}

$khoa_hoc = $khoa_hoc_model->layTheoId($bai_hoc['id_khoa_hoc']);
$bai_hoc_list = $bai_hoc_model->layTheoKhoaHoc($bai_hoc['id_khoa_hoc']);
$tong_thoi_luong = $bai_hoc_model->tinhTongThoiLuong($bai_hoc['id_khoa_hoc']);

// Kiểm tra đăng ký
$nguoi_dung = $auth->layThongTinNguoiDung();
$trang_thai_dk = $nguoi_dung
    ? $khoa_hoc_model->daDangKy($nguoi_dung['id'], $bai_hoc['id_khoa_hoc'])
    : null;
$da_xac_nhan = $trang_thai_dk === 'da_xac_nhan';

// Tiến độ học tập
$tien_do_bai = false; // bài hiện tại đã hoàn thành chưa
$tien_do_map = [];    // [bai_hoc_id => bool]
$phan_tram = 0;
if ($da_xac_nhan && $nguoi_dung) {
    $tien_do_map = $td_model->layTienDoKhoaHoc($nguoi_dung['id'], $bai_hoc['id_khoa_hoc']);
    $tien_do_bai  = isset($tien_do_map[$id]) && $tien_do_map[$id];
    $phan_tram    = $td_model->tinhPhanTram($nguoi_dung['id'], $bai_hoc['id_khoa_hoc']);
    $da_hoan_thanh_kh = $td_model->daHoanThanhKhoaHoc($nguoi_dung['id'], $bai_hoc['id_khoa_hoc']);
}

// Xử lý đánh dấu hoàn thành / hủy
$thong_bao_td = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_hoan_thanh']) && $da_xac_nhan && $nguoi_dung) {
    if ($tien_do_bai) {
        $td_model->huyHoanThanh($nguoi_dung['id'], $id);
        $thong_bao_td = '<div class="alert alert-secondary py-2">Đã hủy hoàn thành bài học.</div>';
    } else {
        $td_model->danhDauHoanThanh($nguoi_dung['id'], $id);
        $thong_bao_td = '<div class="alert alert-success py-2">Bài học đã được đánh dấu hoàn thành!</div>';
    }
    // Reload tiến độ
    $tien_do_bai  = !$tien_do_bai;
    $phan_tram    = $td_model->tinhPhanTram($nguoi_dung['id'], $bai_hoc['id_khoa_hoc']);
    $da_hoan_thanh_kh = $td_model->daHoanThanhKhoaHoc($nguoi_dung['id'], $bai_hoc['id_khoa_hoc']);
    $tien_do_map  = $td_model->layTienDoKhoaHoc($nguoi_dung['id'], $bai_hoc['id_khoa_hoc']);

    // Nếu hoàn thành 100% → tạo chứng chỉ + gửi thông báo
    if ($da_hoan_thanh_kh) {
        $chung_chi = $cc_model->tao($nguoi_dung['id'], $bai_hoc['id_khoa_hoc']);
        // Gửi thông báo hoàn thành khóa học
        $thong_bao_model->guiThongBao(
            $nguoi_dung['id'],
            'Bạn đã hoàn thành khóa học!',
            'Chúc mừng bạn đã hoàn thành khóa học "' . $khoa_hoc['ten_khoa_hoc'] . '" và nhận được chứng chỉ!',
            'hoan_thanh_khoa',
            VIEWS_URL . '/chung-chi/xem.php'
        );
        // Gửi thông báo chứng chỉ
        if ($chung_chi) {
            $thong_bao_model->guiThongBao(
                $nguoi_dung['id'],
                'Chứng chỉ mới đã được cấp!',
                'Bạn đã nhận được chứng chỉ hoàn thành khóa học "' . $khoa_hoc['ten_khoa_hoc'] . '". Mã: ' . $chung_chi['ma_chung_chi'],
                'chung_chi',
                VIEWS_URL . '/chung-chi/xem.php?ma=' . urlencode($chung_chi['ma_chung_chi'])
            );
        }
    }
}

// Kiểm tra đã có chứng chỉ chưa
$chung_chi = null;
if ($da_xac_nhan && $nguoi_dung) {
    $chung_chi = $cc_model->lay($nguoi_dung['id'], $bai_hoc['id_khoa_hoc']);
}

// Tìm bài học trước và sau
$current_index = array_search($id, array_column($bai_hoc_list, 'id'));
$prev_lesson = ($current_index > 0) ? $bai_hoc_list[$current_index - 1] : null;
$next_lesson = ($current_index < count($bai_hoc_list) - 1) ? $bai_hoc_list[$current_index + 1] : null;

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container mt-4">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php">Khóa học</a></li>
            <li class="breadcrumb-item"><a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo $khoa_hoc['id']; ?>"><?php echo htmlspecialchars($khoa_hoc['ten_khoa_hoc']); ?></a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($bai_hoc['tieu_de']); ?></li>
        </ol>
    </nav>

    <?php if (!$da_xac_nhan && $nguoi_dung): ?>
        <div class="alert alert-warning mb-3">
            <i class="fas fa-lock me-1"></i>
            Bạn chưa được xác nhận đăng ký khóa học này.
            <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo $khoa_hoc['id']; ?>" class="alert-link">
                Xem chi tiết khóa học
            </a>
        </div>
    <?php endif; ?>

    <?php if ($thong_bao_td) echo $thong_bao_td; ?>

    <!-- Thông báo hoàn thành khóa học + nhận chứng chỉ -->
    <?php if ($da_xac_nhan && isset($da_hoan_thanh_kh) && $da_hoan_thanh_kh): ?>
        <div class="alert alert-success d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-trophy fa-lg"></i>
                <div>
                    <strong>Chúc mừng!</strong> Bạn đã hoàn thành toàn bộ khóa học
                    <strong><?php echo htmlspecialchars($khoa_hoc['ten_khoa_hoc']); ?></strong>!
                </div>
            </div>
            <?php if ($chung_chi): ?>
                <a href="<?php echo VIEWS_URL; ?>/chung-chi/xem.php?ma=<?php echo urlencode($chung_chi['ma_chung_chi']); ?>"
                   class="btn btn-warning text-dark fw-semibold btn-sm">
                    <i class="fas fa-award me-1"></i>Xem chứng chỉ
                </a>
            <?php else: ?>
                <span class="badge bg-warning text-dark">
                    <i class="fas fa-award me-1"></i>Chứng chỉ đã được cấp
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- Nội dung bài học -->
        <div class="col-lg-8">

            <!-- Video -->
            <?php if (!empty($bai_hoc['video_url'])): ?>
                <div class="card mb-4 overflow-hidden" style="border-radius:16px">
                    <div class="card-header bg-white fw-bold">
                        <i class="fas fa-video me-2" style="color:var(--primary)"></i>Video bài học
                    </div>
                    <div class="card-body p-0">
                        <div class="ratio ratio-16x9">
                            <iframe src="<?php echo $bai_hoc['video_url']; ?>"
                                    title="Video bài học" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Nội dung -->
            <div class="card mb-4" style="border-radius:16px">
                <div class="card-header bg-white fw-bold py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-book-open me-2" style="color:var(--primary)"></i>
                            <?php echo htmlspecialchars($bai_hoc['tieu_de']); ?>
                            <?php if ($da_xac_nhan): ?>
                                <?php if ($tien_do_bai): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Đã hoàn thành
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-spinner me-1"></i>Chưa hoàn thành
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <span class="badge bg-light text-muted py-2 px-3">
                            <i class="fas fa-clock me-1"></i><?php echo $bai_hoc['thoi_luong_phut']; ?> phút
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="lesson-content" style="white-space:pre-line;font-size:1.05rem;line-height:1.8">
                        <?php echo nl2br(htmlspecialchars($bai_hoc['noi_dung'] ?? 'Chưa có nội dung bài học.')); ?>
                    </div>

                    <!-- Nút hoàn thành bài học -->
                    <?php if ($da_xac_nhan): ?>
                        <div class="mt-4 pt-4 border-top">
                            <form method="POST">
                                <?php if ($tien_do_bai): ?>
                                    <button type="submit" name="toggle_hoan_thanh" class="btn btn-outline-secondary">
                                        <i class="fas fa-rotate-left me-1"></i>Hủy hoàn thành bài học này
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="toggle_hoan_thanh" class="btn btn-success">
                                        <i class="fas fa-check-circle me-1"></i>Hoàn thành bài học này
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- Nút điều hướng bài học -->
                    <div class="d-flex justify-content-between mt-4 pt-4 border-top flex-wrap gap-2">
                        <?php if ($prev_lesson): ?>
                            <a href="chi-tiet.php?id=<?php echo $prev_lesson['id']; ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-chevron-left me-1"></i><?php echo htmlspecialchars($prev_lesson['tieu_de']); ?>
                            </a>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>

                        <?php if ($next_lesson): ?>
                            <a href="chi-tiet.php?id=<?php echo $next_lesson['id']; ?>" class="btn btn-primary">
                                <?php echo htmlspecialchars($next_lesson['tieu_de']); ?><i class="fas fa-chevron-right ms-1"></i>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo $khoa_hoc['id']; ?>" class="btn btn-success">
                                <i class="fas fa-check-circle me-1"></i>Hoàn thành khóa học
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quiz -->
            <?php
require_once __DIR__ . '/../../models/quiz.php';
require_once __DIR__ . '/../../models/hoi_dap.php';
$quiz_model = new Quiz();
$qd_model = new HoiDap();
$quiz_list = $quiz_model->layTheoBaiHoc($id);

// Load Q&A
$cau_hoi_list = $qd_model->layCauHoiTheoBaiHoc($id, 10);
?>

            <?php if (!empty($quiz_list)): ?>
                <div class="card" style="border-radius:16px">
                    <div class="card-header bg-success text-white fw-bold py-3">
                        <i class="fas fa-circle-question me-2"></i>Bài Quiz kiểm tra
                    </div>
                    <div class="card-body p-0">
                        <?php foreach ($quiz_list as $quiz): ?>
                            <div class="d-flex justify-content-between align-items-center p-3 <?php echo next($quiz_list) ? 'border-bottom' : ''; ?>">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($quiz['tieu_de']); ?></h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i><?php echo $quiz['thoi_gian_phut']; ?> phút ·
                                        <i class="fas fa-star me-1"></i><?php echo $quiz['diem_toi_da']; ?> điểm
                                    </small>
                                </div>
                                <?php if ($da_xac_nhan): ?>
                                    <a href="<?php echo VIEWS_URL; ?>/quiz/lam-bai.php?id=<?php echo $quiz['id']; ?>"
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-play me-1"></i>Làm bài
                                    </a>
                                <?php elseif ($nguoi_dung): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-lock me-1"></i>Chưa được duyệt
                                    </span>
                                <?php else: ?>
                                    <a href="<?php echo VIEWS_URL; ?>/tai-khoan/dang-nhap.php"
                                       class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ══ Q&A / Thảo luận ══ -->
            <div class="card mt-3" style="border-radius:16px">
                <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-comments me-2" style="color:var(--accent)"></i>
                        Hỏi đáp & Thảo luận
                        <?php if (count($cau_hoi_list) > 0): ?>
                            <span class="badge bg-secondary ms-1"><?php echo count($cau_hoi_list); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($nguoi_dung): ?>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAskQuestion">
                            <i class="fas fa-plus me-1"></i>Đặt câu hỏi
                        </button>
                    <?php endif; ?>
                </div>

                <?php if (empty($cau_hoi_list)): ?>
                    <div class="card-body text-center py-4">
                        <i class="fas fa-comment-dots fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-2">Chưa có câu hỏi nào cho bài học này.</p>
                        <?php if ($nguoi_dung): ?>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAskQuestion">
                                <i class="fas fa-question me-1"></i>Hãy là người đầu tiên đặt câu hỏi
                            </button>
                        <?php else: ?>
                            <a href="<?php echo VIEWS_URL; ?>/tai-khoan/dang-nhap.php" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập để đặt câu hỏi
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="card-body p-0">
                        <?php foreach ($cau_hoi_list as $ch): ?>
                            <?php
                            $tra_loi_list = $qd_model->layTraLoi($ch['id']);
                            $da_thich = $nguoi_dung ? $qd_model->daThichCauHoi($nguoi_dung['id'], $ch['id']) : false;
                            $role_color = $ch['vai_tro'] === 'quan_tri' ? 'danger' : ($ch['vai_tro'] === 'giao_vien' ? 'success' : 'primary');
                            ?>
                            <div class="qa-item border-bottom p-3" id="qa-<?php echo $ch['id']; ?>">
                                <div class="d-flex gap-2">
                                    <div class="qa-avatar flex-shrink-0">
                                        <div style="width:38px;height:38px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.9rem">
                                            <?php echo mb_substr($ch['ho_ten'], 0, 1, 'UTF-8'); ?>
                                        </div>
                                    </div>
                                    <div class="qa-content flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
                                            <div>
                                                <strong class="small"><?php echo htmlspecialchars($ch['ho_ten']); ?></strong>
                                                <span class="badge bg-<?php echo $role_color; ?> ms-1" style="font-size:0.6rem"><?php echo $ch['vai_tro'] === 'giao_vien' ? 'GV' : ($ch['vai_tro'] === 'quan_tri' ? 'Admin' : 'HV'); ?></span>
                                                <span class="text-muted ms-1" style="font-size:0.72rem"><?php echo $this->getTimeAgo($ch['ngay_tao']); ?></span>
                                            </div>
                                            <?php if ($ch['trang_thai'] === 'da_tra_loi'): ?>
                                                <span class="badge bg-success" style="font-size:0.65rem">
                                                    <i class="fas fa-check-circle me-1"></i>Đã trả lời
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark" style="font-size:0.65rem">
                                                    <i class="fas fa-clock me-1"></i>Chờ trả lời
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="#qa-<?php echo $ch['id']; ?>" class="qa-title d-block fw-semibold mt-1 mb-1" style="font-size:0.92rem;color:var(--secondary);text-decoration:none">
                                            <?php echo htmlspecialchars($ch['tieu_de']); ?>
                                        </a>
                                        <p class="qa-text mb-2" style="font-size:0.85rem;color:var(--muted);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                                            <?php echo htmlspecialchars($ch['noi_dung']); ?>
                                        </p>
                                        <div class="d-flex gap-3 align-items-center flex-wrap">
                                            <button class="qa-like-btn btn btn-sm <?php echo $da_thich ? 'btn-primary' : 'btn-outline-secondary'; ?>" 
                                                    data-id="<?php echo $ch['id']; ?>" data-type="question"
                                                    style="font-size:0.75rem;padding:2px 10px">
                                                <i class="fas fa-thumbs-up me-1"></i>
                                                Thích <span class="like-count"><?php echo $ch['luot_thich']; ?></span>
                                            </button>
                                            <span class="text-muted" style="font-size:0.75rem">
                                                <i class="fas fa-comment me-1"></i><?php echo count($tra_loi_list); ?> câu trả lời
                                            </span>
                                            <span class="text-muted" style="font-size:0.75rem">
                                                <i class="fas fa-eye me-1"></i><?php echo $ch['luot_xem']; ?> lượt xem
                                            </span>
                                            <a href="<?php echo VIEWS_URL; ?>/hoi-dap/chi-tiet.php?id=<?php echo $ch['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary ms-auto" style="font-size:0.75rem;padding:2px 10px">
                                                <i class="fas fa-reply me-1"></i>Xem & Trả lời
                                            </a>
                                        </div>

                                        <?php if (!empty($tra_loi_list)): ?>
                                            <div class="qa-answers mt-2 ps-3 border-start border-2" style="border-color:var(--primary) !important">
                                                <?php foreach (array_slice($tra_loi_list, 0, 2) as $tl): ?>
                                                    <?php
                                                    $da_thich_tl = $nguoi_dung ? $qd_model->daThichTraLoi($nguoi_dung['id'], $tl['id']) : false;
                                                    $role_color2 = $tl['vai_tro'] === 'quan_tri' ? 'danger' : ($tl['vai_tro'] === 'giao_vien' ? 'success' : 'primary');
                                                    ?>
                                                    <div class="answer-item py-2 <?php echo $tl['la_cau_tra_tot_nhat'] ? 'bg-success-soft' : ''; ?>">
                                                        <div class="d-flex gap-2">
                                                            <div style="width:28px;height:28px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.7rem;flex-shrink:0">
                                                                <?php echo mb_substr($tl['ho_ten'], 0, 1, 'UTF-8'); ?>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                                                    <strong class="small"><?php echo htmlspecialchars($tl['ho_ten']); ?></strong>
                                                                    <span class="badge bg-<?php echo $role_color2; ?>" style="font-size:0.55rem;padding:1px 5px"><?php echo $tl['vai_tro'] === 'giao_vien' ? 'GV' : ($tl['vai_tro'] === 'quan_tri' ? 'Admin' : 'HV'); ?></span>
                                                                    <?php if ($tl['la_cau_tra_tot_nhat']): ?>
                                                                        <span class="badge bg-warning text-dark" style="font-size:0.55rem;padding:1px 5px">
                                                                            <i class="fas fa-star me-1"></i>Hay nhất
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <p class="mb-1" style="font-size:0.82rem"><?php echo htmlspecialchars($tl['noi_dung']); ?></p>
                                                                <button class="qa-like-btn btn btn-sm <?php echo $da_thich_tl ? 'btn-primary' : 'btn-outline-secondary'; ?> <?php echo $tl['la_cau_tra_tot_nhat'] ? 'bg-warning text-dark border-warning' : ''; ?>" 
                                                                        data-id="<?php echo $tl['id']; ?>" data-type="answer"
                                                                        style="font-size:0.7rem;padding:1px 8px">
                                                                    <i class="fas fa-thumbs-up me-1"></i><?php echo $tl['so_thich']; ?>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php if (count($tra_loi_list) > 2): ?>
                                                    <a href="<?php echo VIEWS_URL; ?>/hoi-dap/chi-tiet.php?id=<?php echo $ch['id']; ?>" 
                                                       class="small text-primary d-block py-1">
                                                        Xem tất cả <?php echo count($tra_loi_list); ?> câu trả lời →
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($cau_hoi_list) >= 10): ?>
                            <div class="text-center py-3 border-top">
                                <a href="<?php echo VIEWS_URL; ?>/hoi-dap/index.php?bai_hoc=<?php echo $id; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-list me-1"></i>Xem tất cả câu hỏi
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">

            <!-- Tiến độ khóa học -->
            <?php if ($da_xac_nhan): ?>
                <div class="card mb-3" style="border-radius:16px">
                    <div class="card-header bg-white fw-bold py-3">
                        <i class="fas fa-chart-line me-2" style="color:var(--primary)"></i>
                        Tiến độ học tập
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-semibold"><?php echo $phan_tram; ?>% hoàn thành</span>
                            <span class="small text-muted">
                                <?php echo count(array_filter($tien_do_map)); ?> / <?php echo count($bai_hoc_list); ?> bài
                            </span>
                        </div>
                        <div class="progress mb-3" style="height:10px;border-radius:99px">
                            <?php
                            $bar_color = $phan_tram >= 100 ? 'bg-success' : ($phan_tram >= 50 ? 'bg-info' : 'bg-primary');
                            ?>
                            <div class="progress-bar <?php echo $bar_color; ?>"
                                 style="width:<?php echo $phan_tram; ?>%;border-radius:99px;transition:width .5s">
                            </div>
                        </div>
                        <?php if ($phan_tram >= 100): ?>
                            <div class="alert alert-success py-2 small mb-0">
                                <i class="fas fa-trophy me-1"></i>Hoàn thành 100% — Nhận chứng chỉ ngay!
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Thông tin khóa học -->
            <div class="card mb-3" style="border-radius:16px">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">
                        <i class="fas fa-book me-1" style="color:var(--primary)"></i>
                        <?php echo htmlspecialchars($khoa_hoc['ten_khoa_hoc']); ?>
                    </h6>
                    <div class="small text-muted mb-2">
                        <i class="fas fa-user-tie me-1"></i>
                        <?php echo htmlspecialchars($khoa_hoc['ten_giao_vien'] ?? 'N/A'); ?>
                    </div>
                    <div class="small text-muted mb-2">
                        <i class="fas fa-file-lines me-1"></i>
                        <?php echo count($bai_hoc_list); ?> bài học ·
                        <?php echo $tong_thoi_luong; ?> phút
                    </div>
                    <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo $khoa_hoc['id']; ?>"
                       class="btn btn-outline-secondary btn-sm w-100 mt-2">
                        <i class="fas fa-arrow-left me-1"></i>Quay về khóa học
                    </a>
                </div>
            </div>

            <!-- Danh sách bài học -->
            <div class="card" style="border-radius:16px">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="fas fa-list me-2" style="color:var(--primary)"></i>
                    Danh sách bài học
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($bai_hoc_list as $i => $bh): ?>
                            <?php
                            $hien_tai = (int)$bh['id'] === (int)$id;
                            $da_hoan  = isset($tien_do_map[$bh['id']]) && $tien_do_map[$bh['id']];
                            ?>
                            <a href="chi-tiet.php?id=<?php echo $bh['id']; ?>"
                               class="list-group-item list-group-item-action <?php echo $hien_tai ? 'active' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($da_hoan): ?>
                                            <span class="badge bg-success rounded-circle"
                                                  style="width:22px;height:22px;display:flex;align-items:center;justify-content:center;font-size:0.65rem">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge <?php echo $hien_tai ? 'bg-light text-dark' : 'bg-secondary'; ?> rounded-circle"
                                                  style="width:22px;height:22px;display:flex;align-items:center;justify-content:center;font-size:0.65rem;font-weight:700">
                                                <?php echo $i + 1; ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="small fw-semibold"><?php echo htmlspecialchars($bh['tieu_de']); ?></span>
                                    </div>
                                    <small class="<?php echo $hien_tai ? 'text-dark' : 'text-muted'; ?>">
                                        <i class="fas fa-clock me-1"></i><?php echo $bh['thoi_luong_phut']; ?>p
                                    </small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>

<!-- ══ Modal Đặt câu hỏi ══ -->
<?php if ($nguoi_dung): ?>
<div class="modal fade" id="modalAskQuestion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-question-circle me-2 text-primary"></i>Đặt câu hỏi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAskQuestion">
                <div class="modal-body">
                    <input type="hidden" name="id_bai_hoc" value="<?php echo $id; ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tiêu đề câu hỏi <span class="text-danger">*</span></label>
                        <input type="text" name="tieu_de" class="form-control" 
                               placeholder="Ví dụ: Cách sử dụng flexbox trong CSS?" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nội dung chi tiết <span class="text-danger">*</span></label>
                        <textarea name="noi_dung" class="form-control" rows="4"
                                  placeholder="Mô tả chi tiết câu hỏi của bạn..." required></textarea>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Câu hỏi của bạn sẽ được hiển thị trong phần Hỏi đáp của bài học này. 
                        Giáo viên và học viên khác có thể trả lời.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitQuestion">
                        <i class="fas fa-paper-plane me-1"></i>Gửi câu hỏi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const SITE_URL = '<?php echo SITE_URL; ?>';

    // Submit question
    const formAsk = document.getElementById('formAskQuestion');
    if (formAsk) {
        formAsk.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(formAsk);
            const btn = document.getElementById('btnSubmitQuestion');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang gửi...';

            fetch(SITE_URL + '/api/hoi-dap.php?action=create_question', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalAskQuestion')).hide();
                    formAsk.reset();
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            })
            .catch(err => { alert('Có lỗi: ' + err); })
            .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Gửi câu hỏi'; });
        });
    }

    // Like buttons
    document.querySelectorAll('.qa-like-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const type = this.dataset.type;
            fetch(SITE_URL + '/api/hoi-dap.php?action=like&id=' + id + '&type=' + type)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (type === 'question') {
                            const countSpan = this.querySelector('.like-count');
                            if (data.liked) {
                                this.classList.remove('btn-outline-secondary');
                                this.classList.add('btn-primary');
                                countSpan.textContent = parseInt(countSpan.textContent) + 1;
                            } else {
                                this.classList.remove('btn-primary');
                                this.classList.add('btn-outline-secondary');
                                countSpan.textContent = Math.max(0, parseInt(countSpan.textContent) - 1);
                            }
                        } else {
                            const current = parseInt(this.textContent.trim().split(' ')[1]) || 0;
                            if (data.liked) {
                                this.classList.remove('btn-outline-secondary');
                                this.classList.add('btn-primary');
                                this.innerHTML = '<i class="fas fa-thumbs-up me-1"></i>' + (current + 1);
                            } else {
                                this.classList.remove('btn-primary');
                                this.classList.add('btn-outline-secondary');
                                this.innerHTML = '<i class="fas fa-thumbs-up me-1"></i>' + Math.max(0, current - 1);
                            }
                        }
                    }
                })
                .catch(console.error);
        });
    });
});
</script>
