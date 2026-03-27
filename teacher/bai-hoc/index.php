<?php
$page_title = 'Quản lý Bài học - Giáo viên';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/models/bai_hoc.php';
require_once dirname(__DIR__) . '/models/khoa_hoc.php';

$bh = new BaiHoc();
$kh = new KhoaHoc();
$thong_bao = '';

// Xử lý xóa
if (isset($_GET['action']) && $_GET['action'] === 'xoa' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $b = $bh->layTheoId($id);
    if ($b) {
        $k = $kh->layTheoId($b['id_khoa_hoc']);
        if ($k && (int)$k['id_nguoi_tao'] === (int)$nguoi_dung['id']) {
            $r = $bh->xoa($id);
            $thong_bao = $r['success']
                ? '<div class="alert alert-success">Đã xóa bài học.</div>'
                : '<div class="alert alert-danger">' . htmlspecialchars($r['message']) . '</div>';
        }
    }
}

// Lọc theo khóa học
$loc_kh = isset($_GET['khoa_hoc']) ? (int)$_GET['khoa_hoc'] : 0;
$khoa_list = $kh->layKhoaHocCuaGiaoVien($nguoi_dung['id']);
$bh_all = $bh->layTatCa();

if ($loc_kh > 0) {
    $bh_all = array_filter($bh_all, fn($b) => (int)$b['id_khoa_hoc'] === $loc_kh);
}

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="tv-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-0"><i class="fas fa-file-lines me-2 text-success"></i>Quản lý Bài học</h1>
        <p class="text-muted small mb-0"><?php echo count($bh_all); ?> bài học</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <select class="form-select form-select-sm" onchange="location.href='index.php?khoa_hoc='+this.value"
                style="min-width:200px">
            <option value="0">-- Tất cả khóa học --</option>
            <?php foreach ($khoa_list as $k): ?>
                <option value="<?php echo $k['id']; ?>" <?php if ($loc_kh == $k['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($k['ten_khoa_hoc']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($loc_kh > 0): ?>
            <a href="form.php?khoa_hoc=<?php echo $loc_kh; ?>" class="btn btn-success btn-sm">
                <i class="fas fa-plus me-1"></i>Thêm bài học
            </a>
        <?php endif; ?>
    </div>
</div>

<?php echo $thong_bao; ?>

<?php if (empty($khoa_list)): ?>
    <div class="card tv-stat-card text-center py-5">
        <div class="card-body">
            <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Bạn chưa có khóa học nào.</h4>
            <a href="<?php echo SITE_URL; ?>/teacher/khoa-hoc/them-moi.php" class="btn btn-success mt-2">
                <i class="fas fa-plus me-1"></i>Tạo khóa học trước
            </a>
        </div>
    </div>
<?php elseif (empty($bh_all)): ?>
    <div class="card tv-stat-card text-center py-5">
        <div class="card-body">
            <i class="fas fa-file-lines fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Chưa có bài học nào.</h4>
            <?php if ($loc_kh > 0): ?>
                <a href="form.php?khoa_hoc=<?php echo $loc_kh; ?>" class="btn btn-success mt-2">
                    <i class="fas fa-plus me-1"></i>Thêm bài học đầu tiên
                </a>
            <?php else: ?>
                <p class="text-muted">Chọn khóa học ở trên để thêm bài học.</p>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="card tv-stat-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Tiêu đề bài học</th>
                            <th>Khóa học</th>
                            <th>Thứ tự</th>
                            <th>Thời lượng</th>
                            <th>Quiz</th>
                            <th class="pe-4 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bh_all as $i => $b): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?php echo $i + 1; ?></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($b['tieu_de']); ?></td>
                                <td class="text-muted small"><?php echo htmlspecialchars($b['ten_khoa_hoc'] ?? ''); ?></td>
                                <td class="text-muted"><?php echo $b['thu_tu']; ?></td>
                                <td class="text-muted"><?php echo $b['thoi_luong_phut']; ?>p</td>
                                <td><?php echo (int)($b['so_quiz'] ?? 0); ?></td>
                                <td class="pe-4 text-center">
                                    <a href="form.php?id=<?php echo $b['id']; ?>" class="btn btn-sm btn-outline-success me-1">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="?action=xoa&id=<?php echo $b['id']; ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Xóa bài học này?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../partials/layout_end.php'; ?>
