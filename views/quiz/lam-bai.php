<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/quiz.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';
require_once __DIR__ . '/../../models/bai_hoc.php';

$page_title = 'Làm bài Quiz - ' . SITE_NAME;
$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$quiz_model = new Quiz();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: ' . VIEWS_URL . '/khoa-hoc/index.php'); exit; }

$quiz = $quiz_model->layTheoId($id);
if (!$quiz) { header('Location: ' . VIEWS_URL . '/khoa-hoc/index.php'); exit; }

// Kiểm tra đăng ký khóa học trước khi cho làm
$kh_model = new KhoaHoc();
$nguoi_dung = $auth->layThongTinNguoiDung();
$trang_thai_dk = $nguoi_dung
    ? $kh_model->daDangKy($nguoi_dung['id'], $quiz['id_khoa_hoc'] ?? 0)
    : null;
if ($trang_thai_dk !== 'da_xac_nhan') {
    header('Location: ' . VIEWS_URL . '/bai-hoc/chi-tiet.php?id=' . ($quiz['id_bai_hoc'] ?? 0));
    exit;
}

$cau_hoi = $quiz_model->layCauHoiChiTiet($id);

// Xáo trộn đáp án
foreach ($cau_hoi as &$ch) { shuffle($ch['dap_an']); }

