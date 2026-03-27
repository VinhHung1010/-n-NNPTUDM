<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';
require_once __DIR__ . '/../../models/quiz.php';
require_once __DIR__ . '/../../models/tien_do.php';

$page_title = 'Tiến độ học tập - ' . SITE_NAME;
$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$nguoi_dung = $auth->layThongTinNguoiDung();
$kh_model   = new KhoaHoc();
$quiz_model = new Quiz();
$td_model   = new TienDo();

// Lấy khóa học đã đăng ký
$khoa_dang_ky = $kh_model->layKhoaHocCuaHocVien($nguoi_dung['id']);

// Lấy kết quả quiz
$ket_qua_list = $quiz_model->layKetQua($nguoi_dung['id']);

// Tổng hợp % cho từng khóa học
foreach ($khoa_dang_ky as &$k) {
    $k['phan_tram'] = $td_model->tinhPhanTram($nguoi_dung['id'], $k['id']);
    $k['da_hoan']  = $td_model->daHoanThanhKhoaHoc($nguoi_dung['id'], $k['id']);
}
unset($k);

// Thống kê tổng quan
$tong_khoa = count($khoa_dang_ky);
$tong_bai   = 0;
foreach ($khoa_dang_ky as $k) { $tong_bai += (int)($k['so_bai_hoc'] ?? 0); }

// HV đã hoàn thành đúng trạng thái
$khoa_xac_nhan = array_filter($khoa_dang_ky, fn($k) => $k['trang_thai_dk'] === 'da_xac_nhan');
$khoa_cho_xu_ly = array_filter($khoa_dang_ky, fn($k) => $k['trang_thai_dk'] === 'cho_xu_ly');
$khoa_dhuy = array_filter($khoa_dang_ky, fn($k) => $k['trang_thai_dk'] === 'da_huy');

// Điểm cao nhất
$diem_max = 0;
foreach ($ket_qua_list as $kq) { if ((int)$kq['diem_so'] > $diem_max) $diem_max = (int)$kq['diem_so']; }

// Bài quiz đã làm
$so_quiz_lam = count($ket_qua_list);

// Khóa học gợi ý
$khoa_tat_ca = $kh_model->layTatCa('da_duyet');
$da_dk_ids = array_column($khoa_dang_ky, 'id');
$khoa_goi_y = array_filter($khoa_tat_ca, fn($k) => !in_array((int)$k['id'], array_map('intval', $da_dk_ids)));
$khoa_goi_y = array_slice(array_values($khoa_goi_y), 0, 4);

