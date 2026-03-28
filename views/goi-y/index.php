<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/goi_y.php';
require_once __DIR__ . '/../../models/yeu_thich.php';

$page_title = 'Gợi ý khóa học - ' . SITE_NAME;
$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$nguoi_dung = $auth->layThongTinNguoiDung();
$gy_model = new GoiY();
$yt_model = new YeuThich();

// Lấy gợi ý tổng hợp
$goi_y_list = $gy_model->goiYTongHop($nguoi_dung['id'], 12);
$cung_danh_muc = $gy_model->goiYCungDanhMuc($nguoi_dung['id'], 6);
$cung_giao_vien = $gy_model->goiYCungGiaoVien($nguoi_dung['id'], 4);
$pho_bien = $gy_model->goiYPhoBien($nguoi_dung['id'], 6);
$moi_nhat = $gy_model->goiYMoiNhat($nguoi_dung['id'], 4);
$lich_su_xem = $gy_model->layKhoaHocDaXem($nguoi_dung['id'], 6);

// Lấy IDs yêu thích
$yeu_thich_ids = $yt_model->layIdsYeuThich($nguoi_dung['id']);

include __DIR__ . '/../../views/layouts/header.php';
?>

<style>
.rec-page { padding: 2rem 0; }
.rec-header {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 50%, #334155 100%);
    color: white;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 1.5rem;
}
.rec-section-title {
    font-weight: 800;
    font-size: 1.2rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}
.rec-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.25s;
    height: 100%;
}
.rec-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.1);
}
.rec-card-img {
    height: 140px;
    object-fit: cover;
    width: 100%;
}
.rec-card-body {
    padding: 1rem;
}
.rec-card-title {
    font-weight: 700;
    font-size: 0.9rem;
    color: var(--secondary);
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.rec-card-meta {
    font-size: 0.75rem;
    color: var(--muted);
}
.rec-badge {
    font-size: 0.65rem;
    padding: 2px 8px;
    border-radius: 50px;
    font-weight: 600;
}
.rec-teacher {
    font-size: 0.78rem;
    color: var(--muted);
}
.rec-rating {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.8rem;
}
.rec-rating .stars { color: #F59E0B; }
.history-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 0.75rem;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s;
}
.history-card:hover {
    background: var(--light);
    transform: translateX(4px);
}
.history-thumb {
    width: 60px;
    height: 40px;
    object-fit: cover;
    border-radius: 6px;
    flex-shrink: 0;
}
.empty-rec {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--muted);
}
.empty-rec i {
    font-size: 3rem;
    opacity: 0.3;
    margin-bottom: 1rem;
}
.filter-tabs {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}
.filter-tab {
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 0.82rem;
    font-weight: 600;
    border: 1.5px solid var(--border);
    background: white;
    color: var(--muted);
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}
.filter-tab:hover, .filter-tab.active {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}
</style>

