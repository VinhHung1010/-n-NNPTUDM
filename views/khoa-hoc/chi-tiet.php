<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';

$page_title = 'Chi tiết Khóa học - ' . SITE_NAME;
$auth = new Auth();
$khoa_hoc_model = new KhoaHoc();

// Lấy ID khóa học
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$khoa_hoc = $khoa_hoc_model->layTheoId($id);

if (!$khoa_hoc) {
    header('Location: index.php');
    exit;
}

// Lấy danh mục
$danh_muc = $khoa_hoc_model->layDanhMuc();

// Lấy bài học của khóa học
require_once __DIR__ . '/../../models/bai_hoc.php';
$bai_hoc_model = new BaiHoc();
$bai_hoc_list = $bai_hoc_model->layTheoKhoaHoc($id);
$tong_bai_hoc = count($bai_hoc_list);
$tong_thoi_luong = $bai_hoc_model->tinhTongThoiLuong($id);

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php">Khóa học</a></li>
            <li class="breadcrumb-item active"><?php echo $khoa_hoc['ten_khoa_hoc']; ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Thông tin khóa học -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="row g-0">
                    <div class="col-md-4">
                        <?php 
                        $hinh_anh = !empty($khoa_hoc['hinh_anh']) ? $khoa_hoc['hinh_anh'] : 'https://via.placeholder.com/300x200/4a90d9/fff?text=Khóa+học';
                        ?>
                        <img src="<?php echo $hinh_anh; ?>" class="img-fluid rounded-start h-100" alt="<?php echo $khoa_hoc['ten_khoa_hoc']; ?>" style="object-fit: cover;">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <span class="badge bg-primary mb-2"><?php echo $khoa_hoc['ten_danh_muc']; ?></span>
                            <h3 class="card-title"><?php echo $khoa_hoc['ten_khoa_hoc']; ?></h3>
                            <p class="card-text">
                                <i class="fas fa-user me-1"></i>
                                <strong>Giáo viên:</strong> <?php echo $khoa_hoc['ten_giao_vien'] ?? 'Chưa có'; ?>
                            </p>
                            <p class="card-text">
                                <i class="fas fa-file-alt me-1"></i>
                                <strong><?php echo $tong_bai_hoc; ?></strong> bài học
                            </p>
                            <p class="card-text">
                                <i class="fas fa-clock me-1"></i>
                                <strong><?php echo $tong_thoi_luong; ?></strong> phút
                            </p>
                            <p class="card-text">
                                <span class="<?php echo ($khoa_hoc['gia_tien'] == 0) ? 'price-free fs-5' : 'price-tag fs-5'; ?>">
                                    <strong>
                                        <?php echo ($khoa_hoc['gia_tien'] == 0) ? 'MIỄN PHÍ' : number_format($khoa_hoc['gia_tien']) . ' đ'; ?>
                                    </strong>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mô tả khóa học -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Mô tả khóa học</h5>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo nl2br($khoa_hoc['mo_ta'] ?? 'Chưa có mô tả'); ?></p>
                </div>
            </div>

            <!-- Danh sách bài học -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Danh sách bài học
                        <span class="badge bg-light text-dark ms-2"><?php echo $tong_bai_hoc; ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($bai_hoc_list)): ?>
                        <div class="alert alert-info m-3 mb-0">
                            <i class="fas fa-info-circle me-2"></i>Khóa học này chưa có bài học nào.
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($bai_hoc_list as $index => $bh): ?>
                                <a href="<?php echo VIEWS_URL; ?>/bai-hoc/chi-tiet.php?id=<?php echo $bh['id']; ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-secondary me-2"><?php echo $index + 1; ?></span>
                                            <strong><?php echo $bh['tieu_de']; ?></strong>
                                        </div>
                                        <div>
                                            <span class="badge bg-info me-2">
                                                <i class="fas fa-clock me-1"></i><?php echo $bh['thoi_luong_phut']; ?> phút
                                            </span>
                                            <?php if (!empty($bh['video_url'])): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-video"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <?php if ($auth->kiemTraDangNhap()): ?>
                <div class="card shadow sticky-top" style="top: 20px;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-play-circle me-2"></i>Bắt đầu học</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($bai_hoc_list)): ?>
                            <a href="<?php echo VIEWS_URL; ?>/bai-hoc/chi-tiet.php?id=<?php echo $bai_hoc_list[0]['id']; ?>" 
                               class="btn btn-success w-100 mb-3">
                                <i class="fas fa-play me-2"></i>Học ngay
                            </a>
                        <?php else: ?>
                            <p class="text-muted text-center">Chưa có bài học để học.</p>
                        <?php endif; ?>
                        
                        <?php if ($auth->kiemTraDangNhap() && $_SESSION['nguoi_dung']['vai_tro'] === 'quan_tri'): ?>
                            <hr>
                            <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/sua.php?id=<?php echo $id; ?>" 
                               class="btn btn-warning w-100 mb-2">
                                <i class="fas fa-edit me-2"></i>Sửa khóa học
                            </a>
                            <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/xoa.php?id=<?php echo $id; ?>" 
                               class="btn btn-danger w-100"
                               onclick="return confirm('Bạn có chắc muốn xóa khóa học này?');">
                                <i class="fas fa-trash me-2"></i>Xóa khóa học
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Đăng nhập để học</h5>
                    </div>
                    <div class="card-body text-center">
                        <p class="text-muted">Vui lòng đăng nhập để bắt đầu học khóa học này.</p>
                        <a href="<?php echo VIEWS_URL; ?>/tai-khoan/dang-nhap.php" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                        </a>
                        <a href="<?php echo VIEWS_URL; ?>/tai-khoan/dang-ky.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-user-plus me-2"></i>Đăng ký
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
