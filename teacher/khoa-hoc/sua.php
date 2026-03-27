<?php
$page_title = 'Sửa khóa học - Giáo viên';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/models/khoa_hoc.php';

$kh_model = new KhoaHoc();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$khoa_hoc = $kh_model->layTheoId($id);
if (!$khoa_hoc) { header('Location: index.php'); exit; }
if ((int)$khoa_hoc['id_nguoi_tao'] !== (int)$nguoi_dung['id']) { header('Location: index.php'); exit; }

$danh_muc = $kh_model->layDanhMuc();
$thong_bao = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['ten_khoa_hoc'] ?? '');
    $mo_ta = trim($_POST['mo_ta'] ?? '');
    $hinh_anh = trim($_POST['hinh_anh'] ?? '');
    $gia = (float)($_POST['gia_tien'] ?? 0);
    $id_dm = (int)($_POST['id_danh_muc'] ?? 0);

    if (empty($ten) || $id_dm <= 0) {
        $thong_bao = '<div class="alert alert-danger">Vui lòng điền đầy đủ thông tin bắt buộc!</div>';
    } else {
        $result = $kh_model->sua($id, $ten, $mo_ta, $hinh_anh, $gia, $id_dm, $khoa_hoc['trang_thai']);
        if ($result['success']) {
            $thong_bao = '<div class="alert alert-success">Cập nhật khóa học thành công!</div>';
            $khoa_hoc = $kh_model->layTheoId($id); // reload
        } else {
            $thong_bao = '<div class="alert alert-danger">' . htmlspecialchars($result['message']) . '</div>';
        }
    }
}

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="tv-topbar">
    <div>
        <h1 class="h4 mb-0">
            <i class="fas fa-pen me-2 text-success"></i>Sửa khóa học
        </h1>
        <p class="text-muted small mb-0">
            <a href="index.php" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
        </p>
    </div>
</div>

<?php echo $thong_bao; ?>

<div class="row g-4">
    <!-- Form -->
    <div class="col-lg-7">
        <div class="card tv-stat-card">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-info-circle me-1 text-success"></i>Thông tin khóa học
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên khóa học <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="ten_khoa_hoc" required
                               value="<?php echo htmlspecialchars($khoa_hoc['ten_khoa_hoc']); ?>"
                               placeholder="VD: Lập trình Python cơ bản">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Danh mục <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_danh_muc" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($danh_muc as $dm): ?>
                                <option value="<?php echo $dm['id']; ?>"
                                    <?php if ($khoa_hoc['id_danh_muc'] == $dm['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($dm['ten_danh_muc']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả</label>
                        <textarea class="form-control" name="mo_ta" rows="4"
                                  placeholder="Mô tả nội dung khóa học..."><?php
                                  echo htmlspecialchars($khoa_hoc['mo_ta'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Link hình ảnh</label>
                        <input type="url" class="form-control" name="hinh_anh"
                               value="<?php echo htmlspecialchars($khoa_hoc['hinh_anh'] ?? ''); ?>"
                               placeholder="https://example.com/image.jpg">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Giá tiền (VNĐ)</label>
                        <input type="number" class="form-control" name="gia_tien" min="0" step="1000"
                               value="<?php echo (int)$khoa_hoc['gia_tien']; ?>">
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-save me-1"></i>Lưu thay đổi
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar: trạng thái + quick actions -->
    <div class="col-lg-5">
        <?php
        $tt_map = [
            'ban_nhap' => ['bg-secondary', 'Bản nháp', 'Khóa học chưa được gửi duyệt.'],
            'da_duyet'  => ['bg-success', 'Đã duyệt', 'Khóa học đã được công khai.'],
            'bi_an'     => ['bg-danger',  'Đã ẩn',    'Khóa học bị ẩn khỏi trang chủ.'],
        ];
        [$tt_bg, $tt_label, $tt_desc] = $tt_map[$khoa_hoc['trang_thai']] ?? ['bg-secondary','N/A',''];
        ?>
        <div class="card tv-stat-card mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-eye me-1 text-success"></i>Trạng thái
            </div>
            <div class="card-body">
                <span class="badge <?php echo $tt_bg; ?> mb-2"><?php echo $tt_label; ?></span>
                <p class="text-muted small mb-3"><?php echo $tt_desc; ?></p>
                <?php if ($khoa_hoc['trang_thai'] === 'ban_nhap'): ?>
                    <div class="alert alert-warning small mb-0 py-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Liên hệ quản trị để duyệt khóa học.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card tv-stat-card">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-bolt me-1 text-success"></i>Thao tác nhanh
            </div>
            <div class="card-body d-grid gap-2">
                <a href="<?php echo SITE_URL; ?>/teacher/bai-hoc/index.php?khoa_hoc=<?php echo $id; ?>"
                   class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i>Thêm bài học
                </a>
                <a href="<?php echo SITE_URL; ?>/teacher/quiz/index.php?khoa_hoc=<?php echo $id; ?>"
                   class="btn btn-outline-success btn-sm">
                    <i class="fas fa-plus me-1"></i>Thêm Quiz
                </a>
                <a href="<?php echo SITE_URL; ?>/teacher/hoc-vien/index.php?khoa_hoc=<?php echo $id; ?>"
                   class="btn btn-outline-success btn-sm">
                    <i class="fas fa-user-graduate me-1"></i>Xem học viên
                </a>
                <a href="<?php echo SITE_URL; ?>/views/khoa-hoc/chi-tiet.php?id=<?php echo $id; ?>"
                   class="btn btn-outline-secondary btn-sm" target="_blank">
                    <i class="fas fa-eye me-1"></i>Xem công khai
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/layout_end.php'; ?>
