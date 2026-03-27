<?php
$page_title = 'Quản lý Khóa học';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/../models/khoa_hoc.php';

$kh = new KhoaHoc();
$thong_bao = '';

// ── Xử lý đổi trạng thái ──
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $q = $kh->layTheoId($id);
    if ($q) {
        switch ($_GET['action']) {
            case 'duyet':
                $kh->capNhatTrangThai($id, 'da_duyet');
                $thong_bao = '<div class="alert alert-success">Đã duyệt khóa học "<strong>' . htmlspecialchars($q['ten_khoa_hoc']) . '</strong>".</div>';
                break;
            case 'an':
                $kh->capNhatTrangThai($id, 'bi_an');
                $thong_bao = '<div class="alert alert-success">Đã ẩn khóa học "<strong>' . htmlspecialchars($q['ten_khoa_hoc']) . '</strong>".</div>';
                break;
            case 'hien':
                $kh->capNhatTrangThai($id, 'da_duyet');
                $thong_bao = '<div class="alert alert-success">Đã hiển thị khóa học "<strong>' . htmlspecialchars($q['ten_khoa_hoc']) . '</strong>".</div>';
                break;
            case 'cho_duyet':
                $kh->capNhatTrangThai($id, 'ban_nhap');
                $thong_bao = '<div class="alert alert-success">Đã chuyển khóa học sang chờ duyệt.</div>';
                break;
            case 'xoa':
                if ($kh->xoa($id)['success']) {
                    $thong_bao = '<div class="alert alert-success">Đã xóa khóa học thành công.</div>';
                } else {
                    $thong_bao = '<div class="alert alert-danger">Xóa khóa học thất bại.</div>';
                }
                break;
        }
    }
}

// ── Lấy danh sách + filter ──
$ds = $kh->layTatCaAdmin();
$loc_tt = isset($_GET['trang_thai']) ? $_GET['trang_thai'] : '';
$tu_khoa = isset($_GET['tu_khoa']) ? trim($_GET['tu_khoa']) : '';

if ($loc_tt !== '') {
    $ds = array_filter($ds, fn($k) => $k['trang_thai'] === $loc_tt);
}
if ($tu_khoa !== '') {
    $ds = array_filter($ds, fn($k) => stripos($k['ten_khoa_hoc'], $tu_khoa) !== false || stripos($k['ten_giao_vien'], $tu_khoa) !== false);
}

// Thống kê
$all = $kh->layTatCaAdmin();
$thong_ke = [
    'tong'      => count($all),
    'da_duyet'  => count(array_filter($all, fn($k) => $k['trang_thai'] === 'da_duyet')),
    'ban_nhap'  => count(array_filter($all, fn($k) => $k['trang_thai'] === 'ban_nhap')),
    'bi_an'     => count(array_filter($all, fn($k) => $k['trang_thai'] === 'bi_an')),
];

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="admin-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-0"><i class="fas fa-book me-2 text-primary"></i>Quản lý Khóa học</h1>
        <p class="text-muted small mb-0">Tổng: <?php echo $thong_ke['tong']; ?> khóa học</p>
    </div>
</div>

<?php echo $thong_bao; ?>

<!-- Thống kê trạng thái -->
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-book"></i></div>
                <div>
                    <div class="text-muted small">Tổng</div>
                    <div class="fs-4 fw-semibold"><?php echo $thong_ke['tong']; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <a href="?trang_thai=da_duyet" class="text-decoration-none">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <div class="text-muted small">Đã duyệt</div>
                        <div class="fs-4 fw-semibold"><?php echo $thong_ke['da_duyet']; ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-3">
        <a href="?trang_thai=ban_nhap" class="text-decoration-none">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="text-muted small">Chờ duyệt</div>
                        <div class="fs-4 fw-semibold"><?php echo $thong_ke['ban_nhap']; ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-3">
        <a href="?trang_thai=bi_an" class="text-decoration-none">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon bg-secondary bg-opacity-10 text-secondary"><i class="fas fa-eye-slash"></i></div>
                    <div>
                        <div class="text-muted small">Đã ẩn</div>
                        <div class="fs-4 fw-semibold"><?php echo $thong_ke['bi_an']; ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<?php if ($loc_tt !== ''): ?>
