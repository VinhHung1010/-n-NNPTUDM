<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/chung_chi.php';

$page_title = 'Chứng chỉ - ' . SITE_NAME;
$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$nguoi_dung = $auth->layThongTinNguoiDung();
$cc_model = new ChungChi();

$ma_chung_chi = isset($_GET['ma']) ? trim($_GET['ma']) : '';

if ($ma_chung_chi === '') {
    // Lấy tất cả chứng chỉ của user
    $danh_sach = $cc_model->layTatCa($nguoi_dung['id']);
} else {
    $chung_chi = $cc_model->layTheoMa($ma_chung_chi);
    if (!$chung_chi) {
        header('Location: xem.php');
        exit;
    }
    // Kiểm tra: chứng chỉ phải thuộc về user đang đăng nhập
    if ((int)$chung_chi['id_hoc_vien'] !== (int)$nguoi_dung['id']) {
        header('Location: xem.php');
        exit;
    }
    $danh_sach = [$chung_chi];
}

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container mt-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?php echo VIEWS_URL; ?>/home/index.php">Tiến độ</a></li>
            <li class="breadcrumb-item active">Chứng chỉ</li>
        </ol>
    </nav>

    <?php if ($ma_chung_chi !== '' && !empty($danh_sach)): ?>
        <!-- ══ Trang xem 1 chứng chỉ ══ -->
        <?php $cc = $danh_sach[0]; ?>
        <div class="d-flex justify-content-end mb-3">
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="fas fa-print me-1"></i>In / Lưu PDF
            </button>
        </div>

        <!-- Khung chứng chỉ -->
        <div class="certificate-wrapper">
            <div class="certificate" id="certificate">
                <!-- Viền trang trí -->
                <div class="cert-border">
                    <div class="cert-border-inner">
                        <!-- Header -->
                        <div class="cert-header">
                            <img src="https://img.icons8.com/color/96/graduation-cap.png"
                                 alt="logo" class="cert-logo" onerror="this.style.display='none'">
                            <div class="cert-org"><?php echo htmlspecialchars(SITE_NAME); ?></div>
                            <div class="cert-title">CHỨNG CHỈ HOÀN THÀNH KHÓA HỌC</div>
                            <div class="cert-subtitle">Certificate of Completion</div>
                        </div>

                        <!-- Body -->
                        <div class="cert-body">
                            <p class="cert-label">Họ và tên / Full Name</p>
                            <p class="cert-value cert-name"><?php echo htmlspecialchars($cc['ten_hoc_vien']); ?></p>

                            <p class="cert-label">Đã hoàn thành khóa học / Successfully Completed</p>
                            <p class="cert-value cert-course"><?php echo htmlspecialchars($cc['ten_khoa_hoc']); ?></p>

                            <?php if (!empty($cc['mo_ta_khoa'])): ?>
                            <p class="cert-desc"><?php echo htmlspecialchars($cc['mo_ta_khoa']); ?></p>
                            <?php endif; ?>

                            <div class="cert-info-row">
                                <div class="cert-info-item">
                                    <span class="cert-label">Giảng viên</span>
                                    <span class="cert-info-val"><?php echo htmlspecialchars($cc['ten_giao_vien'] ?? 'E-Learning'); ?></span>
                                </div>
                                <div class="cert-info-item">
                                    <span class="cert-label">Ngày cấp</span>
                                    <span class="cert-info-val"><?php echo date('d/m/Y', strtotime($cc['ngay_cap'])); ?></span>
                                </div>
                                <div class="cert-info-item">
                                    <span class="cert-label">Mã chứng chỉ</span>
                                    <span class="cert-info-val cert-code"><?php echo htmlspecialchars($cc['ma_chung_chi']); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="cert-footer">
                            <div class="cert-stamp">
                                <div class="stamp-circle">
                                    <i class="fas fa-award fa-2x"></i>
                                </div>
                                <div class="stamp-text">XÁC NHẬN</div>
                            </div>
                            <div class="cert-sign">
                                <div class="sign-line"></div>
                                <div class="sign-label">Giám đốc khóa học</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-3 mb-4">
            <a href="xem.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Tất cả chứng chỉ
            </a>
        </div>

    <?php else: ?>
        <!-- ══ Danh sách chứng chỉ ══ -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="fas fa-award me-2" style="color:#D97706"></i>
                    Chứng chỉ của tôi
                </h2>
                <p class="text-muted mb-0"><?php echo count($danh_sach); ?> chứng chỉ đã nhận</p>
            </div>
        </div>

        <?php if (empty($danh_sach)): ?>
            <div class="card text-center py-5" style="border-radius:16px">
                <div class="card-body">
                    <i class="fas fa-award fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Bạn chưa có chứng chỉ nào.</h4>
                    <p class="text-muted mb-3">Hoàn thành 100% khóa học để nhận chứng chỉ.</p>
                    <a href="<?php echo VIEWS_URL; ?>/home/index.php" class="btn btn-primary">
                        <i class="fas fa-graduation-cap me-1"></i>Xem tiến độ học tập
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($danh_sach as $cc): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card cert-card h-100" style="border-radius:16px">
                            <div class="card-body text-center">
                                <div class="cert-thumb mx-auto mb-3">
                                    <?php
                                    $hinh = !empty($cc['hinh_anh'])
                                        ? $cc['hinh_anh']
                                        : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=200&fit=crop';
                                    ?>
                                    <img src="<?php echo $hinh; ?>" alt="course"
                                         onerror="this.src='https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=200&fit=crop'">
                                </div>
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($cc['ten_khoa_hoc']); ?></h5>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('d/m/Y', strtotime($cc['ngay_cap'])); ?>
                                </p>
                                <div class="badge bg-warning text-dark mb-3">
                                    <i class="fas fa-hashtag me-1"></i>
                                    <?php echo htmlspecialchars($cc['ma_chung_chi']); ?>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0">
                                <a href="xem.php?ma=<?php echo urlencode($cc['ma_chung_chi']); ?>"
                                   class="btn btn-warning w-100 text-dark fw-semibold">
                                    <i class="fas fa-award me-1"></i>Xem & In chứng chỉ
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<style>
@media print {
    body * { visibility: hidden; }
    #certificate, #certificate * { visibility: visible; }
    #certificate {
        position: fixed; left: 0; top: 0; width: 100%;
        box-shadow: none !important;
        border: none !important;
    }
    .btn, .breadcrumb, nav, .d-flex.justify-content-end { display: none !important; }
    .container { max-width: 100% !important; padding: 0 !important; }
    .certificate-wrapper { padding: 0 !important; }
    .cert-border { border: 12px solid #D97706 !important; box-shadow: none !important; }
    .cert-border-inner { padding: 40px !important; }
}

