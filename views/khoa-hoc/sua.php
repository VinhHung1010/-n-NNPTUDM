<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';

$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

if (($_SESSION['nguoi_dung']['vai_tro'] ?? '') !== 'quan_tri') {
    header('Location: ' . VIEWS_URL . '/khoa-hoc/index.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: index.php'); exit; }

$kh_model = new KhoaHoc();
$khoa_hoc = $kh_model->layTheoId($id);
if (!$khoa_hoc) { header('Location: index.php'); exit; }

$danh_muc = $kh_model->layDanhMuc();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_khoa_hoc = trim($_POST['ten_khoa_hoc'] ?? '');
    $mo_ta        = trim($_POST['mo_ta'] ?? '');
    $hinh_anh     = trim($_POST['hinh_anh'] ?? '');
    $gia_tien     = (float)($_POST['gia_tien'] ?? 0);
    $id_danh_muc  = (int)($_POST['id_danh_muc'] ?? 0);
    $trang_thai   = $_POST['trang_thai'] ?? 'ban_nhap';

    if (empty($ten_khoa_hoc) || $id_danh_muc <= 0) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } else {
        $result = $kh_model->sua($id, $ten_khoa_hoc, $mo_ta, $hinh_anh, $gia_tien, $id_danh_muc, $trang_thai);
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
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php">Khóa học</a></li>
            <li class="breadcrumb-item"><a href="chi-tiet.php?id=<?php echo $id; ?>"><?php echo htmlspecialchars($khoa_hoc['ten_khoa_hoc']); ?></a></li>
            <li class="breadcrumb-item active">Sửa</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card" style="border-radius:16px">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-edit me-2" style="color:var(--warning,#D97706)"></i>Sửa Khóa học</span>
                    <a href="chi-tiet.php?id=<?php echo $id; ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tên khóa học <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ten_khoa_hoc" required
                                   value="<?php echo htmlspecialchars($khoa_hoc['ten_khoa_hoc']); ?>"
                                   placeholder="Nhập tên khóa học">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Danh mục <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_danh_muc" required>
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
                                      placeholder="Mô tả khóa học..."><?php
                                      echo htmlspecialchars($khoa_hoc['mo_ta'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Link hình ảnh</label>
                            <input type="url" class="form-control" name="hinh_anh"
                                   value="<?php echo htmlspecialchars($khoa_hoc['hinh_anh'] ?? ''); ?>"
                                   placeholder="https://example.com/image.jpg">
                            <div class="form-text">Dán URL hình ảnh đại diện cho khóa học.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Giá tiền (VNĐ)</label>
                                <input type="number" class="form-control" name="gia_tien" min="0" step="1000"
                                       value="<?php echo $khoa_hoc['gia_tien']; ?>">
                                <div class="form-text">Đặt 0 cho khóa học miễn phí.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Trạng thái</label>
                                <select class="form-select" name="trang_thai">
                                    <option value="ban_nhap"   <?php if ($khoa_hoc['trang_thai'] === 'ban_nhap')   echo 'selected'; ?>>Bản nháp</option>
                                    <option value="da_duyet"   <?php if ($khoa_hoc['trang_thai'] === 'da_duyet')   echo 'selected'; ?>>Đã duyệt</option>
                                    <option value="bi_an"      <?php if ($khoa_hoc['trang_thai'] === 'bi_an')      echo 'selected'; ?>>Đã ẩn</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="chi-tiet.php?id=<?php echo $id; ?>" class="btn btn-secondary">Hủy</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
