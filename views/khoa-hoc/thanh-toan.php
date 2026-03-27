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

// Học viên bấm "Đã chuyển khoản" — demo xác nhận
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xac_nhan_da_chuyen'])) {
    $ket_qua = $tt_model->hoanTatThanhToanDemo($ma, $nd['id']);
    if (!empty($ket_qua['success'])) {
        $kh_model->xacNhanDangKySauThanhToan($nd['id'], (int)$gd['id_khoa_hoc']);
        header('Location: ' . VIEWS_URL . '/khoa-hoc/chi-tiet.php?id=' . (int)$gd['id_khoa_hoc'] . '&thanh_toan=ok');
        exit;
    }
    $thong_bao = $ket_qua['message'] ?? 'Xác nhận thất bại.';
    $thong_bao_type = 'danger';
}

// ── Build VietQR URL ──
$so_tien_hien = number_format((float)$gd['so_tien'], 0, ',', '.');
$bank_id_bin   = defined('QR_BANK_ID')   ? QR_BANK_ID   : '970403';
$bank_name     = defined('QR_BANK_NAME') ? QR_BANK_NAME : 'TECHCOMBANK';
$stk           = defined('QR_STK')         ? QR_STK        : '123456789';
$chu_tk        = defined('QR_CHU_TK')    ? QR_CHU_TK     : 'NGUYEN VAN A';

$qr_url = sprintf(
    'https://img.vietqr.io/image/%s-%s.jpg?amount=%d&addInfo=%s&accountName=%s',
    $bank_id_bin,
    rawurlencode($stk),
    (int)$gd['so_tien'],
    rawurlencode($ma),
    rawurlencode($chu_tk)
);

$qr_co_dinh_url = defined('QR_CO_DINH_URL') ? QR_CO_DINH_URL : '';

include __DIR__ . '/../../views/layouts/header.php';
?>