.certificate-wrapper {
    background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
    padding: 40px 20px;
    border-radius: 16px;
    display: flex; justify-content: center; align-items: center;
}

.certificate {
    width: 100%; max-width: 800px;
}

.cert-border {
    border: 8px solid #D97706;
    border-style: double;
    padding: 6px;
    background: #fff;
}

.cert-border-inner {
    border: 2px solid #D97706;
    padding: 48px 56px;
    text-align: center;
    position: relative;
    background: linear-gradient(to bottom, #fffdf5 0%, #fff9e6 100%);
}

.cert-header { margin-bottom: 32px; }
.cert-logo { width: 64px; margin-bottom: 12px; }
.cert-org { font-size: 1.1rem; font-weight: 700; color: #92400e; letter-spacing: 2px; }
.cert-title { font-size: 1.5rem; font-weight: 900; color: #1a1a2e; margin-top: 8px; letter-spacing: 3px; }
.cert-subtitle { font-size: 0.9rem; color: #6b7280; font-style: italic; margin-top: 4px; }

.cert-body { margin: 32px 0; }
.cert-label { font-size: 0.8rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px; }
.cert-value { font-size: 1.2rem; font-weight: 700; color: #1a1a2e; margin-bottom: 20px; }
.cert-name { font-size: 1.6rem; color: #D97706; border-bottom: 2px solid #D97706; display: inline-block; padding-bottom: 4px; }
.cert-course { font-size: 1.3rem; color: #1e40af; }
.cert-desc { font-size: 0.9rem; color: #6b7280; margin-top: -12px; }

.cert-info-row { display: flex; justify-content: space-around; flex-wrap: wrap; gap: 20px; margin-top: 24px; }
.cert-info-item { text-align: center; }
.cert-info-val { display: block; font-weight: 700; color: #374151; font-size: 1rem; }
.cert-code { color: #D97706; letter-spacing: 1px; }

.cert-footer { display: flex; justify-content: space-around; align-items: flex-end; margin-top: 40px; padding-top: 24px; border-top: 1px dashed #D97706; }
.cert-stamp { text-align: center; }
.stamp-circle { width: 72px; height: 72px; border: 3px solid #D97706; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 4px; color: #D97706; }
.stamp-text { font-size: 0.7rem; color: #D97706; font-weight: 700; letter-spacing: 2px; }
.cert-sign { text-align: center; }
.sign-line { width: 160px; border-bottom: 1px solid #374151; margin: 0 auto 4px; }
.sign-label { font-size: 0.8rem; color: #6b7280; }

.cert-card { transition: transform .2s, box-shadow .2s; }
.cert-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
.cert-thumb { width: 80px; height: 80px; border-radius: 50%; overflow: hidden; border: 3px solid #D97706; }
.cert-thumb img { width: 100%; height: 100%; object-fit: cover; }
</style>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
