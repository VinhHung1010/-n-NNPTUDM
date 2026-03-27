<?php
$page_title = 'Khóa học - Giáo viên';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/models/khoa_hoc.php';

$kh_model = new KhoaHoc();
$thong_bao = '';

// Xử lý xóa
if (isset($_GET['action']) && $_GET['action'] === 'xoa' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $k = $kh_model->layTheoId($id);
    if ($k && (int)$k['id_nguoi_tao'] === (int)$nguoi_dung['id']) {
        $result = $kh_model->xoa($id);
        $thong_bao = $result['success']
            ? '<div class="alert alert-success">Đã xóa khóa học.</div>'
            : '<div class="alert alert-danger">' . htmlspecialchars($result['message']) . '</div>';
    }
}

$khoa_list = $kh_model->layKhoaHocCuaGiaoVien($nguoi_dung['id']);

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="tv-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-0"><i class="fas fa-book me-2 text-success"></i>Quản lý Khóa học</h1>
        <p class="text-muted small mb-0"><?php echo count($khoa_list); ?> khóa học</p>
    </div>
    <a href="them-moi.php" class="btn btn-success">
        <i class="fas fa-plus me-1"></i>Tạo khóa học
    </a>
</div>

<?php echo $thong_bao; ?>

<?php if (empty($khoa_list)): ?>
    <div class="card tv-stat-card text-center py-5">
        <div class="card-body">
            <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Bạn chưa tạo khóa học nào.</h4>
            <a href="them-moi.php" class="btn btn-success mt-2">
                <i class="fas fa-plus me-1"></i>Tạo khóa học đầu tiên
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($khoa_list as $k): ?>
            <?php
            $tt_map = [
                'ban_nhap' => ['bg-secondary', 'Bản nháp'],
                'da_duyet' => ['bg-success', 'Đã duyệt'],
                'bi_an'    => ['bg-danger',  'Đã ẩn'],
            ];
            [$bg, $tt_text] = $tt_map[$k['trang_thai']] ?? ['bg-secondary', 'N/A'];
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card tv-stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge <?php echo $bg; ?>"><?php echo $tt_text; ?></span>
                            <div class="d-flex gap-1">
                                <a href="sua.php?id=<?php echo $k['id']; ?>" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a href="?action=xoa&id=<?php echo $k['id']; ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Xóa khóa học &quot;<?php echo htmlspecialchars($k['ten_khoa_hoc']); ?>&quot;?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($k['ten_khoa_hoc']); ?></h5>
                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($k['ten_danh_muc'] ?? ''); ?></p>
                        <div class="d-flex gap-3 small text-muted">
                            <span><i class="fas fa-file-lines me-1"></i><?php echo (int)($k['so_bai_hoc'] ?? 0); ?> bài</span>
                            <span><i class="fas fa-user-graduate me-1"></i><?php echo (int)($k['so_hoc_vien'] ?? 0); ?> HV</span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0">
                        <a href="<?php echo SITE_URL; ?>/teacher/bai-hoc/index.php?khoa_hoc=<?php echo $k['id']; ?>"
                           class="btn btn-sm btn-outline-success w-100 mb-1">
                            <i class="fas fa-file-lines me-1"></i>Bài học
                        </a>
                        <a href="<?php echo SITE_URL; ?>/views/khoa-hoc/chi-tiet.php?id=<?php echo $k['id']; ?>"
                           class="btn btn-sm btn-outline-secondary w-100" target="_blank">
                            <i class="fas fa-eye me-1"></i>Xem công khai
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../partials/layout_end.php'; ?>
