<?php
$page_title = 'Thống kê Báo cáo';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/../config/database.php';

$db = Database::getInstance()->getConnection();

$dt = [];

// ── Tổng quan ──
$dt['tong_hv']     = (int) $db->query("SELECT COUNT(*) AS c FROM nguoi_dung WHERE vai_tro = 'hoc_vien'")->fetch_assoc()['c'];
$dt['tong_gv']     = (int) $db->query("SELECT COUNT(*) AS c FROM nguoi_dung WHERE vai_tro = 'giao_vien'")->fetch_assoc()['c'];
$dt['tong_kh']     = (int) $db->query("SELECT COUNT(*) AS c FROM khoa_hoc WHERE trang_thai = 'da_duyet'")->fetch_assoc()['c'];
$dt['tong_bh']     = (int) $db->query("SELECT COUNT(*) AS c FROM bai_hoc")->fetch_assoc()['c'];
$dt['tong_quiz']   = (int) $db->query("SELECT COUNT(*) AS c FROM quiz")->fetch_assoc()['c'];
$dt['tong_dk']     = (int) $db->query("SELECT COUNT(*) AS c FROM dang_ky_khoa_hoc")->fetch_assoc()['c'];
$dt['dk_cho']      = (int) $db->query("SELECT COUNT(*) AS c FROM dang_ky_khoa_hoc WHERE trang_thai = 'cho_xu_ly'")->fetch_assoc()['c'];
$dt['dk_da']       = (int) $db->query("SELECT COUNT(*) AS c FROM dang_ky_khoa_hoc WHERE trang_thai = 'da_xac_nhan'")->fetch_assoc()['c'];
$dt['tong_lam_quiz'] = (int) $db->query("SELECT COUNT(*) AS c FROM ket_qua_quiz")->fetch_assoc()['c'];

