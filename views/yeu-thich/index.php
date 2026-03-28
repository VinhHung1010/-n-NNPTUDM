<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/yeu_thich.php';

$page_title = 'Khóa học yêu thích - ' . SITE_NAME;
$auth = new Auth();

// Kiểm tra đăng nhập
if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$nguoi_dung = $_SESSION['nguoi_dung'];
$yeu_thich_model = new YeuThich();

$yeu_thich_list = $yeu_thich_model->layDanhSach($nguoi_dung['id']);

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container mt-4">

    <!-- Tiêu đề -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fas fa-heart me-2" style="color:var(--danger)"></i>Khóa học yêu thích
            </h2>
            <p class="text-muted mb-0"><?php echo count($yeu_thich_list); ?> khóa học</p>
        </div>
        <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i>Quay lại khóa học
        </a>
    </div>

    <?php if (empty($yeu_thich_list)): ?>
        <!-- Trạng thái trống -->
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-heart-broken fa-4x text-muted opacity-25"></i>
            </div>
            <h4 class="text-muted mb-2">Chưa có khóa học yêu thích</h4>
            <p class="text-muted mb-4">Hãy thêm những khóa học bạn quan tâm vào danh sách yêu thích.</p>
            <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php" class="btn btn-primary px-4">
                <i class="fas fa-search me-1"></i>Khám phá khóa học
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($yeu_thich_list as $k): ?>
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
                            <!-- Nút bỏ yêu thích -->
                            <button class="btn btn-sm btn-danger position-absolute bottom-0 end-0 m-2 favorite-btn"
                                    onclick="toggleFavorite(<?php echo $k['id']; ?>, this)"
                                    title="Bỏ yêu thích">
                                <i class="fas fa-heart"></i>
                            </button>
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
                                <small class="text-muted">
                                    <i class="fas fa-heart text-danger me-1"></i>
                                    <?php echo date('d/m/Y', strtotime($k['ngay_yeu_thich'])); ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0">
                            <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo $k['id']; ?>"
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

<script>
// Toggle favorite function
async function toggleFavorite(khoaHocId, btnElement) {
    try {
        const response = await fetch(`<?php echo VIEWS_URL; ?>/yeu-thich/controller.php?action=toggle&id_khoa_hoc=${khoaHocId}`);
        const data = await response.json();

        if (data.success) {
            if (data.data.action === 'removed') {
                // Nếu đang ở trang yêu thích thì xóa card
                const card = btnElement.closest('.col-lg-3, .col-md-4, .col-sm-6');
                if (card) {
                    card.style.transition = 'opacity 0.3s';
                    card.style.opacity = '0';
                    setTimeout(() => card.remove(), 300);
                }
            }
        } else {
            alert(data.message || 'Có lỗi xảy ra!');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Có lỗi xảy ra. Vui lòng thử lại!');
    }
}
</script>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
