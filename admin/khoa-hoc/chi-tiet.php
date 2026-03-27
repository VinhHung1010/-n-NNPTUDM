<?php
$page_title = 'Chi tiết Khóa học';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/../models/khoa_hoc.php';

$kh = new KhoaHoc();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$khoa_hoc = $kh->layTheoId($id);

if (!$khoa_hoc) {
    header('Location: index.php');
    exit;
}

$bai_hocs = $kh->layBaiHoc($id);
$hoc_viens = $kh->layHocVien($id);
$thong_bao = '';

// ── Đổi trạng thái từ trang chi tiết ──
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'duyet':
            $kh->capNhatTrangThai($id, 'da_duyet');
            $thong_bao = '<div class="alert alert-success">Đã duyệt khóa học.</div>';
            $khoa_hoc['trang_thai'] = 'da_duyet';
            break;
        case 'an':
            $kh->capNhatTrangThai($id, 'bi_an');
            $thong_bao = '<div class="alert alert-success">Đã ẩn khóa học.</div>';
            $khoa_hoc['trang_thai'] = 'bi_an';
            break;
        case 'hien':
            $kh->capNhatTrangThai($id, 'da_duyet');
            $thong_bao = '<div class="alert alert-success">Đã hiển thị khóa học.</div>';
            $khoa_hoc['trang_thai'] = 'da_duyet';
            break;
        case 'cho_duyet':
            $kh->capNhatTrangThai($id, 'ban_nhap');
            $thong_bao = '<div class="alert alert-success">Đã chuyển sang chờ duyệt.</div>';
            $khoa_hoc['trang_thai'] = 'ban_nhap';
            break;
    }
}

$tt_labels = [
    'da_duyet'  => '<span class="badge bg-success">Đã duyệt</span>',
    'ban_nhap'  => '<span class="badge bg-warning text-dark">Chờ duyệt</span>',
    'bi_an'     => '<span class="badge bg-secondary">Đã ẩn</span>',
];

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="admin-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-0"><i class="fas fa-book me-2 text-primary"></i>Chi tiết Khóa học</h1>
        <p class="text-muted small mb-0">
            <a href="index.php" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Quay lại danh sách</a>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($khoa_hoc['trang_thai'] === 'ban_nhap'): ?>
            <a href="?id=<?php echo $id; ?>&action=duyet" class="btn btn-success">
                <i class="fas fa-check me-1"></i>Duyệt
            </a>
        <?php elseif ($khoa_hoc['trang_thai'] === 'da_duyet'): ?>
            <a href="?id=<?php echo $id; ?>&action=an" class="btn btn-dark">
                <i class="fas fa-eye-slash me-1"></i>Ẩn
            </a>
        <?php elseif ($khoa_hoc['trang_thai'] === 'bi_an'): ?>
            <a href="?id=<?php echo $id; ?>&action=hien" class="btn btn-primary">
                <i class="fas fa-eye me-1"></i>Hiển thị lại
            </a>
        <?php endif; ?>
        <a href="?id=<?php echo $id; ?>&action=cho_duyet" class="btn btn-outline-warning">
            <i class="fas fa-rotate-left me-1"></i>Chờ duyệt
        </a>
    </div>
</div>

<?php echo $thong_bao; ?>

