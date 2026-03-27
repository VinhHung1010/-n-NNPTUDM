<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';

$page_title = 'Thêm Khóa học - ' . SITE_NAME;
$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

if (!in_array($_SESSION['nguoi_dung']['vai_tro'] ?? '', ['giao_vien', 'quan_tri'])) {
    header('Location: ' . VIEWS_URL . '/khoa-hoc/index.php');
    exit;
}

$kh_model = new KhoaHoc();
$danh_muc = $kh_model->layDanhMuc();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_khoa_hoc = trim($_POST['ten_khoa_hoc'] ?? '');
    $mo_ta        = trim($_POST['mo_ta'] ?? '');
    $hinh_anh     = trim($_POST['hinh_anh'] ?? '');
    $gia_tien     = (float)($_POST['gia_tien'] ?? 0);
    $id_danh_muc  = (int)($_POST['id_danh_muc'] ?? 0);

    if (empty($ten_khoa_hoc) || $id_danh_muc <= 0) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } else {
        $result = $kh_model->them($ten_khoa_hoc, $mo_ta, $hinh_anh, $gia_tien, $id_danh_muc, $_SESSION['nguoi_dung']['id']);
        if ($result['success']) {
            header('Location: index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php">Khóa học</a></li>
            <li class="breadcrumb-item active">Thêm mới</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card" style="border-radius:16px">
                <div class="card-header bg-white fw-bold py-3" style="border-radius:16px 16px 0 0">
                    <i class="fas fa-plus-circle me-2" style="color:var(--primary)"></i>Thêm Khóa học mới
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

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
                            <div class="form-text">Dán URL hình ảnh đại diện. Nên dùng ảnh từ Unsplash.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Giá tiền (VNĐ)</label>
                            <input type="number" class="form-control" name="gia_tien" min="0" step="1000"
                                   value="<?php echo htmlspecialchars($_POST['gia_tien'] ?? '0'); ?>">
                            <div class="form-text">Đặt <strong>0</strong> để miễn phí.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="index.php" class="btn btn-secondary flex-fill">
                                <i class="fas fa-arrow-left me-1"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-1"></i>Thêm khóa học
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
