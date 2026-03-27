<?php
$page_title = 'Quản lý Danh mục';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/../models/danh_muc.php';

$dm = new DanhMuc();
$thong_bao = '';

// ── Xử lý xóa ──
if (isset($_GET['action']) && $_GET['action'] === 'xoa' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $result = $dm->xoa($id);
    if ($result['success']) {
        $thong_bao = '<div class="alert alert-success">Đã xóa danh mục thành công.</div>';
    } else {
        $thong_bao = '<div class="alert alert-danger">' . htmlspecialchars($result['message']) . '</div>';
    }
}

// ── Lấy danh sách ──
$ds = $dm->layTatCa();
$tong = count($ds);

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="admin-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-0"><i class="fas fa-layer-group me-2 text-primary"></i>Quản lý Danh mục</h1>
        <p class="text-muted small mb-0">Tổng: <?php echo $tong; ?> danh mục</p>
    </div>
    <a href="form.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Thêm danh mục
    </a>
</div>

<?php echo $thong_bao; ?>

<!-- Thống kê -->
<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-layer-group"></i></div>
                <div>
                    <div class="text-muted small">Tổng danh mục</div>
                    <div class="fs-4 fw-semibold"><?php echo $tong; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bảng danh sách -->
<div class="card stat-card">
    <div class="card-body p-0">
        <?php if (empty($ds)): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-folder-open fa-3x mb-3"></i>
                <p class="mb-0">Chưa có danh mục nào.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Tên danh mục</th>
                            <th>Mô tả</th>
                            <th>Khóa học</th>
                            <th>Ngày tạo</th>
                            <th class="pe-4 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ds as $i => $d): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?php echo $i + 1; ?></td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($d['ten_danh_muc']); ?></div>
                                </td>
                                <td class="text-muted small">
                                    <?php echo $d['mo_ta'] ? htmlspecialchars($d['mo_ta']) : '<em>Không có mô tả</em>'; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo $d['so_khoa_hoc']; ?></span>
                                </td>
                                <td class="text-muted small"><?php echo date('d/m/Y', strtotime($d['ngay_tao'])); ?></td>
                                <td class="pe-4 text-center">
                                    <a href="form.php?id=<?php echo $d['id']; ?>" class="btn btn-outline-primary btn-sm me-1">
                                        <i class="fas fa-pen me-1"></i>Sửa
                                    </a>
                                    <?php if ((int)$d['so_khoa_hoc'] === 0): ?>
                                        <a href="?action=xoa&id=<?php echo $d['id']; ?>"
                                           class="btn btn-outline-danger btn-sm"
                                           onclick="return confirm('Xóa danh mục &quot;<?php echo htmlspecialchars($d['ten_danh_muc']); ?>&quot;?');">
                                            <i class="fas fa-trash me-1"></i>Xóa
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary btn-sm" disabled
                                                title="Cần xóa <?php echo $d['so_khoa_hoc']; ?> khóa học trước">
                                            <i class="fas fa-trash me-1"></i>Xóa
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../partials/layout_end.php'; ?>