<div class="row g-4">
    <!-- ===== Thông tin khóa học ===== -->
    <div class="col-lg-4">
        <div class="card stat-card mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3"><?php echo htmlspecialchars($khoa_hoc['ten_khoa_hoc']); ?></h2>
                <div class="mb-2">
                    <strong>Trạng thái:</strong>
                    <?php echo $tt_labels[$khoa_hoc['trang_thai']] ?? $khoa_hoc['trang_thai']; ?>
                </div>
                <div class="mb-2">
                    <strong>Giáo viên:</strong>
                    <?php echo htmlspecialchars($khoa_hoc['ten_giao_vien'] ?? '-'); ?>
                </div>
                <div class="mb-2">
                    <strong>Danh mục:</strong>
                    <?php echo htmlspecialchars($khoa_hoc['ten_danh_muc'] ?? '-'); ?>
                </div>
                <div class="mb-2">
                    <strong>Giá:</strong>
                    <?php
                    if ($khoa_hoc['gia_tien'] > 0) {
                        echo number_format($khoa_hoc['gia_tien'], 0, ',', '.') . 'đ';
                    } else {
                        echo '<span class="text-success fw-semibold">Miễn phí</span>';
                    }
                    ?>
                </div>
                <div class="mb-2">
                    <strong>Ngày tạo:</strong>
                    <?php echo date('d/m/Y H:i', strtotime($khoa_hoc['ngay_tao'])); ?>
                </div>
                <?php if ($khoa_hoc['mo_ta']): ?>
                    <hr>
                    <div class="small text-muted"><?php echo nl2br(htmlspecialchars($khoa_hoc['mo_ta'])); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Thống kê -->
        <div class="row g-2">
            <div class="col-6">
                <div class="card stat-card h-100">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-primary"><?php echo count($bai_hocs); ?></div>
                        <div class="small text-muted">Bài học</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card stat-card h-100">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-success"><?php echo count($hoc_viens); ?></div>
                        <div class="small text-muted">Học viên</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card stat-card h-100">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-warning">
                            <?php echo array_sum(array_column($bai_hocs, 'so_quiz')); ?>
                        </div>
                        <div class="small text-muted">Quiz</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card stat-card h-100">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-info">
                            <?php
                            $tong_bai = count($bai_hocs);
                            $tong_hv = count($hoc_viens);
                            echo $tong_hv > 0 ? round($tong_bai / $tong_hv, 1) : 0;
                            ?>
                        </div>
                        <div class="small text-muted">Bài/HV TB</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Bài học & Học viên ===== -->
    <div class="col-lg-8">
        <!-- Bài học -->
        <div class="card stat-card mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-file-lines me-1 text-primary"></i>Danh sách Bài học (<?php echo count($bai_hocs); ?>)
            </div>
            <div class="card-body p-0">
                <?php if (empty($bai_hocs)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-file-circle-xmark fa-2x mb-2"></i>
                        <p class="mb-0">Chưa có bài học nào.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Tiêu đề</th>
                                    <th>Thứ tự</th>
                                    <th>Thời lượng</th>
                                    <th>Quiz</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bai_hocs as $i => $bh): ?>
                                    <tr>
                                        <td class="ps-4 text-muted"><?php echo $i + 1; ?></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($bh['tieu_de']); ?></td>
                                        <td class="text-muted small"><?php echo $bh['thu_tu']; ?></td>
                                        <td class="text-muted small"><?php echo $bh['thoi_luong_phut']; ?> phút</td>
                                        <td>
                                            <?php if ((int)$bh['so_quiz'] > 0): ?>
                                                <span class="badge bg-success"><?php echo $bh['so_quiz']; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
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

        <!-- Học viên -->
        <div class="card stat-card">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-user-graduate me-1 text-primary"></i>Học viên đã đăng ký (<?php echo count($hoc_viens); ?>)
            </div>
            <div class="card-body p-0">
                <?php if (empty($hoc_viens)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-user-slash fa-2x mb-2"></i>
                        <p class="mb-0">Chưa có học viên đăng ký.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Đăng ký</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hoc_viens as $i => $hv): ?>
                                    <?php
                                    $dk_labels = [
                                        'cho_xu_ly' => '<span class="badge bg-warning text-dark">Chờ xử lý</span>',
                                        'da_xac_nhan' => '<span class="badge bg-success">Đã xác nhận</span>',
                                        'da_huy' => '<span class="badge bg-danger">Đã hủy</span>',
                                    ];
                                    ?>
                                    <tr>
                                        <td class="ps-4 text-muted"><?php echo $i + 1; ?></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($hv['ho_ten']); ?></td>
                                        <td class="text-muted small"><?php echo htmlspecialchars($hv['email']); ?></td>
                                        <td class="text-muted small"><?php echo date('d/m/Y', strtotime($hv['ngay_dang_ky'])); ?></td>
                                        <td><?php echo $dk_labels[$hv['trang_thai_dk']] ?? $hv['trang_thai_dk']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/layout_end.php'; ?>
