<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';

$page_title = 'Đổi mật khẩu - ' . SITE_NAME;
$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$nguoi_dung = $auth->layThongTinNguoiDung();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mat_khau_cu = $_POST['mat_khau_cu'] ?? '';
    $mat_khau_moi = $_POST['mat_khau_moi'] ?? '';
    $xac_nhan = $_POST['xac_nhan_mat_khau_moi'] ?? '';

    if (empty($mat_khau_cu) || empty($mat_khau_moi) || empty($xac_nhan)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } elseif ($mat_khau_moi !== $xac_nhan) {
        $error = 'Mật khẩu mới xác nhận không khớp!';
    } elseif (strlen($mat_khau_moi) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
    } else {
        $result = $auth->doiMatKhau($nguoi_dung['id'], $mat_khau_cu, $mat_khau_moi);
        if ($result['success']) {
            $success = $result['message'];
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
            <li class="breadcrumb-item"><a href="<?php echo VIEWS_URL; ?>/tai-khoan/ho-so.php">Hồ sơ</a></li>
            <li class="breadcrumb-item active">Đổi mật khẩu</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card" style="border-radius:16px">
                <div class="card-header bg-white fw-bold py-3" style="border-radius:16px 16px 0 0">
                    <i class="fas fa-key me-2" style="color:var(--primary)"></i>Đổi mật khẩu
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success text-center py-4">
                            <i class="fas fa-check-circle fa-2x mb-3" style="color:var(--success)"></i>
                            <h5 class="mb-2"><?php echo $success; ?></h5>
                            <a href="<?php echo VIEWS_URL; ?>/tai-khoan/ho-so.php" class="btn btn-outline-primary mt-2">
                                <i class="fas fa-arrow-left me-1"></i>Quay về hồ sơ
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mật khẩu cũ <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="mat_khau_cu" required
                                       placeholder="Nhập mật khẩu hiện tại">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mật khẩu mới <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="mat_khau_moi" required
                                       placeholder="Ít nhất 6 ký tự" minlength="6">
                                <div class="form-text">Mật khẩu mới phải có ít nhất 6 ký tự.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="xac_nhan_mat_khau_moi" required
                                       placeholder="Nhập lại mật khẩu mới">
                            </div>

                            <div class="d-flex gap-2">
                                <a href="<?php echo VIEWS_URL; ?>/tai-khoan/ho-so.php" class="btn btn-secondary flex-fill">
                                    <i class="fas fa-arrow-left me-1"></i>Hủy
                                </a>
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="fas fa-save me-1"></i>Đổi mật khẩu
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
