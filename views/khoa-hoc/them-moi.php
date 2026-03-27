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

if (!in_array($_SESSION['nguoi_dung']['vai_tro'], ['giao_vien', 'quan_tri'])) {
    header('Location: ' . VIEWS_URL . '/khoa-hoc/index.php');
    exit;
}

$khoa_hoc_model = new KhoaHoc();
$danh_muc = $khoa_hoc_model->layDanhMuc();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_khoa_hoc = trim($_POST['ten_khoa_hoc'] ?? '');
    $mo_ta = trim($_POST['mo_ta'] ?? '');
    $hinh_anh = trim($_POST['hinh_anh'] ?? '');
    $gia_tien = (float)($_POST['gia_tien'] ?? 0);
    $id_danh_muc = (int)($_POST['id_danh_muc'] ?? 0);
    
    if (empty($ten_khoa_hoc) || $id_danh_muc <= 0) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } else {
        $result = $khoa_hoc_model->them(
            $ten_khoa_hoc, 
            $mo_ta, 
            $hinh_anh, 
            $gia_tien, 
            $id_danh_muc, 
            $_SESSION['nguoi_dung']['id']
        );
        
        if ($result['success']) {
            $success = $result['message'];
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
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Thêm Khóa học mới</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Tên khóa học <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ten_khoa_hoc" required
                                   value="<?php echo $_POST['ten_khoa_hoc'] ?? ''; ?>"
                                   placeholder="Nhập tên khóa học">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_danh_muc" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($danh_muc as $dm): ?>
                                    <option value="<?php echo $dm['id']; ?>" 
                                            <?php echo (isset($_POST['id_danh_muc']) && $_POST['id_danh_muc'] == $dm['id']) ? 'selected' : ''; ?>>
                                        <?php echo $dm['ten_danh_muc']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="mo_ta" rows="5"
                                      placeholder="Nhập mô tả khóa học"><?php echo $_POST['mo_ta'] ?? ''; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Link hình ảnh</label>
                            <input type="url" class="form-control" name="hinh_anh"
                                   value="<?php echo $_POST['hinh_anh'] ?? ''; ?>"
                                   placeholder="https://example.com/image.jpg">
                            <small class="text-muted">Nhập URL hình ảnh khóa học</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Giá tiền (VNĐ)</label>
                            <input type="number" class="form-control" name="gia_tien" min="0" step="1000"
                                   value="<?php echo $_POST['gia_tien'] ?? '0'; ?>"
                                   placeholder="0 = Miễn phí">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Thêm khóa học
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
