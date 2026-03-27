<?php
$page_title = 'Quản lý Bài học';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/../models/bai_hoc.php';
require_once dirname(__DIR__) . '/../models/khoa_hoc.php';

$bh = new BaiHoc();
$kh = new KhoaHoc();
$thong_bao = '';

// ── Xử lý xóa ──
if (isset($_GET['action']) && $_GET['action'] === 'xoa' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $b = $bh->layTheoId($id);
    if ($b) {
        $result = $bh->xoa($id);
        if ($result['success']) {
            $thong_bao = '<div class="alert alert-success">Đã xóa bài học thành công.</div>';
        } else {
            $thong_bao = '<div class="alert alert-danger">' . htmlspecialchars($result['message']) . '</div>';
        }
    }
}

// ── Lấy danh sách + filter ──
$ds = $bh->layTatCaAdmin();
$loc_kh = isset($_GET['khoa_hoc']) ? (int) $_GET['khoa_hoc'] : 0;
$tu_khoa = isset($_GET['tu_khoa']) ? trim($_GET['tu_khoa']) : '';

if ($loc_kh > 0) {
    $ds = array_filter($ds, fn($b) => (int)$b['id_khoa_hoc'] === $loc_kh);
}
if ($tu_khoa !== '') {
    $ds = array_filter($ds, fn($b) => stripos($b['tieu_de'], $tu_khoa) !== false || stripos($b['ten_khoa_hoc'], $tu_khoa) !== false);
}

// Thống kê
$all = $bh->layTatCaAdmin();
$tong_bai  = count($all);
$tong_quiz  = array_sum(array_column($all, 'so_quiz'));
$tong_phut  = array_sum(array_column($all, 'thoi_luong_phut'));

// Nhóm bài học theo khóa học để hiển thị
$theo_khoa_hoc = [];
foreach ($all as $b) {
    $key = $b['id_khoa_hoc'] ?? 0;
    if (!isset($theo_khoa_hoc[$key])) {
        $theo_khoa_hoc[$key] = [
            'ten_khoa_hoc' => $b['ten_khoa_hoc'] ?? 'Không xác định',
            'bai_hocs'     => [],
        ];
    }
    $theo_khoa_hoc[$key]['bai_hocs'][] = $b;
}

$khoa_hocs = $kh->layTatCaAdmin();

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="admin-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-0"><i class="fas fa-file-lines me-2 text-primary"></i>Quản lý Bài học</h1>
        <p class="text-muted small mb-0">Tổng: <?php echo $tong_bai; ?> bài học | <?php echo $tong_quiz; ?> quiz | <?php echo $tong_phut; ?> phút</p>
    </div>
    <a href="form.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Thêm Bài học
    </a>
</div>

<?php echo $thong_bao; ?>

<!-- Thống kê nhanh -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-file-lines"></i></div>
                <div>
                    <div class="text-muted small">Tổng Bài học</div>
                    <div class="fs-4 fw-semibold"><?php echo $tong_bai; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-circle-question"></i></div>
                <div>
                    <div class="text-muted small">Tổng Quiz</div>
                    <div class="fs-4 fw-semibold"><?php echo $tong_quiz; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="text-muted small">Tổng Thời lượng</div>
                    <div class="fs-4 fw-semibold"><?php echo $tong_phut; ?> phút</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($loc_kh > 0): ?>
<div class="alert alert-info d-flex justify-content-between align-items-center">
    <span>Đang lọc: khóa học <strong>"<?php
        $k = array_filter($khoa_hocs, fn($k) => (int)$k['id'] === $loc_kh);
        echo htmlspecialchars(reset($k)['ten_khoa_hoc'] ?? $loc_kh);
    ?>"</strong> — <?php echo count($ds); ?> bài học</span>
    <a href="index.php" class="btn btn-sm btn-outline-info">Xóa lọc</a>
</div>
<?php endif; ?>

<!-- Tìm kiếm + Lọc -->
<div class="card stat-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="tu_khoa" class="form-control"
                       placeholder="Tìm theo tiêu đề bài học..."
                       value="<?php echo htmlspecialchars($tu_khoa); ?>">
            </div>
            <div class="col-md-4">
                <select name="khoa_hoc" class="form-select">
                    <option value="">-- Tất cả khóa học --</option>
                    <?php foreach ($khoa_hocs as $k): ?>
                        <?php if ($k['trang_thai'] === 'da_duyet'): ?>
                            <option value="<?php echo $k['id']; ?>"
                                <?php if ($loc_kh == $k['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($k['ten_khoa_hoc']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>Lọc
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
                <i class="fas fa-file-circle-xmark fa-3x mb-3"></i>
                <p class="mb-0">Không tìm thấy bài học nào. <a href="form.php">Thêm bài học mới</a></p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Tiêu đề</th>
                            <th>Khóa học</th>
                            <th>Thứ tự</th>
                            <th>Thời lượng</th>
                            <th>Quiz</th>
                            <th>Trạng thái KH</th>
                            <th class="pe-4 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ds as $i => $b): ?>
                            <?php
                            $tt_kh_labels = [
                                'da_duyet'  => '<span class="badge bg-success">Hiển thị</span>',
                                'ban_nhap'  => '<span class="badge bg-warning text-dark">Chờ duyệt</span>',
                                'bi_an'     => '<span class="badge bg-secondary">Đã ẩn</span>',
                            ];
                            ?>
                            <tr>
                                <td class="ps-4 text-muted"><?php echo $i + 1; ?></td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($b['tieu_de']); ?></div>
                                </td>
                                <td class="small"><?php echo htmlspecialchars($b['ten_khoa_hoc'] ?? '-'); ?></td>
                                <td class="text-muted small"><?php echo $b['thu_tu']; ?></td>
                                <td class="text-muted small"><?php echo $b['thoi_luong_phut']; ?> phút</td>
                                <td>
                                    <?php if ((int)$b['so_quiz'] > 0): ?>
                                        <span class="badge bg-success"><?php echo $b['so_quiz']; ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $tt_kh_labels[$b['trang_thai_khoa_hoc']] ?? '-'; ?></td>
                                <td class="pe-4 text-center">
                                    <a href="form.php?id=<?php echo $b['id']; ?>" class="btn btn-outline-primary btn-sm me-1">
                                        <i class="fas fa-pen me-1"></i>Sửa
                                    </a>
                                    <a href="?action=xoa&id=<?php echo $b['id']; ?><?php if($loc_kh) echo '&khoa_hoc='.$loc_kh; ?>"
                                       class="btn btn-outline-danger btn-sm"
                                       onclick="return confirm('Xóa bài học &quot;<?php echo htmlspecialchars($b['tieu_de']); ?>&quot;?');">
                                        <i class="fas fa-trash me-1"></i>Xóa
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
