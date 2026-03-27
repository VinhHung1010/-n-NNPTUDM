<?php
$page_title = 'Bảng điều khiển';
require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/config/database.php';

$db = Database::getInstance()->getConnection();

$so_nguoi_dung = (int) $db->query("SELECT COUNT(*) AS c FROM nguoi_dung")->fetch_assoc()['c'];
$so_khoa_hoc   = (int) $db->query("SELECT COUNT(*) AS c FROM khoa_hoc")->fetch_assoc()['c'];
$so_bai_hoc    = (int) $db->query("SELECT COUNT(*) AS c FROM bai_hoc")->fetch_assoc()['c'];
$so_quiz       = (int) $db->query("SELECT COUNT(*) AS c FROM quiz")->fetch_assoc()['c'];

include __DIR__ . '/partials/layout_start.php';
?>

<div class="admin-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-0">Bảng điều khiển</h1>
        <p class="text-muted small mb-0">Tổng quan hệ thống e-learning</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo SITE_URL; ?>/views/tai-khoan/doi-mat-khau.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-key me-1"></i>Đổi mật khẩu
        </a>
        <a href="<?php echo SITE_URL; ?>/views/tai-khoan/dang-xuat.php" class="btn btn-danger btn-sm">
            <i class="fas fa-right-from-bracket me-1"></i>Đăng xuất
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-users"></i></div>
                <div>
                    <div class="text-muted small">Người dùng</div>
                    <div class="fs-4 fw-semibold"><?php echo $so_nguoi_dung; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-book"></i></div>
                <div>
                    <div class="text-muted small">Khóa học</div>
                    <div class="fs-4 fw-semibold"><?php echo $so_khoa_hoc; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-file-lines"></i></div>
                <div>
                    <div class="text-muted small">Bài học</div>
                    <div class="fs-4 fw-semibold"><?php echo $so_bai_hoc; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-info bg-opacity-10 text-info"><i class="fas fa-circle-question"></i></div>
                <div>
                    <div class="text-muted small">Quiz</div>
                    <div class="fs-4 fw-semibold"><?php echo $so_quiz; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card stat-card">
    <div class="card-body">
        <h2 class="h5 mb-3"><i class="fas fa-circle-info me-2 text-primary"></i>Hướng dẫn nhanh</h2>
        <p class="text-muted mb-2">Đây là khu vực quản trị tách biệt với giao diện học viên. Bạn có thể mở <strong>Khóa học</strong> hoặc <strong>Trang chủ công khai</strong> từ menu bên trái khi cần xem như người dùng thường.</p>
        <p class="text-muted mb-0">Các chức năng quản lý chi tiết (duyệt khóa học, người dùng, …) có thể bổ sung dần trên nhánh tính năng tương ứng.</p>
    </div>
</div>

<?php include __DIR__ . '/partials/layout_end.php'; ?>
