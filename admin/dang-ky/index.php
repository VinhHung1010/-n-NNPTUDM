<?php
$page_title = 'Quản lý Đăng ký';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/../models/dang_ky.php';

$dk_model = new DangKy();
$thong_bao = '';

// ── Xử lý hành động ──
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $d = $dk_model->layTheoId($id);
    if ($d) {
        switch ($_GET['action']) {
            case 'xac_nhan':
                $dk_model->capNhatTrangThai($id, 'da_xac_nhan');
                $thong_bao = '<div class="alert alert-success">Đã xác nhận đăng ký của <strong>' . htmlspecialchars($d['ten_hoc_vien']) . '</strong>.</div>';
                break;
            case 'huy':
                $dk_model->capNhatTrangThai($id, 'da_huy');
                $thong_bao = '<div class="alert alert-warning">Đã hủy đăng ký của <strong>' . htmlspecialchars($d['ten_hoc_vien']) . '</strong>.</div>';
                break;
            case 'cho_xu_ly':
                $dk_model->capNhatTrangThai($id, 'cho_xu_ly');
                $thong_bao = '<div class="alert alert-info">Đã chuyển sang chờ xử lý.</div>';
                break;
            case 'xoa':
                if ($dk_model->xoa($id)) {
                    $thong_bao = '<div class="alert alert-success">Đã xóa đăng ký.</div>';
                } else {
                    $thong_bao = '<div class="alert alert-danger">Xóa thất bại.</div>';
                }
                break;
        }
    }
}

// ── Lấy danh sách + filter ──
$loc_tt = isset($_GET['trang_thai']) ? $_GET['trang_thai'] : '';
$tu_khoa = isset($_GET['tu_khoa']) ? trim($_GET['tu_khoa']) : '';

$ds = $dk_model->layTatCa();
if ($loc_tt !== '') {
    $ds = array_filter($ds, fn($d) => $d['trang_thai'] === $loc_tt);
}
if ($tu_khoa !== '') {
    $ds = array_filter($ds, fn($d) =>
        stripos($d['ten_hoc_vien'], $tu_khoa) !== false
        || stripos($d['ten_khoa_hoc'], $tu_khoa) !== false
        || stripos($d['email_hoc_vien'], $tu_khoa) !== false
    );
}

// Thống kê
$thong_ke = $dk_model->thongKe();

$tt_labels = [
    'cho_xu_ly'   => ['Chờ xử lý', 'warning text-dark'],
    'da_xac_nhan' => ['Đã xác nhận', 'success'],
    'da_huy'      => ['Đã hủy', 'secondary'],
];
$tt_icon = [
    'cho_xu_ly'   => 'clock',
    'da_xac_nhan' => 'check-circle',
    'da_huy'      => 'x-circle',
];

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="admin-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-0"><i class="fas fa-user-plus me-2 text-primary"></i>Quản lý Đăng ký</h1>
        <p class="text-muted small mb-0">Tổng: <?php echo $thong_ke['tong']; ?> đăng ký</p>
    </div>
</div>

<?php echo $thong_bao; ?>

<!-- Thống kê trạng thái -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <a href="?trang_thai=cho_xu_ly" class="text-decoration-none">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="text-muted small">Chờ xử lý</div>
                        <div class="fs-4 fw-semibold"><?php echo $thong_ke['cho_xu_ly']; ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-4">
        <a href="?trang_thai=da_xac_nhan" class="text-decoration-none">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <div class="text-muted small">Đã xác nhận</div>
                        <div class="fs-4 fw-semibold"><?php echo $thong_ke['da_xac_nhan']; ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-4">
        <a href="?trang_thai=da_huy" class="text-decoration-none">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon bg-secondary bg-opacity-10 text-secondary"><i class="fas fa-x-circle"></i></div>
                    <div>
                        <div class="text-muted small">Đã hủy</div>
                        <div class="fs-4 fw-semibold"><?php echo $thong_ke['da_huy']; ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<?php if ($loc_tt !== ''): ?>