// ── Top khóa học theo lượt đăng ký ──
$top_khoa_hoc = $db->query("
    SELECT kh.id, kh.ten_khoa_hoc,
           COUNT(dk.id) AS so_luot_dk,
           SUM(CASE WHEN dk.trang_thai = 'da_xac_nhan' THEN 1 ELSE 0 END) AS so_luot_xac_nhan,
           dm.ten_danh_muc
    FROM khoa_hoc kh
    LEFT JOIN dang_ky_khoa_hoc dk ON dk.id_khoa_hoc = kh.id
    LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
    WHERE kh.trang_thai = 'da_duyet'
    GROUP BY kh.id, kh.ten_khoa_hoc, dm.ten_danh_muc
    ORDER BY so_luot_dk DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// ── Top học viên tích cực ──
$top_hv = $db->query("
    SELECT hv.ho_ten, hv.email,
           COUNT(DISTINCT dk.id_khoa_hoc) AS so_khoa_dk,
           COUNT(kq.id) AS so_quiz_lam,
           MAX(kq.diem_so) AS diem_max
    FROM nguoi_dung hv
    LEFT JOIN dang_ky_khoa_hoc dk ON dk.id_hoc_vien = hv.id AND dk.trang_thai = 'da_xac_nhan'
    LEFT JOIN ket_qua_quiz kq ON kq.id_nguoi_dung = hv.id
    WHERE hv.vai_tro = 'hoc_vien'
    GROUP BY hv.id, hv.ho_ten, hv.email
    ORDER BY so_quiz_lam DESC, diem_max DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// ── Đăng ký gần đây ──
$dk_gan_nhat = $db->query("
    SELECT dk.*, hv.ho_ten AS ten_hv, kh.ten_khoa_hoc
    FROM dang_ky_khoa_hoc dk
    JOIN nguoi_dung hv ON dk.id_hoc_vien = hv.id
    JOIN khoa_hoc kh ON dk.id_khoa_hoc = kh.id
    ORDER BY dk.ngay_dang_ky DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// ── Khóa học mới nhất ──
$kh_moi = $db->query("
    SELECT kh.*, dm.ten_danh_muc, nd.ho_ten AS ten_gv,
           (SELECT COUNT(*) FROM bai_hoc WHERE id_khoa_hoc = kh.id) AS so_bai,
           (SELECT COUNT(*) FROM dang_ky_khoa_hoc WHERE id_khoa_hoc = kh.id) AS so_dk
    FROM khoa_hoc kh
    LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
    LEFT JOIN nguoi_dung nd ON kh.id_nguoi_tao = nd.id
    WHERE kh.trang_thai = 'da_duyet'
    ORDER BY kh.ngay_tao DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$tt_labels = [
    'cho_xu_ly'   => ['Chờ xử lý', 'warning text-dark'],
    'da_xac_nhan' => ['Đã xác nhận', 'success'],
    'da_huy'      => ['Đã hủy', 'secondary'],
];

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="admin-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Thống kê & Báo cáo</h1>
        <p class="text-muted small mb-0">Cập nhật: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
</div>

<!-- ══ Tổng quan ══ -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-user-graduate"></i></div>
                <div>
                    <div class="text-muted small">Học viên</div>
                    <div class="fs-4 fw-semibold"><?php echo number_format($dt['tong_hv']); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-book"></i></div>
                <div>
                    <div class="text-muted small">Khóa học</div>
                    <div class="fs-4 fw-semibold"><?php echo number_format($dt['tong_kh']); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-file-lines"></i></div>
                <div>
                    <div class="text-muted small">Bài học</div>
                    <div class="fs-4 fw-semibold"><?php echo number_format($dt['tong_bh']); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-info bg-opacity-10 text-info"><i class="fas fa-circle-question"></i></div>
                <div>
                    <div class="text-muted small">Quiz</div>
                    <div class="fs-4 fw-semibold"><?php echo number_format($dt['tong_quiz']); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-purple bg-opacity-10" style="color:#7c3aed"><i class="fas fa-clipboard-check"></i></div>
                <div>
                    <div class="text-muted small">Lượt làm Quiz</div>
                    <div class="fs-4 fw-semibold"><?php echo number_format($dt['tong_lam_quiz']); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="<?php echo SITE_URL; ?>/admin/dang-ky/index.php" class="text-decoration-none">
            <div class="card stat-card h-100 <?php if ($dt['dk_cho'] > 0) echo 'border-warning'; ?>">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-user-plus"></i></div>
                    <div>
                        <div class="text-muted small">Đăng ký</div>
                        <div class="fs-4 fw-semibold">
                            <?php echo number_format($dt['tong_dk']); ?>
                            <?php if ($dt['dk_cho'] > 0): ?>
                                <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem"><?php echo $dt['dk_cho']; ?> chờ</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4">

    <!-- ══ Top khóa học ══ -->
    <div class="col-lg-6">
        <div class="card stat-card">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-fire me-1 text-danger"></i>Top khóa học nhiều đăng ký
            </div>
            <div class="card-body p-0">
                <?php if (empty($top_khoa_hoc)): ?>
                    <div class="text-center text-muted py-4"><i class="fas fa-chart-bar fa-2x mb-2"></i><p class="mb-0">Chưa có dữ liệu</p></div>
                <?php else: ?>
                    <?php $max_dk = max(array_column($top_khoa_hoc, 'so_luot_dk')); foreach ($top_khoa_hoc as $i => $kh): ?>
                        <?php $pct = $max_dk > 0 ? ($kh['so_luot_dk'] / $max_dk) * 100 : 0; ?>
                        <div class="px-4 py-3 <?php echo $i > 0 ? 'border-top' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-dark fw-normal" style="min-width:22px;text-align:center">
                                        <?php echo $i + 1; ?>
                                    </span>
                                    <span class="fw-semibold small"><?php echo htmlspecialchars($kh['ten_khoa_hoc']); ?></span>
                                </div>
                                <span class="badge bg-success"><?php echo $kh['so_luot_dk']; ?> lượt</span>
                            </div>
                            <div class="progress" style="height:6px">
                                <div class="progress-bar bg-success" style="width:<?php echo $pct; ?>%"></div>
                            </div>
                            <div class="small text-muted mt-1">
                                <?php echo htmlspecialchars($kh['ten_danh_muc'] ?? ''); ?> ·
                                <?php echo $kh['so_luot_xac_nhan']; ?> đã xác nhận
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ══ Top học viên tích cực ══ -->
    <div class="col-lg-6">
        <div class="card stat-card">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-trophy me-1 text-warning"></i>Học viên tích cực nhất
            </div>
            <div class="card-body p-0">
                <?php if (empty($top_hv)): ?>
                    <div class="text-center text-muted py-4"><i class="fas fa-user-graduate fa-2x mb-2"></i><p class="mb-0">Chưa có dữ liệu</p></div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Học viên</th>
                                    <th>Khóa</th>
                                    <th>Quiz</th>
                                    <th class="pe-4 text-end">Điểm MAX</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_hv as $i => $hv): ?>
                                    <tr>
                                        <td class="ps-4 text-muted"><?php echo $i + 1; ?></td>
                                        <td>
                                            <div class="fw-semibold small"><?php echo htmlspecialchars($hv['ho_ten']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($hv['email']); ?></div>
                                        </td>
                                        <td><span class="badge bg-primary"><?php echo $hv['so_khoa_dk']; ?></span></td>
                                        <td><span class="badge bg-info text-dark"><?php echo $hv['so_quiz_lam']; ?></span></td>
                                        <td class="pe-4 text-end">
                                            <?php if ((int)$hv['diem_max'] > 0): ?>
                                                <span class="fw-bold text-success"><?php echo $hv['diem_max']; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
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
    </div>

    <!-- ══ Đăng ký gần đây ══ -->
    <div class="col-lg-6">
        <div class="card stat-card">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-clock me-1 text-primary"></i>Đăng ký gần đây
                <a href="<?php echo SITE_URL; ?>/admin/dang-ky/index.php" class="btn btn-sm btn-outline-primary float-end">
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($dk_gan_nhat)): ?>
                    <div class="text-center text-muted py-4"><i class="fas fa-clipboard-list fa-2x mb-2"></i><p class="mb-0">Chưa có đăng ký nào</p></div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Học viên</th>
                                    <th>Khóa học</th>
                                    <th>Ngày</th>
                                    <th class="pe-4">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dk_gan_nhat as $d): ?>
                                    <?php [$tt_text, $tt_color] = $tt_labels[$d['trang_thai']] ?? ['—', 'secondary']; ?>
                                    <tr>
                                        <td class="ps-4 small fw-semibold"><?php echo htmlspecialchars($d['ten_hv']); ?></td>
                                        <td class="small"><?php echo htmlspecialchars($d['ten_khoa_hoc']); ?></td>
                                        <td class="text-muted small"><?php echo date('d/m', strtotime($d['ngay_dang_ky'])); ?></td>
                                        <td class="pe-4"><span class="badge bg-<?php echo $tt_color; ?>"><?php echo $tt_text; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ══ Khóa học mới ══ -->
    <div class="col-lg-6">
        <div class="card stat-card">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-sparkles me-1 text-info"></i>Khóa học mới nhất
                <a href="<?php echo SITE_URL; ?>/admin/khoa-hoc/index.php" class="btn btn-sm btn-outline-primary float-end">
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($kh_moi)): ?>
                    <div class="text-center text-muted py-4"><i class="fas fa-book fa-2x mb-2"></i><p class="mb-0">Chưa có khóa học nào</p></div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($kh_moi as $k): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold small"><?php echo htmlspecialchars($k['ten_khoa_hoc']); ?></div>
                                    <div class="small text-muted">
                                        <?php echo htmlspecialchars($k['ten_gv'] ?? ''); ?> ·
                                        <i class="fas fa-file-lines me-1"></i><?php echo $k['so_bai']; ?> bài ·
                                        <?php echo htmlspecialchars($k['ten_danh_muc'] ?? ''); ?>
                                    </div>
                                </div>
                                <span class="badge bg-success"><?php echo $k['so_dk']; ?> đăng ký</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/../partials/layout_end.php'; ?>