<div class="container rec-page">

    <!-- Header -->
    <div class="rec-header text-center">
        <h1 class="fw-bold mb-2">
            <i class="fas fa-wand-magic-sparkles me-2"></i>Gợi ý khóa học cho bạn
        </h1>
        <p class="opacity-75 mb-0">Dựa trên sở thích và lịch sử học tập của bạn</p>
    </div>

    <?php if (empty($goi_y_list)): ?>
        <!-- Empty State -->
        <div class="empty-rec">
            <i class="fas fa-graduation-cap"></i>
            <h4>Chưa có gợi ý nào</h4>
            <p>Bắt đầu khám phá và học thêm khóa học để nhận được gợi ý phù hợp hơn!</p>
            <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php" class="btn btn-primary">
                <i class="fas fa-compass me-1"></i>Khám phá khóa học
            </a>
        </div>
    <?php else: ?>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="#" class="filter-tab active" data-filter="all">
                <i class="fas fa-sparkles me-1"></i>Tất cả
            </a>
            <a href="#" class="filter-tab" data-filter="cung_danh_muc">
                <i class="fas fa-layer-group me-1"></i>Cùng danh mục
            </a>
            <a href="#" class="filter-tab" data-filter="cung_giao_vien">
                <i class="fas fa-chalkboard-teacher me-1"></i>Cùng giảng viên
            </a>
            <a href="#" class="filter-tab" data-filter="pho_bien">
                <i class="fas fa-fire me-1"></i>Phổ biến
            </a>
            <a href="#" class="filter-tab" data-filter="moi_nhat">
                <i class="fas fa-bolt me-1"></i>Mới nhất
            </a>
        </div>

        <!-- Gợi ý chính: Tất cả -->
        <div id="rec-all" class="rec-section">
            <h3 class="rec-section-title">
                <i class="fas fa-star" style="color: var(--accent);"></i>
                Gợi ý hàng đầu cho bạn
                <span class="badge bg-secondary ms-2" style="font-size: 0.75rem;"><?php echo count($goi_y_list); ?></span>
            </h3>
            <div class="row g-3">
                <?php foreach ($goi_y_list as $k): ?>
                    <?php
                    $hinh = !empty($k['hinh_anh']) ? $k['hinh_anh'] : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=200&fit=crop';
                    $da_yeu_thich = in_array($k['id'], $yeu_thich_ids);
                    $diem = $k['diem_trung_binh'] ?? 0;
                    $so_dg = $k['so_danh_gia'] ?? 0;
                    $so_hv = $k['so_hoc_vien'] ?? 0;
                    $loai_color = $gy_model->getLoaiColor($k['loai_goi_y'] ?? 'pho_bien');
                    $loai_label = $gy_model->getLoaiLabel($k['loai_goi_y'] ?? 'pho_bien');
                    ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 rec-item" data-type="<?php echo $k['loai_goi_y'] ?? ''; ?>">
                        <div class="rec-card">
                            <div class="position-relative">
                                <img src="<?php echo $hinh; ?>" class="rec-card-img" alt="<?php echo htmlspecialchars($k['ten_khoa_hoc']); ?>"
                                     onerror="this.src='https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=200&fit=crop'">
                                <span class="position-absolute top-0 start-0 rec-badge m-2"
                                      style="background: <?php echo $loai_color; ?>20; color: <?php echo $loai_color; ?>;">
                                    <i class="fas fa-robot me-1"></i><?php echo $loai_label; ?>
                                </span>
                                <button class="btn btn-sm <?php echo $da_yeu_thich ? 'btn-danger' : 'btn-outline-danger'; ?> position-absolute bottom-0 end-0 m-2 p-1"
                                        onclick="toggleFavorite(<?php echo $k['id']; ?>, this)"
                                        style="border-radius: 20px; font-size: 0.7rem;">
                                    <i class="<?php echo $da_yeu_thich ? 'fas' : 'far'; ?> fa-heart"></i>
                                </button>
                            </div>
                            <div class="rec-card-body">
                                <div class="rec-card-title"><?php echo htmlspecialchars($k['ten_khoa_hoc']); ?></div>
                                <div class="rec-teacher mb-2">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($k['ten_giao_vien'] ?? 'Giảng viên'); ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                                    <div class="rec-rating">
                                        <span class="stars"><?php for ($i = 0; $i < 5; $i++) echo $i < round($diem) ? '★' : '☆'; ?></span>
                                        <span class="text-muted"><?php echo $diem > 0 ? number_format($diem, 1) : '0'; ?> (<?php echo $so_dg; ?>)</span>
                                    </div>
                                    <span class="badge bg-light text-dark" style="font-size: 0.7rem;">
                                        <i class="fas fa-users me-1"></i><?php echo $so_hv; ?> HV
                                    </span>
                                </div>
                                <a href="chi-tiet.php?id=<?php echo $k['id']; ?>&ref=recommendation"
                                   class="btn btn-primary btn-sm w-100 mt-2">
                                    <i class="fas fa-play me-1"></i>Xem khóa học
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cùng danh mục -->
        <?php if (!empty($cung_danh_muc)): ?>
            <div id="rec-cung_danh_muc" class="rec-section mt-4" style="display:none;">
                <h3 class="rec-section-title">
                    <i class="fas fa-layer-group" style="color: #4F46E5;"></i>
                    Vì bạn quan tâm danh mục này
                    <span class="badge bg-secondary ms-2" style="font-size: 0.75rem;"><?php echo count($cung_danh_muc); ?></span>
                </h3>
                <div class="row g-3">
                    <?php foreach ($cung_danh_muc as $k): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <?php include __DIR__ . '/../khoa-hoc/partial-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Cùng giảng viên -->
        <?php if (!empty($cung_giao_vien)): ?>
            <div id="rec-cung_giao_vien" class="rec-section mt-4" style="display:none;">
                <h3 class="rec-section-title">
                    <i class="fas fa-chalkboard-teacher" style="color: #10B981;"></i>
                    Từ giảng viên bạn theo dõi
                    <span class="badge bg-secondary ms-2" style="font-size: 0.75rem;"><?php echo count($cung_giao_vien); ?></span>
                </h3>
                <div class="row g-3">
                    <?php foreach ($cung_giao_vien as $k): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <?php include __DIR__ . '/../khoa-hoc/partial-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Phổ biến -->
        <?php if (!empty($pho_bien)): ?>
            <div id="rec-pho_bien" class="rec-section mt-4" style="display:none;">
                <h3 class="rec-section-title">
                    <i class="fas fa-fire" style="color: #F59E0B;"></i>
                    Khóa học phổ biến nhất
                    <span class="badge bg-secondary ms-2" style="font-size: 0.75rem;"><?php echo count($pho_bien); ?></span>
                </h3>
                <div class="row g-3">
                    <?php foreach ($pho_bien as $k): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <?php include __DIR__ . '/../khoa-hoc/partial-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Mới nhất -->
        <?php if (!empty($moi_nhat)): ?>
            <div id="rec-moi_nhat" class="rec-section mt-4" style="display:none;">
                <h3 class="rec-section-title">
                    <i class="fas fa-bolt" style="color: #3B82F6;"></i>
                    Khóa học mới nhất
                    <span class="badge bg-secondary ms-2" style="font-size: 0.75rem;"><?php echo count($moi_nhat); ?></span>
                </h3>
                <div class="row g-3">
                    <?php foreach ($moi_nhat as $k): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <?php include __DIR__ . '/../khoa-hoc/partial-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <!-- Lịch sử xem -->
    <?php if (!empty($lich_su_xem)): ?>
        <div class="mt-5">
            <h3 class="rec-section-title">
                <i class="fas fa-history" style="color: var(--muted);"></i>
                Đã xem gần đây
            </h3>
            <div class="row g-2">
                <?php foreach ($lich_su_xem as $item): ?>
                    <?php
                    $hinh = !empty($item['hinh_anh']) ? $item['hinh_anh'] : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=200&fit=crop';
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo $item['id']; ?>" class="text-decoration-none">
                            <div class="history-card">
                                <img src="<?php echo $hinh; ?>" class="history-thumb" alt=""
                                     onerror="this.src='https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=200&fit=crop'">
                                <div class="flex-grow-1 min-width-0">
                                    <div class="fw-semibold small" style="color: var(--secondary); display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?php echo htmlspecialchars($item['ten_khoa_hoc']); ?>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($item['ten_giao_vien'] ?? ''); ?>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.dataset.filter;

            // Update active tab
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Show/hide sections
            document.querySelectorAll('.rec-section').forEach(function(section) {
                section.style.display = 'none';
            });
            const target = document.getElementById('rec-' + filter);
            if (target) target.style.display = 'block';

            // Show all section if all
            if (filter === 'all') {
                document.getElementById('rec-all').style.display = 'block';
            }
        });
    });
});

// Toggle favorite
async function toggleFavorite(khoaHocId, btnElement) {
    try {
        const response = await fetch(`<?php echo VIEWS_URL; ?>/yeu-thich/controller.php?action=toggle&id_khoa_hoc=${khoaHocId}`);
        const data = await response.json();

        if (data.success) {
            const icon = btnElement.querySelector('i');
            if (data.data.action === 'added') {
                btnElement.classList.remove('btn-outline-danger');
                btnElement.classList.add('btn-danger');
                icon.classList.remove('far');
                icon.classList.add('fas');
            } else {
                btnElement.classList.remove('btn-danger');
                btnElement.classList.add('btn-outline-danger');
                icon.classList.remove('fas');
                icon.classList.add('far');
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
}
</script>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
