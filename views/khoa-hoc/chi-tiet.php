<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';

$page_title = 'Chi tiết Khóa học - ' . SITE_NAME;
$auth = new Auth();
$khoa_hoc_model = new KhoaHoc();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: index.php'); exit; }

$khoa_hoc = $khoa_hoc_model->layTheoId($id);
if (!$khoa_hoc) { header('Location: index.php'); exit; }

// Xử lý đăng ký
$thong_bao = '';
$thong_bao_type = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dang_ky'])) {
    if (!$auth->kiemTraDangNhap()) {
        header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
        exit;
    }
    $nd = $auth->layThongTinNguoiDung();
    if ($khoa_hoc_model->daDangKy($nd['id'], $id)) {
        $thong_bao = 'Bạn đã đăng ký khóa học này rồi.';
        $thong_bao_type = 'warning';
    } else {
        if ($khoa_hoc_model->dangKy($nd['id'], $id)) {
            $thong_bao = 'Đăng ký khóa học thành công! Bạn có thể bắt đầu học ngay.';
        } else {
            $thong_bao = 'Đăng ký thất bại. Vui lòng thử lại.';
            $thong_bao_type = 'danger';
        }
    }
}

// Lấy bài học
require_once __DIR__ . '/../../models/bai_hoc.php';
$bh_model = new BaiHoc();
$bai_hoc_list = $bh_model->layTheoKhoaHoc($id);
$tong_thoi_luong = $bh_model->tinhTongThoiLuong($id);

