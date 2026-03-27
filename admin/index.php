<?php
$page_title = 'Bảng điều khiển';
require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/config/database.php';

$db = Database::getInstance()->getConnection();

$so_nguoi_dung = (int) $db->query("SELECT COUNT(*) AS c FROM nguoi_dung")->fetch_assoc()['c'];
$so_khoa_hoc   = (int) $db->query("SELECT COUNT(*) AS c FROM khoa_hoc WHERE trang_thai = 'da_duyet'")->fetch_assoc()['c'];
$so_bai_hoc    = (int) $db->query("SELECT COUNT(*) AS c FROM bai_hoc")->fetch_assoc()['c'];
$so_quiz       = (int) $db->query("SELECT COUNT(*) AS c FROM quiz")->fetch_assoc()['c'];
$so_dang_ky    = (int) $db->query("SELECT COUNT(*) AS c FROM dang_ky_khoa_hoc")->fetch_assoc()['c'];
$cho_xu_ly     = (int) $db->query("SELECT COUNT(*) AS c FROM dang_ky_khoa_hoc WHERE trang_thai = 'cho_xu_ly'")->fetch_assoc()['c'];

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
    <div class="col-sm-6 col-xl-3">
        <a href="<?php echo SITE_URL; ?>/admin/dang-ky/index.php" class="text-decoration-none">
            <div class="card stat-card h-100 <?php if ($cho_xu_ly > 0) echo 'border-warning'; ?>">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon bg-<?php echo $cho_xu_ly > 0 ? 'warning' : 'secondary'; ?> bg-opacity-10 text-<?php echo $cho_xu_ly > 0 ? 'warning' : 'secondary'; ?>">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Đăng ký</div>
                        <div class="fs-4 fw-semibold">
                            <?php echo $so_dang_ky; ?>
                            <?php if ($cho_xu_ly > 0): ?>
                                <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem"><?php echo $cho_xu_ly; ?> chờ</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-sm-6 col-md-3">
        <a href="<?php echo SITE_URL; ?>/admin/nguoi-dung/index.php" class="text-decoration-none">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="text-muted small">Quản lý</div>
                        <div class="fw-semibold text-dark">Người dùng</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-md-3">
        <a href="<?php echo SITE_URL; ?>/admin/danh-muc/index.php" class="text-decoration-none">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-layer-group"></i></div>
                    <div>
                        <div class="text-muted small">Quản lý</div>
                        <div class="fw-semibold text-dark">Danh mục</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-md-3">
        <a href="<?php echo SITE_URL; ?>/admin/khoa-hoc/index.php" class="text-decoration-none">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon bg-info bg-opacity-10 text-info"><i class="fas fa-book"></i></div>
                    <div>
                        <div class="text-muted small">Quản lý</div>
                        <div class="fw-semibold text-dark">Khóa học</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-md-3">
        <a href="<?php echo SITE_URL; ?>/admin/bai-hoc/index.php" class="text-decoration-none">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon bg-purple bg-opacity-10" style="color:#7c3aed"><i class="fas fa-file-lines"></i></div>
                    <div>
                        <div class="text-muted small">Quản lý</div>
                        <div class="fw-semibold text-dark">Bài học</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-md-3">
        <a href="<?php echo SITE_URL; ?>/admin/quiz/index.php" class="text-decoration-none">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-circle-question"></i></div>
                    <div>
                        <div class="text-muted small">Quản lý</div>
                        <div class="fw-semibold text-dark">Quiz</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-md-3">
        <a href="<?php echo SITE_URL; ?>/admin/dang-ky/index.php" class="text-decoration-none">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-user-plus"></i></div>
                    <div>
                        <div class="text-muted small">Quản lý</div>
                        <div class="fw-semibold text-dark">Đăng ký</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<?php include __DIR__ . '/partials/layout_end.php'; ?>
