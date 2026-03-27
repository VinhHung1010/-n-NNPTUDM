<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';

$page_title = 'Đăng ký - ' . SITE_NAME;
$auth = new Auth();

// Nếu đã đăng nhập thì chuyển về trang chủ
if ($auth->kiemTraDangNhap()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten = trim($_POST['ho_ten'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mat_khau = $_POST['mat_khau'] ?? '';
    $xac_nhan_mat_khau = $_POST['xac_nhan_mat_khau'] ?? '';
    
    if (empty($ho_ten) || empty($email) || empty($mat_khau) || empty($xac_nhan_mat_khau)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } else {
        $result = $auth->dangKy($ho_ten, $email, $mat_khau, $xac_nhan_mat_khau);
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
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg mt-5">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold">Đăng ký tài khoản</h3>
                        <p class="text-muted">Tham gia cùng E-LEARNING ngay hôm nay!</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            <br>
                            <a href="<?php echo SITE_URL; ?>/tai-khoan/dang-nhap.php" class="alert-link">
                                Nhấn vào đây để đăng nhập
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="ho_ten" class="form-label">
                                    <i class="fas fa-user me-2"></i>Họ và tên
                                </label>
                                <input type="text" class="form-control" id="ho_ten" name="ho_ten" 
                                       value="<?php echo $_POST['ho_ten'] ?? ''; ?>" required
                                       placeholder="Nhập họ và tên của bạn">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $_POST['email'] ?? ''; ?>" required
                                       placeholder="Nhập địa chỉ email của bạn">
                            </div>

                            <div class="mb-3">
                                <label for="mat_khau" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Mật khẩu
                                </label>
                                <input type="password" class="form-control" id="mat_khau" name="mat_khau" required
                                       placeholder="Mật khẩu phải có ít nhất 6 ký tự">
                            </div>

                            <div class="mb-4">
                                <label for="xac_nhan_mat_khau" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Xác nhận mật khẩu
                                </label>
                                <input type="password" class="form-control" id="xac_nhan_mat_khau" 
                                       name="xac_nhan_mat_khau" required
                                       placeholder="Nhập lại mật khẩu">
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                                <i class="fas fa-user-plus me-2"></i>Đăng ký
                            </button>
                        </form>
                    <?php endif; ?>

                    <div class="text-center">
                        <p class="mb-0">
                            Đã có tài khoản? 
                            <a href="<?php echo SITE_URL; ?>/tai-khoan/dang-nhap.php" class="text-decoration-none">
                                <strong>Đăng nhập ngay</strong>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
