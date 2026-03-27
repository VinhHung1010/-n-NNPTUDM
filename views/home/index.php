<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';

$page_title = 'Trang chủ - ' . SITE_NAME;
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

include __DIR__ . '/../layouts/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="mb-4">Chào mừng đến với E-LEARNING</h1>
        <p class="lead mb-4">Học mọi lúc, mọi nơi với các khóa học chất lượng cao</p>
        
        <form action="" method="GET" class="search-box">
            <div class="input-group">
                <input type="text" name="tu_khoa" class="form-control" 
                       placeholder="Tìm kiếm khóa học..." 
                       value="<?php echo htmlspecialchars($tu_khoa); ?>">
                <button class="btn btn-light" type="submit">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
        </form>
    </div>
</section>

<div class="container">
    <!-- Danh mục -->
    <section class="mb-5">
        <h3 class="mb-4"><i class="fas fa-th-large me-2"></i>Danh mục khóa học</h3>
        <div class="row">
            <?php foreach ($danh_muc as $dm): ?>
                <div class="col-md-2 col-6 mb-3">
                    <a href="?danh_muc=<?php echo $dm['id']; ?>" 
                       class="text-decoration-none">
                        <div class="card text-center py-3 <?php echo ($danh_muc_id == $dm['id']) ? 'border-primary' : ''; ?>">
                            <div class="card-body">
                                <i class="fas fa-folder-open fa-2x text-primary mb-2"></i>
                                <p class="mb-0 fw-medium"><?php echo $dm['ten_danh_muc']; ?></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
            <?php if ($danh_muc_id > 0): ?>
                <div class="col-md-2 col-6 mb-3">
                    <a href="index.php" class="text-decoration-none">
                        <div class="card text-center py-3 border-danger">
                            <div class="card-body">
                                <i class="fas fa-times fa-2x text-danger mb-2"></i>
                                <p class="mb-0 fw-medium">Xóa lọc</p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Danh sách khóa học -->
    <section class="mb-5">
        <h3 class="mb-4">
            <i class="fas fa-book me-2"></i>
            <?php 
            if (!empty($tu_khoa)) {
                echo "Kết quả tìm kiếm: \"{$tu_khoa}\"";
            } elseif ($danh_muc_id > 0) {
                foreach ($danh_muc as $dm) {
                    if ($dm['id'] == $danh_muc_id) {
                        echo "Khóa học: " . $dm['ten_danh_muc'];
                        break;
                    }
                }
            } else {
                echo 'Khóa học nổi bật';
            }
            ?>
            <span class="badge bg-secondary ms-2"><?php echo count($khoa_hoc_list); ?></span>
        </h3>

        <?php if (empty($khoa_hoc_list)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>
                <?php if (!empty($tu_khoa)): ?>
                    Không tìm thấy khóa học nào với từ khóa "<?php echo htmlspecialchars($tu_khoa); ?>"
                <?php else: ?>
                    Hiện chưa có khóa học nào.
                <?php endif; ?>
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
                                    <i class="fas fa-user me-1"></i><?php echo $kh['ten_giao_vien']; ?>
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
                                <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo $kh['id']; ?>" 
                                   class="btn btn-primary w-100">
                                    <i class="fas fa-eye me-1"></i>Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
