<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';
require_once __DIR__ . '/../../models/danh_muc.php';

$page_title = 'Khóa học - ' . SITE_NAME;
$auth = new Auth();
$kh_model = new KhoaHoc();
$dm_model = new DanhMuc();

$all_khoa_hoc = $kh_model->layTatCa('da_duyet');
$danh_mucs = $dm_model->layTatCa();

$tu_khoa    = isset($_GET['tu_khoa']) ? trim($_GET['tu_khoa']) : '';
$danh_muc_id = isset($_GET['danh_muc']) ? (int)$_GET['danh_muc'] : 0;

$khoa_hoc_list = $all_khoa_hoc;

if ($tu_khoa !== '') {
    $khoa_hoc_list = array_filter($khoa_hoc_list,
        fn($k) => stripos($k['ten_khoa_hoc'], $tu_khoa) !== false
               || stripos($k['ten_giao_vien'] ?? '', $tu_khoa) !== false
    );
} elseif ($danh_muc_id > 0) {
    $khoa_hoc_list = array_filter($khoa_hoc_list,
        fn($k) => (int)($k['id_danh_muc'] ?? 0) === $danh_muc_id
    );
}

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container mt-4">

    <!-- Tiêu đề -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fas fa-book me-2" style="color:var(--primary)"></i>Danh sách Khóa học
            </h2>
            <p class="text-muted mb-0"><?php echo count($khoa_hoc_list); ?> khóa học</p>
        </div>
        <?php if ($auth->kiemTraDangNhap() && in_array($_SESSION['nguoi_dung']['vai_tro'] ?? '', ['giao_vien', 'quan_tri'])): ?>
            <a href="them-moi.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Thêm khóa học
            </a>
        <?php endif; ?>
    </div>

    <!-- Filter -->
    <div class="card mb-4" style="border-radius:16px">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="tu_khoa" class="form-control"
                               placeholder="Tìm tên khóa học..."
                               value="<?php echo htmlspecialchars($tu_khoa); ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="danh_muc" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả danh mục</option>
                        <?php foreach ($danh_mucs as $dm): ?>
                            <option value="<?php echo $dm['id']; ?>"
                                <?php if ($danh_muc_id == $dm['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($dm['ten_danh_muc']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($tu_khoa !== '' || $danh_muc_id > 0): ?>
                    <div class="col-md-2">
                        <a href="index.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-redo me-1"></i>Reset
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Danh sách -->
    <?php if (empty($khoa_hoc_list)): ?>
        <div class="text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Không tìm thấy khóa học nào.</h5>
            <a href="index.php" class="btn btn-outline-primary mt-2">Xem tất cả</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($khoa_hoc_list as $k): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card course-card h-100">
                        <div class="position-relative">
                            <?php
                            $hinh_anh = !empty($k['hinh_anh'])
                                ? $k['hinh_anh']
                                : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=200&fit=crop';
                            ?>
                            <img src="<?php echo $hinh_anh; ?>" class="card-img-top"
                                 alt="<?php echo htmlspecialchars($k['ten_khoa_hoc']); ?>"
                                 onerror="this.src='https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=200&fit=crop'">
                            <span class="position-absolute top-0 end-0 badge badge-category m-2">
                                <?php echo htmlspecialchars($k['ten_danh_muc'] ?? ''); ?>
                            </span>
                            <?php if ((int)($k['so_bai_hoc'] ?? 0) > 0): ?>
                                <span class="position-absolute top-0 start-0 badge bg-dark m-2" style="font-size:0.68rem;opacity:0.8">
                                    <i class="fas fa-file-lines me-1"></i><?php echo $k['so_bai_hoc']; ?> bài
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($k['ten_khoa_hoc']); ?></h5>
                            <p class="card-text mt-auto small text-muted">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($k['ten_giao_vien'] ?? 'Giảng viên'); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="<?php echo ((int)$k['gia_tien'] === 0) ? 'price-free' : 'price-tag'; ?>">
                                    <?php echo ((int)$k['gia_tien'] === 0) ? 'Miễn phí' : number_format($k['gia_tien'], 0, ',', '.') . 'đ'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0">
                            <a href="chi-tiet.php?id=<?php echo $k['id']; ?>"
                               class="btn btn-primary w-100">
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
