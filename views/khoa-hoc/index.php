<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';

$page_title = 'Danh sách Khóa học - ' . SITE_NAME;
$auth = new Auth();
$khoa_hoc = new KhoaHoc();

$danh_muc = $khoa_hoc->layDanhMuc();
$khoa_hoc_list = $khoa_hoc->layTatCa('da_duyet');

// Xử lý tìm kiếm
$tu_khoa = isset($_GET['tu_khoa']) ? trim($_GET['tu_khoa']) : '';
if (!empty($tu_khoa)) {
    $khoa_hoc_list = $khoa_hoc->timKiem($tu_khoa);
}

// Xử lý lọc theo danh mục
$danh_muc_id = isset($_GET['danh_muc']) ? (int)$_GET['danh_muc'] : 0;
if ($danh_muc_id > 0) {
    $khoa_hoc_list = $khoa_hoc->layTheoDanhMuc($danh_muc_id);
}

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container mt-4">
    <!-- Tiêu đề -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-book me-2"></i>Danh sách Khóa học</h2>
        <?php if ($auth->kiemTraDangNhap() && in_array($_SESSION['nguoi_dung']['vai_tro'], ['giao_vien', 'quan_tri'])): ?>
            <a href="them-moi.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Thêm khóa học
            </a>
        <?php endif; ?>
    </div>

    <!-- Tìm kiếm và lọc -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="tu_khoa" class="form-control" 
                               placeholder="Tìm kiếm khóa học..." 
                               value="<?php echo htmlspecialchars($tu_khoa); ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="danh_muc" class="form-select">
                        <option value="">-- Tất cả danh mục --</option>
                        <?php foreach ($danh_muc as $dm): ?>
                            <option value="<?php echo $dm['id']; ?>" <?php echo ($danh_muc_id == $dm['id']) ? 'selected' : ''; ?>>
                                <?php echo $dm['ten_danh_muc']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="index.php" class="btn btn-secondary w-100">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách khóa học -->
    <?php if (empty($khoa_hoc_list)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i>
            Không tìm thấy khóa học nào.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($khoa_hoc_list as $kh): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card course-card h-100">
                        <div class="position-relative">
                            <?php 
                            $hinh_anh = !empty($kh['hinh_anh']) ? $kh['hinh_anh'] : 'https://via.placeholder.com/300x180/4a90d9/fff?text=Khóa+học';
                            ?>
                            <img src="<?php echo $hinh_anh; ?>" class="card-img-top" alt="<?php echo $kh['ten_khoa_hoc']; ?>">
                            <span class="position-absolute top-0 end-0 badge badge-category m-2">
                                <?php echo $kh['ten_danh_muc']; ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $kh['ten_khoa_hoc']; ?></h5>
                            <p class="card-text text-muted small">
                                <i class="fas fa-user me-1"></i><?php echo $kh['ten_giao_vien'] ?? 'Chưa có giáo viên'; ?>
                            </p>
                            <p class="card-text small">
                                <i class="fas fa-file-alt me-1"></i><?php echo $kh['so_bai_hoc']; ?> bài học
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="<?php echo ($kh['gia_tien'] == 0) ? 'price-free' : 'price-tag'; ?>">
                                    <?php echo ($kh['gia_tien'] == 0) ? 'Miễn phí' : number_format($kh['gia_tien']) . ' đ'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="chi-tiet.php?id=<?php echo $kh['id']; ?>" class="btn btn-primary w-100">
                                <i class="fas fa-eye me-1"></i>Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