include __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-4">

    <!-- Tiêu đề -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fas fa-graduation-cap me-2" style="color:var(--primary)"></i>
                Tiến độ học tập
            </h2>
            <p class="text-muted mb-0">
                Xin chào, <strong><?php echo htmlspecialchars($nguoi_dung['ho_ten']); ?></strong>!
                Chúc bạn một ngày học tập hiệu quả.
            </p>
        </div>
        <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php" class="btn btn-primary">
            <i class="fas fa-search me-1"></i>Khám phá khóa học
        </a>
    </div>

    <!-- Thống kê -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card text-center py-3">
                <div class="stat-icon mx-auto mb-2" style="background:var(--primary-light);color:var(--primary)">
                    <i class="fas fa-book"></i>
                </div>
                <div class="fs-4 fw-bold" style="color:var(--primary)"><?php echo count($khoa_xac_nhan); ?></div>
                <div class="small text-muted">Khóa đã xác nhận</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-center py-3">
                <div class="stat-icon mx-auto mb-2" style="background:#FFF7ED;color:#EA580C">
                    <i class="fas fa-file-lines"></i>
                </div>
                <div class="fs-4 fw-bold" style="color:#EA580C"><?php echo $tong_bai; ?></div>
                <div class="small text-muted">Tổng bài học</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-center py-3">
                <div class="stat-icon mx-auto mb-2" style="background:#FEF3C7;color:#D97706">
                    <i class="fas fa-circle-question"></i>
                </div>
                <div class="fs-4 fw-bold" style="color:#D97706"><?php echo $so_quiz_lam; ?></div>
                <div class="small text-muted">Quiz đã làm</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-center py-3">
                <div class="stat-icon mx-auto mb-2" style="background:#F0FDF4;color:#16A34A">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="fs-4 fw-bold" style="color:#16A34A"><?php echo $diem_max; ?></div>
                <div class="small text-muted">Điểm cao nhất</div>
            </div>
        </div>
    </div>

    <!-- Alert: đăng ký chờ duyệt -->
    <?php if (!empty($khoa_cho_xu_ly)): ?>
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
            <i class="fas fa-clock"></i>
            Bạn có <strong><?php echo count($khoa_cho_xu_ly); ?></strong> khóa học đang chờ duyệt.
            Chờ quản trị xác nhận để bắt đầu học.
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- ══ Khóa học đã xác nhận ══ -->
        <div class="col-lg-8">
            <div class="card" style="border-radius:16px">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="fas fa-book me-2" style="color:var(--primary)"></i>
                    Khóa học của tôi
                </div>
                <div class="card-body p-0">
                    <?php if (empty($khoa_xac_nhan)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Bạn chưa có khóa học nào được xác nhận.</h5>
                            <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php" class="btn btn-primary mt-2">
                                <i class="fas fa-search me-1"></i>Khám phá khóa học
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Khóa học</th>
                                        <th>Bài học</th>
                                        <th style="min-width:140px">Tiến độ</th>
                                        <th class="pe-4 text-end">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($khoa_xac_nhan as $k): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold"><?php echo htmlspecialchars($k['ten_khoa_hoc']); ?></div>
                                                <div class="small text-muted">
                                                    <?php echo htmlspecialchars($k['ten_giao_vien'] ?? ''); ?> ·
                                                    <?php echo date('d/m/Y', strtotime($k['ngay_dang_ky_khoa'])); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $k['so_bai_hoc']; ?> bài</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height:8px;border-radius:99px">
                                                        <?php
                                                        $pct = (int)$k['phan_tram'];
                                                        $bar = $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-info' : 'bg-primary');
                                                        ?>
                                                        <div class="progress-bar <?php echo $bar; ?>"
                                                             style="width:<?php echo $pct; ?>%;border-radius:99px"></div>
                                                    </div>
                                                    <span class="fw-semibold small" style="min-width:36px;text-align:right">
                                                        <?php echo $pct; ?>%
                                                    </span>
                                                </div>
                                                <?php if ($pct >= 100): ?>
                                                    <span class="badge bg-success mt-1" style="font-size:0.65rem">
                                                        <i class="fas fa-trophy me-1"></i>Hoàn thành
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <?php if ($k['phan_tram'] >= 100): ?>
                                                    <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo $k['id']; ?>"
                                                       class="btn btn-success btn-sm">
                                                        <i class="fas fa-redo me-1"></i>Ôn tập
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo $k['id']; ?>"
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-play me-1"></i>Học tiếp
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

            <!-- Đăng ký chờ duyệt / đã hủy -->
            <?php if (!empty($khoa_cho_xu_ly)): ?>
                <div class="card mt-3" style="border-radius:16px">
                    <div class="card-header bg-white fw-bold py-3">
                        <i class="fas fa-clock me-2 text-warning"></i>Đang chờ xác nhận
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Khóa học</th>
                                        <th>Ngày đăng ký</th>
                                        <th class="pe-4 text-center">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($khoa_cho_xu_ly as $k): ?>
                                        <tr>
                                            <td class="ps-4 fw-semibold"><?php echo htmlspecialchars($k['ten_khoa_hoc']); ?></td>
                                            <td class="small text-muted"><?php echo date('d/m/Y', strtotime($k['ngay_dang_ky_khoa'])); ?></td>
                                            <td class="pe-4 text-center">
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-clock me-1"></i>Chờ xử lý
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ══ Kết quả Quiz ══ -->
        <div class="col-lg-4">
            <div class="card" style="border-radius:16px">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="fas fa-chart-bar me-2" style="color:var(--accent)"></i>
                    Kết quả Quiz gần đây
                </div>
                <div class="card-body p-0">
                    <?php if (empty($ket_qua_list)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-circle-question fa-3x text-muted mb-3"></i>
                            <p class="text-muted small mb-0">Bạn chưa làm bài Quiz nào.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($ket_qua_list, 0, 5) as $kq): ?>
                                <?php
                                $tyle = (int)$kq['diem_so'];
                                $color = $tyle >= 80 ? 'success' : ($tyle >= 50 ? 'warning text-dark' : 'danger');
                                ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="fw-semibold small" style="flex:1">
                                            <?php echo htmlspecialchars($kq['tieu_de'] ?? ''); ?>
                                        </div>
                                        <span class="badge bg-<?php echo $color; ?> ms-2">
                                            <?php echo $tyle; ?> đ
                                        </span>
                                    </div>
                                    <div class="progress" style="height:6px;border-radius:99px">
                                        <div class="progress-bar bg-<?php echo $color; ?>"
                                             style="width:<?php echo $tyle; ?>%;border-radius:99px"></div>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <i class="fas fa-check-circle me-1"></i><?php echo (int)($kq['so_cau_dung'] ?? 0); ?> câu đúng ·
                                        <i class="fas fa-clock me-1"></i><?php echo date('d/m/Y', strtotime($kq['ngay_lam'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer text-center">
                            <a href="<?php echo VIEWS_URL; ?>/quiz/ket-qua.php" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-list me-1"></i>Xem tất cả kết quả
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Khóa học gợi ý -->
    <?php if (!empty($khoa_goi_y)): ?>
    <div class="mt-4">
        <h4 class="fw-bold mb-3">
            <i class="fas fa-lightbulb me-2" style="color:var(--accent)"></i>Khóa học gợi ý cho bạn
        </h4>
        <div class="row g-3">
            <?php foreach ($khoa_goi_y as $k): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card course-card h-100" style="border-radius:16px">
                        <div class="position-relative">
                            <?php
                            $hinh_anh = !empty($k['hinh_anh'])
                                ? $k['hinh_anh']
                                : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=200&fit=crop';
                            ?>
                            <img src="<?php echo $hinh_anh; ?>" class="card-img-top"
                                 alt="<?php echo htmlspecialchars($k['ten_khoa_hoc']); ?>"
                                 style="height:140px;object-fit:cover;border-radius:16px 16px 0 0"
                                 onerror="this.src='https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=200&fit=crop'">
                            <span class="position-absolute top-0 end-0 badge badge-category m-2">
                                <?php echo htmlspecialchars($k['ten_danh_muc'] ?? ''); ?>
                            </span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title fw-bold"><?php echo htmlspecialchars($k['ten_khoa_hoc']); ?></h6>
                            <p class="card-text small text-muted mt-auto">
                                <i class="fas fa-file-lines me-1"></i><?php echo (int)($k['so_bai_hoc'] ?? 0); ?> bài ·
                                <i class="fas fa-user-tie me-1"></i><?php echo htmlspecialchars($k['ten_giao_vien'] ?? ''); ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0">
                            <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo $k['id']; ?>"
                               class="btn btn-outline-primary w-100 btn-sm">
                                <i class="fas fa-eye me-1"></i>Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
