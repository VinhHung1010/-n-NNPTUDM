<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/auth.php';

$page_title = 'Tiến độ học tập - ' . SITE_NAME;
$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . BASE_PATH . '/tai-khoan/dang-nhap.php');
    exit;
}

include __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-5">
    <div class="alert alert-info text-center">
        <i class="fas fa-chart-line fa-2x mb-3"></i>
        <h4>Tính năng đang phát triển</h4>
        <p class="mb-0">Trang theo dõi tiến độ học tập sẽ được cập nhật sau.</p>
        <a href="<?php echo HOME_URL; ?>" class="btn btn-primary mt-3">Về trang chủ</a>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