<div class="alert alert-info d-flex justify-content-between align-items-center">
    <span>Đang lọc: <strong><?php
        $labels = ['da_duyet' => 'Đã duyệt', 'ban_nhap' => 'Chờ duyệt', 'bi_an' => 'Đã ẩn'];
        echo $labels[$loc_tt] ?? $loc_tt;
    ?></strong> — <?php echo count($ds); ?> kết quả</span>
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
                       placeholder="Tìm theo tên khóa học hoặc tên giáo viên..."
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
                <i class="fas fa-book-open fa-3x mb-3"></i>
                <p class="mb-0">Không tìm thấy khóa học nào.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Khóa học</th>
                            <th>Giáo viên</th>
                            <th>Danh mục</th>
                            <th>Bài học</th>
                            <th>Học viên</th>
                            <th>Giá</th>
                            <th>Trạng thái</th>
                            <th class="pe-4 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ds as $i => $k): ?>
                            <?php
                            $tt_labels = [
                                'da_duyet'  => '<span class="badge bg-success">Đã duyệt</span>',
                                'ban_nhap'  => '<span class="badge bg-warning text-dark">Chờ duyệt</span>',
                                'bi_an'     => '<span class="badge bg-secondary">Đã ẩn</span>',
                            ];
                            ?>
                            <tr>
                                <td class="ps-4 text-muted"><?php echo $i + 1; ?></td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($k['ten_khoa_hoc']); ?></div>
                                    <div class="small text-muted"><?php echo date('d/m/Y', strtotime($k['ngay_tao'])); ?></div>
                                </td>
                                <td class="small"><?php echo htmlspecialchars($k['ten_giao_vien'] ?? '-'); ?></td>
                                <td class="small"><?php echo htmlspecialchars($k['ten_danh_muc'] ?? '-'); ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo $k['so_bai_hoc']; ?></span></td>
                                <td><span class="badge bg-secondary"><?php echo $k['so_hoc_vien']; ?></span></td>
                                <td class="small"><?php echo $k['gia_tien'] > 0 ? number_format($k['gia_tien'], 0, ',', '.') . 'đ' : '<span class="text-success fw-semibold">Miễn phí</span>'; ?></td>
                                <td><?php echo $tt_labels[$k['trang_thai']] ?? $k['trang_thai']; ?></td>
                                <td class="pe-4 text-center">
                                    <a href="chi-tiet.php?id=<?php echo $k['id']; ?>" class="btn btn-outline-secondary btn-sm me-1" title="Chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($k['trang_thai'] === 'ban_nhap'): ?>
                                        <a href="?action=duyet&id=<?php echo $k['id']; if($loc_tt) echo '&trang_thai='.$loc_tt; ?>"
                                           class="btn btn-outline-success btn-sm me-1" title="Duyệt">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php elseif ($k['trang_thai'] === 'da_duyet'): ?>
                                        <a href="?action=an&id=<?php echo $k['id']; if($loc_tt) echo '&trang_thai='.$loc_tt; ?>"
                                           class="btn btn-outline-dark btn-sm me-1" title="Ẩn">
                                            <i class="fas fa-eye-slash"></i>
                                        </a>
                                    <?php elseif ($k['trang_thai'] === 'bi_an'): ?>
                                        <a href="?action=hien&id=<?php echo $k['id']; if($loc_tt) echo '&trang_thai='.$loc_tt; ?>"
                                           class="btn btn-outline-primary btn-sm me-1" title="Hiển thị lại">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="?action=cho_duyet&id=<?php echo $k['id']; if($loc_tt) echo '&trang_thai='.$loc_tt; ?>"
                                       class="btn btn-outline-warning btn-sm me-1" title="Chờ duyệt lại">
                                        <i class="fas fa-rotate-left"></i>
                                    </a>
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