// Check enrollment
$da_dang_ky = false;
$nguoi_dung_hien_tai = $auth->layThongTinNguoiDung();
if ($nguoi_dung_hien_tai) {
    $da_dang_ky = $khoa_hoc_model->daDangKy($nguoi_dung_hien_tai['id'], $id);
}

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container mt-4">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php">Khóa học</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($khoa_hoc['ten_khoa_hoc']); ?></li>
        </ol>
    </nav>

    <?php if ($thong_bao): ?>
        <div class="alert alert-<?php echo $thong_bao_type; ?>">
            <i class="fas fa-<?php echo $thong_bao_type === 'success' ? 'check-circle' : ($thong_bao_type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?> me-2"></i>
            <?php echo $thong_bao; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- ══ Thông tin khóa học ══ -->
        <div class="col-lg-8">
            <!-- Hình ảnh + info -->
            <div class="card mb-4 overflow-hidden" style="border-radius:16px">
                <?php
                $hinh_anh = !empty($khoa_hoc['hinh_anh'])
                    ? $khoa_hoc['hinh_anh']
                    : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=300&fit=crop';
                ?>
                <img src="<?php echo $hinh_anh; ?>" class="w-100" style="height:260px;object-fit:cover"
                     alt="<?php echo htmlspecialchars($khoa_hoc['ten_khoa_hoc']); ?>"
                     onerror="this.src='https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=300&fit=crop'">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge" style="background:var(--primary)">
                            <?php echo htmlspecialchars($khoa_hoc['ten_danh_muc'] ?? 'Chưa phân loại'); ?>
                        </span>
                        <?php if ((int)($khoa_hoc['gia_tien'] ?? 0) === 0): ?>
                            <span class="badge bg-success">Miễn phí</span>
                        <?php endif; ?>
                    </div>
                    <h3 class="fw-bold mb-3"><?php echo htmlspecialchars($khoa_hoc['ten_khoa_hoc']); ?></h3>

                    <div class="row g-3">
                        <div class="col-auto">
                            <div class="d-flex align-items-center gap-2 text-muted small">
                                <i class="fas fa-user"></i>
                                <span>GV: <strong><?php echo htmlspecialchars($khoa_hoc['ten_giao_vien'] ?? 'Đang cập nhật'); ?></strong></span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex align-items-center gap-2 text-muted small">
                                <i class="fas fa-file-lines"></i>
                                <span><strong><?php echo count($bai_hoc_list); ?></strong> bài học</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex align-items-center gap-2 text-muted small">
                                <i class="fas fa-clock"></i>
                                <span><strong><?php echo $tong_thoi_luong; ?></strong> phút</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mô tả -->
            <?php if (!empty($khoa_hoc['mo_ta'])): ?>
            <div class="card mb-4" style="border-radius:16px">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-info-circle me-2" style="color:var(--primary)"></i>Mô tả khóa học
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space:pre-line"><?php echo htmlspecialchars($khoa_hoc['mo_ta']); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Danh sách bài học -->
            <div class="card" style="border-radius:16px">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-list me-2" style="color:var(--primary)"></i>
                    Nội dung khóa học
                    <span class="badge bg-secondary ms-2"><?php echo count($bai_hoc_list); ?> bài</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($bai_hoc_list)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-folder-open fa-2x mb-2"></i>
                            <p class="mb-0">Khóa học này chưa có bài học nào.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($bai_hoc_list as $index => $bh): ?>
                                <a href="<?php echo VIEWS_URL; ?>/bai-hoc/chi-tiet.php?id=<?php echo $bh['id']; ?>"
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge rounded-circle"
                                              style="background:var(--primary-light);color:var(--primary);width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.8rem">
                                            <?php echo $index + 1; ?>
                                        </span>
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($bh['tieu_de']); ?></div>
                                            <?php if (!empty($bh['video_url'])): ?>
                                                <span class="badge bg-success mt-1" style="font-size:0.68rem">
                                                    <i class="fas fa-video me-1"></i>Có video
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-muted">
                                        <i class="fas fa-clock me-1"></i><?php echo $bh['thoi_luong_phut']; ?>p
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ══ Sidebar ══ -->
        <div class="col-lg-4">
            <!-- Thông tin giá -->
            <div class="card mb-4 sticky-top" style="top:20px;border-radius:16px;border:2px solid var(--border)">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <span class="<?php echo ((int)$khoa_hoc['gia_tien'] === 0) ? 'price-free' : 'price-tag'; ?>"
                              style="font-size:1.8rem">
                            <?php
                            if ((int)$khoa_hoc['gia_tien'] === 0) {
                                echo 'Miễn phí';
                            } else {
                                echo number_format($khoa_hoc['gia_tien'], 0, ',', '.') . 'đ';
                            }
                            ?>
                        </span>
                    </div>

                    <?php if ($auth->kiemTraDangNhap()): ?>
                        <?php if ($da_dang_ky): ?>
                            <!-- Đã đăng ký -->
                            <div class="d-grid gap-2">
                                <?php if (!empty($bai_hoc_list)): ?>
                                    <a href="<?php echo VIEWS_URL; ?>/bai-hoc/chi-tiet.php?id=<?php echo $bai_hoc_list[0]['id']; ?>"
                                       class="btn btn-success py-2 fw-semibold">
                                        <i class="fas fa-play me-1"></i>Học ngay
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary py-2" disabled>Chưa có bài học</button>
                                <?php endif; ?>
                                <div class="alert alert-success py-2 mb-0 text-start">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Bạn đã đăng ký khóa học này.
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Chưa đăng ký -->
                            <form method="POST">
                                <button type="submit" name="dang_ky" class="btn btn-primary w-100 py-2 fw-semibold mb-2">
                                    <i class="fas fa-graduation-cap me-1"></i>
                                    <?php echo ((int)$khoa_hoc['gia_tien'] === 0) ? 'Đăng ký miễn phí' : 'Đăng ký khóa học'; ?>
                                </button>
                            </form>
                            <a href="<?php echo VIEWS_URL; ?>/home/index.php" class="btn btn-outline-secondary w-100 py-2">
                                <i class="fas fa-chart-line me-1"></i>Tiến độ học tập
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Chưa đăng nhập -->
                        <a href="<?php echo VIEWS_URL; ?>/tai-khoan/dang-nhap.php" class="btn btn-primary w-100 py-2 fw-semibold mb-2">
                            <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập để học
                        </a>
                        <a href="<?php echo VIEWS_URL; ?>/tai-khoan/dang-ky.php" class="btn btn-outline-secondary w-100 py-2">
                            <i class="fas fa-user-plus me-1"></i>Tạo tài khoản mới
                        </a>
                    <?php endif; ?>

                    <hr>

                    <!-- Thông tin chi tiết -->
                    <div class="text-start">
                        <div class="d-flex align-items-center gap-2 mb-2 text-muted small">
                            <i class="fas fa-user-tie" style="width:16px"></i>
                            <span>Giảng viên: <strong><?php echo htmlspecialchars($khoa_hoc['ten_giao_vien'] ?? 'N/A'); ?></strong></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2 text-muted small">
                            <i class="fas fa-file-lines" style="width:16px"></i>
                            <span><?php echo count($bai_hoc_list); ?> bài học</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2 text-muted small">
                            <i class="fas fa-clock" style="width:16px"></i>
                            <span><?php echo $tong_thoi_luong; ?> phút tổng thời lượng</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 text-muted small">
                            <i class="fas fa-infinity" style="width:16px"></i>
                            <span>Truy cập trọn đời</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
