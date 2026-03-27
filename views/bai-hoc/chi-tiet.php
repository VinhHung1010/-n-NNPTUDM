<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/bai_hoc.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';

$page_title = 'Chi tiết Bài học - ' . SITE_NAME;
$auth = new Auth();
$bai_hoc_model = new BaiHoc();
$khoa_hoc_model = new KhoaHoc();

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
                        <div>
                            <i class="fas fa-book-open me-2" style="color:var(--primary)"></i>
                            <?php echo htmlspecialchars($bai_hoc['tieu_de']); ?>
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

                    <!-- Nút điều hướng -->
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
                            <?php $hien_tai = (int)$bh['id'] === (int)$id; ?>
                            <a href="chi-tiet.php?id=<?php echo $bh['id']; ?>"
                               class="list-group-item list-group-item-action <?php echo $hien_tai ? 'active' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-circle <?php echo $hien_tai ? 'bg-light text-dark' : 'bg-secondary'; ?>"
                                              style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700">
                                            <?php echo $i + 1; ?>
                                        </span>
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
