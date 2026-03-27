<?php
$page_title = 'Tạo khóa học - Giáo viên';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/models/khoa_hoc.php';

$kh_model = new KhoaHoc();
$danh_muc = $kh_model->layDanhMuc();
$thong_bao = '';
$thong_bao_type = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['ten_khoa_hoc'] ?? '');
    $mo_ta = trim($_POST['mo_ta'] ?? '');
    $hinh_anh = trim($_POST['hinh_anh'] ?? '');
    $gia = (float)($_POST['gia_tien'] ?? 0);
    $id_dm = (int)($_POST['id_danh_muc'] ?? 0);

    if (empty($ten) || $id_dm <= 0) {
        $thong_bao = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } else {
        $result = $kh_model->them($ten, $mo_ta, $hinh_anh, $gia, $id_dm, $nguoi_dung['id']);
        if ($result['success']) {
            header('Location: sua.php?id=' . $result['id'] . '?created=1');
            exit;
        } else {
            $thong_bao = $result['message'];
        }
    }
}

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="tv-topbar">
    <div>
        <h1 class="h4 mb-0">
            <i class="fas fa-plus me-2 text-success"></i>Tạo khóa học mới
        </h1>
        <p class="text-muted small mb-0">
            <a href="index.php" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
        </p>
    </div>
</div>

<?php if ($thong_bao): ?>
    <div class="alert alert-<?php echo $thong_bao_type; ?>"><?php echo $thong_bao; ?></div>
<?php endif; ?>

<div class="card tv-stat-card" style="max-width:640px">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Tên khóa học <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="ten_khoa_hoc" required
                       value="<?php echo htmlspecialchars($_POST['ten_khoa_hoc'] ?? ''); ?>"
                       placeholder="VD: Lập trình Python cơ bản">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Danh mục <span class="text-danger">*</span></label>
                <select class="form-select" name="id_danh_muc" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($danh_muc as $dm): ?>
                        <option value="<?php echo $dm['id']; ?>"
                            <?php if (isset($_POST['id_danh_muc']) && (int)$_POST['id_danh_muc'] === $dm['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($dm['ten_danh_muc']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Mô tả</label>
                <textarea class="form-control" name="mo_ta" rows="4"
                          placeholder="Mô tả nội dung khóa học..."><?php
                          echo htmlspecialchars($_POST['mo_ta'] ?? ''); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Link hình ảnh</label>
                <input type="url" class="form-control" name="hinh_anh"
                       value="<?php echo htmlspecialchars($_POST['hinh_anh'] ?? ''); ?>"
                       placeholder="https://example.com/image.jpg">
                <div class="form-text">Dán URL hình ảnh đại diện.</div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Giá tiền (VNĐ)</label>
                <input type="number" class="form-control" name="gia_tien" min="0" step="1000"
                       value="<?php echo htmlspecialchars($_POST['gia_tien'] ?? '0'); ?>">
                <div class="form-text">Đặt <strong>0</strong> để miễn phí.</div>
            </div>

            <div class="alert alert-info small mb-4">
                <i class="fas fa-info-circle me-1"></i>
                Khóa học sẽ được tạo ở trạng thái <strong>Bản nháp</strong>.
                Sau khi tạo xong, bạn có thể thêm bài học và Quiz.
            </div>

            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-secondary flex-fill">Hủy</a>
                <button type="submit" class="btn btn-success flex-fill">
                    <i class="fas fa-save me-1"></i>Tạo khóa học
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../partials/layout_end.php'; ?>
