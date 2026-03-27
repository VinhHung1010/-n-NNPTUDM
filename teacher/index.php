<?php
$page_title = 'Tổng quan - Giáo viên';
require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/models/khoa_hoc.php';
require_once dirname(__DIR__) . '/models/bai_hoc.php';
require_once dirname(__DIR__) . '/models/quiz.php';

$kh_model   = new KhoaHoc();
$bh_model   = new BaiHoc();
$quiz_model = new Quiz();

$khoa_gv    = $kh_model->layKhoaHocCuaGiaoVien($nguoi_dung['id']);

// Thống kê
$so_kh      = count($khoa_gv);
$so_bai     = 0;
$so_hv      = 0;
$so_quiz    = 0;
foreach ($khoa_gv as $k) {
    $so_bai += (int)($k['so_bai_hoc'] ?? 0);
    $so_hv  += (int)($k['so_hoc_vien'] ?? 0);
}
foreach ($khoa_gv as $k) {
    $q = $quiz_model->layTheoBaiHoc($k['id']);
    $so_quiz += count($q);
}

// 3 khóa gần nhất
$khoa_gv_top = array_slice($khoa_gv, 0, 3);

include __DIR__ . '/partials/layout_start.php';
?>

<div class="tv-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-0">
            <i class="fas fa-chalkboard-teacher me-2 text-success"></i>Tổng quan
        </h1>
        <p class="text-muted small mb-0">Chào mừng, <?php echo htmlspecialchars($nguoi_dung['ho_ten']); ?>!</p>
    </div>
    <a href="<?php echo SITE_URL; ?>/teacher/khoa-hoc/them-moi.php" class="btn btn-success">
        <i class="fas fa-plus me-1"></i>Tạo khóa học
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card tv-stat-card p-3 text-center">
            <div class="icon mx-auto mb-2" style="background:#d1fae5;color:#059669">
                <i class="fas fa-book"></i>
            </div>
            <div class="fs-3 fw-bold" style="color:#059669"><?php echo $so_kh; ?></div>
            <div class="text-muted small">Khóa học</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card tv-stat-card p-3 text-center">
            <div class="icon mx-auto mb-2" style="background:#dbeafe;color:#2563eb">
                <i class="fas fa-file-lines"></i>
            </div>
            <div class="fs-3 fw-bold" style="color:#2563eb"><?php echo $so_bai; ?></div>
            <div class="text-muted small">Bài học</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card tv-stat-card p-3 text-center">
            <div class="icon mx-auto mb-2" style="background:#fef3c7;color:#d97706">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="fs-3 fw-bold" style="color:#d97706"><?php echo $so_hv; ?></div>
            <div class="text-muted small">Học viên</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card tv-stat-card p-3 text-center">
            <div class="icon mx-auto mb-2" style="background:#f3e8ff;color:#7c3aed">
                <i class="fas fa-circle-question"></i>
            </div>
            <div class="fs-3 fw-bold" style="color:#7c3aed"><?php echo $so_quiz; ?></div>
            <div class="text-muted small">Quiz</div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Khóa học gần đây -->
    <div class="col-lg-8">
        <div class="card tv-stat-card">
            <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                <span><i class="fas fa-book me-2 text-success"></i>Khóa học của tôi</span>
                <a href="<?php echo SITE_URL; ?>/teacher/khoa-hoc/index.php" class="btn btn-sm btn-outline-success">
                    Xem tất cả
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($khoa_gv)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Bạn chưa có khóa học nào.</h5>
                        <a href="<?php echo SITE_URL; ?>/teacher/khoa-hoc/them-moi.php" class="btn btn-success mt-2">
                            <i class="fas fa-plus me-1"></i>Tạo khóa học đầu tiên
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Khóa học</th>
                                    <th>Bài</th>
                                    <th>HV</th>
                                    <th>Trạng thái</th>
                                    <th class="pe-4 text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($khoa_gv_top as $k): ?>
                                    <?php
                                    $tt_labels = [
                                        'ban_nhap'  => '<span class="badge bg-secondary">Bản nháp</span>',
                                        'da_duyet'  => '<span class="badge bg-success">Đã duyệt</span>',
                                        'bi_an'     => '<span class="badge bg-danger">Đã ẩn</span>',
                                    ];
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold"><?php echo htmlspecialchars($k['ten_khoa_hoc']); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($k['ten_danh_muc'] ?? ''); ?></div>
                                        </td>
                                        <td><span class="badge bg-secondary"><?php echo (int)($k['so_bai_hoc'] ?? 0); ?></span></td>
                                        <td><span class="badge bg-warning text-dark"><?php echo (int)($k['so_hoc_vien'] ?? 0); ?></span></td>
                                        <td><?php echo $tt_labels[$k['trang_thai']] ?? ''; ?></td>
                                        <td class="pe-4 text-center">
                                            <a href="<?php echo SITE_URL; ?>/teacher/khoa-hoc/sua.php?id=<?php echo $k['id']; ?>"
                                               class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick actions -->
    <div class="col-lg-4">
        <div class="card tv-stat-card">
            <div class="card-header bg-white fw-bold py-3">
                <i class="fas fa-bolt me-2 text-success"></i>Thao tác nhanh
            </div>
            <div class="card-body d-grid gap-2">
                <a href="<?php echo SITE_URL; ?>/teacher/khoa-hoc/them-moi.php" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Tạo khóa học mới
                </a>
                <a href="<?php echo SITE_URL; ?>/teacher/bai-hoc/index.php" class="btn btn-outline-success">
                    <i class="fas fa-file-lines me-1"></i>Quản lý bài học
                </a>
                <a href="<?php echo SITE_URL; ?>/teacher/quiz/index.php" class="btn btn-outline-success">
                    <i class="fas fa-circle-question me-1"></i>Quản lý Quiz
                </a>
                <a href="<?php echo SITE_URL; ?>/teacher/hoc-vien/index.php" class="btn btn-outline-success">
                    <i class="fas fa-user-graduate me-1"></i>Xem học viên
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/layout_end.php'; ?>
