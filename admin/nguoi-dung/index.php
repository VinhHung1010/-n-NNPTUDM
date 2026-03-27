<?php
$page_title = 'Quản lý Người dùng';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/../models/nguoi_dung.php';

$nd = new NguoiDung();
$thong_bao = '';

// ── Xử lý khóa / mở khóa ──
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $user = $nd->layTheoId($id);
    if ($user) {
        if ($user['vai_tro'] === 'quan_tri') {
            $thong_bao = '<div class="alert alert-warning">Không thể khóa tài khoản quản trị viên!</div>';
        } elseif ($_GET['action'] === 'khoa') {
            $nd->khoa($id);
            $thong_bao = '<div class="alert alert-success">Đã khóa tài khoản thành công.</div>';
        } elseif ($_GET['action'] === 'mo_khoa') {
            $nd->moKhoa($id);
            $thong_bao = '<div class="alert alert-success">Đã mở khóa tài khoản thành công.</div>';
        }
    }
}

// ── Lấy danh sách ──
$ds = $nd->layTatCa();

// ── Lọc tìm kiếm ──
$tu_khoa   = isset($_GET['tu_khoa']) ? trim($_GET['tu_khoa']) : '';
$loc_vai_tro   = isset($_GET['vai_tro']) ? $_GET['vai_tro'] : '';
$loc_trang_thai = isset($_GET['trang_thai']) ? $_GET['trang_thai'] : '';

if ($tu_khoa !== '') {
    $ds = array_filter($ds, fn($u) => stripos($u['ho_ten'], $tu_khoa) !== false || stripos($u['email'], $tu_khoa) !== false);
}
if ($loc_vai_tro !== '') {
    $ds = array_filter($ds, fn($u) => $u['vai_tro'] === $loc_vai_tro);
}
if ($loc_trang_thai !== '') {
    $ds = array_filter($ds, fn($u) => $u['trang_thai'] === $loc_trang_thai);
}

// ── Đếm tổng ──
$tong = count($nd->layTatCa());
$da_khoa = count(array_filter($nd->layTatCa(), fn($u) => $u['trang_thai'] === 'khong_hoat_dong'));

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="admin-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-0"><i class="fas fa-users me-2 text-primary"></i>Quản lý Người dùng</h1>
        <p class="text-muted small mb-0">Tổng: <?php echo $tong; ?> người dùng | Đã khóa: <?php echo $da_khoa; ?></p>
    </div>
</div>

<?php echo $thong_bao; ?>

<!-- Thống kê nhanh -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-users"></i></div>
                <div>
                    <div class="text-muted small">Tổng người dùng</div>
                    <div class="fs-4 fw-semibold"><?php echo $tong; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-user-check"></i></div>
                <div>
                    <div class="text-muted small">Hoạt động</div>
                    <div class="fs-4 fw-semibold"><?php echo $tong - $da_khoa; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-danger bg-opacity-10 text-danger"><i class="fas fa-user-lock"></i></div>
                <div>
                    <div class="text-muted small">Đã khóa</div>
                    <div class="fs-4 fw-semibold"><?php echo $da_khoa; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bộ lọc + tìm kiếm -->
<div class="card stat-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="tu_khoa" class="form-control"
                       placeholder="Tìm theo tên hoặc email..."
                       value="<?php echo htmlspecialchars($tu_khoa); ?>">
            </div>
            <div class="col-md-3">
                <select name="vai_tro" class="form-select">
                    <option value="">-- Tất cả vai trò --</option>
                    <option value="hoc_vien" <?php if ($loc_vai_tro === 'hoc_vien') echo 'selected'; ?>>Học viên</option>
                    <option value="giao_vien" <?php if ($loc_vai_tro === 'giao_vien') echo 'selected'; ?>>Giáo viên</option>
                    <option value="quan_tri" <?php if ($loc_vai_tro === 'quan_tri') echo 'selected'; ?>>Quản trị</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="trang_thai" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="hoat_dong" <?php if ($loc_trang_thai === 'hoat_dong') echo 'selected'; ?>>Hoạt động</option>
                    <option value="khong_hoat_dong" <?php if ($loc_trang_thai === 'khong_hoat_dong') echo 'selected'; ?>>Đã khóa</option>
                </select>
            </div>
            <div class="col-md-2">
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
                <i class="fas fa-users-slash fa-3x mb-3"></i>
                <p class="mb-0">Không tìm thấy người dùng nào.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th class="pe-4 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ds as $i => $u): ?>
                            <?php
                            $vai_tro_labels = [
                                'hoc_vien'  => '<span class="badge bg-secondary">Học viên</span>',
                                'giao_vien' => '<span class="badge bg-info text-dark">Giáo viên</span>',
                                'quan_tri'  => '<span class="badge bg-primary">Quản trị</span>',
                            ];
                            $trang_thai_labels = [
                                'hoat_dong'        => '<span class="badge bg-success">Hoạt động</span>',
                                'khong_hoat_dong'  => '<span class="badge bg-danger">Đã khóa</span>',
                            ];
                            ?>
                            <tr>
                                <td class="ps-4 text-muted"><?php echo $i + 1; ?></td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($u['ho_ten']); ?></div>
                                </td>
                                <td class="text-muted small"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo $vai_tro_labels[$u['vai_tro']] ?? $u['vai_tro']; ?></td>
                                <td><?php echo $trang_thai_labels[$u['trang_thai']] ?? $u['trang_thai']; ?></td>
                                <td class="text-muted small"><?php echo date('d/m/Y', strtotime($u['ngay_tao'])); ?></td>
                                <td class="pe-4 text-center">
                                    <?php if ($u['vai_tro'] !== 'quan_tri'): ?>
                                        <?php if ($u['trang_thai'] === 'hoat_dong'): ?>
                                            <a href="?action=khoa&id=<?php echo $u['id']; ?>"
                                               class="btn btn-outline-danger btn-sm"
                                               onclick="return confirm('Khóa tài khoản <?php echo htmlspecialchars($u['ho_ten']); ?>?')">
                                                <i class="fas fa-lock me-1"></i>Khóa
                                            </a>
                                        <?php else: ?>
                                            <a href="?action=mo_khoa&id=<?php echo $u['id']; ?>"
                                               class="btn btn-outline-success btn-sm">
                                                <i class="fas fa-unlock me-1"></i>Mở khóa
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Mặc định</span>
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
