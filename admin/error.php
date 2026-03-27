<?php
$code = isset($_GET['code']) ? (int)$_GET['code'] : 403;
$titles = [403 => 'Truy cập bị từ chối', 404 => 'Trang không tìm thấy', 500 => 'Lỗi máy chủ'];
$msgs   = [
    403 => 'Bạn không có quyền truy cập trang này. Vui lòng liên hệ quản trị viên.',
    404 => 'Trang bạn đang tìm kiếm không tồn tại hoặc đã bị di chuyển.',
    500 => 'Đã xảy ra lỗi phía máy chủ. Vui lòng thử lại sau.',
];
$icons  = [403 => 'fa-ban', 404 => 'fa-magnifying-glass', 500 => 'fa-server'];
$colors = [403 => '#DC2626', 404 => '#D97706', 500 => '#7C3AED'];

$title = $titles[$code] ?? 'Lỗi';
$msg   = $msgs[$code]   ?? $msgs[404];
$icon  = $icons[$code]  ?? $icons[404];
$color = $colors[$code] ?? $colors[404];

$page_title = $title;
include __DIR__ . '/partials/layout_start.php';
?>

<div class="d-flex flex-column align-items-center justify-content-center" style="min-height:60vh">
    <div class="text-center">
        <div class="mb-4" style="font-size:5rem;line-height:1;color:<?php echo $color ?>;opacity:0.15;font-weight:900;position:absolute;user-select:none">
            <?php echo $code; ?>
        </div>
        <i class="fas <?php echo $icon; ?> fa-4x mb-4" style="color:<?php echo $color ?>;position:relative"></i>
        <h2 class="fw-bold mb-2"><?php echo $title; ?></h2>
        <p class="text-muted mb-4" style="max-width:400px;margin:0 auto">
            <?php echo $msg; ?>
        </p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-primary px-4">
                <i class="fas fa-gauge-high me-1"></i>Về Bảng điều khiển
            </a>
            <a href="<?php echo SITE_URL; ?>/views/home/index.php" class="btn btn-outline-secondary px-4">
                <i class="fas fa-house me-1"></i>Trang chủ
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/layout_end.php'; ?>
