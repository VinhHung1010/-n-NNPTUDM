<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/bai_hoc.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';
require_once __DIR__ . '/../../models/tien_do.php';

$page_title = 'Chi tiết Bài học - ' . SITE_NAME;
$auth = new Auth();
$bai_hoc_model = new BaiHoc();
$khoa_hoc_model = new KhoaHoc();
$td_model = new TienDo();

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

    <!-- Thông báo hoàn thành khóa học -->
    <?php if ($da_xac_nhan && isset($da_hoan_thanh_kh) && $da_hoan_thanh_kh): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
            <i class="fas fa-trophy fa-lg"></i>
            <div>
                <strong>Chúc mừng!</strong> Bạn đã hoàn thành toàn bộ khóa học
                <strong><?php echo htmlspecialchars($khoa_hoc['ten_khoa_hoc']); ?></strong>!
            </div>
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
            $quiz_model = new Quiz();
            $quiz_list = $quiz_model->layTheoBaiHoc($id);
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
