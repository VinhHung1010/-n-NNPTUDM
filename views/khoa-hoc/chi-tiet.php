<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';
require_once __DIR__ . '/../../models/danh_gia.php';

$page_title = 'Chi tiết Khóa học - ' . SITE_NAME;
$auth = new Auth();
$khoa_hoc_model = new KhoaHoc();
$danh_gia_model = new DanhGia();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: index.php'); exit; }

$khoa_hoc = $khoa_hoc_model->layTheoId($id);
if (!$khoa_hoc) { header('Location: index.php'); exit; }

// Lấy thông tin đánh giá
$thong_ke = $danh_gia_model->tinhDiemTrungBinh($id);
$danh_sach_danh_gia = $danh_gia_model->layTheoKhoaHoc($id, 20);

// Đánh giá của user hiện tại (nếu có)
$nguoi_dung_hien_tai = $auth->layThongTinNguoiDung();
$danh_gia_cua_toi = null;
if ($nguoi_dung_hien_tai) {
    $danh_gia_cua_toi = $danh_gia_model->layTheoNguoiDung($nguoi_dung_hien_tai['id'], $id);
}

// Xử lý đăng ký
$thong_bao = '';
$thong_bao_type = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dang_ky'])) {
    if (!$auth->kiemTraDangNhap()) {
        header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
        exit;
    }
    $nd = $auth->layThongTinNguoiDung();

    $trang_thai_hien_tai = $khoa_hoc_model->daDangKy($nd['id'], $id);

    // Đã có đăng ký (chờ hoặc đã duyệt) → không tạo lại
    if ($trang_thai_hien_tai !== null) {
        $thong_bao = 'Bạn đã đăng ký khóa học này rồi.';
        $thong_bao_type = 'warning';
    } else {
        if ($khoa_hoc_model->dangKy($nd['id'], $id)) {
            $thong_bao = 'Đăng ký khóa học thành công! Đang chờ quản trị duyệt.';
            $thong_bao_type = 'info';
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

// Kiểm tra đăng ký - lấy đầy đủ trạng thái
$nguoi_dung_hien_tai = $auth->layThongTinNguoiDung();
$trang_thai_dk = $nguoi_dung_hien_tai
    ? $khoa_hoc_model->daDangKy($nguoi_dung_hien_tai['id'], $id)
    : null;

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
                    <?php if ($thong_ke['so_luong'] > 0): ?>
                    <div class="mt-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="text-warning me-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star" style="font-size: 0.9rem; opacity: <?php echo $i <= round($thong_ke['diem_tb']) ? '1' : '0.3'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="badge bg-warning text-dark">
                                <?php echo number_format($thong_ke['diem_tb'], 1); ?>
                            </span>
                            <span class="text-muted small">(<?php echo $thong_ke['so_luong']; ?> đánh giá)</span>
                        </div>
                    </div>
                    <?php endif; ?>
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

            <!-- Đánh giá & Bình luận -->
            <div class="mt-4">
                <h4 class="fw-bold mb-3">
                    <i class="fas fa-star me-2" style="color:var(--warning)"></i>Đánh giá & Bình luận
                </h4>

                <!-- Form đánh giá -->
                <?php if ($auth->kiemTraDangNhap()): ?>
                <div class="card mb-4" style="border-radius:16px" id="review-form">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <?php echo $danh_gia_cua_toi ? 'Cập nhật đánh giá của bạn' : 'Viết đánh giá của bạn'; ?>
                        </h5>
                        <form id="danhGiaForm">
                            <input type="hidden" name="id_khoa_hoc" value="<?php echo $id; ?>">

                            <!-- Rating stars -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Điểm số:</label>
                                <div class="rating-stars" id="ratingStars">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <span class="star <?php echo $danh_gia_cua_toi && $danh_gia_cua_toi['diem_so'] >= $i ? 'active' : ''; ?>"
                                              data-value="<?php echo $i; ?>">
                                            <i class="fas fa-star"></i>
                                        </span>
                                    <?php endfor; ?>
                                    <span class="rating-text ms-2 text-muted" id="ratingText">
                                        <?php echo $danh_gia_cua_toi ? $danh_gia_cua_toi['diem_so'] . ' sao' : 'Chọn số sao'; ?>
                                    </span>
                                </div>
                                <input type="hidden" name="diem_so" id="diemSoInput" value="<?php echo $danh_gia_cua_toi['diem_so'] ?? 0; ?>">
                            </div>

                            <!-- Nội dung bình luận -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nội dung (tùy chọn):</label>
                                <textarea name="noi_dung" class="form-control" rows="3"
                                          placeholder="Chia sẻ cảm nhận của bạn về khóa học..."><?php echo htmlspecialchars($danh_gia_cua_toi['noi_dung'] ?? ''); ?></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-paper-plane me-1"></i>
                                    <?php echo $danh_gia_cua_toi ? 'Cập nhật đánh giá' : 'Gửi đánh giá'; ?>
                                </button>
                                <?php if ($danh_gia_cua_toi): ?>
                                <button type="button" class="btn btn-outline-danger" onclick="xoaDanhGia(<?php echo $id; ?>)">
                                    <i class="fas fa-trash me-1"></i>Xóa đánh giá
                                </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Vui lòng <a href="<?php echo VIEWS_URL; ?>/tai-khoan/dang-nhap.php">đăng nhập</a> để viết đánh giá.
                </div>
                <?php endif; ?>

                <!-- Thống kê đánh giá -->
                <?php if ($thong_ke['so_luong'] > 0): ?>
                <div class="card mb-4" style="border-radius:16px">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center border-end">
                                <div class="display-4 fw-bold text-warning"><?php echo number_format($thong_ke['diem_tb'], 1); ?></div>
                                <div class="text-warning mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star" style="opacity: <?php echo $i <= round($thong_ke['diem_tb']) ? '1' : '0.3'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <div class="text-muted small"><?php echo $thong_ke['so_luong']; ?> đánh giá</div>
                            </div>
                            <div class="col-md-8">
                                <?php
                                $phan_bo = $danh_gia_model->thongKeSao($id);
                                $tong = $thong_ke['so_luong'];
                                for ($sao = 5; $sao >= 1; $sao--):
                                    $tyle = $tong > 0 ? round(($phan_bo[$sao] / $tong) * 100) : 0;
                                ?>
                                <div class="d-flex align-items-center mb-1">
                                    <span class="me-2 text-muted small" style="width:50px"><?php echo $sao; ?> sao</span>
                                    <div class="progress flex-grow-1" style="height:8px">
                                        <div class="progress-bar bg-warning" style="width:<?php echo $tyle; ?>%"></div>
                                    </div>
                                    <span class="ms-2 text-muted small" style="width:40px"><?php echo $tyle; ?>%</span>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Danh sách bình luận -->
                <?php if (!empty($danh_sach_danh_gia)): ?>
                <h5 class="mb-3">Đánh giá từ học viên (<?php echo count($danh_sach_danh_gia); ?>)</h5>
                <div class="review-list">
                    <?php foreach ($danh_sach_danh_gia as $dg): ?>
                    <div class="card mb-3" style="border-radius:12px">
                        <div class="card-body">
                            <div class="d-flex gap-3">
                                <div class="flex-shrink-0">
                                    <?php if (!empty($dg['anh_dai_dien'])): ?>
                                        <img src="<?php echo $dg['anh_dai_dien']; ?>" class="rounded-circle"
                                             style="width:48px;height:48px;object-fit:cover" alt="Avatar">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                                             style="width:48px;height:48px">
                                            <?php echo mb_strtoupper(mb_substr($dg['ho_ten'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="fw-semibold"><?php echo htmlspecialchars($dg['ho_ten']); ?></span>
                                            <?php if ($danh_gia_cua_toi && $danh_gia_cua_toi['id'] == $dg['id']): ?>
                                                <span class="badge bg-primary ms-2" style="font-size:0.65rem">Đánh giá của bạn</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-warning small">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star" style="opacity: <?php echo $i <= $dg['diem_so'] ? '1' : '0.3'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($dg['noi_dung'])): ?>
                                        <p class="mb-2 text-secondary"><?php echo nl2br(htmlspecialchars($dg['noi_dung'])); ?></p>
                                    <?php endif; ?>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($dg['ngay_tao'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-comment-dots fa-3x mb-3 opacity-25"></i>
                    <p>Chưa có đánh giá nào. Hãy là người đầu tiên!</p>
                </div>
                <?php endif; ?>
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
                        <?php if ($trang_thai_dk === 'da_xac_nhan'): ?>
                            <!-- Đã xác nhận → Học ngay -->
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
                        <?php elseif ($trang_thai_dk === 'cho_xu_ly'): ?>
                            <!-- Đang chờ duyệt -->
                            <div class="alert alert-warning py-2 mb-2">
                                <i class="fas fa-clock me-1"></i>
                                Đăng ký đang chờ duyệt.
                            </div>
                            <a href="<?php echo VIEWS_URL; ?>/home/index.php" class="btn btn-outline-secondary w-100 py-2">
                                <i class="fas fa-chart-line me-1"></i>Xem tiến độ
                            </a>
                        <?php elseif ($trang_thai_dk === 'da_huy'): ?>
                            <!-- Đã bị hủy → cho đăng ký lại -->
                            <form method="POST">
                                <button type="submit" name="dang_ky" class="btn btn-primary w-100 py-2 fw-semibold mb-2">
                                    <i class="fas fa-redo me-1"></i>Đăng ký lại
                                </button>
                            </form>
                            <a href="<?php echo VIEWS_URL; ?>/home/index.php" class="btn btn-outline-secondary w-100 py-2">
                                <i class="fas fa-chart-line me-1"></i>Xem tiến độ
                            </a>
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

<style>
.rating-stars .star {
    cursor: pointer;
    font-size: 1.5rem;
    color: #ccc;
    transition: color 0.2s;
}
.rating-stars .star i {
    color: #ccc;
}
.rating-stars .star.active i,
.rating-stars .star.active ~ .star i {
    /* Will be overridden by inline style below */
}
.rating-stars .star i {
    color: #e4e5e9;
    transition: color 0.2s;
}
.rating-stars .star.active i,
.rating-stars .star:hover i,
.rating-stars .star:hover ~ .star i {
    color: #f5b50a;
}
.rating-stars .star.active ~ .star i {
    color: #e4e5e9;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.rating-stars .star');
    const ratingText = document.getElementById('ratingText');
    const diemSoInput = document.getElementById('diemSoInput');

    // Initialize rating
    updateStars(parseInt(diemSoInput.value) || 0);

    stars.forEach(star => {
        star.addEventListener('click', function() {
            const value = parseInt(this.dataset.value);
            diemSoInput.value = value;
            updateStars(value);
        });

        star.addEventListener('mouseenter', function() {
            const value = parseInt(this.dataset.value);
            highlightStars(value);
        });

        star.addEventListener('mouseleave', function() {
            const current = parseInt(diemSoInput.value) || 0;
            updateStars(current);
        });
    });

    function updateStars(value) {
        stars.forEach((star, index) => {
            const starValue = 5 - index;
            if (starValue <= value) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });

        const labels = ['', 'Rất tệ', 'Tệ', 'Bình thường', 'Tốt', 'Xuất sắc'];
        ratingText.textContent = value > 0 ? value + ' sao - ' + labels[value] : 'Chọn số sao';
    }

    function highlightStars(value) {
        stars.forEach((star, index) => {
            const starValue = 5 - index;
            if (starValue <= value) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }

    // Form submit
    const form = document.getElementById('danhGiaForm');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const diemSo = parseInt(document.getElementById('diemSoInput').value);
            if (diemSo < 1 || diemSo > 5) {
                alert('Vui lòng chọn số sao từ 1 đến 5!');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang gửi...';

            const formData = new FormData(form);
            formData.set('diem_so', diemSo);

            try {
                const response = await fetch('<?php echo VIEWS_URL; ?>/danh-gia/controller.php?action=submit', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra. Vui lòng thử lại!');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Gửi đánh giá';
            }
        });
    }
});

async function xoaDanhGia(idKhoaHoc) {
    if (!confirm('Bạn có chắc muốn xóa đánh giá này?')) return;

    try {
        const response = await fetch('<?php echo VIEWS_URL; ?>/danh-gia/controller.php?action=delete&id_khoa_hoc=' + idKhoaHoc);
        const data = await response.json();

        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra!');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Có lỗi xảy ra. Vui lòng thử lại!');
    }
}
</script>