<style>
.bank-transfer-box {
    background: linear-gradient(135deg, #f0f4ff 0%, #e8efff 100%);
    border: 1px solid #d0d9f0;
    border-radius: 16px;
    padding: 24px;
}
.bank-logo-box {
    width: 52px; height: 52px;
    background: white;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.75rem;
    color: var(--primary);
    border: 2px solid var(--primary);
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.account-card {
    background: white;
    border-radius: 10px;
    padding: 14px 16px;
    display: flex; align-items: center; gap: 12px;
    border: 1px solid #e8edf5;
}
.account-card .label { font-size: .72rem; color: #8896ab; text-transform: uppercase; letter-spacing: .5px; }
.account-card .value { font-weight: 600; color: #1e2a3a; font-size: .95rem; word-break: break-all; }
.copy-btn {
    border: 1px solid #d0d9f0; background: #f5f7ff;
    border-radius: 6px; padding: 2px 8px;
    font-size: .75rem; cursor: pointer; color: var(--primary);
    transition: .2s;
}
.copy-btn:hover { background: var(--primary); color: white; }
.qr-amount-tag {
    background: var(--primary); color: white;
    border-radius: 20px; padding: 4px 16px;
    font-weight: 700; font-size: 1.05rem;
    display: inline-block;
}
.qr-pay-img {
    width: 200px;
    height: 200px;
    object-fit: contain;
    border: 3px solid white;
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
    background: #fff;
}
</style>

<div class="container mt-4" style="max-width:560px">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php">Khóa học</a></li>
            <li class="breadcrumb-item"><a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo (int)$khoa_hoc['id']; ?>"><?php echo htmlspecialchars($khoa_hoc['ten_khoa_hoc']); ?></a></li>
            <li class="breadcrumb-item active">Thanh toán</li>
        </ol>
    </nav>

    <?php if ($thong_bao): ?>
        <div class="alert alert-<?php echo htmlspecialchars($thong_bao_type); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($thong_bao); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tóm tắt đơn hàng -->
    <div class="card shadow-sm mb-3" style="border-radius:16px">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small text-muted">Khóa học</div>
                    <div class="fw-semibold" style="max-width:270px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?php echo htmlspecialchars($khoa_hoc['ten_khoa_hoc']); ?>
                    </div>
                </div>
                <div class="text-end">
                    <div class="small text-muted">Thanh toán</div>
                    <div class="price-tag fs-5 mb-0"><?php echo $so_tien_hien; ?>đ</div>
                </div>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between small text-muted">
                <span>Mã giao dịch</span>
                <code><?php echo htmlspecialchars($ma); ?></code>
            </div>
        </div>
    </div>

    <?php if ($gd['trang_thai'] === 'thanh_cong'): ?>
        <!-- Đã thanh toán -->
        <div class="card shadow-sm" style="border-radius:16px">
            <div class="card-body text-center py-5">
                <div style="width:72px;height:72px;background:#d4edda;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                    <i class="fas fa-check fa-2x" style="color:#28a745"></i>
                </div>
                <h5 class="fw-bold text-success mb-2">Thanh toán thành công!</h5>
                <p class="text-muted mb-4">Bạn đã được ghi danh vào khóa học. Giờ có thể bắt đầu học ngay.</p>
                <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo (int)$khoa_hoc['id']; ?>"
                   class="btn btn-success px-5 py-2 fw-semibold">
                    <i class="fas fa-play me-1"></i>Học ngay
                </a>
            </div>
        </div>

    <?php elseif ($gd['trang_thai'] === 'cho_thanh_toan'): ?>
        <!-- QR chuyển khoản ngân hàng -->
        <div class="bank-transfer-box mb-3">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="bank-logo-box">
                    <span><?php echo htmlspecialchars(substr($bank_name, 0, 2)); ?></span>
                </div>
                <div>
                    <div class="fw-bold" style="color:var(--primary)"><?php echo htmlspecialchars($bank_name); ?></div>
                    <div class="small text-muted">Thanh toán qua VietQR</div>
                </div>
            </div>

            <!-- Mã QR thanh toán: cố định + VietQR -->
            <div class="row g-4 justify-content-center mb-3">
                <?php if ($qr_co_dinh_url !== ''): ?>
                <div class="col-sm-6 text-center">
                    <p class="small fw-semibold text-muted mb-2">
                        <i class="fas fa-qrcode me-1"></i>Mã QR thanh toán
                    </p>
                    <img src="<?php echo htmlspecialchars($qr_co_dinh_url); ?>"
                         alt="Mã QR thanh toán"
                         class="rounded qr-pay-img">
                </div>
                <?php endif; ?>
                <div class="col-sm-6 text-center">
                    <p class="small fw-semibold text-muted mb-2">
                        <i class="fas fa-university me-1"></i>VietQR (số tiền &amp; mã đơn)
                    </p>
                    <img src="<?php echo htmlspecialchars($qr_url); ?>"
                         alt="Mã QR VietQR chuyển khoản"
                         class="rounded qr-pay-img"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div style="display:none;width:200px;height:200px;margin:0 auto;border:3px solid #e0e0e0;border-radius:8px;align-items:center;justify-content:center;flex-direction:column;color:#aaa;font-size:.85rem"
                         class="rounded">
                        <i class="fas fa-qrcode fa-3x mb-2"></i>
                        Không tải được VietQR
                    </div>
                </div>
            </div>
            <div class="text-center mb-3">
                <div class="mt-1">
                    <span class="qr-amount-tag"><?php echo $so_tien_hien; ?>đ</span>
                </div>
                <p class="small text-muted mt-2 mb-0">Quét một trong hai mã bằng app ngân hàng / ví, hoặc chuyển khoản thủ công bên dưới.</p>
            </div>

            <!-- Thông tin tài khoản -->
            <div class="account-card mb-2">
                <div>
                    <div class="label">Ngân hàng</div>
                    <div class="value"><?php echo htmlspecialchars($bank_name); ?></div>
                </div>
            </div>
            <div class="account-card mb-2">
                <div style="flex:1">
                    <div class="label">Số tài khoản</div>
                    <div class="value"><?php echo htmlspecialchars($stk); ?></div>
                </div>
                <button type="button" class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($stk, ENT_QUOTES); ?>', this)">
                    <i class="fas fa-copy me-1"></i>Sao chép
                </button>
            </div>
            <div class="account-card mb-2">
                <div>
                    <div class="label">Tên chủ TK</div>
                    <div class="value"><?php echo htmlspecialchars($chu_tk); ?></div>
                </div>
            </div>
            <div class="account-card">
                <div style="flex:1">
                    <div class="label">Nội dung chuyển khoản</div>
                    <div class="value" style="font-size:.82rem"><?php echo htmlspecialchars($ma); ?></div>
                </div>
                <button type="button" class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($ma, ENT_QUOTES); ?>', this)">
                    <i class="fas fa-copy me-1"></i>Sao chép
                </button>
            </div>
        </div>

        <!-- Hướng dẫn + xác nhận -->
        <div class="card shadow-sm" style="border-radius:16px">
            <div class="card-body">
                <div class="alert alert-info small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>Demo:</strong> sau khi chuyển khoản xong, bấm nút bên dưới để xác nhận thanh toán (hệ thống sẽ tự động kích hoạt khóa học).
                </div>

                <div class="small text-muted mb-3">
                    <strong>Hướng dẫn:</strong><br>
                    1. Mở app ngân hàng (VietinBank/iPayMoMo/ZaloPay...)<br>
                    2. Quét mã QR hoặc nhập thông tin bên trên<br>
                    3. Nhập đúng số tiền <strong><?php echo $so_tien_hien; ?>đ</strong><br>
                    4. Nhập đúng nội dung <code><?php echo htmlspecialchars($ma); ?></code><br>
                    5. Bấm <strong>"Đã chuyển khoản"</strong>
                </div>

                <form method="POST">
                    <button type="submit" name="xac_nhan_da_chuyen" class="btn btn-success w-100 py-3 fw-bold">
                        <i class="fas fa-check-circle me-2"></i>Đã chuyển khoản thành công
                    </button>
                </form>

                <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo (int)$khoa_hoc['id']; ?>"
                   class="btn btn-link w-100 mt-2 text-muted text-center" style="text-decoration:none">
                    Hủy và quay lại
                </a>
            </div>
        </div>

    <?php else: ?>
        <!-- Trạng thái khác (that_bai / huy) -->
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Giao dịch không còn ở trạng thái chờ thanh toán.
        </div>
        <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo (int)$khoa_hoc['id']; ?>"
           class="btn btn-outline-secondary">Quay lại khóa học</a>
    <?php endif; ?>

</div>

<script>
function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(function() {
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Đã chép!';
        btn.style.background = 'var(--primary)';
        btn.style.color = 'white';
        setTimeout(function() {
            btn.innerHTML = orig;
            btn.style.background = '#f5f7ff';
            btn.style.color = 'var(--primary)';
        }, 1800);
    });
}
</script>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
