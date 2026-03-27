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
    header('Location: ' . BASE_PATH . '/khoa-hoc/index.php');
    exit;
}

$bai_hoc = $bai_hoc_model->layTheoId($id);

if (!$bai_hoc) {
    header('Location: ' . BASE_PATH . '/khoa-hoc/index.php');
    exit;
}

$khoa_hoc = $khoa_hoc_model->layTheoId($bai_hoc['id_khoa_hoc']);
$bai_hoc_list = $bai_hoc_model->layTheoKhoaHoc($bai_hoc['id_khoa_hoc']);

// Tìm bài học trước và sau
$current_index = array_search($id, array_column($bai_hoc_list, 'id'));
$prev_lesson = ($current_index > 0) ? $bai_hoc_list[$current_index - 1] : null;
$next_lesson = ($current_index < count($bai_hoc_list) - 1) ? $bai_hoc_list[$current_index + 1] : null;

include __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo HOME_URL; ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_PATH; ?>/khoa-hoc/index.php">Khóa học</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_PATH; ?>/khoa-hoc/chi-tiet.php?id=<?php echo $khoa_hoc['id']; ?>"><?php echo $khoa_hoc['ten_khoa_hoc']; ?></a></li>
            <li class="breadcrumb-item active"><?php echo $bai_hoc['tieu_de']; ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Nội dung bài học -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-book-open me-2"></i><?php echo $bai_hoc['tieu_de']; ?></h4>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-clock me-1"></i><?php echo $bai_hoc['thoi_luong_phut']; ?> phút
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Video nếu có -->
                    <?php if (!empty($bai_hoc['video_url'])): ?>
                        <div class="mb-4">
                            <div class="ratio ratio-16x9">
                                <iframe src="<?php echo $bai_hoc['video_url']; ?>" 
                                        title="Video bài học" allowfullscreen></iframe>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Nội dung bài học -->
                    <div class="lesson-content">
                        <?php echo nl2br($bai_hoc['noi_dung'] ?? 'Chưa có nội dung bài học.'); ?>
                    </div>

                    <!-- Nút điều hướng -->
                    <div class="d-flex justify-content-between mt-4 pt-4 border-top">
                        <?php if ($prev_lesson): ?>
                            <a href="chi-tiet.php?id=<?php echo $prev_lesson['id']; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-chevron-left me-2"></i><?php echo $prev_lesson['tieu_de']; ?>
                            </a>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>
                        
                        <?php if ($next_lesson): ?>
                            <a href="chi-tiet.php?id=<?php echo $next_lesson['id']; ?>" class="btn btn-primary">
                                <?php echo $next_lesson['tieu_de']; ?><i class="fas fa-chevron-right ms-2"></i>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_PATH; ?>/khoa-hoc/chi-tiet.php?id=<?php echo $khoa_hoc['id']; ?>" class="btn btn-success">
                                <i class="fas fa-check-circle me-2"></i>Hoàn thành khóa học
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quiz của bài học -->
            <?php 
            require_once __DIR__ . '/../../models/quiz.php';
            $quiz_model = new Quiz();
            $quiz_list = $quiz_model->layTheoBaiHoc($id);
            ?>
            
            <?php if (!empty($quiz_list)): ?>
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>Bài Quiz kiểm tra</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($quiz_list as $quiz): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                                <div>
                                    <h6 class="mb-1"><?php echo $quiz['tieu_de']; ?></h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i><?php echo $quiz['thoi_gian_phut']; ?> phút
                                        <i class="fas fa-star ms-2 me-1"></i><?php echo $quiz['diem_toi_da']; ?> điểm
                                    </small>
                                </div>
                                <?php if ($auth->kiemTraDangNhap()): ?>
                                    <a href="<?php echo BASE_PATH; ?>/quiz/lam-bai.php?id=<?php echo $quiz['id']; ?>" 
                                       class="btn btn-success">
                                        <i class="fas fa-play me-2"></i>Làm bài
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo BASE_PATH; ?>/tai-khoan/dang-nhap.php" 
                                       class="btn btn-outline-success">
                                        <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập để làm
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar - Danh sách bài học -->
        <div class="col-lg-4">
            <div class="card shadow sticky-top" style="top: 20px;">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Danh sách bài học
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($bai_hoc_list as $index => $bh): ?>
                            <a href="chi-tiet.php?id=<?php echo $bh['id']; ?>" 
                               class="list-group-item list-group-item-action <?php echo ($bh['id'] == $id) ? 'active' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-secondary me-2"><?php echo $index + 1; ?></span>
                                        <strong><?php echo $bh['tieu_de']; ?></strong>
                                    </div>
                                    <small>
                                        <i class="fas fa-clock me-1"></i><?php echo $bh['thoi_luong_phut']; ?>p
                                    </small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?php echo BASE_PATH; ?>/khoa-hoc/chi-tiet.php?id=<?php echo $khoa_hoc['id']; ?>" 
                       class="btn btn-outline-secondary w-100 btn-sm">
                        <i class="fas fa-arrow-left me-2"></i>Quay về khóa học
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