<div class="alert alert-info d-flex justify-content-between align-items-center">
    <span>Đang lọc: <strong><?php echo $tt_labels[$loc_tt][0] ?? $loc_tt; ?></strong> — <?php echo count($ds); ?> đăng ký</span>
    <a href="index.php" class="btn btn-sm btn-outline-info">Xóa lọc</a>
</div>
<?php endif; ?>

<!-- Tìm kiếm -->
<div class="card stat-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <?php if ($loc_tt): ?>
                <input type="hidden" name="trang_thai" value="<?php echo htmlspecialchars($loc_tt); ?>">
            <?php endif; ?>
            <div class="col-md-8">
                <input type="text" name="tu_khoa" class="form-control"
                       placeholder="Tìm theo tên học viên, email hoặc tên khóa học..."
                       value="<?php echo htmlspecialchars($tu_khoa); ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>Tìm kiếm
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bảng danh sách -->
<div class="card stat-card">
    <div class="card-body p-0">
        <?php if (empty($ds)): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                <p class="mb-0">Không có đăng ký nào.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Học viên</th>
                            <th>Khóa học</th>
                            <th>Giá</th>
                            <th>Ngày đăng ký</th>
                            <th>Trạng thái</th>
                            <th class="pe-4 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ds as $i => $d): ?>
                            <?php [$tt_text, $tt_color] = $tt_labels[$d['trang_thai']] ?? ['—', 'secondary']; ?>
                            <tr>
                                <td class="ps-4 text-muted"><?php echo $i + 1; ?></td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($d['ten_hoc_vien']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($d['email_hoc_vien']); ?></div>
                                </td>
                                <td class="small">
                                    <div class="fw-semibold"><?php echo htmlspecialchars($d['ten_khoa_hoc']); ?></div>
                                    <div class="text-muted"><?php echo htmlspecialchars($d['ten_danh_muc'] ?? '-'); ?></div>
                                </td>
                                <td class="small">
                                    <?php
                                    if ((int)$d['gia_khoa_hoc'] === 0) {
                                        echo '<span class="text-success fw-semibold">Miễn phí</span>';
                                    } else {
                                        echo number_format($d['gia_khoa_hoc'], 0, ',', '.') . 'đ';
                                    }
                                    ?>
                                </td>
                                <td class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($d['ngay_dang_ky'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $tt_color; ?>">
                                        <i class="fas fa-<?php echo $tt_icon[$d['trang_thai']] ?? 'circle'; ?> me-1"></i>
                                        <?php echo $tt_text; ?>
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <a href="chi-tiet.php?id=<?php echo $d['id']; ?>" class="btn btn-outline-secondary btn-sm me-1">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($d['trang_thai'] === 'cho_xu_ly'): ?>
                                        <a href="?action=xac_nhan&id=<?php echo $d['id']; ?><?php if($loc_tt) echo '&trang_thai='.$loc_tt; ?>"
                                           class="btn btn-outline-success btn-sm me-1" title="Xác nhận">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="?action=huy&id=<?php echo $d['id']; ?><?php if($loc_tt) echo '&trang_thai='.$loc_tt; ?>"
                                           class="btn btn-outline-danger btn-sm me-1" title="Hủy"
                                           onclick="return confirm('Hủy đăng ký của <?php echo htmlspecialchars($d['ten_hoc_vien']); ?>?');">
                                            <i class="fas fa-xmark"></i>
                                        </a>
                                    <?php elseif ($d['trang_thai'] === 'da_xac_nhan'): ?>
                                        <a href="?action=huy&id=<?php echo $d['id']; ?><?php if($loc_tt) echo '&trang_thai='.$loc_tt; ?>"
                                           class="btn btn-outline-dark btn-sm me-1" title="Hủy"
                                           onclick="return confirm('Hủy đăng ký?');">
                                            <i class="fas fa-ban"></i>
                                        </a>
                                    <?php elseif ($d['trang_thai'] === 'da_huy'): ?>
                                        <a href="?action=cho_xu_ly&id=<?php echo $d['id']; ?><?php if($loc_tt) echo '&trang_thai='.$loc_tt; ?>"
                                           class="btn btn-outline-primary btn-sm me-1" title="Chờ xử lý lại">
                                            <i class="fas fa-rotate-left"></i>
                                        </a>
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
