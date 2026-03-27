<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';

$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . SITE_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

if ($_SESSION['nguoi_dung']['vai_tro'] !== 'quan_tri') {
    header('Location: ' . SITE_URL . '/khoa-hoc/index.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$khoa_hoc_model = new KhoaHoc();
$khoa_hoc = $khoa_hoc_model->layTheoId($id);

if (!$khoa_hoc) {
    header('Location: index.php');
    exit;
}

$danh_muc = $khoa_hoc_model->layDanhMuc();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_khoa_hoc = trim($_POST['ten_khoa_hoc'] ?? '');
    $mo_ta = trim($_POST['mo_ta'] ?? '');
    $hinh_anh = trim($_POST['hinh_anh'] ?? '');
    $gia_tien = (float)($_POST['gia_tien'] ?? 0);
    $id_danh_muc = (int)($_POST['id_danh_muc'] ?? 0);
    $trang_thai = $_POST['trang_thai'] ?? 'ban_nhap';
    
    if (empty($ten_khoa_hoc) || $id_danh_muc <= 0) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } else {
        $result = $khoa_hoc_model->sua(
            $id,
            $ten_khoa_hoc, 
            $mo_ta, 
            $hinh_anh, 
            $gia_tien, 
            $id_danh_muc,
            $trang_thai
        );
        
        if ($result['success']) {
            header('Location: chi-tiet.php?id=' . $id);
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$page_title = 'Sửa Khóa học - ' . SITE_NAME;
include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Sửa Khóa học</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Tên khóa học <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ten_khoa_hoc" required
                                   value="<?php echo $khoa_hoc['ten_khoa_hoc']; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_danh_muc" required>
                                <?php foreach ($danh_muc as $dm): ?>
                                    <option value="<?php echo $dm['id']; ?>" 
                                            <?php echo ($khoa_hoc['id_danh_muc'] == $dm['id']) ? 'selected' : ''; ?>>
                                        <?php echo $dm['ten_danh_muc']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="mo_ta" rows="5"><?php echo $khoa_hoc['mo_ta'] ?? ''; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Link hình ảnh</label>
                            <input type="url" class="form-control" name="hinh_anh"
                                   value="<?php echo $khoa_hoc['hinh_anh'] ?? ''; ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giá tiền (VNĐ)</label>
                                <input type="number" class="form-control" name="gia_tien" min="0" step="1000"
                                       value="<?php echo $khoa_hoc['gia_tien']; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-select" name="trang_thai">
                                    <option value="ban_nhap" <?php echo ($khoa_hoc['trang_thai'] === 'ban_nhap') ? 'selected' : ''; ?>>
                                        Bản nháp
                                    </option>
                                    <option value="da_duyet" <?php echo ($khoa_hoc['trang_thai'] === 'da_duyet') ? 'selected' : ''; ?>>
                                        Đã duyệt
                                    </option>
                                    <option value="bi_an" <?php echo ($khoa_hoc['trang_thai'] === 'bi_an') ? 'selected' : ''; ?>>
                                        Bị ẩn
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="chi-tiet.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-2"></i>Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
