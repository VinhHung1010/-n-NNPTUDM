<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';
require_once __DIR__ . '/../../models/thanh_toan.php';

$page_title = 'Thanh toán khóa học - ' . SITE_NAME;
$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$ma = isset($_GET['ma']) ? trim($_GET['ma']) : '';
if ($ma === '' || !preg_match('/^ORD-[A-F0-9]{16}$/', $ma)) {
    header('Location: ' . VIEWS_URL . '/khoa-hoc/index.php');
    exit;
}

$nd = $auth->layThongTinNguoiDung();
$tt_model = new ThanhToan();
$kh_model = new KhoaHoc();
$gd = $tt_model->layTheoMa($ma);

if (!$gd || (int)$gd['id_hoc_vien'] !== (int)$nd['id']) {
    header('Location: ' . VIEWS_URL . '/khoa-hoc/index.php');
    exit;
}

$khoa_hoc = $kh_model->layTheoId((int)$gd['id_khoa_hoc']);
if (!$khoa_hoc) {
    header('Location: ' . VIEWS_URL . '/khoa-hoc/index.php');
    exit;
}

$thong_bao = '';
$thong_bao_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xac_nhan_thanh_toan_demo'])) {
    $ket_qua = $tt_model->hoanTatThanhToanDemo($ma, $nd['id']);
    if (!empty($ket_qua['success'])) {
        $kh_model->xacNhanDangKySauThanhToan($nd['id'], (int)$gd['id_khoa_hoc']);
        header('Location: ' . VIEWS_URL . '/khoa-hoc/chi-tiet.php?id=' . (int)$gd['id_khoa_hoc'] . '&thanh_toan=ok');
        exit;
    }
    $thong_bao = $ket_qua['message'] ?? 'Thanh toán thất bại.';
    $thong_bao_type = 'danger';
}

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container mt-4" style="max-width:640px">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php">Khóa học</a></li>
            <li class="breadcrumb-item"><a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo (int)$khoa_hoc['id']; ?>"><?php echo htmlspecialchars($khoa_hoc['ten_khoa_hoc']); ?></a></li>
            <li class="breadcrumb-item active">Thanh toán</li>
        </ol>
    </nav>

    <?php if ($thong_bao): ?>
        <div class="alert alert-<?php echo htmlspecialchars($thong_bao_type); ?>"><?php echo htmlspecialchars($thong_bao); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm" style="border-radius:16px">
        <div class="card-header bg-white py-3 fw-bold">
            <i class="fas fa-receipt me-2" style="color:var(--primary)"></i>Chi tiết giao dịch
        </div>
        <div class="card-body">
            <p class="text-muted small mb-2">Mã giao dịch: <code><?php echo htmlspecialchars($ma); ?></code></p>
            <h5 class="fw-bold mb-3"><?php echo htmlspecialchars($khoa_hoc['ten_khoa_hoc']); ?></h5>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">Số tiền</span>
                <span class="price-tag fs-4 mb-0"><?php echo number_format((float)$gd['so_tien'], 0, ',', '.'); ?>đ</span>
            </div>
            <div class="d-flex justify-content-between small text-muted mb-4">
                <span>Kênh</span>
                <span class="badge bg-secondary"><?php echo htmlspecialchars($gd['kenh'] ?? 'demo'); ?></span>
            </div>

            <?php if ($gd['trang_thai'] === 'thanh_cong'): ?>
                <div class="alert alert-success mb-0">
                    <i class="fas fa-check-circle me-1"></i>Giao dịch đã thanh toán thành công.
                </div>
                <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo (int)$khoa_hoc['id']; ?>"
                   class="btn btn-primary w-100 mt-3 py-2 fw-semibold">
                    <i class="fas fa-arrow-left me-1"></i>Quay lại khóa học
                </a>
            <?php elseif ($gd['trang_thai'] === 'cho_thanh_toan'): ?>
                <div class="alert alert-info small mb-3">
                    <strong>Chế độ demo:</strong> Nút bên dưới mô phỏng thanh toán thành công (tương đương callback từ VNPay/MoMo sau khi tích hợp thật).
                </div>
                <form method="POST">
                    <button type="submit" name="xac_nhan_thanh_toan_demo" class="btn btn-success w-100 py-3 fw-semibold">
                        <i class="fas fa-lock-open me-2"></i>Xác nhận thanh toán (demo)
                    </button>
                </form>
                <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo (int)$khoa_hoc['id']; ?>"
                   class="btn btn-link w-100 mt-2 text-muted">
                    Hủy và quay lại
                </a>
            <?php else: ?>
                <div class="alert alert-warning mb-0">Giao dịch không còn ở trạng thái chờ thanh toán.</div>
                <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo (int)$khoa_hoc['id']; ?>"
                   class="btn btn-outline-secondary w-100 mt-3">Quay lại</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
