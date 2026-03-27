<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';

$page_title = 'Đăng nhập - ' . SITE_NAME;
$auth = new Auth();

// Nếu đã đăng nhập thì chuyển về đúng khu vực
if ($auth->kiemTraDangNhap()) {
    if ($auth->laQuanTri()) {
        header('Location: ' . SITE_URL . '/admin/index.php');
    } else {
        header('Location: ' . SITE_URL . '/index.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mat_khau = $_POST['mat_khau'] ?? '';
    
    if (empty($email) || empty($mat_khau)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } else {
        $result = $auth->dangNhap($email, $mat_khau);
        if ($result['success']) {
            if (($result['user']['vai_tro'] ?? '') === 'quan_tri') {
                header('Location: ' . SITE_URL . '/admin/index.php');
            } else {
                header('Location: ' . SITE_URL . '/index.php');
            }
            exit;
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
                        <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold">Chào mừng bạn quay trở lại!</h3>
                        <p class="text-muted">Đăng nhập để tiếp tục học tập</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
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
                                   placeholder="Nhập mật khẩu của bạn">
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">
                            Chưa có tài khoản? 
                            <a href="<?php echo VIEWS_URL; ?>/tai-khoan/dang-ky.php" class="text-decoration-none">
                                <strong>Đăng ký ngay</strong>
                            </a>
                        </p>
                    </div>

                    <hr class="my-4">

                    <div class="text-center text-muted small">
                        <p class="mb-2"><strong>Tài khoản demo:</strong></p>
                        <p class="mb-1">Admin: admin@elearning.com</p>
                        <p class="mb-1">Giáo viên: giaovien@elearning.com</p>
                        <p class="mb-0"><strong>Mật khẩu:</strong> password</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
