<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';

$page_title = 'Hồ sơ cá nhân - ' . SITE_NAME;
$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$nguoi_dung = $auth->layThongTinNguoiDung();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten = trim($_POST['ho_ten'] ?? '');
    
    if (!empty($ho_ten)) {
        if ($auth->capNhatHoSo($nguoi_dung['id'], $ho_ten)) {
            $success = 'Cập nhật hồ sơ thành công!';
            $_SESSION['nguoi_dung']['ho_ten'] = $ho_ten;
            $nguoi_dung['ho_ten'] = $ho_ten;
        } else {
            $error = 'Cập nhật hồ sơ thất bại!';
        }
    }
}

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container">
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user me-2"></i>Hồ sơ cá nhân</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-circle fa-10x text-secondary"></i>
                            </div>
                            <span class="badge bg-<?php 
                                echo $nguoi_dung['vai_tro'] === 'quan_tri' ? 'danger' : 
                                    ($nguoi_dung['vai_tro'] === 'giao_vien' ? 'warning text-dark' : 'primary');
                            ?>">
                                <?php 
                                echo $nguoi_dung['vai_tro'] === 'quan_tri' ? 'Quản trị viên' : 
                                    ($nguoi_dung['vai_tro'] === 'giao_vien' ? 'Giáo viên' : 'Học viên');
                                ?>
                            </span>
                        </div>
                        <div class="col-md-8">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label">Họ và tên</label>
                                    <input type="text" class="form-control" name="ho_ten" 
                                           value="<?php echo $nguoi_dung['ho_ten']; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" 
                                           value="<?php echo $nguoi_dung['email']; ?>" readonly>
                                    <small class="text-muted">Email không thể thay đổi</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Vai trò</label>
                                    <input type="text" class="form-control" 
                                           value="<?php 
                                               echo $nguoi_dung['vai_tro'] === 'quan_tri' ? 'Quản trị viên' : 
                                                   ($nguoi_dung['vai_tro'] === 'giao_vien' ? 'Giáo viên' : 'Học viên');
                                           ?>" readonly>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                                </button>
                                <a href="<?php echo VIEWS_URL; ?>/tai-khoan/doi-mat-khau.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-key me-2"></i>Đổi mật khẩu
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
