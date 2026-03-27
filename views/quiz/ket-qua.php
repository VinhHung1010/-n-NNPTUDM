<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/quiz.php';

$page_title = 'Kết quả Quiz - ' . SITE_NAME;
$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$nguoi_dung = $auth->layThongTinNguoiDung();
$quiz_model = new Quiz();
$ket_qua_list = $quiz_model->layKetQua($nguoi_dung['id']);

// Tổng hợp
$tong_quiz = count($ket_qua_list);
$tong_diem = 0;
$diem_max  = 0;
$diem_min  = PHP_INT_MAX;
foreach ($ket_qua_list as $kq) {
    $d = (int)$kq['diem_so'];
    $tong_diem += $d;
    if ($d > $diem_max) $diem_max = $d;
    if ($d < $diem_min) $diem_min = $d;
}
$diem_tb = $tong_quiz > 0 ? round($tong_diem / $tong_quiz) : 0;
if ($diem_min === PHP_INT_MAX) $diem_min = 0;

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container mt-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fas fa-chart-bar me-2" style="color:var(--primary)"></i>
                Kết quả Quiz
            </h2>
            <p class="text-muted mb-0">Lịch sử làm bài của bạn.</p>
        </div>
        <a href="<?php echo VIEWS_URL; ?>/home/index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Quay về
        </a>
    </div>

    <!-- Thống kê -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card text-center py-3">
                <div class="stat-icon mx-auto mb-2" style="background:var(--primary-light);color:var(--primary)">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="fs-4 fw-bold" style="color:var(--primary)"><?php echo $tong_quiz; ?></div>
                <div class="small text-muted">Bài đã làm</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-center py-3">
                <div class="stat-icon mx-auto mb-2" style="background:#FEF3C7;color:#D97706">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="fs-4 fw-bold" style="color:#D97706"><?php echo $diem_tb; ?></div>
                <div class="small text-muted">Điểm trung bình</div>
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
        <div class="col-sm-6 col-lg-3">
            <div class="card text-center py-3">
                <div class="stat-icon mx-auto mb-2" style="background:#FEF2F2;color:#DC2626">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="fs-4 fw-bold" style="color:#DC2626"><?php echo $diem_min; ?></div>
                <div class="small text-muted">Điểm thấp nhất</div>
            </div>
        </div>
    </div>

    <!-- Danh sách -->
    <div class="card" style="border-radius:16px">
        <div class="card-header bg-white fw-bold">
            <i class="fas fa-list me-2" style="color:var(--primary)"></i>
            Lịch sử làm bài
            <span class="badge bg-secondary ms-2"><?php echo $tong_quiz; ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($ket_qua_list)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Bạn chưa làm bài Quiz nào.</h5>
                    <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php" class="btn btn-primary mt-2">
                        <i class="fas fa-search me-1"></i>Khám phá khóa học
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Tên Quiz</th>
                                <th>Bài học</th>
                                <th>Điểm</th>
                                <th>Kết quả</th>
                                <th>Thời gian làm</th>
                                <th>Ngày làm</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ket_qua_list as $index => $kq): ?>
                                <?php
                                $tyle = ($kq['diem_so'] / 100) * 100;
                                if ($tyle >= 80) {
                                    $color = 'success';
                                    $label = 'Xuất sắc';
                                } elseif ($tyle >= 50) {
                                    $color = 'warning text-dark';
                                    $label = 'Đạt';
                                } else {
                                    $color = 'danger';
                                    $label = 'Chưa đạt';
                                }
                                $tgian = gmdate("i:s", $kq['thoi_gian_lam_bai']);
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?php echo $index + 1; ?></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($kq['tieu_de']); ?></td>
                                    <td class="small text-muted"><?php echo htmlspecialchars($kq['ten_bai_hoc'] ?? '-'); ?></td>
                                    <td>
                                        <span class="fw-bold" style="color:var(--<?php echo $color === 'success' ? 'success' : ($color === 'warning text-dark' ? 'warning' : 'danger'); ?>)">
                                            <?php echo $kq['diem_so']; ?>
                                        </span>
                                        <span class="text-muted small">/ 100</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:8px;min-width:60px">
                                                <div class="progress-bar bg-<?php echo $color; ?>"
                                                     style="width:<?php echo $tyle; ?>%"></div>
                                            </div>
                                            <span class="badge bg-<?php echo $color; ?>" style="white-space:nowrap">
                                                <?php echo $label; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-muted small">
                                        <i class="fas fa-stopwatch me-1"></i><?php echo $tgian; ?>
                                    </td>
                                    <td class="text-muted small">
                                        <i class="fas fa-calendar me-1"></i><?php echo date('d/m/Y H:i', strtotime($kq['ngay_lam'])); ?>
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

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
