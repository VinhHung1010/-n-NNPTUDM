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
    $xac_nhan_mat_khau_moi = $_POST['xac_nhan_mat_khau_moi'] ?? '';
    
    if (empty($mat_khau_cu) || empty($mat_khau_moi) || empty($xac_nhan_mat_khau_moi)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } elseif ($mat_khau_moi !== $xac_nhan_mat_khau_moi) {
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

<div class="container">
    <div class="row justify-content-center mt-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-key me-2"></i>Đổi mật khẩu</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                            <a href="<?php echo VIEWS_URL; ?>/tai-khoan/ho-so.php" class="alert-link">
                                Quay về hồ sơ
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu cũ</label>
                                <input type="password" class="form-control" name="mat_khau_cu" required
                                       placeholder="Nhập mật khẩu hiện tại">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control" name="mat_khau_moi" required
                                       placeholder="Mật khẩu mới (ít nhất 6 ký tự)">
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Xác nhận mật khẩu mới</label>
                                <input type="password" class="form-control" name="xac_nhan_mat_khau_moi" required
                                       placeholder="Nhập lại mật khẩu mới">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Đổi mật khẩu
                            </button>
                        </form>
                    <?php endif; ?>

                    <hr class="my-4">

                    <div class="text-center">
                        <a href="<?php echo VIEWS_URL; ?>/tai-khoan/ho-so.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i>Quay về hồ sơ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