$ket_qua = null;
$bat_dau = $_SESSION['quiz_start'][$id] ?? time();
$thoi_gian_con_lai = max(0, $quiz['thoi_gian_phut'] * 60 - (time() - $bat_dau));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nop_bai'])) {
    $dap_an_chon = $_POST['dap_an'] ?? [];
    $thoi_gian_lam = time() - $bat_dau;
    $ket_qua = $quiz_model->nopBai($_SESSION['nguoi_dung']['id'], $id, $dap_an_chon, $thoi_gian_lam);
    unset($_SESSION['quiz_start'][$id]);
}

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container mt-4">

    <?php if ($ket_qua): ?>
        <!-- ═══ KẾT QUẢ ═══ -->
        <?php
        $tyle = ($ket_qua['diem_so'] / $quiz['diem_toi_da']) * 100;
        $color = $tyle >= 80 ? 'success' : ($tyle >= 50 ? 'warning' : 'danger');
        $messages = [
            'success' => ['Chúc mừng bạn!', 'Xuất sắc! 🎉'],
            'warning' => ['Khá tốt!', 'Cố gắng hơn nữa nhé! 💪'],
            'danger'  => ['Chưa đạt', 'Đừng nản chí, thử lại nhé! 🌟'],
        ];
        [$title, $subtitle] = $messages[$color];
        ?>
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card text-center" style="border-radius:20px;overflow:hidden;border:none">
                    <div class="card-header py-4"
                         style="background:<?php echo $color === 'success' ? 'linear-gradient(135deg,#059669,#10B981)' : ($color === 'warning' ? 'linear-gradient(135deg,#D97706,#F59E0B)' : 'linear-gradient(135deg,#DC2626,#EF4444)'); ?>;color:#fff">
                        <i class="fas fa-<?php echo $color === 'success' ? 'trophy' : ($color === 'warning' ? 'star' : 'x-circle'); ?> fa-3x mb-3"></i>
                        <h2 class="fw-bold mb-1"><?php echo $title; ?></h2>
                        <p class="mb-0 opacity-75"><?php echo $subtitle; ?></p>
                    </div>
                    <div class="card-body py-5">
                        <div class="display-2 fw-bold mb-3" style="color:var(--<?php echo $color === 'success' ? 'success' : ($color === 'warning' ? 'warning' : 'danger'); ?>)">
                            <?php echo $ket_qua['diem_so']; ?><span class="fs-3 text-muted"> / <?php echo $quiz['diem_toi_da']; ?></span>
                        </div>
                        <p class="lead mb-4">
                            Bạn trả lời đúng <strong><?php echo $ket_qua['so_cau_dung']; ?></strong>
                            trên <strong><?php echo $ket_qua['tong_cau']; ?></strong> câu hỏi
                        </p>
                        <div class="progress mx-auto mb-4" style="height:20px;max-width:400px;border-radius:10px">
                            <div class="progress-bar bg-<?php echo $color; ?> progress-bar-striped"
                                 style="width:<?php echo $tyle; ?>%;border-radius:10px">
                                <?php echo round($tyle); ?>%
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <a href="ket-qua.php" class="btn btn-<?php echo $color; ?> px-4 py-2 fw-semibold">
                                <i class="fas fa-chart-bar me-1"></i>Xem lịch sử
                            </a>
                            <a href="<?php echo VIEWS_URL; ?>/home/index.php" class="btn btn-outline-secondary px-4 py-2">
                                <i class="fas fa-home me-1"></i>Về trang chủ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- ═══ FORM LÀM BÀI ═══ -->
        <div class="row g-4">
            <!-- Câu hỏi -->
            <div class="col-lg-8">
                <form method="POST" id="quizForm">
                    <div class="card" style="border-radius:16px">
                        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3"
                             style="border-radius:16px 16px 0 0">
                            <div>
                                <i class="fas fa-circle-question me-2" style="color:var(--primary)"></i>
                                <?php echo htmlspecialchars($quiz['tieu_de']); ?>
                                <?php if (!empty($quiz['mo_ta'])): ?>
                                    <div class="small text-muted fw-normal mt-1"><?php echo htmlspecialchars($quiz['mo_ta']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="text-end">
                                <span class="badge fs-6 px-3 py-2"
                                      style="background:rgba(239,68,68,0.1);color:#DC2626;font-weight:700"
                                      id="countdown-badge">
                                    <i class="fas fa-stopwatch me-1"></i>
                                    <span id="countdown"><?php echo gmdate("i:s", $thoi_gian_con_lai); ?></span>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php $stt = 1; foreach ($cau_hoi as $ch): ?>
                                <div class="mb-4 pb-4 <?php echo $stt > 1 ? 'border-bottom' : ''; ?>">
                                    <h5 class="mb-3">
                                        <span class="badge me-2"
                                              style="background:var(--primary-light);color:var(--primary);font-weight:700;padding:6px 12px">
                                            Câu <?php echo $stt++; ?>
                                        </span>
                                        <span style="font-weight:500"><?php echo htmlspecialchars($ch['noi_dung']); ?></span>
                                    </h5>
                                    <div class="ms-3">
                                        <?php foreach ($ch['dap_an'] as $da): ?>
                                            <label class="d-flex align-items-center gap-3 p-3 mb-2 rounded-3"
                                                   style="background:var(--light);cursor:pointer;border:1.5px solid var(--border);transition:all 0.2s"
                                                   onmouseover="this.style.borderColor='var(--primary)'"
                                                   onmouseout="this.style.borderColor='var(--border)'">
                                                <input class="form-check-input m-0" type="radio"
                                                       name="dap_an[<?php echo $ch['id']; ?>]"
                                                       value="<?php echo $da['id']; ?>" required
                                                       style="width:18px;height:18px">
                                                <span style="font-size:0.95rem"><?php echo htmlspecialchars($da['noi_dung']); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="text-center pt-3">
                                <button type="submit" name="nop_bai" class="btn btn-success px-5 py-2 fw-semibold"
                                        onclick="return confirm('Bạn chắc chắn nộp bài?');">
                                    <i class="fas fa-paper-plane me-1"></i>Nộp bài
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sidebar info -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top:20px;border-radius:16px">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-info-circle me-1" style="color:var(--primary)"></i>Thông tin Quiz
                        </h5>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Quiz</span>
                                <strong class="text-dark"><?php echo count($cau_hoi); ?> câu</strong>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Thời gian</span>
                                <strong class="text-dark"><?php echo $quiz['thoi_gian_phut']; ?> phút</strong>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Điểm tối đa</span>
                                <strong class="text-dark"><?php echo $quiz['diem_toi_da']; ?></strong>
                            </div>
                        </div>

                        <hr>

                        <h6 class="fw-semibold mb-2">Lưu ý</h6>
                        <ul class="small text-muted mb-0 ps-3">
                            <li class="mb-1">Chọn 1 đáp án đúng cho mỗi câu hỏi.</li>
                            <li class="mb-1">Bài sẽ tự động nộp khi hết giờ.</li>
                            <li>Điểm = (số câu đúng / tổng câu) × điểm tối đa.</li>
                        </ul>

                        <hr>

                        <a href="<?php echo VIEWS_URL; ?>/home/index.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-1"></i>Quay về
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php if (!$ket_qua && $thoi_gian_con_lai > 0): ?>
<script>
(function() {
    var timeLeft = <?php echo $thoi_gian_con_lai; ?>;
    var countdown = document.getElementById('countdown');
    var badge = document.getElementById('countdown-badge');

    var timer = setInterval(function() {
        timeLeft--;
        if (timeLeft <= 0) {
            clearInterval(timer);
            document.getElementById('quizForm').submit();
            return;
        }
        var m = Math.floor(timeLeft / 60);
        var s = timeLeft % 60;
        countdown.textContent = m + ':' + (s < 10 ? '0' : '') + s;

        // Warning color when low
        if (timeLeft <= 60) {
            badge.style.background = 'rgba(239,68,68,0.2)';
            badge.style.color = '#DC2626';
        }
    }, 1000);
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
