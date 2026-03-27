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
    } else {
        $error = 'Họ tên không được để trống!';
    }
}

$role_labels = [
    'quan_tri'  => ['Quản trị viên', 'danger'],
    'giao_vien' => ['Giáo viên', 'warning text-dark'],
    'hoc_vien'  => ['Học viên', 'primary'],
];

[$role_label, $role_color] = $role_labels[$nguoi_dung['vai_tro']] ?? ['Học viên', 'primary'];

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container mt-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/index.php">Trang chủ</a></li>
            <li class="breadcrumb-item active">Hồ sơ</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <?php if ($error): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Avatar card -->
                <div class="col-md-4">
                    <div class="card text-center py-4" style="border-radius:16px">
                        <div class="card-body">
                            <div class="mb-3">
                                <div style="width:90px;height:90px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;margin:0 auto">
                                    <i class="fas fa-user fa-3x" style="color:#fff"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($nguoi_dung['ho_ten']); ?></h5>
                            <span class="badge bg-<?php echo $role_color; ?>"><?php echo $role_label; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <div class="col-md-8">
                    <div class="card" style="border-radius:16px">
                        <div class="card-header bg-white fw-bold py-3" style="border-radius:16px 16px 0 0">
                            <i class="fas fa-id-card me-2" style="color:var(--primary)"></i>Thông tin cá nhân
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Họ và tên</label>
                                    <input type="text" class="form-control" name="ho_ten"
                                           value="<?php echo htmlspecialchars($nguoi_dung['ho_ten']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control"
                                           value="<?php echo htmlspecialchars($nguoi_dung['email']); ?>" readonly>
                                    <div class="form-text">Email không thể thay đổi.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Vai trò</label>
                                    <input type="text" class="form-control"
                                           value="<?php echo $role_label; ?>" readonly>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Lưu thay đổi
                                    </button>
                                    <a href="<?php echo VIEWS_URL; ?>/tai-khoan/doi-mat-khau.php"
                                       class="btn btn-outline-secondary">
                                        <i class="fas fa-key me-1"></i>Đổi mật khẩu
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
