<?php
$page_title = 'Học viên - Giáo viên';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/models/khoa_hoc.php';
require_once dirname(__DIR__) . '/models/tien_do.php';

$kh_model = new KhoaHoc();
$td_model = new TienDo();

$loc_kh = isset($_GET['khoa_hoc']) ? (int)$_GET['khoa_hoc'] : 0;
$khoa_list = $kh_model->layKhoaHocCuaGiaoVien($nguoi_dung['id']);

$all_hv = [];
foreach ($khoa_list as $k) {
    $hv_list = $kh_model->layHocVien($k['id']);
    foreach ($hv_list as $hv) {
        $hv['ten_khoa_hoc'] = $k['ten_khoa_hoc'];
        $hv['id_khoa_hoc'] = $k['id'];
        $hv['phan_tram'] = $td_model->tinhPhanTram($hv['id'], $k['id']);
        $all_hv[] = $hv;
    }
}

if ($loc_kh > 0) {
    $all_hv = array_filter($all_hv, fn($hv) => (int)$hv['id_khoa_hoc'] === $loc_kh);
}
$all_hv = array_values($all_hv);

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="tv-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-0">
            <i class="fas fa-user-graduate me-2 text-success"></i>Học viên của tôi
        </h1>
        <p class="text-muted small mb-0"><?php echo count($all_hv); ?> học viên</p>
    </div>
    <select class="form-select form-select-sm" onchange="location.href='index.php?khoa_hoc='+this.value" style="min-width:200px">
        <option value="0">-- Tất cả khóa học --</option>
        <?php foreach ($khoa_list as $k): ?>
        <option value="<?php echo $k['id']; ?>" <?php if ($loc_kh == $k['id']) echo 'selected'; ?>>
            <?php echo htmlspecialchars($k['ten_khoa_hoc']); ?>
        </option>
        <?php endforeach; ?>
    </select>
</div>

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
<?php elseif (empty($all_hv)): ?>
<div class="card tv-stat-card text-center py-5">
    <div class="card-body">
        <i class="fas fa-user-graduate fa-4x text-muted mb-3"></i>
        <h4 class="text-muted">Chưa có học viên nào.</h4>
        <p class="text-muted">Học viên sẽ xuất hiện sau khi được duyệt đăng ký.</p>
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
                        <th>Học viên</th>
                        <th>Khóa học</th>
                        <th>Trạng thái ĐK</th>
                        <th>Tiến độ</th>
                        <th class="pe-4">Ngày đăng ký</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_hv as $i => $hv): ?>
                    <?php
                    $dk_labels = [
                        'cho_xu_ly'  => '<span class="badge bg-warning text-dark">Chờ xử lý</span>',
                        'da_xac_nhan' => '<span class="badge bg-success">Đã xác nhận</span>',
                        'da_huy'     => '<span class="badge bg-danger">Đã hủy</span>',
                    ];
                    $pct = (int)($hv['phan_tram'] ?? 0);
                    $bar = $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-info' : 'bg-primary');
                    ?>
                    <tr>
                        <td class="ps-4 text-muted"><?php echo $i + 1; ?></td>
                        <td class="fw-semibold"><?php echo htmlspecialchars($hv['ho_ten']); ?></td>
                        <td class="small text-muted"><?php echo htmlspecialchars($hv['ten_khoa_hoc']); ?></td>
                        <td><?php echo $dk_labels[$hv['trang_thai_dk']] ?? ''; ?></td>
                        <td>
                            <?php if ($hv['trang_thai_dk'] === 'da_xac_nhan'): ?>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:6px;border-radius:99px;min-width:80px">
                                    <div class="progress-bar <?php echo $bar; ?>" style="width:<?php echo $pct; ?>%;border-radius:99px"></div>
                                </div>
                                <span class="fw-semibold small" style="min-width:36px;text-align:right"><?php echo $pct; ?>%</span>
                            </div>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="pe-4 text-muted small"><?php echo date('d/m/Y', strtotime($hv['ngay_dang_ky'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../partials/layout_end.php'; ?>
