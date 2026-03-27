<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/quiz.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';
require_once __DIR__ . '/../../models/bai_hoc.php';

$page_title = 'Làm bài Quiz - ' . SITE_NAME;
$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . SITE_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$quiz_model = new Quiz();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . SITE_URL . '/khoa-hoc/index.php');
    exit;
}

$quiz = $quiz_model->layTheoId($id);

if (!$quiz) {
    header('Location: ' . SITE_URL . '/khoa-hoc/index.php');
    exit;
}

$cau_hoi = $quiz_model->layCauHoiChiTiet($id);

// Xáo trộn đáp án để không hiển thị đáp án đúng trước
foreach ($cau_hoi as &$ch) {
    shuffle($ch['dap_an']);
}

$error = '';
$ket_qua = null;
$bat_dau = isset($_SESSION['quiz_start'][$id]) ? $_SESSION['quiz_start'][$id] : time();
$thoi_gian_con_lai = $quiz['thoi_gian_phut'] * 60 - (time() - $bat_dau);

if ($thoi_gian_con_lai <= 0) {
    $thoi_gian_con_lai = 0;
}

// Xử lý nộp bài
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nop_bai'])) {
    $dap_an_chon = $_POST['dap_an'] ?? [];
    $thoi_gian_lam = time() - $bat_dau;
    
    $ket_qua = $quiz_model->nopBai(
        $_SESSION['nguoi_dung']['id'],
        $id,
        $dap_an_chon,
        $thoi_gian_lam
    );
    
    unset($_SESSION['quiz_start'][$id]);
}

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container mt-4">
    <?php if ($ket_qua): ?>
        <!-- Hiển thị kết quả -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h3 class="mb-0"><i class="fas fa-trophy me-2"></i>Kết quả bài Quiz</h3>
                    </div>
                    <div class="card-body text-center py-5">
                        <div class="display-1 mb-4">
                            <?php 
                            $tyle = ($ket_qua['diem_so'] / $quiz['diem_toi_da']) * 100;
                            if ($tyle >= 80) {
                                echo '<span class="text-success">' . $ket_qua['diem_so'] . '/' . $quiz['diem_toi_da'] . '</span>';
                            } elseif ($tyle >= 50) {
                                echo '<span class="text-warning">' . $ket_qua['diem_so'] . '/' . $quiz['diem_toi_da'] . '</span>';
                            } else {
                                echo '<span class="text-danger">' . $ket_qua['diem_so'] . '/' . $quiz['diem_toi_da'] . '</span>';
                            }
                            ?>
                        </div>
                        <p class="lead">
                            Bạn trả lời đúng <strong><?php echo $ket_qua['so_cau_dung']; ?></strong> / <strong><?php echo $ket_qua['tong_cau']; ?></strong> câu
                        </p>
                        <div class="progress mb-4" style="height: 30px;">
                            <div class="progress-bar bg-<?php echo ($tyle >= 80) ? 'success' : (($tyle >= 50) ? 'warning' : 'danger'); ?> 
                                 progress-bar-striped" 
                                 role="progressbar" 
                                 style="width: <?php echo $tyle; ?>%">
                                <?php echo round($tyle); ?>%
                            </div>
                        </div>
                        <a href="chi-tiet.php?id=<?php echo $id; ?>" class="btn btn-primary me-2">
                            <i class="fas fa-eye me-2"></i>Xem đáp án
                        </a>
                        <a href="<?php echo SITE_URL; ?>/khoa-hoc/index.php" class="btn btn-secondary">
                            <i class="fas fa-home me-2"></i>Về trang chủ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Form làm bài -->
        <form method="POST" id="quizForm">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><i class="fas fa-question-circle me-2"></i><?php echo $quiz['tieu_de']; ?></h4>
                            <small><?php echo $quiz['mo_ta'] ?? ''; ?></small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-light text-dark fs-6">
                                <i class="fas fa-clock me-1"></i>
                                <span id="countdown"><?php echo gmdate("i:s", $thoi_gian_con_lai); ?></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php $stt = 1; foreach ($cau_hoi as $ch): ?>
                        <div class="mb-4 pb-4 border-bottom">
                            <h5 class="mb-3">
                                <span class="badge bg-primary me-2"><?php echo $stt++; ?></span>
                                <?php echo $ch['noi_dung']; ?>
                            </h5>
                            <div class="ms-4">
                                <?php foreach ($ch['dap_an'] as $da): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="dap_an[<?php echo $ch['id']; ?>]" 
                                               id="da_<?php echo $da['id']; ?>" 
                                               value="<?php echo $da['id']; ?>"
                                               required>
                                        <label class="form-check-label" for="da_<?php echo $da['id']; ?>">
                                            <?php echo $da['noi_dung']; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center">
                        <button type="submit" name="nop_bai" class="btn btn-success btn-lg px-5">
                            <i class="fas fa-paper-plane me-2"></i>Nộp bài
                        </button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php if (!$ket_qua && $thoi_gian_con_lai > 0): ?>
<script>
let timeLeft = <?php echo $thoi_gian_con_lai; ?>;
const countdown = document.getElementById('countdown');

const timer = setInterval(() => {
    timeLeft--;
    if (timeLeft <= 0) {
        clearInterval(timer);
        document.getElementById('quizForm').submit();
    }
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    countdown.textContent = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
}, 1000);
</script>
<?php endif; ?>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
